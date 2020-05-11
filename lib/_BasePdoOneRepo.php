<?php 
/** @noinspection PhpUnhandledExceptionInspection
 * @noinspection DisconnectedForeachInstructionInspection
 * @noinspection PhpUnused
 * @noinspection NullPointerExceptionInspection
 * @noinspection PhpUndefinedMethodInspection
 * @noinspection PhpUndefinedClassConstantInspection 
 */


namespace eftec;


use Exception;
use PDOStatement;

/**
 * Class _BaseRepo
 * @version       4.0 2020-05-10  
 * @package       eftec
 * @author        Jorge Castro Castillo
 * @copyright (c) Jorge Castro C. MIT License  https://github.com/EFTEC/PdoOne
 */
abstract class _BasePdoOneRepo
{
    /** @var PdoOne */
    public static $pdoOne;
    /** @var array $gQuery =[['columns'=>[],'joins'=>[],'where'=>[]] */
    public static $gQuery = [];
    public static $gQueryCounter = 0;


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
                       ->createTable(static::TABLE, $definition = static::getDef(), static::getDefKey(), $extra);
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
        //try {
        return self::getPdoOne()
                   ->validateDefTable(static::TABLE, static::getDef(), static::getDefKey(), static::getDefFk());
        /*} catch(Exception $exception) {
            return ['exception'=>'not found '.$exception->getMessage()];
        }*/
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
    protected static function _insert($entity) {
        $entity = self::mergeArrays(static::getDef(true), $entity);
        return self::getPdoOne()->insertObject(static::TABLE, $entity);
    }

    /**
     * It converts ['aaa.bbb'=>'v'] into ['aaa']['bbb']='v';
     *
     * @param array $data
     * @return array
     */
    protected static function convertRow($data) {
        if(!is_array($data)) {
            return $data;
        }
        $row = [];
        foreach ($data as $k => $v) {
            if (strpos($k, '.') === false) {
                $row[$k] = $v;
            } else {
                $ar = explode('.', $k);
                switch (count($ar)) {
                    case 2:
                        $row[$ar[0]][$ar[1]] = $v;
                        break;
                    case 3:
                        $row[$ar[0]][$ar[1]][$ar[2]] = $v;
                        break;
                    case 4:
                        $row[$ar[0]][$ar[1]][$ar[2]][$ar[3]] = $v;
                        break;
                    case 5:
                        $row[$ar[0]][$ar[1]][$ar[2]][$ar[3]][$ar[4]] = $v;
                        break;
                    case 6:
                        $row[$ar[0]][$ar[1]][$ar[2]][$ar[3]][$ar[4]][$ar[5]] = $v;
                        break;
                    case 7:
                        $row[$ar[0]][$ar[1]][$ar[2]][$ar[3]][$ar[4]][$ar[5]][$ar[6]] = $v;
                        break;
                    case 8:
                        $row[$ar[0]][$ar[1]][$ar[2]][$ar[3]][$ar[4]][$ar[5]][$ar[6]][$ar[7]] = $v;
                        break;                        
                }
            }
        }
        return $row;
    }

    protected static function _toList($filter,$filterValue) {
        return self::generationStart('toList',$filter,$filterValue);
    }

    protected static function generationStart($type, $filter = null, $filterValue = null) {

        static::$gQuery = [];
        static::$gQueryCounter = 0;
        $newQuery = [];
        $newQuery['type'] = 'QUERY';
        static::$gQuery[0] =& $newQuery;
        $newQuery['joins'] = static::TABLE . "\n";
        // we build the query
        static::generationRecursive($newQuery, '', '', '', false);
        //die(1);
        /** @var PdoOne $pdoOne instance of PdoOne */
        $pdoOne = self::getPdoOne();

        $rows = false;
        foreach (static::$gQuery as $query) {
            if ($query['type'] === 'QUERY') {

                $from = $query['joins'];
                $cols = implode(',', $query['columns']);
                switch ($type) {
                    case 'toList':
                        $rows = $pdoOne->select($cols)->from($from)->where($filter, $filterValue)->toList();
                        break;
                    case 'first':
                        $pdoOne->builderReset();
                        $rows = [
                            $pdoOne->select($cols)->from($from)->where( $filterValue)
                                   ->first()
                        ];
                        break;
                    default:
                        trigger_error('Repo: method $type not defined');
                        return false;
                }
            }
            foreach ($rows as &$row) {
                if ($query['type'] === 'ONETOMANY') {
                    $from = $query['joins'];
                    $cols = implode(',', $query['columns']);
                    $partialRows =
                        $pdoOne->select($cols)->from($from)->where($query['where'], $row[$query['col']])->toList();
                    //->genError(false)
                    foreach ($partialRows as $k => $rowP) {
                        $row2 = self::convertRow($rowP);
                        $partialRows[$k] = $row2;
                    }
                    //$row['/' . $query['table']] = $partialRows;
                    $row[$query['col2']] = $partialRows;
                }
            }
        }
        if(!is_array($rows)) {
            return $rows;
        }
        $c = count($rows);
        $rowc=[];
        for ($i = 0; $i < $c; $i++) {
            $rowc[$i] = self::convertRow($rows[$i]);
        }
        return $rowc;
    }

    protected static function generationRecursive(
        &$newQuery, $pTable = '', $pColumn = '', $recursiveInit = '', $new = false
    ) {

        $cols = array_keys(static::getDef());
        $keyRels = static::getDefFK(false);
        //$newQuery=[];
        // add columns of the current table
        foreach ($cols as $col) {
            $newQuery['columns'][] = $pTable . $col . ' as `' . $pColumn . $col . '`';
        }
        $ns = self::getNamespace();

        foreach ($keyRels as $nameCol => $keyRel) {
            $type = $keyRel['key'];
            $nameColClean = trim($nameCol, '/');
            //echo "cheking recursive ".$recursiveInit . $nameCol."<br>";
            if (self::getPdoOne()->hasRecursive($recursiveInit . $nameCol)) {
                switch ($type) {
                    case 'MANYTOONE':
                        static::$gQueryCounter++;
                        $tableRelAlias = 't' . static::$gQueryCounter; //$prefixtable.$nameColClean;
                        $colRelAlias = $pColumn . $nameCol;
                        $class = $ns . $keyRel['reftable'] . 'Repo';
                        $refCol = $keyRel['refcol'];
                        $newQuery['joins'] .= " left join {$keyRel['reftable']} as $tableRelAlias " .
                            "on $pTable$nameColClean=$tableRelAlias.$refCol \n"; // $recursiveInit$nameCol\n"; // adds a query to the current query
                        //echo "send [" . $pColumn . ',' . $tableRelAlias . ']<br>';
                        $class::generationRecursive($newQuery, $tableRelAlias . '.', $colRelAlias . '.',
                                                    $recursiveInit . $nameCol, false);
                        break;
                    case 'ONETOMANY':
                        //$tableRelAlias = ''; //'t' . static::$gQueryCounter;
                        $other = [];
                        $refColClean = trim($keyRel['refcol'], '/');
                        $other['type'] = 'ONETOMANY';
                        $other['table'] = $keyRel['reftable'];
                        $other['where'] = $refColClean;
                        $other['joins'] = " {$keyRel['reftable']} \n";
                        //$tableRelAlias = '*2';
                        $other['col'] = $pColumn . $keyRel['col']; //***
                        $other['col2'] = $pColumn .$nameCol;
                        //var_dump($recursiveInit . $nameCol);
                        //var_dump($other['col2']);
                        //var_dump( $pColumn . $nameCol);
                        
                        $other['name'] = $nameCol;
                        $other['data'] = $keyRel;
                        //self::$gQuery[]=$other;
                        $class = $ns . $keyRel['reftable'] . 'Repo';

                        //echo "sendo [" . $pColumn . $recursiveInit . $nameCol . ']<br>';
                        /*$class::experimental($other
                            , $tableRelAlias . '.'
                            , $pColumn.$tableRelAlias . '.'
                            , $recursiveInit . $nameCol
                            , false);
                        */
                        $class::generationRecursive($other, '', ''
                            //, self::recursiveToColAlias($recursiveInit) .'.'.$nameCol.'.'
                            , $pColumn . $recursiveInit. $nameCol, false);
                        self::$gQuery[] = $other;
                        break;
                }
            }
        }
        if ($new) {
            self::$gQuery[] = $newQuery;
        }
    }

    // /col1/col2 => /col1./col2
    protected static function recursiveToColAlias($recursiveInit) {
        return ltrim(str_replace('/', './', $recursiveInit), '.');
    }

    /**
     * Update an registry
     *
     * @param array $entity =static::factory()
     *
     * @return mixed
     * @throws Exception
     */
    protected static function _update($entity) {
        return self::getPdoOne()->from(static::TABLE)->set($entity)->where(static::mergeArrays(static::PK, $entity))
                   ->update();
    }

    /**
     * Merge two arrays only if the value of the second array is contained in the first array<br>
     * It works as masking. Example:<br>
     * <pre>
     * $this->mergeArrays(['a'],['a'=>'aaa','b'=>'bbb'],true); // ['a'=>'aaa']
     * </pre>
     *
     * @param array $arrayIndex A string array with the indexes (if indexisKey=false then index is the value)
     * @param array $arrayValues An associative array with the keys and values.
     * @return array
     */
    public static function mergeArrays($arrayIndex, $arrayValues) {
        $result = [];
        foreach ($arrayIndex as $k => $v) {
            $result[$v] = isset($arrayValues[$v]) ? $arrayValues[$v] : null;
        }
        return $result;
    }

    /**
     * It deletes a registry
     *
     * @param array $entity =static::factory()
     *
     * @return mixed
     * @throws Exception
     */
    protected static function _delete($entity) {
        return self::deleteById($entity[static::PK]);
    }

    /**
     * It deletes a registry
     *
     * @param mixed|array $pk
     *
     * @return mixed
     * @throws Exception
     */
    protected static function _deleteById($pk) {
        if (!is_array($pk)) {
            $pk[static::PK[0]] = $pk; // we convert into an associative array
        }
        return self::getPdoOne()->from(static::TABLE)->where(self::mergeArrays(static::PK, $pk))->delete();
    }

    /**
     * It gets a registry using the primary key.
     *
     * @param mixed $pk If mixed
     *
     * @return array|bool static::factory()
     * @throws Exception
     */
    protected static function _first($pk = null) {
        $pk = is_array($pk) ? $pk : [static::PK[0]=> $pk];
        $r = self::generationStart('first', $pk);
        if (is_array($r)) {
            return $r[0];
        }
        return $r;
    }

    public static function getNamespace() {
        if (strpos(static::class, '\\')) { // we assume that every repo class lives in the same namespace.
            $ns = explode('\\', static::class, 2);
            $ns = $ns[0] . '\\';
        } else {
            $ns = '';
        }
        return $ns;
    }

    public static function getRecursive() {
        return self::getPdoOne()->getRecursive();
    }

    public static function setRecursive($recursive) {
        self::getPdoOne()->recursive($recursive);
        return static::ME;
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
        self::getPdoOne()->invalidateCache('', $family);
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
     * @param array|string $sql =static::factory()
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