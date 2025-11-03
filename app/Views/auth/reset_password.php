<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Reset Password</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" />
  <style>
    body {
      min-height: 100vh;
      background: linear-gradient(rgba(0, 128, 0, 0.25), rgba(0, 128, 0, 0.45)),
                  url('<?= base_url("public/img/telur2.png") ?>') no-repeat center center fixed;
      background-size: cover;
      display: flex;
      justify-content: center;
      align-items: center;
      font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
      margin: 0;
      padding: 0 1rem;
    }
    .card {
      border-radius: 15px;
      box-shadow: 0 8px 20px rgba(0, 128, 0, 0.3);
      max-width: 400px;
      width: 100%;
      padding: 2rem;
    }
    h4 {
      text-align: center;
      color: #276749;
      font-weight: 700;
      margin-bottom: 1.5rem;
    }
    label {
      font-weight: 600;
      color: #276749;
      display: block;
      margin-bottom: 0.5rem;
    }
    input[type="password"] {
      width: 100%;
      padding: 0.75rem 1rem;
      border-radius: 8px;
      border: 1px solid #CBD5E0;
      margin-bottom: 1.5rem;
      font-size: 1rem;
      transition: border-color 0.3s ease;
    }
    input[type="password"]:focus {
      border-color: #38A169;
      outline: none;
      box-shadow: 0 0 5px #38A169;
    }
    .btn-success {
      background-color: #198754;
      border: none;
      padding: 0.75rem;
      width: 100%;
      border-radius: 8px;
      font-weight: 700;
      font-size: 1.1rem;
      cursor: pointer;
      transition: background-color 0.3s ease;
    }
    .btn-success:hover {
      background-color: #157347;
    }
    .text-center a {
      color: #276749;
      text-decoration: none;
      font-weight: 600;
    }
    .text-center a:hover {
      text-decoration: underline;
    }
    @media (max-width: 480px) {
      .card {
        padding: 1.5rem;
      }
    }
    /* Logo di pojok kiri atas */
    .logo-container {
      position: fixed;
      top: 20px;
      left: 30px;
      display: flex;
      align-items: center;
      gap: 15px;
      z-index: 2;
    }
    .logo-container img {
      height: 50px;
      width: auto;
      vertical-align: middle;
    }
  </style>
</head>
<body>
  <!-- Logo fakultas dan universitas -->
  <div class="logo-container">
    <img src="<?= base_url('public/img/logo-unand.png') ?>" alt="[translate:Logo Universitas Andalas]" />
    <img src="<?= base_url('public/img/logo-fapet.png') ?>" alt="[translate:Logo Fakultas Peternakan]" />
  </div>
  
  <div class="card">
    <h4>Reset Password</h4>

    <form action="/auth/doResetPassword" method="post" novalidate>
      <input type="hidden" name="token" value="<?= esc($token) ?>" />
      
      <label for="password">Password Baru</label>
      <input type="password" id="password" name="password" placeholder="Masukkan password baru" required autocomplete="new-password" />
      
      <label for="password_confirm">Konfirmasi Password</label>
      <input type="password" id="password_confirm" name="password_confirm" placeholder="Ulangi password baru" required autocomplete="new-password" />
      
      <button type="submit" class="btn btn-success">Reset Password</button>
    </form>

    <p class="mt-3 text-center">
      <a href="/login">Kembali ke Login</a>
    </p>
  </div>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
