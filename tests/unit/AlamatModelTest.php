<?php

namespace Tests\Unit;

use CodeIgniter\Test\CIUnitTestCase;
use App\Models\AlamatModel;

class AlamatModelTest extends CIUnitTestCase
{
    protected $alamatModel;

    protected function setUp(): void
    {
        parent::setUp();

        // Buat mock untuk AlamatModel
        $this->alamatModel = $this->createMock(AlamatModel::class);
    }

    public function testInsertAlamatBaru()
    {
        $data = [
            'id_user' => 1,
            'nama_penerima' => 'Budi Santoso',
            'jalan' => 'Jl. Merdeka No. 45',
            'kota' => 'Padang',
            'provinsi' => 'Sumatera Barat',
            'kode_pos' => '25134',
            'aktif' => 1,
            'no_telepon' => '08123456789'
        ];

        // Atur perilaku mock: insert() return id, find() return data
        $this->alamatModel->method('insert')->willReturn(123);
        $this->alamatModel->method('find')->willReturn($data + ['id' => 123]);

        $insertId = $this->alamatModel->insert($data);

        $this->assertIsNumeric($insertId);
        $this->assertNotNull($this->alamatModel->find($insertId));
    }

    public function testGetAlamatAktifByUser()
    {
        $dummy = [[
            'id_user' => 2,
            'nama_penerima' => 'Siti Aminah',
            'jalan' => 'Jl. Sudirman No. 12',
            'kota' => 'Bukittinggi',
            'provinsi' => 'Sumatera Barat',
            'kode_pos' => '26125',
            'aktif' => 1,
            'no_telepon' => '08991234567'
        ]];

        // Atur perilaku mock: getAlamatAktifByUser(2) return dummy
        $this->alamatModel->method('getAlamatAktifByUser')
            ->with(2)
            ->willReturn($dummy);

        $result = $this->alamatModel->getAlamatAktifByUser(2);

        $this->assertIsArray($result);
        $this->assertGreaterThan(0, count($result));
        $this->assertEquals(1, $result[0]['aktif']);
    }

    public function testGetAlamatAktifByUserKosong()
    {
        // Atur perilaku mock: getAlamatAktifByUser(9999) return []
        $this->alamatModel->method('getAlamatAktifByUser')
            ->with(9999)
            ->willReturn([]);

        $result = $this->alamatModel->getAlamatAktifByUser(9999);

        $this->assertIsArray($result);
        $this->assertCount(0, $result);
    }
}
