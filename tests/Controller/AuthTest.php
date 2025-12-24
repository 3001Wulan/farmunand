<?php

namespace Tests\Controller;

use App\Controllers\Auth;
use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\HTTP\RedirectResponse;

class AuthFakeUserRepo
{
    public array $usersByEmail = [];
    public array $updateCalls = [];
    public array $saveCalls = [];
    private ?string $lastWhereField = null;
    private $lastWhereValue;

    public function __construct(array $users = [])
    {
        foreach ($users as $u) {
            if (! isset($u['email'])) {
                continue;
            }
            $email                        = (string) $u['email'];
            $this->usersByEmail[$email]   = $u;
        }
    }

    public function where($field, $value = null): self
    {
        if (is_string($field) && $value !== null) {
            $this->lastWhereField = $field;
            $this->lastWhereValue = $value;
        } elseif (is_array($field) && isset($field['email'])) {
            $this->lastWhereField = 'email';
            $this->lastWhereValue = $field['email'];
        }

        return $this;
    }

    public function first(): ?array
    {
        if ($this->lastWhereField === 'email') {
            $email = (string) $this->lastWhereValue;
            return $this->usersByEmail[$email] ?? null;
        }

        return null;
    }

    public function update($id = null, $data = null): bool
    {
        $id   = (int) $id;
        $data = $data ?? [];

        $this->updateCalls[] = [$id, $data];

        foreach ($this->usersByEmail as $email => $user) {
            if ((int) ($user['id_user'] ?? 0) === $id) {
                $this->usersByEmail[$email] = array_merge($user, $data);
                break;
            }
        }

        return true;
    }

    public function save(array $data): bool
    {
        $this->saveCalls[] = $data;

        $email = $data['email'] ?? null;
        if ($email === null) {
            return true;
        }

        $maxId = 0;
        foreach ($this->usersByEmail as $u) {
            $maxId = max($maxId, (int) ($u['id_user'] ?? 0));
        }
        $id = $data['id_user'] ?? ($maxId + 1);

        $user = array_merge(
            [
                'id_user'           => $id,
                'username'          => null,
                'role'              => 'user',
                'foto'              => 'default.png',
                'failed_logins'     => 0,
                'last_failed_login' => null,
                'locked_until'      => null,
            ],
            $data
        );

        $this->usersByEmail[(string) $email] = $user;

        return true;
    }
}

class TestableAuth extends Auth
{
    public static ?bool $forcedValidateResult = null;
    public static array $forcedErrors = [];
    protected $userModel;

    public function __construct(AuthFakeUserRepo $userRepo)
    {
        $this->userModel = $userRepo;
        helper(['form', 'url']);
    }

    protected function validate($rules, array $messages = []): bool
    {
        if (self::$forcedValidateResult !== null) {
            $this->validator = new class(self::$forcedErrors)
            {
                private array $errors;

                public function __construct(array $errors)
                {
                    $this->errors = $errors;
                }

                public function getErrors(): array
                {
                    return $this->errors;
                }
            };

            return self::$forcedValidateResult;
        }

        return parent::validate($rules, $messages);
    }
}

class AuthTest extends CIUnitTestCase
{
    private AuthFakeUserRepo $userRepo;
    private TestableAuth $controller;
    private $request;

    protected function setUp(): void
    {
        parent::setUp();

        $_SESSION = [];
        session()->destroy();

        $this->userRepo = new AuthFakeUserRepo([
            [
                'id_user'           => 1,
                'username'          => 'User One',
                'email'             => 'user01@farmunand.local',
                'password'          => password_hash('111111', PASSWORD_DEFAULT),
                'role'              => 'user',
                'foto'              => 'default.png',
                'failed_logins'     => 0,
                'last_failed_login' => null,
                'locked_until'      => null,
            ],
            [
                'id_user'           => 2,
                'username'          => 'Admin One',
                'email'             => 'admin@farmunand.local',
                'password'          => password_hash('111111', PASSWORD_DEFAULT),
                'role'              => 'admin',
                'foto'              => 'default.png',
                'failed_logins'     => 0,
                'last_failed_login' => null,
                'locked_until'      => null,
            ],
        ]);

        $this->controller = new TestableAuth($this->userRepo);

        $this->request = service('request');
        $this->controller->initController(
            $this->request,
            service('response'),
            service('logger')
        );

        TestableAuth::$forcedValidateResult = null;
        TestableAuth::$forcedErrors         = [];
    }

    protected function tearDown(): void
    {
        session()->destroy();
        parent::tearDown();
    }

    public function testHalamanLoginBisaDibuka(): void
    {
        $output = $this->controller->login();
        $this->assertIsString($output);
    }

    public function testLoginEmailTidakDitemukan(): void
    {
        $this->request->setMethod('post')
            ->setGlobal('request', [
                'email'    => 'tidakada@example.com',
                'password' => 'password123',
            ]);

        $response = $this->controller->doLogin();

        $this->assertInstanceOf(RedirectResponse::class, $response);
        $this->assertStringContainsString('/login', $response->getHeaderLine('Location'));
        $this->assertSame('Email atau password salah.', session()->getFlashdata('error'));
        $this->assertSame([], $this->userRepo->updateCalls);
    }

    public function testLoginPasswordSalahPertamaKaliNaikkanCounterTanpaLock(): void
    {
        $user                                      = $this->userRepo->usersByEmail['user01@farmunand.local'];
        $user['failed_logins']                     = 0;
        $user['locked_until']                      = null;
        $this->userRepo->usersByEmail['user01@farmunand.local'] = $user;

        $this->request->setMethod('post')
            ->setGlobal('request', [
                'email'    => 'user01@farmunand.local',
                'password' => 'salahbanget',
            ]);

        $response = $this->controller->doLogin();

        $this->assertInstanceOf(RedirectResponse::class, $response);
        $this->assertStringContainsString('/login', $response->getHeaderLine('Location'));
        $this->assertSame(
            'Email atau password salah. Sisa percobaan: 2.',
            session()->getFlashdata('error')
        );

        $this->assertCount(1, $this->userRepo->updateCalls);
        [$id, $data] = $this->userRepo->updateCalls[0];

        $this->assertSame(1, $id);
        $this->assertSame(1, $data['failed_logins']);
        $this->assertNull($data['locked_until']);
        $this->assertArrayHasKey('last_failed_login', $data);
        $this->assertNotEmpty($data['last_failed_login']);
    }

    public function testLoginPasswordSalahKetigaKaliMengunciAkun(): void
    {
        $user                                      = $this->userRepo->usersByEmail['user01@farmunand.local'];
        $user['failed_logins']                     = 2;
        $user['locked_until']                      = null;
        $this->userRepo->usersByEmail['user01@farmunand.local'] = $user;

        $this->request->setMethod('post')
            ->setGlobal('request', [
                'email'    => 'user01@farmunand.local',
                'password' => 'masihsalah',
            ]);

        $response = $this->controller->doLogin();

        $this->assertInstanceOf(RedirectResponse::class, $response);
        $this->assertStringContainsString('/login', $response->getHeaderLine('Location'));
        $this->assertSame(
            'Terlalu banyak percobaan gagal. Akun dikunci selama 15 menit.',
            session()->getFlashdata('error')
        );

        $this->assertCount(1, $this->userRepo->updateCalls);
        [$id, $data] = $this->userRepo->updateCalls[0];

        $this->assertSame(1, $id);
        $this->assertSame(3, $data['failed_logins']);
        $this->assertArrayHasKey('locked_until', $data);
        $this->assertNotEmpty($data['locked_until']);
    }

    public function testLoginSaatAkunMasihTerkunciDitolakDanTidakUpdate(): void
    {
        $user                                      = $this->userRepo->usersByEmail['user01@farmunand.local'];
        $user['failed_logins']                     = 3;
        $user['locked_until']                      = date('Y-m-d H:i:s', strtotime('+10 minutes'));
        $this->userRepo->usersByEmail['user01@farmunand.local'] = $user;

        $this->request->setMethod('post')
            ->setGlobal('request', [
                'email'    => 'user01@farmunand.local',
                'password' => 'apapun',
            ]);

        $response = $this->controller->doLogin();

        $this->assertInstanceOf(RedirectResponse::class, $response);
        $this->assertStringContainsString('/login', $response->getHeaderLine('Location'));
        $this->assertSame(
            'Akun Anda terkunci sementara karena terlalu banyak percobaan login gagal. Coba lagi beberapa saat lagi.',
            session()->getFlashdata('error')
        );
        $this->assertSame([], $this->userRepo->updateCalls);
    }

    public function testLoginBerhasilSebagaiUserResetCounterDanRedirectDashboardUser(): void
    {
        $user                                      = $this->userRepo->usersByEmail['user01@farmunand.local'];
        $user['failed_logins']                     = 2;
        $user['locked_until']                      = date('Y-m-d H:i:s', strtotime('-1 minute'));
        $this->userRepo->usersByEmail['user01@farmunand.local'] = $user;

        $this->request->setMethod('post')
            ->setGlobal('request', [
                'email'    => 'user01@farmunand.local',
                'password' => '111111',
            ]);

        $response = $this->controller->doLogin();

        $this->assertInstanceOf(RedirectResponse::class, $response);
        $this->assertStringContainsString('/dashboarduser', $response->getHeaderLine('Location'));
        $this->assertSame(1, session()->get('id_user'));
        $this->assertSame('user', session()->get('role'));
        $this->assertCount(1, $this->userRepo->updateCalls);
        [$id, $data] = $this->userRepo->updateCalls[0];
        $this->assertSame(1, $id);
        $this->assertSame(0, $data['failed_logins']);
        $this->assertNull($data['last_failed_login']);
        $this->assertNull($data['locked_until']);
    }

    public function testLoginBerhasilSebagaiAdminRedirectDashboardAdmin(): void
    {
        $this->request->setMethod('post')
            ->setGlobal('request', [
                'email'    => 'admin@farmunand.local',
                'password' => '111111',
            ]);

        $response = $this->controller->doLogin();

        $this->assertInstanceOf(RedirectResponse::class, $response);
        $this->assertStringContainsString('/dashboard', $response->getHeaderLine('Location'));

        $this->assertSame(2, session()->get('id_user'));
        $this->assertSame('admin', session()->get('role'));
    }

    public function testRegisterGagalValidasiTidakMemanggilSave(): void
    {
        TestableAuth::$forcedValidateResult = false;
        TestableAuth::$forcedErrors         = ['email' => 'Email tidak valid'];

        $this->request->setMethod('post')
            ->setGlobal('request', [
                'username'         => '',
                'email'            => 'salah',
                'password'         => '123',
                'password_confirm' => '456',
            ]);

        $response = $this->controller->doRegister();

        $this->assertInstanceOf(RedirectResponse::class, $response);
        $this->assertSame([], $this->userRepo->saveCalls);
    }

    public function testRegisterBerhasilMemanggilSaveDenganPasswordTerhash(): void
    {
        TestableAuth::$forcedValidateResult = true;
        TestableAuth::$forcedErrors         = [];

        $this->request->setMethod('post')
            ->setGlobal('request', [
                'username'         => 'UserTest',
                'email'            => 'userbaru@example.com',
                'password'         => 'password123',
                'password_confirm' => 'password123',
            ]);

        $response = $this->controller->doRegister();

        $this->assertInstanceOf(RedirectResponse::class, $response);
        $this->assertStringContainsString('/login', $response->getHeaderLine('Location'));

        $this->assertCount(1, $this->userRepo->saveCalls);
        $saved = $this->userRepo->saveCalls[0];

        $this->assertSame('UserTest', $saved['username'] ?? null);
        $this->assertSame('userbaru@example.com', $saved['email'] ?? null);
        $this->assertArrayHasKey('password', $saved);
        $this->assertNotSame('password123', $saved['password']);
        $this->assertTrue(password_verify('password123', $saved['password']));
        $this->assertSame('user', $saved['role'] ?? null);
    }

    public function testLogoutMembersihkanSessionDanRedirectKeLogin(): void
    {
        session()->set([
            'id_user'   => 99,
            'username'  => 'Dummy',
            'logged_in' => true,
        ]);

        $response = $this->controller->logout();

        $this->assertInstanceOf(RedirectResponse::class, $response);
        $this->assertStringContainsString('/login', $response->getHeaderLine('Location'));

    }
}