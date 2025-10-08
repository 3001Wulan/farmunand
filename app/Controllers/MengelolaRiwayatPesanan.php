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
        $status = $this->request->getPost('status_pemesanan');

        // Admin TIDAK boleh set 'Selesai'
        $allowed = ['Belum Bayar','Dikemas','Dikirim','Dibatalkan'];
        if (!in_array($status, $allowed, true)) {
            return redirect()->to('/MengelolaRiwayatPesanan')->with('error', 'Status tidak valid untuk admin.');
        }

        $data = ['status_pemesanan' => $status];

        // Jika di-set ke "Dikirim", buat token konfirmasi 7 hari
        if ($status === 'Dikirim') {
            $data['konfirmasi_token']      = bin2hex(random_bytes(16));
            $data['konfirmasi_expires_at'] = date('Y-m-d H:i:s', strtotime('+7 days'));
            $data['confirmed_at']          = null; // reset
        } else {
            // status lain: kosongkan token (opsional)
            $data['konfirmasi_token']      = null;
            $data['konfirmasi_expires_at'] = null;
            // $data['confirmed_at'] tetap apa adanya
        }

        $ok = $this->pesananModel->update((int)$id_pemesanan, $data);

        return redirect()->to('/MengelolaRiwayatPesanan')
            ->with($ok ? 'success' : 'error', $ok ? 'Status pesanan berhasil diperbarui.' : 'Gagal memperbarui status.');
    }
}
