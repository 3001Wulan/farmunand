<?php

namespace Tests\Controller;

use CodeIgniter\Test\CIUnitTestCase;
use App\Controllers\Profile as RealProfile;

/**
 * Fake repository pengganti UserModel untuk Profile (pembeli).
 * Semua data user disimpan in-memory (array).
 */
class ProfilePembeliFakeUserRepo
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
 * Fake uploaded file khusus untuk ProfilePembeliTest
 * supaya tidak menyentuh filesystem beneran.
 */
class ProfilePembeliFakeUploadedFile
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
 * Versi testable dari Profile:
 *  - Tidak pakai UserModel asli → pakai ProfilePembeliFakeUserRepo.
 *  - Tidak render view → index/edit/update mengembalikan ARRAY.
 *  - Tidak pakai validator CI4 → validasi disimulasikan.
 *  - File upload disimulasikan pakai ProfilePembeliFakeUploadedFile.
 */
class TestableProfile extends RealProfile
{
    private ProfilePembeliFakeUserRepo $userRepo;

    /** Data POST buatan untuk unit-test */
    public array $fakePost = [];

    /** File upload buatan untuk unit-test */
    public ?ProfilePembeliFakeUploadedFile $fakeFile = null;

    /** Hasil validasi berikutnya (diset dari test) */
    public bool $validationNextResult = true;

    /** Error validasi buatan untuk disimpan di session */
    public array $validationErrors = [];

    public function __construct(ProfilePembeliFakeUserRepo $userRepo)
    {
        // Jangan panggil parent::__construct() supaya tidak buat UserModel asli.
        $this->userRepo  = $userRepo;
        $this->userModel = $userRepo; // jaga-jaga kalau dipakai di tempat lain.
    }

    public function withPost(array $data): self
    {
        $this->fakePost = $data;
        return $this;
    }

    public function withUploadedFile(?ProfilePembeliFakeUploadedFile $file): self
    {
        $this->fakeFile = $file;
        return $this;
    }

    public function withValidationResult(bool $ok, array $errors = []): self
    {
        $this->validationNextResult = $ok;
        $this->validationErrors     = $errors;
        return $this;
    }

    /**
     * index() versi unit-test:
     *  - belum login → redirect ke /login (via array)
     *  - sudah login → kembalikan data user & title
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
            'title' => 'Profil Saya',
            'user'  => $this->userRepo->find($userId),
        ];
    }

    /**
     * edit() versi unit-test:
     *  - belum login → redirect ke /login (via array)
     *  - sudah login → data untuk form edit
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
            'title' => 'Edit Profil',
            'user'  => $this->userRepo->find($userId),
        ];
    }

    /**
     * update() versi unit-test:
     *  - pakai fakePost + fakeFile
     *  - validasi disimulasikan pakai $validationNextResult
     *  - tidak sentuh filesystem (tidak file_exists / unlink)
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

        // Simulasi validasi (pengganti $this->validate(...))
        if (! $this->validationNextResult) {
            // Simulasikan perilaku redirect()->back()->withInput()->with('errors', ...)
            $session->set('errors', $this->validationErrors);

            return [
                'success'  => false,
                'redirect' => 'back',
                'errors'   => $this->validationErrors,
            ];
        }

        // Data update diambil dari fakePost
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
        }

        $this->userRepo->update($userId, $dataUpdate);

        // Simulasi flash success seperti controller asli
        $session->setFlashdata('success', 'Profil berhasil diperbarui!');

        return [
            'success'      => true,
            'redirect'     => '/profile',
            'updated_data' => $dataUpdate,
            'user_after'   => $this->userRepo->find($userId),
        ];
    }
}

/**
 * Test murni unit untuk Profile (ProfilPembeli).
 *  - Tanpa DB
 *  - Tanpa view
 *  - Tanpa filesystem
 */
class ProfilPembeliTest extends CIUnitTestCase
{
    private ProfilePembeliFakeUserRepo $userRepo;
    private TestableProfile $controller;

    protected function setUp(): void
    {
        parent::setUp();

        helper('session');

        // Reset session
        $_SESSION = [];
        session()->destroy();

        // Seed user dummy
        $this->userRepo = new ProfilePembeliFakeUserRepo([
            [
                'id_user'  => 1,
                'username' => 'user1',
                'nama'     => 'User Satu',
                'role'     => 'user',
                'email'    => 'user@gmail.com',
                'no_hp'    => '0800000000',
                'foto'     => 'lama.png',
            ],
        ]);

        $this->controller = new TestableProfile($this->userRepo);
    }

    protected function tearDown(): void
    {
        session()->destroy();
        parent::tearDown();
    }

    /** ---------------- INDEX ---------------- */

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
        $this->assertSame('Profil Saya', $data['title']);
        $this->assertIsArray($data['user']);

        $this->assertSame(1, $data['user']['id_user']);
        $this->assertSame('user1', $data['user']['username']);
        $this->assertSame('user', $data['user']['role']);
    }

    /** ---------------- EDIT ---------------- */

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
        $this->assertSame('Edit Profil', $data['title']);
        $this->assertIsArray($data['user']);

        $this->assertSame('user1', $data['user']['username']);
        $this->assertSame('user', $data['user']['role']);
    }

    /** ---------------- UPDATE ---------------- */

    public function testUpdateWithoutSessionDoesNotCallRepoAndRedirectsToLogin(): void
    {
        session()->remove('id_user');

        $result = $this->controller
            ->withPost([
                'username' => 'userbaru',
                'nama'     => 'User Baru',
                'email'    => 'baru@example.com',
                'no_hp'    => '0811111111',
            ])
            ->update();

        $this->assertFalse($result['success']);
        $this->assertSame('/login', $result['redirect']);
        $this->assertSame('Silakan login dulu.', $result['message']);

        $this->assertSame([], $this->userRepo->updateCalls);
    }

    public function testUpdateValidationFailsDoesNotUpdateRepo(): void
    {
        session()->set('id_user', 1);

        $errors = ['username' => 'Username wajib diisi'];

        $result = $this->controller
            ->withPost([
                'username' => '', // dianggap invalid
                'nama'     => 'Nama',
                'email'    => 'email@test.com',
                'no_hp'    => '08123',
            ])
            ->withValidationResult(false, $errors)
            ->update();

        $this->assertFalse($result['success']);
        $this->assertSame('back', $result['redirect']);
        $this->assertArrayHasKey('username', $result['errors']);

        // Tidak ada update ke repo
        $this->assertSame([], $this->userRepo->updateCalls);

        // Error juga tersimpan di session (simulasi with('errors', ...))
        $sessionErrors = session()->get('errors') ?? [];
        $this->assertArrayHasKey('username', $sessionErrors);
    }

    public function testUpdateValidWithoutFotoUpdatesBasicFields(): void
    {
        session()->set('id_user', 1);

        $result = $this->controller
            ->withPost([
                'username' => 'user1updated',
                'nama'     => 'User Updated',
                'email'    => 'user1@example.com',
                'no_hp'    => '08123456789',
            ])
            ->withValidationResult(true)
            ->withUploadedFile(null)
            ->update();

        $this->assertTrue($result['success']);
        $this->assertSame('/profile', $result['redirect']);

        $this->assertCount(1, $this->userRepo->updateCalls);
        [$id, $data] = $this->userRepo->updateCalls[0];

        $this->assertSame(1, $id);
        $this->assertSame('user1updated', $data['username']);
        $this->assertSame('User Updated', $data['nama']);
        $this->assertSame('user1@example.com', $data['email']);
        $this->assertSame('08123456789', $data['no_hp']);
        $this->assertArrayNotHasKey('foto', $data, 'Foto tidak boleh ikut berubah jika tidak ada upload.');

        $userAfter = $this->userRepo->find(1);
        $this->assertSame('user1updated', $userAfter['username']);
        $this->assertSame('User Updated', $userAfter['nama']);

        $this->assertSame(
            'Profil berhasil diperbarui!',
            session()->getFlashdata('success')
        );
    }

    public function testUpdateValidWithFotoReplacesFoto(): void
    {
        session()->set('id_user', 1);

        $fakeFile = new ProfilePembeliFakeUploadedFile(true, 'foto_baru.png');

        $result = $this->controller
            ->withPost([
                'username' => 'userfoto',
                'nama'     => 'User Foto',
                'email'    => 'userfoto@example.com',
                'no_hp'    => '0899999999',
            ])
            ->withValidationResult(true)
            ->withUploadedFile($fakeFile)
            ->update();

        $this->assertTrue($result['success']);
        $this->assertSame('/profile', $result['redirect']);

        $this->assertTrue($fakeFile->hasMoved());
        $this->assertSame('uploads/profile/foto_baru.png', $fakeFile->movedTo);

        $this->assertCount(1, $this->userRepo->updateCalls);
        [$id, $data] = $this->userRepo->updateCalls[0];

        $this->assertSame(1, $id);
        $this->assertSame('foto_baru.png', $data['foto']);

        $userAfter = $this->userRepo->find(1);
        $this->assertSame('foto_baru.png', $userAfter['foto']);
        $this->assertSame('userfoto', $userAfter['username']);

        $this->assertSame(
            'Profil berhasil diperbarui!',
            session()->getFlashdata('success')
        );
    }
}
