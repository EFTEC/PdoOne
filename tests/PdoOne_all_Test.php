<?php

use eftec\PdoOne;
use eftec\tests\CacheServicesmysql;
use PHPUnit\Framework\TestCase;

class PdoOne_mysql_Test extends TestCase
{
    /** @var PdoOne */
    protected $pdoOne;

    public function setUp() {
        $this->pdoOne = new PdoOne("mysql", "127.0.0.1", "travis", "", "travisdb");
        $this->pdoOne->connect();
        $this->pdoOne->logLevel = 3;

    }
    public function test_missingerr() {

        try {
            $this->pdoOne->select('*')->from('missintable')->toList();
        } catch (Exception $e) {
            $this->assertContains('Failed to run query',$this->pdoOne->errorText);
            $this->assertEquals('select * from missintable',$this->pdoOne->lastQuery);
            try {
                $this->pdoOne->toList();
            } catch (Exception $e) {
                // stack was deleted so the columns and table are not keeped
                $this->assertEquals('select  from ',$this->pdoOne->lastQuery);
            
            }
        }
        try {
            $this->pdoOne->select('*')->from('missintable')->setNoReset(true)->toList();
        } catch (Exception $e) {
            $this->assertContains('Failed to run query',$this->pdoOne->errorText);
            $this->assertEquals('select * from missintable',$this->pdoOne->lastQuery);
            try {
                $this->pdoOne->toList();
            } catch (Exception $e) {
                // stack is not deleted (even on error so the columns and table are not keeped
                $this->assertEquals('select * from missintable',$this->pdoOne->lastQuery);
                $this->pdoOne->builderReset(true); // reset the stack
                try {
                    $this->pdoOne->toList();
                } catch (Exception $e) {
                    // stack was reset (manually) so the columns and table are not keeped
                    $this->assertEquals('select  from ',$this->pdoOne->lastQuery);

                }

            }
        }
    }

    public function test_1()
    {
        $this->pdoOne->render();
        $a1=1;
        $this->assertEquals(1,$a1);
 
    }

    public function test_2()
    {
        $a1=1;
        $this->pdoOne->cliEngine();
        $this->assertEquals(1,$a1);
        
        
        
    }

    
    public function test_3() {
        
        $this->assertEquals('2020-01-30',$this->pdoOne->dateConvert('30/01/2020','human','sql'));
        $this->assertEquals('2020-01-30',$this->pdoOne->dateConvert('30/01/2020','human','iso'));
        $this->assertEquals(new DateTime('01/30/2020 00:00:00'),$this->pdoOne->dateConvert('30/01/2020','human','class'));
        $this->assertEquals('30/01/2020',$this->pdoOne->dateConvert('2020-01-30','sql','human'));
    }
    public function test_4() {
        $this->assertGreaterThan(0,count($this->pdoOne->tableSorted()));
    }
}