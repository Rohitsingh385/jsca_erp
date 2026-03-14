<!-- app/Views/teams/edit.php -->
<div class="mb-3">
  <a href="<?= base_url('teams/view/' . $team['id']) ?>" class="text-muted" style="font-size:13px;">
    <i class="bi bi-arrow-left me-1"></i>Back to Team
  </a>
</div>

<form method="post" action="<?= base_url('teams/update/' . $team['id']) ?>" enctype="multipart/form-data">
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
                value="<?= esc($team['name']) ?>" required>
            </div>
            <div class="col-md-2">
              <label class="form-label" style="font-size:12px;font-weight:600;">Short Name</label>
              <input type="text" name="short_name" class="form-control form-control-sm"
                value="<?= esc($team['short_name']) ?>" maxlength="5">
            </div>
            <div class="col-md-3">
              <label class="form-label" style="font-size:12px;font-weight:600;">Status</label>
              <select name="status" class="form-select form-select-sm">
                <?php foreach (['Active','Inactive','Disqualified'] as $s): ?>
                  <option value="<?= $s ?>" <?= $team['status'] === $s ? 'selected' : '' ?>><?= $s ?></option>
                <?php endforeach; ?>
              </select>
            </div>
            <div class="col-md-4">
              <label class="form-label" style="font-size:12px;font-weight:600;">Category</label>
              <select name="category" class="form-select form-select-sm">
                <?php foreach (['U14','U16','U19','Senior','Masters'] as $c): ?>
                  <option value="<?= $c ?>" <?= $team['category'] === $c ? 'selected' : '' ?>><?= $c ?></option>
                <?php endforeach; ?>
              </select>
            </div>
            <div class="col-md-4">
              <label class="form-label" style="font-size:12px;font-weight:600;">District</label>
              <select name="district_id" class="form-select form-select-sm">
                <option value="">Select…</option>
                <?php foreach ($districts as $d): ?>
                  <option value="<?= $d['id'] ?>" <?= $team['district_id'] == $d['id'] ? 'selected' : '' ?>><?= esc($d['name']) ?></option>
                <?php endforeach; ?>
              </select>
            </div>
            <div class="col-md-4">
              <label class="form-label" style="font-size:12px;font-weight:600;">Tournament</label>
              <select name="tournament_id" class="form-select form-select-sm">
                <option value="">Select…</option>
                <?php foreach ($tournaments as $tr): ?>
                  <option value="<?= $tr['id'] ?>" <?= $team['tournament_id'] == $tr['id'] ? 'selected' : '' ?>><?= esc($tr['name']) ?></option>
                <?php endforeach; ?>
              </select>
            </div>
            <div class="col-md-6">
              <label class="form-label" style="font-size:12px;font-weight:600;">Home Ground</label>
              <input type="text" name="home_ground" class="form-control form-control-sm" value="<?= esc($team['home_ground']) ?>">
            </div>
            <div class="col-md-6">
              <label class="form-label" style="font-size:12px;font-weight:600;">Jersey Color</label>
              <input type="text" name="jersey_color" class="form-control form-control-sm" value="<?= esc($team['jersey_color']) ?>">
            </div>
          </div>
        </div>
      </div>

      <div class="card mb-3">
        <div class="card-header">Leadership</div>
        <div class="card-body">
          <div class="row g-3">
            <div class="col-md-6">
              <label class="form-label" style="font-size:12px;font-weight:600;">Captain</label>
              <select name="captain_id" class="form-select form-select-sm">
                <option value="">Select from squad…</option>
                <?php foreach ($players as $p): ?>
                  <option value="<?= $p['id'] ?>" <?= $team['captain_id'] == $p['id'] ? 'selected' : '' ?>>
                    <?= esc($p['full_name']) ?>
                  </option>
                <?php endforeach; ?>
              </select>
            </div>
            <div class="col-md-6">
              <label class="form-label" style="font-size:12px;font-weight:600;">Vice Captain</label>
              <select name="vice_captain_id" class="form-select form-select-sm">
                <option value="">Select from squad…</option>
                <?php foreach ($players as $p): ?>
                  <option value="<?= $p['id'] ?>" <?= $team['vice_captain_id'] == $p['id'] ? 'selected' : '' ?>>
                    <?= esc($p['full_name']) ?>
                  </option>
                <?php endforeach; ?>
              </select>
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
              <input type="text" name="manager_name" class="form-control form-control-sm" value="<?= esc($team['manager_name']) ?>">
            </div>
            <div class="col-md-4">
              <label class="form-label" style="font-size:12px;font-weight:600;">Manager Phone</label>
              <input type="text" name="manager_phone" class="form-control form-control-sm" value="<?= esc($team['manager_phone']) ?>">
            </div>
            <div class="col-md-4">
              <label class="form-label" style="font-size:12px;font-weight:600;">Manager Email</label>
              <input type="email" name="manager_email" class="form-control form-control-sm" value="<?= esc($team['manager_email']) ?>">
            </div>
          </div>
        </div>
      </div>
    </div>

    <div class="col-lg-4">
      <div class="card">
        <div class="card-header">Update Logo</div>
        <div class="card-body text-center">
          <div id="logoPreview" style="width:100px;height:100px;border-radius:8px;margin:0 auto 12px;
            overflow:hidden;background:#f8f9fa;border:2px solid #eee;">
            <?php if ($team['logo_path']): ?>
              <img src="<?= base_url('uploads/' . ltrim($team['logo_path'], 'uploads/')) ?>"
                style="width:100%;height:100%;object-fit:cover;">
            <?php else: ?>
              <div style="display:flex;align-items:center;justify-content:center;height:100%;">
                <i class="bi bi-shield text-muted" style="font-size:36px;"></i>
              </div>
            <?php endif; ?>
          </div>
          <input type="file" name="logo" id="logoInput" accept="image/*" class="form-control form-control-sm">
          <div class="form-text" style="font-size:11px;">Leave blank to keep current logo</div>
        </div>
      </div>
    </div>

  </div>

  <div class="d-flex gap-2 mt-3">
    <button type="submit" class="btn btn-jsca-primary">Save Changes</button>
    <a href="<?= base_url('teams/view/' . $team['id']) ?>" class="btn btn-outline-secondary">Cancel</a>
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
