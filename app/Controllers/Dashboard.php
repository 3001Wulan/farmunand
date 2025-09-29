<?php

namespace App\Controllers;

use App\Models\ProdukModel;
use App\Models\UserModel;
use App\Models\PesanModel;
use App\Models\PesananModel;

class Dashboard extends BaseController
{
    public function index()
    {
        $produkModel   = new ProdukModel();
        $userModel     = new UserModel();
        $pesanModel    = new PesanModel();
        $pesananModel  = new PesananModel();

        $userId = session()->get('id_user');
        if (!$userId) {
            return redirect()->to('/login')->with('error', 'Silakan login dulu.');
        }

        $user = $userModel->find($userId);

        $data = [
            'title'           => 'Dashboard',
            'total_produk'    => method_exists($produkModel, 'getTotalProduk')
                                    ? $produkModel->getTotalProduk()
                                    : $produkModel->countAllResults(),
            'total_user'      => method_exists($userModel, 'getTotalUser')
                                    ? $userModel->getTotalUser()
                                    : $userModel->countAllResults(),
            // ✅ pakai PesananModel (bukan TransaksiModel)
            'transaksi_hari'  => $pesananModel->getTransaksiHariIni(),
            'penjualan_bulan' => $pesananModel->getPenjualanBulan(),
            'stok_rendah'     => method_exists($produkModel, 'getStokRendah')
                                    ? $produkModel->getStokRendah()
                                    : 0,
            'pesan_masuk'     => method_exists($pesanModel, 'getPesanMasuk')
                                    ? $pesanModel->getPesanMasuk()
                                    : 0,
            'total_pesanan'   => $pesananModel->countAllResults(),
            'user'            => $user
        ];

        return view('Admin/Dashboard', $data);
    }
}
