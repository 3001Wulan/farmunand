<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Laporan Penjualan</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
<style>
html, body {
  margin: 0;
  padding: 0;
  height: 100%;
  background: #f8f9fa;
}

/* Sidebar fixed */
.sidebar {
  position: fixed;
  top: 0;
  left: 0;
  width: 220px;
  height: 100vh;
  background: #198754;
  padding: 20px;
  color: white;
  overflow-y: auto;
  z-index: 1000;
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

/* Content margin kiri sesuai sidebar */
.content {
  margin-left: 240px;
  padding: 30px;
}

/* Tabel laporan */
.table thead {
  background: #198754;
  color: white;
}

.status-success { color: #198754; font-weight: bold; }
.status-pending { color: #ffc107; font-weight: bold; }
.status-cancel  { color: #dc3545; font-weight: bold; }
</style>
</head>
<body>
<!-- Sidebar -->
<div class="sidebar">
  <div class="profile">Admin</div>
  <a href="#">Profil</a>
      <a href="#" class="active">Dashboard</a>
      <a href="#">Product</a>
      <a href="mengelolariwayatpesanan">Pesanan</a>
      <a href="manajemenakunuser">akunuser</a>
      <a href="melihatlaporan">Laporan</a>
      <a href="login">Log Out</a>
</div>

<!-- Content -->
<div class="content">
  <h3 class="mb-4 text-success">Laporan Penjualan</h3>

  <!-- Filter -->
  <div class="card mb-3">
    <div class="card-body">
      <form class="row g-3" onsubmit="return false;">
        <div class="col-md-4">
          <label for="startDate" class="form-label">Dari Tanggal</label>
          <input type="date" id="startDate" class="form-control">
        </div>
        <div class="col-md-4">
          <label for="endDate" class="form-label">Sampai Tanggal</label>
          <input type="date" id="endDate" class="form-control">
        </div>
        <div class="col-md-4 d-flex align-items-end">
          <button type="button" id="filterBtn" class="btn btn-success w-100">Filter</button>
        </div>
      </form>
    </div>
  </div>

  <!-- Tabel Laporan -->
  <div class="card">
    <div class="card-body">
      <table class="table table-bordered align-middle">
        <thead>
          <tr>
            <th>No</th>
            <th>Nama Pembeli</th>
            <th>Produk</th>
            <th>Tanggal</th>
            <th>Total</th>
            <th>Status</th>
          </tr>
        </thead>
        <tbody id="laporanTable">
          <?php if (!empty($laporan)): ?>
            <?php $no = 1; foreach ($laporan as $row): ?>
              <tr>
                <td><?= $no++; ?></td>
                <td><?= esc($row['nama_pembeli'] ?? 'Tidak ada'); ?></td>
                <td><?= esc($row['nama_produk'] ?? '-'); ?></td>
                <td><?= date('d-m-Y', strtotime($row['created_at'] ?? date('Y-m-d'))); ?></td>
                <td>Rp <?= number_format($row['harga_produk'] * ($row['jumlah_produk'] ?? 1), 0, ',', '.'); ?></td>
                <td class="<?php 
                      if ($row['status_pemesanan'] == 'sukses') echo 'status-success'; 
                      elseif ($row['status_pemesanan'] == 'pending') echo 'status-pending'; 
                      else echo 'status-cancel'; ?>">
                  <?= ucfirst($row['status_pemesanan']); ?>
                </td>
              </tr>
            <?php endforeach; ?>
          <?php else: ?>
            <tr>
              <td colspan="6" class="text-center text-muted">Belum ada data laporan</td>
            </tr>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>

<!-- Script Filter -->
<script>
document.getElementById("filterBtn").addEventListener("click", function() {
  const startDate = document.getElementById("startDate").value;
  const endDate = document.getElementById("endDate").value;

  const rows = document.querySelectorAll("#laporanTable tr");

  rows.forEach(row => {
    const tanggalCell = row.cells[3];
    if (!tanggalCell) return;

    const tanggalText = tanggalCell.textContent.trim(); // format dd-mm-yyyy
    const parts = tanggalText.split("-");
    const formattedDate = `${parts[2]}-${parts[1]}-${parts[0]}`; // yyyy-mm-dd
    const rowDate = new Date(formattedDate);

    let show = true;
    if (startDate && rowDate < new Date(startDate)) show = false;
    if (endDate && rowDate > new Date(endDate)) show = false;

    row.style.display = show ? "" : "none";
  });
});
</script>
</body>
</html>
