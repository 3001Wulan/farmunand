<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Manajemen Akun User - Admin</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">

  <style>
    body {
      background: #f8f9fa;
      font-family: "Segoe UI", Tahoma, Geneva, Verdana, sans-serif;
    }

    .content {
      margin-left: 250px;
      padding: 30px;
    }

    /* Header hijau */
    .page-header {
      background: linear-gradient(135deg, #198754, #28a745);
      color: white;
      border-radius: 12px 12px 0 0;
      padding: 20px 30px;
      display: flex;
      justify-content: space-between;
      align-items: center;
    }

    .page-header h3 {
      margin: 0;
      font-weight: 700;
      font-size: 22px;
      display: flex;
      align-items: center;
      gap: 10px;
    }

    .card-container {
      background: #fff;
      border-radius: 0 0 12px 12px;
      box-shadow: 0 4px 12px rgba(0,0,0,0.08);
      padding: 20px;
    }

    /* Table */
    .table thead th {
      background: #198754;
      color: #fff;
      border: none;
      text-align: center;
    }
    .table tbody tr:hover {
      background: #f1fdf6;
    }
    .table td, .table th {
      text-align: center;
      vertical-align: middle;
      padding: 12px;
    }

    /* Tombol */
    .btn-action {
      padding: 6px 14px;
      border-radius: 8px;
      font-size: 14px;
      font-weight: 500;
      transition: 0.2s;
    }
    .btn-warning {
      background: #ffc107;
      border: none;
      color: #333;
    }
    .btn-warning:hover {
      background: #e0a800;
      color: white;
      transform: translateY(-2px);
    }
    .btn-danger {
      background: #dc3545;
      border: none;
    }
    .btn-danger:hover {
      background: #a71d2a;
      transform: translateY(-2px);
    }

    /* Badge status */
    .badge {
      font-size: 13px;
      padding: 6px 12px;
      border-radius: 12px;
    }
    .badge.bg-success { background: #198754 !important; }
    .badge.bg-secondary { background: #6c757d !important; }
  </style>
</head>
<body>
  <div class="container-fluid px-0">
    <div class="row g-0">

      <!-- Sidebar -->
      <?= $this->include('layout/sidebarAdmin') ?>

      <!-- Content -->
      <div class="col content">

        <!-- Header Hijau -->
        <div class="page-header">
          <h3>👥 Manajemen Akun User</h3>
        </div>

        <!-- Card Isi -->
        <div class="card-container">
          <table class="table table-hover align-middle">
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
                      <a href="<?= site_url('manajemenakunuser/edit/'.$user['id_user']) ?>" class="btn btn-warning btn-sm btn-action">✏️ Edit</a>
                      <a href="<?= site_url('manajemenakunuser/delete/'.$user['id_user']) ?>" onclick="return confirm('Yakin ingin menghapus user ini?')" class="btn btn-danger btn-sm btn-action">🗑 Hapus</a>
                    </td>
                  </tr>
                <?php endforeach; ?>
              <?php else: ?>
                <tr>
                  <td colspan="6" class="text-center text-muted">Belum ada data user.</td>
                </tr>
              <?php endif; ?>
            </tbody>
          </table>
        </div>

      </div>
    </div>
  </div>
</body>
</html>
