<?php

namespace App\Models;

use CodeIgniter\Model;

class AlamatModel extends Model
{
    protected $table = 'alamat';
    protected $primaryKey = 'id_alamat';
    protected $allowedFields = [
        'id_user','nama_penerima','jalan','kota','provinsi',
        'kode_pos','aktif','no_telepon'
    ];

    protected $useTimestamps = true;
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';
    
    public function getAlamatAktifByUser($id_user)
    {
        return $this->where('id_user',$id_user)
                    ->where('aktif',1)
                    ->findAll();
    }
}
