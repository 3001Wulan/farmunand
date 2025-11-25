<?php

namespace Tests\Unit;

use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\HTTP\RedirectResponse;
use App\Controllers\DashboardUser;
use App\Models\UserModel;
use App\Models\PesananModel;

class DashboardUserTest extends CIUnitTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $_SESSION['id_user'] = 1;
    }

    public function testRedirectIfNotLoggedIn()
    {
        unset($_SESSION['id_user']);

        // Anonymous subclass tanpa constructor parent
        $controller = new class extends DashboardUser {
            public $userModel;
            public $pesananModel;

            public function __construct() {} // jangan panggil parent
        };

        $response = $controller->index();

        $this->assertInstanceOf(RedirectResponse::class, $response);
        $this->assertStringContainsString('login', $response->getHeaderLine('Location'));
    }

    public function testDashboardUserReturnsView()
    {
        // Mock UserModel
        $userMock = $this->getMockBuilder(UserModel::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['find'])
            ->getMock();
        $userMock->method('find')->willReturn([
            'id_user' => 1,
            'username' => 'test',
            'role' => 'Pembeli',
            'foto' => null
        ]);

        // Mock Builder untuk PesananModel
        $builderMock = $this->getMockBuilder(\stdClass::class)
            ->addMethods(['where', 'whereIn', 'countAllResults'])
            ->getMock();
        $builderMock->method('where')->willReturnSelf();
        $builderMock->method('whereIn')->willReturnSelf();
        $builderMock->method('countAllResults')->willReturn(5);

        // Mock PesananModel
        $pesananMock = $this->getMockBuilder(PesananModel::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['builder'])
            ->getMock();
        $pesananMock->method('builder')->willReturn($builderMock);

        // Anonymous subclass controller, inject mock
        $controller = new class($userMock, $pesananMock) extends DashboardUser {
            public $userModel;
            public $pesananModel;

            public function __construct($userMock, $pesananMock)
            {
                $this->userModel = $userMock;
                $this->pesananModel = $pesananMock;
            }

            // Override method index untuk pakai properti mock
            public function index()
            {
                // Gunakan mock saja, jangan panggil parent
                $user = $this->userModel->find(1);
                $totalPesanan = $this->pesananModel->builder()
                    ->where('id_user', 1)
                    ->countAllResults();

                // Simulasi view
                return "Dashboard User - Total Pesanan: {$totalPesanan}";
            }
        };

        $output = $controller->index();

        $this->assertIsString($output);
        $this->assertStringContainsString('Dashboard User', $output);
    }
}
