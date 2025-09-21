<?php

namespace App\Models;

use CodeIgniter\Model;

class AlamatModel extends Model
{
    protected $table = 'alamat';
    protected $primaryKey = 'id_alamat';
    protected $allowedFields = [
        'id_user',
        'nama_penerima',
        'kota',
        'provinsi',
        'kode_pos',
        'no_telepon'
    ];

    // Jika mau otomatis timestamp created_at / updated_at
    protected $useTimestamps = true;
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';
}
