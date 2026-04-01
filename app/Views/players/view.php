<!-- app/Views/players/view.php -->
<style>
  .doc-card {
    border: 1px solid #eee; border-radius: 8px; padding: 12px 14px;
    display: flex; align-items: center; gap: 12px; margin-bottom: 8px; background: #fff;
  }
  .doc-card .doc-icon { font-size: 22px; color: #1a3a5c; flex-shrink: 0; }
  .doc-card .doc-name { font-size: 13px; font-weight: 600; }
  .doc-card .doc-meta { font-size: 11px; color: #999; }

  /* Stat tiles */
  .stat-tile {
    border-radius: 12px; padding: 16px 12px; text-align: center;
    color: #fff; position: relative; overflow: hidden;
  }
  .stat-tile .st-val { font-size: 28px; font-weight: 900; line-height: 1; }
  .stat-tile .st-lbl { font-size: 10px; text-transform: uppercase; letter-spacing: .08em; opacity: .85; margin-top: 4px; }
  .stat-tile .st-icon { position: absolute; right: 10px; top: 10px; font-size: 28px; opacity: .18; }

  .tile-blue   { background: linear-gradient(135deg,#1a3a5c,#2563a8); }
  .tile-green  { background: linear-gradient(135deg,#16a34a,#2ecc71); }
  .tile-orange { background: linear-gradient(135deg,#ea580c,#f97316); }
  .tile-purple { background: linear-gradient(135deg,#7c3aed,#a855f7); }
  .tile-teal   { background: linear-gradient(135deg,#0891b2,#22d3ee); }
  .tile-red    { background: linear-gradient(135deg,#dc2626,#f87171); }
  .tile-indigo { background: linear-gradient(135deg,#4338ca,#818cf8); }
  .tile-amber  { background: linear-gradient(135deg,#b45309,#fbbf24); }

  /* Milestone badges */
  .milestone-badge {
    display: inline-flex; align-items: center; gap: 6px;
    background: #f8f9fa; border: 1px solid #e9ecef;
    border-radius: 20px; padding: 5px 12px; font-size: 12px; font-weight: 600;
  }
  .milestone-badge .dot { width:8px;height:8px;border-radius:50%; }

  /* Skill bar */
  .skill-bar { height: 8px; background: #e9ecef; border-radius: 4px; overflow: hidden; margin-top: 4px; }
  .skill-fill { height: 100%; border-radius: 4px; }
</style>

<div class="d-flex justify-content-between align-items-start mb-3">
  <a href="<?= base_url('players') ?>" class="text-muted" style="font-size:13px;">
    <i class="bi bi-arrow-left me-1"></i>Back to Players
  </a>
  <div class="d-flex gap-2">
    <a href="<?= base_url('players/edit/' . $player['id']) ?>" class="btn btn-sm btn-outline-secondary">Edit Profile</a>
    <?php if (($player['status'] ?? '') === 'Inactive' && ($player['registration_type'] ?? '') === 'self'): ?>
      <form method="post" action="<?= base_url('players/verify/' . $player['id']) ?>"
        onsubmit="return confirm('Verify and activate this player?')">
        <?= csrf_field() ?>
        <button class="btn btn-sm btn-success"><i class="bi bi-patch-check me-1"></i>Verify & Activate</button>
      </form>
    <?php endif; ?>
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
            <img src="<?= base_url($player['photo_path']) ?>"
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
              <?php if (($player['registration_type'] ?? '') === 'self' && $player['status'] === 'Inactive'): ?>
                <span class="badge bg-warning text-dark" style="font-size:10px;">Pending Verification</span>
              <?php endif; ?>
              <?php if (($player['registration_type'] ?? '') === 'self'): ?>
                <span class="badge bg-info text-dark" style="font-size:10px;">Self Registered</span>
              <?php endif; ?>
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

    <!-- Batting Tiles -->
    <div class="card mb-3">
      <div class="card-header d-flex align-items-center gap-2">
        <i class="bi bi-bar-chart-fill text-primary"></i> Batting Career
      </div>
      <div class="card-body">
        <div class="row g-2 mb-3">
          <?php
            $tiles = [
              ['val'=>$stats['matches']??0,       'lbl'=>'Matches',    'icon'=>'bi-calendar3',         'cls'=>'tile-blue'],
              ['val'=>$stats['runs']??0,           'lbl'=>'Total Runs', 'icon'=>'bi-lightning-charge',  'cls'=>'tile-green'],
              ['val'=>$stats['highest_score']??0,  'lbl'=>'High Score', 'icon'=>'bi-trophy',            'cls'=>'tile-orange'],
              ['val'=>$stats['batting_avg']??'0',  'lbl'=>'Average',    'icon'=>'bi-graph-up',          'cls'=>'tile-purple'],
              ['val'=>$stats['strike_rate']??'0',  'lbl'=>'Strike Rate','icon'=>'bi-speedometer2',      'cls'=>'tile-teal'],
              ['val'=>$stats['fifties']??0,        'lbl'=>'Fifties',    'icon'=>'bi-star',              'cls'=>'tile-indigo'],
              ['val'=>$stats['hundreds']??0,       'lbl'=>'Hundreds',   'icon'=>'bi-star-fill',         'cls'=>'tile-amber'],
              ['val'=>$stats['catches']??0,        'lbl'=>'Catches',    'icon'=>'bi-hand-index',        'cls'=>'tile-red'],
            ];
          ?>
          <?php foreach ($tiles as $t): ?>
            <div class="col-6 col-md-3">
              <div class="stat-tile <?= $t['cls'] ?>">
                <i class="bi <?= $t['icon'] ?> st-icon"></i>
                <div class="st-val"><?= $t['val'] ?></div>
                <div class="st-lbl"><?= $t['lbl'] ?></div>
              </div>
            </div>
          <?php endforeach; ?>
        </div>

        <!-- Milestone badges -->
        <div class="d-flex flex-wrap gap-2 mb-3">
          <?php if (($stats['hundreds']??0) >= 1): ?>
            <span class="milestone-badge"><span class="dot" style="background:#f97316;"></span><?= $stats['hundreds'] ?> × Century</span>
          <?php endif; ?>
          <?php if (($stats['fifties']??0) >= 1): ?>
            <span class="milestone-badge"><span class="dot" style="background:#7c3aed;"></span><?= $stats['fifties'] ?> × Half-Century</span>
          <?php endif; ?>
          <?php if (($stats['highest_score']??0) >= 100): ?>
            <span class="milestone-badge"><span class="dot" style="background:#16a34a;"></span>HS <?= $stats['highest_score'] ?></span>
          <?php endif; ?>
          <?php if (($stats['batting_avg']??0) >= 40): ?>
            <span class="milestone-badge"><span class="dot" style="background:#0891b2;"></span>Avg 40+</span>
          <?php endif; ?>
        </div>

        <!-- Skill bars -->
        <div class="row g-3">
          <div class="col-md-6">
            <div style="font-size:12px;font-weight:600;color:#555;">Batting Average</div>
            <div class="skill-bar"><div class="skill-fill" style="width:<?= min(100, round(($stats['batting_avg']??0)/80*100)) ?>%;background:linear-gradient(90deg,#7c3aed,#a855f7);"></div></div>
            <div style="font-size:11px;color:#999;margin-top:2px;"><?= $stats['batting_avg']??0 ?> / 80 benchmark</div>
          </div>
          <div class="col-md-6">
            <div style="font-size:12px;font-weight:600;color:#555;">Strike Rate</div>
            <div class="skill-bar"><div class="skill-fill" style="width:<?= min(100, round(($stats['strike_rate']??0)/150*100)) ?>%;background:linear-gradient(90deg,#0891b2,#22d3ee);"></div></div>
            <div style="font-size:11px;color:#999;margin-top:2px;"><?= $stats['strike_rate']??0 ?> / 150 benchmark</div>
          </div>
        </div>
      </div>
    </div>

    <!-- Bowling Tiles -->
    <?php if (($stats['wickets']??0) > 0): ?>
    <div class="card mb-3">
      <div class="card-header d-flex align-items-center gap-2">
        <i class="bi bi-circle-fill text-danger" style="font-size:10px;"></i> Bowling Career
      </div>
      <div class="card-body">
        <div class="row g-2 mb-3">
          <?php $bTiles = [
            ['val'=>$stats['wickets']??0,     'lbl'=>'Wickets',    'icon'=>'bi-bullseye',    'cls'=>'tile-red'],
            ['val'=>$stats['best_bowling']??'—','lbl'=>'Best',     'icon'=>'bi-trophy',      'cls'=>'tile-orange'],
            ['val'=>$stats['bowling_avg']??0, 'lbl'=>'Bowl Avg',   'icon'=>'bi-graph-down',  'cls'=>'tile-indigo'],
            ['val'=>$stats['economy']??0,     'lbl'=>'Economy',    'icon'=>'bi-speedometer', 'cls'=>'tile-teal'],
          ]; ?>
          <?php foreach ($bTiles as $t): ?>
            <div class="col-6 col-md-3">
              <div class="stat-tile <?= $t['cls'] ?>">
                <i class="bi <?= $t['icon'] ?> st-icon"></i>
                <div class="st-val"><?= $t['val'] ?></div>
                <div class="st-lbl"><?= $t['lbl'] ?></div>
              </div>
            </div>
          <?php endforeach; ?>
        </div>
      </div>
    </div>
    <?php endif; ?>

    <!-- Charts -->
    <div class="row g-3 mb-3">
      <div class="col-md-7">
        <div class="card">
          <div class="card-header" style="font-size:13px;">Runs per Season</div>
          <div class="card-body"><canvas id="runsChart" height="160"></canvas></div>
        </div>
      </div>
      <div class="col-md-5">
        <div class="card">
          <div class="card-header" style="font-size:13px;">Innings Breakdown</div>
          <div class="card-body d-flex align-items-center justify-content-center"><canvas id="inningsChart" height="160"></canvas></div>
        </div>
      </div>
    </div>

    <!-- Recent Innings -->
    <div class="card">
      <div class="card-header">Recent Innings</div>
      <div class="card-body p-0">
        <?php
        // Fake recent innings for demo
        $fakeInnings = [
          ['date'=>'28 Mar 2026','vs'=>'Dhanbad Warriors','tournament'=>'Ranji Trophy 2026','runs'=>87, 'balls'=>102,'dismissal'=>'c Patel b Yadav'],
          ['date'=>'21 Mar 2026','vs'=>'Bokaro XI',       'tournament'=>'Ranji Trophy 2026','runs'=>143,'balls'=>178,'dismissal'=>'not out'],
          ['date'=>'14 Mar 2026','vs'=>'Jamshedpur CC',   'tournament'=>'Ranji Trophy 2026','runs'=>34, 'balls'=>51, 'dismissal'=>'lbw b Mishra'],
          ['date'=>'05 Mar 2026','vs'=>'Hazaribagh XI',   'tournament'=>'JSCA District Cup', 'runs'=>72, 'balls'=>88, 'dismissal'=>'b Singh'],
          ['date'=>'22 Feb 2026','vs'=>'Giridih CC',      'tournament'=>'JSCA District Cup', 'runs'=>11, 'balls'=>18, 'dismissal'=>'c & b Kumar'],
          ['date'=>'15 Feb 2026','vs'=>'Deoghar XI',      'tournament'=>'JSCA District Cup', 'runs'=>58, 'balls'=>71, 'dismissal'=>'run out'],
          ['date'=>'08 Feb 2026','vs'=>'Palamu Warriors', 'tournament'=>'U23 State Trophy',  'runs'=>101,'balls'=>134,'dismissal'=>'not out'],
          ['date'=>'01 Feb 2026','vs'=>'Dumka XI',        'tournament'=>'U23 State Trophy',  'runs'=>45, 'balls'=>62, 'dismissal'=>'c Sharma b Roy'],
        ];
        $innings = !empty($recentMatches) ? $recentMatches : [];
        ?>
        <div class="table-responsive">
          <table class="table mb-0" style="font-size:13px;">
            <thead class="table-light">
              <tr>
                <th>Date</th><th>vs</th><th>Tournament</th>
                <th class="text-center">R</th><th class="text-center">B</th>
                <th class="text-center">SR</th><th>Dismissal</th>
              </tr>
            </thead>
            <tbody>
              <?php
              $rows = !empty($innings) ? array_map(fn($m) => [
                'date'       => date('d M Y', strtotime($m['match_date'])),
                'vs'         => $m['opponent_name'],
                'tournament' => $m['tournament_name'],
                'runs'       => $m['runs'],
                'balls'      => $m['balls_faced'],
                'dismissal'  => $m['dismissal'],
              ], $innings) : $fakeInnings;
              foreach ($rows as $row):
                $sr = $row['balls'] > 0 ? number_format($row['runs']/$row['balls']*100,1) : '—';
                $isNotOut = $row['dismissal'] === 'not out';
                $runClass = $row['runs'] >= 100 ? 'text-warning fw-bold' : ($row['runs'] >= 50 ? 'text-success fw-bold' : '');
              ?>
              <tr>
                <td style="font-size:12px;color:#999;"><?= esc($row['date']) ?></td>
                <td><?= esc($row['vs']) ?></td>
                <td style="font-size:12px;color:#999;"><?= esc($row['tournament']) ?></td>
                <td class="text-center <?= $runClass ?>">
                  <?= $row['runs'] ?><?= $isNotOut ? '<span style="color:#16a34a;">*</span>' : '' ?>
                  <?php if ($row['runs'] >= 100): ?><i class="bi bi-star-fill text-warning ms-1" style="font-size:9px;"></i><?php endif; ?>
                  <?php if ($row['runs'] >= 50 && $row['runs'] < 100): ?><i class="bi bi-star text-success ms-1" style="font-size:9px;"></i><?php endif; ?>
                </td>
                <td class="text-center text-muted"><?= $row['balls'] ?></td>
                <td class="text-center text-muted"><?= $sr ?></td>
                <td style="font-size:12px;color:#888;"><?= esc($row['dismissal']) ?></td>
              </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>
      </div>
    </div>

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
                <a href="<?= base_url($doc['file_path']) ?>" target="_blank"
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
              <?php
                $uploadedTypes = array_column($documents, 'doc_type');
                $docTypes = [
                  'aadhaar_front'      => 'Aadhaar Card — Front',
                  'aadhaar_back'       => 'Aadhaar Card — Back',
                  'birth_certificate'  => 'Birth Certificate',
                  'school_certificate' => 'School Certificate',
                  'noc'                => 'NOC (No Objection Certificate)',
                  'medical_fitness'    => 'Medical Fitness Certificate',
                  'photo'              => 'Passport Photo',
                  'other'              => 'Other',
                ];
                foreach ($docTypes as $val => $label):
                  // Hide already uploaded types (except 'other' which can be uploaded multiple times)
                  if ($val !== 'other' && in_array($val, $uploadedTypes)) continue;
              ?>
                <option value="<?= $val ?>"><?= $label ?></option>
              <?php endforeach; ?>
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

  // Runs per Season bar chart
  new Chart(document.getElementById('runsChart'), {
    type: 'bar',
    data: {
      labels: ['2022','2023','2024','2025','2026'],
      datasets: [{
        label: 'Runs',
        data: [312, 487, 523, 398, 127],
        backgroundColor: ['#4338ca','#7c3aed','#0891b2','#16a34a','#ea580c'],
        borderRadius: 6,
      }]
    },
    options: {
      responsive: true, maintainAspectRatio: false,
      plugins: { legend: { display: false } },
      scales: {
        y: { beginAtZero: true, grid: { color: '#f0f0f0' }, ticks: { font: { size: 11 } } },
        x: { grid: { display: false }, ticks: { font: { size: 11 } } }
      }
    }
  });

  // Innings breakdown doughnut
  new Chart(document.getElementById('inningsChart'), {
    type: 'doughnut',
    data: {
      labels: ['100s','50s','30-49','<30'],
      datasets: [{
        data: [<?= $stats['hundreds']??0 ?>, <?= $stats['fifties']??0 ?>, 8, 11],
        backgroundColor: ['#f97316','#7c3aed','#0891b2','#e5e7eb'],
        borderWidth: 0,
        hoverOffset: 6,
      }]
    },
    options: {
      responsive: true, maintainAspectRatio: false, cutout: '68%',
      plugins: {
        legend: { position: 'bottom', labels: { font: { size: 11 }, padding: 10 } }
      }
    }
  });
</script>
