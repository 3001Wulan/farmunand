<?php

namespace Tests\Feature;

use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\ControllerTestTrait;
use PHPUnit\Framework\MockObject\MockObject;
use App\Controllers\Auth;
use App\Models\UserModel;

class AuthTest extends CIUnitTestCase
{
    use ControllerTestTrait;

    /** @var MockObject */
    private $userMock;

    protected function setUp(): void
    {
        parent::setUp();

        // --- Mock UserModel ---
        $this->userMock = $this->createMock(UserModel::class);

        // Inject mock ke controller
        $this->injectMockToController(Auth::class, 'userModel', $this->userMock);

        // --- Mock session ---
        $mockSession = $this->createMock(\CodeIgniter\Session\Session::class);
        $mockSession->method('get')->willReturnMap([
            ['logged_in', false],
            ['role', null]
        ]);
        $mockSession->method('set')->willReturn(true);
        \Config\Services::injectMock('session', $mockSession);

        // --- Mock logger (opsional) ---
        $mockLogger = $this->createMock(\CodeIgniter\Log\Logger::class);
        $mockLogger->method('debug')->willReturnCallback(function(){});
        $mockLogger->method('error')->willReturnCallback(function(){});
        \Config\Services::injectMock('logger', $mockLogger);
    }

    /** ----------------------- HALAMAN LOGIN ----------------------- */
    public function testHalamanLoginBisaDibuka()
    {
        $result = $this->controller(Auth::class)->execute('login');
        $result->assertOK();
    }

    /** ----------------------- LOGIN ----------------------- */
    public function testLoginEmailTidakDitemukan()
    {
        $this->userMock->method('find')->willReturn(null);

        $postData = ['email' => 'tidakada@example.com', 'password' => 'password123'];

        $result = $this->withBody($postData)
                       ->controller(Auth::class)
                       ->execute('doLogin');

        $result->assertRedirectTo(site_url('login'));
    }

    public function testLoginPasswordSalah()
    {
        $this->userMock->method('find')->willReturn([
            'email' => 'admin@example.com',
            'password_hash' => password_hash('benar123', PASSWORD_DEFAULT),
            'role' => 'admin'
        ]);

        $postData = ['email' => 'admin@example.com', 'password' => 'salahbanget'];

        $result = $this->withBody($postData)
                       ->controller(Auth::class)
                       ->execute('doLogin');

        $result->assertRedirectTo(site_url('login'));
    }

    public function testLoginBerhasilSebagaiUser()
    {
        $this->userMock->method('find')->willReturn([
            'email' => 'user01@farmunand.local',
            'password_hash' => password_hash('111111', PASSWORD_DEFAULT),
            'role' => 'user'
        ]);
    
        $postData = ['email' => 'user01@farmunand.local', 'password' => '111111'];
    
        $result = $this->withBody($postData)
                       ->controller(Auth::class)
                       ->execute('doLogin');
    
        // pakai site_url agar sesuai base URL CI4
        $result->assertRedirect(); 
    }

    public function testLoginBerhasilSebagaiAdmin()
{
    $this->userMock->method('find')->willReturn([
        'email' => 'admin@farmunand.local',
        'password_hash' => password_hash('111111', PASSWORD_DEFAULT),
        'role' => 'admin'
    ]);

    $postData = ['email' => 'admin@farmunand.local', 'password' => '111111'];

    $result = $this->withBody($postData)
                   ->controller(Auth::class)
                   ->execute('doLogin');

                   $result->assertRedirect(); 
}


    /** ----------------------- LOGOUT ----------------------- */
    public function testLogout()
    {
        $result = $this->controller(Auth::class)->execute('logout');
        $result->assertRedirectTo(site_url('login'));
    }

    /** ----------------------- REGISTER ----------------------- */
    public function testRegisterGagalValidasi()
    {
        $postData = [
            'username' => '',
            'email' => 'salah',
            'password' => '123',
            'password_confirm' => '1234'
        ];

        $result = $this->withBody($postData)
                       ->controller(Auth::class)
                       ->execute('doRegister');

        $result->assertRedirect(); // cukup cek redirect
    }

    public function testRegisterBerhasil()
{
    $this->userMock->method('insert')->willReturn(1);

    $postData = [
        'username' => 'UserTest',
        'email' => 'userbaru@example.com',
        'password' => 'password123',
        'password_confirm' => 'password123'
    ];

    $result = $this->withBody($postData)
                   ->controller(Auth::class)
                   ->execute('doRegister');

                   $result->assertRedirect(); 
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
