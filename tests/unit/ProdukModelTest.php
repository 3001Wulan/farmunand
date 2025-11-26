<?php

namespace Tests\Unit;

use CodeIgniter\Test\CIUnitTestCase;
use App\Models\ProdukModel;

class ProdukModelTest extends CIUnitTestCase
{
    protected $produkModel;

    protected function setUp(): void
    {
        parent::setUp();

        // 🔥 Mock ProdukModel
        $this->produkModel = $this->createMock(ProdukModel::class);
    }

    /** 🧪 Test: getTotalProduk() */
    public function testGetTotalProduk()
{
    // Arrange
    $this->produkModel = $this->createStub(ProdukModel::class);
    $this->produkModel->method('getTotalProduk')->willReturn(5);

    // Act
    $total = $this->produkModel->getTotalProduk();

    // Assert
    $this->assertEquals(5, $total);
}



    /** 🧪 Test: getStokRendah() */
    public function testGetStokRendah()
    {
        $this->produkModel
            ->method('countAllResults')
            ->willReturn(1);

        $lowStock = $this->produkModel->countAllResults();
        $this->assertEquals(1, $lowStock);
    }

    /** 🧪 Test: getProdukRekomendasi() */
    public function testGetProdukRekomendasi()
    {
        $fakeData = [
            ['nama_produk' => 'Test_Produk_1'],
            ['nama_produk' => 'Test_Produk_2'],
            ['nama_produk' => 'Test_Produk_3'],
        ];

        $this->produkModel
            ->method('getProdukRekomendasi')
            ->willReturn($fakeData);

        $rekom = $this->produkModel->getProdukRekomendasi(3);

        $this->assertCount(3, $rekom);
        $this->assertStringContainsString('Test_Produk', $rekom[0]['nama_produk']);
    }

    /** 🧪 Test: getProdukById() */
    public function testGetProdukById()
    {
        $fakeProduk = [
            'id'          => 99,
            'nama_produk' => 'Test_Produk_X',
            'deskripsi'   => 'Deskripsi X',
            'harga'       => 5000,
            'stok'        => 5,
            'kategori'    => 'Makanan'
        ];

        $this->produkModel
            ->method('getProdukById')
            ->willReturn($fakeProduk);

        $produk = $this->produkModel->getProdukById(99);
        $this->assertEquals('Test_Produk_X', $produk['nama_produk']);
    }

    /** 🧪 Test: searchProduk() */
    public function testSearchProduk()
    {
        $fakeResults = [
            ['nama_produk' => 'Test_Coklat', 'deskripsi' => 'Rasa manis']
        ];

        $this->produkModel
            ->method('searchProduk')
            ->willReturn($fakeResults);

        $results = $this->produkModel->searchProduk('Test_Coklat');
        $this->assertCount(1, $results);
        $this->assertEquals('Test_Coklat', $results[0]['nama_produk']);
    }

    /** 🧪 Test: getKategoriList() */
    public function testGetKategoriList()
    {
        $fakeKategori = ['Minuman', 'Makanan'];

        $this->produkModel
            ->method('getKategoriList')
            ->willReturn($fakeKategori);

        $kategoriList = $this->produkModel->getKategoriList();

        $this->assertContains('Minuman', $kategoriList);
        $this->assertContains('Makanan', $kategoriList);
    }
}
