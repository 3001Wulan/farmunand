<?php

namespace Tests\Feature;

use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\FeatureTestTrait;
use App\Models\UserModel;

class ProfilPembeliFunctionalTest extends CIUnitTestCase
{
    use FeatureTestTrait;

    protected $testUserId;

    protected function setUp(): void
    {
        parent::setUp();

        $userModel = new UserModel();
        $user = $userModel->where('email', 'testuser@example.com')->first();
        $this->testUserId = $user ? $user['id_user'] : $userModel->insert([
            'nama'     => 'Test User',
            'username' => 'testuser',
            'email'    => 'testuser@example.com',
            'password' => password_hash('secret', PASSWORD_DEFAULT),
            'no_hp'    => '08123456789',
            'foto'     => 'default.png',
        ]);
    }

    // ================= INDEX =================
    public function testIndexRedirectsWhenNotLoggedIn()
    {
        $result = $this->get('/profile');
        $result->assertRedirect('/login');
        $result->assertSessionHas('error');
    }

    public function testIndexLoadsProfile()
    {
        $result = $this->withSession(['id_user' => $this->testUserId])
                       ->call('get', '/profile');

        $result->assertStatus(200);
        $result->assertSee('Profil Saya');
    }

    // ================= EDIT =================
    public function testEditRedirectsWhenNotLoggedIn()
    {
        $result = $this->get('/profile/edit');
        $result->assertRedirect('/login');
        $result->assertSessionHas('error');
    }

    public function testEditLoadsEditProfile()
    {
        $result = $this->withSession(['id_user' => $this->testUserId])
                       ->call('get', '/profile/edit');

        $result->assertStatus(200);
        $result->assertSee('Edit Profil');
    }

    // ================= UPDATE =================
    public function testUpdateValidationFails()
    {
        $postData = [
            'username' => '',
            'nama'     => '',
            'email'    => 'tidakvalid',
        ];

        $response = $this->withSession(['id_user' => $this->testUserId])
                         ->post('/profile/update', $postData, true);

        $response->assertSessionHas('errors'); // cek flashdata error
    }

    public function testUpdateSucceedsWithoutFoto()
    {
        $postData = [
            'username' => 'userbaru',
            'nama'     => 'Nama Baru',
            'email'    => 'baru@example.com',
            'no_hp'    => '08123456789',
        ];

        // POST data tanpa cek flashdata, cukup cek database
        $this->withSession(['id_user' => $this->testUserId])
             ->post('/profile/update', $postData, true);

        $userModel = new UserModel();
        $updated = $userModel->find($this->testUserId);

        $this->assertEquals('userbaru', $updated['username']);
        $this->assertEquals('Nama Baru', $updated['nama']);
        $this->assertEquals('baru@example.com', $updated['email']);
        $this->assertEquals('08123456789', $updated['no_hp']);
    }
}
