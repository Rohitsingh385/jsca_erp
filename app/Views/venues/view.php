<?php // app/Views/venues/view.php ?>

<div class="d-flex justify-content-between align-items-start mb-3">
  <a href="<?= base_url('venues') ?>" class="text-muted" style="font-size:13px;">
    <i class="bi bi-arrow-left me-1"></i>Back to Venues
  </a>
  <div class="d-flex gap-2">
    <a href="<?= base_url('venues/edit/' . $venue['id']) ?>" class="btn btn-sm btn-outline-secondary">
      <i class="bi bi-pencil me-1"></i>Edit
    </a>
    <form method="post" action="<?= base_url('venues/toggle/' . $venue['id']) ?>"
      onsubmit="return confirm('<?= $venue['is_active'] ? 'Deactivate' : 'Activate' ?> this venue?')">
      <?= csrf_field() ?>
      <button class="btn btn-sm <?= $venue['is_active'] ? 'btn-outline-danger' : 'btn-outline-success' ?>">
        <?= $venue['is_active'] ? 'Deactivate' : 'Activate' ?>
      </button>
    </form>
  </div>
</div>

<div class="row g-3">

  <!-- Header card -->
  <div class="col-12">
    <div class="card">
      <div class="card-body">
        <div class="d-flex justify-content-between align-items-start">
          <div>
            <h4 class="mb-1 fw-bold"><?= esc($venue['name']) ?></h4>
            <div class="text-muted mb-2" style="font-size:13px;">
              <i class="bi bi-geo-alt me-1"></i><?= esc($venue['district_name']) ?> &middot; <?= esc($venue['zone']) ?> Zone
            </div>
            <div class="d-flex flex-wrap gap-2">
              <?php if ($venue['has_floodlights']): ?>
                <span class="badge bg-warning text-dark"><i class="bi bi-lightbulb me-1"></i>Floodlights</span>
              <?php endif; ?>
              <?php if ($venue['has_scoreboard']): ?>
                <span class="badge bg-info text-dark"><i class="bi bi-display me-1"></i>Scoreboard</span>
              <?php endif; ?>
              <?php if ($venue['has_dressing']): ?>
                <span class="badge bg-light text-dark border"><i class="bi bi-door-open me-1"></i>Dressing Rooms</span>
              <?php endif; ?>
              <span class="badge bg-secondary"><?= esc($venue['pitch_type']) ?> Pitch</span>
            </div>
          </div>
          <div class="text-end">
            <span class="badge <?= $venue['is_active'] ? 'bg-success' : 'bg-danger' ?> mb-1">
              <?= $venue['is_active'] ? 'Active' : 'Inactive' ?>
            </span>
            <?php if ($venue['capacity']): ?>
              <div style="font-size:22px;font-weight:800;color:#1a3a5c;"><?= number_format($venue['capacity']) ?></div>
              <div style="font-size:11px;color:#999;text-transform:uppercase;">Capacity</div>
            <?php endif; ?>
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- Details -->
  <div class="col-lg-4">
    <div class="card">
      <div class="card-header">Details</div>
      <div class="card-body p-0">
        <?php
          $rows = [
            'District'  => $venue['district_name'],
            'Zone'      => $venue['zone'],
            'Pitch'     => $venue['pitch_type'],
            'Capacity'  => $venue['capacity'] ? number_format($venue['capacity']) : '—',
            'Contact'   => $venue['contact_person'] ?? '—',
            'Phone'     => $venue['contact_phone']  ?? '—',
            'Address'   => $venue['address']        ?? '—',
          ];
          if ($venue['lat'] && $venue['lng']) {
              $rows['Coordinates'] = $venue['lat'] . ', ' . $venue['lng'];
          }
        ?>
        <?php foreach ($rows as $k => $v): ?>
          <div class="d-flex px-3 py-2 border-bottom" style="font-size:13px;">
            <span class="text-muted" style="width:100px;flex-shrink:0;"><?= $k ?></span>
            <span><?= esc($v) ?></span>
          </div>
        <?php endforeach; ?>
        <?php if ($venue['lat'] && $venue['lng']): ?>
          <div class="p-3">
            <a href="https://maps.google.com/?q=<?= $venue['lat'] ?>,<?= $venue['lng'] ?>" target="_blank"
              class="btn btn-sm btn-outline-secondary w-100">
              <i class="bi bi-map me-1"></i>View on Google Maps
            </a>
          </div>
        <?php endif; ?>
      </div>
    </div>
  </div>

  <!-- Fixtures -->
  <div class="col-lg-8">
    <div class="card">
      <div class="card-header">Recent & Upcoming Fixtures</div>
      <div class="card-body p-0">
        <?php if (empty($fixtures)): ?>
          <div class="text-center text-muted py-4" style="font-size:13px;">No fixtures at this venue yet.</div>
        <?php else: ?>
          <table class="table mb-0">
            <thead>
              <tr>
                <th>Date</th>
                <th>Match</th>
                <th>Tournament</th>
                <th>Status</th>
              </tr>
            </thead>
            <tbody>
              <?php foreach ($fixtures as $f): ?>
                <tr>
                  <td style="font-size:12px;"><?= date('d M Y', strtotime($f['match_date'])) ?></td>
                  <td style="font-size:13px;">
                    <strong><?= esc($f['team_a']) ?></strong>
                    <span class="text-muted mx-1">vs</span>
                    <strong><?= esc($f['team_b']) ?></strong>
                  </td>
                  <td style="font-size:12px;color:#999;"><?= esc($f['tournament_name']) ?></td>
                  <td>
                    <?php
                      $sc = match($f['status']) {
                        'Live'      => 'badge-status-live',
                        'Completed' => 'badge-status-completed',
                        'Scheduled' => 'badge-status-scheduled',
                        default     => 'bg-secondary',
                      };
                    ?>
                    <span class="badge <?= $sc ?>" style="font-size:10px;"><?= esc($f['status']) ?></span>
                  </td>
                </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        <?php endif; ?>
      </div>
    </div>
  </div>

</div>
