<?php

namespace App\Controllers;

use App\Models\UserModel;

class ManajemenAkunUser extends BaseController
{
    protected $userModel;

    public function __construct()
    {
        $this->userModel = new UserModel();
    }
    public function index()
    {
        $model   = new UserModel();

        // --- Ambil filter dari query string ---
        $keyword = trim((string) $this->request->getGet('keyword'));
        $role    = trim((string) $this->request->getGet('role'));

        // --- Builder dari model 'users' ---
        $builder = $model->builder(); // sama dengan $model->table('users')

        // Filter keyword: nama/email/username
        if ($keyword !== '') {
            $builder->groupStart()
                ->like('nama', $keyword)
                ->orLike('email', $keyword)
                ->orLike('username', $keyword)
            ->groupEnd();
        }

        // Filter role: 'admin' / 'user'
        if ($role !== '') {
            $builder->where('role', $role);
        }

        $builder->orderBy('created_at', 'DESC');

        // Ambil data user hasil filter
        $users  = $builder->get()->getResultArray();

        // Data user yang login (untuk sidebar/header di view)
        $userId = session()->get('id_user');
        $user   = $model->find($userId);

        // Kirim data + nilai filter ke view (biar sticky di form)
        return view('Admin/manajemenakunuser', [
            'users'   => $users,
            'user'    => $user,
            'keyword' => $keyword,
            'role'    => $role,
        ]);
    }

    // Form edit user
    public function edit($id_user)
    {
        $model       = new UserModel();
        $data['user'] = $model->find((int) $id_user);

        return view('admin/edit_manajemenakunuser', $data);
    }

    // Update user
    public function update($id_user)
    {
        $model = new UserModel();
        $model->update((int) $id_user, [
            'nama'   => $this->request->getPost('nama'),
            'email'  => $this->request->getPost('email'),
            'no_hp'  => $this->request->getPost('no_hp'),
            'status' => $this->request->getPost('status'),
        ]);

        return redirect()->to('/manajemenakunuser')->with('success', 'User diperbarui.');
    }

    public function delete($id_user)
    {
        $userModel = new UserModel();
        $id = (int) $id_user;
    
        if ($id <= 0) {
            return redirect()->back()->with('error', 'ID tidak valid.');
        }
    
        // Cegah hapus diri sendiri
        if ((int) session()->get('id_user') === $id) {
            return redirect()->back()->with('error', 'Tidak bisa menghapus akun sendiri.');
        }
    
        // 🔹 Gunakan fungsi dari model
        if ($userModel->hasPendingOrders($id)) {
            return redirect()->back()->with('error', 'User masih memiliki pesanan yang belum diselesaikan. Hapus dibatalkan.');
        }
    
        // Terima POST/DELETE (aman)
        $method = strtolower($this->request->getMethod());
        if (!in_array($method, ['post', 'delete'], true)) {
            if (!$userModel->find($id)) {
                return redirect()->back()->with('error', 'User tidak ditemukan.');
            }
    
            $userModel->delete($id);
            return redirect()->to('/manajemenakunuser')->with('success', 'User dihapus (gunakan POST agar lebih aman).');
        }
    
        if (!$userModel->find($id)) {
            return redirect()->back()->with('error', 'User tidak ditemukan.');
        }
    
        $userModel->delete($id);
        return redirect()->to('/manajemenakunuser')->with('success', 'User dihapus.');
    }
    
}