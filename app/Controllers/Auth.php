<?php

namespace App\Controllers;

use App\Models\UserModel;

class Auth extends BaseController
{
    protected $userModel;

    public function __construct()
    {
        $this->userModel = new UserModel();
        helper(['form', 'url']);
    }

    // ====== FORM LOGIN ======
    public function login()
    {
        return view('auth/login');
    }

    // ====== PROSES LOGIN (dengan lock 3x gagal) ======
    public function doLogin()
{
    $session  = session();
    $email    = trim((string) $this->request->getVar('email'));
    $password = (string) $this->request->getVar('password');

    // Cari user berdasarkan email
    $user = $this->userModel->where('email', $email)->first();

    // Jangan bocorkan apakah email ada atau tidak
    if (!$user) {
        $session->setFlashdata('error', 'Email atau password salah.');
        return redirect()->to('/login')->withInput();
    }

    $now = date('Y-m-d H:i:s');

    // Cek apakah akun sedang dikunci
    if (!empty($user['locked_until']) && $user['locked_until'] > $now) {
        $session->setFlashdata(
            'error',
            'Akun Anda terkunci sementara karena terlalu banyak percobaan login gagal. Coba lagi beberapa saat lagi.'
        );
        return redirect()->to('/login')->withInput();
    }

    // Verifikasi password
    if (!password_verify($password, $user['password'])) {
        // Hitung gagal login
        $failed = (int) ($user['failed_logins'] ?? 0) + 1;

        // Jika gagal >= 3 → kunci 15 menit
        if ($failed >= 3) {
            $lockMinutes = 15;
            $lockUntil   = date('Y-m-d H:i:s', strtotime("+{$lockMinutes} minutes"));

            $this->userModel->update($user['id_user'], [
                'failed_logins'     => $failed,
                'last_failed_login' => $now,
                'locked_until'      => $lockUntil,
            ]);

            $session->setFlashdata(
                'error',
                "Terlalu banyak percobaan gagal. Akun dikunci selama {$lockMinutes} menit."
            );
        } else {
            // Belum mencapai batas → hanya simpan counter & timestamp
            $this->userModel->update($user['id_user'], [
                'failed_logins'     => $failed,
                'last_failed_login' => $now,
                'locked_until'      => null,
            ]);

            $sisa = 3 - $failed;
            $session->setFlashdata(
                'error',
                "Email atau password salah. Sisa percobaan: {$sisa}."
            );
        }

        return redirect()->to('/login')->withInput();
    }

    // Password benar → reset counter & unlock
    $this->userModel->update($user['id_user'], [
        'failed_logins'     => 0,
        'last_failed_login' => null,
        'locked_until'      => null,
    ]);

    // Set session login
    $session->set([
        'id_user'   => $user['id_user'],
        'username'  => $user['username'],
        'email'     => $user['email'],
        'role'      => $user['role'],
        'foto'      => $user['foto'],
        'logged_in' => true,
    ]);

    // Redirect sesuai role
    if ($user['role'] === 'admin') {
        return redirect()->to('/dashboard');
    }

    if ($user['role'] === 'user') {
        return redirect()->to('/dashboarduser');
    }

    // Fallback kalau role aneh
    $session->setFlashdata('error', 'Role tidak dikenali.');
    return redirect()->to('/login');
}


    // ====== FORM REGISTER ======
    public function register()
    {
        return view('auth/register');
    }

    // ====== PROSES REGISTER ======
    public function doRegister()
    {
        $rules = [
            'username'          => 'required|min_length[3]',
            'email'             => 'required|valid_email|is_unique[users.email]',
            'password'          => 'required|min_length[6]',
            'password_confirm'  => 'matches[password]',
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()
                ->withInput()
                ->with('errors', $this->validator->getErrors());
        }

        $this->userModel->save([
            'username' => $this->request->getVar('username'),
            'email'    => $this->request->getVar('email'),
            'password' => password_hash($this->request->getVar('password'), PASSWORD_DEFAULT),
            'role'     => 'user',
        ]);

        session()->setFlashdata('success', 'Registrasi berhasil, silakan login.');
        return redirect()->to('/login');
    }

    // ====== LOGOUT ======
    public function logout()
    {
        session()->destroy();
        return redirect()->to('/login');
    }

    // ====== LUPA PASSWORD & RESET (tetap sama) ======
    public function forgotPassword()
    {
        return view('auth/forgot_password');
    }

    public function sendResetLink()
    {
        $email = $this->request->getVar('email');
        $user  = $this->userModel->where('email', $email)->first();

        if (!$user) {
            return redirect()->back()->with('error', 'Email tidak ditemukan.');
        }

        $token   = bin2hex(random_bytes(50));
        $expires = date('Y-m-d H:i:s', strtotime('+1 hour'));

        $this->userModel->update($user['id_user'], [
            'reset_token'   => $token,
            'reset_expires' => $expires
        ]);

        $resetLink   = base_url("reset-password/$token");
        $emailSender = \Config\Services::email();
        $emailSender->setTo($email);
        $emailSender->setSubject('Reset Password FarmUnand');
        $emailSender->setMessage("Klik link berikut untuk reset password: <a href='$resetLink'>$resetLink</a>");
        $emailSender->send();

        return redirect()->to('/login')->with('success', 'Link reset password sudah dikirim ke email.');
    }

    public function resetPassword($token)
    {
        $user = $this->userModel
            ->where('reset_token', $token)
            ->where('reset_expires >=', date('Y-m-d H:i:s'))
            ->first();

        if (!$user) {
            return redirect()->to('/login')->with('error', 'Token tidak valid atau kadaluarsa.');
        }

        return view('auth/reset_password', ['token' => $token]);
    }

    public function doResetPassword()
    {
        $token    = $this->request->getVar('token');
        $password = $this->request->getVar('password');
        $confirm  = $this->request->getVar('password_confirm');

        if ($password !== $confirm) {
            return redirect()->back()->with('error', 'Password tidak sama.');
        }

        $user = $this->userModel
            ->where('reset_token', $token)
            ->where('reset_expires >=', date('Y-m-d H:i:s'))
            ->first();

        if (!$user) {
            return redirect()->to('/login')->with('error', 'Token tidak valid atau kadaluarsa.');
        }

        $this->userModel->update($user['id_user'], [
            'password'      => password_hash($password, PASSWORD_DEFAULT),
            'reset_token'   => null,
            'reset_expires' => null
        ]);

        return redirect()->to('/login')->with('success', 'Password berhasil direset. Silakan login.');
    }
}
