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

        // Ambil hanya pesanan berstatus Dikirim (sudah otomatis orderBy created_at DESC dari model)
        $pesananUser = $this->pesananModel->getPesananByStatus((int)$idUser, 'Dikirim');

        $data = [
            'pesanan' => $pesananUser,
            'user'    => $this->userModel->find($idUser),
        ];

        return view('pembeli/konfirmasipesanan', $data);
    }

    public function selesai($id_pemesanan)
{
    $idUser = session()->get('id_user');
    if (!$idUser) {
        return redirect()->to('/login')->with('error', 'Silakan login terlebih dahulu.');
    }

    // Gunakan helper method di model
    $row = $this->pesananModel->getPesananByIdAndUser((int)$id_pemesanan, (int)$idUser);

    if (!$row) {
        return redirect()->back()->with('error', 'Pesanan tidak ditemukan.');
    }

    if (($row['status_pemesanan'] ?? '') !== 'Dikirim') {
        return redirect()->back()->with('error', 'Pesanan ini tidak dalam status Dikirim.');
    }

    $ok = $this->pesananModel->update((int)$id_pemesanan, [
        'status_pemesanan' => 'Selesai',
        'confirmed_at'     => date('Y-m-d H:i:s'),
        'konfirmasi_token' => null
    ]);

    return redirect()->to('/pesananselesai')
                     ->with($ok ? 'success' : 'error', $ok ? 'Pesanan berhasil dikonfirmasi!' : 'Gagal mengonfirmasi pesanan.');
}

}
