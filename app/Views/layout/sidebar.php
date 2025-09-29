


<div class="sidebar">
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
  <a href="/profile" class="sidebar-link <?= (url_is('profile')) ? 'active' : '' ?>">Akun Saya</a>
  <a href="/dashboarduser" class="sidebar-link <?= (url_is('dashboarduser')) ? 'active' : '' ?>">Dashboard</a>
  <a href="/riwayatpesanan" class="sidebar-link <?= (url_is('riwayatpesanan*')) ? 'active' : '' ?>">Pesanan Saya</a>

  <a href="/keranjang" class="sidebar-link <?= (url_is('keranjang*')) ? 'active' : '' ?>">
    Keranjang
    <?php $cartCount = session()->get('cart_count_u_' . ($user['id_user'] ?? 0)) ?? 0; ?>
      <span class="badge bg-danger ms-2"><?= (int)$cartCount ?></span>
  </a>

  <a href="/login" class="sidebar-link">Log Out</a>
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
  background: #198754; /* hijau bootstrap */
  color: white;
  overflow-y: auto;
  z-index: 1000;
  padding-top: 20px;
  box-shadow: 4px 0 12px rgba(0,0,0,0.15);
}

/* Foto Profil Lingkaran */
.sidebar .profile-photo {
  width: 120px;
  height: 120px;
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
</style>
