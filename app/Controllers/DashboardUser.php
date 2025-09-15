<?php

namespace App\Controllers;

use App\Models\ProdukModel;
use App\Models\PesanModel;   // ← ganti sesuai nama file/class kamu
use App\Models\UserModel;

class DashboardUser extends BaseController
{
    public function index()
    {
        $produkModel = new ProdukModel();
        $produk = $produkModel->findAll();

        $pesanModel = new PesanModel();
        $pesananSukses = $pesanModel->where('status_pemesanan', 'Selesai')->countAllResults();
        $pesananPending = $pesanModel->where('status_pemesanan', 'Pending')->countAllResults();
        $pesananBatal   = $pesanModel->where('status_pemesanan', 'Batal')->countAllResults();

        $userModel = new UserModel();
        $user = $userModel->find(2); // contoh ambil user id=2

        $data = [
            'title' => 'Dashboard User',
            'username' => $user['nama'],
            'pesanan_sukses' => $pesananSukses,
            'pending' => $pesananPending,
            'batal' => $pesananBatal,
            'produk' => $produk
        ];

        return view('pembeli/dashboarduser', $data);
    }
}
