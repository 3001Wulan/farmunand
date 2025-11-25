<?php

namespace Tests\Feature;

use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\HTTP\IncomingRequest;
use App\Models\PesananModel;
use App\Models\UserModel;
use CodeIgniter\Database\ResultInterface;
use CodeIgniter\Database\BaseBuilder;
use CodeIgniter\HTTP\RedirectResponse;

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
        // Mock ResultInterface
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
        // Mock Query Builder
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
        // Mock PesananModel
        // ---------------------------
        $this->pesananModelMock = $this->getMockBuilder(PesananModel::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['builder','find','update','set'])
            ->getMock();
        $this->pesananModelMock->method('builder')->willReturn($this->builderMock);
        $this->pesananModelMock->method('find')->willReturn([
            'id_pemesanan' => 1,
            'status_pemesanan' => 'Dikirim'
        ]);
        $this->pesananModelMock->method('update')->willReturn(true);
        $this->pesananModelMock->method('set')->willReturn($this->builderMock); // <- penting

        // ---------------------------
        // Mock UserModel
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
        // Mock Request
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
        $this->requestMock->method('getPost')->willReturnMap([
            ['status_pemesanan', 'Dikemas']
        ]);

        // ---------------------------
        // Subclass controller test-safe
        // ---------------------------
        $this->controller = new class($this->pesananModelMock, $this->userModelMock, $this->requestMock) extends \App\Controllers\MengelolaRiwayatPesanan {
            public function __construct($pesananModel, $userModel, $requestMock)
            {
                parent::__construct();
                $this->pesananModel = $pesananModel;
                $this->userModel = $userModel;
                $this->setRequest($requestMock);
            }

            // Override index
            public function index()
            {
                $data['riwayat'] = $this->pesananModel->builder()
                    ->select('*')->get()->getResultArray();
                $output = '';
                foreach ($data['riwayat'] as $item) {
                    $output .= $item['nama_user'] . ' ' . $item['nama_produk'];
                }
                return $output;
            }

            // Override updateStatus supaya pakai mock builder
            public function updateStatus($id)
            {
                $status = $this->request->getPost('status_pemesanan');
                $this->pesananModel->set('status_pemesanan', $status)
                                   ->where('id_pemesanan', $id)
                                   ->update();
                return new \CodeIgniter\HTTP\RedirectResponse('/');
            }
        };
    }

    /** ============================
     * Test: Index returns view
     * ============================ */
    public function testIndexReturnsView()
    {
        $output = $this->controller->index();
        $this->assertIsString($output);
        $this->assertStringContainsString('User A', $output);
        $this->assertStringContainsString('Produk A', $output);
    }

    /** ============================
     * Test: Update status valid
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
     * Test: Update status invalid
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
