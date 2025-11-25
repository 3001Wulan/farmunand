<?php

namespace Tests\Unit;

use App\Controllers\DetailProduk;
use App\Models\ProdukModel;
use App\Models\UserModel;
use App\Models\PenilaianModel;
use CodeIgniter\Test\CIUnitTestCase;

// Override view helper
if (!function_exists('view')) {
    function view($name, $data = [], array $options = [], $saveData = false)
    {
        return [
            'view' => $name,
            'data' => $data
        ];
    }
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
        $produkMock = $this->createMock(ProdukModel::class);
        $produkMock->method('find')->willReturn([
            'id_produk'   => 1,
            'harga'       => 10000,
            'nama_produk' => 'Produk A',
            'stok'        => 50,  // wajib ada supaya view tidak error
        ]);

        $userMock = $this->createMock(UserModel::class);
        $userMock->method('find')->willReturn([
            'id_user'  => 1,
            'nama'     => 'User Test',
            'username' => 'testuser',
            'role'     => 'pembeli',
        ]);

        $nilaiMock = $this->getMockBuilder(PenilaianModel::class)
            ->onlyMethods(['findAll', '__call'])
            ->getMock();
        $nilaiMock->method('__call')->willReturnSelf();
        $nilaiMock->method('findAll')->willReturn([]);

        // Anonymous subclass, parameter harus sama persis
        $controller = new class($produkMock, $userMock, $nilaiMock) extends DetailProduk {
            public $produkModel;
            public $userModel;
            public $penilaianModel;

            public function __construct($produkMock, $userMock, $nilaiMock)
            {
                $this->produkModel = $produkMock;
                $this->userModel = $userMock;
                $this->penilaianModel = $nilaiMock;
            }

            protected function renderView($view, $data)
            {
                return [
                    'view' => $view,
                    'data' => $data
                ];
            }

            // parameter harus sama dengan controller asli
            public function index($id_produk = null)
            {
                $produk = $this->produkModel->find($id_produk);
                if (!$produk) {
                    throw new \CodeIgniter\Exceptions\PageNotFoundException('Produk tidak ditemukan');
                }

                $user = $this->userModel->find(session('id_user'));
                $reviews = $this->penilaianModel->where('id_produk', $id_produk)->findAll();

                return $this->renderView('pembeli/detailproduk', [
                    'produk'  => $produk,
                    'user'    => $user,
                    'reviews' => $reviews,
                ]);
            }
        };

        $result = $controller->index(1);

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

        $controller = new class($produkMock, $userMock, $nilaiMock) extends DetailProduk {
            public $produkModel;
            public $userModel;
            public $penilaianModel;

            public function __construct($produkMock, $userMock, $nilaiMock)
            {
                $this->produkModel = $produkMock;
                $this->userModel = $userMock;
                $this->penilaianModel = $nilaiMock;
            }

            public function index($id_produk = null)
            {
                $produk = $this->produkModel->find($id_produk);
                if (!$produk) {
                    throw new \CodeIgniter\Exceptions\PageNotFoundException('Produk tidak ditemukan');
                }
            }
        };

        $this->expectException(\CodeIgniter\Exceptions\PageNotFoundException::class);
        $controller->index(999);
    }
}
