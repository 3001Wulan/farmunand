<?php

namespace App\Models;

use CodeIgniter\Model;

class PesananModel extends Model
{
    protected $table         = 'pemesanan';
    protected $primaryKey    = 'id_pemesanan';
    protected $returnType    = 'array';

    /**
     * Kolom yang boleh diisi (gabungan lengkap)
     */
    protected $allowedFields = [
        'id_user',
        'id_alamat',
        'id_pembayaran',
        'total_harga',
        'status_pemesanan',
        // flow konfirmasi user 7 hari
        'konfirmasi_token',
        'konfirmasi_expires_at',
        'confirmed_at',
        // timestamps
        'created_at',
        'updated_at',
    ];

    protected $useTimestamps = true;
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';

    /* =========================================================
     *  QUERY UNTUK USER (RIWAYAT & PER STATUS) — TERURUT TERBARU
     * ========================================================= */

    /**
     * Semua pesanan user (join detail, produk, pembayaran).
     * Memuat order_id, transaction_status (mid_status), snap_token.
     */
    public function getPesananWithProduk(int $idUser): array
    {
        return $this->db->table('pemesanan p')
            ->select('
                p.id_pemesanan,
                p.status_pemesanan,
                p.total_harga,
                p.created_at,
                p.konfirmasi_expires_at,
                pr.id_produk,
                pr.nama_produk,
                pr.foto,
                dp.jumlah_produk,
                dp.harga_produk AS harga,
                b.order_id,
                b.transaction_status AS mid_status,
                b.snap_token
            ')
            ->join('detail_pemesanan dp', 'dp.id_pemesanan = p.id_pemesanan', 'inner')
            ->join('produk pr', 'pr.id_produk = dp.id_produk', 'left')
            ->join('pembayaran b', 'b.id_pembayaran = p.id_pembayaran', 'left')
            ->where('p.id_user', $idUser)
            // KUNCI: pakai COALESCE utk fallback & kunci sekunder stabil
            ->orderBy('COALESCE(p.created_at, dp.created_at)', 'DESC', false)
            ->orderBy('p.id_pemesanan', 'DESC')
            ->get()->getResultArray();
    }

    public function getPesananByStatus(int $idUser, string $status): array
    {
        return $this->db->table('pemesanan p')
            ->select('
                p.id_pemesanan,
                p.status_pemesanan,
                p.total_harga,
                p.created_at,
                p.konfirmasi_expires_at,
                pr.foto,
                pr.nama_produk,
                dp.jumlah_produk,
                dp.harga_produk AS harga,
                b.order_id,
                b.transaction_status AS mid_status,
                b.snap_token
            ')
            ->join('detail_pemesanan dp', 'dp.id_pemesanan = p.id_pemesanan', 'inner')
            ->join('produk pr', 'pr.id_produk = dp.id_produk', 'left')
            ->join('pembayaran b', 'b.id_pembayaran = p.id_pembayaran', 'left')
            ->where('p.id_user', $idUser)
            ->where('p.status_pemesanan', $status)
            ->orderBy('COALESCE(p.created_at, dp.created_at)', 'DESC', false)
            ->orderBy('p.id_pemesanan', 'DESC')
            ->get()->getResultArray();
    }

    /* =========================================================
     *  RATING / ULASAN
     * ========================================================= */

    /**
     * Produk dari pesanan user yang Selesai dan belum diberi rating.
     * Kolom referensi: detail_pemesanan.user_rating (0/NULL = belum).
     */
    public function getPesananBelumDinilai(int $idUser): array
    {
        return $this->db->table('pemesanan p')
            ->select('
                p.id_pemesanan,
                dp.id_produk,
                pr.nama_produk,
                pr.harga,
                pr.foto,
                dp.jumlah_produk,
                p.status_pemesanan
            ')
            ->join('detail_pemesanan dp', 'dp.id_pemesanan = p.id_pemesanan')
            ->join('produk pr', 'pr.id_produk = dp.id_produk')
            ->where('p.id_user', $idUser)
            ->where('p.status_pemesanan', 'Selesai')
            ->groupStart()
                ->where('dp.user_rating IS NULL')
                ->orWhere('dp.user_rating', 0)
            ->groupEnd()
            ->orderBy('p.created_at', 'DESC')
            ->orderBy('p.id_pemesanan', 'DESC')
            ->get()->getResultArray();
    }

    /**
     * Detail item yang belum dinilai (varian lain).
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
            ->whereIn('p.status_pemesanan', ['Selesai'])
            ->where('(dp.user_rating IS NULL OR dp.user_rating = 0)', null, false)
            ->orderBy('COALESCE(p.created_at, dp.created_at)', 'DESC', false)
            ->orderBy('p.id_pemesanan', 'DESC')
            ->get()->getResultArray();
    }

    /* =========================================================
     *  METRIK / DASHBOARD
     * ========================================================= */

    /** Jumlah pesanan dibuat hari ini (semua status) */
    public function getTransaksiHariIni(): int
    {
        return $this->where('DATE(created_at)', date('Y-m-d'))->countAllResults();
    }

    /**
     * Total penjualan (sum total_harga) bulan berjalan — hanya status 'Selesai'
     */
    public function getPenjualanBulan(): float
    {
        // Rentang bulan berjalan (Asia/Jakarta)
        $start = date('Y-m-01 00:00:00');
        $end   = date('Y-m-t 23:59:59');

        $row = $this->db->table('pemesanan p')
            ->select('COALESCE(SUM(dp.harga_produk * dp.jumlah_produk), 0) AS total')
            ->join('detail_pemesanan dp', 'dp.id_pemesanan = p.id_pemesanan', 'inner')
            ->where('p.status_pemesanan', 'Selesai')
            ->where('p.created_at >=', $start)
            ->where('p.created_at <=', $end)
            ->get()
            ->getRow();

        return (float) ($row->total ?? 0);
    }

    public function getPenjualanBulanByHeader(): float
    {
        $start = date('Y-m-01 00:00:00');
        $end   = date('Y-m-t 23:59:59');

        $row = $this->selectSum('total_harga')
            ->where('status_pemesanan', 'Selesai')
            ->where('created_at >=', $start)
            ->where('created_at <=', $end)
            ->get()
            ->getRow();

        return (float) ($row->total_harga ?? 0);
    }

    /**
     * Filter tanggal sederhana (contoh admin laporan).
     * Diperbaiki: gunakan tabel 'pemesanan' (bukan 'pesanan').
     */
    public function getFiltered(?string $start = null, ?string $end = null): array
    {
        $builder = $this->db->table('pemesanan p')
            ->select('
                p.*,
                u.nama as nama_pembeli
            ')
            ->join('users u', 'u.id_user = p.id_user', 'inner');

        if ($start && $end) {
            $builder->where("DATE(p.created_at) >=", $start)
                    ->where("DATE(p.created_at) <=", $end);
        }

        return $builder
            ->orderBy('p.created_at', 'DESC')
            ->orderBy('p.id_pemesanan', 'DESC')
            ->get()->getResultArray();
    }

    /* =========================================================
     *  UTILITAS / ADMIN FLOW
     * ========================================================= */

    /** Ambil semua pesanan (hati-hati untuk tabel besar) */
    public function getAllPesanan(): array
    {
        return $this->orderBy('created_at', 'DESC')
            ->orderBy('id_pemesanan', 'DESC')
            ->findAll();
    }

    /** Ambil pesanan berdasarkan ID */
    public function getPesananById(int $id): ?array
    {
        return $this->where('id_pemesanan', $id)->first();
    }

    /** Ambil pesanan berdasarkan nama user */
    public function getPesananByNama(string $nama): array
    {
        return $this->db->table('pemesanan p')
            ->select('p.*, u.nama as nama_user')
            ->join('users u', 'u.id_user = p.id_user', 'inner')
            ->where('u.nama', $nama)
            ->orderBy('p.created_at', 'DESC')
            ->orderBy('p.id_pemesanan', 'DESC')
            ->get()->getResultArray();
    }

    /**
     * Saat admin set ke "Dikirim", generate token + expiry 7 hari.
     */
    public function markAsShippedWithToken(int $idPemesanan): bool
    {
        return $this->update($idPemesanan, [
            'status_pemesanan'      => 'Dikirim',
            'konfirmasi_token'      => bin2hex(random_bytes(16)),
            'konfirmasi_expires_at' => date('Y-m-d H:i:s', strtotime('+7 days')),
            'confirmed_at'          => null,
            'updated_at'            => date('Y-m-d H:i:s'),
        ]);
    }

    /**
     * Auto-close: set 'Selesai' untuk pesanan 'Dikirim' yang lewat 7 hari.
     * Return jumlah baris yang diupdate.
     */
    public function autoCloseExpired(): int
    {
        $builder = $this->builder();
        $builder->where('status_pemesanan', 'Dikirim')
            ->where('konfirmasi_expires_at IS NOT NULL', null, false)
            ->where('confirmed_at IS NULL', null, false)
            ->where('konfirmasi_expires_at <', date('Y-m-d H:i:s'))
            ->set([
                'status_pemesanan' => 'Selesai',
                'updated_at'       => date('Y-m-d H:i:s'),
            ])
            ->update();

        return $this->db->affectedRows();
    }
    public function getPesananByIdAndUser(int $id, int $userId)
    {
        return $this->where('id_pemesanan', $id)
                    ->where('id_user', $userId)
                    ->first();
    }
}
