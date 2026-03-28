<div class="card">
  <div class="card-header d-flex justify-content-between">
    <span><i class="bi bi-receipt me-2"></i>Receipt Vouchers</span>
    <a href="/finance/voucher/rcpt_create" class="btn btn-sm btn-primary">+ New</a>
  </div>

  <div class="p-3 border-bottom">
    <form class="row g-2">
      <div class="col-md-3">
        <input type="text" name="q" class="form-control form-control-sm" placeholder="Search...">
      </div>
      <div class="col-md-3">
        <select name="status" class="form-control form-control-sm">
          <option value="">All Status</option>
          <option>Pending Approval</option>
          <option>Approved</option>
          <option>Paid</option>
          <option>Rejected</option>
        </select>
      </div>
      <div class="col-md-2">
        <button class="btn btn-sm btn-dark">Filter</button>
      </div>
    </form>
  </div>

  <div class="table-responsive">
    <table class="table mb-0">
      <thead>
        <tr>
          <th>No</th>
          <th>Payee</th>
          <th>Amount</th>
          <th>Status</th>
          <th>Date</th>
          <th></th>
        </tr>
      </thead>

      <tbody>
        <?php foreach ($vouchers as $v): ?>
          <tr>
            <td><?= $v['voucher_number'] ?></td>
            <td><?= $v['payee_name'] ?></td>
            <td>₹<?= number_format($v['amount']) ?></td>
            <td><span class="badge bg-light text-dark"><?= $v['status'] ?></span></td>
            <td><?= date('d M Y', strtotime($v['created_at'])) ?></td>
            <td>
              <a href="/finance/voucher/view/<?= $v['id'] ?>" class="btn btn-xs btn-outline-primary">View</a>
            </td>
          </tr>
        <?php endforeach; ?>
      </tbody>

    </table>
  </div>
</div>
<br><br>
<div class="card">
  <div class="card-header d-flex justify-content-between">
    <span><i class="bi bi-receipt me-2"></i>Payment Vouchers</span>
    <a href="/finance/voucher/create" class="btn btn-sm btn-primary">+ New</a>
  </div>

  <div class="p-3 border-bottom">
    <form class="row g-2">
      <div class="col-md-3">
        <input type="text" name="q" class="form-control form-control-sm" placeholder="Search...">
      </div>
      <div class="col-md-3">
        <select name="status" class="form-control form-control-sm">
          <option value="">All Status</option>
          <option>Pending Approval</option>
          <option>Approved</option>
          <option>Paid</option>
          <option>Rejected</option>
        </select>
      </div>
      <div class="col-md-2">
        <button class="btn btn-sm btn-dark">Filter</button>
      </div>
    </form>
  </div>

  <div class="table-responsive">
    <table class="table mb-0">
      <thead>
        <tr>
          <th>No</th>
          <th>Payee</th>
          <th>Amount</th>
          <th>Status</th>
          <th>Date</th>
          <th></th>
        </tr>
      </thead>

      <tbody>
        <?php foreach ($vouchers as $v): ?>
          <tr>
            <td><?= $v['voucher_number'] ?></td>
            <td><?= $v['payee_name'] ?></td>
            <td>₹<?= number_format($v['amount']) ?></td>
            <td><span class="badge bg-light text-dark"><?= $v['status'] ?></span></td>
            <td><?= date('d M Y', strtotime($v['created_at'])) ?></td>
            <td>
              <a href="/finance/voucher/view/<?= $v['id'] ?>" class="btn btn-xs btn-outline-primary">View</a>
            </td>
          </tr>
        <?php endforeach; ?>
      </tbody>

    </table>
  </div>
</div>