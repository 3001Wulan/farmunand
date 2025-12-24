<?php

namespace Tests\Controller;

use App\Controllers\ManajemenAkunUser;
use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\HTTP\RedirectResponse;

class ManajemenAkunUserFakeUserRepo
{
    public array $users = [];
    public array $updateCalls = [];
    public array $deletedIds = [];
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

class TestableManajemenAkunUser extends ManajemenAkunUser
{
    public function __construct(ManajemenAkunUserFakeUserRepo $userRepo)
    {
        $this->userModel = $userRepo;
    }

    public function index()
    {
        $keyword = (string) $this->request->getGet('keyword');
        $role    = (string) $this->request->getGet('role');

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

    public function edit($id_user)
    {
        $id   = (int) $id_user;
        $user = $this->userModel->find($id);

        return ['user' => $user];
    }

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

    public function delete($id_user)
    {
        $userModel = $this->userModel;
        $id        = (int) $id_user;

        if ($id <= 0) {
            return redirect()->back()->with('error', 'ID tidak valid.');
        }

        if ((int) session()->get('id_user') === $id) {
            return redirect()->back()->with('error', 'Tidak bisa menghapus akun sendiri.');
        }

        if ($userModel->hasPendingOrders($id)) {
            return redirect()->back()->with('error', 'User masih memiliki pesanan yang belum diselesaikan. Hapus dibatalkan.');
        }

        $method = strtolower($this->request->getMethod());

        if (! in_array($method, ['post', 'delete'], true)) {
            if (! $userModel->find($id)) {
                return redirect()->back()->with('error', 'User tidak ditemukan.');
            }

            $userModel->delete($id);

            return redirect()->to('/manajemenakunuser')
                ->with('success', 'User dihapus (gunakan POST agar lebih aman).');
        }

        if (! $userModel->find($id)) {
            return redirect()->back()->with('error', 'User tidak ditemukan.');
        }

        $userModel->delete($id);

        return redirect()->to('/manajemenakunuser')->with('success', 'User dihapus.');
    }
}

class ManajemenAkunUserTest extends CIUnitTestCase
{
    private $repo;
    private $controller;
    private $request;

    protected function setUp(): void
    {
        parent::setUp();

        $_SESSION = [];
        session()->destroy();

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

    public function testIndexWithoutFilterReturnsAllUsersAndCurrentUser(): void
    {
        session()->set('id_user', 1);

        $this->request->setMethod('get')->setGlobal('get', []);

        $data = $this->controller->index();

        $this->assertIsArray($data);
        $this->assertArrayHasKey('users', $data);
        $this->assertArrayHasKey('user', $data);

        $this->assertCount(3, $data['users']);
        $this->assertSame('admin', $data['user']['username']);
    }

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

        $this->assertCount(2, $data['users']);

        $usernames = array_column($data['users'], 'username');
        sort($usernames);
        $this->assertSame(['user1', 'user2'], $usernames);
    }

    public function testEditReturnsUserDataForEdit(): void
    {
        $data = $this->controller->edit(2);

        $this->assertIsArray($data);
        $this->assertArrayHasKey('user', $data);
        $this->assertSame('user1', $data['user']['username']);
    }

    public function testEditReturnsNullWhenUserNotFound(): void
    {
        $data = $this->controller->edit(999);

        $this->assertArrayHasKey('user', $data);
        $this->assertNull($data['user']);
    }

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

        $this->assertCount(1, $this->repo->updateCalls);
        [$id, $data] = $this->repo->updateCalls[0];

        $this->assertSame(2, $id);
        $this->assertSame('User Baru', $data['nama']);
        $this->assertSame('new@test.com', $data['email']);
        $this->assertSame('08123456789', $data['no_hp']);
        $this->assertSame('active', $data['status']);
    }

    public function testDeleteWithInvalidIdReturnsError(): void
    {
        session()->set('id_user', 1);
        $this->request->setMethod('post');

        $response = $this->controller->delete(0);

        $this->assertInstanceOf(RedirectResponse::class, $response);
        $this->assertSame('ID tidak valid.', session()->getFlashdata('error'));
        $this->assertSame([], $this->repo->deletedIds);
    }

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

    public function testDeletePreventsUserWithPendingOrders(): void
    {
        session()->set('id_user', 1);
        $this->request->setMethod('post');

        $this->repo->pendingOrdersIds = [2];

        $response = $this->controller->delete(2);

        $this->assertInstanceOf(RedirectResponse::class, $response);
        $this->assertSame(
            'User masih memiliki pesanan yang belum diselesaikan. Hapus dibatalkan.',
            session()->getFlashdata('error')
        );
        $this->assertSame([], $this->repo->deletedIds);
    }

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

    public function testDeleteUserNotFound(): void
    {
        session()->set('id_user', 1);
        $this->request->setMethod('post');

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