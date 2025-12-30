<?php

namespace Tests\Feature;

use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\FeatureTestTrait;
use App\Models\UserModel;

class ProfileAdminFunctionalTest extends CIUnitTestCase
{
    use FeatureTestTrait;

    private string $routeProfile = '/profileadmin';

    /**
     * Test halaman profil admin saat user login
     */
    public function testIndexProfileAdminSaatLogin()
    {
        // Insert user lengkap
        $userTable = db_connect()->table('users');
        $userTable->insert([
            'nama'     => 'Admin Test',
            'username' => 'admintest',
            'email'    => 'admintest_' . time() . '@example.com',
            'password' => password_hash('password', PASSWORD_DEFAULT),
            'no_hp'    => '08123456789',
            'foto'     => 'default.png'
        ]);

        $userId = db_connect()->insertID(); // ambil ID insert dari connection

        // Simulasi login
        $this->withSession(['id_user' => $userId]);

        $result = $this->get($this->routeProfile);

        $result->assertStatus(200);
        $result->assertSee('Profil Admin');
        $result->assertSee('Admin Test');
        $result->assertSee('admintest'); // cek username
    }

    /**
     * Test redirect ke login jika user belum login
     */
    public function testIndexProfileAdminTanpaLogin()
    {
        $result = $this->get($this->routeProfile);

        $result->assertStatus(302);
        $result->assertRedirectTo('/login');
    }

    
}