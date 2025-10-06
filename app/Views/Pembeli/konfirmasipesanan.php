<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Pesanan Saya - FarmUnand</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

  <style>
    body{background:#f8f9fa;}
    .content{margin-left:250px; padding:30px;}

    /* Header hijau seragam */
    .page-header{
      background:linear-gradient(135deg,#198754,#28a745);
      color:#fff; border-radius:12px; padding:18px 20px;
      display:flex; align-items:center; justify-content:space-between;
      box-shadow:0 6px 14px rgba(0,0,0,.08);
      margin-bottom:16px;
    }
    .page-header h5{margin:0; font-weight:700}

    /* Card container untuk isi halaman */
    .card-container{
      background:#fff; border-radius:12px; box-shadow:0 6px 18px rgba(0,0,0,.06);
      padding:18px;
    }

    /* Tabs */
    .tabs-wrap{gap:8px;}
    .btn-filter{border-radius:999px; font-weight:500; padding:6px 14px;}

    /* Kartu pesanan */
    .order-img{width:80px; height:80px; border-radius:8px; object-fit:cover; background:#e9ecef;}
    .order-card{border:none; border-radius:12px; box-shadow:0 4px 10px rgba(0,0,0,.08);}
    .order-card + .order-card{margin-top:12px;} /* jarak antar kartu */
  </style>
</head>
<body>

  <!-- Sidebar -->
  <?= $this->include('layout/sidebar') ?>

  <div class="content">
    <!-- Header seragam -->
    <div class="page-header">
      <h5>Pesanan Dikirim</h5>
      <div class="d-none d-md-block small">Kelola pesanan yang sedang dalam perjalanan</div>
    </div>

    <div class="card-container">
      <!-- Tabs -->
      <div class="mb-3 d-flex flex-wrap tabs-wrap">
        <a href="/riwayatpesanan"     class="btn btn-sm btn-outline-success btn-filter">Semua</a>
        <a href="/pesananbelumbayar"  class="btn btn-sm btn-outline-success btn-filter">Belum Bayar</a>
        <a href="/pesanandikemas"     class="btn btn-sm btn-outline-success btn-filter">Dikemas</a>
        <a href="/konfirmasipesanan"  class="btn btn-sm btn-success btn-filter active">Dikirim</a>
        <a href="/pesananselesai"     class="btn btn-sm btn-outline-success btn-filter">Selesai</a>
        <a href="/pesanandibatalkan"  class="btn btn-sm btn-outline-success btn-filter">Dibatalkan</a>
        <a href="<?= base_url('penilaian/daftar') ?>" class="btn btn-sm btn-outline-success btn-filter">
          Berikan Penilaian
        </a>
      </div>

      <!-- List Pesanan -->
      <?php if (!empty($pesanan)) : ?>
        <?php foreach ($pesanan as $p): ?>
          <div class="card order-card">
            <div class="card-body d-flex flex-wrap justify-content-between align-items-center">
              <div class="d-flex align-items-center">
                <img src="<?= base_url('uploads/produk/'.$p['foto']); ?>" class="order-img" alt="produk">
                <div class="ms-3">
                  <h6 class="fw-bold mb-1"><?= esc($p['nama_produk']); ?></h6>
                  <p class="mb-0">Jumlah: <?= esc($p['jumlah_produk']); ?></p>
                  <p class="mb-0">Harga: Rp <?= number_format($p['harga'],0,',','.'); ?></p>
                </div>
              </div>

              <div class="text-end mt-3 mt-md-0">
                <p class="mb-1 text-success fw-bold"><?= esc($p['status_pemesanan']); ?></p>
                <p class="mb-2">
                  Total Pesanan
                  <span class="fw-bold">Rp <?= number_format($p['harga'] * $p['jumlah_produk'],0,',','.'); ?></span>
                </p>

                <?php if ($p['status_pemesanan'] !== 'Selesai'): ?>
                  <a href="javascript:void(0);"
                     onclick="konfirmasiSelesai('<?= site_url('konfirmasipesanan/selesai/'.$p['id_pemesanan']); ?>')"
                     class="btn btn-sm btn-success btn-filter">
                    Pesanan Selesai
                  </a>
                <?php else: ?>
                  <span class="badge bg-success">Selesai</span>
                <?php endif; ?>
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
  <script>
    function konfirmasiSelesai(url){
      Swal.fire({
        title:'Pesanan Telah Selesai',
        text:'Terima kasih telah berbelanja di FarmUnand!',
        icon:'success',
        showConfirmButton:false,
        timer:2000,
        timerProgressBar:true
      }).then(()=>{ window.location.href = url; });
    }
  </script>
</body>
</html>
