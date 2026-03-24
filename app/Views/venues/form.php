<?php // app/Views/venues/form.php
$isEdit = !empty($venue);
?>

<div class="mb-3">
  <a href="<?= base_url('venues') ?>" class="text-muted" style="font-size:13px;">
    <i class="bi bi-arrow-left me-1"></i>Back to Venues
  </a>
  <h4 class="mt-1"><?= $isEdit ? 'Edit Venue' : 'Add Venue' ?></h4>
</div>

<div class="row">
  <div class="col-lg-8">
    <form method="post"
      action="<?= base_url($isEdit ? 'venues/update/' . $venue['id'] : 'venues/store') ?>"
      id="venueForm" novalidate>
      <?= csrf_field() ?>

      <!-- Basic Info -->
      <div class="card mb-3">
        <div class="card-header">Venue Information</div>
        <div class="card-body">
          <div class="row g-3">
            <div class="col-12">
              <label class="form-label fw-semibold" style="font-size:12px;">Venue Name <span class="text-danger">*</span></label>
              <input type="text" name="name" class="form-control form-control-sm"
                value="<?= esc(old('name', $venue['name'] ?? '')) ?>" required>
            </div>
            <div class="col-md-6">
              <label class="form-label fw-semibold" style="font-size:12px;">District <span class="text-danger">*</span></label>
              <select name="district_id" class="form-select form-select-sm" required>
                <option value="">Select district…</option>
                <?php foreach ($districts as $d): ?>
                  <option value="<?= $d['id'] ?>"
                    <?= old('district_id', $venue['district_id'] ?? '') == $d['id'] ? 'selected' : '' ?>>
                    <?= esc($d['name']) ?>
                  </option>
                <?php endforeach; ?>
              </select>
            </div>
            <div class="col-md-3">
              <label class="form-label fw-semibold" style="font-size:12px;">Capacity</label>
              <input type="number" name="capacity" class="form-control form-control-sm" min="0"
                value="<?= esc(old('capacity', $venue['capacity'] ?? 0)) ?>">
            </div>
            <div class="col-md-3">
              <label class="form-label fw-semibold" style="font-size:12px;">Pitch Type</label>
              <select name="pitch_type" class="form-select form-select-sm">
                <?php foreach (['Grass','Turf','Concrete','Red-soil'] as $pt): ?>
                  <option value="<?= $pt ?>"
                    <?= old('pitch_type', $venue['pitch_type'] ?? 'Grass') === $pt ? 'selected' : '' ?>>
                    <?= $pt ?>
                  </option>
                <?php endforeach; ?>
              </select>
            </div>
            <div class="col-12">
              <label class="form-label fw-semibold" style="font-size:12px;">Address</label>
              <textarea name="address" class="form-control form-control-sm" rows="2"><?= esc(old('address', $venue['address'] ?? '')) ?></textarea>
            </div>
          </div>
        </div>
      </div>

      <!-- Facilities -->
      <div class="card mb-3">
        <div class="card-header">Facilities</div>
        <div class="card-body">
          <div class="row g-3">
            <div class="col-md-4">
              <div class="form-check form-switch">
                <input class="form-check-input" type="checkbox" name="has_floodlights" id="floodlights" value="1"
                  <?= old('has_floodlights', $venue['has_floodlights'] ?? 0) ? 'checked' : '' ?>>
                <label class="form-check-label" for="floodlights">Floodlights</label>
              </div>
            </div>
            <div class="col-md-4">
              <div class="form-check form-switch">
                <input class="form-check-input" type="checkbox" name="has_scoreboard" id="scoreboard" value="1"
                  <?= old('has_scoreboard', $venue['has_scoreboard'] ?? 0) ? 'checked' : '' ?>>
                <label class="form-check-label" for="scoreboard">Electronic Scoreboard</label>
              </div>
            </div>
            <div class="col-md-4">
              <div class="form-check form-switch">
                <input class="form-check-input" type="checkbox" name="has_dressing" id="dressing" value="1"
                  <?= old('has_dressing', $venue['has_dressing'] ?? 0) ? 'checked' : '' ?>>
                <label class="form-check-label" for="dressing">Dressing Rooms</label>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Contact -->
      <div class="card mb-3">
        <div class="card-header">Contact & Location</div>
        <div class="card-body">
          <div class="row g-3">
            <div class="col-md-6">
              <label class="form-label fw-semibold" style="font-size:12px;">Contact Person</label>
              <input type="text" name="contact_person" class="form-control form-control-sm"
                value="<?= esc(old('contact_person', $venue['contact_person'] ?? '')) ?>">
            </div>
            <div class="col-md-6">
              <label class="form-label fw-semibold" style="font-size:12px;">Contact Phone</label>
              <input type="tel" name="contact_phone" id="contact_phone" class="form-control form-control-sm"
                value="<?= esc(old('contact_phone', $venue['contact_phone'] ?? '')) ?>"
                maxlength="10" inputmode="numeric" pattern="[6-9][0-9]{9}">
            </div>
            <div class="col-md-6">
              <label class="form-label fw-semibold" style="font-size:12px;">Latitude</label>
              <input type="text" name="lat" class="form-control form-control-sm"
                value="<?= esc(old('lat', $venue['lat'] ?? '')) ?>" placeholder="e.g. 23.3441">
            </div>
            <div class="col-md-6">
              <label class="form-label fw-semibold" style="font-size:12px;">Longitude</label>
              <input type="text" name="lng" class="form-control form-control-sm"
                value="<?= esc(old('lng', $venue['lng'] ?? '')) ?>" placeholder="e.g. 85.3096">
            </div>
          </div>
        </div>
      </div>

      <?php if ($isEdit): ?>
        <div class="card mb-3">
          <div class="card-body">
            <div class="form-check form-switch">
              <input class="form-check-input" type="checkbox" name="is_active" id="is_active" value="1"
                <?= old('is_active', $venue['is_active'] ?? 1) ? 'checked' : '' ?>>
              <label class="form-check-label fw-semibold" for="is_active">Venue is Active</label>
            </div>
          </div>
        </div>
      <?php endif; ?>

      <div class="d-flex gap-2">
        <button type="submit" class="btn btn-jsca-primary">
          <i class="bi bi-check-lg me-1"></i><?= $isEdit ? 'Update Venue' : 'Add Venue' ?>
        </button>
        <a href="<?= base_url('venues') ?>" class="btn btn-outline-secondary">Cancel</a>
      </div>
    </form>
  </div>

  <div class="col-lg-4">
    <div class="card">
      <div class="card-header">Notes</div>
      <div class="card-body small text-muted">
        <ul class="ps-3 mb-0">
          <li>Capacity is the approximate spectator count.</li>
          <li>Lat/Lng used for map display and travel optimization.</li>
          <li>Inactive venues won't appear in fixture scheduling.</li>
        </ul>
      </div>
    </div>
  </div>
</div>

<script>
  document.getElementById('contact_phone').addEventListener('input', function() {
    this.value = this.value.replace(/\D/g, '').slice(0, 10);
  });
  document.getElementById('venueForm').addEventListener('submit', function(e) {
    if (!this.checkValidity()) { e.preventDefault(); e.stopPropagation(); }
    this.classList.add('was-validated');
  });
</script>
