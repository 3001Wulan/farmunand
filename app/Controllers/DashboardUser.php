<?php

namespace App\Controllers;

use App\Models\ProdukModel;
use App\Models\PesanModel;
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
        $userId = session()->get('id_user');   // ✅ ambil id dari session login
        $user   = $userModel->find($userId);

        if (!$user) {
            return redirect()->to('/login')->with('error', 'User tidak ditemukan.');
        }

        $data = [
            'title'           => 'Dashboard User',
            'username'        => $user['username'], // atau 'nama' tergantung kolom di tabel users
            'pesanan_sukses'  => $pesananSukses,
            'pending'         => $pesananPending,
            'batal'           => $pesananBatal,
            'produk'          => $produk
        ];

        return view('pembeli/dashboarduser', $data);
    }
}
