<?php

namespace App\Controllers;

use App\Models\ProdukModel;
use App\Models\UserModel;
use App\Models\PesananModel;

class Dashboard extends BaseController
{
    public function index()
    {
        $produkModel  = new ProdukModel();
        $userModel    = new UserModel();
        $pesananModel = new PesananModel();

        $userId = session()->get('id_user');
        if (!$userId) {
            return redirect()->to('/login')->with('error', 'Silakan login dulu.');
        }

        $user = $userModel->find($userId);

        // ========= METRIK =========
        // total produk
        $totalProduk = method_exists($produkModel, 'getTotalProduk')
            ? $produkModel->getTotalProduk()
            : (new ProdukModel())->countAllResults();

        // total user
        $totalUser = method_exists($userModel, 'getTotalUser')
            ? $userModel->getTotalUser()
            : (new UserModel())->countAllResults();

        // transaksi hari ini (pakai helper di PesananModel)
        $transaksiHari = $pesananModel->getTransaksiHariIni();

        // penjualan bulan ini (pakai helper di PesananModel)
        $penjualanBulan = $pesananModel->getPenjualanBulan();

        // stok rendah (jika ada helper; fallback 0)
        $stokRendah = method_exists($produkModel, 'getStokRendah')
            ? $produkModel->getStokRendah()
            : 0;

        // “pesan_masuk” sebelumnya berasal dari PesanModel::getPesanMasuk (status ‘belum_dibaca’).
        // Kita ganti definisinya menjadi jumlah pesanan dengan status “Belum Bayar”.
        $pesanMasuk = (new PesananModel())
            ->where('status_pemesanan', 'Belum Bayar')
            ->countAllResults();

        // total pesanan
        $totalPesanan = (new PesananModel())->countAllResults();

        $data = [
            'title'           => 'Dashboard',
            'total_produk'    => $totalProduk,
            'total_user'      => $totalUser,
            'transaksi_hari'  => $transaksiHari,
            'penjualan_bulan' => $penjualanBulan,
            'stok_rendah'     => $stokRendah,
            'pesan_masuk'     => $pesanMasuk,
            'total_pesanan'   => $totalPesanan,
            'user'            => $user,
        ];

        return view('Admin/Dashboard', $data);
    }
}
