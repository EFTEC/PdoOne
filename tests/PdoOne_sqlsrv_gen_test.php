<?php /** @noinspection PhpClassConstantAccessedViaChildClassInspection */
/** @noinspection PhpIllegalPsrClassPathInspection */

/** @noinspection PhpUnhandledExceptionInspection */

use eftec\PdoOne;
use PHPUnit\Framework\TestCase;
use reposqlsrv\generated\TableCategoryRepo;
use reposqlsrv\generated\TableChildRepo;
use reposqlsrv\generated\TableGrandChildTagRepo;
use reposqlsrv\generated\TablegrandchildRepo;
use reposqlsrv\generated\TableParentRepo;
use reposqlsrv\generated\TableparentxcategoryRepo;

include __DIR__ . "/../vendor/autoload.php";
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

    public function setUp(): void
    {
        if (PdoOne::instance(false) === null) {
            $this->pdoOne = new PdoOne('sqlsrv', 'PCJC\SQLSERVER2017', 'sa', 'abc.123', 'testdb', '');
            $this->pdoOne->connect();
            $this->pdoOne->logLevel = 3;
            TablegrandchildRepo::setPdoOne($this->pdoOne);
            TableGrandChildTagRepo::setPdoOne($this->pdoOne);
        }
    }

    /*public function testDef() {
        $dep = $this->pdoOne->tableDependency(true, false);
        $this->assertEquals([
                                [
                                    0 => 'Tablecategory',
                                    1 => 'Tablegrandchildcat',
                                    2 => 'Tableparent',
                                    3 => 'Tableparentxcategory',
                                    4 => 'sysdiagrams',
                                    5 => 'Tablegrandchild',
                                    6 => 'Tablechild'
                                ],
                                [
                                    'Tablecategory' => array(),
                                    'Tablechild' => array(
                                        'idgrandchildFK' => 'Tablegrandchild',
                                        '/idgrandchildFK' => 'Tablegrandchild'
                                    ),
                                    'Tablegrandchild' => array(),
                                    'Tablegrandchildcat' => array(
                                        'IdgrandchildFK' => 'Tablegrandchild',
                                        '/IdgrandchildFK' => 'Tablegrandchild'
                                    ),
                                    'Tableparent' => array(
                                        'idchild2FK' => 'Tablechild',
                                        '/idchild2FK' => 'Tablechild',
                                        'idchildFK' => 'Tablechild',
                                        '/idchildFK' => 'Tablechild'
                                    ),
                                    'Tableparentxcategory' => array(
                                        'idcategoryPKFK' => 'Tablecategory',
                                        '/idcategoryPKFK' => 'Tablecategory',
                                        'idtablaparentPKFK' => 'Tableparent',
                                        '/idtablaparentPKFK' => 'Tableparent'
                                    ),
                                    'sysdiagrams' => array()
                                ],
                                [
                                    'Tablecategory' => array(
                                        'IdTableCategoryPK' => array(
                                            0 => '/idcategoryPKFK',
                                            1 => 'Tableparentxcategory'
                                        )
                                    ),
                                    'Tablechild' => array(
                                        'idTablechildPK' => array(
                                            0 => '/idchildFK',
                                            1 => 'Tableparent'
                                        )
                                    ),
                                    'Tablegrandchild' => array(
                                        'idgrandchildPK' => array(
                                            0 => '/IdgrandchildFK',
                                            1 => 'Tablegrandchildcat'
                                        )
                                    ),
                                    'Tablegrandchildcat' => array(),
                                    'Tableparent' => array(
                                        'idTableparentPK' => array(
                                            0 => '/idtablaparentPKFK',
                                            1 => 'Tableparentxcategory'
                                        )
                                    ),
                                    'Tableparentxcategory' => array(),
                                    'sysdiagrams' => array()
                                ]
                            ], $dep);

        $a1 = $this->pdoOne->getDefTableFK('Tablegrandchild', false);
        $this->assertEquals([], $a1);
        $a1 = $this->pdoOne->getDefTableFK('Tablechild', false);
        $this->assertEquals([
                                'idgrandchildFK' => array(
                                    'key' => 'FOREIGN KEY',
                                    'refcol' => 'idgrandchildPK',
                                    'reftable' => 'Tablegrandchild',
                                    'extra' => ''
                                ),
                                '/idgrandchildFK' => array(
                                    'key' => 'MANYTOONE',
                                    'refcol' => 'idgrandchildPK',
                                    'reftable' => 'Tablegrandchild',
                                    'extra' => null
                                )
                            ], $a1);
        $a1 = $this->pdoOne->getDefTableFK('Tablegrandchildcat', false);
        $this->assertEquals([
                                'IdgrandchildFK' => array(
                                    'key' => 'FOREIGN KEY',
                                    'refcol' => 'idgrandchildPK',
                                    'reftable' => 'Tablegrandchild',
                                    'extra' => ''
                                ),
                                '/IdgrandchildFK' => array(
                                    'key' => 'MANYTOONE',
                                    'refcol' => 'idgrandchildPK',
                                    'reftable' => 'Tablegrandchild',
                                    'extra' => null
                                )
                            ], $a1);
    }*/
    public function test_createtruncate(): void
    {
        if (!TableGrandChildTagRepo::createTable()) {
            $this->assertNotFalse(TableGrandChildTagRepo::deleteAll());
        }
        $this->pdoOne->runRawQuery("DBCC CHECKIDENT ('" . TableGrandChildTagRepo::TABLE . "', RESEED, 0)", [], true);
        if (!TableparentxcategoryRepo::createTable()) {
            $this->assertNotFalse(TableparentxcategoryRepo::deleteAll());
        }
        // it doesn't have identity
        //$this->pdoOne->runRawQuery("DBCC CHECKIDENT ('".TableparentxcategoryRepo::TABLE."', RESEED, 1)",[],true);
        if (!TableCategoryRepo::createTable()) {
            $this->assertNotFalse(TableCategoryRepo::deleteAll());
        }
        $this->pdoOne->runRawQuery("DBCC CHECKIDENT ('" . TableCategoryRepo::TABLE . "', RESEED, 0)", [], true);
        if (!TableParentRepo::createTable()) {
            TableParentRepo::deleteAll();
        }
        $this->pdoOne->runRawQuery("DBCC CHECKIDENT ('" . TableParentRepo::TABLE . "', RESEED, 0)", [], true);
        if (!TableChildRepo::createTable()) {
            $this->assertNotFalse(TableChildRepo::deleteAll());
        }
        $this->pdoOne->runRawQuery("DBCC CHECKIDENT ('" . TableChildRepo::TABLE . "', RESEED, 0)", [], true);
        if (!TablegrandchildRepo::createTable()) {
            $this->assertNotFalse(TablegrandchildRepo::deleteAll());
        }
        $this->pdoOne->runRawQuery("DBCC CHECKIDENT ('" . TablegrandchildRepo::TABLE . "', RESEED, 0)", [], true);
    }

    public function testGrandChild(): void
    {
        $gc = TablegrandchildRepo::factoryNull();
        $gc['idgrandchildPK'] = 1;
        $gc['NameGrandChild'] = 'GrandChild #1';
        $this->assertGreaterThanOrEqual(1, TablegrandchildRepo::insert($gc));
        $gc['idgrandchildPK'] = 2;
        $gc['NameGrandChild'] = 'GrandChild #2';
        $this->assertGreaterThanOrEqual(2, TablegrandchildRepo::insert($gc));
    }

    public function testTablegrandchildcat(): void
    {
        $gc = TableGrandChildTagRepo::factoryNull();
        //$gc['IdTableGrandChildCatPK'] = 1;
        $gc['Name'] = 'GrandChild Cat #1';
        $gc['IdgrandchildFK'] = 1;
        $this->assertGreaterThanOrEqual(1, TableGrandChildTagRepo::insert($gc));
        //$gc['IdTableGrandChildCatPK'] = 2;
        $gc['Name'] = 'GrandChild Cat #2';
        $gc['IdgrandchildFK'] = 1;
        $this->assertGreaterThanOrEqual(2, TableGrandChildTagRepo::insert($gc));
    }

    public function test3(): void
    {
        $gc = TableChildRepo::factoryNull();
        $gc['idTablechildPK'] = 1;
        $gc['valuechild'] = 'Child #1';
        $gc['idgrandchildFK'] = 1;
        $this->assertGreaterThanOrEqual(1, TableChildRepo::insert($gc));
        $gc['idTablechildPK'] = 2;
        $gc['valuechild'] = 'Child #2';
        $gc['idgrandchildFK'] = 1;
        $this->assertGreaterThanOrEqual(2, TableChildRepo::insert($gc));
    }

    public function test4(): void
    {
        $gc = TableParentRepo::factoryNull();
        $gc['idTableparentPK'] = 1;
        $gc['field1'] = 'Parent #1';
        $gc['field2'] = 'unique 1';
        $gc['idchildFK'] = 1;
        $gc['idchild2FK'] = 2;
        $gc['fieldUnique'] = 'uni';
        $this->assertGreaterThanOrEqual(1, TableParentRepo::insert($gc));
        $gc['idTableparentPK'] = 2;
        $gc['field1'] = 'Parent #2';
        $gc['field2'] = 'unique 2';
        $gc['idchildFK'] = 1;
        $gc['idchild2FK'] = 2;
        $gc['fieldUnique'] = 'unidos';
        $this->assertGreaterThanOrEqual(2, TableParentRepo::insert($gc));
    }

    public function test4b(): void
    {
        $gc = []; // TableCategoryRepo::factoryNull();
//        $gc['IdTableCategoryPK'] = 1;
        $gc['Name'] = 'Category #1';
        $this->assertGreaterThanOrEqual(1, TableCategoryRepo::insert($gc));
        //      $gc['IdTableCategoryPK'] = 2;
        $gc['Name'] = 'Category #2';
        $this->assertGreaterThanOrEqual(2, TableCategoryRepo::insert($gc));
    }

    public function test4c(): void
    {
        $gc = TableparentxcategoryRepo::factoryNull();
        $gc['idtablaparentPKFK'] = 1;
        $gc['idcategoryPKFK'] = 1;
        $this->assertEquals('1', TableparentxcategoryRepo::insert($gc));
        $gc['idtablaparentPKFK'] = 1;
        $gc['idcategoryPKFK'] = 2;
        $this->assertEquals('1', TableparentxcategoryRepo::insert($gc));
    }

    public function testSelect(): void
    {
        $rows = TableParentRepo::toList();
        unset($rows[0]['extracol'], $rows[1]['extracol']);
        $this->assertEquals([
            [
                'idchildFK' => 1,
                'idchild2FK' => 2,
                'idtablaparentPK' => 1,
                'fieldVarchar' => null,
                'fieldInt' => null,
                'fielDecimal' => null,
                'fieldDateTime' => null,
                'fieldUnique' => 'uni',
                'fieldKey' => null,
                'extracol2' => 20,
            ],
            [
                'idchildFK' => 1,
                'idchild2FK' => 2,
                'idtablaparentPK' => 2,
                'fieldVarchar' => null,
                'fieldInt' => null,
                'fielDecimal' => null,
                'fieldDateTime' => null,
                'fieldUnique' => 'unidos',
                'fieldKey' => null,
                'extracol2' => 20,
            ]
        ], $rows);
        $rows = (TableParentRepo::setRecursive(['_idchildFK', '_idchild2FK']))->toList();
        unset($rows[0]['extracol'], $rows[1]['extracol']);
        $this->assertEquals([
            [
                'idchildFK' => 1,
                'idchild2FK' => 2,
                'idtablaparentPK' => 1,
                'fieldVarchar' => null,
                'fieldInt' => null,
                'fielDecimal' => null,
                'fieldDateTime' => null,
                'fieldUnique' => 'uni',
                'fieldKey' => null,
                '_idchildFK' => [
                    'idtablachildPK' => 1,
                    'NameChild' => null,
                    'idgrandchildFK' => 1,
                ],
                'extracol2' => 20
            ],
            [
                'idchildFK' => 1,
                'idchild2FK' => 2,
                'idtablaparentPK' => 2,
                'fieldVarchar' => null,
                'fieldInt' => null,
                'fielDecimal' => null,
                'fieldDateTime' => null,
                'fieldUnique' => 'unidos',
                'fieldKey' => null,
                '_idchildFK' => ['idtablachildPK' => 1,
                    'NameChild' => null,
                    'idgrandchildFK' => 1],
                'extracol2' => 20
            ]
        ], $rows);
        $rows = (TableParentRepo::setRecursive(['_idchildFK', '_TableParentExt']))->first('1');
        unset($rows['extracol']);
        $this->assertEquals([
            'idchildFK' => 1,
            'idchild2FK' => 2,
            'idtablaparentPK' => 1,
            'fieldVarchar' => null,
            'fieldInt' => null,
            'fielDecimal' => null,
            'fieldDateTime' => null,
            'fieldUnique' => 'uni',
            'fieldKey' => null,
            'extracol2' => 20,
            '_idchildFK' => ['idtablachildPK' => 1,
                'NameChild' => null,
                'idgrandchildFK' => 1],
            '_TableParentExt' => ['idtablaparentExtPK' => 1,
                'fieldExt' => null],
        ], $rows);
        $rows = (TableParentRepo::setRecursive([
            '_idchildFK',
            '_idchildFK/_idgrandchildFK',
            '_idchildFK/idgrandchildFK/Tablegrandchildcat'
        ]))->first('1');
        unset($rows['extracol']);
        $this->assertEquals([
            'idchildFK' => 1,
            'idchild2FK' => 2,
            'idtablaparentPK' => 1,
            'fieldVarchar' => null,
            'fieldInt' => null,
            'fielDecimal' => null,
            'fieldDateTime' => null,
            'fieldUnique' => 'uni',
            'fieldKey' => null,
            'extracol2' => 20,
            '_idchildFK' => ['idtablachildPK' => 1,
                'NameChild' => null,
                'idgrandchildFK' => 1,
                '_idgrandchildFK' => [
                    'idgrandchildPK' => 1,
                    'NameGrandChild' => 'GrandChild #1'
                ]
            ]
        ], $rows);
    }

    public function testSelectManyToOne_ManyToOne_OneToMany(): void
    {
        $rows = (TableParentRepo::setRecursive([
            '_idchildFK',
            '_idchildFK/_idgrandchildFK',
            '_idchildFK/_idgrandchildFK/_Tablegrandchildcat',
            '_TableParentxCategory'
        ]))->first('1');
        unset($rows['extracol']);
        //var_export($rows);
        $this->assertEquals(array (
            'idtablaparentPK' => 1,
            'fieldVarchar' => NULL,
            'idchildFK' => 1,
            'idchild2FK' => 2,
            'fieldInt' => NULL,
            'fielDecimal' => NULL,
            'fieldDateTime' => NULL,
            'fieldUnique' => 'uni',
            'fieldKey' => NULL,
            '_idchildFK' =>
                array (
                    'idtablachildPK' => 1,
                    'NameChild' => NULL,
                    'idgrandchildFK' => 1,
                    '_idgrandchildFK' =>
                        array (
                            'idgrandchildPK' => 1,
                            'NameGrandChild' => 'GrandChild #1',
                        ),
                ),
            'extracol2' => 20,
            '_TableParentxCategory' =>
                array (
                    0 =>
                        array (
                            'IdTableCategoryPK' => 1,
                            'Name' => 'Category #1',
                        ),
                    1 =>
                        array (
                            'IdTableCategoryPK' => 2,
                            'Name' => 'Category #2',
                        ),
                ),
        ), $rows);
    }

    public function testSelectOneToMany_ManyToOne(): void
    {
        $rows = (TableParentRepo::setRecursive([
            '_TableParentxCategory',
            //'_TableParentxCategory/_idcategoryPKFK'
        ]))->first('1');
        unset($rows['extracol']);
        $this->assertEquals(array (
            'idtablaparentPK' => 1,
            'fieldVarchar' => NULL,
            'idchildFK' => 1,
            'idchild2FK' => 2,
            'fieldInt' => NULL,
            'fielDecimal' => NULL,
            'fieldDateTime' => NULL,
            'fieldUnique' => 'uni',
            'fieldKey' => NULL,
            'extracol2' => 20,
            '_TableParentxCategory' =>
                array (
                    0 =>
                        array (
                            'IdTableCategoryPK' => 1,
                            'Name' => 'Category #1',
                        ),
                    1 =>
                        array (
                            'IdTableCategoryPK' => 2,
                            'Name' => 'Category #2',
                        ),
                ),
        ), $rows);
    }

}
