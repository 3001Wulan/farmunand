<?php

namespace App\Controllers;

use App\Models\ProdukModel;
use App\Models\AlamatModel;
use App\Models\UserModel;

class MelakukanPemesanan extends BaseController
{
    protected $produkModel;
    protected $alamatModel;
    protected $userModel;

    public function __construct()
    {
        $this->produkModel = new ProdukModel();
        $this->alamatModel = new AlamatModel();
        $this->userModel   = new UserModel();
    }

    // Terima POST (direkomendasikan) dan tetap dukung GET (legacy)
    public function index($idProdukFromSegment = null)
    {
        $idUser = session()->get('id_user');
        if (!$idUser) {
            return redirect()->to('/login')->with('error', 'Silakan login terlebih dahulu.');
        }

        // Ambil dari POST > GET > segment (legacy /melakukanpemesanan/{id})
        $idProduk = $this->request->getPost('id_produk')
                 ?? $this->request->getGet('id_produk')
                 ?? $idProdukFromSegment;

        $qty = (int)($this->request->getPost('qty') ?? $this->request->getGet('qty') ?? 1);
        if ($qty < 1) $qty = 1;

        $checkout = null;
        if ($idProduk) {
            $produk = $this->produkModel->find($idProduk);

            if (!$produk) {
                return redirect()->back()->with('error', 'Produk tidak ditemukan.');
            }

            $stok = (int)($produk['stok'] ?? 0);
            if ($stok <= 0) {
                return redirect()->back()->with('error', 'Stok produk habis.');
            }
            if ($qty > $stok) { // clamp ke stok
                $qty = $stok;
                session()->setFlashdata('info', 'Jumlah melebihi stok, disesuaikan ke stok tersedia.');
            }

            $checkout = [
                'id_produk'   => $produk['id_produk'],
                'nama_produk' => $produk['nama_produk'],
                'deskripsi'   => $produk['deskripsi'] ?? '',
                'foto'        => $produk['foto'] ?? 'default.png',
                'harga'       => (float)$produk['harga'],
                'qty'         => $qty,
                'subtotal'    => (float)$produk['harga'] * $qty,
            ];
        }

        // Ambil alamat user (aktif di atas)
        $alamat = $this->alamatModel
                        ->where('id_user', $idUser)
                        ->orderBy('aktif', 'DESC')
                        ->orderBy('id_alamat', 'DESC')
                        ->findAll();

        return view('pembeli/melakukanpemesanan', [
            'checkout' => $checkout,                   // ← gunakan ini di view
            'alamat'   => $alamat,
            'user'     => $this->userModel->find($idUser),
        ]);
    }
}
