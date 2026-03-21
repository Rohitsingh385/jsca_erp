<div class="card">
    <div class="card-header">Account Groups</div>

    <div class="card-body">

        <form method="post" action="/finance/accgroups/store" class="row g-2 mb-3">
            <?= csrf_field() ?>
            <div class="col-md-3">
                <input name="name" class="form-control form-control-sm" placeholder="Group Name">
            </div>


            <div class="col-md-3">
                <select name="acc_type" class="form-control form-control-sm">
                    <option>Assets</option>
                    <option>Liabilities</option>
                    <option>Income</option>
                    <option>Expense</option>
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
                    <th>A/C Type</th>
                    <th></th>
                </tr>
            </thead>

            <tbody>
                <?php foreach ($groups as $g): ?>
                    <tr>
                        <td><?= $g['Acc_Name'] ?></td>
                        <td><?= $g['Acc_Type'] ?></td>
                        <td>
                            <a href="/finance/accgroups/deleteaccGroup/<?= $g['G_Name'] ?>" class="btn btn-xs btn-danger">Delete</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>

        </table>

    </div>
</div>