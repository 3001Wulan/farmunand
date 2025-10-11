<!DOCTYPE html>
<html lang="id">
  <head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= esc($title) ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
      .content { margin-left: 240px; padding: 30px; }
      .card { border-radius: 12px; box-shadow: 0 4px 15px rgba(0,0,0,0.1); }
      .card-header { background: linear-gradient(135deg, #198754, #28a745); color: white; font-weight: bold; border-radius: 12px 12px 0 0 !important; }
      .form-label { font-weight: 600; color: #198754; }
      .btn-primary { background: #198754; border: none; }
      .btn-primary:hover { background: #145c32; }
      .content {
        padding: 30px;
        margin-left: 250px;
      }
    </style>
  </head>

  <body>
    <!-- Sidebar dari layouts -->
    <?= $this->include('layout/sidebarAdmin') ?>
    
    <!-- Content -->
    <div class="content">
      <div class="card">
        <div class="card-header">Edit Profil Admin</div>
        <div class="card-body">
          <form action="<?= base_url('profileadmin/update') ?>" method="post" enctype="multipart/form-data">
            <?= csrf_field() ?>
            <div class="mb-3">
              <label class="form-label">Username</label>
              <input type="text" name="username" value="<?= esc($user['username']) ?>" class="form-control" required>
            </div>
            <div class="mb-3">
              <label class="form-label">Nama</label>
              <input type="text" name="nama" value="<?= esc($user['nama']) ?>" class="form-control">
            </div>
            <div class="mb-3">
              <label class="form-label">Email</label>
              <input type="email" name="email" value="<?= esc($user['email']) ?>" class="form-control" required>
            </div>
            <div class="mb-3">
              <label class="form-label">No HP</label>
              <input type="text" name="no_hp" value="<?= esc($user['no_hp']) ?>" class="form-control">
            </div>
            <div class="mb-3">
              <label class="form-label">Foto Profil</label><br>
              <img src="<?= base_url('uploads/profile/' . ($user['foto'] ?? 'default.png')) ?>" alt="Foto Profil" width="100" class="mb-2 rounded">
              <input type="file" name="foto" class="form-control">
            </div>
            <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
            <a href="<?= base_url('profileadmin') ?>" class="btn btn-secondary">Batal</a>
          </form>
        </div>
      </div>
    </div>
  </body>
</html>
