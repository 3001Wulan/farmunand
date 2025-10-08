<?php

namespace App\Models;

use CodeIgniter\Model;

class ProdukModel extends Model
{
    protected $table = 'produk';
    protected $primaryKey = 'id_produk';

    // ✅ tambahkan 'kategori'
    protected $allowedFields = [
        'nama_produk',
        'deskripsi',
        'foto',
        'harga',
        'stok',
        'rating',
        'id_keranjang',
        'kategori',     // <— baru
    ];

    public function getTotalProduk()
    {
        return $this->countAllResults();
    }

    public function getStokRendah($limit = 10)
    {
        return $this->where('stok <', $limit)->countAllResults();
    }

    public function getProdukRekomendasi($limit = 5)
    {
        return $this->orderBy('id_produk', 'DESC')->limit($limit)->find();
    }

    public function getProdukById($id)
    {
        return $this->where('id_produk', $id)->first();
    }

    public function searchProduk($keyword)
    {
        return $this->table($this->table)
            ->like('nama_produk', $keyword)
            ->orLike('deskripsi', $keyword)
            ->findAll();
    }

    public function getKategoriList(): array
{
    // ambil kategori yang sudah ada di data (kalau ada)
    $fromData = $this->select('kategori')
        ->groupBy('kategori')
        ->findColumn('kategori') ?? [];

    // tiga opsi baku sesuai ENUM di DB
    $enum = ['Makanan','Minuman','Lainnya'];

    // gabungkan, hilangkan duplikat, pertahankan urutan enum
    return array_values(array_unique(array_merge($enum, $fromData)));
}

}
