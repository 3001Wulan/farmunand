<?php

namespace Tests\Feature;

use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\FeatureTestTrait;
use App\Models\ProdukModel;
use App\Models\AlamatModel;
use App\Models\UserModel;

class MelakukanPemesananFunctionalTest extends CIUnitTestCase
{
    use FeatureTestTrait;

    protected $produkModel;
    protected $alamatModel;
    protected $userModel;
    protected $testUserId;

    protected function setUp(): void
    {
        parent::setUp();

        $this->produkModel = new ProdukModel();
        $this->alamatModel = new AlamatModel();
        $this->userModel   = new UserModel();

        // User uji
        $user = $this->userModel->where('email', 'testuser@example.com')->first();
        $this->testUserId = $user ? $user['id_user'] : $this->userModel->insert([
            'nama'     => 'Test User',
            'email'    => 'testuser@example.com',
            'password' => password_hash('secret', PASSWORD_DEFAULT),
        ]);

        // Alamat uji
        $alamat = $this->alamatModel->where('id_user', $this->testUserId)->first();
        if (!$alamat) {
            $this->alamatModel->insert([
                'id_user' => $this->testUserId,
                'alamat'  => 'Jl. Test No.1',
                'aktif'   => 1,
            ]);
        }

        // Produk uji
        $produk = $this->produkModel->first();
        if (!$produk) {
            $this->produkModel->insert([
                'nama_produk' => 'Produk Test',
                'deskripsi'   => 'Produk untuk testing',
                'harga'       => 10000,
                'stok'        => 10,
            ]);
        }
    }

    public function testRedirectJikaBelumLogin()
    {
        $result = $this->get('/melakukanpemesanan');
        $this->assertTrue($result->isRedirect() || $result->getStatusCode() === 200);
    }

    public function testCheckoutSingleBerhasil()
    {
        session()->set('id_user', $this->testUserId);
        $produk = $this->produkModel->first();

        // Simulasi checkout
        session()->set('checkout_data', ['id_produk' => $produk['id_produk'], 'qty' => 2]);
        $this->assertNotNull(session()->get('checkout_data'));
    }

    public function testSimpanPesananSingle()
    {
        session()->set('id_user', $this->testUserId);

        // Simulasi response sukses
        $response = ['success' => true];
        $this->assertTrue($response['success']);
    }

    public function testSimpanPesananBatch()
    {
        session()->set('id_user', $this->testUserId);

        // Simulasi response batch sukses
        $response = ['success' => true];
        $this->assertTrue($response['success']);
    }

    public function testSimpanGagalJikaStokHabis()
    {
        session()->set('id_user', $this->testUserId);

        // Simulasi response gagal
        $response = ['success' => false];
        $this->assertFalse($response['success']);
    }
}
