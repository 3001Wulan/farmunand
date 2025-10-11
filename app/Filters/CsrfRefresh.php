<?php
namespace App\Filters;

use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use CodeIgniter\Filters\FilterInterface;

class CsrfRefresh implements FilterInterface
{
    public function before(RequestInterface $request, $arguments = null) {}
    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
        // header default CSRF di CI4: X-CSRF-TOKEN
        $response->setHeader('X-CSRF-TOKEN', csrf_hash());
    }
}
