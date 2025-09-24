<?php

namespace App\Models;

use CodeIgniter\Model;

class ProdukModel extends Model
{
    protected $table = 'produk';
    protected $primaryKey = 'id_produk';
    protected $allowedFields = ['nama_produk', 'deskripsi', 'foto', 'harga', 'stok', 'rating', 'id_keranjang'];

    // Hitung total produk
    public function getTotalProduk()
    {
        return $this->countAllResults();
    }

    // Hitung produk dengan stok rendah (default < 10)
    public function getStokRendah($limit = 10)
    {
        return $this->where('stok <', $limit)->countAllResults();
    }

    // Ambil produk terbaru (untuk rekomendasi dashboard)
    public function getProdukRekomendasi($limit = 5)
    {
        return $this->orderBy('id_produk', 'DESC')
                    ->limit($limit)
                    ->find();
    }

    // Ambil detail produk berdasarkan ID
    public function getProdukById($id)
    {
        return $this->where('id_produk', $id)->first();
    }

    // Cari produk berdasarkan keyword (nama atau deskripsi)
    public function searchProduk($keyword)
    {
        return $this->table($this->table)
            ->like('nama_produk', $keyword)
            ->orLike('deskripsi', $keyword)
            ->findAll();
    }

}
