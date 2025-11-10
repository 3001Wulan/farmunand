<?php

// file: app/Models/PenilaianModel.php
namespace App\Models;

use CodeIgniter\Model;

class PenilaianModel extends Model
{
    protected $table         = 'detail_pemesanan';
    protected $primaryKey    = 'id_detail_pemesanan';
    protected $returnType    = 'array';
    protected $useTimestamps = false; // kita set updated_at manual

    // Kolom yang diizinkan untuk diupdate saat memberi penilaian
    protected $allowedFields = [
        'id_pemesanan',
        'id_produk',
        'jumlah_produk',
        'harga_produk',
        'user_rating',
        'user_ulasan',
        'user_media',
        'created_at',
        'updated_at',
    ];

}
