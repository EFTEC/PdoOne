<?php /** @noinspection PhpIllegalPsrClassPathInspection */
/** @noinspection UnnecessaryAssertionInspection */
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

use eftec\IPdoOneCache;
use eftec\PdoOne;
use eftec\tests\sakila2021\ActorRepo;
use eftec\tests\sakila2021\ActorRepoRepo;
use eftec\tests\sakila2021\CityRepo;
use eftec\tests\sakila2021\StaffRepo;
use eftec\tests\sakila2021\StoreRepo;
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
     * @param string       $uid
     * @param string|array $family
     * @param null         $data
     * @param null         $ttl
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
                if (isset($this->cacheDataFamily[$fam])) {
                    foreach ($this->cacheDataFamily[$fam] as $id) {
                        unset($this->cacheData[$id]);
                        echo "deleting cache $id\n";
                    }
                }
                $this->cacheDataFamily[$fam] = [];
            }
        }
        //unset($this->cacheData[$uid]);
    }
}


class PdoOne_mysql_sakila2021_Test extends TestCase
{
    /** @var PdoOne */
    protected $pdoOne;


    public function setUp(): void
    {
        $this->pdoOne = new PdoOne('mysql', '127.0.0.1', 'root', 'abc.123', 'sakila_lite');
        $this->pdoOne->connect();
        $this->pdoOne->logLevel = 3;
        $cache = new CacheServicesmysql();
        $this->pdoOne->setCacheService($cache);
    }

    public function testError(): void
    {
        $cr = CityRepo::factory();
        $this->assertEquals(false, CityRepo::setFalseOnError()->insert($cr));
        $this->assertEquals('', CityRepo::base()->lastError());
        $this->assertNotEquals('', CityRepo::base()->errorText);
    }

    public function testList(): void
    {
        $this->assertCount(7, StoreRepo::toList());
    }

    public function testFirst(): void
    {
        $this->assertEquals(['store_id' => 1,
            'manager_staff_id' => 1,
            'address_id' => 1,
            'last_update' => '2006-02-15 04:57:12.000'], StoreRepo::first(1));
    }

    public function testFirst2(): void
    {
        //$f=StaffRepo::first(1);
        //var_export($f,false);
        $this->assertEquals([
            'staff_id' => 1,
            'first_name' => 'Mike',
            'last_name' => 'Hillyer',
            'address_id' => 3,
            'picture' => NULL,
            'email' => 'Mike.Hillyer@sakilastaff.com',
            'store_id' => 1,
            'active' => 1,
            'username' => 'Mike',
            'password' => '8cb2237d0679ca88db6464eac60da96345513964',
            'last_update' => '2006-02-15 04:57:16.000',
        ], StaffRepo::first(1));
        $this->assertEquals([
            'staff_id' => 1,
            'first_name' => 'Mike',
            'last_name' => 'Hillyer',
            'address_id' => 3,
            'picture' => NULL,
            'email' => 'Mike.Hillyer@sakilastaff.com',
            'store_id' => 1,
            'active' => 1,
            'username' => 'Mike',
            'password' => '8cb2237d0679ca88db6464eac60da96345513964',
            'last_update' => '2006-02-15 04:57:16.000',
            '_store_id' => [
                'store_id' => 1,
                'manager_staff_id' => 1,
                'address_id' => 1,
                'last_update' => '2006-02-15 04:57:12.000'
            ]
        ], StaffRepo::recursive(['/_store_id'])->first(1));
    }

    public function testFirst3(): void
    {
        //$f=StaffRepo::first(1);
        //var_export($f,false);
        $this->assertEquals([
            'staff_id' => 1,
            'first_name' => 'Mike',
            'last_name' => 'Hillyer',
            'address_id' => 3,
            'picture' => NULL,
            'email' => 'Mike.Hillyer@sakilastaff.com',
            'store_id' => 1,
            'active' => 1,
            'username' => 'Mike',
            'password' => '8cb2237d0679ca88db6464eac60da96345513964',
            'last_update' => '2006-02-15 04:57:16.000',
        ], StaffRepo::first(1));
        $this->assertEquals([
            'staff_id' => 1,
            'first_name' => 'Mike',
            'last_name' => 'Hillyer',
            'address_id' => 3,
            'picture' => NULL,
            'email' => 'Mike.Hillyer@sakilastaff.com',
            'store_id' => 1,
            'active' => 1,
            'username' => 'Mike',
            'password' => '8cb2237d0679ca88db6464eac60da96345513964',
            'last_update' => '2006-02-15 04:57:16.000',
            '_store_id' => [
                'store_id' => 1,
                'manager_staff_id' => 1,
                'address_id' => 1,
                'last_update' => '2006-02-15 04:57:12.000'
            ]
        ], StaffRepo::recursive(['/_store_id'])->first(1));
    }

    public function testDeleteInsert(): void
    {
        $num = CityRepo::count();
        $this->assertGreaterThan(1, $num);
        $x = CityRepo::factory(['city' => 'xxx', 'country_id' => 6, 'last_update' => '2019-01-01']);
        //try {
        //var_dump($x);
        CityRepo::resetIdentity();
        $id1 = CityRepo::insert($x);
        //var_dump($x);
        $num2 = CityRepo::count();
        $this->assertEquals($num + 1, $num2);
        //var_dump($x);
        $this->assertGreaterThan(600, $x['city_id']);
        $this->assertNotFalse(CityRepo::deleteById($x['city_id']));
        //var_dump($x);
        CityRepo::resetIdentity();
        $id2 = CityRepo::insert($x);
        $this->assertEquals($id1, $id2);
        //var_dump($x);
        $this->assertNotFalse(CityRepo::delete($x));
        $num3 = CityRepo::count();
        $this->assertEquals($num, $num3);
        /* } catch(Exception $ex) {
             var_dump($ex->getMessage());
             CityRepo::base()->lastError();
             CityRepo::base()->errorText;
         }*/
    }

    public function testUpdate(): void
    {
        $original = CityRepo::first(1);
        $this->assertEquals(['city_id' => 1,
            'city' => 'A Corua (La Corua)',
            'country_id' => 87,
            'last_update' => '2006-02-15 04:45:25.000'], $original);
        $copy = $original;
        $copy['city'] = 'Modified';
        $this->assertNotFalse(CityRepo::update($copy));
        $read = CityRepo::first(1);
        $this->assertEquals(['city_id' => 1,
            'city' => 'Modified',
            'country_id' => 87,
            'last_update' => '2006-02-15 04:45:25.000'], $read);
        //$original['city']='A Corua (La Corua)';
        $this->assertNotFalse(CityRepo::update($original));
        $read = CityRepo::first(1);
        $this->assertEquals(['city_id' => 1,
            'city' => 'A Corua (La Corua)',
            'country_id' => 87,
            'last_update' => '2006-02-15 04:45:25.000'], $read);
    }

    public function testUpdateRecursive(): void
    {
        $expected=['city_id' => 1,
            'city' => 'A Corua (La Corua)',
            'country_id' => 87,
            'last_update' => '2006-02-15 04:45:25.000',
            '_country_id' => ['country_id' => 87,
                'country' => 'Spain',
                'last_update' => '2006-02-15 04:44:00.000']];
        $expected_modified=['city_id' => 1,
            'city' => 'Modified',
            'country_id' => 87,
            'last_update' => '2006-02-15 04:45:25.000',
            '_country_id' => ['country_id' => 87,
                'country' => 'Modified',
                'last_update' => '2006-02-15 04:44:00.000']];
        $original = CityRepo::recursive(['/_country_id'])->first(1);
        $this->assertEquals($expected, $original);
        /** @var array $copy=CityRepo::factoryUtil(); */
        //$copy=CityRepo::factoryUtil();
        $copy=$original;
        $copy['city']='Modified';
        $copy['_country_id']['country']='Modified';
        $this->assertNotFalse(CityRepo::recursive(['/_country_id'])->update($copy));

        $read=CityRepo::recursive(['/_country_id'])->first(1);
        $this->assertEquals($expected_modified, $read);
        $copy['city']='A Corua (La Corua)';
        $copy['_country_id']['country']='Spain';
        $this->assertNotFalse(CityRepo::recursive(['/_country_id'])->update($copy));

        /*$copy=$original;
        $copy['city']='Modified';
        $this->assertNotFalse(CityRepo::update($copy));
        $read= CityRepo::first(1);
        $this->assertEquals(['city_id' => 1,
            'city' => 'Modified',
            'country_id' => 87,
            'last_update' => '2006-02-15 04:45:25.000'], $read);
        //$original['city']='A Corua (La Corua)';
        $this->assertNotFalse(CityRepo::update($original));
        $read= CityRepo::first(1);
        $this->assertEquals(['city_id' => 1,
            'city' => 'A Corua (La Corua)',
            'country_id' => 87,
            'last_update' => '2006-02-15 04:45:25.000'], $read);*/
    }
    public function testValida():void
    {
        $a1=ActorRepo::factory();
        $this->assertEquals(false,ActorRepo::validateModel($a1));
        $this->assertEquals('field actor_id must not be null',ActorRepo::base()->errorText);
        $a1['actor_id']=22;
        $this->assertEquals(false,ActorRepo::validateModel($a1));
        $this->assertEquals('field first_name is not a string',ActorRepo::base()->errorText);
        $a1['first_name']='john';
        $a1['last_name']='doe';
        $a1['last_update']='2020-01-01';
        $this->assertEquals(true,ActorRepo::validateModel($a1));
        $this->assertEquals('',ActorRepo::base()->errorText);

    }
}
