<?php

namespace App\Controllers;

use App\Models\ProdukModel;
use App\Models\UserModel;
use App\Models\PenilaianModel;

class DetailProduk extends BaseController
{
    protected $produkModel;
    protected $userModel;
    protected $penilaianModel;

    public function __construct(
        $produkModel = null,
        $userModel = null,
        $penilaianModel = null
    ) {
        $this->produkModel = $produkModel ?? new ProdukModel();
        $this->userModel = $userModel ?? new UserModel();
        $this->penilaianModel = $penilaianModel ?? new PenilaianModel();
    }

    public function index($id_produk = null)
    {
        $userId = session()->get('id_user');
        $user   = $this->userModel->find($userId);

        $produk = $id_produk
            ? $this->produkModel->find($id_produk)
            : $this->produkModel->first();

        if (!$produk) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound(
                "Produk dengan ID $id_produk tidak ditemukan."
            );
        }

        $reviews = $this->penilaianModel
            ->select('detail_pemesanan.*, users.nama as nama_user, users.username')
            ->join('pemesanan', 'pemesanan.id_pemesanan = detail_pemesanan.id_pemesanan')
            ->join('users', 'users.id_user = pemesanan.id_user')
            ->where('detail_pemesanan.id_produk', $produk['id_produk'])
            ->where('detail_pemesanan.user_rating >', 0)
            ->orderBy('detail_pemesanan.updated_at', 'DESC')
            ->findAll();

        return view('pembeli/detailproduk', [
            'title'   => 'Detail Produk',
            'produk'  => $produk,
            'user'    => $user,
            'reviews' => $reviews
        ]);
    }
}
