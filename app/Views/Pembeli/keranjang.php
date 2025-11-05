<!DOCTYPE html>
<html lang="id">
  <head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
  <title>Keranjang</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
      body {
        background: linear-gradient(135deg, #e6f4ea, #c0e0cc);
        font-family: "Segoe UI", Tahoma, Geneva, Verdana, sans-serif;
        min-height: 100vh;
        margin: 0;
      }
      .content{margin-left:250px; padding:30px;}
      .card-h{background:linear-gradient(180deg, #145c32, #198754, #28a745); color:#fff; border-radius:12px 12px 0 0; padding:14px 18px; font-weight:600;}
      .tbl{background:#fff; border-radius:0 0 12px 12px; box-shadow:0 6px 18px rgba(0,0,0,.06); overflow:hidden;}
      .tbl thead th{background:#198754; color:#fff; text-align:center; vertical-align:middle;}
      .tbl td{vertical-align:middle;}
      .prod-img{width:70px; height:70px; object-fit:cover; border-radius:8px; border:1px solid #e9ecef;}
      .btn-outline-danger:hover{color:#fff;}
      .badge-soft{background:#eafaf0; color:#198754; border:1px solid #cfe9db;}
    </style>
  </head>

  <body>
    <!-- Sidebar -->
    <?= $this->include('layout/sidebar') ?>

    <!-- Content -->
    <div class="content">
      <?php
        // jumlah produk unik (item) di keranjang
        $itemCount = is_array($cart ?? null) ? count($cart) : 0;
        // total qty (semua item dijumlah) — sama seperti yang dipakai sidebar
        $qtyCount  = session()->get('cart_count_u_' . (($user['id_user'] ?? 0))) ?? 0;
      ?>
      <div class="card-h d-flex justify-content-between align-items-center">
        <div>🧺 Keranjang Saya</div>
        <div class="small">
          Item:
          <span class="badge badge-soft"><?= (int)$itemCount ?></span>
          <span class="opacity-75 ms-2">(Qty: <?= (int)$qtyCount ?>)</span>
        </div>
      </div>

      <div class="tbl p-3">
        <?php if (session()->getFlashdata('success')): ?>
          <div class="alert alert-success"><?= session()->getFlashdata('success') ?></div>
        <?php endif; ?>
        <?php if (session()->getFlashdata('error')): ?>
          <div class="alert alert-danger"><?= session()->getFlashdata('error') ?></div>
        <?php endif; ?>

        <div class="table-responsive">
          <table class="table table-bordered table-hover align-middle text-center">
            <thead>
              <tr>
                <th>Produk</th>
                <th>Harga</th>
                <th>Kuantitas</th>
                <th>Subtotal</th>
                <th>Aksi</th>
              </tr>
            </thead>

            <tbody>
              <?php if (!empty($cart)): ?>
                <?php foreach ($cart as $row):
                    $subtotal = (float)$row['harga'] * (int)$row['qty'];
                ?>
                  <tr>
                    <td class="text-start">
                      <div class="d-flex align-items-center">
                        <img class="prod-img me-3" src="<?= base_url('uploads/produk/'.$row['foto']) ?>" alt="<?= esc($row['nama_produk']) ?>">
                        <div>
                          <div class="fw-semibold"><?= esc($row['nama_produk']) ?></div>
                        </div>
                      </div>
                    </td>
                    <td>Rp <?= number_format($row['harga'],0,',','.') ?></td>
                    <td style="width:220px;">

                    <div class="cart-qty-row" data-id="<?= $row['id_produk'] ?>">
                      <!-- MODE LIHAT -->
                      <div class="view-state d-flex align-items-center justify-content-center gap-2">
                        <span class="badge bg-secondary" id="qtyLabel<?= $row['id_produk'] ?>">
                          <?= (int)$row['qty'] ?>
                        </span>
                        <button type="button" class="btn btn-outline-success btn-sm"
                                onclick="enterEdit(<?= $row['id_produk'] ?>)">
                          Ubah
                        </button>
                      </div>

                      <!-- MODE UBAH -->
                      <form action="<?= base_url('keranjang/update') ?>" method="post" class="edit-state d-none text-center">
                        <input type="hidden" name="id_produk" value="<?= $row['id_produk'] ?>">
                        <div class="input-group input-group-sm mx-auto" style="max-width:170px;">
                          <button type="button" class="btn btn-outline-secondary" onclick="stepQty(this,-1)">−</button>
                          <input type="number"
                                class="form-control text-center"
                                name="qty"
                                min="0"
                                value="<?= (int)$row['qty'] ?>">
                          <button type="button" class="btn btn-outline-secondary" onclick="stepQty(this,1)">+</button>
                        </div>

                        <div class="mt-2 d-flex justify-content-center gap-2">
                          <button class="btn btn-success btn-sm">Simpan</button>
                          <button type="button" class="btn btn-light btn-sm"
                                  onclick="cancelEdit(<?= $row['id_produk'] ?>)">
                            Batal
                          </button>
                        </div>
                      </form>
                    </div>

                    <td class="fw-semibold">Rp <?= number_format($subtotal,0,',','.') ?></td>
                    <td class="text-center">
                      <!-- Tombol Hapus -> buka modal -->
                      <button type="button"
                              class="btn btn-sm btn-danger d-inline-flex align-items-center gap-1 btn-delete-item"
                              aria-label="Hapus item: <?= esc($row['nama_produk']) ?>"
                              title="Hapus item"
                              data-produkid="<?= esc($row['id_produk']) ?>"
                              data-produkname="<?= esc($row['nama_produk']) ?>"
                              data-deleteurl="<?= base_url('keranjang/remove/'. $row['id_produk']) ?>">
                        <i class="bi bi-trash" aria-hidden="true"></i> Hapus
                      </button>

                      <!-- Checkout 1 produk (POST ke MelakukanPemesanan) -->
                      <form action="<?= base_url('melakukanpemesanan') ?>" method="post" class="d-inline">
                        <?= csrf_field() ?>
                        <input type="hidden" name="id_produk" value="<?= $row['id_produk'] ?>">
                        <input type="hidden" name="qty" value="<?= (int)$row['qty'] ?>">
                        <button class="btn btn-info btn-sm text-white">Checkout</button>
                      </form>
                    </td>
                  </tr>
                <?php endforeach; ?>
              <?php else: ?>
                <tr><td colspan="5" class="text-muted">Keranjang masih kosong.</td></tr>
              <?php endif; ?>

            </tbody>
              <?php if (!empty($cart)): ?>
                <tfoot>
                  <tr>
                    <th colspan="3" class="text-end">Total</th>
                    <th colspan="2" class="text-start">Rp <?= number_format($total,0,',','.') ?></th>
                  </tr>
                </tfoot>
              <?php endif; ?>
          </table>
        </div>

        <div class="d-flex justify-content-between">
          <div>
            <a href="<?= base_url('/dashboarduser') ?>" class="btn btn-outline-secondary">Lanjut Belanja</a>
          </div>
          <div class="d-flex gap-2">
            <?php if (!empty($cart)): ?>
              <form action="<?= base_url('keranjang/checkout-all') ?>" method="post" class="d-inline">
                <?= csrf_field() ?>
                <button class="btn btn-secondary">Checkout Semua</button>
              </form>

              <form action="<?= base_url('keranjang/clear') ?>" method="post" class="d-inline" onsubmit="return confirm('Kosongkan keranjang?')">
                <?= csrf_field() ?>
                <button class="btn btn-outline-danger">Kosongkan</button>
              </form>
            <?php endif; ?>
          </div>
        </div>
      </div>
    </div>

    <!-- Modal Konfirmasi Hapus Item Keranjang -->
    <div class="modal fade" id="deleteCartItemModal" tabindex="-1" aria-labelledby="deleteCartItemModalLabel" aria-hidden="true">
      <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg">
          <form id="form-delete-cart-item" method="post" action="">
            <?= csrf_field() ?>
            <input type="hidden" name="id_produk" id="modalProdukId" value="">
            <div class="modal-header bg-danger text-white">
              <h5 class="modal-title d-flex align-items-center gap-2" id="deleteCartItemModalLabel">
                <i class="bi bi-exclamation-triangle" aria-hidden="true"></i>
                Konfirmasi Hapus
              </h5>
              <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Tutup"></button>
            </div>
            <div class="modal-body">
              <p class="mb-1">Anda yakin ingin menghapus item berikut dari keranjang?</p>
              <p class="mb-0">
                Produk: <strong id="modalProdukName"></strong>
              </p>
              <div class="alert alert-warning mt-3 mb-0" role="alert">
                Tindakan ini hanya menghapus dari keranjang (tidak membatalkan pesanan).
              </div>
            </div>
            <div class="modal-footer">
              <button type="button" class="btn btn-light border" data-bs-dismiss="modal">Batal</button>
              <button type="submit" class="btn btn-danger">
                <i class="bi bi-trash" aria-hidden="true"></i> Ya, Hapus
              </button>
            </div>
          </form>
        </div>
      </div>
    </div>

<!-- Pastikan Bootstrap JS bundle & Bootstrap Icons ada -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

<script>
  (function () {
    const modalEl = document.getElementById('deleteCartItemModal');
    const formEl  = document.getElementById('form-delete-cart-item');
    const nameEl  = document.getElementById('modalProdukName');
    const idEl    = document.getElementById('modalProdukId');

    // Delegasi klik tombol "Hapus"
    document.addEventListener('click', function(e) {
      const btn = e.target.closest('.btn-delete-item');
      if (!btn) return;

      const pid   = btn.getAttribute('data-produkid');
      const pname = btn.getAttribute('data-produkname');
      const durl  = btn.getAttribute('data-deleteurl');

      // Isi konten modal
      nameEl.textContent = pname || 'Tanpa Nama';
      idEl.value = pid || '';

      // Set action form -> arahkan ke endpoint hapus
      // NOTE:
      // - Jika route hapus Anda menerima POST, biarkan method="post".
      // - Jika route hapus Anda hanya GET, ganti form submit menjadi redirect di bawah.
      formEl.setAttribute('action', durl);

      // Tampilkan modal
      const m = new bootstrap.Modal(modalEl);
      m.show();

      // Simpan URL untuk fallback GET (kalau diperlukan)
      formEl.dataset.geturl = durl;
    });

    // Jika route hapus masih GET saja, uncomment blok berikut:
    // formEl.addEventListener('submit', function(ev) {
    //   ev.preventDefault();
    //   // Redirect GET
    //   const url = formEl.dataset.geturl;
    //   if (url) window.location.href = url;
    // });
  })();
</script>

    <script>
      function enterEdit(id){
        const row = document.querySelector(`.cart-qty-row[data-id="${id}"]`);
        row.querySelector('.view-state').classList.add('d-none');
        row.querySelector('.edit-state').classList.remove('d-none');
      }

      function cancelEdit(id){
        const row   = document.querySelector(`.cart-qty-row[data-id="${id}"]`);
        const label = row.querySelector(`#qtyLabel${id}`);
        const input = row.querySelector('input[name="qty"]');
        input.value = (label.textContent || '0').trim();
        row.querySelector('.edit-state').classList.add('d-none');
        row.querySelector('.view-state').classList.remove('d-none');
      }

      function stepQty(btn, delta){
        const form  = btn.closest('form');
        const input = form.querySelector('input[name="qty"]');
        let v = parseInt(input.value || '0', 10) + delta;
        if (isNaN(v) || v < 0) v = 0;
        input.value = v;
      }
    </script>
  </body>
</html>
