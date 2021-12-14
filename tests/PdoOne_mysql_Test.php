<?php /** @noinspection PhpUnusedLocalVariableInspection */
/** @noinspection UnknownInspectionInspection */
/** @noinspection SqlResolve */
/** @noinspection PhpPossiblePolymorphicInvocationInspection */
/** @noinspection SuspiciousAssignmentsInspection */

/** @noinspection PhpUndefinedClassInspection */

/** @noinspection PhpUnhandledExceptionInspection */
/** @noinspection SqlNoDataSourceInspection */
/** @noinspection SqlDialectInspection */

namespace eftec\tests;


use eftec\IPdoOneCache;
use eftec\PdoOne;
use eftec\PdoOneQuery;
use Exception;
use PHPUnit\Framework\TestCase;

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

    public function getCache($uid, $family = '')
    {

        if (isset($this->cacheData[$uid])) {
            $this->cacheCounter++;
            return $this->cacheData[$uid];
        }
        return false;
    }

    /**
     * @param string $uid
     * @param string $family
     * @param null   $data
     * @param null   $ttl
     */
    public function setCache($uid, $family = '', $data = null, $ttl = null)
    {
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

    /**
     * @param string       $uid
     * @param string|array $family
     *
     * @return void
     */
    public function invalidateCache($uid = '', $family = '')
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
}


class PdoOne_mysql_Test extends TestCase
{
    /** @var PdoOne */
    protected $pdoOne;

    public function setUp()
    {
        $this->pdoOne = new PdoOne('mysql', '127.0.0.1', 'travis', '', 'travisdb');
        $this->pdoOne->connect();
        $this->pdoOne->logLevel = 3;

        $cache = new CacheServicesmysql();
        $this->pdoOne->setCacheService($cache);
    }


    public function test_procedure() {
        if(!$this->pdoOne->objectExist('tablelog')) {
            self::assertEquals(true,
                $this->pdoOne->createTable('tablelog'
                    , ['id' => 'int not null AUTO_INCREMENT', 'name' => 'varchar(45)', 'description' => 'varchar(45)'],
                    ['id' => 'PRIMARY KEY']));
        }

        if($this->pdoOne->objectExist('new_procedure','procedure')) {
            $this->pdoOne->drop('new_procedure','procedure');
        }
        $this->pdoOne->createProcedure('new_procedure',
            [
                ['in','in_name','varchar(45)'],
                ['out','in_description','varchar(45)']
            ]
            ,"  insert into tablelog(name,description) values(in_name,in_description);
            set in_description='done!';"
        );
        $args=['in_name'=>'aa','in_description'=>'bbb'];
        self::assertEquals(true,$this->pdoOne->callProcedure('new_procedure',$args,['in_description']));

        self::assertEquals('done!',$args['in_description']);
    }

    public function test_dep()
    {
        // delete all tables
        $tables = $this->pdoOne->objectList('table', true);
        foreach ($tables as $table) {
            self::assertEquals(true, $this->pdoOne->dropTable($table));
        }
        // create two tables
        self::assertEquals(true,
            $this->pdoOne->createTable('country', ['countryid' => 'int not null', 'name' => 'varchar(50)'],
                ['countryid' => 'PRIMARY KEY']));
        self::assertEquals(true, $this->pdoOne->createTable('city',
            ['cityid' => 'int not null', 'name' => 'varchar(50)', 'countryfk' => 'int not null'],
            ['cityid' => 'PRIMARY KEY']));
        $this->pdoOne->createFK('city', ['countryfk' => 'FOREIGN KEY REFERENCES`country`(`countryid`)']);
        echo str_replace(["\n", "\t", '    ', '   ', '  ', ' '], '',
            PdoOne::varExport($this->pdoOne->tableDependency(true), ''));
    }

    /**
     * @doesNotPerformAssertions
     */
    public function test_db()
    {
        $this->pdoOne->db('travisdb');
    }


    public function test_readonly()
    {
        self::assertEquals(false, $this->pdoOne->readonly(), 'the database is read only');
    }

    public function test_connect()
    {
        $this->expectException(Exception::class);
        $this->pdoOne->connect();
    }

    public function test_chainresetErrorList()
    {
        $this->pdoOne->logLevel = 3;
        $rows = $this->pdoOne->genError(false)->select('select 123 field1 from dual222')->toList();
        self::assertEquals(false, $rows);
        try {
            $rows = $this->pdoOne->genError(true)->select('select 123 field1 from dual222')->toList();
        } catch (Exception $exception) {
            $rows = false;
        }
        self::assertEquals(false, $rows);

        try {
            $rows = $this->pdoOne->genError(true)->select('select 123 field1 from dual222')->toList();
        } catch (Exception $exception) {
            $rows = false;
        }
        self::assertEquals(false, $rows);

        $this->pdoOne->throwOnError = false;
        $this->pdoOne->traceBlackList=[];
        $rows = $this->pdoOne->select('select 123 field1 from dual222')->toList();
        $this->pdoOne->throwOnError = true;

        self::assertEquals(false, $rows);


        self::assertNotEmpty($this->pdoOne->errorText); // there is an error.
    }

    public function test_chainresetErrorListSimple()
    {
        $this->pdoOne->logLevel = 3;
        $rows = $this->pdoOne->genError(false)->select('select 123 field1 from dual222')->toListSimple();
        self::assertEquals(false, $rows);
        try {
            $rows = $this->pdoOne->genError(true)->select('select 123 field1 from dual222')->toListSimple();
        } catch (Exception $exception) {
            $rows = false;
        }
        self::assertEquals(false, $rows);

        try {
            $rows = $this->pdoOne->genError(true)->select('select 123 field1 from dual222')->toListSimple();
        } catch (Exception $exception) {
            $rows = false;
        }
        self::assertEquals(false, $rows);

        $this->pdoOne->throwOnError = false;
        $rows = $this->pdoOne->select('select 123 field1 from dual222')->toListSimple();
        $this->pdoOne->throwOnError = true;

        self::assertEquals(false, $rows);


        self::assertNotEmpty($this->pdoOne->errorText); // there is an error.
    }

    public function test_genCode()
    {
        if (!$this->pdoOne->tableExist('table1')) {
            $this->pdoOne->createTable('table1', ['id' => 'int']);
        }
        self::assertNotEquals('', $this->pdoOne->generateCodeClass('table1'));
        self::assertEquals("['id'=>0]", $this->pdoOne->generateCodeArray('table1'));
        self::assertContains("array \$result=array(['id'=>0])",
            $this->pdoOne->generateCodeSelect('select * from table1'));
        self::assertContains('$pdo->createTable(\'table1', $this->pdoOne->generateCodeCreate('table1'));
    }



    public function test_chainresetErrorMeta()
    {
        $this->pdoOne->logLevel = 3;
        $rows = $this->pdoOne->genError(false)->select('select 123 field1 from dual222')->toMeta();
        self::assertEquals(false, $rows);
        try {
            $rows = $this->pdoOne->genError(true)->select('select 123 field1 from dual222')->toMeta();
        } catch (Exception $exception) {
            $rows = false;
        }
        self::assertEquals(false, $rows);

        try {
            $rows = $this->pdoOne->genError(true)->select('select 123 field1 from dual222')->toMeta();
        } catch (Exception $exception) {
            $rows = false;
        }
        self::assertEquals(false, $rows);

        $this->pdoOne->throwOnError = false;
        $rows = $this->pdoOne->select('select 123 field1 from dual222')->toMeta();
        $this->pdoOne->throwOnError = true;

        self::assertEquals(false, $rows);


        self::assertNotEmpty($this->pdoOne->errorText); // there is an error.
    }

    public function test_chainresetErrorFirst()
    {
        $this->pdoOne->logLevel = 3;
        $rows = $this->pdoOne->genError(false)->select('select 123 field1 from dual222')->first();
        self::assertEquals(false, $rows);
        try {
            $rows = $this->pdoOne->genError(true)->select('select 123 field1 from dual222')->first();
        } catch (Exception $exception) {
            $rows = false;
        }
        self::assertEquals(false, $rows);

        try {
            $rows = $this->pdoOne->genError(true)->select('select 123 field1 from dual222')->first();
        } catch (Exception $exception) {
            $rows = false;
        }
        self::assertEquals(false, $rows);

        $this->pdoOne->throwOnError = false;
        $rows = $this->pdoOne->select('select 123 field1 from dual222')->first();
        $this->pdoOne->throwOnError = true;

        self::assertEquals(false, $rows);


        self::assertNotEmpty($this->pdoOne->errorText); // there is an error.

        //$this->pdoOne->builderReset();
        //$rows=$this->pdoOne->select('select 123 field1 from dual')->toList();
        //$this->assertEquals([['field1'=>123]],$rows);
    }

    public function test_chainresetErrorLast()
    {
        $this->pdoOne->logLevel = 3;
        $rows = $this->pdoOne->genError(false)->select('select 123 field1 from dual222')->last();
        self::assertEquals(false, $rows);
        try {
            $rows = $this->pdoOne->genError(true)->select('select 123 field1 from dual222')->last();
            $rows = 'XXX';
        } catch (Exception $exception) {
            var_dump("this message must be visible");
            $rows = false;
        }
        self::assertEquals(false, $rows);

        try {
            $rows = $this->pdoOne->genError(true)->select('select 123 field1 from dual222')->last();
        } catch (Exception $exception) {
            $rows = false;
        }
        self::assertEquals(false, $rows);

        $this->pdoOne->throwOnError = false;
        $rows = $this->pdoOne->select('select 123 field1 from dual222')->last();
        $this->pdoOne->throwOnError = true;

        self::assertEquals(false, $rows);


        self::assertNotEmpty($this->pdoOne->errorText); // there is an error.

    }

    public function test_createtable()
    {
        if ($this->pdoOne->tableExist('table5')) {
            $this->pdoOne->dropTable('table5');
        }
        $r = $this->pdoOne->createTable('table5', ['id' => 'int NOT NULL', 'name' => 'varchar(50)'],
            ['id' => 'PRIMARY KEY']);
        self::assertEquals(true, $r);

        self::assertEquals(array(
            'id' => [
                'phptype' => 'int',
                'conversion' => null,
                'type' => 'int',
                'size' => null,
                'null' => false,
                'identity' => false,
                'sql' => 'int not null'
            ],
            'name' => [
                'phptype' => 'string',
                'conversion' => null,
                'type' => 'varchar',
                'size' => '50',
                'null' => true,
                'identity' => false,
                'sql' => 'varchar(50)'

            ]
        ), $this->pdoOne->getDefTable('table5'));
        self::assertEquals(array('id' => 'PRIMARY KEY'), $this->pdoOne->getDefTableKeys('table5'));
        self::assertEquals(array(), $this->pdoOne->getDefTableFK('table5'));
    }


    public function test_chainreset()
    {
        $this->pdoOne->logLevel = 3;
        $rows = $this->pdoOne->select('select 123 field1 from dual');
        // $this->pdoOne->builderReset();
        $rows = $this->pdoOne->select('select 123 field1 from dual')->toList();
        self::assertEquals([['field1' => 123]], $rows);
    }

    public function test_cache()
    {
        $this->pdoOne->getCacheService()->cacheCounter = 0;

        $rows = $this->pdoOne->select('select 123 field1 from dual')->useCache()->toList();
        self::assertEquals([['field1' => 123]], $rows);

        $this->pdoOne->select('select 123 field1 from dual')->where('1=1')->order('1')->useCache()->toList();

        $rows = $this->pdoOne->select('select 123 field1 from dual')->useCache()->toList();
        self::assertEquals([['field1' => 123]], $rows);


        self::assertEquals(1, $this->pdoOne->getCacheService()->cacheCounter); // 1= cache used 1 time
        $this->pdoOne->invalidateCache();
        $rows = $this->pdoOne->select('select 123 field1 from dual')->useCache()
            ->toList(); // it should not find any cache
        self::assertEquals([['field1' => 123]], $rows);
        self::assertEquals(1, $this->pdoOne->getCacheService()->cacheCounter); // 1= cache used 1 time
        $this->pdoOne->getCacheService()->cacheCounter = 0;
    }

    public function test_cache_expire()
    {
        $this->pdoOne->getCacheService()->cacheCounter = 0;
        $this->pdoOne->invalidateCache();
        $rows = $this->pdoOne->select('select 123 field1 from dual')->useCache(1, 'dual')->toList(); // no cache
        sleep(2); // enough time to expire the cache.
        $rows = $this->pdoOne->select('select 123 field1 from dual')->useCache(1, 'dual')->toList(); // +1 cache
        self::assertEquals(1, $this->pdoOne->getCacheService()->cacheCounter); // 1= cache used 1 time

    }

    public function test_cache_family()
    {
        $this->pdoOne->getCacheService()->cacheCounter = 0;
        $this->pdoOne->invalidateCache();
        $rows = $this->pdoOne->select('123 field1')->from('dual')->useCache(5555, 'dual')->toList(); // no cache
        $rows = $this->pdoOne->select('123 field1')->from('dual')->useCache(5555, 'dual')->toList(); // +1 cache
        $rows = $this->pdoOne->select('123 field1')->from('dual')->useCache(5555, '*')->toList(); // +1 cache
        $rows = $this->pdoOne->select('123 field2')->from('dual')->useCache(5555, '*')
            ->toList(); // other family no cache
        self::assertEquals(2, $this->pdoOne->getCacheService()->cacheCounter); // 1= cache used 1 time
    }

    public function test_cache_multiple_family()
    {
        $this->pdoOne->getCacheService()->cacheCounter = 0;
        $this->pdoOne->invalidateCache();
        $rows = $this->pdoOne->select('select 123 field1 from dual')->useCache(5555, 'dual')->toList(); // no cache
        $rows = $this->pdoOne->select('select 123 field2 from dual')->useCache(5555, 'dual2')->toList(); // no cache
        $rows = $this->pdoOne->select('select 123 field1 from dual')->useCache(5555, 'dual')->toList(); // +1 cache used
        $rows = $this->pdoOne->select('select 123 field2 from dual')->useCache(5555, 'dual2')
            ->toList(); // +1 cache used
        self::assertEquals(2, $this->pdoOne->getCacheService()->cacheCounter); // 1= cache used 1 time
        $this->pdoOne->invalidateCache('', ['dual']); // invalidate one family.
        $rows = $this->pdoOne->select('select 123 field1 from dual')->useCache(5555, 'dual')->toList(); // no cache
        $rows = $this->pdoOne->select('select 123 field2 from dual')->useCache(5555, 'dual2')
            ->toList(); // +1 cache used
        self::assertEquals(3, $this->pdoOne->getCacheService()->cacheCounter); // 1= cache used 1 time

    }

    public function test_cache_noCache()
    {
        $this->pdoOne->setCacheService(null);


        $rows = $this->pdoOne->select('select 123 field1 from dual')->useCache()->toList();
        self::assertEquals([['field1' => 123]], $rows);
        $rows = $this->pdoOne->select('select 123 field1 from dual')->where('1=1')->order('1')->useCache()->toList();
        $rows = $this->pdoOne->select('select 123 field1 from dual')->useCache()->toList();
        self::assertEquals([['field1' => 123]], $rows);
        //$this->assertEquals(1,$this->pdoOne->getCacheService()->cacheCounter); // 1= cache used 1 time
        $rows = $this->pdoOne->invalidateCache();

        $rows = $this->pdoOne->select('select 123 field1 from dual')->useCache()->toList();
        self::assertEquals([['field1' => 123]], $rows);
        //$this->assertEquals(1,$this->pdoOne->getCacheService()->cacheCounter); // 1= cache used 1 time
        //$this->pdoOne->getCacheService()->cacheCounter=0;

        $cache = new CacheServicesmysql();
        $this->pdoOne->setCacheService($cache);
    }

    public function test_open()
    {
        //$this->expectException(\Exception::class);
        //$this->pdoOne->open(true);
        try {
            $r = $this->pdoOne->runRawQuery('drop table product_category');
            self::assertEquals(true, $r, 'Drop failed');
        } catch (Exception $e) {
            $r = false;
            // drops silently
        }


        $sqlT2 = 'CREATE TABLE `product_category` (
	    `id_category` INT NOT NULL,
	    `catname` VARCHAR(45) NULL,
	    PRIMARY KEY (`id_category`));';

        try {
            $r = $this->pdoOne->runRawQuery($sqlT2);
        } catch (Exception $e) {
            echo $e->getMessage() . '<br>';
        }
        self::assertEquals(true, $r, 'failed to create table');
        $this->pdoOne->getCacheService()->cacheCounter = 0;

        self::assertGreaterThan(1, count($this->pdoOne->objectList()));
        // we add some values
        $this->pdoOne->set(['id_category' => 123, 'catname' => 'cheap'])->from('product_category')->insert();
        $this->pdoOne->insert('product_category', ['id_category', 'catname'],
            ['id_category' => 2, 'catname' => 'cheap']);

        $this->pdoOne->insert('product_category', ['id_category' => 3, 'catname' => 'cheap']);

        $this->pdoOne->insert('product_category', ['id_category' => 4, 'catname' => 'cheap4']);
        $this->pdoOne->insert('product_category', ['id_category', '5', 'catname', 'cheap']);
        $count = $this->pdoOne->count('from product_category');
        self::assertEquals(5, $count, 'insert must value 5');

        $count = $this->pdoOne->select('select id_category from product_category where id_category=123')->useCache()
            ->firstScalar();
        self::assertEquals(123, $count, 'insert must value 123');
        $count = $this->pdoOne->select('select id_category from product_category where id_category=123')->useCache()
            ->firstScalar();
        self::assertEquals(123, $count, 'insert must value 123');

        self::assertEquals(1, $this->pdoOne->getCacheService()->cacheCounter); // 1= cache used 1 time

        $count = $this->pdoOne->select('select catname from product_category where id_category>0')->useCache()
            ->firstScalar();
        self::assertEquals('cheap', $count);
        $count = $this->pdoOne->select('select catname from product_category where id_category>0')->useCache()
            ->firstScalar();
        self::assertEquals('cheap', $count);

        $count = $this->pdoOne->select('select catname from product_category where id_category=4')->useCache()->first();
        self::assertEquals(['catname' => 'cheap4'], $count);
        $count = $this->pdoOne->select('select catname from product_category where id_category=4')->useCache()->first();
        self::assertEquals(['catname' => 'cheap4'], $count);

        $count = $this->pdoOne->select('select catname from product_category')->useCache()->last();
        self::assertEquals(['catname' => 'cheap'], $count);
        $count = $this->pdoOne->select('select catname from product_category')->useCache()->last();
        self::assertEquals(['catname' => 'cheap'], $count);

        $count = $this->pdoOne->select('select catname from product_category')->where('id_category=?', [4])->useCache()
            ->firstScalar();
        self::assertEquals('cheap4', $count, 'insert must value cheap4');
        $count = $this->pdoOne->select('select catname from product_category')->where('id_category=?', [4])->useCache()
            ->firstScalar();
        self::assertEquals('cheap4', $count, 'insert must value cheap4');
        $count = $this->pdoOne->select('select catname from product_category')->where('id_category=?', [4])
            ->order('id_category')->useCache()->firstScalar();
        self::assertEquals('cheap4', $count, 'insert must value cheap4');
        $count = $this->pdoOne->select('select catname from product_category')->where('id_category=?', [3])->useCache()
            ->firstScalar();
        self::assertEquals('cheap', $count, 'insert must value cheap');

        $count = $this->pdoOne->select('select catname from product_category')
            ->where('id_category=:idcat', ['idcat' => 4])->firstScalar();
        self::assertEquals('cheap4', $count, 'insert must value cheap4');
        $count = $this->pdoOne->select('select catname from product_category')
            ->where('id_category=:idcat', ['idcat' => 4])->firstScalar();
        self::assertEquals('cheap4', $count, 'insert must value cheap4');
        self::assertEquals(137, $this->pdoOne->from('product_category')->sum('id_category'),
            'sum must value 137');
        self::assertEquals(2, $this->pdoOne->from('product_category')->min('id_category'),
            'min must value 2');
        self::assertEquals(123, $this->pdoOne->from('product_category')->max('id_category'),
            'max must value 123');
        self::assertEquals(27.4, $this->pdoOne->from('product_category')->avg('id_category'),
            'avg must value 27.4');
        self::assertEquals([
            ['id_category' => 2],
            ['id_category' => 3],
            ['id_category' => 4],
            ['id_category' => 5],
            ['id_category' => 123]
        ], $this->pdoOne->select('id_category')->from('product_category')->useCache()->toList());

        self::assertEquals([
            ['id_category' => 2],
            ['id_category' => 3],
            ['id_category' => 4],
            ['id_category' => 5],
            ['id_category' => 123]
        ], $this->pdoOne->select('id_category')->from('product_category')->useCache()->toList());
        self::assertEquals(6, $this->pdoOne->getCacheService()->cacheCounter); // 1= cache used 1 time

        self::assertEquals([2, 3, 4, 5, 123],
            $this->pdoOne->select('id_category')->from('product_category')->useCache()->toListSimple());
        self::assertEquals([2, 3, 4, 5, 123],
            $this->pdoOne->select('id_category')->from('product_category')->useCache()->toListSimple());
        self::assertEquals([2 => 'cheap', 3 => 'cheap', '4' => 'cheap4', 5 => 'cheap', 123 => 'cheap'],
            $this->pdoOne->select('id_category,catname')->from('product_category')->useCache()->toListKeyValue());
        self::assertEquals([2 => 'cheap', 3 => 'cheap', '4' => 'cheap4', 5 => 'cheap', 123 => 'cheap'],
            $this->pdoOne->select('id_category,catname')->from('product_category')->useCache()->toListKeyValue());

        self::assertEquals(8, $this->pdoOne->getCacheService()->cacheCounter); // 3= cache used 1 time

        self::assertEquals([['id_category' => 3]],
            $this->pdoOne->select('id_category')->from('product_category')->where('id_category', 3)->useCache()->toList());
        self::assertEquals([['id_category' => 4]],
            $this->pdoOne->select('id_category')->from('product_category')->where('id_category', 4)->useCache()->toList());
        self::assertEquals(['id_category' => 123],
            $this->pdoOne->select('id_category')->from('product_category')->order('id_category desc')->useCache()->first());
        self::assertEquals(['id_category' => 2],
            $this->pdoOne->select('id_category')->from('product_category')->order('id_category')->useCache()->first());
    }

    public function test_select()
    {
        //$this->expectException(\Exception::class);
        //$this->pdoOne->open(true);
        try {
            $r = $this->pdoOne->runRawQuery('drop table product_category');
            self::assertEquals(true, $r, 'Drop failed');
        } catch (Exception $e) {
            $r = false;
            // drops silently
        }


        $sqlT2 = 'CREATE TABLE `product_category` (
	    `id_category` INT NOT NULL,
	    `catname` VARCHAR(45) NULL,
	    PRIMARY KEY (`id_category`));';

        try {
            $r = $this->pdoOne->runRawQuery($sqlT2);
        } catch (Exception $e) {
            echo $e->getMessage() . '<br>';
        }
        self::assertEquals(true, $r, 'failed to create table');
        $this->pdoOne->getCacheService()->cacheCounter = 0;

        self::assertGreaterThan(1, count($this->pdoOne->objectList()));
        // we add some values
        $this->pdoOne->set(['id_category' => 123, 'catname' => 'cheap'])->from('product_category')->insert();
        $this->pdoOne->insert('product_category', ['id_category', 'catname'],
            ['id_category' => 2, 'catname' => 'cheap']);

        $this->pdoOne->insert('product_category', ['id_category' => 3, 'catname' => 'cheap']);

        $this->pdoOne->insert('product_category', ['id_category' => 4, 'catname' => 'cheap4']);
        $this->pdoOne->insert('product_category', ['id_category', '5', 'catname', 'cheap']);
        $query1=$this->pdoOne->select('*')->from('product_category','travisdb')->where('1=1')->order('id_category');
        $r = $query1->toList();
        $query2=$this->pdoOne->select('*')->from('product_category')->where('catname=?',['cheap'])->order('id_category');
        $r2 = $query2->toList();

        //var_dump(var_export($r));
        self::assertEquals([
            0 =>
                [
                    'id_category' => 2,
                    'catname' => 'cheap',
                ],
            1 =>
                [
                    'id_category' => 3,
                    'catname' => 'cheap',
                ],
            2 =>
                [
                    'id_category' => 4,
                    'catname' => 'cheap4',
                ],
            3 =>
                [
                    'id_category' => 5,
                    'catname' => 'cheap',
                ],
            4 =>
                [
                    'id_category' => 123,
                    'catname' => 'cheap',
                ],
        ], $r);
        self::assertEquals([
            0 =>
                [
                    'id_category' => 2,
                    'catname' => 'cheap',
                ],
            1 =>
                [
                    'id_category' => 3,
                    'catname' => 'cheap',
                ],
            2 =>
                [
                    'id_category' => 5,
                    'catname' => 'cheap',
                ],
            3 =>
                [
                    'id_category' => 123,
                    'catname' => 'cheap',
                ],
        ], $r2);

        $query1=$this->pdoOne->select('*')->from('product_category')->where('1=1')->order('id_category')->useCache(10);
        $r = $query1->toList();
        $query2=$this->pdoOne->select('*')->from('product_category')->where('catname=?',['cheap'])->order('id_category')->useCache(10);
        $r2 = $query2->toList();
        $query3=$this->pdoOne->select('*')->from('product_category')->where('catname=?',['cheap4'])->order('id_category')->useCache(10);
        $r3 = $query3->toList();

        //var_dump(var_export($r));
        self::assertEquals([
            0 =>
                [
                    'id_category' => 2,
                    'catname' => 'cheap',
                ],
            1 =>
                [
                    'id_category' => 3,
                    'catname' => 'cheap',
                ],
            2 =>
                [
                    'id_category' => 4,
                    'catname' => 'cheap4',
                ],
            3 =>
                [
                    'id_category' => 5,
                    'catname' => 'cheap',
                ],
            4 =>
                [
                    'id_category' => 123,
                    'catname' => 'cheap',
                ],
        ], $r);
        self::assertEquals([
            0 =>
                [
                    'id_category' => 2,
                    'catname' => 'cheap',
                ],
            1 =>
                [
                    'id_category' => 3,
                    'catname' => 'cheap',
                ],
            2 =>
                [
                    'id_category' => 5,
                    'catname' => 'cheap',
                ],
            3 =>
                [
                    'id_category' => 123,
                    'catname' => 'cheap',
                ],
        ], $r2);
        self::assertEquals([
            0 =>
                [
                    'id_category' => 4,
                    'catname' => 'cheap4',
                ],
        ], $r3);
    }


    public function test_quota()
    {
        self::assertEquals('`hello` world', $this->pdoOne->addDelimiter('hello world'));
        self::assertEquals('`hello`.`world`', $this->pdoOne->addDelimiter('hello.world'));
        self::assertEquals('`hello`=value', $this->pdoOne->addDelimiter('hello=value'));
        self::assertEquals('`hello` =value', $this->pdoOne->addDelimiter('hello =value'));
        self::assertEquals('2019-01-01', PdoOne::dateConvert('01/01/2019', 'human', 'sql'));
        self::assertEquals('42143278901651563', $this->pdoOne->getUnpredictable('12345678901234561'));
        self::assertEquals('12345678901234561', $this->pdoOne->getUnpredictableInv('42143278901651563'));
        self::assertNotEmpty(PdoOne::dateTextNow()); // '2020-01-25T22:17:41Z',
        self::assertNotEmpty(PdoOne::dateSqlNow()); // '2020-01-25 22:18:32',
    }

    public function test_emptyargs()
    {
        $r = true;
        if ($this->pdoOne->objectExist('product_category')) {
            $r = $this->pdoOne->drop('product_category', 'table');
        }
        self::assertEquals(true, $r, 'Drop failed');

        if ($this->pdoOne->objectExist('category')) {
            $r = $this->pdoOne->drop('category', 'table');
        }

        $sqlT2 = 'CREATE TABLE `product_category` (`id_category` INT NOT NULL,`catname` 
                VARCHAR(45) NULL, PRIMARY KEY (`id_category`));';
        try {
            $r = $this->pdoOne->runRawQuery($sqlT2);
        } catch (Exception $e) {
            echo $e->getMessage() . '<br>';
        }
        $sqlT2 = 'CREATE TABLE `category` (`id_category` INT NOT NULL,`catname` 
                VARCHAR(45) NULL, PRIMARY KEY (`id_category`));';
        try {
            $r = $this->pdoOne->runRawQuery($sqlT2);
        } catch (Exception $e) {
            echo $e->getMessage() . '<br>';
        }
        self::assertEquals(true, $r, 'failed to create table');
        // we add some values
        $this->pdoOne->set(['id_category' => 1, 'catname' => 'cheap'])->from('product_category')->insert();
        $this->pdoOne->set(['id_category' => 2, 'catname' => 'cheap2'])->from('product_category')->insert();
        $this->pdoOne->set("id_category=2,catname='cheap1'")->where('id_category=2')->from('product_category')
            ->update();

        $sr = $this->pdoOne->from('product_category')->set("catname='expensive'")->where('id_category=1')->update();

        self::assertEquals(['id_category' => 1, 'catname' => 'expensive'],
            $this->pdoOne->select('select * from product_category where id_category=1')->first());
        self::assertEquals(['id_category' => 2, 'catname' => 'cheap1'],
            $this->pdoOne->select('select * from product_category where id_category=2')->first());

        $this->pdoOne->runMultipleRawQuery("insert into product_category(id_category,catname) values (3,'multi');
                insert into product_category(id_category,catname) values (4,'multi'); ");
        self::assertEquals(4, $this->pdoOne->from('product_category')->count());
        $r = $this->pdoOne->set(['id_category', 1, 'catname', 'c1'])->from('category')->insert();
        $obj = ['id_category' => 2, 'catname' => 'c2'];
        $r = $this->pdoOne->insertObject('category', $obj);

        $query = $this->pdoOne->select('*')->from('product_category')
            ->innerjoin('category on product_category.id_category=category.id_category')->toList();

        self::assertEquals([['id_category' => 1, 'catname' => 'c1'], ['id_category' => 2, 'catname' => 'c2']], $query);

        $this->pdoOne->delete('product_category where id_category>0');

        self::assertEquals(0, $this->pdoOne->from('product_category')->count());
    }

    public function test_time()
    {
        PdoOne::$dateTimeFormat = 'Y-m-d\TH:i:s\Z';
        PdoOne::$dateTimeMicroFormat = 'Y-m-d\TH:i:s.u\Z';
        PdoOne::$dateFormat = 'Y-m-d';
        self::assertEquals('2019-02-06 05:06:07', PdoOne::dateText2Sql('2019-02-06T05:06:07Z', true));
        self::assertEquals('2019-02-06 00:00:00', PdoOne::dateText2Sql('2019-02-06', false));

        self::assertEquals('2018-02-06 05:06:07.123000', PdoOne::dateText2Sql('2018-02-06T05:06:07.123Z', true));

        // sql format -> human format dd/mm/yyyy
        self::assertEquals('06/02/2019', PdoOne::dateSql2Text('2019-02-06'));

        // 2019-02-06T05:06:07Z -> 2019-02-06 05:06:07 -> 06/02/2019 05:06:07
        self::assertEquals('06/02/2019 05:06:07',
            PdoOne::dateSql2Text(PdoOne::dateText2Sql('2019-02-06T05:06:07Z', true)));
        self::assertEquals('06/02/2019 05:06:07', PdoOne::dateSql2Text('2019-02-06 05:06:07'));
        self::assertEquals('06/02/2018 05:06:07.123000', PdoOne::dateSql2Text('2018-02-06 05:06:07.123'));
    }

    /**
     * @throws Exception
     */
    public function test_sequence()
    {
        $this->pdoOne->tableSequence = 'testsequence';
        try {
            $this->pdoOne->createSequence();
        } catch (Exception $ex) {
            var_dump("it should show some errors:");
            var_dump($ex->getMessage());
            var_dump($this->pdoOne->lastError());
            var_dump($this->pdoOne->lastQuery);
        }

        self::assertLessThan(3639088446091303982, $this->pdoOne->getSequence(true),
            'sequence must be greater than 3639088446091303982');
    }

    /** @noinspection PhpUnitTestsInspection
     * @noinspection TypeUnsafeComparisonInspection
     */
    public function test_sequence2()
    {
        self::assertLessThan(3639088446091303982, $this->pdoOne->getSequencePHP(false),
            'sequence must be greater than 3639088446091303982');
        $s1 = $this->pdoOne->getSequencePHP(false);
        $s2 = $this->pdoOne->getSequencePHP(false);
        self::assertTrue($s1 != $s2, 'sequence must not be the same');
        $this->pdoOne->encryption->encPassword = 1020304050;
        $s1 = $this->pdoOne->getSequencePHP(true);
        $s2 = $this->pdoOne->getSequencePHP(true);
        self::assertTrue($s1 != $s2, 'sequence must not be the same');
    }

    /**
     * @doesNotPerformAssertions
     */
    public function test_close()
    {
        $this->pdoOne->close();
    }

    public function test_getMessages()
    {
        self::assertEquals(null, $this->pdoOne->getMessages(), 'this is not a message container');
    }


    public function test_startTransaction()
    {
        self::assertEquals(true, $this->pdoOne->startTransaction());
        $this->pdoOne->commit();
    }

    public function test_commit()
    {
        self::assertEquals(false, (false), 'transaction is not open');
    }

    public function test_rollback()
    {
        self::assertEquals(false, (false), 'transaction is not open');
    }


    public function test_selectDual()
    {
        self::assertInstanceOf(PdoOneQuery::class, $this->pdoOne->select('select 1 from DUAL'));
    }

    public function test_sqlGen()
    {
        self::assertEquals('select 1 from DUAL', $this->pdoOne->select('select 1 from DUAL')->sqlGen(true));

        self::assertEquals('select 1 from DUAL', $this->pdoOne->select('select 1')->from('DUAL')->sqlGen(true));

        self::assertEquals('select 1, 2 from DUAL',
            $this->pdoOne->select('1')->select('2')->from('DUAL')->sqlGen(true));

        self::assertEquals('select 1, 2 from DUAL', $this->pdoOne->select(['1', '2'])->from('DUAL')->sqlGen(true));

        self::assertEquals('select 1, 2 from DUAL where field=?',
            $this->pdoOne->select(['1', '2'])->from('DUAL')->where('field=?', [20])->sqlGen(true));

        self::assertEquals('select 1, 2 from DUAL where field=:field',
            $this->pdoOne->select(['1', '2'])->from('DUAL')->where('field=:field', [':field' => 20])->sqlGen(true));

        /** @noinspection SqlAggregates */
        self::assertEquals('select 1, 2 from DUAL where field=? group by 2 having field2=? order by 1',
            $this->pdoOne->select(['1', '2'])->from('DUAL')->where('field=?', [20])->order('1')->group('2')
                ->having('field2=?', [4])->sqlGen(true));
    }

    public function test_join()
    {
        self::assertInstanceOf(PdoOneQuery::class, $this->pdoOne->join('tablejoin on t1.field=t2.field'));
    }


    public function test_from()
    {
        self::assertInstanceOf(PdoOneQuery::class, $this->pdoOne->from('table t1'));
    }

    public function test_left()
    {
        self::assertInstanceOf(PdoOneQuery::class, $this->pdoOne->left('table2 on table1.t1=table2.t2'));
    }

    public function test_right()
    {
        self::assertInstanceOf(PdoOneQuery::class, $this->pdoOne->right('table2 on table1.t1=table2.t2'));
    }

    public function test_where()
    {
        self::assertInstanceOf(PdoOneQuery::class, $this->pdoOne->where('field1=?,field2=?', [20, 'hello']));
    }

    public function test_set()
    {
        self::assertInstanceOf(PdoOneQuery::class, $this->pdoOne->set('field1=?,field2=?', [20, 'hello']));
    }

    public function test_group()
    {
        self::assertInstanceOf(PdoOneQuery::class, $this->pdoOne->group('fieldgroup'));
    }

    public function test_having()
    {
        self::assertInstanceOf(PdoOneQuery::class, $this->pdoOne->having('field1=?,field2=?', [20, 'hello']));
    }

    public function test_order()
    {
        self::assertInstanceOf(PdoOneQuery::class, $this->pdoOne->order('name desc'));
    }

    public function test_limit()
    {
        self::assertInstanceOf(PdoOneQuery::class, $this->pdoOne->limit('1,10'));
    }

    public function test_distinct()
    {
        self::assertInstanceOf(PdoOneQuery::class, $this->pdoOne->distinct());
    }


    public function test_runQuery()
    {
        self::assertEquals(true, $this->pdoOne->runQuery($this->pdoOne->prepare('select 1 from dual')));
        self::assertEquals([1 => 1], $this->pdoOne->select('1')->from('dual')->first(), 'it must runs');
    }


    public function test_runRawQuery()
    {
        self::assertEquals([0 => [1 => 1]], $this->pdoOne->runRawQuery('select 1', null, true));
    }

    /**
     * @throws Exception
     * @noinspection PhpUnitTestsInspection
     * @noinspection TypeUnsafeComparisonInspection
     */
    public function test_setEncryption()
    {
        $this->pdoOne->setEncryption('123//*/*saass11___1212fgbl@#€€"', '123//*/*saass11___1212fgbl@#€€"',
            'AES-256-CTR');
        self::assertEquals(["hello"],$this->pdoOne->decrypt($this->pdoOne->encrypt(["hello"])));
        $value = $this->pdoOne->encrypt("bv `lfg+hlc ,vc´,c35'ddl ld_vcvñvc +*=/\\");
        self::assertTrue(strlen($value) > 10, 'Encrypted');
        $return = $this->pdoOne->decrypt($value);
        self::assertEquals("bv `lfg+hlc ,vc´,c35'ddl ld_vcvñvc +*=/\\", $return, 'decrypt correct');

        $return = $this->pdoOne->decrypt('wrong' . $value);
        self::assertEquals(false, $return, 'decrypt must fail');
        $return = $this->pdoOne->decrypt('');
        self::assertEquals(false, $return, 'decrypt must fail');
        $return = $this->pdoOne->decrypt(null);
        self::assertEquals(false, $return, 'decrypt must fail');
        // iv =true
        $value1 = $this->pdoOne->encrypt('abc');
        $value2 = $this->pdoOne->encrypt('abc');
        self::assertTrue($value1 != $value2, 'Values must be different');
        // iv =true
        $this->pdoOne->encryption->iv = false;
        $value1 = $this->pdoOne->encrypt('abc_ABC/abc*abc1234567890[]{[');
        $value2 = $this->pdoOne->encrypt('abc_ABC/abc*abc1234567890[]{[');
        self::assertTrue($value1 == $value2, 'Values must be equals');
    }

    /**
     * @throws Exception
     */
    public function test_setEncryptionINTEGER()
    {
        $this->pdoOne->setEncryption(12345678, '', 'INTEGER');
        // 2147483640
        $original = 2147483640;
        $value = $this->pdoOne->encrypt($original);
        self::assertTrue(strlen($value) > 3, 'Encrypted');
        self::assertEquals($original, $this->pdoOne->decrypt($value), 'decrypt correct');
        // 1
        $original = 1;
        $value = $this->pdoOne->encrypt($original);
        self::assertTrue(strlen($value) > 3, 'Encrypted');
        self::assertEquals($original, $this->pdoOne->decrypt($value), 'decrypt correct');
        // 0
        $original = 0;
        $value = $this->pdoOne->encrypt($original);
        self::assertTrue(strlen($value) > 3, 'Encrypted');
        self::assertEquals($original, $this->pdoOne->decrypt($value), 'decrypt correct');
    }

    /**
     * @throws Exception
     */
    public function test_setEncryptionSIMPLE()
    {
        $this->pdoOne->setEncryption('Zoamelgusta', '', 'SIMPLE');
        // 2147483640
        $original = 'abc';
        $value = $this->pdoOne->encrypt($original);
        self::assertEquals('wrzS', $value, 'encrypt with problems');
        self::assertEquals($original, $this->pdoOne->decrypt($value), 'decrypt with problems');
        $original = 'Mary had a little lamb. Whose fleece was white as snow';
        $value = $this->pdoOne->encrypt($original);
        self::assertEquals('rrvh2o3NzcuV1JTNw-PV2cqM09bg1o96xsnc2NGH29_Zxr3UgeTG34fs293Vv4_C4IXf1eTq', $value,
            'encrypt with problems');
        self::assertEquals($original, $this->pdoOne->decrypt($value), 'decrypt with problems');
        // 1
        $original = 1222;
        $value = $this->pdoOne->encrypt($original);
        self::assertEquals('koyhkw==', $value, 'encrypt with problems');
        self::assertEquals($original, $this->pdoOne->decrypt($value), 'decrypt correct');
        // 0
        $original = 0;
        $value = $this->pdoOne->encrypt($original);
        self::assertEquals('kQ==', $value, 'encrypt with problems');
        self::assertEquals($original, $this->pdoOne->decrypt($value), 'decrypt correct');
    }
}
