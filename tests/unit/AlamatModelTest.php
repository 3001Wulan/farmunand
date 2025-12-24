<?php

namespace Tests\Unit;

use CodeIgniter\Test\CIUnitTestCase;
use App\Models\AlamatModel;

class TestableAlamatModel extends AlamatModel
{
    public array $whereCalls = [];
    public array $resultToReturn = [];

    public function __construct(array $resultToReturn = [])
    {
        $this->resultToReturn = $resultToReturn;
    }

    public function where($key, $value = null, ?string $escape = null)
    {
        $this->whereCalls[] = [$key, $value, $escape];
        return $this;
    }

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

        $model = new TestableAlamatModel($expected);

        $result = $model->getAlamatAktifByUser(2);

        $this->assertSame($expected, $result);
        $this->assertIsArray($result);
        $this->assertCount(2, $result);

        foreach ($result as $row) {
            $this->assertSame(2, $row['id_user']);
            $this->assertSame(1, $row['aktif']);
        }

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

        $this->assertCount(2, $model->whereCalls);
        $this->assertSame(['id_user', '5', null], $model->whereCalls[0]);
        $this->assertSame(['aktif', 1, null], $model->whereCalls[1]);
    }
}