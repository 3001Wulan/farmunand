<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pilih Alamat - FarmUnand</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #f8f9fa;
        }
        .sidebar {
            min-height: 100vh;
        }
        .address-card {
            border: 1px solid #dee2e6;
            border-radius: 8px;
            padding: 15px;
            margin-bottom: 15px;
            background: #fff;
        }
        .address-card .badge {
            font-size: 12px;
        }
    </style>
</head>
<body>

<div class="container-fluid">
    <div class="row g-0">

        <!-- Sidebar -->
        <?= $this->include('layout/sidebar'); ?>

        <!-- Main Content -->
        <div class="col-md-9 col-lg-10 p-4">
            <h4 class="mb-3">Alamat</h4>
            <button class="btn btn-sm btn-success mb-3">+ Tambah Alamat</button>

            <!-- Alamat 1 -->
            <div class="address-card d-flex justify-content-between align-items-start">
                <div>
                    <input type="radio" name="alamat" checked class="form-check-input me-2">
                    <span class="fw-bold">Wulandari Yulianis</span> 
                    <span class="text-muted">(+62) 822-8567-1644</span>
                    <p class="mb-1">Kos, Jalan Pasar Ambacang</p>
                    <p class="mb-1">Kota Padang, Sumatera Barat, ID 25151</p>
                    <span class="badge bg-success">Utama</span>
                </div>
                <button class="btn btn-outline-secondary btn-sm">Ubah</button>
            </div>

            <!-- Alamat 2 -->
            <div class="address-card d-flex justify-content-between align-items-start">
                <div>
                    <input type="radio" name="alamat" class="form-check-input me-2">
                    <span class="fw-bold">Wulandari Yulianis</span> 
                    <span class="text-muted">(+62) 822-8567-1644</span>
                    <p class="mb-1">Jalan Sitinanggopoh</p>
                    <p class="mb-1">Lubuk Basung, Kab. Agam, Sumatera Barat, ID 26451</p>
                </div>
                <button class="btn btn-outline-secondary btn-sm">Ubah</button>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>