<?php

namespace Tests\Controllers;

use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\FeatureTestTrait;

class MelihatLaporanTest extends CIUnitTestCase
{
    use FeatureTestTrait;

    protected function setUp(): void
    {
        parent::setUp();

        // Simulasi session user login
        $_SESSION['id_user'] = 1;
    }

    public function testIndexWithFilters()
    {
        // Kirim GET request dengan query params
        $result = $this->call('get', '/melihatlaporan', [
            'start'  => '2025-01-01',
            'end'    => '2025-01-31',
            'status' => 'Selesai'
        ]);

        // Pastikan request sukses
        $result->assertOK();

        // Pastikan view mengandung kata "Selesai"
        $this->assertStringContainsString('Selesai', (string) $result->getBody());
    }

    public function testIndexWithoutFilters()
    {
        // Panggil URL controller
        $result = $this->call('get', '/melihatlaporan');
    
        // Pastikan response OK
        $result->assertOK();
    
        $body = (string) $result->getBody();
    
        // Cek konten yang pasti ada
        $this->assertStringContainsString('<title>Laporan Penjualan</title>', $body);
        $this->assertStringContainsString('<select id="status" name="status"', $body);
        $this->assertStringContainsString('Semua Status', $body);
        $this->assertStringContainsString('<table class="table', $body);
        $this->assertStringContainsString('<tbody id="laporanTable">', $body);
    }
    

}
