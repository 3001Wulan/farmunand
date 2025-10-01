<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0"/>
<title>Berikan Penilaian - FarmUnand</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
<style>
html, body {
    height: 100%;
    margin:0; padding:0;
    background-color:#f8f9fa;
    font-family:'Segoe UI', sans-serif;
}
.main-content {
    margin-left: 250px;
    padding: 30px;
}

/* Tombol Back */
.btn-back {
    background-color: #198754;
    color: #fff;
    font-weight: 500;
    margin-bottom: 20px;
}
.btn-back:hover {
    background-color: #0b5ed7;
    transform: translateY(-2px);
}

/* Card Pesanan */
.card-order-wrapper {
    position: relative;
    margin-bottom: 30px;
}
.card-order-back {
    position: absolute;
    top:6px; left:6px;
    width:100%; height:100%;
    background-color:#e9ecef;
    border-radius:12px;
    z-index:0;
}
.card-order {
    position: relative; 
    z-index:1;
    border-radius:12px;
    border:1px solid #e0e0e0;
    box-shadow:0 4px 12px rgba(0,0,0,0.08);
    transition:all 0.3s ease;
}
.card-order:hover {
    transform: translateY(-5px);
    box-shadow:0 10px 20px rgba(0,0,0,0.15);
}
.order-img img {
    width:100px; height:100px;
    object-fit:cover;
    border-radius:12px;
    border:2px solid #e9ecef;
}

/* Rating Stars */
.stars span {
    font-size:28px;
    color:#ccc;
    cursor:pointer;
    transition:color 0.2s;
}
.stars span:hover, .stars span.active { color:#ffc107; }

/* Button Hijau */
.btn-success {
    background-color:#198754; 
    border:none;
    font-weight:600;
}
.btn-success:hover {
    background-color:#145c32; 
    transform: translateY(-2px);
}

/* Modal */
.modal-content {
    border-radius:12px;
    border:none;
    box-shadow:0 4px 15px rgba(0,0,0,0.2);
}
.modal-header { border-bottom:2px solid #e9ecef; }
.modal-body { max-height:70vh; overflow-y:auto; }
textarea {
    width:100%; min-height:120px;
    border-radius:8px; border:1px solid #ced4da; 
    padding:10px; resize:vertical;
}

/* Modal Sukses */
#successModal .modal-content { text-align:center; padding:30px; }
#successModal svg { margin-bottom:15px; }

@media(max-width:768px){
    .main-content { margin-left: 0; padding:20px; }
    .order-img img { width:70px; height:70px; }
}
</style>
</head>
<body>

<div class="container-fluid px-0">
    <div class="row g-0">
        <!-- Sidebar -->
        <?= $this->include('layout/sidebar') ?>

        <!-- Main Content -->
        <div class="col main-content">
            <a href="<?= base_url('/riwayatpesanan') ?>" class="btn btn-back">&larr; Kembali</a>
            <h4 class="mb-4 text-success fw-bold">Berikan Penilaian</h4>

            <?php if(!empty($pesanan)): ?>
                <?php foreach($pesanan as $p): ?>
                    <div class="card-order-wrapper">
                        <div class="card-order-back"></div>
                        <div class="card card-order p-3">
                            <div class="d-flex justify-content-between align-items-center flex-wrap">
                                <div class="d-flex align-items-center">
                                    <div class="order-img">
                                        <?php if(!empty($p['foto'])): ?>
                                            <img src="<?= base_url('uploads/produk/'.$p['foto']); ?>" alt="<?= esc($p['nama_produk']); ?>">
                                        <?php else: ?>
                                            <span class="text-muted small">No Image</span>
                                        <?php endif; ?>
                                    </div>
                                    <div class="ms-3">
                                        <h6 class="fw-bold mb-1"><?= esc($p['nama_produk']); ?></h6>
                                        <p class="text-muted mb-1">Farm Unand</p>
                                        <p class="mb-0">Jumlah: <?= esc($p['jumlah_produk']); ?></p>
                                    </div>
                                </div>
                                <div class="text-end mt-3 mt-md-0">
                                    <p class="mb-1 text-success fw-semibold"><?= esc($p['status_pemesanan']); ?></p>
                                    <p class="mb-1">Total: <span class="fw-bold text-dark">
                                        Rp <?= number_format($p['harga'] * $p['jumlah_produk'],0,',','.'); ?>
                                    </span></p>
                                    <button class="btn btn-success btn-sm mt-2"
                                        onclick="openReviewModal('<?= esc($p['nama_produk']) ?>', <?= esc($p['id_pemesanan']) ?>)">
                                        Berikan Penilaian
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="alert alert-info">Belum ada pesanan yang bisa dinilai.</div>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Modal Penilaian -->
<div class="modal fade" id="reviewModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable">
    <div class="modal-content p-3">
        <div class="modal-header">
            <h5 class="modal-title">Nilai Produk: <span id="modal-product-name" class="text-success"></span></h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
            <form id="form-penilaian" method="post" enctype="multipart/form-data">
                <?= csrf_field() ?>
                <p class="mb-1 mt-2">Rating (wajib):</p>
                <div class="stars mb-3">
                    <?php for($i=1;$i<=5;$i++): ?>
                        <span data-value="<?= $i ?>">★</span>
                    <?php endfor; ?>
                    <input type="hidden" name="rating" id="rating" value="">
                </div>
                <p class="mb-1">Foto atau Video (opsional, bisa lebih dari 1):</p>
                <input type="file" name="media[]" class="form-control mb-3" accept="image/*,video/*" multiple>
                <p class="mb-1">Tulis Ulasan (opsional):</p>
                <textarea name="ulasan" placeholder="Kualitas produk..."></textarea>
                <button type="submit" class="btn btn-success mt-3 w-100">Kirim Penilaian</button>
            </form>
        </div>
    </div>
  </div>
</div>

<!-- Modal Sukses -->
<div class="modal fade" id="successModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content text-center p-4">
      <div class="modal-body">
        <div class="mb-3">
          <svg xmlns="http://www.w3.org/2000/svg" width="60" height="60" fill="#198754" class="bi bi-check-circle-fill" viewBox="0 0 16 16">
            <path d="M16 8A8 8 0 1 1 0 8a8 8 0 0 1 16 0zM6.97 11.03a.75.75 0 0 0 1.07 0l3.992-3.992a.75.75 0 1 0-1.06-1.06L7.5 9.439 5.06 6.999a.75.75 0 1 0-1.06 1.06l2.97 2.97z"/>
          </svg>
        </div>
        <h5 class="mb-2">Penilaian Berhasil!</h5>
        <p class="text-muted">Terima kasih atas ulasan dan rating Anda.</p>
        <button type="button" class="btn btn-success mt-2" data-bs-dismiss="modal">Tutup</button>
      </div>
    </div>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script>
const reviewModal = new bootstrap.Modal(document.getElementById('reviewModal'));
const successModal = new bootstrap.Modal(document.getElementById('successModal'));

function openReviewModal(productName, idPemesanan){
    document.getElementById('modal-product-name').innerText = productName;
    document.getElementById('form-penilaian').action = '/penilaian/simpan/' + idPemesanan;
    reviewModal.show();
}

// Rating bintang
const stars = document.querySelectorAll('.stars span');
const ratingInput = document.getElementById('rating');
stars.forEach(star => {
    star.addEventListener('click', () => {
        const value = star.getAttribute('data-value');
        ratingInput.value = value;
        stars.forEach(s => s.classList.remove('active'));
        for(let i=0; i<value; i++) stars[i].classList.add('active');
    });
});

// Validasi file & rating
document.getElementById('form-penilaian').addEventListener('submit', function(e){
    const fileInput = this.querySelector('input[name="media[]"]');
    if(fileInput.files.length > 0){
        const allowedTypes = ['image/jpeg','image/png','image/gif','video/mp4','video/webm','video/ogg'];
        for(let i=0; i<fileInput.files.length; i++){
            if(!allowedTypes.includes(fileInput.files[i].type)){
                alert('Hanya file gambar (jpeg, png, gif) atau video (mp4, webm, ogg) yang diperbolehkan.');
                e.preventDefault();
                return false;
            }
        }
    }
    if(ratingInput.value === ''){
        alert('Silakan pilih rating minimal 1 bintang.');
        e.preventDefault();
        return false;
    }
});

// Tampilkan modal sukses jika ada flashdata success
<?php if(session()->getFlashdata('success')): ?>
    successModal.show();
<?php endif; ?>
</script>
</body>
</html>
