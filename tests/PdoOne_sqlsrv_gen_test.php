<?php /** @noinspection PhpIllegalPsrClassPathInspection */

/** @noinspection PhpUnhandledExceptionInspection */

use eftec\PdoOne;
use PHPUnit\Framework\TestCase;
use repo\TablacategoryRepo;
use repo\TablaChildRepo;
use repo\TablagrandchildcatRepo;
use repo\TablagrandchildRepo;
use repo\TablaParentRepo;
use repo\TablaparentxcategoryRepo;

include __DIR__ . "/../examples/reposqlsrv/tablacategoryRepo.php";
include __DIR__ . "/../examples/reposqlsrv/tablachildRepo.php";
include __DIR__ . "/../examples/reposqlsrv/tablagrandchildRepo.php";
include __DIR__ . "/../examples/reposqlsrv/tablagrandchildcatRepo.php";
include __DIR__ . "/../examples/reposqlsrv/tablaparentxcategoryRepo.php";
include __DIR__ . "/../examples/reposqlsrv/tablaParentRepo.php";
include __DIR__ . '/dBug.php';

/**
 * It tests the code generated in examples/repo
 *
 * Class PdoOne_mysql_gen_test
 */
class PdoOne_sqlsrv_gen_test extends TestCase
{
    /** @var PdoOne */
    protected $pdoOne;

    public function setUp() {
        $this->pdoOne = new PdoOne('sqlsrv', 'PCJC\SQLEXPRESS', 'sa', 'abc.123', 'testdb', '');
        $this->pdoOne->connect();
        $this->pdoOne->logLevel = 3;
        TablagrandchildRepo::setPdoOne($this->pdoOne);
        TablagrandchildcatRepo::setPdoOne($this->pdoOne);


    }

    /*public function testDef() {
        $dep = $this->pdoOne->tableDependency(true, false);
        $this->assertEquals([
                                [
                                    0 => 'tablacategory',
                                    1 => 'tablagrandchildcat',
                                    2 => 'tablaparent',
                                    3 => 'tablaparentxcategory',
                                    4 => 'sysdiagrams',
                                    5 => 'tablagrandchild',
                                    6 => 'tablachild'
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
                                    'tablaparent' => array(
                                        'idchild2FK' => 'tablachild',
                                        '/idchild2FK' => 'tablachild',
                                        'idchildFK' => 'tablachild',
                                        '/idchildFK' => 'tablachild'
                                    ),
                                    'tablaparentxcategory' => array(
                                        'idcategoryPKFK' => 'tablacategory',
                                        '/idcategoryPKFK' => 'tablacategory',
                                        'idtablaparentPKFK' => 'tablaparent',
                                        '/idtablaparentPKFK' => 'tablaparent'
                                    ),
                                    'sysdiagrams' => array()
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
                                            1 => 'tablaparent'
                                        )
                                    ),
                                    'tablagrandchild' => array(
                                        'idgrandchildPK' => array(
                                            0 => '/IdgrandchildFK',
                                            1 => 'tablagrandchildcat'
                                        )
                                    ),
                                    'tablagrandchildcat' => array(),
                                    'tablaparent' => array(
                                        'idtablaparentPK' => array(
                                            0 => '/idtablaparentPKFK',
                                            1 => 'tablaparentxcategory'
                                        )
                                    ),
                                    'tablaparentxcategory' => array(),
                                    'sysdiagrams' => array()
                                ]
                            ], $dep);

        $a1 = $this->pdoOne->getDefTableFK('tablagrandchild', false);
        $this->assertEquals([], $a1);
        $a1 = $this->pdoOne->getDefTableFK('tablachild', false);
        $this->assertEquals([
                                'idgrandchildFK' => array(
                                    'key' => 'FOREIGN KEY',
                                    'refcol' => 'idgrandchildPK',
                                    'reftable' => 'tablagrandchild',
                                    'extra' => ''
                                ),
                                '/idgrandchildFK' => array(
                                    'key' => 'MANYTOONE',
                                    'refcol' => 'idgrandchildPK',
                                    'reftable' => 'tablagrandchild',
                                    'extra' => null
                                )
                            ], $a1);
        $a1 = $this->pdoOne->getDefTableFK('tablagrandchildcat', false);
        $this->assertEquals([
                                'IdgrandchildFK' => array(
                                    'key' => 'FOREIGN KEY',
                                    'refcol' => 'idgrandchildPK',
                                    'reftable' => 'tablagrandchild',
                                    'extra' => ''
                                ),
                                '/IdgrandchildFK' => array(
                                    'key' => 'MANYTOONE',
                                    'refcol' => 'idgrandchildPK',
                                    'reftable' => 'tablagrandchild',
                                    'extra' => null
                                )
                            ], $a1);
    }*/


    public function test_createtruncate() {
        if (!TablagrandchildcatRepo::createTable()) {
            $this->assertTrue(TablagrandchildcatRepo::delete() !== false);
        }
        $this->pdoOne->runRawQuery("DBCC CHECKIDENT ('" . TablagrandchildcatRepo::TABLE . "', RESEED, 0)", [], true);

        if (!TablaparentxcategoryRepo::createTable()) {
            $this->assertTrue(TablaparentxcategoryRepo::delete() !== false);
        }
        // it doesn't have identity
        //$this->pdoOne->runRawQuery("DBCC CHECKIDENT ('".TablaparentxcategoryRepo::TABLE."', RESEED, 1)",[],true);

        if (!TablacategoryRepo::createTable()) {
            $this->assertTrue(TablacategoryRepo::delete() !== false);
        }
        $this->pdoOne->runRawQuery("DBCC CHECKIDENT ('" . TablacategoryRepo::TABLE . "', RESEED, 0)", [], true);
        if (!TablaParentRepo::createTable()) {
            $this->assertTrue(TablaParentRepo::delete() !== false);
        }
        $this->pdoOne->runRawQuery("DBCC CHECKIDENT ('" . TablaParentRepo::TABLE . "', RESEED, 0)", [], true);
        if (!TablaChildRepo::createTable()) {
            $this->assertTrue(TablaChildRepo::delete() !== false);
        }
        $this->pdoOne->runRawQuery("DBCC CHECKIDENT ('" . TablaChildRepo::TABLE . "', RESEED, 0)", [], true);
        if (!TablagrandchildRepo::createTable()) {
            $this->assertTrue(TablagrandchildRepo::delete() !== false);
        }
        $this->pdoOne->runRawQuery("DBCC CHECKIDENT ('" . TablagrandchildRepo::TABLE . "', RESEED, 0)", [], true);


    }

    public function testGrandChild() {

        $gc = TablagrandchildRepo::factoryNull();
        $gc['idgrandchildPK'] = 1;
        $gc['NameGrandChild'] = 'GrandChild #1';
        $this->assertGreaterThanOrEqual(1, TablagrandchildRepo::insert($gc));
        $gc['idgrandchildPK'] = 2;
        $gc['NameGrandChild'] = 'GrandChild #2';
        $this->assertGreaterThanOrEqual(2, TablagrandchildRepo::insert($gc));
    }

    public function testtablagrandchildcat() {

        $gc = TablagrandchildcatRepo::factoryNull();
        //$gc['IdTablaGrandChildCatPK'] = 1;
        $gc['Name'] = 'GrandChild Cat #1';
        $gc['IdgrandchildFK'] = 1;
        $this->assertGreaterThanOrEqual(1, TablagrandchildcatRepo::insert($gc));
        //$gc['IdTablaGrandChildCatPK'] = 2;
        $gc['Name'] = 'GrandChild Cat #2';
        $gc['IdgrandchildFK'] = 1;
        $this->assertGreaterThanOrEqual(2, TablagrandchildcatRepo::insert($gc));
    }

    public function test3() {

        $gc = TablaChildRepo::factoryNull();
        $gc['idtablachildPK'] = 1;
        $gc['valuechild'] = 'Child #1';
        $gc['idgrandchildFK'] = 1;
        $this->assertGreaterThanOrEqual(1, TablaChildRepo::insert($gc));
        $gc['idtablachildPK'] = 2;
        $gc['valuechild'] = 'Child #2';
        $gc['idgrandchildFK'] = 1;
        $this->assertGreaterThanOrEqual(2, TablaChildRepo::insert($gc));
    }

    public function test4() {

        $gc = TablaParentRepo::factoryNull();
        $gc['idtablaparentPK'] = 1;
        $gc['field1'] = 'Parent #1';
        $gc['field2'] = 'unique 1';
        $gc['idchildFK'] = 1;
        $gc['idchild2FK'] = 2;
        $this->assertGreaterThanOrEqual(1, TablaParentRepo::insert($gc));
        $gc['idtablaparentPK'] = 2;
        $gc['field1'] = 'Parent #2';
        $gc['field2'] = 'unique 2';
        $gc['idchildFK'] = 1;
        $gc['idchild2FK'] = 2;
        $this->assertGreaterThanOrEqual(2, TablaParentRepo::insert($gc));
    }

    public function test4b() {

        $gc = TablacategoryRepo::factoryNull();
        $gc['IdTablaCategoryPK'] = 1;
        $gc['Name'] = 'Category #1';
        $this->assertGreaterThanOrEqual(1, TablacategoryRepo::insert($gc));
        $gc['IdTablaCategoryPK'] = 2;
        $gc['Name'] = 'Category #2';
        $this->assertGreaterThanOrEqual(2, TablacategoryRepo::insert($gc));
    }

    public function test4c() {

        $gc = TablaparentxcategoryRepo::factoryNull();
        $gc['idtablaparentPKFK'] = 1;
        $gc['idcategoryPKFK'] = 1;
        $this->assertEquals('', TablaparentxcategoryRepo::insert($gc));
        $gc['idtablaparentPKFK'] = 1;
        $gc['idcategoryPKFK'] = 2;
        $this->assertEquals('', TablaparentxcategoryRepo::insert($gc));
    }

    public function testSelect() {
        $rows = TablaParentRepo::toList();
        $this->assertEquals([
                                [
                                    'idtablaparentPK' => '1',
                                    'field1' => 'Parent #1',
                                    'idchildFK' => '1',
                                    'idchild2FK' => '2',
                                    'field2' => 'unique 1'
                                ],
                                [
                                    'idtablaparentPK' => '2',
                                    'field1' => 'Parent #2',
                                    'idchildFK' => '1',
                                    'idchild2FK' => '2',
                                    'field2' => 'unique 2'
                                ]
                            ], $rows);

        $rows = (TablaParentRepo::setRecursive(['/idchildFK', '/idchild2FK']))::toList();

        $this->assertEquals([
                                [
                                    'idtablaparentPK' => '1',
                                    'field1' => 'Parent #1',
                                    'idchildFK' => '1',
                                    'idchild2FK' => '2',
                                    'field2' => 'unique 1',
                                    '/idchildFK' => [
                                        'idtablachildPK' => '1',
                                        'valuechild' => 'Child #1',
                                        'idgrandchildFK' => '1'
                                    ],
                                    '/idchild2FK' => [
                                        'idtablachildPK' => '2',
                                        'valuechild' => 'Child #2',
                                        'idgrandchildFK' => '1'
                                    ],
                                ],
                                [
                                    'idtablaparentPK' => '2',
                                    'field1' => 'Parent #2',
                                    'idchildFK' => '1',
                                    'idchild2FK' => '2',
                                    'field2' => 'unique 2',
                                    '/idchildFK' => [
                                        'idtablachildPK' => '1',
                                        'valuechild' => 'Child #1',
                                        'idgrandchildFK' => '1'
                                    ],
                                    '/idchild2FK' => [
                                        'idtablachildPK' => '2',
                                        'valuechild' => 'Child #2',
                                        'idgrandchildFK' => '1'
                                    ],
                                ]
                            ], $rows);

        $rows = (TablaParentRepo::setRecursive(['/idchildFK', '/idchild2FK']))::first('1');

        $this->assertEquals([
                                'idtablaparentPK' => '1',
                                'field1' => 'Parent #1',
                                'idchildFK' => '1',
                                'idchild2FK' => '2',
                                'field2' => 'unique 1',
                                '/idchildFK' => [
                                    'idtablachildPK' => '1',
                                    'valuechild' => 'Child #1',
                                    'idgrandchildFK' => '1'
                                ],
                                '/idchild2FK' => [
                                    'idtablachildPK' => '2',
                                    'valuechild' => 'Child #2',
                                    'idgrandchildFK' => '1'
                                ],

                            ], $rows);

        $rows = (TablaParentRepo::setRecursive([
                                                   '/idchildFK',
                                                   '/idchild2FK',
                                                   '/idchildFK/idgrandchildFK',
                                                   '/idchildFK/idgrandchildFK/tablagrandchildcat'
                                               ]))::first('1');

        $this->assertEquals([
                                'idtablaparentPK' => '1',
                                'field1' => 'Parent #1',
                                'idchildFK' => '1',
                                'idchild2FK' => '2',
                                'field2' => 'unique 1',
                                '/idchildFK' => [
                                    'idtablachildPK' => '1',
                                    'valuechild' => 'Child #1',
                                    'idgrandchildFK' => '1',
                                    '/idgrandchildFK' => [
                                        'idgrandchildPK' => '1',
                                        'NameGrandChild' => 'GrandChild #1',
                                        '/tablagrandchildcat' => [
                                            [
                                                'IdTablaGrandChildCatPK' => '1',
                                                'Name' => 'GrandChild Cat #1',
                                                'IdgrandchildFK' => '1',
                                            ],
                                            [
                                                'IdTablaGrandChildCatPK' => '2',
                                                'Name' => 'GrandChild Cat #2',
                                                'IdgrandchildFK' => '1'
                                            ]
                                        ]
                                    ]
                                ],
                                '/idchild2FK' => [
                                    'idtablachildPK' => '2',
                                    'valuechild' => 'Child #2',
                                    'idgrandchildFK' => '1'
                                ]

                            ], $rows);
    }

    public function testSelectManyToOne_ManyToOne_OneToMany() {

        $rows = (TablaParentRepo::setRecursive([
                                                   '/idchildFK',
                                                   '/idchild2FK',
                                                   '/idchildFK/idgrandchildFK',
                                                   '/idchildFK/idgrandchildFK/tablagrandchildcat',
                                                   '/tablaparentxcategory'
                                               ]))::first('1');

        $this->assertEquals([
                                'idtablaparentPK' => '1',
                                'field1' => 'Parent #1',
                                'idchildFK' => '1',
                                'idchild2FK' => '2',
                                'field2' => 'unique 1',
                                '/idchildFK' => [
                                    'idtablachildPK' => '1',
                                    'valuechild' => 'Child #1',
                                    'idgrandchildFK' => '1',
                                    '/idgrandchildFK' => [
                                        'idgrandchildPK' => '1',
                                        'NameGrandChild' => 'GrandChild #1',
                                        '/tablagrandchildcat' => [
                                            [
                                                'IdTablaGrandChildCatPK' => '1',
                                                'Name' => 'GrandChild Cat #1',
                                                'IdgrandchildFK' => '1',
                                            ],
                                            [
                                                'IdTablaGrandChildCatPK' => '2',
                                                'Name' => 'GrandChild Cat #2',
                                                'IdgrandchildFK' => '1'
                                            ]
                                        ]
                                    ]
                                ],
                                '/idchild2FK' => [
                                    'idtablachildPK' => '2',
                                    'valuechild' => 'Child #2',
                                    'idgrandchildFK' => '1'
                                ],
                                '/tablaparentxcategory' => [
                                    [
                                        'idtablaparentPKFK' => '1',
                                        'idcategoryPKFK' => '1'
                                    ],
                                    [
                                        'idtablaparentPKFK' => '1',
                                        'idcategoryPKFK' => '2'
                                    ]
                                ]

                            ], $rows);
    }

    public function testSelectOneToMany_ManyToOne() {

        $rows = (TablaParentRepo::setRecursive([
                                                   '/tablaparentxcategory',
                                                   '/tablaparentxcategory/idcategoryPKFK'
                                               ]))::first('1');

        $this->assertEquals([
                                'idtablaparentPK' => '1',
                                'field1' => 'Parent #1',
                                'idchildFK' => '1',
                                'idchild2FK' => '2',
                                'field2' => 'unique 1',
                                '/tablaparentxcategory' => [
                                    [
                                        'idtablaparentPKFK' => '1',
                                        'idcategoryPKFK' => '1',
                                        '/idcategoryPKFK' => [
                                            'IdTablaCategoryPK' => '1',
                                            'Name' => 'Category #1'
                                        ]

                                    ],
                                    [
                                        'idtablaparentPKFK' => '1',
                                        'idcategoryPKFK' => '2',
                                        '/idcategoryPKFK' => [
                                            'IdTablaCategoryPK' => '2',
                                            'Name' => 'Category #2'
                                        ]
                                    ]
                                ]

                            ], $rows);
    }

}