<?php

namespace Tests\Unit;

use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\ControllerTestTrait;
use PHPUnit\Framework\MockObject\MockObject;
use App\Controllers\DashboardUser;
use App\Models\PesananModel;

class DashboardUserTest extends CIUnitTestCase
{
    use ControllerTestTrait;

    /** @var MockObject */
    private $pesananMock;

    protected function setUp(): void
    {
        parent::setUp();

        // --- Mock PesananModel ---
        $this->pesananMock = $this->createMock(PesananModel::class);
        
        // Inject mock ke controller
        $this->injectMockToController(DashboardUser::class, 'pesananModel', $this->pesananMock);

        // --- Mock session ---
        $mockSession = $this->createMock(\CodeIgniter\Session\Session::class);
        $mockSession->method('get')->willReturnMap([
            ['id_user', null], // default: not logged in
            ['username', null],
            ['role', null],
        ]);
        $mockSession->method('set')->willReturn(true);
        \Config\Services::injectMock('session', $mockSession);
    }

    /** ----------------------- TEST REDIRECT JIKA BELUM LOGIN ----------------------- */
    public function testRedirectIfNotLoggedIn()
    {
        // Session id_user = null → belum login
        $result = $this->controller(DashboardUser::class)->execute('index');
        $result->assertRedirectTo(site_url('login'));
    }

    /** ----------------------- TEST INDEX VIEW JIKA LOGIN ----------------------- */
    public function testDashboardUserReturnsView()
{
    // Simulasikan user login
    $mockSession = \Config\Services::session();
    $mockSession->method('get')->willReturnMap([
        ['id_user', 1],
        ['username', 'UserTest'],
        ['role', 'user']
    ]);

    // --- Mock PesananModel dengan method chaining ---
    $this->pesananMock = $this->getMockBuilder(PesananModel::class)
                          ->onlyMethods(['where', 'whereIn', 'countAllResults'])
                          ->getMock();

$this->pesananMock->method('where')->willReturnSelf();
$this->pesananMock->method('whereIn')->willReturnSelf();
$this->pesananMock->method('countAllResults')->willReturn(5); // contoh jumlah pesanan

    // Inject mock baru ke controller
    $this->injectMockToController(DashboardUser::class, 'pesananModel', $this->pesananMock);

    // Jalankan controller
    $result = $this->controller(DashboardUser::class)->execute('index');

    // Pastikan response OK
    $result->assertOK();

    // Cek konten view
    $output = (string) $result->getBody();
    $this->assertStringContainsString('Dashboard User', $output);
    $this->assertStringContainsString('pesanan', strtolower($output));
}

    /** ----------------------- HELPER ----------------------- */
    private function injectMockToController($controllerClass, $property, $mock)
    {
        $this->controller($controllerClass);

        $reflection = new \ReflectionClass($controllerClass);
        $instance = $this->getPrivateProperty($this, 'controller');

        $prop = $reflection->getProperty($property);
        $prop->setAccessible(true);
        $prop->setValue($instance, $mock);
    }
}
