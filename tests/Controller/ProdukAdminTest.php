<?php

namespace Tests\Controllers;

use App\Controllers\ProdukAdmin;
use App\Models\ProdukModel;
use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\ControllerTestTrait;
use CodeIgniter\HTTP\IncomingRequest;
use CodeIgniter\HTTP\RedirectResponse;

class ProdukAdminTest extends CIUnitTestCase
{
    use ControllerTestTrait;

    protected $controller;
    protected $produkModelMock;
    protected $dummyProduct;

    protected function setUp(): void
{
    parent::setUp();

    // ---------------- Dummy Product ----------------
    $this->dummyProduct = [
        'id_produk' => 99,
        'nama_produk' => 'Test Produk Dummy Mocked',
        'deskripsi' => 'Deskripsi dummy',
        'harga' => 10000,
        'stok' => 5,
        'foto' => 'default.jpg'
    ];

    // ---------------- Mock ProdukModel ----------------
    $this->produkModelMock = $this->getMockBuilder(\App\Models\ProdukModel::class)
                                  ->onlyMethods(['findAll','find','insert','update','delete'])
                                  ->getMock();

    // ---------------- Mock UserModel ----------------
    $this->userModelMock = $this->getMockBuilder(\App\Models\UserModel::class)
                                ->onlyMethods(['find'])
                                ->getMock();
    // Return dummy user ketika dipanggil find()
    $this->userModelMock->method('find')->willReturn([
        'id_user' => 1,
        'username' => 'testuser',
        'email' => 'test@example.com'
    ]);

    // ---------------- Buat instance controller ----------------
    $this->controller = new \App\Controllers\ProdukAdmin();

    // ---------------- Inject mock ProdukModel ----------------
    $reflection = new \ReflectionClass($this->controller);

    $produkProp = $reflection->getProperty('produkModel');
    $produkProp->setAccessible(true);
    $produkProp->setValue($this->controller, $this->produkModelMock);

    // ---------------- Inject mock UserModel ----------------
    if ($reflection->hasProperty('userModel')) {
        $userProp = $reflection->getProperty('userModel');
        $userProp->setAccessible(true);
        $userProp->setValue($this->controller, $this->userModelMock);
    }

    // ---------------- Mock Request ----------------
    $mockRequest = $this->getMockBuilder(\CodeIgniter\HTTP\IncomingRequest::class)
                        ->disableOriginalConstructor()
                        ->onlyMethods(['getMethod','getPost','isAJAX','isCLI'])
                        ->getMock();

    $mockRequest->method('isAJAX')->willReturn(false);
    $mockRequest->method('isCLI')->willReturn(false);

    $requestProp = $reflection->getProperty('request');
    $requestProp->setAccessible(true);
    $requestProp->setValue($this->controller, $mockRequest);

    // ---------------- Setup session dummy ----------------
    $session = \Config\Services::session();
    $session->set('id_user', 1);
    $session->set('isLoggedIn', true);
}

    /** ---------------- INDEX ---------------- */
    public function testIndex(): void
    {
        $this->produkModelMock->method('findAll')->willReturn([$this->dummyProduct]);

        $result = $this->controller->index();

        $this->assertIsString($result);
        $this->assertStringContainsString('Manajemen Produk', $result);
        $this->assertStringContainsString($this->dummyProduct['nama_produk'], $result);
    }

    /** ---------------- CREATE ---------------- */
    public function testCreate(): void
    {
        $result = $this->controller->create();

        $this->assertIsString($result);
        $this->assertStringContainsString('Tambah Produk', $result);
    }

    /** ---------------- STORE ---------------- */
    public function testStoreSuccess(): void
    {
        $postData = [
            'nama_produk' => 'Produk Baru',
            'deskripsi' => 'Deskripsi baru',
            'harga' => 25000,
            'stok' => 10
        ];

        // Mock insert
        $this->produkModelMock->method('insert')->willReturn(true);

        // Setup mock Request
        $reflection = new \ReflectionClass($this->controller);
        $requestProp = $reflection->getProperty('request');
        $requestProp->setAccessible(true);
        $mockRequest = $requestProp->getValue($this->controller);
        $mockRequest->method('getMethod')->willReturn('post');
        $mockRequest->method('getPost')->willReturn($postData);

        $result = $this->controller->store();

        $this->assertInstanceOf(RedirectResponse::class, $result);
        $this->assertEquals(302, $result->getStatusCode());
    }

    /** ---------------- EDIT ---------------- */
    public function testEdit(): void
    {
        $id = $this->dummyProduct['id_produk'];
        $this->produkModelMock->method('find')->with($id)->willReturn($this->dummyProduct);

        $result = $this->controller->edit($id);

        $this->assertIsString($result);
        $this->assertStringContainsString('Edit Produk', $result);
        $this->assertStringContainsString($this->dummyProduct['nama_produk'], $result);
    }

    /** ---------------- UPDATE ---------------- */
    public function testUpdateSuccess(): void
    {
        $id = $this->dummyProduct['id_produk'];
        $updatedData = [
            'nama_produk' => 'Produk Updated',
            'deskripsi' => $this->dummyProduct['deskripsi'],
            'harga' => $this->dummyProduct['harga'],
            'stok' => $this->dummyProduct['stok']
        ];

        $this->produkModelMock->method('find')->with($id)->willReturn($this->dummyProduct);
        $this->produkModelMock->method('update')->with($id, $this->anything())->willReturn(true);

        // Setup mock Request
        $reflection = new \ReflectionClass($this->controller);
        $requestProp = $reflection->getProperty('request');
        $requestProp->setAccessible(true);
        $mockRequest = $requestProp->getValue($this->controller);
        $mockRequest->method('getMethod')->willReturn('post');
        $mockRequest->method('getPost')->willReturn($updatedData);

        $result = $this->controller->update($id);

        $this->assertInstanceOf(RedirectResponse::class, $result);
        $this->assertEquals(302, $result->getStatusCode());
    }

    /** ---------------- DELETE ---------------- */
    public function testDeleteSuccess(): void
    {
        $id = $this->dummyProduct['id_produk'];
        $this->produkModelMock->method('delete')->with($id)->willReturn(true);

        $result = $this->controller->delete($id);

        $this->assertInstanceOf(RedirectResponse::class, $result);
        $this->assertEquals(302, $result->getStatusCode());
    }
}
