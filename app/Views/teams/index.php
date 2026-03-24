<?php $statusColors = ['Registered' => 'bg-primary', 'Confirmed' => 'bg-success', 'Withdrawn' => 'bg-danger']; ?>

<div class="d-flex justify-content-between align-items-center mb-4">
  <div>
    <h4 class="mb-0 fw-bold">Teams</h4>
    <small class="text-muted"><?= count($teams) ?> team(s) found</small>
  </div>
  <?php if ($canManage): ?>
    <a href="<?= base_url('teams/create') ?>" class="btn btn-jsca-primary">
      <i class="bi bi-plus-circle me-1"></i> Register Team
    </a>
  <?php endif; ?>
</div>

<div class="card mb-4">
  <div class="card-body py-3">
    <form method="get" class="row g-2 align-items-end">
      <div class="col-md-4">
        <input type="text" name="q" value="<?= esc($search ?? '') ?>" class="form-control form-control-sm" placeholder="Search team name or ID…">
      </div>
      <div class="col-md-2">
        <select name="district_id" class="form-select form-select-sm">
          <option value="">All Districts</option>
          <?php foreach ($districts as $d): ?>
            <option value="<?= $d['id'] ?>" <?= ($districtFilter ?? '') == $d['id'] ? 'selected' : '' ?>><?= esc($d['name']) ?></option>
          <?php endforeach; ?>
        </select>
      </div>
      <div class="col-md-2">
        <select name="status" class="form-select form-select-sm">
          <option value="">All Statuses</option>
          <?php foreach (['Registered', 'Confirmed', 'Withdrawn'] as $s): ?>
            <option value="<?= $s ?>" <?= ($statusFilter ?? '') === $s ? 'selected' : '' ?>><?= $s ?></option>
          <?php endforeach; ?>
        </select>
      </div>
      <div class="col-md-4 d-flex gap-2">
        <button class="btn btn-jsca-primary btn-sm flex-fill">Filter</button>
        <a href="<?= base_url('teams') ?>" class="btn btn-outline-secondary btn-sm">Clear</a>
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
            <th>Team</th>
            <th>Tournament</th>
            <th>Category</th>
            <th>District</th>
            <th>Captain</th>
            <th>Players</th>
            <th>Status</th>
            <th></th>
          </tr>
        </thead>
        <tbody>
          <?php if (empty($teams)): ?>
            <tr><td colspan="8" class="text-center text-muted py-4">No teams found.</td></tr>
          <?php else: ?>
            <?php foreach ($teams as $t): ?>
              <tr>
                <td>
                  <div class="fw-semibold"><?= esc($t['name']) ?></div>
                  <code style="font-size:11px;color:#999;"><?= esc($t['jsca_team_id'] ?? '—') ?></code>
                </td>
                <td style="font-size:13px;"><?= esc($t['tournament_name'] ?? '—') ?></td>
                <td>
                  <?php if (!empty($t['age_category'])): ?>
                    <span class="badge bg-light text-dark"><?= esc($t['age_category']) ?></span>
                  <?php else: ?>
                    <span class="text-muted">—</span>
                  <?php endif; ?>
                </td>
                <td style="font-size:13px;"><?= esc($t['district_name'] ?? '—') ?></td>
                <td style="font-size:13px;"><?= esc($t['captain_name'] ?? '—') ?></td>
                <td>
                  <span class="badge" style="background:#e3f2fd;color:#1565c0;"><?= $t['player_count'] ?></span>
                </td>
                <td>
                  <span class="badge <?= $statusColors[$t['status']] ?? 'bg-secondary' ?>" style="font-size:10px;">
                    <?= esc($t['status']) ?>
                  </span>
                </td>
                <td>
                  <a href="<?= base_url('teams/view/' . $t['id']) ?>" class="btn btn-sm btn-outline-primary">View</a>
                </td>
              </tr>
            <?php endforeach; ?>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>
