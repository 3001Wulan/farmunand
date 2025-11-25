<?php

namespace Tests\Controllers;

use App\Controllers\ProfileAdmin;
use App\Models\UserModel;
use CodeIgniter\Test\CIUnitTestCase;

class ProfilAdminTest extends CIUnitTestCase
{
    protected $userModelMock;
    protected $controller;

    protected function setUp(): void
    {
        parent::setUp();

        // Load session helper
        helper('session');

        // Mock UserModel
        $this->userModelMock = $this->getMockBuilder(UserModel::class)
                                    ->onlyMethods(['find', 'update'])
                                    ->getMock();

        // Buat instance controller
        $this->controller = new ProfileAdmin();

        // Inject mock UserModel menggunakan Reflection
        $reflection = new \ReflectionClass($this->controller);
        $property   = $reflection->getProperty('userModel');
        $property->setAccessible(true);
        $property->setValue($this->controller, $this->userModelMock);
    }

    /** ---------------------- INDEX ---------------------- */

    public function testIndexWithoutSession()
    {
        $_SESSION = [];

        $result = $this->controller->index();

        $this->assertInstanceOf(\CodeIgniter\HTTP\RedirectResponse::class, $result);
        $this->assertStringContainsString('/login', $result->getHeaderLine('Location'));
    }

    public function testIndexWithSession()
    {
        $_SESSION['id_user'] = 1;

        $userData = [
            'id_user' => 1,
            'username' => 'admin',
            'role' => 'admin',
            'email' => 'wulandariyulianis360@gmail.com',
            'foto' => 'default.jpeg'
        ];

        $this->userModelMock->expects($this->once())
                            ->method('find')
                            ->with(1)
                            ->willReturn($userData);

        $result = $this->controller->index();

        // Karena controller return view, cek string HTML
        $this->assertIsString($result);
        $this->assertStringContainsString('<title>Profil Admin</title>', $result);
        $this->assertStringContainsString($userData['username'], $result);
        $this->assertStringContainsString($userData['role'], $result);
    }

    /** ---------------------- EDIT ---------------------- */

    public function testEditWithoutSession()
    {
        $_SESSION = [];

        $result = $this->controller->edit();

        $this->assertInstanceOf(\CodeIgniter\HTTP\RedirectResponse::class, $result);
        $this->assertStringContainsString('/login', $result->getHeaderLine('Location'));
    }

    public function testEditWithSession()
    {
        $_SESSION['id_user'] = 1;

        $userData = [
            'id_user' => 1,
            'username' => 'admin',
            'role' => 'admin',
            'nama' => 'admin',
            'email' => 'wulandariyulianis360@gmail.com',
            'no_hp' => '082285671644',
            'foto' => 'default.jpeg'
        ];

        $this->userModelMock->expects($this->once())
                            ->method('find')
                            ->with(1)
                            ->willReturn($userData);

        $result = $this->controller->edit();

        // Cek return view HTML
        $this->assertIsString($result);
        $this->assertStringContainsString('<title>Edit Profil Admin</title>', $result);
        $this->assertStringContainsString($userData['username'], $result);
        $this->assertStringContainsString($userData['role'], $result);
    }
}
