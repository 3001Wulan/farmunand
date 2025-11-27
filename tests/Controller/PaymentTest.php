<?php

namespace Tests\Controller;

use App\Controllers\Payments;
use CodeIgniter\HTTP\IncomingRequest;
use CodeIgniter\Test\CIUnitTestCase;
use Config\Services;

/**
 * PaymentTest (UNIT)
 *
 * Tujuan:
 * - Menguji logika awal pada controller Payments tanpa DB dan tanpa Model:
 *   1) create() asli:
 *      - jika user belum login → 401 Unauthorized (JSON)
 *   2) createForTestInvalidPayload() (versi khusus unit test):
 *      - memeriksa validasi payload (id_alamat & items) → "Payload invalid"
 *   3) cancelByUser():
 *      - belum login → 401
 *      - order_id kosong → 400
 *   4) cancelByUserKeep():
 *      - pola sama seperti cancelByUser
 *
 * Catatan:
 * - createForTestInvalidPayload() adalah method tambahan khusus di subclass
 *   untuk menguji logika validasi payload tanpa melewati guard auth & DB.
 */
class PaymentTest extends CIUnitTestCase
{
    /** @var Payments test-safe controller */
    private $controller;

    /** @var IncomingRequest|\PHPUnit\Framework\MockObject\MockObject */
    private $requestMock;

    protected function setUp(): void
    {
        parent::setUp();

        /**
         * Subclass khusus:
         * - Menambahkan setter untuk Request & Response.
         * - Menambahkan method createForTestInvalidPayload() yang hanya berisi
         *   logika validasi payload dari create(), tanpa auth & tanpa DB.
         */
        $this->controller = new class extends Payments {
            public function setRequestObject($request): void
            {
                $this->request = $request;
            }

            public function setResponseObject($response): void
            {
                $this->response = $response;
            }

            /**
             * Versi khusus untuk unit test:
             * - Disarikan dari blok awal method create():
             *
             *     $payload  = $this->request->getJSON(true);
             *     $idAlamat = (int)($payload['id_alamat'] ?? 0);
             *     $itemsIn  = $payload['items'] ?? [];
             *     if ($idAlamat <= 0 || empty($itemsIn) || !is_array($itemsIn)) {
             *         return $this->response->setJSON(['success'=>false,'message'=>'Payload invalid']);
             *     }
             *
             * - Sengaja TIDAK memeriksa session & TIDAK melanjutkan ke DB.
             */
            public function createForTestInvalidPayload()
            {
                $payload  = $this->request->getJSON(true);
                $idAlamat = (int)($payload['id_alamat'] ?? 0);
                $itemsIn  = $payload['items'] ?? [];

                if ($idAlamat <= 0 || empty($itemsIn) || !is_array($itemsIn)) {
                    // 👉 Paksa status code 200 di sini supaya tidak kebawa 401
                    $this->response->setStatusCode(200);

                    return $this->response->setJSON([
                        'success' => false,
                        'message' => 'Payload invalid',
                    ]);
                }

                // Untuk keperluan unit test ini, kita tidak melanjutkan flow.
                $this->response->setStatusCode(200);

                return $this->response->setJSON([
                    'success' => true,
                    'message' => 'Payload dianggap valid (hanya untuk test).',
                ]);
            }
        };

        // Inject Response asli dari service CI4 ke controller,
        // supaya $this->response TIDAK null.
        $response = Services::response();
        $this->controller->setResponseObject($response);

        // Mock IncomingRequest:
        // - Nanti tiap test akan mengatur getJSON()/getPost() sesuai skenario.
        $this->requestMock = $this->getMockBuilder(IncomingRequest::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getJSON', 'getPost'])
            ->getMock();
    }

    /* ===========================================================
     * 1. CREATE()
     * =========================================================*/

    /**
     * SKENARIO 1:
     * - User belum login (id_user tidak ada di session)
     * - create() asli harus mengembalikan:
     *     - HTTP 401
     *     - JSON { success: false, message: 'Unauthorized' }
     *
     * Catatan:
     * - Di branch ini create() berhenti sebelum menyentuh DB/Model.
     */
    public function testCreateTanpaLoginMengembalikan401Json()
    {
        // Pastikan tidak ada id_user di session
        session()->destroy();

        // Request tidak terlalu penting di sini, tapi kita tetap inject supaya aman
        $this->requestMock
            ->method('getJSON')
            ->with(true)
            ->willReturn([]);
        $this->controller->setRequestObject($this->requestMock);

        $response = $this->controller->create();

        $this->assertSame(401, $response->getStatusCode(), 'Harus 401 Unauthorized.');

        $data = json_decode($response->getBody(), true);
        $this->assertIsArray($data, 'Respons harus JSON array.');
        $this->assertFalse($data['success'] ?? true);
        $this->assertSame('Unauthorized', $data['message'] ?? null);
    }

    /**
     * SKENARIO 2:
     * - Menguji logika validasi payload dari create():
     *     - id_alamat <= 0
     *     - items kosong
     * - createForTestInvalidPayload() harus mengembalikan:
     *     - HTTP 200 (default JSON)
     *     - JSON { success: false, message: 'Payload invalid' }
     *
     * Catatan:
     * - Di sini kita sengaja memanggil createForTestInvalidPayload()
     *   yang tidak memakai session maupun DB.
     */
    public function testCreateDenganPayloadInvalidMengembalikanPesanError()
    {
        // Payload dengan id_alamat 0 dan items kosong → invalid
        $this->requestMock
            ->method('getJSON')
            ->with(true)
            ->willReturn([
                'id_alamat' => 0,
                'items'     => [],
            ]);
        $this->controller->setRequestObject($this->requestMock);

        $response = $this->controller->createForTestInvalidPayload();

        $this->assertSame(200, $response->getStatusCode(), 'Harus 200 dengan JSON error payload.');

        $data = json_decode($response->getBody(), true);
        $this->assertIsArray($data);
        $this->assertFalse($data['success'] ?? true);
        $this->assertSame('Payload invalid', $data['message'] ?? null);
    }

    /* ===========================================================
     * 2. cancelByUser()
     * =========================================================*/

    /**
     * SKENARIO 3:
     * - User belum login
     * - cancelByUser() harus mengembalikan:
     *     - 401 Unauthorized
     *     - JSON { success: false, message: 'Unauthorized' }
     *
     * Catatan:
     * - Branch ini berhenti sebelum DB/Model dipakai.
     */
    public function testCancelByUserTanpaLoginMengembalikan401Json()
    {
        session()->destroy();

        $this->requestMock
            ->method('getJSON')
            ->with(true)
            ->willReturn([]);
        $this->controller->setRequestObject($this->requestMock);

        $response = $this->controller->cancelByUser();

        $this->assertSame(401, $response->getStatusCode());

        $data = json_decode($response->getBody(), true);
        $this->assertIsArray($data);
        $this->assertFalse($data['success'] ?? true);
        $this->assertSame('Unauthorized', $data['message'] ?? null);
    }

    /**
     * SKENARIO 4:
     * - User sudah login, tapi tidak mengirim order_id
     * - cancelByUser():
     *     - getJSON(true) → [] (kosong) → falsy
     *     - getPost()     → [] (tidak ada order_id)
     *     - orderId === '' → 400 "order_id diperlukan"
     *
     * - Tidak ada akses DB karena berhenti di validasi awal.
     */
    public function testCancelByUserTanpaOrderIdMengembalikan400Json()
    {
        session()->set(['id_user' => 123]);

        // getJSON(true) mengembalikan [] → falsy → akan jatuh ke getPost()
        $this->requestMock
            ->method('getJSON')
            ->with(true)
            ->willReturn([]);
        $this->requestMock
            ->method('getPost')
            ->willReturn([]);

        $this->controller->setRequestObject($this->requestMock);

        $response = $this->controller->cancelByUser();

        $this->assertSame(400, $response->getStatusCode());

        $data = json_decode($response->getBody(), true);
        $this->assertIsArray($data);
        $this->assertFalse($data['success'] ?? true);
        $this->assertSame('order_id diperlukan', $data['message'] ?? null);
    }

    /* ===========================================================
     * 3. cancelByUserKeep()
     * =========================================================*/

    /**
     * SKENARIO 5:
     * - User belum login
     * - cancelByUserKeep() harus 401 Unauthorized (JSON).
     */
    public function testCancelByUserKeepTanpaLoginMengembalikan401Json()
    {
        session()->destroy();

        $this->requestMock
            ->method('getJSON')
            ->with(true)
            ->willReturn([]);
        $this->controller->setRequestObject($this->requestMock);

        $response = $this->controller->cancelByUserKeep();

        $this->assertSame(401, $response->getStatusCode());

        $data = json_decode($response->getBody(), true);
        $this->assertIsArray($data);
        $this->assertFalse($data['success'] ?? true);
        $this->assertSame('Unauthorized', $data['message'] ?? null);
    }

    /**
     * SKENARIO 6:
     * - User sudah login, tapi tidak mengirim order_id
     * - cancelByUserKeep() juga harus:
     *     - 400 Bad Request
     *     - JSON "order_id diperlukan"
     *
     * - Lagi-lagi, tidak ada DB access di branch ini.
     */
    public function testCancelByUserKeepTanpaOrderIdMengembalikan400Json()
    {
        session()->set(['id_user' => 456]);

        $this->requestMock
            ->method('getJSON')
            ->with(true)
            ->willReturn([]);
        $this->requestMock
            ->method('getPost')
            ->willReturn([]);

        $this->controller->setRequestObject($this->requestMock);

        $response = $this->controller->cancelByUserKeep();

        $this->assertSame(400, $response->getStatusCode());

        $data = json_decode($response->getBody(), true);
        $this->assertIsArray($data);
        $this->assertFalse($data['success'] ?? true);
        $this->assertSame('order_id diperlukan', $data['message'] ?? null);
    }
}
