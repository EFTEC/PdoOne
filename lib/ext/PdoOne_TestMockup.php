<?php /** @noinspection DuplicatedCode */

namespace eftec\ext;

use eftec\PdoOne;
use stdClass;

/**
 * Class PdoOne_Test
 *
 * @see           https://github.com/EFTEC/PdoOne
 * @author        Jorge Castro Castillo
 * @copyright (c) Jorge Castro C. MIT License  https://github.com/EFTEC/PdoOne
 * @package       eftec
 */
class PdoOne_TestMockup implements PdoOne_IExt
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
        $this->parent->database_delimiter0 = '';
        $this->parent->database_delimiter1 = '';
        PdoOne::$isoDate                   = 'Ymd';
        PdoOne::$isoDateTime               = 'Ymd H:i:s';
        PdoOne::$isoDateTimeMs             = 'Ymd H:i:s.u';

        return "";
    }

    public function connect($cs)
    {
        $this->parent->conn1 = new stdClass();
    }

    public function getDefTable($table)
    {
        $defArray = [
            [
                'Field' >= 'id',
                'Key'     => 'PRI',
                'Type'    => 'int',
                'Null'    => 'NO',
                'Default' => '',
                'Extra'   => '',
            ],
        ];
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
        if ($returnSimple) {
            return ['col1' => 'FOREIGN KEY'];
        } else {
            return ['col1' => ['key' => 'FOREIGN KEY', 'refcol' => 'col', 'reftable' => 'table2', 'extra' => '']];
        }
    }

    function typeDict($row, $default = true)
    {
        return '';
    }

    public function objectExist($type = 'table')
    {
        switch ($type) {
            case 'function':
                $query
                    = "SELECT * FROM INFORMATION_SCHEMA.ROUTINES where ROUTINE_SCHEMA='{$this->parent->db}' and ROUTINE_NAME=?";
                break;
            case 'table':
                $query
                    = "SELECT * FROM information_schema.tables where table_schema='{$this->parent->db}' and table_name=?";
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
        return "SELECT col.name collocal
					,objrem.name tablerem
					,colrem.name colrem
					FROM columns fk
					where obj.name='$tableName' ";
    }

    public function createSequence($tableSequence = null, $method = 'snowflake')
    {
        return "CREATE TABLE";
    }

    public function createTable($tableName, $definition, $primaryKey = null, $extra = '', $extraOutside = '')
    {
        $sql = "CREATE TABLE {$tableName} (";
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

    public function limit($sql)
    {
        if ( ! $this->parent->order) {
            $this->parent->throwError("limit without a sort", "");
        }
        if (strpos($sql, ',')) {
            $arr                 = explode(',', $sql);
            $this->parent->limit = " OFFSET {$arr[0]} ROWS FETCH NEXT {$arr[1]} ROWS ONLY";
        } else {
            $this->parent->limit = " OFFSET 0 ROWS FETCH NEXT $sql ROWS ONLY";
        }
    }

    public function getPK($query, $pk)
    {
        return 'primary_key';
    }

}