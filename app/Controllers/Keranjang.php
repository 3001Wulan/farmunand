<?php

namespace App\Controllers;

use App\Models\ProdukModel;
use App\Models\UserModel;

class Keranjang extends BaseController
{
    protected $produkModel;
    protected $userModel;

    public function __construct()
    {
        $this->produkModel = new ProdukModel();
        $this->userModel   = new UserModel();
        helper(['form']);
    }

    private function ensureLogin()
    {
        if (!session()->get('id_user')) {
            return redirect()->to('/login')->with('error', 'Silakan login dulu.');
        }
        return null;
    }

    private function currentUser()
    {
        $id = session()->get('id_user');
        return $this->userModel->find($id);
    }

    private function syncCartCount()
    {
        $cart = session()->get('cart') ?? [];
        $count = 0;
        foreach ($cart as $row) { $count += (int)($row['qty'] ?? 0); }
        session()->set('cart_count', $count);
    }

    public function index()
    {
        if ($redir = $this->ensureLogin()) return $redir;

        $cart  = session()->get('cart') ?? [];
        $total = 0;
        foreach ($cart as $row) {
            $total += ((float)$row['harga']) * ((int)$row['qty']);
        }
        $this->syncCartCount();

        return view('pembeli/keranjang', [
            'cart'  => $cart,
            'total' => $total,
            'user'  => $this->currentUser(),
        ]);
    }

    public function add()
    {
        if ($redir = $this->ensureLogin()) return $redir;

        $idProduk = (int) $this->request->getPost('id_produk');
        $qty      = max(1, (int)$this->request->getPost('qty'));

        $produk = $this->produkModel->find($idProduk);
        if (!$produk) {
            return redirect()->back()->with('error', 'Produk tidak ditemukan.');
        }

        // Batasi ke stok yang tersedia
        $stok = (int)($produk['stok'] ?? 0);
        if ($stok <= 0) {
            return redirect()->back()->with('error', 'Stok produk habis.');
        }
        if ($qty > $stok) $qty = $stok;

        $cart = session()->get('cart') ?? [];

        if (isset($cart[$idProduk])) {
            $newQty = $cart[$idProduk]['qty'] + $qty;
            $cart[$idProduk]['qty'] = min($newQty, $stok);
        } else {
            $cart[$idProduk] = [
                'id_produk'   => $produk['id_produk'],
                'nama_produk' => $produk['nama_produk'],
                'harga'       => (float)$produk['harga'],
                'foto'        => $produk['foto'] ?? 'default.png',
                'qty'         => $qty,
            ];
        }

        session()->set('cart', $cart);
        $this->syncCartCount();

        return redirect()->to('/keranjang')->with('success', 'Produk masuk ke keranjang.');
    }

    public function update()
    {
        if ($redir = $this->ensureLogin()) return $redir;

        $idProduk = (int)$this->request->getPost('id_produk');
        $qty      = max(0, (int)$this->request->getPost('qty')); // 0 = hapus

        $cart = session()->get('cart') ?? [];
        if (!isset($cart[$idProduk])) {
            return redirect()->back()->with('error', 'Item tidak ada di keranjang.');
        }

        if ($qty === 0) {
            unset($cart[$idProduk]);
        } else {
            // Validasi stok saat update
            $produk = $this->produkModel->find($idProduk);
            if ($produk) {
                $stok = (int)($produk['stok'] ?? 0);
                if ($qty > $stok) $qty = $stok;
            }
            $cart[$idProduk]['qty'] = $qty;
        }

        session()->set('cart', $cart);
        $this->syncCartCount();

        return redirect()->to('/keranjang')->with('success', 'Keranjang diperbarui.');
    }

    public function remove($idProduk)
    {
        if ($redir = $this->ensureLogin()) return $redir;

        $cart = session()->get('cart') ?? [];
        unset($cart[(int)$idProduk]);

        session()->set('cart', $cart);
        $this->syncCartCount();

        return redirect()->to('/keranjang')->with('success', 'Item dihapus.');
    }

    public function clear()
    {
        if ($redir = $this->ensureLogin()) return $redir;

        session()->remove('cart');
        $this->syncCartCount();

        return redirect()->to('/keranjang')->with('success', 'Keranjang dikosongkan.');
    }
}
