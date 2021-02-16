<?php
/** @noinspection TypeUnsafeComparisonInspection */

/** @noinspection DuplicatedCode */

namespace eftec\ext;


use eftec\PdoOne;
use Exception;
use PDO;

/**
 * Class PdoOne_Mysql
 *
 * @see           https://github.com/EFTEC/PdoOne
 * @author        Jorge Castro Castillo
 * @copyright (c) Jorge Castro C. MIT License  https://github.com/EFTEC/PdoOne
 * @package       eftec
 */
class PdoOne_Mysql implements PdoOne_IExt
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

    public function construct($charset)
    {
        $this->parent->database_delimiter0 = '`';
        $this->parent->database_delimiter1 = '`';
        $this->parent->database_identityName = 'auto_increment';
        $charset = ($charset == null) ? 'utf8' : $charset;
        PdoOne::$dateFormat = 'Y-m-d';
        PdoOne::$dateTimeFormat = 'Y-m-d H:i:s';
        PdoOne::$dateTimeMicroFormat = 'Y-m-d H:i:s.u';
        PdoOne::$isoDateInput = 'Y-m-d';
        PdoOne::$isoDateInputTime = 'Y-m-d H:i:s';
        PdoOne::$isoDateInputTimeMs = 'Y-m-d H:i:s.u';        
        $this->parent->isOpen = false;

        return $charset;
    }

    public function connect($cs, $alterSession=false)
    {
        $this->parent->conn1
            = new PDO("{$this->parent->databaseType}:host={$this->parent->server};dbname={$this->parent->db}{$cs}",
            $this->parent->user, $this->parent->pwd);
        $this->parent->user = '';
        $this->parent->pwd = '';
        $this->parent->conn1->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
       
    }
    
    public function truncate($tableName,$extra,$force) {
        if(!$force) {
            $sql = 'truncate table ' . $this->parent->addDelimiter($tableName) . " $extra";
            return $this->parent->runRawQuery($sql, null, true);
        }
        $sql="SET FOREIGN_KEY_CHECKS = 0;
            TRUNCATE ".$this->parent->addDelimiter($tableName)." $extra;
            SET FOREIGN_KEY_CHECKS = 1;";
        return $this->parent->runMultipleRawQuery($sql, true);
    }

    public function resetIdentity($tableName,$newValue=0,$column='') {
        $sql="ALTER TABLE " . $this->parent->addDelimiter($tableName) . " AUTO_INCREMENT = $newValue";
        return $this->parent->runRawQuery($sql, null, true);
    }

    /**
     * @param string $table
     * @param bool $onlyDescription
     *
     * @return array|string|null
     * @throws Exception
     */
    public function getDefTableExtended($table,$onlyDescription=false) {
        $query="SELECT table_name as `table`,engine as `engine`, table_schema as `schema`,".
            " table_collation as `collation`, table_comment as `description` ".
            "FROM information_schema.tables WHERE table_schema = '?' and table_name='?'";
        $result=$this->parent->runRawQuery($query,[$this->parent->db,$table],true);
        if($onlyDescription) {
            return $result['description'];
        }
        return $result;
    }

    public function getDefTable($table)
    {
        $defArray = $this->parent->runRawQuery('show columns from ' . $table, [], true);
        $result = [];
        foreach ($defArray as $col) {
            /*if ($col['Key'] === 'PRI') {
                $pk = $col['Field'];
            }*/
            $type = $col['Type'];
            $type = str_replace('int(11)', 'int', $type);
            $value = $type;
            $value .= ($col['Null'] === 'NO') ? ' not null' : '';
            if ($col['Default'] === 'CURRENT_TIMESTAMP') {
                $value .= ' default CURRENT_TIMESTAMP';
            } else {
                $value .= ($col['Default']) ? ' default ' . PdoOne::addParenthesis($col['Default'], "'", "'") . '' : '';
            }
            $col['Extra'] = str_replace('DEFAULT_GENERATED ', '', $col['Extra']);
            $value .= ($col['Extra']) ? ' ' . $col['Extra'] : '';

            $result[$col['Field']] = $value;
        }
        return $result;
    }


    public function getDefTableFK($table, $returnSimple, $filter = null, $assocArray = false)
    {
        $columns = [];
        /** @var array $result =array(["CONSTRAINT_NAME"=>'',"COLUMN_NAME"=>'',"REFERENCED_TABLE_NAME"=>''
         * ,"REFERENCED_COLUMN_NAME"=>'',"UPDATE_RULE"=>'',"DELETE_RULE"=>''])
         */
        $fkArr = $this->parent->select('k.CONSTRAINT_NAME,k.COLUMN_NAME,k.REFERENCED_TABLE_NAME,k.REFERENCED_COLUMN_NAME
                    ,c.UPDATE_RULE,c.DELETE_RULE')->from('INFORMATION_SCHEMA.KEY_COLUMN_USAGE k')->innerjoin('information_schema.REFERENTIAL_CONSTRAINTS c 
                        ON k.referenced_table_schema=c.CONSTRAINT_SCHEMA AND k.CONSTRAINT_NAME=c.CONSTRAINT_NAME')
            ->where('k.TABLE_SCHEMA=? AND k.TABLE_NAME = ?', [ $this->parent->db,$table])
            ->order('k.COLUMN_NAME')->toList();

        /*echo "table:";
        var_dump($table);
        echo "<pre>";
        var_dump($fkArr);
        echo "</pre>";*/
        //var_dump($this->parent->lastQuery);
        foreach ($fkArr as $item) {
            $txt = "FOREIGN KEY REFERENCES`{$item['REFERENCED_TABLE_NAME']}`(`{$item['REFERENCED_COLUMN_NAME']}`)";
            $extra = '';
            if ($item['UPDATE_RULE'] && $item['UPDATE_RULE'] !== 'NO ACTION') {
                $extra .= ' ON UPDATE ' . $item['UPDATE_RULE'];
            }
            if ($item['DELETE_RULE'] && $item['DELETE_RULE'] !== 'NO ACTION') {
                $extra .= ' ON DELETE ' . $item['DELETE_RULE'];
            }
            if ($returnSimple) {
                $columns[$item['COLUMN_NAME']] = $txt . $extra;
            } else {
                $columns[$item['COLUMN_NAME']] = PdoOne::newColFK('FOREIGN KEY', $item['REFERENCED_COLUMN_NAME'],
                    $item['REFERENCED_TABLE_NAME'], $extra, $item['CONSTRAINT_NAME']);
                $columns[PdoOne::$prefixBase . $item['COLUMN_NAME']] = PdoOne::newColFK('MANYTOONE', $item['REFERENCED_COLUMN_NAME'],
                    $item['REFERENCED_TABLE_NAME'], $extra, $item['CONSTRAINT_NAME']);
            }
        }


        if ($assocArray) {
            return $columns;
        }
        return $this->parent->filterKey($filter, $columns, $returnSimple);
    }

    public function typeDict($row, $default = true)
    {
        $type = @$row['native_type'];
        switch ($type) {
            case 'VAR_STRING':
            case 'BLOB':
            case 'STRING':
            case 'GEOMETRY':
            case 'TIMESTAMP':
            case 'TIME':
            case 'DATE':
            case 'DATETIME':
            case 'NULL':
                return ($default) ? "''" : 'string';
            case 'LONG':
            case 'LONGLONG':
            case 'SHORT':
            case 'TINY':
            case 'YEAR':
                return ($default) ? '0' : 'int';
            case 'DECIMAL':
            case 'DOUBLE':
            case 'FLOAT':
            case 'NEWDECIMAL':
                return ($default) ? '0.0' : 'float';
            default:
                return '???' . $type;
        }
    }

    public function objectExist($type = 'table')
    {
        switch ($type) {
            case 'table':
                $query
                    = "SELECT * FROM information_schema.tables where table_schema='{$this->parent->db}' and table_name=?";
                break;
            case 'function':
                $query
                    = "SELECT * FROM INFORMATION_SCHEMA.ROUTINES where ROUTINE_SCHEMA='{$this->parent->db}' and ROUTINE_NAME=?";
                break;
            default:
                $this->parent->throwError("objectExist: type [$type] not defined for {$this->parent->databaseType}",
                    '');
                return null;
        }

        return $query;
    }

    public function objectList($type = 'table', $onlyName = false)
    {
        switch ($type) {
            case 'table':
                $query
                    = "SELECT * FROM information_schema.tables where table_schema='{$this->parent->db}' and table_type='BASE TABLE'";
                if ($onlyName) {
                    $query = str_replace('*', 'table_name as name', $query);
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
                return null;
        }

        return $query;
    }

    public function columnTable($tableName)
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

    public function foreignKeyTable($tableName)
    {
        return "SELECT 
							column_name collocal,
						    REFERENCED_TABLE_NAME tablerem,
						    REFERENCED_COLUMN_NAME colrem
						 FROM information_schema.KEY_COLUMN_USAGE
						where table_name='$tableName' and constraint_schema='{$this->parent->db}'
						and referenced_table_name is not null;";
    }

    public function createSequence($tableSequence = null, $method = 'snowflake')
    {
        $ok = $this->parent->createTable($tableSequence, [
            'id'   => 'bigint(20) unsigned NOT NULL AUTO_INCREMENT',
            'stub' => "char(1) NOT NULL DEFAULT ''",
        ], [
                'id'   => 'PRIMARY KEY',
                'stub' => 'UNIQUE KEY'
            ], '', 'ENGINE=MyISAM AUTO_INCREMENT=1 DEFAULT CHARSET=utf8');
        if (!$ok) {
            $this->parent->throwError("Unable to create table $tableSequence", '');
            return '';
        }
        $ok = $this->parent->insert($tableSequence, ['stub' => 'a']);
        if (!$ok) {
            $this->parent->throwError("Unable to insert in table $tableSequence", '');

            return '';
        }
        $sql = 'SET GLOBAL log_bin_trust_function_creators = 1';
        $this->parent->runRawQuery($sql);
        if ($method === 'snowflake') {
            $sql = "CREATE FUNCTION `next_{$tableSequence}`(node integer) RETURNS BIGINT(20)
                    MODIFIES SQL DATA
                    NOT DETERMINISTIC
					BEGIN
					    DECLARE epoch BIGINT(20);
					    DECLARE current_ms BIGINT(20);
					    DECLARE incr BIGINT(20);
					    SET current_ms = round(UNIX_TIMESTAMP(CURTIME(4)) * 1000);
					    SET epoch = 1459440000000; 
					    REPLACE INTO {$tableSequence} (stub) VALUES ('a');
					    SELECT LAST_INSERT_ID() INTO incr;    
					RETURN (current_ms - epoch) << 22 | (node << 12) | (incr % 4096);
					END;";
        } else {
            $sql = "CREATE FUNCTION `next_{$tableSequence}`(node integer) RETURNS BIGINT(20)
                    MODIFIES SQL DATA
                    NOT DETERMINISTIC
					BEGIN
					    DECLARE incr BIGINT(20);
					    REPLACE INTO {$tableSequence} (stub) VALUES ('a');
					    SELECT LAST_INSERT_ID() INTO incr;    
					RETURN incr;
					END;";
        }

        return $sql;
    }

    public function getSequence($sequenceName)
    {
        $sequenceName = ($sequenceName == '') ? $this->parent->tableSequence : $sequenceName;
        return "select next_{$sequenceName}({$this->parent->nodeId}) id";
    }

    public function createTable(
        $tableName,
        $definition,
        $primaryKey = null,
        $extra = '',
        $extraOutside = ''
    ) {
        $extraOutside = ($extraOutside === '') ? "ENGINE=InnoDB DEFAULT CHARSET={$this->parent->charset};"
            : $extraOutside;
        $sql = "CREATE TABLE `{$tableName}` (";
        foreach ($definition as $key => $type) {
            $sql .= "`$key` $type,";
        }
        if ($primaryKey) {
            if (is_array($primaryKey)) {
                $hasPK = false;
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
                                $sql .= "PRIMARY KEY (`$key`*pk*) $value,";
                                $hasPK = true;
                            } else {
                                $sql = str_replace('*pk*', ",`$key`", $sql);
                            }
                            break;
                        case '':
                            $sql .= "KEY `{$tableName}_{$key}_idx` (`$key`) $value,";
                            break;
                        case 'UNIQUE':
                            $sql .= "UNIQUE KEY `{$tableName}_{$key}_idx` (`$key`) $value,";
                            break;
                        default:
                            trigger_error("createTable: [$type KEY] not defined");
                            break;
                    }
                }
                $sql = str_replace('*pk*', '', $sql);
                $sql = rtrim($sql, ',');
            } else {
                $sql .= " PRIMARY KEY(`$primaryKey`) ";
            }
        } else {
            $sql = substr($sql, 0, -1);
        }
        $sql .= "$extra ) " . $extraOutside;
        return $sql;
    }

    public function createFK($tableName, $foreignKey)
    {
        $sql = '';
        foreach ($foreignKey as $key => $value) {
            $p0 = stripos($value . ' ', 'KEY ');
            if ($p0 === false) {
                trigger_error('createTable: Key with a wrong syntax: "FOREIGN KEY.." ');
                break;
            }
            $type = strtoupper(trim(substr($value, 0, $p0)));
            $value = substr($value, $p0 + 4);
            if ($type === 'FOREIGN') {
                $sql .= "ALTER TABLE `{$tableName}` ADD CONSTRAINT `fk_{$tableName}_{$key}` FOREIGN KEY(`$key`) $value;";
            }
        }
        return $sql;
    }

    public function limit($sql)
    {
        $this->parent->limit = ($sql) ? ' limit ' . $sql : '';
    }


    public function getPK($query, $pk=null)
    {
        try {
            $pkResult = [];
            if ($this->parent->isQuery($query)) {
                $q = $this->parent->toMeta($query);
                foreach ($q as $item) {
                    if (in_array('primary_key', $item['flags'])) {
                        $pkResult[] = $item['name'];
                        //break;
                    }
                }
            } else {
                // get the pk by table name
                $r = $this->getDefTableKeys($query, true, 'PRIMARY KEY');
                if (count($r) >= 1) {
                    foreach ($r as $key => $item) {
                        $pkResult[] = $key;
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

    public function getDefTableKeys($table, $returnSimple, $filter = null)
    {
        $columns = [];
        /** @var array $indexArr =array(["Table"=>'',"Non_unique"=>0,"Key_name"=>'',"Seq_in_index"=>0
         * ,"Column_name"=>'',"Collation"=>'',"Cardinality"=>0,"Sub_part"=>0,"Packed"=>'',"Null"=>''
         * ,"Index_type"=>'',"Comment"=>'',"Index_comment"=>'',"Visible"=>'',"Expression"=>''])
         */
        $indexArr = $this->parent->runRawQuery('show index from ' . $table);
        foreach ($indexArr as $item) {
            if (strtoupper($item['Key_name']) === 'PRIMARY') {
                $type = 'PRIMARY KEY';
            } elseif ($item['Non_unique'] != 0) {
                $type = 'KEY';
            } else {
                $type = 'UNIQUE KEY';
            }
            if ($filter === null || $filter === $type) {
                if (!isset($columns[$item['Column_name']])) {
                    if ($returnSimple) {
                        $columns[$item['Column_name']] = $type;
                    } else {
                        $columns[$item['Column_name']] = PdoOne::newColFK($type, '', '');
                    }
                }
            }
        }
        return $columns; //$this->parent->filterKey($filter,$columns,$returnSimple);
    }

}