<!DOCTYPE html>
<html lang="id">
  <head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= esc($title) ?> - Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
    <style>
      html, body {
        margin: 0;
        padding: 0;
        height: 100%;
        background: #f8f9fa;
      }

      /* Konten geser agar tidak tertutup sidebar */
      .content {
        padding: 30px;
        margin-left: 250px; 
      }

      /* Judul Dashboard */
      .dashboard-title {
        font-weight: bold;
        color: #198754;
        margin-bottom: 30px;
      }

      /* Kartu ringkasan */
      .card-dashboard {
        border-radius: 15px;
        box-shadow: 0 4px 12px rgba(0,0,0,0.1);
        text-align: center;
        padding: 25px 20px;
        background: white;
        transition: transform 0.2s, background 0.3s;
      }

      .card-dashboard:hover {
        transform: scale(1.05);
        background: #eafaf0;
      }

      .card-dashboard h5 {
        font-weight: 600;
        color: #198754;
        margin-bottom: 10px;
      }

      .card-dashboard p {
        font-size: 20px;
        font-weight: bold;
        margin: 0;
      }

      /* Ikon dalam kartu */
      .card-dashboard i {
        font-size: 36px;
        color: #198754;
        margin-bottom: 10px;
      }
    </style>
  </head>

  <body>
    <div class="container-fluid">
      <div class="row g-0">
        
        <!-- Sidebar -->
        <?= $this->include('layout/sidebarAdmin') ?>

        <!-- Content Dashboard -->
        <div class="col content">
          <h3 class="dashboard-title">📊 <?= esc($title) ?></h3>

          <div class="row g-4">
            <div class="col-md-4">
              <div class="card-dashboard">
                <i class="bi bi-box-seam-fill"></i>
                <h5>Total Produk</h5>
                <p><?= esc($total_produk) ?></p>
              </div>
            </div>
            <div class="col-md-4">
              <div class="card-dashboard">
                <i class="bi bi-people-fill"></i>
                <h5>Total User</h5>
                <p><?= esc($total_user) ?></p>
              </div>
            </div>
            <div class="col-md-4">
              <div class="card-dashboard">
                <i class="bi bi-cart-check-fill"></i>
                <h5>Transaksi Hari Ini</h5>
                <p><?= esc($transaksi_hari) ?></p>
              </div>
            </div>
            <div class="col-md-4">
              <div class="card-dashboard">
                <i class="bi bi-cash-coin"></i>
                <h5>Penjualan Bulan Ini</h5>
                <p>Rp <?= number_format($penjualan_bulan * 1000, 0, ',', '.') ?></p>
              </div>
            </div>
            <div class="col-md-4">
              <div class="card-dashboard">
                <i class="bi bi-exclamation-triangle-fill"></i>
                <h5>Stok Rendah</h5>
                <p><?= esc($stok_rendah) ?> Produk</p>
              </div>
            </div>
            <div class="col-md-4">
              <div class="card-dashboard">
                <i class="bi bi-envelope-fill"></i>
                <h5>Pesan Masuk</h5>
                <p><?= esc($pesan_masuk) ?></p>
              </div>
            </div>
            <div class="col-md-4">
              <div class="card-dashboard">
                <i class="bi bi-bag-fill"></i>
                <h5>Total Pesanan</h5>
                <p><?= esc($total_pesanan) ?></p>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </body>
</html>
