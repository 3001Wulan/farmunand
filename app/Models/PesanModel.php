<?php

namespace App\Models;

use CodeIgniter\Model;

class PesanModel extends Model
{
    protected $table      = 'pemesanan';
    protected $primaryKey = 'id_pemesanan';
    protected $allowedFields = [
        'id_user','id_produk','quantity','total','pembayaran','status_pemesanan'
    ];

    public function getPesanMasuk()
    {
        return $this->where('status_pemesanan','belum_dibaca')->countAllResults();
    }

    public function getAllPesanan()
    {
        return $this->findAll();
    }

    public function getPesananById($id_pemesanan)
    {
        return $this->where('id_pemesanan',$id_pemesanan)->first();
    }

    public function getPesananByNama($nama)
    {
        return $this->join('users','users.id_user = pemesanan.id_user')
                    ->where('users.nama',$nama)
                    ->findAll();
    }
    public function getPenjualanBulan()
{
    return $this->selectSum('total')
        ->where('status_pemesanan', 'selesai')
        ->where('MONTH(tanggal)', date('m'))
        ->where('YEAR(tanggal)', date('Y'))
        ->first()['total'] ?? 0;
}

}
