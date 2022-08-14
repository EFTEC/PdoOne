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
namespace repomysql;
use eftec\PdoOne;
use eftec\PdoOneQuery;
use mysql\repomodel\TableGrandChildTagModel;
use Exception;
// [EDIT:use] you can edit this part
// Here you can add your custom use
// [/EDIT] end of edit

/**
 * Class AbstractTableGrandChildTagRepo. Copyright (c) Jorge Castro C. (https://github.com/EFTEC/PdoOne)<br>
 * Generated by PdoOne Version 3.11.1 Date generated Sat, 13 Aug 2022 20:37:51 -0400.<br>
 * <b>DO NOT EDIT THE CODE OUTSIDE EDIT BLOCKS</b>. This code is generated<br>
 * If you want to make some changes, then add the changes to the Repository class.<br>
 * <pre>
 * $code=$pdoOne->generateCodeClass('TableGrandChildTag','repomysql',array(),array('TableParent'=>'TableParentRepo','TableChild'=>'TableChildRepo','TableGrandChild'=>'TableGrandChildRepo','TableGrandChildTag'=>'TableGrandChildTagRepo','TableParentxCategory'=>'TableParentxCategoryRepo','TableCategory'=>'TableCategoryRepo','TableParentExt'=>'TableParentExtRepo',),array(),'','','TestDb','mysql\repomodel\TableGrandChildTagModel',array(),array(),array());
 * </pre>
 */
abstract class AbstractTableGrandChildTagRepo extends TestDb
{
    public const TABLE = 'TableGrandChildTag';
    /** @var string A string with the name of the class. It is used to identify itself. */
    public const IDENTITY = 'IdTablaGrandChildTagPK';
    /** @var string[] An indexed array with the original name of the primary keys. Note: Only the first primary key is used */
    public const PK = [
	    'IdTablaGrandChildTagPK'
	];
    /** @var string A string with the name of the class. It is used to identify itself. */
    public const ME=__CLASS__;
    /** @var string|null $schema you can set the current schema/database used by this class. [Default is null] */
    public static $schema;

    //<editor-fold desc="definitions">
    public const EXTRACOLS='';
    /** @var string[][] an associative array with the definition of the columns */
    public const DEF=[
		    'IdTablaGrandChildTagPK' => [
		        'alias' => 'IdTablaGrandChildTagPK',
		        'phptype' => 'int',
		        'conversion' => 'int',
		        'type' => 'int',
		        'size' => null,
		        'null' => false,
		        'identity' => true,
		        'sql' => 'int not null auto_increment'
		    ],
		    'Name' => [
		        'alias' => 'Name',
		        'phptype' => 'string',
		        'conversion' => null,
		        'type' => 'varchar',
		        'size' => '50',
		        'null' => true,
		        'identity' => false,
		        'sql' => 'varchar(50)'
		    ],
		    'IdgrandchildFK' => [
		        'alias' => 'IdgrandchildFK',
		        'phptype' => 'int',
		        'conversion' => 'int',
		        'type' => 'int',
		        'size' => null,
		        'null' => true,
		        'identity' => false,
		        'sql' => 'int'
		    ]
		];
    /** @var string[][] an associative array with the definition of the foreign keys and relations */
    public const DEFFK=[

		];
    public const DEFFKSQL=[

			];
    /** @var string[] An indexed array with the database name of the columns that are not inserted */
    public const DEFNOINSERT=[
		    'IdTablaGrandChildTagPK'
		];
    /** @var string[] An indexed array with the database name of the columns that are not updated */
    public const DEFNOUPDATE=[
		    'IdTablaGrandChildTagPK'
		];
    /** @var string[] an associative array that associates the database column with its alias, ex: ['col'=>'colalias'] */
    public const COL2ALIAS=[
		    'IdTablaGrandChildTagPK' => 'IdTablaGrandChildTagPK',
		    'Name' => 'Name',
		    'IdgrandchildFK' => 'IdgrandchildFK'
		];
    /** @var string[] an associative array that associates the alias with its database column, ex: ['colalias'=>'col'] */
    public const ALIAS2COL=[
		    'IdTablaGrandChildTagPK' => 'IdTablaGrandChildTagPK',
		    'Name' => 'Name',
		    'IdgrandchildFK' => 'IdgrandchildFK'
		];
    //</editor-fold>


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
        $r = self::DEF;
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
    * @param null|array $row [ref]
    */
    public static function convertOutputVal(&$row)
    {
        if ($row === false || $row === null) {
            return;
        }
        if(count($row)===0) {
            $row=null;
            return;
        }
		$row['IdTablaGrandChildTagPK']=isset($row['IdTablaGrandChildTagPK']) ? (int)$row['IdTablaGrandChildTagPK'] : null;
		!isset($row['Name']) and $row['Name']=null; // varchar
		$row['IdgrandchildFK']=isset($row['IdgrandchildFK']) ? (int)$row['IdgrandchildFK'] : null;

    }

    /**
    * It converts a row to be inserted or updated into the database.<br>
    * If the column is missing then it is ignored and not converted.
    *
    * @param array $row [ref]
    * @return array
    */
    public static function convertInputVal(&$row) {
		isset($row['IdTablaGrandChildTagPK']) and $row['IdTablaGrandChildTagPK']=(int)$row['IdTablaGrandChildTagPK'];
		isset($row['IdgrandchildFK']) and $row['IdgrandchildFK']=(int)$row['IdgrandchildFK'];
        return $row;
    }


    /**
    * It gets all the name of the columns.
    *
    * @return string[]
    */
    public static function getDefName($alias=false) {
        if($alias) {
            return static::COL2ALIAS;
        }
        return [
		    'IdTablaGrandChildTagPK',
		    'Name',
		    'IdgrandchildFK'
		];
    }

    /**
    * It returns an associative array (colname=>key type) with all the keys/indexes (if any)
    *
    * @return string[]
    */
    public static function getDefKey() {
        return [
		    'IdTablaGrandChildTagPK' => 'PRIMARY KEY',
		    'IdgrandchildFK' => 'KEY'
		];
    }

    /**
    * It returns a string array with the name of the columns that are skipped when insert
    * @return string[]
    */
    public static function getDefNoInsert() {
        return [
		    'IdTablaGrandChildTagPK'
		];
    }

    /**
    * It returns a string array with the name of the columns that are skipped when update
    * @return string[]
    */
    public static function getDefNoUpdate() {
        return [
		    'IdTablaGrandChildTagPK'
		];
    }

    /**
    * It adds a condition to the query pipeline. It could be stacked using multiple where()
    * <b>Example:</b><br>
    * <pre>
         * self::where(['col'=>'value'])::toList();
         * self::where(['col']=>['value'])::toList(); // s= string/double/date, i=integer, b=bool
         * self::where(['col=?']=>['value'])::toList(); // s= string/double/date, i=integer, b=bool
         * </pre>
    *
    * @param array|string   $sql =self::factoryUtil()
    * @param null|array|int $param
    *
    * @return PdoOneQuery
    */
    public static function where($sql, $param = PdoOne::NULL)
    {
        return static::newQuery()->where($sql, $param,false,TableGrandChildTagRepo::TABLE);
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
            return TableGrandChildTagModel::fromArrayMultiple( self::_toList($filter, $filterValue));
        }
        return self::_toList($filter, $filterValue);
    }

    /**
    * It sets the recursivity. By default, if we query or modify a value, it operates with the fields of the entity.
    * With recursivity, we could use the recursivity of the fields, for example, loading a MANYTOONE relation<br>
    * <b>Example:</b><br>
    * <pre>
    * self::recursive([]); // (default) no use recursivity.
    * self::recursive('*'); // recursive every MANYTOONE,ONETOONE,MANYTOONE and ONETOONE relations (first level)
    * self::recursive('MANYTOONE'); // recursive all relations of the type MANYTOONE (first level)
    * self::recursive(['/_relation1','/_relation2']); // recursive only the relations of the first level
    * self::recursive(['/_relation1','/_relation1/_subrelation1']); //recursive the relations (first and second level)
    * self::recursive(['/_manytomany*'); // the postfix "*" indicates: (only in a many-to-many relation)
    *      // "*": in the case of insert, update or merge, the relational table,left and right table would be modified.
    *      // "" : in the case of insert, update or merge, only the relational table and left would be modified.
    * </pre>
    * If array then it uses the values to set the recursivity.<br>
    * If string then the values allowed are '*', 'MANYTOONE','ONETOMANY','MANYTOMANY','ONETOONE' (first level only)<br>
    * If you don't want to do multiple modifications (insert,update or delete), then simply you can skip this operator.
    *
    * @param string|array $recursive=self::factoryUtil();
    *
    * @return PdoOneQuery
    */
    public static function recursive($recursive=[])
    {
        if(is_string($recursive)) {
            $recursive=TableGrandChildTagRepo::getRelations($recursive);
        }
        return parent::_recursive($recursive);
    }

    /**
    * It adds an "limit" in a query. It depends on the type of database<br>
    * <b>Example:</b><br>
    * <pre>
    *      self::limit("10,20")->toList(); // start row 10th, fetches 20 next 20 rows
    *      self::limit(10,20)->toList();   // start row 10th, fetches 20 next 20 rows
    * </pre>
    *
    * @param string $sql Input SQL query
    *
    * @return PdoOneQuery
    * @throws Exception
    * @test InstanceOf PdoOne::class,this('1,10')
    */
    public static function limit($first,$second=null) : PdoOneQuery
    {
        return static::newQuery()->limit($first,$second);
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
    public static function first($pk = PdoOne::NULL,?PdoOneQuery $query=null) {
        if(self::$useModel) {
            /** @noinspection PhpIncompatibleReturnTypeInspection */
            return TableGrandChildTagModel::fromArray(self::_first($pk));
        }
        return self::_first($pk,$query);
    }

    /**
    *  It returns true if the entity exists, otherwise false.<br>
    *  <b>Example:</b><br>
    *  <pre>
         *  $this->exist(['id'=>'a1','name'=>'name']); // using an array
         *  $this->exist('a1'); // using the primary key. The table needs a pks and it only works with the first pk.
         *  </pre>
    *
    * @param array|mixed $entity =self::factoryUtil()
    *
    * @return bool true if the pks exists
    * @throws Exception
    */
    public static function exist($entity) {
        return self::_exist($entity);
    }

    /**
    * It inserts a new entity(row) into the database<br>
    * It returns false if the operation failed, otherwise, it returns the entity modified (if it has, for example, an
    * identity field<br>
    * @param array|object $entity        =self::factoryUtil()
    * @param bool         $transactional If true (default) then the operation is transactional
    *
    * @return array|false=self::factoryUtil()
    * @throws Exception
    */
    public static function insert(&$entity,$transactional=true) {
        return self::_insert($entity,$transactional,true);
    }

    /**
    * It merge a new entity(row) into the database. If the entity exists then it is updated, otherwise the entity is
    * inserted<br>
    * @param array|object $entity        =self::factoryUtil()
    * @param bool         $transactional If true (default) then the operation is transactional
    *
    * @return array|false=self::factoryUtil()
    * @throws Exception
    */
    public static function merge(&$entity,$transactional=true) {
        return self::_merge($entity,$transactional);
    }

    /**
    * Updates an entity. It uses the primary key as condition.
    * @param array|object $entity        =self::factoryUtil()
    * @param bool         $transactional If true (default) then the operation is transactional<br>
    *                                    If false, then it allows to create your own transaction.
    * @return false|int=self::factoryUtil()
    * @throws Exception
    */
    public static function update($entity,$transactional=true) {
        return self::_update($entity,$transactional);
    }

    /**
     * It deletes an entity using the fields of the entity as conditions
     *
     * @param array|object $entity =self::factoryUtil()
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
    * @param array|mixed $pk =self::factoryUtil()
    * @param bool        $transactional If true (default) then the operation is transactional
    *
    * @return int|false
    * @throws Exception
    */
    public static function deleteById($pk,$transactional=true) {
        return self::_deleteById($pk,$transactional);
    }

    /**
     * Returns an array with the default structure of an entity.
     *
     * @param array|null $values          =self::factoryUtil()
     * @param string     $recursivePrefix It is the prefix of the recursivity.
     *
     * @return array
     */
    public static function factory($values = null, $recursivePrefix = '') {
        $recursive=static::getRecursive();
        static::recursive(); // reset the recursivity.
        $row= [
		    'IdTablaGrandChildTagPK' => null,
		    'Name' => null,
		    'IdgrandchildFK' => null
		];

        if ($values !== null) {
            $row = array_merge($row, $values);
        }
        return $row;
    }

    /**
     * An associative array used to define the full structure of an entity.<br>
     * It is only used for autocomplete because it is prone to circular reference.
     *
     * @return array
     */
    public static function factoryUtil() {
        return [
		    'IdTablaGrandChildTagPK' => null,
		    'Name' => null,
		    'IdgrandchildFK' => null
		];
    }

    // [EDIT:content] you can edit this part
    // Here you can add your custom content.
    // [/EDIT] end of edit
}

