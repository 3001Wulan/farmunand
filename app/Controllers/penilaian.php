<?php

namespace App\Controllers;

use App\Models\ProdukModel;
use App\Models\PenilaianModel; // tetap nama model PenilaianModel
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
        $this->penilaianModel = new PenilaianModel(); // diarahkan ke tabel detail_pemesanan
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

   // Simpan penilaian sesuai id_pemesanan
   public function simpan($id_pemesanan)
   {
       $validation = \Config\Services::validation();
       $rules = [
           'rating'   => 'required|in_list[1,2,3,4,5]',
           'media.*'  => 'max_size[media,2048]|ext_in[media,jpg,jpeg,png,mp4,mov,avi]'
       ];

       if (!$this->validate($rules)) {
           return redirect()->back()->withInput()->with('errors', $validation->getErrors());
       }

       // Upload media jika ada
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

       // Ambil detail pemesanan sesuai id_pemesanan
       $detail = $this->penilaianModel->find($id_pemesanan);
       if (!$detail) {
           return redirect()->back()->with('error', 'Detail pemesanan tidak ditemukan.');
       }

       // Update penilaian
       $this->penilaianModel->update($id_pemesanan, [
           'user_rating' => $this->request->getPost('rating'),
           'user_ulasan' => $this->request->getPost('ulasan') ?? null,
           'user_media'  => !empty($mediaNames) ? json_encode($mediaNames) : null,
           'updated_at'  => date('Y-m-d H:i:s'),
       ]);

       return redirect()->to('/penilaian/daftar')->with('success', 'Penilaian berhasil dikirim');
   }
}
