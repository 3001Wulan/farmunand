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

    public function index()
    {
        $userId = session()->get('id_user');
        $user   = $this->userModel->find($userId);

        $keyword  = $this->request->getVar('keyword');
        $kategori = $this->request->getVar('kategori');

        if ($keyword || $kategori) {
            $builder = $this->produkModel->builder();

            if (!empty($keyword)) {
                $builder->groupStart()
                        ->like('nama_produk', $keyword)
                        ->orLike('deskripsi', $keyword)
                        ->groupEnd();
            }
            if (!empty($kategori)) {
                $builder->where('kategori', $kategori);
            }

            $produk = $builder->get()->getResultArray();
        } else {
            $produk = $this->produkModel->findAll();
        }

        $data = [
            'title'        => 'Manajemen Produk',
            'produk'       => $produk,
            'keyword'      => $keyword,
            'kategori'     => $kategori,
            'kategoriList' => $this->produkModel->getKategoriList(),
            'user'         => $user
        ];

        return view('admin/produk/index', $data);
    }

    public function create()
    {
        $userId = session()->get('id_user');
        $user   = $this->userModel->find($userId);

        return view('admin/produk/create', [
            'title' => 'Tambah Produk',
            'user'  => $user
        ]);
    }

    public function store()
    {
        $file = $this->request->getFile('foto');
        $fotoName = 'default.png';

        if ($file && $file->isValid() && !$file->hasMoved()) {

            $maxBytes = 10 * 1024 * 1024; 
            if ($file->getSize() > $maxBytes) {
                return redirect()->back()
                    ->withInput()
                    ->with('error', 'Ukuran file foto maksimal 10MB.');
            }

            $fotoName = $file->getRandomName();
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



    // === Form Edit Produk
    public function edit($id_produk)
    {
        $produk = $this->produkModel->find($id_produk);
        if (!$produk) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound("Produk dengan ID $id_produk tidak ditemukan.");
        }

        $userId = session()->get('id_user');
        $user   = $this->userModel->find($userId);

        return view('admin/produk/edit', [
            'title'  => 'Edit Produk',
            'produk' => $produk,
            'user'   => $user
        ]);
    }

    public function update($id_produk)
    {
        $produk = $this->produkModel->find($id_produk);
        if (!$produk) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound("Produk dengan ID $id_produk tidak ditemukan.");
        }

        $file = $this->request->getFile('foto');
        if ($file && $file->isValid() && !$file->hasMoved()) {

            $maxBytes = 10 * 1024 * 1024; 
            if ($file->getSize() > $maxBytes) {
                return redirect()->back()
                    ->withInput()
                    ->with('error', 'Ukuran file foto maksimal 10MB.');
            }

            $fotoName = $file->getRandomName();
            $file->move('uploads/produk', $fotoName);

            if ($produk['foto'] !== 'default.png' && file_exists('uploads/produk/' . $produk['foto'])) {
                unlink('uploads/produk/' . $produk['foto']);
            }
        } else {
            $fotoName = $produk['foto'];
        }



        $this->produkModel->update($id_produk, [
            'nama_produk' => $this->request->getVar('nama_produk'),
            'deskripsi'   => $this->request->getVar('deskripsi'),
            'foto'        => $fotoName,
            'harga'       => $this->request->getVar('harga'),
            'stok'        => $this->request->getVar('stok'),
        ]);

        return redirect()->to('/admin/produk')->with('success', 'Produk berhasil diperbarui!');
    }

    public function delete($id_produk)
    {
        $produk = $this->produkModel->find($id_produk);

        if ($produk) {
            if ($produk['foto'] !== 'default.png' && file_exists('uploads/produk/' . $produk['foto'])) {
                unlink('uploads/produk/' . $produk['foto']);
            }

            $this->produkModel->where('id_produk', $id_produk)->delete();
        }

        return redirect()->to('/admin/produk')->with('success', 'Produk berhasil dihapus!');
    }
}
