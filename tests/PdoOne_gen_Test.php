<?php



namespace eftec\tests;

use actorRepo;
use eftec\_BasePdoOneRepo;
use eftec\PdoOne;
use eftec\tests\CacheServicesmysql;
use PHPUnit\Framework\TestCase;

include "ActorRepo.php";

class PdoOne_gen_Test extends TestCase
{
    /** @var PdoOne */
    protected $pdoOne;

    public function setUp() {
        $this->pdoOne = new PdoOne("mysql", "127.0.0.1", "travis", "", "travisdb");
        $this->pdoOne->connect();
        $this->pdoOne->logLevel = 3;

        ActorRepo::setPdoOne($this->pdoOne);
    }

    /**
     * @throws \Exception
     */
    public function test() {
        
        ActorRepo::createTable();
        ActorRepo::truncate();
        ActorRepo::insert(['first_name'=>'xxx','last_name'=>'yyy']);
        ActorRepo::insert(['first_name'=>'yyy','last_name'=>'yyy']);
        ActorRepo::insert(['first_name'=>'zzz','last_name'=>'yyy']);
        $this->assertEquals(3,count(ActorRepo::toList()));
        ActorRepo::where("first_name like ?",['s','%x']);
        $rows=(ActorRepo::order('first_name'))::toList();
        //ActorRepo::findAll();
        $this->assertEquals("select * from actor where first_name like ? order by first_name",$this->pdoOne->lastQuery);
        $this->assertEquals(1,count($rows));
        
        ActorRepo::order('first_name desc');
        $rows=ActorRepo::toList();
        $this->assertEquals('zzz',$rows[0]['first_name']);
    }
}