<?php

namespace App\Controllers;

use App\Models\UserModel;

class Profile extends BaseController
{
    protected $userModel;

    public function __construct()
    {
        $this->userModel = new UserModel();
    }

    // ===== HALAMAN PROFIL =====
    public function index()
    {
        $session = session();
        $userId = $session->get('id_user');

        if (!$userId) {
            return redirect()->to('/login')->with('error', 'Silakan login dulu.');
        }

        $user = $this->userModel->find($userId);

        $data = [
            'title' => 'Profil Saya',
            'user'  => $user,
        ];

        return view('Pembeli/profile', $data);
    }

    // ===== FORM EDIT PROFIL =====
    public function edit()
    {
        $session = session();
        $userId = $session->get('id_user');

        if (!$userId) {
            return redirect()->to('/login')->with('error', 'Silakan login dulu.');
        }

        $user = $this->userModel->find($userId);

        $data = [
            'title' => 'Edit Profil',
            'user'  => $user,
        ];

        return view('Pembeli/edit_profile', $data);
    }

    // ===== PROSES UPDATE PROFIL =====
    public function update()
    {
        $session = session();
        $userId = $session->get('id_user');

        if (!$userId) {
            return redirect()->to('/login')->with('error', 'Silakan login dulu.');
        }

        $user = $this->userModel->find($userId);

        $rules = [
            'username' => 'required|min_length[3]',
            'nama'     => 'required|min_length[3]',
            'email'    => "required|valid_email|is_unique[users.email,id,{$userId}]",
            'no_hp'    => 'permit_empty|min_length[10]|max_length[15]',
            'foto'     => 'is_image[foto]|mime_in[foto,image/jpg,image/jpeg,image/png]'
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $dataUpdate = [
            'username' => $this->request->getPost('username'),
            'nama'     => $this->request->getPost('nama'),
            'email'    => $this->request->getPost('email'),
            'no_hp'    => $this->request->getPost('no_hp'),
        ];

        // Upload foto jika ada
        $file = $this->request->getFile('foto');
        if ($file && $file->isValid() && !$file->hasMoved()) {
            $newName = $file->getRandomName();
            $file->move('uploads/profile', $newName);

            if ($user['foto'] && $user['foto'] !== 'default.png' && file_exists('uploads/profile/'.$user['foto'])) {
                unlink('uploads/profile/'.$user['foto']);
            }

            $dataUpdate['foto'] = $newName;
        }

        $this->userModel->update($userId, $dataUpdate);

        return redirect()->to('/profile')->with('success', 'Profil berhasil diperbarui!');
    }
}
