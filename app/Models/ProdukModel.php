<?php

namespace App\Models;

use CodeIgniter\Model;

class ProdukModel extends Model
{
    protected $table = 'produk';
    protected $primaryKey = 'id';
    protected $allowedFields = ['nama', 'stok', 'harga'];

    public function getTotalProduk()
    {
        return $this->countAllResults();
    }

    public function getStokRendah($limit = 5)
    {
        return $this->where('stok <', 10)->countAllResults();
    }
}
