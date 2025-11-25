<?php

namespace Tests\Unit;

use App\Controllers\Dashboard;
use App\Models\ProdukModel;
use App\Models\UserModel;
use App\Models\PesananModel;
use CodeIgniter\Test\CIUnitTestCase;
use PHPUnit\Framework\MockObject\MockObject;

class DashboardTest extends CIUnitTestCase
{
    /** @var Dashboard */
    private $dashboard;

    /** @var MockObject */
    private $produkModelMock;

    /** @var MockObject */
    private $userModelMock;

    /** @var MockObject */
    private $pesananModelMock;

    protected function setUp(): void
    {
        parent::setUp();

        // --- Mock ProdukModel ---
        $this->produkModelMock = $this->createMock(ProdukModel::class);
        $this->produkModelMock->method('getTotalProduk')->willReturn(50);
        $this->produkModelMock->method('getStokRendah')->willReturn(3);

        // --- Mock UserModel ---
        $this->userModelMock = $this->createMock(UserModel::class);
        $this->userModelMock->method('getTotalUser')->willReturn(20);
        $this->userModelMock->method('find')->with(1)->willReturn([
            'id_user'  => 1,
            'nama'     => 'Admin',
            'username' => 'admin123',
            'role'     => 'admin'
        ]);

        // --- Mock PesananModel ---
        $this->pesananModelMock = $this->createMock(PesananModel::class);
        $this->pesananModelMock->method('countAllResults')->willReturn(7);

        // --- Buat instance Dashboard ---
        $this->dashboard = new Dashboard();

        // --- Inject semua mock ke controller via reflection ---
        $this->injectMockToController(Dashboard::class, 'produkModel', $this->produkModelMock);
        $this->injectMockToController(Dashboard::class, 'userModel', $this->userModelMock);
        $this->injectMockToController(Dashboard::class, 'pesananModel', $this->pesananModelMock);
    }

    public function test_dashboard_returns_metrics_and_user()
    {
        // Simulasi user login
        $_SESSION['id_user'] = 1;

        // Jalankan controller
        $output = $this->dashboard->index(); // HTML string

        // Cek total produk
        $this->assertStringContainsString('50', $output);
        $this->assertStringContainsString('20', $output);
        $this->assertStringContainsString('3', $output);
        $this->assertStringContainsString('7', $output);
        $this->assertStringContainsString('admin123', $output);

        // Bersihkan session
        unset($_SESSION['id_user']);
    }

    public function test_dashboard_redirects_if_not_logged_in()
    {
        unset($_SESSION['id_user']);

        $response = $this->dashboard->index();

        // Cek header Location dari RedirectResponse
        $location = $response->getHeaderLine('Location');

        $this->assertStringContainsString('/login', $location);
    }

    /** ----------------------- HELPER ----------------------- */
    private function injectMockToController($controllerClass, $property, $mock)
    {
        $ref = new \ReflectionClass($controllerClass);
        $instance = $this->dashboard;

        $prop = $ref->getProperty($property);
        $prop->setAccessible(true);
        $prop->setValue($instance, $mock);
    }
}
