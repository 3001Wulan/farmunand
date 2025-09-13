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
        $status = $this->request->getGet('status');
        $keyword = $this->request->getGet('keyword');
        $sort = $this->request->getGet('sort') ?? 'desc';

        $builder = $pesanModel;

        if ($status) {
            $builder = $builder->where('status', $status);
        }

        if ($keyword) {
            $builder = $builder->like('nama', $keyword)
                               ->orLike('produk', $keyword);
        }

        $data = [
            'pesanan' => $builder->orderBy('tanggal', strtoupper($sort))->findAll(),
            'status'  => $status,
            'keyword' => $keyword,
            'sort'    => $sort
        ];

        return view('Admin/MengelolaRiwayatPesanan', $data);
    }
}
