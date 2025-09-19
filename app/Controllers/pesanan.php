<?php

namespace App\Controllers;

use App\Models\PesananModel;

class Pesanan extends BaseController
{
    public function index()
    {
        $pesananModel = new PesananModel();

        // Ambil session
        $session = session();

        // Ambil id_user dari session login
        $id_user = $session->get('id_user');

        // Pastikan id_user ada, jika tidak redirect ke login atau halaman lain
        if (!$id_user) {
            return redirect()->to('/login');
        }

        $data['orders'] = $pesananModel->getPesananWithProduk($id_user);

        return view('pembeli/riwayatpesanan', $data);
    }
}
