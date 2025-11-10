<?php

namespace App\Models;

use CodeIgniter\Model;

class UserModel extends Model
{
    protected $table            = 'users';   // nama tabel
    protected $primaryKey       = 'id_user'; // ✅ sudah ganti ke id_user

    protected $useAutoIncrement = true;

    // kolom yang boleh diisi
   protected $allowedFields = [
        'username',
        'email',
        'password',
        'role',
        'foto',
        'reset_token',
        'reset_expires',
        'failed_logins',
        'last_failed_login',
        'locked_until',
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

    public function hasPendingOrders(int $id_user): bool
{
    $pemesananModel = new \App\Models\PesananModel();

    // Hitung jumlah pesanan user yang belum selesai
    $jumlah = $pemesananModel
        ->where('id_user', $id_user)
        ->where('status_pemesanan !=', 'Selesai')
        ->countAllResults();

    return $jumlah > 0;
}

}
