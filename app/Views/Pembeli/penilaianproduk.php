<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Pesanan Saya - FarmUnand</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
<style>
    body { background-color: #f8f9fa; }
    .sidebar { min-height: 100vh; }
    .product-img { width: 80px; height: 80px; background: #e9ecef; border-radius: 8px; }
    .stars span { font-size: 24px; color: #ccc; cursor: pointer; }
    .stars span.active { color: gold; }
    textarea { width: 100%; min-height: 100px; border-radius: 8px; border: 1px solid #aaa; padding: 10px; resize: vertical; }
    .upload-box { width: 100px; height: 100px; border: 1px dashed #999; display: flex; align-items: center; justify-content: center; border-radius: 8px; font-size: 12px; color: #666; cursor: pointer; }
    .order-card { cursor: pointer; }
</style>
</head>
<body>

<div class="container-fluid px-0">
    <div class="row g-0">

        <!-- Sidebar -->
        <div class="col-md-3 col-lg-2 bg-success text-white d-flex flex-column vh-100 p-0 sidebar">
            <div class="text-center mb-4 p-3">
                <div class="order-img mb-3 mx-auto">
                    <!-- Logo bisa ditambahkan -->
                </div>
                <h5 class="fw-bold">Farm Unand</h5>
            </div>
            <div class="d-grid gap-2 px-3 mb-3">
                <a href="/dashboarduser" class="btn btn-light">Dashboard</a>
                <a href="/akun" class="btn btn-light">Akun Saya</a>
                <a href="/riwayatpesanan" class="btn btn-dark text-white">Pesanan Saya</a>
            </div>
        </div>

        <!-- Content -->
        <div class="col-md-9 col-lg-10 p-4">
            <div class="card shadow-sm mb-3">
                <div class="card-body">
                    <h4 class="mb-4">Berikan Penilaian</h4>

                    <!-- Daftar Pesanan -->
                    <div id="orders-list">
                        <?php if(!empty($pesanan)): ?>
                            <?php foreach($pesanan as $p): ?>
                                <div class="card mb-2 order-card" onclick="showReviewForm('<?= esc($p['nama_produk']) ?>', <?= esc($p['id_produk']) ?>)">
                                    <div class="card-body d-flex align-items-center">
                                        <div class="product-img me-3"></div>
                                        <h6 class="fw-bold mb-0"><?= esc($p['nama_produk']) ?></h6>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <p class="text-muted">Belum ada Pesanan.</p>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- Form Penilaian (disembunyikan awalnya) -->
            <div class="card shadow-sm" id="review-form" style="display:none;">
                <div class="card-body">
                    <h4 class="mb-4">Nilai Produk: <span id="product-name"></span></h4>

                    <form id="form-penilaian" method="post" enctype="multipart/form-data">
                        <?= csrf_field() ?>

                        <!-- Rating Bintang -->
                        <p class="mb-1">Nilai Produk:</p>
                        <div class="stars mb-3">
                            <?php for($i=1;$i<=5;$i++): ?>
                                <span data-value="<?= $i ?>">★</span>
                            <?php endfor; ?>
                            <input type="hidden" name="rating" id="rating" value="">
                        </div>

                        <!-- Upload Media -->
                        <p class="mb-1">Tambahkan Foto atau Video:</p>
                        <input type="file" name="media" class="form-control mb-3" accept="image/*,video/*">

                        <!-- Ulasan -->
                        <p class="mb-1">Tulis ulasan minimal 50 karakter per aspek:</p>
                        <textarea name="ulasan" placeholder="Kualitas Gambar:&#10;Kualitas Produk:&#10;Kesegaran:" required></textarea>

                        <button class="btn btn-success mt-3">Kirim</button>
                    </form>
                </div>
            </div>

        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script>
    function showReviewForm(namaProduk, idProduk) {
        // Tampilkan form
        const form = document.getElementById('review-form');
        form.style.display = 'block';

        // Update nama produk
        document.getElementById('product-name').innerText = namaProduk;

        // Update form action sesuai produk
        document.getElementById('form-penilaian').action = '/penilaian/simpan/' + idProduk;

        // Scroll ke form
        form.scrollIntoView({ behavior: 'smooth' });
    }

    // Script bintang
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

    // Validasi file upload
    document.getElementById('form-penilaian').addEventListener('submit', function(e){
        const fileInput = this.querySelector('input[name="media"]');
        if(fileInput.files.length > 0){
            const file = fileInput.files[0];
            const allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'video/mp4', 'video/webm', 'video/ogg'];
            if(!allowedTypes.includes(file.type)){
                alert('Hanya file gambar (jpeg, png, gif) atau video (mp4, webm, ogg) yang diperbolehkan.');
                e.preventDefault();
            }
        }
    });
</script>

</body>
</html>
