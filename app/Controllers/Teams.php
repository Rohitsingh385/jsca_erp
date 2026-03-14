<?php
// app/Controllers/Teams.php
namespace App\Controllers;

class Teams extends BaseController
{
    // ── GET /teams ────────────────────────────────────────────
    public function index()
    {
        $search   = $this->request->getGet('q');
        $category = $this->request->getGet('category');
        $status   = $this->request->getGet('status') ?? 'Active';

        $builder = $this->db->table('teams t')
            ->select('t.*, d.name as district_name,
                      p.full_name as captain_name,
                      (SELECT COUNT(*) FROM team_players tp WHERE tp.team_id = t.id AND tp.is_current = 1) as player_count,
                      (SELECT COUNT(*) FROM team_coaches tc WHERE tc.team_id = t.id AND tc.is_current = 1) as coach_count')
            ->join('districts d', 'd.id = t.district_id', 'left')
            ->join('players p',   'p.id = t.captain_id',  'left');

        if ($search)   $builder->groupStart()->like('t.name', $search)->orLike('t.jsca_team_id', $search)->groupEnd();
        if ($category) $builder->where('t.category', $category);
        if ($status)   $builder->where('t.status', $status);

        $teams = $builder->orderBy('t.name')->get()->getResultArray();

        return $this->render('teams/index', [
            'pageTitle' => 'Team Registry — JSCA ERP',
            'teams'     => $teams,
            'search'    => $search,
            'category'  => $category,
            'status'    => $status,
        ]);
    }

    // ── GET /teams/create ─────────────────────────────────────
    public function create()
    {
        $this->requirePermission('teams');
        return $this->render('teams/create', [
            'pageTitle'   => 'Create Team — JSCA ERP',
            'districts'   => $this->db->table('districts')->where('is_active', 1)->orderBy('name')->get()->getResultArray(),
            'tournaments' => $this->db->table('tournaments')->where('status !=', 'Completed')->orderBy('name')->get()->getResultArray(),
        ]);
    }

    // ── POST /teams/store ─────────────────────────────────────
    public function store()
    {
        $this->requirePermission('teams');

        $rules = [
            'name'     => 'required|min_length[2]|max_length[100]',
            'category' => 'required|in_list[U14,U16,U19,Senior,Masters]',
        ];
        if (!$this->validate($rules)) {
            return redirect()->back()->with('errors', $this->validator->getErrors())->withInput();
        }

        $post = $this->request->getPost();

        $logoPath = null;
        $logo     = $this->request->getFile('logo');
        if ($logo && $logo->isValid() && !$logo->hasMoved()) {
            $name = $logo->getRandomName();
            $logo->move(WRITEPATH . 'uploads/teams', $name);
            $logoPath = 'uploads/teams/' . $name;
        }

        $data = [
            'jsca_team_id'    => $this->generateTeamId(),
            'name'            => $post['name'],
            'short_name'      => strtoupper($post['short_name'] ?? ''),
            'district_id'     => $post['district_id']    ?: null,
            'tournament_id'   => $post['tournament_id']  ?: null,
            'category'        => $post['category'],
            'home_ground'     => $post['home_ground']    ?? null,
            'jersey_color'    => $post['jersey_color']   ?? null,
            'manager_name'    => $post['manager_name']   ?? null,
            'manager_phone'   => $post['manager_phone']  ?? null,
            'manager_email'   => $post['manager_email']  ?? null,
            'logo_path'       => $logoPath,
            'registered_by'   => session('user_id'),
            'created_at'      => date('Y-m-d H:i:s'),
        ];

        $this->db->table('teams')->insert($data);
        $teamId = $this->db->insertID();
        $this->audit('CREATE', 'teams', $teamId, null, $data);

        return redirect()->to('teams/view/' . $teamId)
            ->with('success', 'Team ' . $data['jsca_team_id'] . ' created successfully.');
    }

    // ── GET /teams/view/:id ───────────────────────────────────
    public function view(int $id)
    {
        $team = $this->db->table('teams t')
            ->select('t.*, d.name as district_name, tr.name as tournament_name,
                      cp.full_name as captain_name, vp.full_name as vice_captain_name')
            ->join('districts d',   'd.id = t.district_id',    'left')
            ->join('tournaments tr','tr.id = t.tournament_id', 'left')
            ->join('players cp',    'cp.id = t.captain_id',    'left')
            ->join('players vp',    'vp.id = t.vice_captain_id','left')
            ->where('t.id', $id)
            ->get()->getRowArray();

        if (!$team) return redirect()->to('teams')->with('error', 'Team not found.');

        $players = $this->db->table('team_players tp')
            ->select('tp.*, p.full_name, p.jsca_player_id, p.role, p.batting_style,
                      p.bowling_style, p.age_category, p.photo_path, p.aadhaar_verified')
            ->join('players p', 'p.id = tp.player_id')
            ->where('tp.team_id', $id)
            ->where('tp.is_current', 1)
            ->orderBy('tp.jersey_no')
            ->get()->getResultArray();

        $coaches = $this->db->table('team_coaches tc')
            ->select('tc.*, c.full_name, c.jsca_coach_id, c.level, c.specialization, c.photo_path')
            ->join('coaches c', 'c.id = tc.coach_id')
            ->where('tc.team_id', $id)
            ->where('tc.is_current', 1)
            ->get()->getResultArray();

        $documents = $this->db->table('team_documents')
            ->where('team_id', $id)
            ->orderBy('created_at', 'DESC')
            ->get()->getResultArray();

        $availablePlayers = $this->db->table('players p')
            ->select('p.id, p.full_name, p.jsca_player_id, p.role, p.age_category')
            ->where('p.status', 'Active')
            ->where('p.age_category', $team['category'])
            ->whereNotIn('p.id', array_column($players, 'player_id') ?: [0])
            ->orderBy('p.full_name')
            ->get()->getResultArray();

        $availableCoaches = $this->db->table('coaches')
            ->where('status', 'Active')
            ->whereNotIn('id', array_column($coaches, 'coach_id') ?: [0])
            ->orderBy('full_name')
            ->get()->getResultArray();

        return $this->render('teams/view', [
            'pageTitle'        => $team['name'] . ' — Team Profile',
            'team'             => $team,
            'players'          => $players,
            'coaches'          => $coaches,
            'documents'        => $documents,
            'availablePlayers' => $availablePlayers,
            'availableCoaches' => $availableCoaches,
        ]);
    }

    // ── GET /teams/edit/:id ───────────────────────────────────
    public function edit(int $id)
    {
        $this->requirePermission('teams');
        $team = $this->db->table('teams')->where('id', $id)->get()->getRowArray();
        if (!$team) return redirect()->to('teams')->with('error', 'Team not found.');

        $players = $this->db->table('team_players tp')
            ->select('p.id, p.full_name, p.jsca_player_id')
            ->join('players p', 'p.id = tp.player_id')
            ->where('tp.team_id', $id)
            ->where('tp.is_current', 1)
            ->get()->getResultArray();

        return $this->render('teams/edit', [
            'pageTitle'   => 'Edit Team — JSCA ERP',
            'team'        => $team,
            'players'     => $players,
            'districts'   => $this->db->table('districts')->where('is_active', 1)->orderBy('name')->get()->getResultArray(),
            'tournaments' => $this->db->table('tournaments')->orderBy('name')->get()->getResultArray(),
        ]);
    }

    // ── POST /teams/update/:id ────────────────────────────────
    public function update(int $id)
    {
        $this->requirePermission('teams');
        $old = $this->db->table('teams')->where('id', $id)->get()->getRowArray();
        if (!$old) return redirect()->to('teams')->with('error', 'Team not found.');

        $rules = ['name' => 'required|min_length[2]|max_length[100]'];
        if (!$this->validate($rules)) {
            return redirect()->back()->with('errors', $this->validator->getErrors())->withInput();
        }

        $post = $this->request->getPost();
        $data = [
            'name'          => $post['name'],
            'short_name'    => strtoupper($post['short_name'] ?? ''),
            'district_id'   => $post['district_id']   ?: null,
            'tournament_id' => $post['tournament_id'] ?: null,
            'category'      => $post['category'],
            'home_ground'   => $post['home_ground']   ?? null,
            'jersey_color'  => $post['jersey_color']  ?? null,
            'captain_id'    => $post['captain_id']    ?: null,
            'vice_captain_id'=> $post['vice_captain_id'] ?: null,
            'manager_name'  => $post['manager_name']  ?? null,
            'manager_phone' => $post['manager_phone'] ?? null,
            'manager_email' => $post['manager_email'] ?? null,
            'status'        => $post['status']        ?? 'Active',
            'updated_at'    => date('Y-m-d H:i:s'),
        ];

        $logo = $this->request->getFile('logo');
        if ($logo && $logo->isValid() && !$logo->hasMoved()) {
            $name = $logo->getRandomName();
            $logo->move(WRITEPATH . 'uploads/teams', $name);
            $data['logo_path'] = 'uploads/teams/' . $name;
        }

        $this->db->table('teams')->where('id', $id)->update($data);
        $this->audit('UPDATE', 'teams', $id, $old, $data);
        return redirect()->to('teams/view/' . $id)->with('success', 'Team updated.');
    }

    // ── POST /teams/delete/:id ────────────────────────────────
    public function delete(int $id)
    {
        $this->requirePermission('teams');
        $this->db->table('teams')->where('id', $id)->update(['status' => 'Inactive', 'updated_at' => date('Y-m-d H:i:s')]);
        $this->audit('DELETE', 'teams', $id);
        return redirect()->to('teams')->with('success', 'Team deactivated.');
    }

    // ── POST /teams/add-player/:id ────────────────────────────
    public function addPlayer(int $id)
    {
        $this->requirePermission('teams');
        $playerId = (int)$this->request->getPost('player_id');
        $jerseyNo = $this->request->getPost('jersey_no') ?: null;

        $exists = $this->db->table('team_players')
            ->where('team_id', $id)->where('player_id', $playerId)->where('is_current', 1)
            ->countAllResults();

        if ($exists) return redirect()->back()->with('error', 'Player already in this team.');

        $this->db->table('team_players')->insert([
            'team_id'   => $id,
            'player_id' => $playerId,
            'jersey_no' => $jerseyNo,
            'joined_at' => date('Y-m-d'),
            'is_current'=> 1,
        ]);
        $this->audit('PLAYER_ADDED', 'teams', $id, null, ['player_id' => $playerId]);
        return redirect()->to('teams/view/' . $id)->with('success', 'Player added to team.');
    }

    // ── POST /teams/remove-player/:teamId/:playerId ───────────
    public function removePlayer(int $teamId, int $playerId)
    {
        $this->requirePermission('teams');
        $this->db->table('team_players')
            ->where('team_id', $teamId)->where('player_id', $playerId)
            ->update(['is_current' => 0, 'left_at' => date('Y-m-d')]);
        $this->audit('PLAYER_REMOVED', 'teams', $teamId, null, ['player_id' => $playerId]);
        return redirect()->to('teams/view/' . $teamId)->with('success', 'Player removed from team.');
    }

    // ── POST /teams/add-coach/:id ─────────────────────────────
    public function addCoach(int $id)
    {
        $this->requirePermission('teams');
        $coachId = (int)$this->request->getPost('coach_id');
        $role    = $this->request->getPost('coach_role') ?? 'Head Coach';

        $exists = $this->db->table('team_coaches')
            ->where('team_id', $id)->where('coach_id', $coachId)->where('is_current', 1)
            ->countAllResults();

        if ($exists) return redirect()->back()->with('error', 'Coach already assigned to this team.');

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
        $this->requirePermission('teams');
        $this->db->table('team_coaches')
            ->where('team_id', $teamId)->where('coach_id', $coachId)
            ->update(['is_current' => 0, 'to_date' => date('Y-m-d')]);
        $this->audit('COACH_REMOVED', 'teams', $teamId, null, ['coach_id' => $coachId]);
        return redirect()->to('teams/view/' . $teamId)->with('success', 'Coach removed from team.');
    }

    // ── POST /teams/upload-doc/:id ────────────────────────────
    public function uploadDoc(int $id)
    {
        $this->requirePermission('teams');
        $file = $this->request->getFile('document');
        if (!$file || !$file->isValid()) return redirect()->back()->with('error', 'Invalid file.');
        if (!in_array(strtolower($file->getExtension()), ['jpg','jpeg','png','pdf'])) {
            return redirect()->back()->with('error', 'Only JPG, PNG or PDF allowed.');
        }

        $fileName = $file->getRandomName();
        $file->move(WRITEPATH . 'uploads/team_docs/' . $id, $fileName);

        $this->db->table('team_documents')->insert([
            'team_id'     => $id,
            'doc_type'    => $this->request->getPost('doc_type'),
            'label'       => $this->request->getPost('label'),
            'file_path'   => 'uploads/team_docs/' . $id . '/' . $fileName,
            'file_name'   => $file->getClientName(),
            'mime_type'   => $file->getMimeType(),
            'uploaded_by' => session('user_id'),
            'created_at'  => date('Y-m-d H:i:s'),
        ]);

        $this->audit('DOC_UPLOADED', 'teams', $id);
        return redirect()->to('teams/view/' . $id)->with('success', 'Document uploaded.');
    }

    // ── POST /teams/verify-doc/:docId ─────────────────────────
    public function verifyDoc(int $docId)
    {
        $this->requirePermission('teams');
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
        $this->requirePermission('teams');
        $doc = $this->db->table('team_documents')->where('id', $docId)->get()->getRowArray();
        if (!$doc) return redirect()->back()->with('error', 'Document not found.');

        $fullPath = WRITEPATH . $doc['file_path'];
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
