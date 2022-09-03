<?php /** @noinspection ForgottenDebugOutputInspection */
/** @noinspection PhpUnhandledExceptionInspection */
/** @noinspection PhpDeprecationInspection */

/** @noinspection PhpIllegalPsrClassPathInspection */

//<editor-fold desc="use">
use eftec\IPdoOneCache;
use eftec\PdoOne;
use PHPUnit\Framework\TestCase;
use repomysql\TableCategoryRepo;
use repomysql\TableChildRepo;
use repomysql\TableGrandChildRepo;
use repomysql\TableGrandChildTagRepo;
use repomysql\TableParentExtRepo;
use repomysql\TableParentRepo;
use repomysql\TableParentxCategoryRepo;

//</editor-fold>

//<editor-fold desc="includes">
//include __DIR__ . '/../lib/_BasePdoOneRepo.php';
include __DIR__ . '/../examples/repomysql/generated/TestDb.php';

include __DIR__ . '/../examples/repomysql/generated/AbstractTableGrandChildRepo.php';
include __DIR__ . '/../examples/repomysql/generated/TableGrandChildRepo.php';

include __DIR__ . '/../examples/repomysql/generated/AbstractTableCategoryRepo.php';
include __DIR__ . '/../examples/repomysql/generated/TableCategoryRepo.php';
include __DIR__ . '/../examples/repomysql/generated/AbstractTableChildRepo.php';
include __DIR__ . '/../examples/repomysql/generated/TableChildRepo.php';
include __DIR__ . '/../examples/repomysql/generated/AbstractTableGrandChildTagRepo.php';
include __DIR__ . '/../examples/repomysql/generated/TableGrandChildTagRepo.php';
include __DIR__ . '/../examples/repomysql/generated/AbstractTableParentRepo.php';
include __DIR__ . '/../examples/repomysql/generated/TableParentRepo.php';
include __DIR__ . '/../examples/repomysql/generated/AbstractTableParentExtRepo.php';
include __DIR__ . '/../examples/repomysql/generated/TableParentExtRepo.php';
include __DIR__ . '/../examples/repomysql/generated/AbstractTableParentxCategoryRepo.php';
include __DIR__ . '/../examples/repomysql/generated/TableParentxCategoryRepo.php';

include __DIR__ . '/dBug.php';
//</editor-fold>

/**
 * Class CacheServicesmysql
 *
 * @package eftec\tests
 * @noautoload
 */
// it is an example of a CacheService
class CacheServicesmysql implements IPdoOneCache
{
    public $cacheData = [];
    public $cacheDataFamily = [];
    public $cacheCounter = 0; // for debug
    public $track = [];

    public function getCache($uid, $family = '')
    {
        if (isset($this->cacheData[$uid])) {
            $this->track[] = 'getok.' . $uid;
            $this->cacheCounter++;
            return $this->cacheData[$uid];
        }
        $this->track[] = 'getfail.' . $uid;
        return false;
    }

    /**
     * @param string       $uid
     * @param string|array $family
     *
     * @return void
     */
    public function invalidateCache($uid = '', $family = '') : void
    {
        if ($family === '') {
            if ($uid === '') {
                $this->cacheData = []; // we delete all the cache
            } else {
                $this->cacheData[$uid] = [];
            }
        } else {
            if (!is_array($family)) {
                $family = [$family];
            }
            foreach ($family as $fam) {
                foreach ($this->cacheDataFamily[$fam] as $id) {
                    unset($this->cacheData[$id]);
                    echo "deleting cache $id\n";
                }
                $this->cacheDataFamily[$fam] = [];
            }
        }
        //unset($this->cacheData[$uid]);
    }

    /**
     * @param string $uid
     * @param string $family
     * @param null   $data
     * @param null   $ttl
     */
    public function setCache($uid, $family = '', $data = null, $ttl = null) : void
    {
        $this->track[] = 'set.' . $uid;
        if ($family === '') {
            $this->cacheData[$uid] = $data;
        } else {
            if (!is_array($family)) {
                $family = [$family];
            }
            foreach ($family as $fam) {
                if (!isset($this->cacheDataFamily[$fam])) {
                    $this->cacheDataFamily[$fam] = [];
                }

                $this->cacheDataFamily[$fam][] = $uid;
                $this->cacheData[$uid] = $data;
                //var_dump($fam);
                //var_dump($this->cacheDataFamily[$fam]);
            }
        }
    }
}


/**
 * It tests the code generated in examples/repo
 *
 * Class PdoOne_mysql_gen_test
 */
class PdoOne_mysql_gen_test extends TestCase
{
    /** @var PdoOne */
    protected $pdoOne;

    public function CreateAll(): void
    {
        TableParentExtRepo::createTable();
        TableCategoryRepo::createTable();
        TableParentxCategoryRepo::createTable();
        TableParentRepo::createTable();
        TableChildRepo::createTable();
        TableGrandChildRepo::createTable();

        TableParentExtRepo::createFk();
        TableCategoryRepo::createFk();
        TableParentxCategoryRepo::createFk();
        TableParentRepo::createFk();
        TableChildRepo::createFk();
        TableGrandChildRepo::createFk();
    }

    public function DeleteAll(): void
    {
        TableParentExtRepo::setFalseOnError(true)::dropTable();
        TableParentxCategoryRepo::setFalseOnError(true)::dropTable();
        TableCategoryRepo::setFalseOnError(true)::dropTable();

        TableParentRepo::setFalseOnError(true)::dropTable();
        TableChildRepo::setFalseOnError(true)::dropTable();
        TableGrandChildRepo::setFalseOnError(true)::dropTable();
        self::assertEquals(false, TableGrandChildRepo::setFalseOnError(true)::dropTable());

        //TableGrandChildTagRepo::setFalseOnError(true)::dropTable();
        //self::assertEquals(true,TableGrandChildTagRepo::setFalseOnError(true)::dropTable());
        //self::assertEquals(true,TableGrandChildTagRepo::dropTable());
    }

    public function InsertAll(): void
    {
        $cat = TableCategoryRepo::factory();
        $cat['IdTableCategoryPK'] = 1;
        $cat['Name'] = 'cat1';
        self::assertEquals(1, TableCategoryRepo::insert($cat));
        $cat2 = TableCategoryRepo::factory();
        $cat2['IdTableCategoryPK'] = 2;
        $cat2['Name'] = 'cat2';
        self::assertEquals(2, TableCategoryRepo::insert($cat2));

        $gc = TableGrandChildRepo::factory();
        $gc['NameGrandChild'] = 'gc1';
        self::assertEquals(1, TableGrandChildRepo::insert($gc));

        $ch = TableChildRepo::factory();
        $ch['NameChild'] = 'ch1';
        $ch['idgrandchildFK'] = 1;

        self::assertEquals(1, TableChildRepo::insert($ch));


        $p = TableParentRepo::factory();
        $p['idtablaparentPK'] = -1;
        $p['fieldVarchar'] = 'varchar';
        $p['idchildFK'] = 1;
        $p['idchild2FK'] = 1;
        $p['fieldInt'] = 123;
        $p['fielDecimal'] = 123.123;
        $p['fieldUnique'] = 'u1';
        $p['fieldKey'] = 1;
        $p['fieldDateTime'] = DateTime::createFromFormat('j-m-Y', '15-02-2009');
        $p['_TableParentxCategory'] = [TableCategoryRepo::first(1), TableCategoryRepo::first(2)];
        self::assertEquals(1, TableParentRepo::recursive(['/_TableParentxCategory'])->insert($p));


        $pex = TableParentExtRepo::factory();
        $pex['idtablaparentExtPK'] = 1; //$p['idtablaparentPK'];
        $pex['fieldExt'] = 'ext123';
        self::assertEquals(1, TableParentExtRepo::insert($pex));

        $p = TableParentRepo::factory();
        $p['fieldVarchar'] = 'varchar';
        $p['idchildFK'] = 1;
        $p['idchild2FK'] = 1;
        $p['fieldInt'] = 123;
        $p['fielDecimal'] = 123.123;
        $p['fieldUnique'] = 'u2';
        $p['fieldKey'] = 1;
        $p['fieldDateTime'] = DateTime::createFromFormat('j-m-Y', '15-02-2009');
        $p['_TableParentxCategory'] = [TableCategoryRepo::first(1), TableCategoryRepo::first(2)];
        self::assertEquals(2, TableParentRepo::recursive(['/_TableParentxCategory'])->insert($p));

        $pex = TableParentExtRepo::factory();
        $pex['idtablaparentExtPK'] =2; // $p['idtablaparentPK'];
        $pex['fieldExt'] = 'ext123';
        self::assertEquals(2, TableParentExtRepo::insert($pex));

        //$p['_TableParentxCategory']=[TableCategoryRepo::first(1),TableCategoryRepo::first(2)];
        //self::assertEquals(1,TableParentRepo::insert($p,false)); //::setRecursive(['_TableParentxCategory' ])

        //$pe=TableParentExtRepo::factory();
        //$pe['fieldExt']='123';
        //self::assertEquals(1,TableParentExtRepo::insert($pe));

    }

    public function setUp() : void
    {
        //$this->pdoOne = new PdoOne("mysql", "127.0.0.1", "travis", "", "pdotest");
        $this->pdoOne = new PdoOne("mysql", "127.0.0.1", "travis", "", "testdb");
        try {
            $this->pdoOne->connect();
        } catch (Exception $e) {
        }
        $this->pdoOne->logLevel = 3;
        TableGrandChildRepo::$useModel = false;
        TableGrandChildRepo::setPdoOne($this->pdoOne);
        TableGrandChildTagRepo::setPdoOne($this->pdoOne);
        $this->DeleteAll();
        $this->CreateAll();
        $this->InsertAll();


    }

    public function test2(): void
    {
        try {
            if (!TableGrandChildTagRepo::createTable()) {
                try {
                    TableGrandChildTagRepo::truncate();
                } catch (Exception $e) {
                }
            }
        } catch (Exception $e) {
        }
        $gc = TableGrandChildTagRepo::factory();
        $gc['IdTablaGrandChildTagPK'] = 1;
        $gc['Name'] = 'GrandChild Cat #1';
        $gc['IdgrandchildFK'] = 1;
        try {
            self::assertEquals(1, TableGrandChildTagRepo::insert($gc));
        } catch (Exception $e) {
        }
        $gc['IdTablaGrandChildTagPK'] = 2;
        $gc['Name'] = 'GrandChild Cat #2';
        $gc['IdgrandchildFK'] = 1;
        try {
            self::assertEquals(2, TableGrandChildTagRepo::insert($gc));
        } catch (Exception $e) {
        }
    }

    public function test3(): void
    {
        try {
            if (!TableChildRepo::createTable()) {
                (TableChildRepo::setFalseOnError(true))::truncate();
            }
        } catch (Exception $e) {
        }
        $gc = TableChildRepo::factory();

        $gc['valuechild'] = 'Child #1';
        $gc['idgrandchildFK'] = 1;
        try {
            $in = TableChildRepo::insert($gc);
        } catch (Exception $e) {
            $in = $e->getMessage();
        }
        self::assertGreaterThan(0, $in);

        $gc['valuechild'] = 'Child #2';
        $gc['idgrandchildFK'] = 1;
        try {
            self::assertGreaterThan($in, TableChildRepo::insert($gc));
        } catch (Exception $e) {
        }
    }

    public function test4(): void
    {
        try {
            if (!TableParentRepo::createTable()) {
                (TableParentRepo::setFalseOnError(true))::truncate();
                TableParentRepo::setFalseOnError(false);
            }
        } catch (Exception $e) {
        }
        $gc = TableParentRepo::factory();
        //$gc['idtablaparentPK'] = 1;
        $gc['fieldInt'] = 111;
        $gc['fieldVarchar'] = 'hi';
        $gc['fielDecimal'] = 20.3;
        $gc['fieldDateTime'] = new DateTime();
        $gc['idchildFK'] = 1;
        $gc['idchild2FK'] = 1;
        try {
            $in1 = TableParentRepo::insert($gc);
        } catch (Exception $e) {
            $in1 = $e->getMessage();
        }

        self::assertGreaterThan(1, $in1);
        self::assertEquals(1, TableParentRepo::deleteById($in1));
        $gc = TableParentRepo::factory();
        $gc['idtablaparentPK'] = 2;
        $gc['fieldVarchar'] = 'Parent #2';
        $gc['idchildFK'] = 1;
        $gc['idchild2FK'] = 2;
        try {
            $in2 = TableParentRepo::insert($gc);
            self::assertEquals($in1 + 1, $in2);
            self::assertEquals(1, TableParentRepo::deleteById($in2));
        } catch (Exception $e) {
        }
    }

    public function test4b(): void
    {
        try {
            if (!TableCategoryRepo::createTable()) {
                try {
                    TableCategoryRepo::truncate(true);
                    TableCategoryRepo::resetIdentity();
                } catch (Exception $e) {
                    echo "table not truncated " . $e->getMessage() . " \n";
                }
            }
        } catch (Exception $e) {
        }
        $gc = TableCategoryRepo::factory();
        $gc['IdTableCategoryPK'] = 1;
        $gc['Name'] = 'Category #1';
        try {
            self::assertEquals(1, TableCategoryRepo::insert($gc));
        } catch (Exception $e) {
            echo "not inserted<br>\n";
        }
        $gc['IdTableCategoryPK'] = 2;
        $gc['Name'] = 'Category #2';
        try {
            self::assertEquals(2, TableCategoryRepo::insert($gc));
        } catch (Exception $e) {
            echo "not inserted 2<br>\n";
        }
    }

    public function test4c(): void
    {
        if (!TableParentxCategoryRepo::createTable()) {
            TableParentxCategoryRepo::truncate(true);
            TableParentxCategoryRepo::delete([]);
        }
        $gc = TableParentxCategoryRepo::factory();
        $gc['idtablaparentPKFK'] = 1;
        $gc['idcategoryPKFK'] = 1;
        self::assertEquals(1, TableParentxCategoryRepo::insert($gc));
        $gc['idtablaparentPKFK'] = 1;
        $gc['idcategoryPKFK'] = 2;
        self::assertEquals(1, TableParentxCategoryRepo::insert($gc));
    }

    public function testBuild(): void
    {

        $relations = [
            'TableParent' => ['TableParentRepo', 'TableParentModel'],
            'TableChild' => ['TableChildRepo', 'TableChildModel'],
            'TableGrandChild' => ['TableGrandChildRepo', 'TableGrandChildModel'],
            'TableGrandChildTag' => ['TableGrandChildTagRepo', 'TableGrandChildTagModel'],
            'TableParentxCategory' => ['TableParentxCategoryRepo', 'TableParentxCategoryModel'],
            'TableCategory' => ['TableCategoryRepo', 'TableCategoryModel'],
            'TableParentExt' => ['TableParentExtRepo', 'TableParentExtModel'],
        ];
        $columnRelation = [
            'TableParent' => [
                '_idchild2FK' => 'PARENT',
                '_TableParentxCategory' => 'MANYTOMANY',
                'fieldKey' => ['encrypt', null],
                'extracol' => 'datetime3'
            ],
            'TableParentExt' => ['_idtablaparentExtPK' => 'PARENT']
        ];
        $columnRemove = [
            //'TableParent'=>['idchild2FK']
        ];
        $extraColumn = [
            'TableParent' => ['extracol' => 'CURRENT_TIMESTAMP', 'extracol2' => '20']
        ];

        $tables = $this->pdoOne->tableSorted();
        self::assertEquals([0 => 'TableGrandChild',
            1 => 'TableCategory',
            2 => 'TableChild',
            3 => 'TableGrandChildTag',
            4 => 'TableParent',
            5 => 'TableParentExt',
            6 => 'TableParentxCategory'], $tables);

        $this->pdoOne->generateCodeClassConversions([
            'datetime' => 'datetime',
            'tinyint' => 'bool',
            'int' => 'int',
            'decimal' => 'decimal'
        ]);
        $errors = $this->pdoOne->generateAllClasses($relations, 'TestDb', ['repomysql', 'mysql\repomodel'],
            [__DIR__ . '/../examples/repomysql/generated', __DIR__ . '/../examples/repomysql/generatedmodel'], true, $columnRelation, $extraColumn
            , $columnRemove);
        self::assertEquals([], $errors);

    }

    public function testCache(): void
    {
        $rows = TableParentRepo::toList();
        self::assertGreaterThan(0, count($rows));
        $cs = new CacheServicesmysql();

        TableParentRepo::base()->setCacheService($cs);
        //   $rows = TableParentRepo::useCache(2000)->recursive(['_idchildFK'])->first();
        //  self::assertEquals([
        //      "getfail.TableParent::firstc47e9fda10c3e9581450d888d6d47ddbd239765657977d3b6e76d751d918a7a3",
        //      "set.TableParent::firstc47e9fda10c3e9581450d888d6d47ddbd239765657977d3b6e76d751d918a7a3"
        //  ],$cs->track);
        $rows = TableParentRepo::useCache(2000, ['a1'])->recursive(['_idchildFK'])->first();
        self::assertEquals([
            "getfail.TableParent::firstc47e9fda10c3e9581450d888d6d47ddbd239765657977d3b6e76d751d918a7a3",
            "set.TableParent::firstc47e9fda10c3e9581450d888d6d47ddbd239765657977d3b6e76d751d918a7a3"
        ], $cs->track);


        //$rows = TableParentRepo::useCache(2000)->recursive(['_idchildFK'])->limit("0,2")->toList();
        //$rows = TableParentRepo::useCache(2000)->recursive(['_idchildFK'])->limit("0,2")->toList();
        //$this->assertEquals(3,count($rows[0]['_idchildFK']));
        TableParentRepo::base()->setCacheService(null);

    }

    public function testCache2(): void
    {
        $rows = TableParentRepo::toList();
        self::assertGreaterThan(0, count($rows));
        $cs = new CacheServicesmysql();
        TableParentRepo::base()->setCacheService($cs);
        //$rows = TableParentRepo::setRecursive(['_idchildFK'])->useCache(2000)->limit("0,2")->toList();
        $rows = TableParentRepo::recursive(['/_idchildFK'])->useCache(2000)->first();
        //$rows = TableParentRepo::setRecursive(['_idchildFK'])->useCache(2000)->limit("0,2")->toList();
        //$this->assertEquals(3,count($rows[0]['_idchildFK']));
        self::assertEquals([
            "getfail.TableParent::firstc47e9fda10c3e9581450d888d6d47ddbd239765657977d3b6e76d751d918a7a3",
            "set.TableParent::firstc47e9fda10c3e9581450d888d6d47ddbd239765657977d3b6e76d751d918a7a3"
        ], $cs->track);

    }

    /*public function testDef() {
        $dep = $this->pdoOne->tableDependency(true, false);
        $this->assertEquals([
                                [
                                    0 => 'tablacategory',
                                    1 => 'tablachild',
                                    2 => 'tablagrandchild',
                                    3 => 'tablagrandchildcat',
                                    4 => 'tablaParent',
                                    5 => 'tablaparentxcategory',
                                    6 => 'typetable'
                                ],
                                [
                                    'tablacategory' => array(),
                                    'tablachild' => array(
                                        'idgrandchildFK' => 'tablagrandchild',
                                        '/idgrandchildFK' => 'tablagrandchild'
                                    ),
                                    'tablagrandchild' => array(),
                                    'tablagrandchildcat' => array(
                                        'IdgrandchildFK' => 'tablagrandchild',
                                        '/IdgrandchildFK' => 'tablagrandchild'
                                    ),
                                    'tablaParent' => array(
                                        'idchild2FK' => 'tablachild',
                                        '/idchild2FK' => 'tablachild',
                                        'idchildFK' => 'tablachild',
                                        '/idchildFK' => 'tablachild'
                                    ),
                                    'tablaparentxcategory' => array(
                                        'idcategoryPKFK' => 'tablacategory',
                                        '/idcategoryPKFK' => 'tablacategory',
                                        'idtablaparentPKFK' => 'tablaParent',
                                        '/idtablaparentPKFK' => 'tablaParent'
                                    ),
                                    'typetable' => array()
                                ],
                                [
                                    'tablacategory' => array(
                                        'IdTableCategoryPK' => array(
                                            0 => '/idcategoryPKFK',
                                            1 => 'tablaparentxcategory'
                                        )
                                    ),
                                    'tablachild' => array(
                                        'idtablachildPK' => array(
                                            0 => '/idchildFK',
                                            1 => 'tablaParent'
                                        )
                                    ),
                                    'tablagrandchild' => array(
                                        'idgrandchildPK' => array(
                                            0 => '/IdgrandchildFK',
                                            1 => 'tablagrandchildcat'
                                        )
                                    ),
                                    'tablagrandchildcat' => array(),
                                    'tablaParent' => array(
                                        'idtablaparentPK' => array(
                                            0 => '/idtablaparentPKFK',
                                            1 => 'tablaparentxcategory'
                                        )
                                    ),
                                    'tablaparentxcategory' => array(),
                                    'typetable' => array()
                                ]
                            ], $dep);


    }
    */

    public function testCacheSimple(): void
    {
        $rows = TableParentRepo::toList();
        self::assertGreaterThan(0, count($rows));
        $cs = new CacheServicesmysql();
        TableParentRepo::base()->setCacheService($cs);
        TableParentRepo::useCache(2000)->first();
        TableParentRepo::useCache(2000)->first();
        self::assertEquals([
            0 => 'getfail.TableParent::first70d697ea4281195ebedfb784d13aa2241760521ee792326129f609e8da713127',
            1 => 'set.TableParent::first70d697ea4281195ebedfb784d13aa2241760521ee792326129f609e8da713127',
            2 => 'getok.TableParent::first70d697ea4281195ebedfb784d13aa2241760521ee792326129f609e8da713127'
        ], $cs->track);

        TableParentRepo::base()->setCacheService(null);
    }

    public function testCount(): void
    {
        $r = TableParentRepo::where('idtablaparentpk>?', 3)->toList();
        $a2 = count($r);


        $a1 = TableParentRepo::where('idtablaparentpk>?', 3)->count();
        self::assertEquals($a2, $a1);
        //echo "----\n";

        $a1 = TableParentRepo::where('idtablaparentpk>?', 3)->count();
        self::assertEquals($a2, $a1);

    }

    public function testFactory(): void
    {
        //   TODO: FACTORY CON SETRECURSIVE FALLA
        self::assertEquals(['idtablaparentPK' => 0,
            '_TableParentExt' => [
                'idtablaparentExtPK' => 0,
                'fieldExt' => ''
            ],
            '_TableParentxCategory' => [],
            'fieldVarchar' => '',
            'idchildFK' => 0,
            '_idchildFK' => [
                'idtablachildPK' => 0,
                '_TableParent' => null,
                'NameChild' => '',
                'idgrandchildFK' => 0,
                '_idgrandchildFK' => null
            ],
            'idchild2FK' => 0,
            'fieldInt' => 0,
            'fielDecimal' => 0.0,
            'fieldDateTime' => '',
            'fieldUnique' => '',
            'fieldKey' => ''], TableParentRepo::recursive('*')->factory());

    }

    public function testFirst(): void
    {
        $r = TableParentRepo::where('idtablaparentpk>?', 0)->toListSimple();
        self::assertEquals([0 => 1,
            1 => 2], $r);
        $r = TableParentRepo::where('idtablaparentpk>?', 0)->firstScalar();
        self::assertEquals(1, $r);
        $r = TableParentRepo::exist(1);
        self::assertEquals(true, $r);
        $r = TableParentRepo::exist(-1);
        self::assertEquals(false, $r);
        $obj = TableParentRepo::factory();
        $obj['idtablaparentPK'] = 1;
        $r = TableParentRepo::exist($obj);
        $obj = new stdClass();
        self::assertEquals(true, $r);
        $obj->idtablaparentPK = 1;
        $r = TableParentRepo::exist($obj);
        self::assertEquals(true, $r);
    }

    public function testGrandChild(): void
    {
        try {
            if (!TableGrandChildRepo::createTable()) {
                (TableGrandChildRepo::setFalseOnError(true))::truncate();
            }
        } catch (Exception $e) {
        }
        $gc = TableGrandChildRepo::factory();
        $gc['idgrandchildPK'] = 1;
        $gc['NameGrandChild'] = 'GrandChild #1';
        try {
            $in = TableGrandChildRepo::insert($gc);
        } catch (Exception $e) {
            $in = $e->getMessage();
        }
        self::assertGreaterThan(1, $in);

        $gc['idgrandchildPK'] = 2;
        $gc['NameGrandChild'] = 'GrandChild #2';
        try {
            $in2 = TableGrandChildRepo::insert($gc);
        } catch (Exception $e) {
            $in2 = $e->getMessage();
        }
        self::assertGreaterThan($in, $in2);
    }

    public function testOneToMany(): void
    {
        $rows = TableChildRepo::recursive(['/_TableParent'])->first(1);
        $rows['_TableParent'][0]['fieldDateTime'] = null;
        $rows['_TableParent'][1] = null;
        self::assertEquals([
            'idtablachildPK' => 1,
            'NameChild' => 'ch1',
            'idgrandchildFK' => 1,
            '_TableParent' => array(0 => [
                'idtablaparentPK' => 1,
                'fieldVarchar' => 'varchar',
                'idchildFK' => 1,
                'idchild2FK' => 1,
                'fieldInt' => 123,
                'fielDecimal' => 123.123,
                'fieldDateTime' => null,
                'fieldUnique' => 'u1',
                'fieldKey' => '1',
                'extracol' => null,
                'extracol2' => null,

            ]
            , 1 => null)
        ], $rows);
    }

    /** @noinspection SuspiciousAssignmentsInspection */

    public function testQuery(): void
    {
        $sql = 'select idtablaparentpk from ' . TableParentRepo::TABLE . ' where idtablaparentpk=?';
        self::assertEquals([0 => ['idtablaparentpk' => 1]], TableParentRepo::query($sql, [1]));

    }

    /** @noinspection NullPointerExceptionInspection */

    public function testQuery2(): void
    {
        $r = TableParentRepo::order('idtablaparentpk')->toListSimple();
        self::assertEquals([1, 2], $r);
        $r = TableParentRepo::where('idtablaparentpk<?', 3)->order('idtablaparentpk desc')->toListSimple();
        self::assertEquals([2, 1], $r);


    }

    public function testSelect(): void
    {
        $rows = TableParentRepo::toList();
        self::assertGreaterThan(0, count($rows));

        $rows = TableParentRepo::recursive(['/_idchildFK'])->limit("0,2")->toList();

        $rows[0]['fieldDateTime'] = null;
        $rows[0]['extracol'] = null;
        $rows[1]['fieldDateTime'] = null;
        $rows[1]['extracol'] = null;

        self::assertEquals([
            array(
                'idtablaparentPK' => 1,
                'idchildFK' => 1,
                'fieldVarchar' => 'varchar',
                'fieldInt' => 123,
                'fielDecimal' => 123.123,
                'fieldDateTime' => null,
                'fieldUnique' => 'u1',
                'fieldKey' => '1',
                'extracol' => null,
                'extracol2' => 20,
                'idchild2FK' => 1,
                '_idchildFK' => array('idtablachildPK' => 1,
                    'NameChild' => 'ch1',
                    'idgrandchildFK' => 1)
            ),
            [
                'idtablaparentPK' => 2,
                'idchildFK' => 1,
                'fieldVarchar' => 'varchar',
                'fieldInt' => 123,
                'fielDecimal' => 123.123,
                'fieldDateTime' => null,
                'fieldUnique' => 'u2',
                'fieldKey' => '1',
                'extracol' => null,
                'extracol2' => 20,
                'idchild2FK' => 1,
                '_idchildFK' => array('idtablachildPK' => 1,
                    'NameChild' => 'ch1',
                    'idgrandchildFK' => 1)
            ]
        ], $rows);

        try {
            $rows = TableParentRepo::recursive(['/_idchildFK'])->first('1');
        } catch (Exception $e) {
        }

        $rows['fieldDateTime'] = null;
        $rows['extracol'] = null;

        self::assertEquals([
            'idtablaparentPK' => 1,
            'idchildFK' => 1,
            'fieldVarchar' => 'varchar',
            'fieldInt' => 123,
            'fielDecimal' => 123.123,
            'fieldDateTime' => null,
            'fieldUnique' => 'u1',
            'fieldKey' => '1',
            'extracol' => null,
            'extracol2' => 20,
            '_idchildFK' => array(
                'idtablachildPK' => 1,
                'NameChild' => 'ch1',
                'idgrandchildFK' => 1
            ),
            'idchild2FK' => 1
        ], $rows);

        try {
            $rows = TableParentRepo::recursive([
                '/_idchildFK',
                '/_idchildFK/_idgrandchildFK',
                '/_idchildFK/_idgrandchildFK/_tablagrandchildcat'
            ])->first('1');
        } catch (Exception $e) {
        }

        $rows['fieldDateTime'] = null;
        $rows['extracol'] = null;

        self::assertEquals([
            'idtablaparentPK' => 1,
            'idchildFK' => 1,
            'fieldVarchar' => 'varchar',
            'fieldInt' => 123,
            'fielDecimal' => 123.123,
            'fieldDateTime' => null,
            'fieldUnique' => 'u1',
            'fieldKey' => '1',
            '_idchildFK' => array(
                'idtablachildPK' => 1,
                'NameChild' => 'ch1',
                'idgrandchildFK' => 1,
                '_idgrandchildFK' => array(
                    'idgrandchildPK' => 1,
                    'NameGrandChild' => 'gc1',
                )
            ),
            'extracol' => null,
            'extracol2' => 20,
            'idchild2FK' => 1,
        ], $rows);
    }

    public function testSelectManyToMany(): void
    {
        try {
            $rows = (TableParentRepo::recursive([
                '/_TableParentxCategory'
            ]))->first('1');
        } catch (Exception $e) {
        }

        $rows['fieldDateTime'] = null;
        $rows['extracol'] = null;

        self::assertEquals([
            'idtablaparentPK' => 1,
            'idchildFK' => 1,
            'fieldVarchar' => 'varchar',
            'fieldInt' => 123,
            'fielDecimal' => 123.123,
            'fieldDateTime' => null,
            'fieldUnique' => 'u1',
            'fieldKey' => '1',
            'extracol' => null,
            'extracol2' => 20,
            '_TableParentxCategory' => [
                0 => ['IdTableCategoryPK' => 1,
                    'Name' => 'cat1']
                , 1 => ['IdTableCategoryPK' => 2,
                    'Name' => 'cat2']],
            'idchild2FK' => 1
        ], $rows);
    }

    public function testSelectManyToOne_ManyToOne_OneToMany(): void
    {
        try {
            $rows = (TableParentRepo::recursive([
                '/_TableParentxCategory'
            ]))->first('1');
        } catch (Exception $e) {
        }

        $rows['fieldDateTime'] = null;
        $rows['extracol'] = null;

        self::assertEquals([
            'idtablaparentPK' => 1,
            'idchildFK' => 1,
            'fieldVarchar' => 'varchar',
            'fieldInt' => 123,
            'fielDecimal' => 123.123,
            'fieldDateTime' => null,
            'fieldUnique' => 'u1',
            'fieldKey' => '1',
            'extracol' => null,
            'extracol2' => 20,
            '_TableParentxCategory' => [
                0 => ['IdTableCategoryPK' => 1,
                    'Name' => 'cat1']
                , 1 => ['IdTableCategoryPK' => 2,
                    'Name' => 'cat2']],
            'idchild2FK' => 1
        ], $rows);
    }

    public function testSelectOneToMany_ManyToOne(): void
    {
        try {
            $rows = (TableParentRepo::recursive([
                '/_TableParentxCategory',
                // '_TableParentxCategory/_idcategoryPKFK'
            ]))->first(1);
        } catch (Exception $e) {
            var_dump($e->getMessage());
        }

        $rows['fieldDateTime'] = null;
        $rows['extracol'] = null;

        self::assertEquals(array(
                'idtablaparentPK' => 1,
                'idchildFK' => 1,
                'fieldVarchar' => 'varchar',
                'fieldInt' => 123,
                'fielDecimal' => 123.123,
                'fieldDateTime' => null,
                'fieldUnique' => 'u1',
                'fieldKey' => '1',
                '_TableParentxCategory' => array(
                    0 => array('IdTableCategoryPK' => 1,
                        'Name' => 'cat1'),
                    1 => array('IdTableCategoryPK' => 2,
                        'Name' => 'cat2')
                ),
                'extracol' => null,
                'extracol2' => 20,
                'idchild2FK' => 1
            )
            , $rows);
    }

    public function testUpdateDelete(): void
    {
        $p = TableParentRepo::factory();
        $p['idchildFK'] = null;
        $p['idchild2FK'] = null;
        $p['fieldUnique'] = 'uni';
        self::assertGreaterThan(0, TableParentRepo::insert($p));

        $p['fieldInt'] = 123;
        $p['fielDecimal'] = 123.123;
        $p['fieldVarchar'] = 'vc';
        self::assertGreaterThan(0, TableParentRepo::update($p));

        $p2 = TableParentRepo::first($p['idtablaparentPK']);
        // delete the values that we don't want to compare
        $p2['fieldDateTime'] = false;
        $p2['_TableParentExt'] = null;
        $p2['_TableParentxCategory'] = null;
        $p2['_idchildFK'] = null;
        $p2['extracol'] = null;
        $p2['extracol2'] = null;
        $p['extracol'] = null;
        $p['extracol2'] = null;
        self::assertEquals($p2, $p);

        self::assertEquals(true, TableParentRepo::deleteById($p['idtablaparentPK']));


    }

    public function testValidate(): void
    {
        $b = TableParentRepo::factory();
        self::assertEquals(true, TableParentRepo::validateModel($b));
    }

    public function test_ping_ping() {
        $ping='pong';
        $this->assertEquals('pong',$ping);
    }

}
