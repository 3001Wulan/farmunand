<?php

namespace Tests\Controller;

use App\Controllers\Keranjang;
use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\HTTP\RedirectResponse;

class KeranjangFakeProdukRepo
{
    public array $records = [];
    public array $calledWith = [];

    public function __construct(array $records = [])
    {
        $this->records = $records;
    }

    public function find($id)
    {
        $id = (int) $id;
        $this->calledWith[] = $id;

        return $this->records[$id] ?? null;
    }
}

class KeranjangFakeUserRepo
{
    public array $records = [];

    public function __construct(array $records = [])
    {
        $this->records = $records;
    }

    public function find($id)
    {
        $id = (int) $id;
        return $this->records[$id] ?? null;
    }
}

class TestableKeranjang extends Keranjang
{
    public function __construct($produkRepo, $userRepo)
    {
        $this->produkModel = $produkRepo;
        $this->userModel   = $userRepo;

        helper(['form']);
    }

    public function index()
    {
        $idUser = session()->get('id_user');

        if (! $idUser) {
            return redirect()->to('/login')->with('error', 'Silakan login dulu.');
        }

        $cartKey  = 'cart_u_' . $idUser;
        $countKey = 'cart_count_u_' . $idUser;

        $cart  = session()->get($cartKey) ?? [];
        $total = 0;
        $count = 0;

        foreach ($cart as $row) {
            $harga = (float) ($row['harga'] ?? 0);
            $qty   = (int)   ($row['qty'] ?? 0);

            $total += $harga * $qty;
            $count += $qty;
        }

        session()->set($countKey, $count);

        $user = $this->userModel ? $this->userModel->find($idUser) : null;

        return [
            'cart'  => $cart,
            'total' => $total,
            'user'  => $user,
            'count' => $count,
        ];
    }
}

class KeranjangTest extends CIUnitTestCase
{
    private $controller;
    private $produkRepo;
    private $userRepo;

    protected function setUp(): void
    {
        parent::setUp();

        $_SESSION = [];
        session()->destroy();

        $this->userRepo = new KeranjangFakeUserRepo([
            1 => [
                'id_user'  => 1,
                'nama'     => 'User Test',
                'username' => 'usertest',
                'role'     => 'pembeli',
            ],
        ]);

        $this->produkRepo = new KeranjangFakeProdukRepo([]);

        $this->controller = new TestableKeranjang(
            $this->produkRepo,
            $this->userRepo
        );
        $this->controller->initController(
            service('request'),
            service('response'),
            service('logger')
        );
    }

    public function testIndexRedirectKetikaBelumLogin()
    {
        session()->destroy();

        $result = $this->controller->index();

        $this->assertInstanceOf(RedirectResponse::class, $result);
        $this->assertStringContainsString('/login', $result->getHeaderLine('Location'));
        $this->assertSame('Silakan login dulu.', session()->getFlashdata('error'));
    }

    public function testIndexMenghitungTotalDanSyncCartCountUntukUserLogin()
    {
        session()->set('id_user', 1);
        session()->set('cart_u_1', [
            10 => ['id_produk' => 10, 'harga' => 5000, 'qty' => 2],
            20 => ['id_produk' => 20, 'harga' => 3000, 'qty' => 1],
        ]);

        $data = $this->controller->index();

        $this->assertIsArray($data);
        $this->assertArrayHasKey('cart', $data);
        $this->assertArrayHasKey('total', $data);
        $this->assertArrayHasKey('user', $data);
        $this->assertArrayHasKey('count', $data);

        $this->assertSame(13000.0, $data['total']);
        $this->assertSame(3, $data['count']);
        $this->assertSame(3, session()->get('cart_count_u_1'));
        $this->assertSame('User Test', $data['user']['nama']);
    }

    public function testAddTanpaLoginRedirectKeLoginDanTidakMemanggilFind()
    {
        session()->destroy();

        $req = service('request');
        $req->setMethod('post');
        $req->setGlobal('post', [
            'id_produk' => 10,
            'qty'       => 2,
        ]);

        $result = $this->controller->add();

        $this->assertInstanceOf(RedirectResponse::class, $result);
        $this->assertStringContainsString('/login', $result->getHeaderLine('Location'));
        $this->assertSame([], $this->produkRepo->calledWith);
    }

    public function testAddProdukTidakDitemukanMengembalikanErrorFlash()
    {
        session()->set('id_user', 1);

        $this->produkRepo->records = [];

        $req = service('request');
        $req->setMethod('post');
        $req->setGlobal('post', [
            'id_produk' => 10,
            'qty'       => 2,
        ]);

        $result = $this->controller->add();

        $this->assertInstanceOf(RedirectResponse::class, $result);
        $this->assertSame(
            'Produk tidak ditemukan.',
            session()->getFlashdata('error')
        );
        $this->assertNull(session()->get('cart_u_1'));
    }

    public function testAddStokHabisMengembalikanErrorFlash()
    {
        session()->set('id_user', 1);

        $this->produkRepo->records = [
            10 => [
                'id_produk'   => 10,
                'nama_produk' => 'Produk A',
                'harga'       => 5000,
                'stok'        => 0,
                'foto'        => 'default.png',
            ],
        ];

        $req = service('request');
        $req->setMethod('post');
        $req->setGlobal('post', [
            'id_produk' => 10,
            'qty'       => 3,
        ]);

        $result = $this->controller->add();

        $this->assertInstanceOf(RedirectResponse::class, $result);
        $this->assertSame(
            'Stok produk habis.',
            session()->getFlashdata('error')
        );
        $this->assertNull(session()->get('cart_u_1'));
    }

    public function testAddQtyLebihBesarDariStokDibatasiKeStok()
    {
        session()->set('id_user', 1);

        $this->produkRepo->records = [
            10 => [
                'id_produk'   => 10,
                'nama_produk' => 'Produk A',
                'harga'       => 5000,
                'stok'        => 3,
                'foto'        => 'foto.png',
            ],
        ];

        $req = service('request');
        $req->setMethod('post');
        $req->setGlobal('post', [
            'id_produk' => 10,
            'qty'       => 10,
        ]);

        $result = $this->controller->add();

        $cart = session()->get('cart_u_1');

        $this->assertIsArray($cart);
        $this->assertArrayHasKey(10, $cart);
        $this->assertSame(3, $cart[10]['qty']);
        $this->assertSame(
            'Produk masuk ke keranjang.',
            session()->getFlashdata('success')
        );
    }

    public function testAddKeProdukYangSudahAdaTidakMelebihiStok()
    {
        session()->set('id_user', 1);

        session()->set('cart_u_1', [
            10 => [
                'id_produk'   => 10,
                'nama_produk' => 'Produk A',
                'harga'       => 5000,
                'foto'        => 'foto.png',
                'qty'         => 2,
            ],
        ]);

        $this->produkRepo->records = [
            10 => [
                'id_produk'   => 10,
                'nama_produk' => 'Produk A',
                'harga'       => 5000,
                'stok'        => 3,
                'foto'        => 'foto.png',
            ],
        ];

        $req = service('request');
        $req->setMethod('post');
        $req->setGlobal('post', [
            'id_produk' => 10,
            'qty'       => 5,
        ]);

        $result = $this->controller->add();

        $cart = session()->get('cart_u_1');
        $this->assertSame(3, $cart[10]['qty']);
    }

    public function testUpdateItemTidakAdaMengembalikanError()
    {
        session()->set('id_user', 1);
        session()->set('cart_u_1', []);

        $req = service('request');
        $req->setMethod('post');
        $req->setGlobal('post', [
            'id_produk' => 10,
            'qty'       => 2,
        ]);

        $result = $this->controller->update();

        $this->assertInstanceOf(RedirectResponse::class, $result);
        $this->assertSame(
            'Item tidak ada di keranjang.',
            session()->getFlashdata('error')
        );
    }

    public function testUpdateQtyNolMenghapusItemDariCart()
    {
        session()->set('id_user', 1);
        session()->set('cart_u_1', [
            10 => [
                'id_produk'   => 10,
                'nama_produk' => 'Produk A',
                'harga'       => 5000,
                'foto'        => 'foto.png',
                'qty'         => 2,
            ],
        ]);

        $req = service('request');
        $req->setMethod('post');
        $req->setGlobal('post', [
            'id_produk' => 10,
            'qty'       => 0,
        ]);

        $result = $this->controller->update();

        $cart = session()->get('cart_u_1');
        $this->assertIsArray($cart);
        $this->assertArrayNotHasKey(10, $cart);
    }

    public function testUpdateQtyLebihBesarDariStokDibatasi()
    {
        session()->set('id_user', 1);
        session()->set('cart_u_1', [
            10 => [
                'id_produk'   => 10,
                'nama_produk' => 'Produk A',
                'harga'       => 5000,
                'foto'        => 'foto.png',
                'qty'         => 1,
            ],
        ]);

        $this->produkRepo->records = [
            10 => [
                'id_produk'   => 10,
                'nama_produk' => 'Produk A',
                'harga'       => 5000,
                'stok'        => 2,
                'foto'        => 'foto.png',
            ],
        ];

        $req = service('request');
        $req->setMethod('post');
        $req->setGlobal('post', [
            'id_produk' => 10,
            'qty'       => 10,
        ]);

        $result = $this->controller->update();

        $cart = session()->get('cart_u_1');
        $this->assertSame(2, $cart[10]['qty']);
    }

    public function testRemoveCartMenghapusItemDanSyncCount()
    {
        session()->set('id_user', 1);
        session()->set('cart_u_1', [
            10 => ['id_produk' => 10, 'harga' => 5000, 'qty' => 1],
        ]);
        session()->set('cart_count_u_1', 1);

        $result = $this->controller->remove(10);

        $this->assertInstanceOf(RedirectResponse::class, $result);
        $this->assertSame([], session()->get('cart_u_1') ?? []);
        $this->assertSame(0, session()->get('cart_count_u_1'));
    }

    public function testClearCartMenghapusSemuaKeyCartDanCount()
    {
        session()->set('id_user', 1);
        session()->set('cart_u_1', [
            10 => ['id_produk' => 10, 'harga' => 5000, 'qty' => 1],
        ]);
        session()->set('cart_count_u_1', 1);

        $result = $this->controller->clear();

        $this->assertInstanceOf(RedirectResponse::class, $result);
        $this->assertNull(session()->get('cart_u_1'));
        $this->assertNull(session()->get('cart_count_u_1'));
    }

    public function testCheckoutAllKeranjangKosongMengembalikanError()
    {
        session()->set('id_user', 1);
        session()->set('cart_u_1', []);

        $result = $this->controller->checkoutAll();

        $this->assertInstanceOf(RedirectResponse::class, $result);
        $this->assertSame(
            'Keranjang kosong.',
            session()->getFlashdata('error')
        );
        $this->assertNull(session()->get('checkout_all'));
    }

    public function testCheckoutAllTanpaItemValidMengembalikanError()
    {
        session()->set('id_user', 1);
        session()->set('cart_u_1', [
            10 => ['id_produk' => 10, 'harga' => 5000, 'qty' => 2],
        ]);

        $this->produkRepo->records = [];

        $result = $this->controller->checkoutAll();

        $this->assertInstanceOf(RedirectResponse::class, $result);
        $this->assertSame(
            'Tidak ada item yang dapat di-checkout.',
            session()->getFlashdata('error')
        );
        $this->assertNull(session()->get('checkout_all'));
    }

    public function testCheckoutAllDenganPenyesuaianStokMenyimpanPayloadDanInfoFlash()
    {
        session()->set('id_user', 1);
        session()->set('cart_u_1', [
            10 => ['id_produk' => 10, 'harga' => 5000, 'qty' => 10],
            20 => ['id_produk' => 20, 'harga' => 3000, 'qty' => 2],
        ]);

        $this->produkRepo->records = [
            10 => ['id_produk' => 10, 'stok' => 5],
            20 => ['id_produk' => 20, 'stok' => 2],
        ];

        $result = $this->controller->checkoutAll();

        $this->assertInstanceOf(RedirectResponse::class, $result);
        $this->assertStringContainsString(
            '/melakukanpemesanan',
            $result->getHeaderLine('Location')
        );

        $payload = session()->get('checkout_all');
        $this->assertEquals(
            [
                ['id_produk' => 10, 'qty' => 5],
                ['id_produk' => 20, 'qty' => 2],
            ],
            $payload
        );
        $this->assertSame(
            'Sebagian jumlah menyesuaikan stok tersedia.',
            session()->getFlashdata('info')
        );
    }

    public function testCheckoutAllTanpaPenyesuaianStokTidakAdaInfoFlash()
    {
        session()->set('id_user', 1);
        session()->set('cart_u_1', [
            10 => ['id_produk' => 10, 'harga' => 5000, 'qty' => 2],
        ]);

        $this->produkRepo->records = [
            10 => ['id_produk' => 10, 'stok' => 5],
        ];

        $result = $this->controller->checkoutAll();

        $this->assertInstanceOf(RedirectResponse::class, $result);
        $payload = session()->get('checkout_all');

        $this->assertEquals(
            [['id_produk' => 10, 'qty' => 2]],
            $payload
        );
        $this->assertNull(
            session()->getFlashdata('info')
        );
    }
}