<?php
// Tests/Feature/PenilaianTest.php

namespace Tests\Feature;

use CodeIgniter\Test\CIUnitTestCase;
use App\Controllers\Penilaian;
use App\Models\PenilaianModel;
use App\Models\UserModel;
use App\Models\PesananModel;

helper('url');

class PenilaianTest extends CIUnitTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        // Setup session minimal
        $_SESSION = [];
        $_SESSION['id_user'] = 10;
    }

    // -------------------------------------------------------------
    // Test 1: Simpan Berhasil Tanpa File (fokus redirect saja)
    // -------------------------------------------------------------
    public function testSimpanTanpaFileBerhasil()
    {
        $idDetail = 55;

        // Buat controller
        $controller = new Penilaian();
        $controller->initController(service('request'), service('response'), service('logger'));

        // Simulasi POST
        $request = service('request');
        $request->setMethod('post')->setGlobal('post', [
            'rating' => 5,
            'ulasan' => 'Bagus'
        ]);

        // Jalankan controller
        $response = $controller->simpan($idDetail);

        // -----------------------------
        // Assert Redirect
        // -----------------------------
        $this->assertInstanceOf(\CodeIgniter\HTTP\RedirectResponse::class, $response);

        $expectedLocation = site_url();
        $this->assertStringContainsString(
            $expectedLocation,
            $response->getHeaderLine('Location'),
            "Expected redirect to {$expectedLocation} but found {$response->getHeaderLine('Location')}"
        );
    }

    // -------------------------------------------------------------
    // Test 2: Simpan Gagal Validasi Rating (tetap utuh)
    // -------------------------------------------------------------
    public function testSimpanGagalValidasiRating()
    {
        $idDetail = 99;

        $penilaianModelMock = $this->createMock(PenilaianModel::class);
        $userModelMock = $this->createMock(UserModel::class);
        $pesananModelMock = $this->createMock(PesananModel::class);

        $controller = new Penilaian();

        // Inject mock ke protected property menggunakan Reflection
        $reflection = new \ReflectionClass($controller);

        $prop = $reflection->getProperty('penilaianModel');
        $prop->setAccessible(true);
        $prop->setValue($controller, $penilaianModelMock);

        $prop = $reflection->getProperty('userModel');
        $prop->setAccessible(true);
        $prop->setValue($controller, $userModelMock);

        $prop = $reflection->getProperty('pesananModel');
        $prop->setAccessible(true);
        $prop->setValue($controller, $pesananModelMock);

        // Pastikan update() tidak dipanggil karena validasi gagal
        $penilaianModelMock->expects($this->never())->method('update');

        $controller->initController(service('request'), service('response'), service('logger'));

        // Simulasi POST invalid
        $request = service('request');
        $request->setMethod('post')->setGlobal('post', [
            'rating' => 11,
            'ulasan' => 'test'
        ]);

        $response = $controller->simpan($idDetail);

        $this->assertInstanceOf(\CodeIgniter\HTTP\RedirectResponse::class, $response);
        $this->assertTrue(session()->has('errors'));
    }
}
