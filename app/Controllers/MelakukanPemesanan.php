<?php

namespace App\Controllers;

use App\Models\PesanModel;
use App\Models\AlamatModel;

class MelakukanPemesanan extends BaseController
{
    public function index($id = null)
    {
        $idUser = session()->get('id_user');

        $pesanModel = new PesanModel();
        $alamatModel = new AlamatModel();

        // Ambil pesanan
        if ($id) {
            $pesanan = $pesanModel->getPesananById($id);
        } else {
            $pesanan = $pesanModel->first();
        }

        // Ambil alamat aktif user
        $alamatAktif = $alamatModel->getAlamatAktifByUser($idUser);

        $user = session()->get(); 

        return view('pembeli/melakukanpemesanan', [
            'pesanan' => $pesanan,
            'user'    => $user,
            'alamat'  => $alamatAktif, // kirim ke view
        ]);
    }
}
