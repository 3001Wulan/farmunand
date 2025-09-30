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
       <div class="main-content">
          <?php if (session()->getFlashdata('success')): ?>
            <div class="alert alert-success" role="alert">
              <?= esc(session()->getFlashdata('success')) ?>
            </div>
          <?php endif; ?>
          <?php if (session()->getFlashdata('error')): ?>
            <div class="alert alert-danger" role="alert">
              <?= esc(session()->getFlashdata('error')) ?>
            </div>
          <?php endif; ?>

          <div class="card">
            <div class="card-body">
              <form class="row g-2 mb-3" method="get" action="<?= base_url('manajemenakunuser'); ?>">
                <div class="col-sm-6">
                  <input type="search"
                        name="keyword"
                        class="form-control"
                        placeholder="Cari nama, email, atau username…"
                        value="<?= esc((string)($keyword ?? '')) ?>">
                </div>
                <div class="col-sm-3">
                  <select name="role" class="form-select">
                    <option value="">Semua Role</option>
                    <option value="admin" <?= (isset($role) && $role==='admin') ? 'selected' : '' ?>>Admin</option>
                    <option value="user"  <?= (isset($role) && $role==='user')  ? 'selected' : '' ?>>User</option>
                  </select>
                </div>
                <div class="col-sm-3">
                  <button class="btn btn-secondary w-100" type="submit"
                          aria-label="Terapkan filter">Filter</button>
                </div>
              </form>

              <div class="table-responsive">
                <table class="table table-striped align-middle">
                  <thead>
                    <tr>
                      <th scope="col">#</th>
                      <th scope="col">Nama</th>
                      <th scope="col">Email</th>
                      <th scope="col">No. HP</th>
                      <th scope="col">Role</th>
                      <th scope="col">Aksi</th>
                    </tr>
                  </thead>
                  <tbody>
                    <?php if (!empty($users)): ?>
                      <?php $i = 1; foreach ($users as $user): ?>
                        <tr>
                          <td><?= $i++; ?></td>
                          <td><?= esc($user['nama'] ?? '-') ?></td>
                          <td><?= esc($user['email'] ?? '-') ?></td>
                          <td><?= esc($user['no_hp'] ?? '-') ?></td>
                          <td>
                            <span class="badge <?= ($user['role'] ?? '') === 'admin' ? 'bg-primary' : 'bg-secondary' ?>">
                              <?= esc($user['role'] ?? '-') ?>
                            </span>
                          </td>
                          <td class="text-center">
                            <!-- Edit (ikon + teks) -->
                            <a href="<?= base_url('manajemenakunuser/edit/'.($user['id_user'] ?? 0)) ?>"
                              class="btn btn-sm btn-warning d-inline-flex align-items-center gap-1"
                              aria-label="Edit akun: <?= esc($user['nama'] ?? 'Tanpa Nama') ?>"
                              title="Edit akun">
                              <i class="bi bi-pencil" aria-hidden="true"></i>
                              <span>Edit</span>
                            </a>

                            <!-- Hapus: POST + CSRF (ikon + teks) -->
                            <form action="<?= base_url('manajemenakunuser/delete/'.($user['id_user'] ?? 0)) ?>"
                                  method="post"
                                  class="d-inline">
                              <?= csrf_field() ?>
                              <button type="submit"
                                      class="btn btn-sm btn-danger d-inline-flex align-items-center gap-1"
                                      aria-label="Hapus akun: <?= esc($user['nama'] ?? 'Tanpa Nama') ?>"
                                      title="Hapus akun"
                                      onclick="return confirm('Yakin ingin menghapus user ini?')">
                                <i class="bi bi-trash" aria-hidden="true"></i>
                                <span>Hapus</span>
                              </button>
                            </form>
                          </td>
                        </tr>
                      <?php endforeach; ?>
                    <?php else: ?>
                      <tr>
                        <td colspan="6" class="text-center text-muted">
                          Belum ada data pengguna.
                        </td>
                      </tr>
                    <?php endif; ?>
                  </tbody>
                </table>
              </div>

              <?php if (isset($pager)) : ?>
                <div class="mt-3">
                  <?= $pager->links(); ?>
                </div>
              <?php endif; ?>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</body>
</html>
