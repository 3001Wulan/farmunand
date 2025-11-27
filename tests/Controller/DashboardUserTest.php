<?php

namespace Tests\Unit;

use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\FeatureTestTrait;

class DashboardUserTest extends CIUnitTestCase
{
    use FeatureTestTrait;

    public function testDashboardUserRedirectsToLoginWhenNotLoggedIn()
    {
        $result = $this->get('/dashboarduser');

        $result->assertStatus(302);
        $result->assertRedirectTo('/login');
    }

    public function testDashboardUserAccessibleForUser()
    {
        $result = $this->withSession([
                'id_user'   => 1,
                'username'  => 'user_test',
                'role'      => 'user',
                'logged_in' => true,
            ])
            ->get('/dashboarduser');

        $result->assertStatus(200);
        $this->assertNotEmpty($result->getBody());
    }
}
