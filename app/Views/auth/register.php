<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="UTF-8">
    <title>Register - FarmUnand</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <div class="logo-container">
      <img src="<?= base_url('public/img/logo-unand.png') ?>" alt="Logo Universitas Andalas">
      <img src="<?= base_url('public/img/logo-fapet.png') ?>" alt="Logo Fakultas Peternakan">
    </div>

    <style>
      body {
        background: url("<?= base_url('public/img/bg-register.jpg') ?>") no-repeat center center fixed;
        background-size: cover;
        position: relative;
        min-height: 100vh;
      }

      body::before {
        content: "";
        position: absolute;
        top: 0; left: 0; right: 0; bottom: 0;
        background: rgba(0, 128, 0, 0.18);
        z-index: 0;
      }

      .card {
        position: relative;
        z-index: 1;
        background-color: rgba(255, 255, 255, 0.95);
        border-radius: 15px;
        box-shadow: 0 8px 20px rgba(0, 128, 0, 0.3);
      }

      /* Posisi logo di kiri atas */
      .logo-container {
        position: absolute;
        top: 20px;
        left: 30px;
        display: flex;
        align-items: center;
        gap: 10px;
        z-index: 2;
      }

      .logo-container img {
        height: 45px; /* bisa diatur sesuai kebutuhan */
        width: auto;
      }
    </style>
  </head>

  <body>
    <div class="container d-flex justify-content-center align-items-center vh-100">
      <div class="col-md-5">
        <div class="card">
          <div class="card-body p-4">
            <h3 class="text-center mb-4 text-success fw-bold">Registration FarmUnand</h3>

            <?php if (session()->getFlashdata('errors')): ?>
              <div class="alert alert-danger">
                <ul class="mb-0">
                  <?php foreach(session()->getFlashdata('errors') as $error): ?>
                    <li><?= esc($error) ?></li>
                  <?php endforeach; ?>
                </ul>
              </div>
            <?php endif; ?>

            <form action="/auth/doRegister" method="post">
              <div class="mb-3">
                <label for="username" class="form-label">Username</label>
                <input type="text" class="form-control" name="username" id="username" value="<?= old('username') ?>" required>
              </div>
              <div class="mb-3">
                <label for="email" class="form-label">Email</label>
                <input type="email" class="form-control" name="email" id="email" value="<?= old('email') ?>" required>
              </div>
              <div class="mb-3">
                <label for="password" class="form-label">Password</label>
                <input type="password" class="form-control" name="password" id="password" required>
              </div>
              <div class="mb-3">
                <label for="password_confirm" class="form-label">Konfirmasi Password</label>
                <input type="password" class="form-control" name="password_confirm" id="password_confirm" required>
              </div>
              <button type="submit" class="btn btn-success w-100">Sign Up</button>
            </form>

            <p class="mt-3 text-center">
              Sudah Punya Akun Farmunand? <a href="/login" class="text-decoration-none">Login</a>
            </p>

          </div>
        </div>
      </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
  </body>
</html>
