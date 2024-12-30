<?php /** @noinspection SqlNoDataSourceInspection */
/** @noinspection SqlDialectInspection */
/** @noinspection PhpMissingParamTypeInspection */
/** @noinspection UnknownInspectionInspection */
/** @noinspection SqlWithoutWhere */
/** @noinspection SqlResolve */
/** @noinspection PhpUnusedParameterInspection */
/** @noinspection PhpUnreachableStatementInspection */
/** @noinspection AccessModifierPresentedInspection */
/** @noinspection TypeUnsafeComparisonInspection */
/** @noinspection DuplicatedCode */

namespace eftec\ext;

use eftec\PdoOne;
use Exception;
use JsonException;
use PDO;
use PDOStatement;
use RuntimeException;

/**
 * Class PdoOne_Pgsql
 *
 * @see           https://github.com/EFTEC/PdoOne
 * @author        Jorge Castro Castillo
 * @copyright (c) Jorge Castro C. Dual Licence: MIT and Commercial License  https://github.com/EFTEC/PdoOne
 * @package       eftec
 */
class PdoOne_Pgsql implements PdoOne_IExt
{

    /** @var PdoOne|null */
    protected ?PdoOne $parent;
    private array $config = ['noquote' => false];

    /**
     * PdoOne_Mysql constructor.
     *
     * @param PdoOne $parent
     */
    public function __construct(PdoOne $parent)
    {
        $this->parent = $parent;
    }

    public function construct($charset, $config): string
    {
        $this->config = array_merge($this->config, $config);
        $this->parent->database_delimiter0 = '"';
        $this->parent->database_delimiter1 = '"';
        $this->parent->database_identityName = 'IDENTITY';
        // you should check the correct value at select * from nls_session_parameterswhere parameter = 'NLS_DATE_FORMAT';
        PdoOne::$dateFormat = 'Y-m-d';
        PdoOne::$dateTimeFormat = 'Y-m-d H:i:s';
        PdoOne::$dateTimeMicroFormat = 'Y-m-d H:i:s.u';
        PdoOne::$isoDateInput = 'Y-m-d';
        PdoOne::$isoDateInputTime = 'Y-m-d H:i:s';
        PdoOne::$isoDateInputTimeMs = 'Y-m-d H:i:s.u';
        $this->parent->isOpen = false;
        return '';
    }

    public function connect($cs, $alterSession = true): void
    {
        // pgsql:host=localhost;port=5432;dbname=dvdrental;
        $cstring = "pgsql:host={$this->parent->server};options='--client_encoding=UTF8';port=5432;user={$this->parent->user};password={$this->parent->pwd};";
        $this->parent->conn1 = new PDO($cstring);
        $this->parent->user = '';
        $this->parent->pwd = '';
        $this->parent->conn1->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $this->parent->conn1->setAttribute(PDO::ATTR_AUTOCOMMIT, true);
        $this->parent->conn1->setAttribute(PDO::ATTR_PERSISTENT, true);
        //$this->parent->conn1->setAttribute(PDO::ATTR_STRINGIFY_FETCHES, false); // otherwise return "0.1" as "0,1"
        //$this->parent->conn1->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        if ($alterSession) {
          /*  $this->parent->conn1->exec("ALTER SESSION SET NLS_DATE_FORMAT='YYYY-MM-DD HH24:MI:SS'
              NLS_TIMESTAMP_FORMAT='YYYY-MM-DD HH24:MI:SS'
              NLS_TIMESTAMP_TZ_FORMAT='YYYY-MM-DD HH24:MI:SS'
              NLS_NUMERIC_CHARACTERS = '.,'");*/
        }
        $this->parent->isOpen = true; // we mark for open only to set the current schema
        $this->parent->db($this->parent->db);
        $this->parent->isOpen = false; // it will open in PdoOne (if no error).
    }

    public function truncate(string $tableName, string $extra, bool $force)
    {
        if (!$force) {
            $sql = 'truncate table ' . $this->parent->addDelimiter($tableName) . " $extra";
            return $this->parent->runRawQuery($sql);
        }
        $sql = "DELETE FROM " . $this->parent->addDelimiter($tableName) . " $extra";
        return $this->parent->runRawQuery($sql);
    }

    /**
     * @param string $tableName
     * @param int    $newValue
     * @param string $column
     * @return array|bool|PDOStatement|null
     * @throws Exception
     */
    public function resetIdentity(string $tableName, int $newValue = 0, string $column = '')
    {
        $sql = "ALTER SEQUENCE {$tableName}_{$column}_seq RESTART WITH $newValue";
        return $this->parent->runRawQuery($sql);
    }

    /**
     * @param string $table
     * @param false  $onlyDescription
     *
     * @return array|bool|mixed|PDOStatement|null
     * @throws Exception
     */
    public function getDefTableExtended(string $table, bool $onlyDescription = false)
    {
        $query = "SELECT t.table_name as table_name
                   ,'' as engine
                    ,t.table_catalog schema
                    ,''  collation
                    ,obj_description(pgc.oid, 'pg_class') description
                    FROM information_schema.tables t
                    INNER JOIN pg_catalog.pg_class pgc
                    ON t.table_name = pgc.relname 
                    WHERE t.table_type='BASE TABLE'
                    AND t.table_name=?
                    and t.table_schema=?";

        $result = $this->parent->runRawQuery($query, [$table, $this->parent->db]);
        $result= array_change_key_case($result[0],0); // CASE_LOWER
        if ($onlyDescription) {
            return $result['description'];
        }
        return $result;
    }

    /**
     * todo pending
     * @param string $table
     * @return array
     * @throws Exception
     */
    public function getDefTable(string $table): array
    {
        /** @var array $result =array(["name"=>'',"is_identity"=>0,"increment_value"=>0,"seed_value"=>0]) */
        // throw new RuntimeException("no yet implemented");
        $raw = $this->parent->runRawQuery('select TO_CHAR(DBMS_METADATA.GET_DDL(\'TABLE\',?)) COL from dual', [$table]);
        if (!isset($raw[0]['COL'])) {
            return [];
        }
        $r = $raw[0]['COL'];
        $p0 = strpos($r, '(') + 1;
        $p1a = strpos($r, ' TABLESPACE ', $p0);
        $p1b = strpos($r, ' CONSTRAINT ', $p0);
        $p1c = strpos($r, ' USING ', $p0);
        $p1 = min($p1a, $p1b, $p1c);
        $rcut = trim(substr($r, $p0, $p1 - $p0), " \t\n\r\0\x0B,");
        $cols = explode(", \n", $rcut);
        $result = [];
        foreach ($cols as $v) {
            $key = explode(' ', $v . ' ', 2); // the last space avoid to return $key as an array with a single value.
            $result[PdoOne::removeDoubleQuotes($key[0])] = trim($key[1]);
        }
        return $result;
    }

    /**
     * todo pending
     * It gets a column from INFORMATION_SCHEMA.COLUMNS and returns a type of the form type,type(size)
     * or type(size,size)
     *
     * @param array $col An associative array with the data of the column
     *
     * @return string
     * @noinspection PhpUnused
     */
    protected static function pgsql_getType($col): string
    {
        throw new RuntimeException("no yet implemented");
        /** @var array $exclusion type of columns that don't use size */
        $exclusion = ['int', 'long', 'tinyint', 'year', 'bigint', 'bit', 'smallint', 'float', 'money'];
        if (in_array($col['DATA_TYPE'], $exclusion, true) !== false) {
            return $col['DATA_TYPE'];
        }
        if ($col['NUMERIC_SCALE']) {
            $result = "{$col['DATA_TYPE']}({$col['NUMERIC_PRECISION']},{$col['NUMERIC_SCALE']})";
        } elseif ($col['NUMERIC_PRECISION'] || $col['CHARACTER_MAXIMUM_LENGTH']) {
            $result = "{$col['DATA_TYPE']}(" . ($col['CHARACTER_MAXIMUM_LENGTH'] + $col['NUMERIC_PRECISION']) . ')';
        } else {
            $result = $col['DATA_TYPE'];
        }
        return $result;
    }

    /**
     * todo: pending
     * @param string      $table
     * @param bool        $returnSimple
     * @param string|null $filter
     * @return array
     * @throws Exception
     */
    public function getDefTableKeys(string $table, bool $returnSimple, ?string $filter = null): array
    {
        $columns = [];
        /** @var array $result =array(["IndexName"=>'',"ColumnName"=>'',"is_unique"=>0,"is_primary_key"=>0,"TYPE"=>0]) */
        $pks = $this->getPK($table);
        $result =
            $this->parent->select('SELECT ALL_indexes.INDEX_NAME "IndexName",all_ind_columns.COLUMN_NAME "ColumnName",
                        (CASE WHEN UNIQUENESS = \'UNIQUE\' THEN 1 ELSE 0 END) "is_unique",0 "is_primary_key",0 "TYPE"')
                ->from('ALL_indexes')
                ->innerjoin('all_ind_columns on ALL_indexes.index_name=all_ind_columns.index_name ')
                ->where("ALL_indexes.table_name='$table' and ALL_indexes.table_owner='{$this->parent->db}'")
                ->order('"IndexName"')->toList();
        foreach ($result as $k => $item) {
            if (in_array($item['ColumnName'], $pks, true)) {
                $type = 'PRIMARY KEY';
                $result[$k]['is_primary_key'] = 1;
            } elseif ($item['is_unique']) {
                $type = 'UNIQUE KEY';
            } else {
                $type = 'KEY';
            }
            if ($filter === null || $filter === $type) {
                if ($returnSimple) {
                    $columns[$item['ColumnName']] = $type;
                } else {
                    $columns[$item['ColumnName']] = PdoOne::newColFK($type, '', '');
                }
            }
        }
        return $columns; //$this->parent->filterKey($filter, $columns, $returnSimple);
    }

    /**
     * todo: pending
     * @param string      $table
     * @param bool        $returnSimple
     * @param string|null $filter
     * @param bool        $assocArray
     * @return array
     * @throws Exception
     * todo: missing checking
     */
    public function getDefTableFK(string $table, bool $returnSimple, ?string $filter = null, bool $assocArray = false): array
    {
        $columns = [];
        /** @var array $fkArr =array(["foreign_key_name"=>'',"referencing_table_name"=>'',"COLUMN_NAME"=>''
         * ,"referenced_table_name"=>'',"referenced_column_name"=>'',"referenced_schema_name"=>''
         * ,"update_referential_action_desc"=>'',"delete_referential_action_desc"=>''])
         */
        $fkArr = $this->parent->select('SELECT 
                a.constraint_name "foreign_key_name",
                a.table_name "referencing_table_name",
                a.column_name "COLUMN_NAME",
                c_pk.table_name "referenced_table_name",
                b.column_name "referenced_column_name",
                c_pk.OWNER "referenced_schema_name",
                \'\' "update_referential_action_desc",
                c.DELETE_RULE "delete_referential_action_desc"
            FROM
                user_cons_columns a
            JOIN all_constraints c ON
                a.owner = c.owner
                AND a.constraint_name = c.constraint_name
            JOIN all_constraints c_pk ON
                c.r_owner = c_pk.owner
                AND c.r_constraint_name = c_pk.constraint_name
            LEFT JOIN USER_CONS_COLUMNS b ON
                b.OWNER = C_PK.owner
                AND b.CONSTRAINT_NAME = c_pk.CONSTRAINT_NAME')
            ->where("c.constraint_type = 'R' AND a.table_name=?", [$table])
            ->order('a.column_name')->toList();
        foreach ($fkArr as $item) {
            $extra = ($item['update_referential_action_desc'] !== 'NO_ACTION') ? ' ON UPDATE ' .
                str_replace('_', ' ', $item['update_referential_action_desc']) : '';
            $extra .= ($item['delete_referential_action_desc'] !== 'NO_ACTION') ? ' ON DELETE ' .
                str_replace('_', ' ', $item['delete_referential_action_desc']) : '';
            //FOREIGN KEY REFERENCES TABLEREF(COLREF)
            if ($returnSimple) {
                $columns[$item['COLUMN_NAME']] =
                    'FOREIGN KEY REFERENCES ' . $this->parent->addQuote($item['referenced_table_name'])
                    . '(' . $this->parent->addQuote($item['referenced_column_name']) . ')' . $extra;
            } else {
                $columns[$item['COLUMN_NAME']] = PdoOne::newColFK('FOREIGN KEY'
                    , $item['referenced_column_name']
                    , $item['referenced_table_name']
                    , $extra
                    , $item['foreign_key_name']); // fk_name
                $columns[PdoOne::$prefixBase . $item['COLUMN_NAME']] = PdoOne::newColFK(
                    'MANYTOONE'
                    , $item['referenced_column_name']
                    , $item['referenced_table_name']
                    , $extra
                    , $item['foreign_key_name']); // fk_name
            }
        }
        if ($assocArray) {
            return $columns;
        }
        return $this->parent->filterKey($filter, $columns, $returnSimple);
    }

    /**
     * todo: pending
     * @param      $row
     * @param bool $default
     * @return string
     */
    function typeDict($row, bool $default = true): string
    {
        $type = strtolower(@$row['pgsql:decl_type']);
        switch ($type) {
            case 'varchar':
            case 'varchar2':
            case 'nvarchar':
            case 'nvarchar2':
            case 'text':
            case 'ntext':
            case 'char':
            case 'nchar':
            case 'binary':
            case 'varbinary':
            case 'timestamp':
            case 'time':
            case 'date':
            case 'smalldatetime':
            case 'datetime2':
            case 'datetimeoffset':
            case 'datetime':
            case 'image':
                return ($default) ? "''" : 'string';
            case 'long':
            case 'tinyint':
            case 'number':
            case 'int':
            case 'sql_variant':
            case 'int identity':
            case 'year':
            case 'bigint':
            case 'numeric':
            case 'bit':
            case 'smallint':
                return ($default) ? '0' : 'int';
            case 'decimal':
            case 'smallmoney':
            case 'money':
            case 'double':
            case 'real':
            case 'float':
                return ($default) ? '0.0' : 'float';
            default:
                return '???pgsql:' . $type;
        }
    }

    public function objectExist(string $type = 'table'): ?string
    {
        switch ($type) {
            case 'table':
                $query = 'SELECT * FROM pg_catalog.pg_tables where tablename=? and tableowner=?';
                break;
            case 'procedure':
                $query = "SELECT * FROM ALL_OBJECTS WHERE routine_type='PROCEDURE' and routine_name=? and routine_schema=?";
                break;
            case 'function':
                $query = "SELECT * FROM ALL_OBJECTS WHERE routine_type='FUNCTION' and routine_name=? and routine_schema=?";
                break;
            default:
                $this->parent->throwError("objectExist: type [$type] not defined for {$this->parent->databaseType}", '');
                return null;
        }
        return $query;
    }

    /**
     * @param string $type
     * @param bool   $onlyName
     * @return array|string|string[]|null
     * @throws JsonException
     */
    public function objectList(string $type = 'table', bool $onlyName = false)
    {
        switch ($type) {
            case 'table':
                $query = "select * from pg_catalog.pg_tables where schemaname=?";
                if ($onlyName) {
                    $query = str_replace('*', 'tablename name', $query);
                }
                break;
            case 'procedure':
                $query = "SELECT routine_name FROM information_schema.routines WHERE routine_type='PROCEDURE' and routine_schema=?;";
                if ($onlyName) {
                    $query = str_replace('*', 'routine_name name', $query);
                }
                break;
            case 'function':
                $query = "SELECT routine_name FROM information_schema.routines WHERE routine_type='FUNCTION' and routine_schema=?;";
                if ($onlyName) {
                    $query = str_replace('*', 'routine_name name', $query);
                }
                break;
            default:
                $this->parent->throwError("objectExist: type [$type] not defined for {$this->parent->databaseType}", '');
                return null;
        }
        return $query;
    }

    /**
     * @inheritDoc
     */
    public function columnTable($tableName):string
    {
        return "SELECT 
            col.column_name as colname,
                col.data_type as coltype,
                COALESCE(col.numeric_precision,0)+COALESCE(character_maximum_length,0) as colsize,
                col.numeric_precision as colpres,
                col.numeric_scale as colscale,
              (case when (ts.constraint_type is null) then 0 else 1 end) as iskey,
                col.is_identity,
                col.is_nullable	
              FROM information_schema.columns col
              left join information_schema.key_column_usage kcu
                on col.table_name=kcu.table_name
                and col.table_schema=kcu.table_schema    
                and col.column_name  =kcu.column_name
                left join information_schema.table_constraints ts
                      on kcu.table_schema = ts.table_schema
                      and kcu.table_name = ts.table_name
                      and kcu.table_catalog = ts.table_catalog		
                  and ts.constraint_type='PRIMARY KEY'   
             WHERE col.table_schema = '{$this->parent->db}'
               AND col.table_name   = '$tableName'";
    }

    /**
     * @param $tableName
     * @return string
     */
    public function foreignKeyTable($tableName): string
    {
        return "SELECT
                    kcu.column_name as collocal, 	
                    ccu.table_name AS tablerem,
                    ccu.column_name AS colrem ,
                    tc.constraint_name as fk_name
                FROM information_schema.table_constraints AS tc 
                JOIN information_schema.key_column_usage AS kcu
                    ON tc.constraint_name = kcu.constraint_name
                    AND tc.table_schema = kcu.table_schema
                JOIN information_schema.constraint_column_usage AS ccu
                    ON ccu.constraint_name = tc.constraint_name
                WHERE tc.constraint_type = 'FOREIGN KEY'
                    AND tc.table_schema='{$this->parent->db}'
                    AND tc.table_name='$tableName'";
    }

    public function createSequence(?string $tableSequence = null, string $method = 'snowflake'): array
    {
        return ["CREATE SEQUENCE $tableSequence
                    INCREMENT BY 1
				    START WITH 1"];
    }

    public function getSequence($sequenceName): string
    {
        $sequenceName = ($sequenceName == '') ? $this->parent->tableSequence : $sequenceName;
        return "select \"$sequenceName\".nextval as \"id\" from dual";
    }

    public function translateExtra($universalExtra): string
    {
        /** @noinspection DegradedSwitchInspection */
        switch ($universalExtra) {
            case 'autonumeric':
                $sqlExtra = 'GENERATED BY DEFAULT AS IDENTITY';
                break;
            default:
                $sqlExtra = $universalExtra;
        }
        return $sqlExtra;
    }

    public function translateType($universalType, $len = null): string
    {
        switch ($universalType) {
            case 'int':
                $sqlType = "int";
                break;
            case 'long':
                $sqlType = "long";
                break;
            case 'decimal':
                $sqlType = "decimal($len) ";
                break;
            case 'bool':
                $sqlType = "char(1)";
                break;
            case 'date':
            case 'datetime':
                $sqlType = "date";
                break;
            case 'timestamp':
                $sqlType = "timestamp";
                break;
            case 'string':
            default:
                $sqlType = "varchar2($len) ";
                break;
        }
        return $sqlType;
    }

    public function createTable(string $tableName, array $definition, $primaryKey = null, string $extra = '', string $extraOutside = ''): string
    {
        $sql = "CREATE TABLE \"$tableName\" (";
        foreach ($definition as $key => $type) {
            $sql .= "\"$key\" $type,";
        }
        $sql = rtrim($sql, ',');
        $sql .= "$extra ); ";
        if ($primaryKey !== null) {
            if (!is_array($primaryKey)) {
                $pks = is_array($primaryKey) ? implode(',', $primaryKey) : $primaryKey;
                $sql .= "ALTER TABLE \"$tableName\" ADD (
                      CONSTRAINT {$tableName}_PK PRIMARY KEY ($pks)
                      ENABLE VALIDATE) $extraOutside;";
            } else {
                $hasPK = false;
                // ['field'=>'FOREIGN KEY REFERENCES TABLEREF(COLREF) ...]
                foreach ($primaryKey as $key => $value) {
                    $p0 = stripos($value . ' ', 'KEY ');
                    if ($p0 === false) {
                        trigger_error('createTable: Key with a wrong syntax. Example: "PRIMARY KEY.." ,
                                 "KEY...", "UNIQUE KEY..." "FOREIGN KEY.." ');
                        break;
                    }
                    $type = strtoupper(trim(substr($value, 0, $p0)));
                    $value = substr($value, $p0 + 4);
                    switch ($type) {
                        case 'PRIMARY':
                            if (!$hasPK) {
                                $sql .= "ALTER TABLE \"$tableName\" ADD ( CONSTRAINT PK_$tableName PRIMARY KEY(\"$key*pk*\") ENABLE VALIDATE);";
                                $hasPK = true;
                            } else {
                                $sql = str_replace('*pk*', ",$key", $sql); // we add an extra primary key
                            }
                            break;
                        case '':
                            $sql .= "CREATE INDEX \"{$tableName}_{$key}_KEY\" ON $tableName (\"$key\") $value;";
                            break;
                        case 'UNIQUE':
                            $sql .= "CREATE UNIQUE INDEX \"{$tableName}_{$key}_UK\" ON $tableName (\"$key\") $value;";
                            break;
                        case 'FOREIGN':
                            $sql .= "ALTER TABLE \"$tableName\" ADD CONSTRAINT {$tableName}_{$key}_FK FOREIGN KEY (\"$key\") $value;";
                            break;
                        default:
                            trigger_error("createTable: [$type KEY] not defined");
                            break;
                    }
                }
                $sql = str_replace('*pk*', '', $sql);
            }
        }
        return $sql;
    }
    public function addColumn(string $tableName,array $definition):string {
        $sql = "ALTER TABLE \"$tableName\" ADD (";
        foreach ($definition as $key => $type) {
            $sql .= "\"$key\" $type,";
        }
        return rtrim($sql,',').')';
    }
    public function deleteColumn(string $tableName, $columnName): string {
        if(!is_array($columnName)) {
            $columnName=[$columnName];
        }
        $sql = "ALTER TABLE \"$tableName\" DROP(";
        foreach($columnName as $c) {
            $sql .= "$c,";
        }
        return rtrim($sql,';').')';
    }

    public function createFK(string $tableName, array $foreignKeys): ?string
    {
        $sql = '';
        foreach ($foreignKeys as $key => $value) {
            $p0 = stripos($value . ' ', 'KEY ');
            if ($p0 === false) {
                trigger_error('createFK: Key with a wrong syntax. Example: "PRIMARY KEY.." ,
                                 "KEY...", "UNIQUE KEY..." "FOREIGN KEY.." ');
                return null;
            }
            $type = strtoupper(trim(substr($value, 0, $p0)));
            $value = substr($value, $p0 + 4);
            if ($type === 'FOREIGN') {
                $sql .= "ALTER TABLE \"$tableName\" ADD CONSTRAINT {$tableName}_fk_$key FOREIGN KEY (\"$key\") $value;";
            }
        }
        return $sql;
    }

    public function createIndex(string $tableName, array $indexesAndDef): string
    {
        throw new RuntimeException('not tested');
        $sql = '';
        foreach ($indexesAndDef as $key => $typeIndex) {
            $sql .= "ALTER TABLE `$tableName` ADD $typeIndex `idx_{$tableName}_$key` (`$key`) ;";
        }
        return $sql;
    }

    /**
     * For 12c and higher.
     *
     * @param int|null $first
     * @param int|null $second
     * @return string
     */
    public function limit(?int $first, ?int $second): string
    {
        if ($second === null) {
            return " OFFSET 0 ROWS FETCH NEXT $first ROWS ONLY";
        }
        return " OFFSET $first ROWS FETCH NEXT $second ROWS ONLY";
    }

    /**
     * todo: not tested
     * @return string
     */
    public function now(): string
    {
        return "select TO_CHAR(SYSDATE, 'YYYY-DD-MM HH24:MI:SS') as NOW from dual";
    }

    /**
     *
     * @param string $tableKV
     * @param bool   $memoryKV You must set this value in the tablespace and not here.
     * @return string
     */
    public function createTableKV($tableKV, $memoryKV = false): string
    {
        return $this->createTable($tableKV
            , ['KEYT' => 'VARCHAR2(256)', 'VALUE' => 'CLOB', 'TIMESTAMP' => 'BIGINT']
            , 'KEYT');
    }

    public function getPK($query, $pk = null)
    {
        try {
            $pkResult = [];
            if ($this->parent->isQuery($query)) {
                if (!$pk) {
                    return 'PGSQL: unable to find pk via query. Use the name of the table';
                }
            } else {
                $q = "select kcu.column_name as \"RESULT\"
                        from information_schema.table_constraints tco
                        join information_schema.key_column_usage kcu 
                             on kcu.constraint_name = tco.constraint_name
                             and kcu.constraint_schema = tco.constraint_schema
                             and kcu.constraint_name = tco.constraint_name
                        where tco.constraint_type = 'PRIMARY KEY'	
                            and kcu.table_name=?
                            and kcu.table_schema=?";
                $r = $this->parent->runRawQuery($q, [$query, $this->parent->db]);
                if (count($r) >= 1) {
                    foreach ($r as $item) {
                        $pkResult[] = $item['RESULT'];
                    }
                } else {
                    $pkResult[] = '??nopk??';
                }
            }
            $pkAsArray = (is_array($pk)) ? $pk : array($pk);
            return count($pkResult) === 0 ? $pkAsArray : $pkResult;
        } catch (Exception $ex) {
            return false;
        }
    }
    public function callProcedure(string $procName, array &$arguments = [], array $outputColumns = [])
    {
        // TODO: Implement callProcedure() method.
        throw new RuntimeException('not defined');
        return null;
    }

    public function createProcedure(string $procedureName, $arguments = [], string $body = '', string $extra = ''): string
    {
        if (is_array($arguments)) {
            $sqlArgs = '';
            foreach ($arguments as $k => $v) {
                if (is_array($v)) {
                    if (count($v) > 2) {
                        $sqlArgs .= "$v[1] $v[0] $v[2],";
                    } else {
                        $sqlArgs .= "$v[1] in $v[2],";
                    }
                } else {
                    $sqlArgs .= "$k in $v,";
                }
            }
            $sqlArgs = trim($sqlArgs, ',');
        } else {
            $sqlArgs = $arguments;
        }
        $sql = "CREATE OR REPLACE PROCEDURE \"$procedureName\" ($sqlArgs) $extra AS\n";
        $sql .= "BEGIN\n$body\nEND $procedureName;";
        return $sql;
    }

    public function db($dbname): string
    {
        return "SET search_path TO $dbname";
    }
}
