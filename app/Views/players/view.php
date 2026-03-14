<!-- app/Views/players/view.php -->
<style>
  .doc-card {
    border: 1px solid #eee;
    border-radius: 8px;
    padding: 12px 14px;
    display: flex;
    align-items: center;
    gap: 12px;
    margin-bottom: 8px;
    background: #fff;
  }
  .doc-card .doc-icon { font-size: 22px; color: #1a3a5c; flex-shrink: 0; }
  .doc-card .doc-name { font-size: 13px; font-weight: 600; }
  .doc-card .doc-meta { font-size: 11px; color: #999; }
  .stat-box { text-align: center; padding: 14px 10px; }
  .stat-box .val { font-size: 22px; font-weight: 800; color: #1a3a5c; }
  .stat-box .lbl { font-size: 10px; color: #999; text-transform: uppercase; letter-spacing: .06em; }
</style>

<div class="d-flex justify-content-between align-items-start mb-3">
  <a href="<?= base_url('players') ?>" class="text-muted" style="font-size:13px;">
    <i class="bi bi-arrow-left me-1"></i>Back to Players
  </a>
  <div class="d-flex gap-2">
    <a href="<?= base_url('players/edit/' . $player['id']) ?>" class="btn btn-sm btn-outline-secondary">Edit Profile</a>
    <?php if ($player['status'] === 'Active'): ?>
      <form method="post" action="<?= base_url('players/delete/' . $player['id']) ?>"
        onsubmit="return confirm('Deactivate this player?')">
        <?= csrf_field() ?>
        <button class="btn btn-sm btn-outline-danger">Deactivate</button>
      </form>
    <?php endif; ?>
  </div>
</div>

<div class="row g-3">

  <!-- Profile Header -->
  <div class="col-12">
    <div class="card">
      <div class="card-body">
        <div class="d-flex gap-4 align-items-center">
          <?php if ($player['photo_path']): ?>
            <img src="<?= base_url('uploads/' . ltrim($player['photo_path'], 'uploads/')) ?>"
              style="width:90px;height:105px;object-fit:cover;border-radius:8px;border:2px solid #eee;">
          <?php else: ?>
            <div style="width:90px;height:105px;border-radius:8px;background:#1a3a5c;display:flex;
              align-items:center;justify-content:center;font-size:36px;font-weight:800;color:#fff;flex-shrink:0;">
              <?= strtoupper(substr($player['full_name'], 0, 1)) ?>
            </div>
          <?php endif; ?>
          <div class="flex-grow-1">
            <div class="d-flex align-items-center gap-2 mb-1">
              <h5 class="mb-0 fw-bold"><?= esc($player['full_name']) ?></h5>
              <span class="badge <?= $player['status'] === 'Active' ? 'bg-success' : 'bg-secondary' ?>"
                style="font-size:10px;"><?= esc($player['status']) ?></span>
              <?php if ($player['aadhaar_verified']): ?>
                <span class="text-success" style="font-size:12px;" title="Aadhaar Verified">
                  <i class="bi bi-patch-check-fill"></i>
                </span>
              <?php endif; ?>
            </div>
            <div style="font-size:13px;color:#666;" class="mb-2">
              <code><?= esc($player['jsca_player_id']) ?></code>
              &nbsp;·&nbsp; <?= esc($player['role']) ?>
              &nbsp;·&nbsp; <?= esc($player['age_category']) ?>
              &nbsp;·&nbsp; <?= esc($player['district_name']) ?>
              <?php if ($player['zone']): ?>&nbsp;(<?= esc($player['zone']) ?> Zone)<?php endif; ?>
            </div>
            <div class="d-flex gap-3" style="font-size:12px;color:#888;">
              <?php if ($player['phone']): ?>
                <span><i class="bi bi-phone me-1"></i><?= esc($player['phone']) ?></span>
              <?php endif; ?>
              <?php if ($player['email']): ?>
                <span><i class="bi bi-envelope me-1"></i><?= esc($player['email']) ?></span>
              <?php endif; ?>
              <span><i class="bi bi-calendar me-1"></i>
                DOB: <?= date('d M Y', strtotime($player['date_of_birth'])) ?>
                (<?= (int)date_diff(date_create($player['date_of_birth']), date_create('now'))->y ?> yrs)
              </span>
            </div>
          </div>
          <div class="text-end" style="flex-shrink:0;">
            <div style="font-size:11px;color:#aaa;">Selection Pool</div>
            <div style="font-size:15px;font-weight:700;color:#1a3a5c;"><?= esc($player['selection_pool']) ?></div>
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- Career Stats -->
  <div class="col-lg-8">
    <div class="card mb-3">
      <div class="card-header">Career Statistics</div>
      <div class="card-body p-0">
        <div class="row g-0 border-bottom">
          <?php
            $batting = [
              'Matches'   => $stats['matches']       ?? 0,
              'Runs'      => $stats['runs']           ?? 0,
              'High Score'=> $stats['highest_score']  ?? 0,
              'Avg'       => $stats['batting_avg']    ?? '0.00',
              'SR'        => $stats['strike_rate']    ?? '0.00',
              '50s'       => $stats['fifties']        ?? 0,
              '100s'      => $stats['hundreds']       ?? 0,
            ];
          ?>
          <?php foreach ($batting as $lbl => $val): ?>
            <div class="col stat-box border-end">
              <div class="val"><?= $val ?></div>
              <div class="lbl"><?= $lbl ?></div>
            </div>
          <?php endforeach; ?>
        </div>
        <div class="row g-0">
          <?php
            $bowling = [
              'Wickets' => $stats['wickets']     ?? 0,
              'Bowl Avg'=> $stats['bowling_avg'] ?? '0.00',
              'Economy' => $stats['economy']     ?? '0.00',
            ];
          ?>
          <?php foreach ($bowling as $lbl => $val): ?>
            <div class="col stat-box border-end">
              <div class="val"><?= $val ?></div>
              <div class="lbl"><?= $lbl ?></div>
            </div>
          <?php endforeach; ?>
          <div class="col"></div><div class="col"></div><div class="col"></div><div class="col"></div>
        </div>
      </div>
    </div>

    <!-- Recent Matches -->
    <?php if (!empty($recentMatches)): ?>
      <div class="card">
        <div class="card-header">Recent Innings</div>
        <div class="card-body p-0">
          <div class="table-responsive">
            <table class="table mb-0">
              <thead>
                <tr>
                  <th>Date</th>
                  <th>vs</th>
                  <th>Tournament</th>
                  <th>Runs</th>
                  <th>Balls</th>
                  <th>Dismissal</th>
                </tr>
              </thead>
              <tbody>
                <?php foreach ($recentMatches as $m): ?>
                  <tr>
                    <td style="font-size:12px;"><?= date('d M Y', strtotime($m['match_date'])) ?></td>
                    <td style="font-size:13px;"><?= esc($m['opponent_name']) ?></td>
                    <td style="font-size:12px;color:#999;"><?= esc($m['tournament_name']) ?></td>
                    <td><strong><?= $m['runs'] ?></strong><?= $m['dismissal'] === 'not out' ? '*' : '' ?></td>
                    <td style="font-size:12px;"><?= $m['balls_faced'] ?></td>
                    <td style="font-size:12px;color:#999;"><?= esc($m['dismissal']) ?></td>
                  </tr>
                <?php endforeach; ?>
              </tbody>
            </table>
          </div>
        </div>
      </div>
    <?php endif; ?>
  </div>

  <!-- Right: Info + Documents -->
  <div class="col-lg-4">

    <!-- Personal Details -->
    <div class="card mb-3">
      <div class="card-header">Details</div>
      <div class="card-body p-0">
        <?php
          $details = [
            'Batting'  => $player['batting_style'] ?? '—',
            'Bowling'  => $player['bowling_style'] ?? '—',
            'Aadhaar'  => $player['aadhaar_number'] ? '••••' . substr($player['aadhaar_number'], -4) : '—',
            'Guardian' => $player['guardian_name'] ?? '—',
            'Guardian Ph' => $player['guardian_phone'] ?? '—',
            'Address'  => $player['address'] ?? '—',
          ];
        ?>
        <?php foreach ($details as $k => $v): ?>
          <div class="d-flex px-3 py-2 border-bottom" style="font-size:13px;">
            <span class="text-muted" style="width:90px;flex-shrink:0;"><?= $k ?></span>
            <span><?= esc($v) ?></span>
          </div>
        <?php endforeach; ?>
      </div>
    </div>

    <!-- Documents -->
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
              <div class="doc-icon">
                <?= $doc['mime_type'] === 'application/pdf' ? '<i class="bi bi-file-earmark-pdf text-danger"></i>' : '<i class="bi bi-file-earmark-image text-primary"></i>' ?>
              </div>
              <div class="flex-grow-1">
                <div class="doc-name"><?= esc(ucwords(str_replace('_', ' ', $doc['doc_type']))) ?>
                  <?php if ($doc['label']): ?><span class="text-muted fw-normal"> — <?= esc($doc['label']) ?></span><?php endif; ?>
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
                  <form method="post" action="<?= base_url('players/verify-doc/' . $doc['id']) ?>">
                    <?= csrf_field() ?>
                    <button class="btn btn-xs btn-outline-success w-100" style="font-size:10px;padding:2px 8px;">Verify</button>
                  </form>
                <?php endif; ?>
                <form method="post" action="<?= base_url('players/delete-doc/' . $doc['id']) ?>"
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

<!-- Upload Document Modal -->
<div class="modal fade" id="uploadDocModal" tabindex="-1">
  <div class="modal-dialog">
    <form method="post" action="<?= base_url('players/upload-doc/' . $player['id']) ?>" enctype="multipart/form-data">
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
              <option value="aadhaar_front">Aadhaar Card — Front</option>
              <option value="aadhaar_back">Aadhaar Card — Back</option>
              <option value="birth_certificate">Birth Certificate</option>
              <option value="school_certificate">School Certificate</option>
              <option value="noc">NOC (No Objection Certificate)</option>
              <option value="medical_fitness">Medical Fitness Certificate</option>
              <option value="photo">Passport Photo</option>
              <option value="other">Other</option>
            </select>
          </div>
          <div class="mb-3" id="labelField" style="display:none;">
            <label class="form-label" style="font-size:12px;font-weight:600;">Document Label</label>
            <input type="text" name="label" class="form-control form-control-sm" placeholder="e.g. Transfer Certificate">
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
