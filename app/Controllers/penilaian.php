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

        $id_detail_pemesanan = (int) $id_detail_pemesanan;

        // ===== Validasi input rating/ulasan + file =====
        $validation = \Config\Services::validation();
        $rules = [
            'rating'        => 'required|in_list[1,2,3,4,5]',
            'ulasan'        => 'permit_empty|max_length[1000]',
            // Catatan: pakai permit_empty agar upload opsional.
            // Validasi per-file akan dicek manual juga.
            'media.*'       => 'permit_empty|max_size[media,4096]|ext_in[media,jpg,jpeg,png,gif,mp4,webm,ogg]',
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()
                ->withInput()
                ->with('errors', $validation->getErrors());
        }

        // ===== Ambil detail + pastikan milik user ini + status Selesai =====
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

        // (Opsional kuat): hanya boleh menilai setelah pesanan Selesai
        if (($detail['status_pemesanan'] ?? '') !== 'Selesai') {
            return redirect()->back()->with('error', 'Penilaian hanya dapat dilakukan setelah pesanan berstatus Selesai.');
        }

        // Cegah double rating
        if (!empty($detail['user_rating'])) {
            return redirect()->back()->with('info', 'Item ini sudah pernah dinilai.');
        }

        // ===== Persiapan upload =====
        $uploadDir = rtrim(FCPATH, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . 'uploads' . DIRECTORY_SEPARATOR . 'penilaian';
        if (!is_dir($uploadDir)) {
            @mkdir($uploadDir, 0755, true);
        }

        $mediaNames = [];
        $files = $this->request->getFiles();

        // Normalisasi: dukung <input type="file" name="media"> (single) atau name="media[]" (multiple)
        $mediaFiles = [];
        if (isset($files['media'])) {
            // Bisa array file atau single UploadedFile
            if (is_array($files['media'])) {
                $mediaFiles = $files['media'];
            } else {
                $mediaFiles = [$files['media']];
            }
        }

        // Batasi jumlah file (misal maks 5)
        $MAX_FILES = 5;
        if (count($mediaFiles) > $MAX_FILES) {
            return redirect()->back()->withInput()->with('error', "Maksimal {$MAX_FILES} file media.");
        }

        // Validasi manual tambahan (mime/ukuran) + move
        $totalMoved = 0;
        $uploadErrors = [];

        foreach ($mediaFiles as $idx => $file) {
            if (!$file || !$file->isValid()) {
                // Abaikan slot kosong (misal user tidak memilih file sama sekali)
                continue;
            }

            // Double guard: ekstensi & ukuran sudah divalidasi rules, tapi kita cek lagi
            $ext  = strtolower($file->getClientExtension());
            $size = (int) $file->getSizeByUnit('kb'); // KB

            $allowedExt = ['jpg','jpeg','png','gif','mp4','webm','ogg'];
            if (!in_array($ext, $allowedExt, true)) {
                $uploadErrors[] = "File #".($idx+1)." memiliki ekstensi tidak diizinkan.";
                continue;
            }
            if ($size > 4096) { // 4MB
                $uploadErrors[] = "File #".($idx+1)." melebihi 4MB.";
                continue;
            }

            // Move aman (hindari nama asli)
            try {
                $newName = $file->getRandomName();
                $file->move($uploadDir, $newName, true);
                $mediaNames[] = $newName;
                $totalMoved++;
            } catch (\Throwable $e) {
                $uploadErrors[] = "Gagal mengunggah file #".($idx+1).".";
                // lanjut ke file berikutnya
            }
        }

        // Jika semua file gagal diunggah (padahal user kirim file), boleh dianggap error
        if (!empty($mediaFiles) && $totalMoved === 0 && !empty($uploadErrors)) {
            return redirect()->back()->withInput()->with('error', implode(' ', $uploadErrors));
        }

        // ===== Sanitasi ulasan & simpan =====
        $rating = (int) $this->request->getPost('rating');
        $ulasan = (string) ($this->request->getPost('ulasan') ?? '');
        if (mb_strlen($ulasan) > 1000) {
            $ulasan = mb_substr($ulasan, 0, 1000);
        }

        $mediaJson = $mediaNames ? json_encode($mediaNames) : null;

        // Update tabel detail_pemesanan langsung (model PenilaianModel = table detail_pemesanan)
        $ok = $this->penilaianModel->update($id_detail_pemesanan, [
            'user_rating' => $rating,
            'user_ulasan' => $ulasan,
            'user_media'  => $mediaJson,
            'updated_at'  => date('Y-m-d H:i:s'),
        ]);

        if (!$ok) {
            // (Opsional) bersihkan file yang sudah ter-upload jika ingin atomic
            // foreach ($mediaNames as $n) { @unlink($uploadDir . DIRECTORY_SEPARATOR . $n); }
            return redirect()->back()->withInput()->with('error', 'Gagal menyimpan penilaian. Coba lagi.');
        }

        // (Opsional) Beri tahu user jika ada sebagian file gagal
        if (!empty($uploadErrors)) {
            return redirect()->to('/penilaian/daftar')->with('success', 'Penilaian tersimpan, namun sebagian file gagal diunggah: ' . implode(' ', $uploadErrors));
        }

        return redirect()->to('/penilaian/daftar')->with('success', 'Terima kasih! Penilaian berhasil dikirim.');
    }
}
