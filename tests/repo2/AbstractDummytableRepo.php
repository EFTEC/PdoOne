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
* Class AbstractDummytableRepo. Copyright (c) Jorge Castro C. (https://github.com/EFTEC/PdoOne)<br>
* Generated by PdoOne Version 2.27 Date generated Mon, 28 Feb 2022 00:10:18 -0300.<br>
* <b>DO NOT EDIT THIS CODE</b>. This code is generated<br>
* If you want to make some changes, then add the changes to the Repository class.<br>
* <pre>
* $code=$pdoOne->generateCodeClass('dummytable','eftec\examples\clitest\repo2',array('dummytablecol'=>NULL,'dummytablecol1'=>NULL,'dummytablecol10'=>NULL,'dummytablecol11'=>NULL,'dummytablecol12'=>NULL,'dummytablecol13'=>NULL,'dummytablecol14'=>NULL,'dummytablecol15'=>NULL,'dummytablecol16'=>NULL,'dummytablecol17'=>NULL,'dummytablecol2'=>NULL,'dummytablecol3'=>NULL,'dummytablecol4'=>NULL,'dummytablecol5'=>NULL,'dummytablecol6'=>NULL,'dummytablecol7'=>NULL,'dummytablecol8'=>NULL,'dummytablecol9'=>NULL,'iddummytable'=>NULL,),array('actor'=>'ActorRepo','actor2'=>'Actor2Repo','address'=>'AddresRepo','category'=>'CategoryRepo','city'=>'CityRepo','country'=>'CountryRepo','customer'=>'CustomerRepo','dummyt'=>'DummytRepo','dummytable'=>'DummytableRepo','film'=>'FilmRepo','film2'=>'Film2Repo','film_actor'=>'FilmActorRepo','film_category'=>'FilmCategoryRepo','film_text'=>'FilmTextRepo','fum_jobs'=>'FumJobRepo','fum_logs'=>'FumLogRepo','inventory'=>'InventoryRepo','language'=>'LanguageRepo','mysec_table'=>'MysecTableRepo','payment'=>'PaymentRepo','product'=>'ProductRepo','producttype'=>'ProducttypeRepo','producttype_auto'=>'ProducttypeAutoRepo','rental'=>'RentalRepo','staff'=>'StaffRepo','store'=>'StoreRepo','tablachild'=>'TablachildRepo','tablagrandchild'=>'TablagrandchildRepo','tablaparent'=>'TablaparentRepo','tabletest'=>'TabletestRepo','test_products'=>'TestProductRepo','typetable'=>'TypetableRepo',),array(),'','','Sakila','',array(),array());
* </pre>
*/
abstract class AbstractDummytableRepo extends Sakila
{
    public const TABLE = 'dummytable';
    public const IDENTITY = NULL;
    public const PK = [
	    'iddummytable'
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
		    'iddummytable' => [
		        'phptype' => 'int',
		        'conversion' => NULL,
		        'type' => 'int',
		        'size' => NULL,
		        'null' => FALSE,
		        'identity' => FALSE,
		        'sql' => 'int not null'
		    ],
		    'dummytablecol' => [
		        'phptype' => 'string',
		        'conversion' => NULL,
		        'type' => 'varchar',
		        'size' => '45',
		        'null' => TRUE,
		        'identity' => FALSE,
		        'sql' => 'varchar(45)'
		    ],
		    'dummytablecol1' => [
		        'phptype' => 'string',
		        'conversion' => NULL,
		        'type' => 'char',
		        'size' => '40',
		        'null' => TRUE,
		        'identity' => FALSE,
		        'sql' => 'char(40)'
		    ],
		    'dummytablecol2' => [
		        'phptype' => 'datetime',
		        'conversion' => NULL,
		        'type' => 'datetime',
		        'size' => NULL,
		        'null' => TRUE,
		        'identity' => FALSE,
		        'sql' => 'datetime'
		    ],
		    'dummytablecol3' => [
		        'phptype' => 'float',
		        'conversion' => NULL,
		        'type' => 'decimal',
		        'size' => '9,3',
		        'null' => TRUE,
		        'identity' => FALSE,
		        'sql' => 'decimal(9,3)'
		    ],
		    'dummytablecol4' => [
		        'phptype' => 'binary',
		        'conversion' => NULL,
		        'type' => 'blob',
		        'size' => NULL,
		        'null' => TRUE,
		        'identity' => FALSE,
		        'sql' => 'blob'
		    ],
		    'dummytablecol5' => [
		        'phptype' => 'date',
		        'conversion' => NULL,
		        'type' => 'date',
		        'size' => NULL,
		        'null' => TRUE,
		        'identity' => FALSE,
		        'sql' => 'date'
		    ],
		    'dummytablecol6' => [
		        'phptype' => 'timestamp',
		        'conversion' => NULL,
		        'type' => 'timestamp',
		        'size' => NULL,
		        'null' => TRUE,
		        'identity' => FALSE,
		        'sql' => 'timestamp'
		    ],
		    'dummytablecol7' => [
		        'phptype' => 'binary',
		        'conversion' => NULL,
		        'type' => 'longblob',
		        'size' => NULL,
		        'null' => TRUE,
		        'identity' => FALSE,
		        'sql' => 'longblob'
		    ],
		    'dummytablecol8' => [
		        'phptype' => 'string',
		        'conversion' => NULL,
		        'type' => 'char',
		        'size' => '55',
		        'null' => TRUE,
		        'identity' => FALSE,
		        'sql' => 'char(55)'
		    ],
		    'dummytablecol9' => [
		        'phptype' => 'string',
		        'conversion' => NULL,
		        'type' => 'char',
		        'size' => '55',
		        'null' => TRUE,
		        'identity' => FALSE,
		        'sql' => 'char(55)'
		    ],
		    'dummytablecol10' => [
		        'phptype' => 'int',
		        'conversion' => NULL,
		        'type' => 'tinyint',
		        'size' => NULL,
		        'null' => TRUE,
		        'identity' => FALSE,
		        'sql' => 'tinyint'
		    ],
		    'dummytablecol11' => [
		        'phptype' => 'string',
		        'conversion' => NULL,
		        'type' => 'varchar',
		        'size' => '45',
		        'null' => TRUE,
		        'identity' => FALSE,
		        'sql' => 'varchar(45)'
		    ],
		    'dummytablecol12' => [
		        'phptype' => 'string',
		        'conversion' => NULL,
		        'type' => 'geometry',
		        'size' => NULL,
		        'null' => TRUE,
		        'identity' => FALSE,
		        'sql' => 'geometry'
		    ],
		    'dummytablecol13' => [
		        'phptype' => 'string',
		        'conversion' => NULL,
		        'type' => 'json',
		        'size' => NULL,
		        'null' => TRUE,
		        'identity' => FALSE,
		        'sql' => 'json'
		    ],
		    'dummytablecol14' => [
		        'phptype' => 'float',
		        'conversion' => NULL,
		        'type' => 'double',
		        'size' => NULL,
		        'null' => TRUE,
		        'identity' => FALSE,
		        'sql' => 'double'
		    ],
		    'dummytablecol15' => [
		        'phptype' => 'float',
		        'conversion' => NULL,
		        'type' => 'float',
		        'size' => NULL,
		        'null' => TRUE,
		        'identity' => FALSE,
		        'sql' => 'float'
		    ],
		    'dummytablecol16' => [
		        'phptype' => 'time',
		        'conversion' => NULL,
		        'type' => 'time',
		        'size' => NULL,
		        'null' => TRUE,
		        'identity' => FALSE,
		        'sql' => 'time'
		    ],
		    'dummytablecol17' => [
		        'phptype' => 'string',
		        'conversion' => NULL,
		        'type' => 'varchar',
		        'size' => '45',
		        'null' => TRUE,
		        'identity' => FALSE,
		        'sql' => 'varchar(45)'
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
        		!isset($row['iddummytable']) and $row['iddummytable']=null; // int
		!isset($row['dummytablecol']) and $row['dummytablecol']=null; // varchar
		!isset($row['dummytablecol1']) and $row['dummytablecol1']=null; // char
		!isset($row['dummytablecol2']) and $row['dummytablecol2']=null; // datetime
		!isset($row['dummytablecol3']) and $row['dummytablecol3']=null; // decimal
		!isset($row['dummytablecol4']) and $row['dummytablecol4']=null; // blob
		!isset($row['dummytablecol5']) and $row['dummytablecol5']=null; // date
		!isset($row['dummytablecol6']) and $row['dummytablecol6']=null; // timestamp
		!isset($row['dummytablecol7']) and $row['dummytablecol7']=null; // longblob
		!isset($row['dummytablecol8']) and $row['dummytablecol8']=null; // char
		!isset($row['dummytablecol9']) and $row['dummytablecol9']=null; // char
		!isset($row['dummytablecol10']) and $row['dummytablecol10']=null; // tinyint
		!isset($row['dummytablecol11']) and $row['dummytablecol11']=null; // varchar
		!isset($row['dummytablecol12']) and $row['dummytablecol12']=null; // geometry
		!isset($row['dummytablecol13']) and $row['dummytablecol13']=null; // json
		!isset($row['dummytablecol14']) and $row['dummytablecol14']=null; // double
		!isset($row['dummytablecol15']) and $row['dummytablecol15']=null; // float
		!isset($row['dummytablecol16']) and $row['dummytablecol16']=null; // time
		!isset($row['dummytablecol17']) and $row['dummytablecol17']=null; // varchar
        
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
		    'iddummytable',
		    'dummytablecol',
		    'dummytablecol1',
		    'dummytablecol2',
		    'dummytablecol3',
		    'dummytablecol4',
		    'dummytablecol5',
		    'dummytablecol6',
		    'dummytablecol7',
		    'dummytablecol8',
		    'dummytablecol9',
		    'dummytablecol10',
		    'dummytablecol11',
		    'dummytablecol12',
		    'dummytablecol13',
		    'dummytablecol14',
		    'dummytablecol15',
		    'dummytablecol16',
		    'dummytablecol17'
		];
    }

    /**
    * It returns an associative array (colname=>key type) with all the keys/indexes (if any)
    *
    * @return string[]
    */
    public static function getDefKey() {
        return [
		    'iddummytable' => 'PRIMARY KEY'
		];
    }

    /**
    * It returns a string array with the name of the columns that are skipped when insert
    * @return string[]
    */
    public static function getDefNoInsert() {
        return [

		];
    }

    /**
    * It returns a string array with the name of the columns that are skipped when update
    * @return string[]
    */
    public static function getDefNoUpdate() {
        return [
		    'iddummytable'
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
        return static::newQuery()->where($sql, $param,false,DummytableRepo::TABLE);
    }

    public static function getDefFK($structure=false) {
        if ($structure) {
            return [

			];
        }
        /* key,refcol,reftable,extra */
        return [

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
            $recursive=DummytableRepo::getRelations($recursive);
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
		'iddummytable'=>0,
		'dummytablecol'=>'',
		'dummytablecol1'=>'',
		'dummytablecol2'=>'',
		'dummytablecol3'=>0.0,
		'dummytablecol4'=>'',
		'dummytablecol5'=>'',
		'dummytablecol6'=>'',
		'dummytablecol7'=>'',
		'dummytablecol8'=>'',
		'dummytablecol9'=>'',
		'dummytablecol10'=>0,
		'dummytablecol11'=>'',
		'dummytablecol12'=>'',
		'dummytablecol13'=>???,
		'dummytablecol14'=>0.0,
		'dummytablecol15'=>0.0,
		'dummytablecol16'=>'',
		'dummytablecol17'=>''
		];
        
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
		'iddummytable'=>null,
		'dummytablecol'=>null,
		'dummytablecol1'=>null,
		'dummytablecol2'=>null,
		'dummytablecol3'=>null,
		'dummytablecol4'=>null,
		'dummytablecol5'=>null,
		'dummytablecol6'=>null,
		'dummytablecol7'=>null,
		'dummytablecol8'=>null,
		'dummytablecol9'=>null,
		'dummytablecol10'=>null,
		'dummytablecol11'=>null,
		'dummytablecol12'=>null,
		'dummytablecol13'=>null,
		'dummytablecol14'=>null,
		'dummytablecol15'=>null,
		'dummytablecol16'=>null,
		'dummytablecol17'=>null
		];
        if ($values !== null) {
            $row = array_merge($row, $values);
        }
        return $row;
    }
}

