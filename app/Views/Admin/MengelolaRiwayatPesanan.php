<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Riwayat Pesanan - Admin</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <style>
html, body {
  margin: 0;
  padding: 0;
  height: 100%;
  background: #f8f9fa;
}

.container-fluid { margin: 0; padding: 0; }
.row.g-0 { margin: 0; }

/* Sidebar */
.sidebar {
  min-height: 100vh;
  background: #198754;
  padding: 20px;
  margin: 0;
  color: white;
}
.sidebar .profile {
  width: 120px;
  height: 120px;
  border-radius: 50%;
  background: white;
  margin: 0 auto 20px auto;
  display: flex;
  align-items: center;
  justify-content: center;
  font-weight: bold;
  color: #198754;
  font-size: 18px;
}
.sidebar a {
  display: block;
  padding: 10px;
  margin: 10px 0;
  background: white;
  color: #198754;
  text-decoration: none;
  border-radius: 5px;
  font-weight: 500;
  text-align: center;
  transition: all 0.3s;
}
.sidebar a:hover,
.sidebar a.active {
  background: #145c32;
  color: white;
}
.content { padding: 30px; }
table th { background: #198754; color: white; }
  </style>
</head>
<body>
<div class="container-fluid">
  <div class="row g-0">
    
  <!-- Sidebar dari layouts -->
  <?= $this->include('layout/sidebarAdmin') ?>

    <!-- Content -->
    <div class="col-md-9 col-lg-10 content">
      <div class="d-flex justify-content-between align-items-center mb-3">
        <h3 class="text-success">Mengelola Riwayat Pesanan</h3>
        <button class="btn btn-success btn-sm">Export Data</button>
      </div>

      <!-- Filter & Search -->
      <form method="get" class="row mb-3">
        <div class="col-md-6">
          <input type="text" class="form-control" name="keyword" placeholder="Cari Pesanan..." value="<?= esc($keyword ?? '') ?>">
        </div>
        <div class="col-md-3">
          <select name="status" class="form-select">
            <option value="">Semua Status</option>
            <option value="Diproses" <?= isset($status) && $status=="Diproses" ? 'selected':'' ?>>Diproses</option>
            <option value="Selesai" <?= isset($status) && $status=="Selesai" ? 'selected':'' ?>>Selesai</option>
            <option value="Dibatalkan" <?= isset($status) && $status=="Dibatalkan" ? 'selected':'' ?>>Dibatalkan</option>
          </select>
        </div>
        <div class="col-md-3">
          <select name="sort" class="form-select">
            <option value="desc" <?= isset($sort) && $sort=="desc" ? 'selected':'' ?>>Terbaru</option>
            <option value="asc" <?= isset($sort) && $sort=="asc" ? 'selected':'' ?>>Terlama</option>
          </select>
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
                <td><?= esc($row['id_user']) ?></td>
                <td><?= esc($row['tanggal']) ?></td>
                <td><?= esc($pesanan['nama_user'] ?? $pesanan['nama'] ?? '-') ?></td>
<td><?= esc($pesanan['nama_produk'] ?? '-') ?></td>


                <td><?= esc($row['quantity']) ?></td>
                <td>Rp <?= number_format($row['total'], 0, ',', '.') ?></td>
                <td><?= esc($row['pembayaran']) ?></td>
                <td>
                  <?php if ($row['status'] === 'Selesai'): ?>
                    <span class="badge bg-success">Selesai</span>
                  <?php elseif ($row['status'] === 'Diproses'): ?>
                    <span class="badge bg-warning text-dark">Diproses</span>
                  <?php else: ?>
                    <span class="badge bg-danger">Dibatalkan</span>
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
</body>
</html>
