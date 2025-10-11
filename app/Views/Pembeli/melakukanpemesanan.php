<!DOCTYPE html>
<html lang="id">
  <head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pemesanan</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
    <style>
      :root { --brand:#198754; --brand-dark:#145c32; --muted:#f8f9fa; }
      html, body { margin:0; padding:0; height:100%; background:var(--muted); }
      .content { margin-left:250px; padding:30px; }
      .page-head { display:flex; justify-content:space-between; align-items:center; margin-bottom:18px; }
      .page-title { font-weight:700; color:var(--brand); margin:0; }
      .btn-back { background:#fff; color:var(--brand); border:1px solid var(--brand); }
      .btn-back:hover { background:var(--brand); color:#fff; }
      .section { border-radius:12px; box-shadow:0 6px 18px rgba(0,0,0,0.06); overflow:hidden; margin-bottom:16px; background:#fff; }
      .section-title { background: linear-gradient(90deg, var(--brand), #20c997); color:#fff; font-weight:700; padding:12px 16px; display:flex; align-items:center; justify-content:space-between; }
      .section-body { padding:16px; }
      .product-wrap { display:flex; gap:16px; align-items:flex-start; }
      .product-image { width:150px; height:120px; border-radius:8px; overflow:hidden; border:1px solid #e9ecef; flex-shrink:0; background:#f0f0f0; }
      .product-image img { width:100%; height:100%; object-fit:cover; }
      .pill { display:inline-block; padding:2px 10px; border-radius:999px; font-size:13px; margin-right:6px; border:1px solid #e9ecef; background:#f8f9fa; }
      .total { font-weight:700; color:var(--brand); font-size:16px; margin-top:8px; }
      .pay-grid { display:grid; gap:10px; grid-template-columns: 1fr; }
      .method-option { background:#fff; padding:12px 14px; border-radius:8px; border:2px solid #e9ecef; cursor:pointer; display:flex; align-items:center; gap:10px; }
      .method-option:hover { border-color:var(--brand); background:#f6fff8; }
      .method-option.selected { border-color:var(--brand); background:#e9f8ef; }
      .footer-actions { display:flex; justify-content:flex-end; margin-top:14px; }
      .btn-order { background:var(--brand); color:#fff; border:none; padding:10px 20px; border-radius:8px; font-weight:600; }
      .btn-order:hover { background:var(--brand-dark); }
      @media (max-width: 992px){ .content{margin-left:0;padding:18px} .product-wrap{flex-direction:column} .btn-order{width:100%} }
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
          <a href="<?= base_url('memilihalamat') ?>" class="btn btn-light btn-sm">Ubah / Tambah</a>
        </div>
        <div class="section-body">
          <?php if (!empty($alamat)): $alamatAktif = $alamat[0]; ?>
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
          <?php if (!empty($checkout_multi['items'])): ?>
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

            <!-- data pesanan utk js -->
            <script type="application/json" id="checkoutMultiData">
              <?= json_encode($checkout_multi, JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES) ?>
            </script>

          <?php else: ?>
            <?php
              $namaProduk = $checkout['nama_produk'] ?? '-';
              $deskripsi  = $checkout['deskripsi'] ?? '-';
              $qty        = (int)($checkout['qty'] ?? 0);
              $harga      = (float)($checkout['harga'] ?? 0);
              $subtotal   = $qty * $harga;
              $fotoPath   = isset($checkout['foto']) ? base_url('uploads/produk/'.$checkout['foto']) : base_url('assets/images/no-image.png');
            ?>

            <div class="product-wrap">
              <div class="product-image"><img src="<?= $fotoPath ?>" alt="<?= esc($namaProduk) ?>"></div>
              <div>
                <div class="fw-bold"><?= esc($namaProduk) ?></div>
                <div class="text-muted"><?= esc($deskripsi) ?></div>
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
            <div class="method-option selected" data-method="cod"><div>💵 COD (Cash on Delivery)</div></div>
            <div class="method-option" data-method="online"><div>💳 Bayar Online (Midtrans)</div></div>
          </div>

          <div class="footer-actions mt-3">
            <?php
              $hasAddress = !empty($alamat);
              $idAlamat   = $alamat[0]['id_alamat'] ?? 0;
              $isMulti    = !empty($checkout_multi['items']);
              $totalSemua = $isMulti ? (float)$checkout_multi['grandTotal'] : (float)$subtotal;
            ?>
            <button class="btn-order"
                    data-mode="<?= $isMulti ? 'multi' : 'single' ?>"
                    data-id-alamat="<?= (int)$idAlamat ?>"
                    data-id-produk="<?= (int)($checkout['id_produk'] ?? 0) ?>"
                    data-qty="<?= (int)($checkout['qty'] ?? 0) ?>"
                    data-harga="<?= (float)($checkout['harga'] ?? 0) ?>"
                    data-total="<?= $totalSemua ?>"
                    data-has-address="<?= $hasAddress ? '1' : '0' ?>"
                    onclick="openConfirmModal(event)">
              Buat Pesanan<?= $isMulti ? ' (Semua)' : '' ?>
            </button>
          </div>
        </div>
      </div>
    </div>

    <!-- Modal konfirmasi -->
    <div class="modal fade" id="confirmModal" tabindex="-1">
      <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content text-center">
          <div class="modal-header border-0"><h5 class="fw-bold text-success m-0">Konfirmasi Pemesanan</h5></div>
          <div class="modal-body"><p id="confirmText" class="mb-0 text-secondary"></p></div>
          <div class="modal-footer border-0">
            <button class="btn btn-secondary px-4" data-bs-dismiss="modal">Batal</button>
            <button class="btn btn-success px-4" id="confirmYesBtn">Ya, Pesan Sekarang</button>
          </div>
        </div>
      </div>
    </div>

    <!-- Modal sukses utk COD -->
    <div class="modal fade" id="successModal" tabindex="-1">
      <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content text-center">
          <div class="modal-header border-0"><h5 class="fw-bold text-success m-0">Pesanan Berhasil!</h5></div>
          <div class="modal-body"><p id="successText" class="mb-0 text-secondary"></p></div>
          <div class="modal-footer border-0">
            <button class="btn btn-success px-4" onclick="location.href='<?= base_url('riwayatpesanan') ?>'">Lihat Riwayat</button>
          </div>
        </div>
      </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
      /* ===== CSRF helpers (token & headerName diambil dari meta pada sidebar) ===== */
      function getCsrf() {
        const token  = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
        const header = document.querySelector('meta[name="csrf-header"]')?.getAttribute('content') || 'X-CSRF-TOKEN';
        return { token, header };
      }
      function withCsrf(headers = {}) {
        const { token, header } = getCsrf();
        return Object.assign({}, headers, { [header]: token });
      }
      let currentData = {};
      document.querySelectorAll('.method-option').forEach(opt=>{
        opt.addEventListener('click', function(){
          document.querySelectorAll('.method-option').forEach(x=>x.classList.remove('selected'));
          this.classList.add('selected');
        });
      });

      function openConfirmModal(e){
        const btn = e.currentTarget;
        if (btn.dataset.hasAddress !== '1') { alert('Silakan tambahkan alamat terlebih dahulu.'); return; }

        const mode   = btn.dataset.mode;
        const metode = document.querySelector('.method-option.selected').dataset.method;
        const total  = Number(btn.dataset.total || 0);
        const idAlamat = Number(btn.dataset.idAlamat);

        if (mode === 'multi') {
          const el = document.getElementById('checkoutMultiData');
          const multi = el ? JSON.parse(el.textContent) : null;
          if (!multi || !multi.items?.length) { alert('Data pesanan tidak ditemukan.'); return; }
          currentData = { mode, metode, idAlamat, items: multi.items };
        } else {
          currentData = {
            mode, metode, idAlamat,
            idProduk: Number(btn.dataset.idProduk),
            qty     : Number(btn.dataset.qty),
            harga   : Number(btn.dataset.harga)
          };
        }

        new bootstrap.Modal('#confirmModal').show();
        document.getElementById('confirmText').innerHTML =
          `Apakah Anda yakin ingin menggunakan <b>${metode==='online'?'Bayar Online (Midtrans)':'COD'}</b>?<br>
          Total: <b>Rp ${new Intl.NumberFormat('id-ID').format(total)}</b>`;

        document.getElementById('confirmYesBtn').onclick = () => buatPesanan(btn);
      }

      function buatPesanan(btn){
        const modal = bootstrap.Modal.getInstance(document.getElementById('confirmModal'));
        modal.hide(); btn.disabled = true; btn.textContent = 'Memproses...';

        const { mode, metode, idAlamat } = currentData;

        if (metode === 'online') {
          (async ()=>{
            let items = (mode === 'multi')
              ? currentData.items.map(it => ({id_produk:Number(it.id_produk), qty:Number(it.qty)}))
              : [{id_produk: Number(currentData.idProduk), qty: Number(currentData.qty)}];

            const res  = await fetch('<?= site_url('payments/create') ?>', {
              method: 'POST',
              headers: withCsrf({ 'Content-Type':'application/json' }),
              credentials: 'same-origin',
              body: JSON.stringify({ id_alamat: idAlamat, items })
            });
            const data = await res.json();
            if (!data?.success) { alert(data?.message || 'Gagal membuat transaksi Midtrans'); }
            else {
              const goTo = '<?= site_url('pesananbelumbayar') ?>'
                          + '?order=' + encodeURIComponent(data.order_id)
                          + '&autopay=1';
              location.href = goTo;
            }
          })().finally(()=>{ btn.disabled=false; btn.textContent = (mode==='multi')?'Buat Pesanan (Semua)':'Buat Pesanan'; });
          return;
        }

        // COD
        const endpoint = (mode==='multi') ? '<?= site_url("pemesanan/simpan-batch") ?>' : '<?= site_url("pemesanan/simpan") ?>';
        let opts;
        if (mode==='multi') {
          const items = currentData.items.map(it=>({id_produk:it.id_produk, qty:it.qty}));
          opts = {
            method:'POST',
            headers: withCsrf({ 'Content-Type':'application/json' }),
            credentials: 'same-origin',
            body: JSON.stringify({ id_alamat:idAlamat, metode:'cod', items })
          };
        } else {
          const fd = new FormData();
          fd.append('id_produk', currentData.idProduk);
          fd.append('qty', currentData.qty);
          fd.append('harga', currentData.harga);
          fd.append('id_alamat', idAlamat);
          fd.append('metode', 'cod');
          // kirim CSRF di header (FormData tidak perlu append token, CI4 default baca dari header)
          opts = { method:'POST', headers: withCsrf(), credentials:'same-origin', body: fd };
        }
        
        fetch(endpoint, opts).then(r=>r.json()).then(res=>{
          if (res?.success) {
            const sm = new bootstrap.Modal('#successModal');
            document.getElementById('successText').innerHTML =
              `Status Pesanan: <b>${res.status ?? 'Dikemas'}</b><br>Terima kasih telah berbelanja di <b>FarmUnand</b>!`;
            sm.show();
          } else alert(res?.message || 'Gagal membuat pesanan.');
        }).finally(()=>{ btn.disabled=false; btn.textContent = (mode==='multi')?'Buat Pesanan (Semua)':'Buat Pesanan'; });
      }
    </script>
  </body>
</html>
