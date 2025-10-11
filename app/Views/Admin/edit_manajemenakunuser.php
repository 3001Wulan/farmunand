<!DOCTYPE html>
<html lang="id">
  <head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Akun User - Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
      body {
        background: #f8f9fa;
        font-family: "Segoe UI", Tahoma, Geneva, Verdana, sans-serif;}
      .main-content {
        margin-left: 250px;
        padding: 30px;}
      .page-header {
        background: linear-gradient(135deg, #198754, #28a745);
        color: white;
        border-radius: 12px 12px 0 0;
        padding: 20px 30px;
        display: flex;
        justify-content: space-between;
        align-items: center;}
      .page-header h3 {
        margin: 0;
        font-weight: 700;
        font-size: 22px;
        display: flex;
        align-items: center;
        gap: 10px;}
      .card-container {
        background: #fff;
        border-radius: 0 0 12px 12px;
        box-shadow: 0 4px 12px rgba(0,0,0,0.08);
        padding: 20px;}
      label { font-weight: 500; }
      .btn-action {
        padding: 8px 18px;
        border-radius: 8px;
        font-size: 14px;
        font-weight: 500;
        transition: 0.2s;}
      .btn-primary.btn-action {
        background: #198754;
        border: none;}
      .btn-primary.btn-action:hover {
        background: #146c43;
        transform: translateY(-2px);}
      .btn-secondary.btn-action {
        background: #6c757d;
        border: none;}
      .btn-secondary.btn-action:hover {
        background: #565e64;
        transform: translateY(-2px);}
      .btn:focus-visible, a:focus-visible, input:focus-visible, select:focus-visible {
        outline: 3px solid #0d6efd;
        outline-offset: 2px;}
    </style>
  </head>

  <body>
    <!-- Sidebar -->
    <?= $this->include('layout/sidebarAdmin') ?>

    <div class="main-content">
      <!-- Header -->
      <div class="page-header">
        <h3><i class="bi bi-person-lines-fill"></i> Edit Akun User</h3>
        <a href="<?= base_url('manajemenakunuser'); ?>" class="btn btn-secondary btn-action">Kembali</a>
      </div>

      <!-- Container -->
      <div class="card-container">
        <?php if (session()->getFlashdata('success')): ?>
          <div class="alert alert-success"><?= esc(session()->getFlashdata('success')) ?></div>
        <?php endif; ?>
        <?php if (session()->getFlashdata('error')): ?>
          <div class="alert alert-danger"><?= esc(session()->getFlashdata('error')) ?></div>
        <?php endif; ?>

        <form action="<?= base_url('manajemenakunuser/update/'.($user['id_user'] ?? 0)) ?>" method="post" class="row g-3">
          <?= csrf_field() ?>
          <div class="col-md-6">
            <label for="nama" class="form-label">Nama Lengkap</label>
            <input type="text" name="nama" id="nama" class="form-control"
                  value="<?= esc($user['nama'] ?? '') ?>" required>
          </div>

          <div class="col-md-6">
            <label for="email" class="form-label">Email</label>
            <input type="email" name="email" id="email" class="form-control"
                  value="<?= esc($user['email'] ?? '') ?>" required>
          </div>

          <div class="col-md-6">
            <label for="no_hp" class="form-label">No. HP</label>
            <input type="text" name="no_hp" id="no_hp" class="form-control"
                  value="<?= esc($user['no_hp'] ?? '') ?>">
          </div>

          <div class="col-md-6">
            <label for="status" class="form-label">Status</label>
            <select name="status" id="status" class="form-select">
              <option value="Aktif"    <?= (strtolower($user['status'] ?? '')==='aktif')?'selected':'' ?>>Aktif</option>
              <option value="Nonaktif" <?= (strtolower($user['status'] ?? '')==='nonaktif')?'selected':'' ?>>Nonaktif</option>
              <option value="Suspend"  <?= (strtolower($user['status'] ?? '')==='suspend')?'selected':'' ?>>Suspend</option>
            </select>
          </div>

          <div class="col-12 d-flex justify-content-end gap-2 mt-3">
            <a href="<?= base_url('manajemenakunuser'); ?>"
              class="btn btn-secondary btn-action">Batal</a>
            <button type="submit"
                    class="btn btn-primary btn-action">Simpan Perubahan</button>
          </div>
        </form>
      </div>
    </div>
  </body>
</html>
