<?php

use eftec\_BasePdoOneRepo;
use eftec\PdoOne;
use PHPUnit\Framework\TestCase;

class PdoOne_mysql_Test extends TestCase
{
    /** @var PdoOne */
    protected $pdoOne;

    public function setUp()
    {
        $this->pdoOne = new PdoOne("mysql", "127.0.0.1", "travis", "", "travisdb");
        $this->pdoOne->connect();
        $this->pdoOne->logLevel = 3;
    }

    public function test_gen()
    {
        $this->pdoOne->builderReset();
        self::assertEquals('select * from table where a1=:a1 and a2=:a2', $this->pdoOne->select('*')
            ->from('table')->where(['a1' => 123, 'a2' => 456])->sqlGen());
        self::assertEquals([0 => [':a1', 123, 1, null], 1 => [':a2', 456, 1, null]], $this->pdoOne->getWhereParamAssoc());

        $this->pdoOne->builderReset();
        self::assertEquals('select * from table where fn(a3)=:a3 and a2=:a2', $this->pdoOne->select('*')
            ->from('table')->where(['fn(a3)=:a3' => 1234, 'a2' => 456])->sqlGen());
        self::assertEquals([0 => [':a3', 1234, 1, null], 1 => [':a2', 456, 1, null]], $this->pdoOne->getWhereParamAssoc());

        $this->pdoOne->builderReset();
        self::assertEquals('select * from table where a1=? and a2=?', $this->pdoOne->select('*')
            ->from('table')->where(['a1', 123, 'a2', 456])->sqlGen());
        self::assertEquals([0 => [1, 123, 1, null], 1 => [2, 456, 1, null]], $this->pdoOne->getWhereParamAssoc());

    }
    public function test_dateconvert() {


        $ms=false;
        $time=false;
        $r=PdoOne::dateConvertInput('01/12/2020','human',$ms,$time);
        self::assertEquals('2020-12-01 00:00:00',$r->format('Y-m-d H:i:s'));
        $ms=false;
        $time=false;
        $r=PdoOne::dateConvertInput('2020-12-01','iso',$ms,$time);
        self::assertEquals('2020-12-01 00:00:00',$r->format('Y-m-d H:i:s'));
        $ms=false;
        $time=false;
        $r=PdoOne::dateConvertInput('2020-12-01','sql',$ms,$time);
        self::assertEquals('2020-12-01 00:00:00',$r->format('Y-m-d H:i:s'));
        $ms=false;
        $time=false;
        $r=PdoOne::dateConvertInput(50000,'timestamp',$ms,$time);
        self::assertEquals('1970',$r->format('Y'));
        $ms=false;
        $time=false;
        $now=new DateTime('now');
        $r=PdoOne::dateConvertInput($now,'class',$ms,$time);
        self::assertEquals($now->format('Y-m-d H:i:s'),$r->format('Y-m-d H:i:s'));
    }

    /**
     * @throws RuntimeException
     */
    public function test_parameter()
    {

        $this->pdoOne->builderReset();
        self::assertEquals([
            ['name=:name and type<:type'],
            [[':name', 'Coca-Cola', PDO::PARAM_STR, null], [':type', 987, PDO::PARAM_INT, null]]
        ], $this->pdoOne->constructParam2('name=:name and type<:type', [':name' => 'Coca-Cola', ':type' => 987],
            'where', true));

        $this->pdoOne->builderReset();
        self::assertEquals([
            ['name=? and type<?'],
            [[1, 'Coca-Cola', PDO::PARAM_STR, null], [2, 987, PDO::PARAM_INT, null]]
        ], $this->pdoOne->constructParam2('name=? and type<?', ['Coca-Cola', 987], 'where', true));

        $this->pdoOne->builderReset();
        self::assertEquals([
            ['name=:name', 'type=:type'],
            [[':name', 'Coca-Cola', PDO::PARAM_STR, null], [':type', 987, PDO::PARAM_INT, null]]
        ], $this->pdoOne->constructParam2(['name', 'type'], [':name' => 'Coca-Cola', ':type' => 987], 'where', true));

        $this->pdoOne->builderReset();
        self::assertEquals([
            ['name=?', 'type<?'],
            [[1, 'Coca-Cola', PDO::PARAM_STR, null], [2, 987, PDO::PARAM_INT, null]]
        ], $this->pdoOne->constructParam2(['name=?' => 'Coca-Cola', 'type<?' => 987], null, 'where', true));

        $this->pdoOne->builderReset();
        self::assertEquals([
            ['name=?', 'type<?'],
            [[1, 'Coca-Cola', PDO::PARAM_STR, null], [2, 987, PDO::PARAM_INT, null]]
        ], $this->pdoOne->constructParam2(['name=?', 'Coca-Cola', 'type<?', 987], null, 'where', true));

        $this->pdoOne->builderReset();
        self::assertEquals([['aa=bbb'], []], $this->pdoOne->constructParam2('aa=bbb', PdoOne::NULL, 'where', true));

        $this->pdoOne->builderReset();
        self::assertEquals([
            ['name=:name', 'type=:type'],
            [
                [':name', 'Coca-Cola', PDO::PARAM_STR, null],
                [':type', 987, PDO::PARAM_INT, null]
            ]
        ], $this->pdoOne->constructParam2(['name' => 'Coca-Cola', 'type' => 987], null, 'where', true));

        $this->pdoOne->builderReset();
    }

    public function test_Time()
    {
        self::assertNotEquals(null, PdoOne::dateNow());
    }

    public function test_dml()
    {
        if ($this->pdoOne->tableExist('tdummy')) {
            $this->pdoOne->dropTable('tdummy');
        }
        $this->pdoOne->createTable('tdummy', ['c1' => 'int', 'c2' => 'varchar(50)'], 'c1');

        self::assertNotEquals(false, $this->pdoOne->insert('tdummy', ['c1', 'c2'], [1, 'hello']));
        self::assertNotEquals(false, $this->pdoOne->insert('tdummy', ['c1', 'c2'], [2, 'hello2']));

        var_dump($this->pdoOne->select('*')->from('tdummy')->first());

        self::assertNotEquals(false, $this->pdoOne->update('tdummy', ['c2'], ['hellox'], ['c1'], [1]));
        self::assertNotEquals(false, $this->pdoOne->update('tdummy', ['c2'], ['hellox'], ['c1'], [2]));

        self::assertNotEquals(false, $this->pdoOne->delete('tdummy', ['c1'], [2]));
        self::assertEquals([['c1' => 1, 'c2' => 'hellox']], $this->pdoOne->select('*')->from('tdummy')->toList());
    }

    public function test_f1()
    {
        if ($this->pdoOne->tableExist('tdummy')) {
            try {
                $this->pdoOne->dropTable('tdummy');
                $this->pdoOne->dropTable('tdummy2');
            } catch (Exception $e) {
            }
        }
        $this->pdoOne->createTable('tdummy', ['c1' => 'int', 'c2' => 'varchar(50)', 'c3' => 'int'], 'c1');
        $this->pdoOne->createTable('tdummy2', ['c1' => 'int', 'c2' => 'varchar(50)', 'c3' => 'int'], 'c1');

        self::assertNotEquals(false, $this->pdoOne->insert('tdummy2', ['c1', 'c2'], [1, 'DUMMY2.1']));
        self::assertNotEquals(false, $this->pdoOne->insert('tdummy2', ['c1', 'c2'], [2, 'DUMMY2.2']));

        self::assertNotEquals(false, $this->pdoOne->insert('tdummy', ['c1', 'c2', 'c3'], [1, 'DUMMY1.1', 1]));
        self::assertNotEquals(false, $this->pdoOne->insert('tdummy', ['c1', 'c2', 'c3'], [2, 'DUMMY1.2', 2]));

        $r = $this->pdoOne->select('tdummy.c1,tdummy.c2,tdummy.c3')
            ->from('tdummy')
            ->join('tdummy2 on tdummy.c3=tdummy2.c1')
            ->toList();

        self::assertEquals([
            [
                'c1' => 1,
                'c2' => 'DUMMY1.1',
                'c3' => 1
            ],
            [
                'c1' => 2,
                'c2' => 'DUMMY1.2',
                'c3' => 2
            ],
        ], $r);

        $result = [
            [
                'c1' => 1,
                'c2' => 'DUMMY1.1',
                'c3' => 1
            ]
        ];

        $r = $this->pdoOne->select('tdummy.c1,tdummy.c2,tdummy.c3')
            ->from('tdummy')
            ->join('tdummy2 on tdummy.c3=tdummy2.c1')
            ->where(['tdummy.c1' => 1])
            ->toList();

        self::assertEquals($result, $r);

        $r = $this->pdoOne->select('tdummy.c1,tdummy.c2,tdummy.c3')
            ->from('tdummy')
            ->join('tdummy2 on tdummy.c3=tdummy2.c1')
            ->where(['c1' => 1], PdoOne::NULL, false, 'tdummy')
            ->toList();

        self::assertEquals($result, $r);

        $r = $this->pdoOne->select('tdummy.c1,tdummy.c2,tdummy.c3')
            ->from('tdummy')
            ->join('tdummy2 on tdummy.c3=tdummy2.c1')
            ->where(['tdummy.c1' => 1], PdoOne::NULL, false, 'tdummy')
            ->toList();

        self::assertEquals($result, $r);

        $r = $this->pdoOne->select('tdummy.c1,tdummy.c2,tdummy.c3')
            ->from('tdummy')
            ->join('tdummy2 on tdummy.c3=tdummy2.c1')
            ->where(['tdummy.c1=:c1' => 1])
            ->toList();

        self::assertEquals($result, $r);

        self::assertEquals("select tdummy.c1,tdummy.c2,tdummy.c3 from tdummy " .
            "inner join tdummy2 on tdummy.c3=tdummy2.c1  where tdummy.c1=:c1"
            , $this->pdoOne->lastQuery);

    }

    public function test_fail()
    {
        $this->pdoOne->throwOnError = true;

        if ($this->pdoOne->tableExist('tdummy')) {
            try {
                $this->pdoOne->dropTable('tdummy');
                $this->pdoOne->dropTable('tdummy2');
            } catch (Exception $e) {
            }
        }
        $this->pdoOne->createTable('tdummy', ['c1' => 'int', 'c2' => 'varchar(50)', 'c3' => 'int'], 'c1');

        try {
            $this->expectException($this->pdoOne->set(['x1' => 1, 'x2' => 2])->insert('WRONGTABLE'));
        } catch (Exception $e) {
            // this error is expected. However, we should flush the fields.
        }
        self::assertNotFalse($this->pdoOne->set(['c1' => 1, 'c2' => 2, 'c3' => 3])->insert('tdummy'));
        try {
            $this->expectException($this->pdoOne->set(['x1' => 1, 'x2' => 2])->where(['x4' => 1])->update('WRONGTABLE'));
        } catch (Exception $e) {
            // this error is expected. However, we should flush the fields.
        }
        self::assertNotFalse($this->pdoOne->set(['c2' => 2, 'c3' => 3])->where(['c1' => 1])->update('tdummy'));


    }

    /**
     * @throws Exception
     */
    public function test_raw_and_internal_cache()
    {
        if ($this->pdoOne->tableExist('tdummy')) {
            $this->pdoOne->dropTable('tdummy');
        }
        $this->pdoOne->createTable('tdummy', ['c1' => 'int', 'c2' => 'varchar(50)'], 'c1');
        self::assertNotEquals(false,
            $this->pdoOne->runRawQuery('insert into tdummy(c1,c2) values (:c1 , :c2)', [':c1' => 1, ':c2' => 'hello'],
                true));
        self::assertNotEquals(false,
            $this->pdoOne->runRawQuery('insert into tdummy(c1,c2) values (:c1 , :c2)', [':c1' => 2, ':c2' => 'hello2'],
                true));

        self::assertEquals(['c1' => 1, 'c2' => 'hello'], $this->pdoOne->select('*')->from('tdummy')->first());

        $this->pdoOne->setUseInternalCache(true);
        //$this->pdoOne->flushInternalCache(true);
        self::assertEquals(['c1' => 1, 'c2' => 'hello'], $this->pdoOne->select('*')->from('tdummy')->first());
        self::assertEquals(['c1' => 1, 'c2' => 'hello'], $this->pdoOne->select('*')->from('tdummy')->first());
        self::assertEquals(['c1' => 1, 'c2' => 'hello'], $this->pdoOne->select('*')->from('tdummy')->first());
        self::assertEquals(2, $this->pdoOne->internalCacheCounter);
        $this->pdoOne->flushInternalCache();

        $this->pdoOne->setUseInternalCache(true);
        //$this->pdoOne->flushInternalCache(true);
        $result = [['c1' => 1, 'c2' => 'hello'], ['c1' => 2, 'c2' => 'hello2']];
        self::assertEquals($result, $this->pdoOne->select('*')->from('tdummy')->toList());
        self::assertEquals($result, $this->pdoOne->select('*')->from('tdummy')->toList());
        self::assertEquals($result, $this->pdoOne->select('*')->from('tdummy')->toList());
        self::assertEquals(2, $this->pdoOne->internalCacheCounter);
        $this->pdoOne->setUseInternalCache(false);

        $this->pdoOne->flushInternalCache();
        $this->pdoOne->setUseInternalCache(true);
        //$this->pdoOne->flushInternalCache(true);
        $result = [['c1' => 1, 'c2' => 'hello'], ['c1' => 2, 'c2' => 'hello2']];
        self::assertEquals($result, $this->pdoOne->select('*')->from('tdummy')->where(['c1>?' => 0])->toList());
        self::assertEquals($result, $this->pdoOne->select('*')->from('tdummy')->where(['c1>?' => 0])->toList());
        self::assertEquals($result, $this->pdoOne->select('*')->from('tdummy')->where(['c1>?' => 0])->toList());
        self::assertEquals(2, $this->pdoOne->internalCacheCounter);
        $this->pdoOne->setUseInternalCache(false);
    }

    /**
     * @throws Exception
     */
    public function test_dml2()
    {
        if ($this->pdoOne->tableExist('tdummy')) {
            $this->pdoOne->dropTable('tdummy');
        }
        $this->pdoOne->createTable('tdummy', ['c1' => 'int', 'c2' => 'varchar(50)'], 'c1');

        self::assertNotEquals(false, $this->pdoOne->set(['c1', 'c2'], [1, 'hello'])->from('tdummy')->insert());
        self::assertNotEquals(false, $this->pdoOne->insert('tdummy', ['c1', 'c2'], ['c1' => 2, 'c2' => 'hello2']));

        self::assertEquals(['c1' => 1, 'c2' => 'hello'], $this->pdoOne->select('*')->from('tdummy')->first());

        self::assertEquals([['c1' => 1, 'count' => 1], ['c1' => 2, 'count' => 1]],
            $this->pdoOne->select('c1,count(*) count')->from('tdummy')->group('c1')->having('count(*)>?', [0])
                ->toList());
        self::assertEquals('select c1,count(*) count from tdummy group by c1 having count(*)>?',
            $this->pdoOne->lastQuery);

        self::assertNotEquals(false,
            $this->pdoOne->set(['c2'], ['hellox1'])->where(['c1'], [1])->from('tdummy')->update());
        self::assertNotEquals(false,
            $this->pdoOne->set(['c2'], ['hellox2'])->where(['c1'], [2])->from('tdummy')->update());

        self::assertNotEquals(false, $this->pdoOne->where(['c1'], [2])->from('tdummy')->delete());
        self::assertEquals([['c1' => 1, 'c2' => 'hellox1']], $this->pdoOne->select('*')->from('tdummy')->toList());
    }

    public function test_missingerr()
    {
        try {
            $this->pdoOne->select('*')->from('missintable')->toList();
        } catch (Exception $e) {
            self::assertContains('Failed to prepare', $this->pdoOne->errorText);
            self::assertEquals('select * from missintable', $this->pdoOne->lastQuery);
            try {
                $this->pdoOne->toList();
            } catch (Exception $e) {
                // stack was deleted so the columns and table are not keeped
                self::assertEquals('select  from ', $this->pdoOne->lastQuery);
            }
        }
        try {
            $this->pdoOne->select('*')->from('missintable')->setNoReset(true)->toList();
        } catch (Exception $e) {
            self::assertContains('Failed to prepare', $this->pdoOne->errorText);
            self::assertEquals('select * from missintable', $this->pdoOne->lastQuery);
            self::assertFalse($this->pdoOne->hasWhere());
        }
    }

    public function test_1()
    {
        $this->pdoOne->render();
        $a1 = 1;
        self::assertEquals(1, $a1);
    }

    public function test_2()
    {
        $a1 = 1;
        $this->pdoOne->cliEngine();
        self::assertEquals(1, $a1);
    }

    public function test_base()
    {
        $array1 = ["a" => 1, "b" => 2, "c" => 3];
        $array2 = ["a", "b"];
        $array2As = ["a" => 222, "b" => 333];
        $array3 = ["a", "b", 'd'];

        self::assertEquals(["a" => 1, "b" => 2], _BasePdoOneRepo::intersectArrays($array1, $array2));
        self::assertEquals(["c" => 3], _BasePdoOneRepo::diffArrays($array1, $array2));

        self::assertEquals(["a" => 1, "b" => 2], _BasePdoOneRepo::intersectArrays($array1, $array2As, true));
        self::assertEquals(["c" => 3], _BasePdoOneRepo::diffArrays($array1, $array2As, true));

        self::assertEquals(["a" => 1, "b" => 2, "d" => null],
            _BasePdoOneRepo::intersectArrays($array1, $array3, false));
        self::assertEquals(["c" => 3], _BasePdoOneRepo::diffArrays($array1, $array3, false));



    }

    public function test_3()
    {
        $dt = new DateTime('18-07-2020');
        $cv = PdoOne::dateConvert('2020-07-18 00:00:00.000', 'sql', 'class');
        var_dump($cv);
        self::assertEquals($dt, $cv);
        self::assertEquals('2020-01-30', PdoOne::dateConvert('30/01/2020', 'human', 'sql'));
        self::assertEquals('2020-01-30', PdoOne::dateConvert('30/01/2020', 'human', 'iso'));
        self::assertEquals(new DateTime('01/30/2020 00:00:00'), PdoOne::dateConvert('30/01/2020', 'human', 'class'));
        self::assertEquals('30/01/2020', PdoOne::dateConvert('2020-01-30', 'sql', 'human'));
    }

    public function test_4()
    {
        self::assertGreaterThan(0, count($this->pdoOne->tableSorted()));
    }
}