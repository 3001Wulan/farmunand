<div class="sidebarAdmin">
  <!-- Judul -->
  <div class="text-center mb-4 px-3">
    <h4 class="fw-bold" style="font-size: 24px;">Farm Unand</h4>
  </div>

  <!-- Foto Profil -->
  <div class="text-center mb-4">
    <img src="<?= base_url('uploads/profile/' . ($user['foto'] ?? 'delfaut.jpeg')) ?>" 
         alt="Foto Profil" 
         class="profile-photo">
    <p class="mt-2 mb-0 fw-semibold"><?= esc($user['username']) ?> | <?= esc($user['role']) ?></p>
  </div>

  <!-- Menu -->
  <div class="d-grid gap-2 px-3">
    <a href="/profileadmin" class="sidebar-link <?= (url_is('profileadmin')) ? 'active' : '' ?>">Profil</a>
    <a href="/dashboard" class="sidebar-link">Dashboard</a>
    <a href="<?= base_url('admin/produk') ?>" 
      class="sidebar-link <?= (url_is('admin/produk*')) ? 'active' : '' ?>">
      Produk
    </a>

    <a href="/MengelolaRiwayatPesanan" class="sidebar-link <?= (url_is('pesanan*')) ? 'active' : '' ?>">Pesanan</a>
    <a href="/manajemenakunuser" class="sidebar-link <?= (url_is('manajemenakunuser')) ? 'active' : '' ?>">Manajemen Akun User</a>
    <a href="/melihatlaporan" class="sidebar-link <?= (url_is('laporanpenjualan')) ? 'active' : '' ?>">Laporan Penjualan</a>
    <a href="/login" class="sidebar-link">Log Out</a>
  </div>
</div>

<style>
/* Sidebar Admin container */
.sidebarAdmin {
  position: fixed;
  top: 0;
  left: 0;
  width: 250px;
  height: 100vh;
  background: #198754; /* biru khas admin */
  color: white;
  overflow-y: auto;
  z-index: 1000;
  padding-top: 20px;
  box-shadow: 4px 0 12px rgba(0,0,0,0.15);
}

/* Foto Profil */
.sidebarAdmin .profile-photo {
  width: 120px;
  height: 120px;
  border-radius: 50%;
  object-fit: cover;
  border: 3px solid #fff;
  box-shadow: 0 2px 8px rgba(0,0,0,0.3);
}

/* Sidebar link */
.sidebarAdmin .sidebar-link {
  display: block;
  padding: 12px;
  margin: 8px 0;
  background: white;
  color: #198754;
  text-decoration: none;
  border-radius: 8px;
  font-weight: 500;
  text-align: center;
  transition: all 0.3s;
}

/* Hover & Active */
.sidebarAdmin .sidebar-link:hover,
.sidebarAdmin .sidebar-link.active {
  background: #145c32;
  color: white;
}
</style>
