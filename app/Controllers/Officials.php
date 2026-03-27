<?php
// app/Controllers/Officials.php
namespace App\Controllers;

class Officials extends BaseController
{
    // ── GET /officials ────────────────────────────────────────
    public function index()
    {
        $search   = $this->request->getGet('q');
        $typeId   = $this->request->getGet('type');
        $district = $this->request->getGet('district');
        $status   = $this->request->getGet('status');

        $allowedIds = $this->getAllowedDistrictIdsFlat();

        $builder = $this->db->table('officials o')
            ->select('o.*, d.name as district_name, d.zone, ot.name as type_name, ot.prefix')
            ->join('districts d',      'd.id = o.district_id')
            ->join('official_types ot', 'ot.id = o.official_type_id');

        if (($this->currentUser['role_name'] ?? '') !== 'superadmin') {
            if (empty($allowedIds)) {
                $builder->where('1=0');
            } else {
                $builder->whereIn('o.district_id', $allowedIds);
            }
        }

        if ($search) {
            $builder->groupStart()
                ->like('o.full_name', $search)
                ->orLike('o.jsca_official_id', $search)
                ->orLike('o.phone', $search)
            ->groupEnd();
        }
        if ($typeId)   $builder->where('o.official_type_id', $typeId);
        if ($district && $this->canAccessDistrict((int)$district)) $builder->where('o.district_id', $district);
        if ($status !== null && $status !== '') $builder->where('o.status', $status);

        $officials = $builder->orderBy('o.full_name')->get()->getResultArray();

        $districtQuery = $this->db->table('districts')->where('is_active', 1)->orderBy('name');
        if (($this->currentUser['role_name'] ?? '') !== 'superadmin' && !empty($allowedIds)) {
            $districtQuery->whereIn('id', $allowedIds);
        }

        return $this->render('officials/index', [
            'pageTitle'     => 'Officials — JSCA ERP',
            'officials'     => $officials,
            'officialTypes' => $this->db->table('official_types')->where('is_active', 1)->get()->getResultArray(),
            'districts'     => $districtQuery->get()->getResultArray(),
            'search'        => $search,
            'typeId'        => $typeId,
            'district'      => $district,
            'status'        => $status,
        ]);
    }

    // ── GET /officials/create ─────────────────────────────────
    public function create()
    {
        $this->requirePermission('officials');

        $allowedIds    = $this->getAllowedDistrictIdsFlat();
        $districtQuery = $this->db->table('districts')->where('is_active', 1)->orderBy('name');
        if (($this->currentUser['role_name'] ?? '') !== 'superadmin' && !empty($allowedIds)) {
            $districtQuery->whereIn('id', $allowedIds);
        }

        return $this->render('officials/form', [
            'pageTitle'     => 'Add Official — JSCA ERP',
            'official'      => null,
            'certs'         => [],
            'officialTypes' => $this->db->table('official_types')->where('is_active', 1)->get()->getResultArray(),
            'districts'     => $districtQuery->get()->getResultArray(),
        ]);
    }

    // ── POST /officials/store ─────────────────────────────────
    public function store()
    {
        $this->requirePermission('officials');

        $rules = [
            'full_name'        => 'required|min_length[3]|max_length[100]',
            'official_type_id' => 'required|is_natural_no_zero',
            'district_id'      => 'required|is_natural_no_zero',
            'gender'           => 'required|in_list[Male,Female,Other]',
            'email'            => 'permit_empty|valid_email',
            'phone'            => 'permit_empty|regex_match[/^[6-9][0-9]{9}$/]',
            'dob'              => 'permit_empty|valid_date[Y-m-d]',
            'experience_years' => 'permit_empty|is_natural',
            'fee_per_match'    => 'permit_empty|decimal',
            'bank_ifsc'        => 'permit_empty|regex_match[/^[A-Z]{4}0[A-Z0-9]{6}$/]',
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()->with('errors', $this->validator->getErrors())->withInput();
        }

        if (!$this->canAccessDistrict((int)$this->request->getPost('district_id'))) {
            return redirect()->back()->with('error', 'You do not have access to the selected district.')->withInput();
        }

        $post = $this->request->getPost();

        // Get official type for prefix + role
        $type = $this->db->table('official_types')->where('id', $post['official_type_id'])->get()->getRowArray();
        if (!$type) return redirect()->back()->with('error', 'Invalid official type.')->withInput();

        $jscaId = $this->generateOfficialIdByType($type['prefix']);

        // Photo upload
        $photoPath = null;
        try {
            $photoPath = $this->uploadFile('profile_photo', 'officials', ['jpg','jpeg','png'], 5);
        } catch (\RuntimeException $e) {
            return redirect()->back()->with('error', $e->getMessage())->withInput();
        }

        // Check for duplicate email before inserting user
        if (!empty($post['email'])) {
            $exists = $this->db->table('users')->where('email', $post['email'])->countAllResults();
            if ($exists) {
                return redirect()->back()->with('error', 'A user with this email already exists.')->withInput();
            }
        }

        // Create user account
        $plainPassword = $this->generatePassword();
        $userEmail     = !empty($post['email']) ? $post['email'] : ($jscaId . '@jsca.in');

        $this->db->table('users')->insert([
            'role_id'       => $type['role_id'],
            'full_name'     => $post['full_name'],
            'email'         => $userEmail,
            'phone'         => $post['phone'] ?? null,
            'password_hash' => password_hash($plainPassword, PASSWORD_BCRYPT),
            'is_active'     => 1,
            'created_at'    => date('Y-m-d H:i:s'),
        ]);
        $userId = $this->db->insertID();

        $data = [
            'jsca_official_id' => $jscaId,
            'official_type_id' => $post['official_type_id'],
            'full_name'        => $post['full_name'],
            'email'            => $post['email'] ?: null,
            'phone'            => $post['phone'] ?: null,
            'gender'           => $post['gender'],
            'dob'              => $post['dob'] ?: null,
            'district_id'      => $post['district_id'],
            'address'          => $post['address'] ?: null,
            'experience_years' => $post['experience_years'] ?: null,
            'grade'            => $post['grade']         ?: null,
            'fee_per_match'    => $post['fee_per_match']  ?: null,
            'bank_name'        => $post['bank_name']      ?: null,
            'bank_account'     => $post['bank_account']   ?: null,
            'bank_ifsc'        => $post['bank_ifsc']      ?: null,
            'profile_photo'    => $photoPath,
            'user_id'          => $userId,
            'status'           => 'Active',
            'registered_by'    => session('user_id'),
            'created_at'       => date('Y-m-d H:i:s'),
        ];

        $this->db->table('officials')->insert($data);
        $officialId = $this->db->insertID();

        // Save certifications
        $this->saveCertifications($officialId, $post);

        $this->audit('CREATE', 'officials', $officialId, null, $data);

        if (!empty($post['email'])) {
            (new \App\Libraries\EmailHelper())->sendOfficialCredentials(
                $post['email'], $post['full_name'], $jscaId, $plainPassword, $type['name']
            );
        }

        return redirect()->to('/officials/view/' . $officialId)
            ->with('success', 'Official ' . $jscaId . ' registered. Credentials sent to email.');
    }

    // ── GET /officials/view/:id ───────────────────────────────
    public function view(int $id)
    {
        $official = $this->db->table('officials o')
            ->select('o.*, d.name as district_name, d.zone, ot.name as type_name, ot.prefix, u2.full_name as registered_by_name')
            ->join('districts d',       'd.id = o.district_id')
            ->join('official_types ot', 'ot.id = o.official_type_id')
            ->join('users u2',          'u2.id = o.registered_by', 'left')
            ->where('o.id', $id)
            ->get()->getRowArray();

        if (!$official) return redirect()->to('/officials')->with('error', 'Official not found.');

        if (!$this->canAccessDistrict((int)$official['district_id'])) {
            return redirect()->to('/officials')->with('error', 'Access denied for this district.');
        }

        $certs = $this->db->table('official_certifications')
            ->where('official_id', $id)->orderBy('certified_date', 'DESC')->get()->getResultArray();

        return $this->render('officials/view', [
            'pageTitle' => $official['full_name'] . ' — Official Profile',
            'official'  => $official,
            'certs'     => $certs,
        ]);
    }

    // ── GET /officials/edit/:id ───────────────────────────────
    public function edit(int $id)
    {
        $this->requirePermission('officials');

        $official = $this->db->table('officials')->where('id', $id)->get()->getRowArray();
        if (!$official) return redirect()->to('/officials')->with('error', 'Official not found.');

        if (!$this->canAccessDistrict((int)$official['district_id'])) {
            return redirect()->to('/officials')->with('error', 'Access denied for this district.');
        }

        $allowedIds    = $this->getAllowedDistrictIdsFlat();
        $districtQuery = $this->db->table('districts')->where('is_active', 1)->orderBy('name');
        if (($this->currentUser['role_name'] ?? '') !== 'superadmin' && !empty($allowedIds)) {
            $districtQuery->whereIn('id', $allowedIds);
        }

        $certs = $this->db->table('official_certifications')->where('official_id', $id)->get()->getResultArray();

        return $this->render('officials/form', [
            'pageTitle'     => 'Edit Official — JSCA ERP',
            'official'      => $official,
            'certs'         => $certs,
            'officialTypes' => $this->db->table('official_types')->where('is_active', 1)->get()->getResultArray(),
            'districts'     => $districtQuery->get()->getResultArray(),
        ]);
    }

    // ── POST /officials/update/:id ────────────────────────────
    public function update(int $id)
    {
        $this->requirePermission('officials');

        $old = $this->db->table('officials')->where('id', $id)->get()->getRowArray();
        if (!$old) return redirect()->to('/officials')->with('error', 'Official not found.');

        if (!$this->canAccessDistrict((int)$old['district_id'])) {
            return redirect()->to('/officials')->with('error', 'Access denied for this district.');
        }

        $rules = [
            'full_name'        => 'required|min_length[3]|max_length[100]',
            'official_type_id' => 'required|is_natural_no_zero',
            'district_id'      => 'required|is_natural_no_zero',
            'gender'           => 'required|in_list[Male,Female,Other]',
            'email'            => 'permit_empty|valid_email',
            'phone'            => 'permit_empty|regex_match[/^[6-9][0-9]{9}$/]',
            'dob'              => 'permit_empty|valid_date[Y-m-d]',
            'experience_years' => 'permit_empty|is_natural',
            'fee_per_match'    => 'permit_empty|decimal',
            'bank_ifsc'        => 'permit_empty|regex_match[/^[A-Z]{4}0[A-Z0-9]{6}$/]',
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()->with('errors', $this->validator->getErrors())->withInput();
        }

        $post = $this->request->getPost();
        $data = [
            'official_type_id' => $post['official_type_id'],
            'full_name'        => $post['full_name'],
            'email'            => $post['email'] ?: null,
            'phone'            => $post['phone'] ?: null,
            'gender'           => $post['gender'],
            'dob'              => $post['dob'] ?: null,
            'district_id'      => $post['district_id'],
            'address'          => $post['address'] ?: null,
            'experience_years' => $post['experience_years'] ?: null,
            'grade'            => $post['grade']         ?: null,
            'fee_per_match'    => $post['fee_per_match']  ?: null,
            'bank_name'        => $post['bank_name']      ?: null,
            'bank_account'     => $post['bank_account']   ?: null,
            'bank_ifsc'        => $post['bank_ifsc']      ?: null,
            'status'           => $post['status'] ?? $old['status'],
            'updated_at'       => date('Y-m-d H:i:s'),
        ];

        try {
            $newPhoto = $this->uploadFile('profile_photo', 'officials', ['jpg','jpeg','png'], 5);
            if ($newPhoto) $data['profile_photo'] = $newPhoto;
        } catch (\RuntimeException $e) {
            return redirect()->back()->with('error', $e->getMessage())->withInput();
        }

        $this->db->table('officials')->where('id', $id)->update($data);

        // Replace certifications
        $this->db->table('official_certifications')->where('official_id', $id)->delete();
        $this->saveCertifications($id, $post);

        $this->audit('UPDATE', 'officials', $id, $old, $data);

        return redirect()->to('/officials/view/' . $id)->with('success', 'Official updated successfully.');
    }

    // ── POST /officials/toggle/:id ────────────────────────────
    public function toggle(int $id)
    {
        $this->requirePermission('officials');

        $official = $this->db->table('officials')->where('id', $id)->get()->getRowArray();
        if (!$official) return redirect()->back()->with('error', 'Official not found.');

        if (!$this->canAccessDistrict((int)$official['district_id'])) {
            return redirect()->back()->with('error', 'Access denied for this district.');
        }

        $newStatus = $official['status'] === 'Active' ? 'Inactive' : 'Active';
        $this->db->table('officials')->where('id', $id)->update([
            'status'     => $newStatus,
            'updated_at' => date('Y-m-d H:i:s'),
        ]);
        $this->audit('TOGGLE', 'officials', $id);

        return redirect()->to('/officials/view/' . $id)
            ->with('success', 'Official ' . strtolower($newStatus) . '.');
    }

    // ── Private helpers ───────────────────────────────────────

    private function generateOfficialIdByType(string $prefix): string
    {
        $count = $this->db->table('officials o')
            ->join('official_types ot', 'ot.id = o.official_type_id')
            ->where('ot.prefix', $prefix)
            ->countAllResults() + 1;
        return 'JSCA-' . $prefix . '-' . str_pad($count, 4, '0', STR_PAD_LEFT);
    }

    private function generatePassword(int $length = 10): string
    {
        $chars = 'abcdefghjkmnpqrstuvwxyzABCDEFGHJKMNPQRSTUVWXYZ23456789@#!';
        $pw    = '';
        for ($i = 0; $i < $length; $i++) {
            $pw .= $chars[random_int(0, strlen($chars) - 1)];
        }
        return $pw;
    }

    private function saveCertifications(int $officialId, array $post): void
    {
        $names = $post['cert_name']  ?? [];
        $bodies = $post['cert_body'] ?? [];
        $levels = $post['cert_level'] ?? [];
        $dates  = $post['cert_date']  ?? [];

        foreach ($names as $i => $name) {
            if (empty(trim($name))) continue;
            $this->db->table('official_certifications')->insert([
                'official_id'        => $officialId,
                'certification_name' => trim($name),
                'body'               => $bodies[$i] ?? null,
                'level'              => $levels[$i] ?? null,
                'certified_date'     => !empty($dates[$i]) ? $dates[$i] : null,
                'created_at'         => date('Y-m-d H:i:s'),
            ]);
        }
    }
}
