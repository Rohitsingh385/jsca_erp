<!-- app/Views/players/create.php -->
<div class="mb-3">
  <a href="<?= base_url('players') ?>" class="text-muted" style="font-size:13px;">
    <i class="bi bi-arrow-left me-1"></i>Back to Players
  </a>
</div>

<form method="post" action="<?= base_url('players/store') ?>" enctype="multipart/form-data">
  <?= csrf_field() ?>

  <div class="row g-3">

    <!-- Left: Personal Info -->
    <div class="col-lg-8">

      <div class="card mb-3">
        <div class="card-header">Personal Information</div>
        <div class="card-body">
          <div class="row g-3">
            <div class="col-md-6">
              <label class="form-label" style="font-size:12px;font-weight:600;">Full Name <span class="text-danger">*</span></label>
              <input type="text" name="full_name" class="form-control form-control-sm"
                value="<?= old('full_name') ?>" required>
            </div>
            <div class="col-md-3">
              <label class="form-label" style="font-size:12px;font-weight:600;">Date of Birth <span class="text-danger">*</span></label>
              <input type="date" name="date_of_birth" class="form-control form-control-sm"
                value="<?= old('date_of_birth') ?>" required>
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
            <div class="col-md-6">
              <label class="form-label" style="font-size:12px;font-weight:600;">District <span class="text-danger">*</span></label>
              <select name="district_id" class="form-select form-select-sm" required>
                <option value="">Select district…</option>
                <?php foreach ($districts as $d): ?>
                  <option value="<?= $d['id'] ?>" <?= old('district_id') == $d['id'] ? 'selected' : '' ?>>
                    <?= esc($d['name']) ?>
                  </option>
                <?php endforeach; ?>
              </select>
            </div>
            <div class="col-md-6">
              <label class="form-label" style="font-size:12px;font-weight:600;">Phone</label>
              <input type="text" name="phone" class="form-control form-control-sm"
                value="<?= old('phone') ?>" placeholder="10-digit mobile">
            </div>
            <div class="col-md-6">
              <label class="form-label" style="font-size:12px;font-weight:600;">Email</label>
              <input type="email" name="email" class="form-control form-control-sm"
                value="<?= old('email') ?>">
            </div>
            <div class="col-12">
              <label class="form-label" style="font-size:12px;font-weight:600;">Address</label>
              <textarea name="address" class="form-control form-control-sm" rows="2"><?= old('address') ?></textarea>
            </div>
          </div>
        </div>
      </div>

      <div class="card mb-3">
        <div class="card-header">Cricket Profile</div>
        <div class="card-body">
          <div class="row g-3">
            <div class="col-md-4">
              <label class="form-label" style="font-size:12px;font-weight:600;">Playing Role <span class="text-danger">*</span></label>
              <select name="role" class="form-select form-select-sm" required>
                <option value="">Select…</option>
                <?php foreach (['Batsman','Bowler','All-rounder','Wicket-keeper'] as $r): ?>
                  <option value="<?= $r ?>" <?= old('role') === $r ? 'selected' : '' ?>><?= $r ?></option>
                <?php endforeach; ?>
              </select>
            </div>
            <div class="col-md-4">
              <label class="form-label" style="font-size:12px;font-weight:600;">Batting Style</label>
              <select name="batting_style" class="form-select form-select-sm">
                <option value="">Select…</option>
                <option value="Right-hand" <?= old('batting_style') === 'Right-hand' ? 'selected' : '' ?>>Right-hand</option>
                <option value="Left-hand"  <?= old('batting_style') === 'Left-hand'  ? 'selected' : '' ?>>Left-hand</option>
              </select>
            </div>
            <div class="col-md-4">
              <label class="form-label" style="font-size:12px;font-weight:600;">Bowling Style</label>
              <select name="bowling_style" class="form-select form-select-sm">
                <option value="N/A">N/A</option>
                <?php foreach (['Right-arm Fast','Right-arm Medium','Right-arm Off-spin','Right-arm Leg-spin','Left-arm Fast','Left-arm Medium','Left-arm Orthodox','Left-arm Wrist-spin'] as $bs): ?>
                  <option value="<?= $bs ?>" <?= old('bowling_style') === $bs ? 'selected' : '' ?>><?= $bs ?></option>
                <?php endforeach; ?>
              </select>
            </div>
          </div>
        </div>
      </div>

      <div class="card mb-3">
        <div class="card-header">Guardian Details <span class="text-muted fw-normal" style="font-size:12px;">(required for U19 and below)</span></div>
        <div class="card-body">
          <div class="row g-3">
            <div class="col-md-6">
              <label class="form-label" style="font-size:12px;font-weight:600;">Guardian Name</label>
              <input type="text" name="guardian_name" class="form-control form-control-sm" value="<?= old('guardian_name') ?>">
            </div>
            <div class="col-md-6">
              <label class="form-label" style="font-size:12px;font-weight:600;">Guardian Phone</label>
              <input type="text" name="guardian_phone" class="form-control form-control-sm" value="<?= old('guardian_phone') ?>">
            </div>
          </div>
        </div>
      </div>

      <div class="card">
        <div class="card-header">Aadhaar Details</div>
        <div class="card-body">
          <div class="row g-3">
            <div class="col-md-5">
              <label class="form-label" style="font-size:12px;font-weight:600;">Aadhaar Number</label>
              <input type="text" name="aadhaar_number" class="form-control form-control-sm"
                value="<?= old('aadhaar_number') ?>" maxlength="12" placeholder="12-digit number">
              <div class="form-text" style="font-size:11px;">Documents can be uploaded after registration.</div>
            </div>
          </div>
        </div>
      </div>

    </div>

    <!-- Right: Photo -->
    <div class="col-lg-4">
      <div class="card mb-3">
        <div class="card-header">Player Photo</div>
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
        <div class="card-header">Registration Notes</div>
        <div class="card-body" style="font-size:12px;color:#666;line-height:1.7;">
          <ul class="ps-3 mb-0">
            <li>JSCA Player ID will be auto-generated</li>
            <li>Age category is calculated from DOB</li>
            <li>Upload Aadhaar documents after saving</li>
            <li>Guardian details mandatory for U19 &amp; below</li>
          </ul>
        </div>
      </div>
    </div>

  </div>

  <div class="d-flex gap-2 mt-3">
    <button type="submit" class="btn btn-jsca-primary">Register Player</button>
    <a href="<?= base_url('players') ?>" class="btn btn-outline-secondary">Cancel</a>
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
