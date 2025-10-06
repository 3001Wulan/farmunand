<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Pemesanan</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">

  <style>
    :root {
      --brand: #198754;
      --brand-dark: #145c32;
      --muted: #f8f9fa;
    }
    html, body { margin:0; padding:0; height:100%; background:var(--muted); }
    .content { margin-left:250px; padding:30px; }

    .page-head { display:flex; justify-content:space-between; align-items:center; margin-bottom:18px; }
    .page-title { font-weight:700; color:var(--brand); margin:0; }
    .btn-back {
      background:white; color:var(--brand); border:1px solid var(--brand);
    }
    .btn-back:hover { background:var(--brand); color:white; }

    .section {
      border-radius:12px; box-shadow:0 6px 18px rgba(0,0,0,0.06);
      overflow:hidden; margin-bottom:16px; background:#fff;
    }
    .section-title {
      background: linear-gradient(90deg, var(--brand), #20c997);
      color:#fff; font-weight:700; padding:12px 16px;
      display:flex; align-items:center; justify-content:space-between;
    }
    .section-body { padding:16px; }

    .ubah-btn {
      background:#fff; color:var(--brand); border:1px solid #fff; 
      padding:6px 12px; border-radius:6px; text-decoration:none;
    }
    .ubah-btn:hover { background:rgba(255,255,255,0.15); color:#fff; }

    .product-wrap { display:flex; gap:16px; align-items:flex-start; }
    .product-image {
      width:150px; height:120px; border-radius:8px; overflow:hidden;
      border:1px solid #e9ecef; flex-shrink:0; background:#f0f0f0;
    }
    .product-image img { width:100%; height:100%; object-fit:cover; }
    .product-info .name { font-size:18px; font-weight:700; color:#333; }
    .product-info .desc { font-size:14px; color:#666; margin-top:6px; }
    .pill {
      display:inline-block; padding:2px 10px; border-radius:999px;
      font-size:13px; margin-right:6px; border:1px solid #e9ecef; background:#f8f9fa;
    }
    .total { font-weight:700; color:var(--brand); font-size:16px; margin-top:8px; }

    .pay-grid { display:grid; gap:10px; grid-template-columns: 1fr; }
    .method-option {
      background:#fff; padding:12px 14px; border-radius:8px; border:2px solid #e9ecef;
      cursor:pointer; transition:.2s; display:flex; align-items:center; gap:10px;
    }
    .method-option:hover { border-color:var(--brand); background:#f6fff8; }
    .method-option.selected { border-color:var(--brand); background:#e9f8ef; }
    .method-text { font-weight:600; color:#333; }

    .footer-actions { display:flex; justify-content:flex-end; margin-top:14px; }
    .btn-order {
      background:var(--brand); color:white; border:none; padding:10px 20px; border-radius:8px; font-weight:600;
    }
    .btn-order:hover { background:var(--brand-dark); }

    /* Modal styling */
    .modal-confirm .modal-content,
    .modal-success .modal-content {
      border-radius:12px; border:none; box-shadow:0 8px 30px rgba(0,0,0,0.2);
    }
    .modal-confirm .modal-header, .modal-success .modal-header {
      border-bottom:none; text-align:center; flex-direction:column;
    }
    .modal-confirm .icon-circle, .modal-success .icon-circle {
      width:80px; height:80px; border-radius:50%; display:flex;
      align-items:center; justify-content:center; font-size:36px; color:white;
      margin-top:10px;
    }
    .modal-confirm .icon-circle { background:var(--brand); }
    .modal-success .icon-circle { background:#28a745; animation:pop 0.4s ease; }
    @keyframes pop { from {transform:scale(0.5); opacity:0;} to {transform:scale(1); opacity:1;} }
    .modal-body { text-align:center; }
    .modal-footer { border:none; justify-content:center; gap:10px; padding-bottom:25px; }

    @media (max-width: 992px) {
      .content { margin-left:0; padding:18px; }
      .product-wrap { flex-direction:column; }
      .footer-actions { justify-content:stretch; }
      .btn-order { width:100%; }
    }
  </style>
</head>
<body>

<?= $this->include('layout/sidebar') ?>

<div class="content">
  <div class="page-head">
    <h3 class="page-title">Pemesanan</h3>
    <?php 
      $backHref = isset($checkout['id_produk'])
        ? base_url('detailproduk/'.$checkout['id_produk'])
        : 'javascript:history.back()';
    ?>
    <a href="<?= $backHref ?>" class="btn btn-back">Kembali</a>
  </div>

  <!-- Alamat -->
  <div class="section">
    <div class="section-title">
      <span>Alamat Pemesanan</span>
      <a href="<?= base_url('memilihalamat') ?>" class="ubah-btn">Ubah / Tambah</a>
    </div>
    <div class="section-body">
      <?php if (!empty($alamat)): ?>
        <?php $alamatAktif = $alamat[0]; ?>
        <p><b><?= esc($alamatAktif['nama_penerima']); ?></b> | <?= esc($alamatAktif['no_telepon']); ?></p>
        <p class="text-muted mb-0">
          <?= esc($alamatAktif['jalan']); ?>, <?= esc($alamatAktif['kota']); ?>, <?= esc($alamatAktif['provinsi']); ?>, <?= esc($alamatAktif['kode_pos']); ?>
        </p>
      <?php else: ?>
        <div class="alert alert-warning mb-0">Belum ada alamat aktif. Silakan tambahkan alamat terlebih dahulu.</div>
      <?php endif; ?>
    </div>
  </div>

  <!-- Detail -->
  <div class="section">
    <div class="section-title">Detail Pemesanan</div>
    <div class="section-body">

      <?php if (!empty($checkout_multi) && !empty($checkout_multi['items'])): ?>
        <!-- === MODE MULTI ITEM (Checkout Semua) === -->
        <div class="table-responsive">
          <table class="table table-bordered align-middle">
            <thead class="table-success">
              <tr>
                <th>Produk</th>
                <th class="text-center" style="width:120px;">Qty</th>
                <th class="text-end" style="width:160px;">Harga</th>
                <th class="text-end" style="width:180px;">Subtotal</th>
              </tr>
            </thead>
            <tbody>
              <?php foreach ($checkout_multi['items'] as $it): ?>
                <tr>
                  <td class="text-start">
                    <div class="d-flex align-items-center">
                      <div class="product-image me-2" style="width:60px;height:60px;">
                        <img src="<?= base_url('uploads/produk/'.($it['foto'] ?? 'default.png')) ?>" alt="<?= esc($it['nama_produk']) ?>">
                      </div>
                      <div class="fw-semibold"><?= esc($it['nama_produk']) ?></div>
                    </div>
                  </td>
                  <td class="text-center"><?= (int)$it['qty'] ?></td>
                  <td class="text-end">Rp <?= number_format((float)$it['harga'], 0, ',', '.') ?></td>
                  <td class="text-end fw-semibold">Rp <?= number_format((float)$it['subtotal'], 0, ',', '.') ?></td>
                </tr>
              <?php endforeach; ?>
            </tbody>
            <tfoot>
              <tr>
                <th colspan="3" class="text-end">Total</th>
                <th class="text-end">Rp <?= number_format((float)($checkout_multi['grandTotal'] ?? 0), 0, ',', '.') ?></th>
              </tr>
            </tfoot>
          </table>
        </div>

        <!-- inject data multi untuk JS -->
        <script type="application/json" id="checkoutMultiData">
          <?= json_encode($checkout_multi, JSON_UNESCAPED_UNICODE) ?>
        </script>

      <?php else: ?>
        <!-- === MODE SINGLE ITEM === -->
        <?php
          $namaProduk = $checkout['nama_produk'] ?? '-';
          $deskripsi  = $checkout['deskripsi'] ?? '-';
          $qty        = (int)($checkout['qty'] ?? 0);
          $harga      = (float)($checkout['harga'] ?? 0);
          $subtotal   = $qty * $harga;
          $fotoPath   = isset($checkout['foto']) ? base_url('uploads/produk/'.$checkout['foto']) : base_url('assets/images/sapi.jpg');
        ?>
        <div class="product-wrap">
          <div class="product-image"><img src="<?= $fotoPath ?>" alt="<?= esc($namaProduk) ?>"></div>
          <div class="product-info">
            <div class="name"><?= esc($namaProduk) ?></div>
            <div class="desc"><?= esc($deskripsi) ?></div>
            <div class="mt-2">
              <span class="pill">Harga: Rp <?= number_format($harga, 0, ',', '.') ?></span>
              <span class="pill">Qty: <?= esc($qty) ?></span>
            </div>
            <div class="total">Total: Rp <?= number_format($subtotal, 0, ',', '.') ?></div>
          </div>
        </div>
      <?php endif; ?>

    </div>
  </div>

  <!-- Pembayaran -->
  <div class="section">
    <div class="section-title">Metode Pembayaran</div>
    <div class="section-body">
      <div class="pay-grid">
        <div class="method-option selected" data-method="cod">
          <div class="method-text">💵 COD (Cash on Delivery)</div>
        </div>
        <div class="method-option" data-method="transfer">
          <div class="method-text">🏦 Transfer Bank</div>
        </div>
        <div class="method-option" data-method="ewallet">
          <div class="method-text">📱 E-Wallet</div>
        </div>
      </div>

      <div class="footer-actions mt-3">
        <?php
          $hasAddress = !empty($alamat);
          $idAlamat   = $alamat[0]['id_alamat'] ?? 0;
          $isMulti    = !empty($checkout_multi) && !empty($checkout_multi['items']);
        ?>
        <?php if ($isMulti): ?>
          <!-- Tombol untuk mode MULTI -->
          <button class="btn-order"
                  data-mode="multi"
                  data-id-alamat="<?= (int)$idAlamat ?>"
                  data-total="<?= (float)($checkout_multi['grandTotal'] ?? 0) ?>"
                  data-has-address="<?= $hasAddress ? '1' : '0' ?>"
                  onclick="openConfirmModal(event)">
            Buat Pesanan (Semua)
          </button>
        <?php else: ?>
          <!-- Tombol untuk mode SINGLE -->
          <button class="btn-order"
                  data-mode="single"
                  data-id-produk="<?= (int)($checkout['id_produk'] ?? 0) ?>"
                  data-harga="<?= (float)($checkout['harga'] ?? 0) ?>"
                  data-qty="<?= (int)($checkout['qty'] ?? 0) ?>"
                  data-id-alamat="<?= (int)$idAlamat ?>"
                  data-total="<?= (float)($subtotal ?? 0) ?>"
                  data-has-address="<?= $hasAddress ? '1' : '0' ?>"
                  onclick="openConfirmModal(event)">
            Buat Pesanan
          </button>
        <?php endif; ?>
      </div>
    </div>
  </div>
</div>

<!-- Modal Konfirmasi -->
<div class="modal fade modal-confirm" id="confirmModal" tabindex="-1">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content text-center">
      <div class="modal-header flex-column">
        <div class="icon-circle"><i class="bi bi-question-lg"></i></div>
        <h5 class="mt-3 fw-bold text-success">Konfirmasi Pemesanan</h5>
      </div>
      <div class="modal-body">
        <p id="confirmText" class="mb-0 text-secondary"></p>
      </div>
      <div class="modal-footer">
        <button class="btn btn-secondary px-4" data-bs-dismiss="modal">Batal</button>
        <button class="btn btn-success px-4" id="confirmYesBtn">Ya, Pesan Sekarang</button>
      </div>
    </div>
  </div>
</div>

<!-- Modal Sukses -->
<div class="modal fade modal-success" id="successModal" tabindex="-1">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content text-center">
      <div class="modal-header flex-column">
        <div class="icon-circle"><i class="bi bi-check-lg"></i></div>
        <h5 class="mt-3 fw-bold text-success">Pesanan Berhasil!</h5>
      </div>
      <div class="modal-body">
        <p id="successText" class="mb-0 text-secondary"></p>
      </div>
      <div class="modal-footer">
        <button class="btn btn-success px-4" onclick="window.location.href='<?= base_url('riwayatpesanan') ?>'">
          <i class="bi bi-box-seam"></i> Lihat Riwayat Pesanan
        </button>
      </div>
    </div>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script>
let currentData = {};

document.querySelectorAll('.method-option').forEach(opt => {
  opt.addEventListener('click', function(){
    document.querySelectorAll('.method-option').forEach(x => x.classList.remove('selected'));
    this.classList.add('selected');
  });
});

function openConfirmModal(e){
  const btn = e.currentTarget; // penting: currentTarget, bukan target (ikonnya)
  const hasAddress = btn.dataset.hasAddress === '1';
  if (!hasAddress) {
    alert('Silakan tambahkan atau aktifkan alamat pengiriman terlebih dahulu.');
    return;
  }

  const metode = document.querySelector('.method-option.selected').dataset.method;
  const mode   = btn.dataset.mode;

  // rakit data konfirmasi
  let total = 0;
  if (mode === 'multi') {
    const el = document.getElementById('checkoutMultiData');
    const multi = el ? JSON.parse(el.textContent) : null;
    if (!multi || !multi.items || !multi.items.length) {
      alert('Data pesanan tidak ditemukan.');
      return;
    }
    total = Number(btn.dataset.total || multi.grandTotal || 0);
    currentData = {
      mode: 'multi',
      idAlamat: btn.dataset.idAlamat,
      metode: metode,
      items: multi.items // [{id_produk, qty, harga, subtotal, ...}]
    };
  } else {
    total = Number(btn.dataset.total || 0);
    currentData = {
      mode: 'single',
      idProduk: btn.dataset.idProduk,
      harga: btn.dataset.harga,
      qty: btn.dataset.qty,
      idAlamat: btn.dataset.idAlamat,
      metode: metode
    };
  }

  const modal = new bootstrap.Modal(document.getElementById('confirmModal'));
  document.getElementById('confirmText').innerHTML = `
    Apakah Anda yakin ingin memesan dengan metode 
    <b>${metode.toUpperCase()}</b>?<br>
    Total Pembayaran: <b>Rp ${new Intl.NumberFormat('id-ID').format(total)}</b>
  `;
  modal.show();

  document.getElementById('confirmYesBtn').onclick = function(){ buatPesanan(btn, modal); };
}

function buatPesanan(btn, modal){
  modal.hide();
  btn.disabled = true;
  btn.textContent = 'Memproses...';

  const mode = currentData.mode;
  const endpoint = (mode === 'multi')
    ? '<?= base_url("pemesanan/simpan-batch") ?>'
    : '<?= base_url("pemesanan/simpan") ?>';

  let body, fetchOpts;

  if (mode === 'multi') {
    // kirim JSON batch: { id_alamat, metode, items: [{id_produk, qty}, ...] }
    const items = currentData.items.map(it => ({ id_produk: it.id_produk, qty: it.qty }));
    body = JSON.stringify({
      id_alamat: currentData.idAlamat,
      metode: currentData.metode,
      items: items
    });
    fetchOpts = {
      method: 'POST',
      headers: {'Content-Type': 'application/json'},
      body: body
    };
  } else {
    // single item pakai FormData (endpoint lama)
    const formData = new FormData();
    formData.append('id_produk', currentData.idProduk);
    formData.append('qty', currentData.qty);
    formData.append('harga', currentData.harga);
    formData.append('id_alamat', currentData.idAlamat);
    formData.append('metode', currentData.metode);
    fetchOpts = { method: 'POST', body: formData };
  }

  fetch(endpoint, fetchOpts)
    .then(res => res.json())
    .then(result => {
      if (result && result.success) {
        const successModal = new bootstrap.Modal(document.getElementById('successModal'));
        document.getElementById('successText').innerHTML = `
          Status Pesanan: <b>${result.status ?? 'diproses'}</b><br>
          Terima kasih telah berbelanja di <b>FarmUnand</b>! 🛒
        `;
        successModal.show();
      } else {
        alert(result?.message || '❌ Gagal membuat pesanan.');
      }
    })
    .catch(() => alert('Terjadi kesalahan koneksi.'))
    .finally(() => {
      btn.disabled = false;
      btn.textContent = (mode === 'multi') ? 'Buat Pesanan (Semua)' : 'Buat Pesanan';
    });
}
</script>

</body>
</html>
