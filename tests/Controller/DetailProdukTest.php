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

    /**
     * Helper inject mock ke properti private/protected di controller.
     * Harus dipanggil setelah $this->controller() dipanggil.
     */
    private function injectMock(string $property, $mock): void
    {
        // instance controller yang disimpan oleh ControllerTestTrait
        $instance = $this->getPrivateProperty($this, 'controller');
        $this->setPrivateProperty($instance, $property, $mock);
    }

    /**
     * Helper buat mock session dengan data user login standar
     */
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
                // fallback default
                [null, null],
            ]);

        // kalau controller / view manggil set(), kita biarkan saja (no-op)
        $mockSession->method('set')->willReturn(null);

        return $mockSession;
    }

    /** ----------------------- 1. DETAIL PRODUK SUKSES (USER LOGIN) ----------------------- */
    public function testDetailProdukMenampilkanHalamanUntukProdukValid()
    {
        // Mock session dengan user login
        $mockSession = $this->makeSessionMockForUser();
        Services::injectMock('session', $mockSession);

        // Mock ProdukModel::find()
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

        // Mock UserModel::find() (untuk data user di sidebar)
        $this->userMock->method('find')
            ->with(1)
            ->willReturn([
                'id_user'  => 1,
                'username' => 'tester',
                'role'     => 'pembeli',
                'foto'     => 'default.png',
            ]);

        // 1. Buat controller
        $this->controller(self::CONTROLLER_CLASS);

        // 2. Inject mock model ke controller
        $this->injectMock('produkModel', $this->produkMock);
        $this->injectMock('userModel', $this->userMock);

        // 3. Eksekusi method index(10)
        $result = $this->execute('index', 10);

        // 4. Asersi
        $result->assertOK();

        $body = $result->getBody();
        $this->assertStringContainsString('Produk Test', $body);
        $this->assertStringContainsString('tester', $body);       // nama user di layout
    }

    /** ----------------------- 2. DETAIL PRODUK TANPA LOGIN (HANYA CEK BISA AKSES) ----------------------- */
    public function testDetailProdukDapatDiaksesMeskiTanpaSessionLogin()
    {
        // Session kosong, tapi kita mock supaya dipanggil get('role') tidak error
        /** @var Session&MockObject $mockSession */
        $mockSession = $this->createMock(Session::class);
        $mockSession->method('get')->willReturnMap([
            ['id_user', null],
            ['username', null],
            ['role', 'pembeli'],   // biar sidebar tidak undefined index
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

        // Tidak perlu userModel di sini kalau controller hanya pakai saat ada id_user

        $this->controller(self::CONTROLLER_CLASS);
        $this->injectMock('produkModel', $this->produkMock);

        $result = $this->execute('index', 5);

        $result->assertOK();
        $this->assertStringContainsString('Produk Tanpa Login', $result->getBody());
    }

    /** ----------------------- 3. DETAIL PRODUK NOT FOUND ----------------------- */
    public function testDetailProdukNotFoundMenghasilkan404AtauPesanError()
    {
        // Session mock standar
        $mockSession = $this->makeSessionMockForUser();
        Services::injectMock('session', $mockSession);

        // Produk tidak ditemukan
        $this->produkMock->method('find')
            ->with(999)
            ->willReturn(null);

        $this->controller(self::CONTROLLER_CLASS);
        $this->injectMock('produkModel', $this->produkMock);

        $result = $this->execute('index', 999);

        $status = $result->response()->getStatusCode();
        $body   = $result->getBody();

        // Longgar: boleh 404 (PageNotFoundException) atau 200 dengan pesan "Produk tidak ditemukan"
        $this->assertTrue(
            $status === 404 || str_contains($body, 'Produk tidak ditemukan'),
            'DetailProduk untuk id 999 seharusnya 404 atau menampilkan pesan "Produk tidak ditemukan".'
        );
    }
}
