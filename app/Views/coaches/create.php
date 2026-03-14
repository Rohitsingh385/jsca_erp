<!-- app/Views/coaches/create.php -->
<div class="mb-3">
  <a href="<?= base_url('coaches') ?>" class="text-muted" style="font-size:13px;">
    <i class="bi bi-arrow-left me-1"></i>Back to Coaches
  </a>
</div>

<form method="post" action="<?= base_url('coaches/store') ?>" enctype="multipart/form-data">
  <?= csrf_field() ?>

  <div class="row g-3">

    <div class="col-lg-8">

      <div class="card mb-3">
        <div class="card-header">Personal Information</div>
        <div class="card-body">
          <div class="row g-3">
            <div class="col-md-6">
              <label class="form-label" style="font-size:12px;font-weight:600;">Full Name <span class="text-danger">*</span></label>
              <input type="text" name="full_name" class="form-control form-control-sm" value="<?= old('full_name') ?>" required>
            </div>
            <div class="col-md-3">
              <label class="form-label" style="font-size:12px;font-weight:600;">Date of Birth <span class="text-danger">*</span></label>
              <input type="date" name="date_of_birth" class="form-control form-control-sm" value="<?= old('date_of_birth') ?>" required>
            </div>
            <div class="col-md-3">
              <label class="form-label" style="font-size:12px;font-weight:600;">Gender <span class="text-danger">*</span></label>
              <select name="gender" class="form-select form-select-sm" required>
                <option value="">Select…</option>
                <option value="Male"   <?= old('gender') === 'Male'   ? 'selected' : '' ?>>Male</option>
                <option value="Female" <?= old('gender') === 'Female' ? 'selected' : '' ?>>Female</option>
                <option value="Other"  <?= old('gender') === 'Other'  ? 'selected' : '' ?>>Other</option>
              </select>
            </div>
            <div class="col-md-4">
              <label class="form-label" style="font-size:12px;font-weight:600;">Phone</label>
              <input type="text" name="phone" class="form-control form-control-sm" value="<?= old('phone') ?>">
            </div>
            <div class="col-md-4">
              <label class="form-label" style="font-size:12px;font-weight:600;">Email</label>
              <input type="email" name="email" class="form-control form-control-sm" value="<?= old('email') ?>">
            </div>
            <div class="col-md-4">
              <label class="form-label" style="font-size:12px;font-weight:600;">District</label>
              <select name="district_id" class="form-select form-select-sm">
                <option value="">Select…</option>
                <?php foreach ($districts as $d): ?>
                  <option value="<?= $d['id'] ?>" <?= old('district_id') == $d['id'] ? 'selected' : '' ?>><?= esc($d['name']) ?></option>
                <?php endforeach; ?>
              </select>
            </div>
            <div class="col-12">
              <label class="form-label" style="font-size:12px;font-weight:600;">Address</label>
              <textarea name="address" class="form-control form-control-sm" rows="2"><?= old('address') ?></textarea>
            </div>
          </div>
        </div>
      </div>

      <div class="card mb-3">
        <div class="card-header">Coaching Profile</div>
        <div class="card-body">
          <div class="row g-3">
            <div class="col-md-4">
              <label class="form-label" style="font-size:12px;font-weight:600;">Coaching Level <span class="text-danger">*</span></label>
              <select name="level" class="form-select form-select-sm" required>
                <?php foreach (['Assistant','Head Coach','Bowling Coach','Batting Coach','Fielding Coach','Fitness Trainer','NCA Level 1','NCA Level 2','NCA Level 3'] as $l): ?>
                  <option value="<?= $l ?>" <?= old('level') === $l ? 'selected' : '' ?>><?= $l ?></option>
                <?php endforeach; ?>
              </select>
            </div>
            <div class="col-md-4">
              <label class="form-label" style="font-size:12px;font-weight:600;">Specialization</label>
              <select name="specialization" class="form-select form-select-sm">
                <?php foreach (['General','Batting','Bowling','Fielding','Wicket-keeping','Fitness'] as $s): ?>
                  <option value="<?= $s ?>" <?= old('specialization') === $s ? 'selected' : '' ?>><?= $s ?></option>
                <?php endforeach; ?>
              </select>
            </div>
            <div class="col-md-4">
              <label class="form-label" style="font-size:12px;font-weight:600;">Experience (years)</label>
              <input type="number" name="experience_years" class="form-control form-control-sm"
                value="<?= old('experience_years', 0) ?>" min="0" max="50">
            </div>
            <div class="col-md-6">
              <label class="form-label" style="font-size:12px;font-weight:600;">BCCI Coach ID</label>
              <input type="text" name="bcci_coach_id" class="form-control form-control-sm"
                value="<?= old('bcci_coach_id') ?>" placeholder="If applicable">
            </div>
            <div class="col-md-6">
              <label class="form-label" style="font-size:12px;font-weight:600;">Aadhaar Number</label>
              <input type="text" name="aadhaar_number" class="form-control form-control-sm"
                value="<?= old('aadhaar_number') ?>" maxlength="12" placeholder="12-digit number">
            </div>
            <div class="col-12">
              <label class="form-label" style="font-size:12px;font-weight:600;">Previous Teams / Clubs</label>
              <input type="text" name="previous_teams" class="form-control form-control-sm"
                value="<?= old('previous_teams') ?>" placeholder="e.g. Ranchi XI, Jharkhand U19, JSCA Academy">
            </div>
            <div class="col-12">
              <label class="form-label" style="font-size:12px;font-weight:600;">Achievements</label>
              <textarea name="achievements" class="form-control form-control-sm" rows="2"
                placeholder="Notable achievements, awards, records…"><?= old('achievements') ?></textarea>
            </div>
          </div>
        </div>
      </div>

    </div>

    <!-- Right: Photo -->
    <div class="col-lg-4">
      <div class="card mb-3">
        <div class="card-header">Photo</div>
        <div class="card-body text-center">
          <div id="photoPreview" style="width:120px;height:140px;border:2px dashed #ddd;border-radius:8px;
            margin:0 auto 12px;display:flex;align-items:center;justify-content:center;overflow:hidden;background:#f8f9fa;">
            <i class="bi bi-person-bounding-box text-muted" style="font-size:36px;"></i>
          </div>
          <input type="file" name="photo" id="photoInput" accept="image/*" class="form-control form-control-sm">
          <div class="form-text" style="font-size:11px;">JPG or PNG, passport size preferred</div>
        </div>
      </div>

      <div class="card">
        <div class="card-header">Notes</div>
        <div class="card-body" style="font-size:12px;color:#666;line-height:1.8;">
          <ul class="ps-3 mb-0">
            <li>JSCA Coach ID auto-generated</li>
            <li>Upload certificates after saving</li>
            <li>BCCI ID required for NCA levels</li>
            <li>Police verification doc mandatory</li>
          </ul>
        </div>
      </div>
    </div>

  </div>

  <div class="d-flex gap-2 mt-3">
    <button type="submit" class="btn btn-jsca-primary">Register Coach</button>
    <a href="<?= base_url('coaches') ?>" class="btn btn-outline-secondary">Cancel</a>
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
