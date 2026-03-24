<?php
$canManage    = $canManage ?? false;
$statusColors = [
    'Draft'         => 'bg-secondary',
    'Registration'  => 'bg-primary',
    'Fixture Ready' => 'bg-info text-dark',
    'Ongoing'       => 'bg-success',
    'Completed'     => 'bg-dark',
    'Cancelled'     => 'bg-danger',
];
?>

<div class="d-flex justify-content-between align-items-center mb-4">
  <div>
    <h4 class="mb-0 fw-bold">Tournaments</h4>
    <small class="text-muted"><?= count($tournaments) ?> tournament(s) found</small>
  </div>
  <?php if ($canManage): ?>
    <a href="<?= base_url('tournaments/create') ?>" class="btn btn-jsca-primary">
      <i class="bi bi-plus-circle me-1"></i> Create Tournament
    </a>
  <?php endif; ?>
</div>

<!-- Filters -->
<div class="card mb-4">
  <div class="card-body py-3">
    <form method="get" class="row g-2 align-items-end">
      <div class="col-md-3">
        <input type="text" name="q" value="<?= esc($search ?? '') ?>" class="form-control form-control-sm" placeholder="Search name or ID…">
      </div>
      <div class="col-md-2">
        <select name="status" class="form-select form-select-sm">
          <option value="">All Statuses</option>
          <?php foreach (['Draft','Registration','Fixture Ready','Ongoing','Completed','Cancelled'] as $s): ?>
            <option value="<?= $s ?>" <?= ($status ?? '') === $s ? 'selected' : '' ?>><?= $s ?></option>
          <?php endforeach; ?>
        </select>
      </div>
      <div class="col-md-2">
        <select name="category" class="form-select form-select-sm">
          <option value="">All Categories</option>
          <?php foreach (['U14','U16','U19','Senior','Masters','Women'] as $c): ?>
            <option value="<?= $c ?>" <?= ($category ?? '') === $c ? 'selected' : '' ?>><?= $c ?></option>
          <?php endforeach; ?>
        </select>
      </div>
      <div class="col-md-2">
        <select name="format" class="form-select form-select-sm">
          <option value="">All Formats</option>
          <?php foreach (['T10','T20','ODI-40','ODI-50','Test','Custom'] as $f): ?>
            <option value="<?= $f ?>" <?= ($format ?? '') === $f ? 'selected' : '' ?>><?= $f ?></option>
          <?php endforeach; ?>
        </select>
      </div>
      <div class="col-md-3 d-flex gap-2">
        <button class="btn btn-jsca-primary btn-sm flex-fill">Filter</button>
        <a href="<?= base_url('tournaments') ?>" class="btn btn-outline-secondary btn-sm">Clear</a>
      </div>
    </form>
  </div>
</div>

<div class="card">
  <div class="card-body p-0">
    <div class="table-responsive">
      <table class="table table-hover mb-0">
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
                  <div class="fw-semibold"><?= esc($t['name']) ?></div>
                  <div style="font-size:11px;color:#999;">
                    <code><?= esc($t['jsca_tournament_id'] ?? '') ?></code>
                    <?php if (!empty($t['edition'])): ?> · <?= esc($t['edition']) ?><?php endif; ?>
                    · <?= esc($t['season']) ?>
                  </div>
                </td>
                <td><span class="badge bg-light text-dark"><?= esc($t['format']) ?></span></td>
                <td><span class="badge bg-light text-dark"><?= esc($t['age_category']) ?></span></td>
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
                  <span class="badge <?= $statusColors[$t['status']] ?? 'bg-secondary' ?>" style="font-size:10px;">
                    <?= esc($t['status']) ?>
                  </span>
                </td>
                <td>
                  <a href="<?= base_url('tournaments/view/' . $t['id']) ?>" class="btn btn-sm btn-outline-primary">View</a>
                </td>
              </tr>
            <?php endforeach; ?>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>
