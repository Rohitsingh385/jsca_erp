<?php // app/Views/fixtures/view.php
$statusBadge = match($fixture['status']) {
  'Scheduled' => 'bg-primary',
  'Live'      => 'bg-success',
  'Completed' => 'bg-secondary',
  'Abandoned' => 'bg-warning text-dark',
  'Postponed' => 'bg-danger',
  default     => 'bg-light text-dark'
};
?>

<!-- Header -->
<div class="d-flex justify-content-between align-items-start mb-4">
  <div>
    <a href="<?= base_url('fixtures?tournament_id=' . $fixture['tournament_id']) ?>" class="text-decoration-none text-muted small">
      <i class="bi bi-arrow-left me-1"></i> <?= esc($fixture['tournament_name']) ?>
    </a>
    <h4 class="mt-1 mb-0 fw-bold">
      <?= esc($fixture['team_a_name']) ?> <span class="text-muted">vs</span> <?= esc($fixture['team_b_name']) ?>
    </h4>
    <div class="text-muted small mt-1">
      <?= esc($fixture['match_number']) ?> · <?= esc($fixture['stage']) ?> ·
      <?= date('d M Y', strtotime($fixture['match_date'])) ?> at <?= date('h:i A', strtotime($fixture['match_time'])) ?>
    </div>
  </div>
  <div class="d-flex gap-2 align-items-center">
    <?php if ($canManage && $fixture['status'] !== 'Completed'): ?>
    <!-- Quick status update -->
    <form method="post" action="<?= base_url('fixtures/update-status/' . $fixture['id']) ?>" class="d-flex align-items-center gap-2">
      <?= csrf_field() ?>
      <select name="status" class="form-select form-select-sm" style="width:auto;"
        onchange="if(confirm('Change status to ' + this.value + '?')) this.form.submit(); else this.value='<?= esc($fixture['status']) ?>';">
        <?php foreach (['Scheduled','Live','Completed','Abandoned','Postponed'] as $s): ?>
          <option value="<?= $s ?>" <?= $fixture['status'] === $s ? 'selected' : '' ?>
            <?php
              $colors = ['Scheduled'=>'','Live'=>'','Completed'=>'','Abandoned'=>'','Postponed'=>''];
            ?>>
            <?= $s ?>
          </option>
        <?php endforeach; ?>
      </select>
    </form>
    <?php else: ?>
      <span class="badge <?= $statusBadge ?> fs-6"><?= esc($fixture['status']) ?></span>
    <?php endif; ?>
    <?php if ($canManage && $fixture['status'] !== 'Completed'): ?>
      <a href="<?= base_url('fixtures/edit/' . $fixture['id']) ?>" class="btn btn-sm btn-outline-secondary">
        <i class="bi bi-pencil me-1"></i> Edit
      </a>
    <?php endif; ?>
    <?php if ($canManage && $fixture['status'] !== 'Completed'): ?>
      <a href="<?= base_url('matches/score/' . $fixture['id']) ?>" class="btn btn-sm btn-jsca-green">
        <i class="bi bi-broadcast me-1"></i> <?= $fixture['status'] === 'Live' ? 'Continue Scoring' : 'Start Scoring' ?>
      </a>
    <?php endif; ?>
  </div>
</div>

<div class="row g-4">

  <!-- Scorecard -->
  <div class="col-lg-8">

    <!-- Score summary if played -->
    <?php if (in_array($fixture['status'], ['Live', 'Completed', 'Abandoned'])): ?>
    <div class="card mb-4">
      <div class="card-header d-flex justify-content-between">
        <span>Scorecard</span>
        <?php if ($fixture['status'] === 'Live'): ?>
          <span class="badge bg-success"><i class="bi bi-circle-fill me-1" style="font-size:8px;"></i> LIVE</span>
        <?php endif; ?>
      </div>
      <div class="card-body">
        <div class="row text-center">
          <div class="col-5">
            <div class="fw-bold fs-5"><?= esc($fixture['team_a_name']) ?></div>
            <div class="display-6 fw-bold text-primary">
              <?= $fixture['team_a_score'] !== null ? esc($fixture['team_a_score']) . '/' . $fixture['team_a_wickets'] : '—' ?>
            </div>
            <?php if ($fixture['team_a_overs']): ?>
              <div class="text-muted small">(<?= $fixture['team_a_overs'] ?> ov)</div>
            <?php endif; ?>
          </div>
          <div class="col-2 d-flex align-items-center justify-content-center">
            <span class="text-muted fw-bold">VS</span>
          </div>
          <div class="col-5">
            <div class="fw-bold fs-5"><?= esc($fixture['team_b_name']) ?></div>
            <div class="display-6 fw-bold text-primary">
              <?= $fixture['team_b_score'] !== null ? esc($fixture['team_b_score']) . '/' . $fixture['team_b_wickets'] : '—' ?>
            </div>
            <?php if ($fixture['team_b_overs']): ?>
              <div class="text-muted small">(<?= $fixture['team_b_overs'] ?> ov)</div>
            <?php endif; ?>
          </div>
        </div>
        <?php if ($fixture['result_summary']): ?>
          <div class="text-center mt-3 fw-semibold text-success">
            <i class="bi bi-trophy me-1"></i> <?= esc($fixture['result_summary']) ?>
          </div>
        <?php endif; ?>
        <?php if ($fixture['toss_winner_name']): ?>
          <div class="text-center text-muted small mt-2">
            Toss: <?= esc($fixture['toss_winner_name']) ?> won and chose to <?= strtolower(esc($fixture['toss_decision'])) ?> first
          </div>
        <?php endif; ?>
      </div>
    </div>
    <?php endif; ?>

    <!-- Batting Stats -->
    <?php if (!empty($battingStats)): ?>
    <?php
      $innings1Batting = array_filter($battingStats, fn($r) => $r['innings'] == 1);
      $innings2Batting = array_filter($battingStats, fn($r) => $r['innings'] == 2);
    ?>
    <?php foreach ([1 => $innings1Batting, 2 => $innings2Batting] as $inn => $rows):
      if (empty($rows)) continue;
      $teamName = $rows[array_key_first($rows)]['team_name'];
    ?>
    <div class="card mb-3">
      <div class="card-header small fw-semibold">
        Innings <?= $inn ?> — <?= esc($teamName) ?> Batting
      </div>
      <div class="card-body p-0">
        <table class="table table-sm mb-0">
          <thead>
            <tr>
              <th>Batsman</th>
              <th class="text-center">R</th>
              <th class="text-center">B</th>
              <th class="text-center">4s</th>
              <th class="text-center">6s</th>
              <th class="text-center">SR</th>
              <th>Dismissal</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($rows as $r): ?>
            <tr>
              <td><?= esc($r['full_name']) ?></td>
              <td class="text-center fw-semibold"><?= $r['runs'] ?></td>
              <td class="text-center"><?= $r['balls_faced'] ?></td>
              <td class="text-center"><?= $r['fours'] ?></td>
              <td class="text-center"><?= $r['sixes'] ?></td>
              <td class="text-center">
                <?= $r['balls_faced'] > 0 ? number_format($r['runs'] / $r['balls_faced'] * 100, 1) : '—' ?>
              </td>
              <td class="small text-muted"><?= esc(strtoupper($r['dismissal'])) ?></td>
            </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    </div>
    <?php endforeach; ?>
    <?php endif; ?>

    <!-- Bowling Stats -->
    <?php if (!empty($bowlingStats)): ?>
    <?php
      $innings1Bowling = array_filter($bowlingStats, fn($r) => $r['innings'] == 1);
      $innings2Bowling = array_filter($bowlingStats, fn($r) => $r['innings'] == 2);
    ?>
    <?php foreach ([1 => $innings1Bowling, 2 => $innings2Bowling] as $inn => $rows):
      if (empty($rows)) continue;
      $teamName = $rows[array_key_first($rows)]['team_name'];
    ?>
    <div class="card mb-3">
      <div class="card-header small fw-semibold">
        Innings <?= $inn ?> — <?= esc($teamName) ?> Bowling
      </div>
      <div class="card-body p-0">
        <table class="table table-sm mb-0">
          <thead>
            <tr>
              <th>Bowler</th>
              <th class="text-center">O</th>
              <th class="text-center">M</th>
              <th class="text-center">R</th>
              <th class="text-center">W</th>
              <th class="text-center">Econ</th>
              <th class="text-center">Wd</th>
              <th class="text-center">NB</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($rows as $r): ?>
            <tr>
              <td><?= esc($r['full_name']) ?></td>
              <td class="text-center"><?= $r['overs'] ?></td>
              <td class="text-center"><?= $r['maidens'] ?></td>
              <td class="text-center"><?= $r['runs_conceded'] ?></td>
              <td class="text-center fw-semibold"><?= $r['wickets'] ?></td>
              <td class="text-center">
                <?= $r['overs'] > 0 ? number_format($r['runs_conceded'] / $r['overs'], 2) : '—' ?>
              </td>
              <td class="text-center"><?= $r['wides'] ?></td>
              <td class="text-center"><?= $r['no_balls'] ?></td>
            </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    </div>
    <?php endforeach; ?>
    <?php endif; ?>

    <!-- Playing XIs -->
    <div class="row g-3">
      <?php foreach ([
        ['team' => $fixture['team_a_name'], 'players' => $teamAPlayers],
        ['team' => $fixture['team_b_name'], 'players' => $teamBPlayers],
      ] as $side): ?>
      <div class="col-md-6">
        <div class="card">
          <div class="card-header small fw-semibold"><?= esc($side['team']) ?> — Squad</div>
          <div class="card-body p-0">
            <table class="table table-sm mb-0">
              <tbody>
                <?php foreach ($side['players'] as $p): ?>
                <tr>
                  <td class="text-muted" style="width:30px;"><?= $p['jersey_number'] ?? '—' ?></td>
                  <td>
                    <?= esc($p['full_name']) ?>
                    <?php if ($p['is_captain']): ?><span class="badge bg-warning text-dark ms-1" style="font-size:9px;">C</span><?php endif; ?>
                    <?php if ($p['is_vice_captain']): ?><span class="badge bg-info ms-1" style="font-size:9px;">VC</span><?php endif; ?>
                    <?php if ($p['is_wk']): ?><span class="badge bg-secondary ms-1" style="font-size:9px;">WK</span><?php endif; ?>
                  </td>
                  <td class="text-muted small"><?= esc($p['role']) ?></td>
                </tr>
                <?php endforeach; ?>
              </tbody>
            </table>
          </div>
        </div>
      </div>
      <?php endforeach; ?>
    </div>

  </div>

  <!-- Right sidebar -->
  <div class="col-lg-4">

    <!-- Match Info -->
    <div class="card mb-3">
      <div class="card-header small fw-semibold">Match Info</div>
      <div class="card-body small">
        <table class="table table-sm table-borderless mb-0">
          <tr><td class="text-muted">Tournament</td><td><?= esc($fixture['tournament_name']) ?></td></tr>
          <tr><td class="text-muted">Format</td><td><?= esc($fixture['format'] ?? '—') ?> (<?= $fixture['tournament_overs'] ?> ov)</td></tr>
          <tr><td class="text-muted">Stage</td><td><?= esc($fixture['stage']) ?></td></tr>
          <tr><td class="text-muted">Venue</td><td><?= esc($fixture['venue_name'] ?? '—') ?></td></tr>
          <tr><td class="text-muted">Date</td><td><?= date('d M Y', strtotime($fixture['match_date'])) ?></td></tr>
          <tr><td class="text-muted">Time</td><td><?= date('h:i A', strtotime($fixture['match_time'])) ?> <?= $fixture['is_day_night'] ? '🌙' : '☀️' ?></td></tr>
        </table>
      </div>
    </div>

    <!-- Officials -->
    <div class="card mb-3">
      <div class="card-header small fw-semibold">Officials</div>
      <div class="card-body small">
        <table class="table table-sm table-borderless mb-0">
          <tr><td class="text-muted">Umpire 1</td><td><?= esc($fixture['umpire1_name'] ?? '—') ?></td></tr>
          <tr><td class="text-muted">Umpire 2</td><td><?= esc($fixture['umpire2_name'] ?? '—') ?></td></tr>
          <tr><td class="text-muted">Scorer</td><td><?= esc($fixture['scorer_name'] ?? '—') ?></td></tr>
          <tr><td class="text-muted">Referee</td><td><?= esc($fixture['referee_name'] ?? '—') ?></td></tr>
        </table>
      </div>
    </div>

    <?php if ($fixture['youtube_url']): ?>
    <div class="card mb-3">
      <div class="card-body">
        <a href="<?= esc($fixture['youtube_url']) ?>" target="_blank" class="btn btn-danger w-100 btn-sm">
          <i class="bi bi-youtube me-1"></i> Watch Live Stream
        </a>
      </div>
    </div>
    <?php endif; ?>

    <?php if ($canManage && $fixture['status'] !== 'Completed'): ?>
    <div class="card">
      <div class="card-body d-grid gap-2">
        <a href="<?= base_url('matches/score/' . $fixture['id']) ?>" class="btn btn-jsca-green btn-sm">
          <i class="bi bi-broadcast me-1"></i> <?= $fixture['status'] === 'Live' ? 'Continue Scoring' : 'Start Scoring' ?>
        </a>
        <a href="<?= base_url('fixtures/edit/' . $fixture['id']) ?>" class="btn btn-outline-secondary btn-sm">
          <i class="bi bi-pencil me-1"></i> Edit Fixture
        </a>
        <form method="post" action="<?= base_url('fixtures/delete/' . $fixture['id']) ?>"
          onsubmit="return confirm('Delete this fixture?')">
          <?= csrf_field() ?>
          <button type="submit" class="btn btn-outline-danger btn-sm w-100">
            <i class="bi bi-trash me-1"></i> Delete
          </button>
        </form>
      </div>
    </div>
    <?php endif; ?>

  </div>
</div>
