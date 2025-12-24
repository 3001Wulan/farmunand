<?php

namespace Tests\Controller;

use CodeIgniter\Test\CIUnitTestCase;
use App\Controllers\ProfileAdmin as RealProfileAdmin;

class ProfileAdminFakeUserRepo
{
    public array $users = [];
    public array $updateCalls = [];

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

class TestableProfileAdmin extends RealProfileAdmin
{
    private ProfileAdminFakeUserRepo $userRepo;
    public array $fakePost = [];
    public ?FakeUploadedFile $fakeFile = null;

    public function __construct(ProfileAdminFakeUserRepo $userRepo)
    {
        $this->userRepo  = $userRepo;
        $this->userModel = $userRepo;
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

class ProfilAdminTest extends CIUnitTestCase
{
    private ProfileAdminFakeUserRepo $userRepo;
    private TestableProfileAdmin $controller;

    protected function setUp(): void
    {
        parent::setUp();

        helper('session');

        $_SESSION = [];
        session()->destroy();

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

        $this->assertCount(1, $this->userRepo->updateCalls);
        [$id, $data] = $this->userRepo->updateCalls[0];

        $this->assertSame(1, $id);
        $this->assertSame('AdminBaru', $data['username']);
        $this->assertSame('Admin Nama Baru', $data['nama']);
        $this->assertSame('admin.new@test.com', $data['email']);
        $this->assertSame('08123456789', $data['no_hp']);
        $this->assertArrayNotHasKey('foto', $data);

        $user = $this->userRepo->find(1);
        $this->assertSame('AdminBaru', $user['username']);
        $this->assertSame('Admin Nama Baru', $user['nama']);
        $this->assertSame('admin.new@test.com', $user['email']);
        $this->assertSame('08123456789', $user['no_hp']);

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

        $this->assertTrue($fakeFile->hasMoved());
        $this->assertSame('uploads/profile/foto_baru.png', $fakeFile->movedTo);

        $this->assertCount(1, $this->userRepo->updateCalls);
        [$id, $data] = $this->userRepo->updateCalls[0];

        $this->assertSame(1, $id);
        $this->assertSame('foto_baru.png', $data['foto']);

        $this->assertSame('foto_baru.png', session()->get('foto'));

        $userAfter = $this->userRepo->find(1);
        $this->assertSame('foto_baru.png', $userAfter['foto']);
        $this->assertSame('AdminFoto', $userAfter['username']);

        $this->assertSame(
            'Profil Admin berhasil diperbarui.',
            session()->getFlashdata('success')
        );
    }
}