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
        $status   = $this->request->getGet('status'); // no default — show all by default
        // Only apply status filter if explicitly set
        $perPage  = 25;

        $allowedIds = $this->getAllowedDistrictIdsFlat();

        $builder = $this->db->table('players p')
            ->select('p.*, d.name as district_name, d.zone')
            ->join('districts d', 'd.id = p.district_id');

        // District RBAC
        if (($this->currentUser['role_name'] ?? '') !== 'superadmin') {
            if (empty($allowedIds)) {
                $builder->where('1=0');
            } else {
                $builder->whereIn('p.district_id', $allowedIds);
            }
        }

        if ($search) {
            $builder->groupStart()
                ->like('p.full_name', $search)
                ->orLike('p.jsca_player_id', $search)
                ->orLike('p.phone', $search)
                ->groupEnd();
        }
        if ($category) $builder->where('p.age_category', $category);
        if ($district && $this->canAccessDistrict((int)$district)) $builder->where('p.district_id', $district);
        if ($status === 'pending') {
            $builder->where('p.status', 'Inactive')->where('p.registration_type', 'self');
        } elseif ($status !== null && $status !== '') {
            $builder->where('p.status', $status);
        }

        $total   = $builder->countAllResults(false);
        $page    = (int)($this->request->getGet('page') ?? 1);
        $players = $builder->orderBy('p.full_name', 'ASC')
            ->limit($perPage, ($page - 1) * $perPage)
            ->get()->getResultArray();

        $districtQuery = $this->db->table('districts')->orderBy('name');
        if (($this->currentUser['role_name'] ?? '') !== 'superadmin' && !empty($allowedIds)) {
            $districtQuery->whereIn('id', $allowedIds);
        }

        return $this->render('players/index', [
            'pageTitle' => 'Players — JSCA ERP',
            'players'   => $players,
            'total'     => $total,
            'page'      => $page,
            'perPage'   => $perPage,
            'search'    => $search,
            'category'  => $category,
            'district'  => $district,
            'districts' => $districtQuery->get()->getResultArray(),
        ]);
    }

    // ── GET /players/create ──────────────────────────────────
    public function create()
    {
        $this->requirePermission('players.create');

        $allowedIds = $this->getAllowedDistrictIdsFlat();
        $districtQuery = $this->db->table('districts')->where('is_active', 1)->orderBy('name');
        if (($this->currentUser['role_name'] ?? '') !== 'superadmin' && !empty($allowedIds)) {
            $districtQuery->whereIn('id', $allowedIds);
        }

        return $this->render('players/create', [
            'pageTitle' => 'Register Player — JSCA ERP',
            'districts' => $districtQuery->get()->getResultArray(),
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
            'phone'          => 'permit_empty|regex_match[/^[6-9][0-9]{9}$/]',
            'guardian_phone' => 'permit_empty|regex_match[/^[6-9][0-9]{9}$/]',
            'pin_code'       => 'permit_empty|exact_length[6]|numeric',
            'email'          => 'permit_empty|valid_email',
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()->with('errors', $this->validator->getErrors())->withInput();
        }

        // District access check
        if (!$this->canAccessDistrict((int)$this->request->getPost('district_id'))) {
            return redirect()->back()->with('error', 'You do not have access to the selected district.')->withInput();
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
        try {
            $photoPath = $this->uploadFile('photo', 'players', ['jpg','jpeg','png'], 5);
        } catch (\RuntimeException $e) {
            return redirect()->back()->with('error', $e->getMessage())->withInput();
        }

        $addressParts = array_filter([
            $post['address_line1'] ?? '',
            $post['city']          ?? '',
            $post['state']         ?? '',
            !empty($post['pin_code']) ? 'PIN: ' . $post['pin_code'] : '',
        ]);
        $address = implode(', ', $addressParts) ?: null;

        // Generate JSCA Player ID first so we can use it for fallback email
        $jscaPlayerId = $this->generatePlayerId();

        // Generate user account for the player
        $plainPassword  = $this->generatePassword();
        $playerEmail    = !empty($post['email']) ? $post['email'] : null;
        $userEmail      = $playerEmail ?? ($jscaPlayerId . '@jsca.in');

        // Check for duplicate email before inserting user
        if (!empty($playerEmail)) {
            $exists = $this->db->table('users')->where('email', $playerEmail)->countAllResults();
            if ($exists) {
                return redirect()->back()->with('error', 'A user with this email already exists.')->withInput();
            }
        }

        $this->db->table('users')->insert([
            'role_id'       => $this->getDefaultPlayerRoleId(),
            'full_name'     => $post['full_name'],
            'email'         => $userEmail,
            'phone'         => $post['phone'] ?? null,
            'password_hash' => password_hash($plainPassword, PASSWORD_BCRYPT),
            'is_active'     => 1,
            'created_at'    => date('Y-m-d H:i:s'),
        ]);
        $userId = $this->db->insertID();

        $data = [
            'jsca_player_id'    => $jscaPlayerId,
            'full_name'         => $post['full_name'],
            'date_of_birth'     => $post['date_of_birth'],
            'gender'            => $post['gender'],
            'age_category'      => $ageCategory,
            'district_id'       => $post['district_id'],
            'role'              => $post['role'],
            'batting_style'     => $post['batting_style'] ?? null,
            'bowling_style'     => $post['bowling_style'] ?? 'N/A',
            'aadhaar_number'    => $post['aadhaar_number'] ?? null,
            'phone'             => $post['phone'] ?? null,
            'email'             => $playerEmail,
            'address'           => $address,
            'guardian_name'     => $post['guardian_name'] ?? null,
            'guardian_phone'    => $post['guardian_phone'] ?? null,
            'photo_path'        => $photoPath,
            'status'            => 'Active',
            'registration_type' => 'manual',
            'user_id'           => $userId,
            'registered_by'     => session('user_id') ?: null,
            'created_at'        => date('Y-m-d H:i:s'),
        ];

        $this->db->table('players')->insert($data);
        $playerId = $this->db->insertID();

        // Create empty career stats record
        $this->db->table('player_career_stats')->insert(['player_id' => $playerId]);

        $this->audit('CREATE', 'players', $playerId, null, $data);

        // Send credentials email if real email provided
        if ($playerEmail) {
            (new \App\Libraries\EmailHelper())->sendPlayerCredentials(
                $playerEmail, $post['full_name'], $jscaPlayerId, $plainPassword
            );
        }

        return redirect()->to('/players/view/' . $playerId)
            ->with('success', 'Player ' . $data['jsca_player_id'] . ' registered. Credentials sent to email.');
    }

    // ── GET /players/view/:id ────────────────────────────────
    public function view(int $id)
    {
        $player = $this->db->query(
            'SELECT p.*, d.name as district_name, d.zone, u.full_name as registered_by_name
             FROM players p
             JOIN districts d ON d.id = p.district_id
             LEFT JOIN users u ON u.id = p.registered_by
             WHERE p.id = ?',
            [$id]
        )->getRowArray();

        if (!$player) {
            return redirect()->to('/players')->with('error', 'Player not found.');
        }

        if (!$this->canAccessDistrict((int)$player['district_id'])) {
            return redirect()->to('/players')->with('error', 'Access denied for this district.');
        }

        $stats = $this->db->table('player_career_stats')->where('player_id', $id)->get()->getRowArray();

        $recentMatches = $this->db->query(
            'SELECT bs.*, f.match_date, f.match_number,
                    t.name as tournament_name,
                    tm.name as opponent_name
             FROM batting_stats bs
             JOIN fixtures f ON f.id = bs.fixture_id
             JOIN tournaments t ON t.id = f.tournament_id
             JOIN teams tm ON tm.id = IF(f.team_a_id = bs.team_id, f.team_b_id, f.team_a_id)
             WHERE bs.player_id = ?
             ORDER BY f.match_date DESC
             LIMIT 10',
            [$id]
        )->getResultArray();

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

        if (!$this->canAccessDistrict((int)$player['district_id'])) {
            return redirect()->to('/players')->with('error', 'Access denied for this district.');
        }

        $allowedIds = $this->getAllowedDistrictIdsFlat();
        $districtQuery = $this->db->table('districts')->where('is_active', 1)->orderBy('name');
        if (($this->currentUser['role_name'] ?? '') !== 'superadmin' && !empty($allowedIds)) {
            $districtQuery->whereIn('id', $allowedIds);
        }

        return $this->render('players/edit', [
            'pageTitle' => 'Edit Player — JSCA ERP',
            'player'    => $player,
            'districts' => $districtQuery->get()->getResultArray(),
        ]);
    }

    // ── POST /players/update/:id ─────────────────────────────
    public function update(int $id)
    {
        $this->requirePermission('players');

        $old  = $this->db->table('players')->where('id', $id)->get()->getRowArray();
        if (!$old) return redirect()->to('/players')->with('error', 'Player not found.');

        if (!$this->canAccessDistrict((int)$old['district_id'])) {
            return redirect()->to('/players')->with('error', 'Access denied for this district.');
        }

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
        try {
            $newPhoto = $this->uploadFile('photo', 'players', ['jpg','jpeg','png'], 5);
            if ($newPhoto) $data['photo_path'] = $newPhoto;
        } catch (\RuntimeException $e) {
            return redirect()->back()->with('error', $e->getMessage())->withInput();
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

        if (!$this->canAccessDistrict((int)$player['district_id'])) {
            return redirect()->to('/players')->with('error', 'Access denied for this district.');
        }

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

        $allowedIds = $this->getAllowedDistrictIdsFlat();
        $builder = $this->db->table('players p')
            ->select('p.jsca_player_id, p.full_name, p.date_of_birth, p.gender, p.age_category, p.role, p.batting_style, p.bowling_style, p.phone, p.email, p.status, p.aadhaar_verified, d.name as district')
            ->join('districts d', 'd.id = p.district_id')
            ->orderBy('p.full_name');

        if (($this->currentUser['role_name'] ?? '') !== 'superadmin' && !empty($allowedIds)) {
            $builder->whereIn('p.district_id', $allowedIds);
        }

        $players = $builder->get()->getResultArray();

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
            return redirect()->back()->with('error', 'Please select a file to upload.');
        }

        $allowed = ['jpg','jpeg','png','pdf'];
        if (!in_array(strtolower($file->getClientExtension()), $allowed)) {
            return redirect()->back()->with('error', 'Only JPG, PNG or PDF allowed.');
        }

        if ($file->getSizeByUnit('mb') > 10) {
            return redirect()->back()->with('error', 'File too large. Maximum 10MB.');
        }

        $docType  = $this->request->getPost('doc_type');
        $label    = $this->request->getPost('label');
        $dir      = FCPATH . 'assets/uploads/player_docs/' . $id;
        if (!is_dir($dir)) mkdir($dir, 0775, true);
        $ext      = strtolower($file->getClientExtension());
        $fileName = time() . '_' . bin2hex(random_bytes(8)) . '.' . $ext;
        $file->move($dir, $fileName);

        $this->db->table('player_documents')->insert([
            'player_id'   => $id,
            'doc_type'    => $docType,
            'label'       => $label,
            'file_path'   => 'assets/uploads/player_docs/' . $id . '/' . $fileName,
            'file_name'   => $file->getClientName(),
            'mime_type'   => $file->getClientMimeType(),
            'uploaded_by' => session('user_id'),
            'created_at'  => date('Y-m-d H:i:s'),
        ]);

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

        // If both aadhaar front AND back are now verified, mark player aadhaar_verified
        if (in_array($doc['doc_type'], ['aadhaar_front', 'aadhaar_back'])) {
            $bothVerified = $this->db->table('player_documents')
                ->whereIn('doc_type', ['aadhaar_front', 'aadhaar_back'])
                ->where('player_id', $doc['player_id'])
                ->where('verified', 1)
                ->countAllResults();
            if ($bothVerified >= 2) {
                $this->db->table('players')->where('id', $doc['player_id'])->update(['aadhaar_verified' => 1]);
            }
        }

        $this->audit('DOC_VERIFIED', 'players', $doc['player_id'], null, ['doc_id' => $docId]);
        return redirect()->to('players/view/' . $doc['player_id'])->with('success', 'Document verified.');
    }

    // ── POST /players/delete-doc/:docId ──────────────────────
    public function deleteDoc(int $docId)
    {
        $this->requirePermission('players');
        $doc = $this->db->table('player_documents')->where('id', $docId)->get()->getRowArray();
        if (!$doc) return redirect()->back()->with('error', 'Document not found.');

        $fullPath = FCPATH . $doc['file_path'];
        if (file_exists($fullPath)) unlink($fullPath);

        $this->db->table('player_documents')->where('id', $docId)->delete();
        $this->audit('DOC_DELETED', 'players', $doc['player_id'], $doc);
        return redirect()->to('players/view/' . $doc['player_id'])->with('success', 'Document removed.');
    }

    // ── POST /players/verify/:id ────────────────────────────
    // Admin activates a self-registered player
    public function verify(int $id)
    {
        $this->requirePermission('players');

        $player = $this->db->table('players')->where('id', $id)->get()->getRowArray();
        if (!$player) return redirect()->back()->with('error', 'Player not found.');

        $this->db->table('players')->where('id', $id)->update([
            'status'      => 'Active',
            'verified_by' => session('user_id'),
            'verified_at' => date('Y-m-d H:i:s'),
            'updated_at'  => date('Y-m-d H:i:s'),
        ]);

        // Activate the linked user account
        if (!empty($player['user_id'])) {
            $this->db->table('users')->where('id', $player['user_id'])->update(['is_active' => 1]);
        }

        $this->audit('VERIFY', 'players', $id, null, [
            'verified_by' => session('user_id'),
            'verified_at' => date('Y-m-d H:i:s'),
        ]);

        // Send activation email
        if (!empty($player['email'])) {
            (new \App\Libraries\EmailHelper())->sendAccountActivated(
                $player['email'], $player['full_name'], $player['jsca_player_id']
            );
        }

        return redirect()->to('/players/view/' . $id)
            ->with('success', 'Player verified and account activated.');
    }

    // ── Private helpers ───────────────────────────────────────
    private function generatePassword(int $length = 10): string
    {
        $chars = 'abcdefghjkmnpqrstuvwxyzABCDEFGHJKMNPQRSTUVWXYZ23456789@#!';
        $pw    = '';
        for ($i = 0; $i < $length; $i++) {
            $pw .= $chars[random_int(0, strlen($chars) - 1)];
        }
        return $pw;
    }

    private function getDefaultPlayerRoleId(): int
    {
        $role = $this->db->table('roles')->where('name', 'player')->get()->getRowArray();
        if ($role) return (int)$role['id'];
        $role = $this->db->table('roles')->where('name', 'selector')->get()->getRowArray();
        return $role ? (int)$role['id'] : 3;
    }

    // ── Recalculate career stats ─────────────────────────────
    public static function recalculateStats(int $playerId, \CodeIgniter\Database\ConnectionInterface $db): void
    {
        $batting = $db->table('batting_stats')->where('player_id', $playerId)->get()->getResultArray();
        $bowling = $db->table('bowling_stats')->where('player_id', $playerId)->get()->getResultArray();

        $runs          = array_sum(array_column($batting, 'runs'));
        $innings        = count(array_filter($batting, fn($r) => $r['dismissal'] !== 'not out'));
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
