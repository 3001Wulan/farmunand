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
        'jalan',
        'kota',
        'provinsi',
        'kode_pos',
        'aktif',
        'no_telepon'
    ];

    // Jika mau otomatis timestamp created_at / updated_at
    protected $useTimestamps = true;
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';
    
    public function getAlamatAktifByUser($idUser)
    {
        return $this->where('id_user', $idUser)
                    ->where('aktif', 1)
                    ->findAll();
    }
}
