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
        'user_rating',   // INT/TINYINT
        'user_ulasan',   // TEXT
        'user_media',    // TEXT/VARCHAR (JSON list file)
        'updated_at',    // DATETIME (opsional)
    ];
}
