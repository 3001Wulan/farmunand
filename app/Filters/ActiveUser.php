<?php
namespace App\Filters;

use App\Models\UserModel;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use CodeIgniter\Filters\FilterInterface;

class ActiveUser implements FilterInterface
{
    public function before(RequestInterface $request, $arguments = null)
    {
        $id = (int) session()->get('id_user');
        if ($id <= 0) return; // biar filter 'auth' yg handle login

        $user = (new UserModel())->find($id);
        $status = strtolower(trim((string)($user['status'] ?? '')));

        if ($status !== 'aktif') {
            return redirect()->to('/')->with('error', 'Akun Anda nonaktif. Anda hanya dapat melihat produk.');
        }
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {}
}
