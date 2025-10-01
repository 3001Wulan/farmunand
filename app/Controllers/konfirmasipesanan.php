<?php

namespace App\Controllers;

use App\Models\PesanModel; 
use App\Models\UserModel;

class KonfirmasiPesanan extends BaseController
{
    protected $pesanModel;
    protected $userModel;

    public function __construct()
    {
        $this->pesanModel = new PesanModel();
        $this->userModel  = new UserModel();
    }

    public function index()
{
    $idUser = session()->get('id_user');
    if (!$idUser) {
        return redirect()->to('/login')->with('error', 'Silakan login terlebih dahulu.');
    }

    // ✅ Ambil langsung hanya yang status = dikirim
    $pesananUser = $this->pesanModel->getPesananWithProduk($idUser, 'dikirim');

    $data = [
        'pesanan' => $pesananUser,
        'user'    => $this->userModel->find($idUser),
    ];

    return view('pembeli/konfirmasipesanan', $data);
}

    
    public function selesai($id_pemesanan)
    {
        $this->pesanModel->update($id_pemesanan, [
            'status_pemesanan' => 'selesai' // konsisten lowercase
        ]);

        return redirect()->to('/konfirmasipesanan')
                         ->with('success', 'Pesanan berhasil dikonfirmasi!');
    }
}
