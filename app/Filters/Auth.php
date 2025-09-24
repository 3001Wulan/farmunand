<?php

namespace App\Filters;

use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use CodeIgniter\Filters\FilterInterface;

class Auth implements FilterInterface
{
    public function before(RequestInterface $request, $arguments = null)
    {
        // Cek apakah sudah login
        if (! session()->get('logged_in')) {   // ✅ sesuaikan dengan Auth controller
            return redirect()->to('/login');
        }

        // Kalau filter dipakai untuk admin
        if ($arguments && in_array('admin', $arguments)) {
            if (session()->get('role') !== 'admin') {
                return redirect()->to('/'); // kalau bukan admin lempar ke home
            }
        }

        // Kalau filter dipakai untuk user
        if ($arguments && in_array('user', $arguments)) {
            if (session()->get('role') !== 'user') {
                return redirect()->to('/'); // kalau bukan user lempar ke home
            }
        }
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
        // Tidak perlu aksi setelah response
    }
}
