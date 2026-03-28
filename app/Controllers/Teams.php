<?php
// app/Controllers/Teams.php
namespace App\Controllers;

class Teams extends BaseController
{
    // ── GET /teams ────────────────────────────────────────────
    public function index()
    {
        $search     = $this->request->getGet('q');
        $statusFilter = $this->request->getGet('status');
        $districtFilter = $this->request->getGet('district_id');

        $districtIds = $this->getAllowedDistrictIdsFlat();

        $builder = $this->db->table('teams t')
            ->select('t.*, d.name as district_name, tr.name as tournament_name,
                      tr.age_category,
                      cp.full_name as captain_name,
                      (SELECT COUNT(*) FROM team_players tp WHERE tp.team_id = t.id) as player_count')
            ->join('districts d',    'd.id = t.district_id',    'left')
            ->join('tournaments tr', 'tr.id = t.tournament_id', 'left')
            ->join('players cp',     'cp.id = t.captain_id',    'left');

        if (empty($districtIds)) {
            $builder->where('1=0');
        } else {
            $builder->whereIn('t.district_id', $districtIds);
        }

        if ($search)         $builder->like('t.name', $search);
        if ($statusFilter)   $builder->where('t.status', $statusFilter);
        if ($districtFilter) $builder->where('t.district_id', $districtFilter);

        $teams = $builder->orderBy('t.name')->get()->getResultArray();

        $districts = $this->db->table('districts')
            ->whereIn('id', $districtIds ?: [0])
            ->where('is_active', 1)->orderBy('name')->get()->getResultArray();

        return $this->render('teams/index', [
            'pageTitle'      => 'Teams — JSCA ERP',
            'teams'          => $teams,
            'districts'      => $districts,
            'search'         => $search,
            'statusFilter'   => $statusFilter,
            'districtFilter' => $districtFilter,
            'canManage'      => $this->can('players'),
        ]);
    }

    // ── GET /teams/create ─────────────────────────────────────
    public function create()
    {
        $this->requirePermission('players');

        $districtIds = $this->getAllowedDistrictIdsFlat();

        // Only show tournaments in Registration status + deadline not passed
        $today       = date('Y-m-d');
        $tournaments = $this->db->table('tournaments t')
            ->select('t.id, t.name, t.age_category, t.status, t.max_teams, t.registration_deadline,
                (SELECT COUNT(*) FROM teams tm WHERE tm.tournament_id=t.id AND tm.status="Confirmed") as confirmed_count')
            ->where('t.status', 'Registration')
            ->groupStart()
                ->where('t.registration_deadline IS NULL')
                ->orWhere('t.registration_deadline >=', $today)
            ->groupEnd()
            ->orderBy('t.name')->get()->getResultArray();

        // Filter out tournaments that have hit max confirmed teams
        $tournaments = array_filter($tournaments, function($tr) {
            return empty($tr['max_teams']) || $tr['confirmed_count'] < $tr['max_teams'];
        });
        $tournaments = array_values($tournaments);

        $districts = $this->db->table('districts')
            ->whereIn('id', $districtIds ?: [0])
            ->where('is_active', 1)->orderBy('name')->get()->getResultArray();

        return $this->render('teams/form', [
            'pageTitle'   => 'Register Team — JSCA ERP',
            'team'        => null,
            'tournaments' => $tournaments,
            'districts'   => $districts,
        ]);
    }

    // ── POST /teams/store ─────────────────────────────────────
    public function store()
    {
        $this->requirePermission('players');

        $rules = [
            'name'          => 'required|min_length[2]|max_length[150]',
            'tournament_id' => 'required|is_natural_no_zero',
            'district_id'   => 'required|is_natural_no_zero',
            'zone'          => 'permit_empty|in_list[North,South,East,West,Central,None]',
            'manager_name'  => 'required|min_length[2]|max_length[100]',
            'manager_phone' => 'required|regex_match[/^[6-9][0-9]{9}$/]',
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()->with('errors', $this->validator->getErrors())->withInput();
        }

        $post = $this->request->getPost();

        // Enforce district access
        if (!$this->canAccessDistrict((int)$post['district_id'])) {
            return redirect()->back()->with('error', 'You do not have access to the selected district.')->withInput();
        }

        // Verify tournament is open for registration
        $tournament = $this->db->table('tournaments')->where('id', $post['tournament_id'])->get()->getRowArray();
        if (!$tournament || $tournament['status'] !== 'Registration') {
            return redirect()->back()->with('error', 'This tournament is not open for registration.')->withInput();
        }

        // Check registration deadline
        if (!empty($tournament['registration_deadline']) && $tournament['registration_deadline'] < date('Y-m-d')) {
            return redirect()->back()->with('error', 'Registration deadline for this tournament has passed.')->withInput();
        }

        // Hard block when confirmed teams hit max_teams
        if (!empty($tournament['max_teams'])) {
            $confirmed = $this->db->table('teams')
                ->where('tournament_id', $post['tournament_id'])
                ->where('status', 'Confirmed')
                ->countAllResults();
            if ($confirmed >= $tournament['max_teams']) {
                return redirect()->back()->with('error', 'This tournament is full. Maximum confirmed teams (' . $tournament['max_teams'] . ') already reached.')->withInput();
            }
        }

        $data = [
            'jsca_team_id'  => $this->generateTeamId(),
            'tournament_id' => $post['tournament_id'],
            'district_id'   => $post['district_id'],
            'name'          => $post['name'],
            'zone'          => $post['zone'] ?: 'None',
            'manager_name'  => $post['manager_name'],
            'manager_phone' => $post['manager_phone'],
            'registered_by' => session('user_id'),
            'status'        => 'Registered',
            'created_at'    => date('Y-m-d H:i:s'),
        ];

        $this->db->table('teams')->insert($data);
        $teamId = $this->db->insertID();
        $this->audit('CREATE', 'teams', $teamId, null, $data);

        return redirect()->to('teams/view/' . $teamId)
            ->with('success', 'Team ' . $data['jsca_team_id'] . ' registered successfully.');
    }

    // ── GET /teams/view/:id ───────────────────────────────────
    public function view(int $id)
    {
        $team = $this->db->table('teams t')
            ->select('t.*, d.name as district_name, tr.name as tournament_name,
                      tr.age_category, tr.status as tournament_status,
                      cp.full_name as captain_name, vp.full_name as vice_captain_name')
            ->join('districts d',    'd.id = t.district_id',     'left')
            ->join('tournaments tr', 'tr.id = t.tournament_id',  'left')
            ->join('players cp',     'cp.id = t.captain_id',     'left')
            ->join('players vp',     'vp.id = t.vice_captain_id','left')
            ->where('t.id', $id)
            ->get()->getRowArray();

        if (!$team) return redirect()->to('teams')->with('error', 'Team not found.');

        // District access check
        if (!$this->canAccessDistrict((int)$team['district_id'])) {
            return redirect()->to('teams')->with('error', 'Access denied.');
        }

        $players = $this->db->table('team_players tp')
            ->select('tp.*, p.full_name, p.jsca_player_id, p.role, p.batting_style, p.bowling_style, p.photo_path')
            ->join('players p', 'p.id = tp.player_id')
            ->where('tp.team_id', $id)
            ->orderBy('tp.jersey_number')
            ->get()->getResultArray();

        $coaches = $this->db->table('team_coaches tc')
            ->select('tc.*, c.full_name, c.jsca_coach_id, c.level, c.specialization')
            ->join('coaches c', 'c.id = tc.coach_id')
            ->where('tc.team_id', $id)
            ->where('tc.is_current', 1)
            ->get()->getResultArray();

        $documents = $this->db->table('team_documents')
            ->where('team_id', $id)
            ->orderBy('created_at', 'DESC')
            ->get()->getResultArray();

        // Available players: eligibility filters
        $tournament = $this->db->table('tournaments')->where('id', $team['tournament_id'])->get()->getRowArray();

        $takenPlayerIds = $this->db->table('team_players tp')
            ->select('tp.player_id')
            ->join('teams t', 't.id = tp.team_id')
            ->where('t.tournament_id', $team['tournament_id'])
            ->get()->getResultArray();
        $takenIds = array_column($takenPlayerIds, 'player_id') ?: [0];

        $availableQuery = $this->db->table('players p')
            ->select('p.id, p.full_name, p.jsca_player_id, p.role, p.age_category, p.gender')
            ->where('p.status', 'Active')
            ->whereNotIn('p.id', $takenIds)
            ->orderBy('p.full_name');

        // District filter: District/Club tournaments = same district only, State/National = any district
        $tournamentType = $tournament['type'] ?? 'District';
        if (in_array($tournamentType, ['District', 'Club'])) {
            $availableQuery->where('p.district_id', $team['district_id']);
        }

        // Age category filter
        if (!empty($tournament['age_category']) && $tournament['age_category'] !== 'Open') {
            $availableQuery->where('p.age_category', $tournament['age_category']);
        }

        // Gender filter
        if (!empty($tournament['gender']) && $tournament['gender'] !== 'Mixed') {
            $availableQuery->where('p.gender', $tournament['gender']);
        }

        $availablePlayers = $availableQuery->get()->getResultArray();

        $availableCoaches = $this->db->table('coaches')
            ->where('status', 'Active')
            ->whereNotIn('id', array_column($coaches, 'coach_id') ?: [0])
            ->orderBy('full_name')
            ->get()->getResultArray();

        $canManage = $this->can('players') && $this->canAccessDistrict((int)$team['district_id']);

        return $this->render('teams/view', [
            'pageTitle'        => $team['name'] . ' — JSCA ERP',
            'team'             => $team,
            'players'          => $players,
            'coaches'          => $coaches,
            'documents'        => $documents,
            'availablePlayers' => $availablePlayers,
            'availableCoaches' => $availableCoaches,
            'canManage'        => $canManage,
        ]);
    }

    // ── GET /teams/edit/:id ───────────────────────────────────
    public function edit(int $id)
    {
        $this->requirePermission('players');

        $team = $this->db->table('teams')->where('id', $id)->get()->getRowArray();
        if (!$team) return redirect()->to('teams')->with('error', 'Team not found.');

        if (!$this->canAccessDistrict((int)$team['district_id'])) {
            return redirect()->to('teams')->with('error', 'Access denied.');
        }

        $districtIds = $this->getAllowedDistrictIdsFlat();

        return $this->render('teams/form', [
            'pageTitle'   => 'Edit Team — JSCA ERP',
            'team'        => $team,
            'tournaments' => $this->db->table('tournaments')->whereIn('status', ['Draft', 'Registration'])->orderBy('name')->get()->getResultArray(),
            'districts'   => $this->db->table('districts')->whereIn('id', $districtIds ?: [0])->where('is_active', 1)->orderBy('name')->get()->getResultArray(),
        ]);
    }

    // ── POST /teams/update/:id ────────────────────────────────
    public function update(int $id)
    {
        $this->requirePermission('players');

        $old = $this->db->table('teams')->where('id', $id)->get()->getRowArray();
        if (!$old) return redirect()->to('teams')->with('error', 'Team not found.');

        if (!$this->canAccessDistrict((int)$old['district_id'])) {
            return redirect()->to('teams')->with('error', 'Access denied.');
        }

        $rules = [
            'name'          => 'required|min_length[2]|max_length[150]',
            'status'        => 'required|in_list[Registered,Confirmed,Withdrawn]',
            'zone'          => 'permit_empty|in_list[North,South,East,West,Central,None]',
            'manager_name'  => 'required|min_length[2]|max_length[100]',
            'manager_phone' => 'required|regex_match[/^[6-9][0-9]{9}$/]',
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()->with('errors', $this->validator->getErrors())->withInput();
        }

        $post = $this->request->getPost();
        $data = [
            'name'          => $post['name'],
            'zone'          => $post['zone'] ?: 'None',
            'manager_name'  => $post['manager_name'],
            'manager_phone' => $post['manager_phone'],
            'status'        => $post['status'],
            'updated_at'    => date('Y-m-d H:i:s'),
        ];

        $this->db->table('teams')->where('id', $id)->update($data);
        $this->audit('UPDATE', 'teams', $id, $old, $data);

        return redirect()->to('teams/view/' . $id)->with('success', 'Team updated.');
    }

    // ── POST /teams/add-player/:id ────────────────────────────
    public function addPlayer(int $id)
    {
        $this->requirePermission('players');

        $team = $this->db->table('teams')->where('id', $id)->get()->getRowArray();
        if (!$team || !$this->canAccessDistrict((int)$team['district_id'])) {
            return redirect()->back()->with('error', 'Access denied.');
        }

        // Get tournament for eligibility checks
        $tournament = $this->db->table('tournaments')->where('id', $team['tournament_id'])->get()->getRowArray();

        $playerId  = (int)$this->request->getPost('player_id');
        $jerseyNo  = $this->request->getPost('jersey_number') ?: null;
        $isCaptain = $this->request->getPost('is_captain') ? 1 : 0;
        $isVc      = $this->request->getPost('is_vice_captain') ? 1 : 0;
        $isWk      = $this->request->getPost('is_wk') ? 1 : 0;

        $player = $this->db->table('players')->where('id', $playerId)->get()->getRowArray();
        if (!$player) return redirect()->back()->with('error', 'Player not found.');

        // Gender check — Female tournament only allows Female players, Male only Male
        if ($tournament && $tournament['gender'] !== 'Mixed') {
            if ($player['gender'] !== $tournament['gender']) {
                return redirect()->back()->with('error',
                    'This is a ' . $tournament['gender'] . ' tournament. Player gender (' . $player['gender'] . ') does not match.');
            }
        }

        // Age category check
        if ($tournament && !empty($tournament['age_category']) && $tournament['age_category'] !== 'Open') {
            if ($player['age_category'] !== $tournament['age_category']) {
                return redirect()->back()->with('error',
                    'Player age category (' . $player['age_category'] . ') does not match tournament category (' . $tournament['age_category'] . ').');
            }
        }

        $exists = $this->db->table('team_players')
            ->where('team_id', $id)->where('player_id', $playerId)
            ->countAllResults();
        if ($exists) return redirect()->back()->with('error', 'Player is already in this team.');

        // Block if player is already in another team in the same tournament
        $inOtherTeam = $this->db->table('team_players tp')
            ->join('teams t', 't.id = tp.team_id')
            ->where('tp.player_id', $playerId)
            ->where('t.tournament_id', $team['tournament_id'])
            ->where('tp.team_id !=', $id)
            ->countAllResults();
        if ($inOtherTeam) {
            $otherTeam = $this->db->table('teams t')
                ->select('t.name')
                ->join('team_players tp', 'tp.team_id = t.id')
                ->where('tp.player_id', $playerId)
                ->where('t.tournament_id', $team['tournament_id'])
                ->where('t.id !=', $id)
                ->get()->getRowArray();
            return redirect()->back()->with('error', 'Player is already registered in "' . $otherTeam['name'] . '" for this tournament.');
        }

        $this->db->table('team_players')->insert([
            'team_id'         => $id,
            'player_id'       => $playerId,
            'jersey_number'   => $jerseyNo,
            'is_captain'      => $isCaptain,
            'is_vice_captain' => $isVc,
            'is_wk'           => $isWk,
            'created_at'      => date('Y-m-d H:i:s'),
        ]);

        // Update captain/vice-captain on team record
        if ($isCaptain)  $this->db->table('teams')->where('id', $id)->update(['captain_id' => $playerId]);
        if ($isVc)       $this->db->table('teams')->where('id', $id)->update(['vice_captain_id' => $playerId]);

        $this->audit('PLAYER_ADDED', 'teams', $id, null, ['player_id' => $playerId]);
        return redirect()->to('teams/view/' . $id)->with('success', 'Player added to team.');
    }

    // ── POST /teams/remove-player/:teamId/:playerId ───────────
    public function removePlayer(int $teamId, int $playerId)
    {
        $this->requirePermission('players');

        $team = $this->db->table('teams')->where('id', $teamId)->get()->getRowArray();
        if (!$team || !$this->canAccessDistrict((int)$team['district_id'])) {
            return redirect()->back()->with('error', 'Access denied.');
        }

        $this->db->table('team_players')
            ->where('team_id', $teamId)->where('player_id', $playerId)->delete();

        // Clear captain/vc if removed
        if ($team['captain_id'] == $playerId)
            $this->db->table('teams')->where('id', $teamId)->update(['captain_id' => null]);
        if ($team['vice_captain_id'] == $playerId)
            $this->db->table('teams')->where('id', $teamId)->update(['vice_captain_id' => null]);

        $this->audit('PLAYER_REMOVED', 'teams', $teamId, null, ['player_id' => $playerId]);
        return redirect()->to('teams/view/' . $teamId)->with('success', 'Player removed from team.');
    }

    // ── POST /teams/add-coach/:id ─────────────────────────────
    public function addCoach(int $id)
    {
        $this->requirePermission('players');

        $team = $this->db->table('teams')->where('id', $id)->get()->getRowArray();
        if (!$team || !$this->canAccessDistrict((int)$team['district_id'])) {
            return redirect()->back()->with('error', 'Access denied.');
        }

        $coachId = (int)$this->request->getPost('coach_id');
        $role    = $this->request->getPost('coach_role') ?: 'Head Coach';

        $exists = $this->db->table('team_coaches')
            ->where('team_id', $id)->where('coach_id', $coachId)->where('is_current', 1)
            ->countAllResults();

        if ($exists) return redirect()->back()->with('error', 'Coach is already assigned to this team.');

        $this->db->table('team_coaches')->insert([
            'team_id'    => $id,
            'coach_id'   => $coachId,
            'role'       => $role,
            'from_date'  => date('Y-m-d'),
            'is_current' => 1,
        ]);

        $this->audit('COACH_ADDED', 'teams', $id, null, ['coach_id' => $coachId]);
        return redirect()->to('teams/view/' . $id)->with('success', 'Coach assigned to team.');
    }

    // ── POST /teams/remove-coach/:teamId/:coachId ─────────────
    public function removeCoach(int $teamId, int $coachId)
    {
        $this->requirePermission('players');

        $team = $this->db->table('teams')->where('id', $teamId)->get()->getRowArray();
        if (!$team || !$this->canAccessDistrict((int)$team['district_id'])) {
            return redirect()->back()->with('error', 'Access denied.');
        }

        $this->db->table('team_coaches')
            ->where('team_id', $teamId)->where('coach_id', $coachId)
            ->update(['is_current' => 0, 'to_date' => date('Y-m-d')]);

        $this->audit('COACH_REMOVED', 'teams', $teamId, null, ['coach_id' => $coachId]);
        return redirect()->to('teams/view/' . $teamId)->with('success', 'Coach removed from team.');
    }

    // ── POST /teams/upload-doc/:id ────────────────────────────
    public function uploadDoc(int $id)
    {
        $this->requirePermission('players');

        $team = $this->db->table('teams')->where('id', $id)->get()->getRowArray();
        if (!$team || !$this->canAccessDistrict((int)$team['district_id'])) {
            return redirect()->back()->with('error', 'Access denied.');
        }

        $file = $this->request->getFile('document');
        if (!$file || !$file->isValid()) return redirect()->back()->with('error', 'Invalid file.');
        if (!in_array(strtolower($file->getClientExtension()), ['jpg', 'jpeg', 'png', 'pdf'])) {
            return redirect()->back()->with('error', 'Only JPG, PNG or PDF allowed.');
        }

        $ext      = strtolower($file->getClientExtension());
        $dir      = FCPATH . 'assets/uploads/team_docs/' . $id;
        if (!is_dir($dir)) mkdir($dir, 0775, true);
        $fileName = time() . '_' . bin2hex(random_bytes(8)) . '.' . $ext;
        $file->move($dir, $fileName);

        $this->db->table('team_documents')->insert([
            'team_id'     => $id,
            'doc_type'    => $this->request->getPost('doc_type'),
            'label'       => $this->request->getPost('label'),
            'file_path'   => 'assets/uploads/team_docs/' . $id . '/' . $fileName,
            'file_name'   => $file->getClientName(),
            'mime_type'   => $file->getClientMimeType(),
            'uploaded_by' => session('user_id'),
            'created_at'  => date('Y-m-d H:i:s'),
        ]);

        $this->audit('DOC_UPLOADED', 'teams', $id);
        return redirect()->to('teams/view/' . $id)->with('success', 'Document uploaded.');
    }

    // ── POST /teams/verify-doc/:docId ─────────────────────────
    public function verifyDoc(int $docId)
    {
        $this->requirePermission('players');
        $doc = $this->db->table('team_documents')->where('id', $docId)->get()->getRowArray();
        if (!$doc) return redirect()->back()->with('error', 'Document not found.');

        $this->db->table('team_documents')->where('id', $docId)->update([
            'verified'    => 1,
            'verified_by' => session('user_id'),
            'verified_at' => date('Y-m-d H:i:s'),
        ]);
        $this->audit('DOC_VERIFIED', 'teams', $doc['team_id']);
        return redirect()->to('teams/view/' . $doc['team_id'])->with('success', 'Document verified.');
    }

    // ── POST /teams/delete-doc/:docId ─────────────────────────
    public function deleteDoc(int $docId)
    {
        $this->requirePermission('players');
        $doc = $this->db->table('team_documents')->where('id', $docId)->get()->getRowArray();
        if (!$doc) return redirect()->back()->with('error', 'Document not found.');;

        $fullPath = FCPATH . $doc['file_path'];
        if (file_exists($fullPath)) unlink($fullPath);

        $this->db->table('team_documents')->where('id', $docId)->delete();
        $this->audit('DOC_DELETED', 'teams', $doc['team_id']);
        return redirect()->to('teams/view/' . $doc['team_id'])->with('success', 'Document removed.');
    }

    // ── Private ───────────────────────────────────────────────
    private function generateTeamId(): string
    {
        $year  = date('Y');
        $count = $this->db->table('teams')->countAllResults() + 1;
        return 'JSCA-T-' . $year . '-' . str_pad($count, 4, '0', STR_PAD_LEFT);
    }
}
