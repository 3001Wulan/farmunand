<?php

use CodeIgniter\Router\RouteCollection;

/**
 * @var RouteCollection $routes
 */
$routes->get('/', 'Home::index');
// LOGIN
$routes->get('/login', 'Auth::login');
$routes->post('/auth/doLogin', 'Auth::doLogin');

// REGISTER
$routes->get('/register', 'Auth::register');
$routes->post('/auth/doRegister', 'Auth::doRegister');

// LOGOUT
$routes->get('/logout', 'Auth::logout');

$routes->get('/dashboard', 'Dashboard::index');
$routes->get('/MengelolaRiwayatPesanan', 'MengelolaRiwayatPesanan::index');
$routes->group('manajemenakunuser', ['namespace' => 'App\Controllers'], function($routes) {
    $routes->get('/', 'ManajemenAkunUser::index');               // list user
    $routes->get('create', 'ManajemenAkunUser::create');         // form tambah
    $routes->post('store', 'ManajemenAkunUser::store');          // simpan
    $routes->get('edit/(:num)', 'ManajemenAkunUser::edit/$1');   // form edit
    $routes->post('update/(:num)', 'ManajemenAkunUser::update/$1'); // update
    $routes->get('delete/(:num)', 'ManajemenAkunUser::delete/$1');  // hapus
});
$routes->get('/riwayatpesanan', 'Pesanan::index');

$routes->group('', ['namespace' => 'App\Controllers'], function($routes) {
    // Halaman daftar pesanan
    $routes->get('konfirmasipesanan', 'KonfirmasiPesanan::index');

    // Aksi konfirmasi selesai pesanan (pakai id pesanan)
    $routes->get('konfirmasipesanan/selesai/(:num)', 'KonfirmasiPesanan::selesai/$1');
});
$routes->get('/dashboarduser', 'DashboardUser::index');
$routes->get('/detailproduk', 'DetailProduk::index');
$routes->get('/detailproduk/(:num)', 'DetailProduk::index/$1');
$routes->get('/melakukanpemesanan', 'MelakukanPemesanan::index');
$routes->get('/melakukanpemesanan/(:num)', 'MelakukanPemesanan::index/$1');

