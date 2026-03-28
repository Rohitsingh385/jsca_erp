<?php
// app/Controllers/Finance.php
namespace App\Controllers;

class Finance extends BaseController
{
    // ── GET /finance ──────────────────────────────────────────
    public function index()
    {
        $this->requirePermission('finance.view');

        $summary = [
            'total_paid'      => $this->getSum('Paid'),
            'total_pending'   => $this->getSum('Pending Approval'),
            'total_approved'  => $this->getSum('Approved'),
            'total_rejected'  => $this->getSum('Rejected'),
            'voucher_count'   => $this->db->table('payment_vouchers')->countAllResults(),
            'pending_count'   => $this->db->table('payment_vouchers')->where('status', 'Pending Approval')->countAllResults(),
        ];

        $monthlyTrend = $this->db->query("
            SELECT DATE_FORMAT(created_at, '%Y-%m') as month,
                   SUM(CASE WHEN status = 'Paid' THEN amount ELSE 0 END) as paid,
                   COUNT(*) as total_vouchers
            FROM payment_vouchers
            WHERE created_at >= DATE_SUB(NOW(), INTERVAL 6 MONTH)
            GROUP BY month ORDER BY month ASC
        ")->getResultArray();

        $byPayeeType = $this->db->table('payment_vouchers')
            ->select('payee_type, SUM(amount) as total, COUNT(*) as count')
            ->where('status', 'Paid')
            ->groupBy('payee_type')
            ->get()->getResultArray();

        $bankAccounts = $this->db->table('bank_acc_master')
            ->select('id, bank_name, acc_no, opening_bal, acc_type, updated_at')
            ->get()
            ->getResultArray();

        return $this->render('finance/index', [
            'pageTitle'    => 'Finance — JSCA ERP',
            'summary'      => $summary,
            'monthlyTrend' => $monthlyTrend,
            'byPayeeType'  => $byPayeeType,
            'bankAccounts' => $bankAccounts,
        ]);
    }

    // ── GET /finance/vouchers ─────────────────────────────────
    public function vouchers()
    {
        $this->requirePermission('finance.view');

        $status   = $this->request->getGet('status');
        $search   = $this->request->getGet('q');
        $perPage  = 25;
        $page     = (int)($this->request->getGet('page') ?? 1);

        $builder = $this->db->table('payment_vouchers pv')
            ->select('pv.*, u1.full_name as created_by_name, u2.full_name as approved_by_name, t.name as tournament_name')
            ->join('users u1', 'u1.id = pv.created_by', 'left')
            ->join('users u2', 'u2.id = pv.approved_by', 'left')
            ->join('tournaments t', 't.id = pv.tournament_id', 'left');

        if ($status)  $builder->where('pv.status', $status);
        if ($search)  $builder->groupStart()->like('pv.voucher_number', $search)->orLike('pv.payee_name', $search)->groupEnd();

        $total    = $builder->countAllResults(false);
        $vouchers = $builder->orderBy('pv.created_at', 'DESC')
            ->limit($perPage, ($page - 1) * $perPage)
            ->get()->getResultArray();

        return $this->render('finance/vouchers', [
            'pageTitle' => 'Payment Vouchers — JSCA ERP',
            'vouchers'  => $vouchers,
            'total'     => $total,
            'page'      => $page,
            'perPage'   => $perPage,
            'status'    => $status,
        ]);
    }

    // ── GET /finance/voucher/create ───────────────────────────
    public function createVoucher()
    {
        $this->requirePermission('finance.view');

        return $this->render('finance/voucher_form', [
            'pageTitle'   => 'Create Voucher — JSCA ERP',
            'voucher'     => $this->generateVoucherNumber(),
            'officials'   => $this->db->table('officials')->where('status', 1)->orderBy('full_name')->get()->getResultArray(),
            'tournaments' => $this->db->table('tournaments')->orderBy('name')->get()->getResultArray(),
            'fixtures'    => $this->db->table('fixtures f')
                ->select('f.id, f.match_number, f.match_date, ta.name as team_a, tb.name as team_b')
                ->join('teams ta', 'ta.id = f.team_a_id')
                ->join('teams tb', 'tb.id = f.team_b_id')
                ->where('f.status', 'Completed')
                ->orderBy('f.match_date', 'DESC')
                ->limit(100)
                ->get()->getResultArray(),
            'ledger_heads' => $this->db->table('ledger_heads')->select('id,group_id,name,opening_balance,balance_type')->get()->getresultArray(),
            'bank_acc' => $this->db->table('bank_acc_master')->select('*')->get()->getresultArray()
        ]);
    }

    // ── POST /finance/voucher/store ───────────────────────────
    public function storeVoucher()
    {
        $this->requirePermission('finance.view');

        $rules = [
            'payee_name'  => 'required|min_length[3]',
            'payee_type'  => 'required',
            'amount'      => 'required|decimal|greater_than[0]',
            'bank_account' => 'permit_empty|min_length[9]',
            'bank_ifsc'   => 'permit_empty|min_length[11]|max_length[11]',
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()->with('errors', $this->validator->getErrors())->withInput();
        }

        $post = $this->request->getPost();

        $data = [
            'voucher_number' => $this->generateVoucherNumber(),
            'fixture_id'    => $post['fixture_id']    ?: null,
            'tournament_id' => $post['tournament_id'] ?: null,
            'official_id'   => $post['official_id']   ?: null,
            'payee_name'    => $post['payee_name'],
            'payee_type'    => $post['payee_type'],
            'amount'        => $post['amount'],
            'description'   => $post['description']   ?? null,
            'bank_account'  => $post['bank_account']  ?? null,
            'bank_ifsc'     => $post['bank_ifsc']      ?? null,
            'bank_name'     => $post['bank_name']      ?? null,
            'payment_mode'  => $post['payment_mode']   ?? 'NEFT',
            'status'        => 'Pending Approval',
            'created_by'    => session('user_id'),
            'created_at'    => date('Y-m-d H:i:s'),
        ];

        $this->db->table('payment_vouchers')->insert($data);
        $id = $this->db->insertID();

        $this->audit('CREATE_VOUCHER', 'finance', $id, null, $data);

        return redirect()->to('/finance/voucher/view/' . $id)
            ->with('success', 'Voucher ' . $data['voucher_number'] . ' created and sent for approval.');
    }

    // ── GET /finance/voucher/view/:id ─────────────────────────
    public function viewVoucher(int $id)
    {
        $this->requirePermission('finance.view');

        $voucher = $this->db->table('payment_vouchers pv')
            ->select('pv.*, u1.full_name as created_by_name, u2.full_name as approved_by_name,
                      f.match_number, f.match_date, ta.name as team_a, tb.name as team_b,
                      t.name as tournament_name')
            ->join('users u1', 'u1.id = pv.created_by', 'left')
            ->join('users u2', 'u2.id = pv.approved_by', 'left')
            ->join('fixtures f', 'f.id = pv.fixture_id', 'left')
            ->join('teams ta', 'ta.id = f.team_a_id', 'left')
            ->join('teams tb', 'tb.id = f.team_b_id', 'left')
            ->join('tournaments t', 't.id = pv.tournament_id', 'left')
            ->where('pv.id', $id)
            ->get()->getRowArray();

        if (!$voucher) return redirect()->to('/finance/vouchers')->with('error', 'Voucher not found.');

        return $this->render('finance/voucher_view', [
            'pageTitle' => 'Voucher ' . $voucher['voucher_number'],
            'voucher'   => $voucher,
            'canApprove' => $this->can('finance.approve'),
        ]);
    }

    // ── POST /finance/voucher/approve/:id ────────────────────
    public function approveVoucher(int $id)
    {
        $this->requirePermission('finance.approve');

        $voucher = $this->db->table('payment_vouchers')->where('id', $id)->get()->getRowArray();
        if (!$voucher || $voucher['status'] !== 'Pending Approval') {
            return redirect()->back()->with('error', 'Voucher not found or not pending approval.');
        }

        $this->db->table('payment_vouchers')->where('id', $id)->update([
            'status'      => 'Approved',
            'approved_by' => session('user_id'),
            'approved_at' => date('Y-m-d H:i:s'),
            'remarks'     => $this->request->getPost('remarks'),
            'updated_at'  => date('Y-m-d H:i:s'),
        ]);

        $this->audit('APPROVE_VOUCHER', 'finance', $id, $voucher);

        // TODO: Trigger bank transfer API here
        // $this->initiateBankTransfer($voucher);

        return redirect()->back()->with('success', 'Voucher approved. Bank transfer will be initiated shortly.');
    }

    // ── POST /finance/voucher/reject/:id ─────────────────────
    public function rejectVoucher(int $id)
    {
        $this->requirePermission('finance.approve');

        $voucher = $this->db->table('payment_vouchers')->where('id', $id)->get()->getRowArray();
        if (!$voucher) return redirect()->back()->with('error', 'Voucher not found.');

        $this->db->table('payment_vouchers')->where('id', $id)->update([
            'status'      => 'Rejected',
            'approved_by' => session('user_id'),
            'approved_at' => date('Y-m-d H:i:s'),
            'remarks'     => $this->request->getPost('remarks'),
            'updated_at'  => date('Y-m-d H:i:s'),
        ]);

        $this->audit('REJECT_VOUCHER', 'finance', $id, $voucher);

        return redirect()->back()->with('success', 'Voucher rejected.');
    }

    // ── POST /finance/voucher/mark-paid/:id ──────────────────
    public function markPaid(int $id)
    {
        $this->requirePermission('finance.approve');

        $this->db->table('payment_vouchers')->where('id', $id)->update([
            'status'      => 'Paid',
            'paid_at'     => date('Y-m-d H:i:s'),
            'payment_ref' => $this->request->getPost('payment_ref'),
            'updated_at'  => date('Y-m-d H:i:s'),
        ]);

        $this->audit('MARK_PAID', 'finance', $id);

        return redirect()->back()->with('success', 'Payment marked as completed.');
    }

    // ── GET /finance/auto-generate/:fixtureId ────────────────
    // Auto-creates vouchers for all officials of a completed match
    public function autoGenerate(int $fixtureId)
    {
        $this->requirePermission('finance.view');

        $fixture = $this->db->table('fixtures f')
            ->select('f.*, o1.full_name as u1_name, o1.fee_per_match as u1_fee, o1.bank_account as u1_acct, o1.bank_ifsc as u1_ifsc, o1.bank_name as u1_bank,
                      o2.full_name as u2_name, o2.fee_per_match as u2_fee, o2.bank_account as u2_acct, o2.bank_ifsc as u2_ifsc, o2.bank_name as u2_bank,
                      sc.full_name as sc_name, sc.fee_per_match as sc_fee, sc.bank_account as sc_acct, sc.bank_ifsc as sc_ifsc, sc.bank_name as sc_bank,
                      rf.full_name as rf_name, rf.fee_per_match as rf_fee, rf.bank_account as rf_acct, rf.bank_ifsc as rf_ifsc, rf.bank_name as rf_bank,
                      t.name as tournament_name')
            ->join('officials o1', 'o1.id = f.umpire1_id', 'left')
            ->join('officials o2', 'o2.id = f.umpire2_id', 'left')
            ->join('officials sc', 'sc.id = f.scorer_id',  'left')
            ->join('officials rf', 'rf.id = f.referee_id', 'left')
            ->join('tournaments t', 't.id = f.tournament_id')
            ->where('f.id', $fixtureId)
            ->get()->getRowArray();

        if (!$fixture) {
            return redirect()->back()->with('error', 'Fixture not found.');
        }

        $created = 0;
        $officials = [
            ['name' => $fixture['u1_name'], 'fee' => $fixture['u1_fee'], 'acct' => $fixture['u1_acct'], 'ifsc' => $fixture['u1_ifsc'], 'bank' => $fixture['u1_bank'], 'type' => 'Umpire'],
            ['name' => $fixture['u2_name'], 'fee' => $fixture['u2_fee'], 'acct' => $fixture['u2_acct'], 'ifsc' => $fixture['u2_ifsc'], 'bank' => $fixture['u2_bank'], 'type' => 'Umpire'],
            ['name' => $fixture['sc_name'], 'fee' => $fixture['sc_fee'], 'acct' => $fixture['sc_acct'], 'ifsc' => $fixture['sc_ifsc'], 'bank' => $fixture['sc_bank'], 'type' => 'Scorer'],
            ['name' => $fixture['rf_name'], 'fee' => $fixture['rf_fee'], 'acct' => $fixture['rf_acct'], 'ifsc' => $fixture['rf_ifsc'], 'bank' => $fixture['rf_bank'], 'type' => 'Referee'],
        ];

        foreach ($officials as $off) {
            if (empty($off['name'])) continue;

            // Don't duplicate if voucher already exists for this fixture+official
            $existing = $this->db->table('payment_vouchers')
                ->where('fixture_id', $fixtureId)
                ->where('payee_name', $off['name'])
                ->countAllResults();

            if ($existing > 0) continue;

            $this->db->table('payment_vouchers')->insert([
                'voucher_number' => $this->generateVoucherNumber(),
                'fixture_id'     => $fixtureId,
                'tournament_id'  => $fixture['tournament_id'],
                'payee_name'     => $off['name'],
                'payee_type'     => $off['type'],
                'amount'         => $off['fee'] ?? 500,
                'description'    => "Match " . ($fixture['match_number'] ?? $fixtureId) . " — " . $fixture['tournament_name'],
                'bank_account'   => $off['acct'] ?? null,
                'bank_ifsc'      => $off['ifsc'] ?? null,
                'bank_name'      => $off['bank'] ?? null,
                'payment_mode'   => 'NEFT',
                'status'         => 'Pending Approval',
                'created_by'     => session('user_id'),
                'created_at'     => date('Y-m-d H:i:s'),
            ]);
            $created++;
        }

        $this->audit('AUTO_VOUCHERS', 'finance', $fixtureId, null, ['vouchers_created' => $created]);

        return redirect()->to('/finance/vouchers')
            ->with('success', "{$created} payment voucher(s) auto-generated for match officials.");
    }

    // ── GET /finance/reports ──────────────────────────────────
    public function reports()
    {
        $this->requirePermission('finance.view');

        $byTournament = $this->db->table('payment_vouchers pv')
            ->select('t.name as tournament, SUM(pv.amount) as total, COUNT(*) as voucher_count,
                      SUM(CASE WHEN pv.status = "Paid" THEN pv.amount ELSE 0 END) as paid,
                      SUM(CASE WHEN pv.status = "Pending Approval" THEN pv.amount ELSE 0 END) as pending')
            ->join('tournaments t', 't.id = pv.tournament_id', 'left')
            ->groupBy('pv.tournament_id')
            ->orderBy('total', 'DESC')
            ->get()->getResultArray();

        return $this->render('finance/reports', [
            'pageTitle'    => 'Finance Reports — JSCA ERP',
            'byTournament' => $byTournament,
        ]);
    }

    // ── GET /finance/export ───────────────────────────────────
    public function export()
    {
        $this->requirePermission('reports.finance');

        $vouchers = $this->db->table('payment_vouchers pv')
            ->select('pv.voucher_number, pv.payee_name, pv.payee_type, pv.amount, pv.status, pv.payment_mode,
                      pv.bank_account, pv.bank_ifsc, pv.bank_name, pv.description,
                      pv.created_at, pv.approved_at, pv.paid_at, pv.payment_ref,
                      u1.full_name as created_by, u2.full_name as approved_by, t.name as tournament')
            ->join('users u1', 'u1.id = pv.created_by', 'left')
            ->join('users u2', 'u2.id = pv.approved_by', 'left')
            ->join('tournaments t', 't.id = pv.tournament_id', 'left')
            ->orderBy('pv.created_at', 'DESC')
            ->get()->getResultArray();

        $filename = 'JSCA_Finance_' . date('Ymd_His') . '.csv';
        header('Content-Type: text/csv');
        header('Content-Disposition: attachment; filename="' . $filename . '"');

        $out = fopen('php://output', 'w');
        fputcsv($out, array_keys($vouchers[0] ?? []));
        foreach ($vouchers as $v) fputcsv($out, array_values($v));
        fclose($out);
        exit;
    }

    // ─── Private helpers ──────────────────────────────────────
    private function getSum(string $status): float
    {
        return (float)$this->db->table('payment_vouchers')
            ->where('status', $status)->selectSum('amount')->get()->getRowArray()['amount'] ?? 0;
    }


    public function accgroups()
    {
        $groups = $this->db->table('account_groups')
            ->select('*')
            ->orderby('(CAST(SUBSTRING(G_Name, 2) AS UNSIGNED)) ASC')
            ->get()
            ->getResultArray();

        return $this->render('finance/accgroups', [
            'groups' => $groups
        ]);
    }

    public function storeaccGroup()
    {
        $row = $this->db->table('account_groups')
            ->select('(MAX(CAST(SUBSTRING(G_Name, 2) AS UNSIGNED)) + 1) AS new_id')
            ->get()
            ->getRow();

        $gpid = 'G' . ($row->new_id ?? 1);

        $this->db->table('account_groups')->insert([
            'G_Name' => $gpid,
            'Acc_Name' => $this->request->getPost('name'),
            'Acc_Type' => $this->request->getPost('acc_type'),
            'YesNo' => 'No'
        ]);

        return redirect()->back()->with('success', 'Group created');
    }

    public function deleteaccGroup($G_Name)
    {
        $this->db->table('account_groups')->delete(['G_Name' => $G_Name]);
        return redirect()->back()->with('success', 'Deleted');
    }

    public function ledgerHeads()
    {
        $ledgers = $this->db->table('ledger_heads l')
            ->select('l.*, g.Acc_Name as group_name')
            ->join('account_groups g', 'g.G_Name = l.group_id')
            ->get()->getResultArray();

        $groups = $this->db->table('account_groups')->get()->getResultArray();

        return $this->render('finance/ledger_heads', [
            'ledgers' => $ledgers,
            'groups'  => $groups
        ]);
    }

    public function storeLedger()
    {
        $this->db->table('ledger_heads')->insert([
            'group_id' => $this->request->getPost('group_id'),
            'name' => $this->request->getPost('name'),
            'opening_balance' => $this->request->getPost('opening_balance'),
            'balance_type' => $this->request->getPost('balance_type'),
            'created_at' => date('Y-m-d H:i:s')
        ]);

        return redirect()->back()->with('success', 'Ledger created');
    }

    public function deleteLedger($id)
    {
        $this->db->table('ledger_heads')->delete(['id' => $id]);
        return redirect()->back()->with('success', 'Deleted');
    }

    // ── GET /finance/voucher/receipt voucher create ───────────────────────────
    public function rcpt_create()
    {

        $this->requirePermission('finance.view');

        return $this->render('finance/voucher_form_rcpt', [
            'pageTitle'   => 'Create Voucher — JSCA ERP',
            'voucher'     => $this->generateVoucherNumber(),
            'officials'   => $this->db->table('officials')->where('status', 1)->orderBy('full_name')->get()->getResultArray(),
            'official_types'   => $this->db->table('official_types')->where('is_active', 1)->orderBy('name')->get()->getResultArray(),
            'tournaments' => $this->db->table('tournaments')->orderBy('name')->get()->getResultArray(),
            'fixtures'    => $this->db->table('fixtures f')
                ->select('f.tournament_id, f.match_number, f.match_date, ta.name as team_a, tb.name as team_b,f.team_a_id,f.team_b_id')
                ->join('teams ta', 'ta.id = f.team_a_id')
                ->join('teams tb', 'tb.id = f.team_b_id')
                ->where('f.status', 'Completed')
                ->orderBy('f.match_date', 'DESC')
                ->limit(100)
                ->get()->getResultArray(),
            'ledger_heads' => $this->db->table('ledger_heads')->select('id,group_id,name,opening_balance,balance_type')->get()->getresultArray(),
            'bank_acc' => $this->db->table('bank_acc_master')->select('*')->get()->getresultArray()
        ]);
    }

    public function getMatchesByTournament()
    {
        $tournamentId = $this->request->getPost('tournament_id');

        if (empty($tournamentId)) {
            return $this->response->setJSON([]);
        }

        $matches = $this->db->table('fixtures f')
            ->select('f.id, ta.name as team_a, tb.name as team_b, f.team_a_id, f.team_b_id')
            ->join('teams ta', 'ta.id = f.team_a_id')
            ->join('teams tb', 'tb.id = f.team_b_id')
            ->where('f.tournament_id', $tournamentId)
            ->where('f.status', 'Completed')
            ->get()->getResultArray();

        return $this->response->setJSON($matches);
    }

    public function getOfficialsByType()
    {
        $typeId = $this->request->getPost('type_id');
        $matchId = $this->request->getPost('match_id');

        if (empty($typeId)) {
            return $this->response->setJSON([]);
        }

        // Adjust table name and column names to match your database
        $officials = $this->db->table('match_officials')
            ->select('id, name')
            ->where('official_type_id', $typeId)
            ->where('status', 'Active')
            ->where('match_id', $matchId)
            ->where('official_type_id', $typeId)
            ->where('PAmt', 0.00)
            ->orderBy('name', 'ASC')
            ->get()->getResultArray();

        return $this->response->setJSON($officials);
    }
}
