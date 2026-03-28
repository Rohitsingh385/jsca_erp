<?php
$isEdit = !empty($team);
$action = $isEdit ? base_url('teams/update/' . $team['id']) : base_url('teams/store');
?>

<div class="d-flex justify-content-between align-items-center mb-4">
  <h4 class="mb-0 fw-bold"><?= $isEdit ? 'Edit Team' : 'Register Team' ?></h4>
  <a href="<?= $isEdit ? base_url('teams/view/' . $team['id']) : base_url('teams') ?>" class="btn btn-outline-secondary btn-sm">
    <i class="bi bi-arrow-left me-1"></i> Back
  </a>
</div>

<form method="post" action="<?= $action ?>">
  <?= csrf_field() ?>

  <div class="card mb-4">
    <div class="card-header">Team Details</div>
    <div class="card-body">
      <div class="row g-3">
        <div class="col-md-6">
          <label class="form-label">Team Name <span class="text-danger">*</span></label>
          <input type="text" name="name" class="form-control"
            value="<?= esc(old('name', $team['name'] ?? '')) ?>" required placeholder="e.g. Ranchi District XI">
        </div>
        <div class="col-md-3">
          <label class="form-label">District <span class="text-danger">*</span></label>
          <select name="district_id" class="form-select" <?= $isEdit ? 'disabled' : 'required' ?>>
            <option value="">— Select —</option>
            <?php foreach ($districts as $d): ?>
              <option value="<?= $d['id'] ?>"
                <?= old('district_id', $team['district_id'] ?? '') == $d['id'] ? 'selected' : '' ?>>
                <?= esc($d['name']) ?>
              </option>
            <?php endforeach; ?>
          </select>
          <?php if ($isEdit): ?>
            <input type="hidden" name="district_id" value="<?= $team['district_id'] ?>">
            <div class="form-text">District cannot be changed after registration.</div>
          <?php endif; ?>
        </div>
        <div class="col-md-3">
          <label class="form-label">Zone</label>
          <select name="zone" class="form-select">
            <?php foreach (['None', 'North', 'South', 'East', 'West', 'Central'] as $z): ?>
              <option value="<?= $z ?>" <?= old('zone', $team['zone'] ?? 'None') === $z ? 'selected' : '' ?>><?= $z ?></option>
            <?php endforeach; ?>
          </select>
        </div>

        <?php if (!$isEdit): ?>
          <div class="col-md-6">
            <label class="form-label">Tournament <span class="text-danger">*</span></label>
            <select name="tournament_id" class="form-select" required>
              <option value="">— Select —</option>
              <?php foreach ($tournaments as $tr): ?>
                <option value="<?= $tr['id'] ?>" <?= old('tournament_id') == $tr['id'] ? 'selected' : '' ?>>
                  <?= esc($tr['name']) ?> (<?= esc($tr['age_category']) ?> · <?= esc($tr['type'] ?? '') ?> · <?= esc($tr['status']) ?>)
                </option>
              <?php endforeach; ?>
            </select>
            <?php if (empty($tournaments)): ?>
              <div class="form-text text-warning">No tournaments are currently open for registration.</div>
            <?php endif; ?>
          </div>
        <?php endif; ?>

        <?php if ($isEdit): ?>
          <div class="col-md-3">
            <label class="form-label">Status <span class="text-danger">*</span></label>
            <select name="status" class="form-select" required>
              <?php foreach (['Registered', 'Confirmed', 'Withdrawn'] as $s): ?>
                <option value="<?= $s ?>" <?= old('status', $team['status'] ?? 'Registered') === $s ? 'selected' : '' ?>><?= $s ?></option>
              <?php endforeach; ?>
            </select>
          </div>
        <?php endif; ?>
      </div>
    </div>
  </div>

  <div class="card mb-4">
    <div class="card-header">Team Manager <span class="text-danger">*</span></div>
    <div class="card-body">
      <div class="row g-3">
        <div class="col-md-6">
          <label class="form-label">Manager Name <span class="text-danger">*</span></label>
          <input type="text" name="manager_name" class="form-control"
            value="<?= esc(old('manager_name', $team['manager_name'] ?? '')) ?>" required
            placeholder="Full name of team manager">
        </div>
        <div class="col-md-6">
          <label class="form-label">Manager Phone <span class="text-danger">*</span></label>
          <input type="text" name="manager_phone" class="form-control"
            value="<?= esc(old('manager_phone', $team['manager_phone'] ?? '')) ?>" required
            placeholder="10-digit mobile number">
        </div>
      </div>
    </div>
  </div>

  <div class="d-flex gap-2">
    <button type="submit" class="btn btn-jsca-primary">
      <i class="bi bi-check-circle me-1"></i> <?= $isEdit ? 'Save Changes' : 'Register Team' ?>
    </button>
    <a href="<?= base_url('teams') ?>" class="btn btn-outline-secondary">Cancel</a>
  </div>
</form>
