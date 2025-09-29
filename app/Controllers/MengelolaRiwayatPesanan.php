<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Models\PesananModel;
use App\Models\UserModel;

class MengelolaRiwayatPesanan extends BaseController
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
        $status  = $this->request->getGet('status_pemesanan');
        $keyword = $this->request->getGet('keyword');
        $sort    = $this->request->getGet('sort') ?? 'DESC';

        $builder = $this->pesananModel
            ->select('
                pemesanan.id_pemesanan,
                pemesanan.status_pemesanan,
                pemesanan.created_at,
                users.nama AS nama_user,
                users.foto AS foto_user,
                produk.nama_produk,
                produk.harga,
                detail_pemesanan.jumlah_produk
            ')
            ->join('users', 'users.id_user = pemesanan.id_user')
            ->join('detail_pemesanan', 'detail_pemesanan.id_pemesanan = pemesanan.id_pemesanan')
            ->join('produk', 'produk.id_produk = detail_pemesanan.id_produk');

        if ($status) {
            $builder->where('pemesanan.status_pemesanan', $status);
        }

        if ($keyword) {
            $builder->groupStart()
                    ->like('users.nama', $keyword)
                    ->orLike('produk.nama_produk', $keyword)
                    ->groupEnd();
        }

        $pesanan = $builder
            ->orderBy('pemesanan.created_at', $sort)
            ->get()
            ->getResultArray();

        // Data user untuk sidebar
        $userId = session()->get('id_user');
        $user   = $this->userModel->find($userId);

        return view('Admin/MengelolaRiwayatPesanan', [
            'pesanan' => $pesanan,
            'status_pemesanan' => $status,
            'keyword' => $keyword,
            'sort'    => $sort,
            'user'    => $user
        ]);
    }
}
