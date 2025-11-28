<?php

namespace Tests\Unit;

use App\Models\PenilaianModel;
use CodeIgniter\Test\CIUnitTestCase;

/**
 * Versi testable dari PenilaianModel:
 * - Tidak memanggil constructor Model → tidak menyentuh DB.
 * - Override select(), join(), where(), findAll():
 *   • Mencatat pemanggilan (parameter-parameter).
 *   • Mengembalikan data dummy yang sudah diset oleh test.
 */
class TestablePenilaianModel extends PenilaianModel
{
    /** @var array<int,array{0:mixed,1:mixed}> */
    public array $selectCalls = [];

    /** @var array<int,array{0:mixed,1:mixed,2:mixed,3:mixed}> */
    public array $joinCalls = [];

    /** @var array<int,array{0:mixed,1:mixed,2:mixed}> */
    public array $whereCalls = [];

    /** @var array<int,array{0:mixed,1:mixed}> */
    public array $findAllCalls = [];

    /** @var array result dummy yang akan dikembalikan oleh findAll() */
    private array $dummyResult;

    public function __construct(array $dummyResult = [])
    {
        // JANGAN panggil parent::__construct()
        $this->dummyResult = $dummyResult;
    }

    // Fake dari Model::select($fields, ?bool $escape = null)
    public function select($fields, $escape = null)
    {
        $this->selectCalls[] = [$fields, $escape];
        return $this; // chaining
    }

    // Fake dari Model::join(string $table, string $cond, string $type = '', ?bool $escape = null)
    public function join($table, $cond, $type = '', $escape = null)
    {
        $this->joinCalls[] = [$table, $cond, $type, $escape];
        return $this;
    }

    // Fake dari Model::where($key, $value = null, ?string $escape = null)
    public function where($key, $value = null, $escape = null)
    {
        $this->whereCalls[] = [$key, $value, $escape];
        return $this;
    }

    // Fake dari Model::findAll(?int $limit = null, int $offset = 0)
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
        // default: tanpa dummy result
        $this->model = new TestablePenilaianModel();
    }

    public function testModelBisaDiinstansiasi(): void
    {
        $this->assertInstanceOf(PenilaianModel::class, $this->model);
    }

    public function testNamaTabelDanPrimaryKeyTidakKosong(): void
    {
        $refClass = new \ReflectionClass(PenilaianModel::class);

        // $table
        if ($refClass->hasProperty('table')) {
            $propTable = $refClass->getProperty('table');
            $propTable->setAccessible(true);
            $table = $propTable->getValue($this->model);

            $this->assertNotEmpty($table, 'Nama tabel ($table) di PenilaianModel kosong.');
            $this->assertSame('detail_pemesanan', $table);
        } else {
            $this->assertTrue(true);
        }

        // $primaryKey
        if ($refClass->hasProperty('primaryKey')) {
            $propPK = $refClass->getProperty('primaryKey');
            $propPK->setAccessible(true);
            $primaryKey = $propPK->getValue($this->model);

            $this->assertNotEmpty($primaryKey, 'Primary key ($primaryKey) di PenilaianModel kosong.');
            $this->assertSame('id_detail_pemesanan', $primaryKey);
        } else {
            $this->assertTrue(true);
        }
    }

    public function testAllowedFieldsBerupaArrayDanTidakKosong(): void
    {
        $refClass = new \ReflectionClass(PenilaianModel::class);

        $this->assertTrue(
            $refClass->hasProperty('allowedFields'),
            'PenilaianModel tidak memiliki properti $allowedFields.'
        );

        $prop = $refClass->getProperty('allowedFields');
        $prop->setAccessible(true);

        $allowedFields = $prop->getValue($this->model);

        $this->assertIsArray($allowedFields, 'allowedFields harus berupa array.');
        $this->assertNotEmpty($allowedFields, 'allowedFields di PenilaianModel tidak boleh kosong.');

        // beberapa field penting
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
            $this->assertFalse($useTimestamps, 'PenilaianModel seharusnya tidak memakai timestamps otomatis.');
        } else {
            $this->assertTrue(true);
        }
    }

    /** 🔹 Uji utama: getPenilaianByProduk menyusun query dengan benar dan mengembalikan hasil. */
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
                'harga_produk'        => 30000,
                'user_rating'         => 5,
                'user_ulasan'         => 'Enak banget',
                'user_media'          => 'foto1.jpg',
                'nama'                => 'Siti',
            ],
        ];

        // pakai instance baru dengan dummy result
        $model = new TestablePenilaianModel($dummyRows);

        $result = $model->getPenilaianByProduk($idProduk);

        // 1) Output harus sama dengan dummy
        $this->assertSame($dummyRows, $result);
        $this->assertCount(2, $result);
        $this->assertSame(4, $result[0]['user_rating']);
        $this->assertSame('Mantap sekali', $result[0]['user_ulasan']);

        // 2) Pastikan SELECT dipanggil dengan field yang benar
        $this->assertCount(1, $model->selectCalls);
        $this->assertSame('penilaian.*, user.nama', $model->selectCalls[0][0]);

        // 3) Pastikan JOIN ke tabel user dengan kondisi yang benar
        $this->assertCount(1, $model->joinCalls);
        [$table, $cond, $type] = $model->joinCalls[0];
        $this->assertSame('user', $table);
        $this->assertSame('user.id_user = penilaian.id_user', $cond);
        $this->assertSame('', $type);

        // 4) Pastikan WHERE memakai kolom id_produk yang benar
        $this->assertCount(1, $model->whereCalls);
        [$key, $val] = $model->whereCalls[0];
        $this->assertSame('penilaian.id_produk', $key);
        $this->assertSame($idProduk, $val);

        // 5) Pastikan findAll() dipanggil sekali
        $this->assertCount(1, $model->findAllCalls);
        $this->assertSame([null, 0], $model->findAllCalls[0]);
    }

    /** 🔹 Uji: jika tidak ada data, harus kembali array kosong dan query tetap benar. */
    public function testGetPenilaianByProdukReturnsEmptyArrayWhenNoData(): void
    {
        $idProduk = 999;
        $model    = new TestablePenilaianModel([]); // tidak ada hasil

        $result = $model->getPenilaianByProduk($idProduk);

        $this->assertIsArray($result);
        $this->assertCount(0, $result);

        // WHERE tetap harus dipanggil dengan ID tersebut
        $this->assertCount(1, $model->whereCalls);
        [$key, $val] = $model->whereCalls[0];
        $this->assertSame('penilaian.id_produk', $key);
        $this->assertSame($idProduk, $val);
    }
}
