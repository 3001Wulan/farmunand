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
        $idUser = session()->get('id_user'); // Ambil ID user dari session
        $data['alamat'] = $this->alamatModel->where('id_user', $idUser)->findAll();
    
        // Ambil data user lain dari session
        $data['user'] = session()->get(); // Bisa diakses di view sebagai $user
    
        return view('pembeli/memilihalamat', $data);
    }
    

    // Tambah alamat baru
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

    // Pilih alamat untuk pemesanan
    public function pilih($id)
    {
        $alamat = $this->alamatModel->find($id);

        if (!$alamat) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound("Alamat tidak ditemukan");
        }

        // Simpan alamat terpilih di session agar bisa dipakai di halaman pemesanan
        session()->set('alamat_terpilih', $alamat);

        // Redirect ke halaman pemesanan
        return redirect()->to('/pemesanan');
    }
}
