<!DOCTYPE html>
<html lang="id">
  <head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Berikan Penilaian - FarmUnand</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>
      body{background:#f8f9fa; font-family:'Segoe UI',sans-serif;}
      .content{margin-left:250px; padding:30px;}
      .page-header{
        background:linear-gradient(135deg,#198754,#28a745);
        color:#fff; border-radius:12px; padding:18px 20px;
        display:flex; align-items:center; justify-content:space-between;
        box-shadow:0 6px 14px rgba(0,0,0,.08); margin-bottom:16px;}
      .page-header h5{margin:0; font-weight:700}
      .card-container{
        background:#fff; border-radius:12px; box-shadow:0 6px 18px rgba(0,0,0,.06);
        padding:18px;}
      .tabs-wrap{gap:8px;}
      .btn-filter{border-radius:999px; font-weight:500; padding:6px 14px;}
      .card-order{border:none; border-radius:12px; box-shadow:0 4px 10px rgba(0,0,0,.08);}
      .card-order + .card-order{margin-top:12px;}
      .order-img img{width:100px; height:100px; object-fit:cover; border-radius:12px; border:2px solid #e9ecef;}
      .stars span{font-size:28px; color:#ccc; cursor:pointer; transition:color .2s}
      .stars span:hover,.stars span.active{color:#ffc107}
      .btn-success{background:#198754; border:none; font-weight:600}
      .btn-success:hover{background:#145c32; transform:translateY(-2px)}
      .modal-content{border-radius:12px; border:none; box-shadow:0 4px 15px rgba(0,0,0,.2)}
      .modal-header{border-bottom:2px solid #e9ecef}
      .modal-body{max-height:70vh; overflow-y:auto}
      textarea{width:100%; min-height:120px; border-radius:8px; border:1px solid #ced4da; padding:10px; resize:vertical}
      @media(max-width:768px){
        .content{margin-left:0; padding:20px}
        .order-img img{width:70px; height:70px}}
    </style>
  </head>

  <body>
    <!-- Sidebar -->
    <?= $this->include('layout/sidebar') ?>

    <div class="content">
      <!-- Header -->
      <div class="page-header">
        <h5>Berikan Penilaian</h5>
        <div class="d-none d-md-block small">Nilai produk yang sudah kamu terima</div>
      </div>

      <div class="card-container">
        <!-- Tabs -->
        <div class="mb-3 d-flex flex-wrap tabs-wrap">
          <a href="<?= base_url('riwayatpesanan') ?>"     class="btn btn-sm btn-outline-success btn-filter rounded-pill">Semua</a>
          <a href="<?= base_url('pesananbelumbayar') ?>"  class="btn btn-sm btn-outline-success btn-filter rounded-pill">Belum Bayar</a>
          <a href="<?= base_url('pesanandikemas') ?>"     class="btn btn-sm btn-outline-success btn-filter rounded-pill">Dikemas</a>
          <a href="<?= base_url('konfirmasipesanan') ?>"  class="btn btn-sm btn-outline-success btn-filter rounded-pill">Dikirim</a>
          <a href="<?= base_url('pesananselesai') ?>"     class="btn btn-sm btn-outline-success btn-filter rounded-pill">Selesai</a>
          <a href="<?= base_url('pesanandibatalkan') ?>"  class="btn btn-sm btn-outline-success btn-filter rounded-pill">Dibatalkan</a>
          <a href="<?= base_url('penilaian/daftar') ?>"   class="btn btn-sm btn-success btn-filter rounded-pill active">Berikan Penilaian</a>
        </div>

        <!-- List pesanan yang bisa dinilai (per-item / per-detail) -->
        <?php if(!empty($pesanan)): ?>
          <?php foreach($pesanan as $p): ?>
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
                  <p class="mb-1">
                    Total:
                    <span class="fw-bold text-dark">
                      Rp <?= number_format(($p['harga'] ?? 0) * ($p['jumlah_produk'] ?? 0),0,',','.'); ?>
                    </span>
                  </p>
                  <button
                    class="btn btn-success btn-sm mt-2 btn-filter rounded-pill"
                    onclick="openReviewModal('<?= esc($p['nama_produk']) ?>', <?= (int)$p['id_detail_pemesanan'] ?>)">
                    Berikan Penilaian
                  </button>
                </div>

              </div>
            </div>
          <?php endforeach; ?>
        <?php else: ?>
          <div class="alert alert-info mb-0">Belum ada pesanan yang bisa dinilai.</div>
        <?php endif; ?>
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
              <button type="submit" class="btn btn-success mt-3 w-100 rounded-pill">Kirim Penilaian</button>
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
            <button type="button" class="btn btn-success mt-2 rounded-pill" data-bs-dismiss="modal">Tutup</button>
          </div>
        </div>
      </div>
    </div>

    <!-- Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
      const reviewModal = new bootstrap.Modal(document.getElementById('reviewModal'));
      const successModal = new bootstrap.Modal(document.getElementById('successModal'));

      function openReviewModal(productName, idDetail){
        document.getElementById('modal-product-name').innerText = productName;
        document.getElementById('form-penilaian').action = '<?= base_url('penilaian/simpan') ?>/' + idDetail;
        reviewModal.show();
      }

      // Rating bintang
      const stars = document.querySelectorAll('.stars span');
      const ratingInput = document.getElementById('rating');
      stars.forEach((star, idx) => {
        star.addEventListener('click', () => {
          const value = parseInt(star.getAttribute('data-value'),10);
          ratingInput.value = value;
          stars.forEach(s => s.classList.remove('active'));
          for(let i=0;i<value;i++){ stars[i].classList.add('active'); }
        });
      });

      // Validasi file & rating
      document.getElementById('form-penilaian').addEventListener('submit', function(e){
        const fileInput = this.querySelector('input[name="media[]"]');
        if(fileInput.files.length > 0){
          const allowed = ['image/jpeg','image/png','image/gif','video/mp4','video/webm','video/ogg'];
          for(let i=0;i<fileInput.files.length;i++){
            if(!allowed.includes(fileInput.files[i].type)){
              alert('Hanya file gambar (jpeg, png, gif) atau video (mp4, webm, ogg).');
              e.preventDefault(); return false;
            }
          }
        }
        if(!ratingInput.value){
          alert('Silakan pilih rating minimal 1 bintang.');
          e.preventDefault(); return false;
        }
      });

      // Show success modal dari flashdata
      <?php if(session()->getFlashdata('success')): ?>
        successModal.show();
      <?php endif; ?>
    </script>
  </body>
</html>
