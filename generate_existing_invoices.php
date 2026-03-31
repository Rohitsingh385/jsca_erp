<?php
// One-time script to generate invoices for already-completed fixtures
// Run: docker exec jsca_erp-web-1 php generate_existing_invoices.php

define('FCPATH', __DIR__ . '/public/');
define('ROOTPATH', __DIR__ . '/');

require_once 'vendor/autoload.php';

$app = \Config\Services::codeigniter();
$app->initialize();

$db = \Config\Database::connect();

// Get all completed fixtures
$fixtures = $db->table('fixtures f')
    ->select('f.*, t.name as tournament_name, ta.name as team_a, tb.name as team_b, v.name as venue_name')
    ->join('tournaments t', 't.id = f.tournament_id')
    ->join('teams ta',      'ta.id = f.team_a_id')
    ->join('teams tb',      'tb.id = f.team_b_id')
    ->join('venues v',      'v.id = f.venue_id', 'left')
    ->where('f.status', 'Completed')
    ->get()->getResultArray();

$prefixMap = ['Umpire' => 'UMP', 'Scorer' => 'SCR', 'Referee' => 'REF', 'Match Referee' => 'MRF'];
$generated = 0;

foreach ($fixtures as $fixture) {
    $officials = $db->table('match_officials mo')
        ->select('mo.id as mo_id, mo.official_id, mo.PAmt, o.full_name, o.jsca_official_id,
                  o.grade, o.phone, o.email, o.address, o.bank_name, o.bank_account, o.bank_ifsc,
                  ot.name as type_name')
        ->join('officials o',       'o.id = mo.official_id')
        ->join('official_types ot', 'ot.id = mo.official_type_id')
        ->where('mo.match_id', $fixture['id'])
        ->get()->getResultArray();

    foreach ($officials as $off) {
        $exists = $db->table('invoices')->where('match_officials_id', $off['mo_id'])->countAllResults();
        if ($exists || empty($off['PAmt']) || $off['PAmt'] <= 0) continue;

        $prefix = $prefixMap[$off['type_name']] ?? 'OFF';
        $year   = date('Y');
        $count  = $db->table('invoices')->like('invoice_number', 'INV-' . $year . '-' . $prefix . '-', 'after')->countAllResults() + 1;
        $invNum = 'INV-' . $year . '-' . $prefix . '-' . str_pad($count, 4, '0', STR_PAD_LEFT);

        $db->table('invoices')->insert([
            'invoice_number'     => $invNum,
            'fixture_id'         => $fixture['id'],
            'match_officials_id' => $off['mo_id'],
            'official_id'        => $off['official_id'],
            'snap_name'          => $off['full_name'],
            'snap_jsca_id'       => $off['jsca_official_id'],
            'snap_type'          => $off['type_name'],
            'snap_grade'         => $off['grade'],
            'snap_phone'         => $off['phone'],
            'snap_email'         => $off['email'],
            'snap_address'       => $off['address'],
            'snap_bank_name'     => $off['bank_name'],
            'snap_bank_account'  => $off['bank_account'],
            'snap_bank_ifsc'     => $off['bank_ifsc'],
            'snap_tournament'    => $fixture['tournament_name'],
            'snap_match_number'  => $fixture['match_number'],
            'snap_match_date'    => $fixture['match_date'],
            'snap_teams'         => $fixture['team_a'] . ' vs ' . $fixture['team_b'],
            'snap_venue'         => $fixture['venue_name'],
            'snap_role'          => $off['type_name'],
            'amount'             => $off['PAmt'],
            'status'             => 'Generated',
            'generated_at'       => date('Y-m-d H:i:s'),
        ]);
        echo "Generated: $invNum for {$off['full_name']} — {$fixture['match_number']}\n";
        $generated++;
    }
}

echo "\nDone. $generated invoices generated.\n";
