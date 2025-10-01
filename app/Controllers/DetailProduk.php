<?php

namespace App\Controllers;

use App\Models\ProdukModel;
use App\Models\UserModel;
use App\Models\PenilaianModel;

class DetailProduk extends BaseController
{
    public function index($id_produk = null)
    {
        $produkModel    = new ProdukModel();
        $userModel      = new UserModel();
        $penilaianModel = new PenilaianModel(); // model untuk tabel detail_pemesanan

        // Ambil data user login
        $userId = session()->get('id_user');
        $user   = $userModel->find($userId);

        // Ambil data produk
        $produk = $id_produk
            ? $produkModel->find($id_produk)
            : $produkModel->first();

        if (!$produk) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound("Produk dengan ID $id_produk tidak ditemukan.");
        }
        $reviews = $penilaianModel
        ->select('detail_pemesanan.*, users.nama as nama_user, users.username')
        ->join('pemesanan', 'pemesanan.id_pemesanan = detail_pemesanan.id_pemesanan')
        ->join('users', 'users.id_user = pemesanan.id_user')
        ->where('detail_pemesanan.id_produk', $produk['id_produk'])
        ->where('detail_pemesanan.user_rating >', 0)
        ->orderBy('detail_pemesanan.updated_at', 'DESC')
        ->findAll();
    
        return view('pembeli/detailproduk', [
            'title'   => 'Detail Produk',
            'produk'  => $produk,
            'user'    => $user,
            'reviews' => $reviews
        ]);
    }
}
