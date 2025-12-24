<?php

namespace Tests\Feature;

use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\FeatureTestTrait;
use App\Models\UserModel;
use App\Controllers\MelihatLaporan;

class MelihatLaporanFunctionalTest extends CIUnitTestCase
{
    use FeatureTestTrait;

    protected $testUserId;

    protected function setUp(): void
    {
        parent::setUp();

        // Buat user testing jika belum ada
        $userModel = new UserModel();
        $user = $userModel->where('email', 'testuser@example.com')->first();
        $this->testUserId = $user ? $user['id_user'] : $userModel->insert([
            'nama'     => 'Test User',
            'email'    => 'testuser@example.com',
            'password' => password_hash('secret', PASSWORD_DEFAULT),
        ]);

        session()->set('id_user', $this->testUserId);
    }

    // ================= BLACKBOX TEST =================
    public function testExportExcelWithoutFilters()
    {
        $result = $this->get('/melihatlaporan/exportExcel');

        $this->assertEquals(200, $result->getStatusCode());

        $this->assertTrue(
            $result->hasHeader('Content-Type') &&
            strpos($result->getHeaderLine('Content-Type'), 'spreadsheetml') !== false
        );

        $this->assertTrue(
            $result->hasHeader('Content-Disposition') &&
            strpos($result->getHeaderLine('Content-Disposition'), 'laporan_penjualan') !== false
        );

        $body = (string)$result->getBody();
        $this->assertNotEmpty($body);
        $this->assertStringContainsString('Content_Types', $body);
        $this->assertStringContainsString('workbook', $body);
    }

    public function testExportExcelWithFilters()
    {
        $result = $this->get('/melihatlaporan/exportExcel?status=Dikirim&start=2025-01-01&end=2025-12-31');

        $this->assertEquals(200, $result->getStatusCode());

        $this->assertTrue(
            $result->hasHeader('Content-Type') &&
            strpos($result->getHeaderLine('Content-Type'), 'spreadsheetml') !== false
        );

        $this->assertTrue(
            $result->hasHeader('Content-Disposition') &&
            strpos($result->getHeaderLine('Content-Disposition'), 'laporan_penjualan') !== false
        );

        $body = (string)$result->getBody();
        $this->assertNotEmpty($body);
        $this->assertStringContainsString('Content_Types', $body);
        $this->assertStringContainsString('workbook', $body);
    }

    // ================= FUNCTIONAL TEST LANGSUNG CONTROLLER =================
    public function testExportExcelContentDirectController()
    {
        $controller = new MelihatLaporan();

        $_GET['start'] = null;
        $_GET['end'] = null;
        $_GET['status'] = '';

        ob_start();
        $controller->exportExcel();
        $output = ob_get_clean();

        $this->assertNotEmpty($output);
        $this->assertStringContainsString('Content_Types', $output);
        $this->assertStringContainsString('workbook', $output);
    }
}
