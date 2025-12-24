<?php

namespace Tests\Unit;

use CodeIgniter\Test\CIUnitTestCase;
use App\Models\PembayaranModel;

class TestablePembayaranModel extends PembayaranModel
{
    public array $whereCalls = [];
    public ?array $firstResult = null;
    public array $insertCalls = [];

    public function __construct(?array $firstResult = null)
    {
        $this->firstResult = $firstResult;
    }

    public function where($key, $value = null, ?string $escape = null)
    {
        $this->whereCalls[] = [$key, $value, $escape];
        return $this;
    }

    public function first(?string $column = null)
    {
        return $this->firstResult;
    }

    public function insert($data = null, bool $returnID = true)
    {
        if ($data === null) {
            return 0;
        }

        $id = count($this->insertCalls) + 1;

        if (!empty($this->allowedFields) && is_array($data)) {
            $data = array_intersect_key($data, array_flip($this->allowedFields));
        }

        if (is_array($data)) {
            $data[$this->primaryKey ?? 'id_pembayaran'] = $id;
        }

        $this->insertCalls[] = $data;

        return $returnID ? $id : true;
    }
}

class PembayaranModelTest extends CIUnitTestCase
{
    public function testInsertPembayaranRespectsAllowedFieldsAndReturnsId(): void
    {
        $model = new TestablePembayaranModel();

        $data = [
            'gateway'            => 'Midtrans',
            'order_id'           => 'ORD12345',
            'snap_token'         => 'token123',
            'redirect_url'       => 'https://midtrans.com/pay',
            'payment_type'       => 'bank_transfer',
            'gross_amount'       => 150000,
            'transaction_status' => 'pending',
            'fraud_status'       => 'accept',
            'va_number'          => '1234567890',
            'pdf_url'            => 'https://midtrans.com/invoice',
            'payload'            => json_encode(['sample' => 'data']),
            'metode'             => 'Transfer Bank',
            'referensi'          => 'REF123',
            'status_bayar'       => 'Belum Lunas',
            'field_illegal'      => 'harus_dibuang',
        ];

        $id = $model->insert($data);

        $this->assertSame(1, $id);
        $this->assertCount(1, $model->insertCalls);

        $row = $model->insertCalls[0];

        $this->assertSame($id, $row['id_pembayaran']);
        $this->assertSame('Midtrans', $row['gateway']);
        $this->assertSame('ORD12345', $row['order_id']);
        $this->assertSame('bank_transfer', $row['payment_type']);
        $this->assertSame(150000, $row['gross_amount']);
        $this->assertSame('pending', $row['transaction_status']);
        $this->assertSame('Belum Lunas', $row['status_bayar']);

        $this->assertArrayNotHasKey('field_illegal', $row);
    }

    public function testFindByOrderIdBuildsCorrectWhereAndReturnsRow(): void
    {
        $orderId = 'ORD98765';

        $dummy = [
            'id_pembayaran'      => 10,
            'gateway'            => 'Midtrans',
            'order_id'           => $orderId,
            'snap_token'         => 'snap987',
            'redirect_url'       => 'https://midtrans.com/pay/987',
            'payment_type'       => 'credit_card',
            'gross_amount'       => 250000,
            'transaction_status' => 'settlement',
            'fraud_status'       => 'accept',
            'va_number'          => '9876543210',
            'pdf_url'            => 'https://midtrans.com/invoice/987',
            'payload'            => json_encode(['id' => 987]),
            'metode'             => 'Kartu Kredit',
            'referensi'          => 'REF987',
            'status_bayar'       => 'Lunas',
        ];

        $model = new TestablePembayaranModel($dummy);

        $result = $model->findByOrderId($orderId);

        $this->assertSame($dummy, $result);
        $this->assertIsArray($result);
        $this->assertSame($orderId, $result['order_id']);
        $this->assertSame('Lunas', $result['status_bayar']);

        $this->assertCount(1, $model->whereCalls);
        $this->assertSame(['order_id', $orderId, null], $model->whereCalls[0]);
    }

    public function testFindByOrderIdReturnsNullWhenNotFound(): void
    {
        $model = new TestablePembayaranModel(null);

        $result = $model->findByOrderId('ORDER_TIDAK_ADA');

        $this->assertNull($result);

        $this->assertCount(1, $model->whereCalls);
        $this->assertSame(['order_id', 'ORDER_TIDAK_ADA', null], $model->whereCalls[0]);
    }
}