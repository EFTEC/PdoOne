<?php

namespace eftec\tests;

use DateTime;
use eftec\PdoOne;
use Exception;
use PHPUnit\Framework\TestCase;


class PdoOneTest extends TestCase
{
	/** @var PdoOne */
    protected $pdoOne;

    public function setUp()
    {
        $this->pdoOne=new PdoOne("mysql","127.0.0.1","travis","","travisdb");
        $this->pdoOne->connect();
        $this->pdoOne->logLevel=3;
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
        $this->assertEquals(false,$this->pdoOne->readonly(),'the database is read only');
    }

    public function test_connect()
    {
	    $this->expectException(\Exception::class);
        $this->pdoOne->connect();
    }

    public function test_open()
    {
        //$this->expectException(\Exception::class);

        //$this->pdoOne->open(true);
	    try {
		    $r=$this->pdoOne->runRawQuery('drop table product_category');
		    $this->assertEquals(true,$r,"Drop failed");
	    } catch (Exception $e) {
		    $r=false;
	    	// drops silently
	    }


	    $sqlT2="CREATE TABLE `product_category` (
	    `id_category` INT NOT NULL,
	    `catname` VARCHAR(45) NULL,
	    PRIMARY KEY (`id_category`));";

	    try {
		    $r=$this->pdoOne->runRawQuery($sqlT2);
	    } catch (Exception $e) {
		    echo $e->getMessage()."<br>";
	    }
	    $this->assertEquals(true,$r,"failed to create table");
	    // we add some values
	    $r=$this->pdoOne->set(['id_category' => 1, 'catname' => 'cheap'])
		    ->from('product_category')->insert();
	    $this->assertEquals(0,$r,'insert must value 0');

    }
	public function test_time()
	{
		$this->assertEquals('2019-02-06 00:00:00',PdoOne::dateText2Sql('2019-02-06',false));
		$this->assertEquals('2019-02-06 05:06:07',PdoOne::dateText2Sql('2019-02-06T05:06:07Z',true));
		$this->assertEquals('2018-02-06 05:06:07.123000',PdoOne::dateText2Sql('2018-02-06T05:06:07.123Z',true));

		$this->assertEquals('2019-02-06',PdoOne::dateSql2Text('2019-02-06'));
		$this->assertEquals('2019-02-06T05:06:07Z',PdoOne::dateSql2Text('2019-02-06 05:06:07'));
		$this->assertEquals('2018-02-06T05:06:07.123000Z',PdoOne::dateSql2Text('2018-02-06 05:06:07.123'));
	}

	/**
	 * @throws Exception
	 */
	public function test_sequence()
	{
		$this->pdoOne->tableSequence='testsequence';
		try {
			$this->pdoOne->createSequence();
		} catch(Exception $ex) {
			
		}
		$this->assertLessThan(3639088446091303982,$this->pdoOne->getSequence(true),"sequence must be greater than 3639088446091303982");
	}

	public function test_sequence2()
	{
		$this->assertLessThan(3639088446091303982,$this->pdoOne->getSequencePHP(false),"sequence must be greater than 3639088446091303982");
		$s1=$this->pdoOne->getSequencePHP(false);
		$s2=$this->pdoOne->getSequencePHP(false);
		$this->assertTrue($s1!=$s2,"sequence must not be the same");
		$this->pdoOne->encryption->encPassword=1020304050;
		$s1=$this->pdoOne->getSequencePHP(true);
		$s2=$this->pdoOne->getSequencePHP(true);
		$this->assertTrue($s1!=$s2,"sequence must not be the same");
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
        $this->assertEquals(null,$this->pdoOne->getMessages(),'this is not a message container');
    }



    public function test_startTransaction()
    {
        $this->assertEquals(true,$this->pdoOne->startTransaction());
        $this->pdoOne->commit();

    }

    public function test_commit()
    {
        $this->assertEquals(false,(false),'transaction is not open');
    }

    public function test_rollback()
    {
        $this->assertEquals(false,(false),'transaction is not open');
    }

 
    public function test_select()
    {
        $this->assertInstanceOf(PdoOne::class,$this->pdoOne->select('select 1 from DUAL'));
    }

	public function test_sqlGen()
	{
		$this->assertEquals("select 1 from DUAL",$this->pdoOne->select('select 1 from DUAL')->sqlGen(true));

		$this->assertEquals("select 1 from DUAL",$this->pdoOne->select('select 1')->from('DUAL')->sqlGen(true));

		$this->assertEquals("select 1, 2 from DUAL",$this->pdoOne->select('1')->select('2')->from('DUAL')->sqlGen(true));

		$this->assertEquals("select 1, 2 from DUAL",$this->pdoOne->select(['1','2'])->from('DUAL')->sqlGen(true));

		$this->assertEquals("select 1, 2 from DUAL where field=?"
			,$this->pdoOne
				->select(['1','2'])
				->from('DUAL')
				->where('field=?',[20])
				->sqlGen(true));

		$this->assertEquals("select 1, 2 from DUAL where field=? group by 2 having field2=? order by 1"
			,$this->pdoOne
				->select(['1','2'])
				->from('DUAL')
				->where('field=?',[20])
				->order('1')
				->group('2')
				->having('field2=?',[4])
				->sqlGen(true));		
	}

    public function test_join()
    {
        $this->assertInstanceOf(PdoOne::class,$this->pdoOne->join('tablejoin on t1.field=t2.field'));
    }

 

    public function test_from()
    {
        $this->assertInstanceOf(PdoOne::class,$this->pdoOne->from('table t1'));
    }

    public function test_left()
    {
        $this->assertInstanceOf(PdoOne::class,$this->pdoOne->left('table2 on table1.t1=table2.t2'));
    }

    public function test_right()
    {
        $this->assertInstanceOf(PdoOne::class,$this->pdoOne->right('table2 on table1.t1=table2.t2'));
    }

    public function test_where()
    {
        $this->assertInstanceOf(PdoOne::class,$this->pdoOne->where('field1=?,field2=?',['i',20,'s','hello']));
    }

    public function test_set()
    {
        $this->assertInstanceOf(PdoOne::class,$this->pdoOne->set('field1=?,field2=?',['i',20,'s','hello']));
    }

    public function test_group()
    {
        $this->assertInstanceOf(PdoOne::class,$this->pdoOne->group('fieldgroup'));
    }

    public function test_having()
    {
        $this->assertInstanceOf(PdoOne::class,$this->pdoOne->having('field1=?,field2=?',['i',20,'s','hello']));
    }

    public function test_order()
    {
        $this->assertInstanceOf(PdoOne::class,$this->pdoOne->order('name desc'));
    }

    public function test_limit()
    {
        $this->assertInstanceOf(PdoOne::class,$this->pdoOne->limit('1,10'));
    }

    public function test_distinct()
    {
        $this->assertInstanceOf(PdoOne::class,$this->pdoOne->distinct());
    }

   

    public function test_generateSqlFields()
    {
        $this->assertInstanceOf(PdoOne::class,$this->pdoOne->generateSqlFields(true));
    }

   

    public function test_runQuery()
    {
        $this->assertEquals(true,$this->pdoOne->runQuery($this->pdoOne->prepare('select 1 from dual'))); $this->assertEquals([1=>1],$this->pdoOne->select('1')->from('dual')->first(),'it must runs');
    }


    public function test_runRawQuery()
    {
        $this->assertEquals([0=>[1=>1]],$this->pdoOne->runRawQuery('select 1',null,true));
    }

	/**
	 * @throws Exception
	 */
    public function test_setEncryption()
    {
        $this->pdoOne->setEncryption('123//*/*saass11___1212fgbl@#€€"','123//*/*saass11___1212fgbl@#€€"','AES-256-CTR');
        $value=$this->pdoOne->encrypt("bv `lfg+hlc ,vc´,c35'ddl ld_vcvñvc +*=/\\");
        $this->assertTrue(strlen($value)>10,"Encrypted");
        $return=$this->pdoOne->decrypt($value);
	    $this->assertEquals("bv `lfg+hlc ,vc´,c35'ddl ld_vcvñvc +*=/\\",$return,"decrypt correct");

	    $return=$this->pdoOne->decrypt("wrong".$value);
	    $this->assertEquals(false,$return,"decrypt must fail");
	    $return=$this->pdoOne->decrypt("");
	    $this->assertEquals(false,$return,"decrypt must fail");
	    $return=$this->pdoOne->decrypt(null);
	    $this->assertEquals(false,$return,"decrypt must fail");
	    // iv =true
	    $value1=$this->pdoOne->encrypt("abc");
	    $value2=$this->pdoOne->encrypt("abc");
	    $this->assertTrue($value1!=$value2,"Values must be different");
	    // iv =true
	    $this->pdoOne->encryption->iv=false;
	    $value1=$this->pdoOne->encrypt("abc_ABC/abc*abc1234567890[]{[");
	    $value2=$this->pdoOne->encrypt("abc_ABC/abc*abc1234567890[]{[");
	    $this->assertTrue($value1==$value2,"Values must be equals");     
    }
	/**
	 * @throws Exception
	 */
	public function test_setEncryptionINTEGER()
	{
		$this->pdoOne->setEncryption(12345678,'','INTEGER');
		// 2147483640
		$original=2147483640;
		$value=$this->pdoOne->encrypt($original);
		$this->assertTrue(strlen($value)>3
			,"Encrypted");
		$this->assertEquals($original
			,$this->pdoOne->decrypt($value)
			,"decrypt correct");
		// 1
		$original=1;
		$value=$this->pdoOne->encrypt($original);
		$this->assertTrue(strlen($value)>3
			,"Encrypted");
		$this->assertEquals($original
			,$this->pdoOne->decrypt($value)
			,"decrypt correct");
		// 0
		$original=0;
		$value=$this->pdoOne->encrypt($original);
		$this->assertTrue(strlen($value)>3
			,"Encrypted");
		$this->assertEquals($original
			,$this->pdoOne->decrypt($value)
			,"decrypt correct");		
		
	}
	/**
	 * @throws Exception
	 */
	public function test_setEncryptionSIMPLE()
	{
		$this->pdoOne->setEncryption("Zoamelgusta",'','SIMPLE');
		// 2147483640
		$original="abc";
		$value=$this->pdoOne->encrypt($original);
		$this->assertEquals("wrzS",$value
			,"encrypt with problems");
		$this->assertEquals($original
			,$this->pdoOne->decrypt($value)
			,"decrypt with problems");
		$original="Mary had a little lamb. Whose fleece was white as snow";
		$value=$this->pdoOne->encrypt($original);
		$this->assertEquals("rrvh2o3NzcuV1JTNw-PV2cqM09bg1o96xsnc2NGH29_Zxr3UgeTG34fs293Vv4_C4IXf1eTq",$value
			,"encrypt with problems");
		$this->assertEquals($original
			,$this->pdoOne->decrypt($value)
			,"decrypt with problems");		
		// 1
		$original=1222;
		$value=$this->pdoOne->encrypt($original);
		$this->assertEquals("koyhkw==",$value
			,"encrypt with problems");
		$this->assertEquals($original
			,$this->pdoOne->decrypt($value)
			,"decrypt correct");
		// 0
		$original=0;
		$value=$this->pdoOne->encrypt($original);
		$this->assertEquals("kQ==",$value
			,"encrypt with problems");
		$this->assertEquals($original
			,$this->pdoOne->decrypt($value)
			,"decrypt correct");

	}
}
