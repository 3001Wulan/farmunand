<?php

namespace App\Models;

use CodeIgniter\Model;

class PesanModel extends Model
{
    protected $table      = 'pemesanan';
    protected $primaryKey = 'id';
    protected $allowedFields = [
        'tanggal',
        'nama',
        'produk',
        'quantity',
        'total',
        'pembayaran',
        'status'
    ];

    // Sudah ada
    public function getPesanMasuk()
    {
        return $this->where('status', 'belum_dibaca')->countAllResults();
    }

    // Tambahan -> untuk riwayat pesanan
    public function getAllPesanan()
    {
        return $this->orderBy('tanggal', 'DESC')->findAll();
    }
}
