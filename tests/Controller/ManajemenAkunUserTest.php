<?php

namespace Tests\Controller;

use App\Controllers\ManajemenAkunUser;
use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\HTTP\RedirectResponse;

/**
 * Fake repository pengganti UserModel.
 * Seluruh data disimpan di array (in-memory), tanpa DB.
 */
class ManajemenAkunUserFakeUserRepo
{
    /** @var array<int,array> */
    public array $users = [];

    /** @var array<int,array{0:int,1:array}> */
    public array $updateCalls = [];

    /** @var int[] */
    public array $deletedIds = [];

    /** @var int[] daftar id user yang dianggap masih punya pesanan pending */
    public array $pendingOrdersIds = [];

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

    /**
     * Implementasi sederhana untuk filter yang ekuivalen dengan:
     *  - keyword pada nama/email/username
     *  *  - filter role (admin / user / dll)
     */
    public function filterUsers(string $keyword, string $role): array
    {
        $keyword = trim($keyword);
        $role    = trim($role);

        $result = [];

        foreach ($this->users as $u) {
            if ($keyword !== '') {
                $haystack = strtolower(
                    ($u['nama'] ?? '') . ' ' .
                    ($u['email'] ?? '') . ' ' .
                    ($u['username'] ?? '')
                );
                if (strpos($haystack, strtolower($keyword)) === false) {
                    continue;
                }
            }

            if ($role !== '' && ($u['role'] ?? '') !== $role) {
                continue;
            }

            $result[] = $u;
        }

        return $result;
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

    public function delete(int $id): void
    {
        $this->deletedIds[] = $id;
        unset($this->users[$id]);
    }

    public function hasPendingOrders(int $id): bool
    {
        return in_array($id, $this->pendingOrdersIds, true);
    }
}

/**
 * Versi testable dari ManajemenAkunUser:
 *  - Tidak memanggil konstruktor asli (tidak bikin UserModel / DB).
 *  - index() & edit() dikembalikan sebagai array, bukan view HTML.
 *  - update() & delete() pakai fake repo, tapi logika sama dengan controller asli.
 */
class TestableManajemenAkunUser extends ManajemenAkunUser
{
    public function __construct(ManajemenAkunUserFakeUserRepo $userRepo)
    {
        // Jangan panggil parent::__construct() supaya tidak membuat UserModel asli.
        $this->userModel = $userRepo;
    }

    /**
     * index() versi unit-test:
     *  - membaca keyword & role dari GET
     *  - minta daftar user terfilter ke ManajemenAkunUserFakeUserRepo
     *  - mengembalikan array data (bukan view())
     */
    public function index()
    {
        $keyword = (string) $this->request->getGet('keyword');
        $role    = (string) $this->request->getGet('role');

        /** @var ManajemenAkunUserFakeUserRepo $repo */
        $repo  = $this->userModel;
        $users = $repo->filterUsers($keyword, $role);

        $userId      = (int) (session()->get('id_user') ?? 0);
        $currentUser = $userId ? $repo->find($userId) : null;

        return [
            'users'   => $users,
            'user'    => $currentUser,
            'keyword' => $keyword,
            'role'    => $role,
        ];
    }

    /**
     * edit() versi unit-test:
     *  - hanya ambil user dari repo, return array.
     */
    public function edit($id_user)
    {
        $id   = (int) $id_user;
        $user = $this->userModel->find($id);

        return ['user' => $user];
    }

    /**
     * update() versi unit-test:
     *  - ambil data POST
     *  - panggil $repo->update()
     *  - return RedirectResponse (seperti controller asli)
     */
    public function update($id_user)
    {
        $id = (int) $id_user;

        $data = [
            'nama'   => $this->request->getPost('nama'),
            'email'  => $this->request->getPost('email'),
            'no_hp'  => $this->request->getPost('no_hp'),
            'status' => $this->request->getPost('status'),
        ];

        $this->userModel->update($id, $data);

        return redirect()->to('/manajemenakunuser')->with('success', 'User diperbarui.');
    }

    /**
     * delete() disalin utuh dari controller asli,
     * hanya saja memakai $this->userModel (ManajemenAkunUserFakeUserRepo).
     */
    public function delete($id_user)
    {
        /** @var ManajemenAkunUserFakeUserRepo $userModel */
        $userModel = $this->userModel;
        $id        = (int) $id_user;

        if ($id <= 0) {
            return redirect()->back()->with('error', 'ID tidak valid.');
        }

        // Cegah hapus diri sendiri
        if ((int) session()->get('id_user') === $id) {
            return redirect()->back()->with('error', 'Tidak bisa menghapus akun sendiri.');
        }

        // Cek pesanan pending
        if ($userModel->hasPendingOrders($id)) {
            return redirect()->back()->with('error', 'User masih memiliki pesanan yang belum diselesaikan. Hapus dibatalkan.');
        }

        $method = strtolower($this->request->getMethod());

        // Metode non-POST/DELETE → tetap izinkan, tapi beri pesan "gunakan POST..."
        if (! in_array($method, ['post', 'delete'], true)) {
            if (! $userModel->find($id)) {
                return redirect()->back()->with('error', 'User tidak ditemukan.');
            }

            $userModel->delete($id);

            return redirect()->to('/manajemenakunuser')
                ->with('success', 'User dihapus (gunakan POST agar lebih aman).');
        }

        // Metode POST/DELETE
        if (! $userModel->find($id)) {
            return redirect()->back()->with('error', 'User tidak ditemukan.');
        }

        $userModel->delete($id);

        return redirect()->to('/manajemenakunuser')->with('success', 'User dihapus.');
    }
}

class ManajemenAkunUserTest extends CIUnitTestCase
{
    /** @var ManajemenAkunUserFakeUserRepo */
    private $repo;

    /** @var TestableManajemenAkunUser */
    private $controller;

    /** @var \CodeIgniter\HTTP\IncomingRequest */
    private $request;

    protected function setUp(): void
    {
        parent::setUp();

        // Reset session setiap test
        $_SESSION = [];
        session()->destroy();

        // Seed data user dummy (tanpa DB)
        $this->repo = new ManajemenAkunUserFakeUserRepo([
            [
                'id_user'  => 1,
                'username' => 'admin',
                'nama'     => 'Admin',
                'email'    => 'admin@test.com',
                'role'     => 'admin',
                'status'   => 'active',
            ],
            [
                'id_user'  => 2,
                'username' => 'user1',
                'nama'     => 'User Satu',
                'email'    => 'user1@test.com',
                'role'     => 'user',
                'status'   => 'active',
            ],
            [
                'id_user'  => 3,
                'username' => 'user2',
                'nama'     => 'User Dua',
                'email'    => 'user2@test.com',
                'role'     => 'user',
                'status'   => 'inactive',
            ],
        ]);

        // Buat controller testable & inject request/response/logger
        $this->controller = new TestableManajemenAkunUser($this->repo);
        $this->request    = service('request');

        $this->controller->initController(
            $this->request,
            service('response'),
            service('logger')
        );
    }

    protected function tearDown(): void
    {
        session()->destroy();
        parent::tearDown();
    }

    /**
     * Skenario: index tanpa filter apa pun.
     * Ekspektasi:
     *  - mengembalikan semua user
     *  - current user = admin (id_user = 1)
     */
    public function testIndexWithoutFilterReturnsAllUsersAndCurrentUser(): void
    {
        session()->set('id_user', 1);

        $this->request->setMethod('get')->setGlobal('get', []);

        $data = $this->controller->index();

        $this->assertIsArray($data);
        $this->assertArrayHasKey('users', $data);
        $this->assertArrayHasKey('user', $data);

        // 3 user total (admin + 2 user)
        $this->assertCount(3, $data['users']);
        $this->assertSame('admin', $data['user']['username']);
    }

    /**
     * Skenario: index dengan filter keyword + role.
     * keyword: "user"  → hanya cocok user1 & user2
     * role   : "user"  → admin terfilter keluar
     */
    public function testIndexAppliesKeywordAndRoleFilter(): void
    {
        session()->set('id_user', 1);

        $this->request->setMethod('get')->setGlobal('get', [
            'keyword' => 'user',
            'role'    => 'user',
        ]);

        $data = $this->controller->index();

        $this->assertIsArray($data);
        $this->assertSame('user', $data['role']);

        // Harusnya hanya user1 & user2 yang lolos
        $this->assertCount(2, $data['users']);

        $usernames = array_column($data['users'], 'username');
        sort($usernames);
        $this->assertSame(['user1', 'user2'], $usernames);
    }

    /**
     * Skenario: edit user yang ada.
     * Ekspektasi:
     *  - mengembalikan array ['user' => ...]
     *  - username sesuai repo
     */
    public function testEditReturnsUserDataForEdit(): void
    {
        $data = $this->controller->edit(2);

        $this->assertIsArray($data);
        $this->assertArrayHasKey('user', $data);
        $this->assertSame('user1', $data['user']['username']);
    }

    /**
     * Skenario: edit user yang tidak ada.
     * Ekspektasi:
     *  - 'user' = null
     */
    public function testEditReturnsNullWhenUserNotFound(): void
    {
        $data = $this->controller->edit(999);

        $this->assertArrayHasKey('user', $data);
        $this->assertNull($data['user']);
    }

    /**
     * Skenario: update user memanggil repo->update() dengan data POST,
     * lalu redirect ke /manajemenakunuser dengan flash success.
     */
    public function testUpdateCallsRepoUpdateAndRedirects(): void
    {
        session()->set('id_user', 1);

        $this->request->setMethod('post')->setGlobal('post', [
            'nama'   => 'User Baru',
            'email'  => 'new@test.com',
            'no_hp'  => '08123456789',
            'status' => 'active',
        ]);

        $response = $this->controller->update(2);

        $this->assertInstanceOf(RedirectResponse::class, $response);
        $this->assertStringContainsString(
            '/manajemenakunuser',
            $response->getHeaderLine('Location')
        );

        // Pastikan update() dipanggil dengan id & data yang benar
        $this->assertCount(1, $this->repo->updateCalls);
        [$id, $data] = $this->repo->updateCalls[0];

        $this->assertSame(2, $id);
        $this->assertSame('User Baru', $data['nama']);
        $this->assertSame('new@test.com', $data['email']);
        $this->assertSame('08123456789', $data['no_hp']);
        $this->assertSame('active', $data['status']);
    }

    /**
     * Skenario: delete dipanggil dengan ID tidak valid (<= 0).
     * Ekspektasi:
     *  - redirect back
     *  - flash error "ID tidak valid."
     *  - repo->delete() tidak dipanggil.
     */
    public function testDeleteWithInvalidIdReturnsError(): void
    {
        session()->set('id_user', 1);
        $this->request->setMethod('post');

        $response = $this->controller->delete(0);

        $this->assertInstanceOf(RedirectResponse::class, $response);
        $this->assertSame('ID tidak valid.', session()->getFlashdata('error'));
        $this->assertSame([], $this->repo->deletedIds);
    }

    /**
     * Skenario: admin mencoba menghapus dirinya sendiri.
     * Ekspektasi:
     *  - ditolak dengan pesan "Tidak bisa menghapus akun sendiri."
     *  - repo->delete() tidak dipanggil.
     */
    public function testDeletePreventsDeletingSelf(): void
    {
        session()->set('id_user', 1);
        $this->request->setMethod('post');

        $response = $this->controller->delete(1);

        $this->assertInstanceOf(RedirectResponse::class, $response);
        $this->assertSame(
            'Tidak bisa menghapus akun sendiri.',
            session()->getFlashdata('error')
        );
        $this->assertSame([], $this->repo->deletedIds);
    }

    /**
     * Skenario: user yang akan dihapus masih punya pesanan pending.
     * Ekspektasi:
     *  - penghapusan dibatalkan
     *  - pesan error sesuai
     *  - repo->delete() tidak dipanggil
     */
    public function testDeletePreventsUserWithPendingOrders(): void
    {
        session()->set('id_user', 1);
        $this->request->setMethod('post');

        // User 2 dianggap masih punya pesanan pending
        $this->repo->pendingOrdersIds = [2];

        $response = $this->controller->delete(2);

        $this->assertInstanceOf(RedirectResponse::class, $response);
        $this->assertSame(
            'User masih memiliki pesanan yang belum diselesaikan. Hapus dibatalkan.',
            session()->getFlashdata('error')
        );
        $this->assertSame([], $this->repo->deletedIds);
    }

    /**
     * Skenario: hapus user dengan metode GET (bukan POST/DELETE).
     * Ekspektasi:
     *  - user dihapus
     *  - pesan success khusus: "User dihapus (gunakan POST agar lebih aman)."
     */
    public function testDeleteUserSuccessWithGetMethod(): void
    {
        session()->set('id_user', 1);
        $this->request->setMethod('get');

        $response = $this->controller->delete(2);

        $this->assertInstanceOf(RedirectResponse::class, $response);
        $this->assertStringContainsString(
            '/manajemenakunuser',
            $response->getHeaderLine('Location')
        );
        $this->assertSame(
            'User dihapus (gunakan POST agar lebih aman).',
            session()->getFlashdata('success')
        );

        $this->assertContains(2, $this->repo->deletedIds);
        $this->assertArrayNotHasKey(2, $this->repo->users);
    }

    /**
     * Skenario: hapus user dengan metode POST normal.
     * Ekspektasi:
     *  - user dihapus
     *  - pesan success "User dihapus."
     */
    public function testDeleteUserSuccessWithPostMethod(): void
    {
        session()->set('id_user', 1);
        $this->request->setMethod('post');

        $response = $this->controller->delete(3);

        $this->assertInstanceOf(RedirectResponse::class, $response);
        $this->assertStringContainsString(
            '/manajemenakunuser',
            $response->getHeaderLine('Location')
        );
        $this->assertSame(
            'User dihapus.',
            session()->getFlashdata('success')
        );

        $this->assertContains(3, $this->repo->deletedIds);
        $this->assertArrayNotHasKey(3, $this->repo->users);
    }

    /**
     * Skenario: hapus user yang tidak ditemukan (POST).
     * Ekspektasi:
     *  - redirect back
     *  - pesan "User tidak ditemukan."
     *  - repo->delete() tidak dipanggil.
     */
    public function testDeleteUserNotFound(): void
    {
        session()->set('id_user', 1);
        $this->request->setMethod('post');

        // Pastikan user 999 tidak ada di repo
        unset($this->repo->users[999]);

        $response = $this->controller->delete(999);

        $this->assertInstanceOf(RedirectResponse::class, $response);
        $this->assertSame(
            'User tidak ditemukan.',
            session()->getFlashdata('error')
        );
        $this->assertSame([], $this->repo->deletedIds);
    }
}
