<!DOCTYPE html>
<html lang="id">
  <head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Detail Produk</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
    <style>
      :root { --brand:#198754; --brand-dark:#145c32; --muted:#f8f9fa; }

      html, body { margin:0; padding:0; height:100%; background:var(--muted); }
      .content { margin-left:250px; padding:30px; }

      /* Section bergaya "judul di tabel hijau" */
      .section { border-radius:12px; box-shadow:0 6px 18px rgba(0,0,0,0.06); overflow:hidden; margin-bottom:16px; background:#fff; }
      .section-title {
        background: linear-gradient(90deg, var(--brand), #20c997);
        color:#fff; font-weight:700; padding:12px 16px;
        display:flex; align-items:center; justify-content:space-between;
      }
      .section-body { padding:16px; }

      /* Produk */
      .product-wrap { display:flex; gap:16px; align-items:flex-start; }
      .product-image {
        width:160px; height:130px; border-radius:10px; overflow:hidden; background:#f0f0f0; flex-shrink:0;
        border:1px solid #e9ecef;
      }
      .product-image img { width:100%; height:100%; object-fit:cover; }
      .product-title { font-size:20px; font-weight:700; color:#333; }
      .product-price { font-size:18px; color:var(--brand); font-weight:700; }
      .stars i { font-size:18px; margin-right:2px; }
      .rating-badge { font-size:12px; color:#6c757d; }

      /* Qty */
      .qty-group { display:flex; align-items:stretch; gap:8px; margin-top:8px; }
      .qty-group .btn-qty {
        width:38px; border:1px solid #e6efe9; background:#fff; color:#333; font-weight:700; border-radius:8px;
      }
      .qty-group input[type="number"] {
        width:90px; border:1px solid #e6efe9; border-radius:8px; padding:6px 10px; text-align:center;
      }

      /* Tombol aksi */
      .actions { display:flex; gap:10px; margin-top:12px; flex-wrap:wrap; }
      .btn-cart { background:#fff; color:var(--brand); border:1px solid var(--brand); }
      .btn-cart:hover { background:var(--brand); color:#fff; }
      .btn-checkout { background:var(--brand); color:#fff; border:none; }
      .btn-checkout:hover { background:var(--brand-dark); }

      /* Informasi */
      .pill {
        display:inline-block; padding:4px 10px; border-radius:999px; font-size:13px; margin-right:6px;
        border:1px solid #e9ecef; background:#f8f9fa;
      }

      /* Review list */
      .review-item { border-bottom:1px dashed #e9ecef; padding:12px 0; }
      .review-item:last-child { border-bottom:0; }
      .review-name { font-weight:700; color:#333; }
      .review-date { font-size:12px; color:#6c757d; }
      .review-stars i { font-size:16px; margin-right:2px; }
      .review-text { margin-top:6px; white-space:pre-wrap; }

      @media (max-width: 992px) {
        .content { margin-left:0; padding:18px; }
        .product-wrap { flex-direction:column; }
      }
    </style>
  </head>

  <body>
    <!-- Sidebar -->
    <?= $this->include('layout/sidebar') ?>

    <!-- Content -->
    <div class="content">

      <?php
        // Helper kecil untuk render bintang (5 skala)
        $renderStars = function($val) {
          $val   = floatval($val ?? 0);
          $full  = (int) floor($val);
          $half  = ($val - $full) >= 0.5 ? 1 : 0;
          $empty = 5 - $full - $half;
          $html  = '';
          for ($i=0; $i<$full; $i++) $html .= '<i class="bi bi-star-fill"></i>';
          if ($half) $html .= '<i class="bi bi-star-half"></i>';
          for ($i=0; $i<$empty; $i++) $html .= '<i class="bi bi-star"></i>';
          return $html;
        };
      ?>

      <!-- Detail Produk -->
      <div class="section">
        <div class="section-title">
          <span>Detail Produk</span>
          <a href="javascript:history.back()" class="btn btn-light btn-sm">Kembali</a>
        </div>
        <div class="section-body">

          <div class="product-wrap">
            <div class="product-image">
              <?php $foto = !empty($produk['foto']) ? $produk['foto'] : 'default.png'; ?>
              <img src="<?= base_url('uploads/produk/' . esc($foto)) ?>" alt="<?= esc($produk['nama_produk']) ?>">
            </div>

            <div class="product-info flex-grow-1">
              <div class="product-title mb-1"><?= esc($produk['nama_produk']) ?></div>

              <div class="d-flex align-items-center gap-2 mb-1">
                <span class="stars"><?= $renderStars($produk['rating'] ?? 0) ?></span>
                <span class="rating-badge">(<?= number_format((float)($produk['rating'] ?? 0), 1) ?>)</span>
              </div>

              <div class="product-price mb-2">Rp <?= number_format($produk['harga'], 0, ',', '.') ?></div>

              <div class="mb-2"><span class="pill">Stok: <?= esc($produk['stok']) ?></span></div>

              <!-- SATU FORM UNTUK KEDUA AKSI -->
<form id="actionForm" method="post" class="d-inline">
  <?= csrf_field() ?>
  <input type="hidden" name="id_produk" value="<?= $produk['id_produk'] ?>">

  <!-- Qty control (satu-satunya) -->
  <div class="input-group input-group-sm" style="width:180px;">
    <button class="btn btn-outline-secondary" type="button" onclick="chgQty(-1)">−</button>
    <input
      type="number"
      id="qty"
      name="qty"
      class="form-control text-center"
      min="1"
      max="<?= (int)($produk['stok'] ?? 999999) ?>"
      value="1"
    >
    <button class="btn btn-outline-secondary" type="button" onclick="chgQty(1)">+</button>
  </div>

  <!-- Dua tombol submit, beda tujuan -->
  <div class="mt-2 d-flex gap-2">
    <button
      type="submit"
      class="btn btn-sm btn-success"
      formaction="<?= base_url('keranjang/add') ?>"
      formmethod="post"
    >
      Masukkan Keranjang
    </button>

    <button
      type="submit"
      class="btn btn-sm btn-secondary"
      formaction="<?= base_url('melakukanpemesanan') ?>"
      formmethod="post"
    >
      Checkout
    </button>
  </div>
</form>

<script>
function chgQty(d) {
  const el  = document.getElementById('qty');
  const min = parseInt(el.min || '1', 10);
  const max = parseInt(el.max || '999999', 10);
  let v     = parseInt(el.value || '1', 10) + d;

  if (v < min) v = min;
  if (!isNaN(max) && v > max) v = max;
  el.value = v;
}

// Clamp saat user ketik manual
document.getElementById('qty').addEventListener('input', function(){
  const min = parseInt(this.min || '1', 10);
  const max = parseInt(this.max || '999999', 10);
  let v     = parseInt(this.value || '1', 10);
  if (isNaN(v) || v < min) v = min;
  if (!isNaN(max) && v > max) v = max;
  this.value = v;
});
</script>

                
            </div>
          </div>

        </div>
      </div>

      <!-- Deskripsi Produk -->
      <div class="section">
        <div class="section-title">Deskripsi Produk</div>
        <div class="section-body">
          <p class="mb-0"><?= !empty($produk['deskripsi']) ? esc($produk['deskripsi']) : 'Tidak ada deskripsi.' ?></p>
        </div>
      </div>

      <!-- Informasi Produk -->
      <div class="section">
        <div class="section-title">Informasi Produk</div>
        <div class="section-body">
          <div class="mb-1"><b>Stok:</b> <?= esc($produk['stok']) ?></div>
          <div class="mb-1"><b>Kategori:</b> -</div>
          <div class="mb-0"><b>Alamat Produksi:</b> -</div>
        </div>
      </div>

      <!-- Penilaian (ambil dari detail_pemesanan.user_rating/.user_review) -->
      <div class="section">
        <div class="section-title">Penilaian Produk</div>
        <div class="section-body">
          <?php if (!empty($reviews) && is_array($reviews)): ?>
            <?php foreach ($reviews as $r): ?>
              <div class="review-item">
                <div class="d-flex justify-content-between">
                  <div class="review-name"><?= esc($r['nama_user'] ?? $r['username'] ?? $r['nama'] ?? 'Pengguna') ?></div>
                  <div class="review-date">
                    <?= esc(isset($r['updated_at']) ? date('d/m/Y', strtotime($r['updated_at'])) : (isset($r['created_at']) ? date('d/m/Y', strtotime($r['created_at'])) : '')) ?>
                  </div>
                </div>
                <div class="review-stars mt-1"><?= $renderStars($r['user_rating'] ?? 0) ?></div>
                <?php if (!empty($r['user_review'])): ?>
                  <div class="review-text"><?= esc($r['user_review']) ?></div>
                <?php endif; ?>
              </div>
            <?php endforeach; ?>
          <?php else: ?>
            <div class="text-muted">Belum ada ulasan.</div>
          <?php endif; ?>
        </div>
      </div>

    </div>

    <script>
      function addToCart() { alert("✅ Produk berhasil ditambahkan ke keranjang!"); }

      const qtyInput = document.getElementById('qtyInput');
      const maxQty = parseInt(qtyInput.getAttribute('max') || '999999', 10);

      function incQty(){
        let v = parseInt(qtyInput.value || '1', 10);
        if (v < maxQty) qtyInput.value = v + 1;
      }
      function decQty(){
        let v = parseInt(qtyInput.value || '1', 10);
        if (v > 1) qtyInput.value = v - 1;
      }
    </script>
  </body>
</html>
