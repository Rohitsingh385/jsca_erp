<!-- app/Views/players/edit.php -->
<div class="mb-3">
  <a href="<?= base_url('players/view/' . $player['id']) ?>" class="text-muted" style="font-size:13px;">
    <i class="bi bi-arrow-left me-1"></i>Back to Profile
  </a>
</div>

<form method="post" action="<?= base_url('players/update/' . $player['id']) ?>" enctype="multipart/form-data">
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
                value="<?= esc($player['full_name']) ?>" required>
            </div>
            <div class="col-md-3">
              <label class="form-label" style="font-size:12px;font-weight:600;">Date of Birth</label>
              <input type="date" name="date_of_birth" class="form-control form-control-sm"
                value="<?= esc($player['date_of_birth']) ?>" disabled>
              <div class="form-text" style="font-size:11px;">Contact admin to change DOB</div>
            </div>
            <div class="col-md-3">
              <label class="form-label" style="font-size:12px;font-weight:600;">Gender</label>
              <input type="text" class="form-control form-control-sm" value="<?= esc($player['gender']) ?>" disabled>
            </div>
            <div class="col-md-6">
              <label class="form-label" style="font-size:12px;font-weight:600;">District <span class="text-danger">*</span></label>
              <select name="district_id" class="form-select form-select-sm" required>
                <?php foreach ($districts as $d): ?>
                  <option value="<?= $d['id'] ?>" <?= $player['district_id'] == $d['id'] ? 'selected' : '' ?>>
                    <?= esc($d['name']) ?>
                  </option>
                <?php endforeach; ?>
              </select>
            </div>
            <div class="col-md-3">
              <label class="form-label" style="font-size:12px;font-weight:600;">Status</label>
              <select name="status" class="form-select form-select-sm">
                <?php foreach (['Active','Inactive','Suspended','Retired'] as $s): ?>
                  <option value="<?= $s ?>" <?= $player['status'] === $s ? 'selected' : '' ?>><?= $s ?></option>
                <?php endforeach; ?>
              </select>
            </div>
            <div class="col-md-3">
              <label class="form-label" style="font-size:12px;font-weight:600;">Selection Pool</label>
              <select name="selection_pool" class="form-select form-select-sm">
                <?php foreach (['None','District','State','National'] as $sp): ?>
                  <option value="<?= $sp ?>" <?= $player['selection_pool'] === $sp ? 'selected' : '' ?>><?= $sp ?></option>
                <?php endforeach; ?>
              </select>
            </div>
            <div class="col-md-6">
              <label class="form-label" style="font-size:12px;font-weight:600;">Phone</label>
              <input type="text" name="phone" class="form-control form-control-sm" value="<?= esc($player['phone']) ?>">
            </div>
            <div class="col-md-6">
              <label class="form-label" style="font-size:12px;font-weight:600;">Email</label>
              <input type="email" name="email" class="form-control form-control-sm" value="<?= esc($player['email']) ?>">
            </div>
            <div class="col-12">
              <label class="form-label" style="font-size:12px;font-weight:600;">Address</label>
              <textarea name="address" class="form-control form-control-sm" rows="2"><?= esc($player['address']) ?></textarea>
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
                <?php foreach (['Batsman','Bowler','All-rounder','Wicket-keeper'] as $r): ?>
                  <option value="<?= $r ?>" <?= $player['role'] === $r ? 'selected' : '' ?>><?= $r ?></option>
                <?php endforeach; ?>
              </select>
            </div>
            <div class="col-md-4">
              <label class="form-label" style="font-size:12px;font-weight:600;">Batting Style</label>
              <select name="batting_style" class="form-select form-select-sm">
                <option value="">—</option>
                <option value="Right-hand" <?= $player['batting_style'] === 'Right-hand' ? 'selected' : '' ?>>Right-hand</option>
                <option value="Left-hand"  <?= $player['batting_style'] === 'Left-hand'  ? 'selected' : '' ?>>Left-hand</option>
              </select>
            </div>
            <div class="col-md-4">
              <label class="form-label" style="font-size:12px;font-weight:600;">Bowling Style</label>
              <select name="bowling_style" class="form-select form-select-sm">
                <?php foreach (['N/A','Right-arm Fast','Right-arm Medium','Right-arm Off-spin','Right-arm Leg-spin','Left-arm Fast','Left-arm Medium','Left-arm Orthodox','Left-arm Wrist-spin'] as $bs): ?>
                  <option value="<?= $bs ?>" <?= $player['bowling_style'] === $bs ? 'selected' : '' ?>><?= $bs ?></option>
                <?php endforeach; ?>
              </select>
            </div>
          </div>
        </div>
      </div>

      <div class="card">
        <div class="card-header">Guardian Details</div>
        <div class="card-body">
          <div class="row g-3">
            <div class="col-md-6">
              <label class="form-label" style="font-size:12px;font-weight:600;">Guardian Name</label>
              <input type="text" name="guardian_name" class="form-control form-control-sm" value="<?= esc($player['guardian_name']) ?>">
            </div>
            <div class="col-md-6">
              <label class="form-label" style="font-size:12px;font-weight:600;">Guardian Phone</label>
              <input type="text" name="guardian_phone" class="form-control form-control-sm" value="<?= esc($player['guardian_phone']) ?>">
            </div>
          </div>
        </div>
      </div>

    </div>

    <!-- Right: Photo update -->
    <div class="col-lg-4">
      <div class="card">
        <div class="card-header">Update Photo</div>
        <div class="card-body text-center">
          <div id="photoPreview" style="width:120px;height:140px;border-radius:8px;margin:0 auto 12px;overflow:hidden;background:#f8f9fa;border:2px solid #eee;">
            <?php if ($player['photo_path']): ?>
              <img src="<?= base_url('uploads/' . ltrim($player['photo_path'], 'uploads/')) ?>"
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
    <a href="<?= base_url('players/view/' . $player['id']) ?>" class="btn btn-outline-secondary">Cancel</a>
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
