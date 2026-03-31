<?php // app/Views/official/dashboard.php ?>

<!-- Welcome -->
<div class="mb-4">
  <h4 class="fw-bold mb-0">Welcome, <?= esc($official['full_name']) ?></h4>
  <div class="text-muted small">
    <?= esc($official['type_name']) ?> &nbsp;·&nbsp;
    <?= esc($official['jsca_official_id']) ?> &nbsp;·&nbsp;
    <?= esc($official['district_name']) ?>
    <?php if ($official['grade']): ?>
      &nbsp;·&nbsp; <span class="badge bg-light text-dark border"><?= esc($official['grade']) ?></span>
    <?php endif; ?>
  </div>
</div>

<!-- Summary Cards -->
<div class="row g-3 mb-4">
  <div class="col-6 col-md-3">
    <div class="stat-card">
      <div style="font-size:28px;font-weight:800;color:#1a3a5c;"><?= $totalMatches ?></div>
      <div style="font-size:11px;color:#888;text-transform:uppercase;letter-spacing:.06em;">Total Matches</div>
    </div>
  </div>
  <div class="col-6 col-md-3">
    <div class="stat-card" style="border-left-color:#3498db;">
      <div style="font-size:28px;font-weight:800;color:#1a3a5c;">₹<?= number_format($totalEarned) ?></div>
      <div style="font-size:11px;color:#888;text-transform:uppercase;letter-spacing:.06em;">Total Earned</div>
    </div>
  </div>
  <div class="col-6 col-md-3">
    <div class="stat-card" style="border-left-color:#e74c3c;">
      <div style="font-size:28px;font-weight:800;color:#e74c3c;">₹<?= number_format($totalPending) ?></div>
      <div style="font-size:11px;color:#888;text-transform:uppercase;letter-spacing:.06em;">Pending Payment</div>
    </div>
  </div>
  <div class="col-6 col-md-3">
    <div class="stat-card" style="border-left-color:#2ecc71;">
      <div style="font-size:28px;font-weight:800;color:#2ecc71;">₹<?= number_format($totalPaid) ?></div>
      <div style="font-size:11px;color:#888;text-transform:uppercase;letter-spacing:.06em;">Paid</div>
    </div>
  </div>
</div>

<!-- Upcoming Matches -->
<?php if (!empty($upcoming)): ?>
<div class="card mb-4">
  <div class="card-header"><i class="bi bi-calendar-event text-primary me-2"></i>Upcoming Assignments</div>
  <div class="card-body p-0">
    <table class="table table-hover mb-0">
      <thead>
        <tr><th>Match</th><th>Teams</th><th>Tournament</th><th>Date & Time</th><th>Venue</th><th>Your Role</th><th>Fee</th></tr>
      </thead>
      <tbody>
        <?php foreach ($upcoming as $m): ?>
        <tr>
          <td class="fw-semibold"><?= esc($m['match_number']) ?></td>
          <td><?= esc($m['team_a']) ?> <span class="text-muted">vs</span> <?= esc($m['team_b']) ?></td>
          <td>
            <div><?= esc($m['tournament_name']) ?></div>
            <div class="small text-muted"><?= esc($m['format']) ?> · <?= esc($m['tournament_overs']) ?> ov · <?= esc($m['age_category']) ?></div>
          </td>
          <td>
            <div><?= date('d M Y', strtotime($m['match_date'])) ?></div>
            <div class="small text-muted"><?= date('h:i A', strtotime($m['match_time'])) ?></div>
          </td>
          <td class="small"><?= esc($m['venue_name'] ?? '—') ?></td>
          <td><span class="badge bg-secondary"><?= esc($m['official_role']) ?></span></td>
          <td><?= $m['PAmt'] ? '₹' . number_format($m['PAmt']) : '<span class="text-muted">—</span>' ?></td>
        </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</div>
<?php endif; ?>

<!-- By Tournament — with payment request -->
<?php if (empty($byTournament)): ?>
  <div class="card"><div class="card-body text-center text-muted py-4">No matches assigned yet.</div></div>
<?php else: ?>
  <?php foreach ($byTournament as $tid => $tData): ?>
  <div class="card mb-4">
    <div class="card-header d-flex justify-content-between align-items-center">
      <div>
        <span class="fw-bold"><?= esc($tData['tournament_name']) ?></span>
        <?php
          $tBadge = match($tData['tournament_status']) {
            'Ongoing'       => 'bg-success',
            'Completed'     => 'bg-secondary',
            'Fixture Ready' => 'bg-primary',
            default         => 'bg-light text-dark border'
          };
        ?>
        <span class="badge <?= $tBadge ?> ms-2"><?= esc($tData['tournament_status']) ?></span>
      </div>
      <div class="d-flex align-items-center gap-3">
        <div class="small text-muted">
          <?= count($tData['matches']) ?> match<?= count($tData['matches']) > 1 ? 'es' : '' ?> &nbsp;·&nbsp;
          ₹<?= number_format($tData['total_fee']) ?> total
          <?php if ($tData['paid'] > 0): ?>
            &nbsp;·&nbsp; <span class="text-success">₹<?= number_format($tData['paid']) ?> paid</span>
          <?php endif; ?>
        </div>
        <?php if ($tData['total_fee'] > 0): ?>
          <div class="small text-muted">
            <?= count($tData['matches']) ?> match<?= count($tData['matches']) > 1 ? 'es' : '' ?> &nbsp;·&nbsp;
            ₹<?= number_format($tData['total_fee']) ?> total
            <?php if ($tData['paid'] > 0): ?>
              &nbsp;·&nbsp; <span class="text-success">₹<?= number_format($tData['paid']) ?> paid</span>
            <?php endif; ?>
          </div>
        <?php endif; ?>
      </div>
    </div>
    <div class="card-body p-0">
      <table class="table table-hover mb-0">
        <thead>
          <tr><th>Match</th><th>Teams</th><th>Format</th><th>Date</th><th>Result</th><th>Your Role</th><th>Fee</th><th>Payment</th></tr>
        </thead>
        <tbody>
          <?php foreach ($tData['matches'] as $m): ?>
          <tr>
            <td class="fw-semibold"><?= esc($m['match_number']) ?></td>
            <td class="small">
              <?= esc($m['team_a']) ?> vs <?= esc($m['team_b']) ?>
              <?php if ($m['team_a_score'] !== null): ?>
                <div class="text-muted" style="font-size:11px;">
                  <?= $m['team_a_score'] ?>/<?= $m['team_a_wickets'] ?> vs <?= $m['team_b_score'] ?>/<?= $m['team_b_wickets'] ?>
                </div>
              <?php endif; ?>
            </td>
            <td class="small text-muted"><?= esc($m['format']) ?> · <?= esc($m['tournament_overs']) ?> ov</td>
            <td class="small"><?= date('d M Y', strtotime($m['match_date'])) ?></td>
            <td class="small">
              <?php if ($m['result_summary']): ?>
                <span class="text-success" style="font-size:11px;"><?= esc($m['result_summary']) ?></span>
              <?php else: ?>
                <span class="badge <?= $m['fixture_status'] === 'Live' ? 'bg-success' : 'bg-light text-dark border' ?>"
                  style="font-size:10px;"><?= esc($m['fixture_status']) ?></span>
              <?php endif; ?>
            </td>
            <td><span class="badge bg-secondary" style="font-size:10px;"><?= esc($m['official_role']) ?></span></td>
            <td><?= $m['PAmt'] ? '₹' . number_format($m['PAmt']) : '<span class="text-muted">—</span>' ?></td>
            <td>
              <?php if (!empty($m['invoice_id'])): ?>
                <a href="<?= base_url('official/invoice/' . $m['invoice_id']) ?>" target="_blank"
                  class="btn btn-sm btn-outline-primary">
                  <i class="bi bi-download me-1"></i> Invoice
                  <span class="badge <?= $m['invoice_status'] === 'Paid' ? 'bg-success' : 'bg-warning text-dark' ?> ms-1">
                    <?= esc($m['invoice_status']) ?>
                  </span>
                </a>
              <?php elseif ($m['fixture_status'] === 'Completed' && $m['PAmt']): ?>
                <span class="text-muted small">Invoice pending</span>
              <?php elseif ($m['fixture_status'] === 'Completed' && !$m['PAmt']): ?>
                <span class="text-muted small">No fee set</span>
              <?php elseif ($m['PAmt']): ?>
                <span class="badge bg-light text-dark border">Pending match completion</span>
              <?php else: ?>
                <span class="text-muted small">—</span>
              <?php endif; ?>
            </td>
          </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </div>
  <?php endforeach; ?>
<?php endif; ?>
