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
        border-radius: 10px;
        padding: 20px;
        margin-bottom: 20px;
        background: #fff;
        transition: all 0.3s ease;
        position: relative;
        cursor: pointer;
    }

    .address-card:hover {
        transform: translateY(-4px);
        box-shadow: 0 8px 20px rgba(0,0,0,0.12);
    }

    .address-card.selected {
        border-color: #28a745;
        box-shadow: 0 0 0 3px rgba(40, 167, 69, 0.2);
    }

    .address-card .badge {
        font-size: 12px;
    }

    .active-badge {
        position: absolute;
        top: 15px;
        right: 15px;
    }

    .btn-submit, .btn-success {
        font-weight: 600;
    }

    /* Toast styling di tengah layar */
    #toastContainer {
        position: fixed;
        top: 50%;
        left: 50%;
        transform: translate(-50%, -50%);
        z-index: 1080;
        display: flex;
        flex-direction: column;
        align-items: center;
    }

    .toast {
        min-width: 250px;
        max-width: 350px;
        border-radius: 12px;
        padding: 15px 20px;
        font-weight: 500;
        font-size: 14px;
        text-align: center;
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

            <!-- Tombol Gunakan Alamat di atas daftar -->
            <?php if(!empty($alamat)): ?>
                <button class="btn btn-success mb-3" id="gunakanAlamatBtn">Gunakan Alamat</button>
            <?php endif; ?>

            <!-- Daftar Alamat -->
            <?php if(!empty($alamat)): ?>
                <?php foreach($alamat as $a): ?>
                    <div class="address-card d-flex justify-content-between align-items-start flex-wrap <?= isset($a['aktif']) && $a['aktif'] ? 'selected' : '' ?>" data-id="<?= $a['id_alamat'] ?>">
                        <div onclick="this.closest('.address-card').querySelector('input[type=radio]').click()">
                            <input type="radio" name="alamat" class="form-check-input me-2" value="<?= $a['id_alamat'] ?>" <?= isset($a['aktif']) && $a['aktif'] ? 'checked' : '' ?>>
                            <span class="fw-bold"><?= esc($a['nama_penerima']) ?></span>
                            <span class="text-muted">(<?= esc($a['no_telepon']) ?>)</span>
                            <p class="mb-1"><?= esc($a['jalan']) ?>, <?= esc($a['kota']) ?>, <?= esc($a['provinsi']) ?></p>
                            <p class="mb-1">Kode Pos: <?= esc($a['kode_pos']) ?></p>
                        </div>
                        <div class="d-flex flex-column align-items-end">
                            <?php if(isset($a['aktif']) && $a['aktif']): ?>
                                <span class="badge bg-success active-badge">Aktif</span>
                            <?php endif; ?>
                            <button class="btn btn-outline-secondary btn-sm mt-2 mt-md-0 ubahAlamatBtn"
                                    data-id="<?= $a['id_alamat'] ?>"
                                    data-nama="<?= esc($a['nama_penerima']) ?>"
                                    data-jalan="<?= esc($a['jalan']) ?>"
                                    data-no="<?= esc($a['no_telepon']) ?>"
                                    data-kota="<?= esc($a['kota']) ?>"
                                    data-provinsi="<?= esc($a['provinsi']) ?>"
                                    data-kodepos="<?= esc($a['kode_pos']) ?>"
                                    data-bs-toggle="modal"
                                    data-bs-target="#ubahAlamatModal">
                                Ubah
                            </button>
                        </div>
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
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form action="<?= base_url('/memilihalamat/tambah') ?>" method="post">
                <div class="modal-header">
                    <h5 class="modal-title" id="tambahAlamatLabel">Tambah Alamat</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Nama Penerima</label>
                            <input type="text" class="form-control" name="nama_penerima" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">No. Telepon</label>
                            <input type="text" class="form-control" name="no_telepon" required>
                        </div>
                        <div class="col-12">
                            <label class="form-label">Jalan</label>
                            <input type="text" class="form-control" name="jalan" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Kota</label>
                            <input type="text" class="form-control" name="kota" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Provinsi</label>
                            <input type="text" class="form-control" name="provinsi" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Kode Pos</label>
                            <input type="text" class="form-control" name="kode_pos" required>
                        </div>
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

<!-- Modal Ubah Alamat -->
<div class="modal fade" id="ubahAlamatModal" tabindex="-1" aria-labelledby="ubahAlamatLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form id="formUbahAlamat">
                <div class="modal-header">
                    <h5 class="modal-title" id="ubahAlamatLabel">Ubah Alamat</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="id_alamat" id="ubah_id_alamat">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Nama Penerima</label>
                            <input type="text" class="form-control" name="nama_penerima" id="ubah_nama_penerima" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">No. Telepon</label>
                            <input type="text" class="form-control" name="no_telepon" id="ubah_no_telepon" required>
                        </div>
                        <div class="col-12">
                            <label class="form-label">Jalan</label>
                            <input type="text" class="form-control" name="jalan" id="ubah_jalan" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Kota</label>
                            <input type="text" class="form-control" name="kota" id="ubah_kota" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Provinsi</label>
                            <input type="text" class="form-control" name="provinsi" id="ubah_provinsi" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Kode Pos</label>
                            <input type="text" class="form-control" name="kode_pos" id="ubah_kode_pos" required>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-success">Simpan Perubahan</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Toast Container -->
<div id="toastContainer"></div>

<!-- CSRF Token -->
<script>
const csrfName = '<?= csrf_token() ?>';
const csrfHash = '<?= csrf_hash() ?>';
let originalData = {};
</script>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script>
// Toast
function showToast(message, type='success') {
    const toastId = 'toast' + Date.now();
    const toastHtml = `
    <div id="${toastId}" class="toast align-items-center text-bg-${type} border-0 mb-2" role="alert" aria-live="assertive" aria-atomic="true">
        <div class="d-flex">
            <div class="toast-body">${message}</div>
            <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
        </div>
    </div>`;
    document.getElementById('toastContainer').insertAdjacentHTML('beforeend', toastHtml);
    const toastEl = document.getElementById(toastId);
    new bootstrap.Toast(toastEl, { delay: 3000 }).show();
}

// Gunakan Alamat
document.getElementById('gunakanAlamatBtn')?.addEventListener('click', function() {
    const selected = document.querySelector('input[name="alamat"]:checked');
    if(!selected){ showToast('Silakan pilih alamat terlebih dahulu.', 'danger'); return; }
    const alamatId = selected.value;

    fetch('<?= base_url("memilihalamat/pilih") ?>/' + alamatId, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ [csrfName]: csrfHash })
    })
    .then(res => res.json())
    .then(data => {
        if(data.success){
            document.querySelectorAll('.active-badge').forEach(b => b.remove());
            document.querySelectorAll('.address-card').forEach(c => c.classList.remove('selected'));
            const card = selected.closest('.address-card');
            card.classList.add('selected');
            const rightDiv = card.querySelector('div.d-flex.flex-column');
            rightDiv.insertAdjacentHTML('afterbegin','<span class="badge bg-success active-badge">Aktif</span>');
            showToast('Alamat berhasil dipilih!');

            // Redirect ke halaman melakukanpemesanan
            setTimeout(()=>{ window.location.href='<?= base_url("melakukanpemesanan") ?>'; }, 1200);
        } else showToast(data.message || 'Gagal menandai alamat aktif.', 'danger');
    }).catch(err => { console.error(err); showToast('Terjadi kesalahan saat memilih alamat.', 'danger'); });
});

// Modal ubah alamat
document.querySelectorAll('.ubahAlamatBtn').forEach(btn => {
    btn.addEventListener('click', function() {
        const id = this.dataset.id;
        originalData[id] = {
            nama_penerima: this.dataset.nama,
            jalan: this.dataset.jalan,
            no_telepon: this.dataset.no,
            kota: this.dataset.kota,
            provinsi: this.dataset.provinsi,
            kode_pos: this.dataset.kodepos
        };
        document.getElementById('ubah_id_alamat').value = id;
        document.getElementById('ubah_nama_penerima').value = this.dataset.nama;
        document.getElementById('ubah_jalan').value = this.dataset.jalan;
        document.getElementById('ubah_no_telepon').value = this.dataset.no;
        document.getElementById('ubah_kota').value = this.dataset.kota;
        document.getElementById('ubah_provinsi').value = this.dataset.provinsi;
        document.getElementById('ubah_kode_pos').value = this.dataset.kodepos;
    });
});

// Submit ubah alamat via AJAX
document.getElementById('formUbahAlamat').addEventListener('submit', function(e){
    e.preventDefault();
    const id = document.getElementById('ubah_id_alamat').value;
    const changedData = {};
    ['nama_penerima','jalan','no_telepon','kota','provinsi','kode_pos'].forEach(field => {
        const input = document.getElementById(`ubah_${field}`);
        if(input.value !== originalData[id][field]) changedData[field] = input.value;
    });

    if(Object.keys(changedData).length === 0){ showToast('Tidak ada perubahan pada alamat.', 'info'); return; }
    changedData[csrfName] = csrfHash;

    fetch('<?= base_url("memilihalamat/ubah") ?>/' + id, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(changedData)
    })
    .then(res => res.json())
    .then(res => {
        if(res.success){ showToast(res.message); setTimeout(()=>location.reload(), 1200); }
        else showToast(res.message || 'Gagal mengubah alamat.', 'danger');
    })
    .catch(err => console.error(err));
});
</script>

</body>
</html>
