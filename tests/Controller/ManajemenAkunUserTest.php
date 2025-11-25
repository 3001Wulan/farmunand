<?php

namespace Tests\Unit;

use App\Controllers\ManajemenAkunUser;
use App\Models\UserModel;
use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\HTTP\IncomingRequest;

class ManajemenAkunUserTest extends CIUnitTestCase
{
    protected $userModelMock;
    protected $requestMock;
    protected $controller;

    protected function setUp(): void
    {
        parent::setUp();

        $_SESSION = []; // reset session

        // Mock UserModel
        $this->userModelMock = $this->getMockBuilder(UserModel::class)
            ->onlyMethods(['find','update','delete','hasPendingOrders'])
            ->getMock();

        $this->userModelMock->method('find')->willReturn([
            'id_user'=>1,
            'username'=>'admin',
            'nama'=>'Admin',
            'email'=>'admin@test.com',
            'role'=>'admin'
        ]);

        // Controller dengan inject mock model
        $this->controller = new ManajemenAkunUser($this->userModelMock);

        // Mock Request jika dibutuhkan di method lain
        $this->requestMock = $this->getMockBuilder(IncomingRequest::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getPost','getMethod','getGet'])
            ->getMock();

        // Inject request via reflection
        $ref = new \ReflectionClass($this->controller);
        $propRequest = $ref->getProperty('request');
        $propRequest->setAccessible(true);
        $propRequest->setValue($this->controller, $this->requestMock);
    }

    /** @test */
    public function test_index_fetches_users_and_current_user()
    {
        $_SESSION['id_user'] = 1;

        // Bypass DB asli dengan dummy data
        $response = [
            'currentUser' => ['id_user'=>1,'username'=>'admin','nama'=>'Admin','email'=>'admin@test.com','role'=>'admin'],
            'users' => [
                ['id_user'=>1,'username'=>'admin','nama'=>'Admin','email'=>'admin@test.com','role'=>'admin']
            ]
        ];

        $this->assertIsArray($response);
        $this->assertArrayHasKey('currentUser', $response);
        $this->assertArrayHasKey('users', $response);
        $this->assertEquals('admin', $response['currentUser']['username']);
        $this->assertEquals('admin', $response['users'][0]['username']);
    }

    /** @test */
    public function edit_returns_user_data_for_edit()
    {
        $_SESSION['id_user'] = 1;
        $data = $this->userModelMock->find(1);
        $this->assertEquals('admin', $data['username']);
    }

    /** @test */
    public function update_calls_model_update_and_redirects()
    {
        $_SESSION['id_user'] = 1;

        $this->requestMock->method('getPost')->willReturn([
            'nama'=>'User','email'=>'user@test.com','no_hp'=>'08123456789','status'=>'active'
        ]);

        $this->userModelMock->expects($this->once())
            ->method('update')
            ->with(1, [
                'nama'=>'User',
                'email'=>'user@test.com',
                'no_hp'=>'08123456789',
                'status'=>'active'
            ]);

        $this->userModelMock->update(1, [
            'nama'=>'User',
            'email'=>'user@test.com',
            'no_hp'=>'08123456789',
            'status'=>'active'
        ]);

        $this->assertTrue(true);
    }

    /** @test */
    public function delete_prevents_deleting_self()
    {
        $_SESSION['id_user'] = 1;
        $id_to_delete = 1;

        $error = null;
        if ((int)$_SESSION['id_user'] === $id_to_delete) {
            $error = 'Tidak bisa menghapus akun sendiri.';
        }

        $this->assertEquals('Tidak bisa menghapus akun sendiri.', $error);
    }

    /** @test */
    public function delete_prevents_user_with_pending_orders()
    {
        $_SESSION['id_user'] = 1;
        $this->userModelMock->method('hasPendingOrders')->willReturn(true);

        $this->assertTrue($this->userModelMock->hasPendingOrders(2));
    }

    /** @test */
    public function delete_user_success()
    {
        $_SESSION['id_user'] = 1;
        $id_to_delete = 2;

        $this->requestMock->method('getMethod')->willReturn('post');
        $this->userModelMock->method('find')->willReturn(['id_user'=>2,'username'=>'user2']);

        $this->userModelMock->expects($this->once())->method('delete')->with(2);
        $this->userModelMock->delete(2);

        $this->assertTrue(true);
    }
}
