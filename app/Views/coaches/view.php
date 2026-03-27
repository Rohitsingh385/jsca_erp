<!-- app/Views/coaches/view.php -->
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
  .doc-card .doc-icon { font-size: 22px; flex-shrink: 0; }
  .doc-card .doc-name { font-size: 13px; font-weight: 600; }
  .doc-card .doc-meta { font-size: 11px; color: #999; }
</style>

<div class="d-flex justify-content-between align-items-start mb-3">
  <a href="<?= base_url('coaches') ?>" class="text-muted" style="font-size:13px;">
    <i class="bi bi-arrow-left me-1"></i>Back to Coaches
  </a>
  <div class="d-flex gap-2">
    <a href="<?= base_url('coaches/edit/' . $coach['id']) ?>" class="btn btn-sm btn-outline-secondary">Edit Profile</a>
    <?php if ($coach['status'] === 'Active'): ?>
      <form method="post" action="<?= base_url('coaches/delete/' . $coach['id']) ?>"
        onsubmit="return confirm('Deactivate this coach?')">
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
          <?php if ($coach['photo_path']): ?>
            <img src="<?= base_url($coach['photo_path']) ?>"
              style="width:90px;height:105px;object-fit:cover;border-radius:8px;border:2px solid #eee;">
          <?php else: ?>
            <div style="width:90px;height:105px;border-radius:8px;background:#1a3a5c;display:flex;
              align-items:center;justify-content:center;font-size:36px;font-weight:800;color:#fff;flex-shrink:0;">
              <?= strtoupper(substr($coach['full_name'], 0, 1)) ?>
            </div>
          <?php endif; ?>
          <div class="flex-grow-1">
            <div class="d-flex align-items-center gap-2 mb-1">
              <h5 class="mb-0 fw-bold"><?= esc($coach['full_name']) ?></h5>
              <span class="badge <?= $coach['status'] === 'Active' ? 'bg-success' : 'bg-secondary' ?>"
                style="font-size:10px;"><?= esc($coach['status']) ?></span>
              <?php if ($coach['aadhaar_verified']): ?>
                <span class="text-success" style="font-size:12px;" title="Aadhaar Verified">
                  <i class="bi bi-patch-check-fill"></i>
                </span>
              <?php endif; ?>
            </div>
            <div style="font-size:13px;color:#666;" class="mb-2">
              <code><?= esc($coach['jsca_coach_id']) ?></code>
              &nbsp;·&nbsp; <?= esc($coach['level']) ?>
              &nbsp;·&nbsp; <?= esc($coach['specialization']) ?> Coach
              <?php if ($coach['district_name']): ?>
                &nbsp;·&nbsp; <?= esc($coach['district_name']) ?>
              <?php endif; ?>
            </div>
            <div class="d-flex flex-wrap gap-3" style="font-size:12px;color:#888;">
              <?php if ($coach['phone']): ?>
                <span><i class="bi bi-phone me-1"></i><?= esc($coach['phone']) ?></span>
              <?php endif; ?>
              <?php if ($coach['email']): ?>
                <span><i class="bi bi-envelope me-1"></i><?= esc($coach['email']) ?></span>
              <?php endif; ?>
              <span><i class="bi bi-calendar me-1"></i>
                DOB: <?= date('d M Y', strtotime($coach['date_of_birth'])) ?>
                (<?= (int)date_diff(date_create($coach['date_of_birth']), date_create('now'))->y ?> yrs)
              </span>
              <span><i class="bi bi-briefcase me-1"></i><?= $coach['experience_years'] ?> yrs experience</span>
            </div>
          </div>
          <?php if ($coach['bcci_coach_id']): ?>
            <div class="text-end" style="flex-shrink:0;">
              <div style="font-size:11px;color:#aaa;">BCCI Coach ID</div>
              <div style="font-size:14px;font-weight:700;color:#1a3a5c;"><?= esc($coach['bcci_coach_id']) ?></div>
            </div>
          <?php endif; ?>
        </div>
      </div>
    </div>
  </div>

  <!-- Left: Details + Teams -->
  <div class="col-lg-8">

    <!-- Details -->
    <div class="card mb-3">
      <div class="card-header">Profile Details</div>
      <div class="card-body p-0">
        <?php
          $details = [
            'Aadhaar'       => $coach['aadhaar_number'] ? '••••' . substr($coach['aadhaar_number'], -4) : '—',
            'Previous Teams'=> $coach['previous_teams'] ?? '—',
            'Address'       => $coach['address'] ?? '—',
          ];
        ?>
        <?php foreach ($details as $k => $v): ?>
          <div class="d-flex px-3 py-2 border-bottom" style="font-size:13px;">
            <span class="text-muted" style="width:120px;flex-shrink:0;"><?= $k ?></span>
            <span><?= esc($v) ?></span>
          </div>
        <?php endforeach; ?>
        <?php if ($coach['achievements']): ?>
          <div class="px-3 py-2" style="font-size:13px;">
            <span class="text-muted d-block mb-1">Achievements</span>
            <span><?= nl2br(esc($coach['achievements'])) ?></span>
          </div>
        <?php endif; ?>
      </div>
    </div>

    <!-- Team Assignments -->
    <div class="card">
      <div class="card-header">Team Assignments</div>
      <div class="card-body p-0">
        <?php if (empty($teams)): ?>
          <div class="text-center py-4 text-muted" style="font-size:13px;">
            Not assigned to any team yet.
          </div>
        <?php else: ?>
          <div class="table-responsive">
            <table class="table mb-0">
              <thead>
                <tr>
                  <th>Team</th>
                  <th>Tournament</th>
                  <th>Role</th>
                  <th>From</th>
                  <th>To</th>
                  <th>Status</th>
                </tr>
              </thead>
              <tbody>
                <?php foreach ($teams as $t): ?>
                  <tr>
                    <td style="font-size:13px;font-weight:600;">
                      <a href="<?= base_url('teams/view/' . $t['team_id']) ?>" class="text-decoration-none">
                        <?= esc($t['team_name']) ?>
                      </a>
                    </td>
                    <td style="font-size:12px;color:#999;"><?= esc($t['tournament_name'] ?? '—') ?></td>
                    <td style="font-size:13px;"><?= esc($t['role']) ?></td>
                    <td style="font-size:12px;"><?= $t['from_date'] ? date('d M Y', strtotime($t['from_date'])) : '—' ?></td>
                    <td style="font-size:12px;"><?= $t['to_date']   ? date('d M Y', strtotime($t['to_date']))   : '—' ?></td>
                    <td>
                      <?php if ($t['is_current']): ?>
                        <span class="badge bg-success" style="font-size:10px;">Current</span>
                      <?php else: ?>
                        <span class="badge bg-secondary" style="font-size:10px;">Past</span>
                      <?php endif; ?>
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

  <!-- Right: Documents -->
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
              <div class="doc-icon">
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
                <a href="<?= base_url($doc['file_path']) ?>" target="_blank"
                  class="btn btn-xs btn-outline-secondary" style="font-size:10px;padding:2px 8px;">View</a>
                <?php if (!$doc['verified']): ?>
                  <form method="post" action="<?= base_url('coaches/verify-doc/' . $doc['id']) ?>">
                    <?= csrf_field() ?>
                    <button class="btn btn-xs btn-outline-success w-100" style="font-size:10px;padding:2px 8px;">Verify</button>
                  </form>
                <?php endif; ?>
                <form method="post" action="<?= base_url('coaches/delete-doc/' . $doc['id']) ?>"
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
    <form method="post" action="<?= base_url('coaches/upload-doc/' . $coach['id']) ?>" enctype="multipart/form-data">
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
              <option value="coaching_certificate">Coaching Certificate</option>
              <option value="bcci_certificate">BCCI Certificate</option>
              <option value="nca_certificate">NCA Certificate</option>
              <option value="medical_fitness">Medical Fitness Certificate</option>
              <option value="police_verification">Police Verification</option>
              <option value="photo">Passport Photo</option>
              <option value="other">Other</option>
            </select>
          </div>
          <div class="mb-3" id="labelField" style="display:none;">
            <label class="form-label" style="font-size:12px;font-weight:600;">Document Label</label>
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
