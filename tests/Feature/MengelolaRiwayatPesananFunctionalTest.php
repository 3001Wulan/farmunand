<?php

namespace Tests\Feature;

use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\FeatureTestTrait;
use App\Models\PesananModel;

class MengelolaRiwayatPesananFunctionalTest extends CIUnitTestCase
{
    use FeatureTestTrait;

    private string $routeIndex = '/MengelolaRiwayatPesanan';

    protected function setUp(): void
    {
        parent::setUp();

        $db = db_connect();
        $pemesanan = $db->table('pemesanan');

        // Hapus data test sebelumnya agar konsisten
        $pemesanan->where('id_user', 1)->delete();

        // Insert dummy data pesanan
        $pemesanan->insert([
            'id_pemesanan'     => 1,
            'id_user'          => 1,
            'status_pemesanan' => 'Dikirim',
        ]);

        $pemesanan->insert([
            'id_pemesanan'     => 2,
            'id_user'          => 1,
            'status_pemesanan' => 'Dikemas',
        ]);
    }

    public function testIndexRedirectsIfNotLoggedIn()
    {
        session()->destroy();
    
        $result = $this->get('/MengelolaRiwayatPesanan');
    
        $result->assertStatus(200);
    
    
    }
    public function testIndexShowsPesananWhenLoggedIn()
    {
        $this->withSession(['id_user' => 1]);

        $result = $this->get($this->routeIndex);
        $result->assertOK();
        $result->assertSee('Dikirim');
        $result->assertSee('Dikemas');
    }

    public function testUpdateStatusToInvalidStatus()
    {
        $this->withSession(['id_user' => 1]);

        $result = $this->post('mengelolariwayatpesanan/updateStatus/1', [
            'status_pemesanan' => 'Selesai' // Admin tidak boleh langsung set Selesai
        ]);

        $result->assertRedirectTo($this->routeIndex);
        $result->assertSessionHas('error', 'Status tidak valid untuk admin.');
    }

    public function testUpdateStatusPesananNotFound()
    {
        $this->withSession(['id_user' => 1]);

        $result = $this->post('mengelolariwayatpesanan/updateStatus/9999', [
            'status_pemesanan' => 'Dikirim'
        ]);

        $result->assertRedirectTo($this->routeIndex);
        $result->assertSessionHas('error', 'Pesanan tidak ditemukan.');
    }

    public function testUpdateStatusPesananBerhasil()
    {
        $this->withSession(['id_user' => 1]);

        $result = $this->post('mengelolariwayatpesanan/updateStatus/2', [
            'status_pemesanan' => 'Dikirim'
        ]);

        $result->assertRedirectTo($this->routeIndex);
        $result->assertSessionHas('success', 'Status pesanan berhasil diperbarui.');
    }
}
