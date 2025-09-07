<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Ulasan Produk - FarmUnand</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <style>
    body {
      background-color: #f8f9fa;
    }
    .sidebar {
      min-height: 100vh;
    }
    .product-img {
      width: 80px;
      height: 80px;
      background: #e9ecef;
      border-radius: 8px;
    }
    .stars span {
      font-size: 24px;
      color: #ccc;
      cursor: pointer;
    }
    .stars span.active {
      color: gold;
    }
    textarea {
      width: 100%;
      min-height: 100px;
      border-radius: 8px;
      border: 1px solid #aaa;
      padding: 10px;
      resize: vertical;
    }
    .upload-box {
      width: 100px;
      height: 100px;
      border: 1px dashed #999;
      display: flex;
      align-items: center;
      justify-content: center;
      border-radius: 8px;
      font-size: 12px;
      color: #666;
    }
  </style>
</head>
<body>

<div class="container-fluid">
  <div class="row g-0">
    
    <!-- Sidebar (include biar sama dengan halaman lain) -->
    <?= $this->include('layout/sidebar'); ?>

    <!-- Content -->
    <div class="col-md-9 col-lg-10 p-4">
      <div class="card shadow-sm">
        <div class="card-body">
          <h4 class="mb-4">Nilai Produk</h4>

          <!-- Produk -->
          <div class="d-flex align-items-center mb-3">
            <div class="product-img me-3"></div>
            <h6 class="fw-bold mb-0">Daging Sapi Premium</h6>
          </div>

          <!-- Bintang -->
          <p class="mb-1">Nilai Produk:</p>
          <div class="stars mb-3">
            <span>★</span><span>★</span><span>★</span><span>★</span><span>★</span>
          </div>

          <!-- Upload -->
          <p class="mb-1">Tambahkan 1 Foto atau 1 Video:</p>
          <div class="d-flex gap-3 mb-3">
            <div class="upload-box">Foto</div>
            <div class="upload-box">Video</div>
          </div>

          <!-- Textarea -->
          <p class="mb-1">Tulis ulasan minimal 50 karakter:</p>
          <textarea placeholder="Kualitas Gambar:&#10;Kualitas Produk:&#10;Kesegaran:"></textarea>

          <!-- Submit -->
          <button class="btn btn-success mt-3">Kirim</button>
        </div>
      </div>
    </div>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
