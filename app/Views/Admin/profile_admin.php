<!DOCTYPE html>
<html lang="id">
  <head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= esc($title) ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
    <style>
      .content { margin-left: 240px; padding: 30px; 
      }
      .profile-card { border-radius: 15px; box-shadow: 0 4px 15px rgba(0,0,0,0.1); border: none; overflow: hidden;
       }
      .profile-header { background: linear-gradient(135deg, #198754, #28a745); color: white; padding: 25px 30px; display: flex; align-items: center; justify-content: space-between; 
      }
      .profile-header h2 { margin: 0; font-weight: bold; font-size: 28px; 
      }
      .profile-header p { font-size: 16px; margin: 5px 0 0; 
      }
      .profile-photo { width: 120px; height: 120px; border-radius: 50%; object-fit: cover; border: 4px solid #fff; box-shadow: 0 4px 12px rgba(0,0,0,0.3); 
      }
      .profile-body { padding: 25px 30px;
      }
      .info-box { border: 1px solid #dee2e6; border-radius: 10px; padding: 12px 20px; margin-bottom: 10px; background: #fff; display: flex; justify-content: space-between; align-items: center; 
      }
      .info-box strong { color: #198754; font-weight: 600; font-size: 16px; 
      }
      .info-box span { font-size: 16px; 
      }
      .btn-edit { background: #198754; border: none; font-size: 16px; padding: 10px 22px; 
        border-radius: 8px; transition: background 0.3s, transform 0.2s; 
      }
      .btn-edit:hover { background: #145c32; 
        transform: translateY(-2px); 
      }
      .summary-title { font-weight: 600; color: #198754; margin-top: 20px; margin-bottom: 10px; 
        font-size: 18px; 
      }
    </style>
  </head>

  <body>
    <?= $this->include('layout/sidebarAdmin') ?>
    <div class="content">
      <div class="card profile-card">
        
        <div class="profile-header">
          <div>
            <h2><i class="bi bi-person-badge-fill me-2"></i>Profil Admin</h2>
            <p><?= esc($user['username']) ?> | <?= esc($user['role']) ?></p>
          </div>
          <img src="<?= base_url('uploads/profile/' . ($user['foto'] ?? 'default.png')) ?>" alt="Foto Profil" class="profile-photo">
        </div>

        <div class="profile-body">
          <!-- Data admin -->
          <div class="info-box"><strong>Username:</strong><span><?= esc($user['username']) ?></span></div>
          <div class="info-box"><strong>Nama:</strong><span><?= esc($user['nama'] ?? '-') ?></span></div>
          <div class="info-box"><strong>Email:</strong><span><?= esc($user['email']) ?></span></div>
          <div class="info-box"><strong>No HP:</strong><span><?= esc($user['no_hp'] ?? '-') ?></span></div>

          <!-- Tombol Edit -->
          <div class="mt-4 text-center">
            <a href="<?= base_url('profileadmin/edit') ?>" class="btn btn-edit text-white"><i class="bi bi-pencil-square me-1"></i> Edit Profil</a>
          </div>
        </div>

      </div>
    </div>
  </body>
</html>
