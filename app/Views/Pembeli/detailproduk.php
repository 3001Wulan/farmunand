<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Detail Produk</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
<style>
html, body { margin: 0; padding: 0; height: 100%; background: #f8f9fa; }

/* Sidebar fixed */
.sidebar {
  position: fixed; top: 0; left: 0;
  width: 220px; height: 100vh;
  background: #198754; padding: 20px;
  color: white; overflow-y: auto; z-index: 1000;
}
.sidebar .profile {
  width: 120px; height: 120px; border-radius: 50%;
  background: white; margin: 0 auto 20px auto;
  display: flex; align-items: center; justify-content: center;
  font-weight: bold; color: #198754; font-size: 18px;
}
.sidebar a {
  display: block; padding: 10px; margin: 10px 0;
  background: white; color: #198754; text-decoration: none;
  border-radius: 5px; font-weight: 500; text-align: center;
  transition: all 0.3s;
}
.sidebar a:hover, .sidebar a.active { background: #145c32; color: white; }

/* Content */
.content { margin-left: 240px; padding: 30px; }

/* Product */
.product-image { width: 150px; height: 120px; background: #f0f0f0; border-radius: 5px; overflow: hidden; flex-shrink: 0; }
.product-image img { width: 100%; height: 100%; object-fit: cover; }
.product-info .product-title { font-size: 18px; font-weight: bold; color: #333; }
.product-info .product-price { font-size: 16px; color: #198754; font-weight: 600; }
.btn-cart { background: #198754; color: white; }
.btn-checkout { background: #6c757d; color: white; }
.read-more-btn { color: #198754; cursor: pointer; font-size: 14px; text-decoration: underline; font-weight: bold; }
.read-more-btn:hover { color: #145c32; }

/* Stars */
.stars { color: #ffc107; font-size: 18px; margin: 2px 0; line-height: 1.2; display: block; }
.card-body p { margin: 2px 0; }
</style>
</head>
<body>

<!-- Sidebar -->
<div class="sidebar">
  <div class="profile">Pembeli</div>
  <a href="#">Akun Saya</a>
  <a href="#">Dashboard</a>
  <a href="#" class="active">Pemesanan Saya</a>
</div>

<!-- Content -->
<div class="content">
  <h3 class="mb-4 text-success">Detail Produk</h3>

  <!-- Detail Produk -->
  <div class="card mb-3">
    <div class="card-body d-flex gap-3">
      <div class="product-image">
        <img src="<?= base_url('assets/images/' . esc($produk['foto'])) ?>" alt="<?= esc($produk['nama_produk']) ?>">
      </div>
      <div class="product-info">
        <div class="product-title"><?= esc($produk['nama_produk']) ?></div>
        <div class="text-muted">⭐ <?= esc($produk['rating'] ?? 'Belum ada rating') ?></div>
        <div class="product-price">Rp<?= number_format($produk['harga'], 0, ',', '.') ?></div>
        <div class="mt-2">
          <button class="btn btn-sm btn-cart" onclick="addToCart()">Masukkan Keranjang</button>
          <button class="btn btn-sm btn-checkout" onclick="checkout()">Checkout</button>
        </div>
      </div>
    </div>
  </div>

  <!-- Deskripsi Produk -->
  <div class="card mb-3">
    <div class="card-header bg-success text-white">Deskripsi Produk</div>
    <div class="card-body">
      <p><?= esc($produk['deskripsi']) ?></p>
    </div>
  </div>

  <!-- Informasi Produk -->
  <div class="card mb-3">
    <div class="card-header bg-success text-white">Informasi Produk</div>
    <div class="card-body">
      <p><b>Stok:</b> <?= esc($produk['stok']) ?></p>
      <p><b>Kategori:</b> Makanan</p>
      <p><b>Alamat Produksi:</b> Peternakan Unggulan Indonesia</p>
    </div>
  </div>

  <!-- Penilaian -->
  <div class="card">
    <div class="card-header bg-success text-white">Penilaian Produk</div>
    <div class="card-body">
      <p><b>Heni Yunida</b></p>
      <div class="stars">★★★★★</div>
      <p class="text-muted"><?= date('d-m-Y') ?></p>
    </div>
  </div>
</div>

<script>
function addToCart() { alert("✅ Produk berhasil ditambahkan ke keranjang!"); }
function checkout() { alert("🛒 Mengarahkan ke halaman pembayaran..."); }
</script>
</body>
</html>
