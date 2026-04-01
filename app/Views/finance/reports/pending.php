<?php // app/Views/finance/reports/pending.php ?>

<div class="d-flex justify-content-between align-items-center mb-4">
  <div>
    <a href="<?= base_url('finance/reports') ?>" class="text-muted small"><i class="bi bi-arrow-left me-1"></i>Finance Reports</a>
    <h4 class="mb-0 fw-bold mt-1">Pending Payments</h4>
  </div>
  <div class="fw-bold text-danger fs-5">Total Pending: ₹<?= number_format($grandTotal, 2) ?></div>
</div>

<?php if (empty($officials)): ?>
  <div class="card"><div class="card-body text-center text-muted py-4">
    <i class="bi bi-check-circle text-success fs-1 d-block mb-2"></i>
    All invoices are paid. Nothing pending.
  </div></div>
<?php else: ?>
  <?php foreach ($officials as $off): ?>
  <div class="card mb-3">
    <div class="card-header d-flex justify-content-between align-items-center">
      <div>
        <span class="fw-bold"><?= esc($off['snap_name']) ?></span>
        <span class="badge bg-secondary ms-2"><?= esc($off['snap_type']) ?></span>
        <?php if ($off['snap_grade']): ?>
          <span class="badge bg-light text-dark border ms-1"><?= esc($off['snap_grade']) ?></span>
        <?php endif; ?>
        <code class="ms-2 small"><?= esc($off['snap_jsca_id']) ?></code>
      </div>
      <div class="text-end">
        <div class="fw-bold text-danger">₹<?= number_format($off['total_pending'], 2) ?></div>
        <div class="small text-muted"><?= $off['match_count'] ?> match<?= $off['match_count'] != 1 ? 'es' : '' ?></div>
      </div>
    </div>
    <div class="card-body p-0">
      <!-- Bank details -->
      <div class="px-3 py-2 border-bottom bg-light d-flex gap-4 small">
        <?php if ($off['snap_bank_account']): ?>
          <span><i class="bi bi-bank me-1 text-muted"></i><?= esc($off['snap_bank_name'] ?? '—') ?></span>
          <span><i class="bi bi-credit-card me-1 text-muted"></i><?= esc($off['snap_bank_account']) ?></span>
          <span><i class="bi bi-hash me-1 text-muted"></i><?= esc($off['snap_bank_ifsc'] ?? '—') ?></span>
        <?php else: ?>
          <span class="text-danger"><i class="bi bi-exclamation-triangle me-1"></i>No bank details on record</span>
        <?php endif; ?>
        <?php if ($off['snap_phone']): ?>
          <span><i class="bi bi-phone me-1 text-muted"></i><?= esc($off['snap_phone']) ?></span>
        <?php endif; ?>
      </div>
      <!-- Invoices -->
      <table class="table table-sm mb-0">
        <thead>
          <tr><th>Invoice</th><th>Tournament</th><th>Match</th><th>Date</th><th>Amount</th><th></th></tr>
        </thead>
        <tbody>
          <?php foreach ($off['invoices'] as $inv): ?>
          <tr>
            <td><code><?= esc($inv['invoice_number']) ?></code></td>
            <td class="small"><?= esc($inv['snap_tournament']) ?></td>
            <td class="small"><?= esc($inv['snap_match_number']) ?> · <?= esc($inv['snap_teams']) ?></td>
            <td class="small"><?= date('d M Y', strtotime($inv['snap_match_date'])) ?></td>
            <td class="fw-semibold">₹<?= number_format($inv['amount'], 2) ?></td>
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
    </div>
    <!-- Mark all paid for this official -->
    <div class="card-footer bg-white d-flex justify-content-end">
      <form method="post" action="<?= base_url('finance/reports/mark-paid') ?>"
        onsubmit="return confirm('Mark all <?= $off['match_count'] ?> invoice(s) for <?= esc($off['snap_name']) ?> as paid?')">
        <?= csrf_field() ?>
        <?php foreach ($off['invoices'] as $inv): ?>
          <input type="hidden" name="invoice_ids[]" value="<?= $inv['id'] ?>">
        <?php endforeach; ?>
        <button type="submit" class="btn btn-sm btn-jsca-green">
          <i class="bi bi-check-circle me-1"></i> Mark All Paid — ₹<?= number_format($off['total_pending'], 2) ?>
        </button>
      </form>
    </div>
  </div>
  <?php endforeach; ?>
<?php endif; ?>
