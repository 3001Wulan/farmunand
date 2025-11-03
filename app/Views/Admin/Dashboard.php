<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title><?= esc($title) ?> - Admin</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet" />
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet" />
  <style>
    body {
      background: linear-gradient(135deg, #e6f4ea, #c0e0cc);
      font-family: "Segoe UI", Tahoma, Geneva, Verdana, sans-serif;
      min-height: 100vh;
      margin: 0;
    }
    .content {
      margin-left: 250px;
      padding: 30px 40px;
    }
    .page-header {
      background: linear-gradient(135deg, #28a745, #198754);
      color: white;
      border-radius: 12px;
      padding: 20px 30px;
      margin-bottom: 30px;
      display: flex;
      align-items: center;
      gap: 15px;
      font-weight: 700;
      font-size: 1.5rem;
      box-shadow: 0 6px 18px rgba(25, 135, 84, 0.45);
      user-select: none;
    }
    .row.g-4 {
      display: grid;
      grid-template-columns: repeat(auto-fill,minmax(280px,1fr));
      gap: 1.5rem;
    }
    .card-dashboard {
      border-radius: 16px;
      box-shadow: 0 6px 25px rgba(0,0,0,0.1);
      background: white;
      text-align: center;
      padding: 36px 24px 40px;
      transition: transform 0.3s ease, box-shadow 0.4s ease;
      cursor: default;
      user-select: none;
    }
    .card-dashboard:hover {
      transform: translateY(-10px) scale(1.03);
      box-shadow: 0 12px 45px rgba(0,0,0,0.18);
    }
    .card-dashboard i {
      font-size: 52px;
      color: #198754;
      margin-bottom: 18px;
      filter: drop-shadow(0px 2px 2px rgba(25,135,84,0.45));
    }
    .card-dashboard h5 {
      font-weight: 700;
      color: #198754;
      margin-bottom: 10px;
      letter-spacing: 0.02em;
    }
    .card-dashboard p {
      font-size: 1.5rem;
      font-weight: 700;
      margin: 0;
      color: #333;
      letter-spacing: 0.015em;
    }
    @media (max-width: 768px) {
      .content {
        margin-left: 0;
        padding: 20px;
      }
      .page-header {
        font-size: 1.3rem;
        justify-content: center;
      }
    }
  </style>
</head>
<body>
  <div class="container-fluid">
    <div class="row g-0">

      <?= $this->include('layout/sidebarAdmin') ?>

      <div class="col content">
        <div class="page-header">
          <i class="bi bi-bar-chart-line-fill"></i>
          <?= esc($title) ?>
        </div>

        <div class="row g-4">
          <div><div class="card-dashboard" tabindex="0" aria-label="Total Produk"><i class="bi bi-box-seam-fill"></i><h5>Total Produk</h5><p><?= esc($total_produk) ?></p></div></div>
          <div><div class="card-dashboard" tabindex="0" aria-label="Total User"><i class="bi bi-people-fill"></i><h5>Total User</h5><p><?= esc($total_user) ?></p></div></div>
          <div><div class="card-dashboard" tabindex="0" aria-label="Transaksi Hari Ini"><i class="bi bi-cart-check-fill"></i><h5>Transaksi Hari Ini</h5><p><?= esc($transaksi_hari) ?></p></div></div>
          <div><div class="card-dashboard" tabindex="0" aria-label="Penjualan Bulan Ini"><i class="bi bi-cash-coin"></i><h5>Penjualan Bulan Ini</h5><p>Rp <?= number_format($penjualan_bulan, 0, ',', '.') ?></p></div></div>
          <div><div class="card-dashboard" tabindex="0" aria-label="Stok Rendah"><i class="bi bi-exclamation-triangle-fill"></i><h5>Stok Rendah</h5><p><?= esc($stok_rendah) ?> Produk</p></div></div>
          <div><div class="card-dashboard" tabindex="0" aria-label="Total Pesanan"><i class="bi bi-bag-fill"></i><h5>Total Pesanan</h5><p><?= esc($total_pesanan) ?></p></div></div>
        </div>
      </div>

    </div>
  </div>
</body>
</html>
