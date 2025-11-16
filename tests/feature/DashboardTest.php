<?php

namespace Tests\Support\Controllers;

use App\Controllers\Dashboard;
use App\Models\ProdukModel;
use App\Models\UserModel;
use App\Models\PesananModel;
use CodeIgniter\Test\CIUnitTestCase;

class DashboardTest extends CIUnitTestCase
{
    protected $dashboard;
    protected $produkModelMock;
    protected $userModelMock;

    protected function setUp(): void
    {
        parent::setUp();

        $this->produkModelMock = $this->getMockBuilder(ProdukModel::class)
            ->onlyMethods(['getTotalProduk', 'getStokRendah'])
            ->getMock();
        $this->produkModelMock->method('getTotalProduk')->willReturn(50);
        $this->produkModelMock->method('getStokRendah')->willReturn(3);

        $this->userModelMock = $this->getMockBuilder(UserModel::class)
            ->onlyMethods(['getTotalUser', 'find'])
            ->getMock();
        $this->userModelMock->method('getTotalUser')->willReturn(20);

        $this->userModelMock->method('find')->with(1)->willReturn([
            'id_user'  => 1,
            'nama'     => 'Admin',
            'username' => 'admin123',
            'role'     => 'admin'
        ]);

        $pesananModel = new PesananModel();

        $this->dashboard = new Dashboard();

        $ref = new \ReflectionClass($this->dashboard);

        $assign = function ($prop, $value) use ($ref) {
            $property = $ref->getProperty($prop);
            $property->setAccessible(true);
            $property->setValue($this->dashboard, $value);
        };

        $assign('produkModel', $this->produkModelMock);
        $assign('userModel', $this->userModelMock);
        $assign('pesananModel', $pesananModel);
    }

    public function test_dashboard_returns_metrics_and_user()
    {
        $_SESSION['id_user'] = 1;
    
        $output = $this->dashboard->index(); // STRING HTML
    
        $this->assertStringContainsString("Total Produk", $output);
        $this->assertStringContainsString("50", $output);
    
        $this->assertStringContainsString("Total User", $output);
        $this->assertStringContainsString("20", $output);
    
        $this->assertStringContainsString("Stok Rendah", $output);
        $this->assertStringContainsString("3", $output);
    
        $this->assertStringContainsString("admin123", $output);
    }
    
    public function test_dashboard_redirects_if_not_logged_in()
    {
        unset($_SESSION['id_user']);

        $response = $this->dashboard->index();

        $location = $response->getHeaderLine('Location');

        // URL CI bisa berubah tergantung baseURL → gunakan contains
        $this->assertStringContainsString("/login", $location);
    }
}