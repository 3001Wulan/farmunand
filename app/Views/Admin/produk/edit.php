<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Edit Produk - Admin</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
  <style>
    :root{ --brand:#198754; --brand-dark:#145c32; --muted:#f8f9fa; }
    body{ background:var(--muted) }
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
    <div class="card form-card">
      <div class="form-header"><i class="bi bi-pencil-square"></i> Edit Produk</div>

      <form action="<?= base_url('admin/produk/update/'.$produk['id_produk']) ?>" method="post" enctype="multipart/form-data">
        <table class="table form-table mb-0">
          <tbody>
            <tr>
              <th>Nama Produk <span class="text-danger">*</span></th>
              <td><input type="text" name="nama_produk" class="form-control" value="<?= esc($produk['nama_produk']) ?>" required></td>
            </tr>

            <tr>
              <th>Deskripsi</th>
              <td><textarea name="deskripsi" class="form-control" rows="4"><?= esc($produk['deskripsi']) ?></textarea></td>
            </tr>

            <tr>
              <th>Foto Produk</th>
              <td>
                <div class="d-flex gap-3 align-items-center">
                  <img src="<?= base_url('uploads/produk/'.$produk['foto']) ?>" id="imgPreview" class="img-preview" alt="Foto saat ini">
                  <div class="flex-grow-1">
                    <input type="file" name="foto" id="fotoInput" class="form-control mt-2" accept="image/*">
                    <div class="form-text">Kosongkan jika tidak ingin mengganti.</div>
                  </div>
                </div>
              </td>
            </tr>

            <tr>
              <th>Harga (Rp)</th>
              <td><input type="number" min="0" name="harga" class="form-control" value="<?= esc($produk['harga']) ?>" required></td>
            </tr>

            <tr>
              <th>Stok</th>
              <td><input type="number" min="0" name="stok" class="form-control" value="<?= esc($produk['stok']) ?>" required></td>
            </tr>

            <tr>
              <th></th>
              <td class="pt-3">
                <button type="submit" class="btn btn-save text-white me-2"><i class="bi bi-save me-2"></i> Update</button>
                <a href="<?= base_url('admin/produk') ?>" class="btn btn-outline-secondary rounded-pill">Kembali</a>
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
