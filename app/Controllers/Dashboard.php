<?php

namespace App\Controllers;

use App\Models\ProdukModel;
use App\Models\UserModel;
use App\Models\PesananModel;

class Dashboard extends BaseController
{
    protected $produkModel;
    protected $userModel;
    protected $pesananModel;

    public function __construct()
    {
        $this->produkModel = new ProdukModel();
        $this->userModel   = new UserModel();
        $this->pesananModel = new PesananModel();
    }

    public function index()
    {
        $userId = session()->get('id_user');
        if (!$userId) {
            return redirect()->to('/login')->with('error', 'Silakan login dulu.');
        }

        $user = $this->userModel->find($userId);

        $data = [
            'title'           => 'Dashboard',
            'total_produk'    => method_exists($this->produkModel, 'getTotalProduk') ? $this->produkModel->getTotalProduk() : $this->produkModel->countAllResults(),
            'total_user'      => method_exists($this->userModel, 'getTotalUser') ? $this->userModel->getTotalUser() : $this->userModel->countAllResults(),
            'transaksi_hari'  => $this->pesananModel->getTransaksiHariIni(),
            'penjualan_bulan' => $this->pesananModel->getPenjualanBulan(),
            'stok_rendah'     => method_exists($this->produkModel, 'getStokRendah') ? $this->produkModel->getStokRendah() : 0,
            'pesan_masuk'     => $this->pesananModel->where('status_pemesanan', 'Belum Bayar')->countAllResults(),
            'total_pesanan'   => $this->pesananModel->countAllResults(),
            'user'            => $user,
        ];

        return view('Admin/Dashboard', $data);
    }
}
