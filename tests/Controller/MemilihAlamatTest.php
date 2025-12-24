<?php

namespace Tests\Controller;

use App\Controllers\MemilihAlamat;
use CodeIgniter\HTTP\IncomingRequest;
use CodeIgniter\HTTP\RedirectResponse;
use CodeIgniter\Test\CIUnitTestCase;
use Config\Services;

class FakeAlamatModel
{
    private array $rows;
    public array $lastWhere = [];
    public ?array $lastSet = null;
    public array $updateLog = [];
    public array $saveLog = [];

    public function __construct(array $rows = [])
    {
        $this->rows = array_values($rows);
    }

    public function where(string $field, $value): self
    {
        $this->lastWhere[$field] = $value;
        return $this;
    }

    public function orderBy(string $field, string $direction = 'ASC'): self
    {
        usort($this->rows, function ($a, $b) use ($field, $direction) {
            $av = $a[$field] ?? null;
            $bv = $b[$field] ?? null;

            if ($av == $bv) {
                return 0;
            }

            $cmp = $av <=> $bv;
            return strtoupper($direction) === 'DESC' ? -$cmp : $cmp;
        });

        return $this;
    }

    public function findAll(): array
    {
        $rows = $this->rows;

        if (!empty($this->lastWhere)) {
            foreach ($this->lastWhere as $field => $value) {
                $rows = array_values(array_filter(
                    $rows,
                    fn ($r) => ($r[$field] ?? null) === $value
                ));
            }
        }

        return $rows;
    }

    public function find($id)
    {
        foreach ($this->rows as $row) {
            if ((int)($row['id_alamat'] ?? 0) === (int)$id) {
                foreach ($this->lastWhere as $field => $value) {
                    if (($row[$field] ?? null) !== $value) {
                        return null;
                    }
                }
                return $row;
            }
        }
        return null;
    }

    public function set(array $data): self
    {
        $this->lastSet = $data;
        return $this;
    }

    public function update($id = null, $data = null): bool
    {
        if ($id === null) {
            if ($this->lastSet === null) {
                return false;
            }

            foreach ($this->rows as &$row) {
                $match = true;
                foreach ($this->lastWhere as $field => $value) {
                    if (($row[$field] ?? null) !== $value) {
                        $match = false;
                        break;
                    }
                }
                if ($match) {
                    $row = array_merge($row, $this->lastSet);
                }
            }

            $this->updateLog[] = [
                'where' => $this->lastWhere,
                'data'  => $this->lastSet,
            ];

            $this->lastSet = null;
            return true;
        }

        if (!is_array($data)) {
            return false;
        }

        foreach ($this->rows as &$row) {
            if ((int)($row['id_alamat'] ?? 0) === (int)$id) {
                $row = array_merge($row, $data);
            }
        }

        $this->updateLog[] = [
            'id'   => $id,
            'data' => $data,
        ];

        return true;
    }

    public function save(array $data): bool
    {
        if (!isset($data['id_alamat'])) {
            $maxId = 0;
            foreach ($this->rows as $row) {
                $maxId = max($maxId, (int)($row['id_alamat'] ?? 0));
            }
            $data['id_alamat'] = $maxId + 1;
        }

        $this->rows[]    = $data;
        $this->saveLog[] = $data;

        return true;
    }

    public function getRows(): array
    {
        return $this->rows;
    }
}

class FakeUserModel
{
    private array $users;

    public function __construct(array $users = [])
    {
        $this->users = $users;
    }

    public function find($id)
    {
        return $this->users[$id] ?? [
            'id_user' => $id,
            'nama'    => 'User-' . $id,
        ];
    }
}

class MemilihAlamatTest extends CIUnitTestCase
{
    private $controller;
    private $alamatModel;
    private $userModel;
    private $requestMock;

    protected function setUp(): void
    {
        parent::setUp();

        $dummyAlamat = [
            [
                'id_alamat'     => 1,
                'id_user'       => 100,
                'nama_penerima' => 'Penerima Satu',
                'jalan'         => 'Jalan Satu 123',
                'no_telepon'    => '0811111111',
                'kota'          => 'Padang',
                'provinsi'      => 'Sumatera Barat',
                'kode_pos'      => '25111',
                'aktif'         => 1,
            ],
            [
                'id_alamat'     => 2,
                'id_user'       => 100,
                'nama_penerima' => 'Penerima Dua',
                'jalan'         => 'Jalan Dua 456',
                'no_telepon'    => '0822222222',
                'kota'          => 'Padang',
                'provinsi'      => 'Sumatera Barat',
                'kode_pos'      => '25112',
                'aktif'         => 0,
            ],
            [
                'id_alamat'     => 3,
                'id_user'       => 200,
                'nama_penerima' => 'Penerima Lain',
                'jalan'         => 'Jalan Lain 789',
                'no_telepon'    => '0833333333',
                'kota'          => 'Bukittinggi',
                'provinsi'      => 'Sumatera Barat',
                'kode_pos'      => '26111',
                'aktif'         => 1,
            ],
        ];

        $this->alamatModel = new FakeAlamatModel($dummyAlamat);
        $this->userModel   = new FakeUserModel([
            100 => ['id_user' => 100, 'nama' => 'Alamat Tester'],
        ]);

        $this->controller = new class($this->alamatModel, $this->userModel) extends MemilihAlamat {
            public bool $testValidateReturn = true;

            public function __construct($alamatModel, $userModel)
            {
                $this->alamatModel = $alamatModel;
                $this->userModel   = $userModel;
                $this->produkModel = null;
            }

            public function setRequestObject($request): void
            {
                $this->request = $request;
            }

            public function setResponseObject($response): void
            {
                $this->response = $response;
            }

            public function validate($rules, array $messages = []): bool
            {
                return $this->testValidateReturn;
            }

            public function indexForTest(): array
            {
                $idUser = session()->get('id_user');

                $alamat = $this->alamatModel
                    ->where('id_user', $idUser)
                    ->orderBy('id_alamat', 'DESC')
                    ->findAll();

                $user = $this->userModel->find($idUser);

                return [
                    'alamat' => $alamat,
                    'user'   => $user,
                ];
            }
        };

        $response = Services::response();
        $this->controller->setResponseObject($response);

        $this->requestMock = $this->getMockBuilder(IncomingRequest::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getMethod', 'getPost', 'getJSON'])
            ->getMock();
    }

    protected function tearDown(): void
    {
        session()->destroy();
        parent::tearDown();
    }

    public function testIndexMengambilAlamatHanyaUntukUserLogin()
    {
        session()->set(['id_user' => 100]);

        $data = $this->controller->indexForTest();

        $this->assertArrayHasKey('alamat', $data);
        $this->assertArrayHasKey('user', $data);

        $alamat = $data['alamat'];
        $user   = $data['user'];

        $this->assertCount(2, $alamat);

        foreach ($alamat as $row) {
            $this->assertSame(100, $row['id_user']);
        }

        $this->assertSame(2, $alamat[0]['id_alamat']);
        $this->assertSame(1, $alamat[1]['id_alamat']);

        $this->assertSame(100, $user['id_user']);
        $this->assertSame('Alamat Tester', $user['nama']);
    }

    public function testTambahDenganMetodeGetLangsungRedirect()
    {
        session()->set(['id_user' => 100]);

        $this->requestMock
            ->method('getMethod')
            ->willReturn('GET');
        $this->controller->setRequestObject($this->requestMock);

        $response = $this->controller->tambah();

        $this->assertInstanceOf(RedirectResponse::class, $response);
        $this->assertStringContainsString(
            'memilihalamat',
            $response->getHeaderLine('Location')
        );

        $rows = $this->alamatModel->getRows();
        $this->assertCount(3, $rows);
    }

    public function testTambahDenganDataTidakValidTidakMenyimpanAlamat()
    {
        session()->set(['id_user' => 100]);

        $this->requestMock
            ->method('getMethod')
            ->willReturn('POST');

        $this->requestMock
            ->method('getPost')
            ->willReturn([
                'nama_penerima' => '',
                'jalan'         => '',
                'no_telepon'    => '',
                'kota'          => '',
                'provinsi'      => '',
                'kode_pos'      => '',
            ]);

        $this->controller->setRequestObject($this->requestMock);
        $this->controller->testValidateReturn = false;

        $before = $this->alamatModel->getRows();
        $response = $this->controller->tambah();
        $after   = $this->alamatModel->getRows();

        $this->assertInstanceOf(RedirectResponse::class, $response);
        $this->assertSameSize($before, $after);
        $this->assertEmpty($this->alamatModel->saveLog);
    }

    public function testTambahDenganDataValidMenambahAlamatBaruDanMenonaktifkanLama()
    {
        session()->set(['id_user' => 100]);

        $beforeRows = $this->alamatModel->getRows();
        $beforeForUser = array_values(array_filter(
            $beforeRows,
            fn ($r) => $r['id_user'] === 100
        ));
        $this->assertCount(2, $beforeForUser);

        $this->requestMock
            ->method('getMethod')
            ->willReturn('POST');

        $this->requestMock
            ->method('getPost')
            ->willReturnCallback(function ($key = null) {
                $data = [
                    'nama_penerima' => 'Penerima Baru',
                    'jalan'         => 'Jalan Baru 10',
                    'no_telepon'    => '0899999999',
                    'kota'          => 'Padang',
                    'provinsi'      => 'Sumatera Barat',
                    'kode_pos'      => '25113',
                ];

                if ($key === null) {
                    return $data;
                }

                return $data[$key] ?? null;
            });

        $this->controller->setRequestObject($this->requestMock);
        $this->controller->testValidateReturn = true;

        $response = $this->controller->tambah();

        $this->assertInstanceOf(RedirectResponse::class, $response);
        $this->assertStringContainsString(
            'memilihalamat',
            $response->getHeaderLine('Location')
        );

        $afterRows = $this->alamatModel->getRows();
        $afterForUser = array_values(array_filter(
            $afterRows,
            fn ($r) => $r['id_user'] === 100
        ));

        $this->assertCount(3, $afterForUser);

        $aktif = array_values(array_filter(
            $afterForUser,
            fn ($r) => (int) $r['aktif'] === 1
        ));
        $this->assertCount(1, $aktif);

        $nonAktif = array_values(array_filter(
            $afterForUser,
            fn ($r) => (int) $r['aktif'] === 0
        ));
        $this->assertCount(2, $nonAktif);

        $namaList = array_column($afterForUser, 'nama_penerima');

        $this->assertTrue(
            in_array('Penerima Baru', $namaList, true)
        );
    }

    public function testPilihAlamatTidakDitemukanMengembalikanJsonError()
    {
        session()->set(['id_user' => 100]);

        $this->alamatModel->lastWhere = [];

        $response = $this->controller->pilih(9999);

        $data = json_decode($response->getBody(), true);

        $this->assertIsArray($data);
        $this->assertFalse($data['success'] ?? true);
        $this->assertSame('Alamat tidak ditemukan', $data['message'] ?? null);

        $this->assertEmpty($this->alamatModel->updateLog);
        $this->assertNull(session()->get('alamat_aktif'));
    }

    public function testPilihAlamatValidMengubahAktifDanSession()
    {
        session()->set(['id_user' => 100]);

        $response = $this->controller->pilih(2);

        $data = json_decode($response->getBody(), true);
        $this->assertIsArray($data);
        $this->assertTrue($data['success'] ?? false);

        $rows        = $this->alamatModel->getRows();
        $rowsForUser = array_values(array_filter(
            $rows,
            fn ($r) => $r['id_user'] === 100
        ));

        $byId = [];
        foreach ($rowsForUser as $r) {
            $byId[$r['id_alamat']] = $r;
        }

        $this->assertSame(0, (int)$byId[1]['aktif']);
        $this->assertSame(1, (int)$byId[2]['aktif']);

        $sessionAlamat = session()->get('alamat_aktif');
        $this->assertIsArray($sessionAlamat);
        $this->assertSame(2, $sessionAlamat['id_alamat']);
        $this->assertSame('Penerima Dua', $sessionAlamat['nama_penerima']);
    }

    public function testUbahAlamatTidakDitemukanMengembalikanJsonError()
    {
        $response = $this->controller->ubah(9999);

        $data = json_decode($response->getBody(), true);
        $this->assertIsArray($data);
        $this->assertFalse($data['success'] ?? true);
        $this->assertSame('Alamat tidak ditemukan', $data['message'] ?? null);
    }

    public function testUbahAlamatDenganPerubahanMenyimpanUpdateDanMengembalikanChanged()
    {
        $this->requestMock
            ->method('getMethod')
            ->willReturn('POST');

        $this->requestMock
            ->method('getJSON')
            ->with(true)
            ->willReturn([
                'nama_penerima' => 'Penerima Satu Diubah',
                'jalan'         => 'Jalan Satu 123',
                'no_telepon'    => '0811111111',
                'kota'          => 'Padang',
                'provinsi'      => 'Sumatera Barat',
                'kode_pos'      => '25199',
            ]);

        $this->controller->setRequestObject($this->requestMock);

        $response = $this->controller->ubah(1);

        $data = json_decode($response->getBody(), true);

        $this->assertIsArray($data);
        $this->assertTrue($data['success'] ?? false);
        $this->assertArrayHasKey('changed', $data);
        $changed = $data['changed'];

        $this->assertArrayHasKey('nama_penerima', $changed);
        $this->assertArrayHasKey('kode_pos', $changed);

        $this->assertSame('Penerima Satu', $changed['nama_penerima']['old']);
        $this->assertSame('Penerima Satu Diubah', $changed['nama_penerima']['new']);

        $this->assertSame('25111', $changed['kode_pos']['old']);
        $this->assertSame('25199', $changed['kode_pos']['new']);

        $rows = $this->alamatModel->getRows();
        foreach ($rows as $row) {
            if ($row['id_alamat'] === 1) {
                $this->assertSame('Penerima Satu Diubah', $row['nama_penerima']);
                $this->assertSame('25199', $row['kode_pos']);
            }
        }
    }

    public function testUbahAlamatTanpaPerubahanTidakMemanggilUpdate()
    {
        $this->requestMock
            ->method('getMethod')
            ->willReturn('POST');

        $this->requestMock
            ->method('getJSON')
            ->with(true)
            ->willReturn([
                'nama_penerima' => 'Penerima Satu',
                'jalan'         => 'Jalan Satu 123',
                'no_telepon'    => '0811111111',
                'kota'          => 'Padang',
                'provinsi'      => 'Sumatera Barat',
                'kode_pos'      => '25111',
            ]);

        $this->controller->setRequestObject($this->requestMock);

        $this->alamatModel->updateLog = [];

        $response = $this->controller->ubah(1);

        $data = json_decode($response->getBody(), true);
        $this->assertIsArray($data);
        $this->assertFalse($data['success'] ?? true);
        $this->assertSame('Tidak ada perubahan pada alamat', $data['message'] ?? null);

        $this->assertEmpty(
            $this->alamatModel->updateLog
        );
    }
}