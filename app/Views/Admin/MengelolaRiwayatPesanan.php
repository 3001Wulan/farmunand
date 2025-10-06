<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Riwayat Pesanan - Admin</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
  <div class="container-fluid px-0">
    <div class="row g-0">
      <!-- Sidebar -->
      <?= $this->include('layout/sidebarAdmin') ?>

      <!-- Content -->
      <div class="col content">
        <div class="page-header">
          <h4>📜 Mengelola Riwayat Pesanan</h4>
        </div>

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
                <option value="Dikirim" <?= isset($status) && $status=="Dikirim" ? 'selected':'' ?>>Dikirim</option>
                <option value="Dikemas" <?= isset($status) && $status=="Dikemas" ? 'selected':'' ?>>Dikemas</option>
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
              </tr>
            </thead>

            <tbody>
              <?php if (!empty($pesanan)): ?>
                <?php foreach ($pesanan as $row): ?>
                  <tr>
                    <td><?= esc($row['id_pemesanan'] ?? '-') ?></td>
                    <td><?= esc($row['created_at'] ?? '-') ?></td>
                    <td><?= esc($row['nama_user'] ?? '-') ?></td>
                    <td><?= esc($row['nama_produk'] ?? '-') ?></td>
                    <td><?= esc($row['jumlah_produk'] ?? '-') ?></td>
                    <td>Rp <?= isset($row['harga']) ? number_format($row['harga'], 0, ',', '.') : '0' ?></td>
                    <td><?= esc($row['status_pemesanan'] ?? '-') ?></td>
                    <td>
                      <!-- Form untuk mengubah status -->
                      <form action="<?= site_url('mengeloririwayatpesanan/updateStatus/' . $row['id_pemesanan']) ?>" method="POST">
                        <select name="status_pemesanan" class="form-select" onchange="this.form.submit()">
                          <option value="Diproses" <?= $row['status_pemesanan'] == 'Diproses' ? 'selected' : '' ?>>Diproses</option>
                          <option value="Selesai" <?= $row['status_pemesanan'] == 'Selesai' ? 'selected' : '' ?>>Selesai</option>
                          <option value="Dibatalkan" <?= $row['status_pemesanan'] == 'Dibatalkan' ? 'selected' : '' ?>>Dibatalkan</option>
                          <option value="Dikirim" <?= $row['status_pemesanan'] == 'Dikirim' ? 'selected' : '' ?>>Dikirim</option>
                          <option value="Dikemas" <?= $row['status_pemesanan'] == 'Dikemas' ? 'selected' : '' ?>>Dikemas</option>
                        </select>
                      </form>
                    </td>
                  </tr>
                <?php endforeach; ?>
              <?php else: ?>
                <tr><td colspan="8" class="text-center">Belum ada data pesanan</td></tr>
              <?php endif; ?>
            </tbody>
          </table>
        </div>

      </div>
    </div>
  </div>
</body>
</html>