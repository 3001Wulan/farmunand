<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Models\UserModel;
use Config\Database;

class MelihatLaporan extends BaseController
{
    public function index()
    {
        $db = Database::connect();

        $builder = $db->table('pemesanan p')
            ->select('
                p.id_pemesanan,
                u.nama AS nama_pembeli,
                pr.nama_produk,
                dp.jumlah_produk,
                dp.harga_produk,
                p.status_pemesanan,
                p.created_at
            ')
            ->join('users u', 'p.id_user = u.id_user', 'left')
            ->join('detail_pemesanan dp', 'p.id_pemesanan = dp.id_pemesanan', 'left')
            ->join('produk pr', 'dp.id_produk = pr.id_produk', 'left')
            ->orderBy('p.created_at', 'DESC');

        $laporan = $builder->get()->getResultArray();

        // Data user admin untuk sidebar
        $userId = session()->get('id_user');
        $user   = (new UserModel())->find($userId);

        return view('Admin/melihatlaporan', [
            'laporan' => $laporan,
            'user'    => $user
        ]);
    }
}
