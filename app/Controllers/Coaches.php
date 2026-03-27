<?php
// app/Controllers/Coaches.php
namespace App\Controllers;

class Coaches extends BaseController
{
    // ── GET /coaches ──────────────────────────────────────────
    public function index()
    {
        $search = $this->request->getGet('q');
        $level  = $this->request->getGet('level');
        $status = $this->request->getGet('status') ?? 'Active';

        $allowedIds = $this->getAllowedDistrictIdsFlat();

        $builder = $this->db->table('coaches c')
            ->select('c.*, d.name as district_name')
            ->join('districts d', 'd.id = c.district_id', 'left');

        // District RBAC
        if (($this->currentUser['role_name'] ?? '') !== 'superadmin') {
            if (empty($allowedIds)) {
                $builder->where('1=0');
            } else {
                $builder->whereIn('c.district_id', $allowedIds);
            }
        }

        if ($search) {
            $builder->groupStart()
                ->like('c.full_name', $search)
                ->orLike('c.jsca_coach_id', $search)
                ->orLike('c.phone', $search)
                ->orLike('c.bcci_coach_id', $search)
            ->groupEnd();
        }
        if ($level)  $builder->where('c.level', $level);
        if ($status) $builder->where('c.status', $status);

        $coaches = $builder->orderBy('c.full_name')->get()->getResultArray();

        return $this->render('coaches/index', [
            'pageTitle' => 'Coach Registry — JSCA ERP',
            'coaches'   => $coaches,
            'search'    => $search,
            'level'     => $level,
            'status'    => $status,
        ]);
    }

    // ── GET /coaches/create ───────────────────────────────────
    public function create()
    {
        $this->requirePermission('coaches');

        $allowedIds = $this->getAllowedDistrictIdsFlat();
        $districtQuery = $this->db->table('districts')->where('is_active', 1)->orderBy('name');
        if (($this->currentUser['role_name'] ?? '') !== 'superadmin' && !empty($allowedIds)) {
            $districtQuery->whereIn('id', $allowedIds);
        }

        return $this->render('coaches/create', [
            'pageTitle' => 'Register Coach — JSCA ERP',
            'districts' => $districtQuery->get()->getResultArray(),
        ]);
    }

    // ── POST /coaches/store ───────────────────────────────────
    public function store()
    {
        $this->requirePermission('coaches');

        $rules = [
            'full_name'     => 'required|min_length[3]|max_length[100]',
            'date_of_birth' => 'required|valid_date[Y-m-d]',
            'gender'        => 'required|in_list[Male,Female,Other]',
            'phone'         => 'permit_empty|min_length[10]|max_length[15]',
            'email'         => 'permit_empty|valid_email',
            'aadhaar_number'=> 'permit_empty|exact_length[12]|numeric',
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()->with('errors', $this->validator->getErrors())->withInput();
        }

        // District access check
        $districtId = $this->request->getPost('district_id') ?: null;
        if ($districtId && !$this->canAccessDistrict((int)$districtId)) {
            return redirect()->back()->with('error', 'You do not have access to the selected district.')->withInput();
        }

        $post = $this->request->getPost();

        $photoPath = null;
        try {
            $photoPath = $this->uploadFile('photo', 'coaches', ['jpg','jpeg','png'], 5);
        } catch (\RuntimeException $e) {
            return redirect()->back()->with('error', $e->getMessage())->withInput();
        }

        $data = [
            'jsca_coach_id'    => $this->generateCoachId(),
            'full_name'        => $post['full_name'],
            'date_of_birth'    => $post['date_of_birth'],
            'gender'           => $post['gender'],
            'phone'            => $post['phone']            ?? null,
            'email'            => $post['email']            ?? null,
            'address'          => $post['address']          ?? null,
            'district_id'      => $districtId,
            'specialization'   => $post['specialization']   ?? 'General',
            'level'            => $post['level']            ?? 'Assistant',
            'bcci_coach_id'    => $post['bcci_coach_id']    ?? null,
            'aadhaar_number'   => $post['aadhaar_number']   ?? null,
            'experience_years' => (int)($post['experience_years'] ?? 0),
            'previous_teams'   => $post['previous_teams']   ?? null,
            'achievements'     => $post['achievements']     ?? null,
            'photo_path'       => $photoPath,
            'registered_by'    => session('user_id'),
            'created_at'       => date('Y-m-d H:i:s'),
        ];

        $this->db->table('coaches')->insert($data);
        $coachId = $this->db->insertID();
        $this->audit('CREATE', 'coaches', $coachId, null, $data);

        return redirect()->to('coaches/view/' . $coachId)
            ->with('success', 'Coach ' . $data['jsca_coach_id'] . ' registered successfully.');
    }

    // ── GET /coaches/view/:id ─────────────────────────────────
    public function view(int $id)
    {
        $coach = $this->db->table('coaches c')
            ->select('c.*, d.name as district_name')
            ->join('districts d', 'd.id = c.district_id', 'left')
            ->where('c.id', $id)
            ->get()->getRowArray();

        if (!$coach) return redirect()->to('coaches')->with('error', 'Coach not found.');

        if ($coach['district_id'] && !$this->canAccessDistrict((int)$coach['district_id'])) {
            return redirect()->to('coaches')->with('error', 'Access denied for this district.');
        }

        $documents = $this->db->table('coach_documents')
            ->where('coach_id', $id)
            ->orderBy('created_at', 'DESC')
            ->get()->getResultArray();

        $teams = $this->db->table('team_coaches tc')
            ->select('tc.*, t.name as team_name, t.id as team_id, tr.name as tournament_name')
            ->join('teams t', 't.id = tc.team_id')
            ->join('tournaments tr', 'tr.id = t.tournament_id', 'left')
            ->where('tc.coach_id', $id)
            ->orderBy('tc.is_current', 'DESC')
            ->orderBy('tc.from_date', 'DESC')
            ->get()->getResultArray();

        return $this->render('coaches/view', [
            'pageTitle' => $coach['full_name'] . ' — Coach Profile',
            'coach'     => $coach,
            'documents' => $documents,
            'teams'     => $teams,
        ]);
    }

    // ── GET /coaches/edit/:id ─────────────────────────────────
    public function edit(int $id)
    {
        $this->requirePermission('coaches');
        $coach = $this->db->table('coaches')->where('id', $id)->get()->getRowArray();
        if (!$coach) return redirect()->to('coaches')->with('error', 'Coach not found.');

        if ($coach['district_id'] && !$this->canAccessDistrict((int)$coach['district_id'])) {
            return redirect()->to('coaches')->with('error', 'Access denied for this district.');
        }

        $allowedIds = $this->getAllowedDistrictIdsFlat();
        $districtQuery = $this->db->table('districts')->where('is_active', 1)->orderBy('name');
        if (($this->currentUser['role_name'] ?? '') !== 'superadmin' && !empty($allowedIds)) {
            $districtQuery->whereIn('id', $allowedIds);
        }

        return $this->render('coaches/edit', [
            'pageTitle' => 'Edit Coach — JSCA ERP',
            'coach'     => $coach,
            'districts' => $districtQuery->get()->getResultArray(),
        ]);
    }

    // ── POST /coaches/update/:id ──────────────────────────────
    public function update(int $id)
    {
        $this->requirePermission('coaches');
        $old = $this->db->table('coaches')->where('id', $id)->get()->getRowArray();
        if (!$old) return redirect()->to('coaches')->with('error', 'Coach not found.');

        if ($old['district_id'] && !$this->canAccessDistrict((int)$old['district_id'])) {
            return redirect()->to('coaches')->with('error', 'Access denied for this district.');
        }

        $rules = [
            'full_name' => 'required|min_length[3]|max_length[100]',
            'phone'     => 'permit_empty|min_length[10]|max_length[15]',
            'email'     => 'permit_empty|valid_email',
        ];
        if (!$this->validate($rules)) {
            return redirect()->back()->with('errors', $this->validator->getErrors())->withInput();
        }

        $post = $this->request->getPost();
        $data = [
            'full_name'        => $post['full_name'],
            'phone'            => $post['phone']            ?? null,
            'email'            => $post['email']            ?? null,
            'address'          => $post['address']          ?? null,
            'district_id'      => $post['district_id']      ?: null,
            'specialization'   => $post['specialization']   ?? 'General',
            'level'            => $post['level']            ?? 'Assistant',
            'bcci_coach_id'    => $post['bcci_coach_id']    ?? null,
            'experience_years' => (int)($post['experience_years'] ?? 0),
            'previous_teams'   => $post['previous_teams']   ?? null,
            'achievements'     => $post['achievements']     ?? null,
            'status'           => $post['status']           ?? 'Active',
            'updated_at'       => date('Y-m-d H:i:s'),
        ];

        try {
            $newPhoto = $this->uploadFile('photo', 'coaches', ['jpg','jpeg','png'], 5);
            if ($newPhoto) $data['photo_path'] = $newPhoto;
        } catch (\RuntimeException $e) {
            return redirect()->back()->with('error', $e->getMessage())->withInput();
        }

        $this->db->table('coaches')->where('id', $id)->update($data);
        $this->audit('UPDATE', 'coaches', $id, $old, $data);

        return redirect()->to('coaches/view/' . $id)->with('success', 'Coach profile updated.');
    }

    // ── POST /coaches/delete/:id ──────────────────────────────
    public function delete(int $id)
    {
        $this->requirePermission('coaches');
        $coach = $this->db->table('coaches')->where('id', $id)->get()->getRowArray();
        if (!$coach) return redirect()->to('coaches')->with('error', 'Coach not found.');

        if ($coach['district_id'] && !$this->canAccessDistrict((int)$coach['district_id'])) {
            return redirect()->to('coaches')->with('error', 'Access denied for this district.');
        }

        $this->db->table('coaches')->where('id', $id)->update([
            'status'     => 'Inactive',
            'updated_at' => date('Y-m-d H:i:s'),
        ]);
        $this->audit('DELETE', 'coaches', $id, $coach);
        return redirect()->to('coaches')->with('success', 'Coach deactivated.');
    }

    // ── POST /coaches/upload-doc/:id ──────────────────────────
    public function uploadDoc(int $id)
    {
        $this->requirePermission('coaches');
        $coach = $this->db->table('coaches')->where('id', $id)->get()->getRowArray();
        if (!$coach) return redirect()->back()->with('error', 'Coach not found.');

        $file = $this->request->getFile('document');
        if (!$file || !$file->isValid()) {
            return redirect()->back()->with('error', 'Please select a file to upload.');
        }
        if (!in_array(strtolower($file->getClientExtension()), ['jpg','jpeg','png','pdf'])) {
            return redirect()->back()->with('error', 'Only JPG, PNG or PDF allowed.');
        }
        if ($file->getSizeByUnit('mb') > 10) {
            return redirect()->back()->with('error', 'File too large. Maximum 10MB.');
        }

        $docType  = $this->request->getPost('doc_type');
        $dir      = FCPATH . 'assets/uploads/coach_docs/' . $id;
        if (!is_dir($dir)) mkdir($dir, 0775, true);
        $ext      = strtolower($file->getClientExtension());
        $fileName = time() . '_' . bin2hex(random_bytes(8)) . '.' . $ext;
        $file->move($dir, $fileName);

        $this->db->table('coach_documents')->insert([
            'coach_id'    => $id,
            'doc_type'    => $docType,
            'label'       => $this->request->getPost('label'),
            'file_path'   => 'assets/uploads/coach_docs/' . $id . '/' . $fileName,
            'file_name'   => $file->getClientName(),
            'mime_type'   => $file->getClientMimeType(),
            'uploaded_by' => session('user_id'),
            'created_at'  => date('Y-m-d H:i:s'),
        ]);

        $this->audit('DOC_UPLOADED', 'coaches', $id, null, ['doc_type' => $docType]);
        return redirect()->to('coaches/view/' . $id)->with('success', 'Document uploaded.');
    }

    // ── POST /coaches/verify-doc/:docId ───────────────────────
    public function verifyDoc(int $docId)
    {
        $this->requirePermission('coaches');
        $doc = $this->db->table('coach_documents')->where('id', $docId)->get()->getRowArray();
        if (!$doc) return redirect()->back()->with('error', 'Document not found.');

        $this->db->table('coach_documents')->where('id', $docId)->update([
            'verified'    => 1,
            'verified_by' => session('user_id'),
            'verified_at' => date('Y-m-d H:i:s'),
        ]);

        if (in_array($doc['doc_type'], ['aadhaar_front', 'aadhaar_back'])) {
            $bothVerified = $this->db->table('coach_documents')
                ->whereIn('doc_type', ['aadhaar_front', 'aadhaar_back'])
                ->where('coach_id', $doc['coach_id'])
                ->where('verified', 1)
                ->countAllResults();
            if ($bothVerified >= 2) {
                $this->db->table('coaches')->where('id', $doc['coach_id'])->update(['aadhaar_verified' => 1]);
            }
        }
        $this->audit('DOC_VERIFIED', 'coaches', $doc['coach_id'], null, ['doc_id' => $docId]);
        return redirect()->to('coaches/view/' . $doc['coach_id'])->with('success', 'Document verified.');
    }

    // ── POST /coaches/delete-doc/:docId ───────────────────────
    public function deleteDoc(int $docId)
    {
        $this->requirePermission('coaches');
        $doc = $this->db->table('coach_documents')->where('id', $docId)->get()->getRowArray();
        if (!$doc) return redirect()->back()->with('error', 'Document not found.');

        $fullPath = FCPATH . $doc['file_path'];
        if (file_exists($fullPath)) unlink($fullPath);

        $this->db->table('coach_documents')->where('id', $docId)->delete();
        $this->audit('DOC_DELETED', 'coaches', $doc['coach_id'], $doc);
        return redirect()->to('coaches/view/' . $doc['coach_id'])->with('success', 'Document removed.');
    }

    // ── Private ───────────────────────────────────────────────
    private function generateCoachId(): string
    {
        $year  = date('Y');
        $count = $this->db->table('coaches')->countAllResults() + 1;
        return 'JSCA-C-' . $year . '-' . str_pad($count, 4, '0', STR_PAD_LEFT);
    }
}
