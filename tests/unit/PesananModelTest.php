<?php

namespace Tests\Unit;

use App\Models\PesananModel;
use CodeIgniter\Test\CIUnitTestCase;

class FakePesananDb
{
    public int $affectedRows = 0;

    public function affectedRows(): int
    {
        return $this->affectedRows;
    }
}

class FakePesananBuilder
{
    public array $whereCalls = [];
    public array $setData = [];
    public bool $updateCalled = false;

    public function where($key, $value = null, $escape = null)
    {
        $this->whereCalls[] = [$key, $value, $escape];
        return $this;
    }

    public function whereIn($key, $values = null, ?string $escape = null)
    {
        $this->whereCalls[] = ["whereIn:{$key}", $values, $escape];
        return $this;
    }

    public function set($key, $value = null, $escape = null)
    {
        if (is_array($key)) {
            $this->setData = array_merge($this->setData, $key);
        } else {
            $this->setData[$key] = $value;
        }
        return $this;
    }

    public function update()
    {
        $this->updateCalled = true;
        return true;
    }
}

final class PesananModelTest extends CIUnitTestCase
{
    public function testMetadataPesananModelTerkonfigurasiDenganBenar(): void
    {
        $ref = new \ReflectionClass(PesananModel::class);

        $this->assertTrue($ref->hasProperty('table'));
        $propTable = $ref->getProperty('table');
        $propTable->setAccessible(true);
        $table = $propTable->getDefaultValue();
        $this->assertSame('pemesanan', $table);

        $this->assertTrue($ref->hasProperty('primaryKey'));
        $propPk = $ref->getProperty('primaryKey');
        $propPk->setAccessible(true);
        $primaryKey = $propPk->getDefaultValue();
        $this->assertSame('id_pemesanan', $primaryKey);

        $this->assertTrue($ref->hasProperty('allowedFields'));
        $propAllowed = $ref->getProperty('allowedFields');
        $propAllowed->setAccessible(true);
        $allowed = $propAllowed->getDefaultValue();

        $this->assertIsArray($allowed);
        $this->assertNotEmpty($allowed);

        foreach (['id_user', 'id_alamat', 'id_pembayaran', 'total_harga', 'status_pemesanan'] as $kolom) {
            $this->assertContains($kolom, $allowed);
        }

        $this->assertNotContains('kolom_tidak_ada', $allowed);

        $this->assertTrue($ref->hasProperty('useTimestamps'));
        $propTs = $ref->getProperty('useTimestamps');
        $propTs->setAccessible(true);
        $useTimestamps = $propTs->getDefaultValue();
        $this->assertTrue($useTimestamps);
    }

    public function testMarkAsShippedWithTokenMenyusunPayloadUpdateDenganBenar(): void
    {
        $capturedId   = null;
        $capturedData = null;

        $model = $this->getMockBuilder(PesananModel::class)
            ->onlyMethods(['update'])
            ->disableOriginalConstructor()
            ->getMock();

        $model->expects($this->once())
            ->method('update')
            ->willReturnCallback(function ($id, $data) use (&$capturedId, &$capturedData) {
                $capturedId   = $id;
                $capturedData = $data;
                return true;
            });

        $now = time();

        $result = $model->markAsShippedWithToken(42);

        $this->assertTrue($result);
        $this->assertSame(42, $capturedId);
        $this->assertIsArray($capturedData);

        $this->assertSame('Dikirim', $capturedData['status_pemesanan'] ?? null);

        $this->assertArrayHasKey('konfirmasi_token', $capturedData);
        $this->assertIsString($capturedData['konfirmasi_token']);
        $this->assertMatchesRegularExpression('/^[0-9a-f]{32}$/', $capturedData['konfirmasi_token']);

        $this->assertArrayHasKey('confirmed_at', $capturedData);
        $this->assertNull($capturedData['confirmed_at']);

        $this->assertArrayHasKey('konfirmasi_expires_at', $capturedData);
        $expiresTs = strtotime($capturedData['konfirmasi_expires_at']);
        $this->assertIsInt($expiresTs);
        $this->assertGreaterThan($now, $expiresTs);
        $this->assertLessThanOrEqual(strtotime('+8 days', $now), $expiresTs);

        $this->assertArrayHasKey('updated_at', $capturedData);
        $this->assertNotEmpty($capturedData['updated_at']);
    }

    public function testAutoCloseExpiredMemakaiBuilderDanMengembalikanAffectedRows(): void
    {
        $fakeBuilder          = new FakePesananBuilder();
        $fakeDb               = new FakePesananDb();
        $fakeDb->affectedRows = 3;

        $model = $this->getMockBuilder(PesananModel::class)
            ->onlyMethods(['builder'])
            ->disableOriginalConstructor()
            ->getMock();

        $model->method('builder')->willReturn($fakeBuilder);

        $ref   = new \ReflectionObject($model);
        $dbProp = null;

        for ($cls = $ref; $cls !== false; $cls = $cls->getParentClass()) {
            if ($cls->hasProperty('db')) {
                $dbProp = $cls->getProperty('db');
                break;
            }
        }

        $this->assertNotNull($dbProp);
        $dbProp->setAccessible(true);
        $dbProp->setValue($model, $fakeDb);

        $result = $model->autoCloseExpired();

        $this->assertSame(3, $result);
        $this->assertTrue($fakeBuilder->updateCalled);
        $this->assertNotEmpty($fakeBuilder->whereCalls);

        $foundStatusWhere = false;
        foreach ($fakeBuilder->whereCalls as [$key, $value]) {
            if ($key === 'status_pemesanan' && $value === 'Dikirim') {
                $foundStatusWhere = true;
                break;
            }
        }
        $this->assertTrue($foundStatusWhere);

        $this->assertArrayHasKey('status_pemesanan', $fakeBuilder->setData);
        $this->assertSame('Selesai', $fakeBuilder->setData['status_pemesanan']);
    }
}