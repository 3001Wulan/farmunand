<!DOCTYPE html>
<html lang="id">
  <head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Akun User - Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
      /* Konten geser agar tidak tertutup sidebar */
      .content {
        padding: 30px;
        margin-left: 250px;
      }
      
      body {
        background: #f8f9fa;
      }
      .form-container {
        max-width: 700px;
        margin: 50px auto;
        padding: 20px;
        background: white;
        border-radius: 10px;
        box-shadow: 0 0 10px rgba(0,0,0,0.1);
      }
      h3 {
        color: #198754;
      }
    </style>
  </head>

  <body>
    <!-- Sidebar dari layouts -->
    <?= $this->include('layout/sidebarAdmin') ?>

    <div class="container">
      <div class="form-container">
        <h3 class="mb-4">Edit Akun User</h3>
        
        <form action="<?= base_url('manajemenakunuser/update/' . $user['id']); ?>" method="post">
          <?= csrf_field(); ?>
          
          <div class="mb-3">
            <label for="nama" class="form-label">Nama</label>
            <input type="text" name="nama" id="nama" class="form-control" 
                  value="<?= esc($user['nama']); ?>" required>
          </div>
          
          <div class="mb-3">
            <label for="email" class="form-label">Email</label>
            <input type="email" name="email" id="email" class="form-control" 
                  value="<?= esc($user['email']); ?>" required>
          </div>
          
          <div class="mb-3">
            <label for="no_hp" class="form-label">No. HP</label>
            <input type="text" name="no_hp" id="no_hp" class="form-control" 
                  value="<?= esc($user['no_hp']); ?>" required>
          </div>
          
          <div class="mb-3">
            <label for="status" class="form-label">Status</label>
            <select name="status" id="status" class="form-select" required>
              <option value="Aktif" <?= $user['status'] === 'Aktif' ? 'selected' : '' ?>>Aktif</option>
              <option value="Nonaktif" <?= $user['status'] === 'Nonaktif' ? 'selected' : '' ?>>Nonaktif</option>
            </select>
          </div>
          
          <button type="submit" class="btn btn-success">Simpan</button>
          <a href="<?= base_url('admin/manajemenakunuser'); ?>" class="btn btn-secondary">Batal</a>
        </form>
        
      </div>
    </div>
  </body>
</html>
