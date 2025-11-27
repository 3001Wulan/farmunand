<?php

namespace Tests\Unit;

use App\Models\PenilaianModel;
use CodeIgniter\Test\CIUnitTestCase;

class PenilaianModelTest extends CIUnitTestCase
{
    /** @var PenilaianModel */
    protected $model;

    protected function setUp(): void
    {
        parent::setUp();
        $this->model = new PenilaianModel();
    }

    public function testModelBisaDiinstansiasi()
    {
        $this->assertInstanceOf(PenilaianModel::class, $this->model);
    }

    public function testNamaTabelDanPrimaryKeyTidakKosong()
    {
        $refClass = new \ReflectionClass(PenilaianModel::class);

        // Cek properti $table
        if ($refClass->hasProperty('table')) {
            $propTable = $refClass->getProperty('table');
            $propTable->setAccessible(true);
            $table = $propTable->getValue($this->model);

            $this->assertNotEmpty($table, 'Nama tabel ($table) di PenilaianModel kosong.');
        } else {
            // Kalau nggak ada properti, jangan bikin error di PHPUnit
            $this->assertTrue(true);
        }

        // Cek properti $primaryKey
        if ($refClass->hasProperty('primaryKey')) {
            $propPK = $refClass->getProperty('primaryKey');
            $propPK->setAccessible(true);
            $primaryKey = $propPK->getValue($this->model);

            $this->assertNotEmpty($primaryKey, 'Primary key ($primaryKey) di PenilaianModel kosong.');
        } else {
            $this->assertTrue(true);
        }
    }

    public function testAllowedFieldsBerupaArrayDanTidakKosong()
    {
        $refClass = new \ReflectionClass(PenilaianModel::class);

        $this->assertTrue(
            $refClass->hasProperty('allowedFields'),
            'PenilaianModel tidak memiliki properti $allowedFields.'
        );

        $prop = $refClass->getProperty('allowedFields');
        $prop->setAccessible(true);

        $allowedFields = $prop->getValue($this->model);

        $this->assertIsArray($allowedFields, 'allowedFields harus berupa array.');
        $this->assertNotEmpty($allowedFields, 'allowedFields di PenilaianModel tidak boleh kosong.');

        // OPTIONAL: kalau kamu mau cek nama kolom spesifik, boleh diaktifkan
        // $this->assertContains('id_user', $allowedFields);
        // $this->assertContains('id_produk', $allowedFields);
        // $this->assertContains('rating', $allowedFields);
    }

    public function testUseTimestampsPropertyTersetDenganBenar()
    {
        $refClass = new \ReflectionClass(PenilaianModel::class);

        if ($refClass->hasProperty('useTimestamps')) {
            $prop = $refClass->getProperty('useTimestamps');
            $prop->setAccessible(true);

            $useTimestamps = $prop->getValue($this->model);
            $this->assertIsBool($useTimestamps);
        } else {
            // Kalau tidak ada, anggap saja lolos
            $this->assertTrue(true);
        }
    }
}
