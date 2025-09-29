<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Riwayat Pesanan - Admin</title>
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

    /* Header Hijau */
    .page-header {
      background: linear-gradient(135deg, #198754, #28a745);
      color: white;
      border-radius: 12px 12px 0 0;
      padding: 20px 30px;
      display: flex;
      justify-content: space-between;
      align-items: center;
    }
    .page-header h4 {
      margin: 0;
      font-weight: 700;
      display: flex;
      align-items: center;
      gap: 10px;
    }

    /* Card Isi */
    .card-container {
      background: #fff;
      border-radius: 0 0 12px 12px;
      box-shadow: 0 4px 12px rgba(0,0,0,0.08);
      padding: 20px;
    }

    /* Filter */
    .filter-form .form-control,
    .filter-form .form-select {
      border-radius: 8px;
    }

    /* Table */
    .table thead th {
      background: #198754;
      color: #fff;
      text-align: center;
      border: none;
    }
    .table td, .table th {
      text-align: center;
      vertical-align: middle;
      padding: 12px;
    }
    .table tbody tr:hover {
      background: #f1fdf6;
    }

    /* Tombol */
    .btn-success {
      background: #198754;
      border: none;
    }
    .btn-success:hover {
      background: #157347;
      transform: translateY(-2px);
    }
    .btn-info {
      background: #0dcaf0;
      border: none;
      color: #fff;
    }
    .btn-info:hover {
      background: #0bb5d8;
      transform: translateY(-2px);
    }

    /* Badge Status */
    .badge {
      font-size: 0.85rem;
      padding: 6px 10px;
      border-radius: 12px;
    }
    .badge.bg-success { background: #198754 !important; }
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
          <h4>📜 Mengelola Riwayat Pesanan</h4>
          <a href="<?= site_url('pesanan/export') ?>" class="btn btn-light btn-sm fw-semibold">
            ⬇️ Export Data
          </a>
        </div>

        <!-- Card Isi -->
        <div class="card-container">

          <!-- Filter & Search -->
          <form method="get" class="row mb-4 filter-form">
            <div class="col-md-5 mb-2">
              <input type="text" class="form-control" name="keyword" placeholder="Cari Pesanan..."
                value="<?= esc($keyword ?? '') ?>">
            </div>
            <div class="col-md-3 mb-2">
              <select name="status" class="form-select">
                <option value="">Semua Status</option>
                <option value="Diproses" <?= isset($status) && $status=="Diproses" ? 'selected':'' ?>>Diproses</option>
                <option value="Selesai" <?= isset($status) && $status=="Selesai" ? 'selected':'' ?>>Selesai</option>
                <option value="Dibatalkan" <?= isset($status) && $status=="Dibatalkan" ? 'selected':'' ?>>Dibatalkan</option>
              </select>
            </div>
            <div class="col-md-2 mb-2">
              <select name="sort" class="form-select">
                <option value="desc" <?= isset($sort) && $sort=="desc" ? 'selected':'' ?>>Terbaru</option>
                <option value="asc" <?= isset($sort) && $sort=="asc" ? 'selected':'' ?>>Terlama</option>
              </select>
            </div>
            <div class="col-md-2 mb-2">
              <button type="submit" class="btn btn-success w-100">Filter</button>
            </div>
          </form>

          <!-- Tabel Riwayat Pesanan -->
          <table class="table table-bordered table-hover">
            <thead>
              <tr>
                <th>ID</th>
                <th>Tanggal</th>
                <th>Nama</th>
                <th>Pesanan</th>
                <th>Quantity</th>
                <th>Total</th>
                <th>Pembayaran</th>
                <th>Status</th>
                <th>Aksi</th>
              </tr>
            </thead>

            <tbody>
              <?php if (!empty($pesanan)): ?>
                <?php foreach ($pesanan as $row): ?>
                  <tr>
                    <td><?= esc($row['id_user'] ?? '-') ?></td>
                    <td><?= esc($row['tanggal'] ?? '-') ?></td>
                    <td><?= esc($row['nama_user'] ?? $row['nama'] ?? '-') ?></td>
                    <td><?= esc($row['nama_produk'] ?? '-') ?></td>
                    <td><?= esc($row['quantity'] ?? '-') ?></td>
                    <td>Rp <?= isset($row['total']) ? number_format($row['total'], 0, ',', '.') : '0' ?></td>
                    <td><?= esc($row['pembayaran'] ?? '-') ?></td>

                    <td>
                      <?php if (($row['status_pemesanan'] ?? '') === 'Selesai'): ?>
                        <span class="badge bg-success">Selesai</span>
                      <?php elseif (($row['status_pemesanan'] ?? '') === 'Diproses'): ?>
                        <span class="badge bg-warning text-dark">Diproses</span>
                      <?php elseif (($row['status_pemesanan'] ?? '') === 'Dibatalkan'): ?>
                        <span class="badge bg-danger">Dibatalkan</span>
                      <?php else: ?>
                        <span class="badge bg-secondary">Tidak Diketahui</span>
                      <?php endif; ?>
                    </td>

                    <td>
                      <a href="<?= base_url('pesanan/detail/'.$row['id_user']) ?>" class="btn btn-sm btn-info">Detail</a>
                    </td>
                  </tr>
                <?php endforeach; ?>
              <?php else: ?>
                <tr><td colspan="9" class="text-center">Belum ada data pesanan</td></tr>
              <?php endif; ?>
            </tbody>
          </table>
        </div>

        </div>
      </div>
    </div>
  </div>
</body>
</html>
