<?php

namespace eftec\tests;
use eftec\PdoOne;
use PHPUnit\Framework\TestCase;

class PdoOne_Generic_Test extends TestCase
{
    /** @var PdoOne */
    public $pdoOne;
    public function setUp():void {
        $this->pdoOne = new PdoOne('mysql', '127.0.0.1', 'travis', '', 'travisdb');
        $this->pdoOne->connect();
        $this->pdoOne->logLevel = 3;
        if (!$this->pdoOne->tableExist('city')) {
            $this->pdoOne->createTable('city', ['id' => 'int not null AUTO_INCREMENT', 'name' => 'varchar(45)'], ['id' => 'PRIMARY KEY']);
        }
    }
    public function test1():void
    {
        /** @noinspection UnnecessaryAssertionInspection */
        $this->assertInstanceOf(PdoOne::class,PdoOne::factoryFromArray(['mysql', '127.0.0.1', 'travis', '', 'travisdb']));
        $this->assertStringContainsString('1970',PdoOne::unixtime2Sql(50000));
        $this->assertEquals('a(b)',PdoOne::replaceBetween('a(1,2,3)','(',')','b'));
        $this->assertEquals('a(b)',PdoOne::replaceBetween('a(1,2,3)','(',')','b'));
        $this->assertEquals('Abc',PdoOne::tableCase('_abc'));
        $this->assertEquals('Abc',PdoOne::tableCase('abc'));
        //$this->assertEquals('Abc',PdoOne::('abc'));
        $this->assertEquals('AbcDef',PdoOne::tableCase('abc_def'));
        $this->assertEquals('AbcDef',PdoOne::tableCase('abc_defes'));
        $def=$this->pdoOne->getDefTable('city');
        $defkeys=$this->pdoOne->getDefTableKeys('city');
        $defFK=$this->pdoOne->getDefTableFK('city');
        $this->assertEquals([],$this->pdoOne->validateDefTable('city',$def,$defkeys,$defFK));

    }
}
