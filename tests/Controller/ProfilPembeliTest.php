<?php

namespace Tests\Controllers;

use App\Controllers\Profile;
use App\Models\UserModel;
use CodeIgniter\Test\CIUnitTestCase;

class ProfilPembeliTest extends CIUnitTestCase
{
    protected $userModelMock;
    protected $controller;

    protected function setUp(): void
    {
        parent::setUp();

        helper('session');

        // Mock UserModel
        $this->userModelMock = $this->getMockBuilder(UserModel::class)
                                    ->onlyMethods(['find', 'update'])
                                    ->getMock();

        // Instance controller
        $this->controller = new Profile();

        // Inject mock UserModel via Reflection
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
            'username' => 'user1',
            'role' => 'user',
            'email' => 'user@gmqil.com',
            'foto' => 'default.png'
        ];

        $this->userModelMock->expects($this->once())
                            ->method('find')
                            ->with(1)
                            ->willReturn($userData);

        $result = $this->controller->index();

        $this->assertIsString($result); // karena view dikembalikan
        $this->assertStringContainsString('<title>Profil Saya</title>', $result);
        $this->assertStringContainsString($userData['username'], $result);
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
            'username' => 'user1',
            'role' => 'user',
            'nama' => 'user',
            'email' => 'user@gmqil.com',
            'no_hp' => '082285671644',
            'foto' => 'default.png'
        ];

        $this->userModelMock->expects($this->once())
                            ->method('find')
                            ->with(1)
                            ->willReturn($userData);

        $result = $this->controller->edit();

        $this->assertIsString($result); // karena view dikembalikan
        $this->assertStringContainsString('<title>Edit Profil</title>', $result);
        $this->assertStringContainsString($userData['username'], $result);
    }

    /** ---------------------- UPDATE ---------------------- */

    public function testUpdateWithoutSession()
    {
        $_SESSION = [];

        $result = $this->controller->update();

        $this->assertInstanceOf(\CodeIgniter\HTTP\RedirectResponse::class, $result);
        $this->assertStringContainsString('/login', $result->getHeaderLine('Location'));
    }

    public function testUpdateWithSessionAndValidData()
{
    $_SESSION['id_user'] = 1;

    // Mock user lama
    $userData = [
        'id_user' => 1,
        'username' => 'user1',
        'foto' => 'default.png'
    ];

    $this->userModelMock->expects($this->once())
                        ->method('find')
                        ->with(1)
                        ->willReturn($userData);

    // Mock POST request menggunakan IncomingRequest
    $requestMock = $this->getMockBuilder(\CodeIgniter\HTTP\IncomingRequest::class)
                        ->disableOriginalConstructor()
                        ->getMock();

    $requestMock->method('getPost')->willReturn([
        'username' => 'user1updated',
        'nama'     => 'User Updated',
        'email'    => 'user1@example.com',
        'no_hp'    => '08123456789'
    ]);

    $requestMock->method('getFiles')->willReturn([]);

    // Inject request ke controller
    $this->controller->setRequest($requestMock);

    // Mock update
    $this->userModelMock->expects($this->once())
                        ->method('update')
                        ->with(1, $this->callback(function ($data) {
                            return $data['username'] === 'user1updated' &&
                                   $data['nama'] === 'User Updated' &&
                                   $data['email'] === 'user1@example.com' &&
                                   $data['no_hp'] === '08123456789';
                        }));

    $result = $this->controller->update();

    $this->assertInstanceOf(\CodeIgniter\HTTP\RedirectResponse::class, $result);
    $this->assertStringContainsString('/profile', $result->getHeaderLine('Location'));
}

}
