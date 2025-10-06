<?php

namespace App\Controllers;

use App\Models\ProdukModel;
use App\Models\AlamatModel;
use App\Models\UserModel;
use CodeIgniter\Controller;

class MelakukanPemesanan extends BaseController
{
    protected $produkModel;
    protected $alamatModel;
    protected $userModel;

    public function __construct()
    {
        $this->produkModel = new ProdukModel();
        $this->alamatModel = new AlamatModel();
        $this->userModel   = new UserModel();
    }

    // Halaman checkout
    public function index($idProdukFromSegment = null)
    {
        $idUser = session()->get('id_user');
        if (!$idUser) {
            return redirect()->to('/login')->with('error', 'Silakan login terlebih dahulu.');
        }

        $idProduk = $this->request->getPost('id_produk')
                 ?? $this->request->getGet('id_produk')
                 ?? $idProdukFromSegment;

        $qty = (int)($this->request->getPost('qty') ?? $this->request->getGet('qty') ?? 1);
        if ($qty < 1) $qty = 1;

        $checkout = null;
        if ($idProduk) {
            $produk = $this->produkModel->find($idProduk);

            if (!$produk) {
                return redirect()->back()->with('error', 'Produk tidak ditemukan.');
            }

            $stok = (int)($produk['stok'] ?? 0);
            if ($stok <= 0) {
                return redirect()->back()->with('error', 'Stok produk habis.');
            }
            if ($qty > $stok) {
                $qty = $stok;
                session()->setFlashdata('info', 'Jumlah melebihi stok, disesuaikan ke stok tersedia.');
            }

            $checkout = [
                'id_produk'   => $produk['id_produk'],
                'nama_produk' => $produk['nama_produk'],
                'deskripsi'   => $produk['deskripsi'] ?? '',
                'foto'        => $produk['foto'] ?? 'default.png',
                'harga'       => (float)$produk['harga'],
                'qty'         => $qty,
                'subtotal'    => (float)$produk['harga'] * $qty,
            ];
        }

        // ambil alamat user aktif
        $alamat = $this->alamatModel
                        ->where('id_user', $idUser)
                        ->orderBy('aktif', 'DESC')
                        ->orderBy('id_alamat', 'DESC')
                        ->findAll();

        return view('pembeli/melakukanpemesanan', [
            'checkout' => $checkout,
            'alamat'   => $alamat,
            'user'     => $this->userModel->find($idUser),
        ]);
    }

    // 🧩 Simpan pesanan ke database
    public function simpan()
    {
        $idUser   = session()->get('id_user');
        if (!$idUser) {
            return $this->response->setJSON(['success' => false, 'message' => 'User belum login']);
        }

        $db = \Config\Database::connect();

        $idProduk = $this->request->getPost('id_produk');
        $idAlamat = $this->request->getPost('id_alamat');
        $qty      = (int)$this->request->getPost('qty');
        $harga    = (float)$this->request->getPost('harga');
        $metode   = $this->request->getPost('metode');

        if (!$idProduk || !$idAlamat || $qty <= 0) {
            return $this->response->setJSON(['success' => false, 'message' => 'Data pesanan tidak lengkap.']);
        }

        $total = $qty * $harga;
        $status = ($metode === 'cod') ? 'Dikemas' : 'Menunggu Pembayaran';

        $db->transStart();

        // insert ke tabel pembayaran jika bukan COD
        $idPembayaran = null;
        if ($metode !== 'cod') {
            $db->table('pembayaran')->insert([
                'metode' => ucfirst($metode),
                'status_bayar' => 'Belum Bayar',
            ]);
            $idPembayaran = $db->insertID();
        }

        // insert ke tabel pemesanan
        $db->table('pemesanan')->insert([
            'id_user' => $idUser,
            'id_alamat' => $idAlamat,
            'id_pembayaran' => $idPembayaran,
            'status_pemesanan' => $status,
            'total_harga' => $total
        ]);
        $idPemesanan = $db->insertID();

        // insert ke tabel detail_pemesanan
        $db->table('detail_pemesanan')->insert([
            'id_pemesanan' => $idPemesanan,
            'id_produk' => $idProduk,
            'jumlah_produk' => $qty,
            'harga_produk' => $harga
        ]);

        // kurangi stok produk
        $produk = $this->produkModel->find($idProduk);
        if ($produk && isset($produk['stok'])) {
            $stokBaru = max(0, $produk['stok'] - $qty);
            $this->produkModel->update($idProduk, ['stok' => $stokBaru]);
        }

        $db->transComplete();

        if ($db->transStatus() === false) {
            return $this->response->setJSON(['success' => false, 'message' => 'Gagal menyimpan pesanan.']);
        }

        return $this->response->setJSON([
            'success' => true,
            'status' => $status,
            'message' => 'Pesanan berhasil dibuat'
        ]);
    }
}
