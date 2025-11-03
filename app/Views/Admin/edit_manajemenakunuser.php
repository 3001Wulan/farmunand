<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Edit Akun User - Admin</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet" />
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet" />
  <style>
    body {
        background: linear-gradient(135deg, #e6f4ea, #c0e0cc);
        font-family: "Segoe UI", Tahoma, Geneva, Verdana, sans-serif;
        min-height: 100vh;
        margin: 0;
      }
    .main-content {
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
      box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
    }
    .page-header h3 {
      margin: 0;
      font-weight: 700;
      font-size: 24px;
      display: flex;
      align-items: center;
      gap: 12px;
    }
    .card-container {
      background: #fff;
      border-radius: 0 0 12px 12px;
      box-shadow: 0 6px 18px rgba(0, 0, 0, 0.08);
      padding: 30px 25px;
      margin-top: -4px;
    }
    label {
      font-weight: 600;
      color: #234d20;
    }
    input.form-control,
    select.form-select {
      padding: 12px 14px;
      font-size: 1rem;
      border-radius: 8px;
      border: 1px solid #c1e1c1;
      transition: border-color 0.3s ease;
    }
    input.form-control:focus,
    select.form-select:focus {
      border-color: #198754;
      box-shadow: 0 0 8px #a3d1a3;
      outline: none;
    }
    .btn-action {
      padding: 10px 22px;
      border-radius: 8px;
      font-size: 16px;
      font-weight: 600;
      transition: background-color 0.3s ease, transform 0.2s ease;
      cursor: pointer;
      user-select: none;
    }
    .btn-primary.btn-action {
      background: linear-gradient(135deg, #198754, #28a745);
      border: none;
      color: white;
      box-shadow: 0 6px 12px rgba(25, 135, 84, 0.3);
    }
    .btn-primary.btn-action:hover {
      background: linear-gradient(135deg, #28a745, #198754);
      transform: translateY(-3px);
      box-shadow: 0 8px 16px rgba(25, 135, 84, 0.45);
    }
    .btn-secondary.btn-action {
      background: #6c757d;
      border: none;
      color: white;
      box-shadow: 0 4px 10px rgba(108, 117, 125, 0.3);
    }
    .btn-secondary.btn-action:hover {
      background: #545b62;
      transform: translateY(-3px);
      box-shadow: 0 6px 14px rgba(84, 91, 98, 0.45);
    }
    @media (max-width: 576px) {
      .main-content {
        margin-left: 0;
        padding: 15px;
      }
      .page-header {
        flex-direction: column;
        align-items: flex-start;
        gap: 12px;
        padding: 15px 20px;
      }
      .btn-action {
        width: 100%;
        text-align: center;
      }
      .card-container {
        padding: 20px 15px;
      }
    }
  </style>
</head>

<body>
  <!-- Sidebar -->
  <?= $this->include('layout/sidebarAdmin') ?>

  <div class="main-content">
    <!-- Header -->
    <div class="page-header">
      <h3><i class="bi bi-person-lines-fill"></i> Edit Akun User</h3>
      <a href="<?= base_url('manajemenakunuser'); ?>" class="btn btn-secondary btn-action" aria-label="Kembali ke halaman manajemen akun user">Kembali</a>
    </div>

    <!-- Container Form -->
    <div class="card-container">
      <?php if (session()->getFlashdata('success')): ?>
        <div class="alert alert-success" role="alert"><?= esc(session()->getFlashdata('success')) ?></div>
      <?php endif; ?>
      <?php if (session()->getFlashdata('error')): ?>
        <div class="alert alert-danger" role="alert"><?= esc(session()->getFlashdata('error')) ?></div>
      <?php endif; ?>

      <form action="<?= base_url('manajemenakunuser/update/'.($user['id_user'] ?? 0)) ?>" method="post" class="row g-4 needs-validation" novalidate>
        <?= csrf_field() ?>

        <div class="col-md-6">
          <label for="nama" class="form-label">Nama Lengkap</label>
          <input type="text" name="nama" id="nama" class="form-control" value="<?= esc($user['nama'] ?? '') ?>" required aria-required="true" />
          <div class="invalid-feedback">
            Mohon isi nama lengkap.
          </div>
        </div>

        <div class="col-md-6">
          <label for="email" class="form-label">Email</label>
          <input type="email" name="email" id="email" class="form-control" value="<?= esc($user['email'] ?? '') ?>" required aria-required="true" />
          <div class="invalid-feedback">
            Mohon masukkan email valid.
          </div>
        </div>

        <div class="col-md-6">
          <label for="no_hp" class="form-label">No. HP</label>
          <input type="text" name="no_hp" id="no_hp" class="form-control" value="<?= esc($user['no_hp'] ?? '') ?>" />
        </div>

        <div class="col-md-6">
          <label for="status" class="form-label">Status</label>
          <select name="status" id="status" class="form-select" required aria-required="true">
            <option value="Aktif" <?= (strtolower($user['status'] ?? '') === 'aktif') ? 'selected' : '' ?>>Aktif</option>
            <option value="Nonaktif" <?= (strtolower($user['status'] ?? '') === 'nonaktif') ? 'selected' : '' ?>>Nonaktif</option>
          </select>
          <div class="invalid-feedback">
            Mohon pilih status.
          </div>
        </div>

        <div class="col-12 d-flex justify-content-end gap-3 mt-4">
          <a href="<?= base_url('manajemenakunuser'); ?>" class="btn btn-secondary btn-action">Batal</a>
          <button type="submit" class="btn btn-primary btn-action">Simpan Perubahan</button>
        </div>
      </form>
    </div>
  </div>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
  <script>
    // Example starter JavaScript for disabling form submissions if there are invalid fields
    (() => {
      'use strict'

      const forms = document.querySelectorAll('.needs-validation')

      Array.from(forms).forEach(form => {
        form.addEventListener('submit', event => {
          if (!form.checkValidity()) {
            event.preventDefault()
            event.stopPropagation()
          }
          form.classList.add('was-validated')
        }, false)
      })
    })()
  </script>
</body>
</html>
