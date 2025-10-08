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
     */
    protected $allowedFields = [
        'id_user',
        'id_alamat',
        'id_pembayaran',
        'total_harga',
        'status_pemesanan',
        // ⬇️ kolom untuk flow konfirmasi user 7 hari
        'konfirmasi_token',
        'konfirmasi_expires_at',
        'confirmed_at',
        // timestamps
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
     * ⬇️ Mengikutkan konfirmasi_expires_at & alias 'harga' (dipakai view).
     */
    public function getPesananWithProduk($idUser, $status = null)
    {
        $builder = $this->db->table('pemesanan p')
            ->select('
                p.id_pemesanan,
                p.status_pemesanan,
                p.total_harga,
                p.created_at,
                p.konfirmasi_expires_at,
                u.nama AS nama_user,
                pr.nama_produk,
                pr.harga,               -- alias bawaan produk (jika perlu)
                pr.foto,
                dp.jumlah_produk,
                dp.harga_produk AS harga -- dipakai view sebagai unit price
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
     * Ambil pesanan user per status (dipakai di Pesanan::selesai(), dikemas(), dll)
     * ⬇️ Disamakan strukturnya dengan getPesananWithProduk agar view bisa render langsung.
     */
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
                dp.harga_produk AS harga
            ')
            ->join('detail_pemesanan dp', 'dp.id_pemesanan = p.id_pemesanan', 'inner')
            ->join('produk pr', 'pr.id_produk = dp.id_produk', 'left')
            ->where('p.id_user', $idUser)
            ->where('p.status_pemesanan', $status)
            ->orderBy('p.created_at', 'DESC')
            ->get()->getResultArray();
    }

    /**
     * (Opsional) Saat admin set ke "Dikirim", generate token + expiry 7 hari.
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
     * (Opsional) Auto-close: set Selesai untuk pesanan Dikirim yang lewat 7 hari.
     * Return: jumlah baris yang diupdate.
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
}
