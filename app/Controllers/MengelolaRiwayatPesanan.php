<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Models\PesananModel;
use App\Models\UserModel;

class MengelolaRiwayatPesanan extends BaseController
{
    protected $pesananModel;
    protected $userModel;

    public function __construct()
    {
        $this->pesananModel = new PesananModel();
        $this->userModel    = new UserModel();
    }

    public function index()
    {
        // ambil sesuai name di <select name="status">
        $status  = $this->request->getGet('status') ?: '';
        $keyword = $this->request->getGet('keyword') ?: '';
        $sort    = strtoupper($this->request->getGet('sort') ?? 'DESC');

        // AUTO-CLOSE: yang sudah lewat 7 hari sejak dikirim dan belum dikonfirmasi user
        $this->pesananModel->where('status_pemesanan', 'Dikirim')
            ->where('konfirmasi_expires_at IS NOT NULL', null, false)
            ->where('confirmed_at IS NULL', null, false)
            ->where('konfirmasi_expires_at <', date('Y-m-d H:i:s'))
            ->set(['status_pemesanan' => 'Selesai', 'updated_at' => date('Y-m-d H:i:s')])
            ->update();

        $builder = $this->pesananModel
            ->select('
                pemesanan.id_pemesanan,
                pemesanan.status_pemesanan,
                pemesanan.created_at,
                users.id_user,
                users.nama AS nama_user,
                produk.nama_produk,
                detail_pemesanan.jumlah_produk,
                detail_pemesanan.harga_produk AS harga_produk
            ')
            ->join('users', 'users.id_user = pemesanan.id_user')
            ->join('detail_pemesanan', 'detail_pemesanan.id_pemesanan = pemesanan.id_pemesanan')
            ->join('produk', 'produk.id_produk = detail_pemesanan.id_produk');

        if ($status !== '') {
            $builder->where('pemesanan.status_pemesanan', $status);
        }

        if ($keyword !== '') {
            $builder->groupStart()
                ->like('users.nama', $keyword)
                ->orLike('produk.nama_produk', $keyword)
            ->groupEnd();
        }

        $pesanan = $builder->orderBy('pemesanan.created_at', $sort)->get()->getResultArray();

        $userId = session()->get('id_user');
        $user   = $this->userModel->find($userId);

        return view('Admin/MengelolaRiwayatPesanan', [
            'pesanan' => $pesanan,
            'status'  => $status,
            'keyword' => $keyword,
            'sort'    => strtolower($sort),
            'user'    => $user,
        ]);
    }

    public function updateStatus($id_pemesanan)
    {
        $target = (string) $this->request->getPost('status_pemesanan');

        // Admin TIDAK boleh set 'Selesai' manual
        $allowedTargets = ['Belum Bayar','Dikemas','Dikirim','Dibatalkan'];
        if (!in_array($target, $allowedTargets, true)) {
            return redirect()->to('/MengelolaRiwayatPesanan')
                ->with('error', 'Status tidak valid untuk admin.');
        }

        // Ambil status saat ini
        $row = $this->pesananModel
            ->select('status_pemesanan')
            ->find((int) $id_pemesanan);

        if (!$row) {
            return redirect()->to('/MengelolaRiwayatPesanan')->with('error', 'Pesanan tidak ditemukan.');
        }

        $current = (string) ($row['status_pemesanan'] ?? '');

        // Jika sama, tidak perlu update
        if ($current === $target) {
            return redirect()->to('/MengelolaRiwayatPesanan')
                ->with('success', 'Status pesanan tidak berubah (sama dengan sebelumnya).');
        }

        // ==========
        // Aturan transisi (state machine)
        // ==========
        $allowedByCurrent = [
            // 1) Selesai -> tidak bisa diubah ke mana pun
            'Selesai'     => [],
            // 2) Dikemas -> hanya boleh ke Dikirim
            'Dikemas'     => ['Dikirim'],
            // 3) Dikirim -> tidak bisa diubah (menunggu konfirmasi user -> otomatis Selesai)
            'Dikirim'     => [],
            // 4) Belum Bayar -> hanya boleh ke Dikemas atau Dibatalkan
            'Belum Bayar' => ['Dikemas', 'Dibatalkan'],
            // 5) Dibatalkan -> tidak bisa diubah lagi
            'Dibatalkan'  => [],
        ];

        $allowedByCurrent = [
            'Selesai'     => [],            // lock
            'Dikemas'     => ['Dikirim'],   // hanya ke Dikirim
            'Dikirim'     => [],            // lock (tunggu konfirmasi user)
            'Belum Bayar' => [],            // ⬅️ sekarang TIDAK BISA diubah oleh admin
            'Dibatalkan'  => [],            // lock
        ];

        $allowedNext = $allowedByCurrent[$current] ?? [];

        if (!in_array($target, $allowedNext, true)) {
            // Pesan spesifik biar jelas
            $msg = 'Transisi status tidak diizinkan.';
            if ($current === 'Selesai')       $msg = 'Pesanan sudah "Selesai" dan tidak bisa diubah lagi.';
            if ($current === 'Dikemas')       $msg = 'Dari "Dikemas" hanya bisa diubah ke "Dikirim".';
            if ($current === 'Dikirim')       $msg = 'Pesanan "Dikirim" tidak dapat diubah lagi. Menunggu konfirmasi user.';
            if ($current === 'Belum Bayar')   $msg = 'Pesanan "Menunggu Pembayaran" tidak dapat diubah oleh admin.';
            if ($current === 'Dibatalkan')    $msg = 'Pesanan yang "Dibatalkan" tidak bisa diubah lagi.';

            return redirect()->to('/MengelolaRiwayatPesanan')->with('error', $msg);
        }

        // Bangun data update
        $data = ['status_pemesanan' => $target];

        // Jika di-set ke "Dikirim": buat token konfirmasi 7 hari
        if ($target === 'Dikirim') {
            $data['konfirmasi_token']      = bin2hex(random_bytes(16));
            $data['konfirmasi_expires_at'] = date('Y-m-d H:i:s', strtotime('+7 days'));
            $data['confirmed_at']          = null;
        } else {
            // status lain: kosongkan token (opsional)
            $data['konfirmasi_token']      = null;
            $data['konfirmasi_expires_at'] = null;
            // $data['confirmed_at'] biarkan apa adanya
        }

        $ok = $this->pesananModel->update((int) $id_pemesanan, $data);

        return redirect()->to('/MengelolaRiwayatPesanan')
            ->with($ok ? 'success' : 'error', $ok ? 'Status pesanan berhasil diperbarui.' : 'Gagal memperbarui status.');
    }
    }
