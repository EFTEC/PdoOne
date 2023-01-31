<?php /** @noinspection SqlNoDataSourceInspection */
/** @noinspection SqlDialectInspection */
/** @noinspection UnknownInspectionInspection */
/** @noinspection SqlResolve */
/** @noinspection TypeUnsafeComparisonInspection */
/** @noinspection DuplicatedCode */

namespace eftec\ext;

use eftec\PdoOne;
use stdClass;

/**
 * Class PdoOne_Test
 *
 * @see           https://github.com/EFTEC/PdoOne
 * @author        Jorge Castro Castillo
 * @copyright (c) Jorge Castro C. Dual Licence: MIT and Commercial License  https://github.com/EFTEC/PdoOne
 * @package       eftec
 */
class PdoOne_TestMockup implements PdoOne_IExt
{

    /** @var PdoOne */
    protected $parent;

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
        $this->parent->database_delimiter0 = '';
        $this->parent->database_delimiter1 = '';
        $this->parent->database_identityName = 'identity';
        PdoOne::$isoDate = 'Ymd';
        PdoOne::$isoDateTime = 'Ymd H:i:s';
        PdoOne::$isoDateTimeMs = 'Ymd H:i:s.u';
        PdoOne::$isoDateInput = 'Ymd';
        PdoOne::$isoDateInputTime = 'Ymd H:i:s';
        PdoOne::$isoDateInputTimeMs = 'Ymd H:i:s.u';
        $this->parent->isOpen = false;
        return '';
    }

    public function connect($cs, $alterSession = false): void
    {
        $this->parent->conn1 = new stdClass();
        $this->parent->user = '';
        $this->parent->pwd = '';
    }

    public function truncate(string $tableName, string $extra, bool $force): bool
    {
        return true;
    }

    public function resetIdentity(string $tableName, int $newValue = 0, string $column = ''): bool
    {
        return true;
    }

    public function getDefTableExtended(string $table, bool $onlyDescription = false)
    {
        //   $query="SELECT table_name as `table`,engine as `engine`, table_schema as `schema`,".
        //            " table_collation as `collation`, table_comment as `description` ".
        $result = ['name' => 'name', 'engine' => 'engine', 'schema' => $this->parent->db
            , 'collation' => 'collation', 'description' => 'description'];
        if ($onlyDescription) {
            return $result['description'];
        }
        return $result;
    }

    public function getDefTable(string $table): array
    {
        $defArray = [
            [
                'Field' => 'id',
                'Key' => 'PRI',
                'Type' => 'int',
                'Null' => 'NO',
                'Default' => '',
                'Extra' => '',
            ],
        ];
        $result = [];
        foreach ($defArray as $col) {
            /*if ($col['Key'] === 'PRI') {
                $pk = $col['Field'];
            }*/
            $value = $col['Type'];
            $value .= ($col['Null'] === 'NO') ? ' not null' : '';
            if ($col['Default'] === 'CURRENT_TIMESTAMP') {
                $value .= ' default CURRENT_TIMESTAMP';
            } else {
                $value .= ($col['Default']) ? ' default \'' . $col['Default'] . '\'' : '';
            }
            $col['Extra'] = str_replace('DEFAULT_GENERATED ', '', $col['Extra']);
            $value .= ($col['Extra']) ? ' ' . $col['Extra'] : '';
            $result[$col['Field']] = $value;
        }
        return $result;
    }

    public function getDefTableKeys(string $table, bool $returnSimple, string $filter = null): array
    {
        if ($returnSimple) {
            $columns = ['col1' => 'PRIMARY KEY'];
        } else {
            $columns = ['col1' => ['key' => 'PRIMARY KEY', 'refcol' => 'col', 'reftable' => 'table2', 'extra' => '']];
        }
        return $this->parent->filterKey($filter, $columns, $returnSimple);
    }

    public function getDefTableFK(string $table, bool $returnSimple, string $filter = null, bool $assocArray = false): array
    {
        if ($returnSimple) {
            $columns = ['col1' => 'FOREIGN KEY REFERENCES col [tableref](colref)'];
        } else {
            $columns = ['col1' => ['key' => 'FOREIGN KEY', 'refcol' => 'col', 'reftable' => 'table2', 'extra' => '']];
        }
        if ($assocArray) {
            return $columns;
        }
        return $this->parent->filterKey($filter, $columns, $returnSimple);
    }

    public function typeDict($row, bool $default = true): string
    {
        return '';
    }

    public function objectExist(string $type = 'table'): ?string
    {
        switch ($type) {
            case 'table':
                $query
                    = "SELECT * FROM information_schema.tables where table_schema='{$this->parent->db}' and table_name=?";
                break;
            case 'function':
                $query
                    = "SELECT * FROM INFORMATION_SCHEMA.ROUTINES where 
                                                ROUTINE_SCHEMA='{$this->parent->db}' 
                                            and ROUTINE_NAME=?
                                            and ROUTINE_TYPE='FUNCTION'";
                break;
            case 'procedure':
                $query
                    = "SELECT * FROM INFORMATION_SCHEMA.ROUTINES where 
                                                ROUTINE_SCHEMA='{$this->parent->db}' 
                                            and ROUTINE_NAME=?
                                            and ROUTINE_TYPE='PROCEDURE'";
                break;
            default:
                $this->parent->throwError("objectExist: type [$type] not defined for {$this->parent->databaseType}",
                    '');
                die(1);
        }
        return $query;
    }

    public function objectList(string $type = 'table', bool $onlyName = false)
    {
        switch ($type) {
            case 'table':
                $query
                    = "SELECT * FROM information_schema.tables where table_schema='{$this->parent->db}' and table_type='BASE TABLE'";
                if ($onlyName) {
                    $query = str_replace('*', 'table_name', $query);
                }
                break;
            case 'function':
                $query
                    = "SELECT * FROM INFORMATION_SCHEMA.ROUTINES where ROUTINE_SCHEMA='{$this->parent->db}'";
                if ($onlyName) {
                    $query = str_replace('*', 'routine_name', $query);
                }
                break;
            default:
                $this->parent->throwError("objectExist: type [$type] not defined for {$this->parent->databaseType}",
                    '');
                die(1);
        }
        return $query;
    }

    public function columnTable($tableName): string
    {
        return "SELECT column_name colname
								,data_type coltype
								,character_maximum_length colsize
								,numeric_precision colpres
								,numeric_scale colscale
								,if(column_key='PRI',1,0) iskey
								,if(extra='auto_increment',1,0)  isidentity
								,if(is_nullable='NO',1,0)  isnullable
					 	FROM information_schema.columns
						where table_schema='{$this->parent->db}' and table_name='$tableName'";
    }

    public function foreignKeyTable($tableName): string
    {
        return "SELECT col.name collocal
					,objrem.name tablerem
					,colrem.name colrem
					FROM columns fk
					where obj.name='$tableName' ";
    }

    public function createSequence(string $tableSequence = null, string $method = 'snowflake'): array
    {
        return ['CREATE TABLE'];
    }

    public function getSequence($sequenceName): string
    {
        $sequenceName = ($sequenceName == '') ? $this->parent->tableSequence : $sequenceName;
        return "select next_$sequenceName({$this->parent->nodeId}) id";
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
                $sqlType = "date";
                break;
            case 'datetime':
                $sqlType = "datetime";
                break;
            case 'timestamp':
                $sqlType = "timestamp";
                break;
            case 'string':
            default:
                $sqlType = "varchar($len) ";
                break;
        }
        return $sqlType;
    }


    public function createTable(
        string $tableName,
        array  $definition,
               $primaryKey = null,
        string $extra = '',
        string $extraOutside = ''
    ): string
    {
        $sql = "CREATE TABLE $tableName (";
        foreach ($definition as $key => $type) {
            $sql .= "$key $type,";
        }
        if ($primaryKey) {
            $sql .= " PRIMARY KEY(`$primaryKey`) ";
        } else {
            $sql = substr($sql, 0, -1);
        }
        $sql .= "$extra ) $extraOutside";
        return $sql;
    }

    public function createFK(string $tableName, array $foreignKeys): ?string
    {
        return "ALTER TABLE `$tableName` ADD CONSTRAINT `fk_{$tableName}_{key1}` FOREIGN KEY(`key1`);";
    }

    public function createIndex(string $tableName, array $indexesAndDef): string
    {
        $sql = '';
        foreach ($indexesAndDef as $key => $typeIndex) {
            $sql .= "ALTER TABLE `$tableName` ADD $typeIndex `idx_{$tableName}_$key` (`$key`) ;";
        }
        return $sql;
    }

    public function limit(?int $first, ?int $second): string
    {
        return $second === null ? ' limit ' . $first : " limit $first,$second";
    }
    public function now(): string
    {
        return 'select NOW() as NOW';
    }

    public function createTableKV($tableKV, $memoryKV = false): string
    {
        return $this->createTable($tableKV
            , ['KEYT' => 'VARCHAR(256)', 'VALUE' => 'MEDIUMTEXT', 'TIMESTAMP' => 'BIGINT']
            , 'KEYT', '', $memoryKV ? 'ENGINE = MEMORY' : '');
    }

    public function getPK($query, $pk = null): string
    {
        return 'primary_key';
    }

    public function callProcedure(string $procName, array &$arguments = [], array $outputColumns = [])
    {
        // TODO: Implement callProcedure() method.
    }

    public function createProcedure(string $procedureName, $arguments = [], string $body = '', string $extra = '')
    {
        // TODO: Implement createProcedure() method.
    }

    public function db($dbname): string
    {
        return 'use ' . $dbname;
    }
}
