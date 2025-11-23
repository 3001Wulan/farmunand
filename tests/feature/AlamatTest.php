<?php

namespace Tests\Feature;

use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\ControllerTestTrait;
use PHPUnit\Framework\MockObject\MockObject;
use App\Controllers\MemilihAlamat;
use App\Models\AlamatModel;

class AlamatTest extends CIUnitTestCase
{
    use ControllerTestTrait;

    /** @var MockObject */
    private $alamatMock;

    protected function setUp(): void
    {
        parent::setUp();

        // Mock model secara lengkap (HANYA method yang dipakai controller)
        $this->alamatMock = $this->createMock(AlamatModel::class);

        // Override instance model dalam controller
        $this->injectMockToController(MemilihAlamat::class, 'alamatModel', $this->alamatMock);
    }


    /** ----------------------- INDEX ----------------------- */

    public function test_index()
    {
        // --- Mock logger ---
        $mockLogger = $this->createMock(\CodeIgniter\Log\Logger::class);
        $mockLogger->method('debug')->willReturnCallback(function(){});
        $mockLogger->method('error')->willReturnCallback(function(){});
        \Config\Services::injectMock('logger', $mockLogger);
    
        // --- Mock session ---
        $mockSession = $this->createMock(\CodeIgniter\Session\Session::class);
        $mockSession->method('get')->willReturnMap([
            ['id_user', 1],
            ['username', 'tester'],
            ['role', 'pembeli']
        ]);
        $mockSession->method('set')->willReturn(true);
        \Config\Services::injectMock('session', $mockSession);
    
        // --- Mock AlamatModel --- 
        // Hanya mock findAll() yang dipanggil di index
        $this->alamatMock->method('findAll')->willReturn([
            ['id_alamat' => 1, 'nama_penerima' => 'Tester']
        ]);
    
        // Jalankan controller
        $result = $this->controller(\App\Controllers\MemilihAlamat::class)
                       ->execute('index');
    
        $result->assertOK();
    }
    
    /** ----------------------- TAMBAH ----------------------- */

    public function test_tambah()
    {
        // Mock insert() pada tambahAlamat()
        $this->alamatMock->method('insert')->willReturn(1);

        $result = $this->withBody([
                            'nama_penerima' => 'User A',
                            'no_hp' => '08123',
                            'alamat_lengkap' => 'Test alamat',
                            'label' => 'Rumah'
                        ])
                        ->controller(MemilihAlamat::class)
                        ->execute('tambah');

        $result->assertOK();
    }


    /** ----------------------- PILIH ----------------------- */

    public function test_pilih()
    {
        // Mock find() dipanggil pada pilihAlamat()
        $this->alamatMock->method('find')->willReturn([
            'id' => 2,
            'nama_penerima' => 'Tester'
        ]);

        $result = $this->controller(MemilihAlamat::class)
                       ->execute('pilih', 2);

        $result->assertOK();
    }


    /** ----------------------- UBAH ----------------------- */

    public function test_ubah()
    {
        // Mock update()
        $this->alamatMock->method('update')->willReturn(true);

        $result = $this->withBody([
                            'nama_penerima' => 'Updated',
                            'no_hp' => '081999',
                            'alamat_lengkap' => 'Alamat updated',
                            'label' => 'Kantor'
                        ])
                        ->controller(MemilihAlamat::class)
                        ->execute('ubah', 5);

        $result->assertOK();
    }


    /** ----------------------- INJECT MOCK ----------------------- */
    private function injectMockToController($controllerClass, $property, $mock)
    {
        // Trick: memaksa controller menggunakan mock model
        $this->controller($controllerClass);

        $reflection = new \ReflectionClass($controllerClass);
        $instance = $this->getPrivateProperty($this, 'controller');

        $prop = $reflection->getProperty($property);
        $prop->setAccessible(true);
        $prop->setValue($instance, $mock);
    }
    /** ----------------------- TAMBAH (VALIDASI GAGAL) ----------------------- */
public function test_tambah_validation_fail()
{
    $mockSession = $this->createMock(\CodeIgniter\Session\Session::class);
    $mockSession->method('get')->willReturn(1);
    \Config\Services::injectMock('session', $mockSession);

    // Body tidak lengkap
    $result = $this->withBody([
                        'nama_penerima' => '',
                        'jalan' => '',
                        'no_telepon' => '',
                        'kota' => '',
                        'provinsi' => '',
                        'kode_pos' => ''
                    ])
                    ->controller(MemilihAlamat::class)
                    ->execute('tambah');

    $result->assertRedirect(); // pastikan redirect karena gagal validasi
}

/** ----------------------- PILIH (ALAMAT TIDAK DITEMUKAN) ----------------------- */
public function test_pilih_not_found()
{
    $this->alamatMock->method('find')->willReturn(null);

    $mockSession = $this->createMock(\CodeIgniter\Session\Session::class);
    $mockSession->method('get')->willReturn(1);
    \Config\Services::injectMock('session', $mockSession);

    $result = $this->controller(MemilihAlamat::class)
                   ->execute('pilih', 99);

    $body = $result->getBody();
    $this->assertStringContainsString('Alamat tidak ditemukan', $body);
}

/** ----------------------- UBAH (ALAMAT TIDAK DITEMUKAN) ----------------------- */
public function test_ubah_not_found()
{
    $this->alamatMock->method('find')->willReturn(null);

    $result = $this->withBody([
                        'nama_penerima' => 'Updated',
                        'jalan' => 'Jalan Baru',
                        'no_telepon' => '08123',
                        'kota' => 'Kota',
                        'provinsi' => 'Provinsi',
                        'kode_pos' => '12345'
                    ])
                    ->controller(MemilihAlamat::class)
                    ->execute('ubah', 99);

    $body = $result->getBody();
    $this->assertStringContainsString('Alamat tidak ditemukan', $body);
}

/** ----------------------- TAMBAH (GET REQUEST) ----------------------- */
public function test_tambah_get_request()
{
    $result = $this->controller(MemilihAlamat::class)
                   ->execute('tambah');

    $result->assertRedirect(); // pastikan redirect ke halaman index
}

}
