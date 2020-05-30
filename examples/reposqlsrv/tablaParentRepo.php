<?php
/** @noinspection PhpUnused */
namespace repo;
use eftec\PdoOne;
use eftec\_BasePdoOneRepo;

/**
 * Generated by PdoOne Version 1.40.1 Date generated Fri, 29 May 2020 16:55:18 -0400
 * @copyright (c) Jorge Castro C. MIT License  https://github.com/EFTEC/PdoOne 
 * Class TablaparentRepo
 */
class TablaparentRepo extends _BasePdoOneRepo
{
    const TABLE = 'tablaparent';
    const PK = [
	    'idtablaparentPK'
	];
    const ME=__CLASS__;   
    
    public static function getDef($onlyKeys=false) {
        $r= [
		    'idtablaparentPK' => 'int NOT NULL IDENTITY(1,1)',
		    'field1' => 'varchar(50)',
		    'idchildFK' => 'int DEFAULT (NULL)',
		    'idchild2FK' => 'int DEFAULT (NULL)',
		    'field2' => 'varchar(50)'
		];
        return ($onlyKeys)? array_keys($r): $r;
    }
    
    /**
     * It returns an associative array (colname=>key type) with all the keys/indexes (if any)
     * 
     * @return string[]
     */    
    public static function getDefKey() {
        return [
		    'idtablaparentPK' => 'PRIMARY KEY',
		    'idchildFK' => 'KEY',
		    'idchild2FK' => 'KEY',
		    'field2' => 'UNIQUE KEY'
		];
    }
    public static function getDefIdentity() {
        return [
		    'idtablaparentPK'
		];
    }
    public static function getDefFK($structure=false) {
        if ($structure) {
            return [
			    'idchild2FK' => 'FOREIGN KEY REFERENCES [tablachild]([idtablachildPK])',
			    'idchildFK' => 'FOREIGN KEY REFERENCES [tablachild]([idtablachildPK])'
			];
        }
        /* key,refcol,reftable,extra */
        return [
		    'idchild2FK' => [
		        'key' => 'FOREIGN KEY',
		        'refcol' => 'idtablachildPK',
		        'reftable' => 'tablachild',
		        'extra' => '',
		        'name' => 'tablaParent_fk2'
		    ],
		    '/idchild2FK' => [
		        'key' => 'PARENT',
		        'refcol' => 'idtablachildPK',
		        'reftable' => 'tablachild',
		        'extra' => '',
		        'name' => 'tablaParent_fk2'
		    ],
		    'idchildFK' => [
		        'key' => 'FOREIGN KEY',
		        'refcol' => 'idtablachildPK',
		        'reftable' => 'tablachild',
		        'extra' => '',
		        'name' => 'tablaParent_fk1'
		    ],
		    '/idchildFK' => [
		        'key' => 'ONETOONE',
		        'refcol' => 'idtablachildPK',
		        'reftable' => 'tablachild',
		        'extra' => '',
		        'name' => 'tablaParent_fk1'
		    ],
		    '/tablaparentxcategory' => [
		        'key' => 'MANYTOMANY',
		        'col' => 'idtablaparentPK',
		        'reftable' => 'tablaparentxcategory',
		        'class' => '222',
		        'refcol' => '/idtablaparentPKFK',
		        'refcol2' => '/idcategoryPKFK',
		        'col2' => 'IdTablaCategoryPK',
		        'table2' => 'tablacategory'
		    ],
		    '/tablaparent_ext' => [
		        'key' => 'ONETOONE',
		        'col' => 'idtablaparentPK',
		        'reftable' => 'tablaparent_ext',
		        'class' => '222',
		        'refcol' => '/idtablaparentExtPK'
		    ]
		];
    }
    public static function toList($filter=null,$filterValue=null) {
        return self::_toList($filter,$filterValue);
    }
    public static function first($pk = null) {
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
     * @param array|mixed $entity=self::factory()
     * @return bool true if the pks exists
     */
    public static function exist($entity) {
        return self::_exist($entity);
    }
    /**
     * @param array $entity=self::factory()
     * @param bool $transactional If true (default) then the operation is transaction
     * @return array|false=self::factory()
     */
    public static function insert(&$entity,$transactional=true) {
        return self::_insert($entity,$transactional);
    }
    
    /**
     * @param array $entity=self::factory()
     * @param bool $transactional If true (default) then the operation is transaction
     * @return array|false=self::factory()
     */
    public static function update($entity,$transactional=true) {
        return self::_update($entity,$transactional);
    }
    public static function delete($entity,$transactional=true) {
        return self::_delete($entity,$transactional);
    }
    public static function deleteById($pk,$transactional=true) {
        return self::_deleteById($pk,$transactional);
    }  
    

    public static function factory() {
        $recursive=static::getRecursive();
        return [
		'idtablaparentPK'=>0,
		'/tablaparentxcategory'=>(in_array('/tablaparentxcategory',$recursive))
		                            ? [] 
		                            : null, /* onetomany */
		'/tablaparent_ext'=>(in_array('/tablaparent_ext',$recursive))
		                            ? [] 
		                            : null, /* onetomany */
		'field1'=>'',
		'idchildFK'=>0,
		'/idchildFK'=>(in_array('/idchildFK',$recursive)) 
		                            ? tablachildRepo::factory() 
		                            : null, /* manytoone */
		'idchild2FK'=>0,
		'/idchild2FK'=>(in_array('/idchild2FK',$recursive)) 
		                            ? tablachildRepo::factory() 
		                            : null, /* manytoone */
		'field2'=>''
		];
    }
    public static function factoryNull() {
        return [
		'idtablaparentPK'=>null,
		'/tablaparentxcategory'=>null, /* onetomany */
		'/tablaparent_ext'=>null, /* onetomany */
		'field1'=>null,
		'idchildFK'=>null,
		'/idchildFK'=>null, /* manytoone */
		'idchild2FK'=>null,
		'/idchild2FK'=>null, /* manytoone */
		'field2'=>null
		];
    }     
     
}