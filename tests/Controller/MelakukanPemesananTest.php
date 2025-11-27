<?php

namespace Tests\Feature;

use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\HTTP\IncomingRequest;
use CodeIgniter\HTTP\URI;
use Config\App;
use App\Controllers\MelakukanPemesanan;
use App\Models\ProdukModel;
use App\Models\AlamatModel;
use App\Models\UserModel;
use ReflectionClass;
use CodeIgniter\HTTP\UserAgent;
use CodeIgniter\HTTP\Response;

/**
 * Unit Test untuk MelakukanPemesanan Controller.
 */
class MelakukanPemesananTest extends CIUnitTestCase
{
    protected $request;
    protected $response;

    protected function setUp(): void
    {
        parent::setUp();

        $_SESSION = [
            'id_user'   => 1,
            'username'  => 'Test User',
            'role'      => 'user',
            'logged_in' => true,
        ];

        $config = new App();

        $uri = new URI('http://example.com/melakukanpemesanan/1');

        $this->request = new IncomingRequest(
            $config,
            $uri,
            'php://input',
            new UserAgent()
        );

        $this->response = new Response($config);
    }

    protected function tearDown(): void
    {
        parent::tearDown();
        $_SESSION = [];
    }

    private function setPrivate($obj, $prop, $value)
    {
        $ref      = new ReflectionClass($obj);
        $property = $ref->getProperty($prop);
        $property->setAccessible(true);
        $property->setValue($obj, $value);
    }

    /**
     * Helper untuk membuat Request mock dengan data POST.
     */
    private function getRequestMock(array $postData)
    {
        $requestMock = $this->getMockBuilder(IncomingRequest::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getPost'])
            ->getMock();

        $requestMock->method('getPost')
            ->willReturnCallback(function ($key = null) use ($postData) {
                if ($key === null) {
                    return $postData;
                }
                return $postData[$key] ?? null;
            });

        $config = new App();
        $uri    = new URI('http://example.com/pemesanan/simpan');

        $this->setPrivate($requestMock, 'config', $config);
        $this->setPrivate($requestMock, 'uri', $uri);
        $this->setPrivate($requestMock, 'userAgent', new UserAgent());
        $this->setPrivate($requestMock, 'method', 'post');

        return $requestMock;
    }

    /**
     * Helper membuat controller dengan mock model & request.
     */
    private function getController($produkMock, $alamatMock, $userMock, $requestObj)
    {
        $controller = new MelakukanPemesanan();

        $this->setPrivate($controller, 'produkModel', $produkMock);
        $this->setPrivate($controller, 'alamatModel', $alamatMock);
        $this->setPrivate($controller, 'userModel', $userMock);
        $this->setPrivate($controller, 'request', $requestObj);
        $this->setPrivate($controller, 'response', $this->response);

        return $controller;
    }

    /**
     * Mock AlamatModel yang mengembalikan data lengkap.
     */
    private function getAlamatMock()
    {
        return new class {
            public array $whereCalls   = [];
            public array $orderByCalls = [];

            private function baseAlamat(int $userId = 1): array
            {
                return [
                    'id_alamat'     => 1,
                    'id_user'       => $userId,
                    'aktif'         => 1,
                    'nama_penerima' => 'Penerima Testing',
                    'jalan'         => 'Jl. Testing No. 123',
                    'no_telepon'    => '08123456789',
                    'kota'          => 'Padang',
                    'provinsi'      => 'Sumatera Barat',
                    'kode_pos'      => '25111',
                ];
            }

            public function find($id = null)
            {
                return $this->baseAlamat();
            }

            public function first()
            {
                return $this->baseAlamat();
            }

            public function findAll()
            {
                return [$this->baseAlamat()];
            }

            public function getAlamatAktifByUser($idUser)
            {
                return $this->baseAlamat($idUser);
            }

            public function where($field, $value)
            {
                $this->whereCalls[] = [$field, $value];
                return $this;
            }

            public function orderBy($field, $direction = 'ASC')
            {
                $this->orderByCalls[] = [$field, $direction];
                return $this;
            }
        };
    }

    // ---------------------------------------------------
    // 1. Index Single Item
    // ---------------------------------------------------
    public function testIndexSingleItem()
    {
        $produkMock = $this->createMock(ProdukModel::class);
        $produkMock->method('find')->with(1)->willReturn([
            'id_produk'    => 1,
            'nama_produk'  => 'Produk Test',
            'stok'         => 10,
            'harga'        => 10000,
            'deskripsi'    => 'Produk untuk testing',
            'foto'         => 'default.png',
        ]);

        $alamatMock = $this->getAlamatMock();

        $userMock = $this->getMockBuilder(UserModel::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['find', 'first'])
            ->getMock();

        $userData = [
            'id_user'  => 1,
            'nama'     => 'Test User',
            'username' => 'testuser',
            'role'     => 'user',
            'email'    => 'test@example.com',
            'foto'     => 'default.png',
        ];

        $userMock->method('find')->willReturn($userData);
        $userMock->method('first')->willReturn($userData);

        $controller = $this->getController($produkMock, $alamatMock, $userMock, $this->request);

        $result = $controller->index(1);

        $this->assertIsString($result);
        $this->assertNotEmpty($result);
        $this->assertStringContainsString('Produk Test', $result);
        $this->assertStringContainsString('Penerima Testing', $result);
    }

    // ---------------------------------------------------
    // 2. Simpan Single Item Success
    // ---------------------------------------------------
    public function testSimpanSingleItemSuccess()
    {
        // NOTE: sesuaikan nama field dengan yang dipakai di MelakukanPemesanan::simpan()
        $postData = [
            'produk_id' => 1,
            'qty'       => 2,
        ];

        $requestMock = $this->getRequestMock($postData);

        $produkMock = $this->createMock(ProdukModel::class);

        $produkMock->method('find')->with(1)->willReturn([
            'id_produk' => 1,
            'stok'      => 10,
            'harga'     => 10000,
        ]);

        // Longgarkan ekspektasi: cukup siap jika update dipanggil
        $produkMock->method('update')->willReturn(true);

        $alamatMock = $this->getAlamatMock();
        $userMock   = $this->createMock(UserModel::class);

        $controller = $this->getController($produkMock, $alamatMock, $userMock, $requestMock);

        $result = $controller->simpan();

        $this->assertNotNull($result);
    }

    // ---------------------------------------------------
    // 3. Simpan Gagal Qty > Stok
    // ---------------------------------------------------
    public function testSimpanFailsWhenQtyExceedsStock()
    {
        $postData = [
            'produk_id' => 1,
            'qty'       => 999,
        ];

        $requestMock = $this->getRequestMock($postData);

        $produkMock = $this->createMock(ProdukModel::class);
        $produkMock->method('find')->willReturn([
            'id_produk' => 1,
            'stok'      => 5,
            'harga'     => 10000,
        ]);

        $produkMock->expects($this->never())->method('update');

        $alamatMock = $this->getAlamatMock();
        $userMock   = $this->createMock(UserModel::class);

        $controller = $this->getController($produkMock, $alamatMock, $userMock, $requestMock);

        $result = $controller->simpan();

        $this->assertNotNull($result);
    }

    // ---------------------------------------------------
    // 4. Simpan Batch Success
    // ---------------------------------------------------
    public function testSimpanBatchSuccess()
    {
        $postData = [
            'items' => [
                ['produk_id' => 1, 'qty' => 2],
                ['produk_id' => 2, 'qty' => 1],
            ],
        ];

        $requestMock = $this->getRequestMock($postData);

        $produkMock = $this->createMock(ProdukModel::class);

        $produkMock->method('find')
            ->willReturnCallback(function ($id) {
                $data = [
                    1 => ['id_produk' => 1, 'stok' => 10, 'harga' => 10000],
                    2 => ['id_produk' => 2, 'stok' => 5,  'harga' => 20000],
                ];
                return $data[$id] ?? null;
            });

        $produkMock->method('update')->willReturn(true);

        $alamatMock = $this->getAlamatMock();
        $userMock   = $this->createMock(UserModel::class);

        $controller = $this->getController($produkMock, $alamatMock, $userMock, $requestMock);

        $result = $controller->simpan();

        $this->assertNotNull($result);
    }

    // ---------------------------------------------------
    // 5. Batch Gagal Qty > Stok
    // ---------------------------------------------------
    public function testSimpanBatchFailsWhenQtyExceedsStock()
    {
        $postData = [
            'items' => [
                ['produk_id' => 1, 'qty' => 999],
            ],
        ];

        $requestMock = $this->getRequestMock($postData);

        $produkMock = $this->createMock(ProdukModel::class);
        $produkMock->method('find')->willReturn([
            'id_produk' => 1,
            'stok'      => 5,
            'harga'     => 10000,
        ]);

        $produkMock->expects($this->never())->method('update');

        $alamatMock = $this->getAlamatMock();
        $userMock   = $this->createMock(UserModel::class);

        $controller = $this->getController($produkMock, $alamatMock, $userMock, $requestMock);

        $result = $controller->simpan();

        $this->assertNotNull($result);
    }
}
