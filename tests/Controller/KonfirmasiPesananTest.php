<?php

namespace Tests\Controller;

use App\Controllers\KonfirmasiPesanan;
use CodeIgniter\HTTP\RedirectResponse;
use CodeIgniter\Test\CIUnitTestCase;

/**
 * Fake repository pengganti PesananModel.
 * Semua data disimpan di array, tanpa DB.
 */
class FakePesananRepo
{
    /** @var array<string,array[]> pesanan per user & status: key "idUser#status" */
    public array $pesananByStatus = [];

    /** @var array<string,array> pesanan per (id_pemesanan,id_user): key "idPemesanan#idUser" */
    public array $pesananByIdAndUser = [];

    /** @var array<int,array{0:int,1:string}> */
    public array $getByStatusCalls = [];

    /** @var array<int,array{0:int,1:int}> */
    public array $getByIdAndUserCalls = [];

    /** @var array<int,array{0:int,1:array}> */
    public array $updateCalls = [];

    /** Jika true, update() akan mengembalikan false (simulasi gagal) */
    public bool $forceUpdateFail = false;

    public function getPesananByStatus(int $idUser, string $status): array
    {
        $this->getByStatusCalls[] = [$idUser, $status];
        $key = $idUser . '#' . $status;

        return $this->pesananByStatus[$key] ?? [];
    }

    public function getPesananByIdAndUser(int $idPemesanan, int $idUser): ?array
    {
        $this->getByIdAndUserCalls[] = [$idPemesanan, $idUser];
        $key = $idPemesanan . '#' . $idUser;

        return $this->pesananByIdAndUser[$key] ?? null;
    }

    public function update(int $id, array $data): bool
    {
        $this->updateCalls[] = [$id, $data];

        if ($this->forceUpdateFail) {
            return false;
        }

        // Di dunia nyata kita akan merge ke data lama,
        // di sini cukup return true untuk menandakan sukses.
        return true;
    }
}

/**
 * Fake repository pengganti UserModel khusus KonfirmasiPesananTest.
 */
class KonfirmasiPesananFakeUserRepo
{
    /** @var array<int,array> */
    public array $users = [];

    public function __construct(array $users = [])
    {
        $this->users = $users;
    }

    public function find(int $id): ?array
    {
        return $this->users[$id] ?? null;
    }
}

/**
 * Versi testable dari KonfirmasiPesanan:
 * - Tidak memanggil konstruktor asli (tidak buat Model beneran).
 * - index() dikembalikan sebagai array, bukan view HTML.
 */
class TestableKonfirmasiPesanan extends KonfirmasiPesanan
{
    public function __construct(FakePesananRepo $pesananRepo, KonfirmasiPesananFakeUserRepo $userRepo)
    {
        // Jangan panggil parent::__construct() agar tidak membuat PesananModel/UserModel asli.
        $this->pesananModel = $pesananRepo;
        $this->userModel    = $userRepo;
    }

    /**
     * index() versi unit-test:
     * - Jika belum login → redirect ke /login (sama seperti original).
     * - Jika sudah login → kembalikan array ['pesanan' => ..., 'user' => ...]
     *   tanpa memanggil view().
     */
    public function index()
    {
        $idUser = session()->get('id_user');
        if (! $idUser) {
            return redirect()->to('/login')->with('error', 'Silakan login terlebih dahulu.');
        }

        $pesananUser = $this->pesananModel->getPesananByStatus((int) $idUser, 'Dikirim');
        $user        = $this->userModel->find((int) $idUser);

        return [
            'pesanan' => $pesananUser,
            'user'    => $user,
        ];
    }
}

class KonfirmasiPesananTest extends CIUnitTestCase
{
    /** @var TestableKonfirmasiPesanan */
    private $controller;

    /** @var FakePesananRepo */
    private $pesananRepo;

    /** @var KonfirmasiPesananFakeUserRepo */
    private $userRepo;

    protected function setUp(): void
    {
        parent::setUp();

        // Reset session tiap test
        $_SESSION = [];
        session()->destroy();

        // Siapkan fake repo
        $this->pesananRepo = new FakePesananRepo();
        $this->userRepo    = new KonfirmasiPesananFakeUserRepo([
            10 => [
                'id_user'  => 10,
                'username' => 'tester10',
                'email'    => 'tester10@example.com',
                'role'     => 'pembeli',
                'foto'     => 'default.png',
            ],
            9  => [
                'id_user'  => 9,
                'username' => 'tester9',
                'email'    => 'tester9@example.com',
                'role'     => 'pembeli',
                'foto'     => 'default.png',
            ],
            7  => [
                'id_user'  => 7,
                'username' => 'tester7',
                'email'    => 'tester7@example.com',
                'role'     => 'pembeli',
                'foto'     => 'default.png',
            ],
        ]);

        // Buat controller testable dan inject request/response logger CI4
        $this->controller = new TestableKonfirmasiPesanan($this->pesananRepo, $this->userRepo);
        $this->controller->initController(
            service('request'),
            service('response'),
            service('logger')
        );
    }

    protected function tearDown(): void
    {
        session()->destroy();
        parent::tearDown();
    }

    /* =========================================================
     * 1. INDEX
     * =======================================================*/

    /** 
     * Skenario: user belum login.
     * Ekspektasi:
     *  - index() mengembalikan RedirectResponse ke /login
     *  - flash error "Silakan login terlebih dahulu."
     */
    public function testIndexRedirectsToLoginWhenNotLoggedIn(): void
    {
        session()->remove('id_user');

        $result = $this->controller->index();

        $this->assertInstanceOf(RedirectResponse::class, $result);
        $this->assertStringContainsString('/login', $result->getHeaderLine('Location'));
        $this->assertSame(
            'Silakan login terlebih dahulu.',
            session()->getFlashdata('error')
        );
    }

    /**
     * Skenario: user login dan punya pesanan berstatus "Dikirim".
     * Ekspektasi:
     *  - index() mengembalikan array data (bukan view)
     *  - memanggil getPesananByStatus(dengan id_user & "Dikirim")
     *  - mengembalikan user sesuai id_user
     */
    public function testIndexForLoggedInUserReturnsPesananAndUser(): void
    {
        session()->set('id_user', 10);

        // Siapkan pesanan dummy untuk user 10 status Dikirim
        $this->pesananRepo->pesananByStatus['10#Dikirim'] = [
            ['id_pemesanan' => 1, 'status_pemesanan' => 'Dikirim'],
            ['id_pemesanan' => 2, 'status_pemesanan' => 'Dikirim'],
        ];

        $data = $this->controller->index();

        $this->assertIsArray($data);
        $this->assertArrayHasKey('pesanan', $data);
        $this->assertArrayHasKey('user', $data);

        $this->assertCount(2, $data['pesanan']);
        $this->assertSame('Dikirim', $data['pesanan'][0]['status_pemesanan']);

        $this->assertSame(10, $data['user']['id_user']);
        $this->assertSame('tester10', $data['user']['username']);

        // Pastikan getPesananByStatus dipanggil dengan argumen tepat
        $this->assertSame([[10, 'Dikirim']], $this->pesananRepo->getByStatusCalls);
    }

    /* =========================================================
     * 2. SELESAI (konfirmasi pesanan)
     * =======================================================*/

    /**
     * Skenario: user belum login memanggil selesai().
     * Ekspektasi:
     *  - redirect ke /login
     *  - flash error login dulu
     *  - repository tidak dipanggil sama sekali.
     */
    public function testSelesaiRedirectsToLoginWhenNotLoggedIn(): void
    {
        session()->remove('id_user');

        $response = $this->controller->selesai(123);

        $this->assertInstanceOf(RedirectResponse::class, $response);
        $this->assertStringContainsString('/login', $response->getHeaderLine('Location'));
        $this->assertSame(
            'Silakan login terlebih dahulu.',
            session()->getFlashdata('error')
        );
        $this->assertSame([], $this->pesananRepo->getByIdAndUserCalls);
        $this->assertSame([], $this->pesananRepo->updateCalls);
    }

    /**
     * Skenario: pesanan tidak ditemukan (getPesananByIdAndUser() mengembalikan null).
     * Ekspektasi:
     *  - redirect back
     *  - flash error "Pesanan tidak ditemukan."
     *  - update() tidak dipanggil.
     */
    public function testSelesaiGivesErrorWhenOrderNotFound(): void
    {
        session()->set('id_user', 5);

        // Tidak mengisi pesananByIdAndUser → selalu null
        $response = $this->controller->selesai(99);

        $this->assertInstanceOf(RedirectResponse::class, $response);
        $this->assertSame(
            'Pesanan tidak ditemukan.',
            session()->getFlashdata('error')
        );
        $this->assertSame([[99, 5]], $this->pesananRepo->getByIdAndUserCalls);
        $this->assertSame(
            [],
            $this->pesananRepo->updateCalls,
            'update() tidak boleh dipanggil ketika pesanan tidak ditemukan.'
        );
    }

    /**
     * Skenario: pesanan ada tapi status ≠ "Dikirim" (mis: "Diproses").
     * Ekspektasi:
     *  - redirect back
     *  - flash error "Pesanan ini tidak dalam status Dikirim."
     *  - update() tidak dipanggil.
     */
    public function testSelesaiGivesErrorWhenStatusNotDikirim(): void
    {
        session()->set('id_user', 7);

        // Simulasikan pesanan user 7 status "Diproses"
        $this->pesananRepo->pesananByIdAndUser['50#7'] = [
            'id_pemesanan'     => 50,
            'id_user'          => 7,
            'status_pemesanan' => 'Diproses',
        ];

        $response = $this->controller->selesai(50);

        $this->assertInstanceOf(RedirectResponse::class, $response);
        $this->assertSame(
            'Pesanan ini tidak dalam status Dikirim.',
            session()->getFlashdata('error')
        );
        $this->assertSame([[50, 7]], $this->pesananRepo->getByIdAndUserCalls);
        $this->assertSame(
            [],
            $this->pesananRepo->updateCalls,
            'update() tidak boleh dipanggil ketika status bukan Dikirim.'
        );
    }

    /**
     * Skenario: pesanan ada, status "Dikirim", dan update() BERHASIL.
     * Ekspektasi:
     *  - update() dipanggil sekali dengan:
     *      status_pemesanan = "Selesai"
     *      konfirmasi_token = null
     *      confirmed_at     = timestamp (tidak kosong)
     *  - redirect ke /pesananselesai
     *  - flash success "Pesanan berhasil dikonfirmasi!"
     */
    public function testSelesaiSuccessUpdatesOrderAndRedirects(): void
    {
        session()->set('id_user', 9);

        $this->pesananRepo->pesananByIdAndUser['77#9'] = [
            'id_pemesanan'     => 77,
            'id_user'          => 9,
            'status_pemesanan' => 'Dikirim',
        ];

        $response = $this->controller->selesai(77);

        $this->assertInstanceOf(RedirectResponse::class, $response);
        $this->assertStringContainsString(
            '/pesananselesai',
            $response->getHeaderLine('Location')
        );

        // Pastikan getPesananByIdAndUser dipanggil dengan id & user yang benar
        $this->assertSame([[77, 9]], $this->pesananRepo->getByIdAndUserCalls);

        // Pastikan update() dipanggil sekali
        $this->assertCount(1, $this->pesananRepo->updateCalls);
        [$id, $data] = $this->pesananRepo->updateCalls[0];

        $this->assertSame(77, $id);
        $this->assertSame('Selesai', $data['status_pemesanan'] ?? null);
        $this->assertArrayHasKey('confirmed_at', $data);
        $this->assertNotEmpty($data['confirmed_at']);
        $this->assertNull($data['konfirmasi_token'] ?? null);

        $this->assertSame(
            'Pesanan berhasil dikonfirmasi!',
            session()->getFlashdata('success')
        );
    }

    /**
     * Skenario: pesanan ada, status "Dikirim", tapi update() GAGAL (return false).
     * Ekspektasi:
     *  - tetap redirect ke /pesananselesai
     *  - flash error "Gagal mengonfirmasi pesanan."
     *  - update() tetap dipanggil sekali.
     */
    public function testSelesaiFailedUpdateSetsErrorFlash(): void
    {
        session()->set('id_user', 9);

        $this->pesananRepo->pesananByIdAndUser['88#9'] = [
            'id_pemesanan'     => 88,
            'id_user'          => 9,
            'status_pemesanan' => 'Dikirim',
        ];
        $this->pesananRepo->forceUpdateFail = true;

        $response = $this->controller->selesai(88);

        $this->assertInstanceOf(RedirectResponse::class, $response);
        $this->assertStringContainsString(
            '/pesananselesai',
            $response->getHeaderLine('Location')
        );

        $this->assertSame(
            'Gagal mengonfirmasi pesanan.',
            session()->getFlashdata('error')
        );

        $this->assertCount(1, $this->pesananRepo->updateCalls);
        [$id, $data] = $this->pesananRepo->updateCalls[0];
        $this->assertSame(88, $id);
    }
}
