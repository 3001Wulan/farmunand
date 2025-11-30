<?php

namespace Tests\Feature;

use CodeIgniter\Test\FeatureTestTrait;
use CodeIgniter\Test\CIUnitTestCase;
use App\Models\UserModel;
use App\Models\AlamatModel;

class MemilihAlamatFunctionalTest extends CIUnitTestCase
{
    use FeatureTestTrait;

    private function createUser()
    {
        $userModel = new UserModel();

        // email RANDOM supaya tidak duplicate
        $email = 'test' . rand(1000, 9999) . '@mail.com';

        $userId = $userModel->insert([
            'nama_lengkap' => 'User Test',
            'email'        => $email,
            'password'     => password_hash('123456', PASSWORD_DEFAULT)
        ]);

        return $userId;
    }

    private function createAlamat($userId)
    {
        $alamatModel = new AlamatModel();

        return $alamatModel->insert([
            'id_user'       => $userId,
            'nama_penerima' => 'Budi',
            'jalan'         => 'Jl Test',
            'no_telepon'    => '08123',
            'kota'          => 'Padang',
            'provinsi'      => 'Sumbar',
            'kode_pos'      => '25000',
            'aktif'         => 1
        ]);
    }

    public function testIndexBerhasil()
    {
        $userId = $this->createUser();

        $result = $this->withSession([
            'id_user' => $userId
        ])->get('/memilihalamat');

        $result->assertStatus(200);
    }

    public function testTambahAlamatBerhasil()
    {
        $userId = $this->createUser();

        $result = $this->withSession([
            'id_user' => $userId
        ])->post('/memilihalamat/tambah', [
            'nama_penerima' => 'Budi',
            'jalan'         => 'Jl Mawar',
            'no_telepon'    => '08123',
            'kota'          => 'Padang',
            'provinsi'      => 'Sumbar',
            'kode_pos'      => '25000'
        ]);

        $result->assertRedirectTo('/memilihalamat');
    }

    public function testPilihAlamatBerhasil()
    {
        $userId = $this->createUser();
        $alamatId = $this->createAlamat($userId);

        $result = $this->withSession([
            'id_user' => $userId
        ])->get("/memilihalamat/pilih/$alamatId");

        $result->assertJSONFragment(['success' => true]);
    }

    public function testUbahAlamatBerhasil()
{
    $userId = $this->createUser();
    $alamatId = $this->createAlamat($userId);

    $payload = [
        'nama_penerima' => 'Nama Baru',
        'jalan'         => 'Jalan Baru',
        'no_telepon'    => '080000',
        'kota'          => 'Bukittinggi',
        'provinsi'      => 'Sumbar',
        'kode_pos'      => '26000'
    ];

    $result = $this->withSession([
        'id_user' => $userId
    ])
    ->withBody(json_encode($payload))
    ->withHeaders(['Content-Type' => 'application/json'])
    ->post("/memilihalamat/ubah/$alamatId");

    $result->assertJSONFragment(['success' => true]);
}

}
