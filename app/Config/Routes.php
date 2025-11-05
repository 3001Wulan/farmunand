<?php

use CodeIgniter\Router\RouteCollection;

/**
 * @var RouteCollection $routes
 */
$routes->get('/', 'Home::index');

// =======================
// Auth Routes
// =======================

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




// =======================
// Dashboard & Produk
// =======================

// Dashboard Admin & User
$routes->get('/dashboard', 'Dashboard::index');
$routes->get('/dashboarduser', 'DashboardUser::index');

// Detail Produk User
$routes->get('/detailproduk/(:num)', 'DetailProduk::index/$1');

// Fungsional Admin Mengelola Produk
$routes->group('admin', ['filter' => 'auth:admin'], function($routes) {
    $routes->get('produk', 'ProdukAdmin::index');
    $routes->get('produk/create', 'ProdukAdmin::create');
    $routes->post('produk/store', 'ProdukAdmin::store');
    $routes->get('produk/edit/(:num)', 'ProdukAdmin::edit/$1');
    $routes->post('produk/update/(:num)', 'ProdukAdmin::update/$1');
    $routes->post('produk/delete/(:num)', 'ProdukAdmin::delete/$1');
});



// =======================
// Melakukan Pemesanan
// =======================
$routes->post('melakukanpemesanan', 'MelakukanPemesanan::index', ['filter' => 'authActive']);
$routes->get('melakukanpemesanan', 'MelakukanPemesanan::index', ['filter' => 'authActive']);
$routes->get('melakukanpemesanan/(:num)', 'MelakukanPemesanan::index/$1', ['filter' => 'authActive']);

// =======================
// Pemesanan (single & batch)
// =======================
$routes->group('pemesanan', function($routes) {
    $routes->post('simpan', 'MelakukanPemesanan::simpan', ['filter' => 'authActive']);
    $routes->post('simpan-batch', 'MelakukanPemesanan::simpanBatch', ['filter' => 'authActive']);
});

// =======================
// Keranjang (Cart)
// =======================

// Lihat keranjang → hanya butuh login
$routes->get('keranjang', 'Keranjang::index', ['filter' => 'auth']);

// Ubah keranjang & checkout → wajib user aktif
$routes->post('keranjang/add', 'Keranjang::add', ['filter' => 'authActive']);
$routes->post('keranjang/update', 'Keranjang::update', ['filter' => 'authActive']);
$routes->post('keranjang/remove/(:num)', 'Keranjang::remove/$1', ['filter' => 'authActive']);
$routes->post('keranjang/clear', 'Keranjang::clear', ['filter' => 'authActive']);
$routes->post('keranjang/checkout-all', 'Keranjang::checkoutAll', ['filter' => 'authActive']);






// Fungsional Penilaian
$routes->group('penilaian', function($routes) {
    // Halaman daftar pesanan selesai yang belum dinilai
    $routes->get('daftar', 'Penilaian::daftar');

    // Form penilaian untuk produk tertentu (opsional jika pakai halaman terpisah)
    $routes->get('(:num)', 'Penilaian::index/$1');

    // Simpan penilaian
    $routes->post('simpan/(:num)', 'Penilaian::simpan/$1');

});

// Fungsional Memilih Alamat
$routes->get('/memilihalamat', 'MemilihAlamat::index');
$routes->match(['get', 'post'], '/memilihalamat/tambah', 'MemilihAlamat::tambah');
$routes->match(['get', 'post'], '/memilihalamat/ubah/(:num)', 'MemilihAlamat::ubah/$1');
$routes->match(['get','post'], '/memilihalamat/tambah', 'MemilihAlamat::tambah');

$routes->get('memilihalamat', 'MemilihAlamat::index');
$routes->post('memilihalamat/tambah', 'MemilihAlamat::tambah');
$routes->get('memilihalamat/ubah/(:num)', 'MemilihAlamat::ubah/$1');
$routes->post('memilihalamat/ubah/(:num)', 'MemilihAlamat::ubah/$1');
$routes->get('memilihalamat/pilih/(:num)', 'MemilihAlamat::pilih/$1');
$routes->post('memilihalamat/pilih/(:num)', 'MemilihAlamat::pilih/$1');
$routes->get('memilihalamat/(:num)', 'MemilihAlamat::index/$1');

// Page
$routes->get('/MengelolaRiwayatPesanan', 'MengelolaRiwayatPesanan::index');
$routes->get('/konfirmasipesanan', 'Pesanan::konfirmasipesanan');               // halaman Dikirim
$routes->post('/pesanan/konfirmasi/(:num)', 'Pesanan::konfirmasiSelesai/$1');   // tombol konfirmasi


// Fungsional User
$routes->get('/riwayatpesanan', 'Pesanan::index');

// Update status (POST)
$routes->post('mengelolariwayatpesanan/updateStatus/(:num)', 'MengelolaRiwayatPesanan::updateStatus/$1');
// ^ gunakan nama ini persis di form action (tanpa typo)

// Fungsional Admin Konfirmasi Pesanan
$routes->get('/pesananselesai', 'Pesanan::selesai');
$routes->get('/pesanandikemas', 'Pesanan::dikemas');
$routes->get('/pesananbelumbayar', 'Pesanan::belumbayar');
$routes->get('pesanandibatalkan',  'Pesanan::dibatalkan');
$routes->group('', ['namespace' => 'App\Controllers'], function($routes) {
    // Halaman daftar pesanan
    $routes->get('konfirmasipesanan', 'KonfirmasiPesanan::index');

    // Aksi konfirmasi selesai pesanan (pakai id pesanan)
    $routes->get('konfirmasipesanan/selesai/(:num)', 'KonfirmasiPesanan::selesai/$1');
});
$routes->get('pesanan/konfirmasi/(:num)', 'KonfirmasiPesanan::selesai/$1');





// Fungsioanal Admin Manajemen Akun User
$routes->group('manajemenakunuser', ['namespace' => 'App\Controllers'], function($routes) {
    $routes->get('/', 'ManajemenAkunUser::index');
    $routes->get('edit/(:num)', 'ManajemenAkunUser::edit/$1');
    $routes->post('update/(:num)', 'ManajemenAkunUser::update/$1');
    $routes->post('delete/(:num)', 'ManajemenAkunUser::delete/$1');
});

// Fungsional Admin Melihat Laporan
$routes->get('/melihatlaporan', 'MelihatLaporan::index');
$routes->post('/melihatlaporan/filter', 'MelihatLaporan::filter');
$routes->get('/penilaian/(:num)', 'Penilaian::index/$1');
$routes->post('/penilaian/simpan/(:num)', 'Penilaian::simpan/$1');
$routes->get('melihatlaporan', 'MelihatLaporan::index');
$routes->get('melihatlaporan/exportExcel', 'MelihatLaporan::exportExcel');

// Fungsional User Memproses Pembayaran via Midtrans
$routes->group('payments', static function($r){
    $r->post('create',            'Payments::create');              // buat order & token
    $r->post('resume/(:segment)', 'Payments::resume/$1');           // ambil token utk order lama (atau bikin retry)
    $r->post('webhook',           'Payments::webhook');             // notifikasi dari Midtrans
    $r->post('cancel',            'Payments::cancelByUser');        // ← user membatalkan pesanan "Belum Bayar"
    $r->post('cancel_keep',       'Payments::cancelByUserKeep');
    $r->get('finish',             'Payments::finish');
    $r->get('unfinish',           'Payments::unfinish');
    $r->get('error',              'Payments::error');
});

