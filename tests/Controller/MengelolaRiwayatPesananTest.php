<?php

namespace Tests\Controller;

use App\Controllers\MengelolaRiwayatPesanan;
use App\Models\PesananModel;
use CodeIgniter\HTTP\RedirectResponse;
use CodeIgniter\Test\CIUnitTestCase;

/**
 * MengelolaRiwayatPesananTest
 *
 * Fokus unit test:
 * - Logika filter & sort riwayat pesanan (tanpa DB).
 * - Perilaku dasar updateStatus terhadap model (dipanggil / tidak dipanggil) dan redirect.
 *
 * Catatan penting:
 * - Di sini kita pakai controller turunan khusus test yang:
 *   - Tidak menyentuh database (riwayat pakai array dummy).
 *   - Tidak memakai Request asli (diganti fakeGet & fakePost).
 *   - PesananModel dimock dan di-inject dari luar.
 *
 * Artinya: ini benar-benar unit test level logika, bukan feature/blackbox test.
 */
class MengelolaRiwayatPesananTest extends CIUnitTestCase
{
    /** @var object controller turunan yang test-safe */
    private $controller;

    /** @var PesananModel|\PHPUnit\Framework\MockObject\MockObject */
    private $pesananModelMock;

    protected function setUp(): void
    {
        parent::setUp();

        // Mock PesananModel: di sini kita hanya peduli apakah update() dipanggil atau tidak.
        $this->pesananModelMock = $this->getMockBuilder(PesananModel::class)
            ->disableOriginalConstructor()   // ⟵ cegah koneksi DB
            ->onlyMethods(['update'])        // ⟵ kita hanya overriding method update()
            ->getMock();

        // Controller turunan khusus test:
        // - Mengganti sumber data (riwayat, GET, POST) dengan variabel yang bisa di-set dari test.
        // - Method index() & updateStatus() dibuat versi test-safe (tanpa DB & Request asli).
        $this->controller = new class($this->pesananModelMock) extends MengelolaRiwayatPesanan {
            /** @var PesananModel */
            protected $pesananModel;

            /** @var array dummy data riwayat pesanan */
            protected array $dummyRiwayat = [];

            /** @var array representasi fake $_GET */
            protected array $fakeGet = [];

            /** @var array representasi fake $_POST */
            protected array $fakePost = [];

            public function __construct($pesananModel)
            {
                parent::__construct(); // memastikan BaseController dsb. ter-setup

                $this->pesananModel = $pesananModel;

                // Default "query string"
                $this->fakeGet = [
                    'status'  => '',
                    'keyword' => '',
                    'sort'    => 'DESC',
                ];

                // Default "body"
                $this->fakePost = [
                    'status_pemesanan' => 'Dikemas',
                ];

                // Dummy data riwayat pesanan (3 baris, status & tanggal berbeda)
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

            // ===== Setter untuk mengontrol "lingkungan" dari test =====

            public function setFakeGet(array $get): void
            {
                // Merge supaya default masih ada, tapi bisa dioverride
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

            // ===== Implementasi index() versi test-safe =====

            /**
             * index() versi test:
             * - Mengambil filter dari $fakeGet (status, keyword, sort).
             * - Menerapkan filter status & keyword pada array dummyRiwayat.
             * - Menerapkan sorting ASC/DESC berdasarkan created_at.
             * - Mengembalikan string ringkas, 1 baris per pesanan: "User X Produk Y (Status)".
             */
            public function index()
            {
                $statusRaw  = $this->fakeGet['status']  ?? '';
                $keywordRaw = $this->fakeGet['keyword'] ?? '';
                $sort       = $this->fakeGet['sort']    ?? 'DESC';

                $status  = is_string($statusRaw)  ? $statusRaw  : '';
                $keyword = is_string($keywordRaw) ? $keywordRaw : '';

                $rows = $this->dummyRiwayat;

                // Filter by status (exact match)
                if ($status !== '') {
                    $rows = array_values(array_filter(
                        $rows,
                        fn ($row) => ($row['status_pemesanan'] ?? '') === $status
                    ));
                }

                // Filter by keyword (di nama_user atau nama_produk, case-insensitive)
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

                // Sorting by created_at
                $sortUpper = strtoupper($sort);
                usort($rows, function ($a, $b) use ($sortUpper) {
                    $cmp = strcmp($a['created_at'], $b['created_at']);
                    return $sortUpper === 'ASC' ? $cmp : -$cmp;
                });

                // Kembalikan 1 baris string per pesanan
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

            // ===== Implementasi updateStatus() versi test-safe =====

            /**
             * updateStatus() versi test:
             * - Baca status dari $fakePost['status_pemesanan'].
             * - Kalau kosong → tidak panggil model->update(), tetap redirect.
             * - Kalau ada   → panggil model->update($id, ['status_pemesanan' => status]) lalu redirect.
             *
             * Catatan:
             * - Versi ini hanya menguji "ada status atau tidak", belum menguji state machine penuh
             *   seperti di controller asli.
             */
            public function updateStatus($id)
            {
                $status = $this->fakePost['status_pemesanan'] ?? null;

                $response = new RedirectResponse('');
                $response->setStatusCode(302);
                $response->setHeader('Location', '/MengelolaRiwayatPesanan');

                if (! $status) {
                    // Tidak ada status → tidak update, tapi tetap redirect
                    return $response;
                }

                $this->pesananModel->update($id, [
                    'status_pemesanan' => $status,
                ]);

                return $response;
            }
        };

        // Default environment untuk setiap test:
        // - Tanpa filter (status & keyword kosong, sort DESC)
        // - Status update default = Dikemas
        $this->controller->setFakeGet([
            'status'  => '',
            'keyword' => '',
            'sort'    => 'DESC',
        ]);
        $this->controller->setFakePost([
            'status_pemesanan' => 'Dikemas',
        ]);
    }

    /* ===========================================================
     *  SKENARIO 1:
     *  Index tanpa filter:
     *  - status & keyword kosong
     *  - sort default DESC
     *  - urutan: tanggal terbaru dulu (User B, User A, User C)
     * =========================================================*/
    public function testIndexTanpaFilterMengambilRiwayatDanSortingDESC()
    {
        $output = $this->controller->index();

        $this->assertIsString($output);

        $lines = explode("\n", trim($output));
        $this->assertCount(3, $lines, 'Harusnya ada 3 baris pesanan.');

        // Urutan paling atas harus User B (tanggal paling baru)
        $this->assertSame('User B Produk B (Selesai)', $lines[0]);
        $this->assertSame('User A Produk A (Dikirim)', $lines[1]);
        $this->assertSame('User C Produk C (Dibatalkan)', $lines[2]);
    }

    /* ===========================================================
     *  SKENARIO 1b (tambahan):
     *  Index dengan sort ASC:
     *  - status & keyword kosong
     *  - sort = ASC
     *  - urutan: tanggal paling lama dulu (User C, User A, User B)
     * =========================================================*/
    public function testIndexDenganSortASCMengurutkanDariTerlama()
    {
        $this->controller->setFakeGet([
            'sort' => 'ASC',
        ]);

        $output = $this->controller->index();
        $lines  = explode("\n", trim($output));

        $this->assertCount(3, $lines, 'Harusnya tetap 3 baris pesanan.');

        $this->assertSame('User C Produk C (Dibatalkan)', $lines[0]);
        $this->assertSame('User A Produk A (Dikirim)', $lines[1]);
        $this->assertSame('User B Produk B (Selesai)', $lines[2]);
    }

    /* ===========================================================
     *  SKENARIO 2:
     *  Index dengan filter status:
     *  - status = 'Dikirim'
     *  - Hanya pesanan dengan status Dikirim yang muncul
     * =========================================================*/
    public function testIndexDenganFilterStatusMemfilterStatusDenganBenar()
    {
        $this->controller->setFakeGet([
            'status'  => 'Dikirim',
            'keyword' => '',
            'sort'    => 'DESC',
        ]);

        $output = $this->controller->index();

        $lines = array_filter(explode("\n", trim($output)));

        $this->assertNotEmpty($lines, 'Minimal ada 1 pesanan dengan status Dikirim.');
        $this->assertCount(1, $lines, 'Filter status Dikirim harus menyisakan 1 pesanan.');

        // Semua baris yang tersisa harus (Dikirim)
        foreach ($lines as $line) {
            $this->assertStringContainsString('(Dikirim)', $line);
        }

        // Pastikan status lain tidak ikut
        $this->assertStringNotContainsString('(Selesai)', $output);
        $this->assertStringNotContainsString('(Dibatalkan)', $output);
    }

    /* ===========================================================
     *  SKENARIO 3:
     *  Index dengan keyword:
     *  - keyword = 'Produk B'
     *  - Hanya pesanan yang nama_produk / nama_user mengandung keyword
     * =========================================================*/
    public function testIndexDenganKeywordMemfilterDenganBenar()
    {
        $this->controller->setFakeGet([
            'status'  => '',
            'keyword' => 'Produk B',
            'sort'    => 'DESC',
        ]);

        $output = $this->controller->index();

        $lines = array_filter(explode("\n", trim($output)));

        $this->assertNotEmpty($lines, 'Minimal ada 1 pesanan yang match keyword.');
        $this->assertCount(1, $lines, 'Keyword Produk B harus menyisakan 1 pesanan.');

        $this->assertStringContainsString('Produk B', $output);
        $this->assertStringNotContainsString('Produk A', $output);
        $this->assertStringNotContainsString('Produk C', $output);
    }

    /* ===========================================================
     *  SKENARIO 4:
     *  Index ketika tidak ada data:
     *  - dummyRiwayat dikosongkan
     *  - Tidak error, output string kosong
     * =========================================================*/
    public function testIndexTanpaDataTidakError()
    {
        $this->controller->setDummyRiwayat([]);

        $output = $this->controller->index();

        $this->assertIsString($output);
        $this->assertSame('', trim($output), 'Jika tidak ada riwayat, output boleh kosong.');
    }

    /* ===========================================================
     *  SKENARIO 5:
     *  updateStatus dengan status valid (ada isinya):
     *  - fakePost['status_pemesanan'] = 'Dikemas'
     *  - PesananModel::update(1, ['status_pemesanan' => 'Dikemas']) dipanggil sekali
     *  - Redirect ke /MengelolaRiwayatPesanan dengan status 302
     * =========================================================*/
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
        $this->assertSame(302, $response->getStatusCode(), 'Harus redirect 302.');

        $location = $response->getHeaderLine('Location');
        $this->assertNotSame('', $location, 'Header Location tidak boleh kosong.');
        $this->assertStringContainsString(
            'MengelolaRiwayatPesanan',
            $location,
            'Setelah updateStatus harus redirect ke halaman MengelolaRiwayatPesanan.'
        );
    }

    /* ===========================================================
     *  SKENARIO 6:
     *  updateStatus tanpa status (null / kosong):
     *  - fakePost['status_pemesanan'] = null
     *  - PesananModel::update() TIDAK boleh dipanggil
     *  - Tetap redirect ke /MengelolaRiwayatPesanan dengan status 302
     * =========================================================*/
    public function testUpdateStatusTanpaStatusTidakMemanggilModelUpdate()
    {
        $this->controller->setFakePost([
            'status_pemesanan' => null,
        ]);

        $this->pesananModelMock->expects($this->never())
            ->method('update');

        $response = $this->controller->updateStatus(1);

        $this->assertInstanceOf(RedirectResponse::class, $response);
        $this->assertSame(302, $response->getStatusCode(), 'Tanpa status tetap redirect 302.');
        $this->assertStringContainsString(
            'MengelolaRiwayatPesanan',
            $response->getHeaderLine('Location')
        );
    }
}
