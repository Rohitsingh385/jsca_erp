<!-- app/Views/coaches/index.php -->
<div class="d-flex justify-content-between align-items-center mb-3">
  <span style="font-size:13px;color:#999;"><?= count($coaches) ?> coaches found</span>
  <a href="<?= base_url('coaches/create') ?>" class="btn btn-sm btn-jsca-primary">+ Register Coach</a>
</div>

<!-- Filters -->
<form method="get" class="card mb-3">
  <div class="card-body py-3">
    <div class="row g-2 align-items-end">
      <div class="col-md-4">
        <input type="text" name="q" value="<?= esc($search) ?>" class="form-control form-control-sm"
          placeholder="Search name, JSCA ID, BCCI ID, phone…">
      </div>
      <div class="col-md-3">
        <select name="level" class="form-select form-select-sm">
          <option value="">All Levels</option>
          <?php foreach (['Assistant','Head Coach','Bowling Coach','Batting Coach','Fielding Coach','Fitness Trainer','NCA Level 1','NCA Level 2','NCA Level 3'] as $l): ?>
            <option value="<?= $l ?>" <?= $level === $l ? 'selected' : '' ?>><?= $l ?></option>
          <?php endforeach; ?>
        </select>
      </div>
      <div class="col-md-2">
        <select name="status" class="form-select form-select-sm">
          <?php foreach (['Active','Inactive','Suspended'] as $s): ?>
            <option value="<?= $s ?>" <?= $status === $s ? 'selected' : '' ?>><?= $s ?></option>
          <?php endforeach; ?>
        </select>
      </div>
      <div class="col-md-3 d-flex gap-1">
        <button type="submit" class="btn btn-sm btn-jsca-primary flex-grow-1">Filter</button>
        <a href="<?= base_url('coaches') ?>" class="btn btn-sm btn-outline-secondary">Clear</a>
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
            <th>Coach</th>
            <th>JSCA ID</th>
            <th>Level</th>
            <th>Specialization</th>
            <th>Experience</th>
            <th>District</th>
            <th>Aadhaar</th>
            <th>Status</th>
            <th></th>
          </tr>
        </thead>
        <tbody>
          <?php if (empty($coaches)): ?>
            <tr><td colspan="9" class="text-center text-muted py-4">No coaches found.</td></tr>
          <?php else: ?>
            <?php foreach ($coaches as $c): ?>
              <tr>
                <td>
                  <div class="d-flex align-items-center gap-2">
                    <?php if ($c['photo_path']): ?>
                      <img src="<?= base_url('uploads/' . ltrim($c['photo_path'], 'uploads/')) ?>"
                        style="width:32px;height:32px;border-radius:50%;object-fit:cover;">
                    <?php else: ?>
                      <div class="avatar-circle" style="width:32px;height:32px;font-size:12px;">
                        <?= strtoupper(substr($c['full_name'], 0, 1)) ?>
                      </div>
                    <?php endif; ?>
                    <div>
                      <div style="font-size:13px;font-weight:600;"><?= esc($c['full_name']) ?></div>
                      <div style="font-size:11px;color:#999;"><?= esc($c['phone'] ?? '—') ?></div>
                    </div>
                  </div>
                </td>
                <td><code style="font-size:11px;"><?= esc($c['jsca_coach_id']) ?></code></td>
                <td style="font-size:13px;"><?= esc($c['level']) ?></td>
                <td><span class="badge bg-light text-dark"><?= esc($c['specialization']) ?></span></td>
                <td style="font-size:13px;"><?= $c['experience_years'] ?> yrs</td>
                <td style="font-size:13px;"><?= esc($c['district_name'] ?? '—') ?></td>
                <td>
                  <?php if ($c['aadhaar_verified']): ?>
                    <span class="text-success" style="font-size:12px;"><i class="bi bi-patch-check-fill"></i> Verified</span>
                  <?php elseif ($c['aadhaar_number']): ?>
                    <span class="text-warning" style="font-size:12px;"><i class="bi bi-clock"></i> Pending</span>
                  <?php else: ?>
                    <span class="text-muted" style="font-size:12px;">—</span>
                  <?php endif; ?>
                </td>
                <td>
                  <span class="badge <?= $c['status'] === 'Active' ? 'bg-success' : ($c['status'] === 'Suspended' ? 'bg-danger' : 'bg-secondary') ?>"
                    style="font-size:10px;"><?= esc($c['status']) ?></span>
                </td>
                <td>
                  <a href="<?= base_url('coaches/view/' . $c['id']) ?>" class="btn btn-sm btn-outline-secondary">View</a>
                </td>
              </tr>
            <?php endforeach; ?>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>
