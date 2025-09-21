<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Manajemen Akun User - Admin</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <style>
html, body {
  margin: 0;
  padding: 0;
  height: 100%;
  background: #f8f9fa;
}

/* Hapus padding container bootstrap */
.container-fluid {
  margin: 0;
  padding: 0;
}

/* Hapus gap row */
.row.g-0 {
  margin: 0;
}

/* Sidebar */
.sidebar {
  min-height: 100vh;
  background: #198754; /* hijau */
  padding: 20px;
  margin: 0; 
  color: white;
}

/* Profil di sidebar */
.sidebar .profile {
  width: 120px;
  height: 120px;
  border-radius: 50%;
  background: white;
  margin: 0 auto 20px auto;
  display: flex;
  align-items: center;
  justify-content: center;
  font-weight: bold;
  color: #198754;
  font-size: 18px;
}

/* Link sidebar */
.sidebar a {
  display: block;
  padding: 10px;
  margin: 10px 0;
  background: white;
  color: #198754;
  text-decoration: none;
  border-radius: 5px;
  font-weight: 500;
  text-align: center;
  transition: all 0.3s;
}

.sidebar a:hover,
.sidebar a.active {
  background: #145c32;
  color: white;
}

/* Content */
.content {
  padding: 30px;
}

table th {
  background: #198754;
  color: white;
}
  </style>
</head>
<body>
<div class="container-fluid">
  <div class="row g-0">
    
    <!-- Sidebar -->
    <div class="col-md-3 col-lg-2 sidebar">
      <div class="profile">Admin</div>
      <a href="#">Profil</a>
      <a href="dashboard">Dashboard</a>
      <a href="#">Product</a>
      <a href="mengelolariwayatpesanan">Pesanan</a>
      <a href="#" class="active">Manajemen User</a>
      <a href="#">Log Out</a>
    </div>

    <!-- Content -->
    <div class="col-md-9 col-lg-10 content">
      <h3 class="mb-4 text-success">Manajemen Akun User</h3>
      
      <table class="table table-bordered table-hover">
        <thead>
          <tr>
            <th>#</th>
            <th>Nama</th>
            <th>Email</th>
            <th>No. HP</th>
            <th>Status</th>
            <th>Aksi</th>
          </tr>
        </thead>
        <tbody>
          <?php if (!empty($users) && is_array($users)): ?>
            <?php $no = 1; foreach ($users as $user): ?>
              <tr>
                <td><?= $no++ ?></td>
                <td><?= esc($user['nama']) ?></td>
                <td><?= esc($user['email']) ?></td>
                <td><?= esc($user['no_hp']) ?></td>
                <td>
                  <?php if ($user['status'] === 'Aktif'): ?>
                    <span class="badge bg-success">Aktif</span>
                  <?php else: ?>
                    <span class="badge bg-secondary">Nonaktif</span>
                  <?php endif; ?>
                </td>
                <td>
                  <a href="<?= site_url('manajemenakunuser/edit/'.$user['id_user']) ?>" class="btn btn-sm btn-warning">Edit</a>
                  <a href="<?= site_url('manajemenakunuser/delete/'.$user['id_user']) ?>" 
                     onclick="return confirm('Yakin ingin menghapus user ini?')" 
                     class="btn btn-sm btn-danger">Hapus</a>
                </td>
              </tr>
            <?php endforeach; ?>
          <?php else: ?>
            <tr>
              <td colspan="6" class="text-center">Belum ada data user.</td>
            </tr>
          <?php endif; ?>
        </tbody>
      </table>
    </div>

  </div>
</div>
</body>
</html>
