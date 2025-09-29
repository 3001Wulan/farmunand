<?php

namespace App\Controllers;

use App\Models\PesananModel;
use App\Models\UserModel;

class KonfirmasiPesanan extends BaseController
{
    protected $pesananModel;
    protected $userModel;

    public function __construct()
    {
        $this->pesananModel = new PesananModel();
        $this->userModel    = new UserModel();
    }

    public function index()
    {
        $idUser = session()->get('id_user');
        if (!$idUser) {
            return redirect()->to('/login')->with('error', 'Silakan login terlebih dahulu.');
        }

        $pesanan = $this->pesananModel->getPesananWithProduk($idUser);

        $data = [
            'pesanan' => array_filter($pesanan, fn($p) => strtolower($p['status_pemesanan']) === 'dikirim'),
            'user'    => $this->userModel->find($idUser),
        ];

        return view('pembeli/konfirmasipesanan', $data);
    }
    
    public function selesai($id_pemesanan)
    {
        $this->pesananModel->update($id_pemesanan, [
            'status_pemesanan' => 'Selesai'
        ]);

        return redirect()->to('/konfirmasipesanan')
                         ->with('success', 'Pesanan berhasil dikonfirmasi!');
    }
}
