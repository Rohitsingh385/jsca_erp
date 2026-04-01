<?php // app/Views/finance/reports/index.php ?>

<div class="d-flex justify-content-between align-items-center mb-4">
  <h4 class="mb-0 fw-bold">Finance Reports</h4>
</div>

<!-- Summary Cards -->
<div class="row g-3 mb-4">
  <div class="col-sm-6 col-xl-3">
    <div class="stat-card" style="border-left-color:#e74c3c;">
      <div class="stat-val text-danger">₹<?= number_format($amountPending, 2) ?></div>
      <div class="stat-label">Pending Payment</div>
      <div class="mt-1 small text-muted"><?= $totalGenerated ?> invoice<?= $totalGenerated != 1 ? 's' : '' ?></div>
    </div>
  </div>
  <div class="col-sm-6 col-xl-3">
    <div class="stat-card" style="border-left-color:#2ecc71;">
      <div class="stat-val text-success">₹<?= number_format($amountPaid, 2) ?></div>
      <div class="stat-label">Total Paid</div>
      <div class="mt-1 small text-muted"><?= $totalPaid ?> invoice<?= $totalPaid != 1 ? 's' : '' ?></div>
    </div>
  </div>
  <div class="col-sm-6 col-xl-3">
    <div class="stat-card" style="border-left-color:#f39c12;">
      <div class="stat-val" style="color:#f39c12;">₹<?= number_format($amountPending + $amountPaid, 2) ?></div>
      <div class="stat-label">Total Invoiced</div>
      <div class="mt-1 small text-muted"><?= $totalGenerated + $totalPaid ?> total invoices</div>
    </div>
  </div>
  <div class="col-sm-6 col-xl-3">
    <div class="stat-card" style="border-left-color:#e74c3c;">
      <div class="stat-val text-danger"><?= $noBank ?></div>
      <div class="stat-label">No Bank Details</div>
      <div class="mt-1 small text-muted">Officials who can't be paid</div>
    </div>
  </div>
</div>

<!-- Report Links -->
<div class="row g-3">
  <div class="col-md-4">
    <a href="<?= base_url('finance/reports/invoices') ?>" class="card text-decoration-none h-100">
      <div class="card-body d-flex align-items-center gap-3">
        <div style="width:48px;height:48px;background:#e8f4fd;border-radius:10px;display:flex;align-items:center;justify-content:center;">
          <i class="bi bi-receipt fs-4 text-primary"></i>
        </div>
        <div>
          <div class="fw-bold text-dark">Invoice Summary</div>
          <div class="small text-muted">All invoices with filter by status, tournament, type</div>
        </div>
        <i class="bi bi-chevron-right ms-auto text-muted"></i>
      </div>
    </a>
  </div>
  <div class="col-md-4">
    <a href="<?= base_url('finance/reports/pending') ?>" class="card text-decoration-none h-100">
      <div class="card-body d-flex align-items-center gap-3">
        <div style="width:48px;height:48px;background:#fef9e7;border-radius:10px;display:flex;align-items:center;justify-content:center;">
          <i class="bi bi-hourglass-split fs-4 text-warning"></i>
        </div>
        <div>
          <div class="fw-bold text-dark">Pending Payments</div>
          <div class="small text-muted">Unpaid invoices grouped by official</div>
        </div>
        <i class="bi bi-chevron-right ms-auto text-muted"></i>
      </div>
    </a>
  </div>
  <div class="col-md-4">
    <a href="<?= base_url('finance/reports/no-bank') ?>" class="card text-decoration-none h-100">
      <div class="card-body d-flex align-items-center gap-3">
        <div style="width:48px;height:48px;background:#fdf2f8;border-radius:10px;display:flex;align-items:center;justify-content:center;">
          <i class="bi bi-bank fs-4 text-danger"></i>
        </div>
        <div>
          <div class="fw-bold text-dark">No Bank Details</div>
          <div class="small text-muted">Officials missing bank info</div>
        </div>
        <i class="bi bi-chevron-right ms-auto text-muted"></i>
      </div>
    </a>
  </div>
</div>
