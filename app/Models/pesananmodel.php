<?php

namespace App\Models;

use CodeIgniter\Model;

class PesananModel extends Model
{
    protected $table = 'pemesanan';
    protected $primaryKey = 'id_pemesanan';
    protected $allowedFields = ['id_alamat', 'id_pembayaran', 'id_user', 'status_pemesanan'];

    public function getPesananWithProduk($id_user)
    {
        return $this->select('
                        pemesanan.id_pemesanan, 
                        produk.nama_produk, 
                        produk.harga, 
                        produk.foto, 
                        detail_pemesanan.jumlah_produk, 
                        pemesanan.status_pemesanan
                    ')
                    ->join('detail_pemesanan', 'detail_pemesanan.id_pemesanan = pemesanan.id_pemesanan')
                    ->join('produk', 'produk.id_produk = detail_pemesanan.id_produk')
                    ->where('pemesanan.id_user', $id_user)
                    ->findAll();
    }
}
