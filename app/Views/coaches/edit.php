<!-- app/Views/coaches/edit.php -->
<div class="mb-3">
  <a href="<?= base_url('coaches/view/' . $coach['id']) ?>" class="text-muted" style="font-size:13px;">
    <i class="bi bi-arrow-left me-1"></i>Back to Profile
  </a>
</div>

<form method="post" action="<?= base_url('coaches/update/' . $coach['id']) ?>" enctype="multipart/form-data">
  <?= csrf_field() ?>

  <div class="row g-3">

    <div class="col-lg-8">

      <div class="card mb-3">
        <div class="card-header">Personal Information</div>
        <div class="card-body">
          <div class="row g-3">
            <div class="col-md-6">
              <label class="form-label" style="font-size:12px;font-weight:600;">Full Name <span class="text-danger">*</span></label>
              <input type="text" name="full_name" class="form-control form-control-sm"
                value="<?= esc($coach['full_name']) ?>" required>
            </div>
            <div class="col-md-3">
              <label class="form-label" style="font-size:12px;font-weight:600;">Date of Birth</label>
              <input type="date" class="form-control form-control-sm"
                value="<?= esc($coach['date_of_birth']) ?>" disabled>
              <div class="form-text" style="font-size:11px;">Contact admin to change</div>
            </div>
            <div class="col-md-3">
              <label class="form-label" style="font-size:12px;font-weight:600;">Status</label>
              <select name="status" class="form-select form-select-sm">
                <?php foreach (['Active','Inactive','Suspended'] as $s): ?>
                  <option value="<?= $s ?>" <?= $coach['status'] === $s ? 'selected' : '' ?>><?= $s ?></option>
                <?php endforeach; ?>
              </select>
            </div>
            <div class="col-md-4">
              <label class="form-label" style="font-size:12px;font-weight:600;">Phone</label>
              <input type="text" name="phone" class="form-control form-control-sm" value="<?= esc($coach['phone']) ?>">
            </div>
            <div class="col-md-4">
              <label class="form-label" style="font-size:12px;font-weight:600;">Email</label>
              <input type="email" name="email" class="form-control form-control-sm" value="<?= esc($coach['email']) ?>">
            </div>
            <div class="col-md-4">
              <label class="form-label" style="font-size:12px;font-weight:600;">District</label>
              <select name="district_id" class="form-select form-select-sm">
                <option value="">Select…</option>
                <?php foreach ($districts as $d): ?>
                  <option value="<?= $d['id'] ?>" <?= $coach['district_id'] == $d['id'] ? 'selected' : '' ?>>
                    <?= esc($d['name']) ?>
                  </option>
                <?php endforeach; ?>
              </select>
            </div>
            <div class="col-12">
              <label class="form-label" style="font-size:12px;font-weight:600;">Address</label>
              <textarea name="address" class="form-control form-control-sm" rows="2"><?= esc($coach['address']) ?></textarea>
            </div>
          </div>
        </div>
      </div>

      <div class="card mb-3">
        <div class="card-header">Coaching Profile</div>
        <div class="card-body">
          <div class="row g-3">
            <div class="col-md-4">
              <label class="form-label" style="font-size:12px;font-weight:600;">Coaching Level</label>
              <select name="level" class="form-select form-select-sm">
                <?php foreach (['Assistant','Head Coach','Bowling Coach','Batting Coach','Fielding Coach','Fitness Trainer','NCA Level 1','NCA Level 2','NCA Level 3'] as $l): ?>
                  <option value="<?= $l ?>" <?= $coach['level'] === $l ? 'selected' : '' ?>><?= $l ?></option>
                <?php endforeach; ?>
              </select>
            </div>
            <div class="col-md-4">
              <label class="form-label" style="font-size:12px;font-weight:600;">Specialization</label>
              <select name="specialization" class="form-select form-select-sm">
                <?php foreach (['General','Batting','Bowling','Fielding','Wicket-keeping','Fitness'] as $s): ?>
                  <option value="<?= $s ?>" <?= $coach['specialization'] === $s ? 'selected' : '' ?>><?= $s ?></option>
                <?php endforeach; ?>
              </select>
            </div>
            <div class="col-md-4">
              <label class="form-label" style="font-size:12px;font-weight:600;">Experience (years)</label>
              <input type="number" name="experience_years" class="form-control form-control-sm"
                value="<?= esc($coach['experience_years']) ?>" min="0" max="50">
            </div>
            <div class="col-md-6">
              <label class="form-label" style="font-size:12px;font-weight:600;">BCCI Coach ID</label>
              <input type="text" name="bcci_coach_id" class="form-control form-control-sm"
                value="<?= esc($coach['bcci_coach_id']) ?>">
            </div>
            <div class="col-12">
              <label class="form-label" style="font-size:12px;font-weight:600;">Previous Teams / Clubs</label>
              <input type="text" name="previous_teams" class="form-control form-control-sm"
                value="<?= esc($coach['previous_teams']) ?>">
            </div>
            <div class="col-12">
              <label class="form-label" style="font-size:12px;font-weight:600;">Achievements</label>
              <textarea name="achievements" class="form-control form-control-sm" rows="2"><?= esc($coach['achievements']) ?></textarea>
            </div>
          </div>
        </div>
      </div>

    </div>

    <!-- Right: Photo -->
    <div class="col-lg-4">
      <div class="card">
        <div class="card-header">Update Photo</div>
        <div class="card-body text-center">
          <div id="photoPreview" style="width:120px;height:140px;border-radius:8px;margin:0 auto 12px;
            overflow:hidden;background:#f8f9fa;border:2px solid #eee;">
            <?php if ($coach['photo_path']): ?>
              <img src="<?= base_url('uploads/' . ltrim($coach['photo_path'], 'uploads/')) ?>"
                style="width:100%;height:100%;object-fit:cover;">
            <?php else: ?>
              <div style="display:flex;align-items:center;justify-content:center;height:100%;">
                <i class="bi bi-person-bounding-box text-muted" style="font-size:36px;"></i>
              </div>
            <?php endif; ?>
          </div>
          <input type="file" name="photo" id="photoInput" accept="image/*" class="form-control form-control-sm">
          <div class="form-text" style="font-size:11px;">Leave blank to keep current photo</div>
        </div>
      </div>
    </div>

  </div>

  <div class="d-flex gap-2 mt-3">
    <button type="submit" class="btn btn-jsca-primary">Save Changes</button>
    <a href="<?= base_url('coaches/view/' . $coach['id']) ?>" class="btn btn-outline-secondary">Cancel</a>
  </div>

</form>

<script>
  document.getElementById('photoInput').addEventListener('change', function() {
    const file = this.files[0];
    if (!file) return;
    const reader = new FileReader();
    reader.onload = e => {
      document.getElementById('photoPreview').innerHTML =
        `<img src="${e.target.result}" style="width:100%;height:100%;object-fit:cover;">`;
    };
    reader.readAsDataURL(file);
  });
</script>
