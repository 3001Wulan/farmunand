<?php

namespace Tests\Controllers;

use App\Controllers\DetailProduk;
use App\Models\ProdukModel;
use App\Models\UserModel;
use App\Models\PenilaianModel;
use CodeIgniter\Test\CIUnitTestCase;

// Override view helper
function view($name, $data = [], array $options = [], $saveData = false)
{
    return [
        'view' => $name,
        'data' => $data
    ];
}

class DetailProdukTest extends CIUnitTestCase
{
    protected function setUp(): void
{
    parent::setUp();

    session()->set([
        'id_user' => 1,
        'role' => 'pembeli',
        'username' => 'testuser',
        'cart_count_u_1' => 0,
    ]);
}

    public function test_index_returns_view()
    {
        // ==== MOCK PRODUK ====
        $produkMock = $this->createMock(ProdukModel::class);
        $produkMock->method('find')->willReturn([
            'id_produk' => 1,
            'harga'       => 10000,      
            'nama_produk' => 'Produk A'
        ]);

        // ==== MOCK USER ====
        $userMock = $this->createMock(UserModel::class);
        $userMock->method('find')->willReturn([
            'id_user' => 1,
            'nama' => 'User Test',
            'username' => 'testuser',
            'role' => 'pembeli',
        ]);

        // ==== MOCK PENILAIAN ====
        $nilaiMock = $this->getMockBuilder(PenilaianModel::class)
            ->onlyMethods(['findAll', '__call'])
            ->getMock();

        // Magic call untuk chaining: select/join/where/orderBy
        $nilaiMock->method('__call')->willReturnSelf();

        // Hasil review
        $nilaiMock->method('findAll')->willReturn([]);

        // Controller
        $controller = new DetailProduk($produkMock, $userMock, $nilaiMock);

        $result = $controller->index(1);

        // Assertions
        $this->assertIsArray($result);
        $this->assertEquals('pembeli/detailproduk', $result['view']);
        $this->assertArrayHasKey('produk', $result['data']);
        $this->assertArrayHasKey('user', $result['data']);
        $this->assertArrayHasKey('reviews', $result['data']);
    }

    public function test_index_not_found()
    {
        $produkMock = $this->createMock(ProdukModel::class);
        $produkMock->method('find')->willReturn(null);

        $userMock = $this->createMock(UserModel::class);

        $nilaiMock = $this->createMock(PenilaianModel::class);

        $controller = new DetailProduk($produkMock, $userMock, $nilaiMock);

        $this->expectException(\CodeIgniter\Exceptions\PageNotFoundException::class);

        $controller->index(999);
    }
}
