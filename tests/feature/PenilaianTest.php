<?php
// Tests/Feature/PenilaianTest.php

namespace Tests\Feature;

use CodeIgniter\Test\CIUnitTestCase;
// Hapus: use CodeIgniter\Test\ControllerTestTrait; 

use App\Controllers\Penilaian;
use App\Models\PenilaianModel;
use App\Models\UserModel;
use App\Models\PesananModel;

// PENTING: Muat helper 'url' agar fungsi site_url() tersedia
helper('url');

class PenilaianTest extends CIUnitTestCase
{
    // Hapus: use ControllerTestTrait;

    protected function setUp(): void
    {
        parent::setUp();
        // Setup session
        $_SESSION['id_user'] = 10;
        
        // Opsional: Pastikan baseURL diset, meskipun helper('url') sudah membantu
        // service('app')->setbaseURL('http://localhost:8080/'); 
    }

    // -------------------------------------------------------------
    // Test 1: Simpan Berhasil Tanpa File (Perbaikan Assertion Redirect)
    // -------------------------------------------------------------
    public function testSimpanTanpaFileBerhasil()
    {
        $idDetail = 55;

        // 1. Buat Mock untuk semua Model
        $penilaianModelMock = $this->createMock(PenilaianModel::class);
        $userModelMock = $this->createMock(UserModel::class);
        $pesananModelMock = $this->createMock(PesananModel::class);

        // 2. Tentukan Perilaku Mock
        $penilaianModelMock->expects($this->once())
            ->method('update')
            ->willReturn(true);

        // 3. Buat Instance Controller dengan Mock
        $controller = new Penilaian(
            $penilaianModelMock, 
            $userModelMock, 
            $pesananModelMock
        );
        
        // 4. Inisialisasi Lingkungan HTTP
        $controller->initController(service('request'), service('response'), service('logger'));

        // 5. Simulasikan Data POST yang dikirim
        $request = service('request');
        $request->setMethod('post')->setGlobal('post', [
            'rating' => 5,
            'ulasan' => 'Bagus'
        ]);

        // 6. Jalankan Metode Controller Secara Langsung
        $response = $controller->simpan($idDetail); 

        // 7. Assert Hasil (Verifikasi Redirect)
        
        // Cek bahwa response adalah instance RedirectResponse
        $this->assertInstanceOf(\CodeIgniter\HTTP\RedirectResponse::class, $response);

        // Buat URL yang diharapkan menggunakan site_url(). 
        // Ini akan memasukkan 'index.php' jika diperlukan oleh konfigurasi CI.
        $expectedLocation = site_url('penilaian/daftar'); 

        // Assert bahwa header Location mengandung URL yang benar
        $this->assertStringContainsString(
            $expectedLocation, 
            $response->getHeaderLine('Location'),
            "Expected redirect to {$expectedLocation} but found {$response->getHeaderLine('Location')}"
        );
        
        // Pastikan flashdata sukses diset (untuk verifikasi logika Controller)
        $this->assertTrue(session()->has('success')); 
    }

    // -------------------------------------------------------------
    // Test 2: Simpan Gagal Validasi Rating (Tidak Ada Perubahan Signifikan)
    // -------------------------------------------------------------
    public function testSimpanGagalValidasiRating()
    {
        $idDetail = 99;
        
        // 1. Buat Mock dan Controller
        $penilaianModelMock = $this->createMock(PenilaianModel::class);
        $userModelMock = $this->createMock(UserModel::class);
        $pesananModelMock = $this->createMock(PesananModel::class);
        
        $controller = new Penilaian(
            $penilaianModelMock, 
            $userModelMock, 
            $pesananModelMock
        );
        
        // Harapkan metode update TIDAK dipanggil karena validasi gagal
        $penilaianModelMock->expects($this->never())->method('update');

        // 2. Inisialisasi dan Request
        $controller->initController(service('request'), service('response'), service('logger'));

        $request = service('request');
        $request->setMethod('post')->setGlobal('post', [
            'rating' => 11, // Rating di luar batas 1-5
            'ulasan' => 'test'
        ]);
        
        // 3. Jalankan Metode
        $response = $controller->simpan($idDetail);

        // 4. Assert Hasil
        $this->assertInstanceOf(\CodeIgniter\HTTP\RedirectResponse::class, $response);
        
        // Cek apakah ada error di session karena validasi gagal
        $this->assertTrue(session()->has('errors')); 
    }
}