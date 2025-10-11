<!DOCTYPE html>
<html lang="id">
  <head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= esc($title) ?> - Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
    <style>
      :root{ --brand:#198754; --brand2:#28a745; --muted:#f8f9fa; }
      html,body{height:100%;background:var(--muted)}
      .content{margin-left:250px;padding:28px}
      .page-header{
        background:linear-gradient(135deg,var(--brand),var(--brand2));
        color:#fff;border-radius:12px;padding:16px 18px;
        box-shadow:0 6px 14px rgba(0,0,0,.08);margin-bottom:16px;
        display:flex;justify-content:space-between;align-items:center
      }
      .page-header .btn{background:#fff;color:var(--brand);border:none}
      .page-header .btn:hover{opacity:.9}
      .card-wrap{background:#fff;border-radius:12px;box-shadow:0 6px 18px rgba(0,0,0,.06);padding:16px}
      .table thead th{background:var(--brand);color:#fff;border:none;vertical-align:middle;text-align:center}
      .table td,.table th{vertical-align:middle;text-align:center}
      .table tbody tr:hover{background:#f1fdf6}
      .thumb{width:64px;height:64px;object-fit:cover;border-radius:8px;border:2px solid #e9ecef}
      .badge-pill{border-radius:999px}
      .btn-success{background:var(--brand);border:none}
      .btn-success:hover{background:#145c32}
      .filter-rect{
        border-radius: 12px !important;
        height: 44px;                
        padding-inline: 14px;          
      }
      .filter-rect.form-select{
        padding-top: .45rem;
        padding-bottom: .45rem;
      }
      .btn-rect{
        border-radius: 12px !important;
        height: 44px;
        background: #198754;   
        border: none;
        color: #fff;
      }
      .btn-rect:hover{ background:#157347; }
    </style>
  </head>

  <body>
    <!-- Sidebar -->
    <?= $this->include('layout/sidebarAdmin') ?>

    <!-- Content -->
    <div class="content">
      <div class="page-header">
        <h6 class="m-0">📦 <?= esc($title) ?></h6>
      <a href="<?= base_url('admin/produk/create') ?>" class="btn btn-sm fw-semibold">
          <i class="bi bi-plus-circle"></i> Tambah Produk
        </a>
      </div>

      <div class="card-wrap">
        <?php if (session()->getFlashdata('success')): ?>
          <div class="alert alert-success py-2 mb-3"><?= session()->getFlashdata('success') ?></div>
        <?php endif; ?>

        <!-- Filter: Cari + Kategori (keduanya PILl) -->
        <form method="get" action="<?= base_url('admin/produk') ?>" class="row g-2 mb-3">
          <div class="col-md-7">
            <input type="text" name="keyword"
                  value="<?= esc($keyword ?? '') ?>"
                  class="form-control filter-rect"
                  placeholder="Cari produk…">
          </div>

          <div class="col-md-3">
            <select name="kategori" class="form-select filter-rect">
              <option value="">Semua Kategori</option>
              <?php foreach (($kategoriList ?? []) as $k): ?>
                <option value="<?= esc($k) ?>" <?= (!empty($kategori) && $kategori === $k) ? 'selected' : '' ?>>
                  <?= esc($k) ?>
                </option>
              <?php endforeach; ?>
            </select>
          </div>

          <div class="col-md-2 d-grid">
            <button type="submit" class="btn btn-rect">Filter</button>
          </div>
        </form>


        <div class="table-responsive">
          <table class="table table-hover align-middle">
            <thead>
              <tr>
                <th>No</th>
                <th>Foto</th>
                <th>Nama Produk</th>
                <th>Harga</th>
                <th>Stok</th>
                <th style="width:190px">Aksi</th>
              </tr>
            </thead>
            <tbody>
              <?php if (!empty($produk)): ?>
                <?php $no=1; foreach($produk as $p): ?>
                  <tr>
                    <td><?= $no++ ?></td>
                    <td>
                      <img class="thumb"
                          src="<?= !empty($p['foto']) ? base_url('uploads/produk/'.$p['foto']) : base_url('uploads/default.png') ?>"
                          alt="<?= esc($p['nama_produk']) ?>">
                    </td>
                    <td><?= esc($p['nama_produk']) ?></td>
                    <td><span class="badge bg-success badge-pill">Rp <?= number_format($p['harga'],0,',','.') ?></span></td>
                    <td><span class="badge bg-secondary badge-pill"><?= (int)$p['stok'] ?></span></td>
                    <td>
                      <a href="<?= base_url('admin/produk/edit/'.$p['id_produk']) ?>"
                        class="btn btn-warning btn-sm rounded-pill me-1">
                        <i class="bi bi-pencil"></i> Edit
                      </a>
                      <button type="button" class="btn btn-danger btn-sm rounded-pill" data-bs-toggle="modal"
                              data-bs-target="#del<?= $p['id_produk'] ?>">
                        <i class="bi bi-trash"></i> Hapus
                      </button>

                      <!-- Modal delete -->
                      <div class="modal fade" id="del<?= $p['id_produk'] ?>" tabindex="-1" aria-hidden="true">
                        <div class="modal-dialog modal-dialog-centered">
                          <div class="modal-content border-0 shadow">
                            <div class="modal-header bg-success text-white">
                              <h6 class="modal-title">Konfirmasi Hapus</h6>
                              <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                            </div>
                            <div class="modal-body text-center">
                              Apakah yakin ingin menghapus <b><?= esc($p['nama_produk']) ?></b>?
                            </div>
                            <div class="modal-footer justify-content-center">
                              <a href="<?= base_url('admin/produk/delete/'.$p['id_produk']) ?>"
                                class="btn btn-danger px-4 rounded-pill">Ya, Hapus</a>
                              <button type="button" class="btn btn-secondary px-4 rounded-pill" data-bs-dismiss="modal">Batal</button>
                            </div>
                          </div>
                        </div>
                      </div>
                    </td>
                  </tr>
                <?php endforeach; ?>
              <?php else: ?>
                <tr><td colspan="6" class="text-center text-muted">⚠️ Tidak ada produk ditemukan.</td></tr>
              <?php endif; ?>
            </tbody>
          </table>
        </div>
      </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
  </body>
</html>
