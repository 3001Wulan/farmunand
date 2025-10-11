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
        background: #f8f9fa;}
      .content { margin-left: 240px; padding: 30px; }
      .profile-card {
        border-radius: 15px;
        box-shadow: 0 4px 15px rgba(0,0,0,0.1);
        overflow: hidden;
        border: none;}
      .profile-header {
        background: linear-gradient(135deg, #198754, #28a745);
        color: white;
        padding: 25px;
        display: flex;
        justify-content: space-between;
        align-items: center;}
      .profile-header h4 {
        margin: 0;
        font-weight: bold;}
      .profile-body { padding: 25px; }
      .form-label { font-weight: 500; color: #198754; }
      .btn-save {
        background: #198754;
        border: none;}
      .btn-save:hover {
        background: #145c32;}
      .btn-cancel {
        background: #6c757d;
        border: none;}
      .btn-cancel:hover {
        background: #5a6268;}
      .preview-img {
        width: 120px;
        height: 120px;
        object-fit: cover;
        border-radius: 50%;
        box-shadow: 0 4px 10px rgba(0,0,0,0.2);}
    </style>
  </head>
  
  <body>
    <!-- Sidebar -->
    <?= $this->include('layout/sidebar') ?>

    <!-- Content -->
    <div class="content">
      <div class="card profile-card">
        <div class="profile-header">
          <h4><i class="bi bi-pencil-square me-2"></i>Edit Profil</h4>
        </div>

        <div class="profile-body">
          <?php if (session()->getFlashdata('errors')): ?>
            <div class="alert alert-danger">
              <ul class="mb-0">
                <?php foreach (session()->getFlashdata('errors') as $error): ?>
                  <li><?= esc($error) ?></li>
                <?php endforeach ?>
              </ul>
            </div>
          <?php endif; ?>

          <form action="<?= base_url('profile/update') ?>" method="post" enctype="multipart/form-data">
            <?= csrf_field() ?>

            <div class="mb-3 text-center">
              <img src="<?= base_url('uploads/profile/' . ($user['foto'] ?? 'default.png')) ?>" 
                  alt="Foto Profil" class="preview-img mb-2" id="previewFoto">
              <div>
                <input type="file" name="foto" class="form-control mt-2" accept="image/*" onchange="previewImage(event)">
              </div>
            </div>

            <div class="mb-3">
              <label class="form-label">Username</label>
              <input type="text" name="username" value="<?= old('username', $user['username']) ?>" class="form-control" required>
            </div>

            <div class="mb-3">
              <label class="form-label">Nama</label>
              <input type="text" name="nama" value="<?= old('nama', $user['nama']) ?>" class="form-control" required>
            </div>

            <div class="mb-3">
              <label class="form-label">Email</label>
              <input type="email" name="email" value="<?= old('email', $user['email']) ?>" class="form-control" required>
            </div>

            <div class="mb-3">
              <label class="form-label">No HP</label>
              <input type="text" name="no_hp" value="<?= old('no_hp', $user['no_hp']) ?>" class="form-control">
            </div>

            <div class="d-flex justify-content-end gap-2">
              <a href="<?= base_url('profile') ?>" class="btn btn-cancel text-white">
                <i class="bi bi-x-circle me-1"></i> Batal
              </a>
              <button type="submit" class="btn btn-save text-white">
                <i class="bi bi-check-circle me-1"></i> Simpan
              </button>
            </div>
          
          </form>
        </div>
      </div>
    </div>

    <script>
      function previewImage(event) {
        const reader = new FileReader();
        reader.onload = function(){
          document.getElementById('previewFoto').src = reader.result;
        }
        reader.readAsDataURL(event.target.files[0]);
      }
    </script>
  </body>
</html>
