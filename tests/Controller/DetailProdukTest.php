<?php

namespace Tests\Unit;

use App\Controllers\DetailProduk;
use App\Models\ProdukModel;
use App\Models\UserModel;
use CodeIgniter\Config\Services;
use CodeIgniter\Session\Session;
use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\ControllerTestTrait;
use PHPUnit\Framework\MockObject\MockObject;

class DetailProdukTest extends CIUnitTestCase
{
    use ControllerTestTrait;

    /** @var MockObject&ProdukModel */
    private $produkMock;

    /** @var MockObject&UserModel */
    private $userMock;

    private const CONTROLLER_CLASS = DetailProduk::class;

    protected function setUp(): void
    {
        parent::setUp();

        $this->produkMock = $this->createMock(ProdukModel::class);
        $this->userMock   = $this->createMock(UserModel::class);
    }

    private function injectMock(string $property, $mock): void
    {
        $instance = $this->getPrivateProperty($this, 'controller');
        $this->setPrivateProperty($instance, $property, $mock);
    }

    private function makeSessionMockForUser(): Session
    {
        /** @var Session&MockObject $mockSession */
        $mockSession = $this->createMock(Session::class);

        $mockSession->method('get')
            ->willReturnMap([
                ['id_user', 1],
                ['username', 'tester'],
                ['role', 'pembeli'],
                ['foto', 'default.png'],
                [null, null],
            ]);

        $mockSession->method('set')->willReturn(null);

        return $mockSession;
    }

    public function testDetailProdukMenampilkanHalamanUntukProdukValid()
    {
        $mockSession = $this->makeSessionMockForUser();
        Services::injectMock('session', $mockSession);

        $this->produkMock->method('find')
            ->with(10)
            ->willReturn([
                'id_produk'   => 10,
                'nama_produk' => 'Produk Test',
                'deskripsi'   => 'Deskripsi produk test',
                'harga'       => 150000,
                'stok'        => 5,
                'foto'        => 'produk_test.png',
            ]);

        $this->userMock->method('find')
            ->with(1)
            ->willReturn([
                'id_user'  => 1,
                'username' => 'tester',
                'role'     => 'pembeli',
                'foto'     => 'default.png',
            ]);

        $this->controller(self::CONTROLLER_CLASS);

        $this->injectMock('produkModel', $this->produkMock);
        $this->injectMock('userModel', $this->userMock);

        $result = $this->execute('index', 10);

        $result->assertOK();

        $body = $result->getBody();
        $this->assertStringContainsString('Produk Test', $body);
        $this->assertStringContainsString('tester', $body);
    }

    public function testDetailProdukDapatDiaksesMeskiTanpaSessionLogin()
    {
        /** @var Session&MockObject $mockSession */
        $mockSession = $this->createMock(Session::class);
        $mockSession->method('get')->willReturnMap([
            ['id_user', null],
            ['username', null],
            ['role', 'pembeli'],
            ['foto', 'default.png'],
            [null, null],
        ]);
        Services::injectMock('session', $mockSession);

        $this->produkMock->method('find')
            ->with(5)
            ->willReturn([
                'id_produk'   => 5,
                'nama_produk' => 'Produk Tanpa Login',
                'deskripsi'   => 'Deskripsi',
                'harga'       => 100000,
                'stok'        => 3,
            ]);

        $this->controller(self::CONTROLLER_CLASS);
        $this->injectMock('produkModel', $this->produkMock);

        $result = $this->execute('index', 5);

        $result->assertOK();
        $this->assertStringContainsString('Produk Tanpa Login', $result->getBody());
    }

    public function testDetailProdukNotFoundMenghasilkan404AtauPesanError()
    {
        $mockSession = $this->makeSessionMockForUser();
        Services::injectMock('session', $mockSession);

        $this->produkMock->method('find')
            ->with(999)
            ->willReturn(null);

        $this->controller(self::CONTROLLER_CLASS);
        $this->injectMock('produkModel', $this->produkMock);

        $result = $this->execute('index', 999);

        $status = $result->response()->getStatusCode();
        $body   = $result->getBody();

        $this->assertTrue(
            $status === 404 || str_contains($body, 'Produk tidak ditemukan'),
            'DetailProduk untuk id 999 seharusnya 404 atau menampilkan pesan "Produk tidak ditemukan".'
        );
    }
}