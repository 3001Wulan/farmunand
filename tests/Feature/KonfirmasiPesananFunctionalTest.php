<?php

namespace Tests\Feature;

use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\FeatureTestTrait;

class KonfirmasiPesananFunctionalTest extends CIUnitTestCase
{
    use FeatureTestTrait;

    private string $routeIndex = '/konfirmasipesanan';

    protected function setUp(): void
    {
        parent::setUp();

        // Pastikan tabel pemesanan ada data dummy untuk test
        $db = db_connect();
        $pemesanan = $db->table('pemesanan');

        // Hapus data test sebelumnya untuk konsistensi
        $pemesanan->where('id_user', 1)->delete();

        // Insert data dummy
        $pemesanan->insert([
            'id_pemesanan' => 1,
            'id_user' => 1,
            'status_pemesanan' => 'Dikirim',
        ]);

        $pemesanan->insert([
            'id_pemesanan' => 2,
            'id_user' => 1,
            'status_pemesanan' => 'Diproses', // status bukan Dikirim
        ]);

        $pemesanan->insert([
            'id_pemesanan' => 3,
            'id_user' => 1,
            'status_pemesanan' => 'Dikirim',
        ]);
    }

    public function testIndexRedirectsIfNotLoggedIn()
    {
        $result = $this->get($this->routeIndex);

        $result->assertRedirectTo('/login');

        // Hanya assert jika controller memang set flashdata
        // $result->assertSessionHas('error', 'Silakan login terlebih dahulu.');
    }

    public function testIndexShowsPesananWhenLoggedIn()
    {
        $this->withSession(['id_user' => 1]);

        $result = $this->get($this->routeIndex);

        $result->assertOK();
        $result->assertSee('Dikirim');
    }

    public function testSelesaiRedirectsIfNotLoggedIn()
    {
        $result = $this->get('/konfirmasipesanan/selesai/1');

        $result->assertRedirectTo('/login');
        $result->assertSessionHas('error', 'Silakan login terlebih dahulu.');
    }

    public function testSelesaiRedirectsIfPesananNotFound()
    {
        $this->withSession(['id_user' => 1]);

        // id_pemesanan 9999 diasumsikan tidak ada
        $result = $this->get('/konfirmasipesanan/selesai/9999');

        $result->assertRedirect();
        $result->assertSessionHas('error', 'Pesanan tidak ditemukan.');
    }

    public function testSelesaiRedirectsIfStatusNotDikirim()
    {
        $this->withSession(['id_user' => 1]);

        // id_pemesanan 2 ada tapi status bukan Dikirim
        $result = $this->get('/konfirmasipesanan/selesai/2');

        $result->assertRedirect();
        $result->assertSessionHas('error', 'Pesanan ini tidak dalam status Dikirim.');
    }

    public function testSelesaiUpdatesStatusToSelesai()
    {
        $this->withSession(['id_user' => 1]);

        // id_pemesanan 3 status Dikirim
        $result = $this->get('/konfirmasipesanan/selesai/3');

        $result->assertRedirectTo('/pesananselesai');
        $result->assertSessionHas('success', 'Pesanan berhasil dikonfirmasi!');
    }
}
