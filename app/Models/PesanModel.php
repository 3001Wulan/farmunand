<?php

namespace App\Models;

use CodeIgniter\Model;

class PesanModel extends Model
{
    protected $table      = 'pemesanan';
    protected $primaryKey = 'id_pemesanan';
    protected $returnType = 'array';

    /**
     * Catatan:
     * kolom seperti id_produk/jumlah_produk sebenarnya milik detail_pemesanan,
     * jadi sebaiknya allowedFields untuk tabel pemesanan hanya kolom2 milik pemesanan.
     * Kalau model ini dipakai untuk INSERT/UPDATE ke "pemesanan", rapikan allowedFields
     * agar tidak menimbulkan error mass assignment.
     */
    protected $allowedFields = [
        'id_user',
        'id_alamat',
        'id_pembayaran',
        'total_harga',
        'status_pemesanan',
        'created_at',
        'updated_at',
    ];

    /**
     * Hitung pesan masuk dengan status 'belum_dibaca'
     * (Jika tidak dipakai lagi, boleh dihapus)
     */
    public function getPesanMasuk()
    {
        return $this->where('status_pemesanan', 'belum_dibaca')
                    ->countAllResults();
    }

    /** Ambil semua pesanan */
    public function getAllPesanan()
    {
        return $this->findAll();
    }

    /** Ambil pesanan berdasarkan ID */
    public function getPesananById($id_pemesanan)
    {
        return $this->where('id_pemesanan', $id_pemesanan)->first();
    }

    /** Ambil pesanan berdasarkan nama user */
    public function getPesananByNama($nama)
    {
        return $this->db->table('pemesanan p')
            ->select('p.*, u.nama as nama_user')
            ->join('users u', 'u.id_user = p.id_user', 'inner')
            ->where('u.nama', $nama)
            ->get()->getResultArray();
    }

    /**
     * Total penjualan bulan ini (menggunakan created_at & status 'Selesai')
     * Sesuaikan nama kolom tanggal jika kamu memang memakai kolom lain.
     */
    public function getPenjualanBulan()
    {
        $row = $this->selectSum('total_harga')
                    ->where('status_pemesanan', 'Selesai')
                    ->where('MONTH(created_at)', date('m'))
                    ->where('YEAR(created_at)', date('Y'))
                    ->first();

        return (float)($row['total_harga'] ?? 0);
    }

    /**
     * Ambil pesanan lengkap dengan detail produk (join detail_pemesanan + produk)
     * Opsional filter status.
     */
    public function getPesananWithProduk($idUser, $status = null)
    {
        $builder = $this->db->table('pemesanan p')
            ->select('
                p.id_pemesanan,
                p.status_pemesanan,
                p.total_harga,
                p.created_at,
                u.nama as nama_user,
                pr.nama_produk,
                pr.harga,
                pr.foto,
                dp.jumlah_produk,
                dp.harga_produk
            ')
            ->join('users u', 'u.id_user = p.id_user', 'inner')
            ->join('detail_pemesanan dp', 'dp.id_pemesanan = p.id_pemesanan', 'inner')
            ->join('produk pr', 'pr.id_produk = dp.id_produk', 'left')
            ->where('p.id_user', $idUser);

        if (!empty($status)) {
            $builder->where('p.status_pemesanan', $status);
        }

        return $builder->orderBy('p.created_at', 'DESC')
                       ->get()->getResultArray();
    }

    /**
     * Ambil item (per-detail) milik user yang BELUM dinilai.
     * Dipakai untuk halaman "Berikan Penilaian".
     * Ubah whereIn jika kamu hanya mau izinkan status "Selesai".
     */
    public function getDetailBelumDinilai(int $idUser): array
    {
        return $this->db->table('pemesanan p')
            ->select('
                p.id_pemesanan,
                p.status_pemesanan,
                p.created_at,
                dp.id_detail_pemesanan,
                dp.jumlah_produk,
                dp.harga_produk AS harga,
                pr.nama_produk,
                pr.foto
            ')
            ->join('detail_pemesanan dp', 'dp.id_pemesanan = p.id_pemesanan', 'inner')
            ->join('produk pr', 'pr.id_produk = dp.id_produk', 'left')
            ->where('p.id_user', $idUser)
            ->whereIn('p.status_pemesanan', ['Selesai', 'Dikirim', 'Diterima', 'Dikemas'])
            ->where('(dp.user_rating IS NULL OR dp.user_rating = 0)', null, false)
            ->orderBy('p.created_at', 'DESC')
            ->get()->getResultArray();
    }

    /**
     * Shortcut: ambil pesanan by status (tanpa join detail)
     */
    public function getPesananByStatus(int $idUser, string $status): array
    {
        return $this->where('id_user', $idUser)
                    ->where('status_pemesanan', $status)
                    ->orderBy('created_at', 'DESC')
                    ->findAll();
    }
}
