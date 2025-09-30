<div class="sidebarAdmin d-flex flex-column">
  <!-- Judul -->
  <div class="text-center mb-4 px-3">
    <h4 class="fw-bold sidebarAdmin-title">Farm <span>Unand</span></h4>
  </div>

  <!-- Foto Profil -->
  <div class="text-center mb-4">
    <img src="<?= base_url('uploads/profile/' . ($user['foto'] ?? 'default.jpeg')) ?>" 
         alt="Foto Profil" 
         class="profile-photo">
    <p class="mt-2 mb-0 fw-semibold">
      <?= esc($user['username']) ?> | <?= esc($user['role']) ?>
    </p>
  </div>

  <!-- Menu -->
  <div class="d-grid gap-1 px-3 flex-grow-2">
    <a href="/profileadmin" class="sidebar-link <?= (url_is('profileadmin')) ? 'active' : '' ?>">Profil</a>
    <a href="/dashboard" class="sidebar-link <?= (url_is('dashboard')) ? 'active' : '' ?>">Dashboard</a>
    <a href="<?= base_url('admin/produk') ?>" 
       class="sidebar-link <?= (url_is('admin/produk*')) ? 'active' : '' ?>">Produk</a>
    <a href="/MengelolaRiwayatPesanan" class="sidebar-link <?= (url_is('pesanan*')) ? 'active' : '' ?>">Pesanan</a>
    <a href="/manajemenakunuser" class="sidebar-link <?= (url_is('manajemenakunuser')) ? 'active' : '' ?>">Manajemen Akun User</a>
    <a href="/melihatlaporan" class="sidebar-link <?= (url_is('laporanpenjualan')) ? 'active' : '' ?>">Laporan Penjualan</a>
  </div>

  <!-- Log Out di bawah -->
  <div class="mt-auto px-3 mb-3">
    <a href="/login" class="sidebar-link logout-btn">Log Out</a>
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
  background: linear-gradient(180deg, #145c32, #198754, #28a745);
  color: white;
  overflow-y: auto;
  z-index: 1000;
  padding-top: 20px;
  box-shadow: 4px 0 12px rgba(0,0,0,0.15);
}

/* Judul Farm Unand */
.sidebarAdmin-title {
  font-size: 26px;
  font-weight: 800;
  background: linear-gradient(90deg, #ffffff, #b6f5c7);
  background-clip: text;
  -webkit-background-clip: text;
  -webkit-text-fill-color: transparent;
  color: #fff; /* fallback */
}
.sidebarAdmin-title span {
  color: #fff;
}

/* Foto Profil */
.sidebarAdmin .profile-photo {
  width: 140px;
  height: 140px;
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

/* Tombol Logout khusus */
.sidebarAdmin .logout-btn {
  background: #dc3545;
  color: #fff;
}
.sidebarAdmin .logout-btn:hover {
  background: #a71d2a;
  color: #fff;
}
</style>
