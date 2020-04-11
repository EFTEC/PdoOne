<?php /** @noinspection DuplicatedCode */

namespace eftec\ext;

use eftec\PdoOne;
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
     * @param  PdoOne  $parent
     */
    public function __construct(PdoOne $parent)
    {
        $this->parent = $parent;
    }

    public function construct($charset)
    {
        $this->parent->database_delimiter0 = '`';
        $this->parent->database_delimiter1 = '`';
        $charset                           = ($charset == null) ? 'utf8' : $charset;
        PdoOne::$isoDate                   = 'Y-m-d';
        PdoOne::$isoDateTime               = 'Y-m-d H:i:s';
        PdoOne::$isoDateTimeMs             = 'Y-m-d H:i:s.u';

        return $charset;
    }

    public function connect($cs)
    {
        $this->parent->conn1
            = new PDO("{$this->parent->databaseType}:host={$this->parent->server};dbname={$this->parent->db}{$cs}",
            $this->parent->user, $this->parent->pwd);
    }

    public function getDefTable($table)
    {
        $defArray = $this->parent->runRawQuery('show columns from '.$table, [], true);
        $result   = [];
        foreach ($defArray as $col) {
            /*if ($col['Key'] === 'PRI') {
                $pk = $col['Field'];
            }*/
            $value = $col['Type'];
            $value .= ($col['Null'] === 'NO') ? " not null" : '';
            if ($col['Default'] === 'CURRENT_TIMESTAMP') {
                $value .= ' default CURRENT_TIMESTAMP';
            } else {
                $value .= ($col['Default']) ? ' default \''.$col['Default'].'\'' : '';
            }
            $col['Extra'] = str_replace('DEFAULT_GENERATED ', '', $col['Extra']);
            $value        .= ($col['Extra']) ? ' '.$col['Extra'] : '';

            $result[$col['Field']] = $value;
        }

        return $result;
    }

    public function getDefTableKeys($table, $returnSimple)
    {
        $columns = [];
        $struct  = [];
        /** @var array $indexArr =array(["Table"=>'',"Non_unique"=>0,"Key_name"=>'',"Seq_in_index"=>0
         * ,"Column_name"=>'',"Collation"=>'',"Cardinality"=>0,"Sub_part"=>0,"Packed"=>'',"Null"=>''
         * ,"Index_type"=>'',"Comment"=>'',"Index_comment"=>'',"Visible"=>'',"Expression"=>''])
         */
        $indexArr = $this->parent->runRawQuery('show index from '.$table);
        foreach ($indexArr as $item) {
            if ($item['Key_name'] == 'PRIMARY') {
                $tk = "PRIMARY KEY";
            } else {
                if ($item['Non_unique'] != 0) {
                    $tk = "KEY";
                } else {
                    $tk = "UNIQUE KEY";
                }
            }
            if ($returnSimple) {
                $columns[$item['Column_name']] = $tk;
            } else {
                $columns[$item['Column_name']]['key']      = $tk;
                $columns[$item['Column_name']]['refcol']   = '';
                $columns[$item['Column_name']]['reftable'] = '';
                $columns[$item['Column_name']]['extra']    = '';
            }
        }
        /** @var array $result =array(["CONSTRAINT_NAME"=>'',"COLUMN_NAME"=>'',"REFERENCED_TABLE_NAME"=>''
         * ,"REFERENCED_COLUMN_NAME"=>'',"UPDATE_RULE"=>'',"DELETE_RULE"=>''])
         */
        $fkArr = $this->parent
            ->select("k.CONSTRAINT_NAME,k.COLUMN_NAME,k.REFERENCED_TABLE_NAME,k.REFERENCED_COLUMN_NAME
                    ,c.UPDATE_RULE,c.DELETE_RULE")
            ->from("INFORMATION_SCHEMA.KEY_COLUMN_USAGE k")
            ->innerjoin("information_schema.REFERENTIAL_CONSTRAINTS c 
                        ON k.referenced_table_schema=c.CONSTRAINT_SCHEMA AND k.CONSTRAINT_NAME=c.CONSTRAINT_NAME")
            ->where("k.TABLE_SCHEMA=? AND k.TABLE_NAME = ?", ['s', $this->parent->db, 's', $table])
            ->toList();
        foreach ($fkArr as $item) {
            $txt = "FOREIGN KEY REFERENCES`{$item['REFERENCED_TABLE_NAME']}`(`{$item['REFERENCED_COLUMN_NAME']}`)";
            if ($item['UPDATE_RULE'] && $item['UPDATE_RULE'] != 'NO ACTION') {
                $txt .= ' ON UPDATE '.$item['UPDATE_RULE'];
            }
            if ($item['DELETE_RULE'] && $item['DELETE_RULE'] != 'NO ACTION') {
                $txt .= ' ON DELETE '.$item['DELETE_RULE'];
            }
            if ($returnSimple) {
                $columns[$item['COLUMN_NAME']] = $txt;
            } else {
                $columns[$item['COLUMN_NAME']]['key']      = 'FOREIGN KEY';
                $columns[$item['COLUMN_NAME']]['refcol']   = $item['REFERENCED_COLUMN_NAME'];
                $columns[$item['COLUMN_NAME']]['reftable'] = $item['REFERENCED_TABLE_NAME'];
                $columns[$item['COLUMN_NAME']]['extra']    = $txt;
            }
        }

        return $columns;
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
                return ($default) ? "0" : 'int';
            case 'DECIMAL':
            case 'DOUBLE':
            case 'FLOAT':
            case 'NEWDECIMAL':
                return ($default) ? "0.0" : 'float';
            default:
                return "???".$type;
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
                    "");
                die(1);
                break;
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
                    "");
                die(1);
                break;
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
        $ok = $this->parent->createTable($tableSequence
            , [
                'id'   => 'bigint(20) unsigned NOT NULL AUTO_INCREMENT',
                'stub' => "char(1) NOT NULL DEFAULT ''",
            ], 'id'
            , ',UNIQUE KEY `stub` (`stub`)', 'ENGINE=MyISAM AUTO_INCREMENT=1 DEFAULT CHARSET=utf8');
        if ( ! $ok) {
            $this->parent->throwError("Unable to create table $tableSequence", "");

            return "";
        }
        $ok = $this->parent->insert($tableSequence, ['stub' => 'a']);
        if ( ! $ok) {
            $this->parent->throwError("Unable to insert in table $tableSequence", "");

            return "";
        }
        $sql = "SET GLOBAL log_bin_trust_function_creators = 1";
        $this->parent->runRawQuery($sql);
        if ($method == 'snowflake') {
            $sql = "CREATE FUNCTION `next_{$tableSequence}`(node integer) RETURNS BIGINT(20)
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
					BEGIN
					    DECLARE incr BIGINT(20);
					    REPLACE INTO {$tableSequence} (stub) VALUES ('a');
					    SELECT LAST_INSERT_ID() INTO incr;    
					RETURN incr;
					END;";
        }

        return $sql;
    }

    public function createTable($tableName, $definition, $primaryKey = null, $extra = '', $extraOutside = '')
    {
        $extraOutside = ($extraOutside === '') ? "ENGINE=InnoDB DEFAULT CHARSET={$this->parent->charset};"
            : $extraOutside;
        $sql          = "CREATE TABLE `{$tableName}` (";
        foreach ($definition as $key => $type) {
            $sql .= "`$key` $type,";
        }
        if ($primaryKey) {
            if (is_array($primaryKey)) {
                foreach ($primaryKey as $key => $value) {
                    $p0 = stripos($value.' ', "KEY ");
                    if ($p0 === false) {
                        trigger_error('createTable: Key with a wrong syntax. Example: "PRIMARY KEY.." ,
                                 "KEY...", "UNIQUE KEY..." "FOREIGN KEY.." ');
                        break;
                    }
                    $type  = trim(strtoupper(substr($value, 0, $p0)));
                    $value = substr($value, $p0 + 4);
                    switch ($type) {
                        case 'PRIMARY':
                            $sql .= "PRIMARY KEY (`$key`) $value,";
                            break;
                        case '':
                            $sql .= "KEY `{$tableName}_{$key}_idx` (`$key`) $value,";
                            break;
                        case 'UNIQUE':
                            $sql .= "UNIQUE KEY `{$tableName}_{$key}_idx` (`$key`) $value,";
                            break;
                        case 'FOREIGN':
                            $sql .= " CONSTRAINT `fk_{$tableName}_{$key}` FOREIGN KEY(`$key`) $value,";
                            break;
                        default:
                            trigger_error("createTable: [$type KEY] not defined");
                            break;
                    }
                }

                $sql = rtrim($sql, ',');
            } else {
                $sql .= " PRIMARY KEY(`$primaryKey`) ";
            }
        } else {
            $sql = substr($sql, 0, -1);
        }
        $sql .= "$extra ) ".$extraOutside;

        return $sql;
    }

    public function limit($sql)
    {
        $this->parent->limit = ($sql) ? ' limit '.$sql : '';
    }

    public function getPK($query, $pk)
    {
        $q = $this->parent->toMeta($query);
        if ( ! $pk) {
            foreach ($q as $item) {
                if (in_array('primary_key', $item['flags'])) {
                    $pk = $item['name'];
                    break;
                }
            }
        }

        return $pk;
    }

}