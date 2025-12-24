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

    private function getUserData()
    {
        $session = session();
        $userId  = $session->get('id_user'); 

        if (!$userId) {
            return null;
        }
        return $this->userModel->find($userId);
    }

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

    public function konfirmasipesanan() 
    {
        $user = $this->getUserData();
        if (!$user) return redirect()->to('/login');

        $this->pesananModel->where('status_pemesanan', 'Dikirim')
            ->where('id_user', $user['id_user'])
            ->where('konfirmasi_expires_at IS NOT NULL', null, false)
            ->where('confirmed_at IS NULL', null, false)
            ->where('konfirmasi_expires_at <', date('Y-m-d H:i:s'))
            ->set([
                'status_pemesanan' => 'Selesai',
                'updated_at'       => date('Y-m-d H:i:s')
            ])->update();

        $data = [
            'user'    => $user,
            'pesanan' => $this->pesananModel->getPesananByStatus($user['id_user'], 'Dikirim')
        ];
        return view('pembeli/konfirmasipesanan', $data);
    }

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

        $row = $this->pesananModel
            ->where('id_pemesanan', (int)$id)
            ->where('id_user', $user['id_user'])
            ->first();

        if (!$row) {
            return redirect()->back()->with('error', 'Pesanan tidak ditemukan.');
        }

        if (($row['status_pemesanan'] ?? '') !== 'Dikirim') {
            return redirect()->back()->with('error', 'Pesanan ini tidak dalam status Dikirim.');
        }

        $exp = $row['konfirmasi_expires_at'] ?? null;
        if (!$exp || strtotime($exp) < time()) {
            $this->pesananModel->update((int)$id, [
                'status_pemesanan' => 'Selesai',
                'confirmed_at'     => date('Y-m-d H:i:s'),
                'konfirmasi_token' => null
            ]);
            return redirect()->to('/pesananselesai')->with('success', 'Melebihi 7 hari—pesanan ditandai Selesai otomatis.');
        }

        $this->pesananModel->update((int)$id, [
            'status_pemesanan' => 'Selesai',
            'confirmed_at'     => date('Y-m-d H:i:s'),
            'konfirmasi_token' => null
        ]);

        return redirect()->to('/pesananselesai')->with('success', 'Terima kasih! Pesanan ditandai Selesai.');
    }

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
