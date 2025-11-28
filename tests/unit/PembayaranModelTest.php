<?php

namespace Tests\Unit;

use CodeIgniter\Test\CIUnitTestCase;
use App\Models\PembayaranModel;

/**
 * Versi testable dari PembayaranModel:
 * - Tidak memanggil constructor Model (jadi tidak menyentuh DB).
 * - Override where() dan first() agar:
 *   - Mencatat parameter pemanggilan.
 *   - Mengembalikan data dummy yang sudah diset oleh test.
 * - Override insert() supaya penyimpanan dummy bisa dicek tanpa DB.
 */
class TestablePembayaranModel extends PembayaranModel
{
    /** @var array<int,array{0:mixed,1:mixed,2:mixed}> */
    public array $whereCalls = [];

    /** @var array|null hasil yang akan dikembalikan oleh first() */
    public ?array $firstResult = null;

    /** @var array<int,array> daftar row yang "disimpan" melalui insert() */
    public array $insertCalls = [];

    public function __construct(?array $firstResult = null)
    {
        // JANGAN panggil parent::__construct() supaya tidak inisialisasi DB
        $this->firstResult = $firstResult;
    }

    /**
     * Fake dari Model::where() → catat parameter & support chaining.
     */
    public function where($key, $value = null, ?string $escape = null)
    {
        $this->whereCalls[] = [$key, $value, $escape];
        return $this; // chaining
    }

    /**
     * Fake dari Model::first()
     * Signature harus kompatibel dengan BaseModel::first(?string $column = null)
     */
    public function first(?string $column = null)
    {
        return $this->firstResult;
    }

    /**
     * Fake dari Model::insert()
     * Signature harus kompatibel dengan BaseModel::insert($data = null, bool $returnID = true)
     */
    public function insert($data = null, bool $returnID = true)
    {
        if ($data === null) {
            return 0;
        }

        // Auto-ID sederhana berdasarkan jumlah insert yang sudah tercatat
        $id = count($this->insertCalls) + 1;

        // Simulasi perilaku allowedFields: hanya simpan field yang diizinkan
        if (!empty($this->allowedFields) && is_array($data)) {
            $data = array_intersect_key($data, array_flip($this->allowedFields));
        }

        // Tambahkan primary key ke row (pakai id_pembayaran)
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

            // field liar yang seharusnya tidak ikut tersimpan
            'field_illegal'      => 'harus_dibuang',
        ];

        $id = $model->insert($data);

        // ID auto-increment dummy → harus 1 di insert pertama
        $this->assertSame(1, $id);

        // Pastikan ada tepat 1 insert yang tercatat
        $this->assertCount(1, $model->insertCalls);

        $row = $model->insertCalls[0];

        // Primary key ikut terset
        $this->assertSame($id, $row['id_pembayaran']);

        // Beberapa field penting harus tersimpan
        $this->assertSame('Midtrans', $row['gateway']);
        $this->assertSame('ORD12345', $row['order_id']);
        $this->assertSame('bank_transfer', $row['payment_type']);
        $this->assertSame(150000, $row['gross_amount']);
        $this->assertSame('pending', $row['transaction_status']);
        $this->assertSame('Belum Lunas', $row['status_bayar']);

        // Field yang tidak ada di allowedFields harus dibuang
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

        // Model testable dengan firstResult dummy
        $model = new TestablePembayaranModel($dummy);

        // Panggil method asli yang ingin diuji
        $result = $model->findByOrderId($orderId);

        // 1) Output harus sama persis dengan dummy
        $this->assertSame($dummy, $result);
        $this->assertIsArray($result);
        $this->assertSame($orderId, $result['order_id']);
        $this->assertSame('Lunas', $result['status_bayar']);

        // 2) Pastikan query yang dibangun benar → where('order_id', $orderId)
        $this->assertCount(1, $model->whereCalls);
        $this->assertSame(['order_id', $orderId, null], $model->whereCalls[0]);
    }

    public function testFindByOrderIdReturnsNullWhenNotFound(): void
    {
        $model = new TestablePembayaranModel(null);

        $result = $model->findByOrderId('ORDER_TIDAK_ADA');

        $this->assertNull($result);

        // Tetap harus memanggil where('order_id', ...)
        $this->assertCount(1, $model->whereCalls);
        $this->assertSame(['order_id', 'ORDER_TIDAK_ADA', null], $model->whereCalls[0]);
    }
}
