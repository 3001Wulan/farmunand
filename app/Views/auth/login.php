<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Login - FarmUnand</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" />

  <style>
    body {
      background: url("<?= base_url('public/img/sapi.jpg') ?>") no-repeat center center fixed;
      background-size: cover;
      position: relative;
      min-height: 100vh;
      font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    }
    /* Overlay hijau transparan */
    body::before {
      content: "";
      position:fixed;
      top: 0; left: 0; right: 0; bottom: 0;
      background: rgba(0, 128, 0, 0.25);
      z-index: 0;
    }
    .login-container {
      position: relative;
      z-index: 1;
      background: rgba(255, 255, 255, 0.95);
      border-radius: 15px;
      max-width: 420px;
      margin: 50px auto;
      padding: 2rem;
      box-shadow: 0 12px 24px rgba(0, 0, 0, 0.2);
    }
    .logo-container {
      text-align: center;
      margin-bottom: 1.5rem;
      display: flex;
      justify-content: center;
      gap: 15px;
    }
    .logo-container img {
      height: 50px;
      width: auto;
      vertical-align: middle;
    }
    h3 {
      color: #2F855A;
      text-align: center;
      margin-bottom: 1.5rem;
      font-weight: 700;
    }
    label {
      font-weight: 600;
      color: #2F855A;
      margin-bottom: 0.4rem;
    }
    input[type="email"],
    input[type="password"] {
      width: 100%;
      padding: 0.75rem 1rem;
      border-radius: 8px;
      border: 1px solid #CBD5E0;
      margin-bottom: 1rem;
      transition: border-color 0.3s ease;
      font-size: 1rem;
    }
    input[type="email"]:focus,
    input[type="password"]:focus {
      border-color: #38A169;
      outline: none;
      box-shadow: 0 0 5px #38A169;
    }
    .btn-login {
      background-color: #38A169;
      color: white;
      border: none;
      padding: 0.85rem;
      width: 100%;
      border-radius: 8px;
      font-weight: 700;
      cursor: pointer;
      transition: background-color 0.3s ease;
      font-size: 1.1rem;
    }
    .btn-login:hover {
      background-color: #276749;
    }
    .show-password {
      font-size: 0.9rem;
      color: #4A5568;
      cursor: pointer;
      margin-bottom: 1rem;
      user-select: none;
    }
    .links {
      text-align: center;
      margin-top: 1rem;
      font-size: 0.9rem;
    }
    .links a {
      color: #2F855A;
      font-weight: 600;
      text-decoration: none;
    }
    .links a:hover {
      text-decoration: underline;
    }
    /* Responsive for smaller devices */
    @media (max-width: 480px) {
      .login-container {
        margin: 1rem;
        padding: 1.5rem;
      }
      h3 {
        font-size: 1.25rem;
      }
    }
  </style>
</head>
<body>
  
  <div class="login-container">
    <div class="logo-container">
      <img src="<?= base_url('public/img/logo-unand.png') ?>" alt="Logo Universitas Andalas" />
      <img src="<?= base_url('public/img/logo-fapet.png') ?>" alt="Logo Fakultas Peternakan" />
    </div>
    <h3>Selamat Datang Di Website Penjualan FarmUnand</h3>

    <?php if (session()->getFlashdata('error')): ?>
      <div class="alert alert-danger"><?= session()->getFlashdata('error') ?></div>
    <?php endif; ?>

    <?php if (session()->getFlashdata('success')): ?>
      <div class="alert alert-success"><?= session()->getFlashdata('success') ?></div>
    <?php endif; ?>

    <form action="/auth/doLogin" method="post">
      <label for="email">Email</label>
      <input type="email" id="email" name="email" autocomplete="email" required />

      <label for="password">Password</label>
      <input type="password" id="password" name="password" autocomplete="current-password" required />
      
      <label class="show-password">
        <input type="checkbox" onclick="togglePassword()" /> Tampilkan Password
      </label>

      <button type="submit" class="btn-login">Login</button>
    </form>

    <div class="links">
      <p><a href="/forgot-password">Lupa password?</a></p>
      <p>Belum punya akun? <a href="/register">Register</a></p>
    </div>
  </div>

  <script>
    function togglePassword() {
      const pwd = document.getElementById('password');
      pwd.type = pwd.type === 'password' ? 'text' : 'password';
    }
  </script>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
