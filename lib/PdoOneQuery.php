<?php /** @noinspection UnknownInspectionInspection */
/** @noinspection DuplicatedCode */

/** @noinspection PhpUnused */

namespace eftec;

use Exception;
use PDO;
use PDOStatement;
use RuntimeException;

class PdoOneQuery
{
    //<editor-fold desc="query builder fields">
    /** @var PdoOne */
    public $parent;
    /** @var _BasePdoOneRepo */
    public $ormClass;
    /** @var array parameters for the having. [paramvar,value,type,size] */
    public $havingParamAssoc = [];
    public $whereCounter = 1;
    /**
     * @var null|int $ttl If <b>0</b> then the cache never expires.<br>
     *                         If <b>false</b> then we don't use cache.<br>
     *                         If <b>int</b> then it is the duration of the
     *     cache
     *                         (in seconds)
     */
    public $useCache = false;
    /** @var string|array [optional] It is the family or group of the cache */
    public $cacheFamily = '';
    /**
     * @var boolean $numericArgument if true the the arguments are numeric. Otherwise, it doesn't have arguments or
     *      they are named. This value is used for the method where() and having()
     */
    protected $numericArgument = false;
    protected $select = '';
    protected $limit = '';
    protected $order = '';
    /** @var bool if true then builderReset will not reset (unless it is force), if false then it will reset */
    protected $noReset = false;
    protected $uid;
    /** @var array */
    protected $where = [];
    /** @var array parameters for the set. [paramvar,value,type,size] */
    protected $setParamAssoc = [];
    /** @var array */
    protected $set = [];
    /** @var array */
    //private $whereParamValue = [];
    protected $from = '';
    protected $group = '';
    protected $recursive = [];
    /** @var array */
    protected $having = [];
    protected $distinct = '';
    /** @var array parameters for the where. [paramvar,value,type,size] */
    private $whereParamAssoc = [];

    //</editor-fold>

    //<editor-fold desc="Query Builder DQL functions" defaultstate="collapsed" >
    /**
     * @var bool
     */
    private $throwOnErrorB;

    /**
     * PdoOneQuery constructor.
     * @param PdoOne $parent
     * @param string $repo
     */
    public function __construct(PdoOne $parent, $repo = null)
    {
        $this->parent = $parent;
        $this->ormClass = $repo;
    }


    /**
     * It returns an array with the metadata of each columns (i.e. name, type,
     * size, etc.) or false if error.
     *
     * @param null|string $sql     If null then it uses the generation of query
     *                             (if any).<br> if string then get the
     *                             statement of the query
     *
     * @param array       $args
     *
     * @return array|bool
     * @throws Exception
     */
    public function toMeta($sql = null, $args = [])
    {
        $uid = false;
        if ($sql === null) {
            $this->beginTry();
            /** @var PDOStatement $stmt */
            $stmt = $this->runGen(false, PDO::FETCH_ASSOC, 'tometa', $this->parent->genError);
            if ($this->endtry() === false) {
                return false;
            }
        } else {
            if ($this->parent->useInternalCache) {
                $uid = hash($this->parent->encryption->hashType, 'meta:' . $sql . serialize($args));
                if (isset($this->internalCache[$uid])) {
                    // we have an internal cache, so we will return it.
                    $this->parent->internalCacheCounter++;
                    return $this->parent->internalCache[$uid];
                }
            }
            /** @var PDOStatement $stmt */
            $stmt = $this->parent->runRawQuery($sql, $args, false, $this->useCache, $this->cacheFamily);
        }
        if ($stmt === null || $stmt instanceof PDOStatement === false) {
            $stmt = null;

            return false;
        }
        $numCol = $stmt->columnCount();
        $rows = [];
        for ($i = 0; $i < $numCol; $i++) {
            $rows[] = $stmt->getColumnMeta($i);
        }
        $stmt = null;
        if ($uid !== false) {
            $this->parent->internalCache[$uid] = $rows;
        }
        return $rows;
    }

    /**
     * Begin a try block. It marks the errorText as empty and it store the value of genError<br>
     * It also avoids to throw any error.
     */
    public function beginTry()
    {
        $this->parent->errorText = '';
        $this->parent->isThrow = $this->parent->genError; // this value is deleted when it trigger an error
        $this->throwOnErrorB = $this->parent->throwOnError;
        $this->parent->throwOnError = false;
        if ($this->parent->customError) {
            set_exception_handler([$this->parent, 'custom_exception_handler']);
        }
    }

    /**
     * Run builder query and returns a PDOStatement.
     *
     * @param bool   $returnArray      true=return an array. False returns a
     *                                 PDOStatement
     * @param int    $extraMode        PDO::FETCH_ASSOC,PDO::FETCH_BOTH,PDO::FETCH_NUM,etc.
     *                                 By default it returns
     *                                 $extraMode=PDO::FETCH_ASSOC
     *
     * @param string $extraIdCache     [optional] if 'rungen' then cache is
     *                                 stored. If false the cache could be
     *                                 stored
     *
     * @param bool   $throwError
     *
     * @return bool|PDOStatement|array
     * @throws Exception
     */
    public function runGen(
        $returnArray = true,
        $extraMode = PDO::FETCH_ASSOC,
        $extraIdCache = 'rungen',
        $throwError = true
    )
    {
        $this->parent->errorText = '';
        $uid = false;
        $sql = $this->sqlGen();
        $isSelect = PdoOne::queryCommand($sql, true) === 'dql';

        try {
            $allparam = array_merge($this->setParamAssoc, $this->whereParamAssoc, $this->havingParamAssoc);

            if ($isSelect && $this->parent->useInternalCache && $returnArray) {
                $uid = hash($this->parent->encryption->hashType, $sql . $extraMode . serialize($allparam));
                if (isset($this->internalCache[$uid])) {
                    // we have an internal cache, so we will return it.
                    $this->parent->internalCacheCounter++;
                    $this->builderReset();
                    return $this->parent->internalCache[$uid];
                }
            }

            /** @var PDOStatement|bool $stmt */
            $stmt = $this->parent->prepare($sql);
        } catch (Exception $e) {
            $this->throwErrorChain('Error in prepare runGen', $throwError, $e);
            $this->builderReset();
            return false;
        }
        if ($stmt === null || $stmt === false) {
            $this->builderReset();
            return false;
        }
        $reval = true;
        if ($allparam) {
            try {
                foreach ($allparam as &$param) {
                    $reval = $reval && $stmt->bindParam(...$param); // unpack
                }
                if ($this->parent->partition !== null) {
                    if ($this->numericArgument) {
                        $partitionParam = [];
                        $partitionParam[0] = end($allparam)[0] + 1;
                        $partitionParam[1] = end($this->parent->partition);
                    } else {
                        $partitionParam = $this->parent->partition;
                    }
                    $reval = $reval && $stmt->bindParam(...$partitionParam);
                }
            } catch (Exception $ex) {
                $this->throwErrorChain("Error in bind. Parameter error.", $throwError, $ex);
                $this->builderReset();
                return false;
            }
            if (!$reval) {
                $this->throwErrorChain('Error in bind', $throwError);
                $this->builderReset();
                return false;
            }
        }
        $useCache = $this->useCache; // because builderReset cleans this value
        if ($useCache !== false && $returnArray) {
            $this->uid
                = hash($this->parent->encryption->hashType,
                $this->parent->lastQuery . $extraMode . serialize($allparam) . $extraIdCache);
            $result = $this->parent->cacheService->getCache($this->uid, $this->cacheFamily);
            if ($result !== false) {
                // it's found in the cache.
                $this->builderReset();
                if ($uid !== false) {
                    $this->parent->internalCache[$uid] = $result;
                }
                return $result;
            }
        } elseif ($extraIdCache === 'rungen') {
            $this->uid = null;
        }
        $this->parent->runQuery($stmt, null, false);
        if ($returnArray && $stmt instanceof PDOStatement) {
            $result = ($stmt->columnCount() > 0) ? $stmt->fetchAll($extraMode) : [];
            $this->parent->affected_rows = $stmt->rowCount();
            $stmt = null; // close
            if ($extraIdCache === 'rungen' && $this->uid) {
                // we store the information of the cache.
                $this->parent->setCache($this->uid, $this->cacheFamily, $result, $useCache);
            }
            $this->builderReset();
            if ($uid !== false) {
                $this->parent->internalCache[$uid] = $result;
            }
            return $result;
        }

        $this->builderReset();
        return $stmt;
    }

    /**
     * Generates the sql (script). It doesn't run or execute the query.
     *
     * @param bool $resetStack     if true then it reset all the values of the
     *                             stack, including parameters.
     *
     * @return string
     */
    public function sqlGen($resetStack = false)
    {
        if (stripos($this->select, 'select ') === 0) {
            // is it a full query? $this->select=select * ..." instead of $this->select=*
            $words = preg_split('#\s+#', strtolower($this->select));
        } else {
            $words = [];
        }
        if (!in_array('select', $words)) {
            $sql = 'select ' . $this->distinct . $this->select;
        } else {
            $sql = $this->select; // the query already constains "select", so we don't want "select select * from".
        }
        if (!in_array('from', $words)) {
            $sql .= ' from ' . $this->from;
        } else {
            $sql .= $this->from;
        }
        $where = $this->constructWhere();
        $having = $this->constructHaving();

        $sql .= $where . $this->group . $having . $this->order . $this->limit;
        if ($resetStack) {
            $this->builderReset();
        }
        return $sql;
    }

    /**
     * @return string
     */
    private function constructWhere()
    {
        return count($this->where) ? ' where ' . implode(' and ', $this->where) : '';
    }

    /**
     * @return string
     */
    private function constructHaving()
    {
        return count($this->having) ? ' having ' . implode(' and ', $this->having) : '';
    }

    /**
     * It reset the parameters used to Build Query.
     *
     * @param bool $forced if true then calling this method resets the stacks of variables<br>
     *                     if false then it only resets the stack if $this->noreset=false; (default is false)
     */
    public function builderReset($forced = false)
    {
        if ($this->noReset && !$forced) {
            return;
        }
        $this->ormClass = null;
        $this->numericArgument = false;
        $this->select = '';
        $this->noReset = false;
        $this->useCache = false;
        $this->from = '';
        $this->parent->tables = [];
        $this->where = [];

        $this->whereParamAssoc = [];
        $this->setParamAssoc = [];
        $this->havingParamAssoc = [];


        $this->whereCounter = 1;
        //$this->whereParamValue = [];
        $this->set = [];
        $this->group = '';
        $this->recursive = [];
        $this->parent->genError = true;
        $this->having = [];
        $this->limit = '';
        $this->distinct = '';
        $this->order = '';
    }

    /**
     * Write a log line for debug, clean the command chain then throw an error
     * (if throwOnError==true)
     *
     * @param string                $txt        The message to show.
     * @param bool                  $throwError if true then it throw error (is enabled). Otherwise it store the error.
     * @param null|RuntimeException $exception  If we already has an exception, then we could use to throw it.
     *
     * @see \eftec\PdoOne::$logLevel
     */
    public function throwErrorChain($txt, $throwError = true, $exception = null)
    {
        if ($this->parent->logLevel === 0) {
            $txt = 'Error on database';
        }
        if ($this->parent->getMessages() !== null) {
            $this->parent->getMessages()->addItem($this->parent->db, $txt);
        }
        $this->parent->debugFile($txt, 'ERROR');
        $this->parent->errorText = $txt;
        if ($throwError && $this->parent->throwOnError && $this->parent->genError) {
            if ($exception !== null) {
                throw $exception;
            } else {
                throw new RuntimeException($txt);
            }
        }
        $this->builderReset(true); // it resets the chain if any.
    }

    /**
     * It ends a try block and throws the error (if any)
     *
     * @return bool
     * @throws Exception
     */
    private function endTry()
    {

        $this->parent->throwOnError = $this->throwOnErrorB;
        if ($this->parent->errorText) {
            $this->throwErrorChain('endtry:' . $this->parent->errorText, $this->parent->isThrow);
            if ($this->parent->customError) {
                restore_exception_handler();
            }
            return false;
        }
        if ($this->parent->customError) {
            restore_exception_handler();
        }
        return true;
    }


    /**
     * It adds a having to the query builder.
     * <br><b>Example</b>:<br>
     *      select('*')->from('table')->group('col')->having('field=2')
     *      having( ['field'=>20] ) // associative array with automatic type
     *      having( ['field'=>[20]] ) // associative array with type defined
     *      having( ['field',20] ) // array automatic type
     *      having(['field',[20]] ) // array type defined
     *      having('field=20') // literal value
     *      having('field=?',[20]) // automatic type
     *      having('field',[20]) // automatic type (it's the same than
     *      where('field=?',[20]) having('field=?', [20] ) // type(i,d,s,b)
     *      defined having('field=?,field2=?', [20,'hello'] )
     *
     * @param string|array $sql
     * @param array|mixed  $param
     *
     * @return PdoOneQuery
     * @see  http://php.net/manual/en/mysqli-stmt.bind-param.php for types
     * @test InstanceOf
     *       PdoOne::class,this('field1=?,field2=?',[20,'hello'])
     */
    public function having($sql, $param = PdoOne::NULL)
    {
        if ($sql === null) {
            return $this;
        }

        return $this->where($sql, $param, true);
    }

    /**
     * <b>Example:</b><br>
     *      where( ['field'=>20] ) // associative array with automatic type
     *      where( ['field'=>[20]] ) // associative array with type defined
     *      where( ['field',20] ) // array automatic type
     *      where (['field',[20]] ) // array type defined
     *      where('field=20') // literal value
     *      where('field=?',[20]) // automatic type
     *      where('field',[20]) // automatic type (it's the same than
     *      where('field=?',[20]) where('field=?', [20] ) // type(i,d,s,b)
     *      defined where('field=?,field2=?', [20,'hello'] )
     *      where('field=:field,field2=:field2',
     *      ['field'=>'hello','field2'=>'world'] ) // associative array as value
     *
     * @param string|array $sql          Input SQL query or associative/indexed
     *                                   array
     * @param array|mixed  $param        Associative or indexed array with the
     *                                   conditions.
     * @param bool         $isHaving     if true then it is a HAVING sql commando
     *                                   instead of a WHERE.
     *
     * @param null|string  $tablePrefix
     *
     * @return PdoOneQuery
     * @see  http://php.net/manual/en/mysqli-stmt.bind-param.php for types
     * @test InstanceOf
     *       PdoOne::class,this('field1=?,field2=?',[20,'hello'])
     */
    public function where($sql, $param = PdoOne::NULL, $isHaving = false, $tablePrefix = null)
    {
        if ($sql === null || $sql === PdoOne::NULL) {
            return $this;
        }
        $this->constructParam2($sql, $param, $isHaving ? 'having' : 'where', false, $tablePrefix);
        return $this;
    }

    /**
     * <b>Example:</b><br>
     * <pre>
     * where( ['field'=>20] ) // associative array (named)
     * where( ['field=?'=>20] ) // associative array (numeric)
     * where( ['field=:name'=>20] ) // associative array (named)
     * where( ['field=:name and field2=:name'=>20] ) // IT DOESN'T WORK
     * where( ['field'=>[20]] ) // associative array with type defined
     * where( ['field',20] ) // indexed array automatic type
     * where (['field',[20]] ) // indexed array type defined
     * where('field=20') // literal value
     * where('field=?',[20]) // automatic type
     * where('field',[20]) // automatic type (it's the same than
     * where('field=?',[20]) where('field=?', [20] ) // type(i,d,s,b)
     *      defined where('field=?,field2=?', [20,'hello'] )
     * where('field=:field,field2=:field2',
     *      ['field'=>'hello','field2'=>'world'] ) // associative array as value
     * </pre>
     *
     * @param array|string     $where
     * @param string|array|int $params
     * @param string           $type
     * @param bool             $return
     * @param null|string      $tablePrefix
     *
     * @return array|null
     */
    public function constructParam2(
        $where,
        $params = PdoOne::NULL,
        $type = 'where',
        $return = false,
        $tablePrefix = null
    )
    {
        $queryEnd = [];
        $named = [];
        $pars = [];

        if ($params === PdoOne::NULL || $params === null) {
            if (is_array($where)) {
                $numeric = isset($where[0]) || $this->numericArgument;
                if ($numeric) {
                    $this->numericArgument = true;
                    // numeric  column=?
                    $c = count($where) - 1;
                    for ($i = 0; $i < $c; $i += 2) {
                        $v = $where[$i + 1];
                        // constructParam2(['field',20]])
                        $param = [$this->whereCounter, $v, $this->parent->getType($v), null];
                        $queryEnd[] = $where[$i];
                        $named[] = '?';
                        $this->whereCounter++;
                        $pars[] = $param;
                    }
                } else {
                    // named  column=:arg
                    foreach ($where as $k => $v) {
                        if (strpos($k, '?') === false) {
                            if (strpos($k, ':') !== false) {
                                // "aaa=:aaa"
                                $parts = explode(':', $k, 2);
                                $paramName = ':' . str_replace('.', '_', $parts[1]);
                            } else {
                                // "aaa"
                                $paramName = ':' . str_replace('.', '_', $k);
                            }
                            $named[] = $paramName;
                        } else {
                            // "aa=?"
                            $paramName = $this->whereCounter;
                            $this->whereCounter++;
                            $named[] = '?';
                        }
                        // constructParam2(['field'=>20])
                        $param = [$paramName, $v, $this->parent->getType($v), null];
                        $pars[] = $param;
                        if ($tablePrefix !== null && strpos($k, '.') === false) {
                            $queryEnd[] = $tablePrefix . '.' . $k;
                        } else {
                            $queryEnd[] = $k;
                        }
                    }
                }
            } else {
                // constructParam2('query=xxx') without argument
                $named[] = '';
                $queryEnd[] = $where;
            }
        } else {
            // where and params are not empty
            if (!is_array($params)) {
                $params = [$params];
            }
            if (!is_array($where)) {
                $queryEnd[] = $where;
                $numeric = isset($params[0]);
                if ($numeric) {
                    foreach ($params as $v) {
                        // constructParam2('name=? and type>?', ['Coca-Cola',12345]);
                        $named[] = '?';
                        $pars[] = [
                            $this->whereCounter,
                            $v,
                            $this->parent->getType($v),
                            null
                        ];
                        $this->whereCounter++;
                    }
                } else {
                    foreach ($params as $k => $v) {
                        $named[] = $k;
                        // constructParam2('name=:name and type<:type', ['name'=>'Coca-Cola','type'=>987]);;
                        $pars[] = [$k, $v, $this->parent->getType($v), null];
                        //$paramEnd[]=$param;
                    }
                }
                if (count($named) === 0) {
                    $named[] = '?'; // at least one argument.
                }
            } else {
                // constructParam2([],..);
                $numeric = isset($where[0]);

                if ($numeric) {
                    foreach ($where as $v) {
                        //$named[] = '?';
                        $queryEnd[] = $v;
                    }
                } else {
                    trigger_error('parameteres not correctly defined');
                    /*foreach ($where as $k => $v) {
                        $named[] = '?';
                        $queryEnd[] = $k;
                    }*/
                }
                $numeric = isset($params[0]);
                if ($numeric) {
                    foreach ($params as $v) {
                        //$paramEnd[]=$param;
                        // constructParam2(['name','type'], ['Coca-Cola',123]);
                        $named[] = '?';
                        $pars[] = [$this->whereCounter, $v, $this->parent->getType($v), null];
                        $this->whereCounter++;
                        //$paramEnd[]=$param;
                    }
                } else {
                    foreach ($params as $k => $v) {
                        $named[] = $k;
                        // constructParam2(['name=:name','type<:type'], ['name'=>'Coca-Cola','type'=>987]);;
                        $pars[] = [$k, $v, $this->parent->getType($v), null];
                        //$paramEnd[]=$param;
                    }
                }
            }
        }
        $i = -1;
        foreach ($queryEnd as $k => $v) {
            $i++;

            if ($named[$i] !== '' && strpos($v, '?') === false && strpos($v, $named[$i]) === false) {
                $v .= '=' . $named[$i];
                $queryEnd[$k] = $v;
            }
            switch ($type) {
                case 'where':
                    $this->where[] = $v;
                    break;
                case 'having':
                    $this->having[] = $v;
                    break;
                case 'set':
                    $this->set[] = $v;
                    break;
            }
        }

        switch ($type) {
            case 'where':
                $this->whereParamAssoc = array_merge($this->whereParamAssoc, $pars);
                break;
            case 'having':
                $this->havingParamAssoc = array_merge($this->havingParamAssoc, $pars);
                break;
            case 'set':
                $this->setParamAssoc = array_merge($this->setParamAssoc, $pars);
                break;
        }

        if ($return) {
            return [$queryEnd, $pars];
        }
        return null;
    }

    //</editor-fold>


    //<editor-fold desc="Query Builder functions end chain" defaultstate="collapsed" >

    /**
     * Returns the last row. It's not recommended. Use instead first() and change the order.<br>
     * This method is an <b>end of the chain method</b>, so it clears the method stack<br>
     * <b>Note</b>: This method could not be efficient because it reads all the values.
     * If you can, then use the methods sort()::first()<br>
     * <b>Example</b>:<br>
     * <pre>
     * $con->select('*')->from('table')->last(); // select * from table (last scalar value)
     * </pre>
     *
     * @return array|null
     * @throws Exception
     * @see \eftec\PdoOne::first
     */
    public function last()
    {
        if ($this->ormClass !== null) {
            throw new RuntimeException("The method [" . __FUNCTION__ . "] is not yet implemented with an ORM class");
        }
        $useCache = $this->useCache; // because builderReset cleans this value

        if ($useCache !== false) {
            $sql = $this->sqlGen();
            $this->uid = hash($this->parent->encryption->hashType,
                $sql . PDO::FETCH_ASSOC . serialize($this->whereParamAssoc) . serialize($this->havingParamAssoc)
                . 'last');
            $rows = $this->parent->cacheService->getCache($this->uid, $this->cacheFamily);
            if ($rows !== false) {
                $this->builderReset();

                return $rows;
            }
        }
        $this->beginTry();
        /** @var PDOStatement|bool $statement */
        $statement = $this->runGen(false, PDO::FETCH_ASSOC, 'last', false);
        if ($this->endtry() === false) {
            return null;
        }
        $row = null;
        if ($statement === false) {
            $row = null;
        } elseif (!$statement->columnCount()) {
            $row = null;
        } else {
            while ($dummy = $statement->fetch(PDO::FETCH_ASSOC)) {
                $row = $dummy;
            }
            @$statement->closeCursor();
            $statement = null;
        }

        if ($this->uid && $useCache !== false) {
            // we store the information of the cache.
            $this->parent->setCache($this->uid, $this->cacheFamily, $row, $useCache);
        }

        return $row;
    }

    /**
     * It returns an array of simple columns (not declarative). It uses the
     * first column<br>
     * <b>Example:</b><br>
     * <pre>
     * select('select id from table')->toListSimple() // ['1','2','3','4']
     * </pre>
     *
     * @return array|bool
     * @throws Exception
     */
    public function toListSimple()
    {
        $this->usingORM(true);
        $useCache = $this->useCache; // because builderReset cleans this value
        $this->beginTry();
        $rows = $this->runGen(true, PDO::FETCH_COLUMN, 'tolistsimple', false);
        if ($this->endtry() === false) {
            return false;
        }
        if ($this->uid && $useCache !== false) {
            // we store the information of the cache.
            $this->parent->setCache($this->uid, $this->cacheFamily, $rows, $useCache);
        }

        return $rows;
    }

    /**
     * It is called when we want to runs a method and it is called by an ORM.<br>
     *  It helps to assign the table and the fields
     * @param bool $addColumns
     */
    private function usingORM($addColumns = false)
    {
        if ($this->ormClass !== null && $this->from === '') {
            /** @var _BasePdoOneRepo $cls */
            $cls = $this->ormClass;
            if ($addColumns) {
                /** @noinspection PhpPossiblePolymorphicInvocationInspection */
                $this->select($cls::getDefName());
            }
            /** @noinspection PhpPossiblePolymorphicInvocationInspection */
            $this->from($cls::TABLE);
            //throw new RuntimeException('Method toListKeyValue not yet implemented for PdoOne::ORM');
        }
    }

    /**
     * It adds a select to the query builder.
     * <br><b>Example</b>:<br>
     * <pre>
     * ->select("\*")->from('table') = <i>"select * from table"</i><br>
     * ->select(['col1','col2'])->from('table') = <i>"select col1,col2 from
     * table"</i><br>
     * ->select('col1,col2')->from('table') = <i>"select col1,col2 from
     * table"</i><br>
     * ->select('select *')->from('table') = <i>"select * from table"</i><br>
     * ->select('select * from table') = <i>"select * from table"</i><br>
     * ->select('select * from table where id=1') = <i>"select * from table
     * where id=1"</i><br>
     * </pre>
     *
     * @param string|array $sql
     *
     * @return PdoOneQuery
     * @test InstanceOf PdoOne::class,this('select 1 from DUAL')
     */
    public function select($sql)
    {
        if (is_array($sql)) {
            $this->select .= implode(', ', $sql);
        } elseif ($this->select === '') {
            $this->select = $sql;
        } else {
            $this->select .= ', ' . $sql;
        }

        return $this;
    }

    /**
     * Adds a from for a query. It could be used by select,insert,update and
     * delete.<br>
     * <b>Example:</b><br>
     * <pre>
     *      from('table')
     *      from('table alias')
     *      from('table1,table2')
     *      from('table1,table2','dbo')
     *      from('table1 inner join table2 on table1.c=table2.c')
     * </pre>
     *
     * @param string      $sql Input SQL query
     * @param null|string $schema The schema/database of the table without trailing dot.<br>
     *                            Example 'database' or 'database.dbo'
     * @return PdoOneQuery
     * @test InstanceOf PdoOne::class,this('table t1')
     */
    public function from($sql, $schema = null)
    {
        if ($sql === null) {
            return $this;
        }
        if($schema!==null) {
            $schema .= '.';
            $comma=strpos($sql,',')!==false;
            if($comma) {
                // table1,table2 => prefix.table1,prefix.table2
                $sql=str_replace(',',','.$schema,$sql);
            } else {
                $join=stripos($sql,' join ')!==false;
                if($join) {
                    // table1 inner join table2 => prefix.table1 inner join prefix.table2
                    $sql = str_ireplace(' join ', ' join ' . $schema, $sql);
                }
            }
            // prefix at the begginer table1=> prefix.table1
            $sql=$schema.ltrim($sql);
        }

        $this->from = ($sql) ? $sql . $this->from : $this->from;
        $this->parent->tables[] = explode(' ', $sql)[0];
        return $this;
    }

    /**
     * @throws Exception
     */
    public function _exist($conditions)
    {
        if ($this->ormClass !== null) {
            $cls = $this->ormClass;
            return $cls::setPdoOneQuery($this)::exist($conditions);
        }
        $useCache = $this->useCache; // because builderReset cleans this value
        if ($useCache !== false) {
            $sql = $this->sqlGen();
            $this->uid = hash($this->parent->encryption->hashType,
                $sql . PDO::FETCH_ASSOC . serialize($this->whereParamAssoc) . serialize($this->havingParamAssoc)
                . 'firstscalar');
            $rows = $this->parent->cacheService->getCache($this->uid, $this->cacheFamily);
            if ($rows !== false) {
                $this->builderReset();

                return $rows;
            }
        }
        $exist = false;
        $this->beginTry();
        /** @var PDOStatement|bool $statement */
        $statement = $this->runGen(false, PDO::FETCH_ASSOC, 'exist', false);
        if ($this->endtry() === false) {
            return null;
        }
        if ($statement === false) {
            $row = null;
        } elseif (!$statement->columnCount()) {
            $row = null;
        } else {
            $row = $statement->fetch(PDO::FETCH_ASSOC);
            @$statement->closeCursor();
            $statement = null;
            if ($row !== false) {
                $exist = true;
            } else {
                $exist = false;
            }
        }
        if ($this->uid && $useCache !== false) {
            // we store the information of the cache.
            $this->parent->setCache($this->uid, $this->cacheFamily, $row, $useCache);
        }

        return $exist;
    }

    /**
     * Alias of from()
     * @param $sql
     * @return $this
     * @see \eftec\PdoOneQuery::from
     */
    public function table($sql)
    {
        return $this->from($sql);
    }

    /**
     * It returns an associative array where the first value is the key (first column) and the value is the second
     * column<br>
     * If the second column does not exist then it uses first column as the second value<br>
     * If there is 3 columns and it does not use a separator, then it only uses the first 2 columns<br>
     * If there is 3 columns and it does use a separator, then the second value is the merge of the last 2 columns<br>
     * <b>Example:</b><br>
     * <pre>
     * select('select cod,name from table')->toListKeyValue()
     * // ['cod1'=>'name1','cod2'=>'name2']
     * select('select cod,name,ext from table')->toListKeyValue('|')
     * // ['cod1'=>'name1|ext1','cod2'=>'name2|ext2']
     * </pre>
     *
     * @param string|null $extraValueSeparator     (optional) It allows to read a
     *                                             third value and returns it
     *                                             concatenated with the value.
     *                                             Example '|'
     *
     * @return array|null
     * @throws Exception
     */
    public function toListKeyValue($extraValueSeparator = null)
    {
        $this->usingORM();
        $list = $this->_toList(PDO::FETCH_NUM);
        if (!is_array($list)) {
            return null;
        }
        $result = [];
        foreach ($list as $item) {
            if ($extraValueSeparator === null) {
                $result[$item[0]] = isset($item[1]) ? $item[1] : $item[0];
            } else {
                $result[$item[0]] = (isset($item[1]) ? $item[1] : $item[0])
                    . ((isset($item[2]) ? $extraValueSeparator . $item[2] : ''));
            }
        }
        return $result;
    }

    /**
     * @throws Exception
     */
    public function _toList($pdoMode = PDO::FETCH_ASSOC)
    {
        $useCache = $this->useCache; // because builderReset cleans this value
        $this->beginTry();
        $rows = $this->runGen(true, $pdoMode, 'tolist', false);
        if ($this->endtry() === false) {
            return false;
        }
        if ($this->uid && $useCache !== false) {
            // we store the information of the cache.
            $this->parent->setCache($this->uid, $this->cacheFamily, $rows, $useCache);
        }
        return $rows;
    }

    /**
     * It returns an declarative array of rows.<br>
     * If not data is found, then it returns an empty array<br>
     * This method is an <b>end of the chain method</b>, so it clears the method stack<br>
     * <b>Example</b>:<br>
     * <pre>
     * $this->select('select id,name from table')->toList() // [['id'=>'1','name'='john'],['id'=>'2','name'=>'anna']]
     * $this->select('id,name')
     *      ->from('table')
     *      ->where('condition=?',[20])
     *      ->toList();
     * </pre>
     *
     * @param int $pdoMode (optional) By default is PDO::FETCH_ASSOC
     *
     * @return array|bool
     * @throws Exception
     */
    public function toList($pdoMode = PDO::FETCH_ASSOC)
    {
        if ($this->ormClass !== null) {
            $cls = $this->ormClass;
            return $cls::setPdoOneQuery($this)::ToList(PdoOne::NULL, null);
        }
        return $this->_toList($pdoMode);
    }

    /**
     * It returns a PDOStatement.<br>
     * <b>Note:</b> The result is not cached.
     *
     * @return PDOStatement
     * @throws Exception
     */
    public function toResult()
    {
        if ($this->ormClass !== null) {
            throw new RuntimeException("The method [" . __FUNCTION__ . "] is not yet implemented with an ORM class");
        }
        return $this->runGen(false);
    }

    //</editor-fold>
    //<editor-fold desc="Query Builder aggregations" defaultstate="collapsed" >

    /**
     * It returns the first row.  If there is not row then it returns false.<br>
     * This method is an <b>end of the chain method</b>, so it clears the method stack<br>
     * <b>Example</b>:<br>
     * <pre>
     *      $con->select('*')->from('table')->first(); // select * from table (first value)
     *      Repo::->method(...)->first(1); // (ORM only, the first value where the primary key is 1
     *      Repo::->method(...)->first([1,2]); // (ORM only, first value where the primary keys are 1 and 2
     *      Repo::->method(...)->first(['id1'=>1,'id'=>2]); // (ORM only,first value where the primary keys are 1 and 2
     *
     * </pre>
     *
     * @param int $pk the argument is used together with ORM.
     * @return array|null|false
     * @throws Exception
     */
    public function first($pk = PdoOne::NULL)
    {
        if ($this->ormClass !== null) {
            $cls = $this->ormClass;
            /** @see \eftec\_BasePdoOneRepo::first */
            return $cls::setPdoOneQuery($this)::first($pk);
        }
        return $this->_first();
    }

    /**
     * @throws Exception
     */
    public function _first()
    {
        $useCache = $this->useCache; // because builderReset cleans this value
        $uid = false;
        if ($useCache !== false) {
            $sql = $this->sqlGen();
            $this->uid = hash($this->parent->encryption->hashType,
                $sql . PDO::FETCH_ASSOC . serialize($this->whereParamAssoc) . serialize($this->havingParamAssoc)
                . 'firstscalar');
            $rows = $this->parent->cacheService->getCache($this->uid, $this->cacheFamily);
            if ($rows !== false) {
                $this->builderReset();

                return $rows;
            }
        }
        if ($this->parent->useInternalCache) {
            $sql = (!isset($sql)) ? $this->sqlGen() : $sql;
            $allparam = array_merge($this->setParamAssoc, $this->whereParamAssoc, $this->havingParamAssoc);
            $uid = hash($this->parent->encryption->hashType, 'first' . $sql . serialize($allparam));
            if (isset($this->parent->internalCache[$uid])) {
                // we have an internal cache, so we will return it.
                $this->parent->internalCacheCounter++;
                $this->builderReset();
                return $this->parent->internalCache[$uid];
            }
        }
        $this->beginTry();
        /** @var PDOStatement|bool $statement */
        $statement = $this->runGen(false, PDO::FETCH_ASSOC, 'first', false);
        if ($this->endtry() === false) {
            return null;
        }
        if ($statement === false) {
            $row = null;
        } elseif (!$statement->columnCount()) {
            $row = null;
        } else {
            $row = $statement->fetch(PDO::FETCH_ASSOC);
            @$statement->closeCursor();
            $statement = null;
        }

        if ($this->uid && $useCache !== false) {
            // we store the information of the cache.
            $this->parent->setCache($this->uid, $this->cacheFamily, $row, $useCache);
        }
        if ($uid !== false) {
            $this->parent->internalCache[$uid] = $row;
        }

        return $row;
    }

    /**
     * It generates a query for "count". It is a macro of select()
     * <br><b>Example</b>:<br>
     * <pre>
     * ->from('table')->count('') // select count(*) from
     * table<br>
     * ->count('from table') // select count(*) from table<br>
     * ->count('from table where condition=1') // select count(*)
     * from table where condition=1<br>
     * ->count('from table','col') // select count(col) from
     * table<br>
     * </pre>
     *
     * @param string|null $sql [optional]
     * @param string      $arg [optional]
     *
     * @return mixed|null
     * @throws Exception
     */
    public function count($sql = '', $arg = '*')
    {
        return $this->_aggFn('count', $sql, $arg);
    }

    /**
     * This method is used internally for the methods count(), sum(), min(), etc.
     *
     * @param string $method
     * @param string $sql
     * @param string $arg
     * @return mixed|null
     * @throws Exception
     */
    public function _aggFn($method, $sql = '', $arg = '')
    {
        // ORM is read using firstScalar()
        $this->parent->beginTry();
        if ($arg === '') {
            $arg = $sql; // if the argument is empty then it uses sql as argument
            $sql = ''; // and it lefts sql as empty
        }
        if ($arg === '*' || $this->parent->databaseType !== 'sqlsrv') {
            $r = $this->select("select $method($arg) $sql")->firstScalar();
            $this->parent->endTry();
            return $r;
        }
        $r = $this->select("select $method(cast($arg as decimal)) $sql")->firstScalar();
        $this->parent->endTry();
        return $r;
    }

    /**
     * Executes the query, and returns the first column of the first row in the
     * result set returned by the query. Additional columns or rows are ignored.<br>
     * If value is not found then it returns null.<br>
     * * This method is an <b>end of the chain method</b>, so it clears the method stack<br>
     * <b>Example</b>:<br>
     * <pre>
     * $con->select('*')->from('table')->firstScalar(); // select * from table (first scalar value)
     * </pre>
     *
     * @param string|null $colName     If it's null then it uses the first
     *                                 column.
     *
     * @return mixed|null
     * @throws Exception
     */
    public function firstScalar($colName = null)
    {
        $this->usingORM(true);
        $useCache = $this->useCache; // because builderReset cleans this value
        if ($useCache !== false) {
            $sql = $this->sqlGen();
            $this->uid = hash($this->parent->encryption->hashType,
                $sql . PDO::FETCH_ASSOC . serialize($this->whereParamAssoc) . serialize($this->havingParamAssoc)
                . __FUNCTION__);
            $rows = $this->parent->cacheService->getCache($this->uid, $this->cacheFamily);
            if ($rows !== false) {
                $this->builderReset();

                return $rows;
            }
        }
        $this->beginTry();
        /** @var PDOStatement|bool $statement */
        $statement = $this->runGen(false, PDO::FETCH_ASSOC, 'firstscalar', false);
        if ($this->endtry() === false) {
            return null;
        }
        if ($statement === false) {
            $row = null;
        } elseif (!$statement->columnCount()) {
            $row = null;
        } else {
            $row = $statement->fetch(PDO::FETCH_ASSOC);
            @$statement->closeCursor();
            $statement = null;
            if ($row !== false) {
                if ($colName === null) {
                    $row = reset($row); // first column of the first row
                } else {
                    $row = $row[$colName];
                }
            } else {
                $row = null;
            }
        }
        if ($this->uid && $useCache !== false) {
            // we store the information of the cache.
            $this->parent->setCache($this->uid, $this->cacheFamily, $row, $useCache);
        }

        return $row;
    }

    /**
     * It generates a query for "sum". It is a macro of select()
     * <br><b>Example</b>:<br>
     * ->sum('from table','col')->firstScalar() // select sum(col) from
     * table<br>
     * ->sum('col')->from('table')->firstScalar() // select sum(col) from
     * table<br>
     * ->sum('','col')->from('table')->firstScalar() // select sum(col) from
     * table<br>
     *
     * @param string $sql     [optional] it could be the name of column or part
     *                        of the query ("from table..")
     * @param string $arg     [optiona] it could be the name of the column
     *
     * @return mixed|null
     * @throws Exception
     */
    public function sum($sql = '', $arg = '')
    {
        return $this->_aggFn('sum', $sql, $arg);
    }

    /**
     * It generates a query for "min". It is a macro of select()
     * <br><b>Example</b>:<br>
     * ->min('from table','col') // select min(col) from
     * table<br>
     * ->from('table')->min('col') // select min(col) from
     * table<br>
     * ->from('table')->min('','col') // select min(col) from
     * table<br>
     *
     * @param string $sql
     * @param string $arg
     *
     * @return mixed|null
     * @throws Exception
     */
    public function min($sql = '', $arg = '')
    {
        return $this->_aggFn('min', $sql, $arg);
    }


    //</editor-fold>
    //<editor-fold desc="Query Builder functions" defaultstate="collapsed" >

    /**
     * It generates a query for "max". It is a macro of select()
     * <br><b>Example</b>:<br>
     * ->max('from table','col')->firstScalar() // select max(col) from
     * table<br>
     * ->max('col')->from('table')->firstScalar() // select max(col) from
     * table<br>
     * ->max('','col')->from('table')->firstScalar() // select max(col) from
     * table<br>
     *
     * @param string $sql
     * @param string $arg
     *
     * @return mixed|null
     * @throws Exception
     */
    public function max($sql = '', $arg = '')
    {
        return $this->_aggFn('max', $sql, $arg);
    }

    /**
     * It generates a query for "avg". It is a macro of select()
     * <br><b>Example</b>:<br>
     * ->avg('from table','col')->firstScalar() // select avg(col) from
     * table<br>
     * ->avg('col')->from('table')->firstScalar() // select avg(col) from
     * table<br>
     * ->avg('','col')->from('table')->firstScalar() // select avg(col) from
     * table<br>
     *
     * @param string $sql
     * @param string $arg
     *
     * @return mixed|null
     * @throws Exception
     */
    public function avg($sql = '', $arg = '')
    {
        return $this->_aggFn('avg', $sql, $arg);
    }

    /**
     * Returns true if the current query has a "having" or "where"
     *
     * @param bool $having <b>true</b> it return the number of where<br>
     *                     <b>false</b> it returns the number of having
     *
     * @return bool
     */
    public function hasWhere($having = false)
    {
        if ($having) {
            return count($this->having) > 0;
        }

        return count($this->where) > 0;
    }

    /**
     * Its a macro of limit but it works for paging. It uses static::$pageSize to determine the rows to return
     *
     * @param int $numPage Number of page. It starts with 1.
     *
     * @return PdoOneQuery
     * @throws Exception
     */
    public function page($numPage)
    {
        if ($this->ormClass !== null) {
            $cls = $this->ormClass;
            $p0 = $cls::$pageSize * ($numPage - 1);
            $p1 = $p0 + $cls::$pageSize;
        } else {
            $p0 = 20 * ($numPage - 1);
            $p1 = $p0 + 20;
        }
        return $this->limit("$p0,$p1");
    }

    /**
     * It adds an "limit" in a query. It depends on the type of database<br>
     * <b>Example:</b><br>
     * <pre>
     *      ->select("")->limit("10,20")->toList();
     * </pre>
     *
     * @param string $sql Input SQL query
     *
     * @return PdoOneQuery
     * @throws Exception
     * @test InstanceOf PdoOne::class,this('1,10')
     */
    public function limit($sql)
    {
        if ($sql === null) {
            return $this;
        }
        $this->limit = $this->parent->service->limit($sql);

        return $this;
    }

    /**
     * Returns an array with nulls
     *
     * @param array|null $values          =self::factory()
     * @param string     $recursivePrefix It is the prefix of the recursivity.
     *
     * @return array
     */
    public function factoryNull($values = null, $recursivePrefix = '')
    {
        if ($this->ormClass !== null) {
            $cls = $this->ormClass;
            return $cls::setPdoOneQuery($this)::factoryNull($values, $recursivePrefix);
        }
        return $this->factory($values, $recursivePrefix);
    }

    /**
     * Returns an array with the default values (0 for numbers, empty for string, and array|null if recursive)
     *
     * @param array|null $values          =self::factory()
     * @param string     $recursivePrefix It is the prefix of the recursivity.
     *
     * @return array
     */
    public function factory($values = null, $recursivePrefix = '')
    {
        if ($this->ormClass !== null) {
            $cls = $this->ormClass;
            return $cls::setPdoOneQuery($this)::factory($values, $recursivePrefix);
        }
        $result = [];
        $fields = explode(',', $this->select);
        foreach ($fields as $field) {
            $subfield = explode(' ', $field);
            $result[$subfield[0]] = null;
        }
        if ($values !== null) {
            $result = array_merge($result, $values);
        }
        return $result;
    }

    /**
     * Adds a distinct to the query. The value is ignored if the select() is
     * written complete.<br>
     * <pre>
     *      ->select("*")->distinct() // works
     *      ->select("select *")->distinct() // distinct is ignored.
     *</pre>
     *
     * @param string $sql Input SQL query
     *
     * @return PdoOneQuery
     * @test InstanceOf PdoOne::class,this()
     */
    public function distinct($sql = 'distinct')
    {
        if ($sql === null) {
            return $this;
        }
        $this->distinct = ($sql) ? $sql . ' ' : '';

        return $this;
    }

    /**
     * If true, then on error, the code thrown an error.<br>>
     * If false, then on error, the the code returns false and logs the errors ($this->parent->errorText).
     *
     * @param bool $value
     *
     * @return $this
     */
    public function setThrowOnError($value = false)
    {
        $this->parent->throwOnError = $value;
        return $this;
    }

    /**
     * If true then the stack/query builder will not reset the stack (but on error) when it is finished<br>
     * <b>Example:</b><br>
     * <pre>
     * $this->parent->pdoOne->select('*')->from('missintable')->setNoReset(true)->toList();
     * // we do something with the stack
     * $this->parent->pdoOne->builderReset(true); // reset the stack manually
     * </pre>
     *
     * @param bool $noReset
     *
     * @return $this
     */
    public function setNoReset($noReset = true)
    {
        $this->noReset = $noReset;
        return $this;
    }

    /**
     * It returns an uniqued uid ('sha256' or the value defined in PdoOneEncryption::$hashType) based in all the
     * parameters of the query (select,from,where,parameters,group,recursive,having,limit,distinct,order,etc.) and
     * optionally in an extra value
     *
     * @param mixed|null $extra  [optional] If we want to add an extra value to the UID generated
     * @param string     $prefix A prefix added to the UNID generated.
     *
     * @return string
     * @see \eftec\PdoOneEncryption::$hashType
     */
    public function buildUniqueID($extra = null, $prefix = '')
    {
        // set and setparam are not counted
        $all = [
            $this->select,
            $this->from,
            $this->where,
            $this->whereParamAssoc,
            $this->havingParamAssoc,
            // $this->setParamAssoc,
            //$this->whereParamValue,
            $this->group,
            $this->recursive,
            $this->having,
            $this->limit,
            $this->distinct,
            $this->order,
            $extra
        ];
        return $prefix . hash($this->parent->encryption->hashType, json_encode($all));
    }

    /**
     * Adds a left join to the pipeline. It is possible to chain more than one
     * join<br>
     * <b>Example:</b><br>
     * <pre>
     *      left('table on t1.c1=t2.c2')
     *      left('table on table.c1=t2.c2').left('table2 on
     * table1.c1=table2.c2')
     * </pre>
     *
     * @param string $sql Input SQL query
     *
     * @return PdoOneQuery
     * @test InstanceOf PdoOne::class,this('table2 on table1.t1=table2.t2')
     */
    public function left($sql)
    {
        if ($sql === null) {
            return $this;
        }
        $this->from .= ($sql) ? " left join $sql" : '';
        $this->parent->tables[] = explode(' ', $sql)[0];
        return $this;
    }

    /**
     * Adds a right join to the pipeline. It is possible to chain more than one
     * join<br>
     * <b>Example:</b><br>
     *      right('table on t1.c1=t2.c2')<br>
     *      right('table on table.c1=t2.c2').right('table2 on
     *      table1.c1=table2.c2')<br>
     *
     * @param string $sql Input SQL query
     *
     * @return PdoOneQuery
     * @test InstanceOf PdoOne::class,this('table2 on table1.t1=table2.t2')
     */
    public function right($sql)
    {
        if ($sql === null) {
            return $this;
        }
        $this->from .= ($sql) ? " right join $sql" : '';
        $this->parent->tables[] = explode(' ', $sql)[0];
        return $this;
    }

    /**
     * It sets a value into the query (insert or update)<br>
     * <b>Example:</b><br>
     *      ->from("table")->set('field1=?',20),set('field2=?','hello')->insert()<br>
     *      ->from("table")->set("type=?",[6])->where("i=1")->update()<br>
     *      set("type=?",6) // automatic<br>
     *
     * @param string|array $sqlOrArray
     * @param array|mixed  $param
     *
     *
     * @return PdoOneQuery
     * @test InstanceOf
     *       PdoOne::class,this('field1=?,field2=?',[20,'hello'])
     */
    public function set($sqlOrArray, $param = PdoOne::NULL)
    {
        if ($sqlOrArray === null) {
            return $this;
        }
        if (count($this->where)) {
            $this->throwErrorChain('method set() must be before where()');
            return $this;
        }

        $this->constructParam2($sqlOrArray, $param, 'set');
        return $this;
    }

    //</editor-fold>

    /**
     * It groups by a condition.<br>
     * <b>Example:</b><br>
     * ->select('col1,count(*)')->from('table')->group('col1')->toList();
     *
     * @param string $sql Input SQL query
     *
     * @return PdoOneQuery
     * @test InstanceOf PdoOne::class,this('fieldgroup')
     */
    public function group($sql)
    {
        if ($sql === null) {
            return $this;
        }
        $this->group = ($sql) ? ' group by ' . $sql : '';

        return $this;
    }

    /**
     * Alias of recursive()
     *
     * @param array|mixed $fields The fields to load recursively.
     * @return $this
     * @see \eftec\PdoOne::recursive
     */
    public function include($fields)
    {
        return $this->recursive($fields);
    }

    /**
     * It sets a recursive array.<br>
     * <b>example:</b>:<br>
     * <pre>
     * $this->recursive(['field1','field2']);
     * </pre>
     *
     * @param array|mixed $rec The fields to load recursively.
     *
     * @return $this
     */
    public function recursive($rec)
    {
        if ($this->ormClass !== null) {
            $cls = $this->ormClass;
            if (is_string($rec)) {
                /** @noinspection PhpPossiblePolymorphicInvocationInspection */
                $rec = $cls::getRelations($rec);
            }
        }
        return $this->_recursive($rec);
    }

    /**
     * Used internally _BasePdoOneRepo. It always calls the native recursive assignment.
     *
     * @param $rec
     * @return $this
     */
    public function _recursive($rec)
    {
        if (is_array($rec)) {
            $this->recursive = $rec;
        } else {
            $this->recursive = [$rec];
        }
        return $this;
    }

    /**
     * It gets the recursive array.
     *
     * @return array
     */
    public function getRecursive()
    {
        return $this->recursive;
    }

    /**
     * It returns true if recursive has some needle.<br>
     * If $this->recursive is '*' then it always returns true.
     *
     * @param string     $needle
     * @param null|array $recursiveArray If null then it uses the recursive array specified by
     *                                   $this->parent->>recursive();
     *
     * @return bool
     */
    public function hasRecursive($needle, $recursiveArray = null)
    {
        if (count($this->recursive) === 1 && $this->recursive[0] === '*') {
            return true;
        }
        if ($recursiveArray) {
            return in_array($needle, $recursiveArray, true);
        }
        return in_array($needle, $this->recursive, true);
    }

    /**
     * If false then it wont generate an error.<br>
     * If true (default), then on error, it behave normally<br>
     * If false, then the error is captured and store in $this::$errorText<br>
     * This command is specific for generation of query and its reseted when the query is executed.
     *
     * @param bool $error
     *
     * @return PdoOneQuery
     * @see \eftec\PdoOne::$errorText
     */
    public function genError($error = false)
    {
        $this->parent->genError = $error;
        return $this;
    }

    /**
     * It allows to insert a declarative array. It uses "s" (string) as
     * filetype.
     * <p>Example: ->insertObject('table',['field1'=>1,'field2'=>'aaa']);
     *
     * @param string       $tableName     The name of the table.
     * @param array|object $object        associative array with the colums and
     *                                    values. If the insert returns an identity then it changes the value
     * @param array        $excludeColumn (optional) columns to exclude. Example
     *                                    ['col1','col2']
     *
     * @return false|int
     * @throws Exception
     */
    public function insertObject($tableName, &$object, $excludeColumn = [])
    {
        $this->parent->beginTry();
        $objectCopy = (array)$object;
        foreach ($excludeColumn as $ex) {
            unset($objectCopy[$ex]);
        }

        $id = $this->_insert($tableName, $objectCopy);
        /** id could be 0,false or null (when it is not generated) */
        if ($id) {
            $pks = $this->parent->setUseInternalCache()->service->getDefTableKeys($tableName, true, 'PRIMARY KEY');
            if ($pks > 0) {
                // we update the object because it returned an identity.
                $k = array_keys($pks)[0]; // first primary key
                if (is_array($object)) {
                    $object[$k] = $id;
                } else {
                    $object->$k = $id;
                }
            }
        }
        $this->parent->endTry();
        return $id;
    }

    /**
     * @throws Exception
     */
    protected function _insert($tableName = null,
                               $tableDef = null,
                               $values = PdoOne::NULL)
    {
        if ($tableName === null) {
            $tableName = $this->from;
        } else {
            $this->parent->tables[] = $tableName;
        }
        if ($this->useCache === true) {
            $this->parent->invalidateCache('', $this->cacheFamily);
        }
        if ($tableDef !== null) {
            $this->constructParam2($tableDef, $values, 'set');
        }
        // using builder. from()->set()->insert()
        $errorCause = '';
        if (!$tableName) {
            $errorCause = "you can't execute an empty insert() without a from()";
        }
        if (count($this->set) === 0) {
            $errorCause = "you can't execute an empty insert() without a set()";
        }
        if ($errorCause) {
            $this->throwErrorChain('Insert:' . $errorCause);
            return false;
        }
        //$sql = 'insert into ' . $this->parent->addDelimiter($tableName) . '  (' . implode(',', $col) . ') values('
        //    . implode(',', $colT) . ')';
        $sql
            = /** @lang text */
            'insert into ' . $this->parent->addDelimiter($tableName) . '  ' . $this->constructInsert();
        $param = $this->setParamAssoc;
        $this->beginTry();
        $this->parent->runRawQuery($sql, $param, true, $this->useCache, $this->cacheFamily);
        $this->builderReset(true);
        if ($this->endtry() === false) {
            return false;
        }
        return $this->parent->insert_id();
    }

    /**
     * @return string
     */
    private function constructInsert()
    {
        if (count($this->set)) {
            $arr = [];
            $val = [];
            $first = $this->set[0];
            if (strpos($first, '=') !== false) {
                // set([])
                foreach ($this->set as $v) {
                    $tmp = explode('=', $v);
                    $arr[] = $tmp[0];
                    $val[] = $tmp[1];
                }
                $set = '(' . implode(',', $arr) . ') values (' . implode(',', $val) . ')';
            } else {
                // set('(a,b,c) values(?,?,?)',[])
                foreach ($this->setParamAssoc as $v) {
                    $vn = $v[0];
                    if ($vn[0] !== ':') {
                        $vn = ':' . $vn;
                    }
                    $val[] = $vn;
                }
                $set = '(' . implode(',', $this->set) . ') values (' . implode(',', $val) . ')';
            }
        } else {
            $set = '';
        }

        return $set;
    }

    /**
     * Generates and execute an insert command.<br>
     * <b>Example:</b><br>
     * <pre>
     * insert('table',['col1',10,'col2','hello world']); // simple array: name1,value1,name2,value2..
     * insert('table',null,['col1'=>10,'col2'=>'hello world']); // definition is obtained from the values
     * insert('table',['col1'=>10,'col2'=>'hello world']); // definition is obtained from the values
     * insert('table',['col1','col2'],[10,'hello world']); // definition (binary) and value
     * insert('table',['col1','col2'],['col1'=>10,'col2'=>'hello world']); // definition declarative array)
     *      ->set(['col1',10,'col2','hello world'])
     *      ->from('table')
     *      ->insert();
     *</pre>
     *
     * @param string|array      $tableNameOrValues
     * @param string[]|null     $tableDef
     * @param string[]|int|null $values
     *
     * @return false|int|string Returns the identity (if any) or false if the operation fails.
     * @throws Exception
     */
    public function insert(
        &$tableNameOrValues = null,
        $tableDef = null,
        $values = PdoOne::NULL
    )
    {
        if ($this->ormClass !== null) {
            $cls = $this->ormClass;
            return $cls::setPdoOneQuery($this)::insert($tableNameOrValues);
        }
        return $this->_insert($tableNameOrValues, $tableDef, $values);
    }


    //<editor-fold desc="Encryption functions" defaultstate="collapsed" >

    /**
     * Delete a row(s) if they exists.
     * Example:
     *      delete('table',['col1',10,'col2','hello world']);
     *      delete('table',['col1','col2'],[10,'hello world']);
     *      $db->from('table')
     *          ->where('..')
     *          ->delete() // running on a chain
     *      delete('table where condition=1');
     *
     * @param string|null   $tableOrObject
     * @param string[]|null $tableDefWhere
     * @param string[]|int  $valueWhere
     *
     * @return false|int If successes then it returns the number of rows deleted.
     * @throws Exception
     */
    public function delete(
        $tableOrObject = null,
        $tableDefWhere = null,
        $valueWhere = PdoOne::NULL
    )
    {

        if ($this->ormClass !== null) {
            $cls = $this->ormClass;
            $this->ormClass = null; // toavoid recursivity
            /** @see \eftec\_BasePdoOneRepo::_delete() */
            return $cls::setPdoOneQuery($this)::delete($tableOrObject);
        }
        if ($tableOrObject === null) {
            $tableOrObject = $this->from;
        } else {
            $this->parent->tables[] = $tableOrObject;
        }
        if ($this->useCache === true) {
            $this->parent->invalidateCache('', $this->cacheFamily);
        }
        // using builder. from()->set()->where()->update()
        $errorCause = '';
        if (!$tableOrObject) {
            $errorCause = "you can't execute an empty delete() without a from()";
        }
        if ($errorCause) {
            $this->throwErrorChain('Delete:' . $errorCause);
            $this->parent->endTry();
            return false;
        }
        if ($tableDefWhere !== null) {
            $this->constructParam2($tableDefWhere, $valueWhere);
        }

        /** @noinspection SqlWithoutWhere */
        $sql = 'delete from ' . $this->parent->addDelimiter($tableOrObject);
        $sql .= $this->constructWhere();
        $param = $this->whereParamAssoc;

        $this->beginTry();
        $stmt = $this->parent->runRawQuery($sql, $param, false, $this->useCache, $this->cacheFamily);
        $this->builderReset(true);
        if ($this->endtry() === false) {
            return false;
        }

        return $this->parent->affected_rows($stmt);
    }

    /**
     * Generate and run an update in the database.
     * <br><b>Example</b>:<br>
     * <pre>
     *      update('table',['col1',10,'col2','hello world'],['wherecol',10]);
     *      update('table',['col1','col2'],[10,'hello world'],['wherecol'],[10]);
     *      $this->from("producttype")
     *          ->set("name=?",['Captain-Crunch'])
     *          ->where('idproducttype=?',[6])
     *          ->update();
     *      update('product_category set col1=10 where idproducttype=1')
     * </pre>
     *
     * @param string|null|array $tableOrObject The name of the table or the whole
     *                                         query.
     * @param string[]|null     $tableDef
     * @param string[]|int|null $values
     * @param string[]|null     $tableDefWhere
     * @param string[]|int|null $valueWhere
     *
     * @return false|int
     * @throws Exception
     */
    public function update(
        $tableOrObject = null,
        $tableDef = null,
        $values = PdoOne::NULL,
        $tableDefWhere = null,
        $valueWhere = PdoOne::NULL
    )
    {
        if ($this->ormClass !== null) {
            $cls = $this->ormClass;
            $this->ormClass = null; // toavoid recursivity
            /** @see \eftec\_BasePdoOneRepo::_update() */
            return $cls::setPdoOneQuery($this)::update($tableOrObject);
        }
        if ($tableOrObject === null) {
            // using builder. from()->set()->where()->update()
            $tableOrObject = $this->from;
        } else {
            $this->parent->tables[] = $tableOrObject;
        }
        if ($this->useCache === true) {
            $this->parent->invalidateCache('', $this->cacheFamily);
        }

        if ($tableDef !== null) {
            $this->constructParam2($tableDef, $values, 'set');
        }

        if ($tableDefWhere !== null) {
            $this->constructParam2($tableDefWhere, $valueWhere);
        }

        $errorCause = '';

        if (!$tableOrObject) {
            $errorCause = "you can't execute an empty update() without a from()";
        }
        if (count($this->set) === 0) {
            $errorCause = "you can't execute an empty update() without a set()";
        }
        if ($errorCause) {
            $this->throwErrorChain('Update:' . $errorCause);
            return false;
        }

        $sql = 'update ' . $this->parent->addDelimiter($tableOrObject);
        $sql .= $this->constructSet();
        $sql .= $this->constructWhere();
        $param = array_merge($this->setParamAssoc, $this->whereParamAssoc); // the order matters.

        // $this->builderReset();
        $this->beginTry();
        $stmt = $this->parent->runRawQuery($sql, $param, false, $this->useCache, $this->cacheFamily);
        $this->builderReset(true);
        if ($this->endtry() === false) {
            return false;
        }
        return $this->parent->affected_rows($stmt);
    }

    /**
     * @return string
     */
    private function constructSet()
    {
        return count($this->set) ? ' set ' . implode(',', $this->set) : '';
    }

    /**
     * @return array
     */
    public function getSetParamAssoc()
    {
        return $this->setParamAssoc;
    }

    /**
     * @return array
     */
    public function getWhereParamAssoc()
    {
        return $this->whereParamAssoc;
    }

    /**
     * @return array
     */
    public function getHavingParamAssoc()
    {
        return $this->havingParamAssoc;
    }

    /**
     * It adds an "order by" in a query.<br>
     * <b>Example:</b><br>
     * <pre>
     *      ->select("")->order("column")->toList();
     *      ->select("")->order("col1,col2")->toList();
     * </pre>
     *
     * @param string $sql Input SQL query
     *
     * @return PdoOneQuery
     * @test InstanceOf PdoOne::class,this('name desc')
     */
    public function order($sql)
    {
        if ($sql === null) {
            return $this;
        }
        $this->order = ($sql) ? ' order by ' . $sql : '';

        return $this;
    }

    /**
     * Macro of join.<br>
     * <b>Example</b>:<br>
     * <pre>
     *          innerjoin('tablejoin on t1.field=t2.field')
     *          innerjoin('tablejoin tj on t1.field=t2.field')
     *          innerjoin('tablejoin','t1.field=t2.field')
     * </pre>
     *
     * @param string $sql
     * @param string $condition
     *
     * @return PdoOneQuery
     * @see \eftec\PdoOne::join
     */
    public function innerjoin($sql, $condition = '')
    {
        return $this->join($sql, $condition);
    }

    /**
     * It generates an inner join<br>
     * <b>Example:</b><br>
     * <pre>
     *          join('tablejoin on t1.field=t2.field')<br>
     *          join('tablejoin','t1.field=t2.field')<br>
     * </pre>
     *
     * @param string $sql Example "tablejoin on table1.field=tablejoin.field"
     * @param string $condition
     *
     * @return PdoOneQuery
     * @test InstanceOf PdoOne::class,this('tablejoin on t1.field=t2.field')
     */
    public function join($sql, $condition = '')
    {
        if ($condition !== '') {
            $sql = "$sql on $condition";
        }
        $this->from .= ($sql) ? " inner join $sql " : '';
        $this->parent->tables[] = explode(' ', $sql)[0];

        return $this;
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
     * @param null|bool|int     $ttl    <b>null</b> then the cache never expires.<br>
     *                                  <b>false</b> then we don't use cache.<br>
     *                                  <b>int</b> then it is the duration of the cache (in seconds)
     * @param string|array|null $family [optional] It is the family or group of the cache. It could be used to
     *                                  identify a group of cache to invalidate the whole group (for example
     *                                  ,invalidate all cache from a specific table).<br>
     *                                  <b>*</b> If "*" then it uses the tables assigned by from() and join()
     *
     * @return $this
     * @see \eftec\PdoOne::invalidateCache
     */
    public function useCache($ttl = 0, $family = '')
    {
        if ($this->ormClass !== null && $ttl !== false) {
            $cls = $this->ormClass;
            if ($family === '') {
                $family = $cls::setPdoOneQuery($this)::getRecursiveClass();
            }
            //return $cls::setPdoOneQuery($this)::(PdoOne::NULL, null);
        }
        if ($this->parent->cacheService === null) {
            $ttl = false;
        }
        $this->cacheFamily = $family;
        $this->useCache = $ttl;
        return $this;
    }
}