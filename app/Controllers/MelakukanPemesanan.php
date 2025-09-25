<?php

namespace App\Controllers;

use App\Models\PesanModel;

class MelakukanPemesanan extends BaseController
{
    public function index($id = null)
    {
        $idUser = session()->get('id_user');

        $pesanModel = new PesanModel();

        if ($id) {
            $pesanan = $pesanModel->getPesananById($id);
        } else {
            $pesanan = $pesanModel->first();
        }

        $user = session()->get(); 

        return view('pembeli/melakukanpemesanan', [
            'pesanan' => $pesanan,
            'user'    => $user,  
        ]);
    }
}


