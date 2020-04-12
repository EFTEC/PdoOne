<?php

namespace eftec\ext;

use Exception;

/**
 * Interface PdoOne_IExt
 *
 * @package       eftec\ext
 * @author        Jorge Castro Castillo
 * @copyright (c) Jorge Castro C. MIT License  https://github.com/EFTEC/PdoOne
 * @see           https://github.com/EFTEC/PdoOne
 */
interface PdoOne_IExt
{
    public function construct($charset);

    public function connect($cs);

    /**
     * Returns an associative array with the definition of a table (columns of the table).
     *
     * @param string $table
     *
     * @return array
     * @throws Exception
     */
    public function getDefTable($table);

    /**
     * Returns an associative array with the definition of keys of a table.<br>
     * It includes primary key, key and unique keys
     *
     * @param string $table        The name of the table to analize.
     * @param bool   $returnSimple true= returns as a simple associative array<br>
     *                             example:['id'=>'PRIMARY KEY','name'=>'FOREIGN KEY...']<br>
     *                             false= returns as an associative array separated by parts<br>
     *                             ['key','refcol','reftable','extra']
     * @param null|string   $filter if not null then it only returns keys that matches the condition 
     *
     * @return array
     * @throws Exception
     */
    public function getDefTableKeys($table,$returnSimple,$filter=null);

    /**
     * Returns an associative array with the definition of foreign keys of a table.<br>
     * It includes foreign keys.
     *
     * @param string $table        The name of the table to analize.
     * @param bool   $returnSimple true= returns as a simple associative array<br>
     *                             example:['id'=>'PRIMARY KEY','name'=>'FOREIGN KEY...']<br>
     *                             false= returns as an associative array separated by parts<br>
     *                             ['key','refcol','reftable','extra']
     * @param null|string   $filter if not null then it only returns keys that matches the condition
     *
     * @return array
     * @throws Exception
     */
    public function getDefTableFK($table,$returnSimple,$filter=null);
    
    
    /**
     * It returns a default value depending on the type of the column.
     *
     * @param      $row
     * @param bool $default
     *
     * @return mixed
     */
    function typeDict($row, $default = true);

    /**
     * Returns an associative array if the object exists. Otherwise it will return an empty array<br>
     * The fields of the associative array depends on the type of database
     *
     * @param string $type
     *
     * @return string
     * @throws Exception
     */
    public function objectExist($type = 'table');

    /**
     * Returns an associative array with the list of objects from the current schema.<br>
     * The fields of the associative array depends on the type of database
     *
     * @param string $type     =['table','function'][$i]
     * @param bool   $onlyName If true then it only returns the name of the objects
     *
     * @return string|string[]
     * @throws Exception
     */
    public function objectList($type = 'table', $onlyName = false);

    public function columnTable($tableName);

    public function foreignKeyTable($tableName);

    /**
     * @param null|string $tableSequence
     * @param string      $method
     *
     * @return string
     * @throws Exception
     */
    public function createSequence($tableSequence = null, $method = 'snowflake');

    /**
     * DCL command. It creates a database.<br>
     * <b>Example:</b>
     * <pre>
     * $this->createtable("customer"
     *      ,['id'=>'int','name'=>'varchar(50)']
     *      ,'id');
     * $this->createtable("customer"
     *      ,['id'=>'int','name'=>'varchar(50)']
     *      ,['id'=>'PRIMARY KEY','name'=>'KEY']);
     * </pre>
     *
     *
     * @param string            $tableName    The name of the table
     * @param array             $definition   An associative array with the definition of the columns.<br>
     *                                        The key is used as the name of the field
     * @param null|string|array $primaryKey   An associative array with the definition of the indexes/keys<br>
     *                                        The key is used as the name of the field.<br>
     *                                        'field'=>'PRIMARY KEY'<br>
     *                                        'field'=>'KEY'<br>
     *                                        'field'=>'UNIQUE KEY'<br>
     *                                        'field'=>'FOREIGN KEY REFERENCES TABLEREF(COLREF) ...'
     *
     * @param string            $extra        An extra definition inside the operation of create
     * @param string            $extraOutside An extra definition after the operation of create
     *
     * @return string
     */
    public function createTable($tableName, $definition, $primaryKey = null, $extra = '', 
        $extraOutside = '');

    /**
     * Create foreign keys
     *
     * @param string       $tableName    The name of the table
     * @param array        $foreignKey   Associative array with the foreign key.
     *
     * @return mixed
     * @throws Exception
     */
    public function createFK($tableName,$foreignKey); 
    
    
    /**
     * It adds a limit operation for the query. It depends on the type of the database.
     * <b>Example:</b><br>
     * <pre>
     *      ->select("")->limit("10,20")->toList();
     * </pre>
     *
     * @param $sql
     *
     * @throws Exception
     */
    public function limit($sql);

    /**
     * It gets a primary key based in a query.<br>
     * It only works in MYSQL.
     *
     * @param string $query query or name of the table
     * @param string $pk Previous primary key (if the key is not found)
     *
     * @return mixed
     * @throws Exception
     */
    public function getPK($query, $pk);
}