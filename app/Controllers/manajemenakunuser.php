<?php

namespace App\Controllers;

use App\Models\UserModel;

class ManajemenAkunUser extends BaseController
{
    // Tampilkan daftar user
    public function index()
    {
        $model = new UserModel();
        $data['users'] = $model->findAll();
        $userId = session()->get('id_user');   
        $user   = $model->find($userId);

        $data = [
            'users'           => $data['users'],
            'user'            => $user 
        ];

        return view('Admin/manajemenakunuser', $data);
    }

    // Form tambah user
    public function create()
    {
        return view('admin/manajemenakunuser/create');
    }

    // Simpan user baru
    public function store()
    {
        $model = new UserModel();
        $model->insert([
            'nama'   => $this->request->getPost('nama'),
            'email'  => $this->request->getPost('email'),
            'no_hp'  => $this->request->getPost('no_hp'),
            'status' => $this->request->getPost('status'),
        ]);

        return redirect()->to('/manajemenakunuser');
    }

    // Form edit user
    public function edit($id)
    {
        $model = new UserModel();
        $data['user'] = $model->find($id);

        return view('admin/edit_manajemenakunuser', $data);
    }

    // Update user
    public function update($id)
    {
        $model = new UserModel();
        $model->update($id, [
            'nama'   => $this->request->getPost('nama'),
            'email'  => $this->request->getPost('email'),
            'no_hp'  => $this->request->getPost('no_hp'),
            'status' => $this->request->getPost('status'),
        ]);

        return redirect()->to('/manajemenakunuser');
    }

    // Hapus user
    public function delete($id)
    {
        $model = new UserModel();
        $model->delete($id);

        return redirect()->to('/manajemenakunuser');
    }
}
