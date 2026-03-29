<?php // app/Views/official/profile.php ?>

<div class="row g-4">
  <div class="col-md-4">
    <div class="card text-center p-4">
      <?php if ($official['profile_photo']): ?>
        <img src="<?= base_url($official['profile_photo']) ?>" class="rounded-circle mb-3 mx-auto"
          style="width:100px;height:100px;object-fit:cover;">
      <?php else: ?>
        <div class="rounded-circle bg-primary text-white d-flex align-items-center justify-content-center mx-auto mb-3"
          style="width:100px;height:100px;font-size:36px;font-weight:800;">
          <?= strtoupper(substr($official['full_name'], 0, 1)) ?>
        </div>
      <?php endif; ?>
      <h5 class="fw-bold mb-1"><?= esc($official['full_name']) ?></h5>
      <div class="text-muted small"><?= esc($official['type_name']) ?></div>
      <div class="mt-2">
        <span class="badge bg-secondary"><?= esc($official['jsca_official_id']) ?></span>
        <?php if ($official['grade']): ?>
          <span class="badge bg-light text-dark border ms-1"><?= esc($official['grade']) ?></span>
        <?php endif; ?>
      </div>
    </div>
  </div>
  <div class="col-md-8">
    <div class="card">
      <div class="card-header">Profile Details</div>
      <div class="card-body">
        <table class="table table-sm table-borderless mb-0">
          <tr><td class="text-muted" style="width:140px;">Full Name</td><td><?= esc($official['full_name']) ?></td></tr>
          <tr><td class="text-muted">JSCA ID</td><td><code><?= esc($official['jsca_official_id']) ?></code></td></tr>
          <tr><td class="text-muted">Type</td><td><?= esc($official['type_name']) ?></td></tr>
          <tr><td class="text-muted">Grade</td><td><?= esc($official['grade'] ?? '—') ?></td></tr>
          <tr><td class="text-muted">District</td><td><?= esc($official['district_name']) ?></td></tr>
          <tr><td class="text-muted">Phone</td><td><?= esc($official['phone'] ?? '—') ?></td></tr>
          <tr><td class="text-muted">Email</td><td><?= esc($official['email'] ?? '—') ?></td></tr>
          <tr><td class="text-muted">Experience</td><td><?= $official['experience_years'] ? $official['experience_years'] . ' years' : '—' ?></td></tr>
          <tr><td class="text-muted">Fee/Match</td><td><?= $official['fee_per_match'] ? '₹' . number_format($official['fee_per_match']) : '—' ?></td></tr>
        </table>
      </div>
    </div>
  </div>
</div>
