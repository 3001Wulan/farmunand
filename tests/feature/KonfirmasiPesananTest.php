<?php

namespace Tests\Support\Controllers;

use CodeIgniter\Test\CIUnitTestCase;
use App\Models\PesananModel;
use App\Models\UserModel;
use App\Controllers\KonfirmasiPesanan;

class KonfirmasiPesananTest extends CIUnitTestCase
{
    protected $pesananModelMock;
    protected $userModelMock;
    protected $controller;

    protected function setUp(): void
    {
        parent::setUp();

        // Mock PesananModel
        $this->pesananModelMock = $this->createMock(PesananModel::class);

        // Mock UserModel
        $this->userModelMock = $this->createMock(UserModel::class);

        // Inisialisasi Controller
        $this->controller = new KonfirmasiPesanan();

        // Reflection untuk set protected properties
        $reflection = new \ReflectionClass($this->controller);

        $pesananProp = $reflection->getProperty('pesananModel');
        $pesananProp->setAccessible(true);
        $pesananProp->setValue($this->controller, $this->pesananModelMock);

        $userProp = $reflection->getProperty('userModel');
        $userProp->setAccessible(true);
        $userProp->setValue($this->controller, $this->userModelMock);
    }

    /** @test */
    public function index_without_login_redirects_to_login()
    {
        $_SESSION = [];

        $response = $this->controller->index();

        $this->assertStringContainsString('/login', $response->getHeaderLine('Location'));
    }

    /** @test */
    public function index_with_login_fetches_pesanan_and_user()
    {
        $_SESSION['id_user'] = 1;

        $mockPesanan = [
            ['id_pemesanan' => 101, 'status_pemesanan' => 'Dikirim']
        ];
        $mockUser = [
            'id_user' => 1,
            'nama' => 'Test User',
            'username' => 'testuser'
        ];

        $this->pesananModelMock->method('getPesananByStatus')
            ->with(1, 'Dikirim')
            ->willReturn($mockPesanan);

        $this->userModelMock->method('find')
            ->with(1)
            ->willReturn($mockUser);

        // Ambil data dari model mock, tanpa render view
        $pesanan = $this->pesananModelMock->getPesananByStatus(1, 'Dikirim');
        $user    = $this->userModelMock->find(1);

        $this->assertEquals($mockPesanan, $pesanan);
        $this->assertEquals($mockUser, $user);
    }

    /** @test */
    public function selesai_without_login_redirects_to_login()
    {
        $_SESSION = [];

        $response = $this->controller->selesai(101);

        $this->assertStringContainsString('/login', $response->getHeaderLine('Location'));
    }

    /** @test */
public function selesai_pesanan_not_found_redirects_back()
{
    $_SESSION['id_user'] = 1;

    // Mock method helper baru di model
    $this->pesananModelMock->method('getPesananByIdAndUser')
        ->with(101, 1)
        ->willReturn(null);

    $response = $this->controller->selesai(101);

    // Sesuaikan dengan redirect()->back() default CI
    $this->assertStringContainsString('/', $response->getHeaderLine('Location'));
}

    /** @test */
    public function selesai_pesanan_sukses_updates_status()
    {
        $_SESSION['id_user'] = 1;

        $row = [
            'id_pemesanan' => 101,
            'id_user' => 1,
            'status_pemesanan' => 'Dikirim'
        ];

        // Mock method helper baru di model
        $this->pesananModelMock->method('getPesananByIdAndUser')
            ->with(101, 1)
            ->willReturn($row);

        $this->pesananModelMock->method('update')
            ->with(101, $this->callback(function($data) {
                return $data['status_pemesanan'] === 'Selesai'
                    && isset($data['confirmed_at'])
                    && $data['konfirmasi_token'] === null;
            }))
            ->willReturn(true);

        $response = $this->controller->selesai(101);

        $this->assertStringContainsString('/pesananselesai', $response->getHeaderLine('Location'));
    }
}
