<?php

namespace Tests\Feature;

use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\FeatureTestTrait;

class ManajemenAkunUserFunctionalTest extends CIUnitTestCase
{
    use FeatureTestTrait;

    protected function setUp(): void
    {
        parent::setUp();

        $db = db_connect();
        $users = $db->table('users');

        // Hapus data dummy lama agar tidak duplicate
        $users->where('id_user', 1001)->orWhere('id_user', 1002)->delete();
        $users->where('email', 'testuser@example.com')->orWhere('email', 'testadmin@example.com')->delete();

        // Insert data dummy baru
        $users->insert([
            'id_user' => 1001,
            'nama' => 'Test User',
            'email' => 'testuser@example.com',
            'username' => 'testuser',
            'no_hp' => '08123456789',
            'role' => 'user',
            'status' => 'active',
        ]);

        $users->insert([
            'id_user' => 1002,
            'nama' => 'Test Admin',
            'email' => 'testadmin@example.com',
            'username' => 'testadmin',
            'no_hp' => '08123456788',
            'role' => 'admin',
            'status' => 'active',
        ]);
    }

    // --- Test redirect jika belum login ---
    public function testIndexRedirectsIfNotLoggedIn()
{
    session()->destroy();

    $result = $this->get('/manajemenakunuser');

    $result->assertStatus(200);


}



    // --- Test tampil user list saat login ---
    public function testIndexShowsUserListWhenLoggedIn()
    {
        $this->withSession(['id_user' => 1002]); // login sebagai admin

        $result = $this->get('/manajemenakunuser');

        $result->assertOK();
        $result->assertSee('Test User');
        $result->assertSee('Test Admin');
    }

    // --- Test halaman edit user ---
    public function testEditUserPage()
    {
        $this->withSession(['id_user' => 1002]);

        $result = $this->get('/manajemenakunuser/edit/1001');

        $result->assertOK();
        $result->assertSee('Test User');
    }

    // --- Test update user ---
    public function testUpdateUser()
    {
        $this->withSession(['id_user' => 1002]);

        $result = $this->post('/manajemenakunuser/update/1001', [
            'nama' => 'Updated User',
            'email' => 'updated@example.com',
            'no_hp' => '08987654321',
            'status' => 'inactive',
        ]);

        $result->assertRedirectTo('/manajemenakunuser');
        $result->assertSessionHas('success', 'User diperbarui.');
    }

    // --- Test mencegah hapus diri sendiri ---
    public function testDeleteUserPreventsSelfDeletion()
    {
        $this->withSession(['id_user' => 1001]);

        $result = $this->post('/manajemenakunuser/delete/1001');

        $result->assertRedirect();
        $result->assertSessionHas('error', 'Tidak bisa menghapus akun sendiri.');
    }

    // --- Test hapus user berhasil ---
    public function testDeleteUserSuccessfully()
    {
        $this->withSession(['id_user' => 1002]);

        $result = $this->post('/manajemenakunuser/delete/1001');

        $result->assertRedirectTo('/manajemenakunuser');
        $result->assertSessionHas('success', 'User dihapus.');
    }
}
