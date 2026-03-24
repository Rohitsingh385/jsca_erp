<?php $canManage = in_array($currentUser['role_name'] ?? '', ['superadmin', 'admin']); ?>

<div class="d-flex justify-content-between align-items-center mb-4">
  <div>
    <h4 class="mb-0 fw-bold">Officials Registry</h4>
    <small class="text-muted"><?= count($officials) ?> official(s) found</small>
  </div>
  <?php if ($canManage): ?>
    <a href="<?= base_url('officials/create') ?>" class="btn btn-jsca-primary">
      <i class="bi bi-plus-circle me-1"></i> Add Official
    </a>
  <?php endif; ?>
</div>

<!-- Filters -->
<div class="card mb-4">
  <div class="card-body py-3">
    <form method="get" class="row g-2 align-items-end">
      <div class="col-md-4">
        <input type="text" name="q" class="form-control form-control-sm" placeholder="Search name, ID, phone…" value="<?= esc($search ?? '') ?>">
      </div>
      <div class="col-md-2">
        <select name="type" class="form-select form-select-sm">
          <option value="">All Types</option>
          <?php foreach ($officialTypes as $t): ?>
            <option value="<?= $t['id'] ?>" <?= ($typeId ?? '') == $t['id'] ? 'selected' : '' ?>><?= esc($t['name']) ?></option>
          <?php endforeach; ?>
        </select>
      </div>
      <div class="col-md-2">
        <select name="district" class="form-select form-select-sm">
          <option value="">All Districts</option>
          <?php foreach ($districts as $d): ?>
            <option value="<?= $d['id'] ?>" <?= ($district ?? '') == $d['id'] ? 'selected' : '' ?>><?= esc($d['name']) ?></option>
          <?php endforeach; ?>
        </select>
      </div>
      <div class="col-md-2">
        <select name="status" class="form-select form-select-sm">
          <option value="">All Statuses</option>
          <option value="Active"   <?= ($status ?? '') === 'Active'   ? 'selected' : '' ?>>Active</option>
          <option value="Inactive" <?= ($status ?? '') === 'Inactive' ? 'selected' : '' ?>>Inactive</option>
        </select>
      </div>
      <div class="col-md-2 d-flex gap-2">
        <button class="btn btn-jsca-primary btn-sm flex-fill">Filter</button>
        <a href="<?= base_url('officials') ?>" class="btn btn-outline-secondary btn-sm">Clear</a>
      </div>
    </form>
  </div>
</div>

<!-- Table -->
<div class="card">
  <div class="card-body p-0">
    <div class="table-responsive">
      <table class="table table-hover mb-0">
        <thead>
          <tr>
            <th>JSCA ID</th>
            <th>Name</th>
            <th>Type</th>
            <th>District</th>
            <th>Phone</th>
            <th>Experience</th>
            <th>Status</th>
            <th></th>
          </tr>
        </thead>
        <tbody>
          <?php if (empty($officials)): ?>
            <tr><td colspan="8" class="text-center text-muted py-4">No officials found.</td></tr>
          <?php else: ?>
            <?php foreach ($officials as $o): ?>
              <tr>
                <td><code><?= esc($o['jsca_official_id']) ?></code></td>
                <td>
                  <div class="d-flex align-items-center gap-2">
                    <?php if (!empty($o['profile_photo'])): ?>
                      <img src="<?= base_url('uploads/' . ltrim($o['profile_photo'], 'uploads/')) ?>" class="rounded-circle" width="32" height="32" style="object-fit:cover;">
                    <?php else: ?>
                      <div class="avatar-circle" style="width:32px;height:32px;font-size:12px;"><?= strtoupper(substr($o['full_name'], 0, 1)) ?></div>
                    <?php endif; ?>
                    <span><?= esc($o['full_name']) ?></span>
                  </div>
                </td>
                <td><span class="badge bg-primary bg-opacity-10 text-primary"><?= esc($o['type_name']) ?></span></td>
                <td><?= esc($o['district_name']) ?></td>
                <td><?= esc($o['phone'] ?? '—') ?></td>
                <td><?= $o['experience_years'] ? $o['experience_years'] . ' yr' . ($o['experience_years'] > 1 ? 's' : '') : '—' ?></td>
                <td>
                  <span class="badge <?= $o['status'] === 'Active' ? 'bg-success' : 'bg-secondary' ?>">
                    <?= $o['status'] ?>
                  </span>
                </td>
                <td>
                  <a href="<?= base_url('officials/view/' . $o['id']) ?>" class="btn btn-sm btn-outline-primary">View</a>
                </td>
              </tr>
            <?php endforeach; ?>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>
