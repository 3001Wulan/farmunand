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
     * Join pesanan + detail + produk untuk user tertentu.
     */
    public function getPesananWithProduk($id_user)
    {
        return $this->select('
                    pemesanan.id_pemesanan,
                    produk.id_produk,
                    produk.nama_produk,
                    produk.harga,
                    produk.foto,
                    detail_pemesanan.jumlah_produk,
                    pemesanan.status_pemesanan
                ')
                ->join('detail_pemesanan', 'detail_pemesanan.id_pemesanan = pemesanan.id_pemesanan')
                ->join('produk', 'produk.id_produk = detail_pemesanan.id_produk')
                ->where('pemesanan.id_user', $id_user)
                ->findAll();
    }

    /**
     * Join pesanan + detail + produk untuk user tertentu dengan filter status.
     */
    public function getPesananByStatus($id_user, $status)
    {
        return $this->select('
                    pemesanan.id_pemesanan,
                    produk.id_produk,
                    produk.nama_produk,
                    produk.harga,
                    produk.foto,
                    detail_pemesanan.jumlah_produk,
                    pemesanan.status_pemesanan
                ')
                ->join('detail_pemesanan', 'detail_pemesanan.id_pemesanan = pemesanan.id_pemesanan')
                ->join('produk', 'produk.id_produk = detail_pemesanan.id_produk')
                ->where('pemesanan.id_user', $id_user)
                ->where('pemesanan.status_pemesanan', $status)
                ->findAll();
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
                    // ->where('status_pemesanan', 'Selesai') // aktifkan jika hanya hitung order selesai
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
            ->whereIn('p.status_pemesanan', ['Selesai','Dikirim','Diterima','Dikemas'])
            // penting: raw where untuk IS NULL
            ->where('(dp.user_rating IS NULL OR dp.user_rating = 0)', null, false)
            ->orderBy('p.created_at', 'DESC')
            ->get()->getResultArray();
    }

}
