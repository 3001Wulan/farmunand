<?php

namespace Tests\Feature;

use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\FeatureTestTrait;
use App\Models\UserModel;

class DashboardFunctionalTest extends CIUnitTestCase
{
    use FeatureTestTrait;

    /**
     * Helper untuk membuat user dummy
     * Email dibuat random supaya tidak bentrok dengan UNIQUE KEY
     */
    private function createUser(): int
    {
        $userModel = new UserModel();

        $email = 'dash' . rand(1000, 9999) . '@mail.com';

        // Sesuaikan field ini dengan struktur tabel user kamu
        return $userModel->insert([
            'nama_lengkap' => 'User Dashboard',
            'email'        => $email,
            'password'     => password_hash('123456', PASSWORD_DEFAULT),
        ]);
    }

    /**
     * Jika belum login maka harus redirect ke /login
     */
    public function testRedirectToLoginWhenNotLoggedIn()
    {
        $result = $this->get('/dashboard');

        $result->assertStatus(302);
        $result->assertRedirectTo('/login');
    }

    /**
     * Jika sudah login maka dashboard bisa diakses dan mengembalikan status 200
     */
    public function testDashboardCanBeAccessedWhenLoggedIn()
    {
        $userId = $this->createUser();

        $result = $this->withSession([
            'id_user' => $userId,
        ])->get('/dashboard');

        $result->assertStatus(200);
        // Sesuaikan dengan teks yang pasti muncul di view Admin/Dashboard
        $result->assertSee('Dashboard');
    }
}
