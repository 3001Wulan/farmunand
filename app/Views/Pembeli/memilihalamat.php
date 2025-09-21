<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Pilih Alamat - FarmUnand</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
<style>
body { background-color: #f8f9fa; }
.address-card { border: 1px solid #dee2e6; border-radius: 8px; padding: 15px; margin-bottom: 15px; background: #fff; }
.address-card .badge { font-size: 12px; }
</style>
</head>
<body>
<!-- gunakan px-0 supaya tidak ada padding container -->
<div class="container-fluid px-0">
    <div class="row g-0">

        <!-- Sidebar -->
        <div class="col-md-3 col-lg-2 bg-success text-white d-flex flex-column vh-100 p-0 sidebar">
            <div class="text-center mb-4 p-3">
                <div class="order-img mb-3 mx-auto">
                    <!-- kalau mau ngasih logo, pakai <img> di sini -->
                </div>
                <h5 class="fw-bold">Farm Unand</h5>
            </div>
            <div class="d-grid gap-2 px-3 mb-3">
                <a href="/dashboarduser" class="btn btn-light">Dashboard</a>
                <a href="/akun" class="btn btn-light">Akun Saya</a>
                <a href="/riwayatpesanan" class="btn btn-dark text-white">Pesanan Saya</a>
            </div>
        </div>

        <!-- Main Content -->
        <div class="col-md-9 col-lg-10 p-4">
            <h4 class="mb-3">Alamat</h4>

            <!-- Tombol Tambah Alamat (buka modal) -->
            <button type="button" class="btn btn-sm btn-success mb-3" data-bs-toggle="modal" data-bs-target="#tambahAlamatModal">
                + Tambah Alamat
            </button>

            <!-- Daftar Alamat -->
            <?php if(!empty($alamat)): ?>
                <?php foreach($alamat as $a): ?>
                <div class="address-card d-flex justify-content-between align-items-start">
                    <div>
                        <input type="radio" name="alamat" class="form-check-input me-2">
                        <span class="fw-bold"><?= esc($a['nama_penerima']) ?></span>
                        <span class="text-muted">(<?= esc($a['no_telepon']) ?>)</span>
                        <p class="mb-1"><?= esc($a['kota']) ?>, <?= esc($a['provinsi']) ?></p>
                        <p class="mb-1">Kode Pos: <?= esc($a['kode_pos']) ?></p>
                    </div>
                    <a href="<?= base_url('/memilihalamat/ubah/' . $a['id_alamat']) ?>" class="btn btn-outline-secondary btn-sm">Ubah</a>
                </div>
                <?php endforeach; ?>
            <?php else: ?>
                <p class="text-muted">Belum ada alamat. Silakan tambahkan alamat baru.</p>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Modal Tambah Alamat -->
<div class="modal fade" id="tambahAlamatModal" tabindex="-1" aria-labelledby="tambahAlamatLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <form action="<?= base_url('/memilihalamat/tambah') ?>" method="post">
        <div class="modal-header">
          <h5 class="modal-title" id="tambahAlamatLabel">Tambah Alamat</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
            <div class="mb-3">
                <label for="nama_penerima" class="form-label">Nama Penerima</label>
                <input type="text" class="form-control" name="nama_penerima" required>
            </div>
            <div class="mb-3">
                <label for="no_telepon" class="form-label">No. Telepon</label>
                <input type="text" class="form-control" name="no_telepon" required>
            </div>
            <div class="mb-3">
                <label for="kota" class="form-label">Kota</label>
                <input type="text" class="form-control" name="kota" required>
            </div>
            <div class="mb-3">
                <label for="provinsi" class="form-label">Provinsi</label>
                <input type="text" class="form-control" name="provinsi" required>
            </div>
            <div class="mb-3">
                <label for="kode_pos" class="form-label">Kode Pos</label>
                <input type="text" class="form-control" name="kode_pos" required>
            </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
          <button type="submit" class="btn btn-success">Simpan</button>
        </div>
      </form>
    </div>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
