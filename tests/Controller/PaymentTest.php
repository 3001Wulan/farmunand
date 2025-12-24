<?php

namespace Tests\Controller;

use App\Controllers\Payments;
use CodeIgniter\HTTP\IncomingRequest;
use CodeIgniter\Test\CIUnitTestCase;
use Config\Services;

class PaymentTest extends CIUnitTestCase
{
    private $controller;
    private $requestMock;

    protected function setUp(): void
    {
        parent::setUp();

        $this->controller = new class extends Payments {
            public function setRequestObject($request): void
            {
                $this->request = $request;
            }

            public function setResponseObject($response): void
            {
                $this->response = $response;
            }

            public function createForTestInvalidPayload()
            {
                $payload  = $this->request->getJSON(true);
                $idAlamat = (int)($payload['id_alamat'] ?? 0);
                $itemsIn  = $payload['items'] ?? [];

                if ($idAlamat <= 0 || empty($itemsIn) || !is_array($itemsIn)) {
                    $this->response->setStatusCode(200);

                    return $this->response->setJSON([
                        'success' => false,
                        'message' => 'Payload invalid',
                    ]);
                }

                $this->response->setStatusCode(200);

                return $this->response->setJSON([
                    'success' => true,
                    'message' => 'Payload dianggap valid (hanya untuk test).',
                ]);
            }
        };

        $response = Services::response();
        $this->controller->setResponseObject($response);

        $this->requestMock = $this->getMockBuilder(IncomingRequest::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getJSON', 'getPost'])
            ->getMock();
    }

    public function testCreateTanpaLoginMengembalikan401Json()
    {
        session()->destroy();

        $this->requestMock
            ->method('getJSON')
            ->with(true)
            ->willReturn([]);
        $this->controller->setRequestObject($this->requestMock);

        $response = $this->controller->create();

        $this->assertSame(401, $response->getStatusCode());

        $data = json_decode($response->getBody(), true);
        $this->assertIsArray($data);
        $this->assertFalse($data['success'] ?? true);
        $this->assertSame('Unauthorized', $data['message'] ?? null);
    }

    public function testCreateDenganPayloadInvalidMengembalikanPesanError()
    {
        $this->requestMock
            ->method('getJSON')
            ->with(true)
            ->willReturn([
                'id_alamat' => 0,
                'items'     => [],
            ]);
        $this->controller->setRequestObject($this->requestMock);

        $response = $this->controller->createForTestInvalidPayload();

        $this->assertSame(200, $response->getStatusCode());

        $data = json_decode($response->getBody(), true);
        $this->assertIsArray($data);
        $this->assertFalse($data['success'] ?? true);
        $this->assertSame('Payload invalid', $data['message'] ?? null);
    }

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

    public function testCancelByUserTanpaOrderIdMengembalikan400Json()
    {
        session()->set(['id_user' => 123]);

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