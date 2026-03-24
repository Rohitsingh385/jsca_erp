<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Player Registration — JSCA ERP</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css" rel="stylesheet">
  <style>
    body { background: #f0f2f5; font-family: 'Segoe UI', sans-serif; }
    .reg-card { max-width: 480px; margin: 60px auto; border-radius: 12px; box-shadow: 0 4px 20px rgba(0,0,0,0.08); border: none; }
    .reg-header { background: #1a3a5c; color: #fff; border-radius: 12px 12px 0 0; padding: 24px; text-align: center; }
    .step-badge { background: rgba(255,255,255,0.15); border-radius: 20px; padding: 4px 14px; font-size: 12px; display: inline-block; margin-bottom: 8px; }
  </style>
</head>
<body>

<div class="reg-card card mx-auto">
  <div class="reg-header">
    <div class="step-badge">Step 1 of 3</div>
    <h4 class="mb-1">🏏 JSCA Player Registration</h4>
    <p class="mb-0 opacity-75" style="font-size:13px;">Enter your email to get started</p>
  </div>
  <div class="card-body p-4">

    <?php if (session()->getFlashdata('error')): ?>
      <div class="alert alert-danger py-2"><?= session()->getFlashdata('error') ?></div>
    <?php endif; ?>
    <?php if ($errors = session()->getFlashdata('errors')): ?>
      <div class="alert alert-danger py-2"><ul class="mb-0 ps-3"><?php foreach ($errors as $e): ?><li><?= esc($e) ?></li><?php endforeach; ?></ul></div>
    <?php endif; ?>

    <p class="text-muted mb-4" style="font-size:13px;">
      We'll send a 6-digit OTP to verify your email before you can register.
    </p>

    <form method="post" action="<?= base_url('player-register/send-otp') ?>">
      <?= csrf_field() ?>
      <div class="mb-3">
        <label class="form-label fw-semibold">Email Address <span class="text-danger">*</span></label>
        <input type="email" name="email" class="form-control" value="<?= old('email') ?>"
          placeholder="your@email.com" required autofocus>
      </div>
      <button type="submit" class="btn w-100" style="background:#1a3a5c;color:#fff;">
        Send OTP <i class="bi bi-arrow-right ms-1"></i>
      </button>
    </form>

    <hr class="my-3">
    <p class="text-center text-muted mb-0" style="font-size:12px;">
      Already registered? <a href="<?= base_url('login') ?>">Login here</a>
    </p>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
