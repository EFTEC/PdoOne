<?php
/** @noinspection PhpIllegalPsrClassPathInspection */

/** @noinspection PhpUnhandledExceptionInspection */

use eftec\PdoOne;
use PHPUnit\Framework\TestCase;
use repomysql\TableChildRepo;
use repomysql\TableGrandChildRepo;
use repomysql\TableGrandChildTagRepo;
use repomysql\TableParentRepo;

//include __DIR__ . '/../lib/_BasePdoOneRepo.php';
include __DIR__ . '/../examples/repomysql/generated/TestDb.php';

include __DIR__ . '/../examples/repomysql/generated/TableGrandChildRepoExt.php';
include __DIR__ . '/../examples/repomysql/generated/TableGrandChildRepo.php';
include __DIR__ . '/../examples/repomysql/generated/TableCategoryRepoExt.php';
include __DIR__ . '/../examples/repomysql/generated/TableCategoryRepo.php';
include __DIR__ . '/../examples/repomysql/generated/TableChildRepoExt.php';
include __DIR__ . '/../examples/repomysql/generated/TableChildRepo.php';
include __DIR__ . '/../examples/repomysql/generated/TableGrandChildTagRepoExt.php';
include __DIR__ . '/../examples/repomysql/generated/TableGrandChildTagRepo.php';
include __DIR__ . '/../examples/repomysql/generated/TableParentRepoExt.php';
include __DIR__ . '/../examples/repomysql/generated/TableParentRepo.php';
include __DIR__ . '/../examples/repomysql/generated/TableParentExtRepoExt.php';
include __DIR__ . '/../examples/repomysql/generated/TableParentExtRepo.php';
include __DIR__ . '/../examples/repomysql/generated/TableParentxCategoryRepoExt.php';
include __DIR__ . '/../examples/repomysql/generated/TableParentxCategoryRepo.php';

include __DIR__ . '/dBug.php';

/**
 * It tests the code generated in examples/repo
 *
 * Class PdoOne_mysql_gen_test
 */
class PdoOne_mysql_gen_test extends TestCase
{
    /** @var PdoOne */
    protected $pdoOne;

    public function setUp()
    {
        //$this->pdoOne = new PdoOne("mysql", "127.0.0.1", "travis", "", "pdotest");
        $this->pdoOne = new PdoOne("mysql", "127.0.0.1", "travis", "", "testdb");
        $this->pdoOne->connect();
        $this->pdoOne->logLevel = 3;
        TableGrandChildRepo::setPdoOne($this->pdoOne);
        TableGrandChildTagRepo::setPdoOne($this->pdoOne);
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
                                        'IdTablaCategoryPK' => array(
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

    public function testGrandChild()
    {
        if (!TableGrandChildRepo::createTable()) {
            (TableGrandChildRepo::setFalseOnError(true))::truncate();
        }
        $gc = TableGrandChildRepo::factoryNull();
        $gc['idgrandchildPK'] = 1;
        $gc['NameGrandChild'] = 'GrandChild #1';
        $in = TableGrandChildRepo::insert($gc);
        self::assertGreaterThan(1, $in);

        $gc['idgrandchildPK'] = 2;
        $gc['NameGrandChild'] = 'GrandChild #2';
        $in2 = TableGrandChildRepo::insert($gc);
        self::assertGreaterThan($in, $in2);
    }

    public function test2()
    {
        if (!TableGrandChildTagRepo::createTable()) {
            TableGrandChildTagRepo::truncate();
        }
        $gc = TableGrandChildTagRepo::factoryNull();
        $gc['IdTablaGrandChildTagPK'] = 1;
        $gc['Name'] = 'GrandChild Cat #1';
        $gc['IdgrandchildFK'] = 1;
        self::assertEquals(1, TableGrandChildTagRepo::insert($gc));
        $gc['IdTablaGrandChildTagPK'] = 2;
        $gc['Name'] = 'GrandChild Cat #2';
        $gc['IdgrandchildFK'] = 1;
        self::assertEquals(2, TableGrandChildTagRepo::insert($gc));
    }

    public function test3()
    {
        if (!TableChildRepo::createTable()) {
            (TableChildRepo::setFalseOnError(true))::truncate();
        }
        $gc = TableChildRepo::factoryNull();

        $gc['valuechild'] = 'Child #1';
        $gc['idgrandchildFK'] = 1;
        $in = TableChildRepo::insert($gc);
        self::assertGreaterThan(0, $in);

        $gc['valuechild'] = 'Child #2';
        $gc['idgrandchildFK'] = 1;
        self::assertGreaterThan($in, TableChildRepo::insert($gc));
    }

    public function test4()
    {
        if (!TableParentRepo::createTable()) {
            (TableParentRepo::setFalseOnError(true))::truncate();
            TableParentRepo::setFalseOnError(false);
        }
        $gc = TableParentRepo::factoryNull();
        $gc['idtablaparentPK'] = 1;
        $gc['fieldInt'] = 111;
        $gc['fieldVarchar'] = 'hi';
        $gc['fielDecimal'] = 20.3;
        $gc['fieldDateTime'] = new DateTime();
        $gc['idchildFK'] = 1;
        $gc['idchild2FK'] = 2;
        $in1 = TableParentRepo::insert($gc);
        self::assertGreaterThan(1, $in1);
        $gc = TableParentRepo::factoryNull();
        $gc['idtablaparentPK'] = 2;
        $gc['fieldVarchar'] = 'Parent #2';
        $gc['idchildFK'] = 1;
        $gc['idchild2FK'] = 2;
        self::assertEquals($in1 + 1, TableParentRepo::insert($gc));
    }

    public function test4b()
    {
        if (!TablacategoryRepo::createTable()) {
            TablacategoryRepo::truncate();
        }
        $gc = TablacategoryRepo::factoryNull();
        $gc['IdTablaCategoryPK'] = 1;
        $gc['Name'] = 'Category #1';
        self::assertEquals(1, TablacategoryRepo::insert($gc));
        $gc['IdTablaCategoryPK'] = 2;
        $gc['Name'] = 'Category #2';
        self::assertEquals(2, TablacategoryRepo::insert($gc));
    }

    public function test4c()
    {
        if (!TablaparentxcategoryRepo::createTable()) {
            TablaparentxcategoryRepo::truncate();
        }
        $gc = TablaparentxcategoryRepo::factoryNull();
        $gc['idtablaparentPKFK'] = 1;
        $gc['idcategoryPKFK'] = 1;
        self::assertEquals(0, TablaparentxcategoryRepo::insert($gc));
        $gc['idtablaparentPKFK'] = 1;
        $gc['idcategoryPKFK'] = 2;
        self::assertEquals(0, TablaparentxcategoryRepo::insert($gc));
    }

    public function testSelect()
    {
        $rows = TableParentRepo::toList();
        self::assertEquals([
            [
                'idtablaparentPK' => '1',
                'field1'          => 'Parent #1',
                'idchildFK'       => '1',
                'idchild2FK'      => '2',
                'field2'          => null
            ],
            [
                'idtablaparentPK' => '2',
                'field1'          => 'Parent #2',
                'idchildFK'       => '1',
                'idchild2FK'      => '2',
                'field2'          => null
            ]
        ], $rows);

        $rows = (TableParentRepo::setRecursive(['/idchildFK', '/idchild2FK']))::toList();

        self::assertEquals([
            [
                'idtablaparentPK' => '1',
                'field1'          => 'Parent #1',
                'idchildFK'       => '1',
                'idchild2FK'      => '2',
                'field2'          => null,
                '/idchildFK'      => [
                    'idtablachildPK' => '1',
                    'valuechild'     => 'Child #1',
                    'idgrandchildFK' => '1'
                ],
                '/idchild2FK'     => [
                    'idtablachildPK' => '2',
                    'valuechild'     => 'Child #2',
                    'idgrandchildFK' => '1'
                ],
            ],
            [
                'idtablaparentPK' => '2',
                'field1'          => 'Parent #2',
                'idchildFK'       => '1',
                'idchild2FK'      => '2',
                'field2'          => null,
                '/idchildFK'      => [
                    'idtablachildPK' => '1',
                    'valuechild'     => 'Child #1',
                    'idgrandchildFK' => '1'
                ],
                '/idchild2FK'     => [
                    'idtablachildPK' => '2',
                    'valuechild'     => 'Child #2',
                    'idgrandchildFK' => '1'
                ],
            ]
        ], $rows);

        $rows = (TableParentRepo::setRecursive(['/idchildFK', '/idchild2FK']))::first('1');

        self::assertEquals([
            'idtablaparentPK' => '1',
            'field1'          => 'Parent #1',
            'idchildFK'       => '1',
            'idchild2FK'      => '2',
            'field2'          => null,
            '/idchildFK'      => [
                'idtablachildPK' => '1',
                'valuechild'     => 'Child #1',
                'idgrandchildFK' => '1'
            ],
            '/idchild2FK'     => [
                'idtablachildPK' => '2',
                'valuechild'     => 'Child #2',
                'idgrandchildFK' => '1'
            ],

        ], $rows);

        $rows = (TableParentRepo::setRecursive([
            '/idchildFK',
            '/idchild2FK',
            '/idchildFK/idgrandchildFK',
            '/idchildFK/idgrandchildFK/tablagrandchildcat'
        ]))::first('1');

        self::assertEquals([
            'idtablaparentPK' => '1',
            'field1'          => 'Parent #1',
            'idchildFK'       => '1',
            'idchild2FK'      => '2',
            'field2'          => null,
            '/idchildFK'      => [
                'idtablachildPK'  => '1',
                'valuechild'      => 'Child #1',
                'idgrandchildFK'  => '1',
                '/idgrandchildFK' => [
                    'idgrandchildPK'      => '1',
                    'NameGrandChild'      => 'GrandChild #1',
                    '/tablagrandchildcat' => [
                        [
                            'IdTablaGrandChildCatPK' => '1',
                            'Name'                   => 'GrandChild Cat #1',
                            'IdgrandchildFK'         => '1',
                        ],
                        [
                            'IdTablaGrandChildCatPK' => '2',
                            'Name'                   => 'GrandChild Cat #2',
                            'IdgrandchildFK'         => '1'
                        ]
                    ]
                ]
            ],
            '/idchild2FK'     => [
                'idtablachildPK' => '2',
                'valuechild'     => 'Child #2',
                'idgrandchildFK' => '1'
            ]

        ], $rows);
    }

    public function testSelectManyToOne_ManyToOne_OneToMany()
    {
        $rows = (TableParentRepo::setRecursive([
            '/idchildFK',
            '/idchild2FK',
            '/idchildFK/idgrandchildFK',
            '/idchildFK/idgrandchildFK/tablagrandchildcat',
            '/tablaparentxcategory'
        ]))::first('1');

        self::assertEquals([
            'idtablaparentPK'       => '1',
            'field1'                => 'Parent #1',
            'idchildFK'             => '1',
            'idchild2FK'            => '2',
            'field2'                => null,
            '/idchildFK'            => [
                'idtablachildPK'  => '1',
                'valuechild'      => 'Child #1',
                'idgrandchildFK'  => '1',
                '/idgrandchildFK' => [
                    'idgrandchildPK'      => '1',
                    'NameGrandChild'      => 'GrandChild #1',
                    '/tablagrandchildcat' => [
                        [
                            'IdTablaGrandChildCatPK' => '1',
                            'Name'                   => 'GrandChild Cat #1',
                            'IdgrandchildFK'         => '1',
                        ],
                        [
                            'IdTablaGrandChildCatPK' => '2',
                            'Name'                   => 'GrandChild Cat #2',
                            'IdgrandchildFK'         => '1'
                        ]
                    ]
                ]
            ],
            '/idchild2FK'           => [
                'idtablachildPK' => '2',
                'valuechild'     => 'Child #2',
                'idgrandchildFK' => '1'
            ],
            '/tablaparentxcategory' => [
                [
                    'idtablaparentPKFK' => '1',
                    'idcategoryPKFK'    => '1'
                ],
                [
                    'idtablaparentPKFK' => '1',
                    'idcategoryPKFK'    => '2'
                ]
            ]

        ], $rows);
    }

    public function testSelectOneToMany_ManyToOne()
    {
        $rows = (TableParentRepo::setRecursive([
            '_TableParentxCategory',
            '_TableParentxCategory/_idcategoryPKFK'
        ]))::first(1);

        self::assertEquals(array(
            'idtablaparentPK'       => '1',
            'idchildFK'             => '1',
            'idchild2FK'            => '2',
            'fieldVarchar'          => 'Parent #1',
            'fieldInt'              => '555',
            'fielDecimal'           => '555.56',
            'fieldDateTime'         => new DateTime('2019-01-01T00:00:00.000000'),
            'fieldUnique'           => 'U1',
            'fieldKey'              => 'K1',
            '_TableParentxCategory' => array()
        ), $rows);
    }

}