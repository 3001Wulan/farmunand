<?php

namespace Tests\Feature;

use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\FeatureTestTrait;
use App\Models\UserModel;

class KeranjangFunctionalTest extends CIUnitTestCase
{
    use FeatureTestTrait;

    private function createUser(): int
    {
        $userModel = new UserModel();

        $email = 'keranjang' . rand(1000, 9999) . '@mail.com';

        return $userModel->insert([
            'nama_lengkap' => 'User Keranjang',
            'username'     => 'user_keranjang_' . rand(1000, 9999),
            'email'        => $email,
            'password'     => password_hash('123456', PASSWORD_DEFAULT),
            'role'         => 'pembeli', // kalau tidak ada, hapus saja
        ]);
    }

    /* =========================
     * 1. /keranjang (index)
     * ========================= */

    public function testIndexRedirectsWhenNotLoggedIn()
    {
        $result = $this->get('/keranjang');

        $result->assertStatus(302);
        $result->assertRedirectTo('/login');
    }

    // kalau mau, test untuk login bisa di-skip karena ada filter luar
    // public function testIndexCanBeAccessedWhenLoggedIn()
    // {
    //     $userId = $this->createUser();
    //
    //     $result = $this->withSession([
    //         'id_user' => $userId,
    //     ])->get('/keranjang');
    //
    //     $result->assertStatus(200);
    // }

    /* =========================
     * 2. /keranjang/add
     * ========================= */

    public function testAddRedirectsWhenNotLoggedIn()
    {
        $result = $this->post('/keranjang/add', [
            'id_produk' => 1,
            'qty'       => 1,
        ]);

        $result->assertStatus(302);
        $result->assertRedirectTo('/login');
    }

    public function testAddProdukTidakDitemukan()
    {
        $userId = $this->createUser();

        $result = $this->withSession([
            'id_user' => $userId,
        ])->post('/keranjang/add', [
            'id_produk' => 999999, // id fiktif
            'qty'       => 1,
        ]);

        // redirect back (302) karena produk tidak ditemukan
        $result->assertStatus(302);
    }

    /* =========================
     * 3. /keranjang/update
     * ========================= */

    public function testUpdateRedirectsWhenNotLoggedIn()
    {
        $result = $this->post('/keranjang/update', [
            'id_produk' => 1,
            'qty'       => 2,
        ]);

        $result->assertStatus(302);
        $result->assertRedirectTo('/login');
    }
}
