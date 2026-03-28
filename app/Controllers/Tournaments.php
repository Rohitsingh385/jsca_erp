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
        if ($category) $builder->where('t.age_category', $category);
        if ($format)   $builder->where('t.format', $format);

        return $this->render('tournaments/index', [
            'pageTitle'   => 'Tournaments — JSCA ERP',
            'tournaments' => $builder->get()->getResultArray(),
            'canManage'   => $this->can('tournaments'),
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

        return $this->render('tournaments/form', [
            'pageTitle'  => 'Create Tournament — JSCA ERP',
            'tournament' => null,
            'venues'     => $this->db->table('venues')->where('is_active', 1)->orderBy('name')->get()->getResultArray(),
        ]);
    }

    // ── POST /tournaments/store ───────────────────────────────
    public function store()
    {
        $this->requirePermission('tournaments');

        $rules = [
            'name'                  => 'required|min_length[3]|max_length[200]',
            'season'                => 'required|max_length[10]',
            'age_category'          => 'required|in_list[U14,U15,U16,U19,U23,Senior,Open,Masters,Women]',
            'gender'                => 'required|in_list[Male,Female,Mixed]',
            'format'                => 'required|in_list[T10,T20,ODI-40,ODI-50,Test,Custom]',
            'structure'             => 'required|in_list[Round Robin,Knockout,Group+Knockout,League+Playoffs,Zonal]',
            'overs'                 => 'permit_empty|is_natural_no_zero',
            'start_date'            => 'permit_empty|valid_date[Y-m-d]',
            'end_date'              => 'permit_empty|valid_date[Y-m-d]',
            'registration_deadline' => 'permit_empty|valid_date[Y-m-d]',
            'max_teams'             => 'permit_empty|is_natural_no_zero',
            'prize_pool'            => 'permit_empty|numeric',
            'winner_prize'          => 'permit_empty|numeric',
            'runner_prize'          => 'permit_empty|numeric',
            'organizer_phone'       => 'permit_empty|regex_match[/^[6-9][0-9]{9}$/]',
            'organizer_email'       => 'permit_empty|valid_email',
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()->with('errors', $this->validator->getErrors())->withInput();
        }

        $post = $this->request->getPost();

        $bannerPath = null;
        $banner     = $this->request->getFile('banner');
        if ($banner && $banner->isValid() && !$banner->hasMoved()) {
            $bannerName = $banner->getRandomName();
            $banner->move(WRITEPATH . 'uploads/tournaments', $bannerName);
            $bannerPath = 'uploads/tournaments/' . $bannerName;
        }

        $data = [
            'jsca_tournament_id'    => $this->generateTournamentId(),
            'name'                  => $post['name'],
            'short_name'            => $post['short_name']            ?: null,
            'edition'               => $post['edition']               ?: null,
            'season'                => $post['season'],
            'age_category'          => $post['age_category'],
            'gender'                => $post['gender'],
            'format'                => $post['format'],
            'overs'                 => $post['overs']                 ?: null,
            'structure'             => $post['structure'],
            'is_zonal'              => isset($post['is_zonal']) ? 1 : 0,
            'start_date'            => $post['start_date']            ?: null,
            'end_date'              => $post['end_date']              ?: null,
            'registration_deadline' => $post['registration_deadline'] ?: null,
            'venue_id'              => $post['venue_id']              ?: null,
            'max_teams'             => $post['max_teams']             ?: null,
            'organizer_name'        => $post['organizer_name']        ?: null,
            'organizer_phone'       => $post['organizer_phone']       ?: null,
            'organizer_email'       => $post['organizer_email']       ?: null,
            'prize_pool'            => $post['prize_pool']            ?: 0,
            'winner_prize'          => $post['winner_prize']          ?: 0,
            'runner_prize'          => $post['runner_prize']          ?: 0,
            'description'           => $post['description']           ?: null,
            'rules'                 => $post['rules']                 ?: null,
            'banner_path'           => $bannerPath,
            'status'                => 'Draft',
            'created_by'            => session('user_id'),
            'created_at'            => date('Y-m-d H:i:s'),
        ];

        $this->db->table('tournaments')->insert($data);
        $id = $this->db->insertID();
        $this->audit('CREATE', 'tournaments', $id, null, $data);

        return redirect()->to('/tournaments/view/' . $id)
            ->with('success', 'Tournament ' . $data['jsca_tournament_id'] . ' created.');
    }

    // ── GET /tournaments/view/:id ─────────────────────────────
    public function view(int $id)
    {
        $tournament = $this->db->table('tournaments t')
            ->select('t.*, v.name as venue_name, u.full_name as created_by_name')
            ->join('venues v', 'v.id = t.venue_id', 'left')
            ->join('users u',  'u.id = t.created_by', 'left')
            ->where('t.id', $id)
            ->get()->getRowArray();

        if (!$tournament) return redirect()->to('/tournaments')->with('error', 'Tournament not found.');

        $teams = $this->db->table('teams tm')
            ->select('tm.*, d.name as district_name, p.full_name as captain_name,
                (SELECT COUNT(*) FROM team_players tp WHERE tp.team_id = tm.id) as player_count')
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
            ->get()->getResultArray();

        $stats = [
            'total'     => count($fixtures),
            'completed' => count(array_filter($fixtures, fn($f) => $f['status'] === 'Completed')),
            'live'      => count(array_filter($fixtures, fn($f) => $f['status'] === 'Live')),
            'scheduled' => count(array_filter($fixtures, fn($f) => $f['status'] === 'Scheduled')),
        ];

        $existingIds    = array_column($teams, 'id') ?: [0];
        $availableTeams = $this->db->table('teams')
            ->whereNotIn('id', $existingIds)
            ->where('status !=', 'Withdrawn')
            ->orderBy('name')->get()->getResultArray();

        return $this->render('tournaments/view', [
            'pageTitle'      => $tournament['name'] . ' — JSCA ERP',
            'tournament'     => $tournament,
            'teams'          => $teams,
            'fixtures'       => $fixtures,
            'stats'          => $stats,
            'availableTeams' => $availableTeams,
            'canManage'      => $this->can('tournaments'),
            'documents'      => $this->db->table('tournament_documents')->where('tournament_id', $id)->orderBy('created_at', 'DESC')->get()->getResultArray(),
        ]);
    }

    // ── GET /tournaments/edit/:id ─────────────────────────────
    public function edit(int $id)
    {
        $this->requirePermission('tournaments');

        $tournament = $this->db->table('tournaments')->where('id', $id)->get()->getRowArray();
        if (!$tournament) return redirect()->to('/tournaments')->with('error', 'Tournament not found.');

        if (!in_array($tournament['status'], ['Draft', 'Registration'])) {
            return redirect()->to('/tournaments/view/' . $id)
                ->with('error', 'Tournament details cannot be edited once fixtures are ready or the tournament is underway.');
        }

        return $this->render('tournaments/form', [
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
        if (!$old) return redirect()->to('/tournaments')->with('error', 'Tournament not found.');

        if (!in_array($old['status'], ['Draft', 'Registration'])) {
            return redirect()->to('/tournaments/view/' . $id)
                ->with('error', 'Tournament cannot be edited in its current status.');
        }

        $rules = [
            'name'                  => 'required|min_length[3]|max_length[200]',
            'season'                => 'required|max_length[10]',
            'age_category'          => 'required|in_list[U14,U15,U16,U19,U23,Senior,Open,Masters,Women]',
            'gender'                => 'required|in_list[Male,Female,Mixed]',
            'format'                => 'required|in_list[T10,T20,ODI-40,ODI-50,Test,Custom]',
            'structure'             => 'required|in_list[Round Robin,Knockout,Group+Knockout,League+Playoffs,Zonal]',
            'overs'                 => 'permit_empty|is_natural_no_zero',
            'start_date'            => 'permit_empty|valid_date[Y-m-d]',
            'end_date'              => 'permit_empty|valid_date[Y-m-d]',
            'registration_deadline' => 'permit_empty|valid_date[Y-m-d]',
            'max_teams'             => 'permit_empty|is_natural_no_zero',
            'prize_pool'            => 'permit_empty|numeric',
            'winner_prize'          => 'permit_empty|numeric',
            'runner_prize'          => 'permit_empty|numeric',
            'organizer_phone'       => 'permit_empty|regex_match[/^[6-9][0-9]{9}$/]',
            'organizer_email'       => 'permit_empty|valid_email',
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()->with('errors', $this->validator->getErrors())->withInput();
        }

        $post = $this->request->getPost();
        $data = [
            'name'                  => $post['name'],
            'short_name'            => $post['short_name']            ?: null,
            'edition'               => $post['edition']               ?: null,
            'season'                => $post['season'],
            'age_category'          => $post['age_category'],
            'gender'                => $post['gender'],
            'format'                => $post['format'],
            'overs'                 => $post['overs']                 ?: null,
            'structure'             => $post['structure'],
            'is_zonal'              => isset($post['is_zonal']) ? 1 : 0,
            'start_date'            => $post['start_date']            ?: null,
            'end_date'              => $post['end_date']              ?: null,
            'registration_deadline' => $post['registration_deadline'] ?: null,
            'venue_id'              => $post['venue_id']              ?: null,
            'max_teams'             => $post['max_teams']             ?: null,
            'organizer_name'        => $post['organizer_name']        ?: null,
            'organizer_phone'       => $post['organizer_phone']       ?: null,
            'organizer_email'       => $post['organizer_email']       ?: null,
            'prize_pool'            => $post['prize_pool']            ?: 0,
            'winner_prize'          => $post['winner_prize']          ?: 0,
            'runner_prize'          => $post['runner_prize']          ?: 0,
            'description'           => $post['description']           ?: null,
            'rules'                 => $post['rules']                 ?: null,
            'status'                => $post['status'],
            'updated_at'            => date('Y-m-d H:i:s'),
        ];

        $banner = $this->request->getFile('banner');
        if ($banner && $banner->isValid() && !$banner->hasMoved()) {
            $bannerName = $banner->getRandomName();
            $banner->move(WRITEPATH . 'uploads/tournaments', $bannerName);
            $data['banner_path'] = 'uploads/tournaments/' . $bannerName;
        }

        $this->db->table('tournaments')->where('id', $id)->update($data);
        $this->audit('UPDATE', 'tournaments', $id, $old, $data);

        return redirect()->to('/tournaments/view/' . $id)->with('success', 'Tournament updated.');
    }

    // ── POST /tournaments/update-status/:id ───────────────────
    public function updateStatus(int $id)
    {
        $this->requirePermission('tournaments');

        $tournament = $this->db->table('tournaments')->where('id', $id)->get()->getRowArray();
        if (!$tournament) return redirect()->back()->with('error', 'Tournament not found.');

        $newStatus = $this->request->getPost('status');
        $current   = $tournament['status'];

        // Define valid forward transitions
        $transitions = [
            'Draft'         => ['Registration', 'Cancelled'],
            'Registration'  => ['Draft', 'Fixture Ready', 'Cancelled'],
            'Fixture Ready' => ['Ongoing', 'Registration', 'Cancelled'],
            'Ongoing'       => ['Completed', 'Cancelled'],
            'Completed'     => [],
            'Cancelled'     => [],
        ];

        if (!isset($transitions[$current]) || !in_array($newStatus, $transitions[$current])) {
            return redirect()->back()->with('error', 'Invalid status transition from "' . $current . '" to "' . $newStatus . '".');
        }

        // Moving to Fixture Ready: check confirmed teams
        if ($newStatus === 'Fixture Ready') {
            $confirmedCount = $this->db->table('teams')
                ->where('tournament_id', $id)
                ->where('status', 'Confirmed')
                ->countAllResults();

            if ($confirmedCount < 2) {
                return redirect()->back()->with('error', 'At least 2 confirmed teams are required before marking fixtures as ready. Confirm teams first.');
            }

            if (!empty($tournament['max_teams']) && $confirmedCount > $tournament['max_teams']) {
                return redirect()->back()->with('error', 'Confirmed teams (' . $confirmedCount . ') exceed max_teams (' . $tournament['max_teams'] . '). Withdraw some teams first.');
            }
        }

        $this->db->table('tournaments')->where('id', $id)->update([
            'status'     => $newStatus,
            'updated_at' => date('Y-m-d H:i:s'),
        ]);
        $this->audit('STATUS_CHANGE', 'tournaments', $id, ['status' => $current], ['status' => $newStatus]);

        return redirect()->to('/tournaments/view/' . $id)
            ->with('success', 'Status changed to ' . $newStatus . '.');
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

        return redirect()->to('/tournaments')->with('success', 'Tournament cancelled.');
    }

    // ── GET /tournaments/teams/:id (redirect) ─────────────────
    public function teams(int $id)
    {
        return redirect()->to('/tournaments/view/' . $id);
    }

    // ── POST /tournaments/add-team/:id ────────────────────────
    public function addTeam(int $id)
    {
        $this->requirePermission('tournaments');

        $tournament = $this->db->table('tournaments')->where('id', $id)->get()->getRowArray();
        if (!$tournament) return redirect()->back()->with('error', 'Tournament not found.');

        if (!in_array($tournament['status'], ['Draft', 'Registration'])) {
            return redirect()->back()->with('error', 'Teams can only be added during Draft or Registration phase.');
        }

        // Check deadline
        if (!empty($tournament['registration_deadline']) && $tournament['registration_deadline'] < date('Y-m-d')) {
            return redirect()->back()->with('error', 'Registration deadline has passed for this tournament.');
        }

        $teamId = (int)$this->request->getPost('team_id');

        if ($tournament['max_teams']) {
            $current = $this->db->table('teams')->where('tournament_id', $id)->countAllResults();
            if ($current >= $tournament['max_teams']) {
                return redirect()->back()->with('error', 'Maximum team limit (' . $tournament['max_teams'] . ') reached.');
            }
        }

        $this->db->table('teams')->where('id', $teamId)->update(['tournament_id' => $id]);
        $this->audit('TEAM_ADDED', 'tournaments', $id, null, ['team_id' => $teamId]);

        return redirect()->to('/tournaments/view/' . $id)->with('success', 'Team added to tournament.');
    }

    // ── POST /tournaments/remove-team/:id/:teamId ─────────────
    public function removeTeam(int $id, int $teamId)
    {
        $this->requirePermission('tournaments');

        $tournament = $this->db->table('tournaments')->where('id', $id)->get()->getRowArray();
        if (!$tournament) return redirect()->back()->with('error', 'Tournament not found.');

        if (!in_array($tournament['status'], ['Draft', 'Registration'])) {
            return redirect()->back()->with('error', 'Teams cannot be removed once the tournament is past the registration phase.');
        }

        $this->db->table('teams')->where('id', $teamId)->where('tournament_id', $id)
            ->update(['tournament_id' => null]);
        $this->audit('TEAM_REMOVED', 'tournaments', $id, null, ['team_id' => $teamId]);

        return redirect()->to('/tournaments/view/' . $id)->with('success', 'Team removed from tournament.');
    }

    // ── POST /tournaments/upload-doc/:id ──────────────────────
    public function uploadDoc(int $id)
    {
        $this->requirePermission('tournaments');

        $file = $this->request->getFile('document');
        if (!$file || !$file->isValid()) return redirect()->back()->with('error', 'Invalid file.');
        if (!in_array(strtolower($file->getExtension()), ['jpg', 'jpeg', 'png', 'pdf'])) {
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
        return redirect()->to('/tournaments/view/' . $id)->with('success', 'Document uploaded.');
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
        return redirect()->to('/tournaments/view/' . $doc['tournament_id'])->with('success', 'Document verified.');
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
        return redirect()->to('/tournaments/view/' . $doc['tournament_id'])->with('success', 'Document removed.');
    }

    // ── Private ───────────────────────────────────────────────
    private function generateTournamentId(): string
    {
        $year  = date('Y');
        $count = $this->db->table('tournaments')->countAllResults() + 1;
        return 'JSCA-TR-' . $year . '-' . str_pad($count, 3, '0', STR_PAD_LEFT);
    }
}
