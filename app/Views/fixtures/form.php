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

<form method="post" action="<?= $action ?>">
  <?= csrf_field() ?>

  <div class="row g-4">
    <div class="col-lg-8">

      <!-- Tournament & Teams -->
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
                placeholder="e.g. M01, SF1, Final">
              <div class="form-text">Leave blank to auto-assign.</div>
            </div>

            <div class="col-md-4">
              <label class="form-label fw-semibold">Stage</label>
              <select name="stage" class="form-select" id="stageSelect">
                <?php
                  $structure    = $tournament['structure'] ?? 'Round Robin';
                  $confirmedCount = isset($tournament['id'])
                    ? (int) (\Config\Database::connect()->table('teams')
                        ->where('tournament_id', $tournament['id'])
                        ->where('status', 'Confirmed')
                        ->countAllResults())
                    : 0;

                  // Derive valid stages from structure + team count
                  $stageOptions = match(true) {
                    $structure === 'Round Robin' && $confirmedCount <= 2 => ['Final'],
                    $structure === 'Round Robin'                         => ['League'],
                    $structure === 'Knockout' && $confirmedCount <= 2    => ['Final'],
                    $structure === 'Knockout' && $confirmedCount <= 4    => ['Semi Final', 'Final'],
                    $structure === 'Knockout'                            => ['Quarter Final', 'Semi Final', 'Final'],
                    $structure === 'Group+Knockout'                      => ['League', 'Quarter Final', 'Semi Final', 'Final'],
                    $structure === 'League+Playoffs'                     => ['League', 'Qualifier 1', 'Qualifier 2', 'Eliminator', 'Final'],
                    $structure === 'Zonal'                               => ['Zonal League', 'Zonal Final', 'Semi Final', 'Final'],
                    default                                              => ['League', 'Semi Final', 'Final'],
                  };
                  $currentStage = old('stage', $fixture['stage'] ?? $stageOptions[0]);
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
                <?php foreach (['Scheduled', 'Live', 'Completed', 'Abandoned', 'Postponed'] as $s): ?>
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
                <label class="form-check-label" for="isDayNight">Day/Night Match</label>
              </div>
            </div>
            <div class="col-12">
              <label class="form-label fw-semibold">Venue <span class="text-danger">*</span></label>
              <select name="venue_id" class="form-select" required>
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
              <select name="umpire1_id" class="form-select">
                <option value="">— None —</option>
                <?php foreach ($umpires as $u): ?>
                  <option value="<?= $u['id'] ?>" <?= old('umpire1_id', $fixture['umpire1_id'] ?? '') == $u['id'] ? 'selected' : '' ?>>
                    <?= esc($u['full_name']) ?><?= !empty($u['district_name']) ? ' — ' . esc($u['district_name']) : '' ?>
                  </option>
                <?php endforeach; ?>
              </select>
            </div>
            <div class="col-md-6">
              <label class="form-label fw-semibold">Umpire 2</label>
              <select name="umpire2_id" class="form-select">
                <option value="">— None —</option>
                <?php foreach ($umpires as $u): ?>
                  <option value="<?= $u['id'] ?>" <?= old('umpire2_id', $fixture['umpire2_id'] ?? '') == $u['id'] ? 'selected' : '' ?>>
                    <?= esc($u['full_name']) ?><?= !empty($u['district_name']) ? ' — ' . esc($u['district_name']) : '' ?>
                  </option>
                <?php endforeach; ?>
              </select>
            </div>
            <div class="col-md-6">
              <label class="form-label fw-semibold">Scorer</label>
              <select name="scorer_id" class="form-select">
                <option value="">— None —</option>
                <?php foreach ($scorers as $s): ?>
                  <option value="<?= $s['id'] ?>" <?= old('scorer_id', $fixture['scorer_id'] ?? '') == $s['id'] ? 'selected' : '' ?>>
                    <?= esc($s['full_name']) ?><?= !empty($s['district_name']) ? ' — ' . esc($s['district_name']) : '' ?>
                  </option>
                <?php endforeach; ?>
              </select>
            </div>
            <div class="col-md-6">
              <label class="form-label fw-semibold">Match Referee</label>
              <select name="referee_id" class="form-select">
                <option value="">— None —</option>
                <?php foreach ($referees as $r): ?>
                  <option value="<?= $r['id'] ?>" <?= old('referee_id', $fixture['referee_id'] ?? '') == $r['id'] ? 'selected' : '' ?>>
                    <?= esc($r['full_name']) ?><?= !empty($r['district_name']) ? ' — ' . esc($r['district_name']) : '' ?>
                  </option>
                <?php endforeach; ?>
              </select>
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
        <button type="submit" class="btn btn-jsca-primary">
          <i class="bi bi-check-lg me-1"></i> <?= $isEdit ? 'Update Fixture' : 'Create Fixture' ?>
        </button>
        <a href="<?= base_url('fixtures' . ($tournamentId ? '?tournament_id=' . $tournamentId : '')) ?>" class="btn btn-outline-secondary">Cancel</a>
      </div>

    </div>

    <!-- Right sidebar info -->
    <div class="col-lg-4">
      <div class="card">
        <div class="card-header">Quick Reference</div>
        <div class="card-body small text-muted">
          <ul class="ps-3 mb-0">
            <li>Only <strong>Confirmed</strong> teams appear in the team dropdowns.</li>
            <li>Tournament must be in <strong>Fixture Ready</strong> or <strong>Ongoing</strong> status.</li>
            <li>Match number auto-assigns if left blank (M01, M02...).</li>
            <li>Officials are optional at creation — can be assigned later.</li>
            <li>Once a fixture is <strong>Completed</strong> it cannot be edited.</li>
          </ul>
        </div>
      </div>
    </div>
  </div>
</form>

<?php if (!$isEdit): ?>
<script>
// When tournament changes, reload page with tournament_id to populate teams
document.getElementById('tournamentSelect')?.addEventListener('change', function() {
  if (this.value) {
    window.location = '<?= base_url('fixtures/create') ?>?tournament_id=' + this.value;
  }
});

// Prevent same team in both dropdowns
const teamA = document.getElementById('teamASelect');
const teamB = document.getElementById('teamBSelect');

function preventSameTeam() {
  const aVal = teamA.value;
  const bVal = teamB.value;
  if (aVal && bVal && aVal === bVal) {
    alert('Team A and Team B cannot be the same.');
    teamB.value = '';
  }
}
teamA.addEventListener('change', preventSameTeam);
teamB.addEventListener('change', preventSameTeam);
</script>
<?php endif; ?>
