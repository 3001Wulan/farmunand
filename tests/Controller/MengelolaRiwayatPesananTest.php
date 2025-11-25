<?php

namespace Tests\Feature;

use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\HTTP\IncomingRequest;
use App\Controllers\MengelolaRiwayatPesanan;
use App\Models\PesananModel;
use App\Models\UserModel;
use CodeIgniter\Database\ResultInterface;
use CodeIgniter\Database\BaseBuilder;

class MengelolaRiwayatPesananTest extends CIUnitTestCase
{
    private $controller;
    private $pesananModelMock;
    private $userModelMock;
    private $requestMock;
    private $builderMock;
    private $resultMock;

    protected function setUp(): void
    {
        parent::setUp();

        // ---------------------------
        // 1️⃣ Mock ResultInterface
        // ---------------------------
        $this->resultMock = $this->getMockBuilder(ResultInterface::class)
            ->getMockForAbstractClass();
        $this->resultMock->method('getResultArray')->willReturn([
            [
                'id_pemesanan' => 1,
                'status_pemesanan' => 'Dikirim',
                'created_at' => '2025-11-17',
                'id_user' => 1,
                'nama_user' => 'User A',
                'nama_produk' => 'Produk A',
                'jumlah_produk' => 2,
                'harga_produk' => 10000
            ]
        ]);

        // ---------------------------
        // 2️⃣ Mock Query Builder
        // ---------------------------
        $this->builderMock = $this->getMockBuilder(BaseBuilder::class)
            ->disableOriginalConstructor()
            ->onlyMethods([
                'select','join','where','groupStart','orLike','groupEnd','orderBy','get','set','update'
            ])
            ->getMock();

        $this->builderMock->method('select')->willReturnSelf();
        $this->builderMock->method('join')->willReturnSelf();
        $this->builderMock->method('where')->willReturnSelf();
        $this->builderMock->method('groupStart')->willReturnSelf();
        $this->builderMock->method('orLike')->willReturnSelf();
        $this->builderMock->method('groupEnd')->willReturnSelf();
        $this->builderMock->method('orderBy')->willReturnSelf();
        $this->builderMock->method('get')->willReturn($this->resultMock);
        $this->builderMock->method('set')->willReturnSelf();
        $this->builderMock->method('update')->willReturn(true);

        // ---------------------------
        // 3️⃣ Mock PesananModel
        // ---------------------------
        $this->pesananModelMock = $this->getMockBuilder(PesananModel::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['builder','find','update'])
            ->getMock();
        $this->pesananModelMock->method('builder')->willReturn($this->builderMock);
        $this->pesananModelMock->method('find')->willReturn([
            'id_pemesanan' => 1,
            'status_pemesanan' => 'Dikirim'
        ]);
        $this->pesananModelMock->method('update')->willReturn(true);

        // ---------------------------
        // 4️⃣ Mock UserModel
        // ---------------------------
        $this->userModelMock = $this->getMockBuilder(UserModel::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['find'])
            ->getMock();
        $this->userModelMock->method('find')->willReturn([
            'id_user' => 1,
            'nama' => 'User A'
        ]);

        // ---------------------------
        // 5️⃣ Mock Request
        // ---------------------------
        $this->requestMock = $this->getMockBuilder(IncomingRequest::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getGet','getPost'])
            ->getMock();
        $this->requestMock->method('getGet')->willReturnMap([
            ['status',''],
            ['keyword',''],
            ['sort','DESC']
        ]);

        // NOTE: gunakan getPost dengan string langsung
        $this->requestMock->method('getPost')->willReturnMap([
            ['status_pemesanan', 'Dikemas']
        ]);

        // ---------------------------
        // 6️⃣ Controller
        // ---------------------------
        $this->controller = new MengelolaRiwayatPesanan();

        // Inject mock model via Reflection sebelum dipakai
        $this->injectPrivateProperty($this->controller, 'pesananModel', $this->pesananModelMock);
        $this->injectPrivateProperty($this->controller, 'userModel', $this->userModelMock);

        // Inject request
        $this->controller->setRequest($this->requestMock);
    }

    private function injectPrivateProperty($object, string $property, $value)
    {
        $ref = new \ReflectionClass($object);
        if ($ref->hasProperty($property)) {
            $prop = $ref->getProperty($property);
            $prop->setAccessible(true);
            $prop->setValue($object, $value);
        }
    }

    /** ============================
     *  Test: Index returns view
     * ============================ */
    public function testIndexReturnsView()
    {
        $output = $this->controller->index();

        $this->assertIsString($output);
        $this->assertStringContainsString('User A', $output);
        $this->assertStringContainsString('Produk A', $output);
    }

    /** ============================
     *  Test: Update status valid
     * ============================ */
    public function testUpdateStatusValid()
    {
        $this->requestMock->method('getPost')->willReturnMap([
            ['status_pemesanan', 'Dikemas']
        ]);

        $output = $this->controller->updateStatus(1);
        $this->assertInstanceOf(\CodeIgniter\HTTP\RedirectResponse::class, $output);
    }

    /** ============================
     *  Test: Update status invalid
     * ============================ */
    public function testUpdateStatusInvalid()
    {
        $this->requestMock->method('getPost')->willReturnMap([
            ['status_pemesanan', 'Dikirim']
        ]);

        $output = $this->controller->updateStatus(1);
        $this->assertInstanceOf(\CodeIgniter\HTTP\RedirectResponse::class, $output);
    }
}
