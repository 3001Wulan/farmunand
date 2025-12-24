<?php

namespace Tests\Unit;

use App\Models\ProdukModel;
use CodeIgniter\Test\CIUnitTestCase;

class TestableProdukModel extends ProdukModel
{
    public array $log = [];
    public int $countAllResultsReturn = 0;
    public $findReturn = null;
    public $firstReturn = null;
    public array $kategoriColumn = [];
    public array $rows = [];

    public function __construct()
    {
    }

    public function countAllResults(bool $reset = true, bool $test = false)
    {
        $this->log[] = ['countAllResults', $reset, $test];
        return $this->countAllResultsReturn;
    }

    public function where($field, $value = null)
    {
        $this->log[] = ['where', $field, $value];
        return $this;
    }

    public function orderBy($field, $direction = 'ASC', $escape = null)
    {
        $this->log[] = ['orderBy', $field, $direction];
        return $this;
    }

    public function limit(?int $limit = null, int $offset = 0)
    {
        $this->log[] = ['limit', $limit, $offset];
        return $this;
    }

    public function find($id = null)
    {
        $this->log[] = ['find', $id];
        return $this->findReturn ?? $this->rows;
    }

    public function first()
    {
        $this->log[] = ['first'];
        return $this->firstReturn;
    }

    public function select($field)
    {
        $this->log[] = ['select', $field];
        return $this;
    }

    public function groupBy($field)
    {
        $this->log[] = ['groupBy', $field];
        return $this;
    }

    public function findColumn(string $columnName)
    {
        $this->log[] = ['findColumn', $columnName];
        return $this->kategoriColumn;
    }

    public function table($tableName)
    {
        $this->log[] = ['table', $tableName];

        $outer = $this;

        return new class($outer)
        {
            private $outer;

            public function __construct($outer)
            {
                $this->outer = $outer;
            }

            public function like($field, $value)
            {
                $this->outer->log[] = ['like', $field, $value];
                return $this;
            }

            public function orLike($field, $value)
            {
                $this->outer->log[] = ['orLike', $field, $value];
                return $this;
            }

            public function findAll()
            {
                $this->outer->log[] = ['findAll(builder)'];
                return $this->outer->rows;
            }
        };
    }
}

class ProdukModelTest extends CIUnitTestCase
{
    private TestableProdukModel $model;

    protected function setUp(): void
    {
        parent::setUp();
        $this->model = new TestableProdukModel();
    }

    public function testMetadataProdukModelTerkonfigurasiBenar(): void
    {
        $ref = new \ReflectionClass(ProdukModel::class);

        $this->assertTrue($ref->hasProperty('table'));
        $propTable = $ref->getProperty('table');
        $propTable->setAccessible(true);
        $instanceForMeta = new class extends ProdukModel {
            public function __construct() {}
        };
        $this->assertSame('produk', $propTable->getValue($instanceForMeta));

        $this->assertTrue($ref->hasProperty('primaryKey'));
        $propPK = $ref->getProperty('primaryKey');
        $propPK->setAccessible(true);
        $this->assertSame('id_produk', $propPK->getValue($instanceForMeta));

        $this->assertTrue($ref->hasProperty('allowedFields'));
        $propAF = $ref->getProperty('allowedFields');
        $propAF->setAccessible(true);
        $allowed = $propAF->getValue($instanceForMeta);

        $this->assertIsArray($allowed);
        $this->assertContains('nama_produk', $allowed);
        $this->assertContains('stok', $allowed);
        $this->assertContains('harga', $allowed);
        $this->assertContains('kategori', $allowed);
    }

    public function testGetTotalProdukMemanggilCountAllResults(): void
    {
        $this->model->countAllResultsReturn = 10;

        $total = $this->model->getTotalProduk();

        $this->assertSame(10, $total);
        $this->assertSame(['countAllResults', true, false], $this->model->log[0]);
    }

    public function testGetStokRendahDefaultMemakaiWhereDanCountAllResults(): void
    {
        $this->model->countAllResultsReturn = 3;

        $result = $this->model->getStokRendah();

        $this->assertSame(3, $result);
        $this->assertSame(['where', 'stok <', 10], $this->model->log[0]);
        $this->assertSame('countAllResults', $this->model->log[1][0]);
    }

    public function testGetStokRendahDenganLimitCustom(): void
    {
        $this->model->countAllResultsReturn = 1;

        $result = $this->model->getStokRendah(5);

        $this->assertSame(1, $result);
        $this->assertSame(['where', 'stok <', 5], $this->model->log[0]);
    }

    public function testGetProdukRekomendasiMenyusunQueryDenganBenar(): void
    {
        $rows = [
            ['id_produk' => 3],
            ['id_produk' => 2],
        ];
        $this->model->findReturn = $rows;

        $result = $this->model->getProdukRekomendasi(2);

        $this->assertSame($rows, $result);
        $this->assertSame(['orderBy', 'id_produk', 'DESC'], $this->model->log[0]);
        $this->assertSame(['limit', 2, 0], $this->model->log[1]);
        $this->assertSame(['find', null], $this->model->log[2]);
    }

    public function testGetProdukByIdMemakaiWhereDanFirst(): void
    {
        $row = [
            'id_produk'   => 99,
            'nama_produk' => 'Test_Produk_X',
        ];
        $this->model->firstReturn = $row;

        $result = $this->model->getProdukById(99);

        $this->assertSame($row, $result);
        $this->assertSame(['where', 'id_produk', 99], $this->model->log[0]);
        $this->assertSame(['first'], $this->model->log[1]);
    }

    public function testSearchProdukMenyusunLikeOrLikeDanFindAll(): void
    {
        $this->model->rows = [
            ['nama_produk' => 'Test_Coklat', 'deskripsi' => 'Rasa manis'],
        ];

        $result = $this->model->searchProduk('Test_Coklat');

        $this->assertSame($this->model->rows, $result);

        $this->assertSame(['table', 'produk'], $this->model->log[0]);
        $this->assertSame(['like', 'nama_produk', 'Test_Coklat'], $this->model->log[1]);
        $this->assertSame(['orLike', 'deskripsi', 'Test_Coklat'], $this->model->log[2]);
        $this->assertSame(['findAll(builder)'], $this->model->log[3]);
    }

    public function testGetKategoriListMenggabungkanEnumDanDataTanpaDuplikasi(): void
    {
        $this->model->kategoriColumn = ['Snack', 'Makanan'];

        $result = $this->model->getKategoriList();

        $this->assertSame(['Makanan', 'Minuman', 'Lainnya', 'Snack'], $result);

        $this->assertSame(['select', 'kategori'], $this->model->log[0]);
        $this->assertSame(['groupBy', 'kategori'], $this->model->log[1]);
        $this->assertSame(['findColumn', 'kategori'], $this->model->log[2]);
    }

    public function testGetKategoriListTanpaDataTambahanMengembalikanEnumDefault(): void
    {
        $this->model->kategoriColumn = [];

        $result = $this->model->getKategoriList();

        $this->assertSame(['Makanan', 'Minuman', 'Lainnya'], $result);
    }
}