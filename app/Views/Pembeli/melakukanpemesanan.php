<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Pemesanan</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">

  <style>
    :root {
      --brand: #198754;
      --brand-dark: #145c32;
      --muted: #f8f9fa;
    }
    html, body { margin:0; padding:0; height:100%; background:var(--muted); }
    .content { margin-left:250px; padding:30px; }

    /* Header halaman dengan tombol kembali di kanan */
    .page-head {
      display:flex; justify-content:space-between; align-items:center; margin-bottom:18px;
    }
    .page-title {
      font-weight:700; color:var(--brand); margin:0;
    }
    .btn-back {
      background:white; color:var(--brand); border:1px solid var(--brand);
    }
    .btn-back:hover {
      background:var(--brand); color:white;
    }

    /* Section bergaya "judul di tabel hijau" */
    .section {
      border-radius:12px; box-shadow:0 6px 18px rgba(0,0,0,0.06); overflow:hidden; margin-bottom:16px; background:#fff;
    }
    .section-title {
      background: linear-gradient(90deg, var(--brand), #20c997);
      color:#fff; font-weight:700; padding:12px 16px;
      display:flex; align-items:center; justify-content:space-between;
    }
    .section-body { padding:16px; }

    /* Alamat */
    .address-line { margin:0; }
    .ubah-btn {
      background:#fff; color:var(--brand); border:1px solid #fff; 
      padding:6px 12px; border-radius:6px; text-decoration:none;
    }
    .ubah-btn:hover { background:rgba(255,255,255,0.15); color:#fff; }

    /* Detail produk */
    .product-wrap { display:flex; gap:16px; align-items:flex-start; }
    .product-image {
      width:150px; height:120px; border-radius:8px; overflow:hidden; background:#f0f0f0; flex-shrink:0;
      border:1px solid #e9ecef;
    }
    .product-image img { width:100%; height:100%; object-fit:cover; }
    .product-info .name { font-size:18px; font-weight:700; color:#333; }
    .product-info .desc { font-size:14px; color:#666; margin-top:6px; }
    .pill {
      display:inline-block; padding:2px 10px; border-radius:999px; font-size:13px; margin-right:6px;
      border:1px solid #e9ecef; background:#f8f9fa;
    }
    .total {
      font-weight:700; color:var(--brand); font-size:16px; margin-top:8px;
    }

    /* Metode pembayaran */
    .pay-grid { display:grid; gap:10px; grid-template-columns: 1fr; }
    .method-option {
      background:#fff; padding:12px 14px; border-radius:8px; border:2px solid #e9ecef;
      cursor:pointer; transition:.2s; display:flex; align-items:center; gap:10px;
    }
    .method-option:hover { border-color:var(--brand); background:#f6fff8; }
    .method-option.selected { border-color:var(--brand); background:#e9f8ef; }
    .method-text { font-weight:600; color:#333; }

    /* Modal custom simple */
    .modal-overlay { display:none; position:fixed; inset:0; background:rgba(0,0,0,0.5); z-index:1050; justify-content:center; align-items:center; }
    .modal-content {
      background:white; border-radius:12px; width:92%; max-width:520px; max-height:78vh; overflow:auto; position:relative; box-shadow:0 10px 30px rgba(0,0,0,.2);
    }
    .modal-header { padding:14px 16px; border-bottom:1px solid #eee; display:flex; justify-content:space-between; align-items:center; }
    .modal-title { margin:0; font-weight:700; color:var(--brand); }
    .close-btn { background:none; border:none; font-size:22px; color:#666; }
    .close-btn:hover { color:#333; }

    .option-item { display:flex; align-items:center; gap:12px; padding:12px 14px; margin:10px 16px; border:2px solid #eee; border-radius:10px; cursor:pointer; transition:.2s; }
    .option-item:hover { border-color:var(--brand); background:#f6fff8; }
    .option-item.selected { border-color:var(--brand); background:#e9f8ef; }
    .option-logo { width:40px; height:40px; border-radius:8px; display:flex; align-items:center; justify-content:center; font-weight:700; color:#fff; }

    /* Tombol pesan */
    .footer-actions { display:flex; justify-content:flex-end; margin-top:14px; }
    .btn-order {
      background:var(--brand); color:white; border:none; padding:10px 20px; border-radius:8px; font-weight:600;
    }
    .btn-order:hover { background:var(--brand-dark); }

    /* Responsif */
    @media (max-width: 992px) {
      .content { margin-left:0; padding:18px; }
      .product-wrap { flex-direction:column; }
      .footer-actions { justify-content:stretch; }
      .btn-order { width:100%; }
    }
  </style>
</head>
<body>

<!-- Sidebar -->
<?= $this->include('layout/sidebar') ?>

<!-- Content -->
<div class="content">

  <div class="page-head">
    <h3 class="page-title">Pemesanan</h3>
    <?php 
      // back ke detailproduk/{id} jika tersedia, jika tidak fallback ke history.back()
      $backHref = isset($checkout['id_produk'])
        ? base_url('detailproduk/'.$checkout['id_produk'])
        : (isset($produk['id_produk']) ? base_url('detailproduk/'.$produk['id_produk']) : 'javascript:history.back()');
    ?>
    <a href="<?= $backHref ?>" class="btn btn-back">Kembali</a>
  </div>

  <!-- Alamat Pemesanan -->
  <div class="section">
    <div class="section-title">
      <span>Alamat Pemesanan</span>
      <a href="<?= base_url('memilihalamat') ?>" class="ubah-btn">Ubah / Tambah</a>
    </div>
    <div class="section-body">
      <?php if (!empty($alamat)): ?>
        <?php $alamatAktif = $alamat[0]; ?>
        <p class="address-line mb-1"><b><?= esc($alamatAktif['nama_penerima']); ?></b> | <?= esc($alamatAktif['no_telepon']); ?></p>
        <p class="text-muted mb-0">
          <?= esc($alamatAktif['jalan']); ?>, <?= esc($alamatAktif['kota']); ?>, <?= esc($alamatAktif['provinsi']); ?>, <?= esc($alamatAktif['kode_pos']); ?>
        </p>
      <?php else: ?>
        <div class="alert alert-warning mb-0">Belum ada alamat aktif. Silakan tambahkan alamat terlebih dahulu.</div>
      <?php endif; ?>
    </div>
  </div>

  <!-- Detail Pemesanan -->
  <div class="section">
    <div class="section-title">Detail Pemesanan</div>
    <div class="section-body">
      <?php
        $namaProduk = $checkout['nama_produk'] ?? ($pesanan['produk'] ?? '-');
        $deskripsi  = $checkout['deskripsi']   ?? ($pesanan['deskripsi'] ?? '-');
        $qty        = $checkout['qty']         ?? ($pesanan['quantity'] ?? 0);
        $harga      = $checkout['harga']       ?? 0;
        $subtotal   = $checkout['subtotal']    ?? ($pesanan['total'] ?? 0);
        $fotoPath   = isset($checkout['foto']) ? base_url('uploads/produk/'.$checkout['foto'])
                                               : base_url('assets/images/sapi.jpg');
      ?>
      <div class="product-wrap">
        <div class="product-image">
          <img src="<?= $fotoPath ?>" alt="<?= esc($namaProduk) ?>">
        </div>
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
    </div>
  </div>

  <!-- Metode Pembayaran -->
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
      <div class="footer-actions">
        <button class="btn-order" onclick="buatPesanan(event)"
                data-total="<?= (int)$subtotal ?>"
                data-has-address="<?= !empty($alamat) ? '1' : '0' ?>">
          Buat Pesanan
        </button>
      </div>
    </div>
  </div>
</div>

<!-- Modal Bank -->
<div class="modal-overlay" id="bankModal">
  <div class="modal-content">
    <div class="modal-header">
      <h5 class="modal-title">Pilih Bank Transfer</h5>
      <button class="close-btn" onclick="closeModal('bankModal')">&times;</button>
    </div>
    <div class="option-item" data-bank="bca">
      <div class="option-logo" style="background:#003d82;">BCA</div>
      <div class="option-text">Bank Central Asia (BCA)</div>
    </div>
    <div class="option-item" data-bank="mandiri">
      <div class="option-logo" style="background:#003d82;">MDR</div>
      <div class="option-text">Bank Mandiri</div>
    </div>
    <div class="option-item" data-bank="bri">
      <div class="option-logo" style="background:#003d82;">BRI</div>
      <div class="option-text">Bank Rakyat Indonesia (BRI)</div>
    </div>
    <div class="option-item" data-bank="bni">
      <div class="option-logo" style="background:#f37521;">BNI</div>
      <div class="option-text">Bank Negara Indonesia (BNI)</div>
    </div>
  </div>
</div>

<!-- Modal E-Wallet -->
<div class="modal-overlay" id="ewalletModal">
  <div class="modal-content">
    <div class="modal-header">
      <h5 class="modal-title">Pilih E-Wallet</h5>
      <button class="close-btn" onclick="closeModal('ewalletModal')">&times;</button>
    </div>
    <div class="option-item" data-ewallet="gopay">
      <div class="option-logo" style="background:#00aa5b;">GP</div>
      <div class="option-text">GoPay</div>
    </div>
    <div class="option-item" data-ewallet="ovo">
      <div class="option-logo" style="background:#5c2d91;">OVO</div>
      <div class="option-text">OVO</div>
    </div>
    <div class="option-item" data-ewallet="dana">
      <div class="option-logo" style="background:#118eea;">DANA</div>
      <div class="option-text">DANA</div>
    </div>
    <div class="option-item" data-ewallet="shopeepay">
      <div class="option-logo" style="background:#f53d2d;">SP</div>
      <div class="option-text">ShopeePay</div>
    </div>
  </div>
</div>

<script>
let selectedBank = '';
let selectedEwallet = '';

function openModal(id){ document.getElementById(id).style.display = 'flex'; }
function closeModal(id){ document.getElementById(id).style.display = 'none'; }
document.querySelectorAll('.modal-overlay').forEach(ov => {
  ov.addEventListener('click', e => { if (e.target === ov) closeModal(ov.id); });
});

// pilih metode
document.querySelectorAll('.method-option').forEach(opt => {
  opt.addEventListener('click', function(){
    const t = this.getAttribute('data-method');
    if (t === 'transfer') { openModal('bankModal'); return; }
    if (t === 'ewallet')  { openModal('ewalletModal'); return; }
    document.querySelectorAll('.method-option').forEach(x => x.classList.remove('selected'));
    this.classList.add('selected');
    // reset teks
    document.querySelector('[data-method="transfer"] .method-text').textContent = '🏦 Transfer Bank';
    document.querySelector('[data-method="ewallet"] .method-text').textContent  = '📱 E-Wallet';
  });
});

// pilih bank
document.querySelectorAll('#bankModal .option-item').forEach(item => {
  item.addEventListener('click', function(){
    selectedBank = this.getAttribute('data-bank');
    const label  = this.querySelector('.option-text').textContent;
    document.querySelector('[data-method="transfer"] .method-text').textContent = `🏦 ${label}`;
    document.querySelectorAll('.method-option').forEach(x => x.classList.remove('selected'));
    document.querySelector('[data-method="transfer"]').classList.add('selected');
    document.querySelector('[data-method="ewallet"] .method-text').textContent = '📱 E-Wallet';
    closeModal('bankModal');
  });
});

// pilih ewallet
document.querySelectorAll('#ewalletModal .option-item').forEach(item => {
  item.addEventListener('click', function(){
    selectedEwallet = this.getAttribute('data-ewallet');
    const label  = this.querySelector('.option-text').textContent;
    document.querySelector('[data-method="ewallet"] .method-text').textContent = `📱 ${label}`;
    document.querySelectorAll('.method-option').forEach(x => x.classList.remove('selected'));
    document.querySelector('[data-method="ewallet"]').classList.add('selected');
    document.querySelector('[data-method="transfer"] .method-text').textContent = '🏦 Transfer Bank';
    closeModal('ewalletModal');
  });
});

function buatPesanan(e){
  const btn = e.target;
  const total = btn.getAttribute('data-total') || 0;
  const hasAddress = btn.getAttribute('data-has-address') === '1';

  if (!hasAddress) {
    alert('Silakan tambahkan/aktifkan alamat pengiriman terlebih dahulu.');
    return;
  }

  const selected = document.querySelector('.method-option.selected .method-text').textContent;
  if (confirm(`Konfirmasi pembayaran dengan ${selected}?\n\nTotal: Rp ${new Intl.NumberFormat('id-ID').format(total)}`)) {
    btn.disabled = true; btn.textContent = 'Memproses...';
    // TODO: ubah ke form submit ke endpoint pembayaran/pemesanan
    setTimeout(() => {
      alert('✅ Pesanan berhasil dibuat!\n📧 Email konfirmasi telah dikirim.');
      btn.disabled = false; btn.textContent = 'Buat Pesanan';
    }, 1200);
  }
}
</script>
</body>
</html>
