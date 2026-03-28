<div class="card">
  <div class="card-header">Ledger Heads</div>

  <div class="card-body">

    <form method="post" action="/finance/ledger/store" class="row g-2 mb-3">

    <?= csrf_field() ?>
      <div class="col-md-3">
        <input name="name" class="form-control form-control-sm" placeholder="Ledger Name">
      </div>

      <div class="col-md-3">
        <select name="group_id" class="form-control form-control-sm">
          <?php foreach ($groups as $g): ?>
            <option value="<?= $g['G_Name'] ?>"><?= $g['Acc_Name'] ?></option>
          <?php endforeach; ?>
        </select>
      </div>

      <div class="col-md-2">
        <input type="number" step="0.01" name="opening_balance" class="form-control form-control-sm" placeholder="Opening" value='0.00'>
      </div>

      <div class="col-md-2">
        <select name="balance_type" class="form-control form-control-sm">
          <option>Dr</option>
          <option>Cr</option>
        </select>
      </div>

      <div class="col-md-2">
        <button class="btn btn-success btn-sm">Add</button>
      </div>

    </form>

    <table class="table">
      <thead>
        <tr>
          <th>Name</th>
          <th>Group</th>
          <th>Opening</th>
          <th>Type</th>
          <th></th>
        </tr>
      </thead>

      <tbody>
        <?php foreach ($ledgers as $l): ?>
        <tr>
          <td><?= $l['name'] ?></td>
          <td><?= $l['group_name'] ?></td>
          <td><?= $l['opening_balance'] ?></td>
          <td><?= $l['balance_type'] ?></td>
          <td>
            <a href="/finance/ledger/delete/<?= $l['id'] ?>" class="btn btn-xs btn-danger">Delete</a>
          </td>
        </tr>
        <?php endforeach; ?>
      </tbody>

    </table>

  </div>
</div>