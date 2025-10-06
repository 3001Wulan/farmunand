<?php

namespace App\Controllers;

use App\Models\ProdukModel;
use App\Models\PenilaianModel; // diarahkan ke tabel detail_pemesanan
use App\Models\PesananModel;
use App\Models\UserModel;

class Penilaian extends BaseController
{
    protected $produkModel;
    protected $penilaianModel; // <- ini model utk table detail_pemesanan
    protected $pesananModel;
    protected $userModel;

    public function __construct()
    {
        $this->produkModel    = new ProdukModel();
        $this->penilaianModel = new PenilaianModel(); // table: detail_pemesanan
        $this->pesananModel   = new PesananModel();
        $this->userModel      = new UserModel();
        helper(['form']);
    }

    /**
     * Daftar item dari pesanan user yang belum dinilai (per-detail).
     */
    public function daftar()
    {
        $idUser = session()->get('id_user');
        if (!$idUser) {
            return redirect()->to('/login')->with('error', 'Silakan login terlebih dahulu.');
        }

        $user = $this->userModel->find($idUser);

        // Pastikan PesananModel punya method ini (lihat snippet PesananModel di bawah)
        $pesanan = $this->pesananModel->getDetailBelumDinilai($idUser);

        return view('pembeli/penilaianproduk', [
            'pesanan' => $pesanan, // berisi baris per id_detail_pemesanan
            'user'    => $user
        ]);
    }

    /**
     * (Opsional) Form penilaian jika dibuka khusus product tertentu.
     * Direkomendasikan pindah ke per-detail id, tapi kubiarkan kompatibel.
     */
    public function index($id_produk)
    {
        $produk = $this->produkModel->find($id_produk);
        if (!$produk) {
            return redirect()->to('/riwayatpesanan')->with('error', 'Produk tidak ditemukan.');
        }

        $idUser = session()->get('id_user');
        if (!$idUser) {
            return redirect()->to('/login');
        }

        $user = $this->userModel->find($idUser);

        // View bisa menampilkan info produk (opsional),
        // tapi untuk submit tetap gunakan route simpan/<id_detail_pemesanan>.
        return view('pembeli/penilaianproduk', [
            'produk' => $produk,
            'user'   => $user
        ]);
    }

    /**
     * Simpan penilaian per-item (per id_detail_pemesanan).
     */
    public function simpan($id_detail_pemesanan)
    {
        $idUser = session()->get('id_user');
        if (!$idUser) {
            return redirect()->to('/login')->with('error', 'Silakan login terlebih dahulu.');
        }

        // Validasi input
        $validation = \Config\Services::validation();
        $rules = [
            'rating'  => 'required|in_list[1,2,3,4,5]',
            'media.*' => 'permit_empty|max_size[media,4096]|ext_in[media,jpg,jpeg,png,gif,mp4,webm,ogg]' 
        ];
        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()
                ->with('errors', $validation->getErrors());
        }

        // Ambil detail + pastikan milik user ini
        $db = \Config\Database::connect();
        $detail = $db->table('detail_pemesanan dp')
            ->select('dp.*, p.id_user, pr.nama_produk')
            ->join('pemesanan p', 'p.id_pemesanan = dp.id_pemesanan', 'inner')
            ->join('produk pr', 'pr.id_produk = dp.id_produk', 'left')
            ->where('dp.id_detail_pemesanan', (int)$id_detail_pemesanan)
            ->get()->getRowArray();

        if (!$detail) {
            return redirect()->back()->with('error', 'Detail pemesanan tidak ditemukan.');
        }
        if ((int)$detail['id_user'] !== (int)$idUser) {
            return redirect()->back()->with('error', 'Anda tidak berhak menilai item ini.');
        }

        // Cegah double rating
        if (!empty($detail['user_rating'])) {
            return redirect()->back()->with('info', 'Item ini sudah pernah dinilai.');
        }

        // Upload media (opsional)
        $mediaNames = [];
        $files = $this->request->getFiles();
        if (!empty($files) && isset($files['media'])) {
            foreach ($files['media'] as $file) {
                if ($file->isValid() && !$file->hasMoved()) {
                    $newName = $file->getRandomName();
                    $file->move(FCPATH . 'uploads/penilaian', $newName);
                    $mediaNames[] = $newName;
                }
            }
        }
        $mediaJson = $mediaNames ? json_encode($mediaNames) : null;

        // Simpan penilaian langsung ke tabel detail_pemesanan (kolom: user_rating, user_ulasan, user_media)
        $this->penilaianModel->update((int)$id_detail_pemesanan, [
            'user_rating' => (int)$this->request->getPost('rating'),
            'user_ulasan' => (string)($this->request->getPost('ulasan') ?? ''),
            'user_media'  => $mediaJson,
            'updated_at'  => date('Y-m-d H:i:s'),
        ]);

        return redirect()->to('/penilaian/daftar')->with('success', 'Penilaian berhasil dikirim.');
    }
}
