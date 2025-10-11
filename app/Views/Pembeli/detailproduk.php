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
      .section { border-radius:12px; box-shadow:0 6px 18px rgba(0,0,0,0.06); overflow:hidden; margin-bottom:16px; background:#fff; }
      .section-title {
        background: linear-gradient(90deg, var(--brand), #20c997);
        color:#fff; font-weight:700; padding:12px 16px;
        display:flex; align-items:center; justify-content:space-between;}
      .review-text {
      margin-top: 6px;
      white-space: pre-wrap; 
      word-wrap: break-word; 
      overflow-wrap: break-word; 
      max-width: 100%; }
      .section-body { padding:16px; }
      .product-wrap { display:flex; gap:16px; align-items:flex-start; }
      .product-image { width:160px; height:130px; border-radius:10px; overflow:hidden; background:#f0f0f0; flex-shrink:0; border:1px solid #e9ecef; }
      .product-image img { width:100%; height:100%; object-fit:cover; cursor:pointer; }
      .product-title { font-size:20px; font-weight:700; color:#333; }
      .product-price { font-size:18px; color:var(--brand); font-weight:700; }
      .stars i { font-size:18px; margin-right:2px; }
      .rating-badge { font-size:12px; color:#6c757d; }
      .qty-group { display:flex; align-items:stretch; gap:8px; margin-top:8px; }
      .qty-group .btn-qty { width:38px; border:1px solid #e6efe9; background:#fff; color:#333; font-weight:700; border-radius:8px; }
      .qty-group input[type="number"] { width:90px; border:1px solid #e6efe9; border-radius:8px; padding:6px 10px; text-align:center; }
      .actions { display:flex; gap:10px; margin-top:12px; flex-wrap:wrap; }
      .btn-cart { background:#fff; color:var(--brand); border:1px solid var(--brand); }
      .btn-cart:hover { background:var(--brand); color:#fff; }
      .btn-checkout { background:var(--brand); color:#fff; border:none; }
      .btn-checkout:hover { background:var(--brand-dark); }
      .pill { display:inline-block; padding:4px 10px; border-radius:999px; font-size:13px; margin-right:6px; border:1px solid #e9ecef; background:#f8f9fa; }
      .review-item { border-bottom:1px dashed #e9ecef; padding:12px 0; }
      .review-item:last-child { border-bottom:0; }
      .review-name { font-weight:700; color:#333; }
      .review-date { font-size:12px; color:#6c757d; }
      .stars i,
      .review-stars i { color: #ffc107; font-size:16px; margin-right:2px; }
      .stars .bi-star,
      .review-stars .bi-star { opacity: .5; }
      .review-text { margin-top:6px; white-space:pre-wrap; }
      .review-item img, .review-item video { cursor:pointer; }
      @media (max-width: 992px) {
        .content { margin-left:0; padding:18px; }
        .product-wrap { flex-direction:column; }}
    </style>
  </head>

  <body>
    <!-- Sidebar -->
    <?= $this->include('layout/sidebar') ?>

    <!-- Content -->
    <div class="content">
      <?php
        // Helper untuk render bintang
        $renderStars = function($val) {
          $val = floatval($val ?? 0);
          $full = (int) floor($val);
          $half = ($val - $full) >= 0.5 ? 1 : 0;
          $empty = 5 - $full - $half;
          $html = '';
          for ($i=0;$i<$full;$i++) $html .= '<i class="bi bi-star-fill"></i>';
          if ($half) $html .= '<i class="bi bi-star-half"></i>';
          for ($i=0;$i<$empty;$i++) $html .= '<i class="bi bi-star"></i>';
          return $html;
        };

        // Hitung rata-rata rating
        $validRatings = array_filter($reviews ?? [], fn($r) => ($r['user_rating'] ?? 0) > 0);
        $avgRating = count($validRatings) ? array_sum(array_column($validRatings,'user_rating')) / count($validRatings) : 0;
      ?>

      <!-- Detail Produk -->
      <div class="section">
        <div class="section-title">
          <span>Detail Produk</span>
          <a href="javascript:history.back()" class="btn btn-success btn-sm">Kembali</a>
        </div>
        
        <div class="section-body">
          <div class="product-wrap">
            <div class="product-image" onclick="openMedia('img','<?= base_url('uploads/produk/' . esc(!empty($produk['foto']) ? $produk['foto'] : 'default.png')) ?>')">
              <img src="<?= base_url('uploads/produk/' . esc(!empty($produk['foto']) ? $produk['foto'] : 'default.png')) ?>" alt="<?= esc($produk['nama_produk']) ?>">
            </div>

            <div class="product-info flex-grow-1">
              <div class="product-title mb-1"><?= esc($produk['nama_produk']) ?></div>

              <!-- Rating rata-rata -->
              <div class="d-flex align-items-center gap-2 mb-2">
                <span class="stars"><?= $renderStars($avgRating) ?></span>
                <span class="rating-badge">(<?= number_format($avgRating,1) ?> dari <?= count($validRatings) ?> ulasan)</span>
              </div>

              <div class="product-price mb-2">Rp <?= number_format($produk['harga'],0,',','.') ?></div>
              <div class="mb-2"><span class="pill">Stok: <?= esc($produk['stok']) ?></span></div>

              <!-- Form aksi -->
              <form id="actionForm" method="post" class="d-inline">
                <?= csrf_field() ?>
                <input type="hidden" name="id_produk" value="<?= $produk['id_produk'] ?>">

                <div class="input-group input-group-sm" style="width:180px;">
                  <button class="btn btn-outline-secondary" type="button" onclick="chgQty(-1)">−</button>
                  <input type="number" id="qty" name="qty" class="form-control text-center" min="1" max="<?= (int)($produk['stok'] ?? 999999) ?>" value="1">
                  <button class="btn btn-outline-secondary" type="button" onclick="chgQty(1)">+</button>
                </div>

                <div class="mt-2 d-flex gap-2">
                  <button type="submit" class="btn btn-sm btn-success" formaction="<?= base_url('keranjang/add') ?>" formmethod="post">Masukkan Keranjang</button>
                  <button type="submit" class="btn btn-sm btn-secondary" formaction="<?= base_url('melakukanpemesanan') ?>" formmethod="post">Checkout</button>
                </div>
              </form>

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
          <div class="mb-1"><b>Kategori:</b> <?= !empty($produk['kategori']) ? esc($produk['kategori']) : '-' ?></div>
        </div>
      </div>


      <!-- Penilaian Produk -->
      <div class="section">
        <div class="section-title">Penilaian Produk</div>
        <div class="section-body">
          <?php if(!empty($reviews)): ?>
            <?php foreach($reviews as $r): 
              $rating = floatval($r['user_rating'] ?? 0);
              if($rating <= 0) continue;
              $media = !empty($r['user_media']) ? json_decode($r['user_media'],true) : [];
            ?>

            <div class="review-item">
              <div class="d-flex justify-content-between">
                <div class="review-name"><?= esc($r['nama_user'] ?? $r['username'] ?? 'Pengguna') ?></div>
                <div class="review-date"><?= esc(isset($r['updated_at']) ? date('d/m/Y', strtotime($r['updated_at'])) : (isset($r['created_at']) ? date('d/m/Y', strtotime($r['created_at'])) : '')) ?></div>
              </div>
              <div class="review-stars mt-1"><?= $renderStars($rating) ?></div>
              <?php if(!empty($r['user_ulasan'])): ?>
                <div class="review-text"><?= esc($r['user_ulasan']) ?></div>
              <?php endif; ?>

              <?php if(!empty($media)): ?>
                <div class="d-flex flex-wrap gap-2 mt-2">
                  <?php foreach($media as $m): 
                    $ext = strtolower(pathinfo($m, PATHINFO_EXTENSION));
                    $url = base_url('uploads/penilaian/' . esc($m));
                    if(in_array($ext, ['jpg','jpeg','png','gif'])): ?>
                      <img src="<?= $url ?>" alt="Media review" style="width:80px;height:80px;object-fit:cover;border-radius:8px;" onclick="openMedia('img','<?= $url ?>')">
                    <?php elseif(in_array($ext, ['mp4','webm','ogg'])): ?>
                      <video style="width:120px;height:80px;border-radius:8px;" onclick="openMedia('video','<?= $url ?>','video/<?= $ext ?>')">
                        <source src="<?= $url ?>" type="video/<?= $ext ?>">
                      </video>
                    <?php endif; ?>
                  <?php endforeach; ?>
                </div>
              <?php endif; ?>

            </div>
            <?php endforeach; ?>
          <?php else: ?>
            <div class="text-muted">Belum ada ulasan.</div>
          <?php endif; ?>

        </div>
      </div>
    </div>

    <!-- Modal Preview -->
    <div class="modal fade" id="mediaModal" tabindex="-1" aria-hidden="true">
      <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content bg-transparent border-0">
          <div class="modal-body p-0 d-flex justify-content-center align-items-center" style="max-height:90vh; overflow:auto;">
            <button type="button" class="btn-close position-absolute top-0 end-0 m-2" data-bs-dismiss="modal" aria-label="Close"></button>
            <img id="modalImg" src="" alt="Preview" class="img-fluid rounded" style="display:none; max-height:90vh; object-fit:contain;">
            <video id="modalVideo" controls class="img-fluid rounded" style="display:none; max-height:90vh; object-fit:contain;">
              <source id="modalVideoSrc" src="" type="">
              Browser tidak mendukung video.
            </video>
          </div>
        </div>
      </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
      function chgQty(d){
        const el = document.getElementById('qty');
        const min = parseInt(el.min || '1',10);
        const max = parseInt(el.max || '999999',10);
        let v = parseInt(el.value || '1',10)+d;
        if(v<min)v=min;
        if(!isNaN(max)&&v>max)v=max;
        el.value=v;
      }

      document.getElementById('qty').addEventListener('input',function(){
        const min = parseInt(this.min||'1',10);
        const max = parseInt(this.max||'999999',10);
        let v=parseInt(this.value||'1',10);
        if(isNaN(v)||v<min)v=min;
        if(!isNaN(max)&&v>max)v=max;
        this.value=v;
      });

      function openMedia(type, src, videoType='') {
        const modalImg = document.getElementById('modalImg');
        const modalVideo = document.getElementById('modalVideo');
        const modalVideoSrc = document.getElementById('modalVideoSrc');

        if(type === 'img'){
          modalVideo.style.display = 'none';
          modalVideo.pause?.();
          modalImg.src = src;
          modalImg.style.display = 'block';
        } else if(type === 'video'){
          modalImg.style.display = 'none';
          modalVideoSrc.src = src;
          modalVideo.src = src;
          modalVideo.type = videoType;
          modalVideo.load();
          modalVideo.style.display = 'block';
        }

        const modal = new bootstrap.Modal(document.getElementById('mediaModal'));
        modal.show();
      }
    </script>
  </body>
</html>
