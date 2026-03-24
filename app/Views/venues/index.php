<?php // app/Views/venues/index.php ?>

<div class="d-flex justify-content-between align-items-center mb-3">
  <div>
    <h4 class="mb-0">Venues</h4>
    <small class="text-muted"><?= count($venues) ?> venue(s) found</small>
  </div>
  <a href="<?= base_url('venues/create') ?>" class="btn btn-jsca-primary btn-sm">
    <i class="bi bi-plus-circle me-1"></i> Add Venue
  </a>
</div>

<!-- Filters -->
<form method="get" class="card mb-3">
  <div class="card-body py-3">
    <div class="row g-2 align-items-end">
      <div class="col-md-4">
        <input type="text" name="q" value="<?= esc($search) ?>" class="form-control form-control-sm"
          placeholder="Search name, address, contact…">
      </div>
      <div class="col-md-3">
        <select name="district" class="form-select form-select-sm">
          <option value="">All Districts</option>
          <?php foreach ($districts as $d): ?>
            <option value="<?= $d['id'] ?>" <?= $district == $d['id'] ? 'selected' : '' ?>><?= esc($d['name']) ?></option>
          <?php endforeach; ?>
        </select>
      </div>
      <div class="col-md-2">
        <select name="status" class="form-select form-select-sm">
          <option value="">All</option>
          <option value="active"   <?= $status === 'active'   ? 'selected' : '' ?>>Active</option>
          <option value="inactive" <?= $status === 'inactive' ? 'selected' : '' ?>>Inactive</option>
        </select>
      </div>
      <div class="col-md-3 d-flex gap-1">
        <button type="submit" class="btn btn-sm btn-jsca-primary flex-grow-1">Filter</button>
        <a href="<?= base_url('venues') ?>" class="btn btn-sm btn-outline-secondary">Clear</a>
      </div>
    </div>
  </div>
</form>

<!-- Grid -->
<?php if (empty($venues)): ?>
  <div class="card"><div class="card-body text-center text-muted py-5">No venues found.</div></div>
<?php else: ?>
  <div class="row g-3">
    <?php foreach ($venues as $v): ?>
      <div class="col-md-4">
        <div class="card h-100">
          <div class="card-body">
            <div class="d-flex justify-content-between align-items-start mb-2">
              <div>
                <h6 class="mb-0 fw-bold"><?= esc($v['name']) ?></h6>
                <small class="text-muted"><?= esc($v['district_name']) ?> &middot; <?= esc($v['zone']) ?> Zone</small>
              </div>
              <span class="badge <?= $v['is_active'] ? 'bg-success' : 'bg-secondary' ?>" style="font-size:10px;">
                <?= $v['is_active'] ? 'Active' : 'Inactive' ?>
              </span>
            </div>

            <div class="d-flex flex-wrap gap-1 mb-3">
              <?php if ($v['has_floodlights']): ?>
                <span class="badge bg-warning text-dark" style="font-size:10px;"><i class="bi bi-lightbulb me-1"></i>Floodlights</span>
              <?php endif; ?>
              <?php if ($v['has_scoreboard']): ?>
                <span class="badge bg-info text-dark" style="font-size:10px;"><i class="bi bi-display me-1"></i>Scoreboard</span>
              <?php endif; ?>
              <?php if ($v['has_dressing']): ?>
                <span class="badge bg-light text-dark border" style="font-size:10px;"><i class="bi bi-door-open me-1"></i>Dressing Room</span>
              <?php endif; ?>
            </div>

            <div style="font-size:12px;color:#666;" class="mb-3">
              <?php if ($v['capacity']): ?>
                <div><i class="bi bi-people me-1"></i>Capacity: <?= number_format($v['capacity']) ?></div>
              <?php endif; ?>
              <div><i class="bi bi-circle me-1"></i>Pitch: <?= esc($v['pitch_type']) ?></div>
              <?php if ($v['contact_person']): ?>
                <div><i class="bi bi-person me-1"></i><?= esc($v['contact_person']) ?>
                  <?php if ($v['contact_phone']): ?> &middot; <?= esc($v['contact_phone']) ?><?php endif; ?>
                </div>
              <?php endif; ?>
              <?php if ($v['address']): ?>
                <div class="text-truncate"><i class="bi bi-geo-alt me-1"></i><?= esc($v['address']) ?></div>
              <?php endif; ?>
            </div>

            <a href="<?= base_url('venues/view/' . $v['id']) ?>" class="btn btn-sm btn-outline-secondary w-100">View Details</a>
          </div>
        </div>
      </div>
    <?php endforeach; ?>
  </div>
<?php endif; ?>
