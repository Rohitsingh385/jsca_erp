<?php
// app/Controllers/FinanceReports.php
namespace App\Controllers;

class FinanceReports extends BaseController
{
    private function requireFinance(): void
    {
        if (!$this->can('finance') && !$this->can('reports')) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();
        }
    }

    // ── GET /finance/reports ──────────────────────────────────
    public function index()
    {
        $this->requireFinance();

        // Summary stats
        $totalGenerated = $this->db->table('invoices')->where('status', 'Generated')->countAllResults();
        $totalPaid      = $this->db->table('invoices')->where('status', 'Paid')->countAllResults();
        $amountPending  = $this->db->table('invoices')->where('status', 'Generated')->selectSum('amount')->get()->getRowArray()['amount'] ?? 0;
        $amountPaid     = $this->db->table('invoices')->where('status', 'Paid')->selectSum('amount')->get()->getRowArray()['amount'] ?? 0;
        $noBank         = $this->db->table('officials')->where('status', 'Active')->where('(bank_account IS NULL OR bank_account = "")')->countAllResults();

        return $this->render('finance/reports/index', [
            'pageTitle'      => 'Finance Reports — JSCA ERP',
            'totalGenerated' => $totalGenerated,
            'totalPaid'      => $totalPaid,
            'amountPending'  => $amountPending,
            'amountPaid'     => $amountPaid,
            'noBank'         => $noBank,
        ]);
    }

    // ── GET /finance/reports/invoices ─────────────────────────
    public function invoices()
    {
        $this->requireFinance();

        $status     = $this->request->getGet('status');
        $tournament = $this->request->getGet('tournament');
        $type       = $this->request->getGet('type');

        $builder = $this->db->table('invoices i')
            ->select('i.*')
            ->orderBy('i.snap_match_date', 'DESC')
            ->orderBy('i.generated_at', 'DESC');

        if ($status)     $builder->where('i.status', $status);
        if ($tournament) $builder->like('i.snap_tournament', $tournament);
        if ($type)       $builder->where('i.snap_type', $type);

        $invoices = $builder->get()->getResultArray();

        $totalAmount = array_sum(array_column($invoices, 'amount'));

        return $this->render('finance/reports/invoices', [
            'pageTitle'   => 'Invoice Summary — JSCA ERP',
            'invoices'    => $invoices,
            'totalAmount' => $totalAmount,
            'status'      => $status,
            'tournament'  => $tournament,
            'type'        => $type,
            'tournaments' => $this->db->table('invoices')->select('snap_tournament')->distinct()->orderBy('snap_tournament')->get()->getResultArray(),
        ]);
    }

    // ── GET /finance/reports/pending ──────────────────────────
    public function pending()
    {
        $this->requireFinance();

        // Group pending invoices by official
        $rows = $this->db->table('invoices i')
            ->select('i.official_id, i.snap_name, i.snap_jsca_id, i.snap_type, i.snap_grade,
                      i.snap_phone, i.snap_email, i.snap_bank_name, i.snap_bank_account, i.snap_bank_ifsc,
                      COUNT(*) as match_count, SUM(i.amount) as total_pending')
            ->where('i.status', 'Generated')
            ->groupBy('i.official_id')
            ->orderBy('total_pending', 'DESC')
            ->get()->getResultArray();

        // For each official get their pending invoices detail
        $officials = [];
        foreach ($rows as $r) {
            $invoices = $this->db->table('invoices')
                ->where('official_id', $r['official_id'])
                ->where('status', 'Generated')
                ->orderBy('snap_match_date', 'ASC')
                ->get()->getResultArray();
            $officials[] = array_merge($r, ['invoices' => $invoices]);
        }

        $grandTotal = array_sum(array_column($rows, 'total_pending'));

        return $this->render('finance/reports/pending', [
            'pageTitle'  => 'Pending Payments — JSCA ERP',
            'officials'  => $officials,
            'grandTotal' => $grandTotal,
        ]);
    }

    // ── GET /finance/reports/no-bank ──────────────────────────
    public function noBank()
    {
        $this->requireFinance();

        $officials = $this->db->table('officials o')
            ->select('o.id, o.full_name, o.jsca_official_id, o.phone, o.email, o.grade,
                      ot.name as type_name, d.name as district_name,
                      (SELECT COUNT(*) FROM invoices i WHERE i.official_id=o.id AND i.status="Generated") as pending_invoices,
                      (SELECT SUM(i.amount) FROM invoices i WHERE i.official_id=o.id AND i.status="Generated") as pending_amount')
            ->join('official_types ot', 'ot.id = o.official_type_id')
            ->join('districts d',       'd.id = o.district_id')
            ->where('o.status', 'Active')
            ->groupStart()
                ->where('o.bank_account IS NULL')
                ->orWhere('o.bank_account', '')
            ->groupEnd()
            ->orderBy('pending_amount', 'DESC')
            ->get()->getResultArray();

        return $this->render('finance/reports/no_bank', [
            'pageTitle' => 'Officials Without Bank Details — JSCA ERP',
            'officials' => $officials,
        ]);
    }

    // ── POST /finance/reports/mark-paid ───────────────────────
    public function markPaid()
    {
        $this->requireFinance();

        $invoiceIds = $this->request->getPost('invoice_ids') ?? [];
        if (empty($invoiceIds)) {
            return redirect()->back()->with('error', 'No invoices selected.');
        }

        $this->db->table('invoices')
            ->whereIn('id', $invoiceIds)
            ->where('status', 'Generated')
            ->update([
                'status'  => 'Paid',
                'paid_at' => date('Y-m-d H:i:s'),
                'paid_by' => session('user_id'),
            ]);

        // Also update match_officials Pdate
        $invoices = $this->db->table('invoices')->whereIn('id', $invoiceIds)->get()->getResultArray();
        foreach ($invoices as $inv) {
            $this->db->table('match_officials')
                ->where('id', $inv['match_officials_id'])
                ->update(['Pdate' => date('Y-m-d')]);
        }

        $this->audit('MARK_PAID', 'invoices', null, null, ['invoice_ids' => $invoiceIds]);

        return redirect()->back()->with('success', count($invoiceIds) . ' invoice(s) marked as paid.');
    }
}
