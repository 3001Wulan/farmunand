<?php

namespace App\Models;

use CodeIgniter\Model;

class PesanModel extends Model
{
    protected $table      = 'pemesanan';
    protected $primaryKey = 'id_pemesanan';
    protected $allowedFields = [
        'nama',
        'produk',
        'quantity',
        'total',
        'pembayaran',
        'status_pemesanan'
    ];

    // Hitung jumlah pesanan belum dibaca
    public function getPesanMasuk()
    {
        return $this->where('status_pemesanan', 'belum_dibaca')->countAllResults();
    }

    // Ambil semua pesanan
    public function getAllPesanan()
    {
        return $this->findAll(); // tanpa orderBy tanggal
    }

    // Ambil detail pesanan by id
    public function getPesananById($id)
    {
        return $this->where('id_pemesanan', $id)->first();
    }

    // Ambil semua pesanan berdasarkan nama user
    public function getPesananByNama($nama)
    {
        return $this->where('nama', $nama)->findAll();
    }
    public function getPenjualanBulan()
{
    return $this->selectSum('total')
        ->where('status_pemesanan', 'selesai')
        ->where('MONTH(tanggal)', date('m'))
        ->where('YEAR(tanggal)', date('Y'))
        ->first()['total'] ?? 0;
}

}
