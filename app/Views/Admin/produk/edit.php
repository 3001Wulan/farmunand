<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Edit Produk - Admin</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">

  <style>
    :root {
      --brand: #198754;
      --brand-dark: #157347;
      --muted-bg: #f8f9fa;
    }

    html, body {
      height: 100%;
      margin: 0;
      background: var(--muted-bg);
      font-family: Inter, system-ui, -apple-system, "Segoe UI", Roboto, "Helvetica Neue", Arial;
    }

    .main-content {
      margin-left: 250px;
      padding: 30px;
      min-height: 100vh;
    }

    .card.form-card {
      border-radius: 12px;
      box-shadow: 0 8px 24px rgba(6, 50, 20, 0.06);
      overflow: hidden;
    }

    .form-header {
      background: linear-gradient(90deg, var(--brand), #20c997);
      color: #fff;
      padding: 18px 22px;
      display: flex;
      align-items: center;
      gap: 12px;
      font-weight: 600;
      font-size: 1.15rem;
    }

    .form-table th {
      width: 28%;
      background: #f6fff8;
      color: var(--brand-dark);
      font-weight: 600;
      vertical-align: middle;
      border-top: 0;
      padding: 18px;
    }
    .form-table td {
      background: #fff;
      vertical-align: middle;
      padding: 12px 18px;
      border-top: 0;
    }
    .form-table input.form-control,
    .form-table textarea.form-control,
    .form-table .form-select {
      border-radius: 8px;
      border: 1px solid #e6efe9;
      padding: 10px 12px;
      box-shadow: none;
    }

    .img-preview {
      width: 140px;
      height: 100px;
      object-fit: cover;
      border-radius: 8px;
      border: 1px solid #e9f4ee;
    }

    .btn-save {
      background: var(--brand);
      border: 0;
      box-shadow: 0 6px 18px rgba(25,135,84,0.12);
    }
    .btn-save:hover { background: var(--brand-dark); transform: translateY(-2px); }

    @media (max-width: 992px) {
      .main-content { margin-left: 0; padding: 18px; }
    }
  </style>
</head>
<body>
  <!-- Sidebar -->
  <?= $this->include('layout/sidebarAdmin') ?>

  <!-- Konten Utama -->
  <div class="main-content">
    <div class="card form-card">
      <div class="form-header">✏️ Edit Produk</div>
      <div class="p-4">
        <form action="<?= base_url('admin/produk/update/'.$produk['id_produk']) ?>" method="post" enctype="multipart/form-data">
          <table class="table form-table align-middle">
            <tr>
              <th>Nama Produk</th>
              <td>
                <input type="text" name="nama_produk" class="form-control" value="<?= esc($produk['nama_produk']) ?>" required>
              </td>
            </tr>
            <tr>
              <th>Deskripsi</th>
              <td>
                <textarea name="deskripsi" class="form-control" rows="4" required><?= esc($produk['deskripsi']) ?></textarea>
              </td>
            </tr>
            <tr>
              <th>Foto</th>
              <td>
                <img src="<?= base_url('uploads/produk/'.$produk['foto']) ?>" class="img-preview mb-2">
                <input type="file" name="foto" class="form-control mt-2">
              </td>
            </tr>
            <tr>
              <th>Harga</th>
              <td>
                <input type="number" name="harga" class="form-control" value="<?= esc($produk['harga']) ?>" required>
              </td>
            </tr>
            <tr>
              <th>Stok</th>
              <td>
                <input type="number" name="stok" class="form-control" value="<?= esc($produk['stok']) ?>" required>
              </td>
            </tr>
          </table>

          <!-- Tombol Aksi -->
          <div class="mt-3">
            <button type="submit" class="btn btn-save px-4">💾 Update</button>
            <a href="<?= base_url('admin/produk') ?>" class="btn btn-secondary px-4">↩ Kembali</a>
          </div>
        </form>
      </div>
    </div>
  </div>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
