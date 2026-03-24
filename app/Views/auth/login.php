<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?= esc($pageTitle) ?></title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <style>
    body {
      background: linear-gradient(135deg, #1a3a5c 0%, #0d2137 100%);
      min-height: 100vh;
      display: flex;
      align-items: center;
    }

    .login-card {
      border-radius: 16px;
      overflow: hidden;
      box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
      max-width: 420px;
      width: 100%;
    }

    .login-header {
      background: #1a3a5c;
      padding: 32px;
      text-align: center;
    }

    .login-body {
      background: #fff;
      padding: 36px;
    }

    .form-control {
      border-radius: 8px;
      padding: 12px 16px;
      border: 1.5px solid #e0e0e0;
    }

    .form-control:focus {
      border-color: #1a3a5c;
      box-shadow: 0 0 0 3px rgba(26, 58, 92, 0.12);
    }

    .btn-login {
      background: #1a3a5c;
      color: #fff;
      border-radius: 8px;
      padding: 12px;
      font-weight: 700;
      width: 100%;
      font-size: 15px;
      border: none;
    }

    .btn-login:hover {
      background: #16324e;
      color: #fff;
    }

    .form-label {
      font-weight: 600;
      font-size: 13px;
      color: #444;
    }
  </style>
</head>

<body>
  <div class="container">
    <div class="row justify-content-center">
      <div class="col-12">
        <div class="login-card mx-auto">
          <div class="login-header">
            <div class="text-white fs-2 fw-bold mb-1">🏏 JSCA</div>
            <div class="text-white fw-bold fs-5">ERP Solution</div>
            <div class="text-white-50 mt-1" style="font-size:12px;">Jharkhand State Cricket Association</div>
          </div>
          <div class="login-body">
            <h5 class="fw-bold mb-4" style="color:#1a3a5c;">Sign in to your account</h5>

            <?php if ($error = session()->getFlashdata('error')): ?>
              <div class="alert alert-danger py-2 px-3 mb-3" style="font-size:13px;"><?= esc($error) ?></div>
            <?php endif; ?>
            <?php if ($success = session()->getFlashdata('success')): ?>
              <div class="alert alert-success py-2 px-3 mb-3" style="font-size:13px;"><?= esc($success) ?></div>
            <?php endif; ?>

            <form action="<?= base_url('login') ?>" method="POST">
              <?= csrf_field() ?>
              <div class="mb-3">
                <label class="form-label">Email Address</label>
                <input type="email" name="email" class="form-control" placeholder="admin@jsca.in"
                  value="<?= old('email') ?>" required autofocus>
              </div>
              <div class="mb-4">
                <label class="form-label d-flex justify-content-between">
                  Password
                  <a href="<?= base_url('forgot-password') ?>" style="font-size:12px;color:#1a3a5c;">Forgot password?</a>
                </label>
                <input type="password" name="password" class="form-control" placeholder="••••••••" required>
              </div>
              <button type="submit" class="btn btn-login">Sign In →</button>
            </form>

            <div class="text-center mt-4" style="font-size:11px;color:#aaa;">
              JSCA Solutioning ERP Solution &nbsp;|&nbsp; v1.0.0
            </div>
            <hr class="my-3">
            <div class="text-center" style="font-size:13px;">
              New player? <a href="<?= base_url('player-register') ?>" style="color:#1a3a5c;font-weight:600;">Register here</a>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>