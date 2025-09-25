<?php

namespace App\Controllers;

use App\Models\PesananModel;
use App\Models\UserModel;

class Pesanan extends BaseController
{
    public function index()
    {
        $pesananModel = new PesananModel();

        // Ambil session
        $session = session();
        $id_user = $session->get('id_user');

        $model = new UserModel();
        $data['users'] = $model->findAll();
        $userId = $session->get('id_user');   
        $user   = $model->find($userId);

        $data = [
            'users' => $data['users'],
            'user'  => $user 
        ];

        // Jika belum login
        if (!$id_user) {
            return redirect()->to('/login');
        }

        // Semua pesanan user
        $data['orders'] = $pesananModel->getPesananWithProduk($id_user);

        return view('pembeli/riwayatpesanan', $data);
    }

    // === Tambahan untuk menampilkan pesanan selesai ===
    public function selesai()
    {
        $pesananModel = new PesananModel();
$session = session();
$id_user = $session->get('id_user');

$model = new UserModel();
$data['users'] = $model->findAll();
$userId = $session->get('id_user');
$user   = $model->find($userId);

$data = [
    'users' => $data['users'],
    'user'  => $user
];                             

if (!$id_user) {
    return redirect()->to('/login');
}

// Ambil hanya pesanan dengan status "Selesai" + join produk
$db = \Config\Database::connect();
$builder = $db->table('pemesanan p')
    ->select('p.*, pr.nama_produk, pr.foto,pr.harga, dp.jumlah_produk')
    ->join('detail_pemesanan dp', 'dp.id_pemesanan = p.id_pemesanan')
    ->join('produk pr', 'pr.id_produk = dp.id_produk')
    ->where('p.id_user', $id_user)
    ->where('p.status_pemesanan', 'Selesai');

$data['orders'] = $builder->get()->getResultArray();

return view('pembeli/pesananselesai', $data);

    }

    public function dikemas()
    {
        $pesananModel = new PesananModel();
$session = session();
$id_user = $session->get('id_user');

$model = new UserModel();
$data['users'] = $model->findAll();
$userId = $session->get('id_user');
$user   = $model->find($userId);

$data = [
    'users' => $data['users'],
    'user'  => $user
];                             

if (!$id_user) {
    return redirect()->to('/login');
}

// Ambil hanya pesanan dengan status "Selesai" + join produk
$db = \Config\Database::connect();
$builder = $db->table('pemesanan p')
    ->select('p.*, pr.nama_produk, pr.foto,pr.harga, dp.jumlah_produk')
    ->join('detail_pemesanan dp', 'dp.id_pemesanan = p.id_pemesanan')
    ->join('produk pr', 'pr.id_produk = dp.id_produk')
    ->where('p.id_user', $id_user)
    ->where('p.status_pemesanan', 'dikemas');

$data['orders'] = $builder->get()->getResultArray();

return view('pembeli/pesanandikemas', $data);

    }
    public function belumbayar()
    {
        $pesananModel = new PesananModel();
$session = session();
$id_user = $session->get('id_user');

$model = new UserModel();
$data['users'] = $model->findAll();
$userId = $session->get('id_user');
$user   = $model->find($userId);

$data = [
    'users' => $data['users'],
    'user'  => $user
];                             

if (!$id_user) {
    return redirect()->to('/login');
}

// Ambil hanya pesanan dengan status "Selesai" + join produk
$db = \Config\Database::connect();
$builder = $db->table('pemesanan p')
    ->select('p.*, pr.nama_produk, pr.foto,pr.harga, dp.jumlah_produk')
    ->join('detail_pemesanan dp', 'dp.id_pemesanan = p.id_pemesanan')
    ->join('produk pr', 'pr.id_produk = dp.id_produk')
    ->where('p.id_user', $id_user)
    ->where('p.status_pemesanan', 'dikemas');

$data['orders'] = $builder->get()->getResultArray();

return view('pembeli/pesananbelumbayar', $data);

    }
}
