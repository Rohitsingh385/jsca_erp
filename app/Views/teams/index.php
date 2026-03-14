<!-- app/Views/teams/index.php -->
<div class="d-flex justify-content-between align-items-center mb-3">
  <span style="font-size:13px;color:#999;"><?= count($teams) ?> teams found</span>
  <a href="<?= base_url('teams/create') ?>" class="btn btn-sm btn-jsca-primary">+ Create Team</a>
</div>

<form method="get" class="card mb-3">
  <div class="card-body py-3">
    <div class="row g-2 align-items-end">
      <div class="col-md-4">
        <input type="text" name="q" value="<?= esc($search) ?>" class="form-control form-control-sm"
          placeholder="Search team name or JSCA ID…">
      </div>
      <div class="col-md-2">
        <select name="category" class="form-select form-select-sm">
          <option value="">All Categories</option>
          <?php foreach (['U14','U16','U19','Senior','Masters'] as $c): ?>
            <option value="<?= $c ?>" <?= $category === $c ? 'selected' : '' ?>><?= $c ?></option>
          <?php endforeach; ?>
        </select>
      </div>
      <div class="col-md-2">
        <select name="status" class="form-select form-select-sm">
          <?php foreach (['Active','Inactive','Disqualified'] as $s): ?>
            <option value="<?= $s ?>" <?= $status === $s ? 'selected' : '' ?>><?= $s ?></option>
          <?php endforeach; ?>
        </select>
      </div>
      <div class="col-md-4 d-flex gap-1">
        <button type="submit" class="btn btn-sm btn-jsca-primary flex-grow-1">Filter</button>
        <a href="<?= base_url('teams') ?>" class="btn btn-sm btn-outline-secondary">Clear</a>
      </div>
    </div>
  </div>
</form>

<div class="card">
  <div class="card-body p-0">
    <div class="table-responsive">
      <table class="table mb-0">
        <thead>
          <tr>
            <th>Team</th>
            <th>JSCA ID</th>
            <th>Category</th>
            <th>District</th>
            <th>Captain</th>
            <th>Players</th>
            <th>Coaches</th>
            <th>Status</th>
            <th></th>
          </tr>
        </thead>
        <tbody>
          <?php if (empty($teams)): ?>
            <tr><td colspan="9" class="text-center text-muted py-4">No teams found.</td></tr>
          <?php else: ?>
            <?php foreach ($teams as $t): ?>
              <tr>
                <td>
                  <div class="d-flex align-items-center gap-2">
                    <?php if ($t['logo_path']): ?>
                      <img src="<?= base_url('uploads/' . ltrim($t['logo_path'], 'uploads/')) ?>"
                        style="width:32px;height:32px;border-radius:6px;object-fit:cover;border:1px solid #eee;">
                    <?php else: ?>
                      <div style="width:32px;height:32px;border-radius:6px;background:#1a3a5c;
                        display:flex;align-items:center;justify-content:center;color:#fff;font-size:11px;font-weight:700;">
                        <?= esc($t['short_name'] ?: strtoupper(substr($t['name'], 0, 3))) ?>
                      </div>
                    <?php endif; ?>
                    <div>
                      <div style="font-size:13px;font-weight:600;"><?= esc($t['name']) ?></div>
                      <?php if ($t['home_ground']): ?>
                        <div style="font-size:11px;color:#999;"><?= esc($t['home_ground']) ?></div>
                      <?php endif; ?>
                    </div>
                  </div>
                </td>
                <td><code style="font-size:11px;"><?= esc($t['jsca_team_id']) ?></code></td>
                <td><span class="badge bg-light text-dark"><?= esc($t['category']) ?></span></td>
                <td style="font-size:13px;"><?= esc($t['district_name'] ?? '—') ?></td>
                <td style="font-size:13px;"><?= esc($t['captain_name'] ?? '—') ?></td>
                <td>
                  <span class="badge" style="background:#e3f2fd;color:#1565c0;">
                    <?= $t['player_count'] ?> players
                  </span>
                </td>
                <td>
                  <span class="badge" style="background:#e8f5e9;color:#2e7d32;">
                    <?= $t['coach_count'] ?> coaches
                  </span>
                </td>
                <td>
                  <span class="badge <?= $t['status'] === 'Active' ? 'bg-success' : ($t['status'] === 'Disqualified' ? 'bg-danger' : 'bg-secondary') ?>"
                    style="font-size:10px;"><?= esc($t['status']) ?></span>
                </td>
                <td>
                  <a href="<?= base_url('teams/view/' . $t['id']) ?>" class="btn btn-sm btn-outline-secondary">View</a>
                </td>
              </tr>
            <?php endforeach; ?>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>
