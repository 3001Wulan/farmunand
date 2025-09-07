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
                    'id'        => $user['id'],
                    'username'  => $user['username'],
                    'email'     => $user['email'],
                    'role'      => $user['role'],
                    'logged_in' => true,
                ]);
                return redirect()->to('/dashboard'); // halaman setelah login sukses
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
            'password_confirm'  => 'matches[password]'
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $this->userModel->save([
            'username' => $this->request->getVar('username'),
            'email'    => $this->request->getVar('email'),
            'password' => password_hash($this->request->getVar('password'), PASSWORD_DEFAULT),
            'role'     => 'user', // default role
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
}
