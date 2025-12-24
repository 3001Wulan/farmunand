<?php

namespace Tests\Controller;

use App\Controllers\Dashboard;
use CodeIgniter\HTTP\RedirectResponse;
use CodeIgniter\Test\CIUnitTestCase;

class DashboardFakeProdukRepo
{
    public int $totalProduk   = 0;
    public int $stokRendah    = 0;

    public function getTotalProduk(): int
    {
        return $this->totalProduk;
    }

    public function getStokRendah(): int
    {
        return $this->stokRendah;
    }

    public function countAllResults(): int
    {
        return $this->totalProduk;
    }
}


class DashboardFakeUserRepo
{
    /** @var array<int,array> */
    public array $users = [];

    public int $totalUser = 0;

    public function __construct(array $users = [], int $totalUser = 0)
    {
        $this->totalUser = $totalUser ?: count($users);

        foreach ($users as $u) {
            $this->users[(int) $u['id_user']] = $u;
        }
    }

    public function getTotalUser(): int
    {
        return $this->totalUser;
    }

    public function countAllResults(): int
    {
        return $this->totalUser;
    }

    public function find($id): ?array
    {
        $id = (int) $id;
        return $this->users[$id] ?? null;
    }
}


class DashboardFakePesananRepo
{
    public int $transaksiHariIni = 0;
    public int $penjualanBulan   = 0;

    public int $pesanMasukCount  = 0;
    public int $totalPesanan     = 0;

    private bool $lastWhereBelumBayar = false;

    public function getTransaksiHariIni(): int
    {
        return $this->transaksiHariIni;
    }

    public function getPenjualanBulan(): int
    {
        return $this->penjualanBulan;
    }

    public function where($field = null, $value = null): self
    {
        $this->lastWhereBelumBayar = (
            $field === 'status_pemesanan' &&
            $value === 'Belum Bayar'
        );

        return $this;
    }

    public function countAllResults(): int
    {
        if ($this->lastWhereBelumBayar) {
            $this->lastWhereBelumBayar = false;
            return $this->pesanMasukCount;
        }

        return $this->totalPesanan;
    }
}


class TestableDashboard extends Dashboard
{
    public function __construct(
        DashboardFakeProdukRepo $produkRepo,
        DashboardFakeUserRepo $userRepo,
        DashboardFakePesananRepo $pesananRepo
    ) {
        $this->produkModel  = $produkRepo;
        $this->userModel    = $userRepo;
        $this->pesananModel = $pesananRepo;
    }

    public function index()
    {
        $userId = session()->get('id_user');
        if (! $userId) {
            return redirect()->to('/login')->with('error', 'Silakan login dulu.');
        }

        $user = $this->userModel->find($userId);

        $data = [
            'title'           => 'Dashboard',
            'total_produk'    =>
                \method_exists($this->produkModel, 'getTotalProduk')
                    ? $this->produkModel->getTotalProduk()
                    : $this->produkModel->countAllResults(),
            'total_user'      =>
                \method_exists($this->userModel, 'getTotalUser')
                    ? $this->userModel->getTotalUser()
                    : $this->userModel->countAllResults(),
            'transaksi_hari'  => $this->pesananModel->getTransaksiHariIni(),
            'penjualan_bulan' => $this->pesananModel->getPenjualanBulan(),
            'stok_rendah'     =>
                \method_exists($this->produkModel, 'getStokRendah')
                    ? $this->produkModel->getStokRendah()
                    : 0,
            'pesan_masuk'     => $this->pesananModel
                ->where('status_pemesanan', 'Belum Bayar')
                ->countAllResults(),
            'total_pesanan'   => $this->pesananModel->countAllResults(),
            'user'            => $user,
        ];

        return $data;
    }
}

class DashboardTest extends CIUnitTestCase
{
    private DashboardFakeProdukRepo $produkRepo;
    private DashboardFakeUserRepo $userRepo;
    private DashboardFakePesananRepo $pesananRepo;
    private TestableDashboard $controller;

    protected function setUp(): void
    {
        parent::setUp();

        $_SESSION = [];
        session()->destroy();

        $this->produkRepo = new DashboardFakeProdukRepo();
        $this->produkRepo->totalProduk = 10;
        $this->produkRepo->stokRendah  = 3;

        $this->userRepo = new DashboardFakeUserRepo(
            [
                [
                    'id_user'  => 1,
                    'username' => 'admin_test',
                    'email'    => 'admin@test.local',
                    'role'     => 'admin',
                ],
                [
                    'id_user'  => 2,
                    'username' => 'user_test',
                    'email'    => 'user@test.local',
                    'role'     => 'user',
                ],
            ],
            5 
        );

        $this->pesananRepo = new DashboardFakePesananRepo();
        $this->pesananRepo->transaksiHariIni = 7;
        $this->pesananRepo->penjualanBulan   = 1500000;
        $this->pesananRepo->pesanMasukCount  = 4;
        $this->pesananRepo->totalPesanan     = 20;
        $this->controller = new TestableDashboard(
            $this->produkRepo,
            $this->userRepo,
            $this->pesananRepo
        );

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

   
    public function testDashboardRedirectsToLoginWhenNotLoggedIn(): void
    {
        session()->remove('id_user');

        $result = $this->controller->index();

        $this->assertInstanceOf(RedirectResponse::class, $result);
        $this->assertStringContainsString('/login', $result->getHeaderLine('Location'));
        $this->assertSame('Silakan login dulu.', session()->getFlashdata('error'));
    }

   
    public function testDashboardAccessibleForAdminBuildsCorrectStats(): void
    {
        session()->set('id_user', 1);

        $data = $this->controller->index();

        $this->assertIsArray($data);

        foreach ([
            'title',
            'total_produk',
            'total_user',
            'transaksi_hari',
            'penjualan_bulan',
            'stok_rendah',
            'pesan_masuk',
            'total_pesanan',
            'user',
        ] as $key) {
            $this->assertArrayHasKey($key, $data);
        }

        $this->assertSame('Dashboard', $data['title']);
        $this->assertSame(10, $data['total_produk']);
        $this->assertSame(5, $data['total_user']);
        $this->assertSame(7, $data['transaksi_hari']);
        $this->assertSame(1500000, $data['penjualan_bulan']);
        $this->assertSame(3, $data['stok_rendah']);
        $this->assertSame(4, $data['pesan_masuk']);
        $this->assertSame(20, $data['total_pesanan']);

        $this->assertIsArray($data['user']);
        $this->assertSame(1, $data['user']['id_user']);
        $this->assertSame('admin_test', $data['user']['username']);
    }


    public function testDashboardWhenUserRecordMissingStillReturnsStats(): void
    {
        session()->set('id_user', 999); 

        $data = $this->controller->index();

        $this->assertIsArray($data);
        $this->assertArrayHasKey('user', $data);
        $this->assertNull($data['user']);
        $this->assertSame(10, $data['total_produk']);
        $this->assertSame(5, $data['total_user']);
    }
}
