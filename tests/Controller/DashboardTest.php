<?php

namespace Tests\Unit;

use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\FeatureTestTrait;

class DashboardTest extends CIUnitTestCase
{
    use FeatureTestTrait;

    public function testDashboardRedirectsToLoginWhenNotLoggedIn()
    {
        $result = $this->get('/dashboard');

        $result->assertStatus(302);
        $result->assertRedirectTo('/login');
    }

    public function testDashboardAccessibleForAdmin()
    {
        $result = $this->withSession([
                'id_user'   => 1,
                'username'  => 'admin_test',
                'role'      => 'admin',
                'logged_in' => true,
            ])
            ->get('/dashboard');

        $result->assertStatus(200);
        // cukup pastikan view ke-load (nggak perlu cek teks spesifik)
        $this->assertNotEmpty($result->getBody());
    }
}
