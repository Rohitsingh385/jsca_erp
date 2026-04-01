<?php
// app/Controllers/Venues.php
namespace App\Controllers;

class Venues extends BaseController
{
    // ── GET /venues ───────────────────────────────────────────
    public function index()
    {
        $search   = $this->request->getGet('q');
        $district = $this->request->getGet('district');
        $status   = $this->request->getGet('status');

        $allowedIds = $this->getAllowedDistrictIdsFlat();

        $builder = $this->db->table('venues v')
            ->select('v.*, d.name as district_name, d.zone')
            ->join('districts d', 'd.id = v.district_id');

        // District RBAC
        if (($this->currentUser['role_name'] ?? '') !== 'superadmin') {
            if (empty($allowedIds)) {
                $builder->where('1=0');
            } else {
                $builder->whereIn('v.district_id', $allowedIds);
            }
        }

        if ($search) {
            $builder->groupStart()
                ->like('v.name', $search)
                ->orLike('v.address', $search)
                ->orLike('v.contact_person', $search)
            ->groupEnd();
        }
        if ($district && $this->canAccessDistrict((int)$district)) $builder->where('v.district_id', $district);
        if ($status !== null && $status !== '') $builder->where('v.is_active', $status === 'active' ? 1 : 0);

        $venues = $builder->orderBy('v.name')->get()->getResultArray();

        $districtQuery = $this->db->table('districts')->where('is_active', 1)->orderBy('name');
        if (($this->currentUser['role_name'] ?? '') !== 'superadmin' && !empty($allowedIds)) {
            $districtQuery->whereIn('id', $allowedIds);
        }

        return $this->render('venues/index', [
            'pageTitle' => 'Venues — JSCA ERP',
            'venues'    => $venues,
            'search'    => $search,
            'district'  => $district,
            'status'    => $status,
            'districts' => $districtQuery->get()->getResultArray(),
        ]);
    }

    // ── GET /venues/create ────────────────────────────────────
    public function create()
    {
        $this->requirePermission('venues');

        $allowedIds    = $this->getAllowedDistrictIdsFlat();
        $districtQuery = $this->db->table('districts')->where('is_active', 1)->orderBy('name');
        if (($this->currentUser['role_name'] ?? '') !== 'superadmin' && !empty($allowedIds)) {
            $districtQuery->whereIn('id', $allowedIds);
        }

        return $this->render('venues/form', [
            'pageTitle' => 'Add Venue — JSCA ERP',
            'venue'     => null,
            'districts' => $districtQuery->get()->getResultArray(),
        ]);
    }

    // ── POST /venues/store ────────────────────────────────────
    public function store()
    {
        $this->requirePermission('venues');

        $rules = [
            'name'        => 'required|min_length[3]|max_length[150]',
            'district_id' => 'required|is_natural_no_zero',
            'capacity'    => 'permit_empty|is_natural',
            'contact_phone' => 'permit_empty|regex_match[/^[6-9][0-9]{9}$/]',
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()->with('errors', $this->validator->getErrors())->withInput();
        }

        if (!$this->canAccessDistrict((int)$this->request->getPost('district_id'))) {
            return redirect()->back()->with('error', 'You do not have access to the selected district.')->withInput();
        }

        $post = $this->request->getPost();
        $data = [
            'name'            => $post['name'],
            'district_id'     => $post['district_id'],
            'capacity'        => (int)($post['capacity'] ?? 0),
            'has_floodlights' => isset($post['has_floodlights']) ? 1 : 0,
            'has_scoreboard'  => isset($post['has_scoreboard'])  ? 1 : 0,
            'has_dressing'    => isset($post['has_dressing'])    ? 1 : 0,
            'pitch_type'      => $post['pitch_type']      ?? 'Grass',
            'contact_person'  => $post['contact_person']  ?? null,
            'contact_phone'   => $post['contact_phone']   ?? null,
            'address'         => $post['address']         ?? null,
            'lat'             => $post['lat']  ?: null,
            'lng'             => $post['lng']  ?: null,
            'is_active'       => 1,
            'created_at'      => date('Y-m-d H:i:s'),
        ];

        $this->db->table('venues')->insert($data);
        $id = $this->db->insertID();
        $this->audit('CREATE', 'venues', $id, null, $data);

        return redirect()->to('/venues/view/' . $id)
            ->with('success', 'Venue "' . $data['name'] . '" added successfully.');
    }

    // ── GET /venues/view/:id ──────────────────────────────────
    public function view(int $id)
    {
        $venue = $this->db->table('venues v')
            ->select('v.*, d.name as district_name, d.zone')
            ->join('districts d', 'd.id = v.district_id')
            ->where('v.id', $id)
            ->get()->getRowArray();

        if (!$venue) return redirect()->to('/venues')->with('error', 'Venue not found.');

        if (!$this->canAccessDistrict((int)$venue['district_id'])) {
            return redirect()->to('/venues')->with('error', 'Access denied for this district.');
        }

        // Upcoming fixtures at this venue
        $fixtures = $this->db->table('fixtures f')
            ->select('f.*, t.name as tournament_name, ta.name as team_a, tb.name as team_b')
            ->join('tournaments t',  't.id = f.tournament_id')
            ->join('teams ta', 'ta.id = f.team_a_id')
            ->join('teams tb', 'tb.id = f.team_b_id')
            ->where('f.venue_id', $id)
            ->orderBy('f.match_date', 'DESC')
            ->limit(10)
            ->get()->getResultArray();

        return $this->render('venues/view', [
            'pageTitle' => $venue['name'] . ' — Venue',
            'venue'     => $venue,
            'fixtures'  => $fixtures,
        ]);
    }

    // ── GET /venues/edit/:id ──────────────────────────────────
    public function edit(int $id)
    {
        $this->requirePermission('venues');

        $venue = $this->db->table('venues')->where('id', $id)->get()->getRowArray();
        if (!$venue) return redirect()->to('/venues')->with('error', 'Venue not found.');

        if (!$this->canAccessDistrict((int)$venue['district_id'])) {
            return redirect()->to('/venues')->with('error', 'Access denied for this district.');
        }

        $allowedIds    = $this->getAllowedDistrictIdsFlat();
        $districtQuery = $this->db->table('districts')->where('is_active', 1)->orderBy('name');
        if (($this->currentUser['role_name'] ?? '') !== 'superadmin' && !empty($allowedIds)) {
            $districtQuery->whereIn('id', $allowedIds);
        }

        return $this->render('venues/form', [
            'pageTitle' => 'Edit Venue — JSCA ERP',
            'venue'     => $venue,
            'districts' => $districtQuery->get()->getResultArray(),
        ]);
    }

    // ── POST /venues/update/:id ───────────────────────────────
    public function update(int $id)
    {
        $this->requirePermission('venues');

        $old = $this->db->table('venues')->where('id', $id)->get()->getRowArray();
        if (!$old) return redirect()->to('/venues')->with('error', 'Venue not found.');

        if (!$this->canAccessDistrict((int)$old['district_id'])) {
            return redirect()->to('/venues')->with('error', 'Access denied for this district.');
        }

        $rules = [
            'name'          => 'required|min_length[3]|max_length[150]',
            'district_id'   => 'required|is_natural_no_zero',
            'capacity'      => 'permit_empty|is_natural',
            'contact_phone' => 'permit_empty|regex_match[/^[6-9][0-9]{9}$/]',
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()->with('errors', $this->validator->getErrors())->withInput();
        }

        $post = $this->request->getPost();
        $data = [
            'name'            => $post['name'],
            'district_id'     => $post['district_id'],
            'capacity'        => (int)($post['capacity'] ?? 0),
            'has_floodlights' => isset($post['has_floodlights']) ? 1 : 0,
            'has_scoreboard'  => isset($post['has_scoreboard'])  ? 1 : 0,
            'has_dressing'    => isset($post['has_dressing'])    ? 1 : 0,
            'pitch_type'      => $post['pitch_type']     ?? 'Grass',
            'contact_person'  => $post['contact_person'] ?? null,
            'contact_phone'   => $post['contact_phone']  ?? null,
            'address'         => $post['address']        ?? null,
            'lat'             => $post['lat'] ?: null,
            'lng'             => $post['lng'] ?: null,
            'is_active'       => isset($post['is_active']) ? 1 : 0,
        ];

        $this->db->table('venues')->where('id', $id)->update($data);
        $this->audit('UPDATE', 'venues', $id, $old, $data);

        return redirect()->to('/venues/view/' . $id)->with('success', 'Venue updated successfully.');
    }

    // ── GET /venues/report ────────────────────────────────────
    public function report()
    {
        $venueId  = $this->request->getGet('venue_id');
        $fromDate = $this->request->getGet('from');
        $toDate   = $this->request->getGet('to');

        $allowedIds = $this->getAllowedDistrictIdsFlat();

        $vq = $this->db->table('venues v')
            ->select('v.id, v.name, d.name as district_name')
            ->join('districts d', 'd.id = v.district_id')
            ->where('v.is_active', 1)->orderBy('v.name');
        if (($this->currentUser['role_name'] ?? '') !== 'superadmin' && !empty($allowedIds)) {
            $vq->whereIn('v.district_id', $allowedIds);
        }
        $allVenues = $vq->get()->getResultArray();

        $sq = $this->db->table('fixtures f')
            ->select([
                'v.id as venue_id', 'v.name as venue_name', 'd.name as district_name',
                'v.capacity', 'v.pitch_type', 'v.has_floodlights',
                'COUNT(f.id) as total_matches',
                'SUM(f.status = "Completed") as completed',
                'SUM(f.status = "Abandoned") as abandoned',
                'SUM(f.status = "Postponed") as postponed',
                'SUM(f.status = "Scheduled") as scheduled',
                'SUM(f.status = "Live") as live_now',
                'SUM(f.is_day_night = 1) as day_night',
                'COUNT(DISTINCT f.tournament_id) as tournaments',
                'MAX(f.match_date) as last_match',
                'MIN(f.match_date) as first_match',
            ])
            ->join('venues v', 'v.id = f.venue_id')
            ->join('districts d', 'd.id = v.district_id')
            ->groupBy('v.id');

        if (($this->currentUser['role_name'] ?? '') !== 'superadmin' && !empty($allowedIds)) {
            $sq->whereIn('v.district_id', $allowedIds);
        }
        if ($venueId)  $sq->where('f.venue_id', $venueId);
        if ($fromDate) $sq->where('f.match_date >=', $fromDate);
        if ($toDate)   $sq->where('f.match_date <=', $toDate);

        $stats = $sq->get()->getResultArray();

        $recentFixtures = [];
        if ($venueId) {
            $fq = $this->db->table('fixtures f')
                ->select('f.id, f.match_number, f.match_date, f.stage, f.is_day_night, f.status, f.result_summary, t.name as tournament_name, ta.name as team_a, tb.name as team_b')
                ->join('tournaments t', 't.id = f.tournament_id')
                ->join('teams ta', 'ta.id = f.team_a_id')
                ->join('teams tb', 'tb.id = f.team_b_id')
                ->where('f.venue_id', $venueId)
                ->orderBy('f.match_date', 'DESC');
            if ($fromDate) $fq->where('f.match_date >=', $fromDate);
            if ($toDate)   $fq->where('f.match_date <=', $toDate);
            $recentFixtures = $fq->get()->getResultArray();
        }

        return $this->render('venues/report', [
            'pageTitle'      => 'Venue Usage Report — JSCA ERP',
            'stats'          => $stats,
            'allVenues'      => $allVenues,
            'recentFixtures' => $recentFixtures,
            'venueId'        => $venueId,
            'fromDate'       => $fromDate,
            'toDate'         => $toDate,
        ]);
    }

    // ── POST /venues/toggle/:id ───────────────────────────────
    public function toggle(int $id)
    {
        $this->requirePermission('venues');

        $venue = $this->db->table('venues')->where('id', $id)->get()->getRowArray();
        if (!$venue) return redirect()->back()->with('error', 'Venue not found.');

        if (!$this->canAccessDistrict((int)$venue['district_id'])) {
            return redirect()->back()->with('error', 'Access denied for this district.');
        }

        $newStatus = $venue['is_active'] ? 0 : 1;
        $this->db->table('venues')->where('id', $id)->update(['is_active' => $newStatus]);
        $this->audit('TOGGLE', 'venues', $id);

        return redirect()->to('/venues/view/' . $id)
            ->with('success', 'Venue ' . ($newStatus ? 'activated' : 'deactivated') . '.');
    }
}
