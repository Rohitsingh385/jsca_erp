<?php
// app/Controllers/BaseController.php
namespace App\Controllers;

use CodeIgniter\Controller;
use CodeIgniter\HTTP\CLIRequest;
use CodeIgniter\HTTP\IncomingRequest;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use Psr\Log\LoggerInterface;

abstract class BaseController extends Controller
{
    protected $request;
    protected $helpers = ['url', 'form', 'text', 'date', 'number'];
    protected $db;
    protected $session;
    protected $currentUser;

    public function initController(
        RequestInterface $request,
        ResponseInterface $response,
        LoggerInterface $logger
    ) {
        parent::initController($request, $response, $logger);

        $this->db      = \Config\Database::connect();
        $this->session = \Config\Services::session();

        // Load current user into every controller
        if ($this->session->get('user_id')) {
            $this->currentUser = $this->db->table('users u')
                ->select('u.*, r.name as role_name, r.permissions, u.custom_permissions')
                ->join('roles r', 'r.id = u.role_id')
                ->where('u.id', $this->session->get('user_id'))
                ->get()->getRowArray();

            if ($this->currentUser) {
                $rolePerms   = json_decode($this->currentUser['permissions'] ?? '[]', true) ?? [];
                $customPerms = json_decode($this->currentUser['custom_permissions'] ?? '[]', true) ?? [];
                // Merge: role defaults + any extra custom permissions
                $this->currentUser['permissions'] = array_values(array_unique(array_merge($rolePerms, $customPerms)));
            }
        }
    }

    // ── District access helpers ───────────────────────────────

    /**
     * Returns array of district IDs the current user can access.
     * superadmin gets ALL districts. Others get only assigned ones.
     */
    protected function getAllowedDistrictIds(): array
    {
        if (!$this->currentUser) return [];

        // superadmin sees everything
        if (($this->currentUser['role_name'] ?? '') === 'superadmin') {
            return $this->db->table('districts')
                ->select('id')->where('is_active', 1)
                ->get()->getResultArray();
            // return flat array of ids
        }

        // Use session cache to avoid repeated queries
        $cached = $this->session->get('allowed_district_ids');
        if ($cached !== null) return $cached;

        $rows = $this->db->table('user_districts')
            ->select('district_id')
            ->where('user_id', $this->currentUser['id'])
            ->get()->getResultArray();

        $ids = array_column($rows, 'district_id');
        $this->session->set('allowed_district_ids', $ids);
        return $ids;
    }

    /**
     * Flat array of district IDs (ints). superadmin = all.
     */
    protected function getAllowedDistrictIdsFlat(): array
    {
        if (!$this->currentUser) return [];

        if (($this->currentUser['role_name'] ?? '') === 'superadmin') {
            $rows = $this->db->table('districts')
                ->select('id')->where('is_active', 1)
                ->get()->getResultArray();
            return array_map('intval', array_column($rows, 'id'));
        }

        $cached = $this->session->get('allowed_district_ids');
        if ($cached !== null) return array_map('intval', $cached);

        $rows = $this->db->table('user_districts')
            ->select('district_id')
            ->where('user_id', $this->currentUser['id'])
            ->get()->getResultArray();

        $ids = array_map('intval', array_column($rows, 'district_id'));
        $this->session->set('allowed_district_ids', $ids);
        return $ids;
    }

    /**
     * Check if current user can access a specific district.
     */
    protected function canAccessDistrict(int $districtId): bool
    {
        if (!$this->currentUser) return false;
        if (($this->currentUser['role_name'] ?? '') === 'superadmin') return true;
        return in_array($districtId, $this->getAllowedDistrictIdsFlat());
    }

    // ── Render view with layout ──────────────────────────────
    protected function render(string $view, array $data = []): string
    {
        $data['currentUser'] = $this->currentUser;
        $data['pageTitle']   = $data['pageTitle'] ?? 'JSCA ERP';

        $data['content'] = view($view, $data);
        return view('layouts/main', $data);
    }

    // ── Permission check ─────────────────────────────────────
    protected function can(string $permission): bool
    {
        if (!$this->currentUser) return false;
        $perms = $this->currentUser['permissions'] ?? [];
        if (in_array('all', $perms) || in_array($permission, $perms)) return true;
        // parent permission covers sub-permissions: 'players' satisfies 'players.create'
        if (str_contains($permission, '.')) {
            $parent = explode('.', $permission)[0];
            if (in_array($parent, $perms)) return true;
        }
        return false;
    }

    protected function requirePermission(string $permission): void
    {
        if (!$this->can($permission)) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound(
                'You do not have permission to access this page.'
            );
        }
    }

    // ── Audit log ────────────────────────────────────────────
    protected function audit(string $action, string $module, ?int $recordId = null, $oldData = null, $newData = null): void
    {
        $this->db->table('audit_logs')->insert([
            'user_id'    => $this->session->get('user_id'),
            'action'     => $action,
            'module'     => $module,
            'record_id'  => $recordId,
            'old_data'   => $oldData ? json_encode($oldData) : null,
            'new_data'   => $newData ? json_encode($newData) : null,
            'ip_address' => $this->request->getIPAddress(),
            'user_agent' => $this->request->getUserAgent()->getAgentString(),
            'created_at' => date('Y-m-d H:i:s'),
        ]);
    }

    // ── Invoice generation ────────────────────────────────────
    /**
     * Auto-generate invoices for all officials in a fixture.
     * Called whenever a fixture status changes to Completed.
     * Safe to call multiple times — skips officials who already have an invoice.
     */
    protected function generateFixtureInvoices(int $fixtureId): void
    {
        // Get fixture + match details for snapshot
        $fixture = $this->db->table('fixtures f')
            ->select('f.*, t.name as tournament_name, ta.name as team_a, tb.name as team_b, v.name as venue_name')
            ->join('tournaments t', 't.id = f.tournament_id')
            ->join('teams ta',      'ta.id = f.team_a_id')
            ->join('teams tb',      'tb.id = f.team_b_id')
            ->join('venues v',      'v.id = f.venue_id', 'left')
            ->where('f.id', $fixtureId)
            ->get()->getRowArray();

        if (!$fixture) return;

        // Get all officials assigned to this fixture
        $officials = $this->db->table('match_officials mo')
            ->select('mo.id as mo_id, mo.official_id, mo.PAmt, mo.official_type_id,
                      o.full_name, o.jsca_official_id, o.grade, o.phone, o.email,
                      o.address, o.bank_name, o.bank_account, o.bank_ifsc,
                      ot.name as type_name')
            ->join('officials o',      'o.id = mo.official_id')
            ->join('official_types ot','ot.id = mo.official_type_id')
            ->where('mo.match_id', $fixtureId)
            ->get()->getResultArray();

        foreach ($officials as $off) {
            // Skip if invoice already exists for this match_officials row
            $exists = $this->db->table('invoices')
                ->where('match_officials_id', $off['mo_id'])
                ->countAllResults();
            if ($exists) continue;

            // Skip if no fee set
            if (empty($off['PAmt']) || $off['PAmt'] <= 0) continue;

            $invoiceNumber = $this->generateInvoiceNumber($off['type_name']);

            $this->db->table('invoices')->insert([
                'invoice_number'     => $invoiceNumber,
                'fixture_id'         => $fixtureId,
                'match_officials_id' => $off['mo_id'],
                'official_id'        => $off['official_id'],
                // Snapshots
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
        }
    }

    private function generateInvoiceNumber(string $typeName): string
    {
        $prefixMap = [
            'Umpire'        => 'UMP',
            'Scorer'        => 'SCR',
            'Referee'       => 'REF',
            'Match Referee' => 'MRF',
        ];
        $prefix = $prefixMap[$typeName] ?? 'OFF';
        $year   = date('Y');
        $count  = $this->db->table('invoices')
            ->like('invoice_number', 'INV-' . $year . '-' . $prefix . '-', 'after')
            ->countAllResults() + 1;
        return 'INV-' . $year . '-' . $prefix . '-' . str_pad($count, 4, '0', STR_PAD_LEFT);
    }

    // ── File upload helper ────────────────────────────────────
    /**
     * Moves an uploaded file to public/assets/uploads/{folder}/
     * Returns the web-accessible path or null if no file.
     */
    protected function uploadFile(string $inputName, string $folder, array $allowed = ['jpg','jpeg','png','pdf'], int $maxSizeMB = 10): ?string
    {
        $file = $this->request->getFile($inputName);
        if (!$file || !$file->isValid() || $file->hasMoved()) return null;

        if ($file->getSize() > $maxSizeMB * 1024 * 1024) {
            throw new \RuntimeException('File too large. Maximum size is ' . $maxSizeMB . 'MB.');
        }

        $ext = strtolower($file->getClientExtension());
        if (!in_array($ext, $allowed)) {
            throw new \RuntimeException('Invalid file type. Allowed: ' . implode(', ', $allowed));
        }

        $dir = FCPATH . 'assets/uploads/' . $folder;
        if (!is_dir($dir)) mkdir($dir, 0775, true);

        // Build name manually — avoids getRandomName() calling getExtension() -> getMimeType() -> finfo_file()
        $name = time() . '_' . bin2hex(random_bytes(8)) . '.' . $ext;
        $file->move($dir, $name);

        return 'assets/uploads/' . $folder . '/' . $name;
    }

    // ── Generate unique IDs ──────────────────────────────────
    protected function generatePlayerId(): string
    {
        $year  = date('Y');
        $count = $this->db->table('players')->countAllResults() + 1;
        return 'JSCA-P-' . $year . '-' . str_pad($count, 5, '0', STR_PAD_LEFT);
    }

    protected function generateOfficialId(): string
    {
        $year  = date('Y');
        $count = $this->db->table('officials')->countAllResults() + 1;
        return 'JSCA-O-' . $year . '-' . str_pad($count, 4, '0', STR_PAD_LEFT);
    }

    protected function generateVoucherNumber(): string
    {
        $prefix = 'VCH-' . date('Ym') . '-';
        $count  = $this->db->table('vouchers')
            ->like('voucher_number', $prefix)
            ->countAllResults() + 1;
        return $prefix . str_pad($count, 4, '0', STR_PAD_LEFT);
    }
}
