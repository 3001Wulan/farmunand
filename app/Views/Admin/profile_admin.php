<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title><?= esc($title) ?></title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet" />
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet" />
  <style>
    body {
      background: linear-gradient(135deg, #e6f4ea, #c0e0cc);
      font-family: "Segoe UI", Tahoma, Geneva, Verdana, sans-serif;
      min-height: 100vh;
      margin: 0;
    }
    .content {
      margin-left: 250px;
      padding: 30px 40px;
    }
    .page-header {
      background: linear-gradient(135deg, #198754, #20c997);
      color: white;
      border-radius: 12px;
      padding: 20px 30px;
      margin-bottom: 30px;
      display: flex;
      align-items: center;
      gap: 15px;
      font-weight: 700;
      font-size: 1.5rem;
      box-shadow: 0 6px 18px rgba(25, 135, 84, 0.45);
      user-select: none;
    }
    .profile-card {
      border-radius: 18px;
      box-shadow: 0 6px 20px rgba(0,0,0,0.12);
      border: none;
      overflow: hidden;
      background: white;
      max-width: 720px;
      margin: auto;
      user-select: none;
    }
    .profile-header {
      background: linear-gradient(135deg, #198754, #28a745);
      color: white;
      padding: 30px 40px;
      display: flex;
      align-items: center;
      justify-content: space-between;
      gap: 25px;
    }
    .profile-header h2 {
      margin: 0;
      font-weight: 800;
      font-size: 2rem;
      display: flex;
      align-items: center;
      gap: 12px;
    }
    .profile-header p {
      font-size: 1.1rem;
      margin: 6px 0 0;
      letter-spacing: 0.025em;
      opacity: 0.85;
    }
    .profile-photo {
      width: 140px;
      height: 140px;
      border-radius: 50%;
      object-fit: cover;
      border: 5px solid white;
      box-shadow: 0 6px 16px rgba(0,0,0,0.35);
      flex-shrink: 0;
    }
    .profile-body {
      padding: 36px 40px;
    }
    .info-box {
      border: 1px solid #dee2e6;
      border-radius: 12px;
      padding: 16px 28px;
      margin-bottom: 16px;
      background: #fff;
      display: flex;
      justify-content: space-between;
      align-items: center;
      font-size: 1.1rem;
      font-weight: 600;
      color: #198754;
      letter-spacing: 0.015em;
      box-shadow: 0 3px 8px rgba(25, 135, 84, 0.1);
      transition: background-color 0.3s ease;
    }
    .info-box span {
      font-weight: 500;
      color: #444;
    }
    .info-box:hover {
      background-color: #e6f4ea;
    }
    .btn-edit {
      background: linear-gradient(135deg, #198754, #28a745);
      border: none;
      font-size: 1.1rem;
      padding: 12px 28px;
      border-radius: 12px;
      color: white;
      font-weight: 700;
      transition: background 0.3s ease, transform 0.2s ease;
      display: inline-flex;
      align-items: center;
      gap: 8px;
      cursor: pointer;
      user-select: none;
      text-decoration: none;
    }
    .btn-edit:hover {
      background: linear-gradient(135deg, #28a745, #198754);
      transform: translateY(-3px);
      box-shadow: 0 8px 18px rgba(25, 135, 84, 0.45);
    }
    @media (max-width: 768px) {
      .content {
        margin-left: 0;
        padding: 20px;
      }
      .profile-card {
        margin: 0 12px;
      }
      .profile-header {
        flex-direction: column;
        align-items: center;
        text-align: center;
        gap: 18px;
        padding: 25px 25px;
      }
      .profile-photo {
        width: 120px;
        height: 120px;
      }
      .profile-body {
        padding: 25px 20px;
      }
      .info-box {
        font-size: 1rem;
        flex-direction: column;
        gap: 8px;
        align-items: flex-start;
        padding: 12px 18px;
      }
      .btn-edit {
        width: 100%;
        justify-content: center;
        font-size: 1rem;
        padding: 10px 0;
      }
    }
  </style>
</head>
<body>
  <?= $this->include('layout/sidebarAdmin') ?>
  <div class="content" role="main">
    <div class="card profile-card" tabindex="0" aria-label="Profil Admin">
      <div class="profile-header">
        <div>
          <h2><i class="bi bi-person-badge-fill"></i> Profil Admin</h2>
          <p><?= esc($user['username']) ?> | <?= esc($user['role']) ?></p>
        </div>
        <img src="<?= base_url('uploads/profile/' . ($user['foto'] ?? 'default.png')) ?>" alt="Foto Profil Admin" class="profile-photo" />
      </div>
      <div class="profile-body">
        <div class="info-box" tabindex="0">
          <strong>Username:</strong><span><?= esc($user['username']) ?></span>
        </div>
        <div class="info-box" tabindex="0">
          <strong>Nama:</strong><span><?= esc($user['nama'] ?? '-') ?></span>
        </div>
        <div class="info-box" tabindex="0">
          <strong>Email:</strong><span><?= esc($user['email']) ?></span>
        </div>
        <div class="info-box" tabindex="0">
          <strong>No HP:</strong><span><?= esc($user['no_hp'] ?? '-') ?></span>
        </div>
        <div class="mt-4 text-center">
          <a href="<?= base_url('profileadmin/edit') ?>" class="btn btn-edit" aria-label="Edit Profil Admin">
            <i class="bi bi-pencil-square"></i> Edit Profil
          </a>
        </div>
      </div>
    </div>
  </div>
</body>
</html>
