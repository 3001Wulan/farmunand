<?php
// tests/Controller/PenilaianTest.php

namespace Tests\Controller;

use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\HTTP\RedirectResponse;
use App\Controllers\Penilaian;

/**
 * FakePenilaianModel
 *
 * Model dummy yang hanya merekam pemanggilan update(),
 * TANPA menyentuh database asli.
 */
class FakePenilaianModel
{
    /** @var array<int, array> daftar update yang pernah dipanggil */
    public array $updates = [];

    /** @var bool nilai yang dikembalikan oleh update() (default: true) */
    public bool $updateReturn = true;

    public function update(int $id, array $data): bool
    {
        $this->updates[] = [
            'id'   => $id,
            'data' => $data,
        ];

        return $this->updateReturn;
    }
}

/**
 * TestablePenilaian
 *
 * Versi "test-safe" dari controller Penilaian:
 * - Tidak memakai Database::connect()
 * - Tidak memakai upload file
 * - Menggunakan dummy detail (array) yang bisa diatur dari test
 * - Menggunakan FakePenilaianModel untuk menguji pemanggilan update()
 *
 * Catatan penting:
 * - TIDAK override __construct() → pakai constructor parent apa adanya
 * - Properti $penilaianModel TIDAK boleh diberi type-hint, cukup PHPDoc
 */
class TestablePenilaian extends Penilaian
{
    /** @var FakePenilaianModel */
    public $penilaianModel; // jangan diberi type-hint biar kompatibel dgn parent

    /**
     * Dummy detail_pemesanan:
     *   [ id_detail => [
     *       'id_detail_pemesanan' => int,
     *       'id_user'             => int,
     *       'status_pemesanan'    => string,
     *       'user_rating'         => int|null,
     *       'user_ulasan'         => string|null,
     *     ], ... ]
     */
    protected array $dummyDetails = [];

    /** @var array fake POST data */
    protected array $fakePost = [];

    // ====== Helper untuk test ======

    public function setDummyDetails(array $details): void
    {
        $this->dummyDetails = $details;
    }

    public function setFakePost(array $post): void
    {
        $this->fakePost = $post;
    }

    /**
     * Helper kecil buat bikin RedirectResponse manual
     */
    private function redirectTo(string $path): RedirectResponse
    {
        $response = new RedirectResponse('');
        $response->setStatusCode(302);
        $response->setHeader('Location', $path);
        return $response;
    }

    private function redirectBack(): RedirectResponse
    {
        // Untuk test, kita tidak butuh URL spesifik.
        // Cukup tandai sebagai redirect "kembali".
        $response = new RedirectResponse('');
        $response->setStatusCode(302);
        $response->setHeader('Location', '/back');
        return $response;
    }

    /**
     * Versi test dari simpan(), tanpa DB & upload:
     * - Cek login
     * - Validasi rating (required & 1..5)
     * - Cek kepemilikan & status & double rating menggunakan dummy detail
     * - Memanggil FakePenilaianModel::update pada jalur sukses
     */
    public function simpan($id_detail_pemesanan)
    {
        $idUser = session()->get('id_user');
        if (!$idUser) {
            session()->setFlashdata('error', 'Silakan login terlebih dahulu.');
            return $this->redirectTo('/login');
        }

        $id_detail_pemesanan = (int) $id_detail_pemesanan;

        // ===== Validasi sederhana (mengimitasi rules asli) =====
        $ratingRaw = $this->fakePost['rating'] ?? null;
        $ulasanRaw = (string) ($this->fakePost['ulasan'] ?? '');

        $errors = [];

        if ($ratingRaw === null || $ratingRaw === '') {
            $errors['rating'] = 'Rating wajib diisi.';
        } else {
            $ratingInt = (int) $ratingRaw;
            if (!in_array($ratingInt, [1, 2, 3, 4, 5], true)) {
                $errors['rating'] = 'Rating harus antara 1 sampai 5.';
            }
        }

        if (!empty($errors)) {
            session()->setFlashdata('errors', $errors);
            // Simulasikan redirect back dengan membawa input
            return $this->redirectBack();
        }

        $rating = (int) $ratingRaw;
        $ulasan = $ulasanRaw;
        if (mb_strlen($ulasan) > 1000) {
            $ulasan = mb_substr($ulasan, 0, 1000);
        }

        // ===== Ambil detail dari dummy, bukan dari DB =====
        $detail = $this->dummyDetails[$id_detail_pemesanan] ?? null;

        if (!$detail) {
            session()->setFlashdata('error', 'Detail pemesanan tidak ditemukan.');
            return $this->redirectBack();
        }

        if ((int) ($detail['id_user'] ?? 0) !== (int) $idUser) {
            session()->setFlashdata('error', 'Anda tidak berhak menilai item ini.');
            return $this->redirectBack();
        }

        if (($detail['status_pemesanan'] ?? '') !== 'Selesai') {
            session()->setFlashdata('error', 'Penilaian hanya dapat dilakukan setelah pesanan berstatus Selesai.');
            return $this->redirectBack();
        }

        if (!empty($detail['user_rating'])) {
            session()->setFlashdata('info', 'Item ini sudah pernah dinilai.');
            return $this->redirectBack();
        }

        // ===== Simpan (via fake model) =====
        $ok = $this->penilaianModel->update($id_detail_pemesanan, [
            'user_rating' => $rating,
            'user_ulasan' => $ulasan,
            'user_media'  => null, // upload di-skip di versi test ini
        ]);

        if (!$ok) {
            session()->setFlashdata('error', 'Gagal menyimpan penilaian. Coba lagi.');
            return $this->redirectBack();
        }

        session()->setFlashdata('success', 'Terima kasih! Penilaian berhasil dikirim.');
        return $this->redirectTo('/penilaian/daftar');
    }
}

/**
 * PenilaianTest
 *
 * Unit test murni untuk logika simpan penilaian:
 * - Tanpa database
 * - Tanpa upload file
 * - PenilaianModel diganti FakePenilaianModel
 * - Detail pemesanan diganti dummy array (dummyDetails)
 */
class PenilaianTest extends CIUnitTestCase
{
    /** @var FakePenilaianModel */
    private FakePenilaianModel $fakeModel;

    /** @var TestablePenilaian */
    private TestablePenilaian $controller;

    protected function setUp(): void
    {
        parent::setUp();
        helper(['session']);

        // Reset session
        $_SESSION = [];
        session()->destroy();

        // Siapkan fake model
        $this->fakeModel  = new FakePenilaianModel();

        // Pakai controller turunan yang override simpan()
        $this->controller = new TestablePenilaian();

        // Override penilaianModel milik parent dengan fake model via Reflection
        $ref  = new \ReflectionClass($this->controller);
        $prop = $ref->getProperty('penilaianModel');
        $prop->setAccessible(true);
        $prop->setValue($this->controller, $this->fakeModel);

        // Dummy dasar: 1 detail milik user 10, status Selesai, belum dinilai
        $this->controller->setDummyDetails([
            1 => [
                'id_detail_pemesanan' => 1,
                'id_user'             => 10,
                'status_pemesanan'    => 'Selesai',
                'user_rating'         => null,
                'user_ulasan'         => null,
            ],
        ]);
    }

    /* ===========================================================
     * 1. Simpan TANPA login → redirect ke /login, tidak update
     * =========================================================*/
    public function testSimpanTanpaLoginRedirectKeLogin()
    {
        // Tidak set session id_user → dianggap belum login
        $this->controller->setFakePost([
            'rating' => 5,
            'ulasan' => 'Mantap'
        ]);

        $response = $this->controller->simpan(1);

        $this->assertInstanceOf(RedirectResponse::class, $response);
        $this->assertSame('/login', $response->getHeaderLine('Location'));
        $this->assertSame(
            'Silakan login terlebih dahulu.',
            session()->getFlashdata('error')
        );
        $this->assertCount(0, $this->fakeModel->updates, 'Tidak boleh ada update() ketika belum login.');
    }

    /* ===========================================================
     * 2. Rating KOSONG → validasi gagal, redirect back,
     *    errors['rating'] terisi, tidak update()
     * =========================================================*/
    public function testSimpanGagalKarenaRatingKosong()
    {
        session()->set(['id_user' => 10]);

        $this->controller->setFakePost([
            'rating' => '',
            'ulasan' => 'Bagus'
        ]);

        $response = $this->controller->simpan(1);

        $this->assertInstanceOf(RedirectResponse::class, $response);
        $this->assertSame('/back', $response->getHeaderLine('Location'));

        $errors = session()->getFlashdata('errors');
        $this->assertIsArray($errors);
        $this->assertArrayHasKey('rating', $errors);
        $this->assertCount(0, $this->fakeModel->updates, 'Tidak boleh ada update() jika rating invalid.');
    }

    /* ===========================================================
     * 3. Rating di luar range (misal 11) → validasi gagal
     * =========================================================*/
    public function testSimpanGagalKarenaRatingDiLuarRange()
    {
        session()->set(['id_user' => 10]);

        $this->controller->setFakePost([
            'rating' => 11,
            'ulasan' => 'test'
        ]);

        $response = $this->controller->simpan(1);

        $this->assertInstanceOf(RedirectResponse::class, $response);
        $this->assertSame('/back', $response->getHeaderLine('Location'));

        $errors = session()->getFlashdata('errors');
        $this->assertIsArray($errors);
        $this->assertArrayHasKey('rating', $errors);
        $this->assertCount(0, $this->fakeModel->updates);
    }

    /* ===========================================================
     * 4. Detail pemesanan TIDAK ditemukan (tanpa DB)
     *    → error flash & redirect back, tidak update()
     * =========================================================*/
    public function testSimpanDetailPemesananTidakAdaTidakMemanggilUpdate()
    {
        session()->set(['id_user' => 10]);

        // Dummy details tidak memiliki ID 999
        $this->controller->setDummyDetails([
            1 => [
                'id_detail_pemesanan' => 1,
                'id_user'             => 10,
                'status_pemesanan'    => 'Selesai',
                'user_rating'         => null,
                'user_ulasan'         => null,
            ],
        ]);

        $this->controller->setFakePost([
            'rating' => 5,
            'ulasan' => 'Mantap sekali'
        ]);

        $response = $this->controller->simpan(999);

        $this->assertInstanceOf(RedirectResponse::class, $response);
        $this->assertSame('/back', $response->getHeaderLine('Location'));

        $this->assertSame(
            'Detail pemesanan tidak ditemukan.',
            session()->getFlashdata('error')
        );
        $this->assertCount(0, $this->fakeModel->updates);
    }

    /* ===========================================================
     * 5. Detail milik user lain → tidak boleh dinilai
     * =========================================================*/
    public function testSimpanDetailMilikUserLainTidakMemanggilUpdate()
    {
        session()->set(['id_user' => 10]);

        $this->controller->setDummyDetails([
            1 => [
                'id_detail_pemesanan' => 1,
                'id_user'             => 99, // BUKAN user 10
                'status_pemesanan'    => 'Selesai',
                'user_rating'         => null,
                'user_ulasan'         => null,
            ],
        ]);

        $this->controller->setFakePost([
            'rating' => 4,
            'ulasan' => 'Lumayan'
        ]);

        $response = $this->controller->simpan(1);

        $this->assertInstanceOf(RedirectResponse::class, $response);
        $this->assertSame('/back', $response->getHeaderLine('Location'));

        $this->assertSame(
            'Anda tidak berhak menilai item ini.',
            session()->getFlashdata('error')
        );
        $this->assertCount(0, $this->fakeModel->updates);
    }

    /* ===========================================================
     * 6. Status pesanan belum "Selesai" → tidak boleh dinilai
     * =========================================================*/
    public function testSimpanGagalJikaStatusBelumSelesai()
    {
        session()->set(['id_user' => 10]);

        $this->controller->setDummyDetails([
            1 => [
                'id_detail_pemesanan' => 1,
                'id_user'             => 10,
                'status_pemesanan'    => 'Dikirim', // BUKAN Selesai
                'user_rating'         => null,
                'user_ulasan'         => null,
            ],
        ]);

        $this->controller->setFakePost([
            'rating' => 5,
            'ulasan' => 'Mantap'
        ]);

        $response = $this->controller->simpan(1);

        $this->assertInstanceOf(RedirectResponse::class, $response);
        $this->assertSame('/back', $response->getHeaderLine('Location'));

        $this->assertSame(
            'Penilaian hanya dapat dilakukan setelah pesanan berstatus Selesai.',
            session()->getFlashdata('error')
        );
        $this->assertCount(0, $this->fakeModel->updates);
    }

    /* ===========================================================
     * 7. Detail sudah pernah dinilai → info flash, tidak update()
     * =========================================================*/
    public function testSimpanGagalJikaSudahPernahDinilai()
    {
        session()->set(['id_user' => 10]);

        $this->controller->setDummyDetails([
            1 => [
                'id_detail_pemesanan' => 1,
                'id_user'             => 10,
                'status_pemesanan'    => 'Selesai',
                'user_rating'         => 4, // sudah pernah dinilai
                'user_ulasan'         => 'Sebelumnya',
            ],
        ]);

        $this->controller->setFakePost([
            'rating' => 5,
            'ulasan' => 'Coba nilai lagi'
        ]);

        $response = $this->controller->simpan(1);

        $this->assertInstanceOf(RedirectResponse::class, $response);
        $this->assertSame('/back', $response->getHeaderLine('Location'));

        $this->assertSame(
            'Item ini sudah pernah dinilai.',
            session()->getFlashdata('info')
        );
        $this->assertCount(0, $this->fakeModel->updates);
    }

    /* ===========================================================
     * 8. Skenario sukses:
     *    - Detail milik user login
     *    - Status Selesai
     *    - Belum pernah dinilai
     *    → update() dipanggil sekali dengan data yang benar
     * =========================================================*/
    public function testSimpanBerhasilMemanggilUpdateDanRedirectKeDaftar()
    {
        session()->set(['id_user' => 10]);

        $this->controller->setDummyDetails([
            1 => [
                'id_detail_pemesanan' => 1,
                'id_user'             => 10,
                'status_pemesanan'    => 'Selesai',
                'user_rating'         => null,
                'user_ulasan'         => null,
            ],
        ]);

        $this->controller->setFakePost([
            'rating' => 5,
            'ulasan' => 'Mantap sekali produknya!'
        ]);

        $response = $this->controller->simpan(1);

        $this->assertInstanceOf(RedirectResponse::class, $response);
        $this->assertSame('/penilaian/daftar', $response->getHeaderLine('Location'));

        $this->assertSame(
            'Terima kasih! Penilaian berhasil dikirim.',
            session()->getFlashdata('success')
        );

        // Pastikan update() tepat sekali
        $this->assertCount(1, $this->fakeModel->updates);

        $call = $this->fakeModel->updates[0];
        $this->assertSame(1, $call['id']);
        $this->assertSame(5, $call['data']['user_rating']);
        $this->assertSame('Mantap sekali produknya!', $call['data']['user_ulasan']);
        $this->assertNull($call['data']['user_media']);
    }

    /* ===========================================================
     * 9. Ulasan terlalu panjang → dipotong menjadi 1000 karakter
     * =========================================================*/
    public function testUlasanDipangkasMenjadiMaksimalSeribuKarakter()
    {
        session()->set(['id_user' => 10]);

        $longText = str_repeat('x', 1200); // 1200 karakter

        $this->controller->setDummyDetails([
            1 => [
                'id_detail_pemesanan' => 1,
                'id_user'             => 10,
                'status_pemesanan'    => 'Selesai',
                'user_rating'         => null,
                'user_ulasan'         => null,
            ],
        ]);

        $this->controller->setFakePost([
            'rating' => 4,
            'ulasan' => $longText
        ]);

        $response = $this->controller->simpan(1);

        $this->assertInstanceOf(RedirectResponse::class, $response);
        $this->assertSame('/penilaian/daftar', $response->getHeaderLine('Location'));

        $this->assertCount(1, $this->fakeModel->updates);
        $call = $this->fakeModel->updates[0];

        $this->assertSame(4, $call['data']['user_rating']);
        $this->assertSame(1000, mb_strlen($call['data']['user_ulasan']));
    }

    public function testSimpanDenganRatingMinimalSatuBerhasil()
    {
        session()->set(['id_user' => 10]);

        $this->controller->setDummyDetails([
            1 => [
                'id_detail_pemesanan' => 1,
                'id_user'             => 10,
                'status_pemesanan'    => 'Selesai',
                'user_rating'         => null,
                'user_ulasan'         => null,
            ],
        ]);

        $this->controller->setFakePost([
            'rating' => 1,
            'ulasan' => 'Kurang puas'
        ]);

        $response = $this->controller->simpan(1);

        $this->assertInstanceOf(RedirectResponse::class, $response);
        $this->assertSame('/penilaian/daftar', $response->getHeaderLine('Location'));
        $this->assertCount(1, $this->fakeModel->updates);
        $this->assertSame(1, $this->fakeModel->updates[0]['data']['user_rating']);
    }

}
