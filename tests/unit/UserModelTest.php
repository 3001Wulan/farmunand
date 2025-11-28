<?php

namespace Tests\Unit;

use App\Models\UserModel;
use App\Models\PesananModel;
use CodeIgniter\Test\CIUnitTestCase;

/**
 * Fake PesananModel untuk dipakai di dalam hasPendingOrders().
 * Tidak menyentuh DB, hanya mencatat log dan mengembalikan angka dummy.
 */
class PesananModelFake extends PesananModel
{
    /** @var array<int, array> */
    public array $log = [];

    /** @var int jumlah yang akan dikembalikan oleh countAllResults() */
    public int $countAllResultsReturn = 0;

    public function __construct()
    {
        // Jangan panggil parent::__construct() supaya tidak konek DB
        // parent::__construct();
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

/**
 * TestableUserModel:
 * - Tidak panggil parent::__construct() → tidak konek DB.
 * - Override countAllResults() untuk getTotalUser().
 * - Override createPesananModel() untuk mengembalikan PesananModelFake.
 */
class TestableUserModel extends UserModel
{
    /** Log panggilan method di UserModel sendiri */
    public array $log = [];

    /** Nilai yang dikembalikan countAllResults() */
    public int $countAllResultsReturn = 0;

    /** Fake PesananModel yang akan dipakai oleh hasPendingOrders() */
    public ?PesananModelFake $pesananFake = null;

    public function __construct()
    {
        // skip parent::__construct() → no DB
    }

    public function countAllResults(bool $reset = true, bool $test = false)
    {
        $this->log[] = ['countAllResults', $reset, $test];
        return $this->countAllResultsReturn;
    }

    // Override helper yang kita tambahkan di UserModel
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

    /** 🧪 Metadata dasar UserModel (table, primaryKey, allowedFields) */
    public function testMetadataUserModelBenar(): void
    {
        $ref = new \ReflectionClass(UserModel::class);

        // instance dummy tanpa konek DB
        $instanceForMeta = new class extends UserModel {
            public function __construct() {}
        };

        // $table
        $this->assertTrue($ref->hasProperty('table'));
        $propTable = $ref->getProperty('table');
        $propTable->setAccessible(true);
        $this->assertSame('users', $propTable->getValue($instanceForMeta));

        // $primaryKey
        $this->assertTrue($ref->hasProperty('primaryKey'));
        $propPK = $ref->getProperty('primaryKey');
        $propPK->setAccessible(true);
        $this->assertSame('id_user', $propPK->getValue($instanceForMeta));

        // $allowedFields
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

    /** 🧪 getTotalUser() harus memanggil countAllResults() */
    public function testGetTotalUserMemanggilCountAllResults(): void
    {
        $this->model->countAllResultsReturn = 7;

        $total = $this->model->getTotalUser();

        $this->assertSame(7, $total);
        $this->assertSame(['countAllResults', true, false], $this->model->log[0]);
    }

    /** 🧪 hasPendingOrders() TRUE kalau ada pesanan belum selesai */
    public function testHasPendingOrdersTrueJikaAdaPesananBelumSelesai(): void
    {
        $fake = new PesananModelFake();
        $fake->countAllResultsReturn = 3;   // seolah-olah ada 3 pesanan pending

        $this->model->pesananFake = $fake;

        $result = $this->model->hasPendingOrders(10);

        // Secara logika → true
        $this->assertTrue($result);

        // Pastikan helper dipanggil
        $this->assertSame(['createPesananModel'], $this->model->log[0]);

        // Pastikan chain ke PesananModel sesuai
        $this->assertSame(['where', 'id_user', 10], $fake->log[0]);
        $this->assertSame(['where', 'status_pemesanan !=', 'Selesai'], $fake->log[1]);
        $this->assertSame('countAllResults', $fake->log[2][0]);
    }

    /** 🧪 hasPendingOrders() FALSE kalau tidak ada pesanan pending */
    public function testHasPendingOrdersFalseJikaTidakAdaPesananBelumSelesai(): void
    {
        $fake = new PesananModelFake();
        $fake->countAllResultsReturn = 0;   // tidak ada pesanan pending

        $this->model->pesananFake = $fake;

        $result = $this->model->hasPendingOrders(99);

        $this->assertFalse($result);

        // Tetap harus memfilter by user dan status
        $this->assertSame(['where', 'id_user', 99], $fake->log[0]);
        $this->assertSame(['where', 'status_pemesanan !=', 'Selesai'], $fake->log[1]);
    }
}
