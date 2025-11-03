<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Register - FarmUnand</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" />

  <style>
    /* Background image dengan overlay hijau transparan */
    body {
      background: url("<?= base_url('public/img/sapi2.jpg') ?>") no-repeat center center fixed;
      background-size: cover;
      position: relative;
      min-height: 100vh;
      font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    }
    body::before {
      content: "";
      position: fixed;
      top: 0; left: 0; right: 0; bottom: 0;
      background: rgba(0, 128, 0, 0.25);
      z-index: 0;
    }

    /* Kontainer utama form register */
    .register-container {
      position: relative;
      z-index: 1;
      max-width: 480px;
      margin: 4rem auto;
      padding: 2rem;
      background: rgba(255, 255, 255, 0.95);
      border-radius: 15px;
      box-shadow: 0 12px 24px rgba(0, 128, 0, 0.3);
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

    /* Judul form */
    h3 {
      color: #2F855A;
      font-weight: 700;
      text-align: center;
      margin-bottom: 1.5rem;
    }

    /* Label dan input form */
    label {
      font-weight: 600;
      color: #2F855A;
      margin-bottom: 0.4rem;
      display: block;
    }
    input[type="text"],
    input[type="email"],
    input[type="password"] {
      width: 100%;
      padding: 0.75rem 1rem;
      border-radius: 8px;
      border: 1px solid #CBD5E0;
      margin-bottom: 1rem;
      font-size: 1rem;
      transition: border-color 0.3s ease;
    }
    input[type="text"]:focus,
    input[type="email"]:focus,
    input[type="password"]:focus {
      border-color: #38A169;
      outline: none;
      box-shadow: 0 0 5px #38A169;
    }

    /* Tombol submit */
    .btn-signup {
      background-color: #38A169;
      color: white;
      border: none;
      padding: 0.85rem;
      width: 100%;
      border-radius: 8px;
      font-weight: 700;
      cursor: pointer;
      font-size: 1.1rem;
      transition: background-color 0.3s ease;
    }
    .btn-signup:hover {
      background-color: #276749;
    }

    /* Link bawah form */
    .links {
      text-align: center;
      margin-top: 1.5rem;
      font-size: 0.95rem;
    }
    .links a {
      color: #2F855A;
      font-weight: 600;
      text-decoration: none;
    }
    .links a:hover {
      text-decoration: underline;
    }

    /* Responsive untuk layar kecil */
    @media (max-width: 480px) {
      .register-container {
        margin: 1.5rem 1rem;
        padding: 1.5rem;
      }
      h3 {
        font-size: 1.25rem;
      }
    }
  </style>
</head>
<body>

  <!-- Logo fakultas dan universitas -->
  <div class="logo-container">
    <img src="<?= base_url('public/img/logo-unand.png') ?>" alt="[translate:Logo Universitas Andalas]" />
    <img src="<?= base_url('public/img/logo-fapet.png') ?>" alt="[translate:Logo Fakultas Peternakan]" />
  </div>

  <!-- Kontainer form registrasi -->
  <main class="register-container" role="main">
    <h3>Registration FarmUnand</h3>

    <!-- Tampilan error dari session -->
    <?php if (session()->getFlashdata('errors')): ?>
      <div class="alert alert-danger" role="alert">
        <ul class="mb-0">
          <?php foreach(session()->getFlashdata('errors') as $error): ?>
            <li><?= esc($error) ?></li>
          <?php endforeach; ?>
        </ul>
      </div>
    <?php endif; ?>

    <!-- Form register -->
    <form action="/auth/doRegister" method="post" novalidate>
      <label for="username">Username</label>
      <input type="text" id="username" name="username" value="<?= old('username') ?>" required autocomplete="username" />

      <label for="email">Email</label>
      <input type="email" id="email" name="email" value="<?= old('email') ?>" required autocomplete="email" />

      <label for="password">Password</label>
      <input type="password" id="password" name="password" required autocomplete="new-password" />

      <label for="password_confirm">Konfirmasi Password</label>
      <input type="password" id="password_confirm" name="password_confirm" required autocomplete="new-password" />

      <button type="submit" class="btn-signup">Sign Up</button>
    </form>

    <!-- Link ke login -->
    <div class="links">
      <p>Sudah Punya Akun Farmunand? <a href="/login">Login</a></p>
    </div>
  </main>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>
