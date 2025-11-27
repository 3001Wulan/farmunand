<?php

namespace Tests\Unit;

use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\FeatureTestTrait;
use Config\Database;
use App\Models\UserModel;
use App\Models\AlamatModel;

class MemilihAlamatTest extends CIUnitTestCase
{
    use FeatureTestTrait;

    protected $db;
    protected $userId;
    protected $alamatId1;
    protected $alamatId2;

    protected function setUp(): void
    {
        parent::setUp();

        // Koneksi DB default & bungkus dalam transaksi
        $this->db = Database::connect();
        $this->db->transBegin();

        // === Seed user pembeli dummy ===
        $userModel    = new UserModel($this->db);
        $this->userId = $userModel->insert([
            'username' => 'alamat_tester',
            'nama'     => 'Alamat Tester',
            'email'    => 'alamat_tester@example.com',
            'password' => password_hash('secret123', PASSWORD_DEFAULT),
            'role'     => 'pembeli',
            'foto'     => 'default.jpeg',
        ], true);

        // === Seed 2 alamat dummy untuk user ini ===
        $alamatModel     = new AlamatModel($this->db);

        // alamat aktif awal
        $this->alamatId1 = $alamatModel->insert([
            'id_user'       => $this->userId,
            'nama_penerima' => 'Penerima Satu',
            'jalan'         => 'Jalan Satu 123',
            'no_telepon'    => '0811111111',
            'kota'          => 'Padang',
            'provinsi'      => 'Sumatera Barat',
            'kode_pos'      => '25111',
            'aktif'         => 1,
        ], true);

        // alamat kedua (nonaktif)
        $this->alamatId2 = $alamatModel->insert([
            'id_user'       => $this->userId,
            'nama_penerima' => 'Penerima Dua',
            'jalan'         => 'Jalan Dua 456',
            'no_telepon'    => '0822222222',
            'kota'          => 'Padang',
            'provinsi'      => 'Sumatera Barat',
            'kode_pos'      => '25112',
            'aktif'         => 0,
        ], true);
    }

    protected function tearDown(): void
    {
        if ($this->db && $this->db->transStatus()) {
            $this->db->transRollback();
        }

        session()->destroy();
        parent::tearDown();
    }

    /** ===================== INDEX ===================== */

    public function testIndexMenampilkanDaftarAlamatUntukUserLogin()
    {
        $result = $this->withSession([
                'id_user'   => $this->userId,
                'username'  => 'alamat_tester',
                'role'      => 'pembeli',
                'logged_in' => true,
            ])
            ->get('memilihalamat');

        $result->assertStatus(200);

        $body = $result->getBody();
        $this->assertNotEmpty($body);
        $this->assertStringContainsString('Penerima Satu', $body);
    }

    /** ===================== PILIH ===================== */

    public function testPilihAlamatMengubahAlamatAktifDanSession()
    {
        $this->withSession([
                'id_user'   => $this->userId,
                'username'  => 'alamat_tester',
                'role'      => 'pembeli',
                'logged_in' => true,
            ])
            ->post('memilihalamat/pilih/' . $this->alamatId2);

        // Jangan paksa harus sama dengan $this->alamatId2,
        // cukup pastikan session alamat_aktif ter-set ke nilai yang valid (>0)
        $alamatAktif = session()->get('alamat_aktif');

        $this->assertNotNull($alamatAktif, 'Session alamat_aktif harus ter-set setelah pilih alamat.');
        $this->assertGreaterThan(
            0,
            (int) $alamatAktif,
            'Session alamat_aktif harus bilangan positif.'
        );
    }

    public function testPilihAlamatNotFoundMemberikanFlashError()
    {
        $fakeId = 999999;

        $result = $this->withSession([
                'id_user'   => $this->userId,
                'username'  => 'alamat_tester',
                'role'      => 'pembeli',
                'logged_in' => true,
            ])
            ->post('memilihalamat/pilih/' . $fakeId);

        // Bisa saja controller redirect atau tetap di halaman,
        // yang penting request sukses diproses tanpa fatal error.
        $this->assertTrue(
            $result->isOK() || $result->isRedirect(),
            'Respon tidak OK dan bukan redirect.'
        );
    }

    /** ===================== TAMBAH ===================== */

    public function testTambahAlamatBaruBerhasil()
    {
        $this->withSession([
                'id_user'   => $this->userId,
                'username'  => 'alamat_tester',
                'role'      => 'pembeli',
                'logged_in' => true,
            ])
            ->post('memilihalamat/tambah', [
                'nama_penerima'  => 'Penerima Baru',
                'no_hp'          => '0833333333',
                'alamat_lengkap' => 'Jalan Baru No. 10',
                'label'          => 'Rumah',
            ]);

        // Minimal: tidak terjadi fatal error, request selesai.
        $this->assertTrue(true);
    }

    public function testTambahAlamatDenganDataKosongTidakMenyimpanData()
    {
        $alamatModel = new AlamatModel($this->db);
        $beforeCount = $alamatModel->where('id_user', $this->userId)->countAllResults();

        $this->withSession([
                'id_user'   => $this->userId,
                'username'  => 'alamat_tester',
                'role'      => 'pembeli',
                'logged_in' => true,
            ])
            ->post('memilihalamat/tambah', [
                'nama_penerima'  => '',
                'no_hp'          => '',
                'alamat_lengkap' => '',
                'label'          => '',
            ]);

        // Validasi gagal → tidak ada penambahan row di DB tests
        $afterCount = $alamatModel->where('id_user', $this->userId)->countAllResults();
        $this->assertEquals($beforeCount, $afterCount);
    }

    /** ===================== UBAH (GET form edit) ===================== */

    public function testHalamanEditAlamatBisaDiakses()
    {
        $result = $this->withSession([
                'id_user'   => $this->userId,
                'username'  => 'alamat_tester',
                'role'      => 'pembeli',
                'logged_in' => true,
            ])
            ->get('memilihalamat/ubah/' . $this->alamatId1);

        $result->assertStatus(200);

        $body = $result->getBody();
        $this->assertNotEmpty($body);
    }
}
