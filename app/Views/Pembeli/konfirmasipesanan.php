<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Pesanan Saya - FarmUnand</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

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
      box-shadow:0 6px 14px rgba(0,0,0,.08);
      margin-bottom:16px;
    }
    .page-header h5{margin:0; font-weight:700}
    .card-container{background:#fff; border-radius:12px; box-shadow:0 6px 18px rgba(0,0,0,.06); padding:18px;}
    .tabs-wrap{gap:8px;}
    .btn-filter{border-radius:999px; font-weight:500; padding:6px 14px;}
    .order-img{width:80px; height:80px; border-radius:8px; object-fit:cover; background:#e9ecef;}
    .order-card{border:none; border-radius:12px; box-shadow:0 4px 10px rgba(0,0,0,.08);}
    .order-card + .order-card{margin-top:12px;}
  </style>
</head>
<body>

  <?= $this->include('layout/sidebar') ?>

  <div class="content">
    <div class="page-header">
      <h5>Pesanan Dikirim</h5>
      <div class="d-none d-md-block small">Konfirmasi pesanan yang sudah kamu terima (batas 7 hari)</div>
    </div>

    <div class="card-container">
      <div class="mb-3 d-flex flex-wrap tabs-wrap">
        <a href="<?= base_url('riwayatpesanan') ?>"     class="btn btn-sm btn-outline-success btn-filter">Semua</a>
        <a href="<?= base_url('pesananbelumbayar') ?>"  class="btn btn-sm btn-outline-success btn-filter">Belum Bayar</a>
        <a href="<?= base_url('pesanandikemas') ?>"     class="btn btn-sm btn-outline-success btn-filter">Dikemas</a>
        <a href="<?= base_url('konfirmasipesanan') ?>"  class="btn btn-sm btn-success btn-filter active">Dikirim</a>
        <a href="<?= base_url('pesananselesai') ?>"     class="btn btn-sm btn-outline-success btn-filter">Selesai</a>
        <a href="<?= base_url('pesanandibatalkan') ?>"  class="btn btn-sm btn-outline-success btn-filter">Dibatalkan</a>
        <a href="<?= base_url('penilaian/daftar') ?>"   class="btn btn-sm btn-outline-success btn-filter">Berikan Penilaian</a>
      </div>

      <?php
      if (!function_exists('status_badge')) {
        function status_badge(?string $s): string {
          switch ($s) {
            case 'Belum Bayar': return 'bg-secondary';
            case 'Dikemas':     return 'bg-warning text-dark';
            case 'Dikirim':     return 'bg-primary';
            case 'Selesai':     return 'bg-success';
            case 'Dibatalkan':  return 'bg-danger';
            default:            return 'bg-light text-dark';
          }
        }
      }
      ?>

      <?php if (!empty($pesanan)) : ?>
        <?php foreach ($pesanan as $p): ?>
          <?php
            $qty       = (int)($p['jumlah_produk'] ?? 0);
            $harga     = (float)($p['harga'] ?? 0);
            $total     = $p['total_harga'] ?? ($qty * $harga);
            $status    = $p['status_pemesanan'] ?? '-';
            $foto      = $p['foto'] ?? 'default.png';
            $created   = $p['created_at'] ?? null;

            // Format waktu WIB dengan fallback
            $waktuWIB = '-';
            if ($created) {
              if (class_exists('\CodeIgniter\I18n\Time')) {
                try {
                  // Jika data lama tersimpan UTC, ubah ke Asia/Jakarta. Kalau sudah lokal, tetap aman.
                  $t = \CodeIgniter\I18n\Time::parse($created, 'Asia/Jakarta')
                        ->setTimezone('Asia/Jakarta');
                  $waktuWIB = $t->toLocalizedString('dd/MM/yyyy HH:mm');
                } catch (\Throwable $e) {
                  $waktuWIB = date('d/m/Y H:i', strtotime($created));
                }
              } else {
                $waktuWIB = date('d/m/Y H:i', strtotime($created));
              }
            }
          ?>
          <div class="card order-card">
            <div class="card-body d-flex flex-wrap justify-content-between align-items-center">
              <div class="d-flex align-items-center">
                <img src="<?= base_url('uploads/produk/'.$foto) ?>" class="order-img" alt="<?= esc($p['nama_produk'] ?? 'Produk') ?>">
                <div class="ms-3">
                  <h6 class="fw-bold mb-1"><?= esc($p['nama_produk'] ?? 'Produk') ?></h6>
                  <p class="mb-0 small text-muted">Jumlah: <?= esc($qty) ?></p>
                  <p class="mb-0 small text-muted">Harga: Rp <?= number_format($harga,0,',','.') ?></p>
                </div>
              </div>

              <div class="text-end mt-3 mt-md-0">
                <p class="mb-1">
                  <span class="badge <?= status_badge($status) ?>"><?= esc($status) ?></span>
                </p>
                <p class="mb-0">
                  Total Pesanan
                  <span class="fw-bold">Rp <?= number_format((float)$total,0,',','.') ?></span>
                </p>
                <p class="mb-0 text-muted small">
                  <i class="bi bi-clock me-1"></i><?= $waktuWIB ?> WIB
                </p>

                <?php if ($status === 'Dikirim'): ?>
                  <a href="javascript:void(0);"
                     onclick="konfirmasiSelesai('<?= site_url('pesanan/konfirmasi/'.(int)$p['id_pemesanan']) ?>')"
                     class="btn btn-sm btn-success btn-filter mt-2">
                     Pesanan Selesai
                  </a>
                <?php elseif ($status === 'Selesai'): ?>
                  <button class="btn btn-sm btn-outline-success mt-2" disabled>Sudah Selesai</button>
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

  <!-- Popup Konfirmasi SweetAlert2 -->
  <script>
  function konfirmasiSelesai(url) {
    Swal.fire({
      title: 'Pesanan Telah Selesai',
      text: 'Terima kasih telah berbelanja di FarmUnand!',
      icon: 'success',
      showConfirmButton: false,
      timer: 1600,
      timerProgressBar: true
    }).then(() => {
      window.location.href = url;
    });
  }
  </script>

</body>
</html>
