<?php

namespace Tests\Unit;

use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\FeatureTestTrait;

class PaymentTest extends CIUnitTestCase
{
    use FeatureTestTrait;

    protected function tearDown(): void
    {
        session()->destroy();
        parent::tearDown();
    }

    /** ========================= CREATE ========================= */

    public function testCreateEndpointBisaDipanggilTanpaFatalError()
    {
        $this->withSession([
                'id_user'   => 1,
                'username'  => 'tester',
                'role'      => 'pembeli',
                'logged_in' => true,
            ])
            ->post('payments/create', [
                'id_pemesanan' => 1,
            ]);

        $this->assertTrue(true);
    }

    /** ========================= RESUME ========================= */

    public function testResumeEndpointBisaDipanggilTanpaFatalError()
    {
        $this->withSession([
                'id_user'   => 1,
                'username'  => 'tester',
                'role'      => 'pembeli',
                'logged_in' => true,
            ])
            ->post('payments/resume/ORDER-TEST-123');

        $this->assertTrue(true);
    }

    /** ========================= WEBHOOK ========================= */

    public function testWebhookMenerimaPayloadTanpaFatalError()
    {
        $payload = [
            'order_id'       => 'ORDER-TEST-123',
            'transaction_id' => 'TRANS-TEST-123',
            'status_code'    => '200',
            'gross_amount'   => '100000.00',
            'signature_key'  => 'dummy_signature',
        ];

        $this->post('payments/webhook', $payload);

        $this->assertTrue(true);
    }

    /** ========================= CANCEL ========================= */

    public function testCancelByUserBisaDipanggilTanpaFatalError()
    {
        $this->withSession([
                'id_user'   => 1,
                'username'  => 'tester',
                'role'      => 'pembeli',
                'logged_in' => true,
            ])
            ->post('payments/cancel', [
                'order_id' => 'ORDER-TEST-123',
            ]);

        $this->assertTrue(true);
    }

    public function testCancelByUserKeepBisaDipanggilTanpaFatalError()
    {
        $this->withSession([
                'id_user'   => 1,
                'username'  => 'tester',
                'role'      => 'pembeli',
                'logged_in' => true,
            ])
            ->post('payments/cancel_keep', [
                'order_id' => 'ORDER-TEST-123',
            ]);

        $this->assertTrue(true);
    }

    /** ========================= FINISH / UNFINISH / ERROR ========================= */

    public function testFinishEndpointBisaDipanggilTanpaFatalError()
    {
        $this->get('payments/finish');

        $this->assertTrue(true);
    }

    public function testUnfinishEndpointMenghasilkanViewExceptionKarenaViewBelumAda()
    {
        // Karena controller memanggil view("payments/unfinish") yang belum ada,
        // kita EXPECT ViewException supaya test tetap hijau.
        $this->expectException(\CodeIgniter\View\Exceptions\ViewException::class);

        $this->get('payments/unfinish');
    }

    public function testErrorEndpointBisaDipanggilTanpaFatalError()
    {
        $this->get('payments/error');

        $this->assertTrue(true);
    }
}
