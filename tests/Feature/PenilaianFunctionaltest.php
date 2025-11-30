<?php

namespace Tests\Feature;

use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\FeatureTestTrait;
use App\Models\UserModel;

class PenilaianFunctionalTest extends CIUnitTestCase
{
    use FeatureTestTrait;

    private function createUser(): int
    {
        $userModel = new UserModel();

        $email = 'penilaian' . rand(1000, 9999) . '@mail.com';

        return $userModel->insert([
            'nama_lengkap' => 'User Penilaian',
            'username'     => 'user_penilaian_' . rand(1000, 9999),
            'email'        => $email,
            'password'     => password_hash('123456', PASSWORD_DEFAULT),
            'role'         => 'pembeli',
        ]);
    }

    // 1. daftar()

    public function testDaftarRedirectToLoginWhenNotLoggedIn()
    {
        $result = $this->get('/penilaian/daftar');

        $result->assertStatus(302);
        $result->assertRedirectTo('/login');
    }

    public function testDaftarPageCanBeAccessedWhenLoggedIn()
    {
        $userId = $this->createUser();

        $result = $this->withSession([
            'id_user' => $userId,
        ])->get('/penilaian/daftar');

        $result->assertStatus(200);
    }

    // 2. simpan()

    public function testSimpanRedirectToLoginWhenNotLoggedIn()
    {
        $result = $this->post('/penilaian/simpan/1', [
            'rating' => 5,
            'ulasan' => 'Bagus'
        ]);

        $result->assertStatus(302);
        $result->assertRedirectTo('/login');
    }

    public function testSimpanValidationErrorWhenRatingEmpty()
    {
        $userId = $this->createUser();

        $result = $this->withSession([
            'id_user' => $userId,
        ])->post('/penilaian/simpan/12345', [
            'rating' => '',
            'ulasan' => 'Ulasan kosong rating'
        ]);

        $result->assertStatus(302);
    }
}
