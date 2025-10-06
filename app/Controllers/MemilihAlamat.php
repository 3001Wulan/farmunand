<?php

namespace App\Controllers;

use App\Models\AlamatModel;
use App\Models\UserModel;
use App\Models\ProdukModel;

class MemilihAlamat extends BaseController
{
    protected $alamatModel;
    protected $userModel;
    protected $produkModel;

    public function __construct()
    {
        $this->alamatModel = new AlamatModel();
        $this->userModel   = new UserModel();
        $this->produkModel = new ProdukModel();
    }

    // === Halaman memilih alamat
    public function index()
    {
        $idUser = session()->get('id_user');
        $alamat = $this->alamatModel
                        ->where('id_user', $idUser)
                        ->orderBy('id_alamat', 'DESC')
                        ->findAll();

        $user = $this->userModel->find($idUser);

        return view('pembeli/memilihalamat', [
            'alamat'  => $alamat,
            'user'    => $user,
            'session' => session()
        ]);
    }

    // === Tambah alamat baru
    public function tambah()
    {
        if ($this->request->getMethod() === 'POST') {
            $idUser = session()->get('id_user');

            $rules = [
                'nama_penerima' => 'required',
                'jalan'         => 'required',
                'no_telepon'    => 'required',
                'kota'          => 'required',
                'provinsi'      => 'required',
                'kode_pos'      => 'required'
            ];

            if (!$this->validate($rules)) {
                return redirect()->to('/memilihalamat')->with('error', 'Semua field harus diisi.');
            }

            // nonaktifkan semua alamat lama
            $this->alamatModel->where('id_user', $idUser)->set(['aktif' => 0])->update();

            $this->alamatModel->save([
                'id_user'       => $idUser,
                'nama_penerima' => $this->request->getPost('nama_penerima'),
                'jalan'         => $this->request->getPost('jalan'),
                'no_telepon'    => $this->request->getPost('no_telepon'),
                'kota'          => $this->request->getPost('kota'),
                'provinsi'      => $this->request->getPost('provinsi'),
                'kode_pos'      => $this->request->getPost('kode_pos'),
                'aktif'         => 1
            ]);

            return redirect()->to('/memilihalamat')->with('success', 'Alamat baru berhasil ditambahkan dan aktif.');
        }

        return redirect()->to('/memilihalamat');
    }

    // === Pilih alamat aktif
    public function pilih($id_alamat)
    {
        $idUser = session()->get('id_user');
        $alamat = $this->alamatModel->where('id_user', $idUser)->find($id_alamat);

        if (!$alamat) {
            return $this->response->setJSON(['success' => false, 'message' => 'Alamat tidak ditemukan']);
        }

        $this->alamatModel->where('id_user', $idUser)->set(['aktif' => 0])->update();
        $this->alamatModel->update($id_alamat, ['aktif' => 1]);

        // simpan alamat aktif di session
        session()->set('alamat_aktif', $alamat);

        return $this->response->setJSON(['success' => true]);
    }


    // === Ubah alamat via AJAX
    public function ubah($id_alamat)
    {
        $alamat = $this->alamatModel->find($id_alamat);

        if (!$alamat) {
            return $this->response->setJSON(['success' => false, 'message' => 'Alamat tidak ditemukan']);
        }

        if ($this->request->getMethod() === 'POST') {
            $input = $this->request->getJSON(true);
            $dataToUpdate = [];
            $logPerubahan = [];

            $fields = ['nama_penerima','jalan','no_telepon','kota','provinsi','kode_pos'];
            foreach ($fields as $field) {
                if (isset($input[$field]) && $input[$field] != $alamat[$field]) {
                    $dataToUpdate[$field] = $input[$field];
                    $logPerubahan[$field] = [
                        'old' => $alamat[$field],
                        'new' => $input[$field]
                    ];
                }
            }

            if (!empty($dataToUpdate)) {
                $this->alamatModel->update($id_alamat, $dataToUpdate);

                $logMessage = "[".date('Y-m-d H:i:s')."] Alamat ID $id_alamat diubah: ".json_encode($logPerubahan).PHP_EOL;
                file_put_contents(WRITEPATH.'logs/alamat_changes.log', $logMessage, FILE_APPEND);

                return $this->response->setJSON([
                    'success' => true,
                    'message' => 'Alamat berhasil diperbarui',
                    'changed' => $logPerubahan
                ]);
            } else {
                return $this->response->setJSON(['success' => false, 'message' => 'Tidak ada perubahan pada alamat']);
            }
        }
    }
}
