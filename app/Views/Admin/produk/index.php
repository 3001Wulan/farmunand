<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?= esc($title) ?> - Admin</title>
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
    }
    .page-header h6 {
      margin: 0;
      font-weight: 700;
      font-size: 20px;
      display: flex;
      align-items: center;
      gap: 10px;
    }
    .page-header .btn {
      background: #fff;
      color: var(--brand);
      border: none;
      font-weight: 600;
      border-radius: 8px;
    }
    .page-header .btn:hover {
      opacity: .9;
      transform: translateY(-2px);
      transition: 0.2s;
    }

    /* === KONSEP BARU: KONTEN CARD === */
    .card-container {
      background: #fff;
      border-radius: 0 0 12px 12px; /* Rounded bottom */
      box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
      padding: 20px 30px;
    }

    /* === Table Styling (dari konsep baru) === */
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
    }
    .table tbody tr:hover {
      background: #f1fdf6;
      cursor: pointer;
    }
    .thumb {
      width: 64px;
      height: 64px;
      object-fit: cover;
      border-radius: 8px;
      border: 2px solid #e9ecef;
    }

    /* === Badge Styling (dari konsep baru) === */
    .badge {
      font-size: 13px;
      padding: 6px 12px;
      border-radius: 12px;
      font-weight: 600;
    }
    .badge.bg-success { background: #198754 !important; }
    .badge.bg-secondary { background: #6c757d !important; }

    /* === Tombol Aksi Styling (dari konsep baru) === */
    .btn-action {
      padding: 6px 14px;
      border-radius: 8px;
      font-size: 14px;
      font-weight: 500;
      transition: 0.2s;
      display: inline-flex;
      align-items: center;
      gap: 6px;
      border: none;
    }
    .btn-action.btn-warning {
      background: #ffc107;
      color: #333;
    }
    .btn-action.btn-warning:hover {
      background: #e0a800;
      color: white;
      transform: translateY(-2px);
    }
    .btn-action.btn-danger {
      background: #dc3545;
      color: white;
    }
    .btn-action.btn-danger:hover {
      background: #a71d2a;
      transform: translateY(-2px);
    }

    /* === Filter Styling (dipertahankan dari kode lama) === */
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
    .btn-rect:hover {
      background: #157347;
    }

    /* === Modal Styling (dari konsep baru) === */
    .modal-content {
      border-radius: 15px;
      border: 3px solid #198754;
      box-shadow: 0 8px 20px rgba(25, 135, 84, 0.4);
    }
    .modal-header {
      background: linear-gradient(135deg, #198754, #28a745);
      color: white;
      border-bottom: none;
      border-radius: 15px 15px 0 0;
    }
    .modal-header .btn-close {
      filter: brightness(0) invert(1);
    }
    .modal-body {
      font-size: 1rem;
      color: #333;
      padding: 1.5rem 2rem;
      text-align: center;
    }
    .modal-footer {
      border-top: none;
      justify-content: center;
    }
    .modal-footer .btn-danger {
      font-weight: 600;
      border-radius: 8px;
      padding: 0.6rem 1.4rem;
    }
    .modal-footer .btn-secondary {
      border-radius: 8px;
      padding: 0.6rem 1.4rem;
    }

    /* === Media Query (dipertahankan) === */
    @media (max-width: 768px) {
      .content {
        margin-left: 0;
        padding: 20px;
      }
      .page-header {
        flex-direction: column;
        gap: 12px;
        align-items: stretch;
      }
      .page-header .btn {
        width: 100%;
      }
      .card-container {
        padding: 15px;
      }
    }
  </style>
</head>

<body>
  <?= $this->include('layout/sidebarAdmin') ?>

  <main class="content" role="main">
    
    <header class="page-header">
      <h6 class="m-0 fw-bold">📦 <?= esc($title) ?></h6>
      <a href="<?= base_url('admin/produk/create') ?>" class="btn btn-sm fw-semibold" aria-label="Tambah Produk Baru">
        <i class="bi bi-plus-circle"></i> Tambah Produk
      </a>
    </header>

    <section class="card-container" aria-label="Daftar Produk">
      <?php if (session()->getFlashdata('success')): ?>
        <div class="alert alert-success py-2 mb-3" role="alert">
          <?= session()->getFlashdata('success') ?>
        </div>
      <?php endif; ?>

      <form method="get" action="<?= base_url('admin/produk') ?>" class="row g-2 mb-3" role="search" aria-label="Filter Produk">
        <div class="col-md-7">
          <input type="search" name="keyword"
                value="<?= esc($keyword ?? '') ?>"
                class="form-control filter-rect"
                placeholder="Cari produk…"
                aria-label="Cari produk">
        </div>
        <div class="col-md-3">
          <select name="kategori" class="form-select filter-rect" aria-label="Filter berdasarkan kategori">
            <option value="">Semua Kategori</option>
            <?php foreach (($kategoriList ?? []) as $k): ?>
              <option value="<?= esc($k) ?>" <?= (!empty($kategori) && $kategori === $k) ? 'selected' : '' ?>>
                <?= esc($k) ?>
              </option>
            <?php endforeach; ?>
          </select>
        </div>
        <div class="col-md-2 d-grid">
          <button type="submit" class="btn btn-rect fw-semibold">Filter</button>
        </div>
      </form>

      <div class="table-responsive" tabindex="0">
        <table class="table table-hover align-middle" aria-describedby="produkTableDesc">
          <caption id="produkTableDesc" class="visually-hidden">Daftar produk pada sistem admin</caption>
          <thead>
            <tr>
              <th scope="col">No</th>
              <th scope="col">Foto</th>
              <th scope="col">Nama Produk</th>
              <th scope="col">Harga</th>
              <th scope="col">Stok</th>
              <th scope="col" style="width:190px">Aksi</th>
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
                  <td><span class="badge bg-success">Rp <?= number_format($p['harga'],0,',','.') ?></span></td>
                  <td><span class="badge bg-secondary"><?= (int)$p['stok'] ?></span></td>


                  <td class="text-center"> 
                    <a href="<?= base_url('admin/produk/edit/'.$p['id_produk']) ?>"
                      class="btn btn-sm btn-warning d-inline-flex align-items-center gap-1"
                      aria-label="Edit produk <?= esc($p['nama_produk']) ?>">
                      <i class="bi bi-pencil" aria-hidden="true"></i> Edit
                    </a>
                    <button type="button" class="btn btn-sm btn-danger d-inline-flex align-items-center gap-1 btn-delete-product"
                            data-productid="<?= $p['id_produk'] ?>"
                            data-productname="<?= esc($p['nama_produk']) ?>"
                            aria-label="Hapus produk <?= esc($p['nama_produk']) ?>">
                      <i class="bi bi-trash" aria-hidden="true"></i> Hapus
                    </button>
                  </td>
                </tr>
              <?php endforeach; ?>
            <?php else: ?>
              <tr>
                <td colspan="6" class="text-center text-muted p-4" tabindex="0">⚠️ Tidak ada produk ditemukan.</td>
              </tr>
            <?php endif; ?>
          </tbody>
        </table>
      </div>
    </section>
  </main>

  <div class="modal fade" id="deleteProductModal" tabindex="-1" aria-labelledby="deleteProductModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
      <div class="modal-content">
        <form id="form-delete-product" method="post" action="">
          <?= csrf_field() ?>
          <div class="modal-header">
            <h5 class="modal-title" id="deleteProductModalLabel">Konfirmasi Hapus Produk</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
          </div>
          <div class="modal-body">
            Apakah Anda yakin ingin menghapus produk <br>
            <strong id="modalProductName" class="text-danger"></strong>?
            <div class="alert alert-warning py-2 mt-3 mb-0">
              Tindakan ini tidak dapat dibatalkan.
            </div>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
            <button type="submit" class="btn btn-danger">Ya, Hapus</button>
          </div>
        </form>
      </div>
    </div>
  </div>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
  <script>
    document.addEventListener('DOMContentLoaded', () => {
      const deleteModal = new bootstrap.Modal(document.getElementById('deleteProductModal'));
      
      document.querySelectorAll('.btn-delete-product').forEach(button => {
        button.addEventListener('click', () => {
          const productId = button.getAttribute('data-productid');
          const productName = button.getAttribute('data-productname');
          
          const modalForm = document.getElementById('form-delete-product');
          const modalProductName = document.getElementById('modalProductName');

          // Set nama produk di modal body
          modalProductName.textContent = productName;
          
          // Set URL action form
          modalForm.action = '<?= base_url('admin/produk/delete/') ?>' + productId;

          deleteModal.show();
        });
      });
    });
  </script>
</body>
</html>