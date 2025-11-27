<?php

namespace Tests\Controller;

use CodeIgniter\Test\CIUnitTestCase;
use App\Controllers\DashboardUser as RealDashboardUser;

/**
 * Fake repository user untuk DashboardUser.
 * Semua data disimpan di array (in-memory), tanpa DB.
 */
class DashboardUserFakeUserRepo
{
    /** @var array<int,array> */
    public array $users = [];

    /**
     * @param array<int,array> $users
     */
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

/**
 * Fake repository statistik pesanan per user.
 * Kita sederhanakan jadi 3 counter:
 *  - sukses (status Selesai)
 *  - pending (status Dikemas/Dikirim/Belum Bayar)
 *  - batal (status Dibatalkan)
 */
class DashboardUserFakePesananRepo
{
    /** @var array<int,int> */
    public array $sukses = [];

    /** @var array<int,int> */
    public array $pending = [];

    /** @var array<int,int> */
    public array $batal = [];

    /**
     * @param array<int,int> $sukses
     * @param array<int,int> $pending
     * @param array<int,int> $batal
     */
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

/**
 * Fake sumber data produk + rating.
 * Menggantikan query join ke tabel produk + detail_pemesanan.
 */
class DashboardUserFakeProdukSource
{
    /** @var array<int,array> */
    public array $produk = [];

    /**
     * @param array<int,array> $produk
     */
    public function __construct(array $produk = [])
    {
        $this->produk = $produk;
    }

    /**
     * Mengembalikan list produk beserta avg_rating & rating_count (kalau ada).
     *
     * @return array<int,array>
     */
    public function getProdukWithRating(): array
    {
        return $this->produk;
    }
}

/**
 * Versi testable dari DashboardUser:
 *  - TIDAK memanggil DB sama sekali.
 *  - TIDAK memanggil view().
 *  - index() dikembalikan sebagai ARRAY data.
 */
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
        // JANGAN panggil parent::__construct() supaya tidak buat UserModel/PesananModel asli.
        $this->userRepo     = $userRepo     ?? new DashboardUserFakeUserRepo();
        $this->pesananRepo  = $pesananRepo  ?? new DashboardUserFakePesananRepo();
        $this->produkSource = $produkSource ?? new DashboardUserFakeProdukSource();
    }

    /**
     * index() versi unit-test:
     *  - Kalau belum login → return ['redirect' => '/login', 'error' => '...']
     *  - Kalau login → hitung metrik & ambil produk dari fake repo, return array data.
     *
     * @return array<string,mixed>
     */
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

/**
 * Test murni unit untuk DashboardUser.
 */
class DashboardUserTest extends CIUnitTestCase
{
    private DashboardUserFakeUserRepo $userRepo;
    private DashboardUserFakePesananRepo $pesananRepo;
    private DashboardUserFakeProdukSource $produkSource;
    private TestableDashboardUser $controller;

    protected function setUp(): void
    {
        parent::setUp();

        // Reset session tiap test
        $_SESSION = [];
        session()->destroy();

        // Seed user dummy
        $this->userRepo = new DashboardUserFakeUserRepo([
            [
                'id_user'  => 1,
                'username' => 'user_test',
                'role'     => 'user',
                'foto'     => 'user.png',
            ],
        ]);

        // Seed metrik dummy:
        //  - sukses = 5
        //  - pending = 2
        //  - batal = 1
        $this->pesananRepo = new DashboardUserFakePesananRepo(
            sukses:  [1 => 5],
            pending: [1 => 2],
            batal:   [1 => 1],
        );

        // Seed produk dummy dengan rating
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

        // Buat controller testable
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

    /**
     * Skenario: user belum login → harus diarahkan ke /login.
     */
    public function testDashboardUserRedirectsToLoginWhenNotLoggedIn(): void
    {
        // Pastikan tidak ada id_user di session
        session()->remove('id_user');

        $data = $this->controller->index();

        $this->assertIsArray($data);
        $this->assertSame('/login', $data['redirect'] ?? null);
        $this->assertSame('Silakan login dulu.', $data['error'] ?? null);
    }

    /**
     * Skenario: user login → data dashboard (metrik + produk + user) terbangun dengan benar.
     */
    public function testDashboardUserBuildsMetricsAndProdukForLoggedInUser(): void
    {
        // Login sebagai user id=1
        session()->set('id_user', 1);

        $data = $this->controller->index();

        $this->assertIsArray($data);

        // Title & info user
        $this->assertSame('Dashboard User', $data['title']);
        $this->assertSame('user_test', $data['username']);
        $this->assertSame('user', $data['role']);
        $this->assertSame('user.png', $data['foto']);

        // Metrik pesanan
        $this->assertSame(5, $data['pesanan_sukses']);
        $this->assertSame(2, $data['pending']);
        $this->assertSame(1, $data['batal']);

        // Produk + rating
        $this->assertIsArray($data['produk']);
        $this->assertCount(2, $data['produk']);

        $this->assertSame('Produk A', $data['produk'][0]['nama_produk']);
        $this->assertSame(4.5, $data['produk'][0]['avg_rating']);
        $this->assertSame(10, $data['produk'][0]['rating_count']);

        $this->assertSame('Produk B', $data['produk'][1]['nama_produk']);
        $this->assertSame(0.0, $data['produk'][1]['avg_rating']);
        $this->assertSame(0, $data['produk'][1]['rating_count']);

        // User object di dalam data
        $this->assertIsArray($data['user']);
        $this->assertSame(1, $data['user']['id_user']);
        $this->assertSame('user_test', $data['user']['username']);
    }
}
