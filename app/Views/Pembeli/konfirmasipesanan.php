<!DOCTYPE html>
<html lang="id">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Pesanan Saya - FarmUnand</title>
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
        <style>
            body {
                background-color: #f8f9fa;
            }
            
            .order-img {
                width: 80px;
                height: 80px;
                border-radius: 8px;
                object-fit: cover;
                background: #e9ecef;
            }
            /* Main content */
            .content {
                margin-left: 250px;
                padding: 20px;
            }
            /* Responsif */
            @media (max-width: 768px) {
                .sidebar {
                    width: 70px;
                    padding: 15px 10px;
                }
                .sidebar h5,
                .sidebar .nav-link span {
                    display: none;
                }
                .content {
                    margin-left: 70px;
                }
            }
        </style>
    </head>

    <body>
        <!-- Sidebar -->
        <?= $this->include('layout/sidebar') ?>

        <!-- Main Content -->
        <div class="content">
            <!-- Tabs -->
            <div class="mb-4 d-flex flex-wrap gap-2">
                <a href="/riwayatpesanan" class="btn btn-sm btn-outline-success">Semua</a>
                <a href="/pesanan?status=belum_bayar" class="btn btn-sm btn-outline-success">Belum Bayar</a>
                <a href="/pesanan?status=dikemas" class="btn btn-sm btn-outline-success">Dikemas</a>
                <a href="/pesanan?status=dikirim" class="btn btn-sm btn-success active">Dikirim</a>
                <a href="/pesanan?status=selesai" class="btn btn-sm btn-outline-success">Selesai</a>
                <a href="/pesanan?status=penilaian" class="btn btn-sm btn-outline-success">Berikan Penilaian</a>
            </div>

            <!-- Loop Pesanan -->
            <?php if (!empty($pesanan)) : ?>
                <?php foreach ($pesanan as $p): ?>
                <div class="card mb-3 shadow-sm">
                    <div class="card-body d-flex flex-wrap justify-content-between align-items-center">
                        <div class="d-flex align-items-center">
                            <img src="<?= base_url('uploads/produk/'.$p['foto']); ?>" class="order-img" alt="produk">
                            <div class="ms-3">
                                <h6 class="fw-bold mb-1"><?= esc($p['nama_produk']); ?></h6>
                                <p class="mb-0">Jumlah: <?= esc($p['jumlah_produk']); ?></p>
                                <p class="mb-0">Harga: Rp.<?= number_format($p['harga'], 0, ',', '.'); ?></p>
                            </div>
                        </div>
                        <div class="text-end mt-3 mt-md-0">
                            <p class="mb-1 text-success fw-bold"><?= esc($p['status_pemesanan']); ?></p>
                            <p class="mb-2">Total Pesanan 
                                <span class="fw-bold">Rp.<?= number_format($p['harga'] * $p['jumlah_produk'], 0, ',', '.'); ?></span>
                            </p>
                            <?php if ($p['status_pemesanan'] !== 'Selesai'): ?>
                                <a href="<?= site_url('konfirmasipesanan/selesai/'.$p['id_pemesanan']); ?>" 
                                class="btn btn-sm btn-success">
                                Pesanan Selesai
                                </a>
                            <?php else: ?>
                                <span class="badge bg-success">Selesai</span>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="alert alert-info">Belum ada pesanan.</div>
            <?php endif; ?>
        </div>
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    </body>
</html>
