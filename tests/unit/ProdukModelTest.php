<?php

namespace Tests\Unit;

use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\DatabaseTestTrait;
use App\Models\ProdukModel;

class ProdukModelTest extends CIUnitTestCase
{
    use DatabaseTestTrait;

    protected $produkModel;

    protected function setUp(): void
    {
        parent::setUp();
        $this->produkModel = new ProdukModel();
    }

    protected function tearDown(): void
    {
        // 🔥 Hapus hanya data test (yang namanya diawali 'Test_')
        $this->db->table('produk')->like('nama_produk', 'Test_', 'after')->delete();
        parent::tearDown();
    }

    /** 🧪 Test: getTotalProduk() */
    public function testGetTotalProduk()
    {
        $before = $this->produkModel->countAll();

        $this->db->table('produk')->insertBatch([
            [
                'nama_produk' => 'Test_Produk_A',
                'deskripsi'   => 'Deskripsi A',
                'harga'       => 10000,
                'stok'        => 5,
                'kategori'    => 'Makanan'
            ],
            [
                'nama_produk' => 'Test_Produk_B',
                'deskripsi'   => 'Deskripsi B',
                'harga'       => 15000,
                'stok'        => 10,
                'kategori'    => 'Minuman'
            ]
        ]);

        $total = $this->produkModel->countAll();
        $this->assertEquals($before + 2, $total);
    }

    /** 🧪 Test: getStokRendah() */
    public function testGetStokRendah()
    {
        $this->db->table('produk')->insertBatch([
            [
                'nama_produk' => 'Test_Produk_C',
                'deskripsi'   => 'Deskripsi C',
                'harga'       => 12000,
                'stok'        => 2,
                'kategori'    => 'Makanan'
            ],
            [
                'nama_produk' => 'Test_Produk_D',
                'deskripsi'   => 'Deskripsi D',
                'harga'       => 15000,
                'stok'        => 10,
                'kategori'    => 'Minuman'
            ]
        ]);

        $lowStock = $this->produkModel
            ->like('nama_produk', 'Test_')
            ->where('stok <', 5)
            ->countAllResults();

        $this->assertEquals(1, $lowStock);
    }

    /** 🧪 Test: getProdukRekomendasi() */
    public function testGetProdukRekomendasi()
    {
        for ($i = 1; $i <= 6; $i++) {
            $this->db->table('produk')->insert([
                'nama_produk' => "Test_Produk_$i",
                'deskripsi'   => "Deskripsi $i",
                'harga'       => 10000 + $i,
                'stok'        => 10,
                'kategori'    => 'Lainnya'
            ]);
        }

        $rekom = $this->produkModel->getProdukRekomendasi(3);
        $this->assertCount(3, $rekom);
        $this->assertStringContainsString('Test_Produk', $rekom[0]['nama_produk']);
    }

    /** 🧪 Test: getProdukById() */
    public function testGetProdukById()
    {
        $this->db->table('produk')->insert([
            'nama_produk' => 'Test_Produk_X',
            'deskripsi'   => 'Deskripsi X',
            'harga'       => 5000,
            'stok'        => 5,
            'kategori'    => 'Makanan'
        ]);

        $id = $this->db->insertID();
        $produk = $this->produkModel->getProdukById($id);

        $this->assertEquals('Test_Produk_X', $produk['nama_produk']);
    }

    /** 🧪 Test: searchProduk() */
    public function testSearchProduk()
    {
        $this->db->table('produk')->insertBatch([
            [
                'nama_produk' => 'Test_Coklat',
                'deskripsi'   => 'Rasa manis',
                'harga'       => 8000,
                'stok'        => 10,
                'kategori'    => 'Makanan'
            ],
            [
                'nama_produk' => 'Test_Kopi',
                'deskripsi'   => 'Minuman pahit',
                'harga'       => 10000,
                'stok'        => 10,
                'kategori'    => 'Minuman'
            ]
        ]);

        $results = $this->produkModel->searchProduk('Test_Coklat');
        $this->assertCount(1, $results);
        $this->assertEquals('Test_Coklat', $results[0]['nama_produk']);
    }

    /** 🧪 Test: getKategoriList() */
    public function testGetKategoriList()
    {
        $this->db->table('produk')->insertBatch([
            [
                'nama_produk' => 'Test_Teh',
                'deskripsi'   => 'Minuman Teh',
                'harga'       => 5000,
                'stok'        => 10,
                'kategori'    => 'Minuman'
            ],
            [
                'nama_produk' => 'Test_Snack',
                'deskripsi'   => 'Camilan',
                'harga'       => 3000,
                'stok'        => 15,
                'kategori'    => 'Makanan'
            ]
        ]);

        $kategoriList = $this->produkModel->getKategoriList();

        $this->assertContains('Minuman', $kategoriList);
        $this->assertContains('Makanan', $kategoriList);
    }
}
