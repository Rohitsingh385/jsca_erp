<!-- app/Views/tournaments/index.php -->
<div class="d-flex justify-content-between align-items-center mb-3">
  <span style="font-size:13px;color:#999;"><?= count($tournaments) ?> tournaments</span>
  <a href="<?= base_url('tournaments/create') ?>" class="btn btn-sm btn-jsca-primary">+ Create Tournament</a>
</div>

<form method="get" class="card mb-3">
  <div class="card-body py-3">
    <div class="row g-2 align-items-end">
      <div class="col-md-3">
        <input type="text" name="q" value="<?= esc($search) ?>" class="form-control form-control-sm"
          placeholder="Search name or ID…">
      </div>
      <div class="col-md-2">
        <select name="status" class="form-select form-select-sm">
          <option value="">All Statuses</option>
          <?php foreach (['Draft','Registration Open','Registration Closed','Fixture Ready','Ongoing','Completed','Cancelled'] as $s): ?>
            <option value="<?= $s ?>" <?= $status === $s ? 'selected' : '' ?>><?= $s ?></option>
          <?php endforeach; ?>
        </select>
      </div>
      <div class="col-md-2">
        <select name="category" class="form-select form-select-sm">
          <option value="">All Categories</option>
          <?php foreach (['U14','U16','U19','Senior','Masters','Mixed'] as $c): ?>
            <option value="<?= $c ?>" <?= $category === $c ? 'selected' : '' ?>><?= $c ?></option>
          <?php endforeach; ?>
        </select>
      </div>
      <div class="col-md-2">
        <select name="format" class="form-select form-select-sm">
          <option value="">All Formats</option>
          <?php foreach (['T20','ODI','Test','T10','The Hundred','Other'] as $f): ?>
            <option value="<?= $f ?>" <?= $format === $f ? 'selected' : '' ?>><?= $f ?></option>
          <?php endforeach; ?>
        </select>
      </div>
      <div class="col-md-3 d-flex gap-1">
        <button type="submit" class="btn btn-sm btn-jsca-primary flex-grow-1">Filter</button>
        <a href="<?= base_url('tournaments') ?>" class="btn btn-sm btn-outline-secondary">Clear</a>
      </div>
    </div>
  </div>
</form>

<?php
$statusColors = [
  'Draft'                => 'bg-secondary',
  'Registration Open'    => 'bg-primary',
  'Registration Closed'  => 'bg-warning text-dark',
  'Fixture Ready'        => 'bg-info text-dark',
  'Ongoing'              => 'bg-success',
  'Completed'            => 'bg-dark',
  'Cancelled'            => 'bg-danger',
];
?>

<div class="card">
  <div class="card-body p-0">
    <div class="table-responsive">
      <table class="table mb-0">
        <thead>
          <tr>
            <th>Tournament</th>
            <th>Format</th>
            <th>Category</th>
            <th>Dates</th>
            <th>Teams</th>
            <th>Matches</th>
            <th>Prize Pool</th>
            <th>Status</th>
            <th></th>
          </tr>
        </thead>
        <tbody>
          <?php if (empty($tournaments)): ?>
            <tr><td colspan="9" class="text-center text-muted py-4">No tournaments found.</td></tr>
          <?php else: ?>
            <?php foreach ($tournaments as $t): ?>
              <tr>
                <td>
                  <div style="font-size:13px;font-weight:600;"><?= esc($t['name']) ?></div>
                  <div style="font-size:11px;color:#999;">
                    <code><?= esc($t['jsca_tournament_id']) ?></code>
                    <?php if ($t['edition']): ?> · <?= esc($t['edition']) ?><?php endif; ?>
                  </div>
                </td>
                <td><span class="badge bg-light text-dark"><?= esc($t['format']) ?></span></td>
                <td><span class="badge bg-light text-dark"><?= esc($t['category']) ?></span></td>
                <td style="font-size:12px;">
                  <?php if ($t['start_date']): ?>
                    <?= date('d M Y', strtotime($t['start_date'])) ?>
                    <?php if ($t['end_date']): ?>
                      <br><span class="text-muted">to <?= date('d M Y', strtotime($t['end_date'])) ?></span>
                    <?php endif; ?>
                  <?php else: ?>
                    <span class="text-muted">TBD</span>
                  <?php endif; ?>
                </td>
                <td>
                  <span class="badge" style="background:#e3f2fd;color:#1565c0;">
                    <?= $t['team_count'] ?><?= $t['max_teams'] ? '/' . $t['max_teams'] : '' ?>
                  </span>
                </td>
                <td style="font-size:12px;">
                  <?= $t['completed_count'] ?>/<?= $t['match_count'] ?>
                  <span class="text-muted">done</span>
                </td>
                <td style="font-size:13px;">
                  <?= $t['prize_pool'] > 0 ? '₹' . number_format($t['prize_pool']) : '—' ?>
                </td>
                <td>
                  <span class="badge <?= $statusColors[$t['status']] ?? 'bg-secondary' ?>"
                    style="font-size:10px;"><?= esc($t['status']) ?></span>
                </td>
                <td>
                  <a href="<?= base_url('tournaments/view/' . $t['id']) ?>"
                    class="btn btn-sm btn-outline-secondary">View</a>
                </td>
              </tr>
            <?php endforeach; ?>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>
