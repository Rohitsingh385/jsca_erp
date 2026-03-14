<!-- app/Views/teams/create.php -->
<div class="mb-3">
  <a href="<?= base_url('teams') ?>" class="text-muted" style="font-size:13px;">
    <i class="bi bi-arrow-left me-1"></i>Back to Teams
  </a>
</div>

<form method="post" action="<?= base_url('teams/store') ?>" enctype="multipart/form-data">
  <?= csrf_field() ?>

  <div class="row g-3">

    <div class="col-lg-8">
      <div class="card mb-3">
        <div class="card-header">Team Information</div>
        <div class="card-body">
          <div class="row g-3">
            <div class="col-md-7">
              <label class="form-label" style="font-size:12px;font-weight:600;">Team Name <span class="text-danger">*</span></label>
              <input type="text" name="name" class="form-control form-control-sm"
                value="<?= old('name') ?>" required placeholder="e.g. Ranchi District XI">
            </div>
            <div class="col-md-2">
              <label class="form-label" style="font-size:12px;font-weight:600;">Short Name</label>
              <input type="text" name="short_name" class="form-control form-control-sm"
                value="<?= old('short_name') ?>" maxlength="5" placeholder="RAN">
            </div>
            <div class="col-md-3">
              <label class="form-label" style="font-size:12px;font-weight:600;">Category <span class="text-danger">*</span></label>
              <select name="category" class="form-select form-select-sm" required>
                <?php foreach (['U14','U16','U19','Senior','Masters'] as $c): ?>
                  <option value="<?= $c ?>" <?= old('category') === $c ? 'selected' : '' ?>><?= $c ?></option>
                <?php endforeach; ?>
              </select>
            </div>
            <div class="col-md-6">
              <label class="form-label" style="font-size:12px;font-weight:600;">District</label>
              <select name="district_id" class="form-select form-select-sm">
                <option value="">Select district…</option>
                <?php foreach ($districts as $d): ?>
                  <option value="<?= $d['id'] ?>" <?= old('district_id') == $d['id'] ? 'selected' : '' ?>><?= esc($d['name']) ?></option>
                <?php endforeach; ?>
              </select>
            </div>
            <div class="col-md-6">
              <label class="form-label" style="font-size:12px;font-weight:600;">Tournament</label>
              <select name="tournament_id" class="form-select form-select-sm">
                <option value="">Select tournament…</option>
                <?php foreach ($tournaments as $tr): ?>
                  <option value="<?= $tr['id'] ?>" <?= old('tournament_id') == $tr['id'] ? 'selected' : '' ?>><?= esc($tr['name']) ?></option>
                <?php endforeach; ?>
              </select>
            </div>
            <div class="col-md-6">
              <label class="form-label" style="font-size:12px;font-weight:600;">Home Ground</label>
              <input type="text" name="home_ground" class="form-control form-control-sm"
                value="<?= old('home_ground') ?>" placeholder="Stadium / Ground name">
            </div>
            <div class="col-md-6">
              <label class="form-label" style="font-size:12px;font-weight:600;">Jersey Color</label>
              <input type="text" name="jersey_color" class="form-control form-control-sm"
                value="<?= old('jersey_color') ?>" placeholder="e.g. Blue & Gold">
            </div>
          </div>
        </div>
      </div>

      <div class="card">
        <div class="card-header">Team Manager</div>
        <div class="card-body">
          <div class="row g-3">
            <div class="col-md-4">
              <label class="form-label" style="font-size:12px;font-weight:600;">Manager Name</label>
              <input type="text" name="manager_name" class="form-control form-control-sm" value="<?= old('manager_name') ?>">
            </div>
            <div class="col-md-4">
              <label class="form-label" style="font-size:12px;font-weight:600;">Manager Phone</label>
              <input type="text" name="manager_phone" class="form-control form-control-sm" value="<?= old('manager_phone') ?>">
            </div>
            <div class="col-md-4">
              <label class="form-label" style="font-size:12px;font-weight:600;">Manager Email</label>
              <input type="email" name="manager_email" class="form-control form-control-sm" value="<?= old('manager_email') ?>">
            </div>
          </div>
        </div>
      </div>
    </div>

    <div class="col-lg-4">
      <div class="card mb-3">
        <div class="card-header">Team Logo</div>
        <div class="card-body text-center">
          <div id="logoPreview" style="width:100px;height:100px;border:2px dashed #ddd;border-radius:8px;
            margin:0 auto 12px;display:flex;align-items:center;justify-content:center;overflow:hidden;background:#f8f9fa;">
            <i class="bi bi-shield text-muted" style="font-size:36px;"></i>
          </div>
          <input type="file" name="logo" id="logoInput" accept="image/*" class="form-control form-control-sm">
          <div class="form-text" style="font-size:11px;">PNG or JPG, square preferred</div>
        </div>
      </div>
      <div class="card">
        <div class="card-header">Notes</div>
        <div class="card-body" style="font-size:12px;color:#666;line-height:1.8;">
          <ul class="ps-3 mb-0">
            <li>JSCA Team ID auto-generated</li>
            <li>Add players &amp; coaches after saving</li>
            <li>Upload registration docs after saving</li>
            <li>Captain can be set after adding players</li>
          </ul>
        </div>
      </div>
    </div>

  </div>

  <div class="d-flex gap-2 mt-3">
    <button type="submit" class="btn btn-jsca-primary">Create Team</button>
    <a href="<?= base_url('teams') ?>" class="btn btn-outline-secondary">Cancel</a>
  </div>

</form>

<script>
  document.getElementById('logoInput').addEventListener('change', function() {
    const file = this.files[0];
    if (!file) return;
    const reader = new FileReader();
    reader.onload = e => {
      document.getElementById('logoPreview').innerHTML =
        `<img src="${e.target.result}" style="width:100%;height:100%;object-fit:cover;">`;
    };
    reader.readAsDataURL(file);
  });
</script>
