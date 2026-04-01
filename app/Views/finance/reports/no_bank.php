<?php // app/Views/finance/reports/no_bank.php ?>

<div class="d-flex justify-content-between align-items-center mb-4">
  <div>
    <a href="<?= base_url('finance/reports') ?>" class="text-muted small"><i class="bi bi-arrow-left me-1"></i>Finance Reports</a>
    <h4 class="mb-0 fw-bold mt-1">Officials Without Bank Details</h4>
  </div>
  <span class="badge bg-danger fs-6"><?= count($officials) ?> official<?= count($officials) != 1 ? 's' : '' ?></span>
</div>

<?php if (empty($officials)): ?>
  <div class="card"><div class="card-body text-center text-muted py-4">
    <i class="bi bi-check-circle text-success fs-1 d-block mb-2"></i>
    All active officials have bank details on record.
  </div></div>
<?php else: ?>
  <?php
    $withPending = array_filter($officials, fn($o) => $o['pending_invoices'] > 0);
    $withoutPending = array_filter($officials, fn($o) => $o['pending_invoices'] == 0);
  ?>

  <?php if (!empty($withPending)): ?>
  <div class="alert alert-danger mb-3">
    <i class="bi bi-exclamation-triangle-fill me-2"></i>
    <strong><?= count($withPending) ?> official<?= count($withPending) != 1 ? 's' : '' ?></strong> have pending invoices but no bank details — they cannot be paid until bank info is added.
  </div>
  <?php endif; ?>

  <div class="card">
    <div class="card-body p-0">
      <table class="table table-hover mb-0 data-table">
        <thead>
          <tr>
            <th>Official</th>
            <th>Type</th>
            <th>Grade</th>
            <th>District</th>
            <th>Phone</th>
            <th>Pending Invoices</th>
            <th>Pending Amount</th>
            <th></th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($officials as $off): ?>
          <tr class="<?= $off['pending_invoices'] > 0 ? 'table-danger' : '' ?>">
            <td>
              <div class="fw-semibold"><?= esc($off['full_name']) ?></div>
              <div class="small text-muted"><?= esc($off['jsca_official_id']) ?></div>
            </td>
            <td><span class="badge bg-secondary"><?= esc($off['type_name']) ?></span></td>
            <td class="small"><?= esc($off['grade'] ?? '—') ?></td>
            <td class="small"><?= esc($off['district_name']) ?></td>
            <td class="small"><?= esc($off['phone'] ?? '—') ?></td>
            <td class="text-center">
              <?php if ($off['pending_invoices'] > 0): ?>
                <span class="badge bg-danger"><?= $off['pending_invoices'] ?></span>
              <?php else: ?>
                <span class="text-muted">—</span>
              <?php endif; ?>
            </td>
            <td class="fw-semibold <?= $off['pending_amount'] > 0 ? 'text-danger' : 'text-muted' ?>">
              <?= $off['pending_amount'] > 0 ? '₹' . number_format($off['pending_amount'], 2) : '—' ?>
            </td>
            <td>
              <a href="<?= base_url('officials/edit/' . $off['id']) ?>" class="btn btn-sm btn-outline-primary">
                <i class="bi bi-pencil me-1"></i> Add Bank Details
              </a>
            </td>
          </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </div>
<?php endif; ?>
