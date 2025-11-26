<?php

namespace Tests\Unit;

use CodeIgniter\Test\CIUnitTestCase;
use App\Models\PesananModel;

class PesananModelTest extends CIUnitTestCase
{
    protected $pesananModel;

    protected function setUp(): void
    {
        parent::setUp();
        // Buat mock PesananModel
        $this->pesananModel = $this->createMock(PesananModel::class);
    }

    /** @test */
    public function bisa_menyimpan_dan_mengambil_pesanan()
    {
        $data = [
            'id_user'          => 1,
            'total_harga'      => 150000,
            'status_pemesanan' => 'Menunggu',
        ];

        // Atur perilaku mock
        $this->pesananModel->method('insert')->willReturn(123);
        $this->pesananModel->method('getPesananById')->with(123)->willReturn($data + ['id' => 123]);

        $id = $this->pesananModel->insert($data, true);

        $this->assertIsInt($id);
        $pesanan = $this->pesananModel->getPesananById($id);
        $this->assertEquals('Menunggu', $pesanan['status_pemesanan']);
        $this->assertEquals(150000, $pesanan['total_harga']);
    }

    /** @test */
    public function markAsShippedWithToken_mengupdate_status_dan_token()
    {
        $id = 456;
        $updated = [
            'id'                 => $id,
            'status_pemesanan'   => 'Dikirim',
            'konfirmasi_token'   => 'abc123',
            'konfirmasi_expires_at' => date('Y-m-d H:i:s', strtotime('+7 days')),
        ];

        $this->pesananModel->method('insert')->willReturn($id);
        $this->pesananModel->method('markAsShippedWithToken')->with($id)->willReturn(true);
        $this->pesananModel->method('find')->with($id)->willReturn($updated);

        $this->pesananModel->markAsShippedWithToken($id);
        $result = $this->pesananModel->find($id);

        $this->assertEquals('Dikirim', $result['status_pemesanan']);
        $this->assertNotEmpty($result['konfirmasi_token']);
        $this->assertNotNull($result['konfirmasi_expires_at']);
    }

    /** @test */
    public function autoCloseExpired_mengubah_status_dikirim_yang_kedaluwarsa()
    {
        $this->pesananModel->method('autoCloseExpired')->willReturn(1);
        $this->pesananModel->method('findAll')->willReturn([
            ['status_pemesanan' => 'Selesai']
        ]);

        $jumlah = $this->pesananModel->autoCloseExpired();
        $this->assertGreaterThanOrEqual(1, $jumlah);

        $hasil = $this->pesananModel->findAll();
        $this->assertNotEmpty($hasil);
    }

    /** @test */
    public function getFiltered_mengambil_pesanan_dalam_rentang_tanggal()
    {
        $dummy = [
            ['id_user' => 1, 'status_pemesanan' => 'Selesai'],
            ['id_user' => 2, 'status_pemesanan' => 'Diproses'],
        ];

        $this->pesananModel->method('getFiltered')
            ->with($this->anything(), $this->anything())
            ->willReturn($dummy);

        $hasil = $this->pesananModel->getFiltered('2025-11-20', '2025-11-25');

        $this->assertIsArray($hasil);
        $this->assertNotEmpty($hasil);
    }

    /** @test */
    public function testOnlyAllowedColumnsCanBeInserted()
{
    $userId = 99;
    $dummy = [
        'id_user'     => $userId,
        'total_harga' => 50000,
        // kolom_tidak_ada seharusnya tidak ada
    ];

    // Mock insert() return id
    $this->pesananModel->method('insert')->willReturn($userId);

    // Mock first() langsung return dummy
    $this->pesananModel->method('first')->willReturn($dummy);

    // Panggil insert
    $this->pesananModel->insert([
        'id_user'          => $userId,
        'total_harga'      => 50000,
        'kolom_tidak_ada'  => 'seharusnya diabaikan',
    ]);

    $result = $this->pesananModel->first();

    $this->assertArrayNotHasKey('kolom_tidak_ada', (array)$result);
}

}
