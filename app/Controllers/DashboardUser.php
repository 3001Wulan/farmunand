<?php

namespace App\Controllers;

use App\Models\ProdukModel;
use App\Models\PesananModel;
use App\Models\UserModel;

class DashboardUser extends BaseController
{
    public function index()
    {
        $userId = session()->get('id_user');
        if (!$userId) {
            return redirect()->to('/login')->with('error', 'Silakan login dulu.');
        }

        $userModel    = new UserModel();
        $produkModel  = new ProdukModel();
        $pesananModel = new PesananModel();

        $user = $userModel->find($userId);

        $pesananSukses = $pesananModel->where('status_pemesanan', 'Selesai')->where('id_user', $userId)->countAllResults();
        $pesananPending = $pesananModel->where('status_pemesanan', 'Pending')->where('id_user', $userId)->countAllResults();
        $pesananBatal   = $pesananModel->where('status_pemesanan', 'Batal')->where('id_user', $userId)->countAllResults();

        $data = [
            'title'          => 'Dashboard User',
            'username'       => $user['username'],
            'role'           => $user['role'],
            'foto'           => $user['foto'],
            'pesanan_sukses' => $pesananSukses,
            'pending'        => $pesananPending,
            'batal'          => $pesananBatal,
            'produk'         => $produkModel->findAll(),
            'user'           => $user
        ];

        return view('Pembeli/dashboarduser', $data);
    }
}
