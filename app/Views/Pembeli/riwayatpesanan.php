<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
    <title>Pesanan Saya - FarmUnand</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">

    <style>
        /* Hapus margin/padding default yang bisa bikin celah */
        html, body {
            height: 100%;
            margin: 0;
            padding: 0;
        }

        body {
            background-color: #f8f9fa;
        }

        /* Gambar produk */
        .order-img img {
            width: 80px;
            height: 80px;
            object-fit: cover;
            border-radius: 8px;
        }

        /* Jika ingin sidebar menempel dan tidak ada garis/box shadow yang menunjukkan celah */
        .sidebar {
            /* tidak perlu border, biar rapi nempel */
            border: 0;
        }

       /* Responsif: layar kecil sidebar jadi horizontal di atas */
@media (max-width: 768px) {
    .sidebar {
        width: 100%;
        height: auto;
        position: relative;
        flex-direction: row;
        align-items: center;
        justify-content: space-around;
    }
    .sidebar .d-grid {
        display: flex !important;
        flex-direction: row;
        gap: 10px;
    }
    .sidebar .d-grid a {
        flex: 1;
        font-size: 14px;
        padd
        }
    }
    </style>
</head>
<body>

<!-- gunakan px-0 supaya tidak ada padding container -->
<div class="container-fluid px-0">
    <div class="row g-0">

        <!-- Sidebar -->
        <div class="col-md-3 col-lg-2 bg-success text-white d-flex flex-column vh-100 p-0 sidebar">
            <div class="text-center mb-4 p-3">
                <div class="order-img mb-3 mx-auto">
                    <!-- kalau mau ngasih logo, pakai <img> di sini -->
                </div>
                <h5 class="fw-bold">Farm Unand</h5>
            </div>
            <div class="d-grid gap-2 px-3 mb-3">
                <a href="/dashboarduser" class="btn btn-light">Dashboard</a>
                <a href="/akun" class="btn btn-light">Akun Saya</a>
                <a href="/riwayatpesanan" class="btn btn-dark text-white">Pesanan Saya</a>
            </div>
        </div>

        <!-- Main Content -->
        <div class="col-md-9 col-lg-10 p-4">
            <!-- Tabs -->
            <div class="mb-4 d-flex flex-wrap gap-2">
            <a href="/riwayatpesanan" class="btn btn-sm btn-success active">Semua</a>
            <a href="/pesanan?status=belum_bayar" class="btn btn-sm btn-outline-success">Belum Bayar</a>
            <a href="/pesanan?status=dikemas" class="btn btn-sm btn-outline-success">Dikemas</a>
            <a href="/konfirmasipesanan" class="btn btn-sm btn-outline-success">Dikirim</a>
            <a href="/pesanan?status=selesai" class="btn btn-sm btn-outline-success">Selesai</a>
            <a href="/pesanan?status=penilaian" class="btn btn-sm btn-outline-success">Berikan Penilaian</a>
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
