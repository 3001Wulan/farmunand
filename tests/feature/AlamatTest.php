<?php

namespace Tests\Unit;

use CodeIgniter\Test\CIUnitTestCase;
use App\Models\AlamatModel;

class AlamatTest extends CIUnitTestCase
{
    protected $alamatModel;

    protected function setUp(): void
    {
        parent::setUp();
        $this->alamatModel = new AlamatModel();
    }

    /** @test */
    public function testInsertAlamatBerhasil()
    {
        $data = [
            'id_user'         => 1 	,
            'alamat_lengkap'  => 'Jl. Khatib Sulaiman No. 45',
            'kecamatan'       => 'Padang Utara',
            'kota'            => 'Padang',
            'provinsi'        => 'Sumatera Barat',
            'kode_pos'        => '25134',
        ];

        $inserted = $this->alamatModel->insert($data);

        $this->assertIsInt($inserted, 'Insert harus mengembalikan ID integer.');
    }

    /** @test */
    public function testAmbilAlamatByUserId()
    {
        $userId = 1;
        $result = $this->alamatModel->where('id_user', $userId)->findAll();

        $this->assertIsArray($result, 'Hasil findAll harus berupa array.');
    }

    /** @test */
    public function testUpdateAlamatBerhasil()
    {
        $dataUpdate = [
            'jalan' => 'Jl. Veteran No. 21'
        ];

        $updated = $this->alamatModel->update(1, $dataUpdate);

        $this->assertTrue($updated, 'Update alamat seharusnya berhasil.');
    }

    /** @test */
    public function testDeleteAlamatBerhasil()
    {
        $deleted = $this->alamatModel->delete(1);

        $this->assertTrue($deleted, 'Delete alamat seharusnya berhasil.');
    }

    /** @test */
    public function testKolomYangDiizinkanSaja()
    {
        $allowedFields = $this->alamatModel->allowedFields;

        $this->assertContains('kota', $allowedFields);
        $this->assertContains('provinsi', $allowedFields);
        $this->assertContains('kode_pos', $allowedFields);
    }
}
