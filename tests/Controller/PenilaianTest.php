<?php

namespace Tests\Controller;

use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\HTTP\RedirectResponse;
use App\Controllers\Penilaian;

class FakePenilaianModel
{
    public array $updates = [];
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

class TestablePenilaian extends Penilaian
{
    public $penilaianModel;
    protected array $dummyDetails = [];
    protected array $fakePost = [];

    public function setDummyDetails(array $details): void
    {
        $this->dummyDetails = $details;
    }

    public function setFakePost(array $post): void
    {
        $this->fakePost = $post;
    }

    private function redirectTo(string $path): RedirectResponse
    {
        $response = new RedirectResponse('');
        $response->setStatusCode(302);
        $response->setHeader('Location', $path);
        return $response;
    }

    private function redirectBack(): RedirectResponse
    {
        $response = new RedirectResponse('');
        $response->setStatusCode(302);
        $response->setHeader('Location', '/back');
        return $response;
    }

    public function simpan($id_detail_pemesanan)
    {
        $idUser = session()->get('id_user');
        if (!$idUser) {
            session()->setFlashdata('error', 'Silakan login terlebih dahulu.');
            return $this->redirectTo('/login');
        }

        $id_detail_pemesanan = (int) $id_detail_pemesanan;

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
            return $this->redirectBack();
        }

        $rating = (int) $ratingRaw;
        $ulasan = $ulasanRaw;
        if (mb_strlen($ulasan) > 1000) {
            $ulasan = mb_substr($ulasan, 0, 1000);
        }

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

        $ok = $this->penilaianModel->update($id_detail_pemesanan, [
            'user_rating' => $rating,
            'user_ulasan' => $ulasan,
            'user_media'  => null,
        ]);

        if (!$ok) {
            session()->setFlashdata('error', 'Gagal menyimpan penilaian. Coba lagi.');
            return $this->redirectBack();
        }

        session()->setFlashdata('success', 'Terima kasih! Penilaian berhasil dikirim.');
        return $this->redirectTo('/penilaian/daftar');
    }
}

class PenilaianTest extends CIUnitTestCase
{
    private FakePenilaianModel $fakeModel;
    private TestablePenilaian $controller;

    protected function setUp(): void
    {
        parent::setUp();
        helper(['session']);

        $_SESSION = [];
        session()->destroy();

        $this->fakeModel  = new FakePenilaianModel();
        $this->controller = new TestablePenilaian();

        $ref  = new \ReflectionClass($this->controller);
        $prop = $ref->getProperty('penilaianModel');
        $prop->setAccessible(true);
        $prop->setValue($this->controller, $this->fakeModel);

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

    public function testSimpanTanpaLoginRedirectKeLogin()
    {
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
        $this->assertCount(0, $this->fakeModel->updates);
    }

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
        $this->assertCount(0, $this->fakeModel->updates);
    }

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

    public function testSimpanDetailPemesananTidakAdaTidakMemanggilUpdate()
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

    public function testSimpanDetailMilikUserLainTidakMemanggilUpdate()
    {
        session()->set(['id_user' => 10]);

        $this->controller->setDummyDetails([
            1 => [
                'id_detail_pemesanan' => 1,
                'id_user'             => 99,
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

    public function testSimpanGagalJikaStatusBelumSelesai()
    {
        session()->set(['id_user' => 10]);

        $this->controller->setDummyDetails([
            1 => [
                'id_detail_pemesanan' => 1,
                'id_user'             => 10,
                'status_pemesanan'    => 'Dikirim',
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

    public function testSimpanGagalJikaSudahPernahDinilai()
    {
        session()->set(['id_user' => 10]);

        $this->controller->setDummyDetails([
            1 => [
                'id_detail_pemesanan' => 1,
                'id_user'             => 10,
                'status_pemesanan'    => 'Selesai',
                'user_rating'         => 4,
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

        $this->assertCount(1, $this->fakeModel->updates);

        $call = $this->fakeModel->updates[0];
        $this->assertSame(1, $call['id']);
        $this->assertSame(5, $call['data']['user_rating']);
        $this->assertSame('Mantap sekali produknya!', $call['data']['user_ulasan']);
        $this->assertNull($call['data']['user_media']);
    }

    public function testUlasanDipangkasMenjadiMaksimalSeribuKarakter()
    {
        session()->set(['id_user' => 10]);

        $longText = str_repeat('x', 1200);

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