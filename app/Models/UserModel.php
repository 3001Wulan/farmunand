<?php

namespace App\Models;

use CodeIgniter\Model;

class UserModel extends Model
{
    protected $table            = 'users';   // nama tabel
    protected $primaryKey       = 'id_user'; // ✅ sudah ganti ke id_user

    protected $useAutoIncrement = true;

    // kolom yang boleh diisi
    protected $allowedFields    = [
        'username','nama','email','password','status','no_hp','role',
        'reset_token','reset_expires','foto'
    ];

    // timestamps otomatis
    protected $useTimestamps = true;
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';

    protected $returnType     = 'array';

    public function getTotalUser()
    {
        return $this->countAllResults();
    }
}
