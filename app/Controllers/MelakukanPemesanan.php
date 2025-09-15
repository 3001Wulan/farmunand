<?php

namespace App\Controllers;

use App\Models\PesanModel;

class MelakukanPemesanan extends BaseController
{
    public function index($id = null)
    {
        $pesanModel = new PesanModel();

        if ($id) {
            $pesanan = $pesanModel->getPesananById($id);
        } else {
            // ambil pesanan pertama kalau id tidak ada
            $pesanan = $pesanModel->first();
        }

        return view('pembeli/melakukanpemesanan', [
            'pesanan' => $pesanan
        ]);
    }
}
