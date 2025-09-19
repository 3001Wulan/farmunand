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

/* Sidebar fixed */
.sidebar {
  position: fixed;
  top: 0;
  left: 0;
  width: 220px;
  height: 100vh;
  background: #198754;
  padding: 20px;
  color: white;
  overflow-y: auto;
  z-index: 1000;
}

.sidebar .profile {
  width: 120px;
  height: 120px;
  border-radius: 50%;
  background: white;
  margin: 0 auto 20px auto;
  display: flex;
  align-items: center;
  justify-content: center;
  font-weight: bold;
  color: #198754;
  font-size: 18px;
}

.sidebar a {
  display: block;
  padding: 10px;
  margin: 10px 0;
  background: white;
  color: #198754;
  text-decoration: none;
  border-radius: 5px;
  font-weight: 500;
  text-align: center;
  transition: all 0.3s;
}

.sidebar a:hover,
.sidebar a.active {
  background: #145c32;
  color: white;
}

/* Content margin kiri sesuai sidebar */
.content {
  margin-left: 240px;
  padding: 30px;
}

/* Welcome card */
.welcome-card {
  background: linear-gradient(135deg, #198754, #28a745);
  color: white;
  border-radius: 15px;
  padding: 25px;
  margin-bottom: 25px;
  box-shadow: 0 4px 12px rgba(0,0,0,0.15);
}

/* Cards */
.card { border-radius: 12px; box-shadow: 0 4px 10px rgba(0,0,0,0.08); }
.card-header {
  background: #198754;
  color: white;
  font-weight: bold;
  border-top-left-radius: 12px !important;
  border-top-right-radius: 12px !important;
}
.product-card img { border-top-left-radius: 12px; border-top-right-radius: 12px; height: 150px; object-fit: cover; }
.product-card .card-body { text-align: center; }
</style>
</head>
<body>
<!-- Sidebar -->
<div class="sidebar">
  <div class="profile">Pembeli</div>
  <a href="#" class="active">Dashboard</a>
  <a href="#">Akun Saya</a>
  <a href="riwayatpesanan">Pemesanan Saya</a>
  <a href="login">Log Out</a>
</div>

<!-- Content -->
<div class="content">
  <!-- Welcome Section -->
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
            <img src="<?= base_url('uploads/'.$p['foto']) ?>" alt="<?= esc($p['nama_produk']) ?>">
            <div class="card-body">
              <h6 class="card-title"><?= esc($p['nama_produk']) ?></h6>
              <p class="text-success">Rp <?= number_format($p['harga'], 0, ',', '.') ?></p>
              <button class="btn btn-sm btn-success w-100">Beli</button>
            </div>
          </div>
        <?php endforeach; ?>
      <?php else: ?>
        <p class="text-muted">Belum ada produk tersedia.</p>
      <?php endif; ?>
    </div>
  </div>
</div>

<!-- JavaScript untuk tombol Beli -->
<script>
  const cart = [];
  document.querySelectorAll('.btn-success').forEach(button => {
    button.addEventListener('click', () => {
      const productCard = button.closest('.product-card');
      const productName = productCard.querySelector('.card-title').innerText;
      const productPrice = productCard.querySelector('p.text-success').innerText;

      cart.push({ name: productName, price: productPrice });

      alert(`Berhasil menambahkan ke keranjang: ${productName}\nTotal item di keranjang: ${cart.length}`);
      console.log(cart);
    });
  });
</script>
</body>
</html>
