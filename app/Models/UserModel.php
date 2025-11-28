<?php

namespace App\Models;

use CodeIgniter\Model;

class UserModel extends Model
{
    /**
     * Konfigurasi dasar model
     */
    protected $table            = 'users';
    protected $primaryKey       = 'id_user';

    protected $useAutoIncrement = true;
    protected $returnType       = 'array';

    // Kolom yang boleh diisi (mass assignment)
    protected $allowedFields = [
        'username',
        'email',
        'password',          // berisi password yang sudah di-hash
        'role',
        'foto',
        'reset_token',
        'reset_expires',
        'failed_logins',
        'last_failed_login',
        'locked_until',
    ];

    // Timestamps otomatis
    protected $useTimestamps = true;
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';

    /**
     * Hitung total user.
     * Dipakai misalnya di Dashboard admin.
     */
    public function getTotalUser()
    {
        // Ini tetap sama dengan versi sebelumnya
        return $this->countAllResults();
    }

    /**
     * Helper kecil untuk membuat instance PesananModel.
     *
     * Kenapa dipisah?
     * - Di aplikasi normal → method ini mengembalikan PesananModel asli.
     * - Di unit test → method ini bisa dioverride di subclass (TestableUserModel)
     *   untuk mengembalikan fake / stub tanpa koneksi DB.
     *
     * Perubahan ini *tidak mengubah* perilaku sistem, karena sebelumnya
     * kita juga pakai `new \App\Models\PesananModel()` langsung.
     */
    protected function createPesananModel()
    {
        return new \App\Models\PesananModel();
    }

    /**
     * Cek apakah user punya pesanan yang statusnya belum "Selesai".
     *
     * Return:
     *   - true  → kalau ada minimal 1 pesanan dengan status != 'Selesai'
     *   - false → kalau semua pesanan user sudah selesai / tidak ada pesanan
     */
    public function hasPendingOrders(int $id_user): bool
    {
        // Pakai helper yang bisa dioverride di unit test
        $pemesananModel = $this->createPesananModel();

        // Hitung jumlah pesanan user yang belum selesai
        $jumlah = $pemesananModel
            ->where('id_user', $id_user)
            ->where('status_pemesanan !=', 'Selesai')
            ->countAllResults();

        return $jumlah > 0;
    }
}
