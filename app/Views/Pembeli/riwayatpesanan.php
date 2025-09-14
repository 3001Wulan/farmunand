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
        .sidebar {
            min-height: 100vh;
        }
        .order-img {
            width: 100px; 
            height: 100px; 
            background:#e9ecef; 
            border-radius: 8px;
            display:flex;
            align-items:center;
            justify-content:center;
            overflow:hidden;
        }
        .order-img img {
            width:100%;
            height:100%;
            object-fit:cover;
        }
    </style>
</head>
<body>

<div class="container-fluid">
    <div class="row g-0">

        <!-- Sidebar (include) -->
        <?= $this->include('layout/sidebar'); ?>

        <!-- Main Content -->
        <div class="col-md-9 col-lg-10 p-4">
            <!-- Tabs -->
            <div class="mb-4 d-flex flex-wrap gap-2">
                <button class="btn btn-sm btn-outline-success">Semua</button>
                <button class="btn btn-sm btn-outline-success">Belum Bayar</button>
                <button class="btn btn-sm btn-outline-success">Dikemas</button>
                <button class="btn btn-sm btn-outline-success">Dikirim</button>
                <button class="btn btn-sm btn-outline-success">Selesai</button>
                <button class="btn btn-sm btn-outline-success">Berikan Penilaian</button>
            </div>

            <!-- Loop Pesanan -->
            <?php if (!empty($orders)): ?>
                <?php foreach ($orders as $order): ?>
                    <div class="card mb-3 shadow-sm">
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
                                <p class="mb-1 text-success fw-bold"><?= esc($order['status_pemesanan']); ?></p>
                                <p class="mb-0">Total Pesanan 
                                    <span class="fw-bold">
                                        Rp.<?= number_format($order['harga'] * $order['jumlah_produk'], 0, ',', '.'); ?>
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
