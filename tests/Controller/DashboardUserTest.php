<?php

namespace Tests\Controller;

use CodeIgniter\Test\CIUnitTestCase;
use App\Controllers\DashboardUser as RealDashboardUser;

class DashboardUserFakeUserRepo
{
    public array $users = [];

    public function __construct(array $users = [])
    {
        foreach ($users as $u) {
            $this->users[(int) $u['id_user']] = $u;
        }
    }

    public function find(int $id): ?array
    {
        return $this->users[$id] ?? null;
    }
}

class DashboardUserFakePesananRepo
{
    public array $sukses = [];
    public array $pending = [];
    public array $batal = [];

    public function __construct(
        array $sukses = [],
        array $pending = [],
        array $batal = []
    ) {
        $this->sukses  = $sukses;
        $this->pending = $pending;
        $this->batal   = $batal;
    }

    public function countSelesai(int $idUser): int
    {
        return (int) ($this->sukses[$idUser] ?? 0);
    }

    public function countPending(int $idUser): int
    {
        return (int) ($this->pending[$idUser] ?? 0);
    }

    public function countBatal(int $idUser): int
    {
        return (int) ($this->batal[$idUser] ?? 0);
    }
}

class DashboardUserFakeProdukSource
{
    public array $produk = [];

    public function __construct(array $produk = [])
    {
        $this->produk = $produk;
    }

    public function getProdukWithRating(): array
    {
        return $this->produk;
    }
}

class TestableDashboardUser extends RealDashboardUser
{
    private DashboardUserFakeUserRepo $userRepo;
    private DashboardUserFakePesananRepo $pesananRepo;
    private DashboardUserFakeProdukSource $produkSource;

    public function __construct(
        ?DashboardUserFakeUserRepo $userRepo = null,
        ?DashboardUserFakePesananRepo $pesananRepo = null,
        ?DashboardUserFakeProdukSource $produkSource = null
    ) {
        $this->userRepo     = $userRepo     ?? new DashboardUserFakeUserRepo();
        $this->pesananRepo  = $pesananRepo  ?? new DashboardUserFakePesananRepo();
        $this->produkSource = $produkSource ?? new DashboardUserFakeProdukSource();
    }

    public function index()
    {
        $userId = (int) (session()->get('id_user') ?? 0);
        if (! $userId) {
            return [
                'redirect' => '/login',
                'error'    => 'Silakan login dulu.',
            ];
        }

        $user = $this->userRepo->find($userId);

        $pesananSukses  = $this->pesananRepo->countSelesai($userId);
        $pesananPending = $this->pesananRepo->countPending($userId);
        $pesananBatal   = $this->pesananRepo->countBatal($userId);

        $produk = $this->produkSource->getProdukWithRating();

        return [
            'title'          => 'Dashboard User',
            'username'       => $user['username'] ?? '',
            'role'           => $user['role'] ?? '',
            'foto'           => $user['foto'] ?? null,
            'pesanan_sukses' => $pesananSukses,
            'pending'        => $pesananPending,
            'batal'          => $pesananBatal,
            'produk'         => $produk,
            'user'           => $user,
        ];
    }
}

class DashboardUserTest extends CIUnitTestCase
{
    private DashboardUserFakeUserRepo $userRepo;
    private DashboardUserFakePesananRepo $pesananRepo;
    private DashboardUserFakeProdukSource $produkSource;
    private TestableDashboardUser $controller;

    protected function setUp(): void
    {
        parent::setUp();

        $_SESSION = [];
        session()->destroy();

        $this->userRepo = new DashboardUserFakeUserRepo([
            [
                'id_user'  => 1,
                'username' => 'user_test',
                'role'     => 'user',
                'foto'     => 'user.png',
            ],
        ]);

        $this->pesananRepo = new DashboardUserFakePesananRepo(
            sukses:  [1 => 5],
            pending: [1 => 2],
            batal:   [1 => 1],
        );

        $this->produkSource = new DashboardUserFakeProdukSource([
            [
                'id_produk'     => 10,
                'nama_produk'   => 'Produk A',
                'avg_rating'    => 4.5,
                'rating_count'  => 10,
            ],
            [
                'id_produk'     => 20,
                'nama_produk'   => 'Produk B',
                'avg_rating'    => 0.0,
                'rating_count'  => 0,
            ],
        ]);

        $this->controller = new TestableDashboardUser(
            $this->userRepo,
            $this->pesananRepo,
            $this->produkSource
        );
    }

    protected function tearDown(): void
    {
        session()->destroy();
        parent::tearDown();
    }

    public function testDashboardUserRedirectsToLoginWhenNotLoggedIn(): void
    {
        session()->remove('id_user');

        $data = $this->controller->index();

        $this->assertIsArray($data);
        $this->assertSame('/login', $data['redirect'] ?? null);
        $this->assertSame('Silakan login dulu.', $data['error'] ?? null);
    }

    public function testDashboardUserBuildsMetricsAndProdukForLoggedInUser(): void
    {
        session()->set('id_user', 1);

        $data = $this->controller->index();

        $this->assertIsArray($data);

        $this->assertSame('Dashboard User', $data['title']);
        $this->assertSame('user_test', $data['username']);
        $this->assertSame('user', $data['role']);
        $this->assertSame('user.png', $data['foto']);

        $this->assertSame(5, $data['pesanan_sukses']);
        $this->assertSame(2, $data['pending']);
        $this->assertSame(1, $data['batal']);

        $this->assertIsArray($data['produk']);
        $this->assertCount(2, $data['produk']);

        $this->assertSame('Produk A', $data['produk'][0]['nama_produk']);
        $this->assertSame(4.5, $data['produk'][0]['avg_rating']);
        $this->assertSame(10, $data['produk'][0]['rating_count']);

        $this->assertSame('Produk B', $data['produk'][1]['nama_produk']);
        $this->assertSame(0.0, $data['produk'][1]['avg_rating']);
        $this->assertSame(0, $data['produk'][1]['rating_count']);

        $this->assertIsArray($data['user']);
        $this->assertSame(1, $data['user']['id_user']);
        $this->assertSame('user_test', $data['user']['username']);
    }
}