<!-- app/Views/dashboard/index.php -->

<?php if (!empty($overdueFixtures)): ?>
<div class="alert alert-warning alert-dismissible fade show d-flex align-items-start gap-3 mb-4" role="alert">
  <i class="bi bi-exclamation-triangle-fill fs-5 mt-1"></i>
  <div class="flex-grow-1">
    <strong><?= count($overdueFixtures) ?> fixture<?= count($overdueFixtures) > 1 ? 's are' : ' is' ?> overdue</strong> — past scheduled date but still marked as Scheduled.
    <div class="mt-2 d-flex flex-wrap gap-2">
      <?php foreach ($overdueFixtures as $of): ?>
        <a href="<?= base_url('fixtures/view/' . $of['id']) ?>" class="badge bg-warning text-dark text-decoration-none">
          <?= esc($of['match_number']) ?> · <?= esc($of['team_a_name']) ?> vs <?= esc($of['team_b_name']) ?>
          · <?= date('d M', strtotime($of['match_date'])) ?>
        </a>
      <?php endforeach; ?>
    </div>
    <div class="mt-1 small">Please update each fixture status to <strong>Completed</strong>, <strong>Abandoned</strong> or <strong>Postponed</strong>.</div>
  </div>
  <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
<?php endif; ?>

<!-- Stat Cards Row -->
<div class="row g-3 mb-4">
  <div class="col-sm-6 col-xl-3">
    <div class="stat-card" style="border-left-color:#1a3a5c;">
      <div class="stat-val"><?= number_format($totalPlayers) ?></div>
      <div class="stat-label">Active Players</div>
      <div class="mt-2 text-success" style="font-size:12px;">
        <i class="bi bi-check-circle me-1"></i><?= number_format($verifiedPlayers) ?> Aadhaar Verified
      </div>
    </div>
  </div>
  <div class="col-sm-6 col-xl-3">
    <div class="stat-card" style="border-left-color:#2ecc71;">
      <div class="stat-val"><?= $activeTournaments ?></div>
      <div class="stat-label">Active Tournaments</div>
      <div class="mt-2 text-muted" style="font-size:12px;">
        <?= $totalTournaments ?> total this year
      </div>
    </div>
  </div>
  <div class="col-sm-6 col-xl-3">
    <div class="stat-card" style="border-left-color:#3498db;">
      <div class="stat-val"><?= $completedMatches ?></div>
      <div class="stat-label">Matches Completed</div>
      <div class="mt-2 text-muted" style="font-size:12px;">
        <?= $totalMatches ?> total scheduled
      </div>
    </div>
  </div>
  <div class="col-sm-6 col-xl-3">
    <div class="stat-card" style="border-left-color:<?= $pendingVouchers > 0 ? '#e74c3c' : '#2ecc71' ?>;">
      <div class="stat-val">₹<?= number_format($totalDisbursed / 100000, 1) ?>L</div>
      <div class="stat-label">Total Disbursed</div>
      <div class="mt-2 <?= $pendingVouchers > 0 ? 'text-danger' : 'text-success' ?>" style="font-size:12px;">
        <?= $pendingVouchers ?> vouchers pending approval
      </div>
    </div>
  </div>
</div>

<div class="row g-3">

  <!-- Live Matches -->
  <?php if (!empty($liveMatches)): ?>
    <div class="col-12">
      <div class="card">
        <div class="card-header d-flex align-items-center gap-2">
          <span class="badge bg-danger">● LIVE</span> Live Matches
        </div>
        <div class="card-body p-0">
          <div class="table-responsive">
            <table class="table mb-0">
              <thead>
                <tr>
                  <th>Match</th>
                  <th>Teams</th>
                  <th>Venue</th>
                  <th>Tournament</th>
                  <th></th>
                </tr>
              </thead>
              <tbody>
                <?php foreach ($liveMatches as $m): ?>
                  <tr>
                    <td><span class="badge" style="background:#1a3a5c;"><?= esc($m['match_number']) ?></span></td>
                    <td class="fw-semibold"><?= esc($m['team_a_name']) ?> <span class="text-muted">vs</span> <?= esc($m['team_b_name']) ?></td>
                    <td><i class="bi bi-geo-alt text-muted me-1"></i><?= esc($m['venue_name']) ?></td>
                    <td><?= esc($m['tournament_name']) ?></td>
                    <a href="<?= base_url('matches/score/' . $m['id']) ?>" class="btn btn-sm btn-jsca-green">Score →</a>
                  </tr>
                <?php endforeach; ?>
              </tbody>
            </table>
          </div>
        </div>
      </div>
    </div>
  <?php endif; ?>

  <!-- Upcoming Matches -->
  <div class="col-xl-8">
    <div class="card">
      <div class="card-header d-flex justify-content-between">
        <span><i class="bi bi-calendar3 me-2 text-primary"></i>Upcoming Matches (Next 7 Days)</span>
        <a href="<?= base_url('fixtures') ?>" class="btn btn-sm btn-outline-secondary">View All</a>
      </div>
      <div class="card-body p-0">
        <?php if (empty($upcomingMatches)): ?>
          <div class="text-center py-4 text-muted"><i class="bi bi-calendar-x fs-2 d-block mb-2"></i>No matches scheduled in the next 7 days</div>
        <?php else: ?>
          <div class="table-responsive">
            <table class="table mb-0">
              <thead>
                <tr>
                  <th>Date</th>
                  <th>Time</th>
                  <th>Teams</th>
                  <th>Venue</th>
                  <th>Status</th>
                </tr>
              </thead>
              <tbody>
                <?php foreach ($upcomingMatches as $m): ?>
                  <tr>
                    <td><strong><?= date('d M', strtotime($m['match_date'])) ?></strong><br>
                      <small class="text-muted"><?= date('l', strtotime($m['match_date'])) ?></small>
                    </td>
                    <td><?= date('g:i A', strtotime($m['match_time'])) ?>
                      <?php if ($m['has_floodlights'] && date('H', strtotime($m['match_time'])) >= 17): ?>
                        <span class="badge" style="background:#f39c12;font-size:9px;">🌙 D/N</span>
                      <?php endif; ?>
                    </td>
                    <td><?= esc($m['team_a_name']) ?> <small class="text-muted">vs</small> <?= esc($m['team_b_name']) ?></td>
                    <td><small><?= esc($m['venue_name']) ?></small></td>
                    <td><span class="badge badge-status-scheduled rounded-pill"><?= esc($m['status']) ?></span></td>
                  </tr>
                <?php endforeach; ?>
              </tbody>
            </table>
          </div>
        <?php endif; ?>
      </div>
    </div>
  </div>

  <!-- Right Column -->
  <div class="col-xl-4">

    <!-- Top Scorers -->
    <div class="card mb-3">
      <div class="card-header"><i class="bi bi-trophy me-2 text-warning"></i>Top Run Scorers</div>
      <div class="card-body p-0">
        <?php foreach ($topScorers as $i => $player): ?>
          <div class="d-flex align-items-center gap-3 px-3 py-2 border-bottom">
            <span class="fw-bold" style="color:#1a3a5c;width:20px;"><?= $i + 1 ?></span>
            <div class="flex-grow-1">
              <div class="fw-semibold" style="font-size:13px;"><?= esc($player['full_name']) ?></div>
              <div style="font-size:11px;color:#888;"><?= esc($player['district']) ?></div>
            </div>
            <div class="text-end">
              <div class="fw-bold" style="color:#2ecc71;font-size:15px;"><?= number_format($player['total_runs']) ?></div>
              <div style="font-size:10px;color:#888;"><?= $player['innings'] ?> inns</div>
            </div>
          </div>
        <?php endforeach; ?>
        <a href="<?= base_url('analytics/players') ?>" class="btn btn-sm btn-link text-muted" style="font-size:12px;">View full leaderboard →</a>
      </div>
    </div>

    <!-- Pending Vouchers -->
    <?php if (!empty($pendingVouchersList)): ?>
      <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
          <span><i class="bi bi-receipt me-2 text-danger"></i>Pending Approvals</span>
          <span class="badge bg-danger"><?= $pendingVouchers ?></span>
        </div>
        <div class="card-body p-0">
          <?php foreach ($pendingVouchersList as $v): ?>
            <div class="d-flex align-items-center gap-2 px-3 py-2 border-bottom">
              <div class="flex-grow-1">
                <div class="fw-semibold" style="font-size:13px;"><?= esc($v['voucher_number']) ?></div>
                <div style="font-size:11px;color:#888;"><?= esc($v['payee_name']) ?> · <?= esc($v['payee_type']) ?></div>
              </div>
              <div class="text-end">
                <div class="fw-bold" style="font-size:14px;">₹<?= number_format($v['total_amount']) ?></div>
                <a href="<?= base_url('finance/voucher/view/' . $v['id']) ?>" class="btn btn-xs btn-outline-success" style="font-size:10px;padding:1px 8px;">Review</a>
              </div>
            </div>
          <?php endforeach; ?>
        </div>
      </div>
    <?php endif; ?>

  </div>

  <!-- Age Category Breakdown -->
  <div class="col-xl-4">
    <div class="card">
      <div class="card-header"><i class="bi bi-bar-chart me-2 text-primary"></i>Players by Age Category</div>
      <div class="card-body">
        <canvas id="catChart" height="200"></canvas>
      </div>
    </div>
  </div>

  <!-- Recent Activity -->
  <div class="col-xl-8">
    <div class="card">
      <div class="card-header"><i class="bi bi-clock-history me-2"></i>Recent Activity</div>
      <div class="card-body p-0">
        <div class="table-responsive">
          <table class="table mb-0">
            <thead>
              <tr>
                <th>User</th>
                <th>Action</th>
                <th>Module</th>
                <th>Time</th>
              </tr>
            </thead>
            <tbody>
              <?php foreach ($recentActivity as $log): ?>
                <tr>
                  <td><span class="fw-semibold" style="font-size:13px;"><?= esc($log['full_name'] ?? 'System') ?></span></td>
                  <td><span class="badge bg-light text-dark"><?= esc($log['action']) ?></span></td>
                  <td><small class="text-muted"><?= esc(ucfirst($log['module'])) ?></small></td>
                  <td><small class="text-muted"><?= date('d M, H:i', strtotime($log['created_at'])) ?></small></td>
                </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </div>

</div><!-- end row -->

<script>
  // Category Chart
  const cats = <?= json_encode(array_column($categoryBreakdown, 'age_category')) ?>;
  const counts = <?= json_encode(array_column($categoryBreakdown, 'count')) ?>;
  new Chart(document.getElementById('catChart'), {
    type: 'doughnut',
    data: {
      labels: cats,
      datasets: [{
        data: counts,
        backgroundColor: ['#1a3a5c', '#2ecc71', '#3498db', '#e74c3c', '#f39c12'],
        borderWidth: 2
      }],
    },
    options: {
      responsive: true,
      plugins: {
        legend: {
          position: 'bottom',
          labels: {
            font: {
              size: 11
            }
          }
        }
      }
    },
  });
</script>