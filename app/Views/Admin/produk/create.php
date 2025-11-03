<!DOCTYPE html>
<html lang="id">
  <head>
    <meta charset="utf-8"/>
    <meta name="viewport" content="width=device-width, initial-scale=1"/>
    <title>Tambah Produk - Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
    <style>
      :root{ --brand:#198754; --brand-dark:#145c32; --muted:#f8f9fa; }
      body {
        background: linear-gradient(135deg, #e6f4ea, #c0e0cc);
        font-family: "Segoe UI", Tahoma, Geneva, Verdana, sans-serif;
        min-height: 100vh;
        margin: 0;
      }
      .main-content{ margin-left:250px; padding:28px; min-height:100vh; }
      .card.form-card{ border:none; border-radius:12px; box-shadow:0 8px 24px rgba(6,50,20,.06); overflow:hidden; }
      .form-header{
        background:linear-gradient(135deg,var(--brand),#20c997); color:#fff;
        padding:16px 20px; display:flex; align-items:center; gap:10px; font-weight:700;
      }
      .form-table th{
        width:28%; background:#f6fff8; color:#145c32; font-weight:600; vertical-align:middle;
        border-top:0; padding:16px;
      }
      .form-table td{ background:#fff; vertical-align:middle; padding:14px 18px; border-top:0; }
      .form-table .form-control, .form-table .form-select{
        border-radius:10px; border:1px solid #e6efe9; padding:10px 12px; box-shadow:none;
      }
      .img-preview{ width:140px; height:100px; object-fit:cover; border-radius:10px; border:1px solid #e9f4ee; }
      .btn-save{ background:var(--brand); border:0; box-shadow:0 6px 18px rgba(25,135,84,.12); }
      .btn-save:hover{ background:var(--brand-dark); transform:translateY(-2px); }
      .btn{ border-radius:999px; }
      @media (max-width:992px){ .main-content{ margin-left:0; padding:18px; } }
    </style>
  </head>

  <body>
    <?= $this->include('layout/sidebarAdmin') ?>

    <div class="main-content">
      <div class="mb-3 d-flex justify-content-between align-items-center">
        <h3 class="mb-0 text-success fw-bold"><i class="bi bi-plus-circle me-2"></i>Tambah Produk</h3>
        <a href="<?= base_url('admin/produk') ?>" class="btn btn-outline-secondary rounded-pill">Kembali ke Daftar</a>
      </div>

      <?php if (session()->getFlashdata('success')): ?>
        <div class="alert alert-success rounded-3"><?= session()->getFlashdata('success') ?></div>
      <?php endif; ?>

      <div class="card form-card">
        <div class="form-header"><i class="bi bi-box-seam"></i> Detail Produk Baru</div>

        <form action="<?= base_url('admin/produk/store') ?>" method="post" enctype="multipart/form-data">
          <table class="table form-table mb-0">
            <tbody>
              <tr>
                <th>Nama Produk <span class="text-danger">*</span></th>
                <td><input type="text" name="nama_produk" class="form-control" placeholder="Contoh: Telur Organik 1 Kg" required></td>
              </tr>

              <tr>
                <th>Deskripsi</th>
                <td><textarea name="deskripsi" rows="4" class="form-control" placeholder="Deskripsi singkat produk…"></textarea></td>
              </tr>

              <tr>
                <th>Foto Produk</th>
                <td>
                  <div class="d-flex gap-3 align-items-center">
                    <img src="<?= base_url('uploads/produk/default.png') ?>" id="imgPreview" class="img-preview" alt="Preview">
                    <div class="flex-grow-1">
                      <input type="file" name="foto" id="fotoInput" class="form-control" accept="image/*">
                      <div class="form-text">Format gambar (jpg/png), maks ±2MB (sesuaikan konfigurasi server).</div>
                    </div>
                  </div>
                </td>
              </tr>

              <tr>
                <th>Harga (Rp)</th>
                <td><input type="number" min="0" name="harga" class="form-control" placeholder="cth: 10000" required></td>
              </tr>

              <tr>
                <th>Stok</th>
                <td><input type="number" min="0" name="stok" class="form-control" value="0" required></td>
              </tr>

              <!-- Kategori (DI DALAM TABEL, DI BAWAH STOK) -->
              <tr>
                <th>Kategori</th>
                <td>
                  <select name="kategori" class="form-select">
                    <option value="">-- Pilih kategori (opsional) --</option>
                    <option value="Makanan" <?= old('kategori')==='Makanan' ? 'selected' : '' ?>>Makanan</option>
                    <option value="Minuman" <?= old('kategori')==='Minuman' ? 'selected' : '' ?>>Minuman</option>
                    <option value="Lainnya" <?= old('kategori')==='Lainnya' ? 'selected' : '' ?>>Lainnya</option>
                  </select>
                </td>
              </tr>

              <tr>
                <th></th>
                <td class="pt-3">
                  <button type="submit" class="btn btn-save btn-lg text-white me-2"><i class="bi bi-save me-2"></i>Simpan Produk</button>
                  <a href="<?= base_url('admin/produk') ?>" class="btn btn-outline-secondary rounded-pill">Batal</a>
                </td>
              </tr>
            </tbody>
          </table>
        </form>
      </div>
    </div>

    <script>
      const fotoInput = document.getElementById('fotoInput');
      const imgPreview = document.getElementById('imgPreview');
      if (fotoInput) {
        fotoInput.addEventListener('change', (e) => {
          const file = e.target.files?.[0]; if(!file) return;
          const reader = new FileReader();
          reader.onload = (ev) => { imgPreview.src = ev.target.result; };
          reader.readAsDataURL(file);
        });
      }
    </script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
  </body>
</html>
