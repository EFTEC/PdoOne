<?php /** @noinspection UnknownInspectionInspection */
/** @noinspection SqlWithoutWhere */
/** @noinspection SqlResolve */
/** @noinspection AccessModifierPresentedInspection */
/** @noinspection TypeUnsafeComparisonInspection */

/** @noinspection DuplicatedCode */

namespace eftec\ext;


use eftec\PdoOne;
use Exception;
use PDO;
use PDOStatement;

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
        $this->parent->database_identityName='IDENTITY';
        PdoOne::$isoDate = 'Y-m-d';
        PdoOne::$isoDateTime = 'Y-m-d H:i:s';
        PdoOne::$isoDateTimeMs = 'Y-m-d H:i:s.u';
        PdoOne::$isoDateInput = 'Ymd';
        PdoOne::$isoDateInputTime = 'Ymd His';
        PdoOne::$isoDateInputTimeMs = 'Ymd His.u';
        $this->parent->isOpen = false;
        return '';
    }

    public function connect($cs, $alterSession=false)
    {
        $this->parent->conn1 = new PDO("{$this->parent->databaseType}:server={$this->parent->server};" .
                                       "database={$this->parent->db}$cs", $this->parent->user, $this->parent->pwd);
        $this->parent->user='';
        $this->parent->pwd='';
        $this->parent->conn1->setAttribute( PDO::SQLSRV_ATTR_FETCHES_NUMERIC_TYPE,true);

    }

    public function truncate($tableName,$extra,$force) {
        if(!$force) {
            $sql = 'truncate table ' . $this->parent->addDelimiter($tableName) . " $extra";
            return $this->parent->runRawQuery($sql, null, true);
        }
        $sql="DELETE FROM ".$this->parent->addDelimiter($tableName)." $extra";
        return $this->parent->runRawQuery($sql,null, true);
    }
    
    public function resetIdentity($tableName,$newValue=0,$column='') {
        $sql="DBCC CHECKIDENT ('$tableName',RESEED, $newValue)";
        return $this->parent->runRawQuery($sql, null, true);
    }

    /**
     * @param string $table
     * @param false $onlyDescription
     *
     * @return array|bool|mixed|PDOStatement|null
     * @throws Exception
     */
    public function getDefTableExtended($table,$onlyDescription=false) {
        $query="SELECT objects.name as [table],'' as [engine],schemas.name as [schema]
                ,'' as [collation],value description
                FROM sys.objects
                inner join sys.schemas on objects.schema_id=schemas.schema_id
                CROSS APPLY fn_listextendedproperty(default,
                                    'SCHEMA', schema_name(objects.schema_id),
                                    'TABLE', objects.name, null, null) ep
                WHERE sys.objects.name=?";
        $result=$this->parent->runRawQuery($query,[$table],true);
        if($onlyDescription) {
            return $result['description'];
        }
        return $result;
    }

    public function getDefTable($table)
    {
        /** @var array $result =array(["name"=>'',"is_identity"=>0,"increment_value"=>0,"seed_value"=>0]) */
        $findIdentity =
            $this->parent->select('name,is_identity,increment_value,seed_value')->from('sys.identity_columns')
                         ->where('OBJECT_NAME(object_id)=?', $table)->toList();
        $findIdentity = (!is_array($findIdentity)) ? [] : $findIdentity; // it's always an arry

        $defArray = $this->parent->select('COLUMN_NAME,IS_NULLABLE,DATA_TYPE,CHARACTER_MAXIMUM_LENGTH
                        ,NUMERIC_PRECISION,NUMERIC_SCALE,COLUMN_DEFAULT,IDENT_SEED(\'".$table."\') HASIDENTITY')
                                 ->from('INFORMATION_SCHEMA.COLUMNS')->where('TABLE_NAME = ?', $table)
                                 ->order('ORDINAL_POSITION')->toList();

        $result = [];
        foreach ($defArray as $col) {
            $value = self::sqlsrv_getType($col);
            $value .= ($col['IS_NULLABLE'] === 'NO') ? ' NOT NULL' : '';
            $value .= ($col['COLUMN_DEFAULT']) ? ' DEFAULT ' . $col['COLUMN_DEFAULT'] : '';
            $colName = $col['COLUMN_NAME'];
            foreach ($findIdentity as $fi) {
                if ($colName === $fi['name']) {
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
        } elseif ($col['NUMERIC_PRECISION'] || $col['CHARACTER_MAXIMUM_LENGTH']) {
            $result = "{$col['DATA_TYPE']}(" . ($col['CHARACTER_MAXIMUM_LENGTH'] + $col['NUMERIC_PRECISION']) . ')';
        } else {
            $result = $col['DATA_TYPE'];
        }

        return $result;
    }

    /**
     * @param string $table
     * @param bool $returnSimple
     * @param null $filter
     * @return array
     * @throws Exception
     */
    public function getDefTableKeys($table, $returnSimple, $filter = null)
    {
        $columns = [];
        /** @var array $result =array(["IndexName"=>'',"ColumnName"=>'',"is_unique"=>0,"is_primary_key"=>0,"TYPE"=>0]) */

        $result =
            $this->parent->select('IndexName = ind.name,ColumnName = col.name,ind.is_unique,IND.is_primary_key,IND.TYPE')
                         ->from('sys.indexes ind')
                         ->innerjoin('sys.index_columns ic ON ind.object_id = ic.object_id and ind.index_id = ic.index_id')
                         ->innerjoin('sys.columns col ON ic.object_id = col.object_id and ic.column_id = col.column_id')
                         ->where("OBJECT_NAME( ind.object_id)='$table'")
                         ->order('ind.name, ind.index_id, ic.index_column_id')->toList();
        foreach ($result as $item) {
            if ($item['is_primary_key']) {
                $type = 'PRIMARY KEY';
            } elseif ($item['is_unique']) {
                $type = 'UNIQUE KEY';
            } else {
                $type = 'KEY';
            }
            if($filter===null || $filter===$type) {
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
     * @param string $table
     * @param bool $returnSimple
     * @param null $filter
     * @param bool $assocArray
     * @return array
     * @throws Exception
     */
    public function getDefTableFK($table, $returnSimple, $filter = null, $assocArray =false)
    {
        $columns = [];
        /** @var array $fkArr =array(["foreign_key_name"=>'',"referencing_table_name"=>'',"COLUMN_NAME"=>''
         * ,"referenced_table_name"=>'',"referenced_column_name"=>'',"referenced_schema_name"=>''
         * ,"update_referential_action_desc"=>'',"delete_referential_action_desc"=>''])
         */
        $fkArr = $this->parent->select('OBJECT_NAME(f.constraint_object_id) foreign_key_name 
                    ,OBJECT_NAME(f.parent_object_id) referencing_table_name 
                    ,COL_NAME(f.parent_object_id, f.parent_column_id) COLUMN_NAME 
                    ,OBJECT_NAME (f.referenced_object_id) referenced_table_name 
                    ,COL_NAME(f.referenced_object_id, f.referenced_column_id) referenced_column_name 
                    ,OBJECT_SCHEMA_NAME(f.referenced_object_id) referenced_schema_name
                    , fk.update_referential_action_desc, fk.delete_referential_action_desc
                    ,fk.name fk_name')
                               ->from('sys.foreign_key_columns AS f')
                               ->innerjoin('sys.foreign_keys as fk on fk.OBJECT_ID = f.constraint_object_id')
                               ->where("OBJECT_NAME(f.parent_object_id)='$table'")
                               ->order('COL_NAME(f.parent_object_id, f.parent_column_id)')->toList();
        /*echo "table:";
        var_dump($table);
        echo "<pre>";
        var_dump($fkArr);
        echo "</pre>";*/
        foreach ($fkArr as $item) {
            $extra = ($item['update_referential_action_desc'] !== 'NO_ACTION') ? ' ON UPDATE ' .
                str_replace('_', ' ', $item['update_referential_action_desc']) : '';
            $extra .= ($item['delete_referential_action_desc'] !== 'NO_ACTION') ? ' ON DELETE ' .
                str_replace('_', ' ', $item['delete_referential_action_desc']) : '';
            //FOREIGN KEY REFERENCES TABLEREF(COLREF)
            if ($returnSimple) {
                $columns[$item['COLUMN_NAME']] =
                    'FOREIGN KEY REFERENCES ' .$this->parent->addQuote($item['referenced_table_name']) 
                    . '(' . $this->parent->addQuote($item['referenced_column_name']) . ')' . $extra;
            } else {
                $columns[$item['COLUMN_NAME']]=PdoOne::newColFK('FOREIGN KEY'
                    ,$item['referenced_column_name']
                    ,$item['referenced_table_name']
                    ,$extra
                    ,$item['fk_name']);
                $columns[PdoOne::$prefixBase.$item['COLUMN_NAME']]=PdoOne::newColFK(
                    'MANYTOONE'
                    ,$item['referenced_column_name']
                    ,$item['referenced_table_name']
                    ,$extra
                    ,$item['fk_name']);
            }
        }

        if($assocArray) {
            return $columns;
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
                return ($default) ? '0' : 'int';
            case 'decimal':
            case 'smallmoney':
            case 'money':
            case 'double':
            case 'real':
            case 'float':
                return ($default) ? '0.0' : 'float';
            default:
                return '???sqlsrv:' . $type;
        }
    }

    public function objectExist($type = 'table')
    {
        switch ($type) {
            case 'table':
                $query = "SELECT * FROM sys.objects where name=? and type_desc='USER_TABLE'";
                break;
            case 'function':
                $query = "SELECT * FROM sys.objects where name=? and type_desc='SQL_SCALAR_FUNCTION'";
                break;
            case 'sequence':
                $query = "SELECT * FROM sys.objects where name=? and type_desc='SEQUENCE_OBJECT'";
                break;
            case 'procedure':
                $query = "SELECT * FROM sys.objects where name=? and type_desc='SQL_STORED_PROCEDURE'";
                break;
            default:
                $this->parent->throwError("objectExist: type [$type] not defined for {$this->parent->databaseType}", '');
                return null;
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
                $this->parent->throwError("objectExist: type [$type] not defined for {$this->parent->databaseType}", '');
                return null;
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
					,fks.name fk_name
					FROM sys.foreign_key_columns fk
					inner join sys.objects obj on obj.object_id=fk.parent_object_id
					inner join sys.COLUMNS col on obj.object_id=col.object_id and fk.parent_column_id=col.column_id
					inner join sys.types st on col.system_type_id=st.system_type_id	
					inner join sys.objects objrem on objrem.object_id=fk.referenced_object_id
					inner join sys.COLUMNS colrem on fk.referenced_object_id=colrem.object_id and fk.referenced_column_id=colrem.column_id
					inner join sys.foreign_keys fks on fk.constraint_object_id=fks.object_id
					where obj.name='$tableName' ";
    }

    public function createSequence($tableSequence = null, $method = 'snowflake')
    {
        $sql = "CREATE SEQUENCE [$tableSequence]
				    START WITH 1  
				    INCREMENT BY 1
			    ";
        $sql .= "create PROCEDURE next_$tableSequence
					@node int
				AS
					BEGIN
						-- Copyright Jorge Castro https://github.com/EFTEC/PdoOne
						SET NOCOUNT ON
						declare @return bigint
						declare @current_ms bigint 
						declare @incr bigint
						-- 2018-01-01 is an arbitrary epoch
						set @current_ms=cast(DATEDIFF(s, '2018-01-01 00:00:00', GETDATE()) as bigint) *cast(1000 as bigint)  + DATEPART(MILLISECOND,getutcdate())	
						SELECT @incr= NEXT VALUE FOR $tableSequence
						-- current_ms << 22 | (node << 12) | (incr % 4096)
						set @return=(@current_ms*cast(4194304 as bigint)) + (@node *4096) + (@incr % 4096)
						select @return as id
					END";

        return $sql;
    }
    public function getSequence($sequenceName) {
        $sequenceName = ($sequenceName == '') ? $this->parent->tableSequence : $sequenceName;
        return "exec next_$sequenceName {$this->parent->nodeId}";
    }

    public function createTable($tableName, $definition, $primaryKey = null, $extra = '', $extraOutside = '')
    {
        $extraOutside = ($extraOutside === '') ? 'ON [PRIMARY]' : $extraOutside;
        $sql = "set nocount on;
				CREATE TABLE [$tableName] (";
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
            $hasPK=false;
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
                        if(!$hasPK) {
                            $sql .= "ALTER TABLE [$tableName] ADD CONSTRAINT
							            PK_$tableName PRIMARY KEY CLUSTERED ([$key]*pk*) $extraOutside;";
                            $hasPK=true;
                        } else {
                            $sql=str_replace('*pk*',",[$key]",$sql);
                        }
                        break;
                    case '':
                        $sql .= "CREATE INDEX {$tableName}_{$key}_idx ON $tableName ($key) $value;";
                        break;
                    case 'UNIQUE':
                        $sql .= "CREATE UNIQUE INDEX {$tableName}_{$key}_idx ON $tableName ($key) $value;";
                        break;
                    case 'FOREIGN':
                        $sql .= "ALTER TABLE $tableName ADD FOREIGN KEY ($key) $value;";
                        break;
                    default:
                        trigger_error("createTable: [$type KEY] not defined");
                        break;
                }
            }
            $sql=str_replace('*pk*','',$sql);
        }

        return $sql;
    }

    public function createFK($tableName, $foreignKey)
    {
        $sql = '';
        foreach ($foreignKey as $key => $value) {
            $p0 = stripos($value . ' ', 'KEY ');
            if ($p0 === false) {
                trigger_error('createFK: Key with a wrong syntax. Example: "PRIMARY KEY.." ,
                                 "KEY...", "UNIQUE KEY..." "FOREIGN KEY.." ');
                return null;
            }
            $type = strtoupper(trim(substr($value, 0, $p0)));
            $value = substr($value, $p0 + 4);
            if($type==='FOREIGN') {
                $sql .= "ALTER TABLE $tableName ADD FOREIGN KEY ($key) $value;";
            }
        }
        return $sql;
    }

    public function limit($sql)
    {
        //if (!$this->parent->order) {
        //    $this->parent->throwError('limit without a sort', '');
        //}
        if (strpos($sql, ',')) {
            $arr = explode(',', $sql);
            return " OFFSET $arr[0] ROWS FETCH NEXT $arr[1] ROWS ONLY";
        }

        return " OFFSET 0 ROWS FETCH NEXT $sql ROWS ONLY";
    }

    public function getPK($query, $pk=null)
    {
        try {
            $pkResult = [];
            if ($this->parent->isQuery($query)) {
                if (!$pk) {
                    return 'SQLSRV: unable to fin pk via query. Use the name of the table';
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
        } catch(Exception $ex) {
            return false;
        }
    }

    public function callProcedure($procName, &$arguments = [], $outputColumns = [])
    {
        // TODO: Implement callProcedure() method.
        throw new \RuntimeException('not defined yet');
    }

    public function createProcedure($procedureName, $arguments = [], $body = '', $extra = '')
    {
        // TODO: Implement createProcedure() method.
        throw new \RuntimeException('not defined yet');
    }
}