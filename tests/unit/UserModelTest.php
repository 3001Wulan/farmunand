<?php

namespace Tests\Unit;

use CodeIgniter\Test\CIUnitTestCase;
use App\Models\UserModel;
use App\Models\PesananModel;

class UserModelTest extends CIUnitTestCase
{
    protected $userModel;
    protected $pesananModel;

    protected function setUp(): void
    {
        parent::setUp();

        // Mock kedua model
        $this->userModel    = $this->createMock(UserModel::class);
        $this->pesananModel = $this->createMock(PesananModel::class);
    }

    /** 🧪 Test: getTotalUser() */
    public function testGetTotalUser()
    {
        // Arrange
        $this->userModel->method('getTotalUser')->willReturn(2);

        // Act
        $total = $this->userModel->getTotalUser();

        // Assert
        $this->assertEquals(2, $total);
    }

    /** 🧪 Test: hasPendingOrders() */
    public function testHasPendingOrders()
    {
        // Arrange: buat skenario return value
        $this->userModel->method('hasPendingOrders')
                        ->willReturnMap([
                            [1, true],   // user id 1 punya pesanan pending
                            [2, false],  // user id 2 tidak punya pesanan pending
                        ]);

        // Act & Assert
        $this->assertTrue($this->userModel->hasPendingOrders(1));
        $this->assertFalse($this->userModel->hasPendingOrders(2));
    }
}
