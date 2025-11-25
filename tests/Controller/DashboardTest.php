<?php

namespace Tests\Unit;

use App\Controllers\Dashboard;
use App\Models\ProdukModel;
use App\Models\UserModel;
use App\Models\PesananModel;
use CodeIgniter\Test\CIUnitTestCase;

class DashboardTest extends CIUnitTestCase
{
    private $dashboard;

    private $produkModelMock;
    private $userModelMock;
    private $pesananModelMock;

    protected function setUp(): void
    {
        parent::setUp();

        // Mock ProdukModel
        $this->produkModelMock = $this->createMock(ProdukModel::class);
        $this->produkModelMock->method('getTotalProduk')->willReturn(50);
        $this->produkModelMock->method('getStokRendah')->willReturn(3);

        // Mock UserModel
        $this->userModelMock = $this->createMock(UserModel::class);
        $this->userModelMock->method('getTotalUser')->willReturn(20);
        $this->userModelMock->method('find')->with(1)->willReturn([
            'id_user' => 1,
            'nama' => 'Admin',
            'username' => 'admin123',
            'role' => 'admin'
        ]);

        // Mock PesananModel
        $this->pesananModelMock = $this->createMock(PesananModel::class);
        $this->pesananModelMock->method('countAllResults')->willReturn(7);

        // Inject ke konstruktor Dashboard
        $this->dashboard = new Dashboard(
            $this->produkModelMock,
            $this->userModelMock,
            $this->pesananModelMock
        );
    }

    public function test_dashboard_returns_metrics_and_user()
    {
        // Simulasi login
        $_SESSION['id_user'] = 1;

        // Jalankan controller
        $output = $this->dashboard->index();

        // Validasi hasil
        $this->assertStringContainsString('50', $output); // total produk
        $this->assertStringContainsString('20', $output); // total user
        $this->assertStringContainsString('3', $output);  // stok rendah
        $this->assertStringContainsString('7', $output);  // total pesanan
        $this->assertStringContainsString('admin123', $output); // username

        unset($_SESSION['id_user']);
    }

    public function test_dashboard_redirects_if_not_logged_in()
    {
        unset($_SESSION['id_user']);

        $response = $this->dashboard->index();
        $location = $response->getHeaderLine('Location');

        $this->assertStringContainsString('/login', $location);
    }
}
