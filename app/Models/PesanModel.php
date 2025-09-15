<?php

namespace App\Models;

use CodeIgniter\Model;

class PesanModel extends Model
{
    protected $table      = 'pemesanan';
    protected $primaryKey = 'id';
    protected $allowedFields = [
        'nama',
        'produk',
        'quantity',
        'total',
        'pembayaran',
        'status'
    ];

    // Hitung jumlah pesanan belum dibaca
    public function getPesanMasuk()
    {
        return $this->where('status', 'belum_dibaca')->countAllResults();
    }

    // Ambil semua pesanan
    public function getAllPesanan()
    {
        return $this->findAll(); // tanpa orderBy tanggal
    }

    // Ambil detail pesanan by id
    public function getPesananById($id)
    {
        return $this->where('id', $id)->first();
    }

    // Ambil semua pesanan berdasarkan nama user
    public function getPesananByNama($nama)
    {
        return $this->where('nama', $nama)->findAll();
    }
}
