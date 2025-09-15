<?php

namespace App\Controllers;

use App\Models\ProdukModel;

class DetailProduk extends BaseController
{
    public function index($id = null)
    {
        $produkModel = new ProdukModel();

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
            'produk' => $produk
        ];

        return view('pembeli/detailproduk', $data);

    }
}
