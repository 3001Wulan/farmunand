<?php

namespace Tests\Unit;

use CodeIgniter\Test\CIUnitTestCase;
use App\Models\AlamatModel;

/**
 * Versi testable dari AlamatModel:
 * - Tidak memanggil constructor Model (jadi tidak nyentuh DB).
 * - Override where() dan findAll() agar:
 *   - Mencatat parameter pemanggilan.
 *   - Mengembalikan data dummy yang sudah kita set di test.
 */
class TestableAlamatModel extends AlamatModel
{
    /** @var array<int,array{0:mixed,1:mixed,2:mixed}> */
    public array $whereCalls = [];

    /** @var array<int,array> */
    public array $resultToReturn = [];

    public function __construct(array $resultToReturn = [])
    {
        // JANGAN panggil parent::__construct() supaya tidak inisialisasi DB
        $this->resultToReturn = $resultToReturn;
    }

    /**
     * Fake dari Model::where() → cuma nyimpan parameter dan support chaining.
     */
    public function where($key, $value = null, ?string $escape = null)
    {
        $this->whereCalls[] = [$key, $value, $escape];
        return $this; // chaining
    }

    /**
     * Fake dari Model::findAll()
     * Signature harus kompatibel dengan BaseModel::findAll(?int $limit = null, int $offset = 0)
     */
    public function findAll(?int $limit = null, int $offset = 0): array
    {
        return $this->resultToReturn;
    }
}

class AlamatModelTest extends CIUnitTestCase
{
    public function testGetAlamatAktifByUserBuildsCorrectQueryAndReturnsData(): void
    {
        $expected = [
            [
                'id_alamat'     => 10,
                'id_user'       => 2,
                'nama_penerima' => 'Siti Aminah',
                'aktif'         => 1,
            ],
            [
                'id_alamat'     => 11,
                'id_user'       => 2,
                'nama_penerima' => 'Budi',
                'aktif'         => 1,
            ],
        ];

        // Model testable dengan hasil dummy
        $model = new TestableAlamatModel($expected);

        // Panggil method yang benar-benar ingin kita uji
        $result = $model->getAlamatAktifByUser(2);

        // 1) Hasil pengembalian harus sama dengan dummy
        $this->assertSame($expected, $result);
        $this->assertIsArray($result);
        $this->assertCount(2, $result);

        foreach ($result as $row) {
            $this->assertSame(2, $row['id_user']);
            $this->assertSame(1, $row['aktif']);
        }

        // 2) Pastikan query yang dibangun benar → urutan where()
        $this->assertCount(2, $model->whereCalls);

        $this->assertSame(['id_user', 2, null], $model->whereCalls[0]);
        $this->assertSame(['aktif', 1, null], $model->whereCalls[1]);
    }

    public function testGetAlamatAktifByUserReturnsEmptyArrayWhenNoData(): void
    {
        $model = new TestableAlamatModel([]);

        $result = $model->getAlamatAktifByUser(9999);

        $this->assertIsArray($result);
        $this->assertCount(0, $result);

        // Tetap harus memanggil dua where()
        $this->assertCount(2, $model->whereCalls);
        $this->assertSame(['id_user', 9999, null], $model->whereCalls[0]);
        $this->assertSame(['aktif', 1, null], $model->whereCalls[1]);
    }

    public function testGetAlamatAktifByUserAcceptsStringUserId(): void
    {
        $model = new TestableAlamatModel([]);

        $result = $model->getAlamatAktifByUser('5');

        $this->assertIsArray($result);
        $this->assertCount(0, $result);

        // id_user dikirim apa adanya ('5'), sesuai pemanggilan kita
        $this->assertCount(2, $model->whereCalls);
        $this->assertSame(['id_user', '5', null], $model->whereCalls[0]);
        $this->assertSame(['aktif', 1, null], $model->whereCalls[1]);
    }
}
