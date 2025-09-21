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
    public function getPesananBelumDinilai($idUser)
{
    return $this->select('pemesanan.*, produk.nama_produk')
                ->join('produk', 'produk.id_produk = pemesanan.id_produk')
                ->where('pemesanan.id_user', $idUser)
                ->where('pemesanan.status_pemesanan', 'Selesai')
                ->where("pemesanan.id_produk NOT IN (SELECT id_produk FROM penilaian WHERE id_user = $idUser)", null, false)
                ->findAll();
}

}
