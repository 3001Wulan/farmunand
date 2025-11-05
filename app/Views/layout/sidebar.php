<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">

<!-- SIDEBAR USER (konsep seragam dengan admin) -->
<div class="sidebarUser d-flex flex-column position-fixed top-0 start-0 h-100 pt-4"
     style="width:250px; background:linear-gradient(180deg,#145c32,#198754,#28a745); box-shadow:4px 0 12px rgba(0,0,0,.15); z-index:1020;">

  <!-- Brand -->
  <div class="text-center mb-4 px-3">
    <h4 class="fw-bold" style="background:linear-gradient(90deg,#ffffff,#d4f8e8); background-clip:text; -webkit-background-clip:text; -webkit-text-fill-color:transparent;">
      Farm <span>Unand</span>
    </h4>
  </div>

  <!-- Profile -->
  <div class="text-center mb-4 px-3">
    <img src="<?= base_url('uploads/profile/' . ($user['foto'] ?? 'default.jpeg')) ?>"
         alt="Foto Profil"
         class="profile-photo mb-2 rounded-circle border-3 border-white shadow-sm"
         style="width:140px; height:140px; object-fit:cover;">
    <p class="mb-0 fw-bold text-white"><?= esc($user['username']) ?> | <?= esc($user['role']) ?></p>
  </div>

  <!-- Menu -->
  <div class="d-grid gap-2 px-3 flex-grow-1 mb-4">
    <a href="<?= base_url('profile') ?>" class="sidebar-link <?= url_is('profile*') ? 'active' : '' ?>">
      <i class="bi bi-person-circle me-2"></i> Akun Saya
    </a>

    <a href="<?= base_url('dashboarduser') ?>" class="sidebar-link <?= url_is('dashboarduser*') ? 'active' : '' ?>">
      <i class="bi bi-speedometer2 me-2"></i> Dashboard
    </a>

    <a href="<?= base_url('keranjang') ?>" class="sidebar-link <?= url_is('keranjang*') ? 'active' : '' ?>">
      <i class="bi bi-cart3 me-2"></i> Keranjang
      <?php $cartCount = session()->get('cart_count_u_' . ($user['id_user'] ?? 0)) ?? 0; ?>
      <span class="badge bg-danger ms-2"><?= (int)$cartCount ?></span>
    </a>

    <a href="<?= base_url('riwayatpesanan') ?>" class="sidebar-link <?= url_is('riwayatpesanan*') ? 'active' : '' ?>">
      <i class="bi bi-journal-check me-2"></i> Pesanan Saya
    </a>
  </div>

  <!-- Logout -->
  <div class="px-3 pb-4 mb-4 pt-2">
    <a href="/login" class="sidebar-link logout-btn">
      <i class="bi bi-box-arrow-left me-2"></i> Log Out
    </a>
  </div>
</div>

<style>
  .profile-photo {
    width: 140px;
    height: 140px;
    border-radius: 50%;
    object-fit: cover;
    border: 5px solid white;
    box-shadow: 0 6px 16px rgba(0,0,0,0.35);
    flex-shrink: 0;
  }
  .sidebarUser { width: 250px; }
  .sidebar-link{
    display:flex; align-items:center;
    padding:12px; border-radius:8px;
    font-size:1rem; font-weight:500;
    color:#fff; text-decoration:none;
    transition:background .3s, transform .2s;
  }
  .sidebar-link i{ color:inherit; }
  .sidebar-link:hover{ background-color:rgba(255,255,255,.2); transform:translateX(4px); }
  .sidebar-link.active{ background-color:rgba(255,255,255,.3); }
  .logout-btn{ background-color:#dc3545; justify-content:center; }
  .logout-btn:hover{ background-color:#a71d2a; }
  .profile-photo + p{ font-weight:700; color:#fff !important; }
</style>
