<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?= esc($pageTitle ?? 'JSCA Official Portal') ?></title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css" rel="stylesheet">
  <style>
    :root { --jsca-primary: #1a3a5c; --jsca-green: #2ecc71; }
    body { background: #f0f2f5; font-family: 'Segoe UI', sans-serif; }

    .official-sidebar {
      width: 240px; height: 100vh; position: fixed;
      background: var(--jsca-primary); z-index: 100; overflow-y: auto;
    }
    .official-sidebar .brand {
      padding: 20px; background: rgba(0,0,0,0.2);
      border-bottom: 1px solid rgba(255,255,255,0.1);
    }
    .official-sidebar .nav-link {
      color: rgba(255,255,255,0.75); padding: 10px 20px;
      display: flex; align-items: center; gap: 10px;
      transition: all 0.2s; border-left: 3px solid transparent; font-size: 14px;
    }
    .official-sidebar .nav-link:hover,
    .official-sidebar .nav-link.active {
      color: #fff; background: rgba(255,255,255,0.1);
      border-left-color: var(--jsca-green);
    }
    .official-sidebar .nav-section {
      font-size: 10px; font-weight: 700; letter-spacing: 0.12em;
      color: rgba(255,255,255,0.4); padding: 14px 20px 4px;
      text-transform: uppercase;
    }
    .main-content { margin-left: 240px; min-height: 100vh; }
    .topbar {
      background: #fff; border-bottom: 1px solid #e0e0e0;
      padding: 12px 24px; position: sticky; top: 0; z-index: 50;
      display: flex; align-items: center; justify-content: space-between;
    }
    .page-body { padding: 24px; }
    .stat-card {
      background: #fff; border-radius: 12px; padding: 20px;
      border-left: 4px solid var(--jsca-green);
      box-shadow: 0 2px 8px rgba(0,0,0,0.06);
    }
    .card { border: none; border-radius: 12px; box-shadow: 0 2px 8px rgba(0,0,0,0.06); }
    .card-header { background: #fff; border-bottom: 1px solid #f0f0f0; font-weight: 700; font-size: 15px; padding: 14px 18px; border-radius: 12px 12px 0 0 !important; }
  </style>
</head>
<body>

<!-- Sidebar -->
<div class="official-sidebar">
  <div class="brand">
    <div class="text-white fw-bold fs-5">🏏 JSCA</div>
    <div class="text-white-50 small mt-1">Official Portal</div>
  </div>
  <div class="pt-2">
    <div class="nav-section">Main</div>
    <a href="<?= base_url('official/dashboard') ?>" class="nav-link <?= str_starts_with(uri_string(), 'official/dashboard') ? 'active' : '' ?>">
      <i class="bi bi-speedometer2"></i> Dashboard
    </a>
    <a href="<?= base_url('official/profile') ?>" class="nav-link <?= str_starts_with(uri_string(), 'official/profile') ? 'active' : '' ?>">
      <i class="bi bi-person-circle"></i> My Profile
    </a>

    <div class="nav-section">Scoring</div>
    <?php if (in_array($currentUser['role_name'] ?? '', ['scorer'])): ?>
    <a href="#" class="nav-link text-white-50" style="cursor:not-allowed;" title="Coming soon">
      <i class="bi bi-broadcast"></i> Live Scoring
      <span class="badge bg-secondary ms-auto" style="font-size:9px;">Soon</span>
    </a>
    <?php else: ?>
    <a href="#" class="nav-link text-white-50" style="cursor:not-allowed;" title="Not applicable for your role">
      <i class="bi bi-broadcast"></i> Live Scoring
      <span class="badge bg-light text-muted ms-auto" style="font-size:9px;">N/A</span>
    </a>
    <?php endif; ?>
  </div>
</div>

<!-- Main Content -->
<div class="main-content">
  <div class="topbar">
    <h1 style="font-size:17px;font-weight:700;color:var(--jsca-primary);margin:0;">
      <?= esc($pageTitle ?? 'JSCA Official Portal') ?>
    </h1>
    <div class="d-flex align-items-center gap-3">
      <span class="badge" style="background:var(--jsca-primary);font-size:11px;">
        <?= esc(strtoupper($currentUser['role_name'] ?? '')) ?>
      </span>
      <div class="dropdown">
        <button class="btn btn-sm btn-light dropdown-toggle" data-bs-toggle="dropdown">
          <i class="bi bi-person-circle me-1"></i><?= esc($currentUser['full_name'] ?? '') ?>
        </button>
        <ul class="dropdown-menu dropdown-menu-end shadow">
          <li><a class="dropdown-item" href="<?= base_url('official/profile') ?>"><i class="bi bi-person me-2"></i>My Profile</a></li>
          <li><hr class="dropdown-divider"></li>
          <li><a class="dropdown-item text-danger" href="<?= base_url('logout') ?>"><i class="bi bi-box-arrow-right me-2"></i>Logout</a></li>
        </ul>
      </div>
    </div>
  </div>

  <!-- Flash messages -->
  <div class="px-4 pt-3">
    <?php if ($msg = session()->getFlashdata('success')): ?>
      <div class="alert alert-success alert-dismissible fade show">
        <i class="bi bi-check-circle-fill me-2"></i><?= esc($msg) ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
      </div>
    <?php endif; ?>
    <?php if ($msg = session()->getFlashdata('error')): ?>
      <div class="alert alert-danger alert-dismissible fade show">
        <i class="bi bi-exclamation-circle-fill me-2"></i><?= esc($msg) ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
      </div>
    <?php endif; ?>
  </div>

  <div class="page-body"><?= $content ?></div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
