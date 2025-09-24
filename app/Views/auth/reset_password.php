<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Password</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
      body {
        height: 100vh;
        background: linear-gradient(rgba(0, 100, 0, 0.4), rgba(0, 100, 0, 0.6)), url('/images/bg-login.jpg') no-repeat center center fixed;
        background-size: cover;
        display: flex;
        justify-content: center;
        align-items: center;
      }
      .card {
        border-radius: 15px;
        box-shadow: 0px 4px 20px rgba(0, 128, 0, 0.4);
      }
      .btn-success {
        background-color: #198754;
        border: none;
      }
      .btn-success:hover {
        background-color: #157347;
      }
    </style>
  </head>

  <body>
    <div class="col-md-4">
      <div class="card p-4">
        <h4 class="text-center mb-3">Reset Password</h4>

        <form action="/auth/doResetPassword" method="post">
          <input type="hidden" name="token" value="<?= esc($token) ?>">
          <div class="mb-3">
            <label>Password Baru</label>
            <input type="password" name="password" class="form-control" placeholder="Masukkan password baru" required>
          </div>
          <div class="mb-3">
            <label>Konfirmasi Password</label>
            <input type="password" name="password_confirm" class="form-control" placeholder="Ulangi password baru" required>
          </div>
          <button type="submit" class="btn btn-success w-100">Reset Password</button>
        </form>

        <p class="mt-3 text-center">
          <a href="/login" class="text-decoration-none">Kembali ke Login</a>
        </p>
         
      </div>
    </div>
  </body>
</html>
