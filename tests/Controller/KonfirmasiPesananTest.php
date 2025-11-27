<?php

namespace Tests\Unit;

use App\Controllers\KonfirmasiPesanan;
use App\Models\UserModel;
use App\Models\PesananModel;
use CodeIgniter\HTTP\RedirectResponse;
use CodeIgniter\Test\CIUnitTestCase;

class KonfirmasiPesananTest extends CIUnitTestCase
{
    protected function tearDown(): void
    {
        session()->destroy();
        parent::tearDown();
    }

    /**
     * Helper untuk inject fake model ke controller.
     */
    protected function makeController(
        ?object $pesananModel = null,
        ?object $userModel = null
    ): KonfirmasiPesanan {
        $controller = new KonfirmasiPesanan();

        $ref = new \ReflectionClass($controller);

        if ($pesananModel !== null) {
            $prop = $ref->getProperty('pesananModel');
            $prop->setAccessible(true);
            $prop->setValue($controller, $pesananModel);
        }

        if ($userModel !== null) {
            $prop = $ref->getProperty('userModel');
            $prop->setAccessible(true);
            $prop->setValue($controller, $userModel);
        }

        return $controller;
    }

    public function testIndexRedirectsToLoginWhenNotLoggedIn(): void
    {
        session()->remove('id_user');

        $controller = new KonfirmasiPesanan();
        $response   = $controller->index();

        $this->assertInstanceOf(RedirectResponse::class, $response);
        $this->assertStringContainsString('/login', $response->getHeaderLine('Location'));
    }

    public function testIndexUsesGetPesananByStatusForLoggedInUser()
    {
        session()->set(['id_user' => 10]);

        // Mock pesanan model
        $pesananModel = $this->createMock(PesananModel::class);
        $pesananModel->expects($this->once())
            ->method('getPesananByStatus')
            ->with(10, 'Dikirim')
            ->willReturn([
                ['id_pemesanan' => 1, 'status_pemesanan' => 'Dikirim'],
            ]);

        // Mock user model (dipakai di sidebar: role/foto)
        $userModel = $this->createMock(UserModel::class);
        $userModel->expects($this->once())
            ->method('find')
            ->with(10)
            ->willReturn([
                'id_user'  => 10,
                'username' => 'tester',
                'email'    => 'tester@example.com',
                'role'     => 'user',
                'foto'     => 'default.png',
            ]);

        $controller = $this->makeController($pesananModel, $userModel);

        $result = $controller->index();

        $this->assertIsString($result);
        $this->assertStringContainsString('Pesanan', $result);
    }

    public function testSelesaiRedirectsToLoginWhenNotLoggedIn(): void
    {
        session()->remove('id_user');

        $controller = new KonfirmasiPesanan();
        $response   = $controller->selesai(123);

        $this->assertInstanceOf(RedirectResponse::class, $response);
        $this->assertStringContainsString('/login', $response->getHeaderLine('Location'));
    }

    public function testSelesaiGivesErrorWhenOrderNotFound(): void
    {
        session()->set(['id_user' => 5]);

        $fakePesanan = new class {
            public array $calls = [];

            public function getPesananByIdAndUser($idPemesanan, $idUser)
            {
                $this->calls[] = [$idPemesanan, $idUser];
                return null; // tidak ditemukan
            }

            public function update($id, $data)
            {
                // tidak seharusnya dipanggil di kasus ini
                throw new \RuntimeException('update() tidak boleh dipanggil ketika pesanan tidak ditemukan');
            }
        };

        $controller = $this->makeController($fakePesanan, new class {});

        $response = $controller->selesai(99);

        $this->assertInstanceOf(RedirectResponse::class, $response);
        $this->assertSame('Pesanan tidak ditemukan.', session()->getFlashdata('error'));
    }

    public function testSelesaiGivesErrorWhenStatusNotDikirim(): void
    {
        session()->set(['id_user' => 7]);

        $fakePesanan = new class {
            public function getPesananByIdAndUser($idPemesanan, $idUser)
            {
                return [
                    'id_pemesanan'     => $idPemesanan,
                    'id_user'          => $idUser,
                    'status_pemesanan' => 'Diproses', // bukan Dikirim
                ];
            }

            public function update($id, $data)
            {
                throw new \RuntimeException('update() tidak boleh dipanggil ketika status bukan Dikirim');
            }
        };

        $controller = $this->makeController($fakePesanan, new class {});

        $response = $controller->selesai(50);

        $this->assertInstanceOf(RedirectResponse::class, $response);
        $this->assertSame(
            'Pesanan ini tidak dalam status Dikirim.',
            session()->getFlashdata('error')
        );
    }

    public function testSelesaiSuccessUpdatesOrderAndRedirects(): void
    {
        session()->set(['id_user' => 9]);

        $fakePesanan = new class {
            public array $updateArgs = [];

            public function getPesananByIdAndUser($idPemesanan, $idUser)
            {
                return [
                    'id_pemesanan'     => $idPemesanan,
                    'id_user'          => $idUser,
                    'status_pemesanan' => 'Dikirim',
                ];
            }

            public function update($id, $data)
            {
                $this->updateArgs = [$id, $data];
                return true;
            }
        };

        $controller = $this->makeController($fakePesanan, new class {});

        $response = $controller->selesai(77);

        $this->assertInstanceOf(RedirectResponse::class, $response);
        $this->assertStringContainsString('/pesananselesai', $response->getHeaderLine('Location'));

        [$id, $data] = $fakePesanan->updateArgs;

        $this->assertSame(77, $id);
        $this->assertSame('Selesai', $data['status_pemesanan'] ?? null);
        $this->assertArrayHasKey('confirmed_at', $data);
        $this->assertNull($data['konfirmasi_token'] ?? null);

        $this->assertSame(
            'Pesanan berhasil dikonfirmasi!',
            session()->getFlashdata('success')
        );
    }

    public function testSelesaiFailedUpdateSetsErrorFlash(): void
    {
        session()->set(['id_user' => 9]);

        $fakePesanan = new class {
            public function getPesananByIdAndUser($idPemesanan, $idUser)
            {
                return [
                    'id_pemesanan'     => $idPemesanan,
                    'id_user'          => $idUser,
                    'status_pemesanan' => 'Dikirim',
                ];
            }

            public function update($id, $data)
            {
                return false; // gagal update
            }
        };

        $controller = $this->makeController($fakePesanan, new class {});

        $response = $controller->selesai(88);

        $this->assertInstanceOf(RedirectResponse::class, $response);
        $this->assertStringContainsString('/pesananselesai', $response->getHeaderLine('Location'));

        $this->assertSame(
            'Gagal mengonfirmasi pesanan.',
            session()->getFlashdata('error')
        );
    }
}
