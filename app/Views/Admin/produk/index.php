<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?= esc($title) ?> - Admin</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">

  <style>
  /* 🌿 Global */
  body {
    background-color: #f1f3f6;
    font-family: "Segoe UI", Tahoma, Geneva, Verdana, sans-serif;
    margin: 0;
    padding: 0;
  }

  /* 🌿 Sidebar fix */
  .sidebar {
    position: fixed;
    top: 0;
    left: 0;
    width: 250px;
    height: 100%;
    background: #198754;
    color: white;
    padding-top: 20px;
    box-shadow: 2px 0 10px rgba(0,0,0,0.1);
    z-index: 1000;
  }

  .sidebar h4 {
    font-weight: bold;
    text-align: center;
    margin-bottom: 30px;
  }

  .sidebar a {
    display: block;
    color: white;
    padding: 12px 20px;
    text-decoration: none;
    transition: background 0.3s;
  }

  .sidebar a:hover,
  .sidebar a.active {
    background: #157347;
    border-radius: 8px;
  }

  /* 🌿 Konten utama */
  .content {
    margin-left: 250px; /* Biar tidak ketiban sidebar */
    padding: 30px;
    animation: fadeIn 0.6s ease-in-out;
  }

  .dashboard-title {
    color: #198754;
    font-weight: bold;
    margin-bottom: 20px;
    font-size: 1.6rem;
    display: flex;
    align-items: center;
    gap: 8px;
  }

  .dashboard-title::before {
    content: "📦";
    font-size: 1.4rem;
  }

  /* 🌿 Tabel & tombol */
  .table th {
    text-align: center;
    vertical-align: middle;
  }

  .table td {
    vertical-align: middle;
  }

  .btn-success {
    background-color: #198754;
    border: none;
    transition: all 0.3s ease;
  }

  .btn-success:hover {
    background-color: #157347;
    transform: translateY(-2px);
    box-shadow: 0 4px 8px rgba(25,135,84,0.3);
  }

  .btn-warning:hover,
  .btn-danger:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 8px rgba(0,0,0,0.2);
  }

  .table img {
    border: 2px solid #e9ecef;
    padding: 3px;
    border-radius: 10px;
    transition: 0.3s;
  }

  .table img:hover {
    transform: scale(1.1);
  }

  .table thead {
    background: linear-gradient(90deg, #198754, #20c997);
    color: white;
  }

  .table tbody tr:hover {
    background-color: #f1fdf5;
    transition: 0.2s;
  }

  .alert {
    border-radius: 10px;
    animation: slideDown 0.6s ease-in-out;
  }

  /* 🌿 Animasi */
  @keyframes fadeIn {
    from {opacity: 0; transform: translateY(10px);}
    to {opacity: 1; transform: translateY(0);}
  }

  @keyframes slideDown {
    from {opacity: 0; transform: translateY(-20px);}
    to {opacity: 1; transform: translateY(0);}
  }
  </style>
</head>
<body>

  <!-- Sidebar -->
  <?= $this->include('layout/sidebarAdmin') ?>

  <!-- Konten Produk -->
  <div class="content">
    <!-- Judul Halaman -->
    <h3 class="dashboard-title"><?= esc($title) ?></h3>

    <!-- Notifikasi Flash Message -->
    <?php if (session()->getFlashdata('success')): ?>
      <div class="alert alert-success shadow-sm">
        ✅ <?= session()->getFlashdata('success') ?>
      </div>
    <?php endif; ?>

    <!-- Form Pencarian Produk -->
    <form method="get" action="<?= base_url('admin/produk') ?>" class="mb-3 d-flex">
      <input type="text" name="keyword" value="<?= esc($keyword) ?>" 
             class="form-control me-2" placeholder="🔍 Cari produk...">
      <button type="submit" class="btn btn-success">Cari</button>
    </form>

    <!-- Tombol Tambah Produk -->
    <a href="<?= base_url('admin/produk/create') ?>" class="btn btn-success mb-3">➕ Tambah Produk</a>

    <!-- Tabel Produk -->
    <div class="table-responsive">
      <table class="table table-bordered table-striped align-middle">
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
                <td class="text-center"><?= $no++ ?></td>
                <td class="text-center">
                  <img src="<?= base_url('uploads/produk/'.$p['foto']) ?>" width="60" class="rounded">
                </td>
                <td><?= esc($p['nama_produk']) ?></td>
                <td class="text-center">
                <span class="badge bg-success">
                    Rp <?= number_format($p['harga'], 0, ',', '.') ?>
                </span>
                </td>

                <td class="text-center">
                <span class="badge bg-secondary">
                    <?= esc($p['stok']) ?>
                </span>
                </td>

                <td class="text-center">
                <!-- Tombol Edit -->
                <a href="<?= base_url('admin/produk/edit/'.$p['id_produk']) ?>" 
                    class="btn btn-sm btn-warning">✏️ Edit</a>

                <!-- Tombol Hapus (Trigger Modal) -->
                <button type="button" class="btn btn-sm btn-danger" 
                        data-bs-toggle="modal" data-bs-target="#deleteModal<?= $p['id_produk'] ?>">
                    🗑 Hapus
                </button>

                <!-- Modal Konfirmasi Hapus -->
                <div class="modal fade" id="deleteModal<?= $p['id_produk'] ?>" tabindex="-1">
                    <div class="modal-dialog modal-dialog-centered">
                    <div class="modal-content border-0 shadow">
                        <div class="modal-header" style="background: linear-gradient(90deg, #198754, #20c997); color: #fff;">
                        <h5 class="modal-title">Konfirmasi Hapus</h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                        </div>
                        <div class="modal-body text-center">
                        <p class="mb-3">Apakah kamu yakin ingin menghapus produk <b><?= esc($p['nama_produk']) ?></b>?</p>
                        <span class="badge bg-success">Rp <?= number_format($p['harga'], 0, ',', '.') ?></span>
                        <span class="badge bg-secondary"><?= esc($p['stok']) ?> Stok</span>
                        </div>
                        <div class="modal-footer d-flex justify-content-center">
                        <a href="<?= base_url('admin/produk/delete/'.$p['id_produk']) ?>" 
                            class="btn btn-danger px-4">Ya, Hapus</a>
                        <button type="button" class="btn btn-secondary px-4" data-bs-dismiss="modal">Batal</button>
                        </div>
                    </div>
                    </div>
                </div>
                </td>

              </tr>
            <?php endforeach; ?>
          <?php else: ?>
            <tr>
              <td colspan="6" class="text-center text-muted">⚠️ Tidak ada produk ditemukan.</td>
            </tr>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>

</body>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</html>
