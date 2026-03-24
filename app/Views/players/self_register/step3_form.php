<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Register Player — JSCA ERP</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css" rel="stylesheet">
  <style>
    body { background: #f0f2f5; font-family: 'Segoe UI', sans-serif; }
    .reg-wrap { max-width: 760px; margin: 40px auto; }
    .reg-header { background: #1a3a5c; color: #fff; border-radius: 12px 12px 0 0; padding: 20px 24px; }
    .step-badge { background: rgba(255,255,255,0.15); border-radius: 20px; padding: 4px 14px; font-size: 12px; display: inline-block; margin-bottom: 6px; }
    .card { border: none; border-radius: 0 0 12px 12px; box-shadow: 0 4px 20px rgba(0,0,0,0.08); }
    .section-title { font-size: 13px; font-weight: 700; text-transform: uppercase; letter-spacing: .06em; color: #1a3a5c; border-bottom: 2px solid #e8f0fe; padding-bottom: 6px; margin-bottom: 16px; }
  </style>
</head>
<body>

<div class="reg-wrap">
  <div class="reg-header">
    <div class="step-badge">Step 3 of 3</div>
    <h4 class="mb-0">🏏 Complete Your Registration</h4>
    <p class="mb-0 opacity-75" style="font-size:13px;">Registering as: <strong><?= esc($email) ?></strong></p>
  </div>

  <div class="card">
    <div class="card-body p-4">

      <?php if ($errors = session()->getFlashdata('errors')): ?>
        <div class="alert alert-danger py-2"><ul class="mb-0 ps-3"><?php foreach ($errors as $e): ?><li><?= esc($e) ?></li><?php endforeach; ?></ul></div>
      <?php endif; ?>

      <div class="alert alert-info py-2 mb-4" style="font-size:12px;">
        <i class="bi bi-info-circle me-1"></i>
        Your account will be <strong>pending verification</strong> until approved by JSCA admin. You'll receive your login credentials by email.
      </div>

      <form method="post" action="<?= base_url('player-register/submit') ?>" enctype="multipart/form-data" id="regForm" novalidate>
        <?= csrf_field() ?>

        <!-- Personal Info -->
        <div class="section-title">Personal Information</div>
        <div class="row g-3 mb-4">
          <div class="col-md-6">
            <label class="form-label fw-semibold" style="font-size:12px;">Full Name <span class="text-danger">*</span></label>
            <input type="text" name="full_name" class="form-control form-control-sm" value="<?= old('full_name') ?>" required>
          </div>
          <div class="col-md-3">
            <label class="form-label fw-semibold" style="font-size:12px;">Date of Birth <span class="text-danger">*</span></label>
            <input type="date" name="date_of_birth" class="form-control form-control-sm" value="<?= old('date_of_birth') ?>" required max="<?= date('Y-m-d', strtotime('-5 years')) ?>">
          </div>
          <div class="col-md-3">
            <label class="form-label fw-semibold" style="font-size:12px;">Gender <span class="text-danger">*</span></label>
            <select name="gender" class="form-select form-select-sm" required>
              <option value="">Select…</option>
              <option value="Male"   <?= old('gender') === 'Male'   ? 'selected' : '' ?>>Male</option>
              <option value="Female" <?= old('gender') === 'Female' ? 'selected' : '' ?>>Female</option>
              <option value="Other"  <?= old('gender') === 'Other'  ? 'selected' : '' ?>>Other</option>
            </select>
          </div>
          <div class="col-md-6">
            <label class="form-label fw-semibold" style="font-size:12px;">District <span class="text-danger">*</span></label>
            <select name="district_id" class="form-select form-select-sm" required>
              <option value="">Select district…</option>
              <?php foreach ($districts as $d): ?>
                <option value="<?= $d['id'] ?>" <?= old('district_id') == $d['id'] ? 'selected' : '' ?>><?= esc($d['name']) ?></option>
              <?php endforeach; ?>
            </select>
          </div>
          <div class="col-md-6">
            <label class="form-label fw-semibold" style="font-size:12px;">Phone <span class="text-danger">*</span></label>
            <input type="tel" name="phone" id="phone" class="form-control form-control-sm"
              value="<?= old('phone') ?>" pattern="[6-9][0-9]{9}" maxlength="10" inputmode="numeric" required>
          </div>
        </div>

        <!-- Address -->
        <div class="section-title">Address</div>
        <div class="row g-3 mb-4">
          <div class="col-12">
            <input type="text" name="address_line1" class="form-control form-control-sm" placeholder="Street / Village / Locality" value="<?= old('address_line1') ?>">
          </div>
          <div class="col-md-5">
            <input type="text" name="city" class="form-control form-control-sm" placeholder="City / Town" value="<?= old('city') ?>">
          </div>
          <div class="col-md-4">
            <input type="text" name="state" class="form-control form-control-sm" placeholder="State" value="<?= old('state', 'Jharkhand') ?>">
          </div>
          <div class="col-md-3">
            <input type="text" name="pin_code" class="form-control form-control-sm" placeholder="PIN Code" maxlength="6" inputmode="numeric" value="<?= old('pin_code') ?>">
          </div>
        </div>

        <!-- Cricket Profile -->
        <div class="section-title">Cricket Profile</div>
        <div class="row g-3 mb-4">
          <div class="col-md-4">
            <label class="form-label fw-semibold" style="font-size:12px;">Playing Role <span class="text-danger">*</span></label>
            <select name="role" class="form-select form-select-sm" required>
              <option value="">Select…</option>
              <?php foreach (['Batsman','Bowler','All-rounder','Wicket-keeper'] as $r): ?>
                <option value="<?= $r ?>" <?= old('role') === $r ? 'selected' : '' ?>><?= $r ?></option>
              <?php endforeach; ?>
            </select>
          </div>
          <div class="col-md-4">
            <label class="form-label fw-semibold" style="font-size:12px;">Batting Style</label>
            <select name="batting_style" class="form-select form-select-sm">
              <option value="">Select…</option>
              <option value="Right-hand" <?= old('batting_style') === 'Right-hand' ? 'selected' : '' ?>>Right-hand</option>
              <option value="Left-hand"  <?= old('batting_style') === 'Left-hand'  ? 'selected' : '' ?>>Left-hand</option>
            </select>
          </div>
          <div class="col-md-4">
            <label class="form-label fw-semibold" style="font-size:12px;">Bowling Style</label>
            <select name="bowling_style" class="form-select form-select-sm">
              <option value="N/A">N/A</option>
              <?php foreach (['Right-arm Fast','Right-arm Medium','Right-arm Off-spin','Right-arm Leg-spin','Left-arm Fast','Left-arm Medium','Left-arm Orthodox','Left-arm Chinaman'] as $bs): ?>
                <option value="<?= $bs ?>" <?= old('bowling_style') === $bs ? 'selected' : '' ?>><?= $bs ?></option>
              <?php endforeach; ?>
            </select>
          </div>
        </div>

        <!-- Guardian -->
        <div class="section-title">Guardian Details <span class="text-muted fw-normal">(for U19 and below)</span></div>
        <div class="row g-3 mb-4">
          <div class="col-md-6">
            <input type="text" name="guardian_name" class="form-control form-control-sm" placeholder="Guardian Name" value="<?= old('guardian_name') ?>">
          </div>
          <div class="col-md-6">
            <input type="tel" name="guardian_phone" class="form-control form-control-sm" placeholder="Guardian Phone" maxlength="10" inputmode="numeric" value="<?= old('guardian_phone') ?>">
          </div>
        </div>

        <!-- Aadhaar + Photo -->
        <div class="section-title">Identity & Photo</div>
        <div class="row g-3 mb-4">
          <div class="col-md-6">
            <label class="form-label fw-semibold" style="font-size:12px;">Aadhaar Number</label>
            <input type="text" name="aadhaar_number" id="aadhaarInput" class="form-control form-control-sm"
              value="<?= old('aadhaar_number') ?>" maxlength="12" placeholder="12-digit number" inputmode="numeric">
          </div>
          <div class="col-md-6">
            <label class="form-label fw-semibold" style="font-size:12px;">Photo</label>
            <input type="file" name="photo" class="form-control form-control-sm" accept="image/*">
          </div>
        </div>

        <button type="submit" class="btn w-100" style="background:#1a3a5c;color:#fff;">
          <i class="bi bi-person-check me-1"></i> Submit Registration
        </button>
      </form>
    </div>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
  document.getElementById('phone').addEventListener('input', function() {
    this.value = this.value.replace(/\D/g, '').slice(0, 10);
  });
  document.getElementById('aadhaarInput').addEventListener('input', function() {
    this.value = this.value.replace(/\D/g, '').slice(0, 12);
  });
  document.getElementById('regForm').addEventListener('submit', function(e) {
    if (!this.checkValidity()) { e.preventDefault(); e.stopPropagation(); }
    this.classList.add('was-validated');
  });
</script>
</body>
</html>
