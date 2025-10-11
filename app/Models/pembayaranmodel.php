<?php
namespace App\Models;

use CodeIgniter\Model;

class PembayaranModel extends Model
{
    protected $table      = 'pembayaran';
    protected $primaryKey = 'id_pembayaran';
    protected $returnType = 'array';

    // app/Models/PembayaranModel.php
    protected $allowedFields = [
        'gateway','order_id','snap_token','redirect_url', // ← pastikan ini ada
        'payment_type','gross_amount','transaction_status','fraud_status',
        'va_number','pdf_url','payload',
        'metode','referensi','status_bayar',              // ← opsional bila dipakai
        'created_at','updated_at'
    ];


    protected $useTimestamps = true;
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';

    // (opsional) helper kecil
    public function findByOrderId(string $orderId): ?array
    {
        return $this->where('order_id', $orderId)->first();
    }
}
