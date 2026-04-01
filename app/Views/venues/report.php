<?php // app/Views/venues/report.php ?>

<div class="d-flex justify-content-between align-items-center mb-3">
  <div>
    <h4 class="mb-0">Venue Usage Report</h4>
    <small class="text-muted">Match activity across all venues</small>
  </div>
  <a href="<?= base_url('venues') ?>" class="btn btn-sm btn-outline-secondary">
    <i class="bi bi-arrow-left me-1"></i>Back to Venues
  </a>
</div>

<!-- Filters -->
<form method="get" class="card mb-4">
  <div class="card-body py-3">
    <div class="row g-2 align-items-end">
      <div class="col-md-4">
        <label class="form-label form-label-sm mb-1">Venue</label>
        <select name="venue_id" class="form-select form-select-sm">
          <option value="">All Venues</option>
          <?php foreach ($allVenues as $v): ?>
            <option value="<?= $v['id'] ?>" <?= $venueId == $v['id'] ? 'selected' : '' ?>>
              <?= esc($v['name']) ?> — <?= esc($v['district_name']) ?>
            </option>
          <?php endforeach; ?>
        </select>
      </div>
      <div class="col-md-3">
        <label class="form-label form-label-sm mb-1">From Date</label>
        <input type="date" name="from" value="<?= esc($fromDate) ?>" class="form-control form-control-sm">
      </div>
      <div class="col-md-3">
        <label class="form-label form-label-sm mb-1">To Date</label>
        <input type="date" name="to" value="<?= esc($toDate) ?>" class="form-control form-control-sm">
      </div>
      <div class="col-md-2 d-flex gap-1">
        <button type="submit" class="btn btn-sm btn-jsca-primary flex-grow-1">Apply</button>
        <a href="<?= base_url('venues/report') ?>" class="btn btn-sm btn-outline-secondary">Clear</a>
      </div>
    </div>
  </div>
</form>

<?php if (empty($stats)): ?>
  <div class="card"><div class="card-body text-center text-muted py-5">No match data found for the selected filters.</div></div>
<?php else: ?>

  <!-- Summary cards (totals across all shown venues) -->
  <?php
    $totalMatches   = array_sum(array_column($stats, 'total_matches'));
    $totalCompleted = array_sum(array_column($stats, 'completed'));
    $totalAbandoned = array_sum(array_column($stats, 'abandoned'));
    $totalDayNight  = array_sum(array_column($stats, 'day_night'));
  ?>
  <div class="row g-3 mb-4">
    <div class="col-6 col-md-3">
      <div class="card text-center">
        <div class="card-body py-3">
          <div style="font-size:28px;font-weight:800;color:#1a3a5c;"><?= $totalMatches ?></div>
          <div style="font-size:11px;color:#999;text-transform:uppercase;">Total Matches</div>
        </div>
      </div>
    </div>
    <div class="col-6 col-md-3">
      <div class="card text-center">
        <div class="card-body py-3">
          <div style="font-size:28px;font-weight:800;color:#198754;"><?= $totalCompleted ?></div>
          <div style="font-size:11px;color:#999;text-transform:uppercase;">Completed</div>
        </div>
      </div>
    </div>
    <div class="col-6 col-md-3">
      <div class="card text-center">
        <div class="card-body py-3">
          <div style="font-size:28px;font-weight:800;color:#dc3545;"><?= $totalAbandoned ?></div>
          <div style="font-size:11px;color:#999;text-transform:uppercase;">Abandoned</div>
        </div>
      </div>
    </div>
    <div class="col-6 col-md-3">
      <div class="card text-center">
        <div class="card-body py-3">
          <div style="font-size:28px;font-weight:800;color:#6f42c1;"><?= $totalDayNight ?></div>
          <div style="font-size:11px;color:#999;text-transform:uppercase;">Day/Night</div>
        </div>
      </div>
    </div>
  </div>

  <!-- Per-venue stats table -->
  <div class="card mb-4">
    <div class="card-header fw-semibold">Venue-wise Breakdown</div>
    <div class="card-body p-0">
      <div class="table-responsive">
        <table class="table table-hover mb-0" style="font-size:13px;">
          <thead class="table-light">
            <tr>
              <th>#</th>
              <th>Venue</th>
              <th>District</th>
              <th>Pitch</th>
              <th class="text-center">Capacity</th>
              <th class="text-center">Total</th>
              <th class="text-center">Completed</th>
              <th class="text-center">Abandoned</th>
              <th class="text-center">Postponed</th>
              <th class="text-center">Scheduled</th>
              <th class="text-center">Day/Night</th>
              <th class="text-center">Tournaments</th>
              <th>First Match</th>
              <th>Last Match</th>
              <th></th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($stats as $i => $s): ?>
              <tr>
                <td class="text-muted"><?= $i + 1 ?></td>
                <td class="fw-semibold">
                  <?= esc($s['venue_name']) ?>
                  <?php if ($s['has_floodlights']): ?>
                    <i class="bi bi-lightbulb-fill text-warning ms-1" title="Floodlights"></i>
                  <?php endif; ?>
                </td>
                <td class="text-muted"><?= esc($s['district_name']) ?></td>
                <td><?= esc($s['pitch_type']) ?></td>
                <td class="text-center"><?= $s['capacity'] ? number_format($s['capacity']) : '—' ?></td>
                <td class="text-center fw-bold"><?= $s['total_matches'] ?></td>
                <td class="text-center"><span class="badge bg-success"><?= $s['completed'] ?></span></td>
                <td class="text-center"><span class="badge bg-danger"><?= $s['abandoned'] ?></span></td>
                <td class="text-center"><span class="badge bg-warning text-dark"><?= $s['postponed'] ?></span></td>
                <td class="text-center"><span class="badge bg-secondary"><?= $s['scheduled'] ?></span></td>
                <td class="text-center"><?= $s['day_night'] ?></td>
                <td class="text-center"><?= $s['tournaments'] ?></td>
                <td><?= $s['first_match'] ? date('d M Y', strtotime($s['first_match'])) : '—' ?></td>
                <td><?= $s['last_match']  ? date('d M Y', strtotime($s['last_match']))  : '—' ?></td>
                <td>
                  <a href="<?= base_url('venues/report?venue_id=' . $s['venue_id'] . ($fromDate ? '&from='.$fromDate : '') . ($toDate ? '&to='.$toDate : '')) ?>"
                     class="btn btn-xs btn-outline-secondary" style="font-size:11px;padding:2px 8px;">
                    Details
                  </a>
                </td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>

<?php endif; ?>

<!-- Match history for selected venue -->
<?php if ($venueId && !empty($recentFixtures)): ?>
  <?php $selectedVenue = array_filter($allVenues, fn($v) => $v['id'] == $venueId); $sv = reset($selectedVenue); ?>
  <div class="card">
    <div class="card-header fw-semibold">
      All Matches at <?= esc($sv['name'] ?? 'Selected Venue') ?>
      <span class="badge bg-secondary ms-2"><?= count($recentFixtures) ?></span>
    </div>
    <div class="card-body p-0">
      <div class="table-responsive">
        <table class="table table-hover mb-0" style="font-size:13px;">
          <thead class="table-light">
            <tr>
              <th>#</th>
              <th>Date</th>
              <th>Match No.</th>
              <th>Tournament</th>
              <th>Stage</th>
              <th>Teams</th>
              <th class="text-center">D/N</th>
              <th>Status</th>
              <th>Result</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($recentFixtures as $i => $f): ?>
              <tr>
                <td class="text-muted"><?= $i + 1 ?></td>
                <td><?= date('d M Y', strtotime($f['match_date'])) ?></td>
                <td><?= esc($f['match_number']) ?></td>
                <td class="text-muted"><?= esc($f['tournament_name']) ?></td>
                <td><?= esc($f['stage']) ?></td>
                <td>
                  <strong><?= esc($f['team_a']) ?></strong>
                  <span class="text-muted mx-1">vs</span>
                  <strong><?= esc($f['team_b']) ?></strong>
                </td>
                <td class="text-center">
                  <?= $f['is_day_night'] ? '<i class="bi bi-moon-stars-fill text-primary"></i>' : '<i class="bi bi-sun-fill text-warning"></i>' ?>
                </td>
                <td>
                  <?php $sc = match($f['status']) {
                    'Completed' => 'bg-success',
                    'Live'      => 'bg-danger',
                    'Abandoned' => 'bg-dark',
                    'Postponed' => 'bg-warning text-dark',
                    default     => 'bg-secondary',
                  }; ?>
                  <span class="badge <?= $sc ?>" style="font-size:10px;"><?= esc($f['status']) ?></span>
                </td>
                <td style="max-width:200px;" class="text-truncate text-muted">
                  <?= esc($f['result_summary'] ?? '—') ?>
                </td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>
<?php elseif ($venueId): ?>
  <div class="card"><div class="card-body text-center text-muted py-4">No matches found for this venue with the selected filters.</div></div>
<?php endif; ?>
