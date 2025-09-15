<?php

namespace App\Controllers;

use App\Models\PesananModel;

class Pesanan extends BaseController
{
    public function index()
    {
        $pesananModel = new PesananModel();

        // sementara pakai id_user = 1 (nanti bisa ambil dari session login)
        $id_user = 1;

        $data['orders'] = $pesananModel->getPesananWithProduk($id_user);

        return view('pembeli/riwayatpesanan', $data);
    }
}
