<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?= esc($title ?? 'Mengelola Riwayat Pesanan') ?> - Admin</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
  <style>
    :root {
      --brand: #198754;
      --brand2: #28a745;
      --muted: #f8f9fa;
    }
    body {
      background: linear-gradient(135deg, #e6f4ea, #c0e0cc);
      font-family: "Segoe UI", Tahoma, Geneva, Verdana, sans-serif;
      min-height: 100vh;
      margin: 0;
    }
    .content {
      margin-left: 250px;
      padding: 30px;
    }

    /* === KONSEP BARU: HEADER === */
    .page-header {
      background: linear-gradient(135deg, var(--brand), var(--brand2));
      color: white;
      border-radius: 12px 12px 0 0; /* Rounded top */
      padding: 20px 30px;
      display: flex;
      justify-content: space-between;
      align-items: center;
      box-shadow: 0 6px 14px rgba(0, 0, 0, .08);
      margin-bottom: 0;
    }
    .page-header h5 {
      margin: 0;
      font-weight: 700;
      font-size: 20px;
      display: flex;
      align-items: center;
      gap: 10px;
    }

    /* === KONSEP BARU: KONTEN CARD === */
    .card-container {
      background: #fff;
      border-radius: 0 0 12px 12px; /* Rounded bottom */
      box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
      padding: 20px 30px;
    }

    /* === Table Styling === */
    .table thead th {
      background: var(--brand);
      color: #fff;
      border: none;
      vertical-align: middle;
      text-align: center;
      padding: 12px;
      white-space: nowrap;
    }
    .table td,
    .table th {
      vertical-align: middle;
      text-align: center;
      padding: 12px;
      white-space: nowrap;
    }
    .table tbody tr:hover {
      background: #f1fdf6;
      cursor: pointer;
    }
    
    /* === Badge Styling === */
    .badge {
      font-size: 13px;
      padding: 6px 12px;
      border-radius: 12px;
      font-weight: 600;
      text-transform: capitalize;
    }
    .badge.bg-success { background: #198754 !important; }
    .badge.bg-primary { background: #0d6efd !important; }
    .badge.bg-warning { background: #ffc107 !important; color: #333 !important; }
    .badge.bg-danger  { background: #dc3545 !important; }
    .badge.bg-light   { background: #f8f9fa !important; color: #333 !important; border: 1px solid #ddd; }
    .badge.bg-secondary { background: #6c757d !important; color: #fff !important; } /* abu-abu + putih */

    /* === Filter Styling === */
    .filter-rect {
      border-radius: 12px !important;
      height: 44px;
      padding-inline: 14px;
      font-size: 0.95rem;
    }
    .filter-rect.form-select {
      padding-top: .45rem;
      padding-bottom: .45rem;
    }
    .btn-rect {
      border-radius: 12px !important;
      height: 44px;
      background: #198754;
      border: none;
      color: #fff;
      font-weight: 600;
    }
    .btn-rect:hover { background: #157347; }

    .table .form-select-sm {
      font-size: 0.875rem;
      padding-top: 0.4rem;
      padding-bottom: 0.4rem;
      padding-left: 0.6rem;
      border-radius: 8px;
    }

    @media (max-width: 992px) {
      .content { margin-left: 0; padding: 20px; }
      .card-container { padding: 15px; }
    }
  </style>
</head>

<body>
  <?= $this->include('layout/sidebarAdmin') ?>

  <main class="col content" role="main">
    <header class="page-header">
      <h5 class="m-0 fw-bold">📜 Mengelola Riwayat Pesanan</h5>
    </header>

    <section class="card-container">
      <?php if(session()->getFlashdata('success')): ?>
        <div class="alert alert-success py-2 mb-3" role="alert">
          <?= session()->getFlashdata('success') ?>
        </div>
      <?php elseif(session()->getFlashdata('error')): ?>
        <div class="alert alert-danger py-2 mb-3" role="alert">
          <?= session()->getFlashdata('error') ?>
        </div>
      <?php endif; ?>

      <form method="get" class="row g-2 mb-3" role="search" aria-label="Filter Pesanan">
        <div class="col-md-5">
          <input type="search" class="form-control filter-rect" name="keyword" 
                 placeholder="Cari nama pelanggan / produk..."
                 value="<?= esc($keyword ?? '') ?>" aria-label="Cari pesanan">
        </div>
        <div class="col-md-3">
          <select name="status" class="form-select filter-rect" aria-label="Filter status">
            <option value="">Semua Status</option>
            <?php
              $opsi = ['Dikemas','Dikirim','Selesai','Dibatalkan']; // (tetap sesuai kebutuhanmu)
              foreach($opsi as $st):
            ?>
              <option value="<?= $st ?>" <?= (!empty($status) && $status===$st)?'selected':''; ?>><?= $st ?></option>
            <?php endforeach; ?>
          </select>
        </div>
        <div class="col-md-2">
          <select name="sort" class="form-select filter-rect" aria-label="Urutkan">
            <option value="desc" <?= (isset($sort)&&$sort==='desc')?'selected':''; ?>>Terbaru</option>
            <option value="asc"  <?= (isset($sort)&&$sort==='asc') ?'selected':''; ?>>Terlama</option>
          </select>
        </div>
        <div class="col-md-2 d-grid">
          <button type="submit" class="btn btn-rect fw-semibold">Filter</button>
        </div>
      </form>
      
      <?php
      // Teks tampilan: Belum Bayar -> Menunggu Pembayaran
      if (!function_exists('display_label')) {
        function display_label(?string $s): string {
          return ($s === 'Belum Bayar') ? 'Menunggu Pembayaran' : (string)$s;
        }
      }
      // Warna badge: Belum Bayar -> abu-abu teks putih
      if (!function_exists('status_badge_theme')) {
        function status_badge_theme(?string $s): string {
          switch ($s) {
            case 'Belum Bayar': return 'bg-secondary text-white'; // abu-abu + putih
            case 'Dikemas':     return 'bg-warning text-dark';
            case 'Dikirim':     return 'bg-primary';
            case 'Selesai':     return 'bg-success';
            case 'Dibatalkan':  return 'bg-danger';
            default:            return 'bg-light text-dark';
          }
        }
      }
      ?>

      <div class="table-responsive" tabindex="0">
        <table class="table table-hover text-center align-middle" aria-label="Riwayat Pesanan">
          <thead>
            <tr>
              <th scope="col" style="width:160px">Tanggal</th>
              <th scope="col">Nama</th>
              <th scope="col">Produk</th>
              <th scope="col" style="width:110px">Qty</th>
              <th scope="col" style="width:160px">Total</th>
              <th scope="col" style="width:160px">Status</th>
              <th scope="col" style="width:220px">Ubah Status</th>
            </tr>
          </thead>
          <tbody>
            <?php if (!empty($pesanan)): ?>
              <?php foreach ($pesanan as $row): 
                $qty   = (int)($row['jumlah_produk'] ?? 0);
                $harga = (float)($row['harga_produk'] ?? 0);
                $total = $qty * $harga;
                $curr  = (string)($row['status_pemesanan'] ?? '');
              ?>
                <tr>
                  <td><?= esc(date('d M Y H:i', strtotime($row['created_at'] ?? 'now'))) ?></td>
                  <td class="text-start"><?= esc($row['nama_user'] ?? '-') ?></td>
                  <td class="text-start"><?= esc($row['nama_produk'] ?? '-') ?></td>
                  <td class="text-center"><?= $qty ?></td>
                  <td>Rp <?= number_format($total, 0, ',', '.') ?></td>
                  <td>
                    <span class="badge <?= status_badge_theme($curr) ?>">
                      <?= esc(display_label($curr)) ?>
                    </span>
                  </td>
                  <td>
                    <form action="<?= site_url('mengelolariwayatpesanan/updateStatus/' . $row['id_pemesanan']) ?>" method="post" aria-label="Ubah status pesanan <?= esc($row['nama_produk'] ?? '') ?>">
                      <select name="status_pemesanan" class="form-select form-select-sm" onchange="this.form.submit()">
                        <?php foreach ($opsi as $st): ?>
                          <option value="<?= $st ?>" <?= ($curr===$st)?'selected':''; ?>><?= $st ?></option>
                        <?php endforeach; ?>
                      </select>
                    </form>
                  </td>
                </tr>
              <?php endforeach; ?>
            <?php else: ?>
              <tr><td colspan="7" class="text-center text-muted p-4" tabindex="0">Belum ada data pesanan</td></tr>
            <?php endif; ?>
          </tbody>
        </table>
      </div>

    </section>
  </main>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
