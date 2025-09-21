<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?= esc($title) ?> - Admin</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <style>
html, body {
  margin: 0;
  padding: 0;
  height: 100%;
  background: #f8f9fa;
}

.container-fluid {
  margin: 0;
  padding: 0;
}

.row.g-0 {
  margin: 0;
}

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

.content {
  padding: 30px;
}

.card-dashboard {
  border-radius: 10px;
  box-shadow: 0 2px 6px rgba(0,0,0,0.1);
  text-align: center;
  padding: 20px;
  background: white;
  transition: transform 0.2s;
}

.card-dashboard:hover {
  transform: scale(1.03);
  background: #eaf6ef;
}
  </style>
</head>
<body>
<div class="container-fluid">
  <div class="row g-0">
    
    <!-- Sidebar -->
    <div class="col-md-3 col-lg-2 sidebar">
      <div class="profile">Admin</div>
      <a href="#">Profil</a>
      <a href="#" class="active">Dashboard</a>
      <a href="#">Product</a>
      <a href="MengelolaRiwayatPesanan">Pesanan</a>
      <a href="manajemenakunuser">akunuser</a>
      <a href="melihatlaporan">Laporan</a>
      <a href="login">Log Out</a>
    </div>

    <!-- Content Dashboard -->
    <div class="col-md-9 col-lg-10 content">
      <h3 class="mb-4 text-success"><?= esc($title) ?></h3>

      <div class="row g-4">
        <div class="col-md-4">
          <div class="card-dashboard">
            <h5>Total Produk</h5>
            <p><?= esc($total_produk) ?></p>
          </div>
        </div>
        <div class="col-md-4">
          <div class="card-dashboard">
            <h5>Total User</h5>
            <p><?= esc($total_user) ?></p>
          </div>
        </div>
        <div class="col-md-4">
          <div class="card-dashboard">
            <h5>Transaksi Hari Ini</h5>
            <p><?= esc($transaksi_hari) ?></p>
          </div>
        </div>
        <div class="col-md-4">
          <div class="card-dashboard">
            <h5>Penjualan Bulan Ini</h5>
            <p>Rp <?= number_format($penjualan_bulan, 0, ',', '.') ?></p>
          </div>
        </div>
        <div class="col-md-4">
          <div class="card-dashboard">
            <h5>Stok Rendah</h5>
            <p><?= esc($stok_rendah) ?> Produk</p>
          </div>
        </div>
        <div class="col-md-4">
          <div class="card-dashboard">
            <h5>Pesan Masuk</h5>
            <p><?= esc($pesan_masuk) ?></p>
          </div>
        </div>
      </div>

    </div>

  </div>
</div>
</body>
</html>
