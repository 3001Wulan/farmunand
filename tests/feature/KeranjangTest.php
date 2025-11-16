<?php

namespace Tests\Controllers;

use App\Controllers\Keranjang;
use App\Models\ProdukModel;
use App\Models\UserModel;
use CodeIgniter\Test\CIUnitTestCase;

class KeranjangTest extends CIUnitTestCase
{
    protected $keranjang;
    protected $produkModelMock;
    protected $userModelMock;

    protected function setUp(): void
    {
        parent::setUp();

        // Mock ProdukModel
        $this->produkModelMock = $this->getMockBuilder(ProdukModel::class)
            ->onlyMethods(['find'])
            ->getMock();

        // Mock UserModel
        $this->userModelMock = $this->getMockBuilder(UserModel::class)
            ->onlyMethods(['find'])
            ->getMock();

        // Instance controller
        $this->keranjang = new Keranjang();

        // Inject mock ke protected properties via Reflection
        $reflection = new \ReflectionClass($this->keranjang);

        $propProduk = $reflection->getProperty('produkModel');
        $propProduk->setAccessible(true);
        $propProduk->setValue($this->keranjang, $this->produkModelMock);

        $propUser = $reflection->getProperty('userModel');
        $propUser->setAccessible(true);
        $propUser->setValue($this->keranjang, $this->userModelMock);

        // Bersihkan session sebelum test
        session()->destroy();
    }

    protected function injectRequestMock(array $post = [])
    {
        $requestMock = $this->getMockBuilder(\CodeIgniter\HTTP\IncomingRequest::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getPost'])
            ->getMock();

        // setup getPost untuk setiap key
        $map = [];
        foreach ($post as $key => $value) {
            $map[] = [$key, $value];
        }
        $requestMock->method('getPost')->willReturnMap($map);

        $reflection = new \ReflectionClass($this->keranjang);
        $prop = $reflection->getProperty('request');
        $prop->setAccessible(true);
        $prop->setValue($this->keranjang, $requestMock);
    }

    public function testIndexRedirectsWhenNotLoggedIn()
    {
        $result = $this->keranjang->index();
        $this->assertInstanceOf(\CodeIgniter\HTTP\RedirectResponse::class, $result);
        $this->assertStringContainsString('/login', $result->getHeaderLine('Location'));
    }

    public function testIndexShowsCartWhenLoggedIn()
    {
        session()->set('id_user', 1);

        // Mock user lengkap untuk view
        $this->userModelMock->method('find')->willReturn([
            'id_user' => 1,
            'nama' => 'User Test',
            'username' => 'usertest',
            'role' => 'pembeli', // role sesuai layout/sidebar
        ]);

        // Bungkus view dengan output buffer agar tidak risky
        ob_start();
        $result = $this->keranjang->index();
        ob_end_clean();

        $this->assertIsString($result);
        $this->assertStringContainsString('cart', $result);
    }

    public function testAddProductToCart()
{
    session()->set('id_user', 1);

    $this->produkModelMock->method('find')->willReturn([
        'id_produk' => 10,
        'nama_produk' => 'Produk Test',
        'harga' => 5000,
        'stok' => 10,
        'foto' => 'test.png'
    ]);

    $this->injectRequestMock(['id_produk'=>10,'qty'=>2]);

    $result = $this->keranjang->add();

    $cart = session()->get('cart_u_1') ?? [];
    $this->assertArrayHasKey(10, $cart); // pastikan key ada
    $this->assertEquals(2, $cart[10]['qty']);
}

public function testUpdateCart()
{
    session()->set('id_user', 1);
    session()->set('cart_u_1', [
        10 => ['id_produk'=>10,'nama_produk'=>'Test','harga'=>1000,'qty'=>1]
    ]);

    $this->produkModelMock->method('find')->willReturn([
        'id_produk' => 10,
        'stok' => 10 // stok cukup
    ]);

    $this->injectRequestMock(['id_produk'=>10,'qty'=>3]);

    $result = $this->keranjang->update();

    $cart = session()->get('cart_u_1') ?? [];
    $this->assertEquals(3, $cart[10]['qty']);
}

    public function testRemoveCart()
    {
        session()->set('id_user', 1);
        session()->set('cart_u_1', [10 => ['qty'=>1]]);

        $result = $this->keranjang->remove(10);
        $this->assertEmpty(session()->get('cart_u_1'));
    }

    public function testClearCart()
    {
        session()->set('id_user', 1);
        session()->set('cart_u_1', [10 => ['qty'=>1]]);
        session()->set('cart_count_u_1', 1);

        $result = $this->keranjang->clear();
        $this->assertEmpty(session()->get('cart_u_1'));
        $this->assertEmpty(session()->get('cart_count_u_1'));
    }

    public function testCheckoutAllAdjustsQty()
    {
        session()->set('id_user', 1);
        session()->set('cart_u_1', [
            10 => ['id_produk'=>10,'nama_produk'=>'Produk Test','harga'=>5000,'qty'=>10],
            20 => ['id_produk'=>20,'nama_produk'=>'Produk Test 2','harga'=>3000,'qty'=>2],
        ]);

        // Mock stok produk
        $this->produkModelMock->method('find')
            ->willReturnMap([
                [10, ['id_produk'=>10,'stok'=>5]],
                [20, ['id_produk'=>20,'stok'=>2]],
            ]);

        $result = $this->keranjang->checkoutAll();
        $checkout = session()->get('checkout_all');

        $this->assertEquals([
            ['id_produk'=>10,'qty'=>5],
            ['id_produk'=>20,'qty'=>2],
        ], $checkout);

        $this->assertEquals('Sebagian jumlah menyesuaikan stok tersedia.', session()->getFlashdata('info'));
    }
}
