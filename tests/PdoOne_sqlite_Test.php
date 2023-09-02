<?php /** @noinspection UnnecessaryAssertionInspection */
/** @noinspection ForgottenDebugOutputInspection */
/** @noinspection PhpMultipleClassDeclarationsInspection */
/** @noinspection PhpUnusedLocalVariableInspection */
/** @noinspection UnknownInspectionInspection */
/** @noinspection SqlResolve */
/** @noinspection PhpPossiblePolymorphicInvocationInspection */
/** @noinspection SuspiciousAssignmentsInspection */
/** @noinspection PhpUndefinedClassInspection */
/** @noinspection PhpUnhandledExceptionInspection */
/** @noinspection SqlNoDataSourceInspection */
/** @noinspection SqlDialectInspection */

namespace eftec\tests;

use DateTime;
use eftec\IPdoOneCache;
use eftec\MessageContainer;
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
    public function setCache($uid, $family = '', $data = null, $ttl = null): void
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
    public function invalidateCache($uid = '', $family = ''): void
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


    public function setUp(): void
    {
        $file=__DIR__.'/../examples/example.sqlite';
        $this->pdoOne = new PdoOne('sqlite', $file);
        $this->pdoOne->logLevel = 3;
        $this->pdoOne->connect();

        $cache = new CacheServicesmysql();
        $this->pdoOne->setCacheService($cache);
    }
    public function test_pdo():void
    {
        $x1=memory_get_usage();
        var_dump($x1);
        $this->pdoOne->prefixTable = 'table_';
        if (!$this->pdoOne->tableExist('city')) {
            $this->pdoOne->createTable('city',
                ['id' => 'INTEGER PRIMARY KEY AUTOINCREMENT', 'name' => 'varchar(45)']);
        } else {
            $r = $this->pdoOne->truncate('city');
        }
        $this->pdoOne->logLevel=0;
        for($i=1;$i<1000;$i++) {
            $id = $this->pdoOne->insert('city', ['id' => $i, 'name' => 'city'.$i]);
            $this->assertTrue($id > 0);
        }
        var_dump($this->pdoOne);
        //$this->pdoOne->conn1->setAttribute(\PDO::MYSQL_ATTR_USE_BUFFERED_QUERY,false);
        $result=$this->pdoOne
            ->select('select * from table_city')
            ->fetchLoop(static function($row) {return($row);},\PDO::FETCH_ASSOC);
       // var_dump($result);
        $x2=memory_get_usage();
        var_dump($x2);
        var_dump($x2-$x1);
        /*
         $this->pdoOne->select('select * from table_city')->toPdoStatement();
          while ($row=$stat->fetch(\PDO::FETCH_NUM)) {
            var_dump($row);
        }*/
    }
}
