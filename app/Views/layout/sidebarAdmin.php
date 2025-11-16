<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">

<?php 
// Ambil data user dari session jika tersedia, atau pakai default
$user = [
    'username' => session()->get('username') ?? 'Guest',
    'role'     => session()->get('role') ?? '-',
    'foto'     => session()->get('foto') ?? 'default.jpeg'
];
?>

<div class="sidebarAdmin d-flex flex-column position-fixed top-0 start-0 h-100 pt-4" 
     style="width: 250px; background: linear-gradient(180deg, #145c32, #198754, #28a745); box-shadow: 4px 0 12px rgba(0, 0, 0, 0.15); z-index: 1020;">

  <!-- Header -->
  <div class="text-center mb-4 px-3">
    <h4 class="fw-bold" style="background: linear-gradient(90deg, #ffffff, #b6f5c7); background-clip: text; -webkit-background-clip: text; -webkit-text-fill-color: transparent;">
      Farm <span>Unand</span>
    </h4>
  </div>

  <!-- Profil -->
  <div class="text-center mb-4 px-3">
    <img src="<?= base_url('uploads/profile/' . esc($user['foto'])) ?>" 
         alt="Foto Profil" 
         class="profile-photo mb-2 rounded-circle border-3 border-white shadow-sm " 
         style="width: 140px; height: 140px; object-fit: cover;">
    <p class="mb-0 fw-bold text-white">
        <?= esc($user['username']) ?> | <?= esc($user['role']) ?>
    </p>
  </div>

  <!-- Menu -->
  <div class="d-grid gap-2 px-3 flex-grow-1 mb-4">
    <a href="<?= base_url('profileadmin') ?>" class="sidebar-link <?= url_is('profileadmin*') ? 'active' : '' ?>">
      <i class="bi bi-person-circle me-2"></i>Profil
    </a>
    <a href="<?= base_url('dashboard') ?>" class="sidebar-link <?= url_is('dashboard*') ? 'active' : '' ?>">
      <i class="bi bi-speedometer2 me-2"></i>Dashboard
    </a>
    <a href="<?= base_url('admin/produk') ?>" class="sidebar-link <?= url_is('admin/produk*') ? 'active' : '' ?>">
      <i class="bi bi-box-seam me-2"></i>Produk
    </a>
    <a href="<?= base_url('MengelolaRiwayatPesanan') ?>" class="sidebar-link <?= url_is('MengelolaRiwayatPesanan*') ? 'active' : '' ?>">
      <i class="bi bi-journal-check me-2"></i>Pesanan
    </a>
    <a href="<?= base_url('manajemenakunuser') ?>" class="sidebar-link <?= url_is('manajemenakunuser*') ? 'active' : '' ?>">
      <i class="bi bi-people-fill me-2"></i>Manajemen Akun User
    </a>
    <a href="<?= base_url('melihatlaporan') ?>" class="sidebar-link <?= url_is('melihatlaporan*') ? 'active' : '' ?>">
      <i class="bi bi-file-earmark-bar-graph-fill me-2"></i>Laporan Penjualan
    </a>
  </div>

  <!-- Logout -->
  <div class="px-3 pb-4 mb-4 pt-2">
    <a href="<?= base_url('/login') ?>" class="sidebar-link logout-btn">
      <i class="bi bi-box-arrow-left me-2"></i>Log Out
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
  .sidebarAdmin {
    width: 250px;
  }
  .sidebar-link {
    display: flex;
    align-items: center;
    padding: 12px;
    border-radius: 8px;
    font-size: 1rem;
    font-weight: 500;
    color: #fff;
    text-decoration: none;
    transition: background 0.3s, transform 0.2s;
  }
  .sidebar-link i {
    color: inherit;
  }
  .sidebar-link:hover {
    background-color: rgba(255, 255, 255, 0.2);
    transform: translateX(4px);
  }
  .sidebar-link.active {
    background-color: rgba(255, 255, 255, 0.3);
  }
  .logout-btn {
    background-color: #dc3545;
    justify-content: center;
  }
  .logout-btn:hover {
    background-color: #a71d2a;
  }
  .profile-photo + p {
    font-weight: 700;
    color: #fff !important;
  }
</style>
