<?php /** @noinspection UnknownInspectionInspection */

/** @noinspection GrazieInspection */

namespace eftec\ext;

use Exception;

/**
 * Interface PdoOne_IExt
 *
 * @package       eftec\ext
 * @author        Jorge Castro Castillo
 * @copyright (c) Jorge Castro C. Dual Licence: MIT and Commercial License  https://github.com/EFTEC/PdoOne
 * @see           https://github.com/EFTEC/PdoOne
 */
interface PdoOne_IExt
{
    public function construct($charset, $config);

    public function connect($cs, $alterSession): void;

    /**
     * It calls a store procedure.
     *
     * @param string $procName      The name of the store procedure.
     * @param array  $arguments     An associative array with the name of the argument and it's value
     * @param array  $outputColumns [optional] the name of the columns that must be returned.
     * @return mixed
     * @throws Exception
     */
    public function callProcedure(string $procName, array &$arguments = [], array $outputColumns = []);

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
    public function truncate(string $tableName, string $extra, bool $force);

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
    public function resetIdentity(string $tableName, int $newValue = 0, string $column = '');

    /**
     * It returns an associative array or a string with values of a table<br>
     * The results of the table depend on the kind of database. For example, sqlsrv returns the schema used (dbo),
     * while mysql returns the current schema (database).
     * <b>Example:</b><br>
     * ```php
     * $this->getDefTableExtended('table'); // ['name','engine','schema','collation','description']
     * $this->getDefTableExtended('table',true); // "some description of the table"
     *
     * ```
     *
     * @param string $table           The name of the table
     * @param bool   $onlyDescription If true then it only returns a description
     *
     * @return array|string|null  ['table','engine','schema','collation','description']
     * @throws Exception
     */
    public function getDefTableExtended(string $table, bool $onlyDescription = false);

    /**
     * Returns an associative array with the definition of a table (columns of the table).<br>
     * <b>Example:</b><br>
     * ```php
     * $this->getDefTable('table');
     * // ['col1'=>'int not null','col2'=>'varchar(50)']
     * ```
     *
     * @param string $table
     *
     * @return array
     * @throws Exception
     */
    public function getDefTable(string $table): array;

    /**
     * Returns an associative array with the definition of keys of a table.<br>
     * It includes primary key, key and unique keys<br>
     *
     * @param string      $table        The name of the table to analize.
     * @param bool        $returnSimple true= returns as a simple associative array<br>
     *                                  example:['id'=>'PRIMARY KEY','name'=>'FOREIGN KEY...']<br>
     *                                  false= returns as an associative array separated by parts<br>
     *                                  ['key','refcol','reftable','extra']
     * @param string|null $filter       if not null then it only returns keys that match the condition
     *
     * @return array=["IndexName"=>'',"ColumnName"=>'',"is_unique"=>0,"is_primary_key"=>0,"TYPE"=>0]
     * @throws Exception
     */
    public function getDefTableKeys(string $table, bool $returnSimple, string $filter = null): array;

    /**
     * Returns an associative array with the definition of foreign keys of a table.<br>
     * It includes foreign keys.
     *
     * @param string      $table        The name of the table to analize.
     * @param bool        $returnSimple true= returns as a simple associative array<br>
     *                                  example:['id'=>'PRIMARY KEY','name'=>'FOREIGN KEY...']<br>
     *                                  false= returns as an associative array separated by parts<br>
     *                                  ['key','refcol','reftable','extra']
     * @param string|null $filter       if not null then it only returns keys that match the condition
     *
     * @param bool        $assocArray   If true then it returns an associative array (as value)
     * @return array
     * @throws Exception
     */
    public function getDefTableFK(string $table, bool $returnSimple, string $filter = null, bool $assocArray = false): array;

    public function db($dbname);

    /**
     * It returns a default value depending on the type of the column.
     *
     * @param      $row
     * @param bool $default
     *
     * @return mixed
     */
    public function typeDict($row, bool $default = true);

    /**
     * Returns an associative array if the object exists. Otherwise, it will return an empty array<br>
     * The fields of the associative array depends on the type of database
     *
     * @param string $type
     *
     * @return string|null (null on error)
     * @throws Exception
     */
    public function objectExist(string $type = 'table'): ?string;

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
    public function objectList(string $type = 'table', bool $onlyName = false);

    public function columnTable(string $tableName);

    public function foreignKeyTable(string $tableName);

    /**
     * @param string|null $tableSequence
     * @param string      $method
     *
     * @return array the sql command to create a sequence
     * @throws Exception
     */
    public function createSequence(string $tableSequence = null, string $method = 'snowflake'): array;

    /**
     * It creates a store procedure<br>
     * <b>Example:</b><br>
     * ```php
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
     * ```
     *
     * @param string       $procedureName The name of the store procedure
     * @param array|string $arguments     The arguments. It could be an associative array, a string or a multiple array
     * @param string       $body          The body of the store procedure
     * @param string       $extra
     * @return false|int
     * @throws Exception
     */
    public function createProcedure(string $procedureName, $arguments = [], string $body = '', string $extra = '');

    public function getSequence(string $sequenceName);

    public function translateExtra(string $universalExtra): string;

    public function translateType(string $universalType, $len = null): string;

    /**
     * DCL command. It creates a database.<br>
     * <b>Example:</b>
     * ```php
     * $this->createtable("customer"
     *      ,['id'=>'int','name'=>'varchar(50)']
     *      ,'id');
     * $this->createtable("customer"
     *      ,['id'=>'int','name'=>'varchar(50)']
     *      ,['id'=>'PRIMARY KEY','name'=>'KEY']);
     * ```
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
    public function createTable(string $tableName, array $definition, $primaryKey = null, string $extra = '',
                                string $extraOutside = ''): string;

    /**
     * DCL command. It adds a column in a table.<br>
     * <b>Example:</b>
     * ```php
     * $this->addColumn("customer",['id'=>'int']);
     * ```
     * @param string $tableName                The name of the table
     * @param array  $definition               An associative array with the definition of the column.<br>
     *                                         The key is used as the name of the field
     * @return string
     */
    public function addColumn(string $tableName, array $definition): string;

    public function deleteColumn(string $tableName, $columnName): string;

    /**
     * Create foreign keys (other keys are ignored).
     *
     * @param string $tableName   The name of the table
     * @param array  $foreignKeys Associative array with the foreign key ['column'='FOREIGN KEY'].
     *
     * @return null|string the sql resultant
     * @throws Exception
     */
    public function createFK(string $tableName, array $foreignKeys): ?string;

    /**
     * Function to create a sql to create indexes.
     * @param string $tableName     The name of the table
     * @param array  $indexesAndDef Associative array with the indexes ['COLUMN'=>'TYPE INDEX'].
     * @return string the sql
     */
    public function createIndex(string $tableName, array $indexesAndDef): string;

    /**
     * It adds a limit operation for the query. It depends on the type of the database.
     * <b>Example:</b><br>
     * ```php
     *      ->select("")->limit("10,20")->toList();
     *      ->select("")->limit(10,20)->toList();
     * ```
     *
     * @param int|null $first  The whole expression separated by comma, or the first expression (the initial row)
     * @param int|null $second The numbers of row to read. If null, then it uses $sql.
     * @return string
     *
     * @throws Exception
     */
    public function limit(?int $first, ?int $second): string;

    public function now(): string;

    public function createTableKV($tableKV, $memoryKV = false): string;

    /**
     * It gets a primary key based in a query.<br>
     *
     * @param string       $query query (only for MYSQL) or name of the table
     * @param string|array $pk    Previous primary key (if the key is not found)
     *
     * @return array|mixed|string|false
     */
    public function getPK(string $query, $pk = null);
}
