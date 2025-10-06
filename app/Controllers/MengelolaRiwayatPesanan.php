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

        if (!in_array($status, ['Dikemas','Dikirim','Selesai','Diproses','Dibatalkan','Belum Bayar'], true)) {
            return redirect()->to('/MengelolaRiwayatPesanan')->with('error', 'Status tidak valid.');
        }

        $ok = $this->pesananModel->update((int)$id_pemesanan, ['status_pemesanan' => $status]);

        if ($ok) {
            return redirect()->to('/MengelolaRiwayatPesanan')->with('success', 'Status pesanan berhasil diperbarui.');
        }
        return redirect()->to('/MengelolaRiwayatPesanan')->with('error', 'Gagal memperbarui status.');
    }
}
