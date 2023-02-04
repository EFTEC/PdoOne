<?php /** @noinspection PhpMultipleClassDeclarationsInspection */

/** @noinspection SqlNoDataSourceInspection */

namespace eftec;

use Exception;
use PDOStatement;
use RuntimeException;

/**
 * Class _BasePdoOneRepo.<br>
 * This class is used together with the repository classes.
 *
 * @version       6.5 2022-07-22
 * @package       eftec
 * @author        Jorge Castro Castillo
 * @copyright (c) Jorge Castro C. Dual Licence: MIT and Commercial License  https://github.com/EFTEC/PdoOne
 */
abstract class _BasePdoOneRepo
{
    // it is used for compatibility.
    public const BINARYVERSION = 12;

    /**
     * If true then it returns false on exception. Otherwise, it throws an exception.
     *
     * @param bool $falseOnError
     *
     * @return mixed
     */
    public static function setFalseOnError(bool $falseOnError = true)
    {
        static::$falseOnError = $falseOnError;
        return static::ME;
    }


    /**
     * It creates a new table<br>
     * If the table exists then the operation is ignored (and it returns false)
     *
     * @param ?string $extra
     *
     * @return bool
     * @throws Exception
     */
    public static function createTable(?string $extra = null): bool
    {
        try {
            if (!static::getPdoOne()->tableExist(static::TABLE)) {
                return static::getPdoOne()
                    ->createTable(static::TABLE, static::getDef('sql'), static::getDefKey(), $extra);
            }
        } catch (Exception $exception) {
            self::reset();
            if (static::$falseOnError) {
                static::$lastException = $exception->getMessage();
                return false;
            }
            throw $exception;
        }
        self::reset();
        return false; // table already exist
    }


    /**
     * It resets the stack
     *
     */
    public static function reset(): void
    {
        static::$uid = null;
        static::$gQueryCounter = 0;
        static::$gQuery = [];
        static::$falseOnError = false;
        static::$lastException = '';
        //self::getQuery()->builderReset($forcedPdoOne);
        static::$pdoOneQuery = null;
    }

    /**
     * It returns a pdoOne instance (if any one). It also resets any stacked valued.
     * <br><b>Example</b>:<br>
     * <pre>
     * $values=self::base()->select('*')->from('table')->toList();
     * </pre>
     *
     * @return PdoOne
     * @see _BasePdoOneRepo::getPdoOne
     */
    public static function base(): PdoOne
    {
        if (static::getPdoOne() === null) {
            static::getPdoOne()
                ->throwError('PdoOne not set', self::class);
        }
        self::reset();
        return static::getPdoOne();
    }

    /**
     * It is the same as PdoOne->addQuotes() but it avoids an extra call.
     * @param string $txt the column or value to quote
     * @return string
     * @see PdoOne::addQuote
     */
    public static function addQuote(string $txt): string
    {
        if (strlen($txt) < 2) {
            return $txt;
        }
        if ($txt[0] === static::$pdoOne->database_delimiter0 && $txt[-1] === static::$pdoOne->database_delimiter1) {
            // it is already quoted.
            return $txt;
        }
        return static::$pdoOne->database_delimiter0 . $txt . static::$pdoOne->database_delimiter1;
    }

    /**
     * It tests the recursivity by displaying all recursivity.
     *
     * @param ?string $initClass
     * @param string  $recursiveInit
     * @noinspection PhpUnused
     */
    public static function testRecursive(?string $initClass = null, string $recursiveInit = ''): void
    {
        if ($initClass === null) {
            $local = static::ME;
        } else {
            $local = static::NS . $initClass;
        }
        //$recursive=$local::getPdoOne()->getRecursive();
        /** @noinspection PhpUndefinedMethodInspection */
        $relations = $local::DEFFK;
        foreach ($relations as $nameCol => $r) {
            $key = $r['key'];
            $recursiveComplete = ltrim($recursiveInit . '/' . $nameCol, '/');
            if (self::getQuery()->hasRecursive($recursiveComplete)) {
                $used = '';
            } else {
                $used = '// ';
            }
            switch ($key) {
                case 'PARENT':
                    $class = static::RELATIONS[$r['reftable']];
                    echo "// \$relation['" . $recursiveComplete . "']; //" . $local . '->' . $class . " ($key)<br>";
                    break;
                case 'MANYTOONE':
                case 'ONETOONE':
                case 'ONETOMANY':
                    $class = static::RELATIONS[$r['reftable']];
                    echo $used . "\$relation['" . $recursiveComplete . "']; //" . $local . '->' . $class . " ($key)<br>";
                    if ($used === '') {
                        self::testRecursive($class, $recursiveComplete);
                    }
                    break;
                case 'MANYTOMANY':
                    $class = static::RELATIONS[$r['table2']];
                    echo $used . "\$relation['" . $recursiveComplete . "']; //" . $local . '->' . $class . " ($key)<br>";
                    if ($used !== '') {
                        self::testRecursive($class, $recursiveComplete);
                    }
                    break;
            }
        }
    }

    /**
     * @return PdoOneQuery
     */
    protected static function getQuery(): PdoOneQuery
    {
        if (static::$pdoOneQuery === null) {
            static::$pdoOneQuery = new PdoOneQuery(static::getPdoOne(), static::class);
        }
        return static::$pdoOneQuery;
    }

    /**
     * It creates foreign keys of the table using the definition previously defined.
     *
     * @throws Exception
     * @noinspection PhpUnused
     */
    public static function createForeignKeys(): bool
    {
        try {
            $def = static::DEFFKSQL;
            $def2 = static::DEFFK;
            foreach ($def as $k => $v) {
                $sql = 'ALTER TABLE ' . static::addQuote(static::TABLE) . ' ADD CONSTRAINT '
                    . static::addQuote($def2[$k]['name']) . ' ' . $v;
                $sql = str_ireplace('FOREIGN KEY REFERENCES',
                    'FOREIGN KEY(' . static::addQuote($k) . ') REFERENCES', $sql);
                static::getPdoOne()->runRawQuery($sql, []);
            }
        } catch (Exception $exception) {
            self::reset();
            if (static::$falseOnError) {
                static::$lastException = $exception->getMessage();
                return false;
            }
            throw $exception;
        }
        self::reset();
        return true;
    }

    /**
     * It runs a query and returns an array/value or false if error.<br>
     * This command does not stack with other operators (such as where(),sort(),etc.)<br>
     * <b>Example</b>:<br>
     * <pre>
     * $values=$con->query('select * from table where id=?',[20]'); // numeric argument
     * $values=$con->query('select * from table where id=:arg',['arg'=>20]); // named argument
     * $values=$con->query('select * from table where id=1'); // without argument
     * </pre>
     *
     * @param string     $sql   The query to run
     * @param array|null $param [Optional] The arguments of the query.
     *
     * @return array|bool|false|null
     * @throws Exception
     */
    public static function query(string $sql, ?array $param = null)
    {
        try {
            $query = self::getQuery();
            if ($query->useCache && $query->parent->getCacheService() !== null) {
                static::$uid = $query->buildUniqueID([$sql, $param], static::ENTITY . ':' . 'query');
                $getCache = $query->parent->getCacheService()->getCache(static::$uid, static::ENTITY);
                if ($getCache !== false) {
                    self::reset();
                    return $getCache;
                }
                $recursiveClass = static::getRecursiveClass();
                $usingCache = $query->useCache;
            } else {
                $recursiveClass = null;
                $usingCache = false;
            }
            $rowc = static::getPdoOne()->runRawQuery($sql, $param);
            if ($rowc !== false && $usingCache !== false) {
                $query->parent->getCacheService()->setCache(static::$uid, $recursiveClass, $rowc, $usingCache);
                self::reset();
            }
        } catch (Exception $exception) {
            self::reset();
            if (static::$falseOnError) {
                static::$lastException = $exception->getMessage();
                return false;
            }
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
     * CityRepo::recursive(['/countryFK'])::getRecursiveClass(); // ['CityRepo','TableFk']
     * </pre>
     *
     * @param array|null $final  It is used internally for recursivity, it keeps the values.
     * @param string     $prefix It is used internally for recursivity.
     *
     * @return array|null
     */
    public static function getRecursiveClass(array &$final = null, string $prefix = ''): ?array
    {
        $recs = self::getQuery()->getRecursive();
        $keyRels = static::DEFFK;
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
                /** @noinspection PhpUndefinedMethodInspection */
                $class::getRecursiveClass($final, $keyr);
                if ($keyRels[$keyr]['key'] === 'MANYTOMANY') {
                    $className
                        = static::RELATIONS[$keyRels[$keyr]['table2']]; // PdoOne::camelize($keyRels[$keyr]['table2']) . $postfix;
                    $class = $ns . $className;
                    if (!in_array($className, $final, true)) {
                        $final[] = $className;
                    }
                    /** @noinspection PhpUndefinedMethodInspection */
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
    public static function getNamespace(): string
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
     * @return bool
     * @throws Exception
     */
    public static function createFk(): bool
    {
        try {
            return static::getPdoOne()->createFk(static::TABLE, static::DEFFKSQL);
        } catch (Exception $exception) {
            if (static::$falseOnError) {
                static::$lastException = $exception->getMessage();
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
     * @noinspection PhpUnused
     */
    public static function validTable(): array
    {
        try {
            return static::getPdoOne()
                ->validateDefTable(static::TABLE, static::getDef('sql'), static::getDefKey(), static::DEFFKSQL);
        } catch (Exception $exception) {
            self::reset();
            if (static::$falseOnError) {
                static::$lastException = $exception->getMessage();
                return ['exception' => $exception->getMessage()];
            }
            throw $exception;
        }
    }

    /**
     * It cleans the whole table (delete all rows)
     *
     * @param bool $force If true then it forces the truncate (it is useful when the table has a foreign key)
     *
     * @return array|bool
     * @throws Exception
     */
    public static function truncate(bool $force = false)
    {
        try {
            return static::getPdoOne()->truncate(static::TABLE, '', $force);
        } catch (Exception $exception) {
            if (static::$falseOnError) {
                static::$lastException = $exception->getMessage();
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
    public static function resetIdentity(int $newValue = 0)
    {
        try {
            return static::getPdoOne()->resetIdentity(static::TABLE, $newValue);
        } catch (Exception $exception) {
            if (static::$falseOnError) {
                static::$lastException = $exception->getMessage();
                return false;
            }
            throw $exception;
        }
    }

    /**
     * It drops the table (structure and values)
     *
     * @return bool
     * @throws Exception
     */
    public static function dropTable(): bool
    {
        try {
            if (static::getPdoOne()->tableExist(static::TABLE)) {
                return static::getPdoOne()->dropTable(static::TABLE);
            }
        } catch (Exception $exception) {
            $fe = static::$falseOnError;
            self::reset();
            if ($fe) {
                static::$lastException = $exception->getMessage();
                return false;
            }
            throw $exception;
        }
        self::reset();
        return false; // table does not exist
    }

    /**
     * It gets the postfix of the class base considering then the class is based in the table<br>
     * Example: Class "SomeTableRepo" and table "sometable", the postfix is "Repo"
     *
     * @return false|string False on error or not found.
     * @noinspection PhpUnused
     */
    public static function getPostfix()
    {
        $class = static::class;
        $table = static::TABLE;
        $p0 = strripos($class, $table) + strlen($table);
        /** @noinspection PhpStrictComparisonWithOperandsOfDifferentTypesInspection */
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
     * $this->useCache()->select() ...; // The cache never expires
     * $this->useCache(60)->select() ...; // The cache lasts 60 seconds.
     * $this->useCache(60,'customers')
     *        ->select()...; // cache associated with customers
     *                      // it could be invalidated by invalidateCache()
     * $this->useCache(60,['customers','invoices'])
     *        ->select()...; // cache associated with customers
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
     * @return PdoOneQuery
     */
    public static function useCache($ttl = null, $family = ''): PdoOneQuery
    {
        return self::newQuery()->useCache($ttl, $family);
    }

    protected static function newQuery(): PdoOneQuery
    {
        return new PdoOneQuery(static::getPdoOne(), static::class);
    }

    /**
     * It's a macro of limit, but it works for paging. It uses static::$pageSize to determine the rows to return
     *
     * @param int      $numPage  Number of page. It starts with 1.
     * @param int|null $pageSize The size of the page. If the value is null, then it uses _BasePdoOneRepo::$pageSize
     *                           (20)
     * @return PdoOneQuery
     * @throws Exception
     */
    public static function page(int $numPage, ?int $pageSize = null): PdoOneQuery
    {
        $p0 = ($pageSize ?? static::$pageSize) * ($numPage - 1);
        //$p1 = $p0 + ($pageSize ?? static::$pageSize);
        //$p1 =$pageSize ?? static::$pageSize;
        return static::limit($p0, $pageSize);
    }

    /**
     * It adds a "limit" in a query. It depends on the type of database<br>
     * <b>Example:</b><br>
     * <pre>
     *      ->select("")->limit("10,20")->toList(); // it reads from the row 10th and reads the next 20 rows
     *      ->select("")->limit(10,20)->toList(); // it reads from the row 10th and reads the next 20 rows
     * </pre>
     *
     * @param mixed $first  The first value
     * @param mixed $second The second value
     * @return PdoOneQuery
     * @throws Exception
     * @test InstanceOf PdoOne::class,this('1,10')
     */
    public static function limit($first, $second = null): PdoOneQuery
    {
        return static::newQuery()->limit($first, $second);
    }

    /**
     * @param $order
     *
     * @return PdoOneQuery
     */
    public static function order($order): PdoOneQuery
    {
        return static::newQuery()->order($order);
    }

    /**
     * @param        $sql
     * @param string $condition
     *
     * @return PdoOneQuery
     */
    public static function innerjoin($sql, string $condition = ''): PdoOneQuery
    {
        return self::getQuery()->innerjoin($sql, $condition);
    }

    /**
     * @param $sql
     *
     * @return PdoOneQuery
     */
    public static function left($sql): PdoOneQuery
    {
        return static::newQuery()->left($sql);
    }

    /**
     * @param string $sql
     *
     * @return PdoOneQuery
     */
    public static function right(string $sql): PdoOneQuery
    {
        return static::newQuery()->right($sql);
    }

    /**
     * @param string $sql
     *
     * @return PdoOneQuery
     */
    public static function group(string $sql): PdoOneQuery
    {
        return static::newQuery()->group($sql);
    }

    /**
     * It returns the number of rows
     *
     * @param array|null $where =static::factoryUtil()
     *
     * @return int
     * @throws Exception
     */
    public static function count(?array $where = null)
    {
        $pdoOne = self::getQuery();
        if ($pdoOne->useCache && $pdoOne->parent->getCacheService() !== null) {
            static::$uid = $pdoOne->buildUniqueID([$where], static::ENTITY . ':count');
            $getCache = $pdoOne->parent->getCacheService()->getCache(static::$uid, static::ENTITY);
            if ($getCache !== false) {
                self::reset();
                return $getCache;
            }
            $recursiveClass = static::getRecursiveClass();
            $usingCache = $pdoOne->useCache;
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
        $from = static::$gQuery[0]['joins'] ?? [];
        $rowc = static::getPdoOne()
            ->from($from, static::$schema)
            ->where($where)
            ->count();
        if ($rowc !== false && $usingCache !== false) {
            $pdoOne->parent->getCacheService()->setCache(static::$uid, $recursiveClass, (int)$rowc, $usingCache);
            //self::reset();
        }
        self::reset();
        return $rowc;
    }

    public static function convertPlanColumns(array $result): array
    {
        $final = [];
        foreach ($result as $numRow => $rows) {
            foreach ($rows as $k => $v) {
                $keys = explode('/', $k);
                switch (count($keys)) {
                    case 1:
                        $final[$numRow]['??'] = '???';
                        break;
                    case 2:
                        $final[$numRow][$keys[1]] = $v;
                        break;
                    case 3:
                        $final[$numRow][$keys[1]][$keys[2]] = $v;
                        break;
                    case 4:
                        $final[$numRow][$keys[1]][$keys[2]][$keys[3]] = $v;
                        break;
                    case 5:
                        $final[$numRow][$keys[1]][$keys[2]][$keys[3]][$keys[4]] = $v;
                        break;
                }
            }
        }
        return $final;
    }

    /**
     * It starts the recursive execution plan
     * @param PdoOneQuery $currentQuery The initial current query
     * @param array|null  $conditions   (optional) a condition
     * @param bool        $first        if true then it only returns the first value (not as an array). It is used by
     *                                  first()
     * @return array
     */
    public static function executePlan0(PdoOneQuery $currentQuery, ?array $conditions = null, bool $first = false)
    {
        //$currentQuery = self::getQuery();
        $currentQuery->ormClass = null;
        if ($currentQuery->useCache && $currentQuery->parent->getCacheService() !== null) {
            static::$uid = $currentQuery->buildUniqueID(['$filter', '$filterValue'], static::ENTITY . ':' . $first);
            $getCache = $currentQuery->parent->getCacheService()->getCache(static::$uid, static::ENTITY);
            if ($getCache !== false) {
                self::reset();
                return $getCache;
            }
            //$recursiveClass = static::getRecursiveClass();
            $usingCache = $currentQuery->useCache;
            $usingCacheFamily = $currentQuery->cacheFamily;
        } else {
            //$recursiveClass = null;
            $usingCache = false;
            $usingCacheFamily = '';
        }
        $rowc = self::executePlan($currentQuery, '', $conditions, $currentQuery->getRecursive(), $first);
        $cls = static::ME;
        if ($first) {
            $cls::convertOutputVal($rowc);
        } else {
            foreach ($rowc as $v) {
                $cls::convertOutputVal($v);
            }
        }
        if ($rowc !== false && $usingCache !== false) {
            $currentQuery->parent->getCacheService()->setCache(static::$uid, $usingCacheFamily, $rowc, $usingCache);
        }
        self::reset();
        return $rowc;
    }

    /**
     * The execution of the current plan
     * @param PdoOneQuery $currentQuery   The query where the plan will be executed
     * @param string      $absolutePrefix The prefix absolute of the current execution plan
     * @param array|null  $conditions     The condition or conditions.
     * @param array|null  $dependency     The current depdendency
     * @param bool        $first          if true, then it only gets the first value.
     * @return array|false|mixed
     */
    public static function executePlan(PdoOneQuery $currentQuery, string $absolutePrefix, ?array $conditions = null, ?array $dependency = [], bool $first = false)
    {
        try {
            $where = [];
            $query = self::startPlan($absolutePrefix, $dependency, $where);
            [$columnSQL, $fromSQL] = explode('|FROM|', $query);
            if ($conditions !== null) {
                $whereSql = ' ';
                foreach ($conditions as $k => $v) {
                    $whereSql .= sprintf(" %s ='%s' AND", static::TABLE . '.' . static::addQuote($k), $v);
                }
                $whereSql = substr($whereSql, 0, -4); // we remove the last ' ANDº';
            } else {
                $whereSql = null;
            }
            $currentQuery->select($columnSQL)->from($fromSQL)->where($whereSql);
            $useCache = $currentQuery->useCache;
            $currentQuery->useCache = false;
            $result = $currentQuery->toList();
            $currentQuery->useCache = $useCache;
            if ($first && count($result) > 0) {
                $result = [$result[0]];
            }
            $rows = self::convertPlanColumns($result);
            if ($conditions === null) {
                $level = '';
                foreach ($rows as &$row) {
                    self::innerPlan($absolutePrefix, $dependency, $row, $level, $where);
                }
                unset($row);
            }
            if ($first && count($rows) > 0) {
                $rows = $rows[0];
            }
            return $rows;
        } catch (Exception $exception) {
            self::reset();
            if (static::$falseOnError) {
                static::$lastException = $exception->getMessage();
                return false;
            }
            static::getPdoOne()
                ->throwError("PdoOne: Error in $first", json_encode($conditions), '', true, $exception);
            return false;
        }
    }

    /**
     * It executes recursively the onetomany and manytomany.
     * @param string $absolutePrefix the prefix of the values to read, example '/_Customer/_City'
     * @param array  $dependency     the array with the dependencies used.
     * @param array  $row            the rows where the information will be stored
     * @param string $level          the level where the inforrmation is obtained. Is it the same as $absolutePrefix?
     * @param array  $where          if there is a condition
     * @return void
     * @noinspection PhpUndefinedMethodInspection
     */
    public static function innerPlan(string $absolutePrefix, array $dependency, array &$row, string $level, array $where): void
    {
        $ns = self::getNamespace();
        foreach ($row as $col => $cell) {
            if (is_array($cell)) {
                self::innerPlan($absolutePrefix . '/' . $col, $dependency, $row[$col], $level . '/' . $col, $where);
            }
            foreach ($where as $v) {
                $v1 = $v[1];
                if ($v[0] === $level) {
                    $reftable = $v1['reftable'];
                    $colCurrent = $absolutePrefix . '/' . $v1['alias'];
                    if ($dependency === ['*'] || in_array($colCurrent, $dependency, true)) {
                        if (($v1['key'] === 'ONETOMANY') && !isset($row[$v1['alias']])) {
                            $class = $ns . static::RELATIONS[$reftable];
                            /** @see _BasePdoOneRepo::planOneToMany */
                            $valueToFilter = $row[$v1['colalias']];
                            $row[$v1['alias']] = $class::planOneToMany(
                                $absolutePrefix . '/' . $v1['alias'],
                                $dependency,
                                $v1,
                                $valueToFilter);
                        }
                        if (($v1['key'] === 'MANYTOMANY') && !isset($row[$v1['alias']])) {
                            $valueToFilter = $row[$v1['colalias']];
                            $class = $ns . static::RELATIONS[$reftable];
                            /** @see _BasePdoOneRepo::planManyToMany */
                            $row[$v1['alias']] = $class::planManyToMany("$absolutePrefix/{$v1['alias']}", $dependency, $v1, $valueToFilter);
                        }
                    }
                }
            }
        }
    }

    public static function planManyToMany(string $absolutePrefix, array $dependency, array $relation, $value): array
    {
        $where = [substr($relation['refcol'], 1) => $value];
        if (!in_array($absolutePrefix . '/' . PdoOne::$prefixBase . $relation['refcol2alias'], $dependency, true)) {
            $dependency[] = $absolutePrefix . '/' . PdoOne::$prefixBase . $relation['refcol2alias'];
        }
        $nq = self::newQuery();
        $nq->ormClass = null;
        $rows = self::executePlan($nq, $absolutePrefix, $where, $dependency);
        $rowFinal = [];
        // flatting the query, so we remove the array relational
        foreach ($rows as $row) {
            $rowFinal[] = $row[PdoOne::$prefixBase . $relation['refcol2alias']];
        }
        return $rowFinal;
    }

    public static function planOneToMany(string $absolutePrefix, array $dependency, array $relation, $value): array
    {
        $where = [substr($relation['refcol'], 1) => $value];
        $nq = self::newQuery();
        $nq->ormClass = null;
        return self::executePlan($nq, $absolutePrefix, $where, $dependency);
    }

    /**
     * @param string $absolutePrefix The prefix absolute
     * @param array  $dependency     The current dependency to execute the plan
     * @param array  $where          the conditions if any.
     * @return string Returns the query.
     */
    public static function startPlan(string $absolutePrefix, array $dependency, array &$where): string
    {
        [$cols, $tables, $where] = self::plan($absolutePrefix, $dependency);
        $query = implode(",", $cols) . '|FROM|' . static::TABLE . " ";
        $query .= implode(" ", $tables);
        return $query;
    }

    /**
     * It generates the execution plan of a query<br>
     * It does not include relations onetomany and manytomany.
     * @param string $prefix           the prefix of the columns
     * @param string $aliasTableParent The alias of the parent table
     * @param string $noTableBack      the name of the parent table (no alias). it is used to avoid a ping-pong.
     * @return array=[$cols, $table, $where]
     */
    public static function plan(string $absolutePrefix = '', array $recursive = [], string $prefix = ''
        , string                       $aliasTableParent = '', string $noTableBack = ''): array
    {
        $defs = static::DEF;
        $fks = static::DEFFK;
        $ns = self::getNamespace();
        $table = [];
        $where = [];
        $cols = [];
        $aliasTableParentDot = $aliasTableParent === '' ? static::TABLE . '.' : static::addQuote($aliasTableParent) . '.';
        foreach ($defs as $colDB => $v) {
            $cols[] = $aliasTableParentDot . $colDB . ' as ' . static::addQuote($aliasTableParent . '/' . $v['alias']);
        }
        foreach ($fks as $colDB => $v) {
            $vkey = $v['key'];
            $kalias = $v['alias'];
            $aliasTable = $aliasTableParent . '/' . $kalias;
            if (($vkey === 'MANYTOONE' || $vkey === 'ONETOONE') && $v['reftable'] !== $noTableBack) { // || $v['key'] === 'ONETOONE'
                /** @noinspection NestedPositiveIfStatementsInspection */
                if (in_array($absolutePrefix . "/" . $v['alias'], $recursive, true)) {
                    //$colLocal = substr($kalias, 1); // remove the first "_"
                    $colLocalDB = substr($colDB, 1);
                    /** var TableParentRepo $class */
                    $class = $ns . static::RELATIONS[$v['reftable']];
                    $aliasTableQuoted = static::addQuote($aliasTable);
                    if ($vkey === 'ONETOONE') {
                        $table[] = sprintf("left join %s as %s on %s%s=%s.%s"
                            , $v['reftable'], $aliasTableQuoted
                            , $aliasTableParentDot, $v['col']
                            , $aliasTableQuoted, $v['refcol']);
                    } else {
                        // manytoone
                        $table[] = sprintf("left join %s as %s on %s%s=%s.%s"
                            , $v['reftable'], $aliasTableQuoted
                            , $aliasTableParentDot, $colLocalDB
                            , $aliasTableQuoted, $v['refcol']);
                    }
                    /** @noinspection PhpUndefinedMethodInspection */
                    [$cols2, $table2, $where2] = $class::plan($absolutePrefix . '/' . $v['alias'], $recursive, $prefix . '.' . $colDB, $aliasTable, static::TABLE);
                    array_push($cols, ...$cols2);
                    foreach ($where2 as $kwhere => $vwhere) {
                        $where[$kwhere] = $vwhere;
                    }
                    //array_push($where, ...$where2);
                    array_push($table, ...$table2);
                }
            }
            if (($vkey === 'ONETOMANY' || $vkey === 'MANYTOMANY') && $v['reftable'] !== $noTableBack) {
                // if(in_array($absolutePrefix."/".$v['alias'],$recursive)) {
                //$cols[]=$v;
                //$where[$aliasTable]=$v;
                //$where[$aliasTable]=[explode('/',$aliasTable),$v];
                $where[$aliasTable] = [$aliasTableParent, $v];
                // }
            }
        }
        return [$cols, $table, $where];
    }

    /**
     * The result is stored on static::$gQuery
     *
     * @param        $newQuery
     * @param string $pTable prefix table (usually table.)
     * @param string $pColumn
     * @param string $recursiveInit
     * @param bool   $new
     */
    protected static function generationRecursive(
        &$newQuery,
        string $pTable = '',
        string $pColumn = '',
        string $recursiveInit = '',
        bool $new = false
    ): void
    {
        $cols = static::getDefName(false);
        $colAlias = static::COL2ALIAS;
        $keyRels = static::DEFFK;
        //$newQuery=[];
        $pt = $pTable === '' ? static::TABLE . '.' : $pTable;
        // add columns of the current table
        foreach ($cols as $col) {
            $newQuery['columns'][] = $pt . $col . ' as ' . static::addQuote($pColumn . $colAlias[$col]);
        }
        $ns = self::getNamespace();
        $pdoQuery = self::getQuery();
        foreach ($keyRels as $nameCol => $keyRel) {
            $type = $keyRel['key'];
            if ($type !== 'FOREIGN KEY') {
                // $nameColClean = trim($nameCol, PdoOne::$prefixBase);
                $recursiveComplete = ltrim($recursiveInit . '/' . $nameCol, '/');
                if ($pdoQuery->hasRecursive($recursiveComplete)) {
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
                                . "on $pt$col=$tableRelAlias.$refCol \n"; // $recursiveInit$nameCol\n"; // adds a query to the current query
                            /** @noinspection PhpUndefinedMethodInspection */
                            /** @see _BasePdoOneRepo::generationRecursive */
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
                                . "on $pt$col=$tableRelAlias.$refCol \n"; // $recursiveInit$nameCol\n"; // adds a query to the current query
                            /** @noinspection PhpUndefinedMethodInspection */
                            /** @see _BasePdoOneRepo::generationRecursive */
                            $class::generationRecursive($newQuery, $tableRelAlias . '.', $colRelAlias . '.',
                                $recursiveComplete, false); // $recursiveInit . $nameCol
                            break;
                        case 'ONETOMANY':
                            //$tableRelAlias = ''; //'t' . static::$gQueryCounter;
                            $other = [];
                            $other['type'] = 'ONETOMANY';
                            self::generationRecursiveHelp($keyRel, $other, $pColumn, $nameCol);
                            //static::$gQuery[]=$other;
                            $class = $ns
                                . static::RELATIONS[$keyRel['reftable']]; // $ns . PdoOne::camelize($keyRel['reftable']) . $postfix;
                            /** @noinspection PhpUndefinedMethodInspection */
                            /** @see _BasePdoOneRepo::generationRecursive */
                            $class::generationRecursive($other, '', '', $pColumn . $recursiveComplete,
                                false); //$recursiveInit . $nameCol
                            static::$gQuery[] = $other;
                            break;
                        case 'MANYTOMANY':
                            $rec = $pdoQuery->getRecursive();
                            // automatically we add recursive.
                            $rec[] = $recursiveComplete . $keyRel['refcol2']; // $recursiveInit . $nameCol
                            $pdoQuery->_recursive($rec);
                            //$tableRelAlias = ''; //'t' . static::$gQueryCounter;
                            $other = [];
                            $other['type'] = 'MANYTOMANY'; // 2021: ONETOMANY   MANYTOMANY
                            self::generationRecursiveHelp($keyRel, $other, $pColumn, $nameCol);
                            $class = $ns . static::RELATIONS[$keyRel['reftable']]; // $ns . PdoOne::camelize($keyRel['reftable']) . $postfix;
                            /** @noinspection PhpUndefinedMethodInspection */
                            /** @see _BasePdoOneRepo::generationRecursive */
                            $class::generationRecursive($other, '', '', $pColumn . $recursiveComplete,
                                false); //$recursiveInit . $nameCol
                            // we reduce a level
                            //$columns = $other['columns'];
                            $columnFinal = [];
                            $findme = ltrim($keyRel['refcol2'], PdoOne::$prefixBase);
                            $columnFinal[] = $findme . ' as ' . static::addQuote($pColumn . $findme);
                            $other['columns'] = $columnFinal;
                            static::$gQuery[] = $other;
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
            static::$gQuery[] = $newQuery;
        }
    }

    protected static function generationRecursiveHelp($keyRel, &$other, $pColumn, $nameCol): void
    {
        $other['table'] = $keyRel['reftable'];
        $other['where'] = trim($keyRel['refcol'], PdoOne::$prefixBase);
        $other['joins'] = " {$keyRel['reftable']} \n";
        //$tableRelAlias = '*2';
        $other['col'] = $pColumn . $keyRel['col']; //***
        $other['col2'] = $pColumn . $nameCol;
        $other['name'] = $nameCol;
        $other['data'] = $keyRel;
    }


    /**
     * This method validates a model before it is inserted/updated into the database.
     *
     * @param object|array $model     It could be one model or multiple models.
     * @param boolean      $multiple  if true then it validates multiples models at once.
     * @param array        $recursive =static::factoryUtil()
     * @return bool if true then the model is valid, otherwise it's false.
     */
    public static function validateModel($model, bool $multiple = false, array $recursive = []): bool
    {
        if ($multiple) {
            if ($model === null || count($model) === 0) {
                return true;
            }
            $array = $model;
        } else {
            $array[0] = $model;
        }
        $defs = static::DEF;
        $fks = static::DEFFK;
        foreach ($array as $mod) {
            if (is_object($mod)) {
                $mod = (array)$mod;
            }
            foreach ($defs as $col => $def) {
                $curCol = $mod[$col] ?? null;
                // if null (or missing) and it is allowed = true
                // if null (or missing) and not null, and it is not identity = false (identities are generated)
                if (($curCol === null) && !($def['null'] === false && $def['identity'] === false)) {
                    static::getPdoOne()->errorText = "field $col must not be null";
                    return false;
                }
                switch ($def['phptype']) {
                    case 'binary':
                    case 'string':
                        if (!is_string($curCol)) {
                            // not a string
                            static::getPdoOne()->errorText = "field $col is not a string";
                            return false;
                        }
                        break;
                    case 'float':
                        if (!is_float($curCol)) {
                            static::getPdoOne()->errorText = "field $col is not a float";
                            return false;
                        }
                        break;
                    case 'timestamp':
                    case 'int':
                        if (!is_int($curCol)) {
                            static::getPdoOne()->errorText = "field $col is not a int";
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
                            static::getPdoOne()->errorText = "field $col is not a proper date";
                            return false;
                        }
                }
            }
            if (count($recursive) > 0) {
                $ns = self::getNamespace();
                foreach ($fks as $key => $fk) {
                    if (array_key_exists($key, $mod) && self::getQuery()->hasRecursive($key, $recursive)) {
                        $curFK = $fk['key'];
                        $class = $ns . static::RELATIONS[$fk['reftable']];
                        switch ($curFK) {
                            case 'ONETOMANY':
                            case 'MANYTOMANY':
                                /** @noinspection PhpUndefinedMethodInspection */
                                $r = $class::validateModel($mod[$key], true, $recursive);
                                break;
                            case 'MANYTOONE':
                            case 'ONETOONE':
                                /** @noinspection PhpUndefinedMethodInspection */
                                $r = $class::validateModel($mod[$key], false, $recursive);
                                break;
                            default:
                                $r = true;
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
     *
     * @param PdoOneQuery $query
     * @return mixed
     */
    public static function setPdoOneQuery(PdoOneQuery $query)
    {
        static::$pdoOneQuery = $query;
        return static::ME;
    }

    /**
     * It returns the values as a list of elements.
     *
     * @param array|int  $filter      (optional) if we want to filter the results.
     * @param array|null $filterValue (optional) the values of the filter
     * @return array|bool|null
     * @throws Exception
     */
    public static function toList($filter = PdoOne::NULL, ?array $filterValue = null)
    {
        if (static::$useModel) {
            return (static::ME)::fromArrayMultiple(self::_toList($filter, $filterValue));
        }
        $newQuery = self::getQuery();
        $newQuery->ormClass = null;
        /** @see _BasePdoOneRepo::executePlan0 */
        return self::executePlan0($newQuery);
        //return self::_toList($filter, $filterValue);
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
        if (static::$useModel) {
            return (static::ME)::fromArrayMultiple(self::_toList($filter, $filterValue));
        }
        $newQuery = self::getQuery();
        $newQuery->ormClass = null;
        $newQuery->where($filter, $filterValue);
        /** @see _BasePdoOneRepo::executePlan0 */
        return self::executePlan0($newQuery);
    }


    /**
     * @param array $rows The associative arrays with values to convert.
     * @param bool  $list false for a single row, or true for a list of rows.
     * @noinspection PhpUnused
     */
    protected static function convertSQLValueInit(array &$rows, bool $list = false): void
    {
        if (!$list) {
            $rows = [$rows];
        }
        //$defs = static::getDef('conversion');
        $ns = self::getNamespace();
        $rels = static::DEFFK;
        $alias = static::COL2ALIAS;
        foreach ($rows as &$row) {
            //self::_convertOutputValue($row, $defs);
            (static::ME)::convertOutputVal($row);
            foreach ($rels as $k => $v) {
                if (isset($row[$k])) {
                    $colName = $alias[$row[$k]] ?? $row[$k];
                    switch ($v['key']) {
                        // PARENT not because parent is a fk but is used for a one way relation.
                        case 'MANYTOONE':
                            $class = $ns . static::RELATIONS[$v['reftable']];
                            /** @noinspection PhpUndefinedMethodInspection */
                            /** @see _BasePdoOneRepo::convertSQLValueInit */
                            $class::convertSQLValueInit($colName, false);
                            break;
                        case 'ONETOMANY':
                            $class = $ns . static::RELATIONS[$v['reftable']];
                            /** @noinspection PhpUndefinedMethodInspection */
                            /** @see _BasePdoOneRepo::convertSQLValueInit */
                            $class::convertSQLValueInit($colName, true);
                            break;
                        case 'MANYTOMANY':
                            $class = $ns . static::RELATIONS[$v['table2']];
                            /** @noinspection PhpUndefinedMethodInspection */
                            /** @see _BasePdoOneRepo::convertSQLValueInit */
                            $class::convertSQLValueInit($colName, true);
                            break;
                    }
                }
            }
        }
        unset($row);
        if (!$list) {
            $rows = $rows[0];
        }
    }

    /**
     * It returns the first row of a query.
     * @param array|mixed|null $pk [optional] Specify the value of the primary key.
     *
     * @return array|bool It returns false if not file is found.
     * @throws Exception
     */
    public static function first($pk = PdoOne::NULL, ?PdoOneQuery $query = null)
    {
        if (static::$useModel) {
            /** @noinspection PhpUndefinedMethodInspection */
            return static::fromArray(self::_first($pk, $query));
        }
        return self::_first($pk, $query);
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
     * @return array|bool static::factoryUtil()
     * @throws Exception
     */
    protected static function _first($pk = PdoOne::NULL, PdoOneQuery $query = null)
    {
        if ($pk !== PdoOne::NULL) {
            $tmp = is_array($pk) ? $pk : [static::PK[0] => $pk];
            $pt = static::TABLE . '.';
            $pk = [];
            foreach ($tmp as $k => $v) {
                $pk[$pt . $k] = $v;
            }
        }
        $query = $query ?? self::getQuery();
        $query->ormClass = null;
        $query->where($pk);
        /** @see _BasePdoOneRepo::executePlan0 */
        return self::executePlan0($query, null, true);
    }

    /**
     * It converts an associative array with columns (alias) to database columns<br>
     * If iswhere and aliasrows does not contain '/' or '.', then is converted to aliascolumn  => table.dbcolumn
     *
     * @param array $aliasRows
     * @param bool  $isWhere (false by default). if true, then it convers the column considering the input is used
     *                       in a "where".
     * @return array
     */
    public static function convertAliasToDB(array $aliasRows, bool $isWhere = false): array
    {
        $db = [];
        $aliasCol = static::ALIAS2COL;
        foreach ($aliasRows as $keyAlias => $value) {
            if (strpos($keyAlias, '/') !== false) {
                // /_relation/column
                $key = static::transformAliasTosql($keyAlias);
                $db[$key] = $value;
            } else if (strpos($keyAlias, '.') === false) {
                // alias=>real column
                if (strpos($keyAlias, PdoOne::$prefixBase) === 0) {
                    // _column
                    $findKey = false;
                    foreach (static::DEFFK as $key => $val) {
                        if ($val['alias'] === $keyAlias && $val['key'] !== 'FOREIGN KEY') {
                            $findKey = $key;
                            break;
                        }
                    }
                    // $findKey = isset(static::DEFFK[$keyAlias]) ? $keyAlias : false;
                } else {
                    // column
                    $findKey = $aliasCol[$keyAlias] ?? false;
                    if ($isWhere && $findKey !== false) {
                        $findKey = static::addQuote(static::TABLE) . '.' . $findKey;
                    }
                }
                if ($findKey !== false) {
                    $db[$findKey] = $value;
                } else {
                    throw new RuntimeException("Column alias not found [" . static::TABLE . "::$keyAlias] ");
                }
            }
        }
        return $db;
    }

    public static function transformAliasTosql($alias): string
    {
        $parts = explode('/', $alias, 3);
        $ns = self::getNamespace();
        $first = static::DEFFK[$parts[1]] ?? null;
        if ($first === null) {
            throw new RuntimeException("Column [$parts[1]] not found as [$alias]");
        }
        /** var CityRepo $class this comment is only used for autocomplete */
        $class = $ns . static::RELATIONS[$first['reftable']];
        $aliasCol = $class::ALIAS2COL;
        // alias => real column
        $second = $aliasCol[$parts[2]] ?? $parts[2]; // if alias not found then it keeps the same name of column.
        return static::addQuote('/' . $parts[1]) . '.' . static::addQuote($second);
    }

    /**
     * It gets the recursivity of the current query
     *
     * @return array
     */
    protected static function getRecursive(): array
    {
        return self::getQuery()->getRecursive();
    }

    /**
     * @throws Exception
     */
    protected static function _merge($entity, $transaction = true, bool $newQuery = false)
    {
        if (static::_exist($entity)) {
            return static::_update($entity, $transaction, $newQuery);
        }
        return static::_insert($entity, $transaction, $newQuery);
    }

    protected static function _exist($entityAlias): bool
    {
        try {
            $pks = static::PK;
            if (is_object($entityAlias)) {
                $entityAlias = static::objectToArray($entityAlias);
            }
            if (!is_array($pks) || count($pks) === 0) {
                static::getPdoOne()
                    ->throwError('exist: entity not specified as an array or the table lacks a PK', json_encode($entityAlias));
                return false;
            }
            if (is_array($entityAlias)) {
                // we only keep the fields that are primary keys
                $tmp = [];
                foreach ($pks as $pk) {
                    $tmp[$pk] = $entityAlias[static::COL2ALIAS[$pk]] ?? null;
                }
                $entityDBWhere = $tmp;
            } else {
                $entityDBWhere = [$pks[0] => $entityAlias];
            }
            $r = static::getPdoOne()
                ->genError()
                ->select('1')
                ->from(static::TABLE, static::$schema)
                ->where($entityDBWhere)
                ->firstScalar();
            static::getPdoOne()->genError(true);
            self::reset();
            /** @noinspection TypeUnsafeComparisonInspection */
            return ($r == '1');
        } catch (Exception $exception) {
            self::reset();
            if (static::$falseOnError) {
                static::$lastException = $exception->getMessage();
                return false;
            }
            static::getPdoOne()
                ->throwError('', json_encode($entityAlias), '', true, $exception);
        }
        self::reset();
        return false;
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
     * Update a registry
     *
     * @param array|object $entityAlias =static::factoryUtil()
     *
     * @param bool         $transaction
     * @param bool         $newQuery
     * @return false|int
     * @throws Exception
     */
    protected static function _update($entityAlias, bool $transaction = true, bool $newQuery = false)
    {
        if ($entityAlias === null) {
            throw new RuntimeException('unable to update an empty entity');
        }
        try {
            if (is_object($entityAlias)) {
                $entityAlias = static::objectToArray($entityAlias);
            }
            $pdoOnequery = $newQuery === true ? new PdoOneQuery(static::getPdoOne(), static::class) : self::getQuery();
            //$defTable = static::getDef('conversion');
            $entityDB = (static::ME)::convertAliasToDB((static::ME)::convertInputVal($entityAlias));
            self::invalidateCache();
            // only the fields that are defined are inserted
            $entityCopy = self::intersectArraysNotNull($entityDB, static::getDefName());
            //$entityCopy = self::diffArrays($entityCopy, array_merge(array_keys(static::getDefKey()), static::getDefNoUpdate())); // columns discarded
            $noUpdates = static::DEFNOUPDATE;
            /*foreach ($noUpdates as $k => $v) {
                $noUpdates[$k] = static::COL2ALIAS[$v];
            }*/
            $entityCopy = self::diffArrays($entityCopy, $noUpdates); // columns discarded
            if ($pdoOnequery->parent->transactionOpen === true) {
                // we disable transaction to avoid nested transactions.
                // mysql does not allow nested transactions
                // sql server allows nested transaction but afaik, it only counts the outer one.
                $transaction = false;
            }
            if ($transaction) {
                $pdoOnequery->parent->startTransaction();
            }
            $recursiveBackup = $pdoOnequery->getRecursive();
            $r = static::getPdoOne()
                ->from(static::TABLE, static::$schema)
                ->set($entityCopy)
                ->where(static::intersectArrays($entityDB, static::PK))
                ->update();
            $pdoOnequery->_recursive($recursiveBackup); // update() delete the value of recursive
            $defs = static::DEFFK;
            $ns = self::getNamespace();
            //$pk=static::COL2ALIAS[static::PK[0]];
            $pk = static::PK[0];
            if (!isset($entityDB[$pk])) {
                $pkalias = @static::COL2ALIAS[static::PK[0]];
                throw new RuntimeException("Update: Primary key [$pkalias] not set");
            }
            self::recursiveDMLManyToOne('update', $entityAlias, $defs, $pdoOnequery, $recursiveBackup, $ns, $entityDB);
            self::recursiveDMLOxMMxM('update', $entityAlias, $defs, $pdoOnequery, $recursiveBackup, $ns, $entityDB[$pk]);
            if ($transaction) {
                static::getPdoOne()->commit();
            }
            return $r;
        } catch (Exception $exception) {
            if ($transaction) {
                static::getPdoOne()->rollback(false);
            }
            self::reset();
            if (static::$falseOnError) {
                static::$lastException = $exception->getMessage();
                return false;
            }
            throw $exception;
        }
    }

    /**
     * @param string      $type =['update','insert','delete'][$i]
     * @param array       $entityAlias
     * @param array       $defs
     * @param PdoOneQuery $query
     * @param array       $recursiveBack
     * @param string      $ns
     * @param array       $entityCopy
     * @return void
     */
    protected static function recursiveDMLManyToOne(string $type, array $entityAlias, array $defs, PdoOneQuery $query, array $recursiveBack, string $ns, array &$entityCopy): void
    {
        foreach ($defs as $col => $def) { // ['/tablaparentxcategory']=['columnDB'=>...]
            // update/insert/delete recursively.
            $columnAlias = $def['alias'];
            if ($def['key'] === 'MANYTOONE' && isset($entityAlias[$columnAlias]) && $entityAlias[$columnAlias] !== []
                && $query->hasRecursive('/' . $columnAlias, $recursiveBack)) {
                $classMO = $ns . static::RELATIONS[$def['reftable']];
                // if it is an insert, then it must be done before the insertion (cause, fk).
                switch ($type) {
                    case 'insert':
                    case 'update':
                        //  $classMO::_merge($entityAlias[$columnAlias], false, true);
                        if (static::_exist($entityAlias[$columnAlias])) {
                            /**
                             * @noinspection PhpUndefinedMethodInspection
                             * @see          _BasePdoOneRepo::_update
                             */
                            $classMO::_update($entityAlias[$columnAlias], false, true);
                        } else {
                            /**
                             * @noinspection PhpUndefinedMethodInspection
                             * @see          _BasePdoOneRepo::_insert
                             */
                            $objectInserted = $classMO::_insert($entityAlias[$columnAlias], false, true);
                            $colRightAlias = static::ALIAS2COL[ltrim($col, PdoOne::$prefixBase)];
                            if (is_array($objectInserted)) {
                                $entityCopy[$colRightAlias] = $objectInserted[$defs['refcolalias']];
                            } else {
                                $entityCopy[$colRightAlias] = $objectInserted;
                            }
                            // $entityCopy[$columnAlias]=$objectInserted;
                        }
                        break;
                    case 'delete':
                        /** @noinspection PhpUndefinedMethodInspection
                         * @see          _BasePdoOneRepo::_delete
                         */
                        $classMO::_delete($entityAlias[$columnAlias], false, true);
                        break;
                }
            }
        }
    }

    /**
     * @throws Exception
     * @noinspection TypeUnsafeArraySearchInspection
     */
    protected static function recursiveDMLOxMMxM(string $type, array $entityAlias, array $defs, PdoOneQuery $query, array $recursiveBack, string $ns, string $fatherPK): void
    {
        foreach ($defs as $def) { // ['/tablaparentxcategory']=['columnAlias'=>...]
            $columnAlias = $def['alias'];
            $hasRecursive = $query->hasRecursive('/' . $columnAlias, $recursiveBack) ? 'simple' : 'no';
            if ($hasRecursive === 'no') {
                $hasRecursive = $query->hasRecursive('/' . $columnAlias . '*', $recursiveBack) ? 'multiple' : 'no';
            }
            if ($hasRecursive !== 'no') {
                switch ($def['key']) {
                    case 'ONETOMANY':
                        if ($type === 'insert' || $type === 'update') {
                            if (!isset($entityAlias[$columnAlias]) || !is_array($entityAlias[$columnAlias])) {
                                $newRows = [];
                            } else {
                                $newRows = $entityAlias[$columnAlias];
                            }
                            $classRef = $ns
                                . static::RELATIONS[$def['reftable']]; // $ns . PdoOne::camelize($def['reftable']) . $postfix;
                            $col1 = static::COL2ALIAS[ltrim($def['col'], PdoOne::$prefixBase)];
                            $refcol = $classRef::COL2ALIAS[ltrim($def['refcol'], PdoOne::$prefixBase)]; // it is how they are joined
                            $refpk = $classRef::COL2ALIAS[$classRef::PK[0]];
                            $newRowsKeys = [];
                            foreach ($newRows as $v) {
                                $newRowsKeys[] = $v[$refpk];
                            }
                            //self::_recursive([$def['refcol2']]);
                            self::_recursive([]);
                            /** @noinspection PhpUndefinedMethodInspection */
                            $oldRowsAlias = ($classRef::where($refcol, $entityAlias[$col1]))->toList();
                            $oldRowsKeys = [];
                            foreach ($oldRowsAlias as $v) {
                                $oldRowsKeys[] = $v[$refpk];
                            }
                            $insertKeys = array_diff($newRowsKeys, $oldRowsKeys);
                            $deleteKeys = array_diff($oldRowsKeys, $newRowsKeys);
                            // inserting a new value
                            foreach ($newRows as $item) {
                                if (in_array($item[$refpk], $insertKeys)) {
                                    $item[$refcol] = $fatherPK;
                                    /**
                                     * @noinspection PhpUndefinedMethodInspection
                                     * @see          _BasePdoOneRepo::_insert
                                     */
                                    $classRef::_insert($item, false, true);
                                } elseif (!in_array($item[$refpk], $deleteKeys)) {
                                    /** @noinspection PhpUndefinedMethodInspection */
                                    $classRef::update($item, false);
                                }
                            }
                            foreach ($deleteKeys as $key2) {
                                /** @noinspection PhpUndefinedMethodInspection */
                                $classRef::deleteById($key2, false);
                            }
                        } else {
                            $classRef = $ns
                                . static::RELATIONS[$def['reftable']]; // $ns . PdoOne::camelize($def['reftable']) . $postfix;
                            $refcol = $classRef::COL2ALIAS[ltrim($def['refcol'], PdoOne::$prefixBase)]; // it is how they are joined
                            $col = $def['colalias'];
                            self::_recursive([]);
                            $query->from($classRef::TABLE)->where([$refcol => $entityAlias[$col]])->delete();
                        }
                        break;
                    case 'MANYTOMANY':
                        //hasRecursive($recursiveInit . $columnAlias)
                        if (!isset($entityAlias[$columnAlias]) || !is_array($entityAlias[$columnAlias])) {
                            $newRows = [];
                        } else {
                            $newRows = $entityAlias[$columnAlias];
                        }
                        $classRef = $ns
                            . static::RELATIONS[$def['reftable']]; // $ns . PdoOne::camelize($def['reftable']) . $postfix;
                        $class2 = $ns
                            . static::RELATIONS[$def['table2']]; // $ns . PdoOne::camelize($def['table2']) . $postfix;
                        $col1 = $def['colalias'];
                        $refcol = $def['refcolalias'];
                        $col2 = $def['col2alias'];
                        $refcol2alias = $def['refcol2alias'];
                        $newRowsKeys = [];
                        foreach ($newRows as $v) {
                            $newRowsKeys[] = $v[$col2];
                        }
                        //self::_recursive([$def['refcol2']]);
                        //self::_recursive([]);
                        /** @noinspection PhpUndefinedMethodInspection */
                        $oldRowsAlias = ($classRef::where($classRef::ALIAS2COL[$refcol]
                            , $entityAlias[$col1]))->toList();
                        $oldRowsKeys = [];
                        foreach ($oldRowsAlias as $v) {
                            $oldRowsKeys[] = $v[$refcol2alias];
                        }
                        $insertKeys = array_diff($newRowsKeys, $oldRowsKeys);
                        $deleteKeys = array_diff($oldRowsKeys, $newRowsKeys);
                        // inserting a new value
                        foreach ($newRows as $item) {
                            if (in_array($item[$col2], $insertKeys)) {
                                $pk2 = $item[$col2];
                                //if (static::getPdoOne()->recursive($columnAlias, $recursiveBack)) {
                                if ($hasRecursive === 'multiple') {
                                    /** @noinspection PhpUndefinedMethodInspection */
                                    /** @see _BasePdoOneRepo::_first */
                                    $oldItem = $class2::_first($pk2);
                                    if (is_array($oldItem) && self::compareEntity($oldItem, $item) === false) {
                                        /**
                                         * @noinspection PhpUndefinedMethodInspection
                                         * @see          _BasePdoOneRepo::_insert
                                         */
                                        $pk2 = $class2::_insert($item, false, true);
                                    } else {
                                        /** @noinspection PhpUndefinedMethodInspection */
                                        $class2::update($item, false);
                                    }
                                }
                                //}
                                $relationalObjInsert = [$refcol => $fatherPK, $refcol2alias => $pk2];
                                /**
                                 * @noinspection PhpUndefinedMethodInspection
                                 * @see          _BasePdoOneRepo::_insert
                                 */
                                $classRef::_insert($relationalObjInsert, false, true);
                            }
                        }
                        // delete
                        foreach ($oldRowsAlias as $item) {
                            if (in_array($item[$refcol2alias], $deleteKeys)) {
                                $pk2 = $item[$refcol2alias];
                                if ($query->hasRecursive($columnAlias, $recursiveBack)) {
                                    /** @noinspection PhpUndefinedMethodInspection */
                                    $class2::_deleteById($pk2);
                                }
                                //$relationalObjDelete = [$refcol => $entityAlias[$def['col']], $refcol2 => $pk2];
                                //$relationalObjInsert = [$def['refcolalias'] => $fatherPK, $def['refcol2alias'] => $pk2];
                                /** @noinspection PhpUndefinedMethodInspection */
                                $classRef::_deleteById($item, false);
                            }
                        }
                        break;
                }
            }
        }
    }

    /**
     * Compare both entities<br>
     * <ul>
     * <li>If entityMain has more fields as entitySecond then it returns false</li>
     * <li>If entityMain has different values as entitySecond then it returns false</li>
     * <li>Otherwise, it returns true. If entitySecond has more fields, then those fields are ignored</li>
     * </ul>
     * @param array $entityMain
     * @param array $entitySecond
     * @return bool
     */
    public static function compareEntity(array $entityMain, array $entitySecond): bool
    {
        $r = true;
        foreach ($entityMain as $k => $v) {
            if (!isset($entitySecond[$k])) {
                $r = false;
                break;
            }
            if ($entitySecond[$k] !== $v) {
                $r = false;
                break;
            }
        }
        return $r;
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
     * @return PdoOneQuery
     */
    public static function invalidateCache(string $family = ''): PdoOneQuery
    {
        if (static::getPdoOne()->getCacheService() !== null) {
            if (!$family) {
                static::getPdoOne()->getCacheService()->invalidateCache('', self::getRecursiveClass());
            } else {
                static::getPdoOne()->invalidateCache('', $family);
            }
        }
        return self::getQuery();
    }

    /**
     * It filters an associative array<br>
     * <b>Example:</b><br>
     * <pre>
     * self::intersectArraysNotNull(['a1'=>1,'a2'=>2],['a1','a3']); // ['a1'=>1]
     * </pre>
     *
     * @param array $arrayValues An associative array with key as the column
     * @param array $arrayIndex  An indexed array with the name of the columns
     *
     * @return array
     * @noinspection TypeUnsafeArraySearchInspection
     */
    public static function intersectArraysNotNull(array $arrayValues, array $arrayIndex): array
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
     * @noinspection TypeUnsafeArraySearchInspection
     */
    public static function diffArrays($arrayValues, $arrayIndex, bool $indexIsKey = false): array
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
     * @param array $arrayValues  An associative array with the keys and values.
     * @param array $arrayIndex   A string array with the indexes (if indexisKey=false then index is the value)
     * @param bool  $indexIsKey   (default false) if true then the index of $arrayIndex is considered as key
     *                            otherwise, the value of $arrayIndex is considered as key.
     *
     * @return array
     */
    public static function intersectArrays(array $arrayValues, array $arrayIndex, bool $indexIsKey = false): array
    {
        $result = [];
        foreach ($arrayIndex as $k => $v) {
            if ($indexIsKey) {
                $result[$k] = $arrayValues[$k] ?? null;
            } else {
                $result[$v] = $arrayValues[$v] ?? null;
            }
        }
        return $result;
    }

    /**
     * It sets the recursivity of the current query to read/insert/update the information.<br>
     * The fields recursives are marked with the prefix '/'.  For example 'customer' is a single field (column), while
     * '/customer' is a relation. Usually, a relation has both fields and relation.
     * - If the relation is manytoone, then the query is joined with the table indicated in the relation. Example:<br>
     * <pre>
     * ProductRepo::recursive(['/Category'])::toList(); // select ... from Producto inner join Category on ...
     * </pre>
     * - If the relation is onetomany, then it creates an extra query (or queries) with the corresponding values.
     * Example:<br>
     * <pre>
     * CategoryRepo::recursive(['/Product'])::toList(); // select ... from Category and select from Product where...
     * </pre>
     * - If the reation is onetoone, then it is considered as a manytoone, but it returns a single value. Example:<br>
     * <pre>
     * ProductRepo::recursive(['/ProductExtension'])::toList(); // select ... from Product inner join
     * ProductExtension
     * </pre>
     * - If the relation is manytomany, then the system load the relational table (always, not matter the recursivity),
     * and it reads/insert/update the next values only if the value is marked as recursive. Example:<br>
     * <pre>
     * ProductRepo::recursive(['/product_x_category'])::toList(); // it returns porduct, productxcategory and
     * category ProductRepo::recursive([])->toList(); // it returns porduct and productxcategory (if
     * /productcategory is marked as manytomany)
     * </pre>
     *
     *
     * @param array $recursive An indexed array with the recursivity.
     *
     * @return PdoOneQuery
     * @see static::DEFFK for where to define the relation.
     */
    protected static function _recursive(array $recursive): PdoOneQuery
    {
        return self::newQuery()->_recursive($recursive);
    }

    /**
     * Insert a new row
     *
     * @param array|object $entityAlias =static::factoryUtil()
     * @param bool         $transaction if true, then it is transactional
     * @param bool         $newQuery    if true, then it creates a new pipeline query
     * @return mixed       false if the operation failed<br>
     *                                  otherwise, we return the entity modified<br>
     * @throws Exception
     */
    protected static function _insert(&$entityAlias, bool $transaction = true, bool $newQuery = false)
    {
        $returnObject = false;
        if ($entityAlias === null) {
            throw new RuntimeException('Unable to insert an empty entity');
        }
        $pdoOneQuery = $newQuery === true ? new PdoOneQuery(static::getPdoOne(), static::class) : self::getQuery();
        try {
            $entityAlias = (static::ME)::convertInputVal($entityAlias);
            //$defTable = static::getDef('conversion');
            //self::_convertInputValue($entity, $defTable);
            if (is_object($entityAlias)) {
                $returnObject = clone $entityAlias;
                $entityAlias = static::objectToArray($entityAlias);
            }
            //$entityDB = (static::ME)::convertInputVal($entityAlias);
            self::invalidateCache();
            $recursiveBackup = self::getQuery()->getRecursive();  // recursive is deleted by insertObject
            // only the fields that are defined are inserted
            $aliasColumns = static::COL2ALIAS;
            $entityCopy = self::intersectArraysNotNull($entityAlias, $aliasColumns);
            $entityCopy = self::diffArrays($entityCopy, static::DEFNOINSERT); // discard some columns
            if (count($entityCopy) === 0) {
                static::getPdoOne()
                    ->throwError('insert:[' . static::TABLE . '] insert without fields or fields incorrects. Please check the syntax' .
                        ' and case of the fields', $entityCopy);
                return false;
            }
            if ($pdoOneQuery->parent->transactionOpen === true) {
                // we disable transaction to avoid nested transactions.
                // mysql does not allow nested transactions
                // sql server allows nested transaction but afaik, it only counts the outer one.
                $transaction = false;
            }
            if ($transaction) {
                $pdoOneQuery->parent->startTransaction();
            }
            $defs = static::DEFFK;
            $ns = self::getNamespace();
            $identityDB = static::IDENTITY;
            $identityAlias = $identityDB === null ? null : (static::COL2ALIAS[$identityDB]);
            if ($identityAlias !== null) {
                // we don't insert identities.
                unset($entityCopy[$identityAlias]);
            }
            /*foreach ($defs as $key => $def) {
                if ($pdoOneQuery->hasRecursive('/' . $key, $recursiveBackup)) {
                    switch ($def['key']) {
                        case 'MANYTOONE':
                            if (isset($entityAlias[$key])
                                && $entityAlias[$key] !== []) {
                                $classMO = $ns . static::RELATIONS[$def['reftable']];
                            }
                    }
                }
            }*/
            self::recursiveDMLManyToOne('insert', $entityAlias, $defs, $pdoOneQuery, $recursiveBackup, $ns, $entityCopy);
            $insert = $pdoOneQuery->insertObject(static::TABLE, $entityCopy, $identityDB === null ? [] : [$identityDB], static::PK);
            // obtain the identity if any
            if ($identityDB !== null) {
                $pks = static::COL2ALIAS[$identityDB];
                // we update the identity of $entity ($entityCopy is already updated).
                if ($returnObject !== false) {
                    $returnObject->$pks = $insert;
                } else {
                    $entityCopy[$pks] = $insert;
                }
            } else {
                // no identity, so we obtain the first primary key.
                $pks = static::COL2ALIAS[static::PK[0]];
                $insert = $returnObject !== false ? $returnObject->$pks : $entityCopy[$pks];
            }
            $entityAlias[$pks] = $insert;
            self::recursiveDMLOxMMxM('insert', $entityAlias, $defs, $pdoOneQuery, $recursiveBackup, $ns, $entityAlias[$pks]);
            if ($transaction) {
                $pdoOneQuery->parent->commit();
            }
            $entityAlias = $entityCopy;
            if ($returnObject !== false) {
                return $entityAlias;
            }
            return $insert;
        } catch (Exception $exception) {
            if ($transaction) {
                $pdoOneQuery->parent->rollback();
            }
            self::reset();
            if (static::$falseOnError) {
                static::$lastException = $exception->getMessage();
                if ($returnObject !== false) {
                    return $entityAlias;
                }
                return false;
            }
            if ($returnObject !== false) {
                return $entityAlias;
            }
            throw $exception;
        }
    }

    /**
     * It deletes a registry by its id (primary key)
     *
     * @param mixed|array $pks
     *
     * @param bool        $transaction
     *
     * @return false|int
     * @throws Exception
     */
    protected static function _deleteById($pks, bool $transaction = true)
    {
        if (!is_array($pks)) {
            $pksI = [];
            $pksI[static::COL2ALIAS[static::PK[0]]] = $pks; // we convert into an associative array
        } else {
            $pksI = $pks;
        }
        return self::_delete($pksI, $transaction, static::PK);
    }

    /**
     * It deletes a row or rows.
     *
     * @param array|object $entityAlias
     * @param bool         $transaction
     * @param array|null   $columns
     * @param bool         $newQuery
     * @return false|int
     * @throws Exception
     */
    protected static function _delete($entityAlias, bool $transaction = true, ?array $columns = null, bool $newQuery = false)
    {
        if ($entityAlias === null) {
            throw new RuntimeException('unable to delete an empty entity');
        }
        $columns = $columns ?? static::getDefName();
        try {
            $pdoOneQuery = $newQuery === true ? new PdoOneQuery(static::getPdoOne(), static::class) : self::getQuery();
            if (is_object($entityAlias)) {
                $entityAlias = static::objectToArray($entityAlias);
            }
            $entityDB = (static::ME)::convertAliasToDB((static::ME)::convertInputVal($entityAlias));
            $entityCopy = self::intersectArraysNotNull($entityDB, $columns);
            if ($entityCopy === []) {
                throw new RuntimeException('Delete without conditions');
            }
            self::invalidateCache();
            if ($pdoOneQuery->parent->transactionOpen === true) {
                // we disable transaction to avoid nested transactions.
                // mysql does not allow nested transactions
                // sql server allows nested transaction but afaik, it only counts the outer one.
                $transaction = false;
            }
            if ($transaction) {
                $pdoOneQuery->parent->startTransaction();
            }
            $defs = static::DEFFK;
            $ns = self::getNamespace();
            $recursiveBackup = self::getQuery()->getRecursive();
            $pk = static::PK[0];
            $dummy = [];
            self::recursiveDMLManyToOne('delete', $entityAlias, $defs, $pdoOneQuery, $recursiveBackup, $ns, $dummy);
            self::recursiveDMLOxMMxM('delete', $entityAlias, $defs, $pdoOneQuery, $recursiveBackup, $ns, $entityDB[$pk]);
            foreach ($defs as $key => $def) { // ['/tablaparentxcategory']=['key'=>...]
                if ($def['key'] === 'ONETOMANY' && $pdoOneQuery->hasRecursive($key, $recursiveBackup)) {
                    if (!isset($entityAlias[$key]) || !is_array($entityAlias[$key])) {
                        $newRows = [];
                    } else {
                        $newRows = $entityAlias[$key];
                    }
                    $classRef = $ns
                        . static::RELATIONS[$def['reftable']]; // $ns . PdoOne::camelize($def['reftable']) . $postfix;
                    //$col1 = ltrim($def['col'], PdoOne::$prefixBase);
                    //$refcol = ltrim($def['refcol'], PdoOne::$prefixBase); // it is how they are joined
                    //$refpk = $classRef::PK[0];
                    foreach ($newRows as $item) {
                        /** @noinspection PhpUndefinedMethodInspection */
                        $classRef::deleteById($item, false);
                    }
                }
                if ($def['key'] === 'MANYTOMANY' && isset($entityAlias[$key])
                    && is_array($entityAlias[$key])
                ) { //hasRecursive($recursiveInit . $key)
                    $classRef = $ns
                        . static::RELATIONS[$def['reftable']]; // $ns . PdoOne::camelize($def['reftable']) . $postfix;
                    $class2 = $ns
                        . static::RELATIONS[$def['table2']]; //$ns . PdoOne::camelize($def['table2']) . $postfix;
                    $col1 = ltrim($def['col'], PdoOne::$prefixBase);
                    $refcol = ltrim($def['refcol'], PdoOne::$prefixBase);
                    //$refcol2 = ltrim($def['refcol2'], PdoOne::$prefixBase);
                    $col2 = $def['col2'];
                    //self::_recursive([$def['refcol2']]);
                    self::_recursive([]);
                    $cols2 = [];
                    foreach ($entityAlias[$key] as $item) {
                        $cols2[] = $item[$col2];
                    }
                    $relationalObjDelete = [$refcol => $entityAlias[$col1]];
                    /** @noinspection PhpUndefinedMethodInspection */
                    $classRef::delete($relationalObjDelete, false);
                    if (self::getQuery()->hasRecursive($key, $recursiveBackup)) {
                        foreach ($cols2 as $c2) {
                            // $k = $v[$refcol2];
                            $object2Delete = [$col2 => $c2];
                            /** @noinspection PhpUndefinedMethodInspection */
                            $class2::delete($object2Delete, false);
                        }
                    }
                    self::_recursive($recursiveBackup);
                }
            }
            $r = static::getPdoOne()->delete(static::TABLE, $entityCopy);
            if ($transaction) {
                //static::getPdoOne()->rollback();
                static::getPdoOne()->commit();
            }
            self::reset();
            return $r;
        } catch (Exception $exception) {
            if ($transaction) {
                static::getPdoOne()->rollback();
            }
            self::reset();
            if (static::$falseOnError) {
                static::$lastException = $exception->getMessage();
                return false;
            }
            throw $exception;
        }
    }

    /**
     * @return bool
     * @noinspection PhpUnused
     */
    public static function deleteAll(): bool
    {
        static::getPdoOne()->runRawQuery('delete from ' . static::TABLE . ' where 1=1', []);
        self::reset();
        return true;
    }

    /**
     * It adds a "having" condition to the query pipeline. It could be stacked with many having()
     * @param array|string   $sql =static::factoryUtil()
     * @param null|array|int $param
     *
     * @return PdoOneQuery
     */
    public function having($sql, $param = PdoOne::NULL): PdoOneQuery
    {
        return self::getQuery()->having($sql, $param);
    }
}
