<?php

/**
 * @noinspection PhpMissingParamTypeInspection
 * @noinspection ReturnTypeCanBeDeclaredInspection
 * @noinspection UnknownInspectionInspection
 * @noinspection DuplicatedCode
 * @noinspection PhpUnhandledExceptionInspection
 * @noinspection DisconnectedForeachInstructionInspection
 * @noinspection PhpUnused
 * @noinspection NullPointerExceptionInspection
 * @noinspection PhpUndefinedMethodInspection
 * @noinspection PhpUndefinedClassConstantInspection
 */

namespace eftec;


use Exception;
use PDOStatement;
use RuntimeException;

/**
 * Class _BasePdoOneRepo
 *
 * @version       4.13 2021-02-13
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
    public static $pageSize = 20;
    public static $lastException = '';
    /** @var bool if true then it returns a false on error. If false, it throw an exception in case of error */
    protected static $falseOnError = false;
    /**
     * @var null|bool|int $ttl If <b>null</b> then the cache never expires.<br>
     *                         If <b>false</b> then we don't use cache.<br>
     *                         If <b>int</b> then it is the duration of the
     *     cache
     *                         (in seconds)
     */
    private static $useCache = false;
    /** @var null|string the unique id generate by sha256 and based in the query, arguments, type and methods */
    private static $uid;
    /** @var string [optional] It is the family or group of the cache */
    private static $cacheFamily = '';

    /**
     * If true then it returns false on exception. Otherwise, it throws an exception.
     *
     * @param bool $falseOnError
     *
     * @return mixed
     */
    public static function setFalseOnError($falseOnError = true)
    {
        self::$falseOnError = $falseOnError;
        return static::ME;
    }

    /**
     * It creates a new table<br>
     * If the table exists then the operation is ignored (and it returns false)
     *
     * @param null $extra
     *
     * @return array|bool|PDOStatement
     * @throws Exception
     */
    public static function createTable($extra = null)
    {
        try {
            if (!self::getPdoOne()->tableExist(static::TABLE)) {
                return self::getPdoOne()
                    ->createTable(static::TABLE, $definition = static::getDef('sql'), static::getDefKey(), $extra);
            }
        } catch (Exception $exception) {
            if (self::$falseOnError) {
                self::reset();
                self::$lastException = $exception->getMessage();
                return false;
            }
            self::reset();
            throw $exception;
        }
        self::reset();
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
    protected static function getPdoOne()
    {
        if (self::$pdoOne !== null) {
            return self::$pdoOne;
        }
        if (function_exists('PdoOne')) {
            return PdoOne();
        }
        if (isset($GLOBALS['pdoOne']) && $GLOBALS['pdoOne'] instanceof PdoOne) {
            return $GLOBALS['pdoOne'];
        }
        return null;
    }

    /**
     * It sets the field self::$pdoOne
     *
     * @param $pdoOne
     */
    public static function setPdoOne($pdoOne)
    {
        self::$pdoOne = $pdoOne;
    }

    /**
     * It resets the stack
     *
     * @param bool $forcedPdoOne if true then it also resets the inner stack of PdoOne
     */
    protected static function reset($forcedPdoOne = false)
    {
        self::$useCache = false;
        self::$uid = null;
        self::$cacheFamily = '';
        self::$gQueryCounter = 0;
        self::$gQuery = [];
        self::$falseOnError = false;
        self::$lastException = '';
        self::getPdoOne()->builderReset($forcedPdoOne);
    }

    /**
     * It returns a pdoOne instance (if any one). It also resets any stacked valued.
     * <br><b>Example</b>:<br>
     * <pre>
     * $values=self::base()->select('*')->from('table')->toList();
     * </pre>
     *
     * @return PdoOne|null
     * @see \eftec\_BasePdoOneRepo::getPdoOne
     */
    public static function base()
    {
        if (self::getPdoOne() === null) {
            throw new RuntimeException('PdoOne not set');
        }
        self::reset(true);
        return self::getPdoOne();
    }


    /**
     * It test the recursivity by displaying all recursivity.
     *
     * @param null   $initClass
     * @param string $recursiveInit
     */
    public static function testRecursive($initClass = null, $recursiveInit = '')
    {
        if ($initClass === null) {
            $local = static::ME;
        } else {
            $local = static::NS . $initClass;
        }
        //$recursive=$local::getPdoOne()->getRecursive();
        $relations = $local::getDefFK();
        foreach ($relations as $nameCol => $r) {
            $key = $r['key'];
            $recursiveComplete = ltrim($recursiveInit . '/' . $nameCol, '/');
            if (self::getPdoOne()->hasRecursive($recursiveComplete)) {
                $used = '';
            } else {
                $used = '// ';
            }
            switch ($key) {
                case 'PARENT':
                    $class = static::RELATIONS[$r['reftable']];
                    echo "// \$relation['".$recursiveComplete. "']; //".$local . '->' . $class . " ($key)<br>";
                    break;
                case 'MANYTOONE':
                case 'ONETOONE':
                case 'ONETOMANY':
                    $class = static::RELATIONS[$r['reftable']];
                    echo $used."\$relation['".$recursiveComplete. "']; //".$local . '->' . $class . " ($key)<br>";
                    if($used==='') {
                        self::testRecursive($class,$recursiveComplete);
                    }
                    break;
                case 'MANYTOMANY':
                    $class = static::RELATIONS[$r['table2']];
                    echo $used."\$relation['".$recursiveComplete. "']; //".$local . '->' . $class . " ($key)<br>";
                    if($used!=='') {
                        self::testRecursive($class,$recursiveComplete);
                    }
                    break;
            }
        }
    }

    /**
     * It creates foreign keys of the table using the definition previously defined.
     *
     * @throws Exception
     */
    public static function createForeignKeys()
    {
        try {
            $def = static::getDefFK(true);
            $def2 = static::getDefFK(false);
            foreach ($def as $k => $v) {
                $sql = 'ALTER TABLE ' . self::getPdoOne()->addQuote(static::TABLE) . ' ADD CONSTRAINT '
                    . self::getPdoOne()->addQuote($def2[$k]['name']) . ' ' . $v;
                $sql = str_ireplace('FOREIGN KEY REFERENCES',
                    'FOREIGN KEY(' . self::getPdoOne()->addQuote($k) . ') REFERENCES', $sql);
                self::getPdoOne()->runRawQuery($sql, []);
            }
        } catch (Exception $exception) {
            if (self::$falseOnError) {
                self::reset();
                self::$lastException = $exception->getMessage();
                return false;
            }
            self::reset();
            throw $exception;
        }
        self::reset();
        return true;
    }

    /**
     * It runs a query and returns an array, value or false if error.<br>
     * The this command does not stack with other operators (such as where(),sort(),etc.)
     * <br><b>Example</b>:<br>
     * <pre>
     * $values=$con->query('select * from table where id=?',["i",20]',true);
     * </pre>
     *
     * @param string     $sql   The query to run
     * @param array|null $param [Optional] The arguments of the query in the form [value,value2..]
     *
     * @return array|bool|false|null
     * @throws Exception
     */
    public static function query($sql, $param = null)
    {
        try {
            $pdoOne = self::getPdoOne();
            if (self::$useCache && $pdoOne->getCacheService() !== null) {
                self::$uid = $pdoOne->buildUniqueID([$sql, $param], 'query');
                $getCache = $pdoOne->getCacheService()->getCache(self::$uid, static::TABLE);
                if ($getCache !== false) {
                    self::reset();
                    return $getCache;
                }
                $recursiveClass = static::getRecursiveClass();
                $usingCache = true;
            } else {
                $recursiveClass = null;
                $usingCache = false;
            }
            $rowc = self::getPdoOne()->runRawQuery($sql, $param);
            if ($rowc !== false && $usingCache) {
                $pdoOne->getCacheService()->setCache(self::$uid, $recursiveClass, $rowc, self::$useCache);
                self::reset();
            }
        } catch (Exception $exception) {
            if (self::$falseOnError) {
                self::reset();
                self::$lastException = $exception->getMessage();
                return false;
            }
            self::reset();
            throw $exception;
        }
        self::reset();
        return $rowc;
    }

    /**
     * With the recursive arrays, it gets all the classes related to the query (including this class) without the
     * namespace<br>
     * <b>Example:</b><br>
     * <pre>
     * CityRepo::setRecursive(['/countryFK'])::getRecursiveClass(); // ['CityRepo','TableFk']
     * </pre>
     *
     * @param null|array $final  It is used internally for recursivity, it keeps the values.
     * @param string     $prefix It is used internally for recursivity.
     *
     * @return array|null
     */
    public static function getRecursiveClass(&$final = null, $prefix = '')
    {
        $recs = self::getPdoOne()->getRecursive();
        $keyRels = static::getDefFK(false);
        $ns = self::getNamespace();
        if ($final === null) {
            // we start the chain
            $final = [];
            $final[] = static::RELATIONS[static::TABLE]; // PdoOne::camelize(static::TABLE) . $postfix;
        }
        foreach ($recs as $rec) {
            $keyr = $prefix . $rec;
            if (isset($keyRels[$keyr])) {
                $className
                    = static::RELATIONS[$keyRels[$keyr]['reftable']]; //PdoOne::camelize($keyRels[$keyr]['reftable']) . $postfix;
                $class = $ns . $className;
                if (!in_array($className, $final, true)) {
                    $final[] = $className;
                }
                $class::getRecursiveClass($final, $keyr);
                if ($keyRels[$keyr]['key'] === 'MANYTOMANY') {
                    $className
                        = static::RELATIONS[$keyRels[$keyr]['table2']]; // PdoOne::camelize($keyRels[$keyr]['table2']) . $postfix;
                    $class = $ns . $className;
                    if (!in_array($className, $final, true)) {
                        $final[] = $className;
                    }
                    $class::getRecursiveClass($final, $keyr);
                }
            }
        }
        return $final;
    }

    /**
     * It gets the current namespace.
     *
     * @return string
     */
    public static function getNamespace()
    {
        if (strpos(static::class, '\\')) { // we assume that every repo class lives in the same namespace.
            $ns = explode('\\', static::class);
            array_pop($ns);
            $ns = implode('\\', $ns) . '\\';
        } else {
            $ns = '';
        }
        return $ns;
    }

    /**
     * It creates a foreign keys<br>
     *
     * @return array|bool|PDOStatement
     * @throws Exception
     */
    public static function createFk()
    {
        try {
            return self::getPdoOne()->createFk(static::TABLE, static::getDefFk());
        } catch (Exception $exception) {
            if (self::$falseOnError) {
                self::$lastException = $exception->getMessage();
                return false;
            }
            throw $exception;
        }
    }

    /**
     * It validates the table and returns an associative array with the errors.
     *
     * @return array If valid then it returns an empty array
     * @throws Exception
     */
    public static function validTable()
    {
        try {
            return self::getPdoOne()
                ->validateDefTable(static::TABLE, static::getDef('sql'), static::getDefKey(), static::getDefFk(true));
        } catch (Exception $exception) {
            if (self::$falseOnError) {
                self::reset();
                self::$lastException = $exception->getMessage();
                return ['exception' => $exception->getMessage()];
            }
            self::reset();
            throw $exception;
        }
    }

    /**
     * It cleans the whole table (delete all rows)
     *
     * @param bool $force If true then it forces the truncate (it is useful when the table has a foreign key)
     *
     * @return array|bool|PDOStatement
     * @throws Exception
     */
    public static function truncate($force = false)
    {
        try {
            return self::getPdoOne()->truncate(static::TABLE, '', $force);
        } catch (Exception $exception) {
            if (self::$falseOnError) {
                self::$lastException = $exception->getMessage();
                return false;
            }
            throw $exception;
        }
    }


    /**
     *  It resets the identity of a table (if any)
     *
     * @param int $newValue
     *
     * @return array|bool|null
     * @throws Exception
     */
    public static function resetIdentity($newValue = 0)
    {
        try {
            return self::getPdoOne()->resetIdentity(static::TABLE, $newValue);
        } catch (Exception $exception) {
            if (self::$falseOnError) {
                self::$lastException = $exception->getMessage();
                return false;
            }
            throw $exception;
        }
    }

    /**
     * It drops the table (structure and values)
     *
     * @return array|bool|PDOStatement
     * @throws Exception
     */
    public static function dropTable()
    {
        try {
            if (!self::getPdoOne()->tableExist(static::TABLE)) {
                return self::getPdoOne()->dropTable(static::TABLE);
            }
        } catch (Exception $exception) {
            if (self::$falseOnError) {
                self::reset();
                self::$lastException = $exception->getMessage();
                return false;
            }
            self::reset();
            throw $exception;
        }
        self::reset();
        return false; // table does not exist
    }

    /**
     * It gets the postfix of the class base considering the the class is based in the table<br>
     * Example: Class "SomeTableRepo" and table "sometable", the prefix is "Repo"
     *
     * @return false|string False on error or not found.
     */
    public static function getPostfix()
    {
        $class = static::class;
        $table = static::TABLE;
        $p0 = strripos($class, $table) + strlen($table);
        if ($p0 === false) {
            return false;
        }
        return substr($class, $p0);
    }

    /**
     * It sets to use cache for the current pipelines. It is disabled at the end of the pipeline<br>
     * It only works if we set the cacheservice<br>
     * <b>Example</b><br>
     * <pre>
     * $this->setCacheService($instanceCache);
     * $this->useCache()->select()..; // The cache never expires
     * $this->useCache(60)->select()..; // The cache lasts 60 seconds.
     * $this->useCache(60,'customers')
     *        ->select()..; // cache associated with customers
     *                      // it could be invalidated by invalidateCache()
     * $this->useCache(60,['customers','invoices'])
     *        ->select()..; // cache associated with customers
     *                      // it could be invalidated by invalidateCache()
     * $this->useCache(60,'*')->select('col')
     *      ->from('table')->toList(); // '*' uses all the table assigned.
     * </pre>
     *
     * @param null|bool|int $ttl        <b>null</b> then the cache never expires.<br>
     *                                  <b>false</b> then we don't use cache.<br>
     *                                  <b>int</b> then it is the duration of the cache (in seconds)
     * @param string|array  $family     [optional] It is the family or group of the cache. It could be used to
     *                                  identify a group of cache to invalidate the whole group (for example
     *                                  ,invalidate all cache from a specific table).<br>
     *                                  <b>*</b> If "*" then it uses the tables assigned by from() and join()
     *
     * @return self
     */
    public static function useCache($ttl = null, $family = '')
    {
        self::getPdoOne()->useCache($ttl, $family);
        self::$useCache = $ttl;
        return static::ME;
    }

    /**
     * Its a macro of limit but it works for paging. It uses static::$pageSize to determine the rows to return
     *
     * @param int $numPage Number of page. It starts with 1.
     *
     * @return mixed
     * @throws Exception
     */
    public static function page($numPage)
    {
        $p0 = static::$pageSize * ($numPage - 1);
        $p1 = $p0 + static::$pageSize;
        return static::limit("$p0,$p1");
    }

    /**
     * It adds an "limit" in a query. It depends on the type of database<br>
     *
     * @param $sql
     *
     * @return self
     * @throws Exception
     */
    public static function limit($sql)
    {
        self::getPdoOne()->limit($sql);
        return static::ME;
    }

    /**
     * @param $order
     *
     * @return self
     */
    public static function order($order)
    {
        self::getPdoOne()->order($order);
        return static::ME;
    }

    /**
     * @param        $sql
     * @param string $condition
     *
     * @return self
     */
    public static function innerjoin($sql, $condition = '')
    {
        self::getPdoOne()->innerjoin($sql, $condition);
        return static::ME;
    }

    /**
     * @param $sql
     *
     * @return self
     */
    public static function left($sql)
    {
        self::getPdoOne()->left($sql);
        return static::ME;
    }

    /**
     * @param string $sql
     *
     * @return self
     */
    public static function right($sql)
    {
        self::getPdoOne()->right($sql);
        return static::ME;
    }

    /**
     * @param string $sql
     *
     * @return self
     */
    public static function group($sql)
    {
        self::getPdoOne()->group($sql);
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
    public static function count($where = null)
    {
        $pdoOne = self::getPdoOne();
        if (self::$useCache && $pdoOne->getCacheService() !== null) {
            self::$uid = $pdoOne->buildUniqueID([$where], static::TABLE . '::count');
            $getCache = $pdoOne->getCacheService()->getCache(self::$uid, static::TABLE);
            if ($getCache !== false) {
                self::reset();
                return $getCache;
            }
            $recursiveClass = static::getRecursiveClass();
            $usingCache = true;
        } else {
            $recursiveClass = null;
            $usingCache = false;
        }
        $newQuery = [];
        $newQuery['type'] = 'QUERY';
        static::$gQuery[0] =& $newQuery;
        $newQuery['joins'] = static::TABLE . ' as ' . static::TABLE . " \n";
        // we build the query
        static::generationRecursive($newQuery, static::TABLE . '.'); //, '', '', false
        $from = (isset(self::$gQuery[0]['joins'])) ? self::$gQuery[0]['joins'] : [];
        $rowc = self::getPdoOne()->count()->from($from)->where($where)->firstScalar();
        if ($rowc !== false && $usingCache) {
            $pdoOne->getCacheService()->setCache(self::$uid, $recursiveClass, (int)$rowc, self::$useCache);
            self::reset();
        }
        return $rowc;
    }

    /**
     * The result is stored on self::$gQuery
     *
     * @param        $newQuery
     * @param string $pTable prefix table (usually table.)
     * @param string $pColumn
     * @param string $recursiveInit
     * @param bool   $new
     */
    protected static function generationRecursive(
        &$newQuery,
        $pTable = '',
        $pColumn = '',
        $recursiveInit = '',
        $new = false
    )
    {
        $cols = static::getDefName();
        $keyRels = static::getDefFK(false);
        //$newQuery=[];
        $pt = $pTable === '' ? static::TABLE . '.' : $pTable;
        // add columns of the current table
        foreach ($cols as $col) {
            $newQuery['columns'][] = $pt . $col . ' as ' . self::getPdoOne()->addQuote($pColumn . $col);
        }
        $ns = self::getNamespace();

        foreach ($keyRels as $nameCol => $keyRel) {
            $type = $keyRel['key'];
            if ($type !== 'FOREIGN KEY') {
                // $nameColClean = trim($nameCol, PdoOne::$prefixBase);
                $recursiveComplete = ltrim($recursiveInit . '/' . $nameCol, '/');
                //echo "check recursive: $recursiveComplete<br>";
                if (self::getPdoOne()->hasRecursive($recursiveComplete)) {
                    //echo "OK $type<br>";
                    // type='PARENT' is n
                    switch ($type) {
                        case 'MANYTOONE':
                            static::$gQueryCounter++;
                            $tableRelAlias = 't' . static::$gQueryCounter; //$prefixtable.$nameColClean;
                            $col = ltrim($nameCol, PdoOne::$prefixBase); //$keyRel['col'];
                            //$tableRelAlias =trim($recursiveInit.'_'.$nameColClean,'/'); //str_replace(['/'],['.'],$recursiveInit.'.'.$nameColClean);
                            $colRelAlias = $pColumn . $nameCol;
                            $class = $ns
                                . static::RELATIONS[$keyRel['reftable']]; // $ns . PdoOne::camelize($keyRel['reftable']) . $postfix;
                            $refCol = ltrim($keyRel['refcol'], PdoOne::$prefixBase);
                            $newQuery['joins'] .= " left join {$keyRel['reftable']} as $tableRelAlias "
                                . "on {$pt}{$col}=$tableRelAlias.$refCol \n"; // $recursiveInit$nameCol\n"; // adds a query to the current query
                            $class::generationRecursive($newQuery, $tableRelAlias . '.', $colRelAlias . '.',
                                $recursiveComplete, false); // $recursiveInit . $nameCol
                            break;
                        case 'ONETOONE':
                            static::$gQueryCounter++;
                            $tableRelAlias = 't' . static::$gQueryCounter; //$prefixtable.$nameColClean;
                            $col = $keyRel['col'];
                            $colRelAlias = $pColumn . $nameCol;
                            $class = $ns
                                . static::RELATIONS[$keyRel['reftable']]; // $ns . PdoOne::camelize($keyRel['reftable']) . $postfix;
                            $refCol = $keyRel['refcol']; // ltrim($keyRel['refcol'], PdoOne::$prefixBase);

                            $newQuery['joins'] .= " left join {$keyRel['reftable']} as $tableRelAlias "
                                . "on {$pt}{$col}=$tableRelAlias.$refCol \n"; // $recursiveInit$nameCol\n"; // adds a query to the current query
                            $class::generationRecursive($newQuery, $tableRelAlias . '.', $colRelAlias . '.',
                                $recursiveComplete, false); // $recursiveInit . $nameCol
                            break;
                        case 'ONETOMANY':
                            //$tableRelAlias = ''; //'t' . static::$gQueryCounter;
                            $other = [];
                            $refColClean = trim($keyRel['refcol'], PdoOne::$prefixBase);
                            $other['type'] = 'ONETOMANY';
                            $other['table'] = $keyRel['reftable'];
                            $other['where'] = $refColClean;
                            $other['joins'] = " {$keyRel['reftable']} \n";
                            //$tableRelAlias = '*2';
                            $other['col'] = $pColumn . $keyRel['col']; //***
                            $other['col2'] = $pColumn . $nameCol;
                            $other['name'] = $nameCol;
                            $other['data'] = $keyRel;
                            //self::$gQuery[]=$other;
                            $class = $ns
                                . static::RELATIONS[$keyRel['reftable']]; // $ns . PdoOne::camelize($keyRel['reftable']) . $postfix;
                            $class::generationRecursive($other, '', '', $pColumn . $recursiveComplete,
                                false); //$recursiveInit . $nameCol
                            self::$gQuery[] = $other;
                            break;
                        case 'MANYTOMANY':
                            $rec = self::getPdoOne()->getRecursive();
                            // automatically we add recursive.
                            $rec[] = $recursiveComplete . $keyRel['refcol2']; // $recursiveInit . $nameCol
                            self::getPdoOne()->recursive($rec);
                            //$tableRelAlias = ''; //'t' . static::$gQueryCounter;
                            $other = [];
                            $refColClean = trim($keyRel['refcol2'], PdoOne::$prefixBase);
                            $other['type'] = 'ONETOMANY';
                            $other['table'] = $keyRel['reftable'];
                            $other['where'] = $refColClean;
                            $other['joins'] = " {$keyRel['reftable']} \n";
                            //$tableRelAlias = '*2';
                            $other['col'] = $pColumn . $keyRel['col']; //***
                            $other['col2'] = $pColumn . $nameCol;
                            $other['name'] = $nameCol;
                            $other['data'] = $keyRel;
                            $class = $ns
                                . static::RELATIONS[$keyRel['reftable']]; // $ns . PdoOne::camelize($keyRel['reftable']) . $postfix;
                            $class::generationRecursive($other, '', '', $pColumn . $recursiveComplete,
                                false); //$recursiveInit . $nameCol
                            // we reduce a level
                            //$columns = $other['columns'];
                            $columnFinal = [];
                            $findme = ltrim($keyRel['refcol2'], PdoOne::$prefixBase);
                            //echo "findme $findme<br>";
                            //if($pTable==='') {
                            $columnFinal[] = $findme . ' as ' . self::getPdoOne()->addQuote($pColumn . $findme);
                            /*} else {
                                echo "<hr>ptable:$pTable manytoone";
                                echo $findme."<br>";
                                // convert /somefk.column -> column
                                // convert /anything.column -> (deleted)
                                foreach ($columns as $vc) {
                                    //$findme = $keyRel['refcol2'] . '.';
                                    if (strpos($vc, $findme) !== false) {
                                        $columnFinal[] = str_replace($findme, '', $vc);
                                    }
                                }
                            }*/
                            $other['columns'] = $columnFinal;
                            self::$gQuery[] = $other;
                            break;
                        case 'PARENT':
                            // parent does not load recursively information.
                            break;
                        default:
                            trigger_error(static::TABLE . "Repo : type [$type] not defined.");
                    }
                }
            }
        }
        if ($new) {
            self::$gQuery[] = $newQuery;
        }
    }

    protected static function _merge($entity, $transaction = true)
    {
        if (static::_exist($entity)) {
            return static::_update($entity, $transaction);
        }
        return static::_insert($entity, $transaction);
    }

    protected static function _exist($entity)
    {
        try {
            $pks = static::PK;
            if (is_object($entity)) {
                $entity = static::objectToArray($entity);
            }
            if (is_array($entity)) {
                foreach ($entity as $k => $v) { // we keep the pks
                    if (!in_array($k, $pks, true)) {
                        unset($entity[$k]);
                    }
                }
            } elseif (is_array($pks) && count($pks)) {
                $entity = [$pks[0] => $entity];
            } else {
                self::getPdoOne()
                    ->throwError('exist: entity not specified as an array or table lacks of PKs', json_encode($entity));
                return false;
            }
            $r = self::getPdoOne()->genError()->select('1')->from(static::TABLE)->where($entity)->firstScalar();
            self::getPdoOne()->genError(true);
            self::reset();
            return ($r === '1');
        } catch (Exception $exception) {
            if (self::$falseOnError) {
                self::reset();
                self::$lastException = $exception->getMessage();
                return false;
            }
            self::reset();
            throw $exception;
        }
    }

    /**
     * Update an registry
     *
     * @param array|object $entity =static::factory()
     *
     * @param bool         $transaction
     *
     * @return mixed
     * @throws Exception
     */
    protected static function _update($entity, $transaction = true)
    {
        try {
            if (is_object($entity)) {
                $entity = static::objectToArray($entity);
            }
            $pdoOne = self::getPdoOne();
            //$defTable = static::getDef('conversion');
            (static::ME)::convertInputVal($entity);
            self::invalidateCache();
            // only the fields that are defined are inserted
            $entityCopy = self::intersectArraysNotNull($entity, static::getDefName());
            $entityCopy = self::diffArrays($entityCopy, array_merge(static::getDefKey(), static::getDefNoUpdate())); // columns discarded
            if ($pdoOne->transactionOpen === true) {
                // we disable transaction to avoid nested transactions.
                // mysql does not allows nested transactions
                // sql server allows nested transaction but afaik, it only counts the outer one.
                $transaction = false;
            }
            if ($transaction) {
                $pdoOne->startTransaction();
            }
            $recursiveBack = $pdoOne->getRecursive();
            $r = $pdoOne->from(static::TABLE)->set($entityCopy)->where(static::intersectArrays($entity, static::PK))
                ->update();
            $pdoOne->recursive($recursiveBack); // update() delete the value of recursive
            $defs = static::getDefFK();
            $ns = self::getNamespace();
            $fatherPK = $entity[static::PK[0]];
            foreach ($defs as $key => $def) { // ['/tablaparentxcategory']=['key'=>...]
                if ($def['key'] === 'ONETOMANY' && $pdoOne->hasRecursive($key, $recursiveBack)) {
                    if (!isset($entity[$key]) || !is_array($entity[$key])) {
                        $newRows = [];
                    } else {
                        $newRows = $entity[$key];
                    }
                    $classRef = $ns
                        . static::RELATIONS[$def['reftable']]; // $ns . PdoOne::camelize($def['reftable']) . $postfix;
                    $col1 = ltrim($def['col'], PdoOne::$prefixBase);
                    $refcol = ltrim($def['refcol'], PdoOne::$prefixBase); // it is how they are joined
                    $refpk = $classRef::PK[0];
                    $newRowsKeys = [];
                    foreach ($newRows as $v) {
                        $newRowsKeys[] = $v[$refpk];
                    }
                    //self::_setRecursive([$def['refcol2']]);
                    self::_setRecursive([]);

                    $oldRows = ($classRef::where($refcol, $entity[$col1]))::_toList();
                    $oldRowsKeys = [];
                    foreach ($oldRows as $v) {
                        $oldRowsKeys[] = $v[$refpk];
                    }
                    $insertKeys = array_diff($newRowsKeys, $oldRowsKeys);
                    $deleteKeys = array_diff($oldRowsKeys, $newRowsKeys);
                    // inserting a new value
                    foreach ($newRows as $item) {
                        if (in_array($item[$refpk], $insertKeys, false)) {
                            $item[$refcol] = $fatherPK;
                            $classRef::insert($item, false);
                        } elseif (!in_array($item[$refpk], $deleteKeys, false)) {
                            $classRef::update($item, false);
                        }
                    }
                    foreach ($deleteKeys as $key2) {
                        $classRef::deleteById($key2, false);
                    }
                }
                if ($def['key'] === 'MANYTOMANY') { //hasRecursive($recursiveInit . $key)
                    if (!isset($entity[$key]) || !is_array($entity[$key])) {
                        $newRows = [];
                    } else {
                        $newRows = $entity[$key];
                    }
                    $classRef = $ns
                        . static::RELATIONS[$def['reftable']]; // $ns . PdoOne::camelize($def['reftable']) . $postfix;
                    $class2 = $ns
                        . static::RELATIONS[$def['table2']]; // $ns . PdoOne::camelize($def['table2']) . $postfix;
                    $col1 = ltrim($def['col'], PdoOne::$prefixBase);
                    $refcol = ltrim($def['refcol'], PdoOne::$prefixBase);
                    $refcol2 = ltrim($def['refcol2'], PdoOne::$prefixBase);
                    $col2 = ltrim($def['col2'], PdoOne::$prefixBase);
                    $newRowsKeys = [];
                    foreach ($newRows as $v) {
                        $newRowsKeys[] = $v[$col2];
                    }
                    //self::_setRecursive([$def['refcol2']]);
                    self::_setRecursive([]);
                    $oldRows = ($classRef::where($refcol, $entity[$col1]))::_toList();
                    $oldRowsKeys = [];
                    foreach ($oldRows as $v) {
                        $oldRowsKeys[] = $v[$refcol2];
                    }
                    $insertKeys = array_diff($newRowsKeys, $oldRowsKeys);
                    $deleteKeys = array_diff($oldRowsKeys, $newRowsKeys);
                    // inserting a new value
                    foreach ($newRows as $item) {
                        if (in_array($item[$col2], $insertKeys, false)) {
                            $pk2 = $item[$def['col2']];
                            if ($class2::exist($item) === false
                                && self::getPdoOne()->hasRecursive($key, $recursiveBack)
                            ) {
                                $pk2 = $class2::insert($item, false);
                            } else {
                                $class2::update($item, false);
                            }
                            $relationalObjInsert = [$refcol => $entity[$def['col']], $refcol2 => $pk2];
                            $classRef::insert($relationalObjInsert, false);
                        }
                    }
                    // delete
                    foreach ($newRows as $item) {
                        if (in_array($item[$col2], $deleteKeys)) {
                            $pk2 = $item[$def['col2']];
                            if (self::getPdoOne()->hasRecursive($key, $recursiveBack)) {
                                $class2::deleteById($item, $pk2);
                            }
                            $relationalObjDelete = [$refcol => $entity[$def['col']], $refcol2 => $pk2];
                            $classRef::deleteById($relationalObjDelete, false);
                        }
                    }
                }
            }
            if ($transaction) {
                self::getPdoOne()->commit();
            }
            return $r;
        } catch (Exception $exception) {
            if ($transaction) {
                self::getPdoOne()->rollback();
            }
            if (self::$falseOnError) {
                self::reset();
                self::$lastException = $exception->getMessage();
                return false;
            }
            self::reset();
            throw $exception;
        }
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
     * @param string $family The family/grupo of cache(s) to invalidate. If empty or null, then it invalidates the
     *                       current table and all recursivity (if any)
     *
     * @return self
     */
    public static function invalidateCache($family = '')
    {
        if (self::getPdoOne()->getCacheService() !== null) {
            if (!$family) {
                self::getPdoOne()->getCacheService()->invalidateCache('', self::getRecursiveClass());
            } else {
                self::getPdoOne()->invalidateCache('', $family);
            }
        }
        return static::ME;
    }

    /**
     * It filter an associative array<br>
     * <b>Example:</b><br>
     * <pre>
     * self::intersectArraysNotNull(['a1'=>1,'a2'=>2],['a1','a3']); // ['a1'=>1]
     * </pre>
     *
     * @param array $arrayValues An associative array with key as the column
     * @param array $arrayIndex  An indexed array with the name of the columns
     *
     * @return array
     */
    public static function intersectArraysNotNull($arrayValues, $arrayIndex)
    {
        $result = [];
        foreach ($arrayValues as $k => $v) {
            if (in_array($k, $arrayIndex)) {
                $result[$k] = $v;
            }
        }
        return $result;
    }

    /**
     * Remove elements of an array unsing an array (indexed or not)<br>
     * <pre>
     * $this->diffArrays(['a'=>'aaa','b'=>'bbb'],['a'],false); // [b'=>'bbb']
     * $this->diffArrays(['a'=>'aaa','b'=>'bbb'],[0=>'a'],true); // [b'=>'bbb']
     * </pre>
     *
     * @param      $arrayValues
     * @param      $arrayIndex
     * @param bool $indexIsKey
     *
     * @return array
     */
    public static function diffArrays($arrayValues, $arrayIndex, $indexIsKey = false)
    {
        $result = [];
        foreach ($arrayValues as $k => $v) {
            if (!$indexIsKey && !in_array($k, $arrayIndex)) {
                $result[$k] = $v;
            }
            if ($indexIsKey && !array_key_exists($k, $arrayIndex)) {
                $result[$k] = $v;
            }
        }
        return $result;
    }

    /**
     * Merge two arrays only if the value of the second array is contained in the first array<br>
     * It works as masking. Example:<br>
     * <pre>
     * $this->intersectArrays(['a'=>'aaa','b'=>'bbb'],['a'],false); // ['a'=>'aaa']
     * $this->intersectArrays(['a'=>'aaa','b'=>'bbb'],[0=>'a'],true); // ['a'=>'aaa']
     * </pre>
     *
     * @param array $arrayValues An associative array with the keys and values.
     * @param array $arrayIndex  A string array with the indexes (if indexisKey=false then index is the value)
     * @param bool  $indexIsKey  (default false) if true then the index of $arrayIndex is considered as key
     *                           , otherwise the value of $arrayIndex is considered as key.
     *
     * @return array
     */
    public static function intersectArrays($arrayValues, $arrayIndex, $indexIsKey = false)
    {
        $result = [];
        foreach ($arrayIndex as $k => $v) {
            if ($indexIsKey) {
                $result[$k] = isset($arrayValues[$k]) ? $arrayValues[$k] : null;
            } else {
                $result[$v] = isset($arrayValues[$v]) ? $arrayValues[$v] : null;
            }
        }
        return $result;
    }

    /**
     * It sets the recursivity to read/insert/update the information.<br>
     * The fields recursives are marked with the prefix '/'.  For example 'customer' is a single field (column), while
     * '/customer' is a relation. Usually, a relation has both fields and relation.
     * - If the relation is manytoone, then the query is joined with the table indicated in the relation. Example:<br>
     * <pre>
     * ProductRepo::setRecursive(['/Category'])::toList(); // select .. from Producto inner join Category on ..
     * </pre>
     * - If the relation is onetomany, then it creates an extra query (or queries) with the corresponding values.
     * Example:<br>
     * <pre>
     * CategoryRepo::setRecursive(['/Product'])::toList(); // select .. from Category and select from Product where..
     * </pre>
     * - If the reation is onetoone, then it is considered as a manytoone, but it returns a single value. Example:<br>
     * <pre>
     * ProductRepo::setRecursive(['/ProductExtension'])::toList(); // select .. from Product inner join
     * ProductExtension
     * </pre>
     * - If the relation is manytomany, then the system load the relational table (always, not matter the recursivity),
     * and it reads/insert/update the next values only if the value is marked as recursive. Example:<br>
     * <pre>
     * ProductRepo::setRecursive(['/product_x_category'])::toList(); // it returns porduct, productxcategory and
     * category ProductRepo::setRecursive([])->toList(); // it returns porduct and productxcategory (if
     * /productcategory is marked as manytomany)
     * </pre>
     *
     *
     * @param array $recursive An indexed array with the recursivity.
     *
     * @return self
     * @see static::getDefFK for where to define the relation.
     */
    protected static function _setRecursive($recursive)
    {
        self::getPdoOne()->recursive($recursive);
        return static::ME;
    }

    /**
     * Insert an new row
     *
     * @param array|object $entity =static::factory()
     *
     * @param bool         $transaction
     *
     * @return mixed
     * @throws Exception
     */
    protected static function _insert(&$entity, $transaction = true)
    {
        $returnObject = false;
        try {
            $pdoOne = self::getPdoOne();
            //$defTable = static::getDef('conversion');
            //self::_convertInputValue($entity, $defTable);

            if (is_object($entity)) {
                $returnObject = clone $entity;
                $entity = static::objectToArray($entity);
            }
            (static::ME)::convertInputVal($entity);
            self::invalidateCache();
            $recursiveBack = $pdoOne->getRecursive();  // recursive is deleted by insertObject
            // only the fields that are defined are inserted
            $entityCopy = self::intersectArraysNotNull($entity, static::getDefName());
            $entityCopy = self::diffArrays($entityCopy, static::getDefNoInsert()); // discard some columns
            if (count($entityCopy) === 0) {
                self::getPdoOne()
                    ->throwError('insert: insert without fields or fields incorrects. Please check the syntax' .
                        ' and case of the fields', $entity);
                return false;
            }
            if ($pdoOne->transactionOpen === true) {
                // we disable transaction to avoid nested transactions.
                // mysql does not allows nested transactions
                // sql server allows nested transaction but afaik, it only counts the outer one.
                $transaction = false;
            }
            if ($transaction) {
                $pdoOne->startTransaction();
            }
            $insert = $pdoOne->insertObject(static::TABLE, $entityCopy);
            $pks = static::IDENTITY;
            if ($pks !== null) {
                // we update the identity of $entity ($entityCopy is already updated).
                if ($returnObject !== false) {
                    $returnObject->$pks = $insert;
                } else {
                    $entity[$pks] = $insert;
                }
            } else {
                $pks = static::PK[0];
                $insert = $returnObject !== false ? $returnObject->$pks : $entity[$pks];
            }
            $defs = static::getDefFK();
            $ns = self::getNamespace();
            foreach ($defs as $key => $def) { // ['/tablaparentxcategory']=['key'=>...]
                if (isset($entity[$key]) && is_array($entity[$key])) {
                    if ($def['key'] === 'ONETOMANY' && $pdoOne->hasRecursive($key, $recursiveBack)) {
                        $classRef = $ns
                            . static::RELATIONS[$def['reftable']]; // $ns . PdoOne::camelize($def['reftable']) . $postfix;
                        foreach ($entity[$key] as $item) {
                            // we only insert it if it has a recursive
                            $refCol = ltrim($def['refcol'], PdoOne::$prefixBase);
                            $item[$refCol]
                                = $entityCopy[$def['col']]; // if the pk (of the original object) is identity.
                            $classRef::insert($item, false);
                        }
                    }
                    if ($def['key'] === 'MANYTOMANY') {
                        $class2 = $ns
                            . static::RELATIONS[$def['table2']]; // $ns . PdoOne::camelize($def['table2']) . $postfix;
                        foreach ($entity[$key] as $item) {
                            $pk2 = $item[$def['col2']];
                            if ($pdoOne->hasRecursive($key, $recursiveBack) && $class2::exist($item) === false) {
                                // we only update it if it has a recursive
                                $pk2 = $class2::insert($item, false);
                            }
                            $classRel = $ns
                                . static::RELATIONS[$def['reftable']]; // $ns . PdoOne::camelize($def['reftable']) . $postfix;
                            $refCol = ltrim($def['refcol'], PdoOne::$prefixBase);
                            $refCol2 = ltrim($def['refcol2'], PdoOne::$prefixBase);
                            $relationalObj = [$refCol => $entityCopy[$def['col']], $refCol2 => $pk2];
                            $classRel::insert($relationalObj, false);
                        }
                    }
                }
            }
            if ($transaction) {
                self::getPdoOne()->commit();
            }
            if ($returnObject !== false) {
                $entity = $returnObject;
            }
            return $insert;
        } catch (Exception $exception) {
            if ($transaction) {
                self::getPdoOne()->rollback();
            }
            if (self::$falseOnError) {
                self::reset();
                self::$lastException = $exception->getMessage();
                if ($returnObject !== false) {
                    $entity = $returnObject;
                }
                return false;
            }
            self::reset();
            if ($returnObject !== false) {
                $entity = $returnObject;
            }
            throw $exception;
        }
    }


    protected static function objectToArray($obj)
    {
        if (is_object($obj) || is_array($obj)) {
            $ret = (array)$obj;
            foreach ($ret as &$item) {
                $item = self::objectToArray($item);
            }
            return $ret;
        }
        return $obj;
    }

    /**
     * @param mixed $filter
     * @param mixed $filterValue
     *
     * @return mixed|array|bool|PDOStatement
     * @throws Exception
     */
    protected static function _toList($filter = PdoOne::NULL, $filterValue = null)
    {
        if ($filterValue === null && is_array($filter)) {
            // _tolist(['field'=>'aaa') => (['table.field'=>'aaa']);
            $pt = static::TABLE . '.';
            $pk = [];
            foreach ($filter as $k => $v) {
                if (strpos($k, '.') === false) {
                    $pk[$pt . $k] = $v;
                } else {
                    $pk[$k] = $v;
                }
            }
            $filter = $pk;
        }


        return self::generationStart('toList', $filter, $filterValue);
    }

    protected static function generationStart($type, $filter = PdoOne::NULL, $filterValue = PdoOne::NULL)
    {
        try {
            static::$gQuery = [];
            static::$gQueryCounter = 0;
            /** @var PdoOne $pdoOne instance of PdoOne */
            $pdoOne = self::getPdoOne();
            if (self::$useCache && $pdoOne->getCacheService() !== null) {
                self::$uid = $pdoOne->buildUniqueID([$filter, $filterValue], static::TABLE . '::' . $type);
                $getCache = $pdoOne->getCacheService()->getCache(self::$uid, static::TABLE);
                if ($getCache !== false) {
                    self::reset();
                    return $getCache;
                }
                $recursiveClass = static::getRecursiveClass();
                $usingCache = true;
            } else {
                $recursiveClass = null;
                $usingCache = false;
            }
            $newQuery = [];
            $newQuery['type'] = 'QUERY';
            static::$gQuery[0] =& $newQuery;
            $newQuery['joins'] = static::TABLE . ' as ' . static::TABLE . " \n";
            // we build the query
            static::generationRecursive($newQuery, static::TABLE . '.');//, '', '', false
            $rows = false;
            foreach (static::$gQuery as $query) {
                if ($query['type'] === 'QUERY') {
                    $from = $query['joins'];
                    $cols = implode(',', $query['columns']);
                    if (static::EXTRACOLS !== '') {
                        $cols .= (($cols !== '') ? ',' : '') . static::EXTRACOLS;
                    }
                    switch ($type) {
                        case 'toList':
                            $rows = $pdoOne->select($cols)->from($from)->where($filter, $filterValue)->toList();
                            break;
                        case 'first':
                            //$pdoOne->builderReset();
                            $rows = [
                                $pdoOne->select($cols)->from($from)->where($filter)->first()
                            ];
                            break;
                        default:
                            trigger_error('Repo: method $type not defined');
                            self::reset();
                            return false;
                    }
                }
                foreach ($rows as &$row) {
                    if ($query['type'] === 'ONETOMANY') {
                        $from = $query['joins'];
                        $cols = implode(',', $query['columns']);
                        $partialRows = $pdoOne->select($cols)->from($from)->where($query['where'], $row[$query['col']])
                            ->toList();
                        foreach ($partialRows as $k => $rowP) {
                            $row2 = self::convertRow($rowP);
                            $partialRows[$k] = $row2;
                        }
                        //$row['/' . $query['table']] = $partialRows;
                        $row[$query['col2']] = $partialRows;
                    }
                }
            }
            if (!is_array($rows)) {
                $rowc = $rows;
            } else {
                $c = count($rows);
                $rowc = [];
                for ($i = 0; $i < $c; $i++) {
                    $rowc[$i] = self::convertRow($rows[$i]);
                }
                self::convertSQLValueInit($rowc, true);
            }
            if ($rowc !== false && $usingCache) {
                $pdoOne->getCacheService()->setCache(self::$uid, $recursiveClass, $rowc, self::$useCache);
            }
            self::reset();
            return $rowc;
        } catch (Exception $exception) {
            if (self::$falseOnError) {
                self::reset();
                self::$lastException = $exception->getMessage();
                return false;
            }
            self::reset();
            throw $exception;
        }
    }

    /**
     * It converts ['aaa.bbb'=>'v'] into ['aaa']['bbb']='v';
     *
     * @param array $data
     *
     * @return array
     */
    protected static function convertRow($data)
    {
        if (!is_array($data)) {
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
                        // 'aaa.bb' => ['aaa']['bbb']
                        $row[$ar[0]][$ar[1]] = $v;
                        break;
                    case 3:
                        // 'aaa.bb.cc' => ['aaa']['bbb']['ccc']
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

    /**
     * @param array $rows The associative arrays with values to convert.
     * @param bool  $list false for a single row, or true for a list of rows.
     */
    protected static function convertSQLValueInit(&$rows, $list = false)
    {
        if (!$list) {
            $rows = [$rows];
        }
        //$defs = static::getDef('conversion');
        $ns = self::getNamespace();
        $rels = static::getDefFK();
        foreach ($rows as &$row) {
            //self::_convertOutputValue($row, $defs);
            (static::ME)::convertOutputVal($row);
            foreach ($rels as $k => $v) {
                if (isset($row[$k])) {
                    switch ($v['key']) {
                        // PARENT not because parent is a fk but is used for a one way relation.
                        case 'MANYTOONE':
                            $class = $ns . static::RELATIONS[$v['reftable']];
                            $class::convertSQLValueInit($row[$k], false);
                            break;
                        case 'ONETOMANY':
                            $class = $ns . static::RELATIONS[$v['reftable']];
                            $class::convertSQLValueInit($row[$k], true);
                            break;
                        case 'MANYTOMANY':
                            $class = $ns . static::RELATIONS[$v['table2']];
                            $class::convertSQLValueInit($row[$k], true);
                            break;
                    }
                }
            }
        }
        if (!$list) {
            $rows = $rows[0];
        }
    }

    /**
     * This method validates a model before it is inserted/updated into the database.
     *
     * @param object|array $model     It could be one model or multiple models.
     * @param boolean      $multiple  if true then it validates multiples models at once.
     * @param array        $recursive =self::factory()
     * @return bool if true then the model is valid, otherwise its false.
     */
    public static function validateModel($model, $multiple = false, $recursive = [])
    {
        if ($multiple) {
            if ($model === null || count($model) === 0) {
                return true;
            }
            $array = $model;
        } else {
            $array[0] = $model;
        }
        $defs = static::getDef();
        $fks = static::getDefFK();
        foreach ($array as $mod) {
            if (is_object($mod)) {
                $mod = (array)$mod;
            }
            foreach ($defs as $col => $def) {
                $curCol = array_key_exists($col, $mod) ? $mod[$col] : null;

                // if null (or missing) and it is allowed = true
                // if null (or missing) and not null and it is not identity = false (identities are generated)
                if (($curCol === null) && !($def['null'] === false && $def['identity'] === false)) {
                    return false;
                }
                switch ($def['phptype']) {
                    case 'binary':
                    case 'string':
                        if (!is_string($curCol)) {
                            // not a string
                            return false;
                        }
                        break;
                    case 'float':
                        if (!is_float($curCol)) {
                            return false;
                        }
                        break;
                    case 'timestamp':
                    case 'int':
                        if (!is_int($curCol)) {
                            return false;
                        }
                        break;
                    case 'time':
                    case 'datetime':
                    case 'date':
                        $bool = false;
                        $time = false;
                        $r = false;
                        if ($def['conversion'] === 'datetime2') {
                            $r = PdoOne::dateConvertInput($curCol, 'iso', $bool, $time);
                        } elseif ($def['conversion'] === 'datetime3') {
                            $r = PdoOne::dateConvertInput($curCol, 'human', $bool, $time);
                        } elseif ($def['conversion'] === 'datetime') {
                            $r = PdoOne::dateConvertInput($curCol, 'class', $bool, $time);
                        } elseif ($def['conversion'] === 'datetime4') {
                            $r = PdoOne::dateConvertInput($curCol, 'sql', $bool, $time);
                        }
                        if ($r === false) {
                            return false;
                        }
                }
            }
            if (count($recursive) > 0) {
                $ns = self::getNamespace();
                foreach ($fks as $key => $fk) {
                    if (array_key_exists($key, $mod) && self::getPdoOne()->hasRecursive($key, $recursive)) {
                        $curFK = $fk['key'];
                        $class = $ns . static::RELATIONS[$fk['reftable']];
                        switch ($curFK) {
                            case 'ONETOMANY':
                            case 'MANYTOMANY':
                                $r = $class::validateModel($mod[$key], true, $recursive);
                                break;
                            case 'MANYTOONE':
                            case 'ONETOONE':
                                $r = $class::validateModel($mod[$key], false, $recursive);
                                break;
                            default:
                                $r=true;
                        }
                        if ($r === false) {
                            return false;
                        }
                    }
                }
            }
        }


        return true;
    }

    /**
     * It deletes a registry
     *
     * @param mixed|array $pks
     *
     * @param bool        $transaction
     *
     * @return mixed
     * @throws Exception
     */
    protected static function _deleteById($pks, $transaction = true)
    {
        if (!is_array($pks)) {
            $pksI = [];
            $pksI[static::PK[0]] = $pks; // we convert into an associative array
        } else {
            $pksI = $pks;
        }
        return self::_delete($pksI, $transaction, static::PK);
    }

    /**
     * It deletes a row or rows.
     *
     * @param array|object $entity
     * @param bool         $transaction
     * @param array|null   $columns
     *
     * @return mixed
     * @throws Exception
     */
    protected static function _delete($entity, $transaction = true, $columns = null)
    {
        $columns = ($columns === null) ? static::getDefName() : $columns;
        try {
            if (is_object($entity)) {
                $entity = static::objectToArray($entity);
            }
            $entityCopy = self::intersectArraysNotNull($entity, $columns);
            self::invalidateCache();
            $pdoOne = self::getPdoOne();
            if ($pdoOne->transactionOpen === true) {
                // we disable transaction to avoid nested transactions.
                // mysql does not allows nested transactions
                // sql server allows nested transaction but afaik, it only counts the outer one.
                $transaction = false;
            }
            if ($transaction) {
                $pdoOne->startTransaction();
            }
            $defs = static::getDefFK();
            $ns = self::getNamespace();
            $recursiveBackup = self::getRecursive();
            foreach ($defs as $key => $def) { // ['/tablaparentxcategory']=['key'=>...]
                if ($def['key'] === 'ONETOMANY' && $pdoOne->hasRecursive($key, $recursiveBackup)) {
                    if (!isset($entity[$key]) || !is_array($entity[$key])) {
                        $newRows = [];
                    } else {
                        $newRows = $entity[$key];
                    }
                    $classRef = $ns
                        . static::RELATIONS[$def['reftable']]; // $ns . PdoOne::camelize($def['reftable']) . $postfix;
                    //$col1 = ltrim($def['col'], PdoOne::$prefixBase);
                    //$refcol = ltrim($def['refcol'], PdoOne::$prefixBase); // it is how they are joined
                    //$refpk = $classRef::PK[0];
                    foreach ($newRows as $item) {
                        $classRef::deleteById($item, false);
                    }
                }
                if ($def['key'] === 'MANYTOMANY' && isset($entity[$key])
                    && is_array($entity[$key])
                ) { //hasRecursive($recursiveInit . $key)
                    $classRef = $ns
                        . static::RELATIONS[$def['reftable']]; // $ns . PdoOne::camelize($def['reftable']) . $postfix;
                    $class2 = $ns
                        . static::RELATIONS[$def['table2']]; //$ns . PdoOne::camelize($def['table2']) . $postfix;
                    $col1 = ltrim($def['col'], PdoOne::$prefixBase);
                    $refcol = ltrim($def['refcol'], PdoOne::$prefixBase);
                    //$refcol2 = ltrim($def['refcol2'], PdoOne::$prefixBase);
                    $col2 = $def['col2'];
                    //self::_setRecursive([$def['refcol2']]);
                    self::_setRecursive([]);
                    $cols2 = [];
                    foreach ($entity[$key] as $item) {
                        $cols2[] = $item[$col2];
                    }
                    $relationalObjDelete = [$refcol => $entity[$col1]];
                    $classRef::delete($relationalObjDelete, false);
                    if (self::getPdoOne()->hasRecursive($key, $recursiveBackup)) {
                        foreach ($cols2 as $c2) {
                            // $k = $v[$refcol2];
                            $object2Delete = [$col2 => $c2];
                            $class2::delete($object2Delete, false);
                        }
                    }
                    self::_setRecursive($recursiveBackup);
                }
            }
            $r = self::getPdoOne()->delete(static::TABLE, $entityCopy);
            if ($transaction) {
                //self::getPdoOne()->rollback();
                self::getPdoOne()->commit();
            }
            self::reset();
            return $r;
        } catch (Exception $exception) {
            if ($transaction) {
                self::getPdoOne()->rollback();
            }
            if (self::$falseOnError) {
                self::reset();
                self::$lastException = $exception->getMessage();
                return false;
            }
            self::reset();
            throw $exception;
        }
    }

    public static function getRecursive()
    {
        return self::getPdoOne()->getRecursive();
    }

    /**
     * It gets the first value of a query<br>
     * <b>Example:</b><br>
     * <pre>
     * self::_first('2'); // select * from table where pk='2' (only returns the first value)
     * self::_first();  // select * from table (returns the first row if any)
     * self::where(['pk'=>'2'])::_first();  // select * from table where pk='2'
     * self::_first(['pk'=>'2']);  // select * from table where pk='2'
     * </pre>
     *
     * @param mixed $pk If mixed. If null then it doesn't use the primary key to obtain data.
     *
     * @return array|bool static::factory()
     * @throws Exception
     */
    protected static function _first($pk = PdoOne::NULL)
    {
        if ($pk !== PdoOne::NULL) {
            $tmp = is_array($pk) ? $pk : [static::PK[0] => $pk];
            $pt = static::TABLE . '.';
            $pk = [];
            foreach ($tmp as $k => $v) {
                $pk[$pt . $k] = $v;
            }

        }
        $r = self::generationStart('first', $pk);
        if (is_array($r)) {
            return $r[0];
        }
        return $r;
    }

    /**
     * It adds an having condition to the query pipeline. It could be stacked with many having()
     * @param array|string   $sql =self::factory()
     * @param null|array|int $param
     *
     * @return self
     */
    public function having($sql, $param = self::NULL)
    {
        self::getPdoOne()->having($sql, $param);
        return static::ME;
    }
}