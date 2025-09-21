<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?= esc($title) ?></title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
  <style>
    html, body {
      margin: 0;
      padding: 0;
      height: 100%;
      background: #f8f9fa;
    }

    .content { margin-left: 240px; padding: 30px; }

    .profile-card {
      border-radius: 15px;
      box-shadow: 0 4px 15px rgba(0,0,0,0.1);
      border: none;
      overflow: hidden;
    }

    .profile-header {
      background: linear-gradient(135deg, #198754, #28a745);
      color: white;
      padding: 25px 30px;
      display: flex;
      align-items: center;
      justify-content: space-between;
    }

    .profile-header h2 {
      margin: 0;
      font-weight: bold;
      font-size: 28px;
    }

    .profile-header p {
      font-size: 16px;
      margin: 5px 0 0;
    }

    /* Foto Profil */
    .profile-photo {
      width: 120px;
      height: 120px;
      border-radius: 50%;
      object-fit: cover;
      border: 4px solid #fff;
      box-shadow: 0 4px 12px rgba(0,0,0,0.3);
      background: #fff;
    }

    .profile-body {
      padding: 25px 30px;
    }

    /* Info box tiap baris */
    .info-box {
      border: 1px solid #dee2e6;
      border-radius: 10px;
      padding: 12px 20px;
      margin-bottom: 10px;
      background: #fff;
    }

    .info-label {
      font-weight: 600;
      color: #198754;
      font-size: 16px;
    }

    .info-value {
      font-size: 16px;
    }

    .btn-edit {
      background: #198754;
      border: none;
      transition: 0.3s;
      font-size: 16px;
      padding: 10px 22px;
    }
    .btn-edit:hover {
      background: #145c32;
    }
  </style>
</head>
<body>

  <!-- Sidebar -->
  <?= $this->include('layout/sidebar') ?>

  <!-- Content -->
  <div class="content">
    <div class="card profile-card">
      <!-- Header -->
      <div class="profile-header">
        <div>
          <h2><i class="bi bi-person-circle me-2"></i>Profil Saya</h2>
          <p><?= esc($user['username']) ?> | <?= esc($user['role']) ?></p>
        </div>
        <img src="<?= base_url('uploads/profile/' . ($user['foto'] ?? 'default.png')) ?>" 
             alt="Foto Profil" 
             class="profile-photo">
      </div>

      <!-- Body -->
      <div class="profile-body">
        <div class="info-box row">
          <div class="col-4 info-label">Username:</div>
          <div class="col-8 info-value"><?= esc($user['username']) ?></div>
        </div>
        <div class="info-box row">
          <div class="col-4 info-label">Nama:</div>
          <div class="col-8 info-value"><?= esc($user['nama'] ?? '-') ?></div>
        </div>
        <div class="info-box row">
          <div class="col-4 info-label">Email:</div>
          <div class="col-8 info-value"><?= esc($user['email']) ?></div>
        </div>
        <div class="info-box row">
          <div class="col-4 info-label">No HP:</div>
          <div class="col-8 info-value"><?= esc($user['no_hp'] ?? '-') ?></div>
        </div>
        <div class="info-box row">
          <div class="col-4 info-label">Status:</div>
          <div class="col-8 info-value">
            <span class="badge <?= ($user['status'] == 'Aktif') ? 'bg-success' : 'bg-secondary' ?> p-2">
              <?= esc($user['status'] ?? '-') ?>
            </span>
          </div>
        </div>
        <div class="info-box row">
          <div class="col-4 info-label">Role:</div>
          <div class="col-8 info-value">
            <span class="badge bg-dark p-2"><?= esc($user['role']) ?></span>
          </div>
        </div>

        <!-- Tombol Edit -->
        <div class="mt-4 text-center">
          <a href="<?= base_url('profile/edit') ?>" class="btn btn-edit text-white">
            <i class="bi bi-pencil-square me-1"></i> Edit Profil
          </a>
        </div>
      </div>
    </div>
  </div>

</body>
</html>
