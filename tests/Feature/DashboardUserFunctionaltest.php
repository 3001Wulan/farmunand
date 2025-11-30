<?php

namespace Tests\Feature;

use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\FeatureTestTrait;
use App\Models\UserModel;

class DashboardUserFunctionalTest extends CIUnitTestCase
{
    use FeatureTestTrait;

    /**
     * Helper untuk membuat user dummy.
     * Email dibuat random supaya tidak bentrok dengan UNIQUE KEY.
     */
    private function createUser(): int
    {
        $userModel = new UserModel();

        $email = 'dashuser' . rand(1000, 9999) . '@mail.com';

        // Sesuaikan field dengan struktur tabel user kamu
        return $userModel->insert([
            'nama_lengkap' => 'User Dashboard',
            'username'     => 'userdash' . rand(1000, 9999),
            'email'        => $email,
            'password'     => password_hash('123456', PASSWORD_DEFAULT),
            'role'         => 'pembeli',   // kalau tidak ada kolom ini, boleh dihapus
        ]);
    }

    /**
     * Jika belum login maka harus redirect ke /login.
     */
    public function testRedirectToLoginWhenNotLoggedIn()
    {
        $result = $this->get('/dashboarduser');

        $result->assertStatus(302);
        $result->assertRedirectTo('/login');
    }

    /**
     * Jika sudah login maka dashboard user bisa diakses.
     */
    public function testDashboardUserCanBeAccessedWhenLoggedIn()
    {
        $userId = $this->createUser();

        $result = $this->withSession([
            'id_user' => $userId,
        ])->get('/dashboarduser');

        $result->assertStatus(200);

        // Kalau di view ada teks tertentu, boleh tambahkan misalnya:
        // $result->assertSee('Dashboard User');
    }
}
