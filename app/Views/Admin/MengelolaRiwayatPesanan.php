<!DOCTYPE html>
<html lang="id">
  <head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mengelola Riwayat Pesanan</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
      :root{--brand:#198754;--muted:#f8f9fa;
      }
      html,body{height:100%;background:var(--muted);
        font-family:"Segoe UI",Tahoma,Geneva,Verdana,sans-serif;
        scroll-behavior:smooth;
      }
      .content{margin-left:250px;padding:28px;
      }
      .page-header{
        background:linear-gradient(135deg,#198754,#28a745);
        color:#fff;border-radius:12px;padding:16px 18px;
        box-shadow:0 6px 14px rgba(0,0,0,.08);margin-bottom:18px;
        display:flex;justify-content:space-between;align-items:center
      }
      .card-container{background:#fff;border-radius:12px;box-shadow:0 6px 18px rgba(0,0,0,.06);padding:16px}
      .filter-form .form-control,.filter-form .form-select{border-radius:10px}
      .table{background:#fff}
      .table thead th{background:#198754;color:#fff;vertical-align:middle}
      .table td,.table th{vertical-align:middle}
      .badge-soft{background:#eafaf0;color:#198754;border:1px solid #cfe9db}
      @media(max-width:992px){.content{margin-left:0;padding:18px}}
    </style>
  </head>

  <body>
    <div class="container-fluid px-0">
      <div class="row g-0">
        <!-- Sidebar -->
        <?= $this->include('layout/sidebarAdmin') ?>

        <!-- Content -->
        <div class="col content">
          <div class="page-header">
            <h5 class="m-0">📜 Mengelola Riwayat Pesanan</h5>
            <?php if(session()->getFlashdata('success')): ?>
              <span class="badge badge-soft px-3 py-2"><?= session()->getFlashdata('success') ?></span>
            <?php elseif(session()->getFlashdata('error')): ?>
              <span class="badge bg-danger px-3 py-2"><?= session()->getFlashdata('error') ?></span>
            <?php endif; ?>
          </div>

          <div class="card-container">
            <!-- Filter & Search -->
            <form method="get" class="row g-2 mb-3 filter-form">
              <div class="col-md-5">
                <input type="text" class="form-control" name="keyword" placeholder="Cari nama pelanggan / produk..."
                      value="<?= esc($keyword ?? '') ?>">
              </div>
              <div class="col-md-3">
                <select name="status" class="form-select">
                  <option value="">Semua Status</option>
                  <?php
                    $opsi = ['Dikemas','Dikirim','Selesai','Dibatalkan'];
                    foreach($opsi as $st):
                  ?>
                    <option value="<?= $st ?>" <?= (!empty($status) && $status===$st)?'selected':''; ?>><?= $st ?></option>
                  <?php endforeach; ?>
                </select>
              </div>
              <div class="col-md-2">
                <select name="sort" class="form-select">
                  <option value="desc" <?= (isset($sort)&&$sort==='desc')?'selected':''; ?>>Terbaru</option>
                  <option value="asc"  <?= (isset($sort)&&$sort==='asc') ?'selected':''; ?>>Terlama</option>
                </select>
              </div>
              <div class="col-md-2">
                <button type="submit" class="btn btn-success w-100">Filter</button>
              </div>
            </form>
            
            <?php
            if (!function_exists('status_badge')) {
              function status_badge(?string $s): string {
                switch ($s) {
                  case 'Dikemas':     return 'bg-warning text-dark';
                  case 'Dikirim':     return 'bg-primary';
                  case 'Selesai':     return 'bg-success';
                  case 'Dibatalkan':  return 'bg-danger';
                  default:            return 'bg-light text-dark';
                }
              }
            }
            ?>

            <!-- Tabel -->
            <div class="table-responsive">
              <table class="table table-bordered table-hover text-center align-middle">
                <thead>
                  <tr>
                    <th style="width:160px">Tanggal</th>
                    <th>Nama</th>
                    <th>Produk</th>
                    <th style="width:110px">Qty</th>
                    <th style="width:160px">Total</th>
                    <th style="width:160px">Status</th>
                    <th style="width:220px">Ubah Status</th>
                  </tr>
                </thead>
                <tbody>
                <?php if (!empty($pesanan)): ?>
                  <?php foreach ($pesanan as $row): 
                    $qty   = (int)($row['jumlah_produk'] ?? 0);
                    $harga = (float)($row['harga_produk'] ?? 0);
                    $total = $qty * $harga;
                  ?>
                    <tr>
                      <td><?= esc(date('d M Y H:i', strtotime($row['created_at'] ?? 'now'))) ?></td>
                      <td><?= esc($row['nama_user'] ?? '-') ?></td>
                      <td><?= esc($row['nama_produk'] ?? '-') ?></td>
                      <td class="text-center"><?= $qty ?></td>
                      <td>Rp <?= number_format($total, 0, ',', '.') ?></td>
                      <td>
                        <span class="badge <?= status_badge($row['status_pemesanan'] ?? '') ?>">
                          <?= esc($row['status_pemesanan'] ?? '-') ?>
                        </span>
                      </td>

                      <td>
                        <form action="<?= site_url('mengelolariwayatpesanan/updateStatus/' . $row['id_pemesanan']) ?>" method="post">
                          <select name="status_pemesanan" class="form-select" onchange="this.form.submit()">
                            <?php foreach ($opsi as $st): ?>
                              <option value="<?= $st ?>" <?= (($row['status_pemesanan'] ?? '')===$st)?'selected':''; ?>>
                                <?= $st ?>
                              </option>
                            <?php endforeach; ?>
                          </select>
                        </form>
                      </td>
                    </tr>
                  <?php endforeach; ?>
                <?php else: ?>
                  <tr><td colspan="8" class="text-center text-muted">Belum ada data pesanan</td></tr>
                <?php endif; ?>
                </tbody>
              </table>
            </div>
          </div>
        </div>
      </div>
    </div>
  </body>
</html>
