<?php /** @noinspection PhpMissingParamTypeInspection */
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
use PDO;
use PDOStatement;
use RuntimeException;

/**
 * Class PdoOne_Oci
 *
 * @see           https://github.com/EFTEC/PdoOne
 * @author        Jorge Castro Castillo
 * @copyright (c) Jorge Castro C. MIT License  https://github.com/EFTEC/PdoOne
 * @package       eftec
 */
class PdoOne_Oci implements PdoOne_IExt
{

    /** @var PdoOne */
    protected $parent;
    private $config=['noquote'=>true];

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
        $this->config=array_merge($this->config, $config);
        $this->parent->database_delimiter0 = '"';
        $this->parent->database_delimiter1 = '"';
        $this->parent->database_identityName='IDENTITY';
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

    public function connect($cs, $alterSession=true) : void
    {
        // dbname '(DESCRIPTION=(ADDRESS=(PROTOCOL=TCP)(HOST=localhost)(PORT=1521))(CONNECT_DATA=(SERVICE_NAME=XE)))';
        // 'oci:dbname=localhost/XE', $user, $pass
        $cstring="oci:dbname={$this->parent->server}";
        $this->parent->conn1 = new PDO($cstring, $this->parent->user, $this->parent->pwd);
        $this->parent->user='';
        $this->parent->pwd='';
        $this->parent->conn1->setAttribute( PDO::ATTR_AUTOCOMMIT, true);
        $this->parent->conn1->setAttribute(PDO::ATTR_PERSISTENT, true);
        //$this->parent->conn1->setAttribute(PDO::ATTR_STRINGIFY_FETCHES, false); // otherwise return "0.1" as "0,1"
        //$this->parent->conn1->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        if($alterSession) {
            $this->parent->conn1->exec("ALTER SESSION SET NLS_DATE_FORMAT='YYYY-MM-DD HH24:MI:SS' 
              NLS_TIMESTAMP_FORMAT='YYYY-MM-DD HH24:MI:SS' 
              NLS_TIMESTAMP_TZ_FORMAT='YYYY-MM-DD HH24:MI:SS' 
              NLS_NUMERIC_CHARACTERS = '.,'");
        }
    }

    public function truncate($tableName,$extra,$force)   {
        if(!$force) {
            $sql = 'truncate table ' . $this->parent->addDelimiter($tableName) . " $extra";
            return $this->parent->runRawQuery($sql, null, true);
        }
        $sql="DELETE FROM ".$this->parent->addDelimiter($tableName)." $extra";
        return $this->parent->runRawQuery($sql,null, true);
    }

    public function resetIdentity($tableName,$newValue=0,$column='') {
        $sql="ALTER TABLE \"$tableName\" MODIFY($column GENERATED AS IDENTITY (START WITH $newValue))";
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

        $query="SELECT
                  ut.table_name
                    ,'' as engine
                    ,ut.owner schema
                    ,default_collation  collation
                    ,uTC.COMMENTS description
                FROM
                  all_tables ut,all_tab_comments utc
                  WHERE ut.TABLE_NAME=? and ut.owner=?
                  and ut.table_name=utc.table_name and ut.owner=utc.owner ";
        $result=$this->parent->runRawQuery($query,[$table,$this->parent->db],true);
        if($onlyDescription) {
            return $result['description'];
        }
        return $result;
    }

    public function getDefTable($table) : array
    {
        /** @var array $result =array(["name"=>'',"is_identity"=>0,"increment_value"=>0,"seed_value"=>0]) */
        // throw new RuntimeException("no yet implemented");

        $raw=$this->parent->runRawQuery('select TO_CHAR(DBMS_METADATA.GET_DDL(\'TABLE\',?)) COL from dual',[$table]);
        if(!isset($raw[0]['COL'])) {
            return [];
        }
        $r=$raw[0]['COL'];
        $p0=strpos($r,'(')+1;
        $p1a=strpos($r,' TABLESPACE ',$p0);
        $p1b=strpos($r,' CONSTRAINT ',$p0);
        $p1c=strpos($r,' USING ',$p0);
        $p1=min($p1a,$p1b,$p1c);
        $rcut=trim(substr($r,$p0,$p1-$p0)," \t\n\r\0\x0B,");
        $cols=explode(", \n",$rcut);
        $result=[];
        foreach($cols as $v) {
            $key=explode(' ',$v.' ',2); // the last space avoid to return $key as an array with a single value.
            $result[PdoOne::removeDoubleQuotes($key[0])]=trim($key[1]);
        }
        return $result;
    }

    /**
     * It gets a column from INFORMATION_SCHEMA.COLUMNS and returns a type of the form type,type(size)
     * or type(size,size)
     *
     * @param array $col An associative array with the data of the column
     *
     * @return string
     */
    protected static function oci_getType($col): string
    {
        throw new RuntimeException("no yet implemented");
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
    public function getDefTableKeys($table, $returnSimple, $filter = null) : array
    {
        $columns = [];
        /** @var array $result =array(["IndexName"=>'',"ColumnName"=>'',"is_unique"=>0,"is_primary_key"=>0,"TYPE"=>0]) */
        $pks=$this->getPK($table);
        $result =
            $this->parent->select('SELECT ALL_indexes.INDEX_NAME "IndexName",all_ind_columns.COLUMN_NAME "ColumnName",
                        (CASE WHEN UNIQUENESS = \'UNIQUE\' THEN 1 ELSE 0 END) "is_unique",0 "is_primary_key",0 "TYPE"')
                         ->from('ALL_indexes')
                         ->innerjoin('all_ind_columns on ALL_indexes.index_name=all_ind_columns.index_name ')
                         ->where("ALL_indexes.table_name='$table' and ALL_indexes.table_owner='{$this->parent->db}'")
                         ->order('"IndexName"')->toList();
        foreach ($result as $k=>$item) {
            if (in_array($item['ColumnName'],$pks)) {
                $type = 'PRIMARY KEY';
                $result[$k]['is_primary_key']=1;
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
     * todo: missing checking
     */
    public function getDefTableFK($table, $returnSimple, $filter = null, $assocArray =false) : array
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
               ->where("c.constraint_type = 'R' AND a.table_name=?",[$table])
               ->order('a.column_name')->toList();
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
                    ,$item['foreign_key_name']); // fk_name
                $columns[PdoOne::$prefixBase.$item['COLUMN_NAME']]=PdoOne::newColFK(
                    'MANYTOONE'
                    ,$item['referenced_column_name']
                    ,$item['referenced_table_name']
                    ,$extra
                    ,$item['foreign_key_name']); // fk_name
            }
        }

        if($assocArray) {
            return $columns;
        }

        return $this->parent->filterKey($filter, $columns, $returnSimple);
    }

    function typeDict($row, $default = true): string
    {
        $type = strtolower(@$row['oci:decl_type']);
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
                return '???oci:' . $type;
        }
    }

    public function objectExist($type = 'table') :  null|string
    {

        switch ($type) {
            case 'table':
                $query = 'select * from all_tables where table_name=? and owner=?';
                break;
            case 'procedure':
                $query="SELECT * FROM ALL_OBJECTS WHERE OBJECT_TYPE='PROCEDURE' and object_name=? and owner=?";
                break;
            case 'function':
                $query="SELECT * FROM ALL_OBJECTS WHERE OBJECT_TYPE='FUNCTION' and object_name=? and owner=?";
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
                $query = "select * from user_tables";
                if ($onlyName) {
                    $query = str_replace('*', 'table_name name', $query);
                }
                break;
            case 'function':
                $query = "select object_name from all_objects where owner = ? and object_type = 'FUNCTION';";
                if ($onlyName) {
                    $query = str_replace('*', 'object_name name', $query);
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
        throw new RuntimeException("no yet implemented");
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

    public function foreignKeyTable($tableName): string
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

    public function createSequence($tableSequence = null, $method = 'snowflake') : array
    {
        return ["CREATE SEQUENCE \"$tableSequence\"
				    START WITH 1  
				    INCREMENT BY 1"];
    }
    public function getSequence($sequenceName): string
    {
        $sequenceName = ($sequenceName == '') ? $this->parent->tableSequence : $sequenceName;
        return "select \"$sequenceName\".nextval as \"id\" from dual";
    }

    public function createTable($tableName, $definition, $primaryKey = null, $extra = '', $extraOutside = '') : string
    {
        $sql = "CREATE TABLE \"$tableName\" (";
        foreach ($definition as $key => $type) {
            $sql .= "\"$key\" $type,";
        }
        $sql = rtrim($sql, ',');
        $sql .= "$extra ); ";
        if($primaryKey!==null) {
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

    public function createFK($tableName, $foreignKey): ?string
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
                $sql .= "ALTER TABLE \"$tableName\" ADD CONSTRAINT {$tableName}_fk_$key FOREIGN KEY (\"$key\") $value;";
            }
        }
        return $sql;
    }

    /**
     * For 12c and higher.
     *
     * @param $sql
     * @return string
     */
    public function limit($sql) : string
    {
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
                    return 'OCI: unable to find pk via query. Use the name of the table';
                }
            } else {
                $q="SELECT cols.column_name RESULT
                        FROM all_constraints cons, all_cons_columns cols
                        WHERE cols.table_name = ? and COLS.OWNER=? 
                        AND cons.constraint_type = 'P'
                        AND cons.constraint_name = cols.constraint_name
                        AND cons.owner = cols.owner";

                $r=$this->parent->runRawQuery($q,[$query,$this->parent->db],true);

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
        } catch(Exception $ex) {
            return false;
        }
    }

    public function callProcedure($procName, &$arguments = [], $outputColumns = [])
    {
        // TODO: Implement callProcedure() method.
    }

    public function createProcedure($procedureName, $arguments = [], $body = '', $extra = ''): string
    {
        if(is_array($arguments)) {
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
            $sqlArgs=$arguments;
        }
        $sql="CREATE OR REPLACE PROCEDURE \"$procedureName\" ($sqlArgs) $extra AS\n";
        $sql.="BEGIN\n$body\nEND $procedureName;";
        return $sql;
    }

    public function db($dbname): string
    {
        return  'ALTER SESSION SET CURRENT_SCHEMA = "' . $dbname.'"';
    }
}
