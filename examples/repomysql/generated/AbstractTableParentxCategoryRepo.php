<?php
/** @noinspection PhpIncompatibleReturnTypeInspection
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
use mysql\repomodel\TableParentxCategoryModel;
use Exception;

/**
 * Generated by PdoOne Version 1.54 Date generated Mon, 10 Aug 2020 18:37:30 -0400. 
 * DO NOT EDIT THIS CODE. Use instead the Repo Class.
 * @copyright (c) Jorge Castro C. MIT License  https://github.com/EFTEC/PdoOne
 * Class AbstractTableParentxCategoryRepo
 * <pre>
 * $code=$pdoOne->generateCodeClass('TableParentxCategory','repomysql',array(),array('TableParent'=>'TableParentRepo','TableChild'=>'TableChildRepo','TableGrandChild'=>'TableGrandChildRepo','TableGrandChildTag'=>'TableGrandChildTagRepo','TableParentxCategory'=>'TableParentxCategoryRepo','TableCategory'=>'TableCategoryRepo','TableParentExt'=>'TableParentExtRepo',),array(),'','','TestDb','mysql\repomodel\TableParentxCategoryModel',array(),array());
 * </pre>
 */
abstract class AbstractTableParentxCategoryRepo extends TestDb
{
    const TABLE = 'TableParentxCategory';
    const PK = [
	    'idtablaparentPKFK',
	    'idcategoryPKFK'
	];
    const ME=__CLASS__;
    CONST EXTRACOLS='';

    /**
     * It returns the definitions of the columns<br>
     * <b>Example:</b><br>
     * <pre>
     * self::getDef(); // ['colName'=>[php type,php conversion type,type,size,nullable,extra,sql],'colName2'=>..]
     * self::getDef('sql'); // ['colName'=>'sql','colname2'=>'sql2']
     * self::getDef('identity',true); // it returns the columns that are identities ['col1','col2']
     * </pre>
     * <b>PHP Types</b>: binary, date, datetime, decimal,int, string,time, timestamp<br>
     * <b>PHP Conversions</b>: datetime3 (human string), datetime2 (iso), datetime (datetime class), timestamp (int), bool, int, float<br>
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
		    'idtablaparentPKFK' => [
		        'phptype' => 'int',
		        'conversion' => 'int',
		        'type' => 'int',
		        'size' => NULL,
		        'null' => FALSE,
		        'identity' => TRUE,
		        'sql' => 'int not null auto_increment'
		    ],
		    'idcategoryPKFK' => [
		        'phptype' => 'int',
		        'conversion' => 'int',
		        'type' => 'int',
		        'size' => NULL,
		        'null' => FALSE,
		        'identity' => FALSE,
		        'sql' => 'int not null'
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
     * If the column is missing then it sets the null value.
     * 
     * @param array $row [ref]
     */    
    public static function convertOutputVal(&$row) {
		$row['idtablaparentPKFK']=isset($row['idtablaparentPKFK']) ? (int)$row['idtablaparentPKFK'] : null;
		$row['idcategoryPKFK']=isset($row['idcategoryPKFK']) ? (int)$row['idcategoryPKFK'] : null;
		isset($row['_idcategoryPKFK'])
            and $row['_idcategoryPKFK']['IdTableCategoryPK']=&$row['IdTableCategoryPK']; // linked MANYTOONE
		isset($row['_idtablaparentPKFK'])
            and $row['_idtablaparentPKFK']['idtablaparentPK']=&$row['idtablaparentPK']; // linked ONETOONE

    }

    /**
     * It converts a row to be inserted or updated into the database.<br>
     * If the column is missing then it is ignored and not converted.
     * 
     * @param array $row [ref]
     */    
    public static function convertInputVal(&$row) {
		isset($row['idtablaparentPKFK']) and $row['idtablaparentPKFK']=(int)$row['idtablaparentPKFK'];
		isset($row['idcategoryPKFK']) and $row['idcategoryPKFK']=(int)$row['idcategoryPKFK'];
    }


    /**
     * It gets all the name of the columns.
     *
     * @return string[]
     */
    public static function getDefName() {
        return [
		    'idtablaparentPKFK',
		    'idcategoryPKFK'
		];
    }

    /**
     * It returns an associative array (colname=>key type) with all the keys/indexes (if any)
     *
     * @return string[]
     */
    public static function getDefKey() {
        return [
		    'idtablaparentPKFK' => 'PRIMARY KEY',
		    'idcategoryPKFK' => 'PRIMARY KEY'
		];
    }

    /**
     * It returns a string array with the name of the columns that are skipped when insert
     * @return string[]
     */
    public static function getDefNoInsert() {
        return [
		    'idtablaparentPKFK'
		];
    }

    /**
     * It returns a string array with the name of the columns that are skipped when update
     * @return string[]
     */
    public static function getDefNoUpdate() {
        return [
		    'idtablaparentPKFK'
		];
    }

    /**
     * It adds a where to the income query. It could be stacked with more where()<br>
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
     * @return TableParentxCategoryRepo
     */
    public static function where($sql, $param = PdoOne::NULL)
    {
        self::getPdoOne()->where($sql, $param);
        return TableParentxCategoryRepo::class;
    }

    public static function getDefFK($structure=false) {
        if ($structure) {
            return [
			    'idcategoryPKFK' => 'FOREIGN KEY REFERENCES`TableCategory`(`IdTableCategoryPK`)',
			    'idtablaparentPKFK' => 'FOREIGN KEY REFERENCES`TableParent`(`idtablaparentPK`)'
			];
        }
        /* key,refcol,reftable,extra */
        return [
		    'idcategoryPKFK' => [
		        'key' => 'FOREIGN KEY',
		        'refcol' => 'IdTableCategoryPK',
		        'reftable' => 'TableCategory',
		        'extra' => '',
		        'name' => 'tablaparentxcategory_fk2'
		    ],
		    '_idcategoryPKFK' => [
		        'key' => 'MANYTOONE',
		        'refcol' => 'IdTableCategoryPK',
		        'reftable' => 'TableCategory',
		        'extra' => '',
		        'name' => 'tablaparentxcategory_fk2'
		    ],
		    'idtablaparentPKFK' => [
		        'key' => 'FOREIGN KEY',
		        'refcol' => 'idtablaparentPK',
		        'reftable' => 'TableParent',
		        'extra' => '',
		        'name' => 'FK_tablaparentxcategory_tablaparent'
		    ],
		    '_idtablaparentPKFK' => [
		        'key' => 'ONETOONE',
		        'refcol' => 'idtablaparentPK',
		        'reftable' => 'TableParent',
		        'extra' => '',
		        'name' => 'FK_tablaparentxcategory_tablaparent'
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
		        '_idcategoryPKFK'
		    ],
		    'ONETOONE' => [
		        '_idtablaparentPKFK'
		    ]
		];
        if($type==='*') {
            $result=[];
            foreach($r as $arr) {
                $result = array_merge($result,$arr);
            }
            return $result;
        }
        return isset($r[$type]) ? $r[$type] : [];  
    
    }
    
    public static function toList($filter=null,$filterValue=null) {
       if(self::$useModel) {
            return TableParentxCategoryModel::fromArrayMultiple( self::_toList($filter, $filterValue));
        }
        return self::_toList($filter, $filterValue);
    }
    
    /**
     * It sets the recursivity.<br>
     * If array then it uses the values to set the recursivity.<br>
     * If string then the values allowed are '*', 'MANYTOONE','ONETOMANY','MANYTOMANY','ONETOONE' (first level only)<br>
     * @param string|array $recursive=self::factory();
     *
     * @return TableParentxCategoryRepo
     * {@inheritDoc}
     */
    public static function setRecursive($recursive)
    {
        if(is_string($recursive)) {
            $recursive=TableParentxCategoryRepo::getRelations($recursive);
        }
        return parent::_setRecursive($recursive); 
    }

    public static function limit($sql)
    {
        self::getPdoOne()->limit($sql);
        return TableParentxCategoryRepo::class;
    }

    /**
     * It returns the first row of a query.
     * @param array|mixed|null $pk [optional] Specify the value of the primary key.
     *
     * @return array|bool
     * @throws Exception
     */
    public static function first($pk = null) {
        if(self::$useModel) {
            return TableParentxCategoryModel::fromArray(self::_first($pk));
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
     * @return mixed
     * @throws Exception
     */
    public static function delete($entity,$transactional=true) {
        return self::_delete($entity,$transactional);
    }

    /**
     * It deletes an entity by the primary key.
     *
     * @param array $pk =self::factory()
     * @param bool  $transactional If true (default) then the operation is transactional   
     *
     * @return mixed
     * @throws Exception
     */
    public static function deleteById($pk,$transactional=true) {
        return self::_deleteById($pk,$transactional);
    }
    
    /**
     * Initialize an empty array with default values (0 for numbers, empty for string, and array|null if recursive)
     * 
     * @param string $recursivePrefix It is the prefix of the recursivity.
     *
     * @return array
     */
    public static function factory($recursivePrefix='') {
        $recursive=static::getRecursive();
        $row= [
		'idtablaparentPKFK'=>0,
		'_idtablaparentPKFK'=>(in_array($recursivePrefix.'_idtablaparentPKFK',$recursive,true)) 
		                            ? TableParentRepo::factory($recursivePrefix.'_idtablaparentPKFK') 
		                            : null, /* ONETOONE!! */
		'idcategoryPKFK'=>0,
		'_idcategoryPKFK'=>(in_array($recursivePrefix.'_idcategoryPKFK',$recursive,true)) 
		                            ? TableCategoryRepo::factory($recursivePrefix.'_idcategoryPKFK') 
		                            : null, /* MANYTOONE!! */
		];
		isset($row['_idcategoryPKFK'])
            and $row['_idcategoryPKFK']['IdTableCategoryPK']=&$row['IdTableCategoryPK']; // linked MANYTOONE
		isset($row['_idtablaparentPKFK'])
            and $row['_idtablaparentPKFK']['idtablaparentPK']=&$row['idtablaparentPK']; // linked ONETOONE
        
        return $row;
    }
    
    /**
     * Initialize an empty array with null values
     * 
     * @return null[]
     */
    public static function factoryNull() {
        return [
		'idtablaparentPKFK'=>null,
		'_idtablaparentPKFK'=>null, /* ONETOONE!! */
		'idcategoryPKFK'=>null,
		'_idcategoryPKFK'=>null, /* MANYTOONE!! */
		];
    }

}