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

    // Menampilkan halaman memilih alamat
    public function index()
    {
        $idUser = session()->get('id_user');

        // Ambil semua alamat user, terbaru paling atas
        $data['alamat'] = $this->alamatModel
                                ->where('id_user', $idUser)
                                ->orderBy('id_alamat', 'DESC')
                                ->findAll();

        $data['user'] = session()->get();
        $data['session'] = session(); // untuk flashdata

        return view('pembeli/memilihalamat', $data);
    }

    // Tambah alamat baru
    public function tambah()
    {
        if ($this->request->getMethod() === 'POST') {
            $idUser = session()->get('id_user');

            // Validasi sederhana
            $validation = \Config\Services::validation();
            $validation->setRules([
                'nama_penerima' => 'required',
                'jalan'         => 'required',
                'no_telepon'    => 'required',
                'kota'          => 'required',
                'provinsi'      => 'required',
                'kode_pos'      => 'required'
            ]);

            if (!$this->validate($validation->getRules())) {
                return redirect()->to('/memilihalamat')->with('error', 'Semua field harus diisi.');
            }

            // Nonaktifkan semua alamat lama user
            $this->alamatModel->where('id_user', $idUser)->set(['aktif' => 0])->update();

            // Data alamat baru
            $data = [
                'id_user'       => $idUser,
                'nama_penerima' => $this->request->getPost('nama_penerima'),
                'jalan'         => $this->request->getPost('jalan'),
                'no_telepon'    => $this->request->getPost('no_telepon'),
                'kota'          => $this->request->getPost('kota'),
                'provinsi'      => $this->request->getPost('provinsi'),
                'kode_pos'      => $this->request->getPost('kode_pos'),
                'aktif'         => 1  // otomatis aktif
            ];

            $this->alamatModel->save($data);

            return redirect()->to('/memilihalamat')->with('success', 'Alamat baru berhasil ditambahkan dan aktif.');
        }

        return redirect()->to('/memilihalamat');
    }

    // Pilih alamat untuk dijadikan aktif
    public function pilih($id_alamat)
    {
        $idUser = session()->get('id_user');
        $alamat = $this->alamatModel->where('id_user', $idUser)->find($id_alamat);

        if (!$alamat) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Alamat tidak ditemukan'
            ]);
        }

        // Nonaktifkan alamat lain
        $this->alamatModel->where('id_user', $idUser)->set(['aktif'=>0])->update();

        // Aktifkan alamat yang dipilih
        $this->alamatModel->update($id_alamat, ['aktif'=>1]);

        return $this->response->setJSON(['success'=>true]);
    }

    // Ubah alamat via AJAX (halaman tetap sama)
    public function ubah($id_alamat)
    {
        $alamat = $this->alamatModel->find($id_alamat);
    
        if (!$alamat) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Alamat tidak ditemukan'
            ]);
        }
    
        if ($this->request->getMethod() === 'POST') {
            $input = $this->request->getJSON(true); // Ambil data JSON
            $dataToUpdate = [];
            $logPerubahan = [];
    
            // Loop setiap field yang bisa diubah
            $fields = ['nama_penerima', 'jalan', 'no_telepon', 'kota', 'provinsi', 'kode_pos'];
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
    
                // Simpan log ke file
                $logMessage = "[" . date('Y-m-d H:i:s') . "] Alamat ID $id_alamat diubah: " . json_encode($logPerubahan) . PHP_EOL;
                file_put_contents(WRITEPATH . 'logs/alamat_changes.log', $logMessage, FILE_APPEND);
    
                return $this->response->setJSON([
                    'success' => true,
                    'message' => 'Alamat berhasil diperbarui',
                    'changed' => $logPerubahan
                ]);
            } else {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'Tidak ada perubahan pada alamat'
                ]);
            }
        }
    
    }
    
}
