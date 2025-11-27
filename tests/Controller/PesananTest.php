<?php

namespace Tests\Controllers;

use App\Controllers\Pesanan;
use App\Models\PesananModel;
use App\Models\UserModel;
use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\HTTP\RedirectResponse;

/**
 * Unit test untuk controller Pesanan.
 *
 * Fokus:
 * - Proteksi akses (harus login)
 * - Pemanggilan method model yang tepat (getPesananWithProduk, getPesananByStatus)
 * - View yang dikembalikan memuat informasi status pesanan
 */
class PesananTest extends CIUnitTestCase
{
    /** @var PesananModel|\PHPUnit\Framework\MockObject\MockObject */
    protected $pesananModelMock;

    /** @var UserModel|\PHPUnit\Framework\MockObject\MockObject */
    protected $userModelMock;

    /** @var Pesanan */
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

        // Instance controller asli
        $this->controller = new Pesanan();

        // Inject mock ke dalam properti protected di controller
        $ref = new \ReflectionClass($this->controller);

        $propPesanan = $ref->getProperty('pesananModel');
        $propPesanan->setAccessible(true);
        $propPesanan->setValue($this->controller, $this->pesananModelMock);

        $propUser = $ref->getProperty('userModel');
        $propUser->setAccessible(true);
        $propUser->setValue($this->controller, $this->userModelMock);
    }

    /**
     * Helper: set session user login + stub UserModel::find()
     */
    private function mockUserSession(int $userId = 1): void
    {
        // Set session id_user
        session()->set('id_user', $userId);

        // Stub: setiap find() dipanggil, kembalikan data user ini
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

    /**
     * Helper: data pesanan dummy untuk berbagai status.
     */
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

    /** ----------------------------------------------------------------
     * 1) INDEX: user belum login → HARUS redirect (tidak load data)
     * ----------------------------------------------------------------*/
    public function testIndexRedirectsIfNotLoggedIn()
    {
        // Pastikan session kosong
        session()->destroy();

        // Pastikan model tidak pernah dipanggil saat belum login
        $this->pesananModelMock
            ->expects($this->never())
            ->method('getPesananWithProduk');

        $this->userModelMock
            ->expects($this->never())
            ->method('find');

        // Panggil controller langsung, dia seharusnya return RedirectResponse
        $response = $this->controller->index();

        $this->assertInstanceOf(RedirectResponse::class, $response);
        $this->assertStringContainsString(
            '/login',
            $response->getHeaderLine('Location')
        );
    }

    /** ----------------------------------------------------------------
     * 2) INDEX: user login → ambil semua pesanan & produk terkait
     * ----------------------------------------------------------------*/
    public function testIndexReturnsViewWithOrders()
    {
        $this->mockUserSession(1);

        // Pastikan getPesananWithProduk dipanggil sekali dengan id_user = 1
        $this->pesananModelMock
            ->expects($this->once())
            ->method('getPesananWithProduk')
            ->with(1)
            ->willReturn($this->mockPesananData('Belum Bayar'));

        // Jalankan controller
        $response = $this->controller->index();
        $output   = (string) $response;

        // Assertion ke view:
        $this->assertIsString($output);
        $this->assertNotEmpty($output);

        // Status pesanan tampil
        $this->assertStringContainsString('Belum Bayar', $output);
        // Nama produk tampil
        $this->assertStringContainsString('Produk Test', $output);
        // Sidebar menampilkan username (bukan "Test User" tapi "testuser | pembeli")
        $this->assertStringContainsString('testuser', $output);
        $this->assertStringContainsString('pembeli', $output);
        // Judul halaman sesuai
        $this->assertStringContainsString('Pesanan Saya', $output);
    }

    /** ----------------------------------------------------------------
     * 3) BELUM BAYAR: filter pesanan dengan status "Belum Bayar"
     * ----------------------------------------------------------------*/
    public function testBelumbayarReturnsViewWithBelumBayarOrders()
    {
        $this->mockUserSession(1);

        // Pastikan getPesananByStatus dipanggil dengan (id_user, 'Belum Bayar')
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

    /** ----------------------------------------------------------------
     * 4) DIKEMAS: filter pesanan dengan status "Dikemas"
     * ----------------------------------------------------------------*/
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

    /** ----------------------------------------------------------------
     * 5) SELESAI: filter pesanan dengan status "Selesai"
     * ----------------------------------------------------------------*/
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

    /** ----------------------------------------------------------------
     * 6) DIBATALKAN: filter pesanan dengan status "Dibatalkan"
     * ----------------------------------------------------------------*/
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
