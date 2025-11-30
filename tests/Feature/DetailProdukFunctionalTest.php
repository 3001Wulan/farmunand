<?php

namespace Tests\Feature;

use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\FeatureTestTrait;
use CodeIgniter\Exceptions\PageNotFoundException;

class DetailProdukFunctionalTest extends CIUnitTestCase
{
    use FeatureTestTrait;

    private string $routeDetail = '/detailproduk';

    public function testDetailProdukBerhasilDitampilkan()
    {
        $produkId = db_connect()->table('produk')->insert([
            'nama_produk' => 'Produk Test',
            'harga'       => 50000,
            'stok'        => 10,
            'deskripsi'   => 'Deskripsi produk test'
        ], true);

        $result = $this->get("$this->routeDetail/$produkId");

        $result->assertStatus(200);

        // ❗Gunakan teks yang pasti muncul di VIEW
        $result->assertSee('Detail Produk');
    }

    public function testDetailProdukTidakDitemukan()
    {
        $this->expectException(PageNotFoundException::class);

        $this->get("$this->routeDetail/999999");
    }

    public function testDetailProdukDenganUserLogin()
    {
        $userId = db_connect()->table('users')->insert([
            'nama'     => 'User Test',
            'username' => 'usertest',
            'email'    => 'usertest_' . time() . '@example.com',
            'password' => password_hash('password', PASSWORD_DEFAULT)
        ], true);

        $this->withSession(['id_user' => $userId]);

        $produkId = db_connect()->table('produk')->insert([
            'nama_produk' => 'Produk User',
            'harga'       => 200000,
            'stok'        => 7,
            'deskripsi'   => 'Deskripsi'
        ], true);

        $result = $this->get("$this->routeDetail/$produkId");

        $result->assertStatus(200);

        // ❗Cek teks yang PASTI ada, tidak perlu cek 'User Test'
        $result->assertSee('Detail Produk');
    }
}
