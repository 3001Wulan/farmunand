<div class="sidebar d-flex flex-column">
  <!-- Judul -->
  <div class="text-center mb-4 px-3">
    <h4 class="fw-bold sidebar-title">Farm <span>Unand</span></h4>
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
    <a href="/profile" class="sidebar-link <?= (url_is('profile')) ? 'active' : '' ?>">Akun Saya</a>
    <a href="/dashboarduser" class="sidebar-link <?= (url_is('dashboarduser')) ? 'active' : '' ?>">Dashboard</a>
    <a href="/keranjang" class="sidebar-link <?= (url_is('keranjang*')) ? 'active' : '' ?>">
      Keranjang
      <?php $cartCount = session()->get('cart_count_u_' . ($user['id_user'] ?? 0)) ?? 0; ?>
      <span class="badge bg-danger ms-2"><?= (int)$cartCount ?></span>
    </a>
    <a href="/riwayatpesanan" class="sidebar-link <?= (url_is('riwayatpesanan*')) ? 'active' : '' ?>">Pesanan Saya</a>

  </div>

  <!-- Log Out di bawah -->
  <div class="mt-auto px-3 mb-3">
    <a href="/login" class="sidebar-link logout-btn">Log Out</a>
  </div>
</div>

<style>
/* Sidebar container */
.sidebar {
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
.sidebar-title {
  font-size: 26px;
  font-weight: 800;
  background: linear-gradient(90deg, #ffffff, #d4f8e8);
  background-clip: text;             
  -webkit-background-clip: text;    
  -webkit-text-fill-color: transparent; 
  color: #fff; 
}

.sidebar-title span {
  color: #fff;
}

/* Foto Profil Lingkaran */
.sidebar .profile-photo {
  width: 140px;
  height: 140px;
  border-radius: 50%;
  object-fit: cover;
  border: 3px solid #fff;
  box-shadow: 0 2px 8px rgba(0,0,0,0.3);
}

/* Sidebar link */
.sidebar .sidebar-link {
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
.sidebar .sidebar-link:hover,
.sidebar .sidebar-link.active {
  background: #145c32;
  color: white;
}

/* Tombol Logout khusus */
.sidebar .logout-btn {
  background: #dc3545;
  color: #fff;
}
.sidebar .logout-btn:hover {
  background: #a71d2a;
  color: #fff;
}
</style>
