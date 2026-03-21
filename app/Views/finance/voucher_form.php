<div class="card">
  <div class="card-header">
    <i class="bi bi-plus-circle me-2 text-success"></i>Create Voucher
  </div>

  <div class="card-body">

    <form method="post" action="/finance/voucher/store" class="row g-3">

      <div class="col-md-6">
        <label class="form-label">Payee Name</label>
        <input type="text" name="payee_name" class="form-control form-control-sm">
      </div>

      <div class="col-md-3">
        <label>Type</label>
        <select name="payee_type" class="form-control form-control-sm">
          <option>Umpire</option>
          <option>Scorer</option>
        </select>
      </div>

      <div class="col-md-3">
        <label>Amount</label>
        <input type="number" name="amount" class="form-control form-control-sm">
      </div>

      <div class="col-md-4">
        <label>Payment Mode</label>
        <select name="payment_mode" class="form-control form-control-sm">
          <option>NEFT</option>
          <option>Cash</option>
        </select>
      </div>

      <div class="col-md-4">
        <label>Bank Account</label>
        <input type="text" name="bank_account" class="form-control form-control-sm">
      </div>

      <div class="col-md-4">
        <label>IFSC</label>
        <input type="text" name="bank_ifsc" class="form-control form-control-sm">
      </div>

      <div class="col-12">
        <label>Description</label>
        <textarea name="description" class="form-control form-control-sm"></textarea>
      </div>

      <div class="col-12">
        <button class="btn btn-success btn-sm">Save Voucher</button>
      </div>

    </form>

  </div>
</div>