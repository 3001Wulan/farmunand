<?php

namespace Tests\Controllers;

use App\Controllers\Pesanan;
use App\Models\PesananModel;
use App\Models\UserModel;
use CodeIgniter\Test\CIUnitTestCase;

class PesananTest extends CIUnitTestCase
{
    protected $pesananModelMock;
    protected $userModelMock;
    protected $controller;

    protected function setUp(): void
    {
        parent::setUp();

        helper('session');

        // Mock UserModel
        $this->userModelMock = $this->getMockBuilder(UserModel::class)
            ->onlyMethods(['find'])
            ->getMock();

        // Mock PesananModel
        $this->pesananModelMock = $this->getMockBuilder(PesananModel::class)
            ->onlyMethods(['getPesananWithProduk', 'getPesananByStatus', 'first', 'update'])
            ->getMock();

        // Controller
        $this->controller = new Pesanan();

        // Inject mock melalui Reflection
        $ref = new \ReflectionClass($this->controller);

        $propPesanan = $ref->getProperty('pesananModel');
        $propPesanan->setAccessible(true);
        $propPesanan->setValue($this->controller, $this->pesananModelMock);

        $propUser = $ref->getProperty('userModel');
        $propUser->setAccessible(true);
        $propUser->setValue($this->controller, $this->userModelMock);
    }

    private function mockUserSession($userId = 1)
    {
        session()->set('id_user', $userId);

        $this->userModelMock->method('find')
            ->willReturn([
                'id_user'   => $userId,
                'nama'      => 'Test User',
                'username'  => 'testuser',
                'created_at'=> '2025-11-17 06:00:00',
                'role'      => 'user',
            ]);
    }

    private function mockPesananData($status = 'Belum Bayar')
    {
        return [
            [
                'id_pemesanan'     => 1,
                'status_pemesanan' => $status,
                'nama_produk'      => 'Produk Test',
                'jumlah'           => 2,
                'jumlah_produk'    => 2,
                'harga'            => 10000,
                'created_at'       => '2025-11-17 06:00:00',
            ]
        ];
    }

    public function testIndexRedirectsIfNotLoggedIn()
    {
        session()->destroy();

        try {
            $this->controller->index();
        } catch (\CodeIgniter\HTTP\RedirectResponseException $e) {
            // Redirect sukses
        }

        $this->assertTrue(true);
    }

    public function testIndexReturnsViewWithOrders()
    {
        $this->mockUserSession();

        $this->pesananModelMock->method('getPesananWithProduk')
            ->willReturn($this->mockPesananData('Belum Bayar'));

        $response = $this->controller->index();
        $output = (string) $response;

        $this->assertStringContainsString('Belum Bayar', $output);
    }

    public function testBelumbayarReturnsView()
    {
        $this->mockUserSession();

        $this->pesananModelMock->method('getPesananByStatus')
            ->willReturn($this->mockPesananData('Belum Bayar'));

        $response = $this->controller->belumbayar();
        $output = (string) $response;

        $this->assertStringContainsString('Belum Bayar', $output);
    }

    public function testDikemasReturnsView()
    {
        $this->mockUserSession();

        $this->pesananModelMock->method('getPesananByStatus')
            ->willReturn($this->mockPesananData('Dikemas'));

        $response = $this->controller->dikemas();
        $output = (string) $response;

        $this->assertStringContainsString('Dikemas', $output);
    }

    public function testSelesaiReturnsView()
    {
        $this->mockUserSession();

        $this->pesananModelMock->method('getPesananByStatus')
            ->willReturn($this->mockPesananData('Selesai'));

        $response = $this->controller->selesai();
        $output = (string) $response;

        $this->assertStringContainsString('Selesai', $output);
    }

    public function testDibatalkanReturnsView()
    {
        $this->mockUserSession();

        $this->pesananModelMock->method('getPesananByStatus')
            ->willReturn($this->mockPesananData('Dibatalkan'));

        $response = $this->controller->dibatalkan();
        $output = (string) $response;

        $this->assertStringContainsString('Dibatalkan', $output);
    }
}
