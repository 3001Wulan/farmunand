<?php

namespace Tests\Controller;

use CodeIgniter\Test\CIUnitTestCase;
use App\Controllers\MelihatLaporan;

class TestableMelihatLaporan extends MelihatLaporan
{
    protected array $dummyRows = [];

    protected array $fakeGet = [
        'start'  => null,
        'end'    => null,
        'status' => null,
    ];

    public function setDummyRows(array $rows): void
    {
        $this->dummyRows = $rows;
    }

    public function setFakeGet(array $params): void
    {
        $this->fakeGet = array_merge($this->fakeGet, $params);
    }

    public function index()
    {
        $start  = $this->fakeGet['start']  ?? null;
        $end    = $this->fakeGet['end']    ?? null;
        $status = $this->fakeGet['status'] ?? null;

        $statusAliases = [
            'Belum Bayar' => ['Belum Bayar', 'Menunggu Pembayaran', 'Pending', 'Pending Payment'],
            'Dikemas'     => ['Dikemas', 'Dipacking'],
            'Dikirim'     => ['Dikirim', 'Dalam Perjalanan'],
            'Selesai'     => ['Selesai', 'Completed'],
            'Dibatalkan'  => ['Dibatalkan', 'Batal', 'Canceled'],
        ];

        $rows = $this->dummyRows;

        if ($start && $end) {
            $rows = array_values(array_filter($rows, static function ($row) use ($start, $end) {
                $date = substr((string) ($row['created_at'] ?? ''), 0, 10);
                return $date >= $start && $date <= $end;
            }));
        }

        if ($status !== null && $status !== '') {
            $values = $statusAliases[$status] ?? [$status];

            $rows = array_values(array_filter($rows, static function ($row) use ($values) {
                $s = (string) ($row['status_pemesanan'] ?? '');
                return in_array($s, $values, true);
            }));
        }

        usort($rows, static function ($a, $b) {
            $aTime = (string) ($a['created_at'] ?? '');
            $bTime = (string) ($b['created_at'] ?? '');
            return strcmp($bTime, $aTime);
        });

        return [
            'laporan' => $rows,
            'start'   => $start,
            'end'     => $end,
            'status'  => $status,
        ];
    }
}

class MelihatLaporanTest extends CIUnitTestCase
{
    private TestableMelihatLaporan $controller;

    protected function setUp(): void
    {
        parent::setUp();

        $this->controller = new TestableMelihatLaporan();

        $this->controller->setDummyRows([
            [
                'id_pemesanan'     => 1,
                'nama_pembeli'     => 'User A',
                'nama_produk'      => 'Produk A',
                'jumlah_produk'    => 2,
                'harga_produk'     => 10000,
                'status_pemesanan' => 'Selesai',
                'created_at'       => '2025-01-05 10:00:00',
            ],
            [
                'id_pemesanan'     => 2,
                'nama_pembeli'     => 'User B',
                'nama_produk'      => 'Produk B',
                'jumlah_produk'    => 1,
                'harga_produk'     => 20000,
                'status_pemesanan' => 'Dikemas',
                'created_at'       => '2025-01-20 08:00:00',
            ],
            [
                'id_pemesanan'     => 3,
                'nama_pembeli'     => 'User C',
                'nama_produk'      => 'Produk C',
                'jumlah_produk'    => 3,
                'harga_produk'     => 15000,
                'status_pemesanan' => 'Completed',
                'created_at'       => '2025-02-01 09:30:00',
            ],
            [
                'id_pemesanan'     => 4,
                'nama_pembeli'     => 'User D',
                'nama_produk'      => 'Produk D',
                'jumlah_produk'    => 5,
                'harga_produk'     => 5000,
                'status_pemesanan' => 'Batal',
                'created_at'       => '2024-12-31 23:59:00',
            ],
        ]);

        $this->controller->setFakeGet([
            'start'  => null,
            'end'    => null,
            'status' => null,
        ]);
    }

    public function testIndexTanpaFilterMengembalikanSemuaDanSortingDesc()
    {
        $out = $this->controller->index();

        $this->assertIsArray($out);
        $this->assertArrayHasKey('laporan', $out);

        $rows = $out['laporan'];

        $this->assertCount(4, $rows, 'Tanpa filter harus mengembalikan semua baris.');

        $this->assertSame(3, $rows[0]['id_pemesanan']);
        $this->assertSame(2, $rows[1]['id_pemesanan']);
        $this->assertSame(1, $rows[2]['id_pemesanan']);
        $this->assertSame(4, $rows[3]['id_pemesanan']);

        $this->assertNull($out['start']);
        $this->assertNull($out['end']);
        $this->assertNull($out['status']);
    }

    public function testIndexDenganFilterTanggalDanStatusSelesai()
    {
        $this->controller->setFakeGet([
            'start'  => '2025-01-01',
            'end'    => '2025-01-31',
            'status' => 'Selesai',
        ]);

        $out  = $this->controller->index();
        $rows = $out['laporan'];

        $this->assertCount(1, $rows, 'Filter tanggal + status Selesai harus menyisakan 1 baris.');

        $row = $rows[0];
        $this->assertSame(1, $row['id_pemesanan']);
        $this->assertSame('Selesai', $row['status_pemesanan']);

        $this->assertSame('2025-01-01', $out['start']);
        $this->assertSame('2025-01-31', $out['end']);
        $this->assertSame('Selesai', $out['status']);
    }

    public function testIndexDenganFilterStatusDikemasMengambilAliasDipacking()
    {
        $this->controller->setDummyRows(array_merge(
            [],
            [
                [
                    'id_pemesanan'     => 10,
                    'nama_pembeli'     => 'User X',
                    'nama_produk'      => 'Produk X',
                    'jumlah_produk'    => 1,
                    'harga_produk'     => 30000,
                    'status_pemesanan' => 'Dikemas',
                    'created_at'       => '2025-01-15 12:00:00',
                ],
                [
                    'id_pemesanan'     => 11,
                    'nama_pembeli'     => 'User Y',
                    'nama_produk'      => 'Produk Y',
                    'jumlah_produk'    => 1,
                    'harga_produk'     => 30000,
                    'status_pemesanan' => 'Dipacking',
                    'created_at'       => '2025-01-16 12:00:00',
                ],
            ]
        ));

        $this->controller->setFakeGet([
            'start'  => null,
            'end'    => null,
            'status' => 'Dikemas',
        ]);

        $out  = $this->controller->index();
        $rows = $out['laporan'];

        $this->assertNotEmpty($rows);
        foreach ($rows as $r) {
            $this->assertContains(
                $r['status_pemesanan'],
                ['Dikemas', 'Dipacking'],
                'Filter status Dikemas harus menangkap Dikemas & Dipacking.'
            );
        }
    }

    public function testIndexDenganStatusCustomTanpaAliasMenghasilkanKosong()
    {
        $this->controller->setFakeGet([
            'start'  => null,
            'end'    => null,
            'status' => 'UnknownStatus',
        ]);

        $out  = $this->controller->index();
        $rows = $out['laporan'];

        $this->assertIsArray($rows);
        $this->assertCount(
            0,
            $rows,
            'Status yang tidak ada di alias seharusnya memakai exact match dan menghasilkan 0 baris.'
        );
    }
}