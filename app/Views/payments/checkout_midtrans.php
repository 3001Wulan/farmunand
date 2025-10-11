<!-- app/Views/payments/checkout_midtrans.php -->
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="utf-8"/>
  <meta name="viewport" content="width=device-width, initial-scale=1"/>
  <title>Bayar Pesanan - FarmUnand</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
  <style>
    :root{ --brand:#198754; --brand-dark:#145c32; --muted:#f8f9fa; }
    html,body{height:100%;background:var(--muted)}
    .content{ margin-left:250px; padding:28px; }
    @media (max-width: 992px){ .content{ margin-left:0; padding:18px; } }

    .hero{ background:linear-gradient(135deg,#198754,#28a745); color:#fff;
           border-radius:14px; padding:20px; box-shadow:0 8px 20px rgba(0,0,0,.08); margin-bottom:16px; }
    .section{ background:#fff; border-radius:14px; box-shadow:0 10px 22px rgba(0,0,0,.06); overflow:hidden; }
    .section + .section{ margin-top:14px; }
    .section-head{ background:#f7fff9; border-bottom:1px solid #e9f4ee; padding:14px 16px; font-weight:700; color:#145c32; }
    .section-body{ padding:16px; }
    .total-line{ display:flex; justify-content:space-between; font-weight:700; font-size:1.05rem; }
    .btn-pill{ border-radius:999px; }
    .btn-success{ background:var(--brand); border:none; }
    .btn-success:hover{ background:var(--brand-dark); }
  </style>
</head>

<body>
  <!-- Sidebar -->
  <?= $this->include('layout/sidebar') ?>

  <!-- Content -->
  <div class="content">
    <div class="hero d-flex align-items-center justify-content-between">
      <h5 class="m-0 fw-bold">Pembayaran Pesanan</h5>
      <div class="small">Pastikan data pesanan sudah benar, lalu pilih metode pembayaran.</div>
    </div>

    <!-- Ringkasan Pesanan -->
    <div class="section">
      <div class="section-head">Ringkasan Pesanan</div>
      <div class="section-body">
        <?php
          $items = $items ?? [];
          $grand = 0;
        ?>
        <?php if(!empty($items)): ?>
          <div class="table-responsive">
            <table class="table align-middle mb-2">
              <thead class="table-light">
                <tr>
                  <th style="width:56px">#</th>
                  <th>Produk</th>
                  <th class="text-center" style="width:120px">Qty</th>
                  <th class="text-end" style="width:160px">Harga</th>
                  <th class="text-end" style="width:180px">Subtotal</th>
                </tr>
              </thead>
              <tbody>
                <?php $i=1; foreach($items as $it):
                  $nama   = $it['nama_produk'] ?? ('Produk #'.$it['id_produk']);
                  $qty    = (int)($it['qty'] ?? 1);
                  $harga  = (int)($it['harga'] ?? 0);
                  $sub    = $qty * $harga;
                  $grand += $sub;
                ?>
                <tr>
                  <td><?= $i++ ?></td>
                  <td><?= esc($nama) ?></td>
                  <td class="text-center"><?= $qty ?></td>
                  <td class="text-end">Rp <?= number_format($harga,0,',','.') ?></td>
                  <td class="text-end fw-semibold">Rp <?= number_format($sub,0,',','.') ?></td>
                </tr>
                <?php endforeach; ?>
              </tbody>
            </table>
          </div>
          <div class="total-line">
            <span>Total</span>
            <span>Rp <?= number_format($grand,0,',','.') ?></span>
          </div>
        <?php else: ?>
          <div class="text-muted">Tidak ada item.</div>
        <?php endif; ?>
      </div>
    </div>

    <!-- Pilihan Pembayaran -->
    <div class="section">
      <div class="section-head">Metode Pembayaran</div>
      <div class="section-body">
        <div class="row g-2">
          <div class="col-auto">
            <!-- COD (opsional; gunakan endpoint milikmu) -->
            <form action="<?= base_url('melakukanpemesanan/simpanBatch') ?>" method="post" class="d-inline">
              <?= csrf_field() ?>
              <input type="hidden" name="payload_json" id="payload_json_cod">
              <button type="submit" class="btn btn-outline-secondary btn-pill">
                <i class="bi bi-cash-coin me-1"></i> Bayar COD
              </button>
            </form>
          </div>
          <div class="col-auto">
            <button type="button" class="btn btn-success btn-pill"
                    onclick="bayarMidtrans(<?= (int)($id_alamat ?? 0) ?>, itemsFromPHP)">
              <i class="bi bi-credit-card-2-front me-1"></i> Bayar Online
            </button>
          </div>
        </div>
        <div class="small text-muted mt-2">
          Klik <b>Bayar Online</b> untuk membuka popup Midtrans Snap (Sandbox).
        </div>
      </div>
    </div>
  </div>

  <!-- SNAP JS -->
  <script src="https://app.sandbox.midtrans.com/snap/snap.js"
          data-client-key="<?= esc(env('MIDTRANS_CLIENT_KEY')) ?>"></script>

  <script>
  // Data dari PHP -> JS
  const itemsFromPHP = <?= json_encode($items ?? []) ?>;

  // Siapkan payload COD bila diperlukan
  (function(){
    const cod = document.getElementById('payload_json_cod');
    if (cod) {
      cod.value = JSON.stringify({
        id_alamat: <?= json_encode($id_alamat ?? 0) ?>,
        metode: 'cod',
        items: itemsFromPHP
      });
    }
  })();

  // Helper fetch yang otomatis pakai CSRF bila tersedia (sidebar memasang window.secureFetch)
  async function xfetch(url, opts){
    if (window.secureFetch) return window.secureFetch(url, opts);
    // fallback manual
    opts = opts || {};
    opts.headers = Object.assign({'Content-Type':'application/json'}, opts.headers||{});
    try{
      const metaToken  = document.querySelector('meta[name="csrf-token"]')?.content;
      const metaHeader = document.querySelector('meta[name="csrf-header"]')?.content || 'X-CSRF-TOKEN';
      if (metaToken) opts.headers[metaHeader] = metaToken;
    }catch(e){}
    return fetch(url, opts);
  }

  async function bayarMidtrans(idAlamat, items) {
    try {
      const resp = await xfetch('<?= base_url('payments/create') ?>', {
        method: 'POST',
        body: JSON.stringify({ id_alamat: idAlamat, items })
      });

      const data = await resp.json();
      if (!data?.success) {
        alert(data?.message || 'Gagal membuat transaksi');
        return;
      }

      window.snap.pay(data.snapToken, {
        onSuccess: () => window.location.href = '<?= base_url('payments/finish') ?>',
        onPending: () => window.location.href = '<?= base_url('payments/unfinish') ?>',
        onError:   () => window.location.href = '<?= base_url('payments/error') ?>',
        onClose:   () => {} // user menutup popup
      });

    } catch(e) {
      console.error(e);
      alert('Terjadi kesalahan koneksi.');
    }
  }
  </script>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
