<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?= esc($pageTitle ?? 'JSCA ERP') ?></title>
  <!-- Bootstrap 5 -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <!-- Bootstrap Icons -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css" rel="stylesheet">
  <!-- DataTables -->
  <link href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css" rel="stylesheet">
  <!-- Chart.js -->
  <style>
    :root {
      --jsca-primary: #1a3a5c;
      --jsca-green: #2ecc71;
      --jsca-accent: #e74c3c;
      --sidebar-width: 260px;
    }

    body {
      background: #f0f2f5;
      font-family: 'Segoe UI', sans-serif;
    }

    .sidebar {
      width: var(--sidebar-width);
      height: 100vh;
      position: fixed;
      background: var(--jsca-primary);
      z-index: 100;
      overflow-y: auto;
      scrollbar-width: thin;
      scrollbar-color: rgba(255,255,255,0.15) transparent;
    }

    .sidebar::-webkit-scrollbar {
      width: 4px;
    }

    .sidebar::-webkit-scrollbar-thumb {
      background: rgba(255,255,255,0.15);
      border-radius: 4px;
    }

    .sidebar .brand {
      padding: 20px 24px;
      background: rgba(0, 0, 0, 0.2);
      border-bottom: 1px solid rgba(255, 255, 255, 0.1);
    }

    .sidebar .brand .badge-erp {
      background: var(--jsca-green);
      color: #fff;
      font-size: 10px;
      padding: 2px 8px;
      border-radius: 4px;
      margin-left: 8px;
    }

    .sidebar .nav-link {
      color: rgba(255, 255, 255, 0.75);
      padding: 10px 24px;
      display: flex;
      align-items: center;
      gap: 10px;
      transition: all 0.2s;
      border-left: 3px solid transparent;
      font-size: 14px;
    }

    .sidebar .nav-link:hover,
    .sidebar .nav-link.active {
      color: #fff;
      background: rgba(255, 255, 255, 0.1);
      border-left-color: var(--jsca-green);
    }

    .sidebar .nav-section {
      font-size: 10px;
      font-weight: 700;
      letter-spacing: 0.12em;
      color: rgba(255, 255, 255, 0.4);
      padding: 16px 24px 6px;
      text-transform: uppercase;
    }

    .main-content {
      margin-left: var(--sidebar-width);
      min-height: 100vh;
    }

    .topbar {
      background: #fff;
      border-bottom: 1px solid #e0e0e0;
      padding: 12px 28px;
      position: sticky;
      top: 0;
      z-index: 50;
      display: flex;
      align-items: center;
      justify-content: space-between;
    }

    .topbar .page-title {
      font-size: 18px;
      font-weight: 700;
      color: var(--jsca-primary);
      margin: 0;
    }

    .page-body {
      padding: 28px;
    }

    .stat-card {
      background: #fff;
      border-radius: 12px;
      padding: 20px;
      border-left: 4px solid var(--jsca-green);
      box-shadow: 0 2px 8px rgba(0, 0, 0, 0.06);
    }

    .stat-card .stat-val {
      font-size: 32px;
      font-weight: 800;
      color: var(--jsca-primary);
    }

    .stat-card .stat-label {
      font-size: 12px;
      color: #888;
      text-transform: uppercase;
      letter-spacing: 0.06em;
    }

    .card {
      border: none;
      border-radius: 12px;
      box-shadow: 0 2px 8px rgba(0, 0, 0, 0.06);
    }

    .card-header {
      background: #fff;
      border-bottom: 1px solid #f0f0f0;
      font-weight: 700;
      font-size: 15px;
      padding: 16px 20px;
      border-radius: 12px 12px 0 0 !important;
    }

    .badge-status-scheduled {
      background: #e3f2fd;
      color: #1565c0;
    }

    .badge-status-live {
      background: #e8f5e9;
      color: #2e7d32;
    }

    .badge-status-completed {
      background: #f3e5f5;
      color: #6a1b9a;
    }

    .badge-status-pending {
      background: #fff3e0;
      color: #e65100;
    }

    .badge-status-approved {
      background: #e0f7fa;
      color: #006064;
    }

    .badge-status-paid {
      background: #e8f5e9;
      color: #1b5e20;
    }

    .badge-status-rejected {
      background: #ffebee;
      color: #b71c1c;
    }

    .btn-jsca-primary {
      background: var(--jsca-primary);
      color: #fff;
      border: none;
    }

    .btn-jsca-primary:hover {
      background: #16324e;
      color: #fff;
    }

    .btn-jsca-green {
      background: var(--jsca-green);
      color: #fff;
      border: none;
    }

    .btn-jsca-green:hover {
      background: #27ae60;
      color: #fff;
    }

    .alert {
      border: none;
      border-radius: 8px;
    }

    .table> :not(caption)>*>* {
      padding: 12px 16px;
    }

    .table thead th {
      background: #f8f9fa;
      font-size: 11px;
      font-weight: 700;
      letter-spacing: 0.08em;
      text-transform: uppercase;
      color: #666;
    }

    .avatar-circle {
      width: 36px;
      height: 36px;
      border-radius: 50%;
      background: var(--jsca-primary);
      color: #fff;
      display: inline-flex;
      align-items: center;
      justify-content: center;
      font-weight: 700;
      font-size: 13px;
    }

    @media (max-width: 992px) {
      .sidebar {
        transform: translateX(-100%);
        transition: transform 0.3s;
      }

      .sidebar.open {
        transform: translateX(0);
      }

      .main-content {
        margin-left: 0;
      }
    }
  </style>
</head>

<body>

  <!-- ─── SIDEBAR ─── -->
  <div class="sidebar" id="sidebar">
    <div class="brand">
      <div class="d-flex align-items-center">
        <span class="fs-5 fw-bold text-white">🏏 JSCA</span>
        <span class="badge-erp">ERP</span>
      </div>
      <div class="text-white-50 mt-1" style="font-size:11px;">Solutioning ERP Solution</div>
    </div>

 <div class="pt-2">
  <div class="nav-section">Main</div>
  <a href="<?= base_url('dashboard') ?>" class="nav-link <?= uri_string() === 'dashboard' ? 'active' : '' ?>">
    <i class="bi bi-speedometer2"></i> Dashboard
  </a>

  <div class="nav-section">Players</div>
  <a href="<?= base_url('players') ?>" class="nav-link <?= str_starts_with(uri_string(), 'players') ? 'active' : '' ?>">
    <i class="bi bi-people"></i> Player Registry
    <?php
      $pendingPlayers = \Config\Database::connect()
        ->table('players')
        ->where('status', 'Inactive')
        ->where('registration_type', 'self')
        ->countAllResults();
      if ($pendingPlayers > 0): ?>
        <span class="badge bg-warning text-dark ms-auto"><?= $pendingPlayers ?></span>
    <?php endif; ?>
  </a>
  <a href="<?= base_url('players/create') ?>" class="nav-link <?= uri_string() === 'players/create' ? 'active' : '' ?>">
    <i class="bi bi-person-plus"></i> Register Player
  </a>

  <div class="nav-section">Coaches</div>
  <a href="<?= base_url('coaches') ?>" class="nav-link <?= str_starts_with(uri_string(), 'coaches') ? 'active' : '' ?>">
    <i class="bi bi-person-video3"></i> Coach Registry
  </a>
  <a href="<?= base_url('coaches/create') ?>" class="nav-link <?= uri_string() === 'coaches/create' ? 'active' : '' ?>">
    <i class="bi bi-person-plus"></i> Register Coach
  </a>

  <div class="nav-section">Venues</div>
  <a href="<?= base_url('venues') ?>" class="nav-link <?= str_starts_with(uri_string(), 'venues') ? 'active' : '' ?>">
    <i class="bi bi-building"></i> Venues
  </a>

  <div class="nav-section">Teams</div>
  <a href="<?= base_url('teams') ?>" class="nav-link <?= str_starts_with(uri_string(), 'teams') ? 'active' : '' ?>">
    <i class="bi bi-shield-fill"></i> Team Registry
  </a>
  <a href="<?= base_url('teams/create') ?>" class="nav-link <?= uri_string() === 'teams/create' ? 'active' : '' ?>">
    <i class="bi bi-plus-circle"></i> Create Team
  </a>

  <div class="nav-section">Tournaments</div>
  <a href="<?= base_url('tournaments') ?>" class="nav-link <?= str_starts_with(uri_string(), 'tournaments') ? 'active' : '' ?>">
    <i class="bi bi-trophy"></i> Tournaments
  </a>
  <a href="<?= base_url('tournaments/create') ?>" class="nav-link <?= uri_string() === 'tournaments/create' ? 'active' : '' ?>">
    <i class="bi bi-plus-circle"></i> Create Tournament
  </a>
  <a href="<?= base_url('fixtures') ?>" class="nav-link <?= str_starts_with(uri_string(), 'fixtures') ? 'active' : '' ?>">
    <i class="bi bi-calendar3"></i> Fixtures
  </a>
  <a href="<?= base_url('matches/live') ?>" class="nav-link <?= str_starts_with(uri_string(), 'matches/live') ? 'active' : '' ?>">
    <i class="bi bi-lightning-charge text-warning"></i> Live Matches
  </a>

  <div class="nav-section">Officials</div>
  <a href="<?= base_url('officials') ?>" class="nav-link <?= str_starts_with(uri_string(), 'officials') ? 'active' : '' ?>">
    <i class="bi bi-patch-check"></i> Officials
  </a>
  <a href="<?= base_url('officials/create') ?>" class="nav-link <?= uri_string() === 'officials/create' ? 'active' : '' ?>">
    <i class="bi bi-person-plus"></i> Add Official
  </a>

  <div class="nav-section">Finance</div>
  <a href="<?= base_url('finance') ?>" class="nav-link <?= str_starts_with(uri_string(), 'finance') ? 'active' : '' ?>">
    <i class="bi bi-currency-rupee"></i> Finance Dashboard
  </a>
  <a href="<?= base_url('finance/vouchers') ?>" class="nav-link <?= str_starts_with(uri_string(), 'finance/vouchers') ? 'active' : '' ?>">
    <i class="bi bi-receipt"></i> Payment Vouchers
    <?php
      $pendingCount = \Config\Database::connect()
                        ->table('payment_vouchers')
                        ->where('status', 'Pending Approval')
                        ->countAllResults();
      if ($pendingCount > 0): ?>
        <span class="badge bg-danger ms-auto"><?= $pendingCount ?></span>
    <?php endif; ?>
  </a>

  <div class="nav-section">Intelligence</div>
  <a href="<?= base_url('analytics') ?>" class="nav-link <?= str_starts_with(uri_string(), 'analytics') ? 'active' : '' ?>">
    <i class="bi bi-bar-chart-line"></i> Analytics
  </a>
  <a href="<?= base_url('reports') ?>" class="nav-link <?= str_starts_with(uri_string(), 'reports') ? 'active' : '' ?>">
    <i class="bi bi-file-earmark-text"></i> Reports
  </a>

  <?php if (in_array($currentUser['role_name'] ?? '', ['superadmin', 'admin'])): ?>
    <div class="nav-section">Administration</div>
    <a href="<?= base_url('admin/users') ?>" class="nav-link <?= str_starts_with(uri_string(), 'admin/users') ? 'active' : '' ?>">
      <i class="bi bi-shield-check"></i> Users & Access
    </a>
    <a href="<?= base_url('admin/audit-log') ?>" class="nav-link <?= str_starts_with(uri_string(), 'admin/audit-log') ? 'active' : '' ?>">
      <i class="bi bi-journal-text"></i> Audit Log
    </a>
  <?php endif; ?>
</div>
  </div>

  <!-- ─── MAIN CONTENT ─── -->
  <div class="main-content">

    <!-- Topbar -->
    <div class="topbar">
      <div class="d-flex align-items-center gap-3">
        <button class="btn btn-sm d-lg-none" onclick="document.getElementById('sidebar').classList.toggle('open')">
          <i class="bi bi-list fs-5"></i>
        </button>
        <h1 class="page-title"><?= esc($pageTitle ?? 'JSCA ERP') ?></h1>
      </div>
      <div class="d-flex align-items-center gap-3">
        <?php if (!empty($currentUser)): ?>
          <span class="badge" style="background:var(--jsca-primary);font-size:11px;"><?= esc(strtoupper($currentUser['role_name'])) ?></span>
          <div class="dropdown">
            <button class="btn btn-sm btn-light dropdown-toggle d-flex align-items-center gap-2" data-bs-toggle="dropdown">
              <div class="avatar-circle"><?= strtoupper(substr($currentUser['full_name'], 0, 1)) ?></div>
              <span style="font-size:13px;"><?= esc($currentUser['full_name']) ?></span>
            </button>
            <ul class="dropdown-menu dropdown-menu-end shadow">
              <li><a class="dropdown-item" href="<?= base_url('profile') ?>"><i class="bi bi-person me-2"></i>My Profile</a></li>
              <li>
                <hr class="dropdown-divider">
              </li>
              <li><a class="dropdown-item text-danger" href="<?= base_url('logout') ?>"><i class="bi bi-box-arrow-right me-2"></i>Logout</a></li>
            </ul>
          </div>
        <?php endif; ?>
      </div>
    </div>

    <!-- Flash Messages -->
    <div class="px-4 pt-3">
      <?php if (session()->getFlashdata('success')): ?>
        <div class="alert alert-success alert-dismissible fade show d-flex align-items-center gap-2">
          <i class="bi bi-check-circle-fill"></i>
          <?= session()->getFlashdata('success') ?>
          <button type="button" class="btn-close ms-auto" data-bs-dismiss="alert"></button>
        </div>
      <?php endif; ?>
      <?php if (session()->getFlashdata('error')): ?>
        <div class="alert alert-danger alert-dismissible fade show d-flex align-items-center gap-2">
          <i class="bi bi-exclamation-circle-fill"></i>
          <?= session()->getFlashdata('error') ?>
          <button type="button" class="btn-close ms-auto" data-bs-dismiss="alert"></button>
        </div>
      <?php endif; ?>
      <?php if ($errors = session()->getFlashdata('errors')): ?>
        <div class="alert alert-danger alert-dismissible fade show">
          <strong><i class="bi bi-exclamation-triangle me-2"></i>Please fix the following errors:</strong>
          <ul class="mb-0 mt-2">
            <?php foreach ((array)$errors as $err): ?>
              <li><?= esc($err) ?></li>
            <?php endforeach; ?>
          </ul>
          <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
      <?php endif; ?>
    </div>

    <!-- Page Content -->
    <div class="page-body">
      <?= $content ?>
    </div>

  </div><!-- end main-content -->

  <!-- Scripts -->
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
  <script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
  <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
  <script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
  <script>
    // Init DataTables
    document.querySelectorAll('.data-table').forEach(el => {
      $(el).DataTable({
        pageLength: 25,
        order: []
      });
    });

    // CSRF token for AJAX
    const CSRF_TOKEN = '<?= csrf_hash() ?>';
    const CSRF_NAME = '<?= csrf_token() ?>';

    function csrfData() {
      return {
        [CSRF_NAME]: CSRF_TOKEN
      };
    }
  </script>
  <?= $this->renderSection('scripts') ?>
</body>

</html>