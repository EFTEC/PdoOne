<?php 
/** @noinspection PhpUndefinedClassInspection */
/** @noinspection PhpUnhandledExceptionInspection */
/** @noinspection SqlNoDataSourceInspection */
/** @noinspection SqlDialectInspection */

namespace eftec\tests;


use eftec\IPdoOneCache;
use eftec\PdoOne;
use Exception;
use PHPUnit\Framework\TestCase;

// it is an example of a CacheService
class CacheServicesrv implements IPdoOneCache {
    public $cacheData=[];
    public $cacheCounter=0; // for debug
    public  function getCache($uid,$family='') {
        if(isset($this->cacheData[$uid])) {
            $this->cacheCounter++;
            echo "test:using cache\n";
            return $this->cacheData[$uid];
        }
        return false;
    }
    public function setCache($uid,$family='',$data=null,$ttl=null) {
        $this->cacheData[$uid]=$data;
    }
    public function invalidateCache($uid = '', $family = '') {
        $this->cacheData=[]; // we delete all the cache
        //unset($this->cacheData[$uid]);
    }
}


class PdoOne_sqlsrv_Test extends TestCase
{
	/** @var PdoOne */
    protected $pdoOne;

    public function setUp()
    {
        $this->pdoOne=new PdoOne("sqlsrv","(local)\SQLEXPRESS","sa","abc.123","travisdb");
        $this->pdoOne->logLevel=3;
        $this->pdoOne->connect();
        

        $cache=new CacheServicesrv();
        $this->pdoOne->setCacheService($cache);
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
        self::assertEquals(false,$this->pdoOne->readonly(),'the database is read only');
    }

    public function test_connect()
    {
	    $this->expectException(Exception::class);
        $this->pdoOne->connect();
    }
    function test_chainresetErrorList() {
        $this->pdoOne->logLevel=3;
        $rows=$this->pdoOne->genError(false)->select('select 123 field1 from sys.routes222')->toList();
        self::assertEquals(false,$rows);
        try {
            $rows = $this->pdoOne->genError(true)->select('select 123 field1 from sys.routes222')->toList();
        } catch(Exception $exception) {
            $rows=false;
        }
        self::assertEquals(false,$rows);
        
        try {
            $rows = $this->pdoOne->genError(true)->select('select 123 field1 from sys.routes222')->toList();
        } catch(Exception $exception) {
            $rows=false;
        }
        self::assertEquals(false,$rows);
        
        $this->pdoOne->throwOnError=false;
        $rows = $this->pdoOne->select('select 123 field1 from sys.routes222')->toList();
        $this->pdoOne->throwOnError=true;
       
        self::assertEquals(false,$rows);

        
        self::assertNotEmpty($this->pdoOne->errorText); // there is an error.
    }
    function test_chainresetErrorListSimple() {
        $this->pdoOne->logLevel=3;
        $rows=$this->pdoOne->genError(false)->select('select 123 field1 from sys.routes222')->toListSimple();
        self::assertEquals(false,$rows);
        try {
            $rows = $this->pdoOne->genError(true)->select('select 123 field1 from sys.routes222')->toListSimple();
        } catch(Exception $exception) {
            $rows=false;
        }
        self::assertEquals(false,$rows);

        try {
            $rows = $this->pdoOne->genError(true)->select('select 123 field1 from sys.routes222')->toListSimple();
        } catch(Exception $exception) {
            $rows=false;
        }
        self::assertEquals(false,$rows);

        $this->pdoOne->throwOnError=false;
        $rows = $this->pdoOne->select('select 123 field1 from sys.routes222')->toListSimple();
        $this->pdoOne->throwOnError=true;

        self::assertEquals(false,$rows);


        self::assertNotEmpty($this->pdoOne->errorText); // there is an error.
    }
    function test_genCode() {
        if(!$this->pdoOne->tableExist('table1')) {
            $this->pdoOne->createTable('table1', ['id' => 'int']);
        }
        self::assertNotEquals('', $this->pdoOne->generateCodeClass('table1'));
        self::assertEquals("['id'=>0]",$this->pdoOne->generateCodeArray('table1'));
        self::assertContains("array \$result=array(['id'=>0])",$this->pdoOne->generateCodeSelect('select * from table1'));
        self::assertContains('$pdo->createTable(\'table1',$this->pdoOne->generateCodeCreate('table1'));
        
    }
    function test_debug() {
        $file=__DIR__."/file.txt";
        $this->pdoOne->logFile=$file;
        $this->pdoOne->debugFile('dummy');
        self::assertEquals(true,file_exists($file));
        @unlink($file);
        $this->pdoOne->logFile='';
    }
    function test_chainresetErrorMeta() {
        $this->pdoOne->logLevel=3;
        $rows=$this->pdoOne->genError(false)->select('select 123 field1 from sys.routes222')->toMeta();
        self::assertEquals(false,$rows);
        try {
            $rows = $this->pdoOne->genError(true)->select('select 123 field1 from sys.routes222')->toMeta();
        } catch(Exception $exception) {
            $rows=false;
        }
        self::assertEquals(false,$rows);

        try {
            $rows = $this->pdoOne->genError(true)->select('select 123 field1 from sys.routes222')->toMeta();
        } catch(Exception $exception) {
            $rows=false;
        }
        self::assertEquals(false,$rows);

        $this->pdoOne->throwOnError=false;
        $rows = $this->pdoOne->select('select 123 field1 from sys.routes222')->toMeta();
        $this->pdoOne->throwOnError=true;

        self::assertEquals(false,$rows);


        self::assertNotEmpty($this->pdoOne->errorText); // there is an error.
    }
    function test_chainresetErrorFirst() {
        $this->pdoOne->logLevel=3;
        $rows=$this->pdoOne->genError(false)->select('select 123 field1 from sys.routes222')->first();
        self::assertEquals(false,$rows);
        try {
            $rows = $this->pdoOne->genError(true)->select('select 123 field1 from sys.routes222')->first();
        } catch(Exception $exception) {
            $rows=false;
        }
        self::assertEquals(false,$rows);

        try {
            $rows = $this->pdoOne->genError(true)->select('select 123 field1 from sys.routes222')->first();
        } catch(Exception $exception) {
            $rows=false;
        }
        self::assertEquals(false,$rows);

        $this->pdoOne->throwOnError=false;
        $rows = $this->pdoOne->select('select 123 field1 from sys.routes222')->first();
        $this->pdoOne->throwOnError=true;

        self::assertEquals(false,$rows);


        self::assertNotEmpty($this->pdoOne->errorText); // there is an error.

        //$this->pdoOne->builderReset();
        //$rows=$this->pdoOne->select('select 123 field1 from sys.routes')->toList();
        //$this->assertEquals([['field1'=>123]],$rows);
    }
    function test_chainresetErrorLast() {
        $this->pdoOne->logLevel=3;
        $rows=$this->pdoOne->genError(false)->select('select 123 field1 from sys.routes222')->last();
        self::assertEquals(false,$rows);
        try {
            $rows = $this->pdoOne->genError(true)->select('select 123 field1 from sys.routes222')->last();
            $rows="XXX";
        } catch(Exception $exception) {
            $rows=false;
        }
        self::assertEquals(false,$rows);

        try {
            $rows = $this->pdoOne->genError(true)->select('select 123 field1 from sys.routes222')->last();
        } catch(Exception $exception) {
            $rows=false;
        }
        self::assertEquals(false,$rows);

        $this->pdoOne->throwOnError=false;
        $rows = $this->pdoOne->select('select 123 field1 from sys.routes222')->last();
        $this->pdoOne->throwOnError=true;

        self::assertEquals(false,$rows);


        self::assertNotEmpty($this->pdoOne->errorText); // there is an error.

    }
    function test_createtable() {
        if($this->pdoOne->tableExist('table5')) {
            $this->pdoOne->dropTable('table5');
        }
        $r=$this->pdoOne->createTable('table5',
            ['id'=>'int not null','name'=>'varchar(50)']
            ,['id'=>'PRIMARY KEY']);
        self::assertEquals(true,$r);
        self::assertEquals(array(
            'id'   => [
                'phptype'    => 'int',
                'conversion' => null,
                'type'       => 'int',
                'size'       => null,
                'null'       => false,
                'identity'   => false,
                'sql'        => 'int NOT NULL'
            ],
            'name' => [
                'phptype'    => 'string',
                'conversion' => null,
                'type'       => 'varchar',
                'size'       => '50',
                'null'       => true,
                'identity'   => false,
                'sql'        => 'varchar(50)'

            ]
        ), $this->pdoOne->getDefTable('table5'));
        self::assertEquals(array('id' => 'PRIMARY KEY'),$this->pdoOne->getDefTableKeys('table5'));
        self::assertEquals(array(),$this->pdoOne->getDefTableFK('table5'));
    }
    
    function test_chainreset() {
        $this->pdoOne->logLevel=3;
        $rows=$this->pdoOne->select('select 123 field1 from sys.routes');
        $this->pdoOne->builderReset();
        $rows=$this->pdoOne->select('select 123 field1 from sys.routes')->toList();
        self::assertEquals([['field1' =>123]],$rows);
    }
    function test_cache() {
        $this->pdoOne->getCacheService()->cacheCounter=0;
        
        $rows=$this->pdoOne->select('select 123 field1 from sys.routes')->useCache()->toList();
        self::assertEquals([['field1' =>123]],$rows);
        $rows=$this->pdoOne->select('select 123 field1 from sys.routes')->where('1=1')->order('1')->useCache()->toList();
        $rows=$this->pdoOne->select('select 123 field1 from sys.routes')->useCache()->toList();
        self::assertEquals([['field1' =>123]],$rows);
        self::assertEquals(1,$this->pdoOne->getCacheService()->cacheCounter); // 1= cache used 1 time
        $rows=$this->pdoOne->invalidateCache();
        
        $rows=$this->pdoOne->select('select 123 field1 from sys.routes')->useCache()->toList();
        self::assertEquals([['field1' =>123]],$rows);
        self::assertEquals(1,$this->pdoOne->getCacheService()->cacheCounter); // 1= cache used 1 time
        $this->pdoOne->getCacheService()->cacheCounter=0;
    }
    function test_cache_noCache() {
        $this->pdoOne->setCacheService(null);
        
        

        $rows=$this->pdoOne->select('select 123 field1 from sys.routes')->useCache()->toList();
        self::assertEquals([['field1' =>123]],$rows);
        $rows=$this->pdoOne->select('select 123 field1 from sys.routes')->where('1=1')->order('1')->useCache()->toList();
        $rows=$this->pdoOne->select('select 123 field1 from sys.routes')->useCache()->toList();
        self::assertEquals([['field1' =>123]],$rows);
        //$this->assertEquals(1,$this->pdoOne->getCacheService()->cacheCounter); // 1= cache used 1 time
        $rows=$this->pdoOne->invalidateCache();

        $rows=$this->pdoOne->select('select 123 field1 from sys.routes')->useCache()->toList();
        self::assertEquals([['field1' =>123]],$rows);
        //$this->assertEquals(1,$this->pdoOne->getCacheService()->cacheCounter); // 1= cache used 1 time
        //$this->pdoOne->getCacheService()->cacheCounter=0;

        $cache=new CacheServicesrv();
        $this->pdoOne->setCacheService($cache);
    }
    public function test_open()
    {
        //$this->expectException(\Exception::class);
        //$this->pdoOne->open(true);
	    try {
		    $r=$this->pdoOne->runRawQuery('drop table product_category');
		    self::assertEquals(true,$r,"Drop failed");
	    } catch (Exception $e) {
		    $r=false;
	    	// drops silently
	    }


	    $sqlT2="CREATE TABLE product_category (
	    id_category INT NOT NULL,
	    catname VARCHAR(45) NULL,
	    PRIMARY KEY (id_category));";

	    try {
		    $r=$this->pdoOne->runRawQuery($sqlT2);
	    } catch (Exception $e) {
		    echo $e->getMessage()."<br>";
	    }
	    self::assertEquals(true,$r,"failed to create table");

        self::assertGreaterThan(1,count($this->pdoOne->objectList('table')));
	    // we add some values
	    $this->pdoOne->set(['id_category' => 123, 'catname' => 'cheap'])
		    ->from('product_category')->insert();
        $this->pdoOne->insert('product_category',['id_category','catname'],['id_category'=>2,'catname'=>'cheap']);
        $this->pdoOne->insert('product_category',['id_category'=>3,'catname'=>'cheap']);
        $this->pdoOne->insert('product_category',['id_category'=>4,'catname'=>'cheap4']);
        $this->pdoOne->insert('product_category',['id_category','5','catname','cheap']);
        $count=$this->pdoOne->count('from product_category')->firstScalar();
	    self::assertEquals(5,$count,'insert must value 5');
	    
        $count=$this->pdoOne->select('select id_category from product_category where id_category=123')->useCache()->firstScalar();
        self::assertEquals(123,$count,'insert must value 123');
        $count=$this->pdoOne->select('select id_category from product_category where id_category=123')->useCache()->firstScalar();
        self::assertEquals(123,$count,'insert must value 123');

        self::assertEquals(1,$this->pdoOne->getCacheService()->cacheCounter); // 1= cache used 1 time

        $count=$this->pdoOne->select('select catname from product_category where id_category>0')->useCache()->firstScalar();
        self::assertEquals('cheap',$count);
        $count=$this->pdoOne->select('select catname from product_category where id_category>0')->useCache()->firstScalar();
        self::assertEquals('cheap',$count);

        $count=$this->pdoOne->select('select catname from product_category where id_category=4')->useCache()->first();
        self::assertEquals(['catname' =>'cheap4'],$count);
        $count=$this->pdoOne->select('select catname from product_category where id_category=4')->useCache()->first();
        self::assertEquals(['catname' =>'cheap4'],$count);        
        
        $count=$this->pdoOne->select('select catname from product_category')->useCache()->last();
        self::assertEquals(['catname' => 'cheap'],$count);
        $count=$this->pdoOne->select('select catname from product_category')->useCache()->last();
        self::assertEquals(['catname' => 'cheap'],$count);

        $count=$this->pdoOne->select('select catname from product_category')->where('id_category=?',[4])->useCache()->firstScalar();
        self::assertEquals('cheap4',$count,'insert must value cheap4');
        $count=$this->pdoOne->select('select catname from product_category')->where('id_category=?',[4])->useCache()->firstScalar();
        self::assertEquals('cheap4',$count,'insert must value cheap4');
        $count=$this->pdoOne->select('select catname from product_category')->where('id_category=?',[4])
                        ->order('id_category')->useCache()->firstScalar();
        self::assertEquals('cheap4',$count,'insert must value cheap4');
        $count=$this->pdoOne->select('select catname from product_category')->where('id_category=?',[3])->useCache()->firstScalar();
        self::assertEquals('cheap',$count,'insert must value cheap');

        $count=$this->pdoOne->select('select catname from product_category')->where('id_category=:idcat',['idcat'=>4])->firstScalar();
        self::assertEquals('cheap4',$count,'insert must value cheap4');
        $count=$this->pdoOne->select('select catname from product_category')->where('id_category=:idcat',['idcat'=>4])->firstScalar();
        self::assertEquals('cheap4',$count,'insert must value cheap4');
        self::assertEquals(137
            ,$this->pdoOne->sum('id_category')->from('product_category')->firstScalar()
            ,'sum must value 137');
        self::assertEquals(2
            ,$this->pdoOne->min('id_category')->from('product_category')->firstScalar()
            ,'min must value 2');
        self::assertEquals(123
            ,$this->pdoOne->max('id_category')->from('product_category')->firstScalar()
            ,'max must value 123');
        $rr=$this->pdoOne->avg('id_category')->from('product_category')->firstScalar();
        self::assertEquals(27.4
            ,$rr
            ,'avg must value 27.4',0.1);
        
        self::assertEquals([['id_category' =>2],
                            ['id_category'=>3],
                            ['id_category'=>4],
                            ['id_category'=>5],
                            ['id_category'=>123]]
            ,$this->pdoOne->select('id_category')->from('product_category')->useCache()->toList());

        self::assertEquals([['id_category' =>2],
                            ['id_category'=>3],
                            ['id_category'=>4],
                            ['id_category'=>5],
                            ['id_category'=>123]]
            ,$this->pdoOne->select('id_category')->from('product_category')->useCache()->toList());
        self::assertEquals(6,$this->pdoOne->getCacheService()->cacheCounter); // 1= cache used 1 time
        
        self::assertEquals([2, 3, 4, 5, 123]
            ,$this->pdoOne->select('id_category')->from('product_category')->useCache()->toListSimple());
        self::assertEquals([2, 3, 4, 5, 123]
            ,$this->pdoOne->select('id_category')->from('product_category')->useCache()->toListSimple());        
        self::assertEquals([2 =>'cheap', 3 =>'cheap', '4' =>'cheap4', 5 =>'cheap', 123 =>'cheap']
            ,$this->pdoOne->select('id_category,catname')->from('product_category')->useCache()->toListKeyValue());
        self::assertEquals([2 =>'cheap', 3 =>'cheap', '4' =>'cheap4', 5 =>'cheap', 123 =>'cheap']
            ,$this->pdoOne->select('id_category,catname')->from('product_category')->useCache()->toListKeyValue());

        self::assertEquals(8,$this->pdoOne->getCacheService()->cacheCounter); // 3= cache used 1 time
        
    }
    public function test_quota()
    {
        self::assertEquals('[hello] world',$this->pdoOne->addDelimiter('hello world'));
        self::assertEquals('[hello].[world]',$this->pdoOne->addDelimiter('hello.world'));
        self::assertEquals('[hello]=value',$this->pdoOne->addDelimiter('hello=value'));
        self::assertEquals('[hello] =value',$this->pdoOne->addDelimiter('hello =value'));
        self::assertEquals('20190101',$this->pdoOne->dateConvert('01/01/2019','human','sql'));
        self::assertEquals('42143278901651563',$this->pdoOne->getUnpredictable("12345678901234561"));
        self::assertEquals('12345678901234561',$this->pdoOne->getUnpredictableInv("42143278901651563"));
        self::assertNotEmpty($this->pdoOne->dateTextNow()); // '2020-01-25T22:17:41Z',
        self::assertNotEmpty($this->pdoOne->dateSqlNow()); // '2020-01-25 22:18:32',
    }
    public function test_emptyargs()
    {
       
        $r=true;
        if($this->pdoOne->objectExist('product_category')) {
            $r = $this->pdoOne->drop('product_category', 'table');
        } 
        self::assertEquals(true,$r,"Drop failed");

        if($this->pdoOne->objectExist('category')) {
            $r = $this->pdoOne->drop('category', 'table');
        }

        $sqlT2="CREATE TABLE product_category (id_category INT NOT NULL,catname 
                VARCHAR(45) NULL, PRIMARY KEY (id_category));";
        try {
            $r=$this->pdoOne->runRawQuery($sqlT2);
        } catch (Exception $e) {
            echo $e->getMessage()."<br>";
        }
        $sqlT2="CREATE TABLE [category] (id_category INT NOT NULL,catname 
                VARCHAR(45) NULL, PRIMARY KEY (id_category));";
        try {
            $r=$this->pdoOne->runRawQuery($sqlT2);
        } catch (Exception $e) {
            echo $e->getMessage()."<br>";
        }
        self::assertEquals(true,$r,"failed to create table");
        // we add some values
        $this->pdoOne->set(['id_category' => 1, 'catname' => 'cheap'])
            ->from('product_category')->insert();
        $this->pdoOne->set(['id_category' => 2, 'catname' => 'cheap2'])
            ->from('product_category')->insert();
        $this->pdoOne->set("id_category=2,catname='cheap1'")->where("id_category=2")
            ->from('product_category')->update();

        $sr=$this->pdoOne->update("product_category set catname='expensive' where id_category=1");
      
        self::assertEquals(['id_category' =>1, 'catname' =>'expensive'],$this->pdoOne->select('select * from product_category where id_category=1')->first());
        self::assertEquals(['id_category' =>2, 'catname' =>'cheap1'],$this->pdoOne->select('select * from product_category where id_category=2')->first());
    
        $this->pdoOne->runMultipleRawQuery("insert into product_category(id_category,catname) values (3,'multi');
                insert into product_category(id_category,catname) values (4,'multi'); ");
        self::assertEquals(4,$this->pdoOne->count()->from('product_category')->firstScalar());
        $r=$this->pdoOne->set(['id_category',1,'catname','c1'])->from('category')->insert();
        
        $obj=['id_category'=>2,'catname'=>'c2'];
        $r=$this->pdoOne->insertObject('category',$obj);
            
        $query=$this->pdoOne->select('*')->from('product_category')
            ->innerjoin('category on product_category.id_category=category.id_category')
            ->toList();

        self::assertEquals([['id_category' =>1, 'catname' =>'c1'], ['id_category' =>2, 'catname' =>'c2']],$query);

        $this->pdoOne->delete('product_category where id_category>0');
        
        self::assertEquals(0,$this->pdoOne->count()->from('product_category')->firstScalar());
        
    }

	public function test_time()
	{
        self::assertEquals('2019-02-06 05:06:07',PdoOne::dateText2Sql('2019-02-06T05:06:07Z',true));
		self::assertEquals('2019-02-06 00:00:00',PdoOne::dateText2Sql('2019-02-06',false));
		
		self::assertEquals('2018-02-06 05:06:07.123000',PdoOne::dateText2Sql('2018-02-06T05:06:07.123Z',true));

		// sql format -> human format dd/mm/yyyy
        self::assertEquals('06/02/2019',PdoOne::dateSql2Text('2019-02-06'));
        
        // 2019-02-06T05:06:07Z -> 2019-02-06 05:06:07 -> 06/02/2019 05:06:07
		self::assertEquals('06/02/2019 05:06:07'
            ,PdoOne::dateSql2Text(PdoOne::dateText2Sql('2019-02-06T05:06:07Z',true)));
		self::assertEquals('06/02/2019 05:06:07',PdoOne::dateSql2Text('2019-02-06 05:06:07'));
		self::assertEquals('06/02/2018 05:06:07.123000',PdoOne::dateSql2Text('2018-02-06 05:06:07.123'));
	}

	/**
	 * @throws Exception
	 */
	public function test_sequence()
	{
        $this->pdoOne->tableSequence='testsequence';
	    if($this->pdoOne->objectExist($this->pdoOne->tableSequence,'sequence')) {
	        $this->pdoOne->drop($this->pdoOne->tableSequence,'sequence');
        }
        if($this->pdoOne->objectExist('next_'.$this->pdoOne->tableSequence,'function')) {
            $this->pdoOne->drop('next_'.$this->pdoOne->tableSequence,'function');
        }
		try {
			$this->pdoOne->createSequence();
		} catch(Exception $ex) {
            //var_dump($this->pdoOne->lastQuery);
			//var_dump($this->pdoOne->lastError());
		}
		self::assertLessThan(3639088446091303982,$this->pdoOne->getSequence(true),"sequence must be greater than 3639088446091303982");
	}

	public function test_sequence2()
	{
		self::assertLessThan(3639088446091303982,$this->pdoOne->getSequencePHP(false),"sequence must be greater than 3639088446091303982");
		$s1=$this->pdoOne->getSequencePHP(false);
		$s2=$this->pdoOne->getSequencePHP(false);
		self::assertTrue($s1!=$s2,"sequence must not be the same");
		$this->pdoOne->encryption->encPassword=1020304050;
		$s1=$this->pdoOne->getSequencePHP(true);
		$s2=$this->pdoOne->getSequencePHP(true);
		self::assertTrue($s1!=$s2,"sequence must not be the same");
		

		
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
        self::assertEquals(null,$this->pdoOne->getMessages(),'this is not a message container');
    }



    public function test_startTransaction()
    {
        self::assertEquals(true,$this->pdoOne->startTransaction());
        $this->pdoOne->commit();

    }

    public function test_commit()
    {
        self::assertEquals(false,(false),'transaction is not open');
    }

    public function test_rollback()
    {
        self::assertEquals(false,(false),'transaction is not open');
    }

 
    public function test_select()
    {
        self::assertInstanceOf(PdoOne::class,$this->pdoOne->select('select 1 from sys.routes'));
    }

	public function test_sqlGen()
	{
		self::assertEquals("select 1 from sys.routes",$this->pdoOne->select('select 1 from sys.routes')->sqlGen(true));

		self::assertEquals("select 1 from sys.routes",$this->pdoOne->select('select 1')->from('sys.routes')->sqlGen(true));

		self::assertEquals("select 1, 2 from sys.routes",$this->pdoOne->select('1')->select('2')->from('sys.routes')->sqlGen(true));

		self::assertEquals("select 1, 2 from sys.routes",$this->pdoOne->select(['1', '2'])->from('sys.routes')->sqlGen(true));

		self::assertEquals("select 1, 2 from sys.routes where field=?"
			,$this->pdoOne
				->select(['1','2'])
				->from('sys.routes')
				->where('field=?',[20])
				->sqlGen(true));

        self::assertEquals("select 1, 2 from sys.routes where field=:field"
            ,$this->pdoOne
                ->select(['1','2'])
                ->from('sys.routes')
                ->where('field=:field',[20])
                ->sqlGen(true));

		self::assertEquals("select 1, 2 from sys.routes where field=? group by 2 having field2=? order by 1"
			,$this->pdoOne
				->select(['1','2'])
				->from('sys.routes')
				->where('field=?',[20])
				->order('1')
				->group('2')
				->having('field2=?',[4])
				->sqlGen(true));		
	}

    public function test_join()
    {
        self::assertInstanceOf(PdoOne::class,$this->pdoOne->join('tablejoin on t1.field=t2.field'));
    }

 

    public function test_from()
    {
        self::assertInstanceOf(PdoOne::class,$this->pdoOne->from('table t1'));
    }

    public function test_left()
    {
        self::assertInstanceOf(PdoOne::class,$this->pdoOne->left('table2 on table1.t1=table2.t2'));
    }

    public function test_right()
    {
        self::assertInstanceOf(PdoOne::class,$this->pdoOne->right('table2 on table1.t1=table2.t2'));
    }

    public function test_where()
    {
        self::assertInstanceOf(PdoOne::class,$this->pdoOne->where('field1=?,field2=?',[20, 'hello']));
    }

    public function test_set()
    {
        self::assertInstanceOf(PdoOne::class,$this->pdoOne->set('field1=?,field2=?',[20, 'hello']));
    }

    public function test_group()
    {
        self::assertInstanceOf(PdoOne::class,$this->pdoOne->group('fieldgroup'));
    }

    public function test_having()
    {
        self::assertInstanceOf(PdoOne::class,$this->pdoOne->having('field1=?,field2=?',[20, 'hello']));
    }

    public function test_order()
    {
        self::assertInstanceOf(PdoOne::class,$this->pdoOne->order('name desc'));
    }

    public function test_limit()
    {
        self::assertInstanceOf(PdoOne::class,$this->pdoOne->order('c1')->limit('1,10'));
    }

    public function test_distinct()
    {
        self::assertInstanceOf(PdoOne::class,$this->pdoOne->distinct());
    }

   



   

    public function test_runQuery()
    {
        self::assertEquals(true,$this->pdoOne->runQuery($this->pdoOne->prepare('select 1')));
        self::assertEquals(['c' =>1],$this->pdoOne->select('1 as c')->from('sys.schemas')->first(),'it must runs');
    }


    public function test_runRawQuery()
    {
        self::assertEquals([0 =>['c' =>1]],$this->pdoOne->runRawQuery('select 1 as c',null,true));
    }

	/**
	 * @throws Exception
	 */
    public function test_setEncryption()
    {
        $this->pdoOne->setEncryption('123//*/*saass11___1212fgbl@#€€"','123//*/*saass11___1212fgbl@#€€"','AES-256-CTR');
        $value=$this->pdoOne->encrypt("bv lfg+hlc ,vc´,c35'ddl ld_vcvñvc +*=/\\");
        self::assertTrue(strlen($value)>10,"Encrypted");
        $return=$this->pdoOne->decrypt($value);
	    self::assertEquals("bv lfg+hlc ,vc´,c35'ddl ld_vcvñvc +*=/\\",$return,"decrypt correct");

	    $return=$this->pdoOne->decrypt("wrong".$value);
	    self::assertEquals(false,$return,"decrypt must fail");
	    $return=$this->pdoOne->decrypt("");
	    self::assertEquals(false,$return,"decrypt must fail");
	    $return=$this->pdoOne->decrypt(null);
	    self::assertEquals(false,$return,"decrypt must fail");
	    // iv =true
	    $value1=$this->pdoOne->encrypt("abc");
	    $value2=$this->pdoOne->encrypt("abc");
	    self::assertTrue($value1!=$value2,"Values must be different");
	    // iv =true
	    $this->pdoOne->encryption->iv=false;
	    $value1=$this->pdoOne->encrypt("abc_ABC/abc*abc1234567890[]{[");
	    $value2=$this->pdoOne->encrypt("abc_ABC/abc*abc1234567890[]{[");
	    self::assertTrue($value1==$value2,"Values must be equals");     
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
		self::assertTrue(strlen($value)>3
			,"Encrypted");
		self::assertEquals($original
			,$this->pdoOne->decrypt($value)
			,"decrypt correct");
		// 1
		$original=1;
		$value=$this->pdoOne->encrypt($original);
		self::assertTrue(strlen($value)>3
			,"Encrypted");
		self::assertEquals($original
			,$this->pdoOne->decrypt($value)
			,"decrypt correct");
		// 0
		$original=0;
		$value=$this->pdoOne->encrypt($original);
		self::assertTrue(strlen($value)>3
			,"Encrypted");
		self::assertEquals($original
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
		self::assertEquals("wrzS",$value
			,"encrypt with problems");
		self::assertEquals($original
			,$this->pdoOne->decrypt($value)
			,"decrypt with problems");
		$original="Mary had a little lamb. Whose fleece was white as snow";
		$value=$this->pdoOne->encrypt($original);
		self::assertEquals("rrvh2o3NzcuV1JTNw-PV2cqM09bg1o96xsnc2NGH29_Zxr3UgeTG34fs293Vv4_C4IXf1eTq",$value
			,"encrypt with problems");
		self::assertEquals($original
			,$this->pdoOne->decrypt($value)
			,"decrypt with problems");		
		// 1
		$original=1222;
		$value=$this->pdoOne->encrypt($original);
		self::assertEquals("koyhkw==",$value
			,"encrypt with problems");
		self::assertEquals($original
			,$this->pdoOne->decrypt($value)
			,"decrypt correct");
		// 0
		$original=0;
		$value=$this->pdoOne->encrypt($original);
		self::assertEquals("kQ==",$value
			,"encrypt with problems");
		self::assertEquals($original
			,$this->pdoOne->decrypt($value)
			,"decrypt correct");

	}
}
