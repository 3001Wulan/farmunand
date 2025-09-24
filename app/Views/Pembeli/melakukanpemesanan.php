<!DOCTYPE html>
<html lang="id">
  <head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pemesanan</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
      html, body {
        margin: 0;
        padding: 0;
        height: 100%;
        background: #f8f9fa;
      }

      .content {
        margin-left: 240px;
        padding: 30px;
      }
      .product-image {
        width: 150px;
        height: 120px;
        background: #f0f0f0;
        border-radius: 5px;
        overflow: hidden;
        flex-shrink: 0;
      }
      .product-image img { width: 100%; height: 100%; object-fit: cover; }
      .product-info .product-name { font-size: 18px; font-weight: bold; color: #333; }
      .product-info .product-description { font-size: 15px; color: #555; }
      .product-info .product-weight { font-size: 14px; color: #666; }
      .product-info .product-price { font-size: 15px; color: #198754; font-weight: bold; margin-top: 8px; }
      .ubah-btn { background: #198754; border: none; padding: 6px 14px; font-size: 13px; color: white; border-radius: 4px; float: right; cursor: pointer; }
      .ubah-btn:hover { background: #145c32; }
      .method-option { background: #fff; padding: 12px 15px; border-radius: 4px; border: 2px solid #999; margin-bottom: 8px; cursor: pointer; transition: all 0.3s; font-size: 14px; }
      .method-option:hover { border-color: #198754; background: #d9f3e6; }
      .method-option.selected { border-color: #198754; background: #d9f3e6; }
      .method-text { font-weight: 500; color: #333; }
      .btn-order { background: #198754; border: none; padding: 10px 22px; font-size: 14px; color: white; border-radius: 4px; cursor: pointer; }
      .btn-order:hover { background: #145c32; }
      .modal-overlay {
        display: none;
        position: fixed;
        top: 0; left: 0;
        width: 100%; height: 100%;
        background: rgba(0,0,0,0.5);
        z-index: 1050;
        justify-content: center;
        align-items: center;
      }
      .modal-content {
        background: white;
        border-radius: 8px;
        padding: 20px;
        width: 90%;
        max-width: 500px;
        max-height: 80vh;
        overflow-y: auto;
        position: relative;
      }
      .modal-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; border-bottom: 1px solid #eee; padding-bottom: 15px; }
      .modal-title { font-size: 18px; font-weight: bold; color: #198754; }
      .close-btn { position: absolute; top: 15px; right: 15px; background: none; border: none; font-size: 24px; color: #666; cursor: pointer; }
      .close-btn:hover { color: #333; }
      .option-item { display: flex; align-items: center; padding: 12px; margin: 8px 0; border: 2px solid #eee; border-radius: 8px; cursor: pointer; transition: all 0.3s; }
      .option-item:hover { border-color: #198754; background: #f8fff8; }
      .option-item.selected { border-color: #198754; background: #d9f3e6; }
      .option-logo { width: 40px; height: 40px; margin-right: 12px; border-radius: 6px; display: flex; align-items: center; justify-content: center; font-size: 18px; background: #f0f0f0; }
      .option-text { font-weight: 500; color: #333; }
    </style>
  </head>
<body>

<!-- Sidebar -->
<?= $this->include('layout/sidebar') ?>

<!-- Content -->
<div class="content">
  <h3 class="mb-4 text-success">Pemesanan</h3>

  <!-- Alamat Pemesanan -->
  <div class="card mb-3">
    <div class="card-header bg-success text-white">Alamat Pemesanan</div>
    <div class="card-body">
      <p><b><?= esc($pesanan['nama'] ?? 'Nama Tidak Ada'); ?></b> | +62 821 6738 3190</p>
      <p class="text-muted">
        Kost XYZ, Jalan Mohamad Hatta, RT.2/RW.1, Pasar Baru, Padang<br>
        PAUH, KOTA PADANG, SUMATERA BARAT, ID, 25162
      </p>
      <button class="ubah-btn" onclick="ubahAlamat()">Ubah</button>
    </div>
  </div>

  <!-- Detail Pemesanan -->
  <div class="card mb-3">
    <div class="card-header bg-success text-white">Detail Pemesanan</div>
    <div class="card-body d-flex gap-3">
      <div class="product-image">
        <img src="<?= base_url('assets/images/sapi.jpg') ?>" alt="<?= esc($pesanan['produk'] ?? 'Produk'); ?>">
      </div>
      <div class="product-info">
        <div class="product-name">FarmUnand</div>
        <div class="product-description"><?= esc($pesanan['produk'] ?? 'Produk Tidak Ada'); ?></div>
        <div class="product-weight"><?= esc($pesanan['quantity'] ?? '0'); ?> pcs</div>
        <div class="product-price">Total Pembayaran: Rp<?= number_format($pesanan['total'] ?? 0, 0, ',', '.'); ?></div>
      </div>
    </div>
  </div>

  <!-- Metode Pembayaran -->
  <div class="card mb-3">
    <div class="card-header bg-success text-white">Metode Pembayaran</div>
    <div class="card-body">
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
  </div>

  <!-- Tombol Pesan -->
  <div class="text-end">
    <button class="btn-order" onclick="buatPesanan(event)">Buat Pesanan</button>
  </div>
</div>

<!-- Modal Bank -->
<div class="modal-overlay" id="bankModal">
  <div class="modal-content">
    <button class="close-btn" onclick="closeModal('bankModal')">&times;</button>
    <div class="modal-header">
      <h3 class="modal-title">Pilih Bank Transfer</h3>
    </div>
    <div class="option-item" data-bank="bca">
      <div class="option-logo" style="background:#003d82;color:white;">BCA</div>
      <div class="option-text">Bank Central Asia (BCA)</div>
    </div>
    <div class="option-item" data-bank="mandiri">
      <div class="option-logo" style="background:#003d82;color:white;">MDR</div>
      <div class="option-text">Bank Mandiri</div>
    </div>
    <div class="option-item" data-bank="bri">
      <div class="option-logo" style="background:#003d82;color:white;">BRI</div>
      <div class="option-text">Bank Rakyat Indonesia (BRI)</div>
    </div>
    <div class="option-item" data-bank="bni">
      <div class="option-logo" style="background:#f37521;color:white;">BNI</div>
      <div class="option-text">Bank Negara Indonesia (BNI)</div>
    </div>
  </div>
</div>

<!-- Modal E-Wallet -->
<div class="modal-overlay" id="ewalletModal">
  <div class="modal-content">
    <button class="close-btn" onclick="closeModal('ewalletModal')">&times;</button>
    <div class="modal-header">
      <h3 class="modal-title">Pilih E-Wallet</h3>
    </div>
    <div class="option-item" data-ewallet="gopay">
      <div class="option-logo" style="background:#00aa5b;color:white;">GP</div>
      <div class="option-text">GoPay</div>
    </div>
    <div class="option-item" data-ewallet="ovo">
      <div class="option-logo" style="background:#5c2d91;color:white;">OVO</div>
      <div class="option-text">OVO</div>
    </div>
    <div class="option-item" data-ewallet="dana">
      <div class="option-logo" style="background:#118eea;color:white;">DANA</div>
      <div class="option-text">DANA</div>
    </div>
    <div class="option-item" data-ewallet="shopeepay">
      <div class="option-logo" style="background:#f53d2d;color:white;">SP</div>
      <div class="option-text">ShopeePay</div>
    </div>
  </div>
</div>

<script>
let selectedBank = '';
let selectedEwallet = '';

document.querySelectorAll('.method-option').forEach(method => {
  method.addEventListener('click', function() {
    const methodType = this.getAttribute('data-method');
    if (methodType === 'transfer') { openModal('bankModal'); return; }
    if (methodType === 'ewallet') { openModal('ewalletModal'); return; }
    document.querySelectorAll('.method-option').forEach(pm => pm.classList.remove('selected'));
    this.classList.add('selected');
  });
});

// Payment method selection
document.querySelectorAll('.method-option').forEach(method => {
  method.addEventListener('click', function() {
    const methodType = this.getAttribute('data-method');

    if (methodType === 'transfer') { 
      openModal('bankModal'); 
      return; 
    }
    if (methodType === 'ewallet') { 
      openModal('ewalletModal'); 
      return; 
    }

    // Kalau COD dipilih
    if (methodType === 'cod') {
      // Reset semua dulu
      document.querySelectorAll('.method-option').forEach(pm => pm.classList.remove('selected'));
      this.classList.add('selected');

      // Reset teks bank & ewallet ke default
      document.querySelector('[data-method="transfer"] .method-text').textContent = '🏦 Transfer Bank';
      document.querySelector('[data-method="ewallet"] .method-text').textContent = '📱 E-Wallet';
    }
  });
});

// Bank selection
document.querySelectorAll('#bankModal .option-item').forEach(item => {
  item.addEventListener('click', function() {
    selectedBank = this.getAttribute('data-bank');
    document.querySelectorAll('.method-option').forEach(pm => pm.classList.remove('selected'));
    const bankText = this.querySelector('.option-text').textContent;
    document.querySelector('[data-method="transfer"] .method-text').textContent = `🏦 ${bankText}`;
    document.querySelector('[data-method="transfer"]').classList.add('selected');

    // Reset ewallet ke default
    document.querySelector('[data-method="ewallet"] .method-text').textContent = '📱 E-Wallet';

    closeModal('bankModal');
  });
});

// Ewallet selection
document.querySelectorAll('#ewalletModal .option-item').forEach(item => {
  item.addEventListener('click', function() {
    selectedEwallet = this.getAttribute('data-ewallet');
    document.querySelectorAll('.method-option').forEach(pm => pm.classList.remove('selected'));
    const ewalletText = this.querySelector('.option-text').textContent;
    document.querySelector('[data-method="ewallet"] .method-text').textContent = `📱 ${ewalletText}`;
    document.querySelector('[data-method="ewallet"]').classList.add('selected');

    // Reset bank ke default
    document.querySelector('[data-method="transfer"] .method-text').textContent = '🏦 Transfer Bank';

    closeModal('ewalletModal');
  });
});


function openModal(id) { document.getElementById(id).style.display = 'flex'; }
function closeModal(id) { document.getElementById(id).style.display = 'none'; }

document.querySelectorAll('.modal-overlay').forEach(overlay => {
  overlay.addEventListener('click', function(e) { if (e.target === this) this.style.display = 'none'; });
});

function ubahAlamat() { alert('🔄 Mengubah alamat pengiriman...'); }

function buatPesanan(event) {
  const selectedMethod = document.querySelector('.method-option.selected .method-text').textContent;
  if (confirm(`Konfirmasi pembayaran dengan ${selectedMethod}?\n\nTotal: Rp<?= number_format($pesanan['total'] ?? 0, 0, ',', '.'); ?>`)) {
    const btn = event.target;
    btn.textContent = 'Memproses...';
    btn.disabled = true;
    setTimeout(() => {
      alert('✅ Pesanan berhasil dibuat!\n📧 Email konfirmasi telah dikirim.');
      btn.textContent = 'Buat Pesanan';
      btn.disabled = false;
    }, 2000);
  }
}
</script>
</body>
</html>
