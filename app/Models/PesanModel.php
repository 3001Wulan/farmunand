<?php

namespace App\Models;

use CodeIgniter\Model;

class PesanModel extends Model
{
    protected $table = 'pemesanan';
    protected $primaryKey = 'id';

    public function getPesanMasuk()
    {
        return $this->where('status', 'belum_dibaca')->countAllResults();
    }
}
