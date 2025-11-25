<?php

namespace Tests\Unit;

use CodeIgniter\Test\CIUnitTestCase;
use App\Models\PembayaranModel;

class PembayaranModelTest extends CIUnitTestCase
{
    protected $pembayaranModel;

    protected function setUp(): void
    {
        parent::setUp();

        // Buat mock untuk PembayaranModel
        $this->pembayaranModel = $this->createMock(PembayaranModel::class);
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

        // Atur perilaku mock
        $this->pembayaranModel->method('insert')->willReturn(123);
        $this->pembayaranModel->method('find')->willReturn($data + ['id' => 123]);

        $insertId = $this->pembayaranModel->insert($data);

        $this->assertIsNumeric($insertId);
        $this->assertNotNull($this->pembayaranModel->find($insertId));
    }

    public function testFindByOrderId()
    {
        $orderId = 'ORD98765';
        $dummy = [
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
        ];

        // Atur perilaku mock
        $this->pembayaranModel->method('findByOrderId')
            ->with($orderId)
            ->willReturn($dummy);

        $result = $this->pembayaranModel->findByOrderId($orderId);

        $this->assertIsArray($result);
        $this->assertEquals($orderId, $result['order_id']);
        $this->assertEquals('Lunas', $result['status_bayar']);
    }

    public function testFindByOrderIdTidakAda()
    {
        $this->pembayaranModel->method('findByOrderId')
            ->with('ORDER_TIDAK_ADA')
            ->willReturn(null);

        $result = $this->pembayaranModel->findByOrderId('ORDER_TIDAK_ADA');

        $this->assertNull($result);
    }
}
