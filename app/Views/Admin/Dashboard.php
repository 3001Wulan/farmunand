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
        font-family: "Segoe UI", Tahoma, Geneva, Verdana, sans-serif;}
      .content {
        margin-left: 250px;
        padding: 30px;}
      .page-header {
        background: linear-gradient(135deg, #198754, #20c997);
        color: white;
        border-radius: 12px;
        padding: 18px 25px;
        margin-bottom: 25px;
        display: flex;
        align-items: center;
        gap: 10px;
        font-weight: 700;
        font-size: 1.3rem;}
      .card-dashboard {
        border-radius: 12px;
        box-shadow: 0 4px 12px rgba(0,0,0,0.08);
        text-align: center;
        padding: 25px 20px;
        background: white;
        transition: transform 0.2s, box-shadow 0.3s;}
      .card-dashboard:hover {
        transform: translateY(-5px);
        box-shadow: 0 6px 18px rgba(0,0,0,0.12);}
      .card-dashboard i {
        font-size: 40px;
        color: #198754;
        margin-bottom: 12px;}
      .card-dashboard h5 {
        font-weight: 600;
        color: #198754;
        margin-bottom: 8px;}
      .card-dashboard p {
        font-size: 1.25rem;
        font-weight: bold;
        margin: 0;
        color: #333;}
    </style>
  </head>

  <body>  
    <div class="container-fluid">
      <div class="row g-0">
        
        <!-- Sidebar -->
        <?= $this->include('layout/sidebarAdmin') ?>

        <!-- Content Dashboard -->
        <div class="col content">

          <!-- Header -->
          <div class="page-header">
            📊 <?= esc($title) ?>
          </div>

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
                  <p>Rp <?= number_format($penjualan_bulan, 0, ',', '.') ?></p>
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
