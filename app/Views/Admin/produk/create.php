<!DOCTYPE html>
<html lang="id">
  <head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Tambah Produk - Admin</title>

    <!-- Bootstrap & Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">

    <style>
      :root {
        --brand: #198754;
        --brand-dark: #157347;
        --muted-bg: #f8f9fa;
      }

      html, body {
        height: 100%;
        margin: 0;
        background: var(--muted-bg);
        font-family: Inter, system-ui, -apple-system, "Segoe UI", Roboto, "Helvetica Neue", Arial;
      }

      /* Sidebar (dipanggil dari layout/sidebarAdmin) diasumsikan fixed left 250px.
        Pastikan konsisten dengan sidebarAdmin (width: 250px). */
      .main-content {
        margin-left: 250px;    /* penting: biar tidak tertutup sidebar */
        padding: 30px;
        min-height: 100vh;
      }

      /* Card utama */
      .card.form-card {
        border-radius: 12px;
        box-shadow: 0 8px 24px rgba(6, 50, 20, 0.06);
        overflow: hidden;
      }

      .form-header {
        background: linear-gradient(90deg, var(--brand), #20c997);
        color: #fff;
        padding: 18px 22px;
        display: flex;
        align-items: center;
        gap: 12px;
        font-weight: 600;
        font-size: 1.15rem;
      }

      /* Table-like form */
      .form-table th {
        width: 28%;
        background: #f6fff8;
        color: var(--brand-dark);
        font-weight: 600;
        vertical-align: middle;
        border-top: 0;
        padding: 18px;
      }
      .form-table td {
        background: #fff;
        vertical-align: middle;
        padding: 12px 18px;
        border-top: 0;
      }
      .form-table input.form-control,
      .form-table textarea.form-control,
      .form-table .form-select {
        border-radius: 8px;
        border: 1px solid #e6efe9;
        padding: 10px 12px;
        box-shadow: none;
      }

      /* Preview image */
      .img-preview {
        width: 140px;
        height: 100px;
        object-fit: cover;
        border-radius: 8px;
        border: 1px solid #e9f4ee;
      }

      /* Tombol */
      .btn-save {
        background: var(--brand);
        border: 0;
        box-shadow: 0 6px 18px rgba(25,135,84,0.12);
      }
      .btn-save:hover { background: var(--brand-dark); transform: translateY(-2px); }

      /* Flash message */
      .alert-custom {
        border-radius: 10px;
        box-shadow: 0 6px 18px rgba(0,0,0,0.04);
      }

      /* Responsive: bila layar kecil, sidebar jadi stacked dan konten tidak diberi margin */
      @media (max-width: 992px) {
        .main-content { margin-left: 0; padding: 18px; }
        /* pastikan sidebarAdmin pada mobile sudah dibuat responsif (pos:relative) */
      }
    </style>
  </head>

  <body>
    <!-- Sidebar -->
    <?= $this->include('layout/sidebarAdmin') ?>

    <!-- Main content -->
    <div class="main-content">

      <!-- Judul -->
      <div class="mb-3 d-flex justify-content-between align-items-center">
        <h3 class="mb-0" style="color:var(--brand); font-weight:700;"><i class="bi bi-plus-circle me-2"></i>Tambah Produk</h3>
        <div>
          <a href="<?= base_url('admin/produk') ?>" class="btn btn-outline-secondary">Kembali ke Daftar</a>
        </div>
      </div>

      <!-- Flash message (jika ada) -->
      <?php if (session()->getFlashdata('success')): ?>
        <div class="alert alert-success alert-custom">
          ✅ <?= session()->getFlashdata('success') ?>
        </div>
      <?php endif; ?>

      <!-- Form card -->
      <div class="card form-card mt-3">
        <div class="form-header">
          <i class="bi bi-box-seam" style="font-size:1.15rem;"></i>
          Detail Produk Baru
        </div>

        <form action="<?= base_url('admin/produk/store') ?>" method="post" enctype="multipart/form-data">
          <table class="table form-table mb-0">
            <tbody>
              <tr>
                <th scope="row">Nama Produk <span class="text-danger">*</span></th>
                <td>
                  <input type="text" name="nama_produk" class="form-control" placeholder="Contoh: Telur Organik 1 Kg" required>
                </td>
              </tr>

              <tr>
                <th scope="row">Deskripsi</th>
                <td>
                  <textarea name="deskripsi" rows="4" class="form-control" placeholder="Deskripsi singkat produk..."></textarea>
                </td>
              </tr>

              <tr>
                <th scope="row">Foto Produk</th>
                <td>
                  <div class="d-flex gap-3 align-items-center">
                    <div>
                      <img src="<?= base_url('uploads/produk/default.png') ?>" id="imgPreview" class="img-preview" alt="Preview">
                    </div>
                    <div style="flex:1;">
                      <input type="file" name="foto" id="fotoInput" class="form-control">
                      <div class="form-text">Format: jpg/png. Maks 2MB (disesuaikan dengan konfigurasi server).</div>
                    </div>
                  </div>
                </td>
              </tr>

              <tr>
                <th scope="row">Harga (Rp)</th>
                <td>
                  <input type="number" min="0" name="harga" class="form-control" placeholder="Harga dalam angka (contoh: 10000)" required>
                </td>
              </tr>

              <tr>
                <th scope="row">Stok</th>
                <td>
                  <input type="number" min="0" name="stok" class="form-control" value="0" required>
                </td>
              </tr>

              <tr>
                <th scope="row">Kategori</th>
                <td>
                  <select name="kategori" class="form-select">
                    <option value="">-- Pilih kategori (opsional) --</option>
                    <option value="makanan">Makanan</option>
                    <option value="minuman">Minuman</option>
                    <option value="lainnya">Lainnya</option>
                  </select>
                </td>
              </tr>

              <!-- aksi -->
              <tr>
                <th scope="row"></th>
                <td class="pt-3">
                  <button type="submit" class="btn btn-save btn-lg text-white me-2"><i class="bi bi-save me-2"></i>Simpan Produk</button>
                  <a href="<?= base_url('admin/produk') ?>" class="btn btn-outline-secondary">Batal</a>
                </td>
              </tr>
            </tbody>
          </table>
        </form>
      </div>

    </div>

    <!-- Preview script -->
    <script>
      const fotoInput = document.getElementById('fotoInput');
      const imgPreview = document.getElementById('imgPreview');

      if (fotoInput) {
        fotoInput.addEventListener('change', (e) => {
          const file = e.target.files[0];
          if (!file) return;
          const reader = new FileReader();
          reader.onload = function(evt) {
            imgPreview.src = evt.target.result;
          };
          reader.readAsDataURL(file);
        });
      }
    </script>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
  </body>
</html>
