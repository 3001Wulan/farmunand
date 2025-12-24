<?php

namespace Tests\Controllers;

use App\Controllers\ProdukAdmin;
use CodeIgniter\Exceptions\PageNotFoundException;
use CodeIgniter\HTTP\IncomingRequest;
use CodeIgniter\HTTP\RedirectResponse;
use CodeIgniter\Test\CIUnitTestCase;

class FakeProdukBuilder
{
    public int $groupStartCount = 0;
    public array $likeCalls = [];
    public array $orLikeCalls = [];
    public array $groupEndCalls = [];
    public array $whereCalls = [];
    public array $result = [];

    public function groupStart()
    {
        $this->groupStartCount++;
        return $this;
    }

    public function like(string $field, $match)
    {
        $this->likeCalls[] = [$field, $match];
        return $this;
    }

    public function orLike(string $field, $match)
    {
        $this->orLikeCalls[] = [$field, $match];
        return $this;
    }

    public function groupEnd()
    {
        $this->groupEndCalls[] = true;
        return $this;
    }

    public function where(string $field, $value)
    {
        $this->whereCalls[] = [$field, $value];
        return $this;
    }

    public function get()
    {
        return new class ($this->result) {
            private array $result;
            public function __construct(array $result)
            {
                $this->result = $result;
            }
            public function getResultArray(): array
            {
                return $this->result;
            }
        };
    }
}

class FakeProdukModel
{
    public array $findAllResult = [];
    public int $findAllCalled = 0;
    public ?FakeProdukBuilder $builderInstance = null;
    public int $builderCalled = 0;
    public array $kategoriList = [];
    public int $getKategoriListCalled = 0;
    public array $findMap = [];
    public array $savedData = [];
    public array $updatedData = [];
    public array $whereCalls = [];
    public array $deleteCalls = [];

    public function findAll(): array
    {
        $this->findAllCalled++;
        return $this->findAllResult;
    }

    public function builder(): FakeProdukBuilder
    {
        $this->builderCalled++;
        if ($this->builderInstance === null) {
            $this->builderInstance = new FakeProdukBuilder();
        }
        return $this->builderInstance;
    }

    public function getKategoriList(): array
    {
        $this->getKategoriListCalled++;
        return $this->kategoriList;
    }

    public function find($id = null)
    {
        return $this->findMap[$id] ?? null;
    }

    public function save(array $data): bool
    {
        $this->savedData[] = $data;
        return true;
    }

    public function update($id = null, $data = null): bool
    {
        $this->updatedData[] = ['id' => $id, 'data' => $data];
        return true;
    }

    public function where(string $field, $value): self
    {
        $this->whereCalls[] = [$field, $value];
        return $this;
    }

    public function delete($id = null, bool $purge = false): bool
    {
        $this->deleteCalls[] = ['id' => $id, 'purge' => $purge];
        return true;
    }
}

class FakeUserModel
{
    public array $findMap = [];
    public array $findCalls = [];

    public function find($id = null)
    {
        $this->findCalls[] = $id;
        return $this->findMap[$id] ?? null;
    }
}

class ProdukAdminTest extends CIUnitTestCase
{
    private FakeProdukModel $produkModel;
    private FakeUserModel $userModel;

    protected function setUp(): void
    {
        parent::setUp();
        $_SESSION = [];
        session()->destroy();
        $this->produkModel = new FakeProdukModel();
        $this->userModel   = new FakeUserModel();
    }

    private function makeRequestMock(array $vars = [], $file = null): IncomingRequest
    {
        $request = $this->getMockBuilder(IncomingRequest::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getVar', 'getFile'])
            ->getMock();

        $request->method('getVar')
            ->willReturnCallback(function (?string $key = null) use ($vars) {
                if ($key === null) {
                    return $vars;
                }
                return $vars[$key] ?? null;
            });

        $request->method('getFile')
            ->willReturnCallback(function (string $key) use ($file) {
                if ($key === 'foto') {
                    return $file;
                }
                return null;
            });

        return $request;
    }

    private function makeController(
        IncomingRequest $request,
        FakeProdukModel $produkModel,
        FakeUserModel $userModel
    ): ProdukAdmin {
        $controller = new \App\Controllers\ProdukAdmin();
        $ref = new \ReflectionClass($controller);

        foreach (['produkModel' => $produkModel, 'userModel' => $userModel, 'request' => $request] as $prop => $val) {
            if ($ref->hasProperty($prop)) {
                $p = $ref->getProperty($prop);
                $p->setAccessible(true);
                $p->setValue($controller, $val);
            }
        }

        return $controller;
    }

    public function testIndexTanpaFilterMemakaiFindAllDanKategoriList()
    {
        session()->set('id_user', 1);
        $this->userModel->findMap[1] = [
            'id_user'   => 1,
            'username'  => 'admin',
            'email'     => 'admin@example.com',
        ];

        $this->produkModel->findAllResult = [
            [
                'id_produk'   => 1,
                'nama_produk' => 'Produk A',
                'deskripsi'   => 'Desc A',
                'harga'       => 10000,
                'stok'        => 3,
                'foto'        => 'default.png',
                'kategori'    => 'Makanan',
            ],
            [
                'id_produk'   => 2,
                'nama_produk' => 'Produk B',
                'deskripsi'   => 'Desc B',
                'harga'       => 20000,
                'stok'        => 5,
                'foto'        => 'default.png',
                'kategori'    => 'Minuman',
            ],
        ];
        $this->produkModel->kategoriList = ['Makanan', 'Minuman'];

        $request    = $this->makeRequestMock(['keyword' => null, 'kategori' => null]);
        $controller = $this->makeController($request, $this->produkModel, $this->userModel);

        ob_start();
        $output = $controller->index();
        ob_end_clean();

        $this->assertIsString($output);
        $this->assertNotSame('', trim($output));

        $this->assertSame(1, $this->produkModel->findAllCalled);
        $this->assertSame(1, $this->produkModel->getKategoriListCalled);
        $this->assertSame(0, $this->produkModel->builderCalled);
        $this->assertSame([1], $this->userModel->findCalls);
    }

    public function testIndexDenganKeywordMenggunakanBuilderLikeNamaProduk()
    {
        session()->set('id_user', 1);
        $this->userModel->findMap[1] = ['id_user' => 1, 'username' => 'admin'];

        $builder = new FakeProdukBuilder();
        $builder->result = [
            [
                'id_produk'   => 1,
                'nama_produk' => 'Kopi Susu',
                'deskripsi'   => 'Kopi enak',
                'harga'       => 15000,
                'stok'        => 10,
                'foto'        => 'default.png',
                'kategori'    => 'Minuman',
            ],
        ];
        $this->produkModel->builderInstance   = $builder;
        $this->produkModel->kategoriList      = ['Minuman'];
        $this->produkModel->findAllResult     = [];

        $request    = $this->makeRequestMock(['keyword' => 'kopi', 'kategori' => '']);
        $controller = $this->makeController($request, $this->produkModel, $this->userModel);

        ob_start();
        $output = $controller->index();
        ob_end_clean();

        $this->assertIsString($output);
        $this->assertNotSame('', trim($output));

        $this->assertSame(0, $this->produkModel->findAllCalled);
        $this->assertSame(1, $this->produkModel->builderCalled);
        $this->assertSame(1, $builder->groupStartCount);
        $this->assertSame([['nama_produk', 'kopi']], $builder->likeCalls);
        $this->assertSame([['deskripsi', 'kopi']], $builder->orLikeCalls);
        $this->assertSame([], $builder->whereCalls);
    }

    public function testIndexDenganKategoriSajaMemakaiWhereKategori()
    {
        session()->set('id_user', 1);
        $this->userModel->findMap[1] = ['id_user' => 1, 'username' => 'admin'];

        $builder = new FakeProdukBuilder();
        $builder->result = [
            [
                'id_produk'   => 2,
                'nama_produk' => 'Teh Manis',
                'deskripsi'   => 'Teh dingin',
                'harga'       => 8000,
                'stok'        => 20,
                'foto'        => 'default.png',
                'kategori'    => 'Minuman',
            ],
        ];
        $this->produkModel->builderInstance  = $builder;
        $this->produkModel->kategoriList     = ['Makanan', 'Minuman'];

        $request    = $this->makeRequestMock(['keyword' => '', 'kategori' => 'Minuman']);
        $controller = $this->makeController($request, $this->produkModel, $this->userModel);

        ob_start();
        $output = $controller->index();
        ob_end_clean();

        $this->assertIsString($output);
        $this->assertNotSame('', trim($output));

        $this->assertSame(1, $this->produkModel->builderCalled);
        $this->assertSame(0, $this->produkModel->findAllCalled);
        $this->assertSame(0, $builder->groupStartCount);
        $this->assertSame([], $builder->likeCalls);
        $this->assertSame([], $builder->orLikeCalls);
        $this->assertSame([['kategori', 'Minuman']], $builder->whereCalls);
    }

    public function testStoreBerhasilTanpaFotoMemakaiDefaultPng()
    {
        $vars = [
            'nama_produk' => 'Produk Tanpa Foto',
            'deskripsi'   => 'Deskripsi singkat',
            'harga'       => '15000',
            'stok'        => '7',
        ];

        $request    = $this->makeRequestMock($vars, null);
        $controller = $this->makeController($request, $this->produkModel, $this->userModel);

        $response = $controller->store();

        $this->assertInstanceOf(RedirectResponse::class, $response);
        $this->assertSame(302, $response->getStatusCode());
        $this->assertStringContainsString('/admin/produk', $response->getHeaderLine('Location'));

        $this->assertCount(1, $this->produkModel->savedData);
        $saved = $this->produkModel->savedData[0];

        $this->assertSame('Produk Tanpa Foto', $saved['nama_produk']);
        $this->assertSame('Deskripsi singkat', $saved['deskripsi']);
        $this->assertSame('default.png', $saved['foto']);
        $this->assertSame('15000', $saved['harga']);
        $this->assertSame('7', $saved['stok']);
        $this->assertSame(0, $saved['rating']);
    }

    public function testStoreMenolakFileFotoLebihDari10MB()
    {
        $vars = [
            'nama_produk' => 'Produk Besar',
            'deskripsi'   => 'Deskripsi besar',
            'harga'       => '20000',
            'stok'        => '3',
        ];

        $bigFile = new class {
            public function isValid() { return true; }
            public function hasMoved() { return false; }
            public function getSize() { return 11 * 1024 * 1024; }
            public function getRandomName() { return 'bigfile.jpg'; }
            public function move($path, $name) { }
        };

        $request    = $this->makeRequestMock($vars, $bigFile);
        $controller = $this->makeController($request, $this->produkModel, $this->userModel);

        $response = $controller->store();

        $this->assertInstanceOf(RedirectResponse::class, $response);
        $this->assertSame(
            'Ukuran file foto maksimal 10MB.',
            session()->getFlashdata('error')
        );

        $this->assertCount(0, $this->produkModel->savedData);
    }

    public function testUpdateTanpaUploadFotoMenggunakanFotoLama()
    {
        $idProduk = 99;

        $this->produkModel->findMap[$idProduk] = [
            'id_produk'   => $idProduk,
            'nama_produk' => 'Produk Lama',
            'deskripsi'   => 'Deskripsi Lama',
            'foto'        => 'lama.png',
            'harga'       => '5000',
            'stok'        => '2',
        ];

        $vars = [
            'nama_produk' => 'Produk Lama (Update)',
            'deskripsi'   => 'Deskripsi Lama Update',
            'harga'       => '6000',
            'stok'        => '4',
        ];

        $request    = $this->makeRequestMock($vars, null);
        $controller = $this->makeController($request, $this->produkModel, $this->userModel);

        $response = $controller->update($idProduk);

        $this->assertInstanceOf(RedirectResponse::class, $response);
        $this->assertStringContainsString('/admin/produk', $response->getHeaderLine('Location'));

        $this->assertCount(1, $this->produkModel->updatedData);
        $update = $this->produkModel->updatedData[0];

        $this->assertSame($idProduk, $update['id']);
        $this->assertSame('lama.png', $update['data']['foto']);
        $this->assertSame('Produk Lama (Update)', $update['data']['nama_produk']);
    }

    public function testUpdateMelemparPageNotFoundJikaProdukTidakAda()
    {
        $this->expectException(PageNotFoundException::class);

        $idProduk = 123;
        $request  = $this->makeRequestMock([], null);

        $controller = $this->makeController($request, $this->produkModel, $this->userModel);
        $controller->update($idProduk);
    }

    public function testDeleteDenganProdukAdaMemanggilWhereDanDelete()
    {
        $idProduk = 99;

        $this->produkModel->findMap[$idProduk] = [
            'id_produk'   => $idProduk,
            'nama_produk' => 'Produk Hapus',
            'foto'        => 'default.png',
        ];

        $request    = $this->makeRequestMock([], null);
        $controller = $this->makeController($request, $this->produkModel, $this->userModel);

        $response = $controller->delete($idProduk);

        $this->assertInstanceOf(RedirectResponse::class, $response);
        $this->assertStringContainsString('/admin/produk', $response->getHeaderLine('Location'));

        $this->assertCount(1, $this->produkModel->whereCalls);
        $this->assertSame(['id_produk', $idProduk], $this->produkModel->whereCalls[0]);

        $this->assertCount(1, $this->produkModel->deleteCalls);
        $this->assertNull($this->produkModel->deleteCalls[0]['id']);

        $this->assertSame(
            'Produk berhasil dihapus!',
            session()->getFlashdata('success')
        );
    }

    public function testDeleteTanpaProdukTidakMemanggilDelete()
    {
        $idProduk = 777;

        $request    = $this->makeRequestMock([], null);
        $controller = $this->makeController($request, $this->produkModel, $this->userModel);

        $response = $controller->delete($idProduk);

        $this->assertInstanceOf(RedirectResponse::class, $response);
        $this->assertStringContainsString('/admin/produk', $response->getHeaderLine('Location'));

        $this->assertSame([], $this->produkModel->whereCalls);
        $this->assertSame([], $this->produkModel->deleteCalls);

        $this->assertSame(
            'Produk berhasil dihapus!',
            session()->getFlashdata('success')
        );
    }
}