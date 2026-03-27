<?php
// app/Controllers/Fixtures.php
namespace App\Controllers;

class Fixtures extends BaseController
{
    // ── GET /fixtures ─────────────────────────────────────────
    public function index()
    {
        $tournamentId = $this->request->getGet('tournament_id');
        $status       = $this->request->getGet('status');
        $date         = $this->request->getGet('date');

        $builder = $this->db->table('fixtures f')
            ->select('f.*, t.name as tournament_name, t.age_category, t.overs as tournament_overs,
                      ta.name as team_a_name, tb.name as team_b_name,
                      v.name as venue_name,
                      u1.full_name as umpire1_name, u2.full_name as umpire2_name')
            ->join('tournaments t',  't.id = f.tournament_id')
            ->join('teams ta',       'ta.id = f.team_a_id')
            ->join('teams tb',       'tb.id = f.team_b_id')
            ->join('venues v',       'v.id = f.venue_id', 'left')
            ->join('officials u1',   'u1.id = f.umpire1_id', 'left')
            ->join('officials u2',   'u2.id = f.umpire2_id', 'left')
            ->orderBy('f.match_date', 'DESC')
            ->orderBy('f.match_time', 'ASC');

        if ($tournamentId) $builder->where('f.tournament_id', $tournamentId);
        if ($status)       $builder->where('f.status', $status);
        if ($date)         $builder->where('f.match_date', $date);

        $fixtures     = $builder->get()->getResultArray();
        $tournaments  = $this->db->table('tournaments')
            ->whereIn('status', ['Fixture Ready', 'Ongoing', 'Completed'])
            ->orderBy('name')->get()->getResultArray();

        return $this->render('fixtures/index', [
            'pageTitle'    => 'Fixtures — JSCA ERP',
            'fixtures'     => $fixtures,
            'tournaments'  => $tournaments,
            'tournamentId' => $tournamentId,
            'status'       => $status,
            'date'         => $date,
            'canManage'    => $this->can('fixtures'),
        ]);
    }

    // ── GET /fixtures/create?tournament_id=X ─────────────────
    public function create()
    {
        $this->requirePermission('fixtures');

        $tournamentId = $this->request->getGet('tournament_id');

        $tournaments = $this->db->table('tournaments')
            ->whereIn('status', ['Fixture Ready', 'Ongoing'])
            ->orderBy('name')->get()->getResultArray();

        // Pre-load teams if tournament selected
        $teams = [];
        $tournament = null;
        if ($tournamentId) {
            $tournament = $this->db->table('tournaments')->where('id', $tournamentId)->get()->getRowArray();
            $teams = $this->db->table('teams')
                ->where('tournament_id', $tournamentId)
                ->where('status', 'Confirmed')
                ->orderBy('name')->get()->getResultArray();
        }

        $venues    = $this->_getVenuesForTournament($tournamentId);
        $umpires   = $this->_getOfficialsByType(['Umpire'], $tournamentId);
        $scorers   = $this->_getOfficialsByType(['Scorer'], $tournamentId);
        $referees  = $this->_getOfficialsByType(['Referee', 'Match Referee'], $tournamentId);

        return $this->render('fixtures/form', [
            'pageTitle'    => 'Create Fixture — JSCA ERP',
            'fixture'      => null,
            'tournaments'  => $tournaments,
            'tournament'   => $tournament,
            'teams'        => $teams,
            'venues'       => $venues,
            'umpires'      => $umpires,
            'scorers'      => $scorers,
            'referees'     => $referees,
            'tournamentId' => $tournamentId,
        ]);
    }

    // ── POST /fixtures/store ──────────────────────────────────
    public function store()
    {
        $this->requirePermission('fixtures');

        $rules = [
            'tournament_id' => 'required|is_natural_no_zero',
            'team_a_id'     => 'required|is_natural_no_zero',
            'team_b_id'     => 'required|is_natural_no_zero',
            'match_date'    => 'required|valid_date[Y-m-d]',
            'match_time'    => 'required',
            'venue_id'      => 'required|is_natural_no_zero',
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()->with('errors', $this->validator->getErrors())->withInput();
        }

        $post = $this->request->getPost();

        if ($post['team_a_id'] === $post['team_b_id']) {
            return redirect()->back()->with('error', 'Team A and Team B cannot be the same.')->withInput();
        }

        if (!empty($post['umpire1_id']) && !empty($post['umpire2_id']) && $post['umpire1_id'] === $post['umpire2_id']) {
            return redirect()->back()->with('error', 'Umpire 1 and Umpire 2 cannot be the same person.')->withInput();
        }

        // Check tournament is in valid state
        $tournament = $this->db->table('tournaments')->where('id', $post['tournament_id'])->get()->getRowArray();
        if (!$tournament || !in_array($tournament['status'], ['Fixture Ready', 'Ongoing'])) {
            return redirect()->back()->with('error', 'Tournament must be in Fixture Ready or Ongoing status.')->withInput();
        }

        // Auto match number
        $matchCount = $this->db->table('fixtures')->where('tournament_id', $post['tournament_id'])->countAllResults();
        $matchNumber = 'M' . str_pad($matchCount + 1, 2, '0', STR_PAD_LEFT);

        $data = [
            'tournament_id' => $post['tournament_id'],
            'match_number'  => $post['match_number'] ?: $matchNumber,
            'stage'         => $post['stage'] ?: 'League',
            'match_date'    => $post['match_date'],
            'match_time'    => $post['match_time'],
            'team_a_id'     => $post['team_a_id'],
            'team_b_id'     => $post['team_b_id'],
            'venue_id'      => $post['venue_id'],
            'is_day_night'  => isset($post['is_day_night']) ? 1 : 0,
            'umpire1_id'    => $post['umpire1_id']  ?: null,
            'umpire2_id'    => $post['umpire2_id']  ?: null,
            'scorer_id'     => $post['scorer_id']   ?: null,
            'referee_id'    => $post['referee_id']  ?: null,
            'youtube_url'   => $post['youtube_url'] ?: null,
            'status'        => 'Scheduled',
            'created_by'    => session('user_id'),
            'created_at'    => date('Y-m-d H:i:s'),
        ];

        $this->db->table('fixtures')->insert($data);
        $id = $this->db->insertID();
        $this->audit('CREATE', 'fixtures', $id, null, $data);

        return redirect()->to('fixtures/view/' . $id)
            ->with('success', 'Fixture ' . $data['match_number'] . ' created successfully.');
    }

    // ── GET /fixtures/view/:id ────────────────────────────────
    public function view(int $id)
    {
        $fixture = $this->_getFixtureFull($id);
        if (!$fixture) return redirect()->to('fixtures')->with('error', 'Fixture not found.');

        $teamAPlayers = $this->_getTeamPlayers($fixture['team_a_id']);
        $teamBPlayers = $this->_getTeamPlayers($fixture['team_b_id']);

        $battingStats = $this->db->table('batting_stats bs')
            ->select('bs.*, p.full_name, t.name as team_name')
            ->join('players p', 'p.id = bs.player_id')
            ->join('teams t',   't.id = bs.team_id')
            ->where('bs.fixture_id', $id)
            ->orderBy('bs.innings')->orderBy('bs.id')
            ->get()->getResultArray();

        $bowlingStats = $this->db->table('bowling_stats bs')
            ->select('bs.*, p.full_name, t.name as team_name')
            ->join('players p', 'p.id = bs.player_id')
            ->join('teams t',   't.id = bs.team_id')
            ->where('bs.fixture_id', $id)
            ->orderBy('bs.innings')->orderBy('bs.id')
            ->get()->getResultArray();

        return $this->render('fixtures/view', [
            'pageTitle'    => $fixture['team_a_name'] . ' vs ' . $fixture['team_b_name'],
            'fixture'      => $fixture,
            'teamAPlayers' => $teamAPlayers,
            'teamBPlayers' => $teamBPlayers,
            'battingStats' => $battingStats,
            'bowlingStats' => $bowlingStats,
            'canManage'    => $this->can('fixtures'),
        ]);
    }

    // ── GET /fixtures/edit/:id ────────────────────────────────
    public function edit(int $id)
    {
        $this->requirePermission('fixtures');

        $fixture = $this->_getFixtureFull($id);
        if (!$fixture) return redirect()->to('fixtures')->with('error', 'Fixture not found.');

        if ($fixture['status'] === 'Completed') {
            return redirect()->to('fixtures/view/' . $id)->with('error', 'Completed fixtures cannot be edited.');
        }

        $teams     = $this->db->table('teams')->where('tournament_id', $fixture['tournament_id'])->where('status', 'Confirmed')->orderBy('name')->get()->getResultArray();
        $venues    = $this->_getVenuesForTournament($fixture['tournament_id']);
        $umpires   = $this->_getOfficialsByType(['Umpire'], $fixture['tournament_id']);
        $scorers   = $this->_getOfficialsByType(['Scorer'], $fixture['tournament_id']);
        $referees  = $this->_getOfficialsByType(['Referee', 'Match Referee'], $fixture['tournament_id']);

        return $this->render('fixtures/form', [
            'pageTitle'   => 'Edit Fixture — JSCA ERP',
            'fixture'     => $fixture,
            'tournaments' => [],
            'tournament'  => ['id' => $fixture['tournament_id'], 'name' => $fixture['tournament_name']],
            'teams'       => $teams,
            'venues'      => $venues,
            'umpires'     => $umpires,
            'scorers'     => $scorers,
            'referees'    => $referees,
            'tournamentId'=> $fixture['tournament_id'],
        ]);
    }

    // ── POST /fixtures/update/:id ─────────────────────────────
    public function update(int $id)
    {
        $this->requirePermission('fixtures');

        $fixture = $this->db->table('fixtures')->where('id', $id)->get()->getRowArray();
        if (!$fixture) return redirect()->to('fixtures')->with('error', 'Fixture not found.');
        if ($fixture['status'] === 'Completed') {
            return redirect()->to('fixtures/view/' . $id)->with('error', 'Completed fixtures cannot be edited.');
        }

        $rules = [
            'match_date' => 'required|valid_date[Y-m-d]',
            'match_time' => 'required',
            'venue_id'   => 'required|is_natural_no_zero',
            'team_a_id'  => 'required|is_natural_no_zero',
            'team_b_id'  => 'required|is_natural_no_zero',
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()->with('errors', $this->validator->getErrors())->withInput();
        }

        $post = $this->request->getPost();

        if ($post['team_a_id'] === $post['team_b_id']) {
            return redirect()->back()->with('error', 'Team A and Team B cannot be the same.')->withInput();
        }

        if (!empty($post['umpire1_id']) && !empty($post['umpire2_id']) && $post['umpire1_id'] === $post['umpire2_id']) {
            return redirect()->back()->with('error', 'Umpire 1 and Umpire 2 cannot be the same person.')->withInput();
        }

        $data = [
            'match_number'  => $post['match_number'],
            'stage'         => $post['stage'] ?: 'League',
            'match_date'    => $post['match_date'],
            'match_time'    => $post['match_time'],
            'team_a_id'     => $post['team_a_id'],
            'team_b_id'     => $post['team_b_id'],
            'venue_id'      => $post['venue_id'],
            'is_day_night'  => isset($post['is_day_night']) ? 1 : 0,
            'umpire1_id'    => $post['umpire1_id']  ?: null,
            'umpire2_id'    => $post['umpire2_id']  ?: null,
            'scorer_id'     => $post['scorer_id']   ?: null,
            'referee_id'    => $post['referee_id']  ?: null,
            'youtube_url'   => $post['youtube_url'] ?: null,
            'status'        => $post['status'],
            'updated_at'    => date('Y-m-d H:i:s'),
        ];

        $this->db->table('fixtures')->where('id', $id)->update($data);
        $this->audit('UPDATE', 'fixtures', $id, $fixture, $data);

        return redirect()->to('fixtures/view/' . $id)->with('success', 'Fixture updated.');
    }

    // ── POST /fixtures/delete/:id ─────────────────────────────
    public function delete(int $id)
    {
        $this->requirePermission('fixtures');

        $fixture = $this->db->table('fixtures')->where('id', $id)->get()->getRowArray();
        if (!$fixture) return redirect()->to('fixtures')->with('error', 'Fixture not found.');

        if ($fixture['status'] === 'Completed') {
            return redirect()->to('fixtures/view/' . $id)->with('error', 'Completed fixtures cannot be deleted.');
        }

        $tournamentId = $fixture['tournament_id'];
        $this->db->table('fixtures')->where('id', $id)->delete();
        $this->audit('DELETE', 'fixtures', $id, $fixture);

        return redirect()->to('fixtures?tournament_id=' . $tournamentId)
            ->with('success', 'Fixture deleted.');
    }

    // ── GET /fixtures/tournament/:id ──────────────────────────
    public function tournament(int $id)
    {
        return redirect()->to('fixtures?tournament_id=' . $id);
    }

    // ── Private helpers ───────────────────────────────────────
    private function _getFixtureFull(int $id): ?array
    {
        $f = $this->db->table('fixtures f')
            ->select('f.*,
                t.name as tournament_name, t.age_category, t.format, t.overs as tournament_overs,
                ta.name as team_a_name, tb.name as team_b_name,
                v.name as venue_name, v.address as venue_address,
                u1.full_name as umpire1_name, u2.full_name as umpire2_name,
                sc.full_name as scorer_name, rf.full_name as referee_name,
                tw.name as toss_winner_name, bf.name as batting_first_name')
            ->join('tournaments t',  't.id = f.tournament_id')
            ->join('teams ta',       'ta.id = f.team_a_id')
            ->join('teams tb',       'tb.id = f.team_b_id')
            ->join('venues v',       'v.id = f.venue_id',        'left')
            ->join('officials u1',   'u1.id = f.umpire1_id',     'left')
            ->join('officials u2',   'u2.id = f.umpire2_id',     'left')
            ->join('officials sc',   'sc.id = f.scorer_id',      'left')
            ->join('officials rf',   'rf.id = f.referee_id',     'left')
            ->join('teams tw',       'tw.id = f.toss_winner_id', 'left')
            ->join('teams bf',       'bf.id = f.batting_first_id','left')
            ->where('f.id', $id)
            ->get()->getRowArray();

        return $f ?: null;
    }

    private function _getTeamPlayers(int $teamId): array
    {
        return $this->db->table('team_players tp')
            ->select('p.id, p.full_name, p.jsca_player_id, p.role, tp.is_captain, tp.is_vice_captain, tp.is_wk, tp.jersey_number')
            ->join('players p', 'p.id = tp.player_id')
            ->where('tp.team_id', $teamId)
            ->orderBy('tp.jersey_number')
            ->get()->getResultArray();
    }

    /**
     * Get venues scoped to districts of confirmed teams in the tournament.
     * Superadmin always gets all venues.
     * If no tournament selected yet, fall back to user's allowed districts.
     */
    private function _getVenuesForTournament(?int $tournamentId): array
    {
        $q = $this->db->table('venues v')
            ->select('v.id, v.name, d.name as district_name')
            ->join('districts d', 'd.id = v.district_id')
            ->where('v.is_active', 1)
            ->orderBy('v.name');

        $isSuperadmin = ($this->currentUser['role_name'] ?? '') === 'superadmin';

        if (!$isSuperadmin) {
            if ($tournamentId) {
                // Scope to districts of confirmed teams in this tournament
                $districtIds = $this->db->table('teams')
                    ->select('DISTINCT district_id')
                    ->where('tournament_id', $tournamentId)
                    ->where('status', 'Confirmed')
                    ->get()->getResultArray();
                $districtIds = array_column($districtIds, 'district_id');
            } else {
                $districtIds = $this->getAllowedDistrictIdsFlat();
            }

            if (empty($districtIds)) return [];
            $q->whereIn('v.district_id', $districtIds);
        }

        return $q->get()->getResultArray();
    }

    /**
     * Get officials of given types scoped to districts of the tournament.
     * Superadmin gets all. Others get officials from tournament districts only.
     */
    private function _getOfficialsByType(array $types, ?int $tournamentId): array
    {
        $q = $this->db->table('officials o')
            ->select('o.id, o.full_name, d.name as district_name')
            ->join('official_types ot', 'ot.id = o.official_type_id')
            ->join('districts d', 'd.id = o.district_id')
            ->where('o.status', 'Active')
            ->whereIn('ot.name', $types)
            ->orderBy('o.full_name');

        $isSuperadmin = ($this->currentUser['role_name'] ?? '') === 'superadmin';

        if (!$isSuperadmin) {
            if ($tournamentId) {
                $districtIds = $this->db->table('teams')
                    ->select('DISTINCT district_id')
                    ->where('tournament_id', $tournamentId)
                    ->where('status', 'Confirmed')
                    ->get()->getResultArray();
                $districtIds = array_column($districtIds, 'district_id');
            } else {
                $districtIds = $this->getAllowedDistrictIdsFlat();
            }

            if (empty($districtIds)) return [];
            $q->whereIn('o.district_id', $districtIds);
        }

        return $q->get()->getResultArray();
    }
}
