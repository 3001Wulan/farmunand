<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title><?= esc($title) ?></title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet" />
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet" />
  <style>
    body {
      background: linear-gradient(135deg, #e6f4ea, #c0e0cc);
      font-family: "Segoe UI", Tahoma, Geneva, Verdana, sans-serif;
      min-height: 100vh;
      margin: 0;
    }
    .content {
      margin-left: auto;
      padding: 30px 40px;
      max-width: 720px;
      margin-right: auto;
    }
    .card {
      border-radius: 18px;
      box-shadow: 0 6px 20px rgba(0,0,0,0.12);
      border: none;
      background: white;
      overflow: hidden;
      user-select: none;
    }
    .card-header {
      background: linear-gradient(135deg, #198754, #28a745);
      color: white;
      font-weight: 800;
      font-size: 1.6rem;
      border-radius: 18px 18px 0 0 !important;
      padding: 24px 32px;
      user-select: none;
    }
    .card-body {
      padding: 30px 32px;
    }
    label.form-label {
      font-weight: 600;
      color: #198754;
      font-size: 1rem;
      margin-bottom: 8px;
      display: inline-block;
    }
    input.form-control, input.form-control[type="file"], textarea.form-control {
      padding: 12px 14px;
      font-size: 1rem;
      border-radius: 12px;
      border: 1px solid #c1e1c1;
      transition: border-color 0.3s ease;
    }
    input.form-control:focus, input.form-control[type="file"]:focus, textarea.form-control:focus {
      border-color: #198754;
      box-shadow: 0 0 8px #a3d1a3;
      outline: none;
    }
    img.profile-preview {
      width: 110px;
      height: 110px;
      border-radius: 50%;
      object-fit: cover;
      border: 4px solid #198754;
      box-shadow: 0 6px 16px rgba(0,0,0,0.2);
      margin-bottom: 16px;
      display: block;
    }
    .btn-primary {
      background: #198754;
      border: none;
      font-weight: 700;
      font-size: 1.1rem;
      padding: 12px 28px;
      border-radius: 12px;
      transition: background 0.3s ease, transform 0.2s ease;
      cursor: pointer;
      user-select: none;
    }
    .btn-primary:hover {
      background: #145c32;
      transform: translateY(-3px);
    }
    .btn-secondary {
      border-radius: 12px;
      padding: 12px 28px;
      font-weight: 600;
      font-size: 1.1rem;
      margin-left: 16px;
      cursor: pointer;
      user-select: none;
    }
    @media (max-width: 768px) {
      .content {
        margin-left: 0;
        padding: 20px;
        max-width: 100%;
      }
      .btn-secondary {
        margin-left: 0;
        margin-top: 12px;
        width: 100%;
      }
    }
  </style>
</head>
<body>
  <?= $this->include('layout/sidebarAdmin') ?>
  <main class="content" role="main">
    <div class="card" tabindex="0" aria-label="Form Edit Profil Admin">
      <header class="card-header">
        Edit Profil Admin
      </header>
      <section class="card-body">
        <form action="<?= base_url('profileadmin/update') ?>" method="post" enctype="multipart/form-data" novalidate>
          <?= csrf_field() ?>
          <div class="mb-4">
            <label for="username" class="form-label">Username</label>
            <input type="text" name="username" id="username" value="<?= esc($user['username']) ?>" class="form-control" required aria-required="true" />
          </div>
          <div class="mb-4">
            <label for="nama" class="form-label">Nama</label>
            <input type="text" name="nama" id="nama" value="<?= esc($user['nama']) ?>" class="form-control" />
          </div>
          <div class="mb-4">
            <label for="email" class="form-label">Email</label>
            <input type="email" name="email" id="email" value="<?= esc($user['email']) ?>" class="form-control" required aria-required="true" />
          </div>
          <div class="mb-4">
            <label for="no_hp" class="form-label">No HP</label>
            <input type="text" name="no_hp" id="no_hp" value="<?= esc($user['no_hp']) ?>" class="form-control" />
          </div>
          <div class="mb-4">
            <label class="form-label">Foto Profil</label><br />
            <img src="<?= base_url('uploads/profile/' . ($user['foto'] ?? 'default.png')) ?>" alt="Foto Profil" class="profile-preview" />
            <input type="file" name="foto" id="foto" class="form-control" aria-label="Upload foto profil" accept="image/*" />
          </div>
          <button type="submit" class="btn btn-primary" aria-label="Simpan perubahan profil">Simpan Perubahan</button>
          <a href="<?= base_url('profileadmin') ?>" class="btn btn-secondary" aria-label="Batal dan kembali ke profil">Batal</a>
        </form>
      </section>
    </div>
  </main>
</body>
</html>