<?php

namespace App\Controllers;

use App\Models\AlamatModel;

class MemilihAlamat extends BaseController
{
    protected $alamatModel;

    public function __construct()
    {
        $this->alamatModel = new AlamatModel();
    }

    // Menampilkan halaman memilih alamat beserta modal tambah alamat
    public function index()
    {
        $userId = 1; // sementara, ganti dengan session user jika sudah login
        $data['alamat'] = $this->alamatModel->where('id_user', $userId)->findAll();

        return view('pembeli/memilihalamat', $data);
    }

    public function tambah()
{
    if ($this->request->getMethod() === 'POST') {
        $data = [
            'id_user'       => 1, // ganti dengan session()->get('id_user') nanti
            'nama_penerima' => $this->request->getPost('nama_penerima'),
            'no_telepon'    => $this->request->getPost('no_telepon'),
            'kota'          => $this->request->getPost('kota'),
            'provinsi'      => $this->request->getPost('provinsi'),
            'kode_pos'      => $this->request->getPost('kode_pos'),
        ];

        $this->alamatModel->save($data);

        return redirect()->to('/memilihalamat');
    }

    return redirect()->to('/memilihalamat');
}


    // Ubah alamat
    public function ubah($id)
    {
        $alamat = $this->alamatModel->find($id);

        if (!$alamat) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound("Alamat tidak ditemukan");
        }

        if ($this->request->getMethod() === 'post') {
            $this->alamatModel->update($id, [
                'nama_penerima' => $this->request->getPost('nama_penerima'),
                'no_telepon' => $this->request->getPost('no_telepon'),
                'kota' => $this->request->getPost('kota'),
                'provinsi' => $this->request->getPost('provinsi'),
                'kode_pos' => $this->request->getPost('kode_pos'),
            ]);

            return redirect()->to('/memilihalamat');
        }

        return view('pembeli/ubahalamat', ['alamat' => $alamat]);
    }
}
