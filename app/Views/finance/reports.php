<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>

<h4>Finance Reports</h4>

<div class="card shadow-sm">
<div class="card-body">

<table class="table table-striped">
    <thead>
        <tr>
            <th>Tournament</th>
            <th>Total</th>
            <th>Paid</th>
            <th>Pending</th>
        </tr>
    </thead>

    <tbody>
        <?php foreach ($byTournament as $r): ?>
        <tr>
            <td><?= $r['tournament'] ?></td>
            <td>₹<?= $r['total'] ?></td>
            <td class="text-success">₹<?= $r['paid'] ?></td>
            <td class="text-warning">₹<?= $r['pending'] ?></td>
        </tr>
        <?php endforeach; ?>
    </tbody>
</table>

</div>
</div>

<?= $this->endSection() ?>