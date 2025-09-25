<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Pilih Alamat - FarmUnand</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
<style>
    body {
        background-color: #f8f9fa;
        font-family: 'Segoe UI', sans-serif;
        margin: 0;
        padding: 0;
    }

    .main-content {
        margin-left: 250px;
        padding: 30px;
    }

    .address-card {
        border: 1px solid #dee2e6;
        border-radius: 8px;
        padding: 15px;
        margin-bottom: 15px;
        background: #fff;
        transition: all 0.2s ease;
        position: relative;
    }
    .address-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 6px 15px rgba(0,0,0,0.1);
    }

    .address-card .badge {
        font-size: 12px;
    }

    .btn-submit, .btn-success {
        font-weight: 600;
    }

    .active-badge {
        position: absolute;
        top: 10px;
        right: 10px;
    }
</style>
</head>
<body>

<div class="container-fluid px-0">
    <div class="row g-0">

        <!-- Sidebar -->
        <div class="col-md-3 col-lg-2">
            <?= $this->include('layout/sidebar') ?>
        </div>

        <!-- Main Content -->
        <div class="col-md-9 col-lg-10 main-content">
            <h4 class="mb-3">Alamat Pengiriman</h4>

            <!-- Tombol Tambah Alamat -->
            <button type="button" class="btn btn-sm btn-success mb-3" data-bs-toggle="modal" data-bs-target="#tambahAlamatModal">
                + Tambah Alamat
            </button>

            <!-- Daftar Alamat -->
            <?php if(!empty($alamat)): ?>
                <?php foreach($alamat as $a): ?>
                    <div class="address-card d-flex justify-content-between align-items-start flex-wrap" data-id="<?= $a['id_alamat'] ?>">
                        <div>
                            <input type="radio" name="alamat" class="form-check-input me-2" value="<?= $a['id_alamat'] ?>" <?= isset($a['aktif']) && $a['aktif'] ? 'checked' : '' ?>>
                            <span class="fw-bold"><?= esc($a['nama_penerima']) ?></span>
                            <span class="text-muted">(<?= esc($a['no_telepon']) ?>)</span>
                            <p class="mb-1"><?= esc($a['kota']) ?>, <?= esc($a['provinsi']) ?></p>
                            <p class="mb-1">Kode Pos: <?= esc($a['kode_pos']) ?></p>
                        </div>
                        <div class="d-flex flex-column align-items-end">
                            <?php if(isset($a['aktif']) && $a['aktif']): ?>
                                <span class="badge bg-success active-badge">Aktif</span>
                            <?php endif; ?>
                            <a href="<?= base_url('/memilihalamat/ubah/' . $a['id_alamat']) ?>" class="btn btn-outline-secondary btn-sm mt-2 mt-md-0">Ubah</a>
                        </div>
                    </div>
                <?php endforeach; ?>

                <!-- Tombol Gunakan Alamat -->
                <button class="btn btn-success mt-3" id="gunakanAlamatBtn">Gunakan Alamat</button>

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
<script>
    const gunakanBtn = document.getElementById('gunakanAlamatBtn');
    gunakanBtn?.addEventListener('click', function() {
        const selected = document.querySelector('input[name="alamat"]:checked');
        if(!selected) {
            alert('Silakan pilih alamat terlebih dahulu.');
            return;
        }

        const alamatId = selected.value;

        // AJAX untuk menandai alamat aktif
        fetch('<?= base_url("memilihalamat/pilih") ?>/' + alamatId, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ id_alamat: alamatId })
        })
        .then(response => response.json())
        .then(data => {
            if(data.success) {
                // Update tampilan badge Aktif
                document.querySelectorAll('.active-badge').forEach(b => b.remove());
                selected.closest('.address-card').querySelector('div.d-flex.flex-column').insertAdjacentHTML('afterbegin','<span class="badge bg-success active-badge">Aktif</span>');
                alert('Alamat berhasil dipilih!');
            } else {
                alert('Gagal menandai alamat aktif.');
            }
        })
        .catch(err => console.error(err));
    });
</script>

</body>
</html>
