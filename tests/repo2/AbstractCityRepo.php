<?php
/** @noinspection PhpUnusedParameterInspection
* @noinspection PhpClassConstantAccessedViaChildClassInspection
* @noinspection PhpClasspublic constantAccessedViaChildClassInspection
* @noinspection NullCoalescingOperatorCanBeUsedInspection
* @noinspection PhpPureAttributeCanBeAddedInspection
* @noinspection PhpArrayShapeAttributeCanBeAddedInspection
* @noinspection PhpMissingParamTypeInspection
* @noinspection AccessModifierPresentedInspection
* @noinspection PhpMissingReturnTypeInspection
* @noinspection UnknownInspectionInspection
* @noinspection PhpIncompatibleReturnTypeInspection
* @noinspection ReturnTypeCanBeDeclaredInspection
* @noinspection DuplicatedCode
* @noinspection PhpUnused
* @noinspection PhpUndefinedMethodInspection
* @noinspection PhpUnusedLocalVariableInspection
* @noinspection PhpUnusedAliasInspection
* @noinspection NullPointerExceptionInspection
* @noinspection SenselessProxyMethodInspection
* @noinspection PhpParameterByRefIsNotUsedAsReferenceInspection
*/
namespace eftec\examples\clitest\repo2;
use eftec\PdoOne;
use eftec\PdoOneQuery;

use Exception;

/**
* Class AbstractCityRepo. Copyright (c) Jorge Castro C. (https://github.com/EFTEC/PdoOne)<br>
* Generated by PdoOne Version 2.27 Date generated Mon, 28 Feb 2022 00:10:18 -0300.<br>
* <b>DO NOT EDIT THIS CODE</b>. This code is generated<br>
* If you want to make some changes, then add the changes to the Repository class.<br>
* <pre>
* $code=$pdoOne->generateCodeClass('city','eftec\examples\clitest\repo2',array('city'=>NULL,'city_id'=>NULL,'country_id'=>NULL,'last_update'=>NULL,'_country_id'=>'MANYTOONE','_address'=>'ONETOMANY','xxxx'=>'new',),array('actor'=>'ActorRepo','actor2'=>'Actor2Repo','address'=>'AddresRepo','category'=>'CategoryRepo','city'=>'CityRepo','country'=>'CountryRepo','customer'=>'CustomerRepo','dummyt'=>'DummytRepo','dummytable'=>'DummytableRepo','film'=>'FilmRepo','film2'=>'Film2Repo','film_actor'=>'FilmActorRepo','film_category'=>'FilmCategoryRepo','film_text'=>'FilmTextRepo','fum_jobs'=>'FumJobRepo','fum_logs'=>'FumLogRepo','inventory'=>'InventoryRepo','language'=>'LanguageRepo','mysec_table'=>'MysecTableRepo','payment'=>'PaymentRepo','product'=>'ProductRepo','producttype'=>'ProducttypeRepo','producttype_auto'=>'ProducttypeAutoRepo','rental'=>'RentalRepo','staff'=>'StaffRepo','store'=>'StoreRepo','tablachild'=>'TablachildRepo','tablagrandchild'=>'TablagrandchildRepo','tablaparent'=>'TablaparentRepo','tabletest'=>'TabletestRepo','test_products'=>'TestProductRepo','typetable'=>'TypetableRepo',),array(),'','','Sakila','',array(),array());
* </pre>
*/
abstract class AbstractCityRepo extends Sakila
{
    public const TABLE = 'city';
    public const IDENTITY = 'city_id';
    public const PK = [
	    'city_id'
	];
    public const ME=__CLASS__;
    public const EXTRACOLS='';
    /** @var string|null $schema you can set the current schema/database used by this class. [Default is null] */
    public static $schema;

    /**
    * It returns the definitions of the columns<br>
    * <b>Example:</b><br>
    * <pre>
         * self::getDef(); // ['colName'=>[php type,php conversion type,type,size,nullable,extra,sql],'colName2'=>..]
         * self::getDef('sql'); // ['colName'=>'sql','colname2'=>'sql2']
         * self::getDef('identity',true); // it returns the columns that are identities ['col1','col2']
         * </pre>
    * <b>PHP Types</b>: binary, date, datetime, decimal/float,int, string,time, timestamp<br>
    * <b>PHP Conversions</b>:  datetime (datetime class), datetime2 (iso),datetime3 (human string)
    *                         , datetime4 (sql no conversion!), timestamp (int), bool, int, float<br>
    * <b>Param Types</b>: PDO::PARAM_LOB, PDO::PARAM_STR, PDO::PARAM_INT<br>
    *
    * @param string|null $column =['phptype','conversion','type','size','null','identity','sql'][$i]
    *                             if not null then it only returns the column specified.
    * @param string|null $filter If filter is not null, then it uses the column to filter the result.
    *
    * @return array|array[]
    */
    public static function getDef($column = null, $filter = null): array
    {
        $r = [
		    'city_id' => [
		        'phptype' => 'int',
		        'conversion' => NULL,
		        'type' => 'smallint',
		        'size' => NULL,
		        'null' => FALSE,
		        'identity' => TRUE,
		        'sql' => 'smallint unsigned not null auto_increment'
		    ],
		    'city' => [
		        'phptype' => 'string',
		        'conversion' => NULL,
		        'type' => 'varchar',
		        'size' => '50',
		        'null' => FALSE,
		        'identity' => FALSE,
		        'sql' => 'varchar(50) not null'
		    ],
		    'country_id' => [
		        'phptype' => 'int',
		        'conversion' => NULL,
		        'type' => 'smallint',
		        'size' => NULL,
		        'null' => FALSE,
		        'identity' => FALSE,
		        'sql' => 'smallint unsigned not null'
		    ],
		    'last_update' => [
		        'phptype' => 'timestamp',
		        'conversion' => NULL,
		        'type' => 'timestamp',
		        'size' => NULL,
		        'null' => FALSE,
		        'identity' => FALSE,
		        'sql' => 'timestamp not null default CURRENT_TIMESTAMP on update CURRENT_TIMESTAMP'
		    ]
		];
        if ($column !== null) {
            if ($filter === null) {
                foreach ($r as $k => $v) {
                    $r[$k] = $v[$column];
                }
            } else {
                $new = [];
                foreach ($r as $k => $v) {
                    if ($v[$column] === $filter) {
                        $new[] = $k;
                    }
                }
                return $new;
            }
        }
        return $r;
    }

    /**
    * It converts a row returned from the database.<br>
    * If the column is missing then it sets the field as null.
    *
    * @param array $row [ref]
    */
    public static function convertOutputVal(&$row)
    {
        if ($row === false || $row === null) {
            return;
        }
        		!isset($row['city_id']) and $row['city_id']=null; // smallint
		!isset($row['city']) and $row['city']=null; // varchar
		!isset($row['country_id']) and $row['country_id']=null; // smallint
		!isset($row['last_update']) and $row['last_update']=null; // timestamp
        		isset($row['_country_id'])
            and $row['_country_id']['country_id']=&$row['country_id']; // linked MANYTOONE

    }

    /**
    * It converts a row to be inserted or updated into the database.<br>
    * If the column is missing then it is ignored and not converted.
    *
    * @param array $row [ref]
    */
    public static function convertInputVal(&$row) {
        
    }


    /**
    * It gets all the name of the columns.
    *
    * @return string[]
    */
    public static function getDefName() {
        return [
		    'city_id',
		    'city',
		    'country_id',
		    'last_update'
		];
    }

    /**
    * It returns an associative array (colname=>key type) with all the keys/indexes (if any)
    *
    * @return string[]
    */
    public static function getDefKey() {
        return [
		    'city_id' => 'PRIMARY KEY',
		    'country_id' => 'KEY'
		];
    }

    /**
    * It returns a string array with the name of the columns that are skipped when insert
    * @return string[]
    */
    public static function getDefNoInsert() {
        return [
		    'city_id'
		];
    }

    /**
    * It returns a string array with the name of the columns that are skipped when update
    * @return string[]
    */
    public static function getDefNoUpdate() {
        return [
		    'city_id'
		];
    }

    /**
    * It adds a where to the query pipeline. It could be stacked with many where()
    * <b>Example:</b><br>
    * <pre>
         * self::where(['col'=>'value'])::toList();
         * self::where(['col']=>['value'])::toList(); // s= string/double/date, i=integer, b=bool
         * self::where(['col=?']=>['value'])::toList(); // s= string/double/date, i=integer, b=bool
         * </pre>
    *
    * @param array|string   $sql =self::factory()
    * @param null|array|int $param
    *
    * @return PdoOneQuery
    */
    public static function where($sql, $param = PdoOne::NULL)
    {
        return static::newQuery()->where($sql, $param,false,CityRepo::TABLE);
    }

    public static function getDefFK($structure=false) {
        if ($structure) {
            return [
			    'country_id' => 'FOREIGN KEY REFERENCES`country`(`country_id`) ON UPDATE CASCADE'
			];
        }
        /* key,refcol,reftable,extra */
        return [
		    'country_id' => [
		        'key' => 'FOREIGN KEY',
		        'refcol' => 'country_id',
		        'reftable' => 'country',
		        'extra' => ' ON UPDATE CASCADE',
		        'name' => 'fk_city_country'
		    ],
		    '_country_id' => [
		        'key' => 'MANYTOONE',
		        'refcol' => 'country_id',
		        'reftable' => 'country',
		        'extra' => ' ON UPDATE CASCADE',
		        'name' => 'fk_city_country'
		    ],
		    '_address' => [
		        'key' => 'ONETOMANY',
		        'col' => 'city_id',
		        'reftable' => 'address',
		        'refcol' => '_city_id'
		    ]
		];
    }

    /**
    * It returns all the relational fields by type. '*' returns all types.<br>
    * It doesn't return normal columns.
    *
    * @param string $type=['*','MANYTOONE','ONETOMANY','ONETOONE','MANYTOMANY'][$i]
    *
    * @return string[]
    * @noinspection SlowArrayOperationsInLoopInspection
    */
    public static function getRelations($type = 'all')
    {
        $r = [
		    'MANYTOONE' => [
		        '_country_id'
		    ],
		    'ONETOMANY' => [
		        '_address'
		    ]
		];
        if ($type === '*') {
            $result = [];
            foreach ($r as $arr) {
                $result = array_merge($result, $arr);
            }
            return $result;
        }
        return $r[$type] ?? [];
    }

    /**
    * @param array|int  $filter      (optional) if we want to filter the results.
    * @param array|null $filterValue (optional) the values of the filter
    * @return array|bool|null
    * @throws Exception
    */
    public static function toList($filter=PdoOne::NULL,$filterValue=null) {
        if(self::$useModel) {
            return false; // no model set
        }
        return self::_toList($filter, $filterValue);
    }

    /**
    * It sets the recursivity. By default, if we query or modify a value, it operates with the fields of the entity.
    * With recursivity, we could use the recursivity of the fields, for example, loading a MANYTOONE relation<br>
    * <b>Example:</b><br>
    * <pre>
         * self::setRecursive([]); // (default) no use recursivity.
         * self::setRecursive('*'); // recursive every MANYTOONE,ONETOONE,MANYTOONE and ONETOONE relations (first level)
         * self::setRecursive('MANYTOONE'); // recursive all relations of the type MANYTOONE (first level)
         * self::setRecursive(['_relation1','_relation2']); // recursive only the relations of the first level
         * self::setRecursive(['_relation1','_relation1/_subrelation1']); //recursive the relations (first and second level)
         * </pre>
    * If array then it uses the values to set the recursivity.<br>
    * If string then the values allowed are '*', 'MANYTOONE','ONETOMANY','MANYTOMANY','ONETOONE' (first level only)<br>
    *
    * @param string|array $recursive=self::factory();
    *
    * @return PdoOneQuery
    */
    public static function setRecursive($recursive=[])
    {
        if(is_string($recursive)) {
            $recursive=CityRepo::getRelations($recursive);
        }
        return parent::_setRecursive($recursive);
    }

    /**
    * It adds an "limit" in a query. It depends on the type of database<br>
    * <b>Example:</b><br>
    * <pre>
         *      ->select("")->limit("10,20")->toList();
         * </pre>
    *
    * @param string $sql Input SQL query
    *
    * @return PdoOneQuery
    * @throws Exception
    * @test InstanceOf PdoOne::class,this('1,10')
    */
    public static function limit($sql) : PdoOneQuery
    {
        return static::newQuery()->limit($sql);
    }

    /**
    * It returns the first row of a query.<br>
    * <b>Example:</b><br>
    * <pre>
         * Repo::first(); // it returns the first value encountered.
         * Repo::first(2); // it returns the first value where the primary key is equals to 2 (simple primary key)
         * Repo::first([2,3]); // it returns the first value where the primary key is equals to 2 (multiple primary keys)
         * Repo::first(['id'=>2,'id2'=>3]); // it returns the first value where id=2 and id2=3 (multiple primary keys)
         * </pre>
    * @param array|mixed|null $pk [optional] Specify the value of the primary key.
    *
    * @return array|bool It returns false if not file is found.
    * @throws Exception
    */
    public static function first($pk = PdoOne::NULL) {
        if(self::$useModel) {
            /** @noinspection PhpIncompatibleReturnTypeInspection */
            return false; // no model set
        }
        return self::_first($pk);
    }

    /**
    *  It returns true if the entity exists, otherwise false.<br>
    *  <b>Example:</b><br>
    *  <pre>
         *  $this->exist(['id'=>'a1','name'=>'name']); // using an array
         *  $this->exist('a1'); // using the primary key. The table needs a pks and it only works with the first pk.
         *  </pre>
    *
    * @param array|mixed $entity =self::factory()
    *
    * @return bool true if the pks exists
    * @throws Exception
    */
    public static function exist($entity) {
        return self::_exist($entity);
    }

    /**
    * It inserts a new entity(row) into the database<br>
    * @param array|object $entity        =self::factory()
    * @param bool         $transactional If true (default) then the operation is transactional
    *
    * @return array|false=self::factory()
    * @throws Exception
    */
    public static function insert(&$entity,$transactional=true) {
        return self::_insert($entity,$transactional);
    }

    /**
    * It merge a new entity(row) into the database. If the entity exists then it is updated, otherwise the entity is
    * inserted<br>
    * @param array|object $entity        =self::factory()
    * @param bool         $transactional If true (default) then the operation is transactional
    *
    * @return array|false=self::factory()
    * @throws Exception
    */
    public static function merge(&$entity,$transactional=true) {
        return self::_merge($entity,$transactional);
    }

    /**
    * @param array|object $entity        =self::factory()
    * @param bool         $transactional If true (default) then the operation is transactional
    *
    * @return false|int=self::factory()
    * @throws Exception
    */
    public static function update($entity,$transactional=true) {
        return self::_update($entity,$transactional);
    }

    /**
    * It deletes an entity by the primary key
    *
    * @param array|object $entity =self::factory()
    * @param bool         $transactional If true (default) then the operation is transactional
    *
    * @return false|int
    * @throws Exception
    */
    public static function delete($entity,$transactional=true) {
        return self::_delete($entity,$transactional);
    }

    /**
    * It deletes an entity by the primary key.
    *
    * @param array|mixed $pk =self::factory()
    * @param bool        $transactional If true (default) then the operation is transactional
    *
    * @return int|false
    * @throws Exception
    */
    public static function deleteById($pk,$transactional=true) {
        return self::_deleteById($pk,$transactional);
    }

    /**
    * Returns an array with the default values (0 for numbers, empty for string, and array|null if recursive)
    *
    * @param array|null $values          =self::factory()
    * @param string     $recursivePrefix It is the prefix of the recursivity.
    *
    * @return array
    */
    public static function factory($values = null, $recursivePrefix = '') {
        $recursive=static::getRecursive();
        static::setRecursive(); // reset the recursivity.
        $row= [
		'city_id'=>0,
		'_address'=>(in_array($recursivePrefix.'_address',$recursive,true))
		                            ? [] 
		                            : null, /* ONETOMANY! */
		'city'=>'',
		'country_id'=>0,
		'_country_id'=>(in_array($recursivePrefix.'_country_id',$recursive,true)) 
		                            ? CountryRepo::factory(null,$recursivePrefix.'_country_id') 
		                            : null, /* MANYTOONE!! */
		'last_update'=>''
		];
        		isset($row['_country_id'])
            and $row['_country_id']['country_id']=&$row['country_id']; // linked MANYTOONE

        if ($values !== null) {
            $row = array_merge($row, $values);
        }
        return $row;
    }

    /**
    * It returns an empty array with null values and no recursivity.
    * @param array|null $values=self::factoryNull()
    *
    * @return array
    */
    public static function factoryNull($values=null) {
        $row= [
		'city_id'=>null,
		'_address'=>null, /* ONETOMANY! */
		'city'=>null,
		'country_id'=>null,
		'_country_id'=>null, /* MANYTOONE!! */
		'last_update'=>null
		];
        if ($values !== null) {
            $row = array_merge($row, $values);
        }
        return $row;
    }
}

