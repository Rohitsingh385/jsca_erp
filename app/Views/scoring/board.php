<?php // app/Views/scoring/board.php ?>

<style>
  .score-header {
    background: linear-gradient(135deg, #1a3a5c 0%, #0d2137 100%);
    border-radius: 14px; color: #fff; padding: 28px 32px; margin-bottom: 20px;
  }
  .score-header .team-score { font-size: 38px; font-weight: 900; letter-spacing: -1px; }
  .score-header .team-name  { font-size: 13px; color: rgba(255,255,255,.6); text-transform: uppercase; letter-spacing: .1em; }
  .score-header .crr        { font-size: 13px; color: rgba(255,255,255,.55); }
  .live-pill { background:#e74c3c; color:#fff; font-size:11px; font-weight:700;
               padding:3px 10px; border-radius:20px; letter-spacing:.06em; animation:blink 1.4s infinite; }
  @keyframes blink { 0%,100%{opacity:1} 50%{opacity:.5} }

  .ball { display:inline-flex; align-items:center; justify-content:center;
          width:30px; height:30px; border-radius:50%; font-size:12px; font-weight:700; margin:2px; }
  .ball-0  { background:#f0f0f0; color:#555; }
  .ball-1  { background:#e3f2fd; color:#1565c0; }
  .ball-2  { background:#e8f5e9; color:#2e7d32; }
  .ball-3  { background:#fff3e0; color:#e65100; }
  .ball-4  { background:#2ecc71; color:#fff; }
  .ball-6  { background:#1a3a5c; color:#fff; }
  .ball-W  { background:#e74c3c; color:#fff; }
  .ball-WD { background:#f8f9fa; color:#999; border:1px solid #ddd; font-size:9px; }
  .ball-NB { background:#fff3e0; color:#e65100; border:1px solid #ffc107; font-size:9px; }

  .batting-row.active-bat { background:#fffde7; }
  .commentary-item { padding:10px 0; border-bottom:1px solid #f5f5f5; font-size:13px; }
  .commentary-item:last-child { border-bottom:none; }
  .commentary-item .ball-no { font-weight:700; color:#1a3a5c; min-width:50px; display:inline-block; }
  .commentary-item .ev { font-size:10px; font-weight:700; padding:2px 7px; border-radius:4px; margin-right:6px; }
  .worm-bar  { height:6px; background:#e9ecef; border-radius:3px; overflow:hidden; }
  .worm-fill { height:100%; border-radius:3px; }
  .coming-soon-banner { background:linear-gradient(90deg,#1a3a5c,#2ecc71); color:#fff;
    border-radius:10px; padding:12px 20px; font-size:13px; margin-bottom:20px;
    display:flex; align-items:center; gap:10px; }
</style>

<!-- Banner -->
<div class="coming-soon-banner">
  <i class="bi bi-tools fs-5"></i>
  <div>
    <strong>Live Scoring Module — Under Development</strong>
    <div style="font-size:11px;opacity:.85;">Full ball-by-ball scoring with real-time updates is coming soon. Below is a preview of the scoreboard interface.</div>
  </div>
  <span class="ms-auto badge bg-warning text-dark">PREVIEW</span>
</div>

<!-- Match selector -->
<div class="d-flex gap-2 mb-3 align-items-center">
  <select class="form-select form-select-sm" style="max-width:340px;" disabled>
    <option>Ranji Trophy 2026 — Match #3 · Ranchi XI vs Dhanbad Warriors</option>
  </select>
  <span class="badge bg-secondary" style="font-size:11px;">Innings 1 of 2</span>
  <span class="live-pill ms-1">● LIVE</span>
</div>

<!-- Score Header -->
<div class="score-header">
  <div class="row align-items-center">
    <div class="col-5 text-center">
      <div class="team-name">Ranchi XI</div>
      <div class="team-score">187 / 4</div>
      <div class="crr">32.3 overs &nbsp;·&nbsp; CRR: 5.75</div>
    </div>
    <div class="col-2 text-center" style="font-size:22px;font-weight:300;color:rgba(255,255,255,.3);">vs</div>
    <div class="col-5 text-center">
      <div class="team-name">Dhanbad Warriors</div>
      <div class="team-score">Yet to Bat</div>
      <div class="crr">Target: —</div>
    </div>
  </div>
  <div class="text-center mt-3" style="font-size:12px;color:rgba(255,255,255,.5);">
    <i class="bi bi-geo-alt me-1"></i>JSCA International Stadium, Ranchi &nbsp;·&nbsp;
    <i class="bi bi-trophy me-1"></i>Ranji Trophy 2026 &nbsp;·&nbsp;
    <i class="bi bi-sun me-1"></i>Day Match &nbsp;·&nbsp;
    Toss: Ranchi XI won, chose to Bat
  </div>
</div>

<div class="row g-3">

  <!-- Left -->
  <div class="col-lg-8">

    <!-- Current Over -->
    <div class="card mb-3">
      <div class="card-header d-flex justify-content-between align-items-center">
        <span>Current Over — 32.3</span>
        <span style="font-size:12px;color:#999;">Last 5 overs: 6, 8, 11, 9, 7</span>
      </div>
      <div class="card-body">
        <div class="d-flex flex-wrap align-items-center mb-3">
          <span class="ball ball-1">1</span>
          <span class="ball ball-0">0</span>
          <span class="ball ball-4">4</span>
          <span class="ball ball-WD">WD</span>
          <span class="ball ball-2">2</span>
          <span class="ball ball-W">W</span>
          <span style="font-size:11px;color:#aaa;margin-left:8px;">· · ·</span>
        </div>
        <div class="row g-3" style="font-size:13px;">
          <div class="col-6">
            <div class="text-muted mb-1" style="font-size:11px;text-transform:uppercase;letter-spacing:.08em;">Partnership</div>
            <strong>34 runs</strong> off 41 balls
          </div>
          <div class="col-6">
            <div class="text-muted mb-1" style="font-size:11px;text-transform:uppercase;letter-spacing:.08em;">Last Wicket</div>
            A. Kumar c Sharma b Yadav — 45 (62)
          </div>
        </div>
      </div>
    </div>

    <!-- Batting -->
    <div class="card mb-3">
      <div class="card-header">Batting — Ranchi XI</div>
      <div class="card-body p-0">
        <div class="table-responsive">
          <table class="table mb-0" style="font-size:13px;">
            <thead class="table-light">
              <tr style="font-size:11px;">
                <th>Batter</th><th class="text-center">R</th><th class="text-center">B</th>
                <th class="text-center">4s</th><th class="text-center">6s</th>
                <th class="text-center">SR</th><th>Dismissal</th>
              </tr>
            </thead>
            <tbody>
              <?php
              $batters = [
                ['R. Sharma',    52, 68, 6, 1, 'c Patel b Yadav',     false],
                ['V. Singh',     38, 51, 4, 0, 'lbw b Mishra',        false],
                ['A. Kumar',     45, 62, 5, 0, 'c Sharma b Yadav',    false],
                ['S. Tiwari *',  31, 38, 3, 1, 'batting',             true],
                ['M. Pandey',    14, 19, 1, 0, 'batting',             true],
                ['K. Rao',        0,  0, 0, 0, 'yet to bat',          false],
                ['P. Das',        0,  0, 0, 0, 'yet to bat',          false],
                ['D. Oraon',      0,  0, 0, 0, 'yet to bat',          false],
                ['T. Mahto',      0,  0, 0, 0, 'yet to bat',          false],
                ['B. Soren',      0,  0, 0, 0, 'yet to bat',          false],
                ['N. Hembrom',    0,  0, 0, 0, 'yet to bat',          false],
              ];
              foreach ($batters as [$name,$r,$b,$fours,$sixes,$out,$active]):
                $sr = $b > 0 ? number_format(($r/$b)*100,1) : '—';
              ?>
              <tr class="batting-row <?= $active ? 'active-bat' : '' ?>">
                <td><?= esc($name) ?><?php if($active): ?> <span class="badge bg-success ms-1" style="font-size:9px;">IN</span><?php endif; ?></td>
                <td class="text-center fw-bold"><?= $b > 0 ? $r : ($out==='yet to bat' ? '' : '0') ?></td>
                <td class="text-center text-muted"><?= $b ?: '' ?></td>
                <td class="text-center"><?= $fours ?: '' ?></td>
                <td class="text-center"><?= $sixes ?: '' ?></td>
                <td class="text-center text-muted"><?= $b > 0 ? $sr : '' ?></td>
                <td style="font-size:12px;color:#888;"><?= esc($out) ?></td>
              </tr>
              <?php endforeach; ?>
              <tr class="table-light" style="font-size:12px;">
                <td colspan="2"><strong>Extras</strong></td>
                <td colspan="5" class="text-muted">7 (WD: 4, NB: 2, B: 1)</td>
              </tr>
              <tr class="table-light">
                <td><strong>Total</strong></td>
                <td class="fw-bold">187</td>
                <td colspan="5" class="text-muted" style="font-size:12px;">4 wkts · 32.3 overs</td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    </div>

    <!-- Bowling -->
    <div class="card mb-3">
      <div class="card-header">Bowling — Dhanbad Warriors</div>
      <div class="card-body p-0">
        <div class="table-responsive">
          <table class="table mb-0" style="font-size:13px;">
            <thead class="table-light">
              <tr style="font-size:11px;">
                <th>Bowler</th><th class="text-center">O</th><th class="text-center">M</th>
                <th class="text-center">R</th><th class="text-center">W</th>
                <th class="text-center">Econ</th><th class="text-center">WD</th><th class="text-center">NB</th>
              </tr>
            </thead>
            <tbody>
              <?php
              $bowlers = [
                ['R. Yadav',  8,   1, 34, 2, 1, 0, true],
                ['S. Mishra', 7,   0, 38, 1, 2, 1, false],
                ['A. Patel',  6,   1, 28, 0, 0, 0, false],
                ['K. Singh',  5,   0, 31, 1, 1, 1, false],
                ['D. Kumar',  4,   0, 29, 0, 0, 0, false],
                ['M. Sharma', 2.3, 0, 20, 0, 0, 0, false],
              ];
              foreach ($bowlers as [$name,$o,$m,$r,$w,$wd,$nb,$current]):
                $econ = $o > 0 ? number_format($r/$o,2) : '—';
              ?>
              <tr class="<?= $current ? 'table-warning' : '' ?>">
                <td><?= esc($name) ?><?php if($current): ?> <span class="badge bg-warning text-dark ms-1" style="font-size:9px;">BOWLING</span><?php endif; ?></td>
                <td class="text-center"><?= $o ?></td>
                <td class="text-center"><?= $m ?></td>
                <td class="text-center"><?= $r ?></td>
                <td class="text-center fw-bold"><?= $w ?></td>
                <td class="text-center text-muted"><?= $econ ?></td>
                <td class="text-center text-muted"><?= $wd ?></td>
                <td class="text-center text-muted"><?= $nb ?></td>
              </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>
      </div>
    </div>

  </div>

  <!-- Right -->
  <div class="col-lg-4">

    <!-- Match Info -->
    <div class="card mb-3">
      <div class="card-header">Match Info</div>
      <div class="card-body p-0">
        <?php foreach ([
          'Tournament' => 'Ranji Trophy 2026',
          'Match No.'  => '#3 · League Stage',
          'Date'       => '01 Apr 2026',
          'Venue'      => 'JSCA Intl. Stadium',
          'Format'     => '50-over (List A)',
          'Umpire 1'   => 'Md. Ajmal Hussain',
          'Umpire 2'   => 'R. K. Verma',
          'Scorer'     => 'P. Mahato',
          'Referee'    => 'S. N. Gupta',
        ] as $k => $v): ?>
          <div class="d-flex px-3 py-2 border-bottom" style="font-size:12px;">
            <span class="text-muted" style="width:90px;flex-shrink:0;"><?= $k ?></span>
            <span><?= esc($v) ?></span>
          </div>
        <?php endforeach; ?>
      </div>
    </div>

    <!-- Over-by-over bars -->
    <div class="card mb-3">
      <div class="card-header">Over-by-Over Runs</div>
      <div class="card-body">
        <?php
        $overs = [6,8,4,11,7,9,5,12,8,6,7,9,10,6,8,5,11,7,9,8,6,10,7,8,9,6,11,8,7,9,6,8];
        $max = max($overs);
        foreach ($overs as $i => $rv):
          $color = $rv >= 10 ? '#2ecc71' : ($rv >= 7 ? '#1a3a5c' : '#adb5bd');
        ?>
          <div class="d-flex align-items-center gap-2 mb-1" style="font-size:11px;">
            <span class="text-muted" style="width:22px;text-align:right;"><?= $i+1 ?></span>
            <div class="worm-bar flex-grow-1">
              <div class="worm-fill" style="width:<?= round(($rv/$max)*100) ?>%;background:<?= $color ?>;"></div>
            </div>
            <span style="width:18px;font-weight:600;"><?= $rv ?></span>
          </div>
        <?php endforeach; ?>
      </div>
    </div>

    <!-- Commentary -->
    <div class="card">
      <div class="card-header d-flex justify-content-between">
        <span>Commentary</span>
        <span style="font-size:11px;color:#aaa;">Latest first</span>
      </div>
      <div class="card-body p-3" style="max-height:380px;overflow-y:auto;">
        <?php foreach ([
          ['32.3','W',  'bg-danger',   'OUT! Yadav to Kumar — outside off, Kumar goes for the drive, thick outside edge, Sharma takes a sharp catch at gully. Big wicket!'],
          ['32.2','WD', 'bg-secondary','Wide down leg. Keeper dives but can\'t stop it. Extra run.'],
          ['32.1','2',  'bg-success',  'Tiwari flicks off his pads, good running between the wickets, two runs.'],
          ['31.6','4',  'bg-success',  'FOUR! Kumar drives through the covers — beautiful timing, races to the boundary.'],
          ['31.5','0',  'bg-secondary','Dot ball. Mishra bowls full, Kumar defends solidly back down the pitch.'],
          ['31.4','1',  'bg-primary',  'Pushed to mid-on, easy single taken.'],
          ['31.3','6',  'bg-dark',     'SIX! Tiwari steps down the track and launches it over long-on! Crowd goes wild!'],
          ['31.2','0',  'bg-secondary','Beaten outside off. Good delivery from Mishra, Tiwari plays and misses.'],
          ['31.1','1',  'bg-primary',  'Tiwari works it to square leg, single.'],
          ['30.6','4',  'bg-success',  'FOUR! Edged but through the gap between slip and gully. Lucky boundary for Kumar.'],
        ] as [$over,$runs,$color,$text]): ?>
          <div class="commentary-item">
            <span class="ball-no"><?= $over ?></span>
            <span class="ev <?= $color ?> text-white"><?= $runs ?></span>
            <span style="color:#444;"><?= esc($text) ?></span>
          </div>
        <?php endforeach; ?>
      </div>
    </div>

  </div>
</div>

<!-- Scoring Controls (disabled preview) -->
<div class="card mt-3" style="border:2px dashed #dee2e6;">
  <div class="card-body">
    <div class="d-flex align-items-center gap-3 mb-3">
      <span style="font-size:13px;font-weight:600;color:#1a3a5c;">Scoring Controls</span>
      <span class="badge bg-warning text-dark">Coming Soon</span>
    </div>
    <div class="d-flex flex-wrap gap-2">
      <?php foreach (['0','1','2','3','4','6','Wide','No Ball','Wicket','Bye','Leg Bye'] as $btn): ?>
        <button class="btn btn-sm btn-outline-secondary" disabled><?= $btn ?></button>
      <?php endforeach; ?>
    </div>
    <div class="row g-2 mt-3">
      <div class="col-md-3">
        <select class="form-select form-select-sm" disabled><option>Select Striker</option></select>
      </div>
      <div class="col-md-3">
        <select class="form-select form-select-sm" disabled><option>Select Non-Striker</option></select>
      </div>
      <div class="col-md-3">
        <select class="form-select form-select-sm" disabled><option>Current Bowler</option></select>
      </div>
      <div class="col-md-3">
        <button class="btn btn-sm btn-jsca-primary w-100" disabled>Save Ball</button>
      </div>
    </div>
  </div>
</div>
