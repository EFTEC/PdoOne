<?php /** @noinspection PhpUnusedLocalVariableInspection */
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

/**
 * Class PdoOne_sqlsrv_Test
 * @package eftec\tests
 * @deprecated
 */
class PdoOne_sqlsrv_Test extends TestCase
{
	/** @var PdoOne */
    protected $pdoOne;

    public function setUp() : void
    {
        $this->pdoOne=new PdoOne("sqlsrv","PCJC\SQLSERVER2017","sa","abc.123","travisdb");
        $this->pdoOne->logLevel=3;
        $this->pdoOne->connect();


        $cache=new CacheServicesrv();
        $this->pdoOne->setCacheService($cache);
    }


	/**
	 * @doesNotPerformAssertions
	 */
    public function test_db(): void
    {
         $this->pdoOne->db('travisdb');
    }


    public function test_readonly(): void
    {
        $this->assertEquals(false,$this->pdoOne->readonly(),'the database is read only');
    }

    public function test_connect(): void
    {
	    $this->expectException(Exception::class);
        $this->pdoOne->connect();
    }
    function test_chainresetErrorList(): void
    {
        $this->pdoOne->logLevel=3;
        $rows=$this->pdoOne->genError(false)->select('select 123 field1 from sys.routes222')->toList();
        $this->assertEquals(false,$rows);
        try {
            $rows = $this->pdoOne->genError(true)->select('select 123 field1 from sys.routes222')->toList();
        } catch(Exception $exception) {
            $rows=false;
        }
        $this->assertEquals(false,$rows);

        try {
            $rows = $this->pdoOne->genError(true)->select('select 123 field1 from sys.routes222')->toList();
        } catch(Exception $exception) {
            $rows=false;
        }
        $this->assertEquals(false,$rows);

        $this->pdoOne->throwOnError=false;
        $rows = $this->pdoOne->select('select 123 field1 from sys.routes222')->toList();
        $this->pdoOne->throwOnError=true;

        $this->assertEquals(false,$rows);


        $this->assertNotEmpty($this->pdoOne->errorText); // there is an error.
    }
    function test_chainresetErrorListSimple(): void
    {
        $this->pdoOne->logLevel=3;
        $rows=$this->pdoOne->genError(false)->select('select 123 field1 from sys.routes222')->toListSimple();
        $this->assertEquals(false,$rows);
        try {
            $rows = $this->pdoOne->genError(true)->select('select 123 field1 from sys.routes222')->toListSimple();
        } catch(Exception $exception) {
            $rows=false;
        }
        $this->assertEquals(false,$rows);

        try {
            $rows = $this->pdoOne->genError(true)->select('select 123 field1 from sys.routes222')->toListSimple();
        } catch(Exception $exception) {
            $rows=false;
        }
        $this->assertEquals(false,$rows);

        $this->pdoOne->throwOnError=false;
        $rows = $this->pdoOne->select('select 123 field1 from sys.routes222')->toListSimple();
        $this->pdoOne->throwOnError=true;

        $this->assertEquals(false,$rows);


        $this->assertNotEmpty($this->pdoOne->errorText); // there is an error.
    }
    function test_genCode(): void
    {
        if(!$this->pdoOne->tableExist('table1')) {
            $this->pdoOne->createTable('table1', ['id' => 'int']);
        }
        $this->assertNotEquals('', $this->pdoOne->generateCodeClass('table1'));
        $this->assertEquals("['id'=>0]",$this->pdoOne->generateCodeArray('table1'));
        $this->assertStringContainsString("array \$result=array(['id'=>0])",$this->pdoOne->generateCodeSelect('select * from table1'));
        $this->assertStringContainsString('$pdo->createTable(\'table1',$this->pdoOne->generateCodeCreate('table1'));

    }
   /* function test_debug() {
        $file=__DIR__."/file.txt";
        $this->pdoOne->logFile=$file;

        $this->pdoOne->debugFile('dummy');
        //$this->assertEquals(true,file_exists($file));
        //@unlink($file);
        //$this->pdoOne->logFile='';
    }*/
    function test_chainresetErrorMeta(): void
    {
        $this->pdoOne->logLevel=3;
        $rows=$this->pdoOne->genError(false)->select('select 123 field1 from sys.routes222')->toMeta();
        $this->assertEquals(false,$rows);
        try {
            $rows = $this->pdoOne->genError(true)->select('select 123 field1 from sys.routes222')->toMeta();
        } catch(Exception $exception) {
            $rows=false;
        }
        $this->assertEquals(false,$rows);

        try {
            $rows = $this->pdoOne->genError(true)->select('select 123 field1 from sys.routes222')->toMeta();
        } catch(Exception $exception) {
            $rows=false;
        }
        $this->assertEquals(false,$rows);

        $this->pdoOne->throwOnError=false;
        $rows = $this->pdoOne->select('select 123 field1 from sys.routes222')->toMeta();
        $this->pdoOne->throwOnError=true;

        $this->assertEquals(false,$rows);


        $this->assertNotEmpty($this->pdoOne->errorText); // there is an error.
    }
    function test_chainresetErrorFirst(): void
    {
        $this->pdoOne->logLevel=3;
        $rows=$this->pdoOne->genError(false)->select('select 123 field1 from sys.routes222')->first();
        $this->assertEquals(false,$rows);
        try {
            $rows = $this->pdoOne->genError(true)->select('select 123 field1 from sys.routes222')->first();
        } catch(Exception $exception) {
            $rows=false;
        }
        $this->assertEquals(false,$rows);

        try {
            $rows = $this->pdoOne->genError(true)->select('select 123 field1 from sys.routes222')->first();
        } catch(Exception $exception) {
            $rows=false;
        }
        $this->assertEquals(false,$rows);

        $this->pdoOne->throwOnError=false;
        $rows = $this->pdoOne->select('select 123 field1 from sys.routes222')->first();
        $this->pdoOne->throwOnError=true;

        $this->assertEquals(false,$rows);


        $this->assertNotEmpty($this->pdoOne->errorText); // there is an error.

        //$this->pdoOne->builderReset();
        //$rows=$this->pdoOne->select('select 123 field1 from sys.routes')->toList();
        //$this->assertEquals([['field1'=>123]],$rows);
    }
    function test_chainresetErrorLast(): void
    {
        $this->pdoOne->logLevel=3;
        $rows=$this->pdoOne->genError(false)->select('select 123 field1 from sys.routes222')->last();
        $this->assertEquals(false,$rows);
        try {
            $rows = $this->pdoOne->genError(true)->select('select 123 field1 from sys.routes222')->last();
            $rows="XXX";
        } catch(Exception $exception) {
            $rows=false;
        }
        $this->assertEquals(false,$rows);

        try {
            $rows = $this->pdoOne->genError(true)->select('select 123 field1 from sys.routes222')->last();
        } catch(Exception $exception) {
            $rows=false;
        }
        $this->assertEquals(false,$rows);

        $this->pdoOne->throwOnError=false;
        $rows = $this->pdoOne->select('select 123 field1 from sys.routes222')->last();
        $this->pdoOne->throwOnError=true;

        $this->assertEquals(false,$rows);


        $this->assertNotEmpty($this->pdoOne->errorText); // there is an error.

    }
    function test_createtable(): void
    {
        if($this->pdoOne->tableExist('table5')) {
            $this->pdoOne->dropTable('table5');
        }
        $r=$this->pdoOne->createTable('table5',
            ['id'=>'int not null','name'=>'varchar(50)','idfk'=>'int not null']
            ,['id'=>'PRIMARY KEY']);
        $this->assertEquals(true,$r);
        $this->assertEquals(array(
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

            ],
            'idfk'   => [
                'phptype'    => 'int',
                'conversion' => null,
                'type'       => 'int',
                'size'       => null,
                'null'       => false,
                'identity'   => false,
                'sql'        => 'int NOT NULL'
            ],
        ), $this->pdoOne->getDefTable('table5'));

        if($this->pdoOne->tableExist('table6')) {
            $this->pdoOne->dropTable('table6');
        }
        $r=$this->pdoOne->createTable('table6',
            ['id'=>'int not null','name'=>'varchar(50)']
            ,['id'=>'PRIMARY KEY']);

        //$this->pdoOne->createFK('table5',['col1','FOREIGN KEY REFERENCES[table6]([id])']);
        $this->pdoOne->createFK('table5',['idfk'=>"FOREIGN KEY REFERENCES[table6]([id])"]);
        $this->assertEquals(['id' => 'PRIMARY KEY'],$this->pdoOne->getDefTableKeys('table5'));
        $this->assertEquals([ 'idfk' => 'FOREIGN KEY REFERENCES [table6]([id])'],$this->pdoOne->getDefTableFK('table5'));
    }

    function test_chainreset(): void
    {
        $this->pdoOne->logLevel=3;
        $rows=$this->pdoOne->select('select 123 field1 from sys.routes');
        //$this->pdoOne->builderReset();
        $rows=$this->pdoOne->select('select 123 field1 from sys.routes')->toList();
        $this->assertEquals([['field1' =>123]],$rows);
    }
    function test_cache(): void
    {
        $this->pdoOne->getCacheService()->cacheCounter=0;

        $rows=$this->pdoOne->select('select 123 field1 from sys.routes')->useCache()->toList();
        $this->assertEquals([['field1' =>123]],$rows);
        $rows=$this->pdoOne->select('select 123 field1 from sys.routes')->where('1=1')->order('1')->useCache()->toList();
        $rows=$this->pdoOne->select('select 123 field1 from sys.routes')->useCache()->toList();
        $this->assertEquals([['field1' =>123]],$rows);
        $this->assertEquals(1,$this->pdoOne->getCacheService()->cacheCounter); // 1= cache used 1 time
        $rows=$this->pdoOne->invalidateCache();

        $rows=$this->pdoOne->select('select 123 field1 from sys.routes')->useCache()->toList();
        $this->assertEquals([['field1' =>123]],$rows);
        $this->assertEquals(1,$this->pdoOne->getCacheService()->cacheCounter); // 1= cache used 1 time
        $this->pdoOne->getCacheService()->cacheCounter=0;
    }
    function test_cache_noCache(): void
    {
        $this->pdoOne->setCacheService(null);



        $rows=$this->pdoOne->select('select 123 field1 from sys.routes')->useCache()->toList();
        $this->assertEquals([['field1' =>123]],$rows);
        $rows=$this->pdoOne->select('select 123 field1 from sys.routes')->where('1=1')->order('1')->useCache()->toList();
        $rows=$this->pdoOne->select('select 123 field1 from sys.routes')->useCache()->toList();
        $this->assertEquals([['field1' =>123]],$rows);
        //$this->assertEquals(1,$this->pdoOne->getCacheService()->cacheCounter); // 1= cache used 1 time
        $rows=$this->pdoOne->invalidateCache();

        $rows=$this->pdoOne->select('select 123 field1 from sys.routes')->useCache()->toList();
        $this->assertEquals([['field1' =>123]],$rows);
        //$this->assertEquals(1,$this->pdoOne->getCacheService()->cacheCounter); // 1= cache used 1 time
        //$this->pdoOne->getCacheService()->cacheCounter=0;

        $cache=new CacheServicesrv();
        $this->pdoOne->setCacheService($cache);
    }
    public function test_open(): void
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


	    $sqlT2="CREATE TABLE product_category (
	    id_category INT NOT NULL,
	    catname VARCHAR(45) NULL,
	    PRIMARY KEY (id_category));";

	    try {
		    $r=$this->pdoOne->runRawQuery($sqlT2);
	    } catch (Exception $e) {
		    echo $e->getMessage()."<br>";
	    }
	    $this->assertEquals(true,$r,"failed to create table");

        $this->assertGreaterThan(1,count($this->pdoOne->objectList('table')));
	    // we add some values
	    $this->pdoOne->set(['id_category' => 123, 'catname' => 'cheap'])
		    ->from('product_category')->insert();
        $this->pdoOne->insert('product_category',['id_category','catname'],['id_category'=>2,'catname'=>'cheap']);
        $this->pdoOne->insert('product_category',['id_category'=>3,'catname'=>'cheap']);
        $this->pdoOne->insert('product_category',['id_category'=>4,'catname'=>'cheap4']);
        $this->pdoOne->insert('product_category',['id_category','5','catname','cheap']);
        $count=$this->pdoOne->count('from product_category');
	    $this->assertEquals(5,$count,'insert must value 5');

        $count=$this->pdoOne->select('select id_category from product_category where id_category=123')->useCache()->firstScalar();
        $this->assertEquals(123,$count,'insert must value 123');
        $count=$this->pdoOne->select('select id_category from product_category where id_category=123')->useCache()->firstScalar();
        $this->assertEquals(123,$count,'insert must value 123');

        $this->assertEquals(1,$this->pdoOne->getCacheService()->cacheCounter); // 1= cache used 1 time

        $count=$this->pdoOne->select('select catname from product_category where id_category>0')->useCache()->firstScalar();
        $this->assertEquals('cheap',$count);
        $count=$this->pdoOne->select('select catname from product_category where id_category>0')->useCache()->firstScalar();
        $this->assertEquals('cheap',$count);

        $count=$this->pdoOne->select('select catname from product_category where id_category=4')->useCache()->first();
        $this->assertEquals(['catname' =>'cheap4'],$count);
        $count=$this->pdoOne->select('select catname from product_category where id_category=4')->useCache()->first();
        $this->assertEquals(['catname' =>'cheap4'],$count);

        $count=$this->pdoOne->select('select catname from product_category')->useCache()->last();
        $this->assertEquals(['catname' => 'cheap'],$count);
        $count=$this->pdoOne->select('select catname from product_category')->useCache()->last();
        $this->assertEquals(['catname' => 'cheap'],$count);

        $count=$this->pdoOne->select('select catname from product_category')->where('id_category=?',[4])->useCache()->firstScalar();
        $this->assertEquals('cheap4',$count,'insert must value cheap4');
        $count=$this->pdoOne->select('select catname from product_category')->where('id_category=?',[4])->useCache()->firstScalar();
        $this->assertEquals('cheap4',$count,'insert must value cheap4');
        $count=$this->pdoOne->select('select catname from product_category')->where('id_category=?',[4])
                        ->order('id_category')->useCache()->firstScalar();
        $this->assertEquals('cheap4',$count,'insert must value cheap4');
        $count=$this->pdoOne->select('select catname from product_category')->where('id_category=?',[3])->useCache()->firstScalar();
        $this->assertEquals('cheap',$count,'insert must value cheap');

        $count=$this->pdoOne->select('select catname from product_category')->where('id_category=:idcat',['idcat'=>4])->firstScalar();
        $this->assertEquals('cheap4',$count,'insert must value cheap4');
        $count=$this->pdoOne->select('select catname from product_category')->where('id_category=:idcat',['idcat'=>4])->firstScalar();
        $this->assertEquals('cheap4',$count,'insert must value cheap4');
        $this->assertEquals(137
            ,$this->pdoOne->from('product_category')->sum('id_category')
            ,'sum must value 137');
        $this->assertEquals(2
            ,$this->pdoOne->from('product_category')->min('id_category')
            ,'min must value 2');
        $this->assertEquals(123
            ,$this->pdoOne->from('product_category')->max('id_category')
            ,'max must value 123');
        $rr=$this->pdoOne->from('product_category')->avg('id_category');
        $this->assertEquals(27.4
            ,$rr
            ,'avg must value 27.4',0.1);

        $this->assertEquals([['id_category' =>2],
                            ['id_category'=>3],
                            ['id_category'=>4],
                            ['id_category'=>5],
                            ['id_category'=>123]]
            ,$this->pdoOne->select('id_category')->from('product_category')->useCache()->toList());

        $this->assertEquals([['id_category' =>2],
                            ['id_category'=>3],
                            ['id_category'=>4],
                            ['id_category'=>5],
                            ['id_category'=>123]]
            ,$this->pdoOne->select('id_category')->from('product_category')->useCache()->toList());
        $this->assertEquals(6,$this->pdoOne->getCacheService()->cacheCounter); // 1= cache used 1 time

        $this->assertEquals([2, 3, 4, 5, 123]
            ,$this->pdoOne->select('id_category')->from('product_category')->useCache()->toListSimple());
        $this->assertEquals([2, 3, 4, 5, 123]
            ,$this->pdoOne->select('id_category')->from('product_category')->useCache()->toListSimple());
        $this->assertEquals([2 =>'cheap', 3 =>'cheap', '4' =>'cheap4', 5 =>'cheap', 123 =>'cheap']
            ,$this->pdoOne->select('id_category,catname')->from('product_category')->useCache()->toListKeyValue());
        $this->assertEquals([2 =>'cheap', 3 =>'cheap', '4' =>'cheap4', 5 =>'cheap', 123 =>'cheap']
            ,$this->pdoOne->select('id_category,catname')->from('product_category')->useCache()->toListKeyValue());

        $this->assertEquals(8,$this->pdoOne->getCacheService()->cacheCounter); // 3= cache used 1 time

    }
    public function test_quota(): void
    {
        $this->assertEquals('[hello] world',$this->pdoOne->addDelimiter('hello world'));
        $this->assertEquals('[hello].[world]',$this->pdoOne->addDelimiter('hello.world'));
        $this->assertEquals('[hello]=value',$this->pdoOne->addDelimiter('hello=value'));
        $this->assertEquals('[hello] =value',$this->pdoOne->addDelimiter('hello =value'));
        $this->assertEquals('20190101',PdoOne::dateConvert('01/01/2019','human','sql'));
        $this->assertEquals('42143278901651563',$this->pdoOne->getUnpredictable("12345678901234561"));
        $this->assertEquals('12345678901234561',$this->pdoOne->getUnpredictableInv("42143278901651563"));
        $this->assertNotEmpty(PdoOne::dateTextNow()); // '2020-01-25T22:17:41Z',
        $this->assertNotEmpty(PdoOne::dateSqlNow()); // '2020-01-25 22:18:32',
    }
    public function test_emptyargs(): void
    {

        $r=true;
        if($this->pdoOne->objectExist('product_category')) {
            $r = $this->pdoOne->drop('product_category', 'table');
        }
        $this->assertEquals(true,$r,"Drop failed");

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
        $this->assertEquals(true,$r,"failed to create table");
        // we add some values
        $this->pdoOne->set(['id_category' => 1, 'catname' => 'cheap'])
            ->from('product_category')->insert();
        $this->pdoOne->set(['id_category' => 2, 'catname' => 'cheap2'])
            ->from('product_category')->insert();
        $this->pdoOne->set("id_category=2,catname='cheap1'")->where("id_category=2")
            ->from('product_category')->update();


        $sr = $this->pdoOne->from('product_category')->set("catname='expensive'")->where('id_category=1')->update();

        $this->assertEquals(['id_category' =>1, 'catname' =>'expensive'],$this->pdoOne->select('select * from product_category where id_category=1')->first());
        $this->assertEquals(['id_category' =>2, 'catname' =>'cheap1'],$this->pdoOne->select('select * from product_category where id_category=2')->first());

        $this->pdoOne->runMultipleRawQuery("insert into product_category(id_category,catname) values (3,'multi');
                insert into product_category(id_category,catname) values (4,'multi'); ");
        $this->assertEquals(4,$this->pdoOne->from('product_category')->count());
        $r=$this->pdoOne->set(['id_category',1,'catname','c1'])->from('category')->insert();

        $obj=['id_category'=>2,'catname'=>'c2'];
        $r=$this->pdoOne->insertObject('category',$obj);

        $query=$this->pdoOne->select('*')->from('product_category')
            ->innerjoin('category on product_category.id_category=category.id_category')
            ->toList();

        $this->assertEquals([['id_category' =>1, 'catname' =>'c1'], ['id_category' =>2, 'catname' =>'c2']],$query);

        $this->pdoOne->delete('product_category where id_category>0');

        $this->assertEquals(0,$this->pdoOne->from('product_category')->count());

    }

	public function test_time(): void
    {
        $this->assertEquals('2019-02-06 05:06:07',PdoOne::dateText2Sql('2019-02-06T05:06:07Z',true));
		$this->assertEquals('2019-02-06 00:00:00',PdoOne::dateText2Sql('2019-02-06',false));

		$this->assertEquals('2018-02-06 05:06:07.123000',PdoOne::dateText2Sql('2018-02-06T05:06:07.123Z',true));

		// sql format -> human format dd/mm/yyyy
        $this->assertEquals('06/02/2019',PdoOne::dateSql2Text('2019-02-06'));

        // 2019-02-06T05:06:07Z -> 2019-02-06 05:06:07 -> 06/02/2019 05:06:07
		$this->assertEquals('06/02/2019 05:06:07'
            ,PdoOne::dateSql2Text(PdoOne::dateText2Sql('2019-02-06T05:06:07Z',true)));
		$this->assertEquals('06/02/2019 05:06:07',PdoOne::dateSql2Text('2019-02-06 05:06:07'));
		$this->assertEquals('06/02/2018 05:06:07.123000',PdoOne::dateSql2Text('2018-02-06 05:06:07.123'));
	}

	/**
	 * @throws Exception
	 */
	public function test_sequence(): void
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
            var_dump('exceptions:');
            var_dump($this->pdoOne->lastQuery);
			var_dump($this->pdoOne->lastError());
		}
		$this->assertLessThan(3639088446091303982,$this->pdoOne->getSequence(true),"sequence must be greater than 3639088446091303982");
	}

    public function test_kv() {
        $this->pdoOne->setKvDefaultTable('KVTABLA');
        try {
            $this->assertEquals(true, $this->pdoOne->dropTableKV());
        } catch(Exception $ex) {

        }
        $this->assertEquals(true,$this->pdoOne->createTableKV());

        $this->assertEquals(true,$this->pdoOne->setKV('hello','it is a value',2));
        $this->assertEquals('it is a value',$this->pdoOne->getKV('hello'));
        $this->assertEquals(true,$this->pdoOne->existKV('hello'));
        $this->assertEquals(true,$this->pdoOne->delKV('hello'));
        $this->assertEquals(false,$this->pdoOne->existKV('hello'));
        $this->assertEquals(true,$this->pdoOne->setKV('hello','it is a value',2));
        $this->assertEquals('it is a value',$this->pdoOne->getKV('hello'));
        $this->assertEquals('it is a value',$this->pdoOne->kv('KVTABLA')->getKV('hello'));
        sleep(3);
        $this->assertEquals(null,$this->pdoOne->getKV('hello'));
        // it never expires:
        $this->assertEquals(true,$this->pdoOne->setKV('hello2','it is a value'));
        $this->assertEquals('it is a value',$this->pdoOne->getKV('hello2'));
        $this->assertEquals(true,$this->pdoOne->existKV('hello2'));
        $this->assertEquals(true,$this->pdoOne->delKV('hello2'));

        // it never expires:
        $this->assertEquals(true,$this->pdoOne->setKV('hello2','it is a value'));
        $this->assertEquals('it is a value',$this->pdoOne->getKV('hello2'));
        $this->assertEquals(true,$this->pdoOne->existKV('hello2'));
        $this->assertEquals(true,$this->pdoOne->delKV('hello2'));
    }

	public function test_sequence2(): void
    {
		$this->assertLessThan(3639088446091303982,$this->pdoOne->getSequencePHP(false),"sequence must be greater than 3639088446091303982");
		$s1=$this->pdoOne->getSequencePHP(false);
		$s2=$this->pdoOne->getSequencePHP(false);
		$this->assertNotEquals($s1, $s2, "sequence must not be the same");
		$this->pdoOne->encryption->encPassword=1020304050;
		$s1=$this->pdoOne->getSequencePHP(true);
		$s2=$this->pdoOne->getSequencePHP(true);
		$this->assertNotEquals($s1, $s2, "sequence must not be the same");



	}
	/**
	 * @doesNotPerformAssertions
	 */
    public function test_close(): void
    {
        $this->pdoOne->close();
    }

    public function test_getMessages(): void
    {
        try {
            $this->pdoOne->getMessagesContainer();
        } catch(Exception $ex) {
            $this->assertTrue(true);
        }
    }



    public function test_startTransaction(): void
    {
        $this->assertEquals(true,$this->pdoOne->startTransaction());
        $this->pdoOne->commit();

    }

    public function test_commit(): void
    {
        $this->assertEquals(false,(false),'transaction is not open');
    }

    public function test_rollback(): void
    {
        $this->assertEquals(false,(false),'transaction is not open');
    }


    public function test_select(): void
    {
        $this->assertInstanceOf(PdoOneQuery::class,$this->pdoOne->select('select 1 from sys.routes'));
    }

	public function test_sqlGen(): void
    {
		$this->assertEquals("select 1 from sys.routes",$this->pdoOne->select('select 1 from sys.routes')->sqlGen(true));

		$this->assertEquals("select 1 from sys.routes",$this->pdoOne->select('select 1')->from('sys.routes')->sqlGen(true));

		$this->assertEquals("select 1, 2 from sys.routes",$this->pdoOne->select('1')->select('2')->from('sys.routes')->sqlGen(true));

		$this->assertEquals("select 1, 2 from sys.routes",$this->pdoOne->select(['1', '2'])->from('sys.routes')->sqlGen(true));

		$this->assertEquals("select 1, 2 from sys.routes where field=?"
			,$this->pdoOne
				->select(['1','2'])
				->from('sys.routes')
				->where('field=?',[20])
				->sqlGen(true));

        $this->assertEquals("select 1, 2 from sys.routes where field=:field"
            ,$this->pdoOne
                ->select(['1','2'])
                ->from('sys.routes')
                ->where('field=:field',['field'=>20])
                ->sqlGen(true));

		$this->assertEquals("select 1, 2 from sys.routes where field=? group by 2 having field2=? order by 1"
			,$this->pdoOne
				->select(['1','2'])
				->from('sys.routes')
				->where('field=?',[20])
				->order('1')
				->group('2')
				->having('field2=?',[4])
				->sqlGen(true));
	}

    public function test_join(): void
    {
        $this->assertInstanceOf(PdoOneQuery::class,$this->pdoOne->join('tablejoin on t1.field=t2.field'));
    }



    public function test_from(): void
    {
        $this->assertInstanceOf(PdoOneQuery::class,$this->pdoOne->from('table t1'));
    }

    public function test_left(): void
    {
        $this->assertInstanceOf(PdoOneQuery::class,$this->pdoOne->left('table2 on table1.t1=table2.t2'));
    }

    public function test_right(): void
    {
        $this->assertInstanceOf(PdoOneQuery::class,$this->pdoOne->right('table2 on table1.t1=table2.t2'));
    }

    public function test_where(): void
    {
        $this->assertInstanceOf(PdoOneQuery::class,$this->pdoOne->where('field1=?,field2=?',[20, 'hello']));
    }

    public function test_set(): void
    {
        $this->assertInstanceOf(PdoOneQuery::class,$this->pdoOne->set('field1=?,field2=?',[20, 'hello']));
    }

    public function test_group(): void
    {
        $this->assertInstanceOf(PdoOneQuery::class,$this->pdoOne->group('fieldgroup'));
    }

    public function test_having(): void
    {
        $this->assertInstanceOf(PdoOneQuery::class,$this->pdoOne->having('field1=?,field2=?',[20, 'hello']));
    }

    public function test_order(): void
    {
        $this->assertInstanceOf(PdoOneQuery::class,$this->pdoOne->order('name desc'));
    }

    public function test_limit(): void
    {
        $this->assertInstanceOf(PdoOneQuery::class,$this->pdoOne->order('c1')->limit('1,10'));
    }

    public function test_distinct(): void
    {
        $this->assertInstanceOf(PdoOneQuery::class,$this->pdoOne->distinct());
    }







    public function test_runQuery(): void
    {
        $this->assertEquals(true,$this->pdoOne->runQuery($this->pdoOne->prepare('select 1')));
        $this->assertEquals(['c' =>1],$this->pdoOne->select('1 as c')->from('sys.schemas')->first(),'it must runs');
    }


    public function test_runRawQuery(): void
    {
        $this->assertEquals([0 =>['c' =>1]],$this->pdoOne->runRawQuery('select 1 as c',null,true));
    }

	/**
	 * @throws Exception
	 */
    public function test_setEncryption(): void
    {
        $this->pdoOne->setEncryption('123//*/*saass11___1212fgbl@#€€"','123//*/*saass11___1212fgbl@#€€"','AES-256-CTR');
        $value=$this->pdoOne->encrypt("bv lfg+hlc ,vc´,c35'ddl ld_vcvñvc +*=/\\");
        $this->assertTrue(strlen($value)>10,"Encrypted");
        $return=$this->pdoOne->decrypt($value);
	    $this->assertEquals("bv lfg+hlc ,vc´,c35'ddl ld_vcvñvc +*=/\\",$return,"decrypt correct");

	    $return=$this->pdoOne->decrypt("wrong".$value);
	    $this->assertEquals(false,$return,"decrypt must fail");
	    $return=$this->pdoOne->decrypt("");
	    $this->assertEquals(false,$return,"decrypt must fail");
	    $return=$this->pdoOne->decrypt(null);
	    $this->assertEquals(false,$return,"decrypt must fail");
	    // iv =true
	    $value1=$this->pdoOne->encrypt("abc");
	    $value2=$this->pdoOne->encrypt("abc");
	    $this->assertNotEquals($value1, $value2, "Values must be different");
	    // iv =true
	    $this->pdoOne->encryption->iv=false;
	    $value1=$this->pdoOne->encrypt("abc_ABC/abc*abc1234567890[]{[");
	    $value2=$this->pdoOne->encrypt("abc_ABC/abc*abc1234567890[]{[");
	    $this->assertEquals($value1, $value2, "Values must be equals");
    }
	/**
	 * @throws Exception
	 */
	public function test_setEncryptionINTEGER(): void
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
	public function test_setEncryptionSIMPLE(): void
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
