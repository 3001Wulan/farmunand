<?php

namespace Tests\Unit;

use App\Models\PenilaianModel;
use CodeIgniter\Test\CIUnitTestCase;

class TestablePenilaianModel extends PenilaianModel
{
    public array $selectCalls = [];
    public array $joinCalls = [];
    public array $whereCalls = [];
    public array $findAllCalls = [];
    private array $dummyResult;

    public function __construct(array $dummyResult = [])
    {
        $this->dummyResult = $dummyResult;
    }

    public function select($fields, $escape = null)
    {
        $this->selectCalls[] = [$fields, $escape];
        return $this;
    }

    public function join($table, $cond, $type = '', $escape = null)
    {
        $this->joinCalls[] = [$table, $cond, $type, $escape];
        return $this;
    }

    public function where($key, $value = null, $escape = null)
    {
        $this->whereCalls[] = [$key, $value, $escape];
        return $this;
    }

    public function findAll($limit = null, $offset = 0)
    {
        $this->findAllCalls[] = [$limit, $offset];
        return $this->dummyResult;
    }
}

class PenilaianModelTest extends CIUnitTestCase
{
    private TestablePenilaianModel $model;

    protected function setUp(): void
    {
        parent::setUp();
        $this->model = new TestablePenilaianModel();
    }

    public function testModelBisaDiinstansiasi(): void
    {
        $this->assertInstanceOf(PenilaianModel::class, $this->model);
    }

    public function testNamaTabelDanPrimaryKeyTidakKosong(): void
    {
        $refClass = new \ReflectionClass(PenilaianModel::class);

        if ($refClass->hasProperty('table')) {
            $propTable = $refClass->getProperty('table');
            $propTable->setAccessible(true);
            $table = $propTable->getValue($this->model);

            $this->assertNotEmpty($table);
            $this->assertSame('detail_pemesanan', $table);
        } else {
            $this->assertTrue(true);
        }

        if ($refClass->hasProperty('primaryKey')) {
            $propPK = $refClass->getProperty('primaryKey');
            $propPK->setAccessible(true);
            $primaryKey = $propPK->getValue($this->model);

            $this->assertNotEmpty($primaryKey);
            $this->assertSame('id_detail_pemesanan', $primaryKey);
        } else {
            $this->assertTrue(true);
        }
    }

    public function testAllowedFieldsBerupaArrayDanTidakKosong(): void
    {
        $refClass = new \ReflectionClass(PenilaianModel::class);

        $this->assertTrue($refClass->hasProperty('allowedFields'));

        $prop = $refClass->getProperty('allowedFields');
        $prop->setAccessible(true);

        $allowedFields = $prop->getValue($this->model);

        $this->assertIsArray($allowedFields);
        $this->assertNotEmpty($allowedFields);

        $this->assertContains('id_pemesanan', $allowedFields);
        $this->assertContains('id_produk', $allowedFields);
        $this->assertContains('user_rating', $allowedFields);
        $this->assertContains('user_ulasan', $allowedFields);
        $this->assertContains('updated_at', $allowedFields);
    }

    public function testUseTimestampsPropertyTersetDenganBenar(): void
    {
        $refClass = new \ReflectionClass(PenilaianModel::class);

        if ($refClass->hasProperty('useTimestamps')) {
            $prop = $refClass->getProperty('useTimestamps');
            $prop->setAccessible(true);

            $useTimestamps = $prop->getValue($this->model);
            $this->assertIsBool($useTimestamps);
            $this->assertFalse($useTimestamps);
        } else {
            $this->assertTrue(true);
        }
    }

    public function testGetPenilaianByProdukBuildsQueryAndReturnsResult(): void
    {
        $idProduk = 5;

        $dummyRows = [
            [
                'id_detail_pemesanan' => 10,
                'id_pemesanan'        => 3,
                'id_produk'           => $idProduk,
                'jumlah_produk'       => 2,
                'harga_produk'        => 25000,
                'user_rating'         => 4,
                'user_ulasan'         => 'Mantap sekali',
                'user_media'          => null,
                'nama'                => 'Budi',
            ],
            [
                'id_detail_pemesanan' => 11,
                'id_pemesanan'        => 4,
                'id_produk'           => $idProduk,
                'jumlah_produk'       => 1,
                'harga_produk'        => 3000,
                'user_rating'         => 5,
                'user_ulasan'         => 'Enak banget',
                'user_media'          => 'foto1.jpg',
                'nama'                => 'Siti',
            ],
        ];

        $model = new TestablePenilaianModel($dummyRows);

        $result = $model->getPenilaianByProduk($idProduk);

        $this->assertSame($dummyRows, $result);
        $this->assertCount(2, $result);
        $this->assertSame(4, $result[0]['user_rating']);
        $this->assertSame('Mantap sekali', $result[0]['user_ulasan']);

        $this->assertCount(1, $model->selectCalls);
        $this->assertSame('penilaian.*, user.nama', $model->selectCalls[0][0]);

        $this->assertCount(1, $model->joinCalls);
        [$table, $cond, $type] = $model->joinCalls[0];
        $this->assertSame('user', $table);
        $this->assertSame('user.id_user = penilaian.id_user', $cond);
        $this->assertSame('', $type);

        $this->assertCount(1, $model->whereCalls);
        [$key, $val] = $model->whereCalls[0];
        $this->assertSame('penilaian.id_produk', $key);
        $this->assertSame($idProduk, $val);

        $this->assertCount(1, $model->findAllCalls);
        $this->assertSame([null, 0], $model->findAllCalls[0]);
    }

    public function testGetPenilaianByProdukReturnsEmptyArrayWhenNoData(): void
    {
        $idProduk = 999;
        $model    = new TestablePenilaianModel([]);

        $result = $model->getPenilaianByProduk($idProduk);

        $this->assertIsArray($result);
        $this->assertCount(0, $result);

        $this->assertCount(1, $model->whereCalls);
        [$key, $val] = $model->whereCalls[0];
        $this->assertSame('penilaian.id_produk', $key);
        $this->assertSame($idProduk, $val);
    }
}