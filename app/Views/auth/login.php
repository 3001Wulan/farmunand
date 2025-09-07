<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Login - FarmUnand</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <style>
    body {
      background: url("<?= base_url('public/img/bg-login.jpg') ?>") no-repeat center center fixed;
      background-size: cover;
      position: relative;
      min-height: 100vh;
    }

    /* Overlay hijau transparan */
    body::before {
      content: "";
      position: absolute;
      top: 0; left: 0; right: 0; bottom: 0;
      background: rgba(0, 128, 0, 0.18); /* hijau transparan */
      z-index: 0;
    }

    .card {
      position: relative;
      z-index: 1;
      background-color: rgba(255, 255, 255, 0.95);
      border-radius: 15px;
      box-shadow: 0 8px 20px rgba(0, 128, 0, 0.3); /* shadow hijau */
    }
  </style>
</head>
<body>

<div class="container d-flex justify-content-center align-items-center vh-100">
  <div class="col-md-4">
    <div class="card shadow-lg">
      <div class="card-body p-4">
        <h3 class="text-center mb-4 text-success fw-bold">Login ke FarmUnand</h3>

        <?php if (session()->getFlashdata('error')): ?>
          <div class="alert alert-danger"><?= session()->getFlashdata('error') ?></div>
        <?php endif; ?>

        <?php if (session()->getFlashdata('success')): ?>
          <div class="alert alert-success"><?= session()->getFlashdata('success') ?></div>
        <?php endif; ?>

        <form action="/auth/doLogin" method="post">
          <div class="mb-3">
            <label for="email" class="form-label">Email</label>
            <input type="email" class="form-control" name="email" id="email" required>
          </div>
          <div class="mb-3">
            <label for="password" class="form-label">Password</label>
            <input type="password" class="form-control" name="password" id="password" required>
          </div>
          <button type="submit" class="btn btn-success w-100">Login</button>
        </form>

        <p class="mt-3 text-center">
          Belum punya akun? <a href="/register" class="text-decoration-none">Register</a>
        </p>
      </div>
    </div>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
