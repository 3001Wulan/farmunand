<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
    <title>Pesanan Saya - FarmUnand</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">

    <style>
        html, body {
            height: 100%;
            margin: 0;
            padding: 0;
            background-color: #f8f9fa;
        }

        /* Konten digeser agar tidak ketutup sidebar */
        .main-content {
            margin-left: 250px; /* lebar sidebar */
            padding: 30px;
        }

        /* Gambar produk */
        .order-img img {
            width: 80px;
            height: 80px;
            object-fit: cover;
            border-radius: 8px;
            border: 2px solid #dee2e6;
        }

        /* Card pesanan */
        .card {
            border-radius: 12px;
            border: none;
            box-shadow: 0 4px 10px rgba(0,0,0,0.08);
            transition: transform 0.2s;
        }
        .card:hover {
            transform: scale(1.01);
        }

        /* Status */
        .status {
            font-weight: 600;
            font-size: 14px;
        }

        /* Tabs filter */
        .btn-filter {
            border-radius: 20px;
            font-weight: 500;
        }
    </style>
</head>
<body>

<div class="container-fluid px-0">
    <div class="row g-0">

        <!-- Sidebar -->
        <?= $this->include('layout/sidebar') ?>

        <!-- Main Content -->
        <div class="col main-content">
            <!-- Tabs -->
            <div class="mb-4 d-flex flex-wrap gap-2">
                <a href="/riwayatpesanan" class="btn btn-sm btn-outline-success btn-filter">Semua</a>
                <a href="/pesananbelumbayar" class="btn btn-sm btn-outline-success btn-filter">Belum Bayar</a>
                <a href="/pesanandikemas" class="btn btn-sm btn-outline-success btn-filter">Dikemas</a>
                <a href="/konfirmasipesanan" class="btn btn-sm btn-outline-success btn-filter">Dikirim</a>
                <a href="/pesananselesai" class="btn btn-sm btn-success btn-filter active">Selesai</a>
                <a href="<?= base_url('penilaian/daftar') ?>" class="btn btn-sm btn-outline-success btn-filter">
                    Berikan Penilaian
                </a>
            </div>

            <!-- Loop Pesanan -->
            <?php if (!empty($orders)): ?>
                <?php foreach ($orders as $order): ?>
                    <div class="card mb-3">
                        <div class="card-body d-flex flex-wrap justify-content-between align-items-center">
                            <div class="d-flex align-items-center">
                                <div class="order-img">
                                    <?php if (!empty($order['foto'])): ?>
                                        <img src="<?= base_url('uploads/produk/' . $order['foto']); ?>" 
                                             alt="<?= esc($order['nama_produk']); ?>">
                                    <?php else: ?>
                                        <span class="text-muted small">No Image</span>
                                    <?php endif; ?>
                                </div>
                                <div class="ms-3">
                                    <h6 class="fw-bold mb-1"><?= esc($order['nama_produk']); ?></h6>
                                    <p class="text-muted mb-1">Farm Unand</p>
                                    <p class="mb-0">Jumlah: <?= esc($order['jumlah_produk']); ?></p>
                                </div>
                            </div>
                            <div class="text-end mt-3 mt-md-0">
                                <p class="mb-1 text-success status"><?= esc($order['status_pemesanan']); ?></p>
                                <p class="mb-0">Total Pesanan 
                                    <span class="fw-bold">
                                        Rp <?= number_format($order['harga'] * $order['jumlah_produk'], 0, ',', '.'); ?>
                                    </span>
                                </p>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="alert alert-info">Belum ada pesanan.</div>
            <?php endif; ?>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
