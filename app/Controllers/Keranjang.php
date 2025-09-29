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

    /* ===== Helpers ===== */

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

    // Key session per-user
    private function cartKey(): string
    {
        return 'cart_u_' . (session()->get('id_user') ?? 0);
    }

    private function countKey(): string
    {
        return 'cart_count_u_' . (session()->get('id_user') ?? 0);
    }

    private function getCart(): array
    {
        return session()->get($this->cartKey()) ?? [];
    }

    private function putCart(array $cart): void
    {
        session()->set($this->cartKey(), $cart);

        // hitung total qty untuk badge
        $count = 0;
        foreach ($cart as $row) {
            $count += (int)($row['qty'] ?? 0);
        }
        session()->set($this->countKey(), $count);
    }

    private function syncCartCount(): void
    {
        $this->putCart($this->getCart());
    }

    /* ===== Actions ===== */

    public function index()
    {
        if ($redir = $this->ensureLogin()) return $redir;

        $cart  = $this->getCart();
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

        // Validasi stok
        $stok = (int)($produk['stok'] ?? 0);
        if ($stok <= 0) {
            return redirect()->back()->with('error', 'Stok produk habis.');
        }
        if ($qty > $stok) $qty = $stok;

        $cart = $this->getCart();

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

        $this->putCart($cart);

        return redirect()->to('/keranjang')->with('success', 'Produk masuk ke keranjang.');
    }

    public function update()
    {
        if ($redir = $this->ensureLogin()) return $redir;

        $idProduk = (int)$this->request->getPost('id_produk');
        $qty      = max(0, (int)$this->request->getPost('qty')); // 0 = hapus

        $cart = $this->getCart();
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

        $this->putCart($cart);

        return redirect()->to('/keranjang')->with('success', 'Keranjang diperbarui.');
    }

    public function remove($idProduk)
    {
        if ($redir = $this->ensureLogin()) return $redir;

        $cart = $this->getCart();
        unset($cart[(int)$idProduk]);

        $this->putCart($cart);

        return redirect()->to('/keranjang')->with('success', 'Item dihapus.');
    }

    public function clear()
    {
        if ($redir = $this->ensureLogin()) return $redir;

        session()->remove($this->cartKey());
        session()->remove($this->countKey());

        return redirect()->to('/keranjang')->with('success', 'Keranjang dikosongkan.');
    }
}
