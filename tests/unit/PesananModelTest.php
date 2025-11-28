<?php

namespace Tests\Unit;

use App\Models\PesananModel;
use CodeIgniter\Test\CIUnitTestCase;

/**
 * Fake DB untuk autoCloseExpired (hanya butuh affectedRows()).
 */
class FakePesananDb
{
    /** @var int */
    public int $affectedRows = 0;

    public function affectedRows(): int
    {
        return $this->affectedRows;
    }
}

/**
 * Fake Query Builder minimal untuk autoCloseExpired().
 * Fokusnya cuma nyimpen where/set dan menandai kalau update() dipanggil.
 */
class FakePesananBuilder
{
    /** @var array<int, array{0:mixed,1:mixed,2:mixed}> */
    public array $whereCalls = [];

    /** @var array<string, mixed> */
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
    /**
     * Cek konfigurasi metadata model:
     * - nama tabel
     * - primary key
     * - allowedFields
     * - useTimestamps
     */
    public function testMetadataPesananModelTerkonfigurasiDenganBenar(): void
    {
        $ref = new \ReflectionClass(PesananModel::class);

        // $table
        $this->assertTrue(
            $ref->hasProperty('table'),
            'PesananModel harus punya properti $table.'
        );
        $propTable = $ref->getProperty('table');
        $propTable->setAccessible(true);
        $table = $propTable->getDefaultValue();
        $this->assertSame('pemesanan', $table);

        // $primaryKey
        $this->assertTrue(
            $ref->hasProperty('primaryKey'),
            'PesananModel harus punya properti $primaryKey.'
        );
        $propPk = $ref->getProperty('primaryKey');
        $propPk->setAccessible(true);
        $primaryKey = $propPk->getDefaultValue();
        $this->assertSame('id_pemesanan', $primaryKey);

        // $allowedFields
        $this->assertTrue(
            $ref->hasProperty('allowedFields'),
            'PesananModel harus punya properti $allowedFields.'
        );
        $propAllowed = $ref->getProperty('allowedFields');
        $propAllowed->setAccessible(true);
        $allowed = $propAllowed->getDefaultValue();

        $this->assertIsArray($allowed);
        $this->assertNotEmpty($allowed);

        // Kolom penting wajib ada
        foreach (['id_user', 'id_alamat', 'id_pembayaran', 'total_harga', 'status_pemesanan'] as $kolom) {
            $this->assertContains(
                $kolom,
                $allowed,
                "Kolom '{$kolom}' harus ada di allowedFields PesananModel."
            );
        }

        // Kolom yang tidak boleh ada
        $this->assertNotContains(
            'kolom_tidak_ada',
            $allowed,
            'Kolom fiktif tidak boleh ada di allowedFields.'
        );

        // $useTimestamps
        $this->assertTrue(
            $ref->hasProperty('useTimestamps'),
            'PesananModel harus punya properti $useTimestamps.'
        );
        $propTs = $ref->getProperty('useTimestamps');
        $propTs->setAccessible(true);
        $useTimestamps = $propTs->getDefaultValue();
        $this->assertTrue($useTimestamps, 'useTimestamps di PesananModel seharusnya true.');
    }

    /**
     * markAsShippedWithToken() harus:
     * - memanggil update() sekali
     * - dengan id yang benar
     * - status_pemesanan = 'Dikirim'
     * - punya konfirmasi_token hex non-kosong
     * - konfirmasi_expires_at ~ 7 hari dari sekarang
     * - confirmed_at = null
     */
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

        $this->assertTrue($result, 'markAsShippedWithToken harus mengembalikan true jika update() true.');

        // Cek ID dan struktur dasar payload
        $this->assertSame(42, $capturedId);
        $this->assertIsArray($capturedData);

        $this->assertSame('Dikirim', $capturedData['status_pemesanan'] ?? null);

        // Token harus string hex non-kosong (hasil bin2hex(random_bytes(16)) = 32 char)
        $this->assertArrayHasKey('konfirmasi_token', $capturedData);
        $this->assertIsString($capturedData['konfirmasi_token']);
        $this->assertMatchesRegularExpression('/^[0-9a-f]{32}$/', $capturedData['konfirmasi_token']);

        // confirmed_at harus null
        $this->assertArrayHasKey('confirmed_at', $capturedData);
        $this->assertNull($capturedData['confirmed_at']);

        // konfirmasi_expires_at kira-kira 7 hari dari sekarang
        $this->assertArrayHasKey('konfirmasi_expires_at', $capturedData);
        $expiresTs = strtotime($capturedData['konfirmasi_expires_at']);
        $this->assertIsInt($expiresTs, 'konfirmasi_expires_at harus parsable oleh strtotime().');
        $this->assertGreaterThan(
            $now,
            $expiresTs,
            'konfirmasi_expires_at harus di masa depan.'
        );
        $this->assertLessThanOrEqual(
            strtotime('+8 days', $now),
            $expiresTs,
            'konfirmasi_expires_at seharusnya sekitar +7 hari (tidak lebih dari +8 hari).'
        );

        // updated_at diisi string non-kosong
        $this->assertArrayHasKey('updated_at', $capturedData);
        $this->assertNotEmpty($capturedData['updated_at']);
    }

    /**
     * autoCloseExpired() harus:
     * - menggunakan builder() untuk menyusun where
     * - memanggil update() di builder
     * - mengembalikan nilai dari $db->affectedRows()
     */
    public function testAutoCloseExpiredMemakaiBuilderDanMengembalikanAffectedRows(): void
    {
        $fakeBuilder          = new FakePesananBuilder();
        $fakeDb               = new FakePesananDb();
        $fakeDb->affectedRows = 3;

        $model = $this->getMockBuilder(PesananModel::class)
            ->onlyMethods(['builder'])
            ->disableOriginalConstructor()
            ->getMock();

        // Stub builder() agar pakai FakePesananBuilder
        $model->method('builder')->willReturn($fakeBuilder);

        // Inject fake DB ke properti protected $db
        $ref   = new \ReflectionObject($model);
        $dbProp = null;

        for ($cls = $ref; $cls !== false; $cls = $cls->getParentClass()) {
            if ($cls->hasProperty('db')) {
                $dbProp = $cls->getProperty('db');
                break;
            }
        }

        $this->assertNotNull($dbProp, 'Properti $db tidak ditemukan di hierarki PesananModel.');
        $dbProp->setAccessible(true);
        $dbProp->setValue($model, $fakeDb);

        // Jalankan method yang diuji
        $result = $model->autoCloseExpired();

        // Harus mengembalikan affectedRows dari fake DB
        $this->assertSame(3, $result);

        // Pastikan update() di builder kepanggil
        $this->assertTrue(
            $fakeBuilder->updateCalled,
            'autoCloseExpired harus memanggil update() pada builder.'
        );

        // Pastikan ada beberapa kondisi where yang diset
        $this->assertNotEmpty(
            $fakeBuilder->whereCalls,
            'autoCloseExpired harus menyusun kondisi WHERE.'
        );

        // Cek ada where untuk status_pemesanan = Dikirim
        $foundStatusWhere = false;
        foreach ($fakeBuilder->whereCalls as [$key, $value]) {
            if ($key === 'status_pemesanan' && $value === 'Dikirim') {
                $foundStatusWhere = true;
                break;
            }
        }
        $this->assertTrue(
            $foundStatusWhere,
            'autoCloseExpired harus mem-filter status_pemesanan = Dikirim.'
        );

        // Cek bahwa set() mengandung status_pemesanan => 'Selesai'
        $this->assertArrayHasKey('status_pemesanan', $fakeBuilder->setData);
        $this->assertSame('Selesai', $fakeBuilder->setData['status_pemesanan']);
    }
}
