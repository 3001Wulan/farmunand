<?php

namespace Tests\Controller;

use App\Controllers\KonfirmasiPesanan;
use CodeIgniter\HTTP\RedirectResponse;
use CodeIgniter\Test\CIUnitTestCase;

class FakePesananRepo
{
    public array $pesananByStatus = [];
    public array $pesananByIdAndUser = [];
    public array $getByStatusCalls = [];
    public array $getByIdAndUserCalls = [];
    public array $updateCalls = [];
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

        return true;
    }
}

class KonfirmasiPesananFakeUserRepo
{
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

class TestableKonfirmasiPesanan extends KonfirmasiPesanan
{
    public function __construct(FakePesananRepo $pesananRepo, KonfirmasiPesananFakeUserRepo $userRepo)
    {
        $this->pesananModel = $pesananRepo;
        $this->userModel    = $userRepo;
    }

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
    private $controller;
    private $pesananRepo;
    private $userRepo;

    protected function setUp(): void
    {
        parent::setUp();

        $_SESSION = [];
        session()->destroy();

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

    public function testIndexForLoggedInUserReturnsPesananAndUser(): void
    {
        session()->set('id_user', 10);

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

        $this->assertSame([[10, 'Dikirim']], $this->pesananRepo->getByStatusCalls);
    }

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

    public function testSelesaiGivesErrorWhenOrderNotFound(): void
    {
        session()->set('id_user', 5);

        $response = $this->controller->selesai(99);

        $this->assertInstanceOf(RedirectResponse::class, $response);
        $this->assertSame(
            'Pesanan tidak ditemukan.',
            session()->getFlashdata('error')
        );
        $this->assertSame([[99, 5]], $this->pesananRepo->getByIdAndUserCalls);
        $this->assertSame(
            [],
            $this->pesananRepo->updateCalls
        );
    }

    public function testSelesaiGivesErrorWhenStatusNotDikirim(): void
    {
        session()->set('id_user', 7);

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
            $this->pesananRepo->updateCalls
        );
    }

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

        $this->assertSame([[77, 9]], $this->pesananRepo->getByIdAndUserCalls);

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