<?php

namespace App\Models;

use CodeIgniter\Model;

class PenilaianModel extends Model
{
    protected $table      = 'detail_pemesanan';
    protected $primaryKey = 'id_pemesanan';
    protected $allowedFields = [
        'id_pemesanan',
        'id_produk',
        'id_user',
        'user_rating',
        'user_ulasan',
        'user_media',
        'updated_at'
    ];
    protected $useTimestamps = false; // karena kita atur manual
}
