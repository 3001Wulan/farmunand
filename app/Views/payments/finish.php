<!doctype html>
<html lang="id">
<head>
  <meta charset="utf-8">
  <title>Pembayaran Berhasil - FarmUnand</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <style>
    :root{ --brand:#198754; --brand-dark:#145c32; --muted:#f8f9fa; }
    html,body{height:100%; background:var(--muted);}
    .content{ margin-left:250px; padding:28px; }
    @media (max-width: 992px){ .content{ margin-left:0; padding:18px; } }
    .hero{ background:linear-gradient(135deg,#198754,#28a745); color:#fff; border-radius:14px; padding:22px 20px;
           display:flex; align-items:center; justify-content:space-between; box-shadow:0 8px 20px rgba(0,0,0,.08); }
    .card-wrap{ background:#fff; border-radius:14px; box-shadow:0 10px 22px rgba(0,0,0,.06); padding:28px; }
    .checkmark{ width:86px; height:86px; border-radius:50%; background:#e9fff1; border:4px solid #c3f7d5;
                display:flex; align-items:center; justify-content:center; margin:0 auto 14px; }
    .checkmark i{ font-size:42px; color:#19a45f; }
    .btn-primary{ background:var(--brand); border:none; }
    .btn-primary:hover{ background:var(--brand-dark); }
  </style>
</head>
<body>

<?= $this->include('layout/sidebar') ?>

<div class="content">
  <div class="hero mb-3">
    <h5 class="m-0 fw-bold">Pembayaran Berhasil</h5>
    <span class="small">Terima kasih! Pesananmu sedang kami proses.</span>
  </div>

  <div class="card-wrap text-center">
    <div class="checkmark"><i class="bi bi-check2"></i></div>
    <h4 class="fw-bold text-success mb-2">Payment successful</h4>
    <p class="text-muted mb-4">Pembayaran kamu sudah kami terima. Status pesanan akan pindah ke <b>Dikemas</b> segera setelah notifikasi kami terima dari Midtrans.</p>

    <div class="d-flex gap-2 justify-content-center">
      <a href="<?= base_url('/pesanandikemas') ?>" class="btn btn-primary px-4">
        Lihat Pesanan
      </a>
      <a href="<?= base_url('/dashboarduser') ?>" class="btn btn-outline-success px-4">
        Kembali ke Dashboard
      </a>
    </div>
  </div>
</div>

<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
