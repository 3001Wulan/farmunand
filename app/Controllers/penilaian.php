<?php

namespace App\Controllers;

use App\Models\ProdukModel;
use App\Models\PenilaianModel;
use App\Models\PesananModel;

class Penilaian extends BaseController
{
    protected $produkModel;
    protected $penilaianModel;
    protected $pesananModel;

    public function __construct()
    {
        $this->produkModel     = new ProdukModel();
        $this->penilaianModel  = new PenilaianModel();
        $this->pesananModel    = new PesananModel();
    }

  
    
    public function daftar()
    {
        $idUser = session()->get('id_user'); // Ambil dari session login

        if (!$idUser) {
            return redirect()->to('/login')->with('error', 'Silakan login terlebih dahulu.');
        }

        // Ambil pesanan yang belum dinilai
        $pesanan = $this->pesananModel->getPesananBelumDinilai($idUser);

        return view('Pembeli/penilaianproduk', [
            'pesanan' => $pesanan
        ]);
    }
    public function index($id_produk)
    {
        $produk = $this->produkModel->find($id_produk);

        if (!$produk) {
            return redirect()->to('/riwayatpesanan')->with('error', 'Produk tidak ditemukan');
        }

        return view('Pembeli/penilaianproduk', [
            'produk' => $produk
        ]);
    }

    /**
     * Simpan penilaian
     */
    public function simpan($id_produk)
    {
        $validation = \Config\Services::validation();

        $rules = [
            'rating' => 'required|in_list[1,2,3,4,5]',
            'ulasan' => 'required|min_length[50]',
            'media'  => 'if_exist|max_size[media,2048]|ext_in[media,jpg,jpeg,png,mp4,mov,avi]',
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $validation->getErrors());
        }

        $fileMedia = $this->request->getFile('media');
        $mediaName = null;

        if ($fileMedia && $fileMedia->isValid() && !$fileMedia->hasMoved()) {
            $mediaName = $fileMedia->getRandomName();
            $fileMedia->move('uploads/penilaian', $mediaName);
        }

        $this->penilaianModel->save([
            'id_produk' => $id_produk,
            'id_user'   => session()->get('id_user'), // pastikan session login user ada
            'rating'    => $this->request->getPost('rating'),
            'ulasan'    => $this->request->getPost('ulasan'),
            'media'     => $mediaName,
        ]);

        return redirect()->to('riwayatpesanan')->with('success', 'Penilaian berhasil dikirim');
    }
}
