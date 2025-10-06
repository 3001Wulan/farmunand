<!DOCTYPE html>
<html lang="id">
  <head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= esc($title) ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
    <style>
      html, body { margin:0; padding:0; height:100%; background:#f8f9fa; }
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
      .product-card { width: 14rem; }
      .product-card img {
        border-top-left-radius: 12px;
        border-top-right-radius: 12px;
        height: 150px;
        object-fit: cover;
      }
      .product-card .card-body { text-align: center; }

      /* rating */
      .rating-line { display:flex; align-items:center; justify-content:center; gap:6px; }
      .rating-stars i { font-size: 14px; }
      .rating-value { font-size: 12px; color:#6c757d; }

      /* stok badge */
      .stock-badge { position:absolute; top:10px; left:10px; }
      .product-wrapper { position:relative; }

      @media (max-width: 992px){
        .product-card { width: calc(50% - 0.75rem); }
      }
      @media (max-width: 576px){
        .content { margin-left: 0; padding: 16px; }
        .product-card { width: 100%; }
      }
    </style>
  </head>

  <body>
    <?= $this->include('layout/sidebar') ?>

    <div class="content">
      <div class="welcome-card">
        <h4>Selamat Datang, <span class="username"><?= esc($username) ?></span> 👋</h4>
        <p class="mb-0">Senang bertemu kembali! <b>Yuk cek pesanan kamu</b> atau lihat <b>produk rekomendasi</b> segar dari FarmUnand.</p>
      </div>

      <!-- Info Cards -->
      <div class="row mb-4">
        <div class="col-md-4">
          <div class="card text-center">
            <div class="card-body">
              <i class="bi bi-bag-check-fill text-success fs-2"></i>
              <h5 class="mt-2">Pesanan Sukses</h5>
              <p class="text-success fw-bold fs-4 mb-0"><?= (int)$pesanan_sukses ?></p>
            </div>
          </div>
        </div>
        <div class="col-md-4">
          <div class="card text-center">
            <div class="card-body">
              <i class="bi bi-clock-history text-warning fs-2"></i>
              <h5 class="mt-2">Pending</h5>
              <p class="text-warning fw-bold fs-4 mb-0"><?= (int)$pending ?></p>
            </div>
          </div>
        </div>
        <div class="col-md-4">
          <div class="card text-center">
            <div class="card-body">
              <i class="bi bi-x-circle-fill text-danger fs-2"></i>
              <h5 class="mt-2">Dibatalkan</h5>
              <p class="text-danger fw-bold fs-4 mb-0"><?= (int)$batal ?></p>
            </div>
          </div>
        </div>
      </div>

      <!-- Rekomendasi Produk -->
      <div class="card">
        <div class="card-header">Rekomendasi Produk</div>
        <div class="card-body d-flex gap-3 flex-wrap">
          <?php if (!empty($produk)): ?>
            <?php foreach ($produk as $p): 
              $avg  = isset($p['avg_rating']) ? (float)$p['avg_rating'] : 0.0;
              $cnt  = isset($p['rating_count']) ? (int)$p['rating_count'] : 0;
              $stok = (int)($p['stok'] ?? 0);

              // hitung bintang (0.5 precision)
              $rounded = round($avg * 2) / 2; // 0.0, 0.5, 1.0, ..., 5.0
              $stars = '';
              for ($i=1; $i<=5; $i++) {
                if ($rounded >= $i) {
                  $stars .= '<i class="bi bi-star-fill text-warning"></i>';
                } elseif ($rounded + 0.5 == $i) {
                  $stars .= '<i class="bi bi-star-half text-warning"></i>';
                } else {
                  $stars .= '<i class="bi bi-star text-warning"></i>';
                }
              }
            ?>
              <div class="card product-card product-wrapper">
                <!-- stok badge -->
                <span class="badge bg-light text-dark stock-badge">Stok: <?= $stok ?></span>

                <img 
                  src="<?= !empty($p['foto']) ? base_url('uploads/produk/'.$p['foto']) : base_url('uploads/default.png') ?>" 
                  class="card-img-top" 
                  alt="<?= esc($p['nama_produk']) ?>">

                <div class="card-body text-center">
                  <h6 class="card-title mb-1"><?= esc($p['nama_produk']) ?></h6>

                  <!-- rating -->
                  <div class="rating-line mb-1">
                    <span class="rating-stars"><?= $stars ?></span>
                    <span class="rating-value">(<?= number_format($avg, 1, ',', '.') ?><?= $cnt ? " · $cnt" : '' ?>)</span>
                  </div>

                  <p class="text-success fw-semibold mb-2">Rp <?= number_format((float)$p['harga'], 0, ',', '.') ?></p>

                  <button class="btn btn-sm btn-success w-100 btn-buy" data-id="<?= (int)$p['id_produk'] ?>">
                    Beli
                  </button>
                </div>
              </div>
            <?php endforeach; ?>
          <?php else: ?>
            <p class="text-muted mb-0">Belum ada produk tersedia.</p>
          <?php endif; ?>
        </div>
      </div>
    </div>

    <script>
      // Hindari bentrok dengan tombol hijau lain: pakai .btn-buy
      document.querySelectorAll('.btn-buy').forEach(button => {
        button.addEventListener('click', () => {
          const productId = button.getAttribute('data-id');
          window.location.href = "<?= base_url('detailproduk') ?>/" + productId;
        });
      });
    </script>
  </body>
</html>
