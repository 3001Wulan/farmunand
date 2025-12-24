<?php

namespace Tests\Controllers;

use App\Controllers\Pesanan;
use App\Models\PesananModel;
use App\Models\UserModel;
use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\HTTP\RedirectResponse;

class PesananTest extends CIUnitTestCase
{
    protected $pesananModelMock;
    protected $userModelMock;
    protected $controller;

    protected function setUp(): void
    {
        parent::setUp();

        helper('session');

        $this->userModelMock = $this->getMockBuilder(UserModel::class)
            ->onlyMethods(['find'])
            ->getMock();

        $this->pesananModelMock = $this->getMockBuilder(PesananModel::class)
            ->onlyMethods(['getPesananWithProduk', 'getPesananByStatus', 'first', 'update'])
            ->getMock();

        $this->controller = new Pesanan();

        $ref = new \ReflectionClass($this->controller);

        $propPesanan = $ref->getProperty('pesananModel');
        $propPesanan->setAccessible(true);
        $propPesanan->setValue($this->controller, $this->pesananModelMock);

        $propUser = $ref->getProperty('userModel');
        $propUser->setAccessible(true);
        $propUser->setValue($this->controller, $this->userModelMock);
    }

    private function mockUserSession(int $userId = 1): void
    {
        session()->set('id_user', $userId);

        $this->userModelMock->method('find')
            ->willReturn([
                'id_user'    => $userId,
                'nama'       => 'Test User',
                'username'   => 'testuser',
                'created_at' => '2025-11-17 06:00:00',
                'role'       => 'pembeli',
                'foto'       => 'default.png',
            ]);
    }

    private function mockPesananData(string $status = 'Belum Bayar'): array
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
            ],
        ];
    }

    public function testIndexRedirectsIfNotLoggedIn()
    {
        session()->destroy();

        $this->pesananModelMock
            ->expects($this->never())
            ->method('getPesananWithProduk');

        $this->userModelMock
            ->expects($this->never())
            ->method('find');

        $response = $this->controller->index();

        $this->assertInstanceOf(RedirectResponse::class, $response);
        $this->assertStringContainsString(
            '/login',
            $response->getHeaderLine('Location')
        );
    }

    public function testIndexReturnsViewWithOrders()
    {
        $this->mockUserSession(1);

        $this->pesananModelMock
            ->expects($this->once())
            ->method('getPesananWithProduk')
            ->with(1)
            ->willReturn($this->mockPesananData('Belum Bayar'));

        $response = $this->controller->index();
        $output   = (string) $response;

        $this->assertIsString($output);
        $this->assertNotEmpty($output);

        $this->assertStringContainsString('Belum Bayar', $output);
        $this->assertStringContainsString('Produk Test', $output);
        $this->assertStringContainsString('testuser', $output);
        $this->assertStringContainsString('pembeli', $output);
        $this->assertStringContainsString('Pesanan Saya', $output);
    }

    public function testBelumbayarReturnsViewWithBelumBayarOrders()
    {
        $this->mockUserSession(1);

        $this->pesananModelMock
            ->expects($this->once())
            ->method('getPesananByStatus')
            ->with(1, 'Belum Bayar')
            ->willReturn($this->mockPesananData('Belum Bayar'));

        $response = $this->controller->belumbayar();
        $output   = (string) $response;

        $this->assertIsString($output);
        $this->assertNotEmpty($output);
        $this->assertStringContainsString('Belum Bayar', $output);
        $this->assertStringContainsString('Produk Test', $output);
    }

    public function testDikemasReturnsViewWithDikemasOrders()
    {
        $this->mockUserSession(1);

        $this->pesananModelMock
            ->expects($this->once())
            ->method('getPesananByStatus')
            ->with(1, 'Dikemas')
            ->willReturn($this->mockPesananData('Dikemas'));

        $response = $this->controller->dikemas();
        $output   = (string) $response;

        $this->assertStringContainsString('Dikemas', $output);
        $this->assertStringContainsString('Produk Test', $output);
    }

    public function testSelesaiReturnsViewWithSelesaiOrders()
    {
        $this->mockUserSession(1);

        $this->pesananModelMock
            ->expects($this->once())
            ->method('getPesananByStatus')
            ->with(1, 'Selesai')
            ->willReturn($this->mockPesananData('Selesai'));

        $response = $this->controller->selesai();
        $output   = (string) $response;

        $this->assertStringContainsString('Selesai', $output);
        $this->assertStringContainsString('Produk Test', $output);
    }

    public function testDibatalkanReturnsViewWithDibatalkanOrders()
    {
        $this->mockUserSession(1);

        $this->pesananModelMock
            ->expects($this->once())
            ->method('getPesananByStatus')
            ->with(1, 'Dibatalkan')
            ->willReturn($this->mockPesananData('Dibatalkan'));

        $response = $this->controller->dibatalkan();
        $output   = (string) $response;

        $this->assertStringContainsString('Dibatalkan', $output);
        $this->assertStringContainsString('Produk Test', $output);
    }
}