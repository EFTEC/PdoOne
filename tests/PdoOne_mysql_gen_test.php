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

include __DIR__ . "/../examples/repo/tablacategoryRepo.php";
include __DIR__ . "/../examples/repo/tablachildRepo.php";
include __DIR__ . "/../examples/repo/tablagrandchildRepo.php";
include __DIR__ . "/../examples/repo/tablagrandchildcatRepo.php";
include __DIR__ . "/../examples/repo/tablaparentxcategoryRepo.php";
include __DIR__ . "/../examples/repo/tablaParentRepo.php";


/**
 * It tests the code generated in examples/repo
 * 
 * Class PdoOne_mysql_gen_test
 */
class PdoOne_mysql_gen_test extends TestCase
{
    /** @var PdoOne */
    protected $pdoOne;

    public function setUp() {
        $this->pdoOne = new PdoOne("mysql", "127.0.0.1", "travis", "", "travisdb");
        $this->pdoOne->connect();
        $this->pdoOne->logLevel = 3;
        TablagrandchildRepo::setPdoOne($this->pdoOne);
        TablagrandchildcatRepo::setPdoOne($this->pdoOne);


    }

    public function testGrandChild() {
        if (!TablagrandchildRepo::createTable()) {
            TablagrandchildRepo::truncate();
        }
        $gc = TablagrandchildRepo::factoryNull();
        $gc['idgrandchildPK'] = 1;
        $gc['NameGrandChild'] = 'GrandChild #1';
        $this->assertEquals(1, TablagrandchildRepo::insert($gc));
        $gc['idgrandchildPK'] = 2;
        $gc['NameGrandChild'] = 'GrandChild #2';
        $this->assertEquals(2, TablagrandchildRepo::insert($gc));
    }

    public function test2() {
        if (!TablagrandchildcatRepo::createTable()) {
            TablagrandchildcatRepo::truncate();
        }
        $gc = TablagrandchildcatRepo::factoryNull();
        $gc['IdTablaGrandChildCatPK'] = 1;
        $gc['Name'] = 'GrandChild Cat #1';
        $gc['IdgrandchildFK'] = 1;
        $this->assertEquals(1, TablagrandchildcatRepo::insert($gc));
        $gc['IdTablaGrandChildCatPK'] = 2;
        $gc['Name'] = 'GrandChild Cat #2';
        $gc['IdgrandchildFK'] = 1;
        $this->assertEquals(2, TablagrandchildcatRepo::insert($gc));
    }

    public function test3() {
        if (!TablaChildRepo::createTable()) {
            TablaChildRepo::truncate();
        }
        $gc = TablaChildRepo::factoryNull();
        $gc['idtablachildPK'] = 1;
        $gc['valuechild'] = 'Child #1';
        $gc['idgrandchildFK'] = 1;
        $this->assertEquals(1, TablaChildRepo::insert($gc));
        $gc['idtablachildPK'] = 2;
        $gc['valuechild'] = 'Child #2';
        $gc['idgrandchildFK'] = 1;
        $this->assertEquals(2, TablaChildRepo::insert($gc));
    }

    public function test4() {
        if (!TablaParentRepo::createTable()) {
            TablaParentRepo::truncate();
        }
        $gc = TablaParentRepo::factoryNull();
        $gc['idtablaparentPK'] = 1;
        $gc['field1'] = 'Parent #1';
        $gc['idchildFK'] = 1;
        $gc['idchild2FK'] = 2;
        $this->assertEquals(1, TablaParentRepo::insert($gc));
        $gc['idtablaparentPK'] = 2;
        $gc['field1'] = 'Parent #2';
        $gc['idchildFK'] = 1;
        $gc['idchild2FK'] = 2;
        $this->assertEquals(2, TablaParentRepo::insert($gc));
    }

    public function test4b() {
        if (!TablacategoryRepo::createTable()) {
            TablacategoryRepo::truncate();
        }
        $gc = TablacategoryRepo::factoryNull();
        $gc['IdTablaCategoryPK'] = 1;
        $gc['Name'] = 'Category #1';
        $this->assertEquals(1, TablacategoryRepo::insert($gc));
        $gc['IdTablaCategoryPK'] = 2;
        $gc['Name'] = 'Category #2';
        $this->assertEquals(2, TablacategoryRepo::insert($gc));
    }

    public function test4c() {
        if (!TablaparentxcategoryRepo::createTable()) {
            TablaparentxcategoryRepo::truncate();
        }
        $gc = TablaparentxcategoryRepo::factoryNull();
        $gc['idtablaparentPKFK'] = 1;
        $gc['idcategoryPKFK'] = 1;
        $this->assertEquals(0, TablaparentxcategoryRepo::insert($gc));
        $gc['idtablaparentPKFK'] = 1;
        $gc['idcategoryPKFK'] = 2;
        $this->assertEquals(0, TablaparentxcategoryRepo::insert($gc));
    }

    public function testSelect() {
        $rows = TablaParentRepo::toList();
        $this->assertEquals([
                                [
                                    'idtablaparentPK' => '1',
                                    'field1' => 'Parent #1',
                                    'idchildFK' => '1',
                                    'idchild2FK' => '2',
                                    'field2' => null
                                ],
                                [
                                    'idtablaparentPK' => '2',
                                    'field1' => 'Parent #2',
                                    'idchildFK' => '1',
                                    'idchild2FK' => '2',
                                    'field2' => null
                                ]
                            ], $rows);

        $rows = (TablaParentRepo::setRecursive(['/idchildFK', '/idchild2FK']))::toList();

        $this->assertEquals([
                                [
                                    'idtablaparentPK' => '1',
                                    'field1' => 'Parent #1',
                                    'idchildFK' => '1',
                                    'idchild2FK' => '2',
                                    'field2' => null,
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
                                    'field2' => null,
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
                                'field2' => null,
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
                                'field2' => null,
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
                                'field2' => null,
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
                                'field2' => null,
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