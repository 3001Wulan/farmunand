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
use CodeIgniter\Test\Mock\MockServices;

/**
 * Unit Test untuk MelakukanPemesanan Controller.
 * Menggunakan mocking penuh pada Model dan Reflection untuk injeksi.
 */
class MelakukanPemesananTest extends CIUnitTestCase 
{
    protected $request;
    protected $response;

    protected function setUp(): void
    {
        parent::setUp();

        // Siapkan session user login yang diperlukan oleh Controller
        $_SESSION['id_user'] = 1;
        
        $config = new App();
        
        // Buat objek URI yang valid.
        $uri = new URI('http://example.com/pemesanan/simpan');

        // Buat objek Request yang valid
        $this->request = new IncomingRequest(
            $config,
            $uri, 
            'php://input', // Body, default
            new UserAgent()
        );
        
        // Inisialisasi Response object
        $this->response = new Response($config);
    }

    protected function tearDown(): void
    {
        parent::tearDown();
        // Bersihkan session
        $_SESSION = [];
    }
    
    /**
     * Helper untuk membuat Request Mock yang mengembalikan data POST
     */
    private function getRequestMock($postData)
    {
        // Mocking IncomingRequest untuk mengontrol getPost()
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
                    
        // Inisialisasi properti dasar yang diperlukan Controller
        $config = new App();
        $uri = new URI('http://example.com/pemesanan/simpan');
        $this->setPrivate($requestMock, 'config', $config);
        $this->setPrivate($requestMock, 'uri', $uri);
        $this->setPrivate($requestMock, 'userAgent', new UserAgent());
        $this->setPrivate($requestMock, 'method', 'post');
        
        return $requestMock;
    }


    /**
     * Helper untuk inject property protected/private
     */
    private function setPrivate($obj, $prop, $value)
    {
        $ref = new ReflectionClass($obj);
        $property = $ref->getProperty($prop);
        $property->setAccessible(true);
        $property->setValue($obj, $value);
    }

    /**
     * Helper membuat controller dengan mock model
     */
    private function getController($produkMock, $alamatMock, $userMock, $requestObj)
    {
        $controller = new MelakukanPemesanan();

        // Injeksi Mock Model ke Controller
        $this->setPrivate($controller, 'produkModel', $produkMock);
        $this->setPrivate($controller, 'alamatModel', $alamatMock);
        $this->setPrivate($controller, 'userModel', $userMock);
        
        // Injeksi objek request
        $this->setPrivate($controller, 'request', $requestObj);

        // Injeksi Response object
        $this->setPrivate($controller, 'response', $this->response); 

        return $controller;
    }

    /** * Helper untuk Mock AlamatModel (NON-CHAINING)
     */
    private function getAlamatMock($isIndexTest = false)
    {
        $alamatMock = $this->getMockBuilder(AlamatModel::class)
                            ->disableOriginalConstructor()
                            ->onlyMethods(['find', 'first']) 
                            ->getMock(); 
        
        $alamatData = ['id_alamat' => 1, 'id_user' => 1];
        
        // Mock find()
        $alamatMock->method('find')->willReturn($alamatData); 
        // Mock first()
        $alamatMock->method('first')->willReturn($alamatData);
        
        return $alamatMock;
    }

    /** -----------------------------------------
     * 1. Test Index Single Item (Perbaikan TypeError)
     * ----------------------------------------*/
    public function testIndexSingleItem()
    {
        $produkMock = $this->createMock(ProdukModel::class);
        $produkMock->method('find')->with(1)->willReturn([
            'id_produk' => 1,
            'nama_produk' => 'Produk Test',
            'stok' => 10,
            'harga' => 10000
        ]);

        $alamatMock = $this->getAlamatMock(true); // Memastikan find dan first di-mock

        $userMock = $this->getMockBuilder(UserModel::class)
                            ->disableOriginalConstructor()
                            ->onlyMethods(['find', 'first'])
                            ->getMock();
                            
        $userData = ['id_user' => 1, 'nama' => 'Test User'];
        // FIX: Tambahkan with() opsional untuk mencakup find() tanpa parameter (find() atau find(null))
        $userMock->method('find')->willReturn($userData);
        $userMock->method('first')->willReturn($userData);
        
        // Gunakan request asli untuk Index
        $controller = $this->getController($produkMock, $alamatMock, $userMock, $this->request);

        $result = $controller->index(1); 

        $this->assertIsArray($result);
        $this->assertArrayHasKey('produk', $result);
        $this->assertEquals(10000, $result['produk']['harga']);
    }

    /** -----------------------------------------
     * 2. Test Simpan Single Item Success (Perbaikan Expectation Failed)
     * ----------------------------------------*/
    public function testSimpanSingleItemSuccess()
    {
        $postData = [
            'produk_id' => 1,
            'qty' => 2 
        ];
        
        // FIX: Gunakan Mock Request yang menjamin data POST terbaca
        $requestMock = $this->getRequestMock($postData); 
        
        $produkMock = $this->createMock(ProdukModel::class);
        
        $produkMock->method('find')->with(1)->willReturn([
            'id_produk' => 1,
            'stok' => 10,
            'harga' => 10000
        ]);
        
        // Ekspektasi: Model::update dipanggil satu kali
        $produkMock->expects($this->once())
                    ->method('update')
                    ->with(1, $this->equalTo(['stok' => 8]))
                    ->willReturn(true); 
        
        $alamatMock = $this->getAlamatMock();
        $userMock = $this->createMock(UserModel::class);

        // Gunakan Mock Request yang baru
        $controller = $this->getController($produkMock, $alamatMock, $userMock, $requestMock); 

        $result = $controller->simpan();

        $this->assertNotNull($result);
    }

    /** -----------------------------------------
     * 3. Test Simpan Gagal Qty > Stok
     * ----------------------------------------*/
    public function testSimpanFailsWhenQtyExceedsStock()
    {
        $postData = [
            'produk_id' => 1,
            'qty' => 999 
        ];
        
        $requestMock = $this->getRequestMock($postData);

        $produkMock = $this->createMock(ProdukModel::class);
        $produkMock->method('find')->willReturn([
            'id_produk' => 1,
            'stok' => 5,
            'harga' => 10000
        ]);
        
        $produkMock->expects($this->never())->method('update');

        $alamatMock = $this->getAlamatMock();
        $userMock = $this->createMock(UserModel::class);

        $controller = $this->getController($produkMock, $alamatMock, $userMock, $requestMock);

        $result = $controller->simpan();

        $this->assertNotNull($result);
    }

    /** -----------------------------------------
     * 4. Test Simpan Batch Success (Perbaikan Expectation Failed)
     * ----------------------------------------*/
    public function testSimpanBatchSuccess()
    {
        $postData = [
            'items' => [
                ['produk_id' => 1, 'qty' => 2],
                ['produk_id' => 2, 'qty' => 1]
            ]
        ];
        
        // FIX: Gunakan Mock Request yang menjamin data POST terbaca
        $requestMock = $this->getRequestMock($postData);

        $produkMock = $this->createMock(ProdukModel::class);
        
        $produkMock->method('find')
            ->willReturnCallback(function ($id) {
                return [
                    1 => ['id_produk' => 1, 'stok' => 10, 'harga' => 10000],
                    2 => ['id_produk' => 2, 'stok' => 5, 'harga' => 20000]
                ][$id] ?? null;
            });
            
        // Ekspektasi: Update Produk ID 1
        $produkMock->expects($this->atLeastOnce())
                    ->method('update')
                    ->with(1, $this->equalTo(['stok' => 8]))
                    ->willReturn(true);
                    
        // Ekspektasi: Update Produk ID 2
        $produkMock->expects($this->atLeastOnce())
                    ->method('update')
                    ->with(2, $this->equalTo(['stok' => 4]))
                    ->willReturn(true);

        $alamatMock = $this->getAlamatMock();
        $userMock = $this->createMock(UserModel::class);
            
        // Gunakan Mock Request yang baru
        $controller = $this->getController($produkMock, $alamatMock, $userMock, $requestMock);
        
        $controller->simpan();
    }
    
    /** -----------------------------------------
     * 5. Test Batch Gagal Qty > Stok
     * ----------------------------------------*/
    public function testSimpanBatchFailsWhenQtyExceedsStock()
    {
        $postData = [
            'items' => [
                ['produk_id' => 1, 'qty' => 999]
            ]
        ];
        
        $requestMock = $this->getRequestMock($postData);

        $produkMock = $this->createMock(ProdukModel::class);
        $produkMock->method('find')->willReturn([
            'id_produk' => 1,
            'stok' => 5,
            'harga' => 10000
        ]);
        
        $produkMock->expects($this->never())->method('update');

        $alamatMock = $this->getAlamatMock();
        $userMock = $this->createMock(UserModel::class);

        $controller = $this->getController($produkMock, $alamatMock, $userMock, $requestMock);

        $result = $controller->simpan();

        $this->assertNotNull($result);
    }
}