<!-- app/Views/matches/_api_matches.php -->
<div class="table-responsive">
  <table class="table mb-0">
    <thead>
      <tr>
        <th>Match</th>
        <th>Teams</th>
        <th>Score</th>
        <th>Venue</th>
        <th>Type</th>
        <th>Status</th>
      </tr>
    </thead>
    <tbody>
      <?php foreach ($apiMatches as $m): ?>
        <tr>
          <td><small class="text-muted"><?= esc($m['name']) ?></small></td>
          <td class="fw-semibold"><?= esc(implode(' vs ', $m['teams'])) ?></td>
          <td>
            <?php if (!empty($m['score'])): ?>
              <?php foreach ($m['score'] as $s): ?>
                <div style="font-size:12px;">
                  <strong><?= esc($s['inning'] ?? '') ?></strong>:
                  <?= esc(($s['r'] ?? '—') . '/' . ($s['w'] ?? '—') . ' (' . ($s['o'] ?? '—') . ' ov)') ?>
                </div>
              <?php endforeach; ?>
            <?php else: ?>
              <span class="text-muted">—</span>
            <?php endif; ?>
          </td>
          <td><small><?= esc($m['venue']) ?></small></td>
          <td><span class="badge bg-secondary"><?= esc(strtoupper($m['matchType'])) ?></span></td>
          <td><span class="badge bg-danger">● <?= esc($m['status']) ?></span></td>
        </tr>
      <?php endforeach; ?>
    </tbody>
  </table>
</div>
