<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Models\PesanModel;

class MengelolaRiwayatPesanan extends BaseController
{
    public function index()
    {
        $pesanModel = new PesanModel();

        // Ambil filter dari GET
        $status  = $this->request->getGet('status_pemesanan');
        $idUser = session()->get('id_user');
        $keyword = $this->request->getGet('keyword');
        $sort    = $this->request->getGet('sort') ?? 'desc';

        // Build query dengan join
        $builder = $pesanModel->select('pemesanan.*, users.nama AS nama_user, produk.nama_produk')
                              ->join('users', 'users.id_user = pemesanan.id_user')
                              ->join('produk', 'produk.id_produk = pemesanan.id_produk');

        if ($status) {
            $builder->where('pemesanan.status', $status);
        }

        if ($keyword) {
            $builder->groupStart()
                    ->like('users.nama', $keyword)
                    ->orLike('produk.nama_produk', $keyword)
                    ->groupEnd();
        }

        $pesanan = $builder->orderBy('pemesanan.tanggal', strtoupper($sort))
                           ->get()
                           ->getResultArray();
        $user = session()->get(); 

        $data = [
            'pesanan' => $pesanan,
            'status_pemesanan'  => $status,
            'keyword' => $keyword,
            'user'    => $user,  
            'sort'    => $sort
        ];

        return view('Admin/MengelolaRiwayatPesanan', $data);
    }
}
