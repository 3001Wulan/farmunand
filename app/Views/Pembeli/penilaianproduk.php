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
        margin: 0;
        padding: 0;
        background-color: #f1f3f6;
        font-family: 'Segoe UI', sans-serif;
    }

    .main-content {
        margin-left: 250px;
        padding: 30px;
    }

    .card-order {
        border-radius: 12px;
        border: none;
        box-shadow: 0 2px 8px rgba(0,0,0,0.08);
        transition: all 0.2s ease;
    }
    .card-order:hover {
        transform: translateY(-3px);
        box-shadow: 0 6px 15px rgba(0,0,0,0.12);
    }

    .order-img img {
        width: 80px;
        height: 80px;
        object-fit: cover;
        border-radius: 10px;
        border: 1px solid #dee2e6;
    }

    .stars span {
        font-size: 28px;
        color: #ccc;
        cursor: pointer;
        transition: color 0.2s;
    }
    .stars span:hover,
    .stars span.active {
        color: #ffc107;
    }

    .btn-submit {
        background-color: #28a745;
        color: white;
        font-weight: 600;
        transition: all 0.2s;
    }
    .btn-submit:hover {
        background-color: #218838;
        transform: translateY(-2px);
    }

    /* Modal scrollable agar tidak terpotong */
    .modal-dialog {
        max-width: 500px;
        margin: 1.75rem auto;
    }
    .modal-body {
        max-height: 70vh;
        overflow-y: auto;
    }

    textarea {
        width: 100%;
        min-height: 120px;
        border-radius: 8px;
        border: 1px solid #ced4da;
        padding: 10px;
        resize: vertical;
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
            <h4 class="mb-4">Berikan Penilaian</h4>

            <!-- Loop Pesanan -->
            <?php if(!empty($pesanan)): ?>
                <?php foreach($pesanan as $p): ?>
                    <div class="card card-order mb-3">
                        <div class="card-body d-flex justify-content-between align-items-center flex-wrap">
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
                                <p class="mb-1 text-success"><?= esc($p['status_pemesanan']); ?></p>
                                <p class="mb-0">Total: <span class="fw-bold">
                                    Rp <?= number_format($p['harga'] * $p['jumlah_produk'],0,',','.'); ?>
                                </span></p>
                                <!-- Button Berikan Penilaian -->
                                <button class="btn btn-success btn-sm mt-2" onclick="openReviewModal('<?= esc($p['nama_produk']) ?>', <?= esc($p['id_produk']) ?>)">Berikan Penilaian</button>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="alert alert-info">Belum ada pesanan.</div>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Modal Penilaian -->
<div class="modal fade" id="reviewModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable">
    <div class="modal-content p-3">
        <h5>Nilai Produk: <span id="modal-product-name"></span></h5>
        <form id="form-penilaian" method="post" enctype="multipart/form-data">
            <?= csrf_field() ?>
            
            <p class="mb-1 mt-3">Rating (wajib):</p>
            <div class="stars mb-3">
                <?php for($i=1;$i<=5;$i++): ?>
                    <span data-value="<?= $i ?>">★</span>
                <?php endfor; ?>
                <input type="hidden" name="rating" id="rating" value="">
            </div>

            <p class="mb-1">Foto atau Video (opsional, boleh lebih dari 1):</p>
            <input type="file" name="media[]" class="form-control mb-3" accept="image/*,video/*" multiple>

            <p class="mb-1">Tulis ulasan (opsional):</p>
            <textarea name="ulasan" placeholder="Kualitas Gambar:&#10;Kualitas Produk:&#10;Kesegaran:"></textarea>

            <button type="submit" class="btn btn-submit mt-3 w-100">Kirim Penilaian</button>
        </form>
    </div>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script>
    const reviewModal = new bootstrap.Modal(document.getElementById('reviewModal'));

    function openReviewModal(productName, productId){
        document.getElementById('modal-product-name').innerText = productName;
        document.getElementById('form-penilaian').action = '/penilaian/simpan/' + productId;
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

    // Validasi file media sebelum submit
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

        // Pastikan rating wajib
        if(ratingInput.value === ''){
            alert('Silakan pilih rating minimal 1 bintang.');
            e.preventDefault();
            return false;
        }
    });
</script>
</body>
</html>
