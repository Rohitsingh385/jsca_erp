<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Verify OTP — JSCA ERP</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css" rel="stylesheet">
  <style>
    body { background: #f0f2f5; font-family: 'Segoe UI', sans-serif; }
    .reg-card { max-width: 480px; margin: 60px auto; border-radius: 12px; box-shadow: 0 4px 20px rgba(0,0,0,0.08); border: none; }
    .reg-header { background: #1a3a5c; color: #fff; border-radius: 12px 12px 0 0; padding: 24px; text-align: center; }
    .step-badge { background: rgba(255,255,255,0.15); border-radius: 20px; padding: 4px 14px; font-size: 12px; display: inline-block; margin-bottom: 8px; }
    .otp-input { font-size: 28px; font-weight: 700; letter-spacing: 12px; text-align: center; }
  </style>
</head>
<body>

<div class="reg-card card mx-auto">
  <div class="reg-header">
    <div class="step-badge">Step 2 of 3</div>
    <h4 class="mb-1">🏏 Verify Your Email</h4>
    <p class="mb-0 opacity-75" style="font-size:13px;">OTP sent to <?= esc($email) ?></p>
  </div>
  <div class="card-body p-4">

    <?php if (session()->getFlashdata('error')): ?>
      <div class="alert alert-danger py-2"><?= session()->getFlashdata('error') ?></div>
    <?php endif; ?>
    <?php if (session()->getFlashdata('success')): ?>
      <div class="alert alert-success py-2"><?= session()->getFlashdata('success') ?></div>
    <?php endif; ?>

    <p class="text-muted mb-4" style="font-size:13px;">
      Enter the 6-digit OTP sent to your email. It expires in <strong>10 minutes</strong>.
    </p>

    <form method="post" action="<?= base_url('player-register/verify-otp') ?>">
      <?= csrf_field() ?>
      <div class="mb-4">
        <input type="text" name="otp" class="form-control otp-input"
          maxlength="6" pattern="[0-9]{6}" inputmode="numeric"
          placeholder="000000" required autofocus>
      </div>
      <button type="submit" class="btn w-100" style="background:#1a3a5c;color:#fff;">
        Verify OTP <i class="bi bi-check-lg ms-1"></i>
      </button>
    </form>

    <hr class="my-3">
    <p class="text-center mb-0" style="font-size:12px;">
      Didn't receive it?
      <a href="<?= base_url('player-register') ?>">Resend OTP</a>
    </p>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
  // Only allow digits in OTP field
  document.querySelector('input[name="otp"]').addEventListener('input', function() {
    this.value = this.value.replace(/\D/g, '').slice(0, 6);
  });
</script>
</body>
</html>
