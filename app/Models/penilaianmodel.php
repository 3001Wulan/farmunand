<?php

namespace App\Models;

use CodeIgniter\Model;

class PenilaianModel extends Model
{
    protected $table      = 'penilaian';
    protected $primaryKey = 'id';
    protected $allowedFields = [
        'id_produk','id_user','rating','ulasan','media','created_at'
    ];
    protected $useTimestamps = true;
}
