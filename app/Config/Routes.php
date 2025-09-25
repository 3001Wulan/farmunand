<?php

use CodeIgniter\Router\RouteCollection;

/**
 * @var RouteCollection $routes
 */
$routes->get('/', 'Home::index');

// LOGIN
$routes->get('/login', 'Auth::login');
$routes->post('/auth/doLogin', 'Auth::doLogin');

// Forgot Password
$routes->get('/forgot-password', 'Auth::forgotPassword');
$routes->post('/auth/sendResetLink', 'Auth::sendResetLink');
$routes->get('/reset-password/(:any)', 'Auth::resetPassword/$1');
$routes->post('/auth/doResetPassword', 'Auth::doResetPassword');

// REGISTER
$routes->get('/register', 'Auth::register');
$routes->post('/auth/doRegister', 'Auth::doRegister');

// Profile Pembeli
$routes->get('/profile', 'Profile::index');
$routes->get('/profile/edit', 'Profile::edit');
$routes->post('/profile/update', 'Profile::update');

// Profile Admin
$routes->get('/profileadmin', 'ProfileAdmin::index');
$routes->get('/profileadmin/edit', 'ProfileAdmin::edit');
$routes->post('/profileadmin/update', 'ProfileAdmin::update');

// LOGOUT
$routes->get('/logout', 'Auth::logout');


// Dashboard Admin & User
$routes->get('/dashboard', 'Dashboard::index');
$routes->get('/dashboarduser', 'DashboardUser::index');

// Fungsional User
$routes->get('/riwayatpesanan', 'Pesanan::index');
//$routes->get('/detailproduk', 'DetailProduk::index');
$routes->get('/detailproduk/(:num)', 'DetailProduk::index/$1');
$routes->get('/melakukanpemesanan', 'MelakukanPemesanan::index');
$routes->get('/melakukanpemesanan/(:num)', 'MelakukanPemesanan::index/$1');
$routes->get('/melihatlaporan', 'MelihatLaporan::index');
$routes->post('/melihatlaporan/filter', 'MelihatLaporan::filter');
$routes->get('/penilaian/(:num)', 'Penilaian::index/$1');
$routes->post('/penilaian/simpan/(:num)', 'Penilaian::simpan/$1');
$routes->group('penilaian', function($routes) {
    // Halaman daftar pesanan selesai yang belum dinilai
    $routes->get('daftar', 'Penilaian::daftar');

    // Form penilaian untuk produk tertentu (opsional jika pakai halaman terpisah)
    $routes->get('(:num)', 'Penilaian::index/$1');

    // Simpan penilaian
    $routes->post('simpan/(:num)', 'Penilaian::simpan/$1');
});
$routes->get('/memilihalamat', 'MemilihAlamat::index');
$routes->match(['get', 'post'], '/memilihalamat/tambah', 'MemilihAlamat::tambah');
$routes->match(['get', 'post'], '/memilihalamat/ubah/(:num)', 'MemilihAlamat::ubah/$1');
$routes->match(['get','post'], '/memilihalamat/tambah', 'MemilihAlamat::tambah');


// Fungsioanal Admin
$routes->get('/MengelolaRiwayatPesanan', 'MengelolaRiwayatPesanan::index');
$routes->group('manajemenakunuser', ['namespace' => 'App\Controllers'], function($routes) {
    $routes->get('/', 'ManajemenAkunUser::index');               // list user
    $routes->get('create', 'ManajemenAkunUser::create');         // form tambah
    $routes->post('store', 'ManajemenAkunUser::store');          // simpan
    $routes->get('edit/(:num)', 'ManajemenAkunUser::edit/$1');   // form edit
    $routes->post('update/(:num)', 'ManajemenAkunUser::update/$1'); // update
    $routes->get('delete/(:num)', 'ManajemenAkunUser::delete/$1');  // hapus
});


$routes->group('', ['namespace' => 'App\Controllers'], function($routes) {
    // Halaman daftar pesanan
    $routes->get('konfirmasipesanan', 'KonfirmasiPesanan::index');

    // Aksi konfirmasi selesai pesanan (pakai id pesanan)
    $routes->get('konfirmasipesanan/selesai/(:num)', 'KonfirmasiPesanan::selesai/$1');
});

// Admin Mengelola Produk
$routes->group('admin', ['filter' => 'auth:admin'], function($routes) {
    $routes->get('produk', 'ProdukAdmin::index');
    $routes->get('produk/create', 'ProdukAdmin::create');
    $routes->post('produk/store', 'ProdukAdmin::store');
    $routes->get('produk/edit/(:num)', 'ProdukAdmin::edit/$1');
    $routes->post('produk/update/(:num)', 'ProdukAdmin::update/$1');
    $routes->get('produk/delete/(:num)', 'ProdukAdmin::delete/$1');
});

$routes->get('/pesananselesai', 'Pesanan::selesai');
$routes->get('/pesanandikemas', 'Pesanan::dikemas');
$routes->get('/pesananbelumbayar', 'Pesanan::belumbayar');

$routes->get('memilihalamat', 'MemilihAlamat::index');
$routes->post('memilihalamat/tambah', 'MemilihAlamat::tambah');
$routes->get('memilihalamat/ubah/(:num)', 'MemilihAlamat::ubah/$1');
$routes->post('memilihalamat/ubah/(:num)', 'MemilihAlamat::ubah/$1');
$routes->get('memilihalamat/pilih/(:num)', 'MemilihAlamat::pilih/$1');
$routes->post('memilihalamat/pilih/(:num)', 'MemilihAlamat::pilih/$1');
$routes->get('memilihalamat/(:num)', 'MemilihAlamat::index/$1');
