<?php

namespace Tests\Controller;

use CodeIgniter\Test\CIUnitTestCase;
use App\Controllers\MelakukanPemesanan as RealMelakukanPemesanan;

/**
 * Fake repository sederhana untuk produk (tanpa DB).
 */
class FakeProdukRepo
{
    /** @var array<int,array> */
    public array $data = [];

    public function __construct(array $products = [])
    {
        foreach ($products as $p) {
            $this->data[(int) $p['id_produk']] = $p;
        }
    }

    public function find($id)
    {
        return $this->data[(int) $id] ?? null;
    }
}

/**
 * Fake repository alamat, disimpan per user.
 */
class FakeAlamatRepo
{
    /** @var array<int,array<int,array>>  userId => list alamat */
    public array $byUser = [];

    public function __construct(array $dataByUser = [])
    {
        $this->byUser = $dataByUser;
    }

    public function getByUser(int $idUser): array
    {
        return $this->byUser[$idUser] ?? [];
    }
}

/**
 * Fake repository user.
 */
class MelakukanPemesananFakeUserRepo
{
    /** @var array<int,array> */
    public array $data = [];

    public function __construct(array $users = [])
    {
        foreach ($users as $u) {
            $this->data[(int) $u['id_user']] = $u;
        }
    }

    public function find(int $id): ?array
    {
        return $this->data[$id] ?? null;
    }
}

/**
 * Fake gateway untuk menyimpan "pesanan" tanpa DB.
 * Hanya mencatat payload yang dikirim controller.
 */
class FakeOrderGateway
{
    /** @var array<int,array{ id:int, header:array, detail:array }> */
    public array $singleCalls = [];

    /** @var array<int,array{ id:int, header:array, details:array[] }> */
    public array $batchCalls = [];

    /** Jika true, createSingle/createBatch akan "gagal" (return 0). */
    public bool $fail = false;

    public function createSingle(array $header, array $detail): int
    {
        if ($this->fail) {
            return 0;
        }

        $id = count($this->singleCalls) + 1;
        $this->singleCalls[] = [
            'id'     => $id,
            'header' => $header,
            'detail' => $detail,
        ];

        return $id;
    }

    public function createBatch(array $header, array $details): int
    {
        if ($this->fail) {
            return 0;
        }

        $id = count($this->batchCalls) + 1;
        $this->batchCalls[] = [
            'id'      => $id,
            'header'  => $header,
            'details' => $details,
        ];

        return $id;
    }
}

/**
 * Versi testable dari MelakukanPemesanan:
 * - Tidak memanggil DB.
 * - Tidak memanggil view().
 * - Mengembalikan ARRAY untuk index/simpan/simpanBatch.
 */
class TestableMelakukanPemesanan extends RealMelakukanPemesanan
{
    /** @var FakeOrderGateway */
    protected $gateway;

    /** Input "POST" buatan untuk unit-test. */
    public array $post = [];

    /** Payload batch buatan untuk unit-test. */
    public array $batchPayload = [];

    public function __construct(
        FakeProdukRepo $produk,
        FakeAlamatRepo $alamat,
        MelakukanPemesananFakeUserRepo $user,
        FakeOrderGateway $gateway
    ) {
        // JANGAN panggil parent::__construct() agar tidak buat model/DB sungguhan
        $this->produkModel = $produk;
        $this->alamatModel = $alamat;
        $this->userModel   = $user;
        $this->gateway     = $gateway;
    }

    public function withPost(array $data): self
    {
        $this->post = $data;
        return $this;
    }

    public function withBatchPayload(array $data): self
    {
        $this->batchPayload = $data;
        return $this;
    }

    protected function getPost(string $key, $default = null)
    {
        return $this->post[$key] ?? $default;
    }

    /**
     * Versi index() unit-test:
     * - Hanya cover alur single-item (idProdukFromSegment + qty dari post).
     * - Return array data untuk view.
     */
    public function index($idProdukFromSegment = null)
    {
        $session = session();

        $idUser = (int) ($session->get('id_user') ?? 0);
        if (!$idUser) {
            return [
                'redirect' => '/login',
                'error'    => 'Silakan login terlebih dahulu.',
            ];
        }

        $idProduk = $idProdukFromSegment ?? (int) ($this->getPost('id_produk', 0));
        $qty      = max(1, (int) $this->getPost('qty', 1));

        if (!$idProduk) {
            return [
                'redirect' => '/keranjang',
                'error'    => 'Data pesanan tidak ditemukan.',
            ];
        }

        $produk = $this->produkModel->find($idProduk);
        if (!$produk) {
            return [
                'redirect' => 'back',
                'error'    => 'Produk tidak ditemukan.',
            ];
        }

        $stok = (int) ($produk['stok'] ?? 0);
        if ($stok <= 0) {
            return [
                'redirect' => 'back',
                'error'    => 'Stok produk habis.',
            ];
        }

        if ($qty > $stok) {
            $qty = $stok;
            $session->setFlashdata('info', 'Jumlah melebihi stok, disesuaikan.');
        }

        $checkout = [
            'id_produk'   => (int) $produk['id_produk'],
            'nama_produk' => $produk['nama_produk'],
            'deskripsi'   => $produk['deskripsi'] ?? '',
            'foto'        => $produk['foto'] ?? 'default.png',
            'harga'       => (float) $produk['harga'],
            'qty'         => $qty,
            'subtotal'    => (float) $produk['harga'] * $qty,
        ];

        $session->set('checkout_data', $checkout);

        return [
            'checkout'       => $checkout,
            'checkout_multi' => null,
            'alamat'         => $this->alamatModel->getByUser($idUser),
            'user'           => $this->userModel->find($idUser),
        ];
    }

    /**
     * Versi simpan() murni tanpa DB.
     * Mengembalikan array status, bukan JSON Response.
     */
    public function simpan(): array
    {
        $session = session();
        $idUser  = (int) ($session->get('id_user') ?? 0);

        if (!$idUser) {
            return ['success' => false, 'message' => 'User belum login'];
        }

        $idProduk = (int) ($this->getPost('id_produk', 0));
        $idAlamat = (int) ($this->getPost('id_alamat', 0));
        $qty      = max(1, (int) $this->getPost('qty', 1));
        $metode   = strtolower(trim((string) ($this->getPost('metode', 'cod') ?: 'cod')));

        if ($idProduk <= 0 || $idAlamat <= 0) {
            return ['success' => false, 'message' => 'Data pesanan tidak lengkap.'];
        }

        $produk = $this->produkModel->find($idProduk);
        if (!$produk) {
            return ['success' => false, 'message' => 'Produk tidak ditemukan.'];
        }

        $stok  = (int) ($produk['stok'] ?? 0);
        $harga = (float) ($produk['harga'] ?? 0);

        if ($stok <= 0) {
            return ['success' => false, 'message' => 'Stok produk habis.'];
        }

        if ($qty > $stok) {
            return ['success' => false, 'message' => 'Jumlah melebihi stok tersedia.'];
        }

        $isCOD  = ($metode === 'cod');
        $status = $isCOD ? 'Dikemas' : 'Menunggu Pembayaran';
        $total  = $harga * $qty;

        // "Simpan" lewat FakeOrderGateway
        $orderId = $this->gateway->createSingle(
            [
                'id_user'          => $idUser,
                'id_alamat'        => $idAlamat,
                'status_pemesanan' => $status,
                'total_harga'      => $total,
                'metode'           => $metode,
            ],
            [
                'id_produk'     => $idProduk,
                'jumlah_produk' => $qty,
                'harga_produk'  => $harga,
            ]
        );

        if ($orderId === 0) {
            return ['success' => false, 'message' => 'Gagal menyimpan pesanan.'];
        }

        // Simulasi bereskan keranjang seperti controller asli
        $cartKey  = 'cart_u_' . $idUser;
        $countKey = 'cart_count_u_' . $idUser;
        $cart     = $session->get($cartKey) ?? [];

        if (isset($cart[$idProduk])) {
            unset($cart[$idProduk]);
            $session->set($cartKey, $cart);

            $count = 0;
            foreach ($cart as $row) {
                $count += (int) ($row['qty'] ?? 0);
            }

            if ($count > 0) {
                $session->set($countKey, $count);
            } else {
                $session->remove([$cartKey, $countKey]);
            }
        }

        $session->remove(['checkout_data']);

        return [
            'success'      => true,
            'status'       => $status,
            'id_pemesanan' => $orderId,
            'total'        => $total,
        ];
    }

    /**
     * Versi simpanBatch() murni tanpa DB.
     */
    public function simpanBatch(): array
    {
        $session = session();
        $idUser  = (int) ($session->get('id_user') ?? 0);

        if (!$idUser) {
            return ['success' => false, 'code' => 401, 'message' => 'Silakan login.'];
        }

        $payload  = $this->batchPayload;
        $idAlamat = (int) ($payload['id_alamat'] ?? 0);
        $metode   = strtolower(trim($payload['metode'] ?? 'cod'));
        $items    = $payload['items'] ?? [];

        if ($idAlamat <= 0 || empty($items) || !is_array($items)) {
            return ['success' => false, 'message' => 'Payload tidak valid.'];
        }

        // Gabung qty per id_produk
        $wanted = [];
        foreach ($items as $it) {
            $pid = (int) ($it['id_produk'] ?? 0);
            $qty = (int) ($it['qty'] ?? 0);
            if ($pid > 0 && $qty > 0) {
                $wanted[$pid] = ($wanted[$pid] ?? 0) + $qty;
            }
        }

        if (!$wanted) {
            return ['success' => false, 'message' => 'Tidak ada item valid.'];
        }

        $detailRows = [];
        $grandTotal = 0.0;

        foreach ($wanted as $pid => $qty) {
            $produk = $this->produkModel->find($pid);
            if (!$produk) {
                return ['success' => false, 'message' => "Produk ID $pid tidak ditemukan."];
            }

            $stok  = (int) ($produk['stok'] ?? 0);
            $harga = (float) ($produk['harga'] ?? 0);

            if ($stok <= 0) {
                return ['success' => false, 'message' => "Stok habis untuk produk ID $pid."];
            }

            if ($qty > $stok) {
                return ['success' => false, 'message' => "Qty melebihi stok untuk produk ID $pid."];
            }

            $subtotal    = $harga * $qty;
            $grandTotal += $subtotal;

            $detailRows[] = [
                'id_produk'     => $pid,
                'jumlah_produk' => $qty,
                'harga_produk'  => $harga,
            ];
        }

        if (!$detailRows) {
            return ['success' => false, 'message' => 'Tidak ada item valid untuk diproses.'];
        }

        $isCOD  = ($metode === 'cod');
        $status = $isCOD ? 'Dikemas' : 'Menunggu Pembayaran';

        $orderId = $this->gateway->createBatch(
            [
                'id_user'          => $idUser,
                'id_alamat'        => $idAlamat,
                'status_pemesanan' => $status,
                'total_harga'      => $grandTotal,
                'metode'           => $metode,
            ],
            $detailRows
        );

        if ($orderId === 0) {
            return ['success' => false, 'message' => 'Gagal menyimpan pesanan.'];
        }

        // Bersihkan keranjang & context batch
        $cartKey  = 'cart_u_' . $idUser;
        $countKey = 'cart_count_u_' . $idUser;
        $session->remove([$cartKey, $countKey, 'checkout_all', 'checkout_data_multi', 'checkout_data']);

        return [
            'success'      => true,
            'status'       => $status,
            'id_pemesanan' => $orderId,
            'total'        => $grandTotal,
        ];
    }
}

/**
 * Test murni unit untuk MelakukanPemesanan (tanpa DB).
 */
class MelakukanPemesananTest extends CIUnitTestCase
{
    private FakeProdukRepo $produkRepo;
    private FakeAlamatRepo $alamatRepo;
    private MelakukanPemesananFakeUserRepo $userRepo;
    private FakeOrderGateway $gateway;
    private TestableMelakukanPemesanan $controller;

    protected function setUp(): void
    {
        parent::setUp();

        // Reset session
        $_SESSION = [];
        session()->destroy();

        // Seed session user login
        session()->set([
            'id_user'   => 1,
            'username'  => 'Test User',
            'role'      => 'user',
            'logged_in' => true,
        ]);

        // Seed produk dummy
        $this->produkRepo = new FakeProdukRepo([
            [
                'id_produk'    => 1,
                'nama_produk'  => 'Produk Test',
                'stok'         => 10,
                'harga'        => 10000.0,
                'deskripsi'    => 'Produk untuk testing',
                'foto'         => 'default.png',
            ],
            [
                'id_produk'    => 2,
                'nama_produk'  => 'Produk Lain',
                'stok'         => 5,
                'harga'        => 20000.0,
            ],
        ]);

        // Seed alamat dummy
        $this->alamatRepo = new FakeAlamatRepo([
            1 => [
                [
                    'id_alamat'     => 1,
                    'id_user'       => 1,
                    'aktif'         => 1,
                    'nama_penerima' => 'Penerima Testing',
                    'jalan'         => 'Jl. Testing No. 123',
                    'no_telepon'    => '08123456789',
                    'kota'          => 'Padang',
                    'provinsi'      => 'Sumatera Barat',
                    'kode_pos'      => '25111',
                ],
            ],
        ]);

        // Seed user dummy
        $this->userRepo = new MelakukanPemesananFakeUserRepo([
            [
                'id_user'  => 1,
                'nama'     => 'Test User',
                'username' => 'testuser',
                'email'    => 'test@example.com',
                'role'     => 'user',
                'foto'     => 'default.png',
            ],
        ]);

        $this->gateway    = new FakeOrderGateway();
        $this->controller = new TestableMelakukanPemesanan(
            $this->produkRepo,
            $this->alamatRepo,
            $this->userRepo,
            $this->gateway
        );
    }

    protected function tearDown(): void
    {
        session()->destroy();
        parent::tearDown();
    }

    /** 🔹 Index single item: data checkout, alamat, dan user harus benar. */
    public function testIndexSingleItemBuildsCheckoutData(): void
    {
        $data = $this->controller
            ->withPost(['qty' => 2]) // id_produk dari segment
            ->index(1);

        $this->assertIsArray($data);
        $this->assertArrayHasKey('checkout', $data);
        $checkout = $data['checkout'];

        $this->assertSame(1, $checkout['id_produk']);
        $this->assertSame('Produk Test', $checkout['nama_produk']);
        $this->assertSame(2, $checkout['qty']);
        $this->assertSame(20000.0, $checkout['subtotal']);

        $this->assertArrayHasKey('alamat', $data);
        $this->assertCount(1, $data['alamat']);
        $this->assertSame('Penerima Testing', $data['alamat'][0]['nama_penerima']);

        $this->assertArrayHasKey('user', $data);
        $this->assertSame('testuser', $data['user']['username']);
    }

    /** 🔹 Index: qty > stok → qty disesuaikan & ada flash info. */
    public function testIndexAdjustsQtyWhenExceedsStock(): void
    {
        $data = $this->controller
            ->withPost(['qty' => 99])
            ->index(1);

        $checkout = $data['checkout'];
        $this->assertSame(10, $checkout['qty']); // stok = 10

        $info = session()->getFlashdata('info');
        $this->assertSame('Jumlah melebihi stok, disesuaikan.', $info);
    }

    /** 🔹 Simpan single item sukses (COD). */
    public function testSimpanSingleItemSuccessCod(): void
    {
        // Keranjang berisi 2 item, salah satunya akan dihapus
        session()->set('cart_u_1', [
            1 => ['id_produk' => 1, 'qty' => 3],
            2 => ['id_produk' => 2, 'qty' => 1],
        ]);
        session()->set('cart_count_u_1', 4);

        $result = $this->controller
            ->withPost([
                'id_produk' => 1,
                'id_alamat' => 1,
                'qty'       => 3,
                'metode'    => 'cod',
            ])
            ->simpan();

        $this->assertTrue($result['success']);
        $this->assertSame('Dikemas', $result['status']);
        $this->assertSame(30000.0, $result['total']); // 3 * 10000

        // Pastikan gateway dipanggil dengan payload yang tepat
        $this->assertCount(1, $this->gateway->singleCalls);
        $call = $this->gateway->singleCalls[0];

        $this->assertSame('Dikemas', $call['header']['status_pemesanan']);
        $this->assertSame(30000.0, $call['header']['total_harga']);
        $this->assertSame(1, $call['detail']['id_produk']);
        $this->assertSame(3, $call['detail']['jumlah_produk']);

        // Item produk 1 dihapus dari keranjang, produk 2 masih ada
        $cart  = session()->get('cart_u_1') ?? [];
        $count = session()->get('cart_count_u_1') ?? 0;

        $this->assertArrayNotHasKey(1, $cart);
        $this->assertArrayHasKey(2, $cart);
        $this->assertSame(1, $count);
    }

    /** 🔹 Simpan gagal ketika qty > stok (tanpa menulis order). */
    public function testSimpanFailsWhenQtyExceedsStock(): void
    {
        $result = $this->controller
            ->withPost([
                'id_produk' => 1,
                'id_alamat' => 1,
                'qty'       => 999,
                'metode'    => 'cod',
            ])
            ->simpan();

        $this->assertFalse($result['success']);
        $this->assertSame('Jumlah melebihi stok tersedia.', $result['message']);
        $this->assertCount(0, $this->gateway->singleCalls);
    }

    /** 🔹 Simpan batch sukses (COD) dengan 2 produk. */
    public function testSimpanBatchSuccess(): void
    {
        $result = $this->controller
            ->withBatchPayload([
                'id_alamat' => 1,
                'metode'    => 'cod',
                'items'     => [
                    ['id_produk' => 1, 'qty' => 2],
                    ['id_produk' => 2, 'qty' => 1],
                ],
            ])
            ->simpanBatch();

        $this->assertTrue($result['success']);
        $this->assertSame('Dikemas', $result['status']);

        // 2*10000 + 1*20000 = 40000
        $this->assertSame(40000.0, $result['total']);

        $this->assertCount(1, $this->gateway->batchCalls);
        $call = $this->gateway->batchCalls[0];

        $this->assertSame(40000.0, $call['header']['total_harga']);
        $this->assertCount(2, $call['details']);

        // Session cart & context batch harus dibersihkan
        $this->assertNull(session()->get('cart_u_1'));
        $this->assertNull(session()->get('cart_count_u_1'));
        $this->assertNull(session()->get('checkout_all'));
        $this->assertNull(session()->get('checkout_data_multi'));
    }

    /** 🔹 Simpan batch gagal ketika qty > stok untuk salah satu produk. */
    public function testSimpanBatchFailsWhenQtyExceedsStock(): void
    {
        $result = $this->controller
            ->withBatchPayload([
                'id_alamat' => 1,
                'metode'    => 'cod',
                'items'     => [
                    ['id_produk' => 1, 'qty' => 999], // stok cuma 10
                ],
            ])
            ->simpanBatch();

        $this->assertFalse($result['success']);
        $this->assertStringContainsString('Qty melebihi stok', $result['message']);
        $this->assertCount(0, $this->gateway->batchCalls);
    }
}
