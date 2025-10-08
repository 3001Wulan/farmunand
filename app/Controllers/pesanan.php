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

    public function konfirmasipesanan() // GET /konfirmasipesanan
    {
        $user = $this->getUserData();
        if (!$user) return redirect()->to('/login');

        // Auto-close yang kadaluarsa > 7 hari untuk user ini
        $this->pesananModel->where('status_pemesanan', 'Dikirim')
            ->where('id_user', $user['id_user'])
            ->where('konfirmasi_expires_at IS NOT NULL', null, false)
            ->where('confirmed_at IS NULL', null, false)
            ->where('konfirmasi_expires_at <', date('Y-m-d H:i:s'))
            ->set([
                'status_pemesanan' => 'Selesai',
                'updated_at'       => date('Y-m-d H:i:s')
            ])->update();

        // Ambil ulang daftar "Dikirim" (yang masih dalam masa 7 hari)
        $data = [
            'user'    => $user,
            'pesanan' => $this->pesananModel->getPesananByStatus($user['id_user'], 'Dikirim')
        ];
        return view('pembeli/konfirmasipesanan', $data);
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

    public function konfirmasiSelesai($id)
    {
        $user = $this->getUserData();
        if (!$user) return redirect()->to('/login');

        // Ambil pesanan milik user
        $row = $this->pesananModel
            ->where('id_pemesanan', (int)$id)
            ->where('id_user', $user['id_user'])
            ->first();

        if (!$row) {
            return redirect()->back()->with('error', 'Pesanan tidak ditemukan.');
        }

        // Hanya boleh jika status Dikirim
        if (($row['status_pemesanan'] ?? '') !== 'Dikirim') {
            return redirect()->back()->with('error', 'Pesanan ini tidak dalam status Dikirim.');
        }

        // Cek masa berlaku 7 hari
        $exp = $row['konfirmasi_expires_at'] ?? null;
        if (!$exp || strtotime($exp) < time()) {
            // Sudah kadaluarsa: auto-close
            $this->pesananModel->update((int)$id, [
                'status_pemesanan' => 'Selesai',
                'confirmed_at'     => date('Y-m-d H:i:s'),
                'konfirmasi_token' => null
            ]);
            return redirect()->to('/pesananselesai')->with('success', 'Melebihi 7 hari—pesanan ditandai Selesai otomatis.');
        }

        // Masih dalam masa berlaku → set Selesai
        $this->pesananModel->update((int)$id, [
            'status_pemesanan' => 'Selesai',
            'confirmed_at'     => date('Y-m-d H:i:s'),
            'konfirmasi_token' => null
        ]);

        return redirect()->to('/pesananselesai')->with('success', 'Terima kasih! Pesanan ditandai Selesai.');
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
