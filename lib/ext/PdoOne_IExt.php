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

    public function connect($cs, $alterSession);

    /**
     * It calls a store procedure.
     *
     * @param string $procName      The name of the store procedure.
     * @param array  $arguments     An associative array with the name of the argument and it's value
     * @param array  $outputColumns [optional] the name of the columns that must be returned.
     * @return mixed
     * @throws Exception
     */
    public function callProcedure($procName, &$arguments = [], $outputColumns = []);

    /**
     * It truncates a table
     *
     * @param string  $tableName The name of the table
     * @param string  $extra     An extra argument
     * @param boolean $force     if true, then it forces the operation. It is useful when the table has a foreign key,
     *                           however, it could be slow or breaks the consistency of the data because we ignore the
     *                           foreign keys.
     *
     * @return array|bool|null
     * @throws Exception
     */
    public function truncate($tableName, $extra, $force);

    /**
     * It resets the identity of a table (if any)
     *
     * @param string $tableName The name of the table
     * @param int    $newValue
     * @param string $column    [optional] The name of the column to reset.
     *
     * @return array|bool|null
     * @throws Exception
     */
    public function resetIdentity($tableName, $newValue = 0, $column = '');

    /**
     * It returns an associative array or a string with values of a table<br>
     * The results of the table depend on the kind of database. For example, sqlsrv returns the schema used (dbo),
     * while mysql returns the current schema (database).
     * <b>Example:</b><br>
     * <pre>
     * $this->getDefTableExtended('table'); // ['name','engine','schema','collation','description']
     * $this->getDefTableExtended('table',true); // "some description of the table"
     *
     * </pre>
     *
     * @param string $table           The name of the table
     * @param bool   $onlyDescription If true then it only returns a description
     *
     * @return array|string|null
     * @throws Exception
     */
    public function getDefTableExtended($table, $onlyDescription = false);

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
     * It includes primary key, key and unique keys<br>
     *
     * @param string      $table        The name of the table to analize.
     * @param bool        $returnSimple true= returns as a simple associative array<br>
     *                                  example:['id'=>'PRIMARY KEY','name'=>'FOREIGN KEY...']<br>
     *                                  false= returns as an associative array separated by parts<br>
     *                                  ['key','refcol','reftable','extra']
     * @param null|string $filter       if not null then it only returns keys that matches the condition
     *
     * @return array=["IndexName"=>'',"ColumnName"=>'',"is_unique"=>0,"is_primary_key"=>0,"TYPE"=>0]
     * @throws Exception
     */
    public function getDefTableKeys($table, $returnSimple, $filter = null);

    /**
     * Returns an associative array with the definition of foreign keys of a table.<br>
     * It includes foreign keys.
     *
     * @param string      $table        The name of the table to analize.
     * @param bool        $returnSimple true= returns as a simple associative array<br>
     *                                  example:['id'=>'PRIMARY KEY','name'=>'FOREIGN KEY...']<br>
     *                                  false= returns as an associative array separated by parts<br>
     *                                  ['key','refcol','reftable','extra']
     * @param null|string $filter       if not null then it only returns keys that matches the condition
     *
     * @param bool        $assocArray   If true then it returns an associative array (as value)
     * @return array
     * @throws Exception
     */
    public function getDefTableFK($table, $returnSimple, $filter = null, $assocArray = false);


    /**
     * It returns a default value depending on the type of the column.
     *
     * @param      $row
     * @param bool $default
     *
     * @return mixed
     */
    public function typeDict($row, $default = true);

    /**
     * Returns an associative array if the object exists. Otherwise it will return an empty array<br>
     * The fields of the associative array depends on the type of database
     *
     * @param string $type
     *
     * @return string|null (null on error)
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
     * @return string|string[]|null null on error
     * @throws Exception
     */
    public function objectList($type = 'table', $onlyName = false);

    public function columnTable($tableName);

    public function foreignKeyTable($tableName);

    /**
     * @param null|string $tableSequence
     * @param string      $method
     *
     * @return string the sql command to create a sequence
     * @throws Exception
     */
    public function createSequence($tableSequence = null, $method = 'snowflake');

    /**
     * It creates a store procedure<br>
     * <b>Example:</b><br>
     * <pre>
     * // arg1 and arg2 are "in" arguments:
     * $this->createProcedure('proc1','in arg1 int,in arg2 varchar(50)','//body here');
     * // arg1 and arg2 are "in" arguments:
     * $this->createProcedure('proc1',['arg1'=>'int','arg2'=>'varchar(50)'],'//body here');
     * // arg1 is "in", arg2 is "out":
     * $this->createProcedure('proc1',
     *                      [
     *                          ['in','arg1','int'],
     *                          ['out','arg2','varchar(50)']
     *                      ],'//body here');
     * // arg1 is "in", arg2 is "in":
     * $this->createProcedure('proc1',
     *                      [
     *                          ['arg1','int'],
     *                          ['arg2','varchar(50)']
     *                      ],'//body here');
     * </pre>
     *
     * @param string       $procedureName The name of the store procedure
     * @param array|string $arguments     The arguments. It could be an associative array, a string or an multiple array
     * @param string       $body          The body of the store procedure
     * @param string       $extra
     * @return false|int
     * @throws Exception
     */
    public function createProcedure($procedureName, $arguments = [], $body = '', $extra = '');


    public function getSequence($sequenceName);

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
     * Create foreign keys (other keys are ignored).
     *
     * @param string $tableName  The name of the table
     * @param array  $foreignKey Associative array with the foreign key.
     *
     * @return mixed
     * @throws Exception
     */
    public function createFK($tableName, $foreignKey);


    /**
     * It adds a limit operation for the query. It depends on the type of the database.
     * <b>Example:</b><br>
     * <pre>
     *      ->select("")->limit("10,20")->toList();
     * </pre>
     *
     * @param $sql
     * @return string
     *
     * @throws Exception
     */
    public function limit($sql);

    /**
     * It gets a primary key based in a query.<br>
     *
     * @param string       $query query (only for MYSQL) or name of the table
     * @param string|array $pk    Previous primary key (if the key is not found)
     *
     * @return array|mixed|string|false
     */
    public function getPK($query, $pk = null);
}