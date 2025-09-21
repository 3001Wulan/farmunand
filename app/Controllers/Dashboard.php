<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Models\ProdukModel;
use App\Models\UserModel;
use App\Models\TransaksiModel;
use App\Models\PesanModel;
use App\Models\PesananModel; // ✅ Tambahkan

class Dashboard extends BaseController
{
    public function index()
    {
        $produkModel     = new ProdukModel();
        $userModel       = new UserModel();
        $transaksiModel  = new TransaksiModel();
        $pesanModel      = new PesanModel();
        $pesananModel    = new PesananModel(); // ✅ Instance

        $userId = session()->get('id_user');   
        $user   = $userModel->find($userId);

        $data = [
            'title'           => 'Dashboard',
            'total_produk'    => $produkModel->getTotalProduk(),
            'total_user'      => $userModel->getTotalUser(),
            'transaksi_hari'  => $transaksiModel->getTransaksiHariIni(),
            'penjualan_bulan' => $transaksiModel->getPenjualanBulan(),
            'stok_rendah'     => $produkModel->getStokRendah(),
            'pesan_masuk'     => $pesanModel->getPesanMasuk(),
            'total_pesanan'   => $pesananModel->countAllResults(), // ✅ Total pesanan
            'user'            => $user 
        ];

        return view('Admin/Dashboard', $data);
    }
}
