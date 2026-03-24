<?php
$isEdit = !empty($tournament);
$action = $isEdit ? base_url('tournaments/update/' . $tournament['id']) : base_url('tournaments/store');
$old    = fn(string $k, $default = '') => old($k, $isEdit ? ($tournament[$k] ?? $default) : $default);
?>

<div class="d-flex justify-content-between align-items-center mb-4">
  <h4 class="mb-0 fw-bold"><?= $isEdit ? 'Edit Tournament' : 'Create Tournament' ?></h4>
  <a href="<?= $isEdit ? base_url('tournaments/view/' . $tournament['id']) : base_url('tournaments') ?>" class="btn btn-outline-secondary btn-sm">
    <i class="bi bi-arrow-left me-1"></i> Back
  </a>
</div>

<form method="post" action="<?= $action ?>" enctype="multipart/form-data">
  <?= csrf_field() ?>

  <div class="row g-4">
    <div class="col-lg-8">

      <!-- Basic Info -->
      <div class="card mb-4">
        <div class="card-header">Basic Information</div>
        <div class="card-body">
          <div class="row g-3">
            <div class="col-md-8">
              <label class="form-label">Tournament Name <span class="text-danger">*</span></label>
              <input type="text" name="name" class="form-control" value="<?= esc($old('name')) ?>" required>
            </div>
            <div class="col-md-4">
              <label class="form-label">Short Name</label>
              <input type="text" name="short_name" class="form-control" value="<?= esc($old('short_name')) ?>" maxlength="20" placeholder="e.g. JSCA U19">
            </div>
            <div class="col-md-4">
              <label class="form-label">Season <span class="text-danger">*</span></label>
              <input type="text" name="season" class="form-control" value="<?= esc($old('season', date('Y') . '-' . (date('Y') + 1))) ?>" placeholder="2025-26" required>
            </div>
            <div class="col-md-4">
              <label class="form-label">Edition</label>
              <input type="text" name="edition" class="form-control" value="<?= esc($old('edition')) ?>" placeholder="e.g. 5th Edition">
            </div>
            <div class="col-md-4">
              <label class="form-label">Gender <span class="text-danger">*</span></label>
              <select name="gender" class="form-select" required>
                <?php foreach (['Male','Female','Mixed'] as $g): ?>
                  <option value="<?= $g ?>" <?= $old('gender', 'Male') === $g ? 'selected' : '' ?>><?= $g ?></option>
                <?php endforeach; ?>
              </select>
            </div>
            <div class="col-md-4">
              <label class="form-label">Age Category <span class="text-danger">*</span></label>
              <select name="age_category" class="form-select" required>
                <option value="">— Select —</option>
                <?php foreach (['U14','U16','U19','Senior','Masters','Women'] as $c): ?>
                  <option value="<?= $c ?>" <?= $old('age_category') === $c ? 'selected' : '' ?>><?= $c ?></option>
                <?php endforeach; ?>
              </select>
            </div>
            <div class="col-md-4">
              <label class="form-label">Format <span class="text-danger">*</span></label>
              <select name="format" class="form-select" required id="formatSelect">
                <option value="">— Select —</option>
                <?php foreach (['T10','T20','ODI-40','ODI-50','Test','Custom'] as $f): ?>
                  <option value="<?= $f ?>" <?= $old('format') === $f ? 'selected' : '' ?>><?= $f ?></option>
                <?php endforeach; ?>
              </select>
            </div>
            <div class="col-md-4">
              <label class="form-label">Overs</label>
              <input type="number" name="overs" id="oversInput" class="form-control" min="1" max="100" value="<?= esc($old('overs', '20')) ?>">
            </div>
            <div class="col-md-4">
              <label class="form-label">Structure <span class="text-danger">*</span></label>
              <select name="structure" class="form-select" required>
                <option value="">— Select —</option>
                <?php foreach (['Round Robin','Knockout','Group+Knockout','League+Playoffs','Zonal'] as $s): ?>
                  <option value="<?= $s ?>" <?= $old('structure') === $s ? 'selected' : '' ?>><?= $s ?></option>
                <?php endforeach; ?>
              </select>
            </div>
            <div class="col-md-4 d-flex align-items-end">
              <div class="form-check mb-2">
                <input class="form-check-input" type="checkbox" name="is_zonal" id="isZonal" value="1"
                  <?= $old('is_zonal', 0) ? 'checked' : '' ?>>
                <label class="form-check-label" for="isZonal">Zonal Tournament</label>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Schedule -->
      <div class="card mb-4">
        <div class="card-header">Schedule & Venue</div>
        <div class="card-body">
          <div class="row g-3">
            <div class="col-md-4">
              <label class="form-label">Start Date</label>
              <input type="date" name="start_date" class="form-control" value="<?= esc($old('start_date')) ?>">
            </div>
            <div class="col-md-4">
              <label class="form-label">End Date</label>
              <input type="date" name="end_date" class="form-control" value="<?= esc($old('end_date')) ?>">
            </div>
            <div class="col-md-4">
              <label class="form-label">Registration Deadline</label>
              <input type="date" name="registration_deadline" class="form-control" value="<?= esc($old('registration_deadline')) ?>">
            </div>
            <div class="col-md-6">
              <label class="form-label">Primary Venue</label>
              <select name="venue_id" class="form-select">
                <option value="">— No fixed venue —</option>
                <?php foreach ($venues as $v): ?>
                  <option value="<?= $v['id'] ?>" <?= $old('venue_id') == $v['id'] ? 'selected' : '' ?>><?= esc($v['name']) ?></option>
                <?php endforeach; ?>
              </select>
            </div>
            <div class="col-md-6">
              <label class="form-label">Max Teams</label>
              <input type="number" name="max_teams" class="form-control" min="2" value="<?= esc($old('max_teams')) ?>">
            </div>
          </div>
        </div>
      </div>

      <!-- Prize Money -->
      <div class="card mb-4">
        <div class="card-header">Prize Money</div>
        <div class="card-body">
          <div class="row g-3">
            <div class="col-md-4">
              <label class="form-label">Total Prize Pool (₹)</label>
              <input type="number" id="prizePool" name="prize_pool" class="form-control" min="0" step="1" value="<?= esc($old('prize_pool', '')) ?>" placeholder="0">
            </div>
            <div class="col-md-4">
              <label class="form-label">Winner Prize (₹)</label>
              <input type="number" id="winnerPrize" name="winner_prize" class="form-control" min="0" step="1" value="<?= esc($old('winner_prize', '')) ?>" placeholder="0">
            </div>
            <div class="col-md-4">
              <label class="form-label">Runner-up Prize (₹)</label>
              <input type="number" id="runnerPrize" name="runner_prize" class="form-control" min="0" step="1" value="<?= esc($old('runner_prize', '')) ?>" placeholder="0">
            </div>
          </div>
        </div>
      </div>

      <!-- Organizer -->
      <div class="card mb-4">
        <div class="card-header">Organizer Details</div>
        <div class="card-body">
          <div class="row g-3">
            <div class="col-md-4">
              <label class="form-label">Organizer Name</label>
              <input type="text" name="organizer_name" class="form-control" value="<?= esc($old('organizer_name')) ?>">
            </div>
            <div class="col-md-4">
              <label class="form-label">Organizer Phone</label>
              <input type="text" name="organizer_phone" class="form-control" value="<?= esc($old('organizer_phone')) ?>">
            </div>
            <div class="col-md-4">
              <label class="form-label">Organizer Email</label>
              <input type="email" name="organizer_email" class="form-control" value="<?= esc($old('organizer_email')) ?>">
            </div>
          </div>
        </div>
      </div>

      <!-- Description & Rules -->
      <div class="card mb-4">
        <div class="card-header">Description & Rules</div>
        <div class="card-body">
          <div class="row g-3">
            <div class="col-12">
              <label class="form-label">Description</label>
              <textarea name="description" class="form-control" rows="3"><?= esc($old('description')) ?></textarea>
            </div>
            <div class="col-12">
              <label class="form-label">Rules & Regulations</label>
              <textarea name="rules" class="form-control" rows="4"><?= esc($old('rules')) ?></textarea>
            </div>
          </div>
        </div>
      </div>

      <?php if ($isEdit): ?>
        <div class="card mb-4">
          <div class="card-header">Status</div>
          <div class="card-body">
            <select name="status" class="form-select">
              <?php foreach (['Draft','Registration','Fixture Ready','Ongoing','Completed','Cancelled'] as $s): ?>
                <option value="<?= $s ?>" <?= $old('status', 'Draft') === $s ? 'selected' : '' ?>><?= $s ?></option>
              <?php endforeach; ?>
            </select>
          </div>
        </div>
      <?php endif; ?>

    </div>

    <!-- Right column -->
    <div class="col-lg-4">
      <div class="card">
        <div class="card-header">Banner Image</div>
        <div class="card-body text-center">
          <?php if ($isEdit && !empty($tournament['banner_path'])): ?>
            <img src="<?= base_url($tournament['banner_path']) ?>" class="img-fluid rounded mb-3" style="max-height:150px;object-fit:cover;">
          <?php else: ?>
            <div class="text-muted mb-3" style="font-size:48px;"><i class="bi bi-trophy"></i></div>
          <?php endif; ?>
          <input type="file" name="banner" class="form-control form-control-sm" accept="image/*">
          <div class="form-text">Optional. JPG or PNG.</div>
        </div>
      </div>
    </div>
  </div>

  <div class="d-flex gap-2 mt-2">
    <button type="submit" class="btn btn-jsca-primary">
      <i class="bi bi-check-circle me-1"></i> <?= $isEdit ? 'Update Tournament' : 'Create Tournament' ?>
    </button>
    <a href="<?= base_url('tournaments') ?>" class="btn btn-outline-secondary">Cancel</a>
  </div>
</form>

<?= $this->section('scripts') ?>
<script>
const oversMap = { T10: 10, T20: 20, 'ODI-40': 40, 'ODI-50': 50, Test: 5, Custom: null };
document.getElementById('formatSelect').addEventListener('change', function () {
  const val = oversMap[this.value];
  if (val !== null && val !== undefined) document.getElementById('oversInput').value = val;
});

const prizePool   = document.getElementById('prizePool');
const winnerPrize = document.getElementById('winnerPrize');
const runnerPrize = document.getElementById('runnerPrize');

function calcRunner() {
  const pool   = parseFloat(prizePool.value)   || 0;
  const winner = parseFloat(winnerPrize.value) || 0;
  if (pool > 0 && winner >= 0 && pool >= winner) {
    runnerPrize.value = pool - winner;
  }
}

prizePool.addEventListener('input', calcRunner);
winnerPrize.addEventListener('input', calcRunner);
</script>
<?= $this->endSection() ?>
