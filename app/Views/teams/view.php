<?php
$statusColors = ['Registered' => 'bg-primary', 'Confirmed' => 'bg-success', 'Withdrawn' => 'bg-danger'];
$editable = $canManage && $team['status'] !== 'Withdrawn';
?>

<div class="d-flex justify-content-between align-items-center mb-4">
  <div>
    <h4 class="mb-0 fw-bold"><?= esc($team['name']) ?></h4>
    <div class="d-flex align-items-center gap-2 mt-1">
      <code class="text-muted" style="font-size:12px;"><?= esc($team['jsca_team_id'] ?? '—') ?></code>
      <span class="badge <?= $statusColors[$team['status']] ?? 'bg-secondary' ?>"><?= esc($team['status']) ?></span>
      <?php if (!empty($team['age_category'])): ?>
        <span class="badge bg-light text-dark"><?= esc($team['age_category']) ?></span>
      <?php endif; ?>
      <?php if ($team['zone'] && $team['zone'] !== 'None'): ?>
        <span class="badge bg-light text-dark"><?= esc($team['zone']) ?> Zone</span>
      <?php endif; ?>
    </div>
  </div>
  <div class="d-flex gap-2">
    <?php if ($canManage): ?>
      <a href="<?= base_url('teams/edit/' . $team['id']) ?>" class="btn btn-outline-primary btn-sm">
        <i class="bi bi-pencil me-1"></i> Edit
      </a>
    <?php endif; ?>
    <a href="<?= base_url('teams') ?>" class="btn btn-outline-secondary btn-sm">
      <i class="bi bi-arrow-left me-1"></i> Back
    </a>
  </div>
</div>

<!-- Info row -->
<div class="row g-3 mb-4">
  <div class="col-6 col-md-3">
    <div class="stat-card">
      <div class="stat-val"><?= count($players) ?></div>
      <div class="stat-label">Players</div>
    </div>
  </div>
  <div class="col-6 col-md-3">
    <div class="stat-card">
      <div class="stat-val"><?= count($coaches) ?></div>
      <div class="stat-label">Coaches</div>
    </div>
  </div>
  <div class="col-6 col-md-3">
    <div class="stat-card">
      <div class="stat-val"><?= count($documents) ?></div>
      <div class="stat-label">Documents</div>
    </div>
  </div>
  <div class="col-6 col-md-3">
    <div class="stat-card">
      <div class="stat-val" style="font-size:14px;"><?= esc($team['district_name'] ?? '—') ?></div>
      <div class="stat-label">District</div>
    </div>
  </div>
</div>

<div class="row g-4">
  <div class="col-lg-8">

    <!-- Tournament info -->
    <div class="card mb-4">
      <div class="card-header">Tournament</div>
      <div class="card-body">
        <div class="row g-3">
          <div class="col-md-6">
            <div class="text-muted small">Tournament</div>
            <div><?= esc($team['tournament_name'] ?? '—') ?></div>
          </div>
          <div class="col-md-3">
            <div class="text-muted small">Category</div>
            <div><?= esc($team['age_category'] ?? '—') ?></div>
          </div>
          <div class="col-md-3">
            <div class="text-muted small">Tournament Status</div>
            <div><?= esc($team['tournament_status'] ?? '—') ?></div>
          </div>
          <?php if ($team['captain_name']): ?>
            <div class="col-md-4">
              <div class="text-muted small">Captain</div>
              <div><?= esc($team['captain_name']) ?></div>
            </div>
          <?php endif; ?>
          <?php if ($team['vice_captain_name']): ?>
            <div class="col-md-4">
              <div class="text-muted small">Vice Captain</div>
              <div><?= esc($team['vice_captain_name']) ?></div>
            </div>
          <?php endif; ?>
          <?php if (!empty($team['manager_name'])): ?>
            <div class="col-md-4">
              <div class="text-muted small">Team Manager</div>
              <div><?= esc($team['manager_name']) ?> &nbsp;<span class="text-muted"><?= esc($team['manager_phone'] ?? '') ?></span></div>
            </div>
          <?php endif; ?>
        </div>
      </div>
    </div>

    <!-- Squad -->
    <div class="card mb-4">
      <div class="card-header d-flex justify-content-between align-items-center">
        Squad (<?= count($players) ?>)
        <?php if ($editable): ?>
          <button class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#addPlayerModal">
            <i class="bi bi-plus"></i> Add Player
          </button>
        <?php endif; ?>
      </div>
      <div class="card-body p-0">
        <?php if (empty($players)): ?>
          <p class="text-muted p-3 mb-0">No players in squad yet.</p>
        <?php else: ?>
          <div class="table-responsive">
            <table class="table table-sm mb-0">
              <thead>
                <tr>
                  <th>#</th>
                  <th>Player</th>
                  <th>Role</th>
                  <th>Flags</th>
                  <?php if ($editable): ?><th></th><?php endif; ?>
                </tr>
              </thead>
              <tbody>
                <?php foreach ($players as $p): ?>
                  <tr>
                    <td><span class="badge bg-dark"><?= $p['jersey_number'] ?? '—' ?></span></td>
                    <td>
                      <a href="<?= base_url('players/view/' . $p['player_id']) ?>" class="fw-semibold text-decoration-none">
                        <?= esc($p['full_name']) ?>
                      </a>
                      <div style="font-size:11px;color:#999;"><?= esc($p['jsca_player_id']) ?></div>
                    </td>
                    <td style="font-size:13px;"><?= esc($p['role']) ?></td>
                    <td>
                      <?php if ($p['is_captain']): ?><span class="badge bg-warning text-dark" style="font-size:10px;">C</span><?php endif; ?>
                      <?php if ($p['is_vice_captain']): ?><span class="badge bg-info text-dark" style="font-size:10px;">VC</span><?php endif; ?>
                      <?php if ($p['is_wk']): ?><span class="badge bg-secondary" style="font-size:10px;">WK</span><?php endif; ?>
                    </td>
                    <?php if ($editable): ?>
                      <td>
                        <form method="post" action="<?= base_url('teams/remove-player/' . $team['id'] . '/' . $p['player_id']) ?>"
                          onsubmit="return confirm('Remove <?= esc($p['full_name']) ?> from squad?')">
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

    <!-- Coaches -->
    <div class="card mb-4">
      <div class="card-header d-flex justify-content-between align-items-center">
        Coaching Staff (<?= count($coaches) ?>)
        <?php if ($editable): ?>
          <button class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#addCoachModal">
            <i class="bi bi-plus"></i> Assign Coach
          </button>
        <?php endif; ?>
      </div>
      <div class="card-body p-0">
        <?php if (empty($coaches)): ?>
          <p class="text-muted p-3 mb-0">No coaches assigned yet.</p>
        <?php else: ?>
          <div class="table-responsive">
            <table class="table table-sm mb-0">
              <thead><tr><th>Coach</th><th>Role</th><th>Level</th><?php if ($editable): ?><th></th><?php endif; ?></tr></thead>
              <tbody>
                <?php foreach ($coaches as $c): ?>
                  <tr>
                    <td>
                      <div class="fw-semibold"><?= esc($c['full_name']) ?></div>
                      <div style="font-size:11px;color:#999;"><?= esc($c['jsca_coach_id']) ?></div>
                    </td>
                    <td style="font-size:13px;"><?= esc($c['role']) ?></td>
                    <td style="font-size:13px;"><?= esc($c['level']) ?></td>
                    <?php if ($editable): ?>
                      <td>
                        <form method="post" action="<?= base_url('teams/remove-coach/' . $team['id'] . '/' . $c['coach_id']) ?>"
                          onsubmit="return confirm('Remove this coach?')">
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

  </div>

  <!-- Documents -->
  <div class="col-lg-4">
    <div class="card">
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
              <li class="list-group-item py-2">
                <div class="d-flex justify-content-between align-items-start">
                  <div>
                    <div style="font-size:13px;"><?= esc(ucwords(str_replace('_', ' ', $doc['doc_type']))) ?></div>
                    <?php if ($doc['label']): ?>
                      <small class="text-muted"><?= esc($doc['label']) ?></small>
                    <?php endif; ?>
                    <div>
                      <?php if ($doc['verified']): ?>
                        <span class="badge bg-success" style="font-size:10px;"><i class="bi bi-check"></i> Verified</span>
                      <?php else: ?>
                        <span class="badge bg-warning text-dark" style="font-size:10px;">Pending</span>
                      <?php endif; ?>
                    </div>
                  </div>
                  <div class="d-flex gap-1">
                    <?php if ($canManage && !$doc['verified']): ?>
                      <form method="post" action="<?= base_url('teams/verify-doc/' . $doc['id']) ?>">
                        <?= csrf_field() ?>
                        <button class="btn btn-sm btn-outline-success" title="Verify"><i class="bi bi-check"></i></button>
                      </form>
                    <?php endif; ?>
                    <?php if ($canManage): ?>
                      <form method="post" action="<?= base_url('teams/delete-doc/' . $doc['id']) ?>"
                        onsubmit="return confirm('Delete this document?')">
                        <?= csrf_field() ?>
                        <button class="btn btn-sm btn-outline-danger"><i class="bi bi-trash"></i></button>
                      </form>
                    <?php endif; ?>
                  </div>
                </div>
              </li>
            <?php endforeach; ?>
          </ul>
        <?php endif; ?>
      </div>
    </div>
  </div>
</div>

<?php if ($editable): ?>
<!-- Add Player Modal -->
<div class="modal fade" id="addPlayerModal" tabindex="-1">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Add Player to Squad</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <form method="post" action="<?= base_url('teams/add-player/' . $team['id']) ?>">
        <?= csrf_field() ?>
        <div class="modal-body">
          <div class="mb-3">
            <label class="form-label">Player <span class="text-danger">*</span></label>
            <select name="player_id" class="form-select" required>
              <option value="">— Select Player —</option>
              <?php foreach ($availablePlayers as $p): ?>
                <option value="<?= $p['id'] ?>"><?= esc($p['full_name']) ?> — <?= esc($p['jsca_player_id']) ?> (<?= esc($p['role']) ?>)</option>
              <?php endforeach; ?>
            </select>
            <?php if (empty($availablePlayers)): ?>
              <div class="form-text text-warning">No eligible players available for this category.</div>
            <?php endif; ?>
          </div>
          <div class="mb-3">
            <label class="form-label">Jersey Number</label>
            <input type="number" name="jersey_number" class="form-control" min="1" max="99">
          </div>
          <div class="d-flex gap-3">
            <div class="form-check">
              <input class="form-check-input" type="checkbox" name="is_captain" value="1" id="isCaptain">
              <label class="form-check-label" for="isCaptain">Captain</label>
            </div>
            <div class="form-check">
              <input class="form-check-input" type="checkbox" name="is_vice_captain" value="1" id="isVc">
              <label class="form-check-label" for="isVc">Vice Captain</label>
            </div>
            <div class="form-check">
              <input class="form-check-input" type="checkbox" name="is_wk" value="1" id="isWk">
              <label class="form-check-label" for="isWk">Wicket Keeper</label>
            </div>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
          <button type="submit" class="btn btn-jsca-primary">Add to Squad</button>
        </div>
      </form>
    </div>
  </div>
</div>

<!-- Add Coach Modal -->
<div class="modal fade" id="addCoachModal" tabindex="-1">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Assign Coach</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <form method="post" action="<?= base_url('teams/add-coach/' . $team['id']) ?>">
        <?= csrf_field() ?>
        <div class="modal-body">
          <div class="mb-3">
            <label class="form-label">Coach <span class="text-danger">*</span></label>
            <select name="coach_id" class="form-select" required>
              <option value="">— Select Coach —</option>
              <?php foreach ($availableCoaches as $c): ?>
                <option value="<?= $c['id'] ?>"><?= esc($c['full_name']) ?> — <?= esc($c['level']) ?></option>
              <?php endforeach; ?>
            </select>
          </div>
          <div>
            <label class="form-label">Role</label>
            <select name="coach_role" class="form-select">
              <?php foreach (['Head Coach', 'Assistant Coach', 'Bowling Coach', 'Batting Coach', 'Fielding Coach', 'Fitness Trainer'] as $r): ?>
                <option value="<?= $r ?>"><?= $r ?></option>
              <?php endforeach; ?>
            </select>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
          <button type="submit" class="btn btn-jsca-primary">Assign</button>
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
      <form method="post" action="<?= base_url('teams/upload-doc/' . $team['id']) ?>" enctype="multipart/form-data">
        <?= csrf_field() ?>
        <div class="modal-body">
          <div class="mb-3">
            <label class="form-label">Document Type <span class="text-danger">*</span></label>
            <select name="doc_type" class="form-select" required>
              <option value="registration_form">Registration Form</option>
              <option value="affiliation_certificate">Affiliation Certificate</option>
              <option value="noc">NOC</option>
              <option value="player_consent">Player Consent Forms</option>
              <option value="insurance">Insurance</option>
              <option value="other">Other</option>
            </select>
          </div>
          <div class="mb-3">
            <label class="form-label">Label</label>
            <input type="text" name="label" class="form-control" placeholder="Optional description">
          </div>
          <div>
            <label class="form-label">File <span class="text-danger">*</span></label>
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
