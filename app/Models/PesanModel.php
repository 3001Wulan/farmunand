<?php

namespace App\Models;

use CodeIgniter\Model;

class PesanModel extends Model
{
    protected $table      = 'pemesanan';
    protected $primaryKey = 'id_pemesanan';
    protected $allowedFields = [
        'id_user','id_produk','jumlah_produk','total_harga','pembayaran','status_pemesanan','tanggal'
    ];

    /**
     * Hitung pesan masuk dengan status 'belum_dibaca'
     */
    public function getPesanMasuk()
    {
        return $this->where('status_pemesanan', 'belum_dibaca')
                    ->countAllResults();
    }

    /**
     * Ambil semua pesanan
     */
    public function getAllPesanan()
    {
        return $this->findAll();
    }

    /**
     * Ambil pesanan berdasarkan ID
     */
    public function getPesananById($id_pemesanan)
    {
        return $this->where('id_pemesanan', $id_pemesanan)
                    ->first();
    }

    /**
     * Ambil pesanan berdasarkan nama user
     */
    public function getPesananByNama($nama)
    {
        return $this->join('users', 'users.id_user = pemesanan.id_user')
                    ->where('users.nama', $nama)
                    ->findAll();
    }

    /**
     * Ambil total penjualan bulan ini
     */
    public function getPenjualanBulan()
    {
        return $this->selectSum('total_harga')
                    ->where('status_pemesanan', 'selesai')
                    ->where('MONTH(tanggal)', date('m'))
                    ->where('YEAR(tanggal)', date('Y'))
                    ->first()['total_harga'] ?? 0;
    }

    /**
     * Ambil pesanan lengkap dengan detail produk
     */
    public function getPesananWithProduk($idUser, $status = null)
{
    $builder = $this->db->table('pemesanan')
        ->select('
            pemesanan.id_pemesanan, 
            pemesanan.status_pemesanan, 
            pemesanan.total_harga, 
            pemesanan.created_at,
            users.nama as nama_user, 
            produk.nama_produk, 
            produk.harga, 
            produk.foto,
            detail_pemesanan.jumlah_produk, 
            detail_pemesanan.harga_produk
        ')
        ->join('users', 'users.id_user = pemesanan.id_user')
        ->join('detail_pemesanan', 'detail_pemesanan.id_pemesanan = pemesanan.id_pemesanan')
        ->join('produk', 'produk.id_produk = detail_pemesanan.id_produk')
        ->where('pemesanan.id_user', $idUser);

    if ($status) {
        $builder->where('pemesanan.status_pemesanan', $status);
    }

    return $builder->orderBy('pemesanan.created_at', 'DESC')
                   ->get()
                   ->getResultArray();
}

}
