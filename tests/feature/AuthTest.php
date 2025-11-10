<?php

namespace Tests\Unit;

use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\FeatureTestTrait;

class AuthTest extends CIUnitTestCase
{
    use FeatureTestTrait;

    /**
     * Test halaman login dapat diakses
     */
    public function testHalamanLoginBisaDibuka()
    {
        $result = $this->get('/login');
        $result->assertStatus(200);
        $result->assertSee('Login');
    }

    /**
     * Test login gagal karena email tidak ditemukan
     */
    public function testLoginEmailTidakDitemukan()
    {
        $postData = [
            'email' => 'tidakada@example.com',
            'password' => 'password123'
        ];

        $result = $this->post('/auth/doLogin', $postData);
        $result->assertRedirectTo('/login');
    }

    /**
     * Test login gagal karena password salah
     */
    public function testLoginPasswordSalah()
    {
        $postData = [
            'email' => 'admin@example.com',
            'password' => 'salahbanget'
        ];

        $result = $this->post('/auth/doLogin', $postData);
        $result->assertRedirectTo('/login');
    }

    /**
     * Test login berhasil untuk user
     */
    public function testLoginBerhasilSebagaiUser()
    {
        $postData = [
            'email' => 'user01@farmunand.local', // pastikan ada di DB
            'password' => '111111'
        ];

        $result = $this->post('/auth/doLogin', $postData);
        $result->assertRedirectTo('/dashboarduser');
    }

    /**
     * Test login berhasil untuk admin
     */
    public function testLoginBerhasilSebagaiAdmin()
    {
        $postData = [
            'email' => 'admin@farmunand.local',
            'password' => '111111'
        ];

        $result = $this->post('/auth/doLogin', $postData);
        $result->assertRedirectTo('/dashboard');
    }

    /**
     * Test logout
     */
    public function testLogout()
    {
        session()->set(['logged_in' => true]);
        $result = $this->get('/auth/logout');
        $result->assertRedirectTo('/login');
    }

    /**
     * Test register gagal karena validasi salah
     */
    public function testRegisterGagalValidasi()
    {
        $postData = [
            'username' => '',
            'email' => 'salah',
            'password' => '123',
            'password_confirm' => '1234'
        ];

        $result = $this->post('/auth/doRegister', $postData);
        $result->assertRedirect();
    }

    /**
     * Test register berhasil
     */
    public function testRegisterBerhasil()
    {
        $emailBaru = 'test' . rand(1000, 9999) . '@example.com';

        $postData = [
            'username' => 'UserTest',
            'email' => $emailBaru,
            'password' => 'password123',
            'password_confirm' => 'password123'
        ];

        $result = $this->post('/auth/doRegister', $postData);
        $result->assertRedirectTo('/login');
    }
}
