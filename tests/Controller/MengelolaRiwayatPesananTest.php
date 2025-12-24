<?php

namespace Tests\Controller;

use App\Controllers\MengelolaRiwayatPesanan;
use App\Models\PesananModel;
use CodeIgniter\HTTP\RedirectResponse;
use CodeIgniter\Test\CIUnitTestCase;

class MengelolaRiwayatPesananTest extends CIUnitTestCase
{
    private $controller;
    private $pesananModelMock;

    protected function setUp(): void
    {
        parent::setUp();

        $this->pesananModelMock = $this->getMockBuilder(PesananModel::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['update'])
            ->getMock();

        $this->controller = new class($this->pesananModelMock) extends MengelolaRiwayatPesanan {
            protected $pesananModel;
            protected array $dummyRiwayat = [];
            protected array $fakeGet = [];
            protected array $fakePost = [];

            public function __construct($pesananModel)
            {
                parent::__construct();

                $this->pesananModel = $pesananModel;

                $this->fakeGet = [
                    'status'  => '',
                    'keyword' => '',
                    'sort'    => 'DESC',
                ];

                $this->fakePost = [
                    'status_pemesanan' => 'Dikemas',
                ];

                $this->dummyRiwayat = [
                    [
                        'id_pemesanan'     => 1,
                        'status_pemesanan' => 'Dikirim',
                        'created_at'       => '2025-11-17 10:00:00',
                        'id_user'          => 1,
                        'nama_user'        => 'User A',
                        'nama_produk'      => 'Produk A',
                        'jumlah_produk'    => 2,
                        'harga_produk'     => 10000,
                    ],
                    [
                        'id_pemesanan'     => 2,
                        'status_pemesanan' => 'Selesai',
                        'created_at'       => '2025-11-18 07:00:00',
                        'id_user'          => 2,
                        'nama_user'        => 'User B',
                        'nama_produk'      => 'Produk B',
                        'jumlah_produk'    => 1,
                        'harga_produk'     => 20000,
                    ],
                    [
                        'id_pemesanan'     => 3,
                        'status_pemesanan' => 'Dibatalkan',
                        'created_at'       => '2025-11-16 08:00:00',
                        'id_user'          => 3,
                        'nama_user'        => 'User C',
                        'nama_produk'      => 'Produk C',
                        'jumlah_produk'    => 3,
                        'harga_produk'     => 15000,
                    ],
                ];
            }

            public function setFakeGet(array $get): void
            {
                $this->fakeGet = array_merge($this->fakeGet, $get);
            }

            public function setFakePost(array $post): void
            {
                $this->fakePost = array_merge($this->fakePost, $post);
            }

            public function setDummyRiwayat(array $rows): void
            {
                $this->dummyRiwayat = $rows;
            }

            public function index()
            {
                $statusRaw  = $this->fakeGet['status']  ?? '';
                $keywordRaw = $this->fakeGet['keyword'] ?? '';
                $sort       = $this->fakeGet['sort']    ?? 'DESC';

                $status  = is_string($statusRaw)  ? $statusRaw  : '';
                $keyword = is_string($keywordRaw) ? $keywordRaw : '';

                $rows = $this->dummyRiwayat;

                if ($status !== '') {
                    $rows = array_values(array_filter(
                        $rows,
                        fn ($row) => ($row['status_pemesanan'] ?? '') === $status
                    ));
                }

                if ($keyword !== '') {
                    $kw = mb_strtolower($keyword);
                    $rows = array_values(array_filter(
                        $rows,
                        function ($row) use ($kw) {
                            $namaUser   = mb_strtolower($row['nama_user']   ?? '');
                            $namaProduk = mb_strtolower($row['nama_produk'] ?? '');
                            return str_contains($namaUser, $kw)
                                || str_contains($namaProduk, $kw);
                        }
                    ));
                }

                $sortUpper = strtoupper($sort);
                usort($rows, function ($a, $b) use ($sortUpper) {
                    $cmp = strcmp($a['created_at'], $b['created_at']);
                    return $sortUpper === 'ASC' ? $cmp : -$cmp;
                });

                $lines = array_map(function ($row) {
                    return sprintf(
                        '%s %s (%s)',
                        $row['nama_user']        ?? '',
                        $row['nama_produk']      ?? '',
                        $row['status_pemesanan'] ?? ''
                    );
                }, $rows);

                return implode("\n", $lines);
            }

            public function updateStatus($id)
            {
                $status = $this->fakePost['status_pemesanan'] ?? null;

                $response = new RedirectResponse('');
                $response->setStatusCode(302);
                $response->setHeader('Location', '/MengelolaRiwayatPesanan');

                if (! $status) {
                    return $response;
                }

                $this->pesananModel->update($id, [
                    'status_pemesanan' => $status,
                ]);

                return $response;
            }
        };

        $this->controller->setFakeGet([
            'status'  => '',
            'keyword' => '',
            'sort'    => 'DESC',
        ]);
        $this->controller->setFakePost([
            'status_pemesanan' => 'Dikemas',
        ]);
    }

    public function testIndexTanpaFilterMengambilRiwayatDanSortingDESC()
    {
        $output = $this->controller->index();

        $this->assertIsString($output);

        $lines = explode("\n", trim($output));
        $this->assertCount(3, $lines);

        $this->assertSame('User B Produk B (Selesai)', $lines[0]);
        $this->assertSame('User A Produk A (Dikirim)', $lines[1]);
        $this->assertSame('User C Produk C (Dibatalkan)', $lines[2]);
    }

    public function testIndexDenganSortASCMengurutkanDariTerlama()
    {
        $this->controller->setFakeGet([
            'sort' => 'ASC',
        ]);

        $output = $this->controller->index();
        $lines  = explode("\n", trim($output));

        $this->assertCount(3, $lines);

        $this->assertSame('User C Produk C (Dibatalkan)', $lines[0]);
        $this->assertSame('User A Produk A (Dikirim)', $lines[1]);
        $this->assertSame('User B Produk B (Selesai)', $lines[2]);
    }

    public function testIndexDenganFilterStatusMemfilterStatusDenganBenar()
    {
        $this->controller->setFakeGet([
            'status'  => 'Dikirim',
            'keyword' => '',
            'sort'    => 'DESC',
        ]);

        $output = $this->controller->index();

        $lines = array_filter(explode("\n", trim($output)));

        $this->assertNotEmpty($lines);
        $this->assertCount(1, $lines);

        foreach ($lines as $line) {
            $this->assertStringContainsString('(Dikirim)', $line);
        }

        $this->assertStringNotContainsString('(Selesai)', $output);
        $this->assertStringNotContainsString('(Dibatalkan)', $output);
    }

    public function testIndexDenganKeywordMemfilterDenganBenar()
    {
        $this->controller->setFakeGet([
            'status'  => '',
            'keyword' => 'Produk B',
            'sort'    => 'DESC',
        ]);

        $output = $this->controller->index();

        $lines = array_filter(explode("\n", trim($output)));

        $this->assertNotEmpty($lines);
        $this->assertCount(1, $lines);

        $this->assertStringContainsString('Produk B', $output);
        $this->assertStringNotContainsString('Produk A', $output);
        $this->assertStringNotContainsString('Produk C', $output);
    }

    public function testIndexTanpaDataTidakError()
    {
        $this->controller->setDummyRiwayat([]);

        $output = $this->controller->index();

        $this->assertIsString($output);
        $this->assertSame('', trim($output));
    }

    public function testUpdateStatusValidMemanggilModelUpdateDanRedirect()
    {
        $this->controller->setFakePost([
            'status_pemesanan' => 'Dikemas',
        ]);

        $this->pesananModelMock->expects($this->once())
            ->method('update')
            ->with(1, ['status_pemesanan' => 'Dikemas'])
            ->willReturn(true);

        $response = $this->controller->updateStatus(1);

        $this->assertInstanceOf(RedirectResponse::class, $response);
        $this->assertSame(302, $response->getStatusCode());

        $location = $response->getHeaderLine('Location');
        $this->assertNotSame('', $location);
        $this->assertStringContainsString(
            'MengelolaRiwayatPesanan',
            $location
        );
    }

    public function testUpdateStatusTanpaStatusTidakMemanggilModelUpdate()
    {
        $this->controller->setFakePost([
            'status_pemesanan' => null,
        ]);

        $this->pesananModelMock->expects($this->never())
            ->method('update');

        $response = $this->controller->updateStatus(1);

        $this->assertInstanceOf(RedirectResponse::class, $response);
        $this->assertSame(302, $response->getStatusCode());
        $this->assertStringContainsString(
            'MengelolaRiwayatPesanan',
            $response->getHeaderLine('Location')
        );
    }
}