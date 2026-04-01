<?php // app/Views/finance/reports/invoices.php ?>

<div class="d-flex justify-content-between align-items-center mb-4">
  <div>
    <a href="<?= base_url('finance/reports') ?>" class="text-muted small"><i class="bi bi-arrow-left me-1"></i>Finance Reports</a>
    <h4 class="mb-0 fw-bold mt-1">Invoice Summary</h4>
  </div>
</div>

<!-- Filters -->
<div class="card mb-4">
  <div class="card-body">
    <form method="get" class="row g-3">
      <div class="col-md-3">
        <label class="form-label small">Status</label>
        <select name="status" class="form-select form-select-sm">
          <option value="">— All —</option>
          <option value="Generated" <?= $status === 'Generated' ? 'selected' : '' ?>>Pending</option>
          <option value="Paid"      <?= $status === 'Paid'      ? 'selected' : '' ?>>Paid</option>
        </select>
      </div>
      <div class="col-md-3">
        <label class="form-label small">Tournament</label>
        <select name="tournament" class="form-select form-select-sm">
          <option value="">— All —</option>
          <?php foreach ($tournaments as $t): ?>
            <option value="<?= esc($t['snap_tournament']) ?>" <?= $tournament === $t['snap_tournament'] ? 'selected' : '' ?>>
              <?= esc($t['snap_tournament']) ?>
            </option>
          <?php endforeach; ?>
        </select>
      </div>
      <div class="col-md-3">
        <label class="form-label small">Official Type</label>
        <select name="type" class="form-select form-select-sm">
          <option value="">— All —</option>
          <?php foreach (['Umpire','Scorer','Referee','Match Referee'] as $t): ?>
            <option value="<?= $t ?>" <?= $type === $t ? 'selected' : '' ?>><?= $t ?></option>
          <?php endforeach; ?>
        </select>
      </div>
      <div class="col-md-3 d-flex align-items-end gap-2">
        <button type="submit" class="btn btn-sm btn-secondary">Filter</button>
        <a href="<?= base_url('finance/reports/invoices') ?>" class="btn btn-sm btn-outline-secondary">Clear</a>
      </div>
    </form>
  </div>
</div>

<div class="card">
  <div class="card-header d-flex justify-content-between align-items-center">
    <span><?= count($invoices) ?> invoice<?= count($invoices) != 1 ? 's' : '' ?></span>
    <span class="fw-bold">Total: ₹<?= number_format($totalAmount, 2) ?></span>
  </div>
  <div class="card-body p-0">
    <?php if (empty($invoices)): ?>
      <div class="text-center text-muted py-4">No invoices found.</div>
    <?php else: ?>
    <table class="table table-hover mb-0 data-table">
      <thead>
        <tr>
          <th>#</th>
          <th>Invoice No.</th>
          <th>Official</th>
          <th>Type</th>
          <th>Tournament</th>
          <th>Match</th>
          <th>Date</th>
          <th>Amount</th>
          <th>Status</th>
          <th></th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($invoices as $i => $inv): ?>
        <tr>
          <td class="text-muted small"><?= $i + 1 ?></td>
          <td><code><?= esc($inv['invoice_number']) ?></code></td>
          <td>
            <div><?= esc($inv['snap_name']) ?></div>
            <div class="small text-muted"><?= esc($inv['snap_jsca_id']) ?></div>
          </td>
          <td><span class="badge bg-secondary"><?= esc($inv['snap_type']) ?></span></td>
          <td class="small"><?= esc($inv['snap_tournament']) ?></td>
          <td class="small">
            <?= esc($inv['snap_match_number']) ?><br>
            <span class="text-muted"><?= esc($inv['snap_teams']) ?></span>
          </td>
          <td class="small"><?= date('d M Y', strtotime($inv['snap_match_date'])) ?></td>
          <td class="fw-semibold">₹<?= number_format($inv['amount'], 2) ?></td>
          <td>
            <?php if ($inv['status'] === 'Paid'): ?>
              <span class="badge bg-success">Paid</span>
              <?php if ($inv['paid_at']): ?>
                <div class="small text-muted"><?= date('d M Y', strtotime($inv['paid_at'])) ?></div>
              <?php endif; ?>
            <?php else: ?>
              <span class="badge bg-warning text-dark">Pending</span>
            <?php endif; ?>
          </td>
          <td>
            <a href="<?= base_url('official/invoice/' . $inv['id']) ?>" target="_blank"
              class="btn btn-xs btn-outline-secondary" style="font-size:11px;padding:2px 8px;">
              <i class="bi bi-eye"></i>
            </a>
          </td>
        </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
    <?php endif; ?>
  </div>
</div>
