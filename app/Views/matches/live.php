<!-- app/Views/matches/live.php -->
<style>
  .live-dot {
    width: 8px; height: 8px;
    background: #e74c3c;
    border-radius: 50%;
    display: inline-block;
    animation: pulse 1.4s infinite;
    margin-right: 6px;
  }
  @keyframes pulse {
    0%, 100% { opacity: 1; transform: scale(1); }
    50%       { opacity: .5; transform: scale(1.3); }
  }
  .match-card {
    background: #fff;
    border-radius: 10px;
    box-shadow: 0 1px 6px rgba(0,0,0,.07);
    padding: 18px 20px;
    margin-bottom: 12px;
    border-left: 3px solid #e74c3c;
  }
  .match-card.local-card {
    border-left-color: #1a3a5c;
  }
  .match-card .teams {
    font-size: 15px;
    font-weight: 700;
    color: #1a3a5c;
  }
  .match-card .score-line {
    font-size: 13px;
    color: #444;
    margin-top: 4px;
  }
  .match-card .meta {
    font-size: 11px;
    color: #999;
    margin-top: 6px;
  }
  .match-card .meta span { margin-right: 14px; }
  .section-label {
    font-size: 11px;
    font-weight: 700;
    letter-spacing: .08em;
    text-transform: uppercase;
    color: #999;
    margin-bottom: 12px;
    margin-top: 4px;
  }
  .refresh-bar {
    font-size: 12px;
    color: #aaa;
  }
  .refresh-bar #countdown { font-weight: 600; color: #666; }
</style>

<!-- Top bar -->
<div class="d-flex justify-content-between align-items-center mb-3">
  <div class="d-flex align-items-center gap-2">
    <span class="live-dot"></span>
    <span style="font-weight:700;font-size:15px;color:#1a3a5c;">Live Matches</span>
    <span class="refresh-bar ms-2">· refreshes in <span id="countdown">60</span>s</span>
  </div>
  <button class="btn btn-sm btn-jsca-primary" data-bs-toggle="modal" data-bs-target="#addMatchModal">
    + Add Local Match
  </button>
</div>

<!-- ── International (API) ──────────────────────────────────── -->
<div class="section-label">International · via cricketdata.org</div>

<?php if (!empty($apiError)): ?>
  <div class="text-muted mb-4" style="font-size:13px;">
    <i class="bi bi-exclamation-circle me-1 text-warning"></i><?= esc($apiError) ?>
  </div>
<?php elseif (empty($apiMatches)): ?>
  <div class="text-muted mb-4" style="font-size:13px;">No live international matches at the moment.</div>
<?php else: ?>
  <?php foreach ($apiMatches as $m): ?>
    <div class="match-card">
      <div class="d-flex justify-content-between align-items-start">
        <div>
          <div class="teams"><?= esc(implode(' vs ', $m['teams'])) ?></div>
          <?php if (!empty($m['score'])): ?>
            <div class="score-line">
              <?php foreach ($m['score'] as $s): ?>
                <span class="me-3">
                  <?= esc($s['inning'] ?? '') ?>:
                  <strong><?= esc(($s['r'] ?? '0') . '/' . ($s['w'] ?? '0')) ?></strong>
                  <span class="text-muted">(<?= esc($s['o'] ?? '0') ?> ov)</span>
                </span>
              <?php endforeach; ?>
            </div>
          <?php endif; ?>
          <div class="meta">
            <?php if ($m['venue'] !== '—'): ?>
              <span><i class="bi bi-geo-alt"></i> <?= esc($m['venue']) ?></span>
            <?php endif; ?>
            <span><i class="bi bi-tag"></i> <?= esc(strtoupper($m['matchType'])) ?></span>
          </div>
        </div>
        <span class="badge bg-danger" style="font-size:10px;white-space:nowrap;">● LIVE</span>
      </div>
    </div>
  <?php endforeach; ?>
<?php endif; ?>

<!-- ── Local Matches ────────────────────────────────────────── -->
<div class="section-label mt-3">Local &amp; District Matches</div>

<?php if (empty($localMatches)): ?>
  <div class="text-muted" style="font-size:13px;">
    No local matches running right now.
    <a href="#" data-bs-toggle="modal" data-bs-target="#addMatchModal" class="ms-1">Add one →</a>
  </div>
<?php else: ?>
  <?php foreach ($localMatches as $m): ?>
    <?php
      $teamA = $m['team_a_name'] ?: $m['team_a_custom'];
      $teamB = $m['team_b_name'] ?: $m['team_b_custom'];
    ?>
    <div class="match-card local-card">
      <div class="d-flex justify-content-between align-items-start">
        <div class="flex-grow-1">
          <div class="teams"><?= esc($teamA) ?> <span style="font-weight:400;color:#aaa;">vs</span> <?= esc($teamB) ?></div>
          <?php if ($m['team_a_score'] || $m['team_b_score']): ?>
            <div class="score-line mt-1">
              <strong><?= esc($teamA) ?>:</strong> <?= esc($m['team_a_score'] ?: '—') ?>
              &nbsp;&nbsp;
              <strong><?= esc($teamB) ?>:</strong> <?= esc($m['team_b_score'] ?: '—') ?>
            </div>
          <?php endif; ?>
          <div class="meta">
            <?php if ($m['venue']): ?><span><i class="bi bi-geo-alt"></i> <?= esc($m['venue']) ?></span><?php endif; ?>
            <?php if ($m['tournament_name']): ?><span><i class="bi bi-trophy"></i> <?= esc($m['tournament_name']) ?></span><?php endif; ?>
            <span><i class="bi bi-tag"></i> <?= esc($m['match_type']) ?></span>
            <?php if ($m['notes']): ?><span class="text-warning"><i class="bi bi-info-circle"></i> <?= esc($m['notes']) ?></span><?php endif; ?>
          </div>
        </div>
        <div class="d-flex gap-1 ms-3">
          <button class="btn btn-sm btn-outline-secondary"
            data-bs-toggle="modal" data-bs-target="#editMatchModal"
            data-id="<?= $m['id'] ?>"
            data-team_a="<?= esc($teamA) ?>"
            data-team_b="<?= esc($teamB) ?>"
            data-team_a_score="<?= esc($m['team_a_score']) ?>"
            data-team_b_score="<?= esc($m['team_b_score']) ?>"
            data-status="<?= esc($m['status']) ?>"
            data-notes="<?= esc($m['notes']) ?>">
            Edit
          </button>
          <form method="post" action="<?= base_url('matches/live/delete/' . $m['id']) ?>" class="d-inline"
            onsubmit="return confirm('Remove this match from live?')">
            <?= csrf_field() ?>
            <button class="btn btn-sm btn-outline-danger">Remove</button>
          </form>
        </div>
      </div>
    </div>
  <?php endforeach; ?>
<?php endif; ?>


<!-- ── Add Match Modal ──────────────────────────────────────── -->
<div class="modal fade" id="addMatchModal" tabindex="-1">
  <div class="modal-dialog modal-dialog-scrollable">
    <form method="post" action="<?= base_url('matches/live/store') ?>">
      <?= csrf_field() ?>
      <div class="modal-content">
        <div class="modal-header border-0 pb-0">
          <h6 class="modal-title fw-bold">Add Local Live Match</h6>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body pt-2">

          <div class="row g-3 mb-1">
            <div class="col-6">
              <label class="form-label" style="font-size:12px;font-weight:600;">Team A</label>
              <select name="team_a_id" class="form-select form-select-sm mb-1">
                <option value="">Select team…</option>
                <?php foreach ($teams as $t): ?>
                  <option value="<?= $t['id'] ?>"><?= esc($t['name']) ?></option>
                <?php endforeach; ?>
              </select>
              <input type="text" name="team_a_custom" class="form-control form-control-sm" placeholder="or custom name">
            </div>
            <div class="col-6">
              <label class="form-label" style="font-size:12px;font-weight:600;">Team B</label>
              <select name="team_b_id" class="form-select form-select-sm mb-1">
                <option value="">Select team…</option>
                <?php foreach ($teams as $t): ?>
                  <option value="<?= $t['id'] ?>"><?= esc($t['name']) ?></option>
                <?php endforeach; ?>
              </select>
              <input type="text" name="team_b_custom" class="form-control form-control-sm" placeholder="or custom name">
            </div>
          </div>

          <hr class="my-3">

          <div class="row g-3 mb-3">
            <div class="col-6">
              <label class="form-label" style="font-size:12px;font-weight:600;">Team A Score</label>
              <input type="text" name="team_a_score" class="form-control form-control-sm" placeholder="145/6 (18 ov)">
            </div>
            <div class="col-6">
              <label class="form-label" style="font-size:12px;font-weight:600;">Team B Score</label>
              <input type="text" name="team_b_score" class="form-control form-control-sm" placeholder="yet to bat">
            </div>
          </div>

          <div class="row g-3 mb-3">
            <div class="col-5">
              <label class="form-label" style="font-size:12px;font-weight:600;">Format</label>
              <select name="match_type" class="form-select form-select-sm">
                <option>T20</option><option>ODI</option><option>Test</option><option>T10</option><option>Other</option>
              </select>
            </div>
            <div class="col-7">
              <label class="form-label" style="font-size:12px;font-weight:600;">Venue</label>
              <input type="text" name="venue" class="form-control form-control-sm" placeholder="Ground name">
            </div>
          </div>

          <div class="mb-3">
            <label class="form-label" style="font-size:12px;font-weight:600;">Tournament / Contest</label>
            <input type="text" name="tournament_name" class="form-control form-control-sm" placeholder="e.g. JSCA District League 2025">
          </div>

          <div>
            <label class="form-label" style="font-size:12px;font-weight:600;">Notes <span class="text-muted fw-normal">(optional)</span></label>
            <input type="text" name="notes" class="form-control form-control-sm" placeholder="Rain delay, 2nd innings, etc.">
          </div>

        </div>
        <div class="modal-footer border-0 pt-0">
          <button type="button" class="btn btn-sm btn-light" data-bs-dismiss="modal">Cancel</button>
          <button type="submit" class="btn btn-sm btn-jsca-primary">Add Match</button>
        </div>
      </div>
    </form>
  </div>
</div>


<!-- ── Edit Match Modal ─────────────────────────────────────── -->
<div class="modal fade" id="editMatchModal" tabindex="-1">
  <div class="modal-dialog">
    <form method="post" id="editMatchForm">
      <?= csrf_field() ?>
      <div class="modal-content">
        <div class="modal-header border-0 pb-0">
          <div>
            <h6 class="modal-title fw-bold mb-0">Update Match</h6>
            <small class="text-muted" id="editMatchTeams"></small>
          </div>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body pt-2">
          <div class="row g-3 mb-3">
            <div class="col-6">
              <label class="form-label" style="font-size:12px;font-weight:600;" id="editLabelA">Team A Score</label>
              <input type="text" name="team_a_score" id="edit_team_a_score" class="form-control form-control-sm" placeholder="145/6 (18 ov)">
            </div>
            <div class="col-6">
              <label class="form-label" style="font-size:12px;font-weight:600;" id="editLabelB">Team B Score</label>
              <input type="text" name="team_b_score" id="edit_team_b_score" class="form-control form-control-sm" placeholder="yet to bat">
            </div>
          </div>
          <div class="row g-3">
            <div class="col-6">
              <label class="form-label" style="font-size:12px;font-weight:600;">Status</label>
              <select name="status" id="edit_status" class="form-select form-select-sm">
                <option value="live">Live</option>
                <option value="completed">Completed</option>
                <option value="abandoned">Abandoned</option>
              </select>
            </div>
            <div class="col-6">
              <label class="form-label" style="font-size:12px;font-weight:600;">Notes</label>
              <input type="text" name="notes" id="edit_notes" class="form-control form-control-sm">
            </div>
          </div>
        </div>
        <div class="modal-footer border-0 pt-0">
          <button type="button" class="btn btn-sm btn-light" data-bs-dismiss="modal">Cancel</button>
          <button type="submit" class="btn btn-sm btn-jsca-primary">Save</button>
        </div>
      </div>
    </form>
  </div>
</div>


<script>
  // Edit modal — populate fields + show team names in subtitle
  document.getElementById('editMatchModal').addEventListener('show.bs.modal', function(e) {
    const btn = e.relatedTarget;
    document.getElementById('editMatchForm').action = '<?= base_url('matches/live/update/') ?>' + btn.dataset.id;
    document.getElementById('editMatchTeams').textContent  = btn.dataset.team_a + ' vs ' + btn.dataset.team_b;
    document.getElementById('editLabelA').textContent      = btn.dataset.team_a;
    document.getElementById('editLabelB').textContent      = btn.dataset.team_b;
    document.getElementById('edit_team_a_score').value     = btn.dataset.team_a_score;
    document.getElementById('edit_team_b_score').value     = btn.dataset.team_b_score;
    document.getElementById('edit_status').value           = btn.dataset.status;
    document.getElementById('edit_notes').value            = btn.dataset.notes;
  });

  // Countdown + auto-refresh
  let secs = 60;
  const cd = document.getElementById('countdown');
  setInterval(() => {
    secs--;
    if (cd) cd.textContent = secs;
    if (secs <= 0) location.reload();
  }, 1000);
</script>
