<?php

namespace Tests\Feature;

use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\FeatureTestTrait;
use App\Models\UserModel;

class PesananFunctionalTest extends CIUnitTestCase
{
    use FeatureTestTrait;

    private function createUser(): int
    {
        $userModel = new UserModel();

        $email = 'pesanan' . rand(1000, 9999) . '@mail.com';

        return $userModel->insert([
            'nama_lengkap' => 'User Pesanan',
            'username'     => 'user_pesanan_' . rand(1000, 9999),
            'email'        => $email,
            'password'     => password_hash('123456', PASSWORD_DEFAULT),
            'role'         => 'pembeli',
        ]);
    }

    /* =========================
     * 1. RIWAYAT PESANAN (index)
     * ========================= */

    public function testIndexRedirectsWhenNotLoggedIn()
    {
        // ⬇ ganti dari /pesanan ke /riwayatpesanan
        $result = $this->get('/riwayatpesanan');

        $result->assertStatus(302);
        $result->assertRedirectTo('/login');
    }

    public function testIndexCanBeAccessedWhenLoggedIn()
    {
        $userId = $this->createUser();

        $result = $this->withSession([
            'id_user' => $userId,
        ])->get('/riwayatpesanan'); // ⬅ ganti juga

        $result->assertStatus(200);
    }

    /* =========================
     * 2. /konfirmasipesanan
     * ========================= */

    public function testKonfirmasiPesananRedirectsWhenNotLoggedIn()
    {
        $result = $this->get('/konfirmasipesanan');

        $result->assertStatus(302);
        $result->assertRedirectTo('/login');
    }

    public function testKonfirmasiPesananCanBeAccessedWhenLoggedIn()
    {
        $userId = $this->createUser();

        $result = $this->withSession([
            'id_user' => $userId,
        ])->get('/konfirmasipesanan');

        $result->assertStatus(200);
    }

    /* =========================
     * 3. /pesananselesai
     * ========================= */

    public function testPesananSelesaiRedirectsWhenNotLoggedIn()
    {
        $result = $this->get('/pesananselesai');

        $result->assertStatus(302);
        $result->assertRedirectTo('/login');
    }

    public function testPesananSelesaiCanBeAccessedWhenLoggedIn()
    {
        $userId = $this->createUser();

        $result = $this->withSession([
            'id_user' => $userId,
        ])->get('/pesananselesai');

        $result->assertStatus(200);
    }

    /* =========================
     * 4. /pesanandikemas
     * ========================= */

    public function testPesananDikemasRedirectsWhenNotLoggedIn()
    {
        $result = $this->get('/pesanandikemas');

        $result->assertStatus(302);
        $result->assertRedirectTo('/login');
    }

    public function testPesananDikemasCanBeAccessedWhenLoggedIn()
    {
        $userId = $this->createUser();

        $result = $this->withSession([
            'id_user' => $userId,
        ])->get('/pesanandikemas');

        $result->assertStatus(200);
    }

    /* =========================
     * 5. /pesananbelumbayar
     * ========================= */

    public function testPesananBelumBayarRedirectsWhenNotLoggedIn()
    {
        $result = $this->get('/pesananbelumbayar');

        $result->assertStatus(302);
        $result->assertRedirectTo('/login');
    }

    public function testPesananBelumBayarCanBeAccessedWhenLoggedIn()
    {
        $userId = $this->createUser();

        $result = $this->withSession([
            'id_user' => $userId,
        ])->get('/pesananbelumbayar');

        $result->assertStatus(200);
    }

    /* =========================
     * 6. /pesanandibatalkan
     * ========================= */

    public function testPesananDibatalkanRedirectsWhenNotLoggedIn()
    {
        $result = $this->get('/pesanandibatalkan');

        $result->assertStatus(302);
        $result->assertRedirectTo('/login');
    }

    public function testPesananDibatalkanCanBeAccessedWhenLoggedIn()
    {
        $userId = $this->createUser();

        $result = $this->withSession([
            'id_user' => $userId,
        ])->get('/pesanandibatalkan');

        $result->assertStatus(200);
    }
}
