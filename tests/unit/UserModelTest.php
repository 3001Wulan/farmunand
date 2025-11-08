<?php

namespace Tests\Unit;

use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\DatabaseTestTrait;
use App\Models\UserModel;
use App\Models\PesananModel;

class UserModelTest extends CIUnitTestCase
{
    use DatabaseTestTrait; // rollback otomatis setelah tiap test

    /**
     * Test getTotalUser()
     */
    public function testGetTotalUser()
    {
        $userModel = new UserModel();

        // Kosongkan tabel users sebelum test (lebih aman dari delete/truncate)
        $this->db->table('users')->emptyTable();

        // Insert 2 user sementara untuk test
        $this->db->table('users')->insert([
            'username' => 'User1',
            'email'    => 'user1@example.com',
            'password' => password_hash('password123', PASSWORD_DEFAULT),
        ]);

        $this->db->table('users')->insert([
            'username' => 'User2',
            'email'    => 'user2@example.com',
            'password' => password_hash('password123', PASSWORD_DEFAULT),
        ]);

        $total = $userModel->getTotalUser();

        $this->assertEquals(2, $total); // pastikan total user = 2
    }

    /**
     * Test hasPendingOrders()
     */
    public function testHasPendingOrders()
    {
        $userModel = new UserModel();
        $pesananModel = new PesananModel();

        // Kosongkan tabel users dan pemesanan sebelum test
        $this->db->table('pemesanan')->emptyTable();
        $this->db->table('users')->emptyTable();

        // Insert user
        $this->db->table('users')->insert([
            'username' => 'User1',
            'email'    => 'user1@example.com',
            'password' => password_hash('password123', PASSWORD_DEFAULT),
        ]);
        $id_user = $this->db->insertID(); // ambil ID otomatis

        // Insert pesanan pending
        $this->db->table('pemesanan')->insert([
            'id_user' => $id_user,
            'status_pemesanan' => 'Proses'
        ]);

        $result = $userModel->hasPendingOrders($id_user);
        $this->assertTrue($result); // user punya pesanan pending

        // Insert pesanan selesai
        $this->db->table('pemesanan')->insert([
            'id_user' => $id_user,
            'status_pemesanan' => 'Selesai'
        ]);

        $result2 = $userModel->hasPendingOrders($id_user);
        $this->assertTrue($result2); // masih ada pending, harus true

        // Insert user baru tanpa pesanan
        $this->db->table('users')->insert([
            'username' => 'User2',
            'email'    => 'user2@example.com',
            'password' => password_hash('password123', PASSWORD_DEFAULT),
        ]);
        $id_user2 = $this->db->insertID();

        $result3 = $userModel->hasPendingOrders($id_user2);
        $this->assertFalse($result3); // user tanpa pesanan pending
    }
}
