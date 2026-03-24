<?php
$isEdit   = !empty($official);
$action   = $isEdit ? base_url('officials/update/' . $official['id']) : base_url('officials/store');
$old      = fn(string $k, $default = '') => old($k, $isEdit ? ($official[$k] ?? $default) : $default);
?>

<div class="d-flex justify-content-between align-items-center mb-4">
  <h4 class="mb-0 fw-bold"><?= $isEdit ? 'Edit Official' : 'Add Official' ?></h4>
  <a href="<?= $isEdit ? base_url('officials/view/' . $official['id']) : base_url('officials') ?>" class="btn btn-outline-secondary btn-sm">
    <i class="bi bi-arrow-left me-1"></i> Back
  </a>
</div>

<form method="post" action="<?= $action ?>" enctype="multipart/form-data">
  <?= csrf_field() ?>

  <div class="row g-4">
    <!-- Left column -->
    <div class="col-lg-8">

      <!-- Basic Info -->
      <div class="card mb-4">
        <div class="card-header">Basic Information</div>
        <div class="card-body">
          <div class="row g-3">
            <div class="col-md-6">
              <label class="form-label">Full Name <span class="text-danger">*</span></label>
              <input type="text" name="full_name" class="form-control" value="<?= esc($old('full_name')) ?>" required>
            </div>
            <div class="col-md-6">
              <label class="form-label">Official Type <span class="text-danger">*</span></label>
              <select name="official_type_id" class="form-select" required>
                <option value="">— Select Type —</option>
                <?php foreach ($officialTypes as $t): ?>
                  <option value="<?= $t['id'] ?>" <?= $old('official_type_id') == $t['id'] ? 'selected' : '' ?>>
                    <?= esc($t['name']) ?> (<?= esc($t['prefix']) ?>)
                  </option>
                <?php endforeach; ?>
              </select>
            </div>
            <div class="col-md-6">
              <label class="form-label">Gender <span class="text-danger">*</span></label>
              <select name="gender" class="form-select" required>
                <?php foreach (['Male','Female','Other'] as $g): ?>
                  <option value="<?= $g ?>" <?= $old('gender', 'Male') === $g ? 'selected' : '' ?>><?= $g ?></option>
                <?php endforeach; ?>
              </select>
            </div>
            <div class="col-md-6">
              <label class="form-label">Date of Birth</label>
              <input type="date" name="dob" class="form-control" value="<?= esc($old('dob')) ?>">
            </div>
            <div class="col-md-6">
              <label class="form-label">District <span class="text-danger">*</span></label>
              <select name="district_id" class="form-select" required>
                <option value="">— Select District —</option>
                <?php foreach ($districts as $d): ?>
                  <option value="<?= $d['id'] ?>" <?= $old('district_id') == $d['id'] ? 'selected' : '' ?>><?= esc($d['name']) ?></option>
                <?php endforeach; ?>
              </select>
            </div>
            <div class="col-md-6">
              <label class="form-label">Experience (years)</label>
              <input type="number" name="experience_years" class="form-control" min="0" max="60" value="<?= esc($old('experience_years')) ?>">
            </div>
            <div class="col-md-6">
              <label class="form-label">Email</label>
              <input type="email" name="email" class="form-control" value="<?= esc($old('email')) ?>">
              <div class="form-text">Login credentials will be sent to this email.</div>
            </div>
            <div class="col-md-6">
              <label class="form-label">Phone</label>
              <input type="text" name="phone" class="form-control" value="<?= esc($old('phone')) ?>">
            </div>
            <div class="col-12">
              <label class="form-label">Address</label>
              <textarea name="address" class="form-control" rows="2"><?= esc($old('address')) ?></textarea>
            </div>
            <?php if ($isEdit): ?>
              <div class="col-md-4">
                <label class="form-label">Status</label>
                <select name="status" class="form-select">
                  <option value="Active"   <?= $old('status') === 'Active'   ? 'selected' : '' ?>>Active</option>
                  <option value="Inactive" <?= $old('status') === 'Inactive' ? 'selected' : '' ?>>Inactive</option>
                </select>
              </div>
            <?php endif; ?>
          </div>
        </div>
      </div>

      <!-- Certifications -->
      <div class="card mb-4">
        <div class="card-header d-flex justify-content-between align-items-center">
          Certifications
          <button type="button" class="btn btn-sm btn-outline-primary" id="addCertBtn">
            <i class="bi bi-plus"></i> Add
          </button>
        </div>
        <div class="card-body">
          <div id="certRows">
            <?php if (!empty($certs)): ?>
              <?php foreach ($certs as $c): ?>
                <div class="cert-row row g-2 mb-2 align-items-end">
                  <div class="col-md-4">
                    <input type="text" name="cert_name[]" class="form-control form-control-sm" placeholder="Certification name" value="<?= esc($c['certification_name']) ?>">
                  </div>
                  <div class="col-md-3">
                    <input type="text" name="cert_body[]" class="form-control form-control-sm" placeholder="Body (ICC, BCCI…)" value="<?= esc($c['body'] ?? '') ?>">
                  </div>
                  <div class="col-md-2">
                    <input type="text" name="cert_level[]" class="form-control form-control-sm" placeholder="Level" value="<?= esc($c['level'] ?? '') ?>">
                  </div>
                  <div class="col-md-2">
                    <input type="date" name="cert_date[]" class="form-control form-control-sm" value="<?= esc($c['certified_date'] ?? '') ?>">
                  </div>
                  <div class="col-md-1">
                    <button type="button" class="btn btn-sm btn-outline-danger remove-cert"><i class="bi bi-trash"></i></button>
                  </div>
                </div>
              <?php endforeach; ?>
            <?php else: ?>
              <p class="text-muted small mb-0" id="noCertMsg">No certifications added yet.</p>
            <?php endif; ?>
          </div>
        </div>
      </div>

    </div>

    <!-- Right column -->
    <div class="col-lg-4">
      <div class="card">
        <div class="card-header">Profile Photo</div>
        <div class="card-body text-center">
          <?php if ($isEdit && !empty($official['profile_photo'])): ?>
            <img src="<?= base_url($official['profile_photo']) ?>" class="rounded-circle mb-3" width="100" height="100" style="object-fit:cover;">
          <?php else: ?>
            <div class="avatar-circle mx-auto mb-3" style="width:80px;height:80px;font-size:28px;">
              <?= $isEdit ? strtoupper(substr($official['full_name'], 0, 1)) : '?' ?>
            </div>
          <?php endif; ?>
          <input type="file" name="profile_photo" class="form-control form-control-sm" accept="image/*">
        </div>
      </div>
    </div>
  </div>

  <div class="d-flex gap-2 mt-2">
    <button type="submit" class="btn btn-jsca-primary">
      <i class="bi bi-check-circle me-1"></i> <?= $isEdit ? 'Update Official' : 'Register Official' ?>
    </button>
    <a href="<?= base_url('officials') ?>" class="btn btn-outline-secondary">Cancel</a>
  </div>
</form>

<?= $this->section('scripts') ?>
<script>
const certRowTemplate = `
  <div class="cert-row row g-2 mb-2 align-items-end">
    <div class="col-md-4"><input type="text" name="cert_name[]" class="form-control form-control-sm" placeholder="Certification name"></div>
    <div class="col-md-3"><input type="text" name="cert_body[]" class="form-control form-control-sm" placeholder="Body (ICC, BCCI…)"></div>
    <div class="col-md-2"><input type="text" name="cert_level[]" class="form-control form-control-sm" placeholder="Level"></div>
    <div class="col-md-2"><input type="date" name="cert_date[]" class="form-control form-control-sm"></div>
    <div class="col-md-1"><button type="button" class="btn btn-sm btn-outline-danger remove-cert"><i class="bi bi-trash"></i></button></div>
  </div>`;

document.getElementById('addCertBtn').addEventListener('click', () => {
  document.getElementById('noCertMsg')?.remove();
  document.getElementById('certRows').insertAdjacentHTML('beforeend', certRowTemplate);
});

document.getElementById('certRows').addEventListener('click', e => {
  if (e.target.closest('.remove-cert')) {
    e.target.closest('.cert-row').remove();
  }
});
</script>
<?= $this->endSection() ?>
