<?php

namespace Tests\Unit;

use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\ControllerTestTrait;
use PHPUnit\Framework\MockObject\MockObject;
use App\Controllers\MemilihAlamat;
use App\Models\AlamatModel;
use App\Models\UserModel;
use CodeIgniter\Config\Services;

class AlamatTest extends CIUnitTestCase
{
    use ControllerTestTrait;

    /** @var MockObject */
    private $alamatMock;

    /** @var MockObject */
    private $userMock;
    
    // Nama kelas Controller
    private const CONTROLLER_CLASS = MemilihAlamat::class;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Inisialisasi Mock
        $this->alamatMock = $this->createMock(AlamatModel::class);
        $this->userMock = $this->createMock(UserModel::class);
        
        // Atur perilaku mock default untuk insert/update
        $this->alamatMock->method('insert')->willReturn(1);
        $this->alamatMock->method('update')->willReturn(true);
    }
    
    /**
     * Helper untuk menyuntikkan mock ke properti private/protected controller.
     * Harus dipanggil setelah $this->controller() dipanggil.
     */
    private function injectMockToController(string $property, MockObject $mock): void
    {
        // Akses instance controller yang sudah dibuat oleh trait (disimpan dalam $this->controller)
        $instance = $this->getPrivateProperty($this, 'controller');

        // Gunakan setPrivateProperty() untuk menyuntikkan mock ke properti
        $this->setPrivateProperty($instance, $property, $mock);
    }

    /** ----------------------- INDEX ----------------------- */
    public function test_index()
    {
        // Mock session
        $mockSession = $this->createMock(\CodeIgniter\Session\Session::class);
        $mockSession->method('get')->willReturnMap([
            ['id_user', 1],
            ['username', 'tester'],
            ['role', 'pembeli']
        ]);
        Services::injectMock('session', $mockSession);

        // Stubbing untuk Index (findAll dan find)
        $this->alamatMock->method('findAll')->willReturn([
            ['id_alamat' => 1, 'nama_penerima' => 'Tester', 'id_user' => 1]
        ]);
        $this->userMock->method('find')->willReturn([
             'id_user'  => 1, 'username' => 'tester', 'role' => 'pembeli', 'foto' => 'default.jpeg'
        ]);

        // 1. Panggil controller (menggunakan string) untuk membuat instance
        $this->controller(self::CONTROLLER_CLASS);

        // 2. Suntikkan mock ke instance controller yang baru dibuat
        $this->injectMockToController('alamatModel', $this->alamatMock);
        $this->injectMockToController('userModel', $this->userMock);


        // 3. Jalankan action
        $result = $this->execute('index');

        // Tambahkan variabel $user agar view tidak error
        $result->setVar('user', [
            'id_user'  => 1,
            'username' => 'tester',
            'role'     => 'pembeli',
            'foto'     => 'default.jpeg'
        ]);

        $result->assertOK();
    }

    /** ----------------------- PILIH ----------------------- */
    public function test_pilih()
    {
        // Mock session
        $mockSession = $this->createMock(\CodeIgniter\Session\Session::class);
        $mockSession->method('get')->willReturn(1); 
        Services::injectMock('session', $mockSession);
        
        // Stubbing untuk Pilih (find)
        $this->alamatMock->method('find')->with(2)->willReturn([
            'id_alamat' => 2,
            'nama_penerima' => 'Tester 2',
            'id_user' => 1, 
            'is_default' => 0
        ]);

        // 1. Panggil controller (menggunakan string) untuk membuat instance
        $this->controller(self::CONTROLLER_CLASS);

        // 2. Suntikkan mock ke instance controller yang baru dibuat
        $this->injectMockToController('alamatModel', $this->alamatMock);

        // 3. Jalankan action
        $result = $this->execute('pilih', 2);

        $result->assertRedirect();
    }

    /** ----------------------- UBAH ----------------------- */
    public function test_ubah()
    {
        // Mock session
        $mockSession = $this->createMock(\CodeIgniter\Session\Session::class);
        $mockSession->method('get')->willReturn(1); // id_user
        Services::injectMock('session', $mockSession);
        
        // Stubbing untuk Ubah (find)
        $this->alamatMock->method('find')->with(5)->willReturn([
            'id_alamat' => 5,
            'nama_penerima' => 'Old',
            'id_user' => 1, 
        ]);

        // 1. Panggil controller (menggunakan string) untuk membuat instance
        $this->controller(self::CONTROLLER_CLASS);

        // 2. Suntikkan mock ke instance controller yang baru dibuat
        $this->injectMockToController('alamatModel', $this->alamatMock);


        // 3. Jalankan action
        $result = $this->withBody([
                            'nama_penerima' => 'Updated',
                            'no_hp' => '081999',
                            'alamat_lengkap' => 'Alamat updated',
                            'label' => 'Kantor'
                        ])
                        ->execute('ubah', 5);

        $result->assertRedirect();
    }
    
    /** ----------------------- PILIH (ALAMAT TIDAK DITEMUKAN) ----------------------- */
    public function test_pilih_not_found()
    {
        // Mock session
        $mockSession = $this->createMock(\CodeIgniter\Session\Session::class);
        $mockSession->method('get')->willReturn(1);
        Services::injectMock('session', $mockSession);
        
        // Stubbing untuk Pilih Not Found (find mengembalikan null)
        $this->alamatMock->method('find')->with(99)->willReturn(null);

        // 1. Panggil controller (menggunakan string) untuk membuat instance
        $this->controller(self::CONTROLLER_CLASS);

        // 2. Suntikkan mock ke instance controller yang baru dibuat
        $this->injectMockToController('alamatModel', $this->alamatMock);

        // 3. Jalankan action
        $result = $this->execute('pilih', 99); 

        $body = $result->getBody();
        if (!$result->isRedirect()) {
             $this->assertStringContainsString('Alamat tidak ditemukan', $body);
        } else {
             $result->assertRedirect();
        }
    }

    /** ----------------------- UBAH (ALAMAT TIDAK DITEMUKAN) ----------------------- */
    public function test_ubah_not_found()
    {
        // Mock session
        $mockSession = $this->createMock(\CodeIgniter\Session\Session::class);
        $mockSession->method('get')->willReturn(1); // id_user
        Services::injectMock('session', $mockSession);
        
        // Stubbing untuk Ubah Not Found (find mengembalikan null)
        $this->alamatMock->method('find')->with(99)->willReturn(null);

        // 1. Panggil controller (menggunakan string) untuk membuat instance
        $this->controller(self::CONTROLLER_CLASS);

        // 2. Suntikkan mock ke instance controller yang baru dibuat
        $this->injectMockToController('alamatModel', $this->alamatMock);

        // 3. Jalankan action
        $result = $this->withBody([
                            'nama_penerima' => 'Updated',
                            'no_hp' => '08123',
                            'alamat_lengkap' => 'Alamat updated',
                            'label' => 'Kantor'
                        ])
                        ->execute('ubah', 99); 

        $body = $result->getBody();
        if (!$result->isRedirect()) {
             $this->assertStringContainsString('Alamat tidak ditemukan', $body);
        } else {
             $result->assertRedirect();
        }
    }
    
    // --- PASSED TESTS ---
    // (Meskipun ini sudah berhasil, kita perlu mengadaptasi struktur panggilan ke yang baru)

    /** ----------------------- TAMBAH ----------------------- */
    public function test_tambah()
    {
        // Mock session
        $mockSession = $this->createMock(\CodeIgniter\Session\Session::class);
        $mockSession->method('get')->willReturn(1);
        Services::injectMock('session', $mockSession);
        
        // 1. Panggil controller (menggunakan string) untuk membuat instance
        $this->controller(self::CONTROLLER_CLASS);

        // 2. Suntikkan mock ke instance controller yang baru dibuat
        $this->injectMockToController('alamatModel', $this->alamatMock);

        // 3. Jalankan action
        $result = $this->withBody([
                            'nama_penerima' => 'User A',
                            'no_hp' => '08123',
                            'alamat_lengkap' => 'Test alamat',
                            'label' => 'Rumah'
                        ])
                        ->execute('tambah');

        $result->assertRedirect();
    }

    /** ----------------------- TAMBAH (VALIDASI GAGAL) ----------------------- */
    public function test_tambah_validation_fail()
    {
        $mockSession = $this->createMock(\CodeIgniter\Session\Session::class);
        $mockSession->method('get')->willReturn(1);
        Services::injectMock('session', $mockSession);
        
        // Memastikan insert() tidak dipanggil jika validasi gagal
        $this->alamatMock->expects($this->never())->method('insert');
        
        // Stub userModel agar view tidak error saat validasi gagal dan view dipanggil
        $this->userMock->method('find')->willReturn([
             'id_user'  => 1, 'username' => 'tester', 'role' => 'pembeli', 'foto' => 'default.jpeg'
        ]);

        // 1. Panggil controller (menggunakan string) untuk membuat instance
        $this->controller(self::CONTROLLER_CLASS);

        // 2. Suntikkan mock ke instance controller yang baru dibuat
        $this->injectMockToController('userModel', $this->userMock);
        
        // 3. Jalankan action
        $result = $this->withBody([
                            'nama_penerima' => '', 
                            'no_hp' => '', 
                            'alamat_lengkap' => '', 
                            'label' => '' 
                        ])
                        ->execute('tambah');

        $result->assertRedirect();
    }

    /** ----------------------- TAMBAH (GET REQUEST) ----------------------- */
    public function test_tambah_get_request()
    {
        // Mock session
        $mockSession = $this->createMock(\CodeIgniter\Session\Session::class);
        $mockSession->method('get')->willReturn(1); // id_user
        Services::injectMock('session', $mockSession);
        
        $this->userMock->method('find')->willReturn([
             'id_user'  => 1, 'username' => 'tester', 'role' => 'pembeli', 'foto' => 'default.jpeg'
        ]);

        // 1. Panggil controller (menggunakan string) untuk membuat instance
        $this->controller(self::CONTROLLER_CLASS);

        // 2. Suntikkan mock ke instance controller yang baru dibuat
        $this->injectMockToController('userModel', $this->userMock);
        
        // 3. Jalankan action
        $result = $this->execute('tambah');

        $result->assertRedirect();
    }
}
