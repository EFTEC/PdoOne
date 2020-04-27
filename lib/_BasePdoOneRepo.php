<?php /** @noinspection NullPointerExceptionInspection */
/** @noinspection PhpUnused */
/** @noinspection PhpUndefinedMethodInspection */

/** @noinspection PhpUndefinedClassConstantInspection */


namespace eftec;


use Exception;
use PDOStatement;
use RuntimeException;

/**
 * Class _BaseRepo
 * @version       2.2 2020-04-27
 * @package       eftec
 * @author        Jorge Castro Castillo
 * @copyright (c) Jorge Castro C. MIT License  https://github.com/EFTEC/PdoOne
 */
abstract class _BasePdoOneRepo
{
    /** @var PdoOne */
    public static $pdoOne;


    /**
     * It creates a new table<br>
     * If the table exists then the operation is ignored (and it returns false)
     *
     * @param null $extra
     *
     * @return array|bool|PDOStatement
     * @throws Exception
     */
    public static function createTable($extra = null) {
        if (!self::getPdoOne()->tableExist(static::TABLE)) {
            return self::getPdoOne()
                       ->createTable(static::TABLE, $definition = static::getDef()
                           , static::getDefKey()
                           , $extra);
        }
        return false; // table already exist
    }


    /**
     * It is used for DI.<br>
     * If the field is not null, it returns the field self::$pdoOne<br>
     * If the global function pdoOne exists, then it is used<br>
     * if the global variable $pdoOne exists, then it is used<br>
     * Otherwise, it returns null
     *
     * @return PdoOne
     */
    protected static function getPdoOne() {
        if (self::$pdoOne !== null) {
            return self::$pdoOne;
        }
        if (function_exists('pdoOne')) {
            return pdoOne();
        }
        if (isset($GLOBALS['pdoOne'])) {
            return $GLOBALS['pdoOne'];
        }
        return null;
    }

    /**
     * It sets the field self::$pdoOne
     *
     * @param $pdoOne
     */
    public static function setPdoOne($pdoOne) {
        self::$pdoOne = $pdoOne;
    }

    /**
     * It creates a foreign keys<br>
     *
     * @return array|bool|PDOStatement
     * @throws Exception
     */
    public static function createFk() {
        return self::getPdoOne()->createFk(static::TABLE, static::getDefFk());
    }

    /**
     * It validates the table and returns an associative array with the errors.
     *
     * @return array If valid then it returns an empty array
     * @throws Exception
     */
    public static function validTable() {
        return self::getPdoOne()
                   ->validateDefTable(static::TABLE, static::getDef(), static::getDefKey(), static::getDefFk());
    }

    /**
     * It cleans the whole table (delete all rows)
     *
     * @return array|bool|PDOStatement
     * @throws Exception
     */
    public static function truncate() {
        return self::getPdoOne()->truncate(static::TABLE);
    }

    /**
     * It drops the table (structure and values)
     *
     * @return array|bool|PDOStatement
     * @throws Exception
     */
    public static function dropTable() {
        if (!self::getPdoOne()->tableExist(static::TABLE)) {
            return self::getPdoOne()->dropTable(static::TABLE);
        }
        return false; // table does not exist
    }

    /**
     * Insert an new row
     *
     * @param array $entity =static::factory()
     *
     * @return mixed
     * @throws Exception
     */
    public static function insert($entity) {
        return self::getPdoOne()->insertObject(static::TABLE, $entity);
    }

    /**
     * Update an registry
     *
     * @param array $entity =static::factory()
     *
     * @return mixed
     * @throws Exception
     */
    public static function update($entity) {
        return self::getPdoOne()->from(static::TABLE)->set($entity)->where(static::PK, $entity[static::PK])->update();
    }

    /**
     * It deletes a registry
     *
     * @param array $entity =static::factory()
     *
     * @return mixed
     * @throws Exception
     */
    public static function delete($entity) {
        return self::deleteById($entity[static::PK]);
    }

    /**
     * It deletes a registry
     *
     * @param mixed $pk
     *
     * @return mixed
     * @throws Exception
     */
    public static function deleteById($pk) {
        return self::getPdoOne()->from(static::TABLE)->where(static::PK, $pk)->delete();
    }

    /**
     * It gets a registry using the primary key.
     *
     * @param mixed $pk If mixe
     *
     * @return array static::factory()
     * @throws Exception
     */
    public static function first($pk=null) {
        $cols=self::buildSelect();
        self::getPdoOne()->select($cols)->from(static::TABLE);
        if(self::getPdoOne()->hasWhere()) {
            return self::transform(self::getPdoOne()->first());
        }

        if($pk===null) {
            throw new RuntimeException('_BasePdoOneRepo: first() without primary key or where');
        }
        return self::transform(self::getPdoOne()->where(static::PK, $pk)->first());
    }
    public static function buildSelect($prefixTable='',$prefix='') {
        $cols='*';
        $recursive=self::getPdoOne()->getRecursive();
        $prefixTable=($prefixTable==='')?static::TABLE:$prefixTable;
    
        if(is_array($recursive) && count($recursive) > 0) {
            $keys=array_keys(static::getDef());
            $keyRel=static::getDefFK(false);
            $cols='';
            foreach($keys as $key=>$value) {
                $cols.=self::getPdoOne()->addDelimiter($prefixTable)
                    .".{$value} as ".self::getPdoOne()->addDelimiter($prefix.$value). ',';
            }
            // adding the relation many to one (if any).
            foreach($keyRel as $key=>$value) {
                $tableAlias=$prefixTable.':'.$key;
                $ui=str_replace(':','/',substr($tableAlias,strpos($tableAlias,':')));
                if(in_array($ui,$recursive)) {

                    $table = $value['reftable'];
                    $colLocal = self::getPdoOne()->addDelimiter($prefixTable . '.' . $key);

                    $class = $table . 'Repo';
                    //$tableAlias=$prefixTable.':'.$table.':'.$key;

                    //$tableAlias2=$prefixTable.':'.$key;
                    $colRef = self::getPdoOne()->addDelimiter($tableAlias . '.' . $value['refcol']);

                    self::getPdoOne()->innerjoin("$table as `$tableAlias` on $colLocal=$colRef");
                    /** @see \eftec\_BasePdoOneRepo::buildSelect however it uses static of each repo */
                    $cols .= $class::buildSelect($tableAlias, $tableAlias . ':') . ','; // the comma is trimmed
                }
            }
        }
        return rtrim($cols,',');
    }
    public static function getRecursive() {
        return self::getPdoOne()->getRecursive();
    }
    public static function setRecursive($recursive) {
        self::getPdoOne()->recursive($recursive);
        return static::ME;
    }

    /**
     * It returns a list of rows
     *
     * @param null|array $where =static::factory()
     *
     * @param array|mixed $args The arguments of the method
     * @return array [static::factory()]
     * @throws Exception
     */
    public static function toList($where = null,$args=PdoOne::NULL) {
        $cols=self::buildSelect();
        self::getPdoOne()->select($cols)->from(static::TABLE)->where($where,$args);

        $rows=self::getPdoOne()->toList();
        $result=[];
        foreach($rows as $row) {
            $result[]=self::transForm($row);    
        }
        return $result;
    }
    public static function transform($inputRow) {
        $row=[];
        foreach($inputRow as $col=>$value) {
            if(strpos($col,':')!==false) {
                $arr=explode(':',$col);
                $c=count($arr);
                $k0='/'.$arr[1];
                switch ($c) {
                    case 2:
                        $row[$k0][$arr[1]] = $value;
                        break;
                    case 3:
                        $row[$k0][$arr[1]][$arr[2]] = $value;
                        break;
                    case 4:
                        $k2=$k0.'/'.$arr[2];
                        if(!isset($row[$k0][$arr[1]][$k2])) {
                            $row[$k0][$arr[1]][$k2]=[];    
                        }
                        $row[$k0][$arr[1]][$k2][$arr[3]] = $value;
                        break;
                    case 5:
                        $row[$k0][$arr[1]][$arr[2]][$arr[3]][$arr[4]]= $value;
                        break;
                    case 6:
                        $k2=$k0.'/'.$arr[2];
                        $k4=$k2.'/'.$arr[4];
                        if(!isset($row[$k0][$arr[1]][$k2][$arr[3]][$k4])) {
                            $row[$k0][$arr[1]][$k2][$arr[3]][$k4]=[];
                        }
                        $row[$k0][$arr[1]][$k2][$arr[3]][$k4][$arr[5]] = $value;
                        break;
                }
               
            } else {
                $row[$col]=$value;
            }
        }
        return $row;
    }
    
    /**
     * The next operation (in the chain of function) must be cached<br>
     * <b>Example</b>
     * <pre>
     * self::useCache(5000,'city')->toList();
     * </pre>
     * 
     * @param null $ttl
     * @param string $family
     * @return self
     */
    public static function useCache($ttl = null, $family = '') {
        self::getPdoOne()->useCache($ttl, $family);
        return static::ME;
    }
    /**
     * It invalidates a family/group of cache<br>
     * <b>Example</b>
     * <pre>
     * $list=CityRepo::useCache(50000,'city')->toList(); // using the cache
     * CityRepo::invalidateCache('city')->insert($city); // inserting a new value & flushing cache
     * $list=CityRepo::useCache(50000,'city')->toList(); // not using the cache
     * </pre>
     * 
     * @param string $family The family/grupo of cache(s) to invalidate.
     * @return self
     */
    public static function invalidateCache($family = '') {
        self::getPdoOne()->invalidateCache('',$family);
        return static::ME;
    }
    /**
     * It adds an "limit" in a query. It depends on the type of database<br>
     * @param $sql
     * @return self
     * @throws Exception
     */
    public static function limit($sql) {
        self::getPdoOne()->limit($sql);
        return static::ME;
    }
    /**
     * @param $order
     * @return self
     */
    public static function order($order) {
        self::getPdoOne()->order($order);
        return static::ME;
    }

    /**
     * @param $sql
     * @param string $condition
     * @return self
     */
    public static function innerjoin($sql, $condition = '') {
        self::getPdoOne()->innerjoin($sql, $condition);
        return static::ME;
    }

    /**
     * @param $sql
     * @return self
     */
    public static function left($sql) {
        self::getPdoOne()->left($sql);
        return static::ME;
    }

    /**
     * @param string $sql
     * @return self
     */
    public static function right($sql) {
        self::getPdoOne()->right($sql);
        return static::ME;
    }

    /**
     * @param string $sql
     * @return self
     */
    public static function group($sql) {
        self::getPdoOne()->group($sql);
        return static::ME;
    }

    /**
     * @param array|string $sql=static::factory()
     * @param null|array $param
     * @return static
     */
    public static function where($sql, $param = null) {
        self::getPdoOne()->where($sql, $param);
        return static::ME;
    }
    
    /**
     * It returns the number of rows
     *
     * @param null|array $where =static::factory()
     *
     * @return int
     * @throws Exception
     */
    public static function count($where = null) {
        return (int)self::getPdoOne()->count()->from(static::TABLE)->where($where)->firstScalar();
    }

    /**
     * @param $sql
     * @param $param
     * @return self
     */
    public function having($sql, $param = self::NULL) {
        self::getPdoOne()->having($sql, $param);
        return static::ME;
    }

}