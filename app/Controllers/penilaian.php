<?php

namespace App\Controllers;

use App\Models\ProdukModel;
use App\Models\PenilaianModel;
use App\Models\PesananModel;
use App\Models\UserModel;

class Penilaian extends BaseController
{
    protected $produkModel;
    protected $penilaianModel;
    protected $pesananModel;
    protected $userModel;

    public function __construct()
    {
        $this->produkModel    = new ProdukModel();
        $this->penilaianModel = new PenilaianModel();
        $this->pesananModel   = new PesananModel();
        $this->userModel      = new UserModel();
    }
    
    // Daftar produk yang bisa dinilai
    public function daftar()
    {
        $idUser = session()->get('id_user');
        if (!$idUser) {
            return redirect()->to('/login')->with('error', 'Silakan login terlebih dahulu.');
        }

        $user    = $this->userModel->find($idUser);
        $pesanan = $this->pesananModel->getPesananBelumDinilai($idUser);

        return view('pembeli/penilaianproduk', [
            'pesanan' => $pesanan,
            'user'    => $user
        ]);
    }

    // Form penilaian untuk produk tertentu
    public function index($id_produk)
    {
        $produk = $this->produkModel->find($id_produk);
        if (!$produk) {
            return redirect()->to('/riwayatpesanan')->with('error', 'Produk tidak ditemukan');
        }

        $idUser = session()->get('id_user');
        $user   = $this->userModel->find($idUser);

        return view('pembeli/penilaianproduk', [
            'produk' => $produk,
            'user'   => $user
        ]);
    }

    // Simpan penilaian
    public function simpan($id_produk)
    {
        $validation = \Config\Services::validation();
        $rules = [
            'rating'   => 'required|in_list[1,2,3,4,5]',
            'media.*'  => 'max_size[media,2048]|ext_in[media,jpg,jpeg,png,mp4,mov,avi]'
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $validation->getErrors());
        }

        $uploadedMedia = $this->request->getFiles();
        $mediaNames = [];

        if (!empty($uploadedMedia['media'])) {
            foreach ($uploadedMedia['media'] as $file) {
                if ($file->isValid() && !$file->hasMoved()) {
                    $fileName = $file->getRandomName();
                    $file->move('uploads/penilaian', $fileName);
                    $mediaNames[] = $fileName;
                }
            }
        }

        $this->penilaianModel->save([
            'id_produk' => $id_produk,
            'id_user'   => session()->get('id_user'),
            'rating'    => $this->request->getPost('rating'),
            'ulasan'    => $this->request->getPost('ulasan') ?? null,
            'media'     => !empty($mediaNames) ? json_encode($mediaNames) : null,
        ]);

        return redirect()->to('/riwayatpesanan')->with('success', 'Penilaian berhasil dikirim');
    }
}
