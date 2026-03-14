<?php
// app/Controllers/Matches.php
namespace App\Controllers;

class Matches extends BaseController
{
    private string $apiKey;
    private string $apiBase = 'https://api.cricapi.com/v1';

    public function initController(
        \CodeIgniter\HTTP\RequestInterface $request,
        \CodeIgniter\HTTP\ResponseInterface $response,
        \Psr\Log\LoggerInterface $logger
    ) {
        parent::initController($request, $response, $logger);
        $this->apiKey = env('CRICKETDATA_API_KEY', '');
    }

    // ── GET /matches/live ─────────────────────────────────────
    public function live()
    {
        $apiMatches   = $this->fetchApiLiveMatches();
        $localMatches = $this->db->table('live_matches lm')
            ->select('lm.*, ta.name as team_a_name, tb.name as team_b_name')
            ->join('teams ta', 'ta.id = lm.team_a_id', 'left')
            ->join('teams tb', 'tb.id = lm.team_b_id', 'left')
            ->where('lm.status', 'live')
            ->orderBy('lm.updated_at', 'DESC')
            ->get()->getResultArray();

        $teams = $this->db->table('teams')->orderBy('name')->get()->getResultArray();

        return $this->render('matches/live', [
            'pageTitle'    => 'Live Matches — JSCA ERP',
            'apiMatches'   => $apiMatches,
            'localMatches' => $localMatches,
            'teams'        => $teams,
            'apiError'     => empty($this->apiKey) ? 'API key not configured.' : null,
        ]);
    }

    // ── POST /matches/live/store ──────────────────────────────
    public function storeLocal()
    {
        $this->requirePermission('matches');
        $data = [
            'team_a_id'      => $this->request->getPost('team_a_id') ?: null,
            'team_b_id'      => $this->request->getPost('team_b_id') ?: null,
            'team_a_custom'  => $this->request->getPost('team_a_custom'),
            'team_b_custom'  => $this->request->getPost('team_b_custom'),
            'venue'          => $this->request->getPost('venue'),
            'tournament_name'=> $this->request->getPost('tournament_name'),
            'match_type'     => $this->request->getPost('match_type') ?? 'T20',
            'team_a_score'   => $this->request->getPost('team_a_score'),
            'team_b_score'   => $this->request->getPost('team_b_score'),
            'status'         => 'live',
            'notes'          => $this->request->getPost('notes'),
            'created_by'     => session('user_id'),
            'created_at'     => date('Y-m-d H:i:s'),
            'updated_at'     => date('Y-m-d H:i:s'),
        ];
        $this->db->table('live_matches')->insert($data);
        $this->audit('LIVE_MATCH_CREATED', 'live_matches', (int)$this->db->insertID());
        return redirect()->to('matches/live')->with('success', 'Live match added.');
    }

    // ── POST /matches/live/update/:id ─────────────────────────
    public function updateLocal(int $id)
    {
        $this->requirePermission('matches');
        $this->db->table('live_matches')->where('id', $id)->update([
            'team_a_score' => $this->request->getPost('team_a_score'),
            'team_b_score' => $this->request->getPost('team_b_score'),
            'status'       => $this->request->getPost('status') ?? 'live',
            'notes'        => $this->request->getPost('notes'),
            'updated_at'   => date('Y-m-d H:i:s'),
        ]);
        $this->audit('LIVE_MATCH_UPDATED', 'live_matches', $id);
        return redirect()->to('matches/live')->with('success', 'Match updated.');
    }

    // ── POST /matches/live/delete/:id ─────────────────────────
    public function deleteLocal(int $id)
    {
        $this->requirePermission('matches');
        $this->db->table('live_matches')->where('id', $id)->delete();
        $this->audit('LIVE_MATCH_DELETED', 'live_matches', $id);
        return redirect()->to('matches/live')->with('success', 'Match removed.');
    }

    // ── GET /matches/live/api-refresh (AJAX) ──────────────────
    public function apiRefresh()
    {
        return $this->response->setJSON([
            'matches' => $this->fetchApiLiveMatches(),
        ]);
    }

    // ── Private: fetch from cricketdata.org ───────────────────
    private function fetchApiLiveMatches(): array
    {
        if (empty($this->apiKey)) return [];

        $ch = curl_init();
        curl_setopt_array($ch, [
            CURLOPT_URL            => "{$this->apiBase}/currentMatches?apikey={$this->apiKey}&offset=0",
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT        => 10,
            CURLOPT_IPRESOLVE      => CURL_IPRESOLVE_V4,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_SSL_VERIFYHOST => false,
            CURLOPT_USERAGENT      => 'JSCA-ERP/1.0',
        ]);

        $response = curl_exec($ch);
        $error    = curl_error($ch);
        curl_close($ch);

        if ($error || !$response) {
            log_message('error', 'CricketData API cURL error: ' . $error);
            return [];
        }

        $body = json_decode($response, true);
        if (($body['status'] ?? '') !== 'success') {
            log_message('error', 'CricketData API bad response: ' . $response);
            return [];
        }

        return array_map(fn($m) => [
            'id'        => $m['id'],
            'name'      => $m['name'],
            'status'    => $m['status'],
            'venue'     => $m['venue'] ?? '—',
            'date'      => $m['date'] ?? '',
            'teams'     => $m['teams'] ?? [],
            'score'     => $m['score'] ?? [],
            'matchType' => $m['matchType'] ?? '',
        ], $body['data'] ?? []);
    }

    // ── Existing scoring routes (stubs if not yet built) ──────
    public function score(int $id)
    {
        $fixture = $this->db->table('fixtures f')
            ->select('f.*, ta.name as team_a_name, tb.name as team_b_name, v.name as venue_name, t.name as tournament_name')
            ->join('teams ta',       'ta.id = f.team_a_id')
            ->join('teams tb',       'tb.id = f.team_b_id')
            ->join('venues v',       'v.id = f.venue_id')
            ->join('tournaments t',  't.id = f.tournament_id')
            ->where('f.id', $id)
            ->get()->getRowArray();

        if (!$fixture) return redirect()->to('matches/live')->with('error', 'Match not found.');

        $scorecard = $this->db->table('match_scorecards')->where('fixture_id', $id)->get()->getRowArray();

        return $this->render('matches/score', [
            'pageTitle' => 'Score — ' . $fixture['match_number'],
            'fixture'   => $fixture,
            'scorecard' => $scorecard,
        ]);
    }

    public function saveScore(int $id)
    {
        $this->requirePermission('matches');
        $data = [
            'fixture_id'    => $id,
            'team_a_score'  => $this->request->getPost('team_a_score'),
            'team_b_score'  => $this->request->getPost('team_b_score'),
            'updated_at'    => date('Y-m-d H:i:s'),
        ];
        $existing = $this->db->table('match_scorecards')->where('fixture_id', $id)->get()->getRowArray();
        if ($existing) {
            $this->db->table('match_scorecards')->where('fixture_id', $id)->update($data);
        } else {
            $data['created_at'] = date('Y-m-d H:i:s');
            $this->db->table('match_scorecards')->insert($data);
        }
        $this->db->table('fixtures')->where('id', $id)->update(['status' => 'Live', 'updated_at' => date('Y-m-d H:i:s')]);
        $this->audit('SCORE_SAVED', 'fixtures', $id);
        return redirect()->to('matches/score/' . $id)->with('success', 'Score updated.');
    }

    public function scorecard(int $id)
    {
        $fixture   = $this->db->table('fixtures f')
            ->select('f.*, ta.name as team_a_name, tb.name as team_b_name, v.name as venue_name, t.name as tournament_name')
            ->join('teams ta', 'ta.id = f.team_a_id')
            ->join('teams tb', 'tb.id = f.team_b_id')
            ->join('venues v',  'v.id = f.venue_id')
            ->join('tournaments t', 't.id = f.tournament_id')
            ->where('f.id', $id)->get()->getRowArray();
        $scorecard = $this->db->table('match_scorecards')->where('fixture_id', $id)->get()->getRowArray();
        return $this->render('matches/scorecard', [
            'pageTitle' => 'Scorecard — ' . ($fixture['match_number'] ?? $id),
            'fixture'   => $fixture,
            'scorecard' => $scorecard,
        ]);
    }

    public function complete(int $id)
    {
        $this->requirePermission('matches');
        $this->db->table('fixtures')->where('id', $id)->update(['status' => 'Completed', 'updated_at' => date('Y-m-d H:i:s')]);
        $this->audit('MATCH_COMPLETED', 'fixtures', $id);
        return redirect()->to('matches/scorecard/' . $id)->with('success', 'Match marked as completed.');
    }
}
