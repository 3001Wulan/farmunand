<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?= esc($title) ?> - Admin</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">

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
      background: linear-gradient(180deg, #145c32, #198754, #28a745);
      color: white;
      border-radius: 12px 12px 0 0;
      padding: 18px 25px;
      display: flex;
      justify-content: space-between;
      align-items: center;
    }
    .page-header h3 {
      margin: 0;
      font-weight: 700;
      font-size: 1.25rem;
      display: flex;
      align-items: center;
      gap: 8px;
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
      text-align: center;
      border: none;
    }
    .table td, .table th {
      vertical-align: middle;
      text-align: center;
    }
    .table tbody tr:hover {
      background: #f1fdf6;
    }

    .table img {
      border-radius: 8px;
      border: 2px solid #e9ecef;
      padding: 2px;
      transition: 0.3s;
    }
    .table img:hover {
      transform: scale(1.08);
    }

    /* Tombol */
    .btn-success {
      background: #198754;
      border: none;
    }
    .btn-success:hover { background: #157347; transform: translateY(-2px); }
    .btn-warning:hover, .btn-danger:hover { transform: translateY(-2px); }

    .badge { border-radius: 8px; padding: 6px 12px; }
  </style>
</head>
<body>

  <!-- Sidebar -->
  <?= $this->include('layout/sidebarAdmin') ?>

  <!-- Konten -->
  <div class="col content">

    <!-- Header hijau -->
    <div class="page-header">
      <h3>📦 <?= esc($title) ?></h3>
      <a href="<?= base_url('admin/produk/create') ?>" class="btn btn-light btn-sm fw-semibold">➕ Tambah Produk</a>
    </div>

    <!-- Card Isi -->
    <div class="card-container">

      <!-- Notifikasi -->
      <?php if (session()->getFlashdata('success')): ?>
        <div class="alert alert-success mb-3">
          ✅ <?= session()->getFlashdata('success') ?>
        </div>
      <?php endif; ?>

      <!-- Pencarian -->
      <form method="get" action="<?= base_url('admin/produk') ?>" class="mb-3 d-flex">
        <input type="text" name="keyword" value="<?= esc($keyword) ?>"
               class="form-control me-2" placeholder="🔍 Cari produk...">
        <button type="submit" class="btn btn-success">Cari</button>
      </form>

      <!-- Tabel Produk -->
      <div class="table-responsive">
        <table class="table table-hover align-middle">
          <thead>
            <tr>
              <th>No</th>
              <th>Foto</th>
              <th>Nama Produk</th>
              <th>Harga</th>
              <th>Stok</th>
              <th>Aksi</th>
            </tr>
          </thead>
          <tbody>
            <?php if (!empty($produk)): ?>
              <?php $no = 1; foreach ($produk as $p): ?>
                <tr>
                  <td><?= $no++ ?></td>
                  <td><img src="<?= base_url('uploads/produk/'.$p['foto']) ?>" width="60"></td>
                  <td><?= esc($p['nama_produk']) ?></td>
                  <td><span class="badge bg-success">Rp <?= number_format($p['harga'], 0, ',', '.') ?></span></td>
                  <td><span class="badge bg-secondary"><?= esc($p['stok']) ?></span></td>
                  <td>
                    <a href="<?= base_url('admin/produk/edit/'.$p['id_produk']) ?>" class="btn btn-sm btn-warning">Edit</a>
                    <button type="button" class="btn btn-sm btn-danger" data-bs-toggle="modal"
                            data-bs-target="#deleteModal<?= $p['id_produk'] ?>">Hapus</button>

                    <!-- Modal Konfirmasi -->
                    <div class="modal fade" id="deleteModal<?= $p['id_produk'] ?>" tabindex="-1">
                      <div class="modal-dialog modal-dialog-centered">
                        <div class="modal-content border-0 shadow">
                          <div class="modal-header bg-success text-white">
                            <h5 class="modal-title">Konfirmasi Hapus</h5>
                            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                          </div>
                          <div class="modal-body text-center">
                            <p>Apakah yakin ingin menghapus <b><?= esc($p['nama_produk']) ?></b>?</p>
                          </div>
                          <div class="modal-footer d-flex justify-content-center">
                            <a href="<?= base_url('admin/produk/delete/'.$p['id_produk']) ?>" class="btn btn-danger px-4">Ya, Hapus</a>
                            <button type="button" class="btn btn-secondary px-4" data-bs-dismiss="modal">Batal</button>
                          </div>
                        </div>
                      </div>
                    </div>
                  </td>
                </tr>
              <?php endforeach; ?>
            <?php else: ?>
              <tr><td colspan="6" class="text-center text-muted">⚠️ Tidak ada produk ditemukan.</td></tr>
            <?php endif; ?>
          </tbody>
        </table>
      </div>

    </div>
  </div>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
