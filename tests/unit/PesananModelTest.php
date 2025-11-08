<?php

namespace Tests\Unit;

use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\DatabaseTestTrait;
use App\Models\PesananModel;

class PesananModelTest extends CIUnitTestCase
{
    use DatabaseTestTrait;

    protected $pesananModel;

    protected function setUp(): void
    {
        parent::setUp();
        $this->pesananModel = new PesananModel();
    }

    /** @test */
    public function bisa_menyimpan_dan_mengambil_pesanan()
    {
        // Data dummy tanpa FK yang berat
        $data = [
            'id_user'           => 1,
            'id_alamat'         => null,
            'id_pembayaran'     => null,
            'total_harga'       => 150000,
            'status_pemesanan'  => 'Menunggu',
            'created_at'        => date('Y-m-d H:i:s'),
            'updated_at'        => date('Y-m-d H:i:s'),
        ];

        $id = $this->pesananModel->insert($data, true);

        $this->assertIsInt($id, 'ID hasil insert harus integer');

        $pesanan = $this->pesananModel->getPesananById($id);

        $this->assertNotNull($pesanan);
        $this->assertEquals('Menunggu', $pesanan['status_pemesanan']);
        $this->assertEquals(150000, $pesanan['total_harga']);
    }

    /** @test */
    public function markAsShippedWithToken_mengupdate_status_dan_token()
    {
        $id = $this->pesananModel->insert([
            'id_user'           => 1,
            'total_harga'       => 200000,
            'status_pemesanan'  => 'Diproses',
        ], true);

        $this->pesananModel->markAsShippedWithToken($id);

        $updated = $this->pesananModel->find($id);

        $this->assertEquals('Dikirim', $updated['status_pemesanan']);
        $this->assertNotEmpty($updated['konfirmasi_token']);
        $this->assertNotNull($updated['konfirmasi_expires_at']);
    }

    /** @test */
    public function autoCloseExpired_mengubah_status_dikirim_yang_kedaluwarsa()
    {
        $this->pesananModel->insert([
            'id_user'                => 2,
            'status_pemesanan'       => 'Dikirim',
            'konfirmasi_expires_at'  => date('Y-m-d H:i:s', strtotime('-8 days')),
            'confirmed_at'           => null,
        ]);

        $jumlah = $this->pesananModel->autoCloseExpired();

        $this->assertGreaterThanOrEqual(1, $jumlah);

        $hasil = $this->pesananModel
            ->where('status_pemesanan', 'Selesai')
            ->findAll();

        $this->assertNotEmpty($hasil);
    }

    /** @test */
    public function getFiltered_mengambil_pesanan_dalam_rentang_tanggal()
    {
        $tanggal1 = date('Y-m-d H:i:s', strtotime('-3 days'));
        $tanggal2 = date('Y-m-d H:i:s', strtotime('-1 days'));

        $this->pesananModel->insert([
            'id_user'           => 1,
            'total_harga'       => 100000,
            'status_pemesanan'  => 'Selesai',
            'created_at'        => $tanggal1,
        ]);

        $this->pesananModel->insert([
            'id_user'           => 2,
            'total_harga'       => 300000,
            'status_pemesanan'  => 'Diproses',
            'created_at'        => $tanggal2,
        ]);

        $hasil = $this->pesananModel->getFiltered(
            date('Y-m-d', strtotime('-4 days')),
            date('Y-m-d')
        );

        $this->assertIsArray($hasil);
        $this->assertNotEmpty($hasil);
    }

    /** @test */
    public function testOnlyAllowedColumnsCanBeInserted()
{
    $userModel = new \App\Models\UserModel();
    $userId = $userModel->insert([
        'nama_user' => 'Dummy User',
        'email'     => 'dummy@example.com',
        'password'  => password_hash('dummy123', PASSWORD_DEFAULT),
    ]);

    $model = new \App\Models\PesananModel();
    $data = [
        'id_user' => $userId, // gunakan user yang valid
        'total_harga' => 50000,
        'kolom_tidak_ada' => 'seharusnya diabaikan',
    ];

    $model->insert($data);
    $result = $model->where('id_user', $userId)->first();

    // pastikan kolom ilegal tidak tersimpan
    $this->assertArrayNotHasKey('kolom_tidak_ada', (array)$result);
}

}
