<?php
$canManage    = $canManage ?? false;
$editable     = in_array($tournament['status'], ['Draft', 'Registration']);
$statusColors = [
    'Draft'         => 'bg-secondary',
    'Registration'  => 'bg-primary',
    'Fixture Ready' => 'bg-info text-dark',
    'Ongoing'       => 'bg-success',
    'Completed'     => 'bg-dark',
    'Cancelled'     => 'bg-danger',
];
$fixtureStatusColors = [
    'Scheduled' => 'badge-status-scheduled',
    'Live'      => 'badge-status-live',
    'Completed' => 'badge-status-completed',
    'Abandoned' => 'badge-status-rejected',
    'Postponed' => 'badge-status-pending',
];
?>

<div class="d-flex justify-content-between align-items-center mb-4">
  <div>
    <h4 class="mb-0 fw-bold"><?= esc($tournament['name']) ?></h4>
    <div class="d-flex align-items-center gap-2 mt-1">
      <code class="text-muted" style="font-size:12px;"><?= esc($tournament['jsca_tournament_id'] ?? '') ?></code>
      <span class="badge <?= $statusColors[$tournament['status']] ?? 'bg-secondary' ?>"><?= $tournament['status'] ?></span>
      <span class="badge bg-light text-dark"><?= esc($tournament['format']) ?></span>
      <span class="badge bg-light text-dark"><?= esc($tournament['age_category']) ?></span>
    </div>
  </div>
  <div class="d-flex gap-2">
    <?php if ($canManage && $editable): ?>
      <a href="<?= base_url('tournaments/edit/' . $tournament['id']) ?>" class="btn btn-outline-primary btn-sm">
        <i class="bi bi-pencil me-1"></i> Edit
      </a>
    <?php endif; ?>
    <?php if ($canManage): ?>
      <!-- Status transitions -->
      <?php
      $transitions = [
          'Draft'         => ['Registration', 'Cancelled'],
          'Registration'  => ['Draft', 'Fixture Ready', 'Cancelled'],
          'Fixture Ready' => ['Ongoing', 'Registration', 'Cancelled'],
          'Ongoing'       => ['Completed', 'Cancelled'],
          'Completed'     => [],
          'Cancelled'     => [],
      ];
      $nextStatuses = $transitions[$tournament['status']] ?? [];
      ?>
      <?php if (!empty($nextStatuses)): ?>
      <div class="dropdown">
        <button class="btn btn-outline-secondary btn-sm dropdown-toggle" data-bs-toggle="dropdown">Change Status</button>
        <ul class="dropdown-menu dropdown-menu-end">
          <?php foreach ($nextStatuses as $s): ?>
            <li>
              <form method="post" action="<?= base_url('tournaments/update-status/' . $tournament['id']) ?>">
                <?= csrf_field() ?>
                <input type="hidden" name="status" value="<?= $s ?>">
                <button class="dropdown-item"><?= $s ?></button>
              </form>
            </li>
          <?php endforeach; ?>
        </ul>
      </div>
      <?php endif; ?>
    <?php endif; ?>
    <a href="<?= base_url('tournaments') ?>" class="btn btn-outline-secondary btn-sm">
      <i class="bi bi-arrow-left me-1"></i> Back
    </a>
  </div>
</div>

<!-- Stats row -->
<div class="row g-3 mb-4">
  <div class="col-6 col-md-3">
    <div class="stat-card">
      <div class="stat-val"><?= count($teams) ?><?= $tournament['max_teams'] ? '<span style="font-size:16px;color:#aaa;">/' . $tournament['max_teams'] . '</span>' : '' ?></div>
      <div class="stat-label">Teams</div>
    </div>
  </div>
  <div class="col-6 col-md-3">
    <div class="stat-card">
      <div class="stat-val"><?= $stats['total'] ?></div>
      <div class="stat-label">Total Matches</div>
    </div>
  </div>
  <div class="col-6 col-md-3">
    <div class="stat-card">
      <div class="stat-val"><?= $stats['completed'] ?></div>
      <div class="stat-label">Completed</div>
    </div>
  </div>
  <div class="col-6 col-md-3">
    <div class="stat-card" style="border-left-color:<?= $stats['live'] > 0 ? '#e74c3c' : 'var(--jsca-green)' ?>">
      <div class="stat-val" style="color:<?= $stats['live'] > 0 ? '#e74c3c' : 'inherit' ?>"><?= $stats['live'] ?></div>
      <div class="stat-label">Live Now</div>
    </div>
  </div>
</div>

<div class="row g-4">
  <!-- Left: Details + Teams + Fixtures -->
  <div class="col-lg-8">

    <!-- Tournament Details -->
    <div class="card mb-4">
      <div class="card-header">Tournament Details</div>
      <div class="card-body">
        <div class="row g-3">
          <div class="col-md-4">
            <div class="text-muted small">Season</div>
            <div><?= esc($tournament['season']) ?></div>
          </div>
          <div class="col-md-4">
            <div class="text-muted small">Structure</div>
            <div><?= esc($tournament['structure']) ?><?= $tournament['is_zonal'] ? ' <span class="badge bg-info text-dark ms-1">Zonal</span>' : '' ?></div>
          </div>
          <div class="col-md-4">
            <div class="text-muted small">Overs</div>
            <div><?= $tournament['overs'] ?? '—' ?></div>
          </div>
          <div class="col-md-4">
            <div class="text-muted small">Start Date</div>
            <div><?= $tournament['start_date'] ? date('d M Y', strtotime($tournament['start_date'])) : '—' ?></div>
          </div>
          <div class="col-md-4">
            <div class="text-muted small">End Date</div>
            <div><?= $tournament['end_date'] ? date('d M Y', strtotime($tournament['end_date'])) : '—' ?></div>
          </div>
          <div class="col-md-4">
            <div class="text-muted small">Reg. Deadline</div>
            <div><?= $tournament['registration_deadline'] ? date('d M Y', strtotime($tournament['registration_deadline'])) : '—' ?></div>
          </div>
          <div class="col-md-4">
            <div class="text-muted small">Primary Venue</div>
            <div><?= esc($tournament['venue_name'] ?? '—') ?></div>
          </div>
          <div class="col-md-4">
            <div class="text-muted small">Prize Pool</div>
            <div><?= $tournament['prize_pool'] > 0 ? '₹' . number_format($tournament['prize_pool']) : '—' ?></div>
          </div>
          <div class="col-md-4">
            <div class="text-muted small">Winner / Runner-up</div>
            <div>
              <?= $tournament['winner_prize'] > 0 ? '₹' . number_format($tournament['winner_prize']) : '—' ?>
              /
              <?= $tournament['runner_prize'] > 0 ? '₹' . number_format($tournament['runner_prize']) : '—' ?>
            </div>
          </div>
          <?php if (!empty($tournament['organizer_name'])): ?>
            <div class="col-md-4">
              <div class="text-muted small">Organizer</div>
              <div><?= esc($tournament['organizer_name']) ?></div>
            </div>
            <div class="col-md-4">
              <div class="text-muted small">Organizer Phone</div>
              <div><?= esc($tournament['organizer_phone'] ?? '—') ?></div>
            </div>
            <div class="col-md-4">
              <div class="text-muted small">Organizer Email</div>
              <div><?= esc($tournament['organizer_email'] ?? '—') ?></div>
            </div>
          <?php endif; ?>
          <?php if (!empty($tournament['description'])): ?>
            <div class="col-12">
              <div class="text-muted small">Description</div>
              <div><?= nl2br(esc($tournament['description'])) ?></div>
            </div>
          <?php endif; ?>
        </div>
      </div>
    </div>

    <!-- Teams -->
    <div class="card mb-4">
      <div class="card-header d-flex justify-content-between align-items-center">
        Teams (<?= count($teams) ?>)
        <?php if ($canManage && $editable): ?>
          <button class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#addTeamModal">
            <i class="bi bi-plus"></i> Add Team
          </button>
        <?php endif; ?>
      </div>
      <div class="card-body p-0">
        <?php if (empty($teams)): ?>
          <p class="text-muted p-3 mb-0">No teams registered yet.</p>
        <?php else: ?>
          <div class="table-responsive">
            <table class="table table-sm mb-0">
              <thead>
                <tr>
                  <th>Team</th>
                  <th>District</th>
                  <th>Captain</th>
                  <th>Players</th>
                  <th>Status</th>
                  <?php if ($canManage && $editable): ?><th></th><?php endif; ?>
                </tr>
              </thead>
              <tbody>
                <?php foreach ($teams as $tm): ?>
                  <tr>
                    <td>
                      <a href="<?= base_url('teams/view/' . $tm['id']) ?>" class="fw-semibold text-decoration-none">
                        <?= esc($tm['name']) ?>
                      </a>
                    </td>
                    <td><?= esc($tm['district_name'] ?? '—') ?></td>
                    <td><?= esc($tm['captain_name'] ?? '—') ?></td>
                    <td><span class="badge bg-light text-dark"><?= $tm['player_count'] ?></span></td>
                    <td><span class="badge bg-light text-dark"><?= esc($tm['status']) ?></span></td>
                    <?php if ($canManage && $editable): ?>
                      <td>
                        <form method="post" action="<?= base_url('tournaments/remove-team/' . $tournament['id'] . '/' . $tm['id']) ?>"
                          onsubmit="return confirm('Remove this team from the tournament?')">
                          <?= csrf_field() ?>
                          <button class="btn btn-sm btn-outline-danger"><i class="bi bi-x"></i></button>
                        </form>
                      </td>
                    <?php endif; ?>
                  </tr>
                <?php endforeach; ?>
              </tbody>
            </table>
          </div>
        <?php endif; ?>
      </div>
    </div>

    <!-- Fixtures -->
    <div class="card mb-4">
      <div class="card-header d-flex justify-content-between align-items-center">
        Fixtures (<?= $stats['total'] ?>)
        <?php if ($canManage && $stats['total'] === 0): ?>
          <a href="<?= base_url('fixtures/generate/' . $tournament['id']) ?>" class="btn btn-sm btn-outline-primary">
            <i class="bi bi-calendar-plus"></i> Generate Fixtures
          </a>
        <?php endif; ?>
      </div>
      <div class="card-body p-0">
        <?php if (empty($fixtures)): ?>
          <p class="text-muted p-3 mb-0">No fixtures generated yet.</p>
        <?php else: ?>
          <div class="table-responsive">
            <table class="table table-sm mb-0">
              <thead>
                <tr>
                  <th>#</th>
                  <th>Match</th>
                  <th>Date</th>
                  <th>Venue</th>
                  <th>Status</th>
                  <th></th>
                </tr>
              </thead>
              <tbody>
                <?php foreach ($fixtures as $f): ?>
                  <tr>
                    <td><small class="text-muted"><?= esc($f['match_number']) ?></small></td>
                    <td class="fw-semibold" style="font-size:13px;">
                      <?= esc($f['team_a_name']) ?> <span class="text-muted">vs</span> <?= esc($f['team_b_name']) ?>
                    </td>
                    <td style="font-size:12px;">
                      <?= $f['match_date'] ? date('d M', strtotime($f['match_date'])) : '—' ?>
                      <span class="text-muted"><?= substr($f['match_time'] ?? '', 0, 5) ?></span>
                    </td>
                    <td style="font-size:12px;"><?= esc($f['venue_name'] ?? '—') ?></td>
                    <td>
                      <span class="badge <?= $fixtureStatusColors[$f['status']] ?? 'bg-secondary' ?>" style="font-size:10px;">
                        <?= $f['status'] ?>
                      </span>
                    </td>
                    <td>
                      <a href="<?= base_url('fixtures/view/' . $f['id']) ?>" class="btn btn-sm btn-outline-secondary">View</a>
                    </td>
                  </tr>
                <?php endforeach; ?>
              </tbody>
            </table>
          </div>
        <?php endif; ?>
      </div>
    </div>

  </div>

  <!-- Right: Banner + Documents -->
  <div class="col-lg-4">

    <?php if (!empty($tournament['banner_path'])): ?>
      <div class="card mb-4">
        <img src="<?= base_url($tournament['banner_path']) ?>" class="card-img-top" style="max-height:200px;object-fit:cover;">
      </div>
    <?php endif; ?>

    <!-- Documents -->
    <div class="card mb-4">
      <div class="card-header d-flex justify-content-between align-items-center">
        Documents
        <?php if ($canManage): ?>
          <button class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#uploadDocModal">
            <i class="bi bi-upload"></i>
          </button>
        <?php endif; ?>
      </div>
      <div class="card-body p-0">
        <?php if (empty($documents)): ?>
          <p class="text-muted p-3 mb-0 small">No documents uploaded.</p>
        <?php else: ?>
          <ul class="list-group list-group-flush">
            <?php foreach ($documents as $doc): ?>
              <li class="list-group-item d-flex justify-content-between align-items-center py-2">
                <div>
                  <div style="font-size:13px;"><?= esc($doc['label'] ?: $doc['doc_type']) ?></div>
                  <small class="text-muted"><?= esc($doc['doc_type']) ?></small>
                </div>
                <div class="d-flex gap-1">
                  <?php if ($canManage && !$doc['verified']): ?>
                    <form method="post" action="<?= base_url('tournaments/verify-doc/' . $doc['id']) ?>">
                      <?= csrf_field() ?>
                      <button class="btn btn-sm btn-outline-success" title="Verify"><i class="bi bi-check"></i></button>
                    </form>
                  <?php elseif ($doc['verified']): ?>
                    <span class="badge bg-success"><i class="bi bi-check"></i></span>
                  <?php endif; ?>
                  <?php if ($canManage): ?>
                    <form method="post" action="<?= base_url('tournaments/delete-doc/' . $doc['id']) ?>"
                      onsubmit="return confirm('Delete this document?')">
                      <?= csrf_field() ?>
                      <button class="btn btn-sm btn-outline-danger"><i class="bi bi-trash"></i></button>
                    </form>
                  <?php endif; ?>
                </div>
              </li>
            <?php endforeach; ?>
          </ul>
        <?php endif; ?>
      </div>
    </div>

    <?php if (!empty($tournament['rules'])): ?>
      <div class="card">
        <div class="card-header">Rules & Regulations</div>
        <div class="card-body">
          <p style="font-size:13px;white-space:pre-line;"><?= esc($tournament['rules']) ?></p>
        </div>
      </div>
    <?php endif; ?>

  </div>
</div>

<!-- Add Team Modal -->
<?php if ($canManage): ?>
<div class="modal fade" id="addTeamModal" tabindex="-1">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Add Team to Tournament</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <form method="post" action="<?= base_url('tournaments/add-team/' . $tournament['id']) ?>">
        <?= csrf_field() ?>
        <div class="modal-body">
          <?php if (empty($available)): ?>
            <p class="text-muted">No available teams. <a href="<?= base_url('teams/create') ?>">Create a team first.</a></p>
          <?php else: ?>
            <select name="team_id" class="form-select" required>
              <option value="">— Select Team —</option>
              <?php foreach ($availableTeams as $at): ?>
                <option value="<?= $at['id'] ?>"><?= esc($at['name']) ?></option>
              <?php endforeach; ?>
            </select>
          <?php endif; ?>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
          <button type="submit" class="btn btn-jsca-primary">Add Team</button>
        </div>
      </form>
    </div>
  </div>
</div>

<!-- Upload Doc Modal -->
<div class="modal fade" id="uploadDocModal" tabindex="-1">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Upload Document</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <form method="post" action="<?= base_url('tournaments/upload-doc/' . $tournament['id']) ?>" enctype="multipart/form-data">
        <?= csrf_field() ?>
        <div class="modal-body">
          <div class="mb-3">
            <label class="form-label">Document Type</label>
            <select name="doc_type" class="form-select" required>
              <?php foreach (['approval_letter','bcci_sanction','insurance','schedule','rules_regulations','sponsorship_agreement','other'] as $dt): ?>
                <option value="<?= $dt ?>"><?= ucwords(str_replace('_', ' ', $dt)) ?></option>
              <?php endforeach; ?>
            </select>
          </div>
          <div class="mb-3">
            <label class="form-label">Label</label>
            <input type="text" name="label" class="form-control" placeholder="Optional description">
          </div>
          <div class="mb-3">
            <label class="form-label">File</label>
            <input type="file" name="document" class="form-control" accept=".jpg,.jpeg,.png,.pdf" required>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
          <button type="submit" class="btn btn-jsca-primary">Upload</button>
        </div>
      </form>
    </div>
  </div>
</div>
<?php endif; ?>
