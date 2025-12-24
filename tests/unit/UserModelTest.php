<?php

namespace Tests\Unit;

use App\Models\UserModel;
use App\Models\PesananModel;
use CodeIgniter\Test\CIUnitTestCase;

class PesananModelFake extends PesananModel
{
    public array $log = [];
    public int $countAllResultsReturn = 0;

    public function __construct()
    {
    }

    public function where($field, $value = null, ?bool $escape = null)
    {
        $this->log[] = ['where', $field, $value];
        return $this;
    }

    public function countAllResults(bool $reset = true, bool $test = false)
    {
        $this->log[] = ['countAllResults', $reset, $test];
        return $this->countAllResultsReturn;
    }
}

class TestableUserModel extends UserModel
{
    public array $log = [];
    public int $countAllResultsReturn = 0;
    public ?PesananModelFake $pesananFake = null;

    public function __construct()
    {
    }

    public function countAllResults(bool $reset = true, bool $test = false)
    {
        $this->log[] = ['countAllResults', $reset, $test];
        return $this->countAllResultsReturn;
    }

    protected function createPesananModel()
    {
        $this->log[] = ['createPesananModel'];
        return $this->pesananFake ?? new PesananModelFake();
    }
}

class UserModelTest extends CIUnitTestCase
{
    private TestableUserModel $model;

    protected function setUp(): void
    {
        parent::setUp();
        $this->model = new TestableUserModel();
    }

    public function testMetadataUserModelBenar(): void
    {
        $ref = new \ReflectionClass(UserModel::class);

        $instanceForMeta = new class extends UserModel {
            public function __construct() {}
        };

        $this->assertTrue($ref->hasProperty('table'));
        $propTable = $ref->getProperty('table');
        $propTable->setAccessible(true);
        $this->assertSame('users', $propTable->getValue($instanceForMeta));

        $this->assertTrue($ref->hasProperty('primaryKey'));
        $propPK = $ref->getProperty('primaryKey');
        $propPK->setAccessible(true);
        $this->assertSame('id_user', $propPK->getValue($instanceForMeta));

        $this->assertTrue($ref->hasProperty('allowedFields'));
        $propAF = $ref->getProperty('allowedFields');
        $propAF->setAccessible(true);
        $af = $propAF->getValue($instanceForMeta);

        $this->assertIsArray($af);
        $this->assertContains('username', $af);
        $this->assertContains('email', $af);
        $this->assertContains('password', $af);
        $this->assertContains('role', $af);
    }

    public function testGetTotalUserMemanggilCountAllResults(): void
    {
        $this->model->countAllResultsReturn = 7;

        $total = $this->model->getTotalUser();

        $this->assertSame(7, $total);
        $this->assertSame(['countAllResults', true, false], $this->model->log[0]);
    }

    public function testHasPendingOrdersTrueJikaAdaPesananBelumSelesai(): void
    {
        $fake = new PesananModelFake();
        $fake->countAllResultsReturn = 3;

        $this->model->pesananFake = $fake;

        $result = $this->model->hasPendingOrders(10);

        $this->assertTrue($result);
        $this->assertSame(['createPesananModel'], $this->model->log[0]);
        $this->assertSame(['where', 'id_user', 10], $fake->log[0]);
        $this->assertSame(['where', 'status_pemesanan !=', 'Selesai'], $fake->log[1]);
        $this->assertSame('countAllResults', $fake->log[2][0]);
    }

    public function testHasPendingOrdersFalseJikaTidakAdaPesananPending(): void
    {
        $fake = new PesananModelFake();
        $fake->countAllResultsReturn = 0;

        $this->model->pesananFake = $fake;

        $result = $this->model->hasPendingOrders(99);

        $this->assertFalse($result);
        $this->assertSame(['where', 'id_user', 99], $fake->log[0]);
        $this->assertSame(['where', 'status_pemesanan !=', 'Selesai'], $fake->log[1]);
    }
}