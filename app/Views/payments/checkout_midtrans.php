<!-- app/Views/payments/checkout_midtrans.php -->
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="utf-8"/>
  <meta name="viewport" content="width=device-width, initial-scale=1"/>
  <title>Bayar Pesanan</title>

  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <style>
    :root{ --brand:#198754; --brand-dark:#145c32; --muted:#f8f9fa; }
    html,body{height:100%;background:var(--muted)}
    .content{ margin-left:250px; padding:28px; }
    .section{ background:#fff; border-radius:12px; box-shadow:0 6px 18px rgba(0,0,0,.06); overflow:hidden; margin-bottom:16px; }
    .section-title{ background:linear-gradient(90deg,var(--brand),#20c997); color:#fff; padding:12px 16px; font-weight:700; }
    .section-body{ padding:16px; }
    .btn-pill{ border-radius:999px; }
  </style>
</head>
<body>

<?= $this->include('layout/sidebar') ?>

<div class="content">

  <div class="section">
    <div class="section-title">Ringkasan Pesanan</div>
    <div class="section-body">
      <?php if(!empty($items)): ?>
        <div class="table-responsive">
          <table class="table table-sm align-middle">
            <thead>
              <tr>
                <th>#</th>
                <th>ID Produk</th>
                <th>Qty</th>
              </tr>
            </thead>
            <tbody>
            <?php $i=1; foreach($items as $it): ?>
              <tr>
                <td><?= $i++ ?></td>
                <td><?= esc($it['id_produk']) ?></td>
                <td><?= esc($it['qty']) ?></td>
              </tr>
            <?php endforeach; ?>
            </tbody>
          </table>
        </div>
      <?php else: ?>
        <div class="text-muted">Tidak ada item.</div>
      <?php endif; ?>
    </div>
  </div>

  <div class="section">
    <div class="section-title">Pilih Pembayaran</div>
    <div class="section-body">
      <div class="d-flex gap-2 flex-wrap">
        <!-- COD (kembali ke flow lama kamu) -->
        <form action="<?= base_url('melakukanpemesanan/simpanBatch') ?>" method="post" class="d-inline">
          <?= csrf_field() ?>
          <input type="hidden" name="payload_json" id="payload_json_cod">
          <button type="submit" class="btn btn-secondary btn-pill">Bayar COD</button>
        </form>

        <!-- Bayar Online (Midtrans) -->
        <button type="button" class="btn btn-success btn-pill"
                onclick="bayarMidtrans(<?= (int)($id_alamat ?? 0) ?>, itemsFromPHP)">
          Bayar Online
        </button>
      </div>
      <div class="small text-muted mt-2">
        Klik <b>Bayar Online</b> akan membuka popup Midtrans Snap (Sandbox).
      </div>
    </div>
  </div>

</div>

<!-- SNAP JS -->
<script src="https://app.sandbox.midtrans.com/snap/snap.js"
        data-client-key="<?= esc(env('MIDTRANS_CLIENT_KEY')) ?>"></script>

<script>
// Data items dari PHP -> JS
const itemsFromPHP = <?= json_encode($items ?? []) ?>;

// Jika kamu butuh kirim ulang payload ke COD endpoint lama (optional)
document.getElementById('payload_json_cod')?.setAttribute('value', JSON.stringify({
  id_alamat: <?= json_encode($id_alamat ?? 0) ?>,
  metode: 'cod',
  items: itemsFromPHP
}));

async function bayarMidtrans(idAlamat, items) {
  try {
    const resp = await fetch('<?= base_url('payments/create') ?>', {
      method: 'POST',
      headers: {'Content-Type':'application/json'},
      body: JSON.stringify({ id_alamat: idAlamat, items })
    });

    const data = await resp.json();
    if (!data.success) {
      alert(data.message || 'Gagal membuat transaksi');
      return;
    }

    window.snap.pay(data.snapToken, {
      onSuccess:   () => window.location.href = '<?= base_url('payments/finish') ?>',
      onPending:   () => window.location.href = '<?= base_url('payments/unfinish') ?>',
      onError:     () => window.location.href = '<?= base_url('payments/error') ?>',
      onClose:     () => {} // user menutup popup
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
