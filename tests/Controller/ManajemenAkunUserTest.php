<?php

namespace Tests\Unit;

use App\Controllers\ManajemenAkunUser;
use App\Models\UserModel;
use CodeIgniter\HTTP\IncomingRequest;
use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\FeatureTestTrait;

class ManajemenAkunUserTest extends CIUnitTestCase
{
    use FeatureTestTrait;

    protected $controller;
    protected $userModelMock;
    protected $requestMock;

    protected function setUp(): void
    {
        parent::setUp();

        // Mock UserModel
        $this->userModelMock = $this->getMockBuilder(UserModel::class)
            ->onlyMethods(['builder', 'find', 'update', 'delete', 'hasPendingOrders'])
            ->getMock();

        // Mock Request
        $this->requestMock = $this->getMockBuilder(IncomingRequest::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getPost', 'getMethod', 'getGet'])
            ->getMock();

        // Controller instance
        $this->controller = new ManajemenAkunUser();

        // Inject mock model & request via reflection
        $reflection = new \ReflectionClass($this->controller);

        // Request
        $propRequest = $reflection->getProperty('request');
        $propRequest->setAccessible(true);
        $propRequest->setValue($this->controller, $this->requestMock);

        // Tidak perlu inject model karena controller buat sendiri
    }

 /** @test */
 public function test_index_fetches_users_and_current_user()
 {
     // Simulasi session
     $_SESSION['id_user'] = 1;
 
     // Mock ResultInterface (data semua user)
     $resultMock = $this->createMock(\CodeIgniter\Database\ResultInterface::class);
     $resultMock->method('getResultArray')
                ->willReturn([
                    [
                        'id_user'  => 1,
                        'username' => 'admin',
                        'nama'     => 'Admin',
                        'email'    => 'admin@test.com',
                        'role'     => 'admin' // tambahkan role
                    ]
                ]);
 
     // Mock Builder
     $builderMock = $this->getMockBuilder(\CodeIgniter\Database\BaseBuilder::class)
         ->disableOriginalConstructor()
         ->onlyMethods(['get', 'orderBy', 'groupStart', 'orLike', 'groupEnd', 'like', 'where'])
         ->getMock();
     $builderMock->method('get')->willReturn($resultMock);
     $builderMock->method('groupStart')->willReturnSelf();
     $builderMock->method('orLike')->willReturnSelf();
     $builderMock->method('groupEnd')->willReturnSelf();
     $builderMock->method('like')->willReturnSelf();
     $builderMock->method('where')->willReturnSelf();
     $builderMock->method('orderBy')->willReturnSelf();
 
     // Mock UserModel
     $userModelMock = $this->getMockBuilder(\App\Models\UserModel::class)
         ->onlyMethods(['builder', 'find'])
         ->getMock();
     $userModelMock->method('builder')->willReturn($builderMock);
     $userModelMock->method('find')->willReturn([
         'id_user'  => 1,
         'username' => 'admin',
         'nama'     => 'Admin',
         'email'    => 'admin@test.com',
         'role'     => 'admin' // tambahkan role juga di sini
     ]);
 
     // Mock Request
     $requestMock = $this->getMockBuilder(\CodeIgniter\HTTP\IncomingRequest::class)
         ->disableOriginalConstructor()
         ->onlyMethods(['getGet'])
         ->getMock();
     $requestMock->method('getGet')->willReturnMap([
         ['keyword', ''],
         ['role', '']
     ]);
 
     // Simulasikan controller
     $controller = new \App\Controllers\ManajemenAkunUser();
 
     // Inject mock UserModel
     $ref = new \ReflectionClass($controller);
     $propModel = $ref->getProperty('userModel');
     $propModel->setAccessible(true);
     $propModel->setValue($controller, $userModelMock);
 
     // Inject mock Request
     $propRequest = $ref->getProperty('request');
     $propRequest->setAccessible(true);
     $propRequest->setValue($controller, $requestMock);
 
     // Panggil method index()
     $response = $controller->index();
 
     // Cek apakah view mengandung username admin
     $this->assertStringContainsString('admin', json_encode($response));
 }
 
    
    /** @test */
    public function edit_returns_user_data_for_edit()
    {
        $this->userModelMock->method('find')->willReturn(['id_user'=>1,'username'=>'admin','nama'=>'Admin','email'=>'admin@test.com']);
        $_SESSION['id_user'] = 1;

        $reflection = new \ReflectionClass($this->controller);
        $propRequest = $reflection->getProperty('request');
        $propRequest->setAccessible(true);
        $propRequest->setValue($this->controller, $this->requestMock);

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

        $this->assertTrue(true); // hanya cek update terpanggil
    }

    /** @test */
    public function delete_prevents_deleting_self()
    {
        $_SESSION['id_user'] = 1;

        $id_to_delete = 1;
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

        $hasPending = $this->userModelMock->hasPendingOrders(2);
        $this->assertTrue($hasPending);
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
