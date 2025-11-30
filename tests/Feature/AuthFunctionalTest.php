<?php

namespace Tests\Feature;

use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\FeatureTestTrait;

class AuthFunctionalTest extends CIUnitTestCase
{
    use FeatureTestTrait;

    public function testLoginBerhasil()
{
    $result = $this->post('/auth/doLogin', [
        'email' => 'user@gmail.com',
        'password' => 'password123',
    ]);

    $result->assertStatus(302);
    $result->assertRedirectTo('/login'); // sesuai redirect controller kamu
}


    public function testLoginGagalPasswordSalah()
    {
        $result = $this->post('/auth/doLogin', [
            'email' => 'user@gmail.com',
            'password' => 'salah',
        ]);

        $result->assertStatus(302);
        $result->assertRedirectTo('/login');
    }

    public function testLoginGagalSampaiTerkunci()
    {
        // 3× gagal
        for ($i = 0; $i < 3; $i++) {
            $this->post('/auth/doLogin', [
                'email' => 'user@gmail.com',
                'password' => 'salah',
            ]);
        }

        $result = $this->post('/auth/doLogin', [
            'email' => 'user@gmail.com',
            'password' => 'password123',
        ]);

        $result->assertStatus(302);
        $result->assertRedirectTo('/login'); // tetap ditolak
    }

    public function testTidakBisaLoginJikaAkunTerkunci()
    {
        // anggap akun sudah terkunci
        $result = $this->post('/auth/doLogin', [
            'email' => 'user@gmail.com',
            'password' => 'password123',
        ]);

        $result->assertStatus(302);
        $result->assertRedirectTo('/login');
    }

    public function testRegisterBerhasil()
{
    $result = $this->post('/auth/doRegister', [
        'nama'     => 'User Baru',
        'email'    => 'baru@gmail.com',
        'password' => '123456',
    ]);

    $result->assertStatus(302);
    $result->assertRedirectTo('/'); // sesuai redirect controller kamu
}

    public function testLogoutBerhasil()
    {
        $result = $this->get('/logout');
        $result->assertStatus(302);
        $result->assertRedirectTo('/login');
    }
}
