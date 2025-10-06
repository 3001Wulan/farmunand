<?php

namespace App\Controllers;

use App\Models\PesananModel;
use App\Models\UserModel;

class Pesanan extends BaseController
{
    protected $pesananModel;
    protected $userModel;

    public function __construct()
    {
        $this->pesananModel = new PesananModel();
        $this->userModel    = new UserModel();
    }

    // Ambil data user dari session
    private function getUserData()
    {
        $session = session();
        $userId  = $session->get('id_user'); // ✅

        if (!$userId) {
            return null;
        }
        return $this->userModel->find($userId);
    }

    // === Semua pesanan user ===
    public function index()
    {
        $user = $this->getUserData();
        if (!$user) return redirect()->to('/login');

        $data = [
            'user'   => $user,
            'orders' => $this->pesananModel->getPesananWithProduk($user['id_user'])
        ];
        return view('pembeli/riwayatpesanan', $data);
    }

    // === Pesanan Selesai ===
    public function selesai()
    {
        $user = $this->getUserData();
        if (!$user) return redirect()->to('/login');

        $data = [
            'user'   => $user,
            'orders' => $this->pesananModel->getPesananByStatus($user['id_user'], 'Selesai')
        ];
        return view('pembeli/pesananselesai', $data);
    }

    // === Pesanan Dikemas ===
    public function dikemas()
    {
        $user = $this->getUserData();
        if (!$user) return redirect()->to('/login');

        $data = [
            'user'   => $user,
            'orders' => $this->pesananModel->getPesananByStatus($user['id_user'], 'Dikemas')
        ];
        return view('pembeli/pesanandikemas', $data);
    }

    // === Pesanan Belum Bayar ===
    public function belumbayar()
    {
        $user = $this->getUserData();
        if (!$user) return redirect()->to('/login');

        $data = [
            'user'   => $user,
            'orders' => $this->pesananModel->getPesananByStatus($user['id_user'], 'Belum Bayar')
        ];
        return view('pembeli/pesananbelumbayar', $data);
    }

    // === Pesanan Dibatalkan (BARU) ===
    public function dibatalkan()
    {
        $user = $this->getUserData();
        if (!$user) return redirect()->to('/login');

        $data = [
            'user'   => $user,
            'orders' => $this->pesananModel->getPesananByStatus($user['id_user'], 'Dibatalkan')
        ];
        return view('pembeli/pesanandibatalkan', $data);
    }
}
