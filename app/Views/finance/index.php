<div class="row g-3 mb-4">

  <div class="col-sm-6 col-xl-3">
    <div class="stat-card" style="border-left-color:#2ecc71;">
      <div class="stat-val">₹<?= number_format($summary['total_paid']) ?></div>
      <div class="stat-label">Total Paid</div>
    </div>
  </div>

  <div class="col-sm-6 col-xl-3">
    <div class="stat-card" style="border-left-color:#f39c12;">
      <div class="stat-val">₹<?= number_format($summary['total_pending']) ?></div>
      <div class="stat-label">Pending Approval</div>
    </div>
  </div>

  <div class="col-sm-6 col-xl-3">
    <div class="stat-card" style="border-left-color:#3498db;">
      <div class="stat-val">₹<?= number_format($summary['total_approved']) ?></div>
      <div class="stat-label">Approved</div>
    </div>
  </div>

  <div class="col-sm-6 col-xl-3">
    <div class="stat-card" style="border-left-color:#e74c3c;">
      <div class="stat-val">₹<?= number_format($summary['total_rejected']) ?></div>
      <div class="stat-label">Rejected</div>
    </div>
  </div>

</div>

<div class="row g-3 mb-3">

  <div class="col-md-3">
    <a href="/finance/accgroups" style="text-decoration:none;">
      <div class="stat-card" style="border-left-color:#1a3a5c;">
        <div class="stat-val" style="font-size:16px;">
          <i class="bi bi-diagram-3 me-2"></i>Group Master
        </div>
        <div class="stat-label">Manage Account Groups</div>
      </div>
    </a>
  </div>

  <div class="col-md-3">
    <a href="/finance/ledger-heads" style="text-decoration:none;">
      <div class="stat-card" style="border-left-color:#2ecc71;">
        <div class="stat-val" style="font-size:16px;">
          <i class="bi bi-journal-text me-2"></i>Ledger Heads
        </div>
        <div class="stat-label">Manage Ledger Accounts</div>
      </div>
    </a>
  </div>

</div>

<div class="row g-3">

  <!-- Recent Vouchers -->
  <div class="col-xl-8">
    <div class="card">
      <div class="card-header">
        <i class="bi bi-receipt me-2 text-primary"></i>Recent Vouchers
      </div>

      <div class="table-responsive">
        <table class="table mb-0">
          <thead>
            <tr>
              <th>No</th>
              <th>Payee</th>
              <th>Amount</th>
              <th>Status</th>
              <th></th>
            </tr>
          </thead>

          <tbody>
            <?php foreach ($recentVouchers ?? [] as $v): ?>
            <tr>
              <td><?= $v['voucher_number'] ?></td>
              <td><?= $v['payee_name'] ?></td>
              <td>₹<?= number_format($v['amount']) ?></td>
              <td>
                <span class="badge badge-status bg-light text-dark">
                  <?= $v['status'] ?>
                </span>
              </td>
              <td>
                <a href="/finance/voucher/view/<?= $v['id'] ?>" class="btn btn-xs btn-outline-primary">View</a>
              </td>
            </tr>
            <?php endforeach; ?>
          </tbody>

        </table>
      </div>
    </div>
  </div>

  <!-- Pending Approvals -->
  <div class="col-xl-4">
    <div class="card">
      <div class="card-header">
        <i class="bi bi-exclamation-circle text-danger me-2"></i>Pending Approvals
      </div>

      <?php foreach ($pendingList ?? [] as $v): ?>
      <div class="d-flex justify-content-between px-3 py-2 border-bottom">
        <div>
          <div style="font-size:13px;" class="fw-semibold"><?= $v['voucher_number'] ?></div>
          <div style="font-size:11px;color:#888;"><?= $v['payee_name'] ?></div>
        </div>
        <div class="text-end">
          <div class="fw-bold">₹<?= number_format($v['amount']) ?></div>
          <a href="/finance/voucher/view/<?= $v['id'] ?>" class="btn btn-xs btn-outline-success">Review</a>
        </div>
      </div>
      <?php endforeach; ?>

    </div>
  </div>

</div>