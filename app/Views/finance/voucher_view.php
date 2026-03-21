<div class="card">
  <div class="card-header">
    <i class="bi bi-file-earmark-text me-2"></i>
    <?= $voucher['voucher_number'] ?>
  </div>

  <div class="card-body">

    <div class="row mb-3">
      <div class="col-md-6">
        <div><b>Payee:</b> <?= $voucher['payee_name'] ?></div>
        <div><b>Amount:</b> ₹<?= number_format($voucher['amount']) ?></div>
      </div>

      <div class="col-md-6">
        <div><b>Status:</b> <?= $voucher['status'] ?></div>
        <div><b>Mode:</b> <?= $voucher['payment_mode'] ?></div>
      </div>
    </div>

    <?php if ($voucher['status']=='Pending Approval'): ?>
      <form method="post" action="/finance/voucher/approve/<?= $voucher['id'] ?>">
        <button class="btn btn-success btn-sm">Approve</button>
      </form>
    <?php endif; ?>

  </div>
</div>