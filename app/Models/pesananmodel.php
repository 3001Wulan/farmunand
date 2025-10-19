<?php

namespace App\Models;

use CodeIgniter\Model;

class PesananModel extends Model
{
    protected $table         = 'pemesanan';
    protected $primaryKey    = 'id_pemesanan';
    protected $allowedFields = ['id_alamat', 'id_pembayaran', 'id_user', 'status_pemesanan'];

    protected $useTimestamps = true;
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';

    /**
    /**
 * Join pesanan + detail + produk + pembayaran untuk user tertentu (tanpa filter status).
 * Hasil sudah memuat order_id & snap_token agar view bisa render tombol "Lanjutkan Pembayaran".
 */
public function getPesananWithProduk(int $idUser): array
{
    return $this->db->table('pemesanan p')
        ->select(
            'p.id_pemesanan,' .
            'p.status_pemesanan,' .
            'p.total_harga,' .
            'p.created_at,' .
            'p.konfirmasi_expires_at,' .
            'pr.id_produk,' .
            'pr.nama_produk,' .
            'pr.foto,' .
            'dp.jumlah_produk,' .
            'dp.harga_produk AS harga,' .
            'b.order_id,' .
            'b.transaction_status AS mid_status,' .
            'b.snap_token'
        )
        ->join('detail_pemesanan dp', 'dp.id_pemesanan = p.id_pemesanan', 'inner')
        ->join('produk pr', 'pr.id_produk = dp.id_produk', 'left')
        ->join('pembayaran b', 'b.id_pembayaran = p.id_pembayaran', 'left')
        ->where('p.id_user', $idUser)
        ->orderBy('p.created_at', 'DESC')
        ->get()->getResultArray();
}

/**
 * Join pesanan + detail + produk + pembayaran untuk user tertentu dengan filter status.
 * Dipakai di halaman Belum Bayar/Dikemas/Dikirim/Selesai/Dibatalkan.
 */
public function getPesananByStatus(int $idUser, string $status): array
{
    return $this->db->table('pemesanan p')
        ->select(
            'p.id_pemesanan,' .
            'p.status_pemesanan,' .
            'p.total_harga,' .
            'p.created_at,' .
            'p.konfirmasi_expires_at,' .
            'pr.foto,' .
            'pr.nama_produk,' .
            'dp.jumlah_produk,' .
            'dp.harga_produk AS harga,' .
            'b.order_id,' .
            'b.transaction_status AS mid_status,' .
            'b.snap_token'
        )
        ->join('detail_pemesanan dp', 'dp.id_pemesanan = p.id_pemesanan', 'inner')
        ->join('produk pr', 'pr.id_produk = dp.id_produk', 'left')
        ->join('pembayaran b', 'b.id_pembayaran = p.id_pembayaran', 'left')
        ->where('p.id_user', $idUser)
        ->where('p.status_pemesanan', $status)
        ->orderBy('p.created_at', 'DESC')
        ->get()->getResultArray();
}



    /**
     * Produk dari pesanan user yang statusnya 'Selesai' dan belum diberi rating oleh user.
     * Menggunakan kolom detail_pemesanan.user_rating (NULL/0 = belum dinilai).
     */
    public function getPesananBelumDinilai($id_user)
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
            ->where('p.id_user', $id_user)
            ->where('p.status_pemesanan', 'Selesai')
            ->groupStart()
                ->where('dp.user_rating IS NULL')
                ->orWhere('dp.user_rating', 0)
            ->groupEnd()
            ->get()
            ->getResultArray();
    }

    /**
     * Metrik dashboard: jumlah pesanan yang dibuat hari ini.
     * (opsional: filter hanya status tertentu)
     */
    public function getTransaksiHariIni(): int
    {
        return $this->where('DATE(created_at)', date('Y-m-d'))
                    ->countAllResults();
    }

    /**
     * Metrik dashboard: total penjualan (sum total_harga) di bulan berjalan.
     * Mengandalkan kolom pemesanan.total_harga yang di-update oleh trigger.
     * (opsional: filter hanya status 'Selesai')
     */
    public function getPenjualanBulan(): float
{
    $row = $this->selectSum('total_harga')
                ->where('MONTH(created_at)', date('m'))
                ->where('YEAR(created_at)', date('Y'))
                ->where('status_pemesanan', 'Selesai') // hanya hitung pesanan selesai
                ->get()
                ->getRow();

    return (float) ($row->total_harga ?? 0);
}

    public function getFiltered($start = null, $end = null)
    {
        $builder = $this->select('pesanan.*, users.nama as nama_pembeli, produk.nama_produk, produk.harga_produk')
            ->join('users', 'users.id_user = pesanan.id_user')
            ->join('produk', 'produk.id_produk = pesanan.id_produk');

        if ($start && $end) {
            $builder->where("DATE(pesanan.created_at) >=", $start)
                    ->where("DATE(pesanan.created_at) <=", $end);
        }

        return $builder->findAll();
    }

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
            // penting: raw where untuk IS NULL
            ->where('(dp.user_rating IS NULL OR dp.user_rating = 0)', null, false)
            ->orderBy('p.created_at', 'DESC')
            ->get()->getResultArray();
    }

    
}
