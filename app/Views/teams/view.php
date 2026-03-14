<!-- app/Views/teams/view.php -->
<style>
  .player-row { display:flex;align-items:center;gap:12px;padding:10px 16px;border-bottom:1px solid #f5f5f5; }
  .player-row:last-child { border-bottom:none; }
  .jersey { width:28px;height:28px;border-radius:6px;background:#1a3a5c;color:#fff;
    display:flex;align-items:center;justify-content:center;font-size:11px;font-weight:700;flex-shrink:0; }
  .doc-card { border:1px solid #eee;border-radius:8px;padding:12px 14px;
    display:flex;align-items:center;gap:12px;margin-bottom:8px; }
  .doc-card .doc-name { font-size:13px;font-weight:600; }
  .doc-card .doc-meta { font-size:11px;color:#999; }
</style>

<div class="d-flex justify-content-between align-items-start mb-3">
  <a href="<?= base_url('teams') ?>" class="text-muted" style="font-size:13px;">
    <i class="bi bi-arrow-left me-1"></i>Back to Teams
  </a>
  <div class="d-flex gap-2">
    <a href="<?= base_url('teams/edit/' . $team['id']) ?>" class="btn btn-sm btn-outline-secondary">Edit Team</a>
    <?php if ($team['status'] === 'Active'): ?>
      <form method="post" action="<?= base_url('teams/delete/' . $team['id']) ?>"
        onsubmit="return confirm('Deactivate this team?')">
        <?= csrf_field() ?>
        <button class="btn btn-sm btn-outline-danger">Deactivate</button>
      </form>
    <?php endif; ?>
  </div>
</div>

<!-- Header -->
<div class="card mb-3">
  <div class="card-body">
    <div class="d-flex gap-4 align-items-center">
      <?php if ($team['logo_path']): ?>
        <img src="<?= base_url('uploads/' . ltrim($team['logo_path'], 'uploads/')) ?>"
          style="width:80px;height:80px;object-fit:cover;border-radius:10px;border:2px solid #eee;">
      <?php else: ?>
        <div style="width:80px;height:80px;border-radius:10px;background:#1a3a5c;display:flex;
          align-items:center;justify-content:center;font-size:22px;font-weight:800;color:#fff;flex-shrink:0;">
          <?= esc($team['short_name'] ?: strtoupper(substr($team['name'], 0, 3))) ?>
        </div>
      <?php endif; ?>
      <div class="flex-grow-1">
        <div class="d-flex align-items-center gap-2 mb-1">
          <h5 class="mb-0 fw-bold"><?= esc($team['name']) ?></h5>
          <span class="badge <?= $team['status'] === 'Active' ? 'bg-success' : 'bg-secondary' ?>"
            style="font-size:10px;"><?= esc($team['status']) ?></span>
          <span class="badge bg-light text-dark" style="font-size:10px;"><?= esc($team['category']) ?></span>
        </div>
        <div style="font-size:13px;color:#666;" class="mb-1">
          <code><?= esc($team['jsca_team_id']) ?></code>
          <?php if ($team['district_name']): ?>&nbsp;·&nbsp; <?= esc($team['district_name']) ?><?php endif; ?>
          <?php if ($team['tournament_name']): ?>&nbsp;·&nbsp; <?= esc($team['tournament_name']) ?><?php endif; ?>
        </div>
        <div class="d-flex flex-wrap gap-3" style="font-size:12px;color:#888;">
          <?php if ($team['home_ground']): ?>
            <span><i class="bi bi-geo-alt me-1"></i><?= esc($team['home_ground']) ?></span>
          <?php endif; ?>
          <?php if ($team['jersey_color']): ?>
            <span><i class="bi bi-palette me-1"></i><?= esc($team['jersey_color']) ?></span>
          <?php endif; ?>
          <?php if ($team['captain_name']): ?>
            <span><i class="bi bi-star me-1"></i>Captain: <?= esc($team['captain_name']) ?></span>
          <?php endif; ?>
          <?php if ($team['vice_captain_name']): ?>
            <span><i class="bi bi-star-half me-1"></i>VC: <?= esc($team['vice_captain_name']) ?></span>
          <?php endif; ?>
        </div>
      </div>
      <?php if ($team['manager_name']): ?>
        <div class="text-end" style="flex-shrink:0;">
          <div style="font-size:11px;color:#aaa;">Team Manager</div>
          <div style="font-size:14px;font-weight:600;"><?= esc($team['manager_name']) ?></div>
          <div style="font-size:12px;color:#999;"><?= esc($team['manager_phone'] ?? '') ?></div>
        </div>
      <?php endif; ?>
    </div>
  </div>
</div>

<div class="row g-3">

  <!-- Squad -->
  <div class="col-lg-8">
    <div class="card mb-3">
      <div class="card-header d-flex justify-content-between align-items-center">
        <span><i class="bi bi-people me-2"></i>Squad <span class="text-muted fw-normal">(<?= count($players) ?>)</span></span>
        <button class="btn btn-sm btn-outline-secondary" data-bs-toggle="modal" data-bs-target="#addPlayerModal">
          + Add Player
        </button>
      </div>
      <div class="card-body p-0">
        <?php if (empty($players)): ?>
          <div class="text-center py-4 text-muted" style="font-size:13px;">
            No players in squad yet.
            <a href="#" data-bs-toggle="modal" data-bs-target="#addPlayerModal" class="ms-1">Add players →</a>
          </div>
        <?php else: ?>
          <?php foreach ($players as $p): ?>
            <div class="player-row">
              <div class="jersey"><?= $p['jersey_no'] ?? '—' ?></div>
              <?php if ($p['photo_path']): ?>
                <img src="<?= base_url('uploads/' . ltrim($p['photo_path'], 'uploads/')) ?>"
                  style="width:32px;height:32px;border-radius:50%;object-fit:cover;">
              <?php else: ?>
                <div class="avatar-circle" style="width:32px;height:32px;font-size:12px;">
                  <?= strtoupper(substr($p['full_name'], 0, 1)) ?>
                </div>
              <?php endif; ?>
              <div class="flex-grow-1">
                <div style="font-size:13px;font-weight:600;">
                  <a href="<?= base_url('players/view/' . $p['player_id']) ?>" class="text-decoration-none text-dark">
                    <?= esc($p['full_name']) ?>
                  </a>
                  <?php if ($p['aadhaar_verified']): ?>
                    <i class="bi bi-patch-check-fill text-success ms-1" style="font-size:11px;" title="Aadhaar Verified"></i>
                  <?php endif; ?>
                </div>
                <div style="font-size:11px;color:#999;">
                  <?= esc($p['role']) ?> · <?= esc($p['batting_style'] ?? '') ?>
                  <?php if ($p['bowling_style'] && $p['bowling_style'] !== 'N/A'): ?>
                    · <?= esc($p['bowling_style']) ?>
                  <?php endif; ?>
                </div>
              </div>
              <code style="font-size:10px;color:#aaa;"><?= esc($p['jsca_player_id']) ?></code>
              <form method="post" action="<?= base_url('teams/remove-player/' . $team['id'] . '/' . $p['player_id']) ?>"
                onsubmit="return confirm('Remove <?= esc($p['full_name']) ?> from squad?')">
                <?= csrf_field() ?>
                <button class="btn btn-xs btn-outline-danger" style="font-size:10px;padding:2px 8px;">Remove</button>
              </form>
            </div>
          <?php endforeach; ?>
        <?php endif; ?>
      </div>
    </div>

    <!-- Coaching Staff -->
    <div class="card">
      <div class="card-header d-flex justify-content-between align-items-center">
        <span><i class="bi bi-person-video3 me-2"></i>Coaching Staff</span>
        <button class="btn btn-sm btn-outline-secondary" data-bs-toggle="modal" data-bs-target="#addCoachModal">
          + Assign Coach
        </button>
      </div>
      <div class="card-body p-0">
        <?php if (empty($coaches)): ?>
          <div class="text-center py-4 text-muted" style="font-size:13px;">No coaches assigned yet.</div>
        <?php else: ?>
          <?php foreach ($coaches as $c): ?>
            <div class="player-row">
              <?php if ($c['photo_path']): ?>
                <img src="<?= base_url('uploads/' . ltrim($c['photo_path'], 'uploads/')) ?>"
                  style="width:36px;height:36px;border-radius:50%;object-fit:cover;">
              <?php else: ?>
                <div class="avatar-circle" style="width:36px;height:36px;font-size:13px;">
                  <?= strtoupper(substr($c['full_name'], 0, 1)) ?>
                </div>
              <?php endif; ?>
              <div class="flex-grow-1">
                <div style="font-size:13px;font-weight:600;">
                  <a href="<?= base_url('coaches/view/' . $c['coach_id']) ?>" class="text-decoration-none text-dark">
                    <?= esc($c['full_name']) ?>
                  </a>
                </div>
                <div style="font-size:11px;color:#999;"><?= esc($c['role']) ?> · <?= esc($c['level']) ?></div>
              </div>
              <code style="font-size:10px;color:#aaa;"><?= esc($c['jsca_coach_id']) ?></code>
              <form method="post" action="<?= base_url('teams/remove-coach/' . $team['id'] . '/' . $c['coach_id']) ?>"
                onsubmit="return confirm('Remove this coach?')">
                <?= csrf_field() ?>
                <button class="btn btn-xs btn-outline-danger" style="font-size:10px;padding:2px 8px;">Remove</button>
              </form>
            </div>
          <?php endforeach; ?>
        <?php endif; ?>
      </div>
    </div>
  </div>

  <!-- Documents -->
  <div class="col-lg-4">
    <div class="card">
      <div class="card-header d-flex justify-content-between align-items-center">
        <span>Documents</span>
        <button class="btn btn-sm btn-outline-secondary" data-bs-toggle="modal" data-bs-target="#uploadDocModal">
          + Upload
        </button>
      </div>
      <div class="card-body">
        <?php if (empty($documents)): ?>
          <div class="text-muted text-center py-2" style="font-size:13px;">No documents uploaded yet.</div>
        <?php else: ?>
          <?php foreach ($documents as $doc): ?>
            <div class="doc-card">
              <div style="font-size:22px;flex-shrink:0;">
                <?= $doc['mime_type'] === 'application/pdf'
                  ? '<i class="bi bi-file-earmark-pdf text-danger"></i>'
                  : '<i class="bi bi-file-earmark-image text-primary"></i>' ?>
              </div>
              <div class="flex-grow-1">
                <div class="doc-name">
                  <?= esc(ucwords(str_replace('_', ' ', $doc['doc_type']))) ?>
                  <?php if ($doc['label']): ?>
                    <span class="text-muted fw-normal"> — <?= esc($doc['label']) ?></span>
                  <?php endif; ?>
                </div>
                <div class="doc-meta">
                  <?= esc($doc['file_name']) ?>
                  <?php if ($doc['verified']): ?>
                    &nbsp;<span class="text-success"><i class="bi bi-patch-check-fill"></i> Verified</span>
                  <?php else: ?>
                    &nbsp;<span class="text-warning">Pending</span>
                  <?php endif; ?>
                </div>
              </div>
              <div class="d-flex flex-column gap-1">
                <a href="<?= base_url('writable/' . $doc['file_path']) ?>" target="_blank"
                  class="btn btn-xs btn-outline-secondary" style="font-size:10px;padding:2px 8px;">View</a>
                <?php if (!$doc['verified']): ?>
                  <form method="post" action="<?= base_url('teams/verify-doc/' . $doc['id']) ?>">
                    <?= csrf_field() ?>
                    <button class="btn btn-xs btn-outline-success w-100" style="font-size:10px;padding:2px 8px;">Verify</button>
                  </form>
                <?php endif; ?>
                <form method="post" action="<?= base_url('teams/delete-doc/' . $doc['id']) ?>"
                  onsubmit="return confirm('Delete this document?')">
                  <?= csrf_field() ?>
                  <button class="btn btn-xs btn-outline-danger w-100" style="font-size:10px;padding:2px 8px;">Delete</button>
                </form>
              </div>
            </div>
          <?php endforeach; ?>
        <?php endif; ?>
      </div>
    </div>
  </div>

</div>

<!-- Add Player Modal -->
<div class="modal fade" id="addPlayerModal" tabindex="-1">
  <div class="modal-dialog modal-dialog-scrollable">
    <form method="post" action="<?= base_url('teams/add-player/' . $team['id']) ?>">
      <?= csrf_field() ?>
      <div class="modal-content">
        <div class="modal-header border-0 pb-0">
          <h6 class="modal-title fw-bold">Add Player to Squad</h6>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body pt-2">
          <div class="mb-3">
            <label class="form-label" style="font-size:12px;font-weight:600;">Player <span class="text-danger">*</span></label>
            <select name="player_id" class="form-select form-select-sm" required>
              <option value="">Select player…</option>
              <?php foreach ($availablePlayers as $p): ?>
                <option value="<?= $p['id'] ?>">
                  <?= esc($p['full_name']) ?> — <?= esc($p['jsca_player_id']) ?> (<?= esc($p['role']) ?>)
                </option>
              <?php endforeach; ?>
            </select>
            <?php if (empty($availablePlayers)): ?>
              <div class="form-text text-warning" style="font-size:11px;">
                No available <?= esc($team['category']) ?> players. Register players first.
              </div>
            <?php endif; ?>
          </div>
          <div>
            <label class="form-label" style="font-size:12px;font-weight:600;">Jersey Number</label>
            <input type="number" name="jersey_no" class="form-control form-control-sm" min="1" max="99" placeholder="1–99">
          </div>
        </div>
        <div class="modal-footer border-0 pt-0">
          <button type="button" class="btn btn-sm btn-light" data-bs-dismiss="modal">Cancel</button>
          <button type="submit" class="btn btn-sm btn-jsca-primary">Add to Squad</button>
        </div>
      </div>
    </form>
  </div>
</div>

<!-- Add Coach Modal -->
<div class="modal fade" id="addCoachModal" tabindex="-1">
  <div class="modal-dialog">
    <form method="post" action="<?= base_url('teams/add-coach/' . $team['id']) ?>">
      <?= csrf_field() ?>
      <div class="modal-content">
        <div class="modal-header border-0 pb-0">
          <h6 class="modal-title fw-bold">Assign Coach</h6>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body pt-2">
          <div class="mb-3">
            <label class="form-label" style="font-size:12px;font-weight:600;">Coach <span class="text-danger">*</span></label>
            <select name="coach_id" class="form-select form-select-sm" required>
              <option value="">Select coach…</option>
              <?php foreach ($availableCoaches as $c): ?>
                <option value="<?= $c['id'] ?>"><?= esc($c['full_name']) ?> — <?= esc($c['level']) ?></option>
              <?php endforeach; ?>
            </select>
          </div>
          <div>
            <label class="form-label" style="font-size:12px;font-weight:600;">Role in Team</label>
            <select name="coach_role" class="form-select form-select-sm">
              <?php foreach (['Head Coach','Assistant Coach','Bowling Coach','Batting Coach','Fielding Coach','Fitness Trainer'] as $r): ?>
                <option value="<?= $r ?>"><?= $r ?></option>
              <?php endforeach; ?>
            </select>
          </div>
        </div>
        <div class="modal-footer border-0 pt-0">
          <button type="button" class="btn btn-sm btn-light" data-bs-dismiss="modal">Cancel</button>
          <button type="submit" class="btn btn-sm btn-jsca-primary">Assign</button>
        </div>
      </div>
    </form>
  </div>
</div>

<!-- Upload Document Modal -->
<div class="modal fade" id="uploadDocModal" tabindex="-1">
  <div class="modal-dialog">
    <form method="post" action="<?= base_url('teams/upload-doc/' . $team['id']) ?>" enctype="multipart/form-data">
      <?= csrf_field() ?>
      <div class="modal-content">
        <div class="modal-header border-0 pb-0">
          <h6 class="modal-title fw-bold">Upload Document</h6>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body pt-2">
          <div class="mb-3">
            <label class="form-label" style="font-size:12px;font-weight:600;">Document Type <span class="text-danger">*</span></label>
            <select name="doc_type" id="docTypeSelect" class="form-select form-select-sm" required>
              <option value="">Select type…</option>
              <option value="registration_form">Team Registration Form</option>
              <option value="affiliation_certificate">Affiliation Certificate</option>
              <option value="noc">NOC (No Objection Certificate)</option>
              <option value="player_consent">Player Consent Forms</option>
              <option value="insurance">Team Insurance</option>
              <option value="other">Other</option>
            </select>
          </div>
          <div class="mb-3" id="labelField" style="display:none;">
            <label class="form-label" style="font-size:12px;font-weight:600;">Label</label>
            <input type="text" name="label" class="form-control form-control-sm" placeholder="Describe the document">
          </div>
          <div>
            <label class="form-label" style="font-size:12px;font-weight:600;">File <span class="text-danger">*</span></label>
            <input type="file" name="document" class="form-control form-control-sm" accept=".jpg,.jpeg,.png,.pdf" required>
            <div class="form-text" style="font-size:11px;">JPG, PNG or PDF · max 5MB</div>
          </div>
        </div>
        <div class="modal-footer border-0 pt-0">
          <button type="button" class="btn btn-sm btn-light" data-bs-dismiss="modal">Cancel</button>
          <button type="submit" class="btn btn-sm btn-jsca-primary">Upload</button>
        </div>
      </div>
    </form>
  </div>
</div>

<script>
  document.getElementById('docTypeSelect').addEventListener('change', function() {
    document.getElementById('labelField').style.display = this.value === 'other' ? 'block' : 'none';
  });
</script>
