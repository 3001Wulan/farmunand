<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8"/>
  <meta name="viewport" content="width=device-width, initial-scale=1"/>

  <title>Manajemen Akun User - Admin</title>

  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet"/>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet"/>
  <style>
    body {
        background: linear-gradient(135deg, #e6f4ea, #c0e0cc);
        font-family: "Segoe UI", Tahoma, Geneva, Verdana, sans-serif;
        min-height: 100vh;
        margin: 0;
      }
    .content {
      margin-left: 250px;
      padding: 30px;
    }
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
    .table thead th {
      background: #198754;
      color: #fff;
      border: none;
      text-align: center;
    }
    .table tbody tr:hover {
      background: #f1fdf6;
      cursor: pointer;
    }
    .table td, .table th {
      text-align: center;
      vertical-align: middle;
      padding: 12px;
    }
    .btn-action {
      padding: 6px 14px;
      border-radius: 8px;
      font-size: 14px;
      font-weight: 500;
      transition: 0.2s;
      display: inline-flex;
      align-items: center;
      gap: 6px;
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
      color: white;
    }
    .btn-danger:hover {
      background: #a71d2a;
      transform: translateY(-2px);
    }
    .badge {
      font-size: 13px;
      padding: 6px 12px;
      border-radius: 12px;
    }
    .badge.bg-success { background: #198754 !important; }
    .badge.bg-secondary { background: #6c757d !important; }
    .badge.bg-primary { background: #0d6efd !important; }
    /* Styling modal content dengan border hijau */
    .modal-content {
      border-radius: 15px;
      border: 3px solid #198754; /* Accent border hijau */
      box-shadow: 0 8px 20px rgba(25, 135, 84, 0.4);
      transition: box-shadow 0.3s ease;
    }
    .modal-content:hover {
      box-shadow: 0 12px 30px rgba(25, 135, 84, 0.6);
    }
    /* Styling header modal dengan background hijau dan teks putih */
    .modal-header {
      background: linear-gradient(135deg, #198754, #28a745);
      color: white;
      border-bottom: none;
      border-radius: 15px 15px 0 0;
    }
    /* Styling tombol tutup modal */
    .modal-header .btn-close {
      filter: brightness(0) invert(1);
    }

    /* Styling body modal dengan padding nyaman */
    .modal-body {
      font-size: 1rem;
      color: #333;
      padding: 1.5rem 2rem;
      text-align: center;
    }

    /* Tombol Hapus dengan warna hijau solid */
    .modal-footer .btn-danger {
      background: #dc3545;
      border: none;
      color: white;
      font-weight: 600;
      box-shadow: 0 4px 8px rgba(25, 135, 84, 0.5);
      transition: background 0.3s ease, box-shadow 0.3s ease, transform 0.2s ease;
      border-radius: 8px;
      padding: 0.6rem 1.4rem;
    }

    .modal-footer .btn-danger:hover {
      background: linear-gradient(135deg, #28a745, #198754);
      box-shadow: 0 6px 12px rgba(25, 135, 84, 0.7);
      transform: translateY(-3px);
    }

    /* Tombol Batal gaya netral */
    .modal-footer .btn-secondary {
      border-radius: 8px;
      padding: 0.6rem 1.4rem;
    }
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
            <div class="alert alert-success" role="alert"><?= esc(session()->getFlashdata('success')) ?></div>
          <?php endif; ?>
          <?php if (session()->getFlashdata('error')): ?>
            <div class="alert alert-danger" role="alert"><?= esc(session()->getFlashdata('error')) ?></div>
          <?php endif; ?>

          <div class="card">
            <div class="card-body">
              <form class="row g-2 mb-3" method="get" action="<?= base_url('manajemenakunuser'); ?>">
                <div class="col-sm-6">
                  <input type="search" name="keyword" class="form-control"
                         placeholder="Cari nama, email, atau username…"
                         value="<?= esc((string)($keyword ?? '')) ?>" aria-label="Cari pengguna" />
                </div>
                <div class="col-sm-3">
                  <select name="role" class="form-select" aria-label="Filter per role">
                    <option value="">Semua Role</option>
                    <option value="admin" <?= (isset($role) && $role==='admin') ? 'selected' : '' ?>>Admin</option>
                    <option value="user"  <?= (isset($role) && $role==='user')  ? 'selected' : '' ?>>User</option>
                  </select>
                </div>
                <div class="col-sm-3">
                  <button class="btn btn-success w-100" type="submit" aria-label="Terapkan filter">Filter</button>
                </div>
              </form>

              <div class="table-responsive">
                <table class="table table-striped align-middle">
                  <thead>
                    <tr>
                      <th scope="col">No</th>
                      <th scope="col">Nama</th>
                      <th scope="col">Email</th>
                      <th scope="col">No. HP</th>
                      <th scope="col">Status</th>
                      <th scope="col">Role</th>
                      <th scope="col">Aksi</th>
                    </tr>
                  </thead>
                  <tbody>
                    <?php if (!empty($users)): ?>
                      <?php $i = 1; foreach ($users as $user): ?>
                        <?php
                          $rawStatus = strtolower(trim((string)($user['status'] ?? '')));
                          $labelStatus = $rawStatus === 'aktif' ? 'Aktif' : 'Nonaktif';
                          $classStatus = $rawStatus === 'aktif' ? 'bg-success' : 'bg-secondary';
                          $roleClass = ($user['role'] ?? '') === 'admin' ? 'bg-primary' : 'bg-secondary';
                        ?>
                        <tr>
                          <td><?= $i++; ?></td>
                          <td><?= esc($user['nama'] ?? '-') ?></td>
                          <td><?= esc($user['email'] ?? '-') ?></td>
                          <td><?= esc($user['no_hp'] ?? '-') ?></td>
                          <td><span class="badge <?= $classStatus ?>"><?= esc($labelStatus) ?></span></td>
                          <td><span class="badge <?= $roleClass ?>"><?= esc($user['role'] ?? '-') ?></span></td>
                          <td class="text-center">
                            <a href="<?= base_url('manajemenakunuser/edit/'.($user['id_user'] ?? 0)) ?>"
                               class="btn btn-sm btn-warning d-inline-flex align-items-center gap-1"
                               aria-label="Edit akun: <?= esc($user['nama'] ?? 'Tanpa Nama') ?>"
                               title="Edit akun">
                              <i class="bi bi-pencil" aria-hidden="true"></i> Edit
                            </a>
                            <button type="button"
                                    class="btn btn-sm btn-danger d-inline-flex align-items-center gap-1 btn-delete-user"
                                    aria-label="Hapus akun: <?= esc($user['nama'] ?? 'Tanpa Nama') ?>"
                                    title="Hapus akun"
                                    data-userid="<?= esc($user['id_user']) ?>"
                                    data-username="<?= esc($user['nama']) ?>">
                              <i class="bi bi-trash" aria-hidden="true"></i> Hapus
                            </button>
                          </td>
                        </tr>
                      <?php endforeach; ?>
                    <?php else: ?>
                      <tr>
                        <td colspan="7" class="text-center text-muted">Belum ada data pengguna.</td>
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

  <!-- Modal Konfirmasi Hapus -->
  <div class="modal fade" id="deleteUserModal" tabindex="-1" aria-labelledby="deleteUserModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
      <div class="modal-content">
        <form id="form-delete-user" method="post" action="">
          <?= csrf_field() ?>
          <input type="hidden" name="id_user" id="modalUserId" value="" />
          <div class="modal-header">
            <h5 class="modal-title" id="deleteUserModalLabel">Konfirmasi Hapus Akun</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
          </div>
          <div class="modal-body">
            Apakah Anda yakin ingin menghapus akun <strong id="modalUserName"></strong>?
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
            <button type="submit" class="btn btn-danger">Ya, Hapus</button>
          </div>
        </form>
      </div>
    </div>
  </div>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
  <script>
    document.querySelectorAll('.btn-delete-user').forEach(button => {
      button.addEventListener('click', () => {
        const userId = button.getAttribute('data-userid');
        const userName = button.getAttribute('data-username');
        const modal = new bootstrap.Modal(document.getElementById('deleteUserModal'));

        document.getElementById('modalUserId').value = userId;
        document.getElementById('modalUserName').textContent = userName;
        document.getElementById('form-delete-user').action = '<?= base_url('manajemenakunuser/delete/') ?>' + userId;

        modal.show();
      });
    });
  </script>
</body>
</html>
