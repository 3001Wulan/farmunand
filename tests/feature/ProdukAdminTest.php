<?php

namespace Tests\Controllers;

use App\Controllers\ProdukAdmin;
use App\Models\ProdukModel;
use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\ControllerTestTrait;
use CodeIgniter\HTTP\IncomingRequest;
use CodeIgniter\HTTP\RedirectResponse;
use CodeIgniter\HTTP\ResponseInterface;

class ProdukAdminTest extends CIUnitTestCase
{
    use ControllerTestTrait;

    protected $produkModelMock;
    protected $controller;
    protected $dummyProduct;
    protected $realRequest; 

    protected function setUp(): void
    {
        parent::setUp();
        
        // Load helpers
        helper(['form', 'url', 'session']);

        // Set session user yang sudah login
        session()->set('id_user', 1);
        session()->set('isLoggedIn', true);

        // Data dummy produk untuk mocking
        $this->dummyProduct = [
            'id_produk' => 99,
            'nama_produk' => 'Test Produk Dummy Mocked',
            'deskripsi' => 'Deskripsi dummy',
            'harga' => 10000,
            'stok' => 5,
            'foto' => 'default.jpg' // FIX: Tambahkan key 'foto' untuk View
        ];

        // 1. Mock ProdukModel
        $this->produkModelMock = $this->getMockBuilder(ProdukModel::class)
                                       ->onlyMethods(['findAll', 'find', 'insert', 'update', 'delete'])
                                       ->getMock();

        // 2. Buat instance Controller 
        $this->controller = new ProdukAdmin();
        
        // 3. Simpan real Request object untuk digunakan di tearDown
        $this->realRequest = service('request');

        // 4. Inject mock ProdukModel menggunakan Reflection
        $reflection = new \ReflectionClass($this->controller);
        $property   = $reflection->getProperty('produkModel'); 
        $property->setAccessible(true);
        $property->setValue($this->controller, $this->produkModelMock);
        
        // 5. Inject real Request object ke controller secara default
        $requestProperty = $reflection->getProperty('request');
        $requestProperty->setAccessible(true);
        $requestProperty->setValue($this->controller, $this->realRequest);
    }

    protected function tearDown(): void
    {
        parent::tearDown();
        // Bersihkan data POST global setelah setiap tes
        $this->realRequest->setGlobal('post', []); 
        $this->realRequest->setMethod('get'); 
    }
    
    /** ---------------------- INDEX ---------------------- */

    public function testIndex(): void
    {
        // Tentukan perilaku Mock: findAll akan mengembalikan array dummy
        $this->produkModelMock->expects($this->once())
                              ->method('findAll')
                              ->willReturn([$this->dummyProduct]);

        // MENGGUNAKAN EXECUTE() (standar untuk GET)
        $result = $this->controller(\App\Controllers\ProdukAdmin::class)
                       ->execute('index');

        $this->assertIsString($result->getBody());
        $this->assertStringContainsString('Manajemen Produk', $result->getBody());
        $this->assertStringContainsString($this->dummyProduct['nama_produk'], $result->getBody());
    }


    /** ---------------------- CREATE ---------------------- */

    public function testCreate(): void
    {
        // MENGGUNAKAN EXECUTE() (standar untuk GET)
        $result = $this->controller(\App\Controllers\ProdukAdmin::class)
                       ->execute('create');

        $this->assertStringContainsString('Tambah Produk', $result->getBody());
    }


    /** ---------------------- STORE (POST) ---------------------- */

    public function testStoreSuccess(): void
    {
        // 1. Tentukan perilaku Mock Model
        $this->produkModelMock->expects($this->once())
                              ->method('insert')
                              ->willReturn(true);

        $postData = [
            'nama_produk' => 'Test Produk Baru',
            'deskripsi'   => 'Deskripsi produk baru',
            'harga'       => 25000,
            'stok'        => 10
        ];

        // 2. FIX KRITIS: Mock Request object, tambahkan isCLI & isAJAX
        $mockRequest = $this->getMockBuilder(IncomingRequest::class)
                            ->disableOriginalConstructor()
                            ->onlyMethods(['getMethod', 'getPost', 'isCLI', 'isAJAX'])
                            ->getMock();

        // Atur perilaku tambahan untuk stabilitas framework
        $mockRequest->expects($this->any())->method('isCLI')->willReturn(false);
        $mockRequest->expects($this->any())->method('isAJAX')->willReturn(false);

        // Atur perilaku yang diharapkan untuk test ini
        $mockRequest->expects($this->once())->method('getMethod')->willReturn('post');
        $mockRequest->expects($this->once())->method('getPost')->willReturn($postData);

        // 3. Inject Mock Request ke Controller
        $reflection = new \ReflectionClass($this->controller);
        $requestProperty = $reflection->getProperty('request');
        $requestProperty->setAccessible(true);
        $requestProperty->setValue($this->controller, $mockRequest);

        // 4. PANGGIL METHOD CONTROLLER SECARA LANGSUNG
        $result = $this->controller->store();
        
        $this->assertInstanceOf(RedirectResponse::class, $result);
        $this->assertEquals(302, $result->getStatusCode());
    }


    /** ---------------------- EDIT ---------------------- */

    public function testEdit(): void
    {
        $id = $this->dummyProduct['id_produk'];

        // Tentukan perilaku Mock: 'find' akan dipanggil sekali dan mengembalikan produk dummy
        $this->produkModelMock->expects($this->once())
                              ->method('find')
                              ->with($id)
                              ->willReturn($this->dummyProduct);

        // PANGGIL METHOD CONTROLLER SECARA LANGSUNG
        $result = $this->controller->edit($id);

        // Karena kita memanggil langsung, hasilnya adalah string view
        $this->assertIsString($result);
        $this->assertStringContainsString('Edit Produk', $result);
        $this->assertStringContainsString($this->dummyProduct['nama_produk'], $result);
    }


    /** ---------------------- UPDATE (POST) ---------------------- */

    public function testUpdateSuccess(): void
    {
        $id = $this->dummyProduct['id_produk'];

        // 1. Tentukan perilaku Mock Model
        // Mock find() untuk cek keberadaan produk
        $this->produkModelMock->expects($this->once())
                              ->method('find')
                              ->with($id)
                              ->willReturn($this->dummyProduct);
                              
        $this->produkModelMock->expects($this->once())
                              ->method('update')
                              ->with($id, $this->anything())
                              ->willReturn(true);

        $postData = [
            'nama_produk' => $this->dummyProduct['nama_produk'] . ' Updated',
            'deskripsi'   => $this->dummyProduct['deskripsi'],
            'harga'       => $this->dummyProduct['harga'],
            'stok'        => $this->dummyProduct['stok']
        ];

        // 2. FIX KRITIS: Mock Request object, tambahkan isCLI & isAJAX
        $mockRequest = $this->getMockBuilder(IncomingRequest::class)
                            ->disableOriginalConstructor()
                            ->onlyMethods(['getMethod', 'getPost', 'isCLI', 'isAJAX'])
                            ->getMock();

        // Atur perilaku tambahan untuk stabilitas framework
        $mockRequest->expects($this->any())->method('isCLI')->willReturn(false);
        $mockRequest->expects($this->any())->method('isAJAX')->willReturn(false);

        // Atur perilaku yang diharapkan untuk test ini
        $mockRequest->expects($this->once())->method('getMethod')->willReturn('post');
        $mockRequest->expects($this->once())->method('getPost')->willReturn($postData); 

        // 3. Inject Mock Request ke Controller
        $reflection = new \ReflectionClass($this->controller);
        $requestProperty = $reflection->getProperty('request');
        $requestProperty->setAccessible(true);
        $requestProperty->setValue($this->controller, $mockRequest);
        
        // 4. PANGGIL METHOD CONTROLLER SECARA LANGSUNG
        $result = $this->controller->update($id);
        
        $this->assertInstanceOf(RedirectResponse::class, $result);
        $this->assertEquals(302, $result->getStatusCode());
    }


    /** ---------------------- DELETE ---------------------- */

    public function testDeleteSuccess(): void
    {
        $id = $this->dummyProduct['id_produk'];
        
        // Tentukan perilaku Mock: 'delete' akan dipanggil sekali dan berhasil
        $this->produkModelMock->expects($this->once())
                              ->method('delete')
                              ->with($id)
                              ->willReturn(true);

        // PANGGIL METHOD CONTROLLER SECARA LANGSUNG
        $result = $this->controller->delete($id);

        $this->assertInstanceOf(RedirectResponse::class, $result);
        $this->assertEquals(302, $result->getStatusCode());
    }
}