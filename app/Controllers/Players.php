<?php
// app/Controllers/Players.php
namespace App\Controllers;

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class Players extends BaseController
{
    // ── GET /players ─────────────────────────────────────────
    public function index()
    {
        $search   = $this->request->getGet('q');
        $category = $this->request->getGet('category');
        $district = $this->request->getGet('district');
        $status   = $this->request->getGet('status') ?? 'Active';
        $perPage  = 25;

        $builder = $this->db->table('players p')
            ->select('p.*, d.name as district_name, d.zone')
            ->join('districts d', 'd.id = p.district_id');

        if ($search) {
            $builder->groupStart()
                ->like('p.full_name', $search)
                ->orLike('p.jsca_player_id', $search)
                ->orLike('p.phone', $search)
                ->groupEnd();
        }
        if ($category) $builder->where('p.age_category', $category);
        if ($district)  $builder->where('p.district_id', $district);
        if ($status)    $builder->where('p.status', $status);

        $total   = $builder->countAllResults(false);
        $page    = (int)($this->request->getGet('page') ?? 1);
        $players = $builder->orderBy('p.full_name', 'ASC')
            ->limit($perPage, ($page - 1) * $perPage)
            ->get()->getResultArray();

        return $this->render('players/index', [
            'pageTitle' => 'Players — JSCA ERP',
            'players'   => $players,
            'total'     => $total,
            'page'      => $page,
            'perPage'   => $perPage,
            'search'    => $search,
            'category'  => $category,
            'district'  => $district,
            'districts' => $this->db->table('districts')->orderBy('name')->get()->getResultArray(),
        ]);
    }

    // ── GET /players/create ──────────────────────────────────
    public function create()
    {
        $this->requirePermission('players.create');

        return $this->render('players/create', [
            'pageTitle' => 'Register Player — JSCA ERP',
            'districts' => $this->db->table('districts')->where('is_active', 1)->orderBy('name')->get()->getResultArray(),
        ]);
    }

    // ── POST /players/store ──────────────────────────────────
    public function store()
    {
        $this->requirePermission('players.create');

        $rules = [
            'full_name'      => 'required|min_length[3]|max_length[100]',
            'date_of_birth'  => 'required|valid_date[Y-m-d]',
            'gender'         => 'required|in_list[Male,Female,Other]',
            'district_id'    => 'required|is_natural_no_zero',
            'role'           => 'required|in_list[Batsman,Bowler,All-rounder,Wicket-keeper]',
            'aadhaar_number' => 'permit_empty|exact_length[12]|numeric',
            'phone'          => 'permit_empty|min_length[10]|max_length[15]',
            'email'          => 'permit_empty|valid_email',
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()->with('errors', $this->validator->getErrors())->withInput();
        }

        $post = $this->request->getPost();

        // Calculate age category from DOB
        $age = (int)date_diff(date_create($post['date_of_birth']), date_create('now'))->y;
        $ageCategory = match (true) {
            $age < 14 => 'U14',
            $age < 16 => 'U16',
            $age < 19 => 'U19',
            $age < 40 => 'Senior',
            default   => 'Masters',
        };

        // Handle photo upload
        $photoPath = null;
        $photo     = $this->request->getFile('photo');
        if ($photo && $photo->isValid() && !$photo->hasMoved()) {
            $photoName = $photo->getRandomName();
            $photo->move(WRITEPATH . 'uploads/players', $photoName);
            $photoPath = 'uploads/players/' . $photoName;
        }

        $data = [
            'jsca_player_id'  => $this->generatePlayerId(),
            'full_name'       => $post['full_name'],
            'date_of_birth'   => $post['date_of_birth'],
            'gender'          => $post['gender'],
            'age_category'    => $ageCategory,
            'district_id'     => $post['district_id'],
            'role'            => $post['role'],
            'batting_style'   => $post['batting_style'] ?? null,
            'bowling_style'   => $post['bowling_style'] ?? 'N/A',
            'aadhaar_number'  => $post['aadhaar_number'] ?? null,
            'phone'           => $post['phone'] ?? null,
            'email'           => $post['email'] ?? null,
            'address'         => $post['address'] ?? null,
            'guardian_name'   => $post['guardian_name'] ?? null,
            'guardian_phone'  => $post['guardian_phone'] ?? null,
            'photo_path'      => $photoPath,
            'registered_by'   => session('user_id'),
            'created_at'      => date('Y-m-d H:i:s'),
        ];

        $this->db->table('players')->insert($data);
        $playerId = $this->db->insertID();

        // Create empty career stats record
        $this->db->table('player_career_stats')->insert(['player_id' => $playerId]);

        $this->audit('CREATE', 'players', $playerId, null, $data);

        return redirect()->to('/players/view/' . $playerId)
            ->with('success', 'Player ' . $data['jsca_player_id'] . ' registered successfully!');
    }

    // ── GET /players/view/:id ────────────────────────────────
    public function view(int $id)
    {
        $player = $this->db->table('players p')
            ->select('p.*, d.name as district_name, d.zone, u.full_name as registered_by_name')
            ->join('districts d', 'd.id = p.district_id')
            ->join('users u', 'u.id = p.registered_by', 'left')
            ->where('p.id', $id)
            ->get()->getRowArray();

        if (!$player) {
            return redirect()->to('/players')->with('error', 'Player not found.');
        }

        $stats = $this->db->table('player_career_stats')->where('player_id', $id)->get()->getRowArray();

        $recentMatches = $this->db->table('batting_stats bs')
            ->select('bs.*, f.match_date, f.match_number, t.name as tournament_name, tm.name as opponent_name')
            ->join('fixtures f', 'f.id = bs.fixture_id')
            ->join('tournaments t', 't.id = f.tournament_id')
            ->join('teams tm', 'IF(f.team_a_id = bs.team_id, f.team_b_id, f.team_a_id) = tm.id')
            ->where('bs.player_id', $id)
            ->orderBy('f.match_date', 'DESC')
            ->limit(10)
            ->get()->getResultArray();

        return $this->render('players/view', [
            'pageTitle'     => $player['full_name'] . ' — Player Profile',
            'player'        => $player,
            'stats'         => $stats,
            'recentMatches' => $recentMatches,
            'documents'     => $this->db->table('player_documents')->where('player_id', $id)->orderBy('created_at','DESC')->get()->getResultArray(),
        ]);
    }

    // ── GET /players/edit/:id ────────────────────────────────
    public function edit(int $id)
    {
        $this->requirePermission('players');

        $player = $this->db->table('players')->where('id', $id)->get()->getRowArray();
        if (!$player) return redirect()->to('/players')->with('error', 'Player not found.');

        return $this->render('players/edit', [
            'pageTitle' => 'Edit Player — JSCA ERP',
            'player'    => $player,
            'districts' => $this->db->table('districts')->where('is_active', 1)->orderBy('name')->get()->getResultArray(),
        ]);
    }

    // ── POST /players/update/:id ─────────────────────────────
    public function update(int $id)
    {
        $this->requirePermission('players');

        $old  = $this->db->table('players')->where('id', $id)->get()->getRowArray();
        if (!$old) return redirect()->to('/players')->with('error', 'Player not found.');

        $rules = [
            'full_name'  => 'required|min_length[3]|max_length[100]',
            'district_id'=> 'required|is_natural_no_zero',
            'role'       => 'required',
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()->with('errors', $this->validator->getErrors())->withInput();
        }

        $post = $this->request->getPost();

        $data = [
            'full_name'     => $post['full_name'],
            'district_id'   => $post['district_id'],
            'role'          => $post['role'],
            'batting_style' => $post['batting_style'] ?? null,
            'bowling_style' => $post['bowling_style'] ?? 'N/A',
            'phone'         => $post['phone'] ?? null,
            'email'         => $post['email'] ?? null,
            'address'       => $post['address'] ?? null,
            'status'        => $post['status'],
            'selection_pool'=> $post['selection_pool'] ?? 'None',
            'updated_at'    => date('Y-m-d H:i:s'),
        ];

        // Photo update
        $photo = $this->request->getFile('photo');
        if ($photo && $photo->isValid() && !$photo->hasMoved()) {
            $photoName  = $photo->getRandomName();
            $photo->move(WRITEPATH . 'uploads/players', $photoName);
            $data['photo_path'] = 'uploads/players/' . $photoName;
        }

        $this->db->table('players')->where('id', $id)->update($data);
        $this->audit('UPDATE', 'players', $id, $old, $data);

        return redirect()->to('/players/view/' . $id)
            ->with('success', 'Player profile updated successfully.');
    }

    // ── POST /players/delete/:id ─────────────────────────────
    public function delete(int $id)
    {
        $this->requirePermission('players');

        $player = $this->db->table('players')->where('id', $id)->get()->getRowArray();
        if (!$player) return redirect()->to('/players')->with('error', 'Player not found.');

        // Soft delete — just mark as Inactive
        $this->db->table('players')->where('id', $id)->update([
            'status'     => 'Inactive',
            'updated_at' => date('Y-m-d H:i:s'),
        ]);

        $this->audit('DELETE', 'players', $id, $player);

        return redirect()->to('/players')
            ->with('success', 'Player has been deactivated.');
    }

    // ── GET /players/verify-aadhaar/:id ─────────────────────
    public function verifyAadhaar(int $id)
    {
        $player = $this->db->table('players')->where('id', $id)->get()->getRowArray();
        if (!$player) return $this->response->setJSON(['success' => false, 'message' => 'Not found']);

        // In production — call actual UIDAI API here
        // For demo: mark as verified
        $this->db->table('players')->where('id', $id)->update([
            'aadhaar_verified' => 1,
            'updated_at'       => date('Y-m-d H:i:s'),
        ]);

        $this->audit('AADHAAR_VERIFY', 'players', $id);

        return $this->response->setJSON([
            'success' => true,
            'message' => 'Aadhaar verified successfully.',
        ]);
    }

    // ── GET /players/export ──────────────────────────────────
    public function export()
    {
        $this->requirePermission('reports');

        $players = $this->db->table('players p')
            ->select('p.jsca_player_id, p.full_name, p.date_of_birth, p.gender, p.age_category, p.role, p.batting_style, p.bowling_style, p.phone, p.email, p.status, p.aadhaar_verified, d.name as district')
            ->join('districts d', 'd.id = p.district_id')
            ->orderBy('p.full_name')
            ->get()->getResultArray();

        // Build CSV
        $filename = 'JSCA_Players_' . date('Ymd_His') . '.csv';
        header('Content-Type: text/csv');
        header('Content-Disposition: attachment; filename="' . $filename . '"');

        $output = fopen('php://output', 'w');
        fputcsv($output, ['JSCA ID','Full Name','DOB','Gender','Age Category','Role','Batting','Bowling','Phone','Email','Status','Aadhaar Verified','District']);
        foreach ($players as $p) {
            fputcsv($output, array_values($p));
        }
        fclose($output);
        exit;
    }

    // ── POST /players/upload-doc/:id ────────────────────────
    public function uploadDoc(int $id)
    {
        $this->requirePermission('players');
        $player = $this->db->table('players')->where('id', $id)->get()->getRowArray();
        if (!$player) return redirect()->back()->with('error', 'Player not found.');

        $file = $this->request->getFile('document');
        if (!$file || !$file->isValid()) {
            return redirect()->back()->with('error', 'Invalid file.');
        }

        $allowed = ['jpg','jpeg','png','pdf'];
        if (!in_array(strtolower($file->getExtension()), $allowed)) {
            return redirect()->back()->with('error', 'Only JPG, PNG or PDF allowed.');
        }

        $docType  = $this->request->getPost('doc_type');
        $label    = $this->request->getPost('label');
        $fileName = $file->getRandomName();
        $file->move(WRITEPATH . 'uploads/player_docs/' . $id, $fileName);

        $this->db->table('player_documents')->insert([
            'player_id'   => $id,
            'doc_type'    => $docType,
            'label'       => $label,
            'file_path'   => 'uploads/player_docs/' . $id . '/' . $fileName,
            'file_name'   => $file->getClientName(),
            'mime_type'   => $file->getMimeType(),
            'uploaded_by' => session('user_id'),
            'created_at'  => date('Y-m-d H:i:s'),
        ]);

        // Auto-mark aadhaar verified when both sides uploaded
        if (in_array($docType, ['aadhaar_front', 'aadhaar_back'])) {
            $sides = $this->db->table('player_documents')
                ->whereIn('doc_type', ['aadhaar_front','aadhaar_back'])
                ->where('player_id', $id)->countAllResults();
            if ($sides >= 2) {
                $this->db->table('players')->where('id', $id)->update(['aadhaar_verified' => 1]);
            }
        }

        $this->audit('DOC_UPLOADED', 'players', $id, null, ['doc_type' => $docType]);
        return redirect()->to('players/view/' . $id)->with('success', 'Document uploaded.');
    }

    // ── POST /players/verify-doc/:docId ──────────────────────
    public function verifyDoc(int $docId)
    {
        $this->requirePermission('players');
        $doc = $this->db->table('player_documents')->where('id', $docId)->get()->getRowArray();
        if (!$doc) return redirect()->back()->with('error', 'Document not found.');

        $this->db->table('player_documents')->where('id', $docId)->update([
            'verified'    => 1,
            'verified_by' => session('user_id'),
            'verified_at' => date('Y-m-d H:i:s'),
        ]);

        $this->audit('DOC_VERIFIED', 'players', $doc['player_id'], null, ['doc_id' => $docId]);
        return redirect()->to('players/view/' . $doc['player_id'])->with('success', 'Document verified.');
    }

    // ── POST /players/delete-doc/:docId ──────────────────────
    public function deleteDoc(int $docId)
    {
        $this->requirePermission('players');
        $doc = $this->db->table('player_documents')->where('id', $docId)->get()->getRowArray();
        if (!$doc) return redirect()->back()->with('error', 'Document not found.');

        $fullPath = WRITEPATH . $doc['file_path'];
        if (file_exists($fullPath)) unlink($fullPath);

        $this->db->table('player_documents')->where('id', $docId)->delete();
        $this->audit('DOC_DELETED', 'players', $doc['player_id'], $doc);
        return redirect()->to('players/view/' . $doc['player_id'])->with('success', 'Document removed.');
    }

    // ── Recalculate career stats ─────────────────────────────
    public static function recalculateStats(int $playerId, \CodeIgniter\Database\ConnectionInterface $db): void
    {
        $batting = $db->table('batting_stats')->where('player_id', $playerId)->get()->getResultArray();
        $bowling = $db->table('bowling_stats')->where('player_id', $playerId)->get()->getResultArray();

        $runs          = array_sum(array_column($batting, 'runs'));
        $innings        = count(array_filter($batting, fn($r) => $r['dismissal'] !== 'not out'));
        $notOuts        = count(array_filter($batting, fn($r) => $r['dismissal'] === 'not out'));
        $battingAvg     = ($innings > 0) ? round($runs / $innings, 2) : 0;
        $totalBalls     = array_sum(array_column($batting, 'balls_faced'));
        $strikeRate     = ($totalBalls > 0) ? round($runs / $totalBalls * 100, 2) : 0;
        $highScore      = max(array_column($batting, 'runs') ?: [0]);
        $fifties        = count(array_filter($batting, fn($r) => $r['runs'] >= 50 && $r['runs'] < 100));
        $hundreds       = count(array_filter($batting, fn($r) => $r['runs'] >= 100));

        $wickets        = array_sum(array_column($bowling, 'wickets'));
        $runsConceded   = array_sum(array_column($bowling, 'runs_conceded'));
        $bowlingAvg     = ($wickets > 0) ? round($runsConceded / $wickets, 2) : 0;
        $totalOvers     = array_sum(array_column($bowling, 'overs'));
        $economy        = ($totalOvers > 0) ? round($runsConceded / $totalOvers, 2) : 0;

        $db->table('player_career_stats')->where('player_id', $playerId)->update([
            'matches'      => count(array_unique(array_column($batting, 'fixture_id'))),
            'runs'         => $runs,
            'highest_score'=> $highScore,
            'batting_avg'  => $battingAvg,
            'strike_rate'  => $strikeRate,
            'fifties'      => $fifties,
            'hundreds'     => $hundreds,
            'wickets'      => $wickets,
            'bowling_avg'  => $bowlingAvg,
            'economy'      => $economy,
            'last_updated' => date('Y-m-d H:i:s'),
        ]);
    }
}
