<?php

namespace Tests\Controller;

use CodeIgniter\Test\CIUnitTestCase;
use App\Controllers\MelihatLaporan;

/**
 * TestableMelihatLaporan
 *
 * Versi test-safe dari controller MelihatLaporan:
 * - TANPA Database::connect()
 * - TANPA Query Builder
 * - Tidak memanggil view()
 * - Hanya menguji logika filter tanggal & status + sorting
 */
class TestableMelihatLaporan extends MelihatLaporan
{
    /**
     * Dummy data laporan:
     * array<array{
     *   id_pemesanan:int,
     *   nama_pembeli:string,
     *   nama_produk:string,
     *   jumlah_produk:int,
     *   harga_produk:float|int,
     *   status_pemesanan:string,
     *   created_at:string (Y-m-d H:i:s)
     * }>
     */
    protected array $dummyRows = [];

    /** @var array fake query string (start, end, status) */
    protected array $fakeGet = [
        'start'  => null,
        'end'    => null,
        'status' => null,
    ];

    // ===== Setter untuk test =====

    public function setDummyRows(array $rows): void
    {
        $this->dummyRows = $rows;
    }

    public function setFakeGet(array $params): void
    {
        $this->fakeGet = array_merge($this->fakeGet, $params);
    }

    /**
     * index() versi unit-test:
     * - Memakai $fakeGet, bukan Request asli
     * - Memakai $dummyRows, bukan DB
     * - Menerapkan:
     *   - Filter tanggal start–end (pakai DATE(created_at))
     *   - Filter status dengan alias (Belum Bayar, Dikemas, dst)
     *   - Sorting created_at DESC
     * - RETURN: array, BUKAN view()
     */
    public function index()
    {
        $start  = $this->fakeGet['start']  ?? null;
        $end    = $this->fakeGet['end']    ?? null;
        $status = $this->fakeGet['status'] ?? null;

        // Padanan status – sama persis dengan controller asli
        $statusAliases = [
            'Belum Bayar' => ['Belum Bayar', 'Menunggu Pembayaran', 'Pending', 'Pending Payment'],
            'Dikemas'     => ['Dikemas', 'Dipacking'],
            'Dikirim'     => ['Dikirim', 'Dalam Perjalanan'],
            'Selesai'     => ['Selesai', 'Completed'],
            'Dibatalkan'  => ['Dibatalkan', 'Batal', 'Canceled'],
        ];

        $rows = $this->dummyRows;

        // ===== Filter tanggal (DATE(p.created_at) antara start..end) =====
        if ($start && $end) {
            $rows = array_values(array_filter($rows, static function ($row) use ($start, $end) {
                $date = substr((string) ($row['created_at'] ?? ''), 0, 10); // ambil YYYY-MM-DD
                return $date >= $start && $date <= $end;
            }));
        }

        // ===== Filter status dengan alias =====
        if ($status !== null && $status !== '') {
            $values = $statusAliases[$status] ?? [$status];

            $rows = array_values(array_filter($rows, static function ($row) use ($values) {
                $s = (string) ($row['status_pemesanan'] ?? '');
                return in_array($s, $values, true);
            }));
        }

        // ===== Sorting created_at DESC =====
        usort($rows, static function ($a, $b) {
            $aTime = (string) ($a['created_at'] ?? '');
            $bTime = (string) ($b['created_at'] ?? '');
            return strcmp($bTime, $aTime); // DESC
        });

        // Alih-alih view(), return data mentah utk diuji
        return [
            'laporan' => $rows,
            'start'   => $start,
            'end'     => $end,
            'status'  => $status,
        ];
    }
}

/**
 * MelihatLaporanTest
 *
 * Fokus:
 * - Logika filter tanggal & status (dengan alias)
 * - Sorting berdasarkan created_at DESC
 * - Tanpa DB, tanpa view, tanpa FeatureTestTrait
 */
class MelihatLaporanTest extends CIUnitTestCase
{
    /** @var TestableMelihatLaporan */
    private TestableMelihatLaporan $controller;

    protected function setUp(): void
    {
        parent::setUp();

        $this->controller = new TestableMelihatLaporan();

        // Dummy data meniru hasil join pemesanan + detail
        // Campuran status & tanggal supaya filter bisa diuji
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
                'status_pemesanan' => 'Completed', // alias dari "Selesai"
                'created_at'       => '2025-02-01 09:30:00',
            ],
            [
                'id_pemesanan'     => 4,
                'nama_pembeli'     => 'User D',
                'nama_produk'      => 'Produk D',
                'jumlah_produk'    => 5,
                'harga_produk'     => 5000,
                'status_pemesanan' => 'Batal', // alias dari "Dibatalkan"
                'created_at'       => '2024-12-31 23:59:00',
            ],
        ]);

        // Default: tanpa filter
        $this->controller->setFakeGet([
            'start'  => null,
            'end'    => null,
            'status' => null,
        ]);
    }

    /* ===========================================================
     * 1. Tanpa filter → semua data dikembalikan & sorting DESC
     * =========================================================*/
    public function testIndexTanpaFilterMengembalikanSemuaDanSortingDesc()
    {
        $out = $this->controller->index();

        $this->assertIsArray($out);
        $this->assertArrayHasKey('laporan', $out);

        $rows = $out['laporan'];

        // Ada 4 baris (semua dummy)
        $this->assertCount(4, $rows, 'Tanpa filter harus mengembalikan semua baris.');

        // Urutan harus berdasarkan created_at DESC
        $this->assertSame(3, $rows[0]['id_pemesanan']); // 2025-02-01
        $this->assertSame(2, $rows[1]['id_pemesanan']); // 2025-01-20
        $this->assertSame(1, $rows[2]['id_pemesanan']); // 2025-01-05
        $this->assertSame(4, $rows[3]['id_pemesanan']); // 2024-12-31

        // Pastikan field start/end/status null (tidak di-set)
        $this->assertNull($out['start']);
        $this->assertNull($out['end']);
        $this->assertNull($out['status']);
    }

    /* ===========================================================
     * 2. Filter tanggal + status "Selesai"
     *    - Hanya status yang alias ke Selesai
     *    - Tanggal antara 2025-01-01 s/d 2025-01-31
     * =========================================================*/
    public function testIndexDenganFilterTanggalDanStatusSelesai()
    {
        $this->controller->setFakeGet([
            'start'  => '2025-01-01',
            'end'    => '2025-01-31',
            'status' => 'Selesai',
        ]);

        $out  = $this->controller->index();
        $rows = $out['laporan'];

        // Dari dummy:
        // - id 1: Selesai, 2025-01-05 → MASUK
        // - id 3: Completed (alias Selesai), 2025-02-01 → KELUAR (di luar range tanggal)
        $this->assertCount(1, $rows, 'Filter tanggal + status Selesai harus menyisakan 1 baris.');

        $row = $rows[0];
        $this->assertSame(1, $row['id_pemesanan']);
        $this->assertSame('Selesai', $row['status_pemesanan']);

        // Pastikan parameter yang dikembalikan konsisten
        $this->assertSame('2025-01-01', $out['start']);
        $this->assertSame('2025-01-31', $out['end']);
        $this->assertSame('Selesai', $out['status']);
    }

    /* ===========================================================
     * 3. Filter status "Dikemas" → harus kena alias "Dipacking" juga
     * =========================================================*/
    public function testIndexDenganFilterStatusDikemasMengambilAliasDipacking()
    {
        // Tambah 1 data dengan status "Dipacking"
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
                    'status_pemesanan' => 'Dipacking', // alias
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

        // Hanya baris dengan status "Dikemas" atau "Dipacking" yang boleh masuk
        $this->assertNotEmpty($rows);
        foreach ($rows as $r) {
            $this->assertContains(
                $r['status_pemesanan'],
                ['Dikemas', 'Dipacking'],
                'Filter status Dikemas harus menangkap Dikemas & Dipacking.'
            );
        }
    }

    /* ===========================================================
     * 4. Filter status yang tidak ada di alias → match exact
     *    (misal "UnknownStatus" → hampir pasti 0 baris)
     * =========================================================*/
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
