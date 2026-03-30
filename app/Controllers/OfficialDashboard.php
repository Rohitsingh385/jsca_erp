<?php
// app/Controllers/OfficialDashboard.php
namespace App\Controllers;

class OfficialDashboard extends BaseController
{
    private function requireOfficial(): array
    {
        $role = $this->currentUser['role_name'] ?? '';
        if (!in_array($role, ['umpire', 'scorer', 'referee', 'match_referee'])) {
            return redirect()->to('/dashboard');
        }

        $official = $this->db->table('officials o')
            ->select('o.*, ot.name as type_name, d.name as district_name')
            ->join('official_types ot', 'ot.id = o.official_type_id')
            ->join('districts d', 'd.id = o.district_id')
            ->where('o.user_id', $this->currentUser['id'])
            ->get()->getRowArray();

        if (!$official) {
            session()->destroy();
            return redirect()->to('/login')->with('error', 'Official profile not found. Contact administrator.');
        }

        return $official;
    }

    // ── GET /official/dashboard ───────────────────────────────
    public function index()
    {
        $official = $this->requireOfficial();
        if (!is_array($official)) return $official;

        // All matches this official has been assigned to
        $matches = $this->db->table('match_officials mo')
            ->select('mo.*, f.match_number, f.match_date, f.match_time, f.status as fixture_status,
                      f.result_summary, f.team_a_score, f.team_a_wickets, f.team_b_score, f.team_b_wickets,
                      t.id as tournament_id, t.name as tournament_name, t.age_category, t.format,
                      t.overs as tournament_overs, t.status as tournament_status,
                      ta.name as team_a, tb.name as team_b,
                      v.name as venue_name,
                      ot.name as official_role')
            ->join('fixtures f',        'f.id = mo.match_id')
            ->join('tournaments t',     't.id = f.tournament_id')
            ->join('teams ta',          'ta.id = f.team_a_id')
            ->join('teams tb',          'tb.id = f.team_b_id')
            ->join('venues v',          'v.id = f.venue_id', 'left')
            ->join('official_types ot', 'ot.id = mo.official_type_id')
            ->where('mo.official_id', $official['id'])
            ->orderBy('f.match_date', 'DESC')
            ->get()->getResultArray();

        // Group by tournament for payment request logic
        $byTournament = [];
        foreach ($matches as $m) {
            $tid = $m['tournament_id'];
            if (!isset($byTournament[$tid])) {
                $byTournament[$tid] = [
                    'tournament_name'   => $m['tournament_name'],
                    'tournament_status' => $m['tournament_status'],
                    'matches'           => [],
                    'total_fee'         => 0,
                    'paid'              => 0,
                    'payreq'            => 0,
                ];
            }
            $byTournament[$tid]['matches'][]   = $m;
            $byTournament[$tid]['total_fee']  += (float)($m['PAmt'] ?? 0);
            if (!empty($m['Pdate'])) {
                $byTournament[$tid]['paid'] += (float)($m['PAmt'] ?? 0);
            }
            // If any match has payreq=1, mark whole tournament as requested
            if (!empty($m['payreq'])) {
                $byTournament[$tid]['payreq'] = 1;
            }
        }

        $totalMatches  = count($matches);
        $totalEarned   = array_sum(array_column($matches, 'PAmt'));
        $totalPaid     = array_sum(array_map(fn($m) => !empty($m['Pdate']) ? (float)($m['PAmt'] ?? 0) : 0, $matches));
        $totalPending  = $totalEarned - $totalPaid;

        $upcoming = array_filter($matches, fn($m) =>
            $m['fixture_status'] === 'Scheduled' && $m['match_date'] >= date('Y-m-d')
        );

        return $this->renderOfficial('official/dashboard', [
            'pageTitle'     => 'My Dashboard — JSCA',
            'official'      => $official,
            'matches'       => $matches,
            'byTournament'  => $byTournament,
            'upcoming'      => array_values($upcoming),
            'totalMatches'  => $totalMatches,
            'totalEarned'   => $totalEarned,
            'totalPaid'     => $totalPaid,
            'totalPending'  => $totalPending,
        ]);
    }

    // ── GET /official/profile ─────────────────────────────────
    public function profile()
    {
        $official = $this->requireOfficial();
        if (!is_array($official)) return $official;

        return $this->renderOfficial('official/profile', [
            'pageTitle' => 'My Profile — JSCA',
            'official'  => $official,
        ]);
    }

    // ── POST /official/request-payment/:tournament_id ────────
    public function requestPayment(int $tournamentId)
    {
        $official = $this->requireOfficial();
        if (!is_array($official)) return $official;

        // Verify tournament is Completed
        $tournament = $this->db->table('tournaments')->where('id', $tournamentId)->get()->getRowArray();
        if (!$tournament || $tournament['status'] !== 'Completed') {
            return redirect()->to('official/dashboard')->with('error', 'Payment can only be requested after the tournament is completed.');
        }

        // Mark all unpaid, unrequested match_officials rows for this official + tournament
        $this->db->table('match_officials mo')
            ->join('fixtures f', 'f.id = mo.match_id')
            ->where('f.tournament_id', $tournamentId)
            ->where('mo.official_id', $official['id'])
            ->where('mo.payreq', 0)
            ->where('mo.Pdate IS NULL')
            ->set('mo.payreq', 1)
            ->update();

        return redirect()->to('official/dashboard')->with('success', 'Payment request submitted for ' . $tournament['name'] . '. Finance team will process it shortly.');
    }

    // ── Render with official layout ───────────────────────────
    private function renderOfficial(string $view, array $data = []): string
    {
        $data['currentUser'] = $this->currentUser;
        $data['content']     = view($view, $data);
        return view('official/layout', $data);
    }
}
