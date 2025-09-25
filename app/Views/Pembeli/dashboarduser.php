<!DOCTYPE html>
<html lang="id">
  <head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= esc($title) ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
    <style>
      html, body {
        margin: 0;
        padding: 0;
        height: 100%;
        background: #f8f9fa;
      }

      .content { margin-left: 240px; padding: 30px; }

      .welcome-card {
        background: linear-gradient(135deg, #198754, #28a745);
        color: white;
        border-radius: 15px;
        padding: 25px;
        margin-bottom: 25px;
        box-shadow: 0 4px 12px rgba(0,0,0,0.15);
      }

      .card { border-radius: 12px; box-shadow: 0 4px 10px rgba(0,0,0,0.08); }
      .card-header {
        background: #198754;
        color: white;
        font-weight: bold;
        border-top-left-radius: 12px !important;
        border-top-right-radius: 12px !important;
      }
      .product-card img {
        border-top-left-radius: 12px;
        border-top-right-radius: 12px;
        height: 150px;
        object-fit: cover;
      }
      .product-card .card-body { text-align: center; }
    </style>
  </head>

  <body>
    <!-- Sidebar dari layouts -->
    <?= $this->include('layout/sidebar') ?>

    <!-- Content -->
    <div class="content">
      <div class="welcome-card">
        <h4>Selamat Datang, <?= esc($username) ?> 👋</h4>
        <p>Senang bertemu kembali! Yuk cek pesanan kamu atau lihat produk rekomendasi segar dari FarmUnand.</p>
      </div>

      <!-- Info Cards -->
      <div class="row mb-4">
        <div class="col-md-4">
          <div class="card text-center">
            <div class="card-body">
              <i class="bi bi-bag-check-fill text-success fs-2"></i>
              <h5 class="mt-2">Pesanan Sukses</h5>
              <p class="text-success fw-bold"><?= esc($pesanan_sukses) ?></p>
            </div>
          </div>
        </div>
        <div class="col-md-4">
          <div class="card text-center">
            <div class="card-body">
              <i class="bi bi-clock-history text-warning fs-2"></i>
              <h5 class="mt-2">Pending</h5>
              <p class="text-warning fw-bold"><?= esc($pending) ?></p>
            </div>
          </div>
        </div>
        <div class="col-md-4">
          <div class="card text-center">
            <div class="card-body">
              <i class="bi bi-x-circle-fill text-danger fs-2"></i>
              <h5 class="mt-2">Dibatalkan</h5>
              <p class="text-danger fw-bold"><?= esc($batal) ?></p>
            </div>
          </div>
        </div>
      </div>

      <!-- Rekomendasi Produk -->
      <div class="card">
  <div class="card-header">Rekomendasi Produk</div>
  <div class="card-body d-flex gap-3 flex-wrap">
    <?php if (!empty($produk)): ?>
      <?php foreach ($produk as $p): ?>
        <div class="card product-card" style="width: 13rem;">
          <img 
            src="<?= !empty($p['foto']) ? base_url('uploads/produk/'.$p['foto']) : base_url('uploads/default.png') ?>" 
            class="card-img-top" 
            alt="<?= esc($p['nama_produk']) ?>">
          <div class="card-body text-center">
            <h6 class="card-title"><?= esc($p['nama_produk']) ?></h6>
            <p class="text-success">Rp <?= number_format($p['harga'], 0, ',', '.') ?></p>
            <button class="btn btn-sm btn-success w-100" data-id="<?= $p['id_produk'] ?>">Beli</button>
          </div>
        </div>
      <?php endforeach; ?>
    <?php else: ?>
      <p class="text-muted">Belum ada produk tersedia.</p>
    <?php endif; ?>
  </div>
</div>


    <script>
      document.querySelectorAll('.btn-success').forEach(button => {
        button.addEventListener('click', () => {
          const productId = button.getAttribute('data-id');
          window.location.href = "<?= base_url('detailproduk') ?>/" + productId;
        });
      });
    </script>
  </body>
</html>
