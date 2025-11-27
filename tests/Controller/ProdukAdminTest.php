<?php

namespace Tests\Controllers;

use App\Controllers\ProdukAdmin;
use CodeIgniter\Exceptions\PageNotFoundException;
use CodeIgniter\HTTP\IncomingRequest;
use CodeIgniter\HTTP\RedirectResponse;
use CodeIgniter\Test\CIUnitTestCase;

/**
 * Fake builder sederhana untuk mensimulasikan Query Builder
 * yang dipakai di ProdukAdmin::index().
 */
class FakeProdukBuilder
{
    public int $groupStartCount = 0;
    public array $likeCalls = [];
    public array $orLikeCalls = [];
    public array $groupEndCalls = [];
    public array $whereCalls = [];

    /** @var array hasil yang akan dikembalikan get()->getResultArray() */
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
        // Object kecil yang cuma punya getResultArray()
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

/**
 * Fake ProdukModel – tidak menyentuh DB sama sekali.
 */
class FakeProdukModel
{
    public array $findAllResult = [];
    public int $findAllCalled = 0;

    public ?FakeProdukBuilder $builderInstance = null;
    public int $builderCalled = 0;

    public array $kategoriList = [];
    public int $getKategoriListCalled = 0;

    /** @var array<int, array|null> */
    public array $findMap = [];

    /** @var array[] list data yang pernah di-save */
    public array $savedData = [];

    /** @var array[] list update yang pernah dipanggil */
    public array $updatedData = [];

    /** @var array[] log pemanggilan where() di delete() */
    public array $whereCalls = [];

    /** @var array[] log pemanggilan delete() */
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

/**
 * Fake UserModel – cukup butuh find().
 */
class FakeUserModel
{
    /** @var array<int, array> */
    public array $findMap = [];

    /** @var array log id yang pernah dicari */
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

        // Reset session di setiap test
        $_SESSION = [];
        session()->destroy();

        $this->produkModel = new FakeProdukModel();
        $this->userModel   = new FakeUserModel();
    }

    /**
     * Helper untuk membuat mock request dengan getVar & getFile.
     */
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

    /**
     * Helper untuk inject model & request ke dalam controller.
     */
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

    /** --------------------------------------------------------
     * 1. index tanpa filter → pakai findAll() dan getKategoriList()
     * -------------------------------------------------------- */
    public function testIndexTanpaFilterMemakaiFindAllDanKategoriList()
    {
        session()->set('id_user', 1);
        $this->userModel->findMap[1] = [
            'id_user'   => 1,
            'username'  => 'admin',
            'email'     => 'admin@example.com',
        ];

        // Lengkapi field sesuai yang dipakai di view
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

        // Jaga-jaga kalau ada echo dari view
        ob_start();
        $output = $controller->index();
        ob_end_clean();

        $this->assertIsString($output);
        $this->assertNotSame('', trim($output));

        // Pastikan pakai findAll & kategori list
        $this->assertSame(1, $this->produkModel->findAllCalled);
        $this->assertSame(1, $this->produkModel->getKategoriListCalled);
        // Tidak memanggil builder jika tanpa filter
        $this->assertSame(0, $this->produkModel->builderCalled);
        // User find(id_user) sekali
        $this->assertSame([1], $this->userModel->findCalls);
    }

    /** --------------------------------------------------------
     * 2. index dengan keyword → pakai builder->like/orLike, tanpa where kategori
     * -------------------------------------------------------- */
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
        $this->produkModel->findAllResult     = []; // seharusnya tidak dipakai

        $request    = $this->makeRequestMock(['keyword' => 'kopi', 'kategori' => '']);
        $controller = $this->makeController($request, $this->produkModel, $this->userModel);

        ob_start();
        $output = $controller->index();
        ob_end_clean();

        $this->assertIsString($output);
        $this->assertNotSame('', trim($output));

        // Tidak memakai findAll
        $this->assertSame(0, $this->produkModel->findAllCalled);
        // Memakai builder
        $this->assertSame(1, $this->produkModel->builderCalled);

        // Pola pencarian keyword
        $this->assertSame(1, $builder->groupStartCount);
        $this->assertSame([['nama_produk', 'kopi']], $builder->likeCalls);
        $this->assertSame([['deskripsi', 'kopi']], $builder->orLikeCalls);
        // Tidak ada where kategori
        $this->assertSame([], $builder->whereCalls);
    }

    /** --------------------------------------------------------
     * 3. index dengan kategori saja → builder->where('kategori', ...)
     * -------------------------------------------------------- */
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

        // Tidak ada groupStart/like/orLike ketika hanya filter kategori
        $this->assertSame(0, $builder->groupStartCount);
        $this->assertSame([], $builder->likeCalls);
        $this->assertSame([], $builder->orLikeCalls);

        // Ada where('kategori','Minuman')
        $this->assertSame([['kategori', 'Minuman']], $builder->whereCalls);
    }

    /** --------------------------------------------------------
     * 4. store tanpa upload foto → pakai 'default.png' & rating = 0
     * -------------------------------------------------------- */
    public function testStoreBerhasilTanpaFotoMemakaiDefaultPng()
    {
        $vars = [
            'nama_produk' => 'Produk Tanpa Foto',
            'deskripsi'   => 'Deskripsi singkat',
            'harga'       => '15000',
            'stok'        => '7',
        ];

        $request    = $this->makeRequestMock($vars, null); // getFile('foto') => null
        $controller = $this->makeController($request, $this->produkModel, $this->userModel);

        $response = $controller->store();

        $this->assertInstanceOf(RedirectResponse::class, $response);
        $this->assertSame(302, $response->getStatusCode());
        $this->assertStringContainsString('/admin/produk', $response->getHeaderLine('Location'));

        // Pastikan save dipanggil dengan foto default & rating 0
        $this->assertCount(1, $this->produkModel->savedData);
        $saved = $this->produkModel->savedData[0];

        $this->assertSame('Produk Tanpa Foto', $saved['nama_produk']);
        $this->assertSame('Deskripsi singkat', $saved['deskripsi']);
        $this->assertSame('default.png', $saved['foto']);
        $this->assertSame('15000', $saved['harga']);
        $this->assertSame('7', $saved['stok']);
        $this->assertSame(0, $saved['rating']);
    }

    /** --------------------------------------------------------
     * 5. store menolak file > 10MB dan tidak memanggil save()
     * -------------------------------------------------------- */
    public function testStoreMenolakFileFotoLebihDari10MB()
    {
        $vars = [
            'nama_produk' => 'Produk Besar',
            'deskripsi'   => 'Deskripsi besar',
            'harga'       => '20000',
            'stok'        => '3',
        ];

        // Fake file upload > 10MB
        $bigFile = new class {
            public function isValid() { return true; }
            public function hasMoved() { return false; }
            public function getSize() { return 11 * 1024 * 1024; } // 11MB
            public function getRandomName() { return 'bigfile.jpg'; }
            public function move($path, $name) { /* tidak dipanggil karena size kegedean */ }
        };

        $request    = $this->makeRequestMock($vars, $bigFile);
        $controller = $this->makeController($request, $this->produkModel, $this->userModel);

        $response = $controller->store();

        $this->assertInstanceOf(RedirectResponse::class, $response);
        $this->assertSame(
            'Ukuran file foto maksimal 10MB.',
            session()->getFlashdata('error')
        );

        // Tidak ada data yang disimpan
        $this->assertCount(0, $this->produkModel->savedData);
    }

    /** --------------------------------------------------------
     * 6. update tanpa upload foto → gunakan foto lama
     * -------------------------------------------------------- */
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

        // Tidak ada file yang diupload
        $request    = $this->makeRequestMock($vars, null);
        $controller = $this->makeController($request, $this->produkModel, $this->userModel);

        $response = $controller->update($idProduk);

        $this->assertInstanceOf(RedirectResponse::class, $response);
        $this->assertStringContainsString('/admin/produk', $response->getHeaderLine('Location'));

        $this->assertCount(1, $this->produkModel->updatedData);
        $update = $this->produkModel->updatedData[0];

        $this->assertSame($idProduk, $update['id']);
        $this->assertSame('lama.png', $update['data']['foto'], 'Foto harus tetap memakai foto lama');
        $this->assertSame('Produk Lama (Update)', $update['data']['nama_produk']);
    }

    /** --------------------------------------------------------
     * 7. update produk yang tidak ada → lempar PageNotFoundException
     * -------------------------------------------------------- */
    public function testUpdateMelemparPageNotFoundJikaProdukTidakAda()
    {
        $this->expectException(PageNotFoundException::class);

        $idProduk = 123; // tidak ada di findMap
        $request  = $this->makeRequestMock([], null);

        $controller = $this->makeController($request, $this->produkModel, $this->userModel);
        $controller->update($idProduk);
    }

    /** --------------------------------------------------------
     * 8. delete dengan produk ada → panggil where('id_produk', id) lalu delete()
     * -------------------------------------------------------- */
    public function testDeleteDenganProdukAdaMemanggilWhereDanDelete()
    {
        $idProduk = 99;

        $this->produkModel->findMap[$idProduk] = [
            'id_produk'   => $idProduk,
            'nama_produk' => 'Produk Hapus',
            'foto'        => 'default.png', // supaya tidak masuk branch unlink()
        ];

        $request    = $this->makeRequestMock([], null);
        $controller = $this->makeController($request, $this->produkModel, $this->userModel);

        $response = $controller->delete($idProduk);

        $this->assertInstanceOf(RedirectResponse::class, $response);
        $this->assertStringContainsString('/admin/produk', $response->getHeaderLine('Location'));

        // Pastikan where dipanggil dengan field & ID yang benar
        $this->assertCount(1, $this->produkModel->whereCalls);
        $this->assertSame(['id_produk', $idProduk], $this->produkModel->whereCalls[0]);

        // delete() dipanggil sekali; karena pakai where()->delete() maka id = null
        $this->assertCount(1, $this->produkModel->deleteCalls);
        $this->assertNull($this->produkModel->deleteCalls[0]['id']);

        // Pesan sukses tetap dikirim
        $this->assertSame(
            'Produk berhasil dihapus!',
            session()->getFlashdata('success')
        );
    }

    /** --------------------------------------------------------
     * 9. delete ketika produk tidak ditemukan → tidak memanggil where/delete
     * -------------------------------------------------------- */
    public function testDeleteTanpaProdukTidakMemanggilDelete()
    {
        $idProduk = 777;
        // findMap tidak diisi → find() akan mengembalikan null

        $request    = $this->makeRequestMock([], null);
        $controller = $this->makeController($request, $this->produkModel, $this->userModel);

        $response = $controller->delete($idProduk);

        $this->assertInstanceOf(RedirectResponse::class, $response);
        $this->assertStringContainsString('/admin/produk', $response->getHeaderLine('Location'));

        // Tidak ada where/delete yang dipanggil
        $this->assertSame([], $this->produkModel->whereCalls);
        $this->assertSame([], $this->produkModel->deleteCalls);

        // Tapi controller tetap memberikan flash success yang sama
        $this->assertSame(
            'Produk berhasil dihapus!',
            session()->getFlashdata('success')
        );
    }
}
