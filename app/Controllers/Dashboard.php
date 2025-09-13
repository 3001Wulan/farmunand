<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Models\ProdukModel;
use App\Models\UserModel;
use App\Models\TransaksiModel;
use App\Models\PesanModel;

class Dashboard extends BaseController
{
    public function index()
    {
        $produkModel     = new ProdukModel();
        $userModel       = new UserModel();
        $transaksiModel  = new TransaksiModel();
        $pesanModel      = new PesanModel();

        $data = [
            'title'           => 'Dashboard',
            'total_produk'    => $produkModel->getTotalProduk(),
            'total_user'      => $userModel->getTotalUser(),
            'transaksi_hari'  => $transaksiModel->getTransaksiHariIni(),
            'penjualan_bulan' => $transaksiModel->getPenjualanBulan(),
            'stok_rendah'     => $produkModel->getStokRendah(),
            'pesan_masuk'     => $pesanModel->getPesanMasuk(),
        ];

        return view('admin/dashboard', $data);
    }
}
