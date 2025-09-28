<?php

namespace App\Controllers;

use CodeIgniter\Controller;
use Config\Database;

class MelihatLaporan extends Controller
{
    public function index()
    {
        $db = Database::connect();
        $idUser = session()->get('id_user');


        // Query join tabel pemesanan, users, produk, detail_pemesanan
        $builder = $db->table('pemesanan p')
            ->select('p.id_pemesanan, u.nama as nama_pembeli, pr.nama_produk, dp.jumlah_produk, dp.harga_produk, p.status_pemesanan')
            ->join('users u', 'p.id_user = u.id_user', 'left')
            ->join('detail_pemesanan dp', 'p.id_pemesanan = dp.id_pemesanan', 'left')
            ->join('produk pr', 'dp.id_produk = pr.id_produk', 'left')
            ->orderBy('p.id_pemesanan', 'DESC');

        $laporan = $builder->get()->getResultArray();
        $user = session()->get(); 


        return view('Admin/melihatlaporan', [
            'laporan' => $laporan,
            'user'    => $user,
        ]);
    }
}
