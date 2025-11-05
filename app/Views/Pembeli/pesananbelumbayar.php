<!DOCTYPE html>
<html lang="id">
  <head>
  <meta charset="UTF-8"/>
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Pesanan Belum Bayar - FarmUnand</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
    <style>
      body {
        background: linear-gradient(135deg, #e6f4ea, #c0e0cc);
        font-family: "Segoe UI", Tahoma, Geneva, Verdana, sans-serif;
        min-height: 100vh;
        margin: 0;
      }
      .content{margin-left:250px; padding:30px;}
      .page-header{
        background:linear-gradient(135deg,#198754,#28a745);
        color:#fff;border-radius:12px;padding:18px 20px;
        display:flex;align-items:center;justify-content:space-between;
        box-shadow:0 6px 14px rgba(0,0,0,.08);margin-bottom:16px
      }
      .page-header h5{margin:0;font-weight:700}
      .card-container{background:#fff;border-radius:12px;box-shadow:0 6px 18px rgba(0,0,0,.06);padding:18px}
      .tabs-wrap{gap:8px}
      .btn-filter{border-radius:999px;font-weight:500;padding:6px 14px}
      .order-img img{width:80px;height:80px;object-fit:cover;border-radius:8px;border:2px solid #dee2e6;background:#e9ecef}
      .order-card{border:none;border-radius:12px;box-shadow:0 4px 10px rgba(0,0,0,.08)}
      .order-card + .order-card{margin-top:12px}
      .status{font-weight:600;font-size:14px}
      .badge-await { background:#6c757d; color:#fff; } /* Menunggu Pembayaran */

      /* ===== Modal Batalkan Pesanan (styling menarik) ===== */
      .cancel-modal {
        border: 0;
        border-radius: 16px;
        overflow: hidden;
        box-shadow: 0 12px 40px rgba(0,0,0,.25);
        background: #ffffff;
      }
      .cancel-modal .modal-header {
        background: linear-gradient(135deg, #dc3545, #ff6b6b);
        color: #fff;
        padding: 14px 16px;
      }
      .cancel-modal .btn-close-white { filter: invert(1); }
      .cancel-icon {
        width: 36px;
        height: 36px;
        border-radius: 10px;
        display: grid;
        place-items: center;
        background: rgba(255,255,255,.15);
        backdrop-filter: blur(2px);
        font-size: 18px;
        color: #fff;
      }
      .cancel-modal .modal-body { padding: 16px 18px; }
      .cancel-modal .modal-footer { padding: 14px 16px; }

      @media (max-width: 992px) {
        .content{margin-left:0; padding:20px;}
      }
    </style>
  </head>

  <body>
    <!-- Sidebar -->
    <?= $this->include('layout/sidebar') ?>

    <!-- Content -->
    <div class="content">
      <div class="page-header">
        <h5>Pesanan Belum Bayar</h5>
        <div class="d-none d-md-block small">Lanjutkan pembayaran atau batalkan pesanan</div>
      </div>

      <div class="card-container">
        <div class="mb-3 d-flex flex-wrap tabs-wrap">
          <a href="/riwayatpesanan"     class="btn btn-sm btn-outline-success btn-filter">Semua</a>
          <a href="/pesananbelumbayar"  class="btn btn-sm btn-success btn-filter active">Belum Bayar</a>
          <a href="/pesanandikemas"     class="btn btn-sm btn-outline-success btn-filter">Dikemas</a>
          <a href="/konfirmasipesanan"  class="btn btn-sm btn-outline-success btn-filter">Dikirim</a>
          <a href="/pesananselesai"     class="btn btn-sm btn-outline-success btn-filter">Selesai</a>
          <a href="/pesanandibatalkan"  class="btn btn-sm btn-outline-success btn-filter">Dibatalkan</a>
          <a href="<?= base_url('penilaian/daftar') ?>" class="btn btn-sm btn-outline-success btn-filter">Berikan Penilaian</a>
        </div>

        <?php if (!empty($orders)): ?>
          <?php foreach ($orders as $order): ?>
            <div class="card order-card">
              <div class="card-body d-flex flex-wrap justify-content-between align-items-center">
                <div class="d-flex align-items-center">
                  <div class="order-img">
                    <img src="<?= !empty($order['foto']) ? base_url('uploads/produk/'.$order['foto']) : base_url('assets/images/no-image.png') ?>" alt="<?= esc($order['nama_produk'] ?? '-') ?>">
                  </div>
                  <div class="ms-3">
                    <h6 class="fw-bold mb-1"><?= esc($order['nama_produk'] ?? '-') ?></h6>
                    <p class="text-muted mb-1">Farm Unand</p>
                    <p class="mb-0">Jumlah: <?= esc($order['jumlah_produk'] ?? 0) ?></p>
                  </div>
                </div>

                <div class="text-end mt-3 mt-md-0">
                  <p class="mb-1 status">
                    <span class="badge badge-await">Menunggu Pembayaran</span>
                  </p>
                  <p class="mb-0">
                    Total Pesanan
                    <span class="fw-bold">
                      Rp <?= number_format(($order['harga'] ?? 0) * ($order['jumlah_produk'] ?? 0), 0, ',', '.'); ?>
                    </span>
                  </p>
                  <p class="mb-0 text-muted small">
                    <i class="bi bi-clock me-1"></i> 
                    <?= date('d M Y H:i', strtotime($order['created_at'])) ?> WIB
                  </p>

                  <?php if (!empty($order['order_id'])): ?>
                    <div class="d-flex gap-2 justify-content-end">
                      <button class="btn btn-success btn-sm"
                              onclick="lanjutkanPembayaranByOrder('<?= esc($order['order_id']) ?>')">
                        Lanjutkan Pembayaran
                      </button>
                      <button class="btn btn-outline-danger btn-sm"
                              onclick="openCancelModal('<?= esc($order['order_id']) ?>')">
                        Batalkan Pesanan
                      </button>
                    </div>
                  <?php else: ?>
                    <span class="badge bg-secondary">Order ID tidak tersedia</span>
                  <?php endif; ?>
                </div>
              </div>
            </div>
          <?php endforeach; ?>
        <?php else: ?>
          <div class="alert alert-info mb-0">Belum ada pesanan.</div>
        <?php endif; ?>
      </div>
    </div>

    <!-- Modal Konfirmasi Batalkan Pesanan -->
    <div class="modal fade" id="cancelModal" tabindex="-1" aria-labelledby="cancelModalLabel" aria-hidden="true">
      <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content cancel-modal">
          <div class="modal-header border-0">
            <div class="d-flex align-items-center gap-2">
              <div class="cancel-icon">
                <i class="bi bi-x-circle-fill"></i>
              </div>
              <h6 class="modal-title m-0 fw-bold" id="cancelModalLabel">Batalkan Pesanan?</h6>
            </div>
            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Tutup"></button>
          </div>

          <div class="modal-body">
            <p class="mb-2">Pesanan ini masih <strong>Menunggu Pembayaran</strong>.</p>
            <p class="mb-0 text-muted small">
              Jika dibatalkan, <strong>stok akan dikembalikan</strong> dan pesanan akan dihapus dari daftar “Belum Bayar”.
            </p>
            <div class="alert alert-warning py-2 px-3 mt-3 mb-0 small">
              Tindakan ini tidak dapat dibatalkan.
            </div>
          </div>

          <div class="modal-footer border-0">
            <input type="hidden" id="cancelOrderId">
            <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Kembali</button>
            <button type="button" class="btn btn-danger" id="btnConfirmCancel">
              <span class="btn-text">Ya, Batalkan</span>
              <span class="spinner-border spinner-border-sm d-none" role="status" aria-hidden="true"></span>
            </button>
          </div>
        </div>
      </div>
    </div>

    <!-- Snap.js -->
    <script src="https://app.sandbox.midtrans.com/snap/snap.js" data-client-key="<?= esc(env('MIDTRANS_CLIENT_KEY')) ?>"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
    // helper ambil query param
    function getParam(name){ return new URLSearchParams(location.search).get(name); }

    // buka Snap berdasarkan ORDER ID (tanpa header/token CSRF)
    async function lanjutkanPembayaranByOrder(orderId){
      try{
        const res  = await fetch('<?= site_url('payments/resume') ?>/' + encodeURIComponent(orderId), {
          method: 'POST',
          headers: { 'Content-Type': 'application/json' }
        });

        const data = await res.json();
        if(!data.success){
          alert(data.message || 'Token tidak tersedia');
          return;  
        }

        window.snap.pay(data.snapToken, {
          onSuccess: () => location.href = '<?= site_url('payments/finish') ?>',
          onPending: () => location.href = '<?= site_url('payments/unfinish') ?>',
          onError:   () => location.href = '<?= site_url('payments/error') ?>',
          onClose:   () => {} // popup ditutup → tetap di Belum Bayar
        });
      }catch(e){
        alert('Gagal mengambil token Midtrans.');
      }
    }

    // Modal & Cancel flow
    let cancelModalInstance = null;

    function openCancelModal(orderId) {
      document.getElementById('cancelOrderId').value = orderId;
      const modalEl = document.getElementById('cancelModal');
      cancelModalInstance = bootstrap.Modal.getOrCreateInstance(modalEl);
      cancelModalInstance.show();
    }

    async function doCancelRequest(orderId) {
      const res = await fetch('<?= site_url('payments/cancel_keep') ?>', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ order_id: orderId })
      });
      return res.json();
    }

    (function attachCancelHandler(){
      const btn = document.getElementById('btnConfirmCancel');
      const spinner = btn.querySelector('.spinner-border');
      const text = btn.querySelector('.btn-text');

      btn.addEventListener('click', async function(){
        const orderId = document.getElementById('cancelOrderId').value || '';
        if (!orderId) return;

        // UI loading state
        btn.disabled = true;
        spinner.classList.remove('d-none');
        text.textContent = 'Memproses...';

        try {
          const data = await doCancelRequest(orderId);
          if (data && data.success) {
            if (cancelModalInstance) cancelModalInstance.hide();
            location.reload();
          } else {
            alert(data.message || 'Gagal membatalkan pesanan.');
          }
        } catch (e) {
          alert('Terjadi kesalahan jaringan saat membatalkan pesanan.');
        } finally {
          btn.disabled = false;
          spinner.classList.add('d-none');
          text.textContent = 'Ya, Batalkan';
        }
      });
    })();

    // auto-open kalau datang dari checkout online
    (function(){
      const params   = new URLSearchParams(location.search);
      const autopay  = params.get('autopay');
      const orderId  = params.get('order');
      const canceled = params.get('cancelled'); // optional

      if (autopay === '1' && orderId && canceled !== '1') {
        lanjutkanPembayaranByOrder(orderId);
      }
    })();

    </script>

  </body>
</html>
