<?php
// app/Controllers/Tournaments.php
namespace App\Controllers;

class Tournaments extends BaseController
{
    // ── GET /tournaments ──────────────────────────────────────
    public function index()
    {
        $search   = $this->request->getGet('q');
        $status   = $this->request->getGet('status');
        $category = $this->request->getGet('category');
        $format   = $this->request->getGet('format');

        $builder = $this->db->table('tournaments t')
            ->select('t.*,
                (SELECT COUNT(*) FROM teams tm WHERE tm.tournament_id = t.id) as team_count,
                (SELECT COUNT(*) FROM fixtures f WHERE f.tournament_id = t.id) as match_count,
                (SELECT COUNT(*) FROM fixtures f WHERE f.tournament_id = t.id AND f.status = "Completed") as completed_count')
            ->orderBy('t.start_date', 'DESC');

        if ($search)   $builder->groupStart()->like('t.name', $search)->orLike('t.jsca_tournament_id', $search)->groupEnd();
        if ($status)   $builder->where('t.status', $status);
        if ($category) $builder->where('t.category', $category);
        if ($format)   $builder->where('t.format', $format);

        $tournaments = $builder->get()->getResultArray();

        return $this->render('tournaments/index', [
            'pageTitle'   => 'Tournaments — JSCA ERP',
            'tournaments' => $tournaments,
            'search'      => $search,
            'status'      => $status,
            'category'    => $category,
            'format'      => $format,
        ]);
    }

    // ── GET /tournaments/create ───────────────────────────────
    public function create()
    {
        $this->requirePermission('tournaments');
        return $this->render('tournaments/create', [
            'pageTitle' => 'Create Tournament — JSCA ERP',
            'venues'    => $this->db->table('venues')->where('is_active', 1)->orderBy('name')->get()->getResultArray(),
        ]);
    }

    // ── POST /tournaments/store ───────────────────────────────
    public function store()
    {
        $this->requirePermission('tournaments');

        $rules = [
            'name'       => 'required|min_length[3]|max_length[150]',
            'format'     => 'required',
            'category'   => 'required',
            'start_date' => 'permit_empty|valid_date[Y-m-d]',
            'end_date'   => 'permit_empty|valid_date[Y-m-d]',
        ];
        if (!$this->validate($rules)) {
            return redirect()->back()->with('errors', $this->validator->getErrors())->withInput();
        }

        $post = $this->request->getPost();

        $bannerPath = null;
        $banner     = $this->request->getFile('banner');
        if ($banner && $banner->isValid() && !$banner->hasMoved()) {
            $name = $banner->getRandomName();
            $banner->move(WRITEPATH . 'uploads/tournaments', $name);
            $bannerPath = 'uploads/tournaments/' . $name;
        }

        $data = [
            'jsca_tournament_id' => $this->generateTournamentId(),
            'name'               => $post['name'],
            'short_name'         => $post['short_name']       ?? null,
            'edition'            => $post['edition']          ?? null,
            'format'             => $post['format'],
            'category'           => $post['category'],
            'type'               => $post['type']             ?? 'League + Knockout',
            'start_date'         => $post['start_date']       ?: null,
            'end_date'           => $post['end_date']         ?: null,
            'venue_id'           => $post['venue_id']         ?: null,
            'organizer_name'     => $post['organizer_name']   ?? null,
            'organizer_phone'    => $post['organizer_phone']  ?? null,
            'organizer_email'    => $post['organizer_email']  ?? null,
            'prize_pool'         => (float)($post['prize_pool']    ?? 0),
            'winner_prize'       => (float)($post['winner_prize']  ?? 0),
            'runner_prize'       => (float)($post['runner_prize']  ?? 0),
            'max_teams'          => (int)($post['max_teams']       ?? 0) ?: null,
            'overs'              => (int)($post['overs']           ?? 0) ?: null,
            'description'        => $post['description']      ?? null,
            'rules'              => $post['rules']            ?? null,
            'banner_path'        => $bannerPath,
            'status'             => 'Draft',
            'registered_by'      => session('user_id'),
            'created_at'         => date('Y-m-d H:i:s'),
        ];

        $this->db->table('tournaments')->insert($data);
        $id = $this->db->insertID();
        $this->audit('CREATE', 'tournaments', $id, null, $data);

        return redirect()->to('tournaments/view/' . $id)
            ->with('success', 'Tournament ' . $data['jsca_tournament_id'] . ' created.');
    }

    // ── GET /tournaments/view/:id ─────────────────────────────
    public function view(int $id)
    {
        $tournament = $this->db->table('tournaments t')
            ->select('t.*, v.name as venue_name')
            ->join('venues v', 'v.id = t.venue_id', 'left')
            ->where('t.id', $id)
            ->get()->getRowArray();

        if (!$tournament) return redirect()->to('tournaments')->with('error', 'Tournament not found.');

        $teams = $this->db->table('teams tm')
            ->select('tm.*, d.name as district_name,
                p.full_name as captain_name,
                (SELECT COUNT(*) FROM team_players tp WHERE tp.team_id = tm.id AND tp.is_current = 1) as player_count')
            ->join('districts d', 'd.id = tm.district_id', 'left')
            ->join('players p',   'p.id = tm.captain_id',  'left')
            ->where('tm.tournament_id', $id)
            ->orderBy('tm.name')
            ->get()->getResultArray();

        $fixtures = $this->db->table('fixtures f')
            ->select('f.*, ta.name as team_a_name, tb.name as team_b_name, v.name as venue_name')
            ->join('teams ta', 'ta.id = f.team_a_id')
            ->join('teams tb', 'tb.id = f.team_b_id')
            ->join('venues v',  'v.id = f.venue_id', 'left')
            ->where('f.tournament_id', $id)
            ->orderBy('f.match_date')->orderBy('f.match_time')
            ->limit(10)
            ->get()->getResultArray();

        $documents = $this->db->table('tournament_documents')
            ->where('tournament_id', $id)
            ->orderBy('created_at', 'DESC')
            ->get()->getResultArray();

        $availableTeams = $this->db->table('teams')
            ->where('status', 'Active')
            ->where('category', $tournament['category'])
            ->whereNotIn('id', array_column($teams, 'id') ?: [0])
            ->orderBy('name')
            ->get()->getResultArray();

        // Stats
        $stats = [
            'total_matches'     => $this->db->table('fixtures')->where('tournament_id', $id)->countAllResults(),
            'completed_matches' => $this->db->table('fixtures')->where('tournament_id', $id)->where('status', 'Completed')->countAllResults(),
            'live_matches'      => $this->db->table('fixtures')->where('tournament_id', $id)->where('status', 'Live')->countAllResults(),
        ];

        return $this->render('tournaments/view', [
            'pageTitle'      => $tournament['name'],
            'tournament'     => $tournament,
            'teams'          => $teams,
            'fixtures'       => $fixtures,
            'documents'      => $documents,
            'availableTeams' => $availableTeams,
            'stats'          => $stats,
        ]);
    }

    // ── GET /tournaments/edit/:id ─────────────────────────────
    public function edit(int $id)
    {
        $this->requirePermission('tournaments');
        $tournament = $this->db->table('tournaments')->where('id', $id)->get()->getRowArray();
        if (!$tournament) return redirect()->to('tournaments')->with('error', 'Tournament not found.');

        return $this->render('tournaments/edit', [
            'pageTitle'  => 'Edit Tournament — JSCA ERP',
            'tournament' => $tournament,
            'venues'     => $this->db->table('venues')->where('is_active', 1)->orderBy('name')->get()->getResultArray(),
        ]);
    }

    // ── POST /tournaments/update/:id ──────────────────────────
    public function update(int $id)
    {
        $this->requirePermission('tournaments');
        $old = $this->db->table('tournaments')->where('id', $id)->get()->getRowArray();
        if (!$old) return redirect()->to('tournaments')->with('error', 'Tournament not found.');

        $rules = ['name' => 'required|min_length[3]|max_length[150]'];
        if (!$this->validate($rules)) {
            return redirect()->back()->with('errors', $this->validator->getErrors())->withInput();
        }

        $post = $this->request->getPost();
        $data = [
            'name'            => $post['name'],
            'short_name'      => $post['short_name']      ?? null,
            'edition'         => $post['edition']         ?? null,
            'format'          => $post['format'],
            'category'        => $post['category'],
            'type'            => $post['type'],
            'start_date'      => $post['start_date']      ?: null,
            'end_date'        => $post['end_date']        ?: null,
            'venue_id'        => $post['venue_id']        ?: null,
            'organizer_name'  => $post['organizer_name']  ?? null,
            'organizer_phone' => $post['organizer_phone'] ?? null,
            'organizer_email' => $post['organizer_email'] ?? null,
            'prize_pool'      => (float)($post['prize_pool']   ?? 0),
            'winner_prize'    => (float)($post['winner_prize'] ?? 0),
            'runner_prize'    => (float)($post['runner_prize'] ?? 0),
            'max_teams'       => (int)($post['max_teams']      ?? 0) ?: null,
            'overs'           => (int)($post['overs']          ?? 0) ?: null,
            'description'     => $post['description']     ?? null,
            'rules'           => $post['rules']           ?? null,
            'status'          => $post['status'],
            'updated_at'      => date('Y-m-d H:i:s'),
        ];

        $banner = $this->request->getFile('banner');
        if ($banner && $banner->isValid() && !$banner->hasMoved()) {
            $name = $banner->getRandomName();
            $banner->move(WRITEPATH . 'uploads/tournaments', $name);
            $data['banner_path'] = 'uploads/tournaments/' . $name;
        }

        $this->db->table('tournaments')->where('id', $id)->update($data);
        $this->audit('UPDATE', 'tournaments', $id, $old, $data);
        return redirect()->to('tournaments/view/' . $id)->with('success', 'Tournament updated.');
    }

    // ── POST /tournaments/delete/:id ──────────────────────────
    public function delete(int $id)
    {
        $this->requirePermission('tournaments');
        $this->db->table('tournaments')->where('id', $id)->update([
            'status'     => 'Cancelled',
            'updated_at' => date('Y-m-d H:i:s'),
        ]);
        $this->audit('DELETE', 'tournaments', $id);
        return redirect()->to('tournaments')->with('success', 'Tournament cancelled.');
    }

    // ── POST /tournaments/update-status/:id ───────────────────
    public function updateStatus(int $id)
    {
        $this->requirePermission('tournaments');
        $status = $this->request->getPost('status');
        $allowed = ['Draft','Registration Open','Registration Closed','Fixture Ready','Ongoing','Completed','Cancelled'];
        if (!in_array($status, $allowed)) return redirect()->back()->with('error', 'Invalid status.');

        $this->db->table('tournaments')->where('id', $id)->update([
            'status'     => $status,
            'updated_at' => date('Y-m-d H:i:s'),
        ]);
        $this->audit('STATUS_CHANGE', 'tournaments', $id, null, ['status' => $status]);
        return redirect()->to('tournaments/view/' . $id)->with('success', 'Status updated to ' . $status . '.');
    }

    // ── GET /tournaments/teams/:id (legacy) ───────────────────
    public function teams(int $id)
    {
        return redirect()->to('tournaments/view/' . $id);
    }

    // ── POST /tournaments/add-team/:id ────────────────────────
    public function addTeam(int $id)
    {
        $this->requirePermission('tournaments');
        $teamId = (int)$this->request->getPost('team_id');

        $tournament = $this->db->table('tournaments')->where('id', $id)->get()->getRowArray();
        if (!$tournament) return redirect()->back()->with('error', 'Tournament not found.');

        // Check max teams
        if ($tournament['max_teams']) {
            $current = $this->db->table('teams')->where('tournament_id', $id)->countAllResults();
            if ($current >= $tournament['max_teams']) {
                return redirect()->back()->with('error', 'Maximum team limit (' . $tournament['max_teams'] . ') reached.');
            }
        }

        $this->db->table('teams')->where('id', $teamId)->update([
            'tournament_id' => $id,
            'updated_at'    => date('Y-m-d H:i:s'),
        ]);
        $this->audit('TEAM_ADDED', 'tournaments', $id, null, ['team_id' => $teamId]);
        return redirect()->to('tournaments/view/' . $id)->with('success', 'Team registered to tournament.');
    }

    // ── POST /tournaments/remove-team/:id/:teamId ─────────────
    public function removeTeam(int $id, int $teamId)
    {
        $this->requirePermission('tournaments');
        $this->db->table('teams')->where('id', $teamId)->where('tournament_id', $id)->update([
            'tournament_id' => null,
            'updated_at'    => date('Y-m-d H:i:s'),
        ]);
        $this->audit('TEAM_REMOVED', 'tournaments', $id, null, ['team_id' => $teamId]);
        return redirect()->to('tournaments/view/' . $id)->with('success', 'Team removed from tournament.');
    }

    // ── POST /tournaments/upload-doc/:id ──────────────────────
    public function uploadDoc(int $id)
    {
        $this->requirePermission('tournaments');
        $file = $this->request->getFile('document');
        if (!$file || !$file->isValid()) return redirect()->back()->with('error', 'Invalid file.');
        if (!in_array(strtolower($file->getExtension()), ['jpg','jpeg','png','pdf'])) {
            return redirect()->back()->with('error', 'Only JPG, PNG or PDF allowed.');
        }

        $fileName = $file->getRandomName();
        $file->move(WRITEPATH . 'uploads/tournament_docs/' . $id, $fileName);

        $this->db->table('tournament_documents')->insert([
            'tournament_id' => $id,
            'doc_type'      => $this->request->getPost('doc_type'),
            'label'         => $this->request->getPost('label'),
            'file_path'     => 'uploads/tournament_docs/' . $id . '/' . $fileName,
            'file_name'     => $file->getClientName(),
            'mime_type'     => $file->getMimeType(),
            'uploaded_by'   => session('user_id'),
            'created_at'    => date('Y-m-d H:i:s'),
        ]);

        $this->audit('DOC_UPLOADED', 'tournaments', $id);
        return redirect()->to('tournaments/view/' . $id)->with('success', 'Document uploaded.');
    }

    // ── POST /tournaments/verify-doc/:docId ───────────────────
    public function verifyDoc(int $docId)
    {
        $this->requirePermission('tournaments');
        $doc = $this->db->table('tournament_documents')->where('id', $docId)->get()->getRowArray();
        if (!$doc) return redirect()->back()->with('error', 'Document not found.');

        $this->db->table('tournament_documents')->where('id', $docId)->update([
            'verified'    => 1,
            'verified_by' => session('user_id'),
            'verified_at' => date('Y-m-d H:i:s'),
        ]);
        $this->audit('DOC_VERIFIED', 'tournaments', $doc['tournament_id']);
        return redirect()->to('tournaments/view/' . $doc['tournament_id'])->with('success', 'Document verified.');
    }

    // ── POST /tournaments/delete-doc/:docId ───────────────────
    public function deleteDoc(int $docId)
    {
        $this->requirePermission('tournaments');
        $doc = $this->db->table('tournament_documents')->where('id', $docId)->get()->getRowArray();
        if (!$doc) return redirect()->back()->with('error', 'Document not found.');

        $fullPath = WRITEPATH . $doc['file_path'];
        if (file_exists($fullPath)) unlink($fullPath);

        $this->db->table('tournament_documents')->where('id', $docId)->delete();
        $this->audit('DOC_DELETED', 'tournaments', $doc['tournament_id']);
        return redirect()->to('tournaments/view/' . $doc['tournament_id'])->with('success', 'Document removed.');
    }

    // ── Private ───────────────────────────────────────────────
    private function generateTournamentId(): string
    {
        $year  = date('Y');
        $count = $this->db->table('tournaments')->countAllResults() + 1;
        return 'JSCA-TR-' . $year . '-' . str_pad($count, 3, '0', STR_PAD_LEFT);
    }
}
