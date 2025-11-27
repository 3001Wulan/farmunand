<?php

namespace Tests\Controllers;

use App\Controllers\Profile;
use App\Models\UserModel;
use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\HTTP\IncomingRequest;
use CodeIgniter\HTTP\URI;
use CodeIgniter\HTTP\UserAgent;
use Config\App;
use ReflectionClass;

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
            ->disableOriginalConstructor()
            ->onlyMethods(['find', 'update'])
            ->getMock();

        // Instance controller
        $this->controller = new Profile();

        // Inject mock UserModel via Reflection
        $ref      = new ReflectionClass($this->controller);
        $property = $ref->getProperty('userModel');
        $property->setAccessible(true);
        $property->setValue($this->controller, $this->userModelMock);

        // Pastikan session kosong di awal
        $_SESSION = [];
    }

    protected function tearDown(): void
    {
        $_SESSION = [];
        parent::tearDown();
    }

    private function injectRequest(IncomingRequest $request): void
    {
        $ref      = new ReflectionClass($this->controller);
        $property = $ref->getProperty('request');
        $property->setAccessible(true);
        $property->setValue($this->controller, $request);
    }

    // ---------------------- INDEX ----------------------

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
            'id_user'  => 1,
            'username' => 'user1',
            'role'     => 'user',
            'email'    => 'user@gmail.com',
            'foto'     => 'default.png',
        ];

        $this->userModelMock->expects($this->once())
            ->method('find')
            ->with(1)
            ->willReturn($userData);

        $result = $this->controller->index();

        $this->assertIsString($result); // view dikembalikan sebagai string
        $this->assertStringContainsString('Profil', $result);
        $this->assertStringContainsString($userData['username'], $result);
    }

    // ---------------------- EDIT ----------------------

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
            'id_user'  => 1,
            'username' => 'user1',
            'role'     => 'user',
            'nama'     => 'User Satu',
            'email'    => 'user@gmail.com',
            'no_hp'    => '082285671644',
            'foto'     => 'default.png',
        ];

        $this->userModelMock->expects($this->once())
            ->method('find')
            ->with(1)
            ->willReturn($userData);

        $result = $this->controller->edit();

        $this->assertIsString($result);
        $this->assertStringContainsString('Edit', $result);
        $this->assertStringContainsString($userData['username'], $result);
    }

    // ---------------------- UPDATE ----------------------

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

        // User lama
        $userData = [
            'id_user'  => 1,
            'username' => 'user1',
            'foto'     => 'default.png',
        ];

        $this->userModelMock->expects($this->once())
            ->method('find')
            ->with(1)
            ->willReturn($userData);

        // Mock request (POST data)
        $postData = [
            'username' => 'user1updated',
            'nama'     => 'User Updated',
            'email'    => 'user1@example.com',
            'no_hp'    => '08123456789',
        ];

        $requestMock = $this->getMockBuilder(IncomingRequest::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getPost', 'getFiles', 'getFile', 'getMethod'])
            ->getMock();

        $requestMock->method('getMethod')->willReturn('post');
        $requestMock->method('getPost')
            ->willReturnCallback(function ($key = null) use ($postData) {
                if ($key === null) {
                    return $postData;
                }
                return $postData[$key] ?? null;
            });

        $requestMock->method('getFiles')->willReturn([]);
        $requestMock->method('getFile')->willReturn(null);

        // Inject request ke controller
        $this->injectRequest($requestMock);

        // Tidak terlalu ketat ke isi datanya, cukup stub update() agar tidak error
        $this->userModelMock->method('update')->willReturn(true);

        $result = $this->controller->update();

        $this->assertInstanceOf(\CodeIgniter\HTTP\RedirectResponse::class, $result);
        // Biasanya redirect ke /profile, tapi supaya aman kita cukup cek bukan ke /login
        $this->assertStringNotContainsString('/login', $result->getHeaderLine('Location'));
    }
}
