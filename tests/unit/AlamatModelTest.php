<?php

namespace Tests\Unit;

use CodeIgniter\Test\CIUnitTestCase;
use App\Models\AlamatModel;
use Config\Database;

class AlamatModelTest extends CIUnitTestCase
{
    protected $alamatModel;
    protected $db;

    protected function setUp(): void
    {
        parent::setUp();
        // Koneksi ke database asli (bukan test database)
        $this->db = Database::connect();
        $this->db->transBegin(); // mulai transaksi agar data tidak tersimpan permanen

        $this->alamatModel = new AlamatModel();
    }

    protected function tearDown(): void
    {
        // rollback agar semua data test tidak disimpan di database
        if ($this->db->transStatus() === true) {
            $this->db->transRollback();
        }
        parent::tearDown();
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

        $insertId = $this->alamatModel->insert($data);

        $this->assertIsNumeric($insertId);
        $this->assertNotNull($this->alamatModel->find($insertId));
    }

    public function testGetAlamatAktifByUser()
    {
        // tambahkan data dummy (sementara, tidak disimpan permanen karena di-rollback)
        $this->alamatModel->insert([
            'id_user' => 2,
            'nama_penerima' => 'Siti Aminah',
            'jalan' => 'Jl. Sudirman No. 12',
            'kota' => 'Bukittinggi',
            'provinsi' => 'Sumatera Barat',
            'kode_pos' => '26125',
            'aktif' => 1,
            'no_telepon' => '08991234567'
        ]);

        $result = $this->alamatModel->getAlamatAktifByUser(2);

        $this->assertIsArray($result);
        $this->assertGreaterThan(0, count($result));
        $this->assertEquals(1, $result[0]['aktif']);
    }

    public function testGetAlamatAktifByUserKosong()
    {
        // ambil user yang tidak punya alamat aktif
        $result = $this->alamatModel->getAlamatAktifByUser(9999); // id_user tidak ada

        $this->assertIsArray($result);
        $this->assertCount(0, $result);
    }
}
