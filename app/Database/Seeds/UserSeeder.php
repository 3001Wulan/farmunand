<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class UserSeeder extends Seeder
{
    public function run()
    {
        $data = [
            'username' => 'admin',
            'email'    => 'admin@farmunand.com',
            'password' => password_hash('admin123', PASSWORD_DEFAULT),
            'role'     => 'admin',
        ];

        $this->db->table('users')->insert($data);
    }
}
