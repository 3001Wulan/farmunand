<?php

namespace App\Controllers;

use App\Models\UserModel;

class ManajemenAkunUser extends BaseController
{
        public function index()
    {
        $model = new UserModel();
        $data['users'] = $model->findAll();
        $userId = session()->get('id_user');   // ✅ sudah benar
        $user   = $model->find($userId);

        $data = [
            'users' => $data['users'],
            'user'  => $user 
        ];

        return view('Admin/manajemenakunuser', $data);
    }

    // Form edit user
    public function edit($id_user) // ✅ param jadi id_user
    {
        $model = new UserModel();
        $data['user'] = $model->find($id_user);

        return view('admin/edit_manajemenakunuser', $data);
    }

    // Update user
    public function update($id_user) // ✅ param jadi id_user
    {
        $model = new UserModel();
        $model->update($id_user, [
            'nama'   => $this->request->getPost('nama'),
            'email'  => $this->request->getPost('email'),
            'no_hp'  => $this->request->getPost('no_hp'),
            'status' => $this->request->getPost('status'),
        ]);

        return redirect()->to('/manajemenakunuser');
    }

    // Hapus user
    public function delete($id_user) // ✅ param jadi id_user
    {
        $model = new UserModel();
        $model->delete($id_user);

        return redirect()->to('/manajemenakunuser');
    }
}
