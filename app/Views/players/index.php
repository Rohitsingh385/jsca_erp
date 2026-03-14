<!-- app/Views/players/index.php -->
<div class="d-flex justify-content-between align-items-center mb-3">
  <div>
    <span style="font-size:13px;color:#999;"><?= number_format($total) ?> players found</span>
  </div>
  <div class="d-flex gap-2">
    <a href="<?= base_url('players/export') ?>" class="btn btn-sm btn-outline-secondary">
      <i class="bi bi-download me-1"></i>Export CSV
    </a>
    <a href="<?= base_url('players/create') ?>" class="btn btn-sm btn-jsca-primary">
      + Register Player
    </a>
  </div>
</div>

<!-- Filters -->
<form method="get" class="card mb-3">
  <div class="card-body py-3">
    <div class="row g-2 align-items-end">
      <div class="col-md-4">
        <input type="text" name="q" value="<?= esc($search) ?>" class="form-control form-control-sm" placeholder="Search name, JSCA ID, phone…">
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
        <select name="district" class="form-select form-select-sm">
          <option value="">All Districts</option>
          <?php foreach ($districts as $d): ?>
            <option value="<?= $d['id'] ?>" <?= $district == $d['id'] ? 'selected' : '' ?>><?= esc($d['name']) ?></option>
          <?php endforeach; ?>
        </select>
      </div>
      <div class="col-md-2">
        <select name="status" class="form-select form-select-sm">
          <?php foreach (['Active','Inactive','Suspended','Retired'] as $s): ?>
            <option value="<?= $s ?>" <?= ($status ?? 'Active') === $s ? 'selected' : '' ?>><?= $s ?></option>
          <?php endforeach; ?>
        </select>
      </div>
      <div class="col-md-2 d-flex gap-1">
        <button type="submit" class="btn btn-sm btn-jsca-primary flex-grow-1">Filter</button>
        <a href="<?= base_url('players') ?>" class="btn btn-sm btn-outline-secondary">Clear</a>
      </div>
    </div>
  </div>
</form>

<!-- Table -->
<div class="card">
  <div class="card-body p-0">
    <div class="table-responsive">
      <table class="table mb-0">
        <thead>
          <tr>
            <th>Player</th>
            <th>JSCA ID</th>
            <th>Role</th>
            <th>Category</th>
            <th>District</th>
            <th>Aadhaar</th>
            <th>Status</th>
            <th></th>
          </tr>
        </thead>
        <tbody>
          <?php if (empty($players)): ?>
            <tr><td colspan="8" class="text-center text-muted py-4">No players found.</td></tr>
          <?php else: ?>
            <?php foreach ($players as $p): ?>
              <tr>
                <td>
                  <div class="d-flex align-items-center gap-2">
                    <?php if ($p['photo_path']): ?>
                      <img src="<?= base_url('uploads/' . ltrim($p['photo_path'], 'uploads/')) ?>"
                           style="width:32px;height:32px;border-radius:50%;object-fit:cover;">
                    <?php else: ?>
                      <div class="avatar-circle" style="width:32px;height:32px;font-size:12px;">
                        <?= strtoupper(substr($p['full_name'], 0, 1)) ?>
                      </div>
                    <?php endif; ?>
                    <div>
                      <div style="font-size:13px;font-weight:600;"><?= esc($p['full_name']) ?></div>
                      <div style="font-size:11px;color:#999;"><?= esc($p['phone'] ?? '—') ?></div>
                    </div>
                  </div>
                </td>
                <td><code style="font-size:11px;"><?= esc($p['jsca_player_id']) ?></code></td>
                <td style="font-size:13px;"><?= esc($p['role']) ?></td>
                <td><span class="badge bg-light text-dark"><?= esc($p['age_category']) ?></span></td>
                <td style="font-size:13px;"><?= esc($p['district_name']) ?></td>
                <td>
                  <?php if ($p['aadhaar_verified']): ?>
                    <span class="text-success" style="font-size:12px;"><i class="bi bi-patch-check-fill"></i> Verified</span>
                  <?php elseif ($p['aadhaar_number']): ?>
                    <span class="text-warning" style="font-size:12px;"><i class="bi bi-clock"></i> Pending</span>
                  <?php else: ?>
                    <span class="text-muted" style="font-size:12px;">—</span>
                  <?php endif; ?>
                </td>
                <td>
                  <span class="badge <?= $p['status'] === 'Active' ? 'bg-success' : ($p['status'] === 'Suspended' ? 'bg-danger' : 'bg-secondary') ?>"
                    style="font-size:10px;"><?= esc($p['status']) ?></span>
                </td>
                <td>
                  <a href="<?= base_url('players/view/' . $p['id']) ?>" class="btn btn-sm btn-outline-secondary">View</a>
                </td>
              </tr>
            <?php endforeach; ?>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>

<!-- Pagination -->
<?php if ($total > $perPage): ?>
  <div class="d-flex justify-content-between align-items-center mt-3">
    <small class="text-muted">
      Showing <?= (($page-1)*$perPage)+1 ?>–<?= min($page*$perPage, $total) ?> of <?= number_format($total) ?>
    </small>
    <div class="d-flex gap-1">
      <?php $totalPages = ceil($total / $perPage); ?>
      <?php for ($i = 1; $i <= $totalPages; $i++): ?>
        <a href="?<?= http_build_query(array_merge($_GET, ['page' => $i])) ?>"
           class="btn btn-sm <?= $i === $page ? 'btn-jsca-primary' : 'btn-outline-secondary' ?>">
          <?= $i ?>
        </a>
      <?php endfor; ?>
    </div>
  </div>
<?php endif; ?>
