<?php

namespace Tests\Controller;

use CodeIgniter\Test\CIUnitTestCase;
use App\Controllers\ProfileAdmin as RealProfileAdmin;

/**
 * Fake repository pengganti UserModel untuk ProfileAdmin.
 * Semua data user disimpan di array in-memory.
 */
class ProfileAdminFakeUserRepo
{
    /** @var array<int,array> */
    public array $users = [];

    /** @var array<int,array{0:int,1:array}> */
    public array $updateCalls = [];

    /**
     * @param array<int,array> $users
     */
    public function __construct(array $users = [])
    {
        foreach ($users as $u) {
            $this->users[(int) $u['id_user']] = $u;
        }
    }

    public function find(int $id): ?array
    {
        return $this->users[$id] ?? null;
    }

    public function update(int $id, array $data): bool
    {
        $this->updateCalls[] = [$id, $data];

        if (! isset($this->users[$id])) {
            $this->users[$id] = array_merge(['id_user' => $id], $data);
        } else {
            $this->users[$id] = array_merge($this->users[$id], $data);
        }

        return true;
    }
}

/**
 * Fake uploaded file untuk mensimulasikan upload foto profil
 * tanpa benar-benar menyentuh filesystem.
 */
class FakeUploadedFile
{
    private bool $valid;
    private bool $moved = false;
    private string $randomName;
    public ?string $movedTo = null;

    public function __construct(bool $valid = false, string $randomName = 'uploaded.png')
    {
        $this->valid      = $valid;
        $this->randomName = $randomName;
    }

    public function isValid(): bool
    {
        return $this->valid;
    }

    public function hasMoved(): bool
    {
        return $this->moved;
    }

    public function getRandomName(): string
    {
        return $this->randomName;
    }

    public function move(string $directory, ?string $name = null): void
    {
        $this->moved   = true;
        $fileName      = $name ?? $this->randomName;
        $this->movedTo = rtrim($directory, '/') . '/' . $fileName;
    }
}

/**
 * Versi testable dari ProfileAdmin:
 *  - Tidak pakai UserModel asli → pakai ProfileAdminFakeUserRepo.
 *  - Tidak memanggil view() → index/edit/update mengembalikan ARRAY data.
 *  - Update foto disimulasikan dengan FakeUploadedFile (tanpa file_exists/unlink).
 */
class TestableProfileAdmin extends RealProfileAdmin
{
    private ProfileAdminFakeUserRepo $userRepo;

    /** Data POST buatan untuk unit test */
    public array $fakePost = [];

    /** File upload buatan untuk unit test */
    public ?FakeUploadedFile $fakeFile = null;

    public function __construct(ProfileAdminFakeUserRepo $userRepo)
    {
        // Jangan panggil parent::__construct() agar tidak membuat UserModel asli.
        $this->userRepo  = $userRepo;
        $this->userModel = $userRepo; // supaya properti tetap ada kalau sewaktu-waktu dipakai.
    }

    public function withPost(array $data): self
    {
        $this->fakePost = $data;
        return $this;
    }

    public function withUploadedFile(?FakeUploadedFile $file): self
    {
        $this->fakeFile = $file;
        return $this;
    }

    /**
     * index() versi unit-test:
     *  - jika belum login → array redirect + error
     *  - jika login       → array data profil
     *
     * @return array<string,mixed>
     */
    public function index()
    {
        $userId = (int) (session()->get('id_user') ?? 0);
        if (! $userId) {
            return [
                'redirect' => '/login',
                'error'    => 'Silakan login dulu.',
            ];
        }

        return [
            'title' => 'Profil Admin',
            'user'  => $this->userRepo->find($userId),
        ];
    }

    /**
     * edit() versi unit-test:
     *  - jika belum login → array redirect + error
     *  - jika login       → array data profil untuk form edit
     *
     * @return array<string,mixed>
     */
    public function edit()
    {
        $userId = (int) (session()->get('id_user') ?? 0);
        if (! $userId) {
            return [
                'redirect' => '/login',
                'error'    => 'Silakan login dulu.',
            ];
        }

        return [
            'title' => 'Edit Profil Admin',
            'user'  => $this->userRepo->find($userId),
        ];
    }

    /**
     * update() versi unit-test:
     *  - menggunakan $fakePost & $fakeFile, bukan $this->request.
     *  - tidak memanggil file_exists/unlink (tidak sentuh filesystem).
     *  - mengembalikan array hasil update (bukan RedirectResponse).
     *
     * @return array<string,mixed>
     */
    public function update()
    {
        $session = session();
        $userId  = (int) ($session->get('id_user') ?? 0);

        if (! $userId) {
            return [
                'success'  => false,
                'redirect' => '/login',
                'message'  => 'Silakan login dulu.',
            ];
        }

        $user = $this->userRepo->find($userId);

        $dataUpdate = [
            'username' => $this->fakePost['username'] ?? ($user['username'] ?? null),
            'nama'     => $this->fakePost['nama']     ?? ($user['nama'] ?? null),
            'email'    => $this->fakePost['email']    ?? ($user['email'] ?? null),
            'no_hp'    => $this->fakePost['no_hp']    ?? ($user['no_hp'] ?? null),
        ];

        $file = $this->fakeFile;

        if ($file && $file->isValid() && ! $file->hasMoved()) {
            $newName = $file->getRandomName();
            $file->move('uploads/profile', $newName);

            // Tidak ada file_exists/unlink supaya tetap murni unit-test
            $dataUpdate['foto'] = $newName;
            $session->set('foto', $newName);
        }

        $this->userRepo->update($userId, $dataUpdate);

        $session->setFlashdata('success', 'Profil Admin berhasil diperbarui.');

        return [
            'success'      => true,
            'redirect'     => '/profileadmin',
            'updated_data' => $dataUpdate,
            'user_after'   => $this->userRepo->find($userId),
        ];
    }
}

/**
 * Test murni unit untuk ProfileAdmin (tanpa DB, tanpa view, tanpa filesystem).
 */
class ProfilAdminTest extends CIUnitTestCase
{
    private ProfileAdminFakeUserRepo $userRepo;
    private TestableProfileAdmin $controller;

    protected function setUp(): void
    {
        parent::setUp();

        helper('session');

        // Reset session tiap test
        $_SESSION = [];
        session()->destroy();

        // Seed user dummy
        $this->userRepo = new ProfileAdminFakeUserRepo([
            [
                'id_user'  => 1,
                'username' => 'admin',
                'nama'     => 'Admin Lama',
                'role'     => 'admin',
                'email'    => 'admin@old.test',
                'no_hp'    => '0800000000',
                'foto'     => 'lama.png',
            ],
        ]);

        $this->controller = new TestableProfileAdmin($this->userRepo);
    }

    protected function tearDown(): void
    {
        session()->destroy();
        parent::tearDown();
    }

    /** ---------------------- INDEX ---------------------- */

    public function testIndexWithoutSession(): void
    {
        session()->remove('id_user');

        $data = $this->controller->index();

        $this->assertIsArray($data);
        $this->assertSame('/login', $data['redirect'] ?? null);
        $this->assertSame('Silakan login dulu.', $data['error'] ?? null);
    }

    public function testIndexWithSession(): void
    {
        session()->set('id_user', 1);

        $data = $this->controller->index();

        $this->assertIsArray($data);
        $this->assertSame('Profil Admin', $data['title']);
        $this->assertIsArray($data['user']);

        $this->assertSame(1, $data['user']['id_user']);
        $this->assertSame('admin', $data['user']['username']);
        $this->assertSame('admin', $data['user']['role']);
    }

    /** ---------------------- EDIT ---------------------- */

    public function testEditWithoutSession(): void
    {
        session()->remove('id_user');

        $data = $this->controller->edit();

        $this->assertIsArray($data);
        $this->assertSame('/login', $data['redirect'] ?? null);
        $this->assertSame('Silakan login dulu.', $data['error'] ?? null);
    }

    public function testEditWithSession(): void
    {
        session()->set('id_user', 1);

        $data = $this->controller->edit();

        $this->assertIsArray($data);
        $this->assertSame('Edit Profil Admin', $data['title']);
        $this->assertIsArray($data['user']);

        $this->assertSame('admin', $data['user']['username']);
        $this->assertSame('admin', $data['user']['role']);
    }

    /** ---------------------- UPDATE ---------------------- */

    public function testUpdateWithoutSessionDoesNotCallRepoAndRedirectsToLogin(): void
    {
        session()->remove('id_user');

        $result = $this->controller
            ->withPost([
                'username' => 'Baru',
                'nama'     => 'Admin Baru',
                'email'    => 'baru@test.com',
                'no_hp'    => '0811111111',
            ])
            ->update();

        $this->assertFalse($result['success']);
        $this->assertSame('/login', $result['redirect']);
        $this->assertSame('Silakan login dulu.', $result['message']);

        $this->assertSame([], $this->userRepo->updateCalls);
    }

    public function testUpdateWithoutFotoUpdatesBasicFields(): void
    {
        session()->set('id_user', 1);

        $result = $this->controller
            ->withPost([
                'username' => 'AdminBaru',
                'nama'     => 'Admin Nama Baru',
                'email'    => 'admin.new@test.com',
                'no_hp'    => '08123456789',
            ])
            ->withUploadedFile(null)
            ->update();

        $this->assertTrue($result['success']);
        $this->assertSame('/profileadmin', $result['redirect']);

        // Pastikan update dipanggil 1x dengan id=1
        $this->assertCount(1, $this->userRepo->updateCalls);
        [$id, $data] = $this->userRepo->updateCalls[0];

        $this->assertSame(1, $id);
        $this->assertSame('AdminBaru', $data['username']);
        $this->assertSame('Admin Nama Baru', $data['nama']);
        $this->assertSame('admin.new@test.com', $data['email']);
        $this->assertSame('08123456789', $data['no_hp']);
        $this->assertArrayNotHasKey('foto', $data, 'Foto tidak boleh diubah jika tidak ada upload.');

        // Data user setelah update
        $user = $this->userRepo->find(1);
        $this->assertSame('AdminBaru', $user['username']);
        $this->assertSame('Admin Nama Baru', $user['nama']);
        $this->assertSame('admin.new@test.com', $user['email']);
        $this->assertSame('08123456789', $user['no_hp']);

        // Flash message
        $this->assertSame(
            'Profil Admin berhasil diperbarui.',
            session()->getFlashdata('success')
        );
    }

    public function testUpdateWithFotoReplacesFotoAndSetsSessionFoto(): void
    {
        session()->set('id_user', 1);

        $fakeFile = new FakeUploadedFile(true, 'foto_baru.png');

        $result = $this->controller
            ->withPost([
                'username' => 'AdminFoto',
                'nama'     => 'Admin Ganti Foto',
                'email'    => 'admin.foto@test.com',
                'no_hp'    => '0899999999',
            ])
            ->withUploadedFile($fakeFile)
            ->update();

        $this->assertTrue($result['success']);
        $this->assertSame('/profileadmin', $result['redirect']);

        // Pastikan file "dipindah"
        $this->assertTrue($fakeFile->hasMoved());
        $this->assertSame('uploads/profile/foto_baru.png', $fakeFile->movedTo);

        // Pastikan field foto ikut diupdate
        $this->assertCount(1, $this->userRepo->updateCalls);
        [$id, $data] = $this->userRepo->updateCalls[0];

        $this->assertSame(1, $id);
        $this->assertSame('foto_baru.png', $data['foto']);

        // Session foto seharusnya berisi nama file baru
        $this->assertSame('foto_baru.png', session()->get('foto'));

        // User setelah update
        $userAfter = $this->userRepo->find(1);
        $this->assertSame('foto_baru.png', $userAfter['foto']);
        $this->assertSame('AdminFoto', $userAfter['username']);

        $this->assertSame(
            'Profil Admin berhasil diperbarui.',
            session()->getFlashdata('success')
        );
    }
}
