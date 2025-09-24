<?php

namespace App\Controllers;

use App\Models\ProdukModel;
use App\Models\UserModel;

class ProdukAdmin extends BaseController
{
    protected $produkModel;
    protected $userModel;

    public function __construct()
    {
        $this->produkModel = new ProdukModel();
        $this->userModel   = new UserModel();
    }

    // Tampilkan semua produk + search
    public function index()
    {
        $userId = session()->get('id_user');
        $user   = $this->userModel->find($userId);

        $keyword = $this->request->getVar('keyword');
        if ($keyword) {
            $produk = $this->produkModel->searchProduk($keyword);
        } else {
            $produk = $this->produkModel->findAll();
        }

        $data = [
            'title'   => 'Manajemen Produk',
            'produk'  => $produk,
            'keyword' => $keyword,
            'user'    => $user
        ];

        return view('admin/produk/index', $data);
    }

    // Form tambah produk
    public function create()
    {
        $userId = session()->get('id_user');
        $user   = $this->userModel->find($userId);

        return view('admin/produk/create', [
            'title' => 'Tambah Produk',
            'user'  => $user
        ]);
    }

    // Simpan produk baru
    public function store()
    {
        $file = $this->request->getFile('foto');
        $fotoName = $file->isValid() && !$file->hasMoved()
            ? $file->getRandomName()
            : 'default.png';

        if ($file->isValid() && !$file->hasMoved()) {
            $file->move('uploads/produk', $fotoName);
        }

        $this->produkModel->save([
            'nama_produk' => $this->request->getVar('nama_produk'),
            'deskripsi'   => $this->request->getVar('deskripsi'),
            'foto'        => $fotoName,
            'harga'       => $this->request->getVar('harga'),
            'stok'        => $this->request->getVar('stok'),
            'rating'      => 0,
        ]);

        return redirect()->to('/admin/produk')->with('success', 'Produk berhasil ditambahkan!');
    }

    // Form edit produk
    public function edit($id)
    {
        $produk = $this->produkModel->find($id);
        if (!$produk) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound("Produk dengan ID $id tidak ditemukan.");
        }

        $userId = session()->get('id_user');
        $user   = $this->userModel->find($userId);

        $data = [
            'title'  => 'Edit Produk',
            'produk' => $produk,
            'user'   => $user
        ];

        return view('admin/produk/edit', $data);
    }

    // Update produk
    public function update($id)
    {
        $produk = $this->produkModel->find($id);
        if (!$produk) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound("Produk dengan ID $id tidak ditemukan.");
        }

        $file = $this->request->getFile('foto');
        if ($file && $file->isValid() && !$file->hasMoved()) {
            $fotoName = $file->getRandomName();
            $file->move('uploads/produk', $fotoName);

            // hapus foto lama kalau bukan default
            if ($produk['foto'] !== 'default.png' && file_exists('uploads/produk/' . $produk['foto'])) {
                unlink('uploads/produk/' . $produk['foto']);
            }
        } else {
            $fotoName = $produk['foto'];
        }

        $this->produkModel->update($id, [
            'nama_produk' => $this->request->getVar('nama_produk'),
            'deskripsi'   => $this->request->getVar('deskripsi'),
            'foto'        => $fotoName,
            'harga'       => $this->request->getVar('harga'),
            'stok'        => $this->request->getVar('stok'),
        ]);

        return redirect()->to('/admin/produk')->with('success', 'Produk berhasil diperbarui!');
    }

    // Hapus produk
    public function delete($id)
    {
        $produk = $this->produkModel->find($id);

        if ($produk) {
            // hapus foto kalau bukan default
            if ($produk['foto'] !== 'default.png' && file_exists('uploads/produk/' . $produk['foto'])) {
                unlink('uploads/produk/' . $produk['foto']);
            }

            // gunakan where explicit
            $this->produkModel->where('id_produk', $id)->delete();
        }

        return redirect()->to('/admin/produk')->with('success', 'Produk berhasil dihapus!');
    }
}
