<?php
/** @noinspection PhpUnusedParameterInspection
 * @noinspection PhpClassConstantAccessedViaChildClassInspection
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
namespace repomysql;
use eftec\PdoOne;
use eftec\PdoOneQuery;
use mysql\repomodel\TableParentModel;
use Exception;

/**
 * Generated by PdoOne Version 2.20 Date generated Sun, 30 Jan 2022 13:28:44 -0400. 
 * DO NOT EDIT THIS CODE. Use instead the Repo Class.
 * @copyright (c) Jorge Castro C. MIT License  https://github.com/EFTEC/PdoOne
 * Class AbstractTableParentRepo
 * <pre>
 * $code=$pdoOne->generateCodeClass('TableParent','repomysql',array('_idchild2FK'=>'PARENT','_TableParentxCategory'=>'MANYTOMANY','fieldKey'=>array(0=>'encrypt',1=>NULL,),'extracol'=>'datetime3',),array('TableParent'=>'TableParentRepo','TableChild'=>'TableChildRepo','TableGrandChild'=>'TableGrandChildRepo','TableGrandChildTag'=>'TableGrandChildTagRepo','TableParentxCategory'=>'TableParentxCategoryRepo','TableCategory'=>'TableCategoryRepo','TableParentExt'=>'TableParentExtRepo',),array(),'','','TestDb','mysql\repomodel\TableParentModel',array('extracol'=>'CURRENT_TIMESTAMP','extracol2'=>'20',),array());
 * </pre>
 */
abstract class AbstractTableParentRepo extends TestDb
{
    const TABLE = 'TableParent';    
    const IDENTITY = 'idtablaparentPK';
    const PK = [
	    'idtablaparentPK'
	];
    const ME=__CLASS__;
    const EXTRACOLS='CURRENT_TIMESTAMP as `extracol`,20 as `extracol2`';
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
    public static function getDef($column=null,$filter=null) {
       $r = [
		    'idtablaparentPK' => [
		        'phptype' => 'int',
		        'conversion' => 'int',
		        'type' => 'int',
		        'size' => NULL,
		        'null' => FALSE,
		        'identity' => TRUE,
		        'sql' => 'int not null auto_increment'
		    ],
		    'fieldVarchar' => [
		        'phptype' => 'string',
		        'conversion' => NULL,
		        'type' => 'varchar',
		        'size' => '50',
		        'null' => TRUE,
		        'identity' => FALSE,
		        'sql' => 'varchar(50)'
		    ],
		    'idchildFK' => [
		        'phptype' => 'int',
		        'conversion' => 'int',
		        'type' => 'int',
		        'size' => NULL,
		        'null' => TRUE,
		        'identity' => FALSE,
		        'sql' => 'int'
		    ],
		    'idchild2FK' => [
		        'phptype' => 'int',
		        'conversion' => 'int',
		        'type' => 'int',
		        'size' => NULL,
		        'null' => TRUE,
		        'identity' => FALSE,
		        'sql' => 'int'
		    ],
		    'fieldInt' => [
		        'phptype' => 'int',
		        'conversion' => 'int',
		        'type' => 'int',
		        'size' => NULL,
		        'null' => TRUE,
		        'identity' => FALSE,
		        'sql' => 'int'
		    ],
		    'fielDecimal' => [
		        'phptype' => 'float',
		        'conversion' => NULL,
		        'type' => 'double',
		        'size' => NULL,
		        'null' => TRUE,
		        'identity' => FALSE,
		        'sql' => 'double'
		    ],
		    'fieldDateTime' => [
		        'phptype' => 'datetime',
		        'conversion' => 'datetime',
		        'type' => 'datetime',
		        'size' => NULL,
		        'null' => TRUE,
		        'identity' => FALSE,
		        'sql' => 'datetime'
		    ],
		    'fieldUnique' => [
		        'phptype' => 'string',
		        'conversion' => NULL,
		        'type' => 'varchar',
		        'size' => '20',
		        'null' => TRUE,
		        'identity' => FALSE,
		        'sql' => 'varchar(20)'
		    ],
		    'fieldKey' => [
		        'phptype' => 'string',
		        'conversion' => [
		            'encrypt',
		            NULL
		        ],
		        'type' => 'varchar',
		        'size' => '100',
		        'null' => TRUE,
		        'identity' => FALSE,
		        'sql' => 'varchar(100)'
		    ]
		];
       if($column!==null) {
            if($filter===null) {
                foreach($r as $k=>$v) {
                    $r[$k]=$v[$column];
                }
            } else {
                $new=[];
                foreach($r as $k=>$v) {
                    if($v[$column]===$filter) {
                        $new[]=$k;
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
    public static function convertOutputVal(&$row) {
        if($row===false || $row===null) { 
            return;
        }
		$row['idtablaparentPK']=isset($row['idtablaparentPK']) ? (int)$row['idtablaparentPK'] : null;
		!isset($row['fieldVarchar']) and $row['fieldVarchar']=null; // varchar
		$row['idchildFK']=isset($row['idchildFK']) ? (int)$row['idchildFK'] : null;
		$row['idchild2FK']=isset($row['idchild2FK']) ? (int)$row['idchild2FK'] : null;
		$row['fieldInt']=isset($row['fieldInt']) ? (int)$row['fieldInt'] : null;
		!isset($row['fielDecimal']) and $row['fielDecimal']=null; // double
		$row['fieldDateTime']=isset($row['fieldDateTime']) ? PdoOne::dateConvert($row['fieldDateTime'], 'sql', 'class') : null;
		!isset($row['fieldUnique']) and $row['fieldUnique']=null; // varchar
		!isset($row['fieldKey']) and $row['fieldKey']=null; // no conversion
		$row['extracol']=isset($row['extracol']) ? PdoOne::dateConvert($row['extracol'], 'sql', 'human') : null;
		!isset($row['extracol2']) and $row['extracol2']=null; // 
		isset($row['_idchildFK'])
            and $row['_idchildFK']['idtablachildPK']=&$row['idchildFK']; // linked MANYTOONE
		isset($row['_TableParentExt'])
            and $row['_TableParentExt']['idtablaparentExtPK']=&$row['idtablaparentPK']; // linked ONETOONE

    }

    /**
     * It converts a row to be inserted or updated into the database.<br>
     * If the column is missing then it is ignored and not converted.
     * 
     * @param array $row [ref]
     */    
    public static function convertInputVal(&$row) {
		isset($row['idtablaparentPK']) and $row['idtablaparentPK']=(int)$row['idtablaparentPK'];
		isset($row['idchildFK']) and $row['idchildFK']=(int)$row['idchildFK'];
		isset($row['idchild2FK']) and $row['idchild2FK']=(int)$row['idchild2FK'];
		isset($row['fieldInt']) and $row['fieldInt']=(int)$row['fieldInt'];
		isset($row['fieldDateTime']) and $row['fieldDateTime']=PdoOne::dateConvert($row['fieldDateTime'], 'class', 'sql');
		isset($row['fieldKey']) and $row['fieldKey']=self::getPdoOne()->encrypt($row['fieldKey']);
		isset($row['extracol']) and $row['extracol']=PdoOne::dateConvert($row['extracol'], 'human', 'sql');
    }


    /**
     * It gets all the name of the columns.
     *
     * @return string[]
     */
    public static function getDefName() {
        return [
		    'idtablaparentPK',
		    'fieldVarchar',
		    'idchildFK',
		    'idchild2FK',
		    'fieldInt',
		    'fielDecimal',
		    'fieldDateTime',
		    'fieldUnique',
		    'fieldKey'
		];
    }

    /**
     * It returns an associative array (colname=>key type) with all the keys/indexes (if any)
     *
     * @return string[]
     */
    public static function getDefKey() {
        return [
		    'idtablaparentPK' => 'PRIMARY KEY',
		    'fieldUnique' => 'UNIQUE KEY',
		    'idchildFK' => 'KEY',
		    'fieldKey' => 'KEY',
		    'idchild2FK' => 'KEY'
		];
    }

    /**
     * It returns a string array with the name of the columns that are skipped when insert
     * @return string[]
     */
    public static function getDefNoInsert() {
        return [
		    'idtablaparentPK'
		];
    }

    /**
     * It returns a string array with the name of the columns that are skipped when update
     * @return string[]
     */
    public static function getDefNoUpdate() {
        return [
		    'idtablaparentPK'
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
        return static::newQuery()->where($sql, $param,false,TableParentRepo::TABLE);
    }

    public static function getDefFK($structure=false) {
        if ($structure) {
            return [
			    'idchild2FK' => 'FOREIGN KEY REFERENCES`TableChild`(`idtablachildPK`)',
			    'idchildFK' => 'FOREIGN KEY REFERENCES`TableChild`(`idtablachildPK`)'
			];
        }
        /* key,refcol,reftable,extra */
        return [
		    'idchild2FK' => [
		        'key' => 'FOREIGN KEY',
		        'refcol' => 'idtablachildPK',
		        'reftable' => 'TableChild',
		        'extra' => '',
		        'name' => 'fk_TableParent_idchild2FK'
		    ],
		    '_idchild2FK' => [
		        'key' => 'PARENT',
		        'refcol' => 'idtablachildPK',
		        'reftable' => 'TableChild',
		        'extra' => '',
		        'name' => 'fk_TableParent_idchild2FK'
		    ],
		    'idchildFK' => [
		        'key' => 'FOREIGN KEY',
		        'refcol' => 'idtablachildPK',
		        'reftable' => 'TableChild',
		        'extra' => '',
		        'name' => 'fk_TableParent_idchildFK'
		    ],
		    '_idchildFK' => [
		        'key' => 'MANYTOONE',
		        'refcol' => 'idtablachildPK',
		        'reftable' => 'TableChild',
		        'extra' => '',
		        'name' => 'fk_TableParent_idchildFK'
		    ],
		    '_TableParentExt' => [
		        'key' => 'ONETOONE',
		        'col' => 'idtablaparentPK',
		        'reftable' => 'TableParentExt',
		        'refcol' => 'idtablaparentExtPK'
		    ],
		    '_TableParentxCategory' => [
		        'key' => 'MANYTOMANY',
		        'col' => 'idtablaparentPK',
		        'reftable' => 'TableParentxCategory',
		        'refcol' => '_idtablaparentPKFK',
		        'refcol2' => '_idcategoryPKFK',
		        'col2' => 'IdTableCategoryPK',
		        'table2' => 'TableCategory'
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
    public static function getRelations($type='all') {
        $r= [
		    'MANYTOONE' => [
		        '_idchildFK'
		    ],
		    'ONETOONE' => [
		        '_TableParentExt'
		    ],
		    'MANYTOMANY' => [
		        '_TableParentxCategory'
		    ]
		];
        if($type==='*') {
            $result=[];
            foreach($r as $arr) {
                $result = array_merge($result,$arr);
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
            return TableParentModel::fromArrayMultiple( self::_toList($filter, $filterValue));
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
            $recursive=TableParentRepo::getRelations($recursive);
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
    public static function limit($sql)
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
            return TableParentModel::fromArray(self::_first($pk));
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
     * @return array|false=self::factory()
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
		'idtablaparentPK'=>0,
		'_TableParentExt'=>(in_array($recursivePrefix.'_TableParentExt',$recursive,true))
		                            ? TableParentExtRepo::factory(null,$recursivePrefix.'_TableParentExt') 
		                            : null, /* ONETOONE! */
		'_TableParentxCategory'=>(in_array($recursivePrefix.'_TableParentxCategory',$recursive,true))
		                            ? [] 
		                            : null, /* MANYTOMANY! */
		'fieldVarchar'=>'',
		'idchildFK'=>0,
		'_idchildFK'=>(in_array($recursivePrefix.'_idchildFK',$recursive,true)) 
		                            ? TableChildRepo::factory(null,$recursivePrefix.'_idchildFK') 
		                            : null, /* MANYTOONE!! */
		'idchild2FK'=>0,
		'fieldInt'=>0,
		'fielDecimal'=>0.0,
		'fieldDateTime'=>'',
		'fieldUnique'=>'',
		'fieldKey'=>''
		];
		isset($row['_idchildFK'])
            and $row['_idchildFK']['idtablachildPK']=&$row['idchildFK']; // linked MANYTOONE
		isset($row['_TableParentExt'])
            and $row['_TableParentExt']['idtablaparentExtPK']=&$row['idtablaparentPK']; // linked ONETOONE

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
		'idtablaparentPK'=>null,
		'_TableParentExt'=>null, /* ONETOONE! */
		'_TableParentxCategory'=>null, /* MANYTOMANY! */
		'fieldVarchar'=>null,
		'idchildFK'=>null,
		'_idchildFK'=>null, /* MANYTOONE!! */
		'idchild2FK'=>null,
		'fieldInt'=>null,
		'fielDecimal'=>null,
		'fieldDateTime'=>null,
		'fieldUnique'=>null,
		'fieldKey'=>null
		];
        if ($values !== null) {
            $row = array_merge($row, $values);
        }    
        return $row;        
    }

}