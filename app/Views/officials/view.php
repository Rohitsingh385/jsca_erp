<?php $canManage = in_array($currentUser['role_name'] ?? '', ['superadmin', 'admin']); ?>

<div class="d-flex justify-content-between align-items-center mb-4">
  <div>
    <h4 class="mb-0 fw-bold"><?= esc($official['full_name']) ?></h4>
    <code class="text-muted"><?= esc($official['jsca_official_id']) ?></code>
  </div>
  <div class="d-flex gap-2">
    <?php if ($canManage): ?>
      <a href="<?= base_url('officials/edit/' . $official['id']) ?>" class="btn btn-outline-primary btn-sm">
        <i class="bi bi-pencil me-1"></i> Edit
      </a>
      <form method="post" action="<?= base_url('officials/toggle/' . $official['id']) ?>" class="d-inline">
        <?= csrf_field() ?>
        <button class="btn btn-sm <?= $official['status'] === 'Active' ? 'btn-outline-warning' : 'btn-outline-success' ?>">
          <?= $official['status'] === 'Active' ? 'Deactivate' : 'Activate' ?>
        </button>
      </form>
    <?php endif; ?>
    <a href="<?= base_url('officials') ?>" class="btn btn-outline-secondary btn-sm">
      <i class="bi bi-arrow-left me-1"></i> Back
    </a>
  </div>
</div>

<div class="row g-4">
  <!-- Profile card -->
  <div class="col-lg-4">
    <div class="card text-center">
      <div class="card-body py-4">
        <?php if (!empty($official['profile_photo'])): ?>
          <img src="<?= base_url($official['profile_photo']) ?>" class="rounded-circle mb-3" width="100" height="100" style="object-fit:cover;">
        <?php else: ?>
          <div class="avatar-circle mx-auto mb-3" style="width:80px;height:80px;font-size:28px;">
            <?= strtoupper(substr($official['full_name'], 0, 1)) ?>
          </div>
        <?php endif; ?>
        <h5 class="mb-1"><?= esc($official['full_name']) ?></h5>
        <span class="badge bg-primary bg-opacity-10 text-primary mb-2"><?= esc($official['type_name']) ?></span><br>
        <span class="badge <?= $official['status'] === 'Active' ? 'bg-success' : 'bg-secondary' ?>"><?= $official['status'] ?></span>
      </div>
    </div>
  </div>

  <!-- Details -->
  <div class="col-lg-8">
    <div class="card mb-4">
      <div class="card-header">Official Details</div>
      <div class="card-body">
        <div class="row g-3">
          <div class="col-md-6">
            <div class="text-muted small">JSCA Official ID</div>
            <div class="fw-semibold"><code><?= esc($official['jsca_official_id']) ?></code></div>
          </div>
          <div class="col-md-6">
            <div class="text-muted small">Type</div>
            <div class="fw-semibold"><?= esc($official['type_name']) ?></div>
          </div>
          <div class="col-md-6">
            <div class="text-muted small">Gender</div>
            <div><?= esc($official['gender']) ?></div>
          </div>
          <div class="col-md-6">
            <div class="text-muted small">Date of Birth</div>
            <div><?= $official['dob'] ? date('d M Y', strtotime($official['dob'])) : '—' ?></div>
          </div>
          <div class="col-md-6">
            <div class="text-muted small">District</div>
            <div><?= esc($official['district_name']) ?> <span class="text-muted">(<?= esc($official['zone']) ?>)</span></div>
          </div>
          <div class="col-md-6">
            <div class="text-muted small">Experience</div>
            <div><?= $official['experience_years'] ? $official['experience_years'] . ' year(s)' : '—' ?></div>
          </div>
          <div class="col-md-6">
            <div class="text-muted small">Email</div>
            <div><?= $official['email'] ? esc($official['email']) : '—' ?></div>
          </div>
          <div class="col-md-6">
            <div class="text-muted small">Phone</div>
            <div><?= $official['phone'] ? esc($official['phone']) : '—' ?></div>
          </div>
          <?php if (!empty($official['address'])): ?>
            <div class="col-12">
              <div class="text-muted small">Address</div>
              <div><?= esc($official['address']) ?></div>
            </div>
          <?php endif; ?>
          <div class="col-md-6">
            <div class="text-muted small">Registered By</div>
            <div><?= esc($official['registered_by_name'] ?? '—') ?></div>
          </div>
          <div class="col-md-6">
            <div class="text-muted small">Registered On</div>
            <div><?= date('d M Y', strtotime($official['created_at'])) ?></div>
          </div>
        </div>
      </div>
    </div>

    <!-- Certifications -->
    <div class="card">
      <div class="card-header">Certifications</div>
      <div class="card-body">
        <?php if (empty($certs)): ?>
          <p class="text-muted mb-0">No certifications on record.</p>
        <?php else: ?>
          <div class="table-responsive">
            <table class="table table-sm mb-0">
              <thead>
                <tr>
                  <th>Certification</th>
                  <th>Body</th>
                  <th>Level</th>
                  <th>Date</th>
                </tr>
              </thead>
              <tbody>
                <?php foreach ($certs as $c): ?>
                  <tr>
                    <td><?= esc($c['certification_name']) ?></td>
                    <td><?= esc($c['body'] ?? '—') ?></td>
                    <td><?= esc($c['level'] ?? '—') ?></td>
                    <td><?= $c['certified_date'] ? date('d M Y', strtotime($c['certified_date'])) : '—' ?></td>
                  </tr>
                <?php endforeach; ?>
              </tbody>
            </table>
          </div>
        <?php endif; ?>
      </div>
    </div>
  </div>
</div>
