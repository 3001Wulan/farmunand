<?php

namespace Tests\Unit;

use CodeIgniter\Test\CIUnitTestCase;
use App\Models\PembayaranModel;
use Config\Database;

class PembayaranModelTest extends CIUnitTestCase
{
    protected $pembayaranModel;
    protected $db;

    protected function setUp(): void
    {
        parent::setUp();
        // koneksi ke DB asli
        $this->db = Database::connect();
        $this->db->transBegin(); // agar data tidak tersimpan permanen

        $this->pembayaranModel = new PembayaranModel();
    }

    protected function tearDown(): void
    {
        // rollback semua data uji
        if ($this->db->transStatus() === true) {
            $this->db->transRollback();
        }
        parent::tearDown();
    }

    public function testInsertPembayaran()
    {
        $data = [
            'gateway' => 'Midtrans',
            'order_id' => 'ORD12345',
            'snap_token' => 'token123',
            'redirect_url' => 'https://midtrans.com/pay',
            'payment_type' => 'bank_transfer',
            'gross_amount' => 150000,
            'transaction_status' => 'pending',
            'fraud_status' => 'accept',
            'va_number' => '1234567890',
            'pdf_url' => 'https://midtrans.com/invoice',
            'payload' => json_encode(['sample' => 'data']),
            'metode' => 'Transfer Bank',
            'referensi' => 'REF123',
            'status_bayar' => 'Belum Lunas',
        ];

        $insertId = $this->pembayaranModel->insert($data);

        $this->assertIsNumeric($insertId);
        $this->assertNotNull($this->pembayaranModel->find($insertId));
    }

    public function testFindByOrderId()
    {
        // buat data dummy
        $orderId = 'ORD98765';
        $this->pembayaranModel->insert([
            'gateway' => 'Midtrans',
            'order_id' => $orderId,
            'snap_token' => 'snap987',
            'redirect_url' => 'https://midtrans.com/pay/987',
            'payment_type' => 'credit_card',
            'gross_amount' => 250000,
            'transaction_status' => 'settlement',
            'fraud_status' => 'accept',
            'va_number' => '9876543210',
            'pdf_url' => 'https://midtrans.com/invoice/987',
            'payload' => json_encode(['id' => 987]),
            'metode' => 'Kartu Kredit',
            'referensi' => 'REF987',
            'status_bayar' => 'Lunas',
        ]);

        $result = $this->pembayaranModel->findByOrderId($orderId);

        $this->assertIsArray($result);
        $this->assertEquals($orderId, $result['order_id']);
        $this->assertEquals('Lunas', $result['status_bayar']);
    }

    public function testFindByOrderIdTidakAda()
    {
        $result = $this->pembayaranModel->findByOrderId('ORDER_TIDAK_ADA');
        $this->assertNull($result);
    }
}
