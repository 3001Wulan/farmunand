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

    // ====== PROSES LOGIN ======
    public function doLogin()
    {
        $session  = session();
        $email    = $this->request->getVar('email');
        $password = $this->request->getVar('password');

        $user = $this->userModel->where('email', $email)->first();

        if ($user) {
            if (password_verify($password, $user['password'])) {
                $session->set([
                    'id_user'        => $user['id_user'],
                    'username'  => $user['username'],
                    'email'     => $user['email'],
                    'role'      => $user['role'],
                    'foto'      => $user['foto'],
                    'logged_in' => true,
                ]);


                // Redirect sesuai role
                if ($user['role'] === 'user') {
                    return redirect()->to('/dashboarduser'); // halaman untuk user/admin
                } elseif ($user['role'] === 'admin') {
                    return redirect()->to('/dashboard'); // halaman untuk pembeli
                } else { 
                    $session->setFlashdata('error', 'Role tidak dikenali!');
                    return redirect()->to('/login');
                }
            } else {
                $session->setFlashdata('error', 'Password salah!');
                return redirect()->to('/login');
            }
        } else {
            $session->setFlashdata('error', 'Email tidak ditemukan!');
            return redirect()->to('/login');
        }
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
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
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

    // FORM FORGOT PASSWORD
    public function forgotPassword()
    {
        return view('auth/forgot_password');
    }

    // KIRIM RESET LINK VIA EMAIL
    public function sendResetLink()
    {
        $email = $this->request->getVar('email');
        $user = $this->userModel->where('email', $email)->first();

        if (!$user) {
            return redirect()->back()->with('error', 'Email tidak ditemukan.');
        }

        // buat token
        $token = bin2hex(random_bytes(50));
        $expires = date('Y-m-d H:i:s', strtotime('+1 hour'));

        // simpan ke tabel users
        $this->userModel->update($user['id_user'], [
            'reset_token' => $token,
            'reset_expires' => $expires
        ]);

        // buat link reset
        $resetLink = base_url("reset-password/$token");

        // kirim email
        $emailService = \Config\Services::email();
        $emailService->setTo($email);
        $emailService->setSubject('Reset Password FarmUnand');
        $emailService->setMessage("Klik link berikut untuk reset password: <a href='$resetLink'>$resetLink</a>");
        $emailService->send();

        return redirect()->to('/login')->with('success', 'Link reset password sudah dikirim ke email.');
    }

    // FORM RESET PASSWORD
    public function resetPassword($token)
    {
        $user = $this->userModel->where('reset_token', $token)
                                ->where('reset_expires >=', date('Y-m-d H:i:s'))
                                ->first();

        if (!$user) {
            return redirect()->to('/login')->with('error', 'Token tidak valid atau kadaluarsa.');
        }

        return view('auth/reset_password', ['token' => $token]);
    }

    // PROSES RESET PASSWORD
    public function doResetPassword()
    {
        $token = $this->request->getVar('token');
        $password = $this->request->getVar('password');
        $confirm = $this->request->getVar('password_confirm');

        if ($password !== $confirm) {
            return redirect()->back()->with('error', 'Password tidak sama.');
        }

        $user = $this->userModel->where('reset_token', $token)
                                ->where('reset_expires >=', date('Y-m-d H:i:s'))
                                ->first();

        if (!$user) {
            return redirect()->to('/login')->with('error', 'Token tidak valid atau kadaluarsa.');
        }

        // update password
        $this->userModel->update($user['id_user'], [
            'password' => password_hash($password, PASSWORD_DEFAULT),
            'reset_token' => null,
            'reset_expires' => null
        ]);

        return redirect()->to('/login')->with('success', 'Password berhasil direset. Silakan login.');
    }
}
