<?php

namespace App\Controllers;

use App\Models\UserModel;
use App\Models\ProdukModel;
use App\Models\PesanModel;

class ProfileAdmin extends BaseController
{
    protected $userModel;
    protected $produkModel;
    protected $pesanModel;

    public function __construct()
    {
        $this->userModel  = new UserModel();
    }

    public function index()
    {
        $session = session();
        $userId = $session->get('id_user');

        if (!$userId) {
            return redirect()->to('/login')->with('error', 'Silakan login dulu.');
        }

        $user = $this->userModel->find($userId);

        $data = [
            'title'        => 'Profil Admin',
            'user'         => $user,
        ];

        return view('Admin/profile_admin', $data);
    }

    public function edit()
    {
        $session = session();
        $userId = $session->get('id_user');

        if (!$userId) {
            return redirect()->to('/login')->with('error', 'Silakan login dulu.');
        }

        $user = $this->userModel->find($userId);

        $data = [
            'title' => 'Edit Profil Admin',
            'user'  => $user,
        ];

        return view('Admin/edit_profile_admin', $data);
    }

    public function update()
    {
        $session = session();
        $userId = $session->get('id_user');
        $user   = $this->userModel->find($userId);

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

            if ($user['foto'] && $user['foto'] !== 'default.png' && file_exists('uploads/profile/' . $user['foto'])) {
                unlink('uploads/profile/' . $user['foto']);
            }

            $dataUpdate['foto'] = $newName;
            $session->set('foto', $newName);
        }

        $this->userModel->update($userId, $dataUpdate);

        $session->setFlashdata('success', 'Profil Admin berhasil diperbarui.');
        return redirect()->to('/profileadmin');
    }
}
