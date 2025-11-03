<!DOCTYPE html>
<html lang="id">
  <head>
  <meta charset="UTF-8"/>
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Pesanan Saya - FarmUnand</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
      body {
        background: linear-gradient(135deg, #e6f4ea, #c0e0cc);
        font-family: "Segoe UI", Tahoma, Geneva, Verdana, sans-serif;
        min-height: 100vh;
        margin: 0;
      }
      .content{margin-left:250px; padding:30px;}
      .page-header{
        background:linear-gradient(135deg,#198754,#28a745);
        color:#fff; border-radius:12px; padding:18px 20px;
        display:flex; align-items:center; justify-content:space-between;
        box-shadow:0 6px 14px rgba(0,0,0,.08); margin-bottom:16px;}
      .page-header h5{margin:0; font-weight:700}
      .card-container{
        background:#fff; border-radius:12px; box-shadow:0 6px 18px rgba(0,0,0,.06);
        padding:18px;}
      .tabs-wrap{gap:8px;}
      .btn-filter{border-radius:999px; font-weight:500; padding:6px 14px;}
      .order-img img{
        width:80px; height:80px; object-fit:cover;
        border-radius:8px; border:2px solid #dee2e6; background:#e9ecef;}
      .order-card{border:none; border-radius:12px; box-shadow:0 4px 10px rgba(0,0,0,.08);}
      .order-card + .order-card{margin-top:12px;}
      .status{font-weight:600; font-size:14px;}
    </style>
  </head>

  <body>
    <!-- Sidebar -->
    <?= $this->include('layout/sidebar') ?>

    <!-- Content -->
    <div class="content">
      <div class="page-header">
        <h5>Pesanan Saya</h5>
        <div class="d-none d-md-block small">Semua status pesanan dalam satu tempat</div>
      </div>
      
      <div class="card-container">
        <div class="mb-3 d-flex flex-wrap tabs-wrap">
          <a href="/riwayatpesanan"     class="btn btn-sm btn-success btn-filter active">Semua</a>
          <a href="/pesananbelumbayar"  class="btn btn-sm btn-outline-success btn-filter">Belum Bayar</a>
          <a href="/pesanandikemas"     class="btn btn-sm btn-outline-success btn-filter">Dikemas</a>
          <a href="/konfirmasipesanan"  class="btn btn-sm btn-outline-success btn-filter">Dikirim</a>
          <a href="/pesananselesai"     class="btn btn-sm btn-outline-success btn-filter">Selesai</a>
          <a href="/pesanandibatalkan"  class="btn btn-sm btn-outline-success btn-filter">Dibatalkan</a>
          <a href="<?= base_url('penilaian/daftar') ?>" class="btn btn-sm btn-outline-success btn-filter">Berikan Penilaian</a>
        </div>

        <?php if (!empty($orders)): ?>
          <?php foreach ($orders as $order): ?>
            <div class="card order-card">
              <div class="card-body d-flex flex-wrap justify-content-between align-items-center">
                <div class="d-flex align-items-center">
                  <div class="order-img">
                    <?php if (!empty($order['foto'])): ?>
                      <img src="<?= base_url('uploads/produk/'.$order['foto']); ?>" alt="<?= esc($order['nama_produk']); ?>">
                    <?php else: ?>
                      <img src="<?= base_url('assets/images/no-image.png'); ?>" alt="No Image">
                    <?php endif; ?>
                  </div>
                  <div class="ms-3">
                    <h6 class="fw-bold mb-1"><?= esc($order['nama_produk']); ?></h6>
                    <p class="text-muted mb-1">Farm Unand</p>
                    <p class="mb-0">Jumlah: <?= esc($order['jumlah_produk']); ?></p>
                  </div>
                </div>
                <div class="text-end mt-3 mt-md-0">
                  <p class="mb-1 text-success status"><?= esc($order['status_pemesanan']); ?></p>
                  <p class="mb-0">
                    Total Pesanan
                    <span class="fw-bold">
                      Rp <?= number_format(($order['harga'] ?? 0) * ($order['jumlah_produk'] ?? 0), 0, ',', '.'); ?>
                    </span>
                  </p>
                </div>
              </div>
            </div>
          <?php endforeach; ?>
        <?php else: ?>
          <div class="alert alert-info mb-0">Belum ada pesanan.</div>
        <?php endif; ?>
      </div>
    </div>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
  </body>
</html>
