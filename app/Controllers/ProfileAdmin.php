<?php

namespace App\Controllers;

use App\Models\UserModel;

class ProfileAdmin extends BaseController
{
    protected $userModel;

    public function __construct()
    {
        $this->userModel = new UserModel();
    }

    public function index()
    {
        $userId = session()->get('id_user');
        if (!$userId) {
            return redirect()->to('/login')->with('error', 'Silakan login dulu.');
        }

        $data = [
            'title' => 'Profil Admin',
            'user'  => $this->userModel->find($userId),
        ];

        return view('Admin/profile_admin', $data);
    }

    public function edit()
    {
        $userId = session()->get('id_user');
        if (!$userId) {
            return redirect()->to('/login')->with('error', 'Silakan login dulu.');
        }

        $data = [
            'title' => 'Edit Profil Admin',
            'user'  => $this->userModel->find($userId),
        ];

        return view('Admin/edit_profile_admin', $data);
    }

    public function update()
    {
        $session = session();
        $userId  = $session->get('id_user');
        $user    = $this->userModel->find($userId);

        $dataUpdate = [
            'username' => $this->request->getPost('username'),
            'nama'     => $this->request->getPost('nama'),
            'email'    => $this->request->getPost('email'),
            'no_hp'    => $this->request->getPost('no_hp'),
        ];

        $fileFoto = $this->request->getFile('foto');
        if ($fileFoto && $fileFoto->isValid() && !$fileFoto->hasMoved()) {
            $newName = $fileFoto->getRandomName();
            $fileFoto->move('uploads/profile', $newName);

            if ($user['foto'] && $user['foto'] !== 'default.png' && file_exists('uploads/profile/'.$user['foto'])) {
                unlink('uploads/profile/'.$user['foto']);
            }

            $dataUpdate['foto'] = $newName;
            $session->set('foto', $newName);
        }

        $this->userModel->update($userId, $dataUpdate);

        $session->setFlashdata('success', 'Profil Admin berhasil diperbarui.');
        return redirect()->to('/profileadmin');
    }
}
