<?php // app/Views/fixtures/form.php
$isEdit = !empty($fixture);
$action = $isEdit ? base_url('fixtures/update/' . $fixture['id']) : base_url('fixtures/store');
?>

<div class="d-flex justify-content-between align-items-center mb-4">
  <h4 class="mb-0 fw-bold"><?= $isEdit ? 'Edit Fixture' : 'Create Fixture' ?></h4>
  <a href="<?= base_url('fixtures' . ($tournamentId ? '?tournament_id=' . $tournamentId : '')) ?>" class="btn btn-outline-secondary btn-sm">
    <i class="bi bi-arrow-left me-1"></i> Back
  </a>
</div>

<!-- Inline error box -->
<div id="errorBox" class="alert alert-danger alert-dismissible fade show d-none mb-3">
  <strong><i class="bi bi-exclamation-triangle me-2"></i>Please fix the following:</strong>
  <ul id="errorList" class="mb-0 mt-2"></ul>
  <button type="button" class="btn-close" onclick="clearErrors()"></button>
</div>

<?php if ($errors = session()->getFlashdata('errors')): ?>
<div class="alert alert-danger alert-dismissible fade show mb-3">
  <strong><i class="bi bi-exclamation-triangle me-2"></i>Please fix the following:</strong>
  <ul class="mb-0 mt-2">
    <?php foreach ((array)$errors as $e): ?>
      <li><?= esc($e) ?></li>
    <?php endforeach; ?>
  </ul>
  <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
<?php endif; ?>
<?php if ($error = session()->getFlashdata('error')): ?>
<div class="alert alert-danger alert-dismissible fade show mb-3">
  <i class="bi bi-exclamation-circle me-2"></i><?= esc($error) ?>
  <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
<?php endif; ?>

<form method="post" action="<?= $action ?>" id="fixtureForm">
  <?= csrf_field() ?>

  <div class="row g-4">
    <div class="col-lg-8">

      <!-- Match Details -->
      <div class="card mb-4">
        <div class="card-header">Match Details</div>
        <div class="card-body">
          <div class="row g-3">

            <?php if (!$isEdit): ?>
            <div class="col-12">
              <label class="form-label fw-semibold">Tournament <span class="text-danger">*</span></label>
              <select name="tournament_id" id="tournamentSelect" class="form-select" required>
                <option value="">— Select Tournament —</option>
                <?php foreach ($tournaments as $t): ?>
                  <option value="<?= $t['id'] ?>" <?= old('tournament_id', $tournamentId) == $t['id'] ? 'selected' : '' ?>>
                    <?= esc($t['name']) ?> (<?= esc($t['age_category']) ?>)
                  </option>
                <?php endforeach; ?>
              </select>
            </div>
            <?php else: ?>
            <div class="col-12">
              <label class="form-label fw-semibold">Tournament</label>
              <input type="text" class="form-control" value="<?= esc($tournament['name']) ?>" disabled>
            </div>
            <?php endif; ?>

            <div class="col-md-6">
              <label class="form-label fw-semibold">Team A <span class="text-danger">*</span></label>
              <select name="team_a_id" id="teamASelect" class="form-select" required>
                <option value="">— Select Team —</option>
                <?php foreach ($teams as $tm): ?>
                  <option value="<?= $tm['id'] ?>" <?= old('team_a_id', $fixture['team_a_id'] ?? '') == $tm['id'] ? 'selected' : '' ?>>
                    <?= esc($tm['name']) ?>
                  </option>
                <?php endforeach; ?>
              </select>
            </div>

            <div class="col-md-6">
              <label class="form-label fw-semibold">Team B <span class="text-danger">*</span></label>
              <select name="team_b_id" id="teamBSelect" class="form-select" required>
                <option value="">— Select Team —</option>
                <?php foreach ($teams as $tm): ?>
                  <option value="<?= $tm['id'] ?>" <?= old('team_b_id', $fixture['team_b_id'] ?? '') == $tm['id'] ? 'selected' : '' ?>>
                    <?= esc($tm['name']) ?>
                  </option>
                <?php endforeach; ?>
              </select>
            </div>

            <div class="col-md-4">
              <label class="form-label fw-semibold">Match Number</label>
              <input type="text" name="match_number" class="form-control"
                value="<?= esc(old('match_number', $fixture['match_number'] ?? '')) ?>"
                placeholder="e.g. M01, Final">
              <div class="form-text">Leave blank to auto-assign.</div>
            </div>

            <div class="col-md-4">
              <label class="form-label fw-semibold">Stage</label>
              <select name="stage" class="form-select">
                <?php
                  $stageOptions = ['League','Zonal League','Zonal Final','Quarter Final','Semi Final','Qualifier 1','Qualifier 2','Eliminator','Final'];
                  $currentStage = old('stage', $fixture['stage'] ?? 'League');
                ?>
                <?php foreach ($stageOptions as $s): ?>
                  <option value="<?= $s ?>" <?= $currentStage === $s ? 'selected' : '' ?>><?= $s ?></option>
                <?php endforeach; ?>
              </select>
            </div>

            <?php if ($isEdit): ?>
            <div class="col-md-4">
              <label class="form-label fw-semibold">Status</label>
              <select name="status" class="form-select">
                <?php foreach (['Scheduled','Live','Completed','Abandoned','Postponed'] as $s): ?>
                  <option value="<?= $s ?>" <?= old('status', $fixture['status'] ?? 'Scheduled') === $s ? 'selected' : '' ?>><?= $s ?></option>
                <?php endforeach; ?>
              </select>
            </div>
            <?php endif; ?>

          </div>
        </div>
      </div>

      <!-- Schedule & Venue -->
      <div class="card mb-4">
        <div class="card-header">Schedule & Venue</div>
        <div class="card-body">
          <div class="row g-3">
            <div class="col-md-4">
              <label class="form-label fw-semibold">Date <span class="text-danger">*</span></label>
              <input type="date" name="match_date" class="form-control" required
                value="<?= esc(old('match_date', $fixture['match_date'] ?? '')) ?>">
            </div>
            <div class="col-md-4">
              <label class="form-label fw-semibold">Time <span class="text-danger">*</span></label>
              <input type="time" name="match_time" class="form-control" required
                value="<?= esc(old('match_time', $fixture['match_time'] ?? '09:00')) ?>">
            </div>
            <div class="col-md-4 d-flex align-items-end">
              <div class="form-check mb-2">
                <input class="form-check-input" type="checkbox" name="is_day_night" id="isDayNight" value="1"
                  <?= old('is_day_night', $fixture['is_day_night'] ?? 0) ? 'checked' : '' ?>>
                <label class="form-check-label" for="isDayNight">Day/Night</label>
              </div>
            </div>
            <div class="col-12">
              <label class="form-label fw-semibold">Venue <span class="text-danger">*</span></label>
              <select name="venue_id" id="venueSelect" class="form-select" required>
                <option value="">— Select Venue —</option>
                <?php foreach ($venues as $v): ?>
                  <option value="<?= $v['id'] ?>" <?= old('venue_id', $fixture['venue_id'] ?? '') == $v['id'] ? 'selected' : '' ?>>
                    <?= esc($v['name']) ?><?= !empty($v['district_name']) ? ' — ' . esc($v['district_name']) : '' ?>
                  </option>
                <?php endforeach; ?>
              </select>
            </div>
          </div>
        </div>
      </div>

      <!-- Officials -->
      <div class="card mb-4">
        <div class="card-header">Officials <span class="text-muted fw-normal small">(optional)</span></div>
        <div class="card-body">
          <div class="row g-3">

            <div class="col-md-6">
              <label class="form-label fw-semibold">Umpire 1</label>
              <select name="umpire1_id" id="umpire1Select" class="form-select official-select" data-fee-target="umpire1_fee">
                <option value="">— None —</option>
                <?php foreach ($umpires as $u): ?>
                  <option value="<?= $u['id'] ?>" data-fee="<?= $u['fee_per_match'] ?? 0 ?>"
                    <?= old('umpire1_id', $fixture['umpire1_id'] ?? '') == $u['id'] ? 'selected' : '' ?>>
                    <?= esc($u['full_name']) ?><?= !empty($u['district_name']) ? ' — ' . esc($u['district_name']) : '' ?>
                  </option>
                <?php endforeach; ?>
              </select>
            </div>
            <div class="col-md-6">
              <label class="form-label fw-semibold">Umpire 1 Fee (₹)</label>
              <input type="number" name="umpire1_fee" id="umpire1_fee" class="form-control" min="0" step="0.01"
                value="<?= old('umpire1_fee', isset($feeMap[$fixture['umpire1_id'] ?? 0]) ? $feeMap[$fixture['umpire1_id']] : '') ?>" placeholder="Auto-filled">
            </div>

            <div class="col-md-6">
              <label class="form-label fw-semibold">Umpire 2</label>
              <select name="umpire2_id" id="umpire2Select" class="form-select official-select" data-fee-target="umpire2_fee">
                <option value="">— None —</option>
                <?php foreach ($umpires as $u): ?>
                  <option value="<?= $u['id'] ?>" data-fee="<?= $u['fee_per_match'] ?? 0 ?>"
                    <?= old('umpire2_id', $fixture['umpire2_id'] ?? '') == $u['id'] ? 'selected' : '' ?>>
                    <?= esc($u['full_name']) ?><?= !empty($u['district_name']) ? ' — ' . esc($u['district_name']) : '' ?>
                  </option>
                <?php endforeach; ?>
              </select>
            </div>
            <div class="col-md-6">
              <label class="form-label fw-semibold">Umpire 2 Fee (₹)</label>
              <input type="number" name="umpire2_fee" id="umpire2_fee" class="form-control" min="0" step="0.01"
                value="<?= old('umpire2_fee', isset($feeMap[$fixture['umpire2_id'] ?? 0]) ? $feeMap[$fixture['umpire2_id']] : '') ?>" placeholder="Auto-filled">
            </div>

            <div class="col-md-6">
              <label class="form-label fw-semibold">Scorer</label>
              <select name="scorer_id" id="scorerSelect" class="form-select official-select" data-fee-target="scorer_fee">
                <option value="">— None —</option>
                <?php foreach ($scorers as $s): ?>
                  <option value="<?= $s['id'] ?>" data-fee="<?= $s['fee_per_match'] ?? 0 ?>"
                    <?= old('scorer_id', $fixture['scorer_id'] ?? '') == $s['id'] ? 'selected' : '' ?>>
                    <?= esc($s['full_name']) ?><?= !empty($s['district_name']) ? ' — ' . esc($s['district_name']) : '' ?>
                  </option>
                <?php endforeach; ?>
              </select>
            </div>
            <div class="col-md-6">
              <label class="form-label fw-semibold">Scorer Fee (₹)</label>
              <input type="number" name="scorer_fee" id="scorer_fee" class="form-control" min="0" step="0.01"
                value="<?= old('scorer_fee', isset($feeMap[$fixture['scorer_id'] ?? 0]) ? $feeMap[$fixture['scorer_id']] : '') ?>" placeholder="Auto-filled">
            </div>

            <div class="col-md-6">
              <label class="form-label fw-semibold">Match Referee</label>
              <select name="referee_id" id="refereeSelect" class="form-select official-select" data-fee-target="referee_fee">
                <option value="">— None —</option>
                <?php foreach ($referees as $r): ?>
                  <option value="<?= $r['id'] ?>" data-fee="<?= $r['fee_per_match'] ?? 0 ?>"
                    <?= old('referee_id', $fixture['referee_id'] ?? '') == $r['id'] ? 'selected' : '' ?>>
                    <?= esc($r['full_name']) ?><?= !empty($r['district_name']) ? ' — ' . esc($r['district_name']) : '' ?>
                  </option>
                <?php endforeach; ?>
              </select>
            </div>
            <div class="col-md-6">
              <label class="form-label fw-semibold">Referee Fee (₹)</label>
              <input type="number" name="referee_fee" id="referee_fee" class="form-control" min="0" step="0.01"
                value="<?= old('referee_fee', isset($feeMap[$fixture['referee_id'] ?? 0]) ? $feeMap[$fixture['referee_id']] : '') ?>" placeholder="Auto-filled">
            </div>

            <div class="col-12">
              <label class="form-label fw-semibold">YouTube / Stream URL</label>
              <input type="url" name="youtube_url" class="form-control"
                value="<?= esc(old('youtube_url', $fixture['youtube_url'] ?? '')) ?>"
                placeholder="https://youtube.com/...">
            </div>
          </div>
        </div>
      </div>

      <div class="d-flex gap-2">
        <button type="submit" class="btn btn-jsca-primary" id="submitBtn">
          <i class="bi bi-check-lg me-1"></i> <?= $isEdit ? 'Update Fixture' : 'Create Fixture' ?>
        </button>
        <a href="<?= base_url('fixtures' . ($tournamentId ? '?tournament_id=' . $tournamentId : '')) ?>" class="btn btn-outline-secondary">Cancel</a>
      </div>

    </div>

    <!-- Sidebar -->
    <div class="col-lg-4">
      <div class="card">
        <div class="card-header">Quick Reference</div>
        <div class="card-body small text-muted">
          <ul class="ps-3 mb-0">
            <li>Only <strong>Confirmed</strong> teams appear in dropdowns.</li>
            <li>Match number auto-assigns if left blank.</li>
            <li>Officials are optional — can be assigned later.</li>
            <li>Once <strong>Completed</strong>, fixture cannot be edited.</li>
          </ul>
        </div>
      </div>
    </div>
  </div>
</form>

<script>
const BASE = '<?= base_url() ?>';
const isEdit = <?= $isEdit ? 'true' : 'false' ?>;

// ── Tournament change → AJAX load teams + officials + venues ──
<?php if (!$isEdit): ?>
const tournamentSelect = document.getElementById('tournamentSelect');

function loadTournamentData(tid) {
  if (!tid) return;

  // Load teams
  fetch(`${BASE}fixtures/teams-for-tournament/${tid}`)
    .then(r => r.json())
    .then(teams => {
      ['teamASelect','teamBSelect'].forEach(id => {
        const sel = document.getElementById(id);
        const current = sel.value;
        sel.innerHTML = '<option value="">— Select Team —</option>';
        teams.forEach(t => {
          const opt = new Option(t.name, t.id);
          if (t.id == current) opt.selected = true;
          sel.appendChild(opt);
        });
      });
    });

  // Load officials + venues
  fetch(`${BASE}fixtures/officials-for-tournament/${tid}`)
    .then(r => r.json())
    .then(data => {
      populateSelect('umpire1Select', data.umpires, 'umpire1_fee');
      populateSelect('umpire2Select', data.umpires, 'umpire2_fee');
      populateSelect('scorerSelect',  data.scorers,  'scorer_fee');
      populateSelect('refereeSelect', data.referees, 'referee_fee');
      populateVenues(data.venues);
    });
}

function populateSelect(selectId, items, feeTarget) {
  const sel = document.getElementById(selectId);
  const current = sel.value;
  sel.innerHTML = '<option value="">— None —</option>';
  items.forEach(item => {
    const opt = document.createElement('option');
    opt.value = item.id;
    opt.dataset.fee = item.fee_per_match ?? 0;
    opt.textContent = item.full_name + (item.district_name ? ' — ' + item.district_name : '');
    if (item.id == current) opt.selected = true;
    sel.appendChild(opt);
  });
}

function populateVenues(venues) {
  const sel = document.getElementById('venueSelect');
  const current = sel.value;
  sel.innerHTML = '<option value="">— Select Venue —</option>';
  venues.forEach(v => {
    const opt = new Option(v.name + (v.district_name ? ' — ' + v.district_name : ''), v.id);
    if (v.id == current) opt.selected = true;
    sel.appendChild(opt);
  });
}

tournamentSelect.addEventListener('change', function() {
  loadTournamentData(this.value);
});

// Load on page init if tournament already selected (e.g. ?tournament_id=1)
if (tournamentSelect.value) loadTournamentData(tournamentSelect.value);
<?php endif; ?>

// ── Prevent same team in both dropdowns ──────────────────────
const teamA = document.getElementById('teamASelect');
const teamB = document.getElementById('teamBSelect');

function preventSameTeam(changed) {
  const other = changed === teamA ? teamB : teamA;
  if (changed.value && changed.value === other.value) {
    showError('Team A and Team B cannot be the same.');
    changed.value = '';
  }
}
teamA?.addEventListener('change', () => preventSameTeam(teamA));
teamB?.addEventListener('change', () => preventSameTeam(teamB));

// ── Auto-fill fee when official selected ─────────────────────
document.querySelectorAll('.official-select').forEach(function(select) {
  select.addEventListener('change', function() {
    const opt = this.options[this.selectedIndex];
    const fee = opt?.dataset?.fee ?? '';
    const input = document.getElementById(this.dataset.feeTarget);
    if (input) input.value = fee && fee > 0 ? fee : '';
  });
  if (select.value) select.dispatchEvent(new Event('change'));
});

// ── Client-side validation before submit ─────────────────────
document.getElementById('fixtureForm').addEventListener('submit', function(e) {
  clearErrors();
  const errors = [];

  const u1 = document.getElementById('umpire1Select')?.value;
  const u2 = document.getElementById('umpire2Select')?.value;
  const tA = teamA?.value;
  const tB = teamB?.value;

  if (tA && tB && tA === tB)
    errors.push('Team A and Team B cannot be the same.');
  if (u1 && u2 && u1 === u2)
    errors.push('Umpire 1 and Umpire 2 cannot be the same person.');

  if (errors.length) {
    e.preventDefault();
    showErrors(errors);
    window.scrollTo({ top: 0, behavior: 'smooth' });
  }
});

function showError(msg) { showErrors([msg]); }

function showErrors(msgs) {
  const box  = document.getElementById('errorBox');
  const list = document.getElementById('errorList');
  list.innerHTML = msgs.map(m => `<li>${m}</li>`).join('');
  box.classList.remove('d-none');
  box.scrollIntoView({ behavior: 'smooth', block: 'start' });
  // Auto-dismiss after 6 seconds
  clearTimeout(window._errorTimer);
  window._errorTimer = setTimeout(() => clearErrors(), 6000);
}

function clearErrors() {
  document.getElementById('errorBox').classList.add('d-none');
  document.getElementById('errorList').innerHTML = '';
}
</script>
