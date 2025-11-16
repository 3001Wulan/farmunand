<?php

namespace Tests\Support\Controllers;

use App\Controllers\DashboardUser;
use CodeIgniter\Test\CIUnitTestCase;

class DashboardUserTest extends CIUnitTestCase
{
    protected $controller;

    protected function setUp(): void
    {
        parent::setUp();
        $this->controller = new DashboardUser();
    }

    public function test_redirect_if_not_logged_in()
    {
        // Tidak ada session
        unset($_SESSION['id_user']);

        $response = $this->controller->index();

        $this->assertStringContainsString('/login', $response->getHeaderLine('Location'));
    }

    public function test_dashboard_user_returns_view()
    {
        // Simulasikan user login
        $_SESSION['id_user'] = 1;

        // Jalankan controller
        $output = $this->controller->index();   // Return view as STRING

        // Cek bahwa HTML mengandung username
        $this->assertStringContainsString("username", strtolower($output));  // fleksibel
        // Atau jika view kamu menampilkan langsung nama user
        // $this->assertStringContainsString("admin", strtolower($output));

        // Minimal pastikan title dashboard tampil
        $this->assertStringContainsString("Dashboard User", $output);

        // Karena pesanan sukses/pending/batal dikalkulasi, pastikan variabel tampil
        // Ini tergantung bagaimana view Pembeli/dashboarduser.php menampilkannya
        $this->assertStringContainsString("pesanan", strtolower($output));
    }
}
