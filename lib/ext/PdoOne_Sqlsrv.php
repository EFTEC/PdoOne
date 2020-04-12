<?php /** @noinspection DuplicatedCode */

namespace eftec\ext;

use eftec\PdoOne;
use PDO;

/**
 * Class PdoOne_Sqlsrv
 *
 * @see           https://github.com/EFTEC/PdoOne
 * @author        Jorge Castro Castillo
 * @copyright (c) Jorge Castro C. MIT License  https://github.com/EFTEC/PdoOne
 * @package       eftec
 */
class PdoOne_Sqlsrv implements PdoOne_IExt
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
        $this->parent->database_delimiter0 = '[';
        $this->parent->database_delimiter1 = ']';
        PdoOne::$isoDate = 'Ymd';
        PdoOne::$isoDateTime = 'Ymd H:i:s';
        PdoOne::$isoDateTimeMs = 'Ymd H:i:s.u';
        $this->parent->isOpen = false;
        return '';
    }

    public function connect($cs)
    {
        $this->parent->conn1 = new PDO("{$this->parent->databaseType}:server={$this->parent->server};" .
                                       "database={$this->parent->db}{$cs}", $this->parent->user, $this->parent->pwd);
    }

    public function getDefTable($table)
    {
        /** @var array $result =array(["name"=>'',"is_identity"=>0,"increment_value"=>0,"seed_value"=>0]) */
        $findIdentity =
            $this->parent->select("name,is_identity,increment_value,seed_value")->from("sys.identity_columns")
                         ->where("OBJECT_NAME(object_id)=?", $table)->toList();
        $findIdentity = (!is_array($findIdentity)) ? [] : $findIdentity; // it's always an arry

        $defArray = $this->parent->select('COLUMN_NAME,IS_NULLABLE,DATA_TYPE,CHARACTER_MAXIMUM_LENGTH
                        ,NUMERIC_PRECISION,NUMERIC_SCALE,COLUMN_DEFAULT,IDENT_SEED(\'".$table."\') HASIDENTITY')
                                 ->from('INFORMATION_SCHEMA.COLUMNS')->where('TABLE_NAME = ?', $table)
                                 ->order('ORDINAL_POSITION')->toList();

        $result = [];
        foreach ($defArray as $col) {
            $value = self::sqlsrv_getType($col);
            $value .= ($col['IS_NULLABLE'] == 'NO') ? ' NOT NULL' : '';
            $value .= ($col['COLUMN_DEFAULT']) ? ' DEFAULT ' . $col['COLUMN_DEFAULT'] : '';
            $colName = $col['COLUMN_NAME'];
            foreach ($findIdentity as $fi) {
                if ($colName == $fi['name']) {
                    $value .= " IDENTITY({$fi['seed_value']},{$fi['increment_value']})";
                    break;
                }
            }
            $result[$colName] = $value;
        }

        return $result;
    }

    /**
     * It gets a column from INFORMATION_SCHEMA.COLUMNS and returns a type of the form type,type(size)
     * or type(size,size)
     *
     * @param array $col
     *
     * @return string
     */
    protected static function sqlsrv_getType($col)
    {
        /** @var array $exclusion type of columns that don't use size */
        $exclusion = ['int', 'long', 'tinyint', 'year', 'bigint', 'bit', 'smallint', 'float', 'money'];
        if (in_array($col['DATA_TYPE'], $exclusion) !== false) {
            return $col['DATA_TYPE'];
        }
        if ($col['NUMERIC_SCALE']) {
            $result = "{$col['DATA_TYPE']}({$col['NUMERIC_PRECISION']},{$col['NUMERIC_SCALE']})";
        } else {
            if ($col['NUMERIC_PRECISION'] || $col['CHARACTER_MAXIMUM_LENGTH']) {
                $result = "{$col['DATA_TYPE']}(" . ($col['CHARACTER_MAXIMUM_LENGTH'] + $col['NUMERIC_PRECISION']) . ")";
            } else {
                $result = $col['DATA_TYPE'];
            }
        }

        return $result;
    }

    public function getDefTableKeys($table, $returnSimple, $filter = null)
    {
        $columns = [];
        /** @var array $result =array(["IndexName"=>'',"ColumnName"=>'',"is_unique"=>0,"is_primary_key"=>0,"TYPE"=>0]) */

        $result =
            $this->parent->select("IndexName = ind.name,ColumnName = col.name,ind.is_unique,IND.is_primary_key,IND.TYPE")
                         ->from("sys.indexes ind")
                         ->innerjoin("sys.index_columns ic ON ind.object_id = ic.object_id and ind.index_id = ic.index_id")
                         ->innerjoin("sys.columns col ON ic.object_id = col.object_id and ic.column_id = col.column_id")
                         ->where("OBJECT_NAME( ind.object_id)='{$table}'")
                         ->order("ind.name, ind.index_id, ic.index_column_id")->toList();

        foreach ($result as $item) {
            if ($item['is_primary_key']) {
                $type = 'PRIMARY KEY';
            } else {
                if ($item['is_unique']) {
                    $type = 'UNIQUE KEY';
                } else {
                    $type = 'KEY';
                }
            }
            if ($returnSimple) {
                $columns[$item['ColumnName']] = $type;
            } else {
                $columns[$item['ColumnName']]['key'] = $type;
                $columns[$item['ColumnName']]['refcol'] = '';
                $columns[$item['ColumnName']]['reftable'] = '';
                $columns[$item['ColumnName']]['extra'] = '';
            }
        }
        return $this->parent->filterKey($filter, $columns, $returnSimple);
    }

    public function getDefTableFK($table, $returnSimple, $filter = null)
    {
        $columns = [];
        /** @var array $result =array(["foreign_key_name"=>'',"referencing_table_name"=>'',"ColumnName"=>''
         * ,"referenced_table_name"=>'',"referenced_column_name"=>'',"referenced_schema_name"=>''
         * ,"update_referential_action_desc"=>'',"delete_referential_action_desc"=>''])
         */
        $result = $this->parent->select("OBJECT_NAME(f.constraint_object_id) foreign_key_name 
                    ,OBJECT_NAME(f.parent_object_id) referencing_table_name 
                    ,COL_NAME(f.parent_object_id, f.parent_column_id) ColumnName 
                    ,OBJECT_NAME (f.referenced_object_id) referenced_table_name 
                    ,COL_NAME(f.referenced_object_id, f.referenced_column_id) referenced_column_name 
                    ,OBJECT_SCHEMA_NAME(f.referenced_object_id) referenced_schema_name
                    , fk.update_referential_action_desc, fk.delete_referential_action_desc")
                               ->from("sys.foreign_key_columns AS f")
                               ->innerjoin("sys.foreign_keys as fk on fk.OBJECT_ID = f.constraint_object_id")
                               ->where("OBJECT_NAME(f.parent_object_id)='{$table}'")->toList();

        foreach ($result as $item) {
            $extra = ($item['update_referential_action_desc'] != 'NO_ACTION') ? ' ON UPDATE ' .
                str_replace('_', ' ', $item['update_referential_action_desc']) : '';
            $extra .= ($item['delete_referential_action_desc'] != 'NO_ACTION') ? ' ON DELETE ' .
                str_replace('_', ' ', $item['delete_referential_action_desc']) : '';
            //FOREIGN KEY REFERENCES TABLEREF(COLREF)
            if ($returnSimple) {
                $columns[$item['ColumnName']] =
                    'FOREIGN KEY REFERENCES ' . $item['referenced_table_name'] . '(' . $item['referenced_column_name'] .
                    ')' . $extra;
            } else {
                $columns[$item['ColumnName']]['key'] = 'FOREIGN KEY';
                $columns[$item['ColumnName']]['refcol'] = $item['referenced_column_name'];
                $columns[$item['ColumnName']]['reftable'] = $item['referenced_table_name'];
                $columns[$item['ColumnName']]['extra'] = $extra;
            }
        }
        return $this->parent->filterKey($filter, $columns, $returnSimple);
    }

    function typeDict($row, $default = true)
    {
        $type = @$row['sqlsrv:decl_type'];
        switch ($type) {
            case 'varchar':
            case 'nvarchar':
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
            case 'int':
            case 'sql_variant':
            case 'int identity':
            case 'year':
            case 'bigint':
            case 'numeric':
            case 'bit':
            case 'smallint':
                return ($default) ? "0" : 'int';
            case 'decimal':
            case 'smallmoney':
            case 'money':
            case 'double':
            case 'real':
            case 'float':
                return ($default) ? "0.0" : 'float';
            default:
                return "???sqlsrv:" . $type;
        }
    }

    public function objectExist($type = 'table')
    {
        switch ($type) {
            case 'table':
                $query = "SELECT * FROM sys.objects where name=? and type_desc='USER_TABLE'";
                break;
            case 'function':
                $query = "SELECT * FROM sys.objects where name=? and type_desc='CLR_SCALAR_FUNCTION'";
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
                $query = "SELECT * FROM sys.objects where type_desc='USER_TABLE'";
                if ($onlyName) {
                    $query = str_replace('*', 'name', $query);
                }
                break;
            case 'function':
                $query = "SELECT * FROM sys.objects where type_desc='CLR_SCALAR_FUNCTION'";
                if ($onlyName) {
                    $query = str_replace('*', 'name', $query);
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
        return "SELECT col.name colname
							,st.name coltype
							,col.max_length colsize
							,col.precision colpres
							,col.scale colscale
							,pk.is_primary_key iskey
							,col.is_identity isidentity
							,col.is_nullable isnullable
						FROM sys.COLUMNS col
						inner join sys.objects obj on obj.object_id=col.object_id
						inner join sys.types st on col.system_type_id=st.system_type_id		
						left join sys.index_columns idx on obj.object_id=idx.object_id and col.column_id=idx.column_id
						left join sys.indexes pk on obj.object_id = pk.object_id and pk.index_id=idx.index_id and pk.is_primary_key=1
						where  obj.name='$tableName'";
    }

    public function foreignKeyTable($tableName)
    {
        return "SELECT col.name collocal
					,objrem.name tablerem
					,colrem.name colrem
					FROM sys.foreign_key_columns fk
					inner join sys.objects obj on obj.object_id=fk.parent_object_id
					inner join sys.COLUMNS col on obj.object_id=col.object_id and fk.parent_column_id=col.column_id
					inner join sys.types st on col.system_type_id=st.system_type_id	
					inner join sys.objects objrem on objrem.object_id=fk.referenced_object_id
					inner join sys.COLUMNS colrem on fk.referenced_object_id=colrem.object_id and fk.referenced_column_id=colrem.column_id
					where obj.name='$tableName' ";
    }

    public function createSequence($tableSequence = null, $method = 'snowflake')
    {
        $sql = "CREATE SEQUENCE [{$tableSequence}]
				    START WITH 1  
				    INCREMENT BY 1
			    ;";
        $sql .= "create PROCEDURE next_{$tableSequence}
					@node int
				AS
					BEGIN
						-- Copyright Jorge Castro https://github.com/EFTEC/PdoOne
						SET NOCOUNT ON;
						declare @return bigint
						declare @current_ms bigint; 
						declare @incr bigint;
						-- 2018-01-01 is an arbitrary epoch
						set @current_ms=cast(DATEDIFF(s, '2018-01-01 00:00:00', GETDATE()) as bigint) *cast(1000 as bigint)  + DATEPART(MILLISECOND,getutcdate());	
						SELECT @incr= NEXT VALUE FOR {$tableSequence};  
						-- current_ms << 22 | (node << 12) | (incr % 4096);
						set @return=(@current_ms*cast(4194304 as bigint)) + (@node *4096) + (@incr % 4096);
						select @return
					END";

        return $sql;
    }

    public function createTable($tableName, $definition, $primaryKey = null, $extra = '', $extraOutside = '')
    {
        $extraOutside = ($extraOutside === '') ? "ON [PRIMARY]" : $extraOutside;
        $sql = "set nocount on;
				CREATE TABLE [{$tableName}] (";
        foreach ($definition as $key => $type) {
            $sql .= "[$key] $type,";
        }
        $sql = rtrim($sql, ',');

        $sql .= "$extra ) ON [PRIMARY]; ";

        if (!is_array($primaryKey)) {
            $sql .= "
						ALTER TABLE [$tableName] ADD CONSTRAINT
							PK_$tableName PRIMARY KEY CLUSTERED ([$primaryKey]) $extraOutside;";
        } else {
            foreach ($primaryKey as $key => $value) {
                $p0 = stripos($value . ' ', "KEY ");
                if ($p0 === false) {
                    trigger_error('createTable: Key with a wrong syntax. Example: "PRIMARY KEY.." ,
                                 "KEY...", "UNIQUE KEY..." "FOREIGN KEY.." ');
                    break;
                }
                $type = trim(strtoupper(substr($value, 0, $p0)));
                $value = substr($value, $p0 + 4);
                switch ($type) {
                    case 'PRIMARY':
                        $sql .= "ALTER TABLE [$tableName] ADD CONSTRAINT
							            PK_$tableName PRIMARY KEY CLUSTERED ([$key]) $extraOutside;";
                        break;
                    case '':
                        $sql .= "CREATE INDEX {$tableName}_{$key}_idx ON {$tableName} ({$key}) $value;";
                        break;
                    case 'UNIQUE':
                        $sql .= "CREATE UNIQUE INDEX {$tableName}_{$key}_idx ON {$tableName} ({$key}) $value;";
                        break;
                    case 'FOREIGN':
                        $sql .= "ALTER TABLE {$tableName} ADD FOREIGN KEY ($key) $value;";
                        break;
                    default:
                        trigger_error("createTable: [$type KEY] not defined");
                        break;
                }
            }
        }

        return $sql;
    }

    public function createFK($tableName, $foreignKey)
    {
        $sql = '';
        foreach ($foreignKey as $key => $value) {
            $p0 = stripos($value . ' ', "KEY ");
            if ($p0 === false) {
                trigger_error('createFK: Key with a wrong syntax. Example: "PRIMARY KEY.." ,
                                 "KEY...", "UNIQUE KEY..." "FOREIGN KEY.." ');
                break;
            }
            $type = trim(strtoupper(substr($value, 0, $p0)));
            $value = substr($value, $p0 + 4);
            switch ($type) {
                case 'FOREIGN':
                    $sql .= "ALTER TABLE {$tableName} ADD FOREIGN KEY ($key) $value;";
                    break;
                default:
                    trigger_error("createFK: [$type KEY] not defined");
                    break;
            }
        }
        return $sql;
    }

    public function limit($sql)
    {
        if (!$this->parent->order) {
            $this->parent->throwError("limit without a sort", "");
        }
        if (strpos($sql, ',')) {
            $arr = explode(',', $sql);
            $this->parent->limit = " OFFSET {$arr[0]} ROWS FETCH NEXT {$arr[1]} ROWS ONLY";
        } else {
            $this->parent->limit = " OFFSET 0 ROWS FETCH NEXT $sql ROWS ONLY";
        }
    }

    public function getPK($query, $pk)
    {

        if ($this->parent->isQuery($query)) {
            if (!$pk) {
                return "SQLSRV: unable to fin pk via query. Use the name of the table";
            }
        } else {

            // get the pk by table name
            $r = $this->getDefTableKeys($query, true, 'PRIMARY KEY');

            if (count($r) >= 1) {
                $pk = array_keys($r)[0];
            } else {
                $pk = '??nopk??';
            }
        }

        return $pk;
    }

}