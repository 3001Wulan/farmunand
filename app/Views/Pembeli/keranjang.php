<!DOCTYPE html>
<html lang="id">
  <head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
  <title>Keranjang</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
      body{background:#f1f3f6;}
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
                        <?= csrf_field() ?>
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
                    <td>
                      <a href="<?= base_url('keranjang/remove/'.$row['id_produk']) ?>" class="btn btn-outline-danger btn-sm"
                        onclick="return confirm('Hapus item ini dari keranjang?')">Hapus</a>

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
