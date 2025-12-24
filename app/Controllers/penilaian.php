<?php

namespace App\Controllers;

use App\Models\ProdukModel;
use App\Models\PenilaianModel; 
use App\Models\PesananModel;
use App\Models\UserModel;

class Penilaian extends BaseController
{
    protected $produkModel;
    protected $penilaianModel; 
    protected $pesananModel;
    protected $userModel;

    public function __construct()
    {
        $this->produkModel    = new ProdukModel();
        $this->penilaianModel = new PenilaianModel(); 
        $this->pesananModel   = new PesananModel();
        $this->userModel      = new UserModel();
        helper(['form']);
    }

    public function daftar()
    {
        $idUser = session()->get('id_user');
        if (!$idUser) {
            return redirect()->to('/login')->with('error', 'Silakan login terlebih dahulu.');
        }

        $user = $this->userModel->find($idUser);

        $pesanan = $this->pesananModel->getDetailBelumDinilai($idUser);

        return view('pembeli/penilaianproduk', [
            'pesanan' => $pesanan, 
            'user'    => $user
        ]);
    }

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

        return view('pembeli/penilaianproduk', [
            'produk' => $produk,
            'user'   => $user
        ]);
    }

    public function simpan($id_detail_pemesanan)
    {
        $idUser = session()->get('id_user');
        if (!$idUser) {
            return redirect()->to('/login')->with('error', 'Silakan login terlebih dahulu.');
        }

        $id_detail_pemesanan = (int) $id_detail_pemesanan;

        $validation = \Config\Services::validation();
        $rules = [
            'rating'        => 'required|in_list[1,2,3,4,5]',
            'ulasan'        => 'permit_empty|max_length[1000]',
            'media.*'       => 'permit_empty|max_size[media,10240]|ext_in[media,jpg,jpeg,png,gif,mp4,webm,ogg]',
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()
                ->withInput()
                ->with('errors', $validation->getErrors());
        }

        $db = \Config\Database::connect();
        $detail = $db->table('detail_pemesanan dp')
            ->select('dp.*, p.id_user, p.status_pemesanan, pr.nama_produk')
            ->join('pemesanan p', 'p.id_pemesanan = dp.id_pemesanan', 'inner')
            ->join('produk pr', 'pr.id_produk = dp.id_produk', 'left')
            ->where('dp.id_detail_pemesanan', $id_detail_pemesanan)
            ->get()->getRowArray();

        if (!$detail) {
            return redirect()->back()->with('error', 'Detail pemesanan tidak ditemukan.');
        }
        if ((int)$detail['id_user'] !== (int)$idUser) {
            return redirect()->back()->with('error', 'Anda tidak berhak menilai item ini.');
        }

        if (($detail['status_pemesanan'] ?? '') !== 'Selesai') {
            return redirect()->back()->with('error', 'Penilaian hanya dapat dilakukan setelah pesanan berstatus Selesai.');
        }

        if (!empty($detail['user_rating'])) {
            return redirect()->back()->with('info', 'Item ini sudah pernah dinilai.');
        }

        $uploadDir = rtrim(FCPATH, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . 'uploads' . DIRECTORY_SEPARATOR . 'penilaian';
        if (!is_dir($uploadDir)) {
            @mkdir($uploadDir, 0755, true);
        }

        $mediaNames = [];
        $files = $this->request->getFiles();

        $mediaFiles = [];
        if (isset($files['media'])) {
            if (is_array($files['media'])) {
                $mediaFiles = $files['media'];
            } else {
                $mediaFiles = [$files['media']];
            }
        }

        $MAX_FILES = 5;
        if (count($mediaFiles) > $MAX_FILES) {
            return redirect()->back()->withInput()->with('error', "Maksimal {$MAX_FILES} file media.");
        }

        $totalMoved = 0;
        $uploadErrors = [];

        foreach ($mediaFiles as $idx => $file) {
            if (!$file || !$file->isValid()) {
                continue;
            }

            $ext  = strtolower($file->getClientExtension());
            $size = (int) $file->getSizeByUnit('kb'); 

            $allowedExt = ['jpg','jpeg','png','gif','mp4','webm','ogg'];
            if (!in_array($ext, $allowedExt, true)) {
                $uploadErrors[] = "File #".($idx+1)." memiliki ekstensi tidak diizinkan.";
                continue;
            }
            if ($size > 10240) { 
                $uploadErrors[] = "File #".($idx+1)." melebihi 4MB.";
                continue;
            }

            try {
                $newName = $file->getRandomName();
                $file->move($uploadDir, $newName, true);
                $mediaNames[] = $newName;
                $totalMoved++;
            } catch (\Throwable $e) {
                $uploadErrors[] = "Gagal mengunggah file #".($idx+1).".";
            }
        }

        if (!empty($mediaFiles) && $totalMoved === 0 && !empty($uploadErrors)) {
            return redirect()->back()->withInput()->with('error', implode(' ', $uploadErrors));
        }

        $rating = (int) $this->request->getPost('rating');
        $ulasan = (string) ($this->request->getPost('ulasan') ?? '');
        if (mb_strlen($ulasan) > 1000) {
            $ulasan = mb_substr($ulasan, 0, 1000);
        }

        $mediaJson = $mediaNames ? json_encode($mediaNames) : null;

        $ok = $this->penilaianModel->update($id_detail_pemesanan, [
            'user_rating' => $rating,
            'user_ulasan' => $ulasan,
            'user_media'  => $mediaJson,
            'updated_at'  => date('Y-m-d H:i:s'),
        ]);

        if (!$ok) {

            return redirect()->back()->withInput()->with('error', 'Gagal menyimpan penilaian. Coba lagi.');
        }

        if (!empty($uploadErrors)) {
            return redirect()->to('/penilaian/daftar')->with('success', 'Penilaian tersimpan, namun sebagian file gagal diunggah: ' . implode(' ', $uploadErrors));
        }

        return redirect()->to('/penilaian/daftar')->with('success', 'Terima kasih! Penilaian berhasil dikirim.');
    }
}
