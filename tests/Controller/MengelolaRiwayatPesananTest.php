<?php

namespace Tests\Feature;

use App\Controllers\MengelolaRiwayatPesanan;
use App\Models\PesananModel;
use App\Models\UserModel;
use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\HTTP\IncomingRequest;
use CodeIgniter\HTTP\URI;
use CodeIgniter\HTTP\UserAgent;
use Config\App;
use Config\UserAgents;
use ReflectionClass;

class MengelolaRiwayatPesananTest extends CIUnitTestCase
{
    protected $controller;
    protected $pesananModelMock;
    protected $userModelMock;

    protected function setUp(): void
    {
        parent::setUp();

        // Mock models
        $this->pesananModelMock = $this->createMock(PesananModel::class);
        $this->userModelMock    = $this->createMock(UserModel::class);

        // Buat controller
        $this->controller = new MengelolaRiwayatPesanan();

        // Inject protected properties via Reflection
        $this->setProtectedProperty($this->controller, 'pesananModel', $this->pesananModelMock);
        $this->setProtectedProperty($this->controller, 'userModel', $this->userModelMock);

        // Setup request
        $appConfig = config(App::class);
        $uri       = new URI('http://localhost/');
        $userAgent = new UserAgent(new UserAgents());

        $globals = [
            '_POST'   => $_POST,
            '_GET'    => $_GET,
            '_COOKIE' => $_COOKIE,
            '_FILES'  => $_FILES,
            '_SERVER' => $_SERVER,
        ];

        $request = new IncomingRequest($appConfig, $uri, $userAgent, $globals);

        $this->setProtectedProperty($this->controller, 'request', $request);

        // Setup session mock
        $session = \Config\Services::session();
        $this->setProtectedProperty($this->controller, 'session', $session);
    }

    // Helper: set protected property via Reflection
    protected function setProtectedProperty($object, string $property, $value)
    {
        $ref = new ReflectionClass($object);
        $prop = $ref->getProperty($property);
        $prop->setAccessible(true);
        $prop->setValue($object, $value);
    }

    public function testIndexReturnsView()
    {
        // Mock data pesanan
        $this->pesananModelMock->method('select')->willReturnSelf();
        $this->pesananModelMock->method('join')->willReturnSelf();
        $this->pesananModelMock->method('where')->willReturnSelf();
        $this->pesananModelMock->method('groupStart')->willReturnSelf();
        $this->pesananModelMock->method('groupEnd')->willReturnSelf();
        $this->pesananModelMock->method('like')->willReturnSelf();
        $this->pesananModelMock->method('orLike')->willReturnSelf();
        $this->pesananModelMock->method('orderBy')->willReturnSelf();
        $this->pesananModelMock->method('get')->willReturnSelf();
        $this->pesananModelMock->method('getResultArray')->willReturn([
            ['id_pemesanan'=>1, 'status_pemesanan'=>'Dikirim', 'created_at'=>'2025-11-17', 'nama_user'=>'User A', 'nama_produk'=>'Produk A','jumlah_produk'=>2,'harga_produk'=>10000]
        ]);

        // Mock user
        $this->userModelMock->method('find')->willReturn([
            'id_user' => 1,
            'nama'    => 'User A'
        ]);

        // Set session user
        session()->set('id_user', 1);

        $output = $this->controller->index();

        $this->assertStringContainsString('User A', $output);
        $this->assertStringContainsString('Produk A', $output);
    }

    public function testUpdateStatusValid()
    {
        // Mock post
        $_POST['status_pemesanan'] = 'Dikemas';

        // Mock find() untuk pesanan
        $this->pesananModelMock->method('select')->willReturnSelf();
        $this->pesananModelMock->method('find')->willReturn([
            'status_pemesanan' => 'Dikemas'
        ]);

        $response = $this->controller->updateStatus(1);

        $this->assertNotNull($response);
    }

    public function testUpdateStatusInvalid()
    {
        // Mock post invalid
        $_POST['status_pemesanan'] = 'Selesai';

        // Mock find() untuk pesanan
        $this->pesananModelMock->method('select')->willReturnSelf();
        $this->pesananModelMock->method('find')->willReturn([
            'status_pemesanan' => 'Belum Bayar'
        ]);

        $response = $this->controller->updateStatus(1);

        $this->assertNotNull($response);
    }
}
