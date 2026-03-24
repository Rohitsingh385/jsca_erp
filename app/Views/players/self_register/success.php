<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Registration Successful — JSCA ERP</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <style>
    body { background: #f0f2f5; font-family: 'Segoe UI', sans-serif; }
    .success-card { max-width: 480px; margin: 80px auto; border-radius: 12px; box-shadow: 0 4px 20px rgba(0,0,0,0.08); border: none; text-align: center; }
  </style>
</head>
<body>

<div class="success-card card p-5">
  <div style="font-size:56px;">✅</div>
  <h4 class="mt-3 mb-1">Registration Submitted!</h4>

  <?php
    $name = session()->getFlashdata('success_name');
    $id   = session()->getFlashdata('success_id');
  ?>

  <?php if ($name): ?>
    <p class="text-muted">Dear <strong><?= esc($name) ?></strong>, your registration has been submitted.</p>
  <?php endif; ?>

  <?php if ($id): ?>
    <div class="alert alert-light border my-3">
      Your JSCA Player ID: <strong class="text-primary"><?= esc($id) ?></strong>
    </div>
  <?php endif; ?>

  <ul class="list-unstyled text-start text-muted mb-4" style="font-size:13px;">
    <li class="mb-2">📧 Your login credentials have been sent to your email.</li>
    <li class="mb-2">⏳ Your account is <strong>pending verification</strong> by JSCA admin.</li>
    <li class="mb-2">✅ Once verified, you'll receive a confirmation email and can log in.</li>
  </ul>

  <a href="<?= base_url('login') ?>" class="btn" style="background:#1a3a5c;color:#fff;">
    Go to Login
  </a>
</div>

</body>
</html>
