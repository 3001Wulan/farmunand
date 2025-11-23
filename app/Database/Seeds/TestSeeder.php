<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class TestSeeder extends Seeder
{
    public function run()
    {
        // -----------------------------
        // 1. Matikan foreign key dulu
        // -----------------------------
        $this->db->disableForeignKeyChecks();

        // -----------------------------
        // 2. Bersihkan tabel yang dipakai test
        // -----------------------------
        $this->db->table('detail_pemesanan')->truncate();
        $this->db->table('pemesanan')->truncate();
        $this->db->table('users')->truncate();

        // -----------------------------
        // 3. Nyalakan kembali foreign key
        // -----------------------------
        $this->db->enableForeignKeyChecks();

        // -----------------------------
        // 4. Tambahkan data uji (test data)
        // -----------------------------

        // User
        $this->db->table('users')->insert([
            'id_user'   => 1,
            'nama'      => 'Tester',
            'email'     => 'test@example.com',
            'password'  => password_hash('123456', PASSWORD_DEFAULT),
        ]);

        // Pesanan 1 → Selesai (bisa dinilai)
        $this->db->table('pemesanan')->insert([
            'id_pemesanan'      => 1,
            'id_user'           => 1,
            'status_pemesanan'  => 'Selesai',
        ]);

        // Detail pemesanan bisa dinilai
        $this->db->table('detail_pemesanan')->insert([
            'id_detail_pemesanan' => 10,
            'id_pemesanan'        => 1,
            'id_produk'           => 99,
            'user_rating'         => null,
            'user_ulasan'         => null,
        ]);

        // Pesanan 2 → Belum selesai (Dikemas)
        $this->db->table('pemesanan')->insert([
            'id_pemesanan'      => 2,
            'id_user'           => 1,
            'status_pemesanan'  => 'Dikemas',
        ]);

        // Detail pemesanan tidak boleh dinilai
        $this->db->table('detail_pemesanan')->insert([
            'id_detail_pemesanan' => 11,
            'id_pemesanan'        => 2,
            'id_produk'           => 100,
            'user_rating'         => null,
            'user_ulasan'         => null,
        ]);
    }
}
