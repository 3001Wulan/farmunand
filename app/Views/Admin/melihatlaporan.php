<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Laporan Penjualan</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">

  <style>
    body{background:#f8f9fa;font-family:"Segoe UI",Tahoma,Geneva,Verdana,sans-serif;}
    .main-content{margin-left:250px;padding:30px;}
    .page-header{
      background:linear-gradient(135deg,#198754,#28a745);color:#fff;
      border-radius:12px 12px 0 0;padding:20px 30px;
      display:flex;align-items:center;justify-content:space-between;gap:12px;
    }
    .page-header h3{margin:0;font-weight:700;font-size:22px;display:flex;align-items:center;gap:10px;}
    .card-container{background:#fff;border-radius:0 0 12px 12px;box-shadow:0 4px 12px rgba(0,0,0,.08);padding:20px;}
    .filter-card .form-label{font-weight:500;}
    .table thead th{background:#198754;color:#fff;border:none;text-align:center}
    .table td,.table th{vertical-align:middle;text-align:center;padding:12px;}
    .table tbody tr:hover{background:#f1fdf6;}
    .table .col-no{width:70px}
    /* Badge status (konsisten) */
    .status-badge{padding:6px 12px;border-radius:12px;font-size:13px;font-weight:600;}
    .bg-status-secondary{background:#e2e3e5;color:#41464b;} /* Belum Bayar */
    .bg-status-warning{background:#fff3cd;color:#664d03;}  /* Dikemas */
    .bg-status-primary{background:#cfe2ff;color:#084298;}  /* Dikirim */
    .bg-status-success{background:#d1f7e3;color:#198754;}  /* Selesai */
    .bg-status-danger{background:#fde2e4;color:#a71d2a;}   /* Dibatalkan */
    .btn:focus-visible,a:focus-visible,input:focus-visible,select:focus-visible{outline:3px solid #0d6efd;outline-offset:2px;}
  </style>
</head>
<body>

  <!-- Sidebar -->
  <?= $this->include('layout/sidebarAdmin') ?>

  <div class="main-content">
    <!-- Header -->
    <div class="page-header">
      <h3>🧾 Laporan Penjualan</h3>
      <div>
        <a href="<?= base_url('melihatlaporan/exportExcel?start='.urlencode($start ?? '').'&end='.urlencode($end ?? '')) ?>"
           class="btn btn-light btn-sm fw-semibold">
          <i class="bi bi-file-earmark-excel"></i> Export Excel
        </a>
      </div>
    </div>

    <!-- Container -->
    <div class="card-container">

      <?php if (session()->getFlashdata('success')): ?>
        <div class="alert alert-success" role="alert"><?= esc(session()->getFlashdata('success')) ?></div>
      <?php endif; ?>
      <?php if (session()->getFlashdata('error')): ?>
        <div class="alert alert-danger" role="alert"><?= esc(session()->getFlashdata('error')) ?></div>
      <?php endif; ?>

      <!-- Filter -->
      <div class="card mb-3 filter-card">
        <div class="card-body">
          <form class="row g-3" method="get" action="<?= base_url('melihatlaporan') ?>">
            <div class="col-md-3">
              <label for="startDate" class="form-label">Dari Tanggal</label>
              <input type="date" id="startDate" name="start" value="<?= esc($start ?? '') ?>" class="form-control" aria-label="Dari tanggal">
            </div>
            <div class="col-md-3">
              <label for="endDate" class="form-label">Sampai Tanggal</label>
              <input type="date" id="endDate" name="end" value="<?= esc($end ?? '') ?>" class="form-control" aria-label="Sampai tanggal">
            </div>
            <div class="col-md-3">
              <label for="status" class="form-label">Status</label>
              <?php
                $allStatus = ['' => 'Semua Status','Belum Bayar'=>'Belum Bayar','Dikemas'=>'Dikemas','Dikirim'=>'Dikirim','Selesai'=>'Selesai','Dibatalkan'=>'Dibatalkan'];
              ?>
              <select id="status" name="status" class="form-select" aria-label="Pilih status">
                <?php foreach ($allStatus as $val => $label): ?>
                  <option value="<?= esc($val) ?>" <?= (isset($status)&&$status===$val)?'selected':''; ?>><?= esc($label) ?></option>
                <?php endforeach; ?>
              </select>
            </div>
            <div class="col-md-3 d-flex align-items-end">
              <button type="submit" class="btn btn-success w-100">Filter</button>
            </div>
          </form>
        </div>
      </div>

      <!-- Tabel Laporan -->
      <div class="card">
        <div class="card-body">
          <div class="table-responsive">
            <table class="table table-bordered align-middle">
              <thead>
                <tr>
                  <th class="col-no">No</th>
                  <th>Nama Pembeli</th>
                  <th>Produk</th>
                  <th>Tanggal</th>
                  <th>Total</th>
                  <th>Status</th>
                </tr>
              </thead>
              <tbody id="laporanTable">
                <?php if (!empty($laporan)): ?>
                  <?php $no = 1; foreach ($laporan as $row): ?>
                    <?php
                      $tgl   = $row['created_at'] ?? date('Y-m-d');
                      $total = (float)($row['harga_produk'] ?? 0) * (int)($row['jumlah_produk'] ?? 1);
                      $statusVal = (string)($row['status_pemesanan'] ?? '');
                      // mapping badge konsisten
                      $cls = 'bg-status-danger'; $label = $statusVal ?: 'Batal';
                      switch ($statusVal) {
                        case 'Menunggu Pembayaran': $cls='bg-status-secondary'; $label='Belum Bayar'; break;
                        case 'Dikemas':     $cls='bg-status-warning';  $label='Dikemas';     break;
                        case 'Dikirim':     $cls='bg-status-primary';  $label='Dikirim';     break;
                        case 'Selesai':     $cls='bg-status-success';  $label='Selesai';     break;
                        case 'Dibatalkan':  $cls='bg-status-danger';   $label='Dibatalkan';  break;
                      }
                    ?>
                    <tr>
                      <td><?= $no++; ?></td>
                      <td><?= esc($row['nama_pembeli'] ?? 'Tidak ada'); ?></td>
                      <td><?= esc($row['nama_produk'] ?? '-'); ?></td>
                      <td><?= date('d-m-Y', strtotime($tgl)); ?></td>
                      <td>Rp <?= number_format($total, 0, ',', '.'); ?></td>
                      <td><span class="status-badge <?= $cls ?>"><?= esc($label) ?></span></td>
                    </tr>
                  <?php endforeach; ?>
                <?php else: ?>
                  <tr><td colspan="6" class="text-center text-muted">Belum ada data laporan</td></tr>
                <?php endif; ?>
              </tbody>
            </table>
          </div>
        </div>
      </div>

    </div><!-- /.card-container -->
  </div><!-- /.main-content -->
</body>
</html>
