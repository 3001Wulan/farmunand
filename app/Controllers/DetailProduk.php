<?php

namespace App\Controllers;

use App\Models\ProdukModel;
use App\Models\UserModel;

class DetailProduk extends BaseController
{
    public function index($id = null)
    {
        $produkModel = new ProdukModel();
        $userModel = new UserModel();
        $userId = session()->get('id_user');   // ✅ ambil id dari session login
        $user   = $userModel->find($userId);

        if ($id === null) {
            // Kalau tidak ada id, ambil produk pertama
            $produk = $produkModel->first();
        } else {
            $produk = $produkModel->find($id);
        }

        if (!$produk) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound("Produk dengan ID $id tidak ditemukan.");
        }

        $data = [
            'title'  => 'Detail Produk',
            'produk' => $produk,
            'user'   => $user 
        ];


        return view('pembeli/detailproduk', $data);

    }
}
