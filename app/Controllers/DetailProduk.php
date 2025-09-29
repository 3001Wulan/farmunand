<?php

namespace App\Controllers;

use App\Models\ProdukModel;
use App\Models\UserModel;

class DetailProduk extends BaseController
{
    public function index($id_produk = null)
    {
        $produkModel = new ProdukModel();
        $userModel   = new UserModel();

        $userId = session()->get('id_user');
        $user   = $userModel->find($userId);

        $produk = $id_produk
            ? $produkModel->find($id_produk)
            : $produkModel->first();

        if (!$produk) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound("Produk dengan ID $id_produk tidak ditemukan.");
        }

        return view('pembeli/detailproduk', [
            'title'  => 'Detail Produk',
            'produk' => $produk,
            'user'   => $user
        ]);
    }
}
