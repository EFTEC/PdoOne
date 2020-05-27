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
 *
 * @version       4.0.1 2020-05-27
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
    public static function createTable($extra = null)
    {
        if (!self::getPdoOne()->tableExist(static::TABLE)) {
            return self::getPdoOne()->createTable(static::TABLE, $definition = static::getDef(), static::getDefKey(),
                $extra);
        }
        return false; // table already exist
    }

    public static function createForeignKeys()
    {
        $def = static::getDefFK(true);
        $def2 = static::getDefFK(false);
        foreach ($def as $k => $v) {
            $sql = 'ALTER TABLE ' . self::getPdoOne()->addQuote(static::TABLE) . ' ADD CONSTRAINT ' . self::getPdoOne()
                    ->addQuote($def2[$k]['name']) . ' ' . $v;
            $sql = str_ireplace('FOREIGN KEY REFERENCES',
                'FOREIGN KEY(' . self::getPdoOne()->addQuote($k) . ') REFERENCES', $sql);
            self::getPdoOne()->runRawQuery($sql, [], true);
        }
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
    public static function setPdoOne($pdoOne)
    {
        self::$pdoOne = $pdoOne;
    }

    /**
     * It creates a foreign keys<br>
     *
     * @return array|bool|PDOStatement
     * @throws Exception
     */
    public static function createFk()
    {
        return self::getPdoOne()->createFk(static::TABLE, static::getDefFk());
    }

    /**
     * It validates the table and returns an associative array with the errors.
     *
     * @return array If valid then it returns an empty array
     * @throws Exception
     */
    public static function validTable()
    {
        //try {
        return self::getPdoOne()->validateDefTable(static::TABLE, static::getDef(), static::getDefKey(),
            static::getDefFk());
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
    public static function truncate()
    {
        return self::getPdoOne()->truncate(static::TABLE);
    }

    /**
     * It drops the table (structure and values)
     *
     * @return array|bool|PDOStatement
     * @throws Exception
     */
    public static function dropTable()
    {
        if (!self::getPdoOne()->tableExist(static::TABLE)) {
            return self::getPdoOne()->dropTable(static::TABLE);
        }
        return false; // table does not exist
    }


    /*protected static function _merge($entity) {
        $entity = self::intersectArrays($entity,static::getDef(true));
        $identities = static::getDefIdentity();
        foreach ($entity as $k => $v) {
            // identities are not inserted
            if (in_array($k, $identities, true)) {
                unset($entity[$k]);
            }
        }
        //$pdo->select('1')->from(static::TABLE)->where()
    }
    */

    protected static function _exist($entity)
    {
        $pks = static::PK;
        if (is_array($entity)) {
            foreach ($entity as $k => $v) { // we keep the pks
                if (!in_array($k, $pks, true)) {
                    unset($entity[$k]);
                }
            }
        } elseif (is_array($pks) && count($pks)) {
            $entity = [$pks[0] => $entity];
        } else {
            self::getPdoOne()->throwError('exist: entity not specified as an array or table lacks of PKs', $entity);
            return false;
        }
        $r = self::getPdoOne()->genError(false)->select('1')->from(static::TABLE)->where($entity)->firstScalar();
        self::getPdoOne()->genError(true);
        return ($r === '1');
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

    protected static function _toList($filter, $filterValue)
    {
        return self::generationStart('toList', $filter, $filterValue);
    }

    protected static function generationStart($type, $filter = null, $filterValue = null)
    {
        static::$gQuery = [];
        static::$gQueryCounter = 0;
        $newQuery = [];
        $newQuery['type'] = 'QUERY';
        static::$gQuery[0] =& $newQuery;
        $newQuery['joins'] = static::TABLE . " as t0 \n";
        // we build the query
        static::generationRecursive($newQuery, 't0.', '', '', false);

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
                            $pdoOne->select($cols)->from($from)->where($filter)->first()
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
                    $partialRows = $pdoOne->select($cols)->from($from)->where($query['where'], $row[$query['col']])
                        ->toList();
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
        if (!is_array($rows)) {
            return $rows;
        }
        $c = count($rows);
        $rowc = [];
        for ($i = 0; $i < $c; $i++) {
            $rowc[$i] = self::convertRow($rows[$i]);
        }
        return $rowc;
    }

    protected static function generationRecursive(
        &$newQuery,
        $pTable = '',
        $pColumn = '',
        $recursiveInit = '',
        $new = false
    ) {
        $cols = array_keys(static::getDef());
        $keyRels = static::getDefFK(false);
        //$newQuery=[];
        // add columns of the current table
        foreach ($cols as $col) {
            $newQuery['columns'][] = $pTable . $col . ' as ' . self::getPdoOne()->addQuote($pColumn . $col);
        }
        $ns = self::getNamespace();

        foreach ($keyRels as $nameCol => $keyRel) {
            $type = $keyRel['key'];
            $nameColClean = trim($nameCol, '/');
            if (self::getPdoOne()->hasRecursive($recursiveInit . $nameCol)) {
                // type='PARENT' is n
                switch ($type) {
                    case 'MANYTOONE':
                    case 'ONETOONE':
                        static::$gQueryCounter++;
                        $tableRelAlias = 't' . static::$gQueryCounter; //$prefixtable.$nameColClean;
                        $colRelAlias = $pColumn . $nameCol;
                        $class = $ns . $keyRel['reftable'] . 'Repo';
                        $refCol = $keyRel['refcol'];
                        $newQuery['joins'] .= " left join {$keyRel['reftable']} as $tableRelAlias "
                            . "on $pTable$nameColClean=$tableRelAlias.$refCol \n"; // $recursiveInit$nameCol\n"; // adds a query to the current query
                        $class::generationRecursive($newQuery, $tableRelAlias . '.', $colRelAlias . '.',
                            $recursiveInit . $nameCol, false);
                        break;
                    case 'ONETOMANY':
                    case 'MANYTOMANY':
                        if ($type === 'MANYTOMANY') {
                            $rec = self::getPdoOne()->getRecursive();
                            // automatically we add recursive.
                            $rec[] = $recursiveInit . $nameCol . $keyRel['refcol2'];
                            self::getPdoOne()->recursive($rec);
                        }

                        //$tableRelAlias = ''; //'t' . static::$gQueryCounter;
                        $other = [];
                        $refColClean = trim($keyRel['refcol'], '/');
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
                        $class = $ns . $keyRel['reftable'] . 'Repo';

                        $class::generationRecursive($other, '', '', $pColumn . $recursiveInit . $nameCol, false);

                        if ($type === 'MANYTOMANY') {
                            // we reduce a level
                            $columns = $other['columns'];
                            $columnFinal = [];
                            // convert /somefk.column -> column
                            // convert /anything.column -> (deleted)
                            foreach ($columns as $vc) {
                                $findme = $keyRel['refcol2'] . '.';
                                if (strpos($vc, $findme) !== false) {
                                    $columnFinal[] = str_replace($findme, '', $vc);
                                }
                            }
                            $other['columns'] = $columnFinal;
                        }
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
        if ($new) {
            self::$gQuery[] = $newQuery;
        }
    }

    /**
     * Insert an new row
     *
     * @param array $entity =static::factory()
     *
     * @param bool  $transaction
     *
     * @return mixed
     * @throws Exception
     */
    protected static function _insert(&$entity, $transaction = true)
    {
        $pdo = self::getPdoOne();
        $recursiveBack = $pdo->getRecursive();  // recursive is deleted by insertObject
        $entityCopy = self::intersectArrays($entity,
            static::getDef(true)); // only the fields that are defined are inserted
        $entityCopy = self::diffArrays($entityCopy, static::getDefIdentity()); // identities are not inserted
        if ($transaction) {
            $pdo->startTransaction();
        }
        $insert = $pdo->insertObject(static::TABLE, $entityCopy);
        $pks = $pdo->getDefTableKeys(static::TABLE, true, 'PRIMARY KEY');
        if (count($pks) > 0) {
            // we update the identity of $entity ($entityCopy is already updated).
            $entity[array_keys($pks)[0]] = $insert;
        }
        $defs = static::getDefFK();
        $ns = self::getNamespace();

        foreach ($defs as $key => $def) { // ['/tablaparentxcategory']=['key'=>...]
            if ($def['key'] === 'MANYTOMANY' && isset($entity[$key]) && is_array($entity[$key])) {
                $class2 = $ns . ucfirst($def['table2']) . 'Repo';
                foreach ($entity[$key] as $item) {
                    $pk2 = $item[$def['col2']];
                    if ($pdo->hasRecursive($key, $recursiveBack) && $class2::exist($item) === false) {
                        // we only update it if it has a recursive
                        $pk2 = $class2::insert($item, false);
                    }
                    $classRel = $ns . $def['reftable'] . 'Repo';
                    $refCol = ltrim($def['refcol'], '/');
                    $refCol2 = ltrim($def['refcol2'], '/');
                    $relationalObj = [$refCol => $entityCopy[$def['col']], $refCol2 => $pk2];
                    $classRel::insert($relationalObj, false);
                }
            }
        }
        if ($transaction) {
            self::getPdoOne()->commit();
        }
        return $insert;
    }

    /**
     * Update an registry
     *
     * @param array $entity =static::factory()
     *
     * @param bool  $transaction
     *
     * @return mixed
     * @throws Exception
     */
    protected static function _update($entity, $transaction = true)
    {
        $pdo = self::getPdoOne();

        $entityCopy = self::intersectArrays($entity,
            static::getDef(true)); // only the fields that are defined are inserted
        $entityCopy = self::diffArrays($entityCopy, static::getDefIdentity()); // identities are not inserted

        if ($transaction) {
            $pdo->startTransaction();
        }
        $recursiveBack = $pdo->getRecursive();
        $r = $pdo->from(static::TABLE)->set($entityCopy)->where(static::intersectArrays($entity, static::PK))->update();
        $pdo->recursive($recursiveBack); // update() delete recursive
        $defs = static::getDefFK();
        $ns = self::getNamespace();
        foreach ($defs as $key => $def) { // ['/tablaparentxcategory']=['key'=>...]

            if ($def['key'] === 'MANYTOMANY') { //hasRecursive($recursiveInit . $key)
                if (!isset($entity[$key]) || !is_array($entity[$key])) {
                    $newRows = [];
                } else {
                    $newRows = $entity[$key];
                }
                $classRef = $ns . ucfirst($def['reftable']) . 'Repo';
                $class2 = $ns . ucfirst($def['table2']) . 'Repo';
                $col1 = ltrim($def['col'], '/');
                $refcol = ltrim($def['refcol'], '/');
                $refcol2 = ltrim($def['refcol2'], '/');
                $col2 = ltrim($def['col2'], '/');
                $newRowsKeys = [];
                foreach ($newRows as $v) {
                    $newRowsKeys[] = $v[$col2];
                }
                //new dBug($newRowsKeys);
                //new dBug($def);
                //self::setRecursive([$def['refcol2']]);
                self::setRecursive([]);
                $oldRows = ($classRef::where($refcol, $entity[$col1]))::toList();
                $oldRowsKeys = [];
                foreach ($oldRows as $v) {
                    $oldRowsKeys[] = $v[$refcol2];
                }
                // new dBug($oldRowsKeys);
                $insertKeys = array_diff($newRowsKeys, $oldRowsKeys);
                $deleteKeys = array_diff($oldRowsKeys, $newRowsKeys);
                // new dBug($insertKeys);
                // new dBug($deleteKeys);
                // inserting a new value
                foreach ($newRows as $item) {
                    if (in_array($item[$col2], $insertKeys)) {
                        $pk2 = $item[$def['col2']];
                        if ($class2::exist($item) === false && self::getPdoOne()->hasRecursive($key)) {
                            $pk2 = $class2::insert($item, false);
                        } else {
                            $class2::update($item, false);
                        }
                        $relationalObjInsert = [$refcol => $entity[$def['col']], $refcol2 => $pk2];
                        //new dBug($relationalObjInsert);
                        $classRef::insert($relationalObjInsert, false);
                    }
                }
                // delete
                foreach ($newRows as $item) {
                    if (in_array($item[$col2], $deleteKeys)) {
                        $pk2 = $item[$def['col2']];
                        if (self::getPdoOne()->hasRecursive($key)) {
                            $class2::deleteById($item, $pk2);
                        }
                        $relationalObjDelete = [$refcol => $entity[$def['col']], $refcol2 => $pk2];
                        //new dBug($relationalObjDelete);
                        $classRef::deleteById($relationalObjDelete, false);
                    }
                }
            }
        }


        if ($transaction) {
            self::getPdoOne()->commit();
        }

        return $r;
    }

    /**
     * It deletes a registry
     *
     * @param array $entity
     * @param bool  $transaction
     *
     * @return mixed
     * @throws Exception
     */
    protected static function _delete($entity, $transaction = true)
    {
        $entityCopy = self::intersectArraysNotNull($entity, static::getDef(), true);
        $pdo = self::getPdoOne();
        if ($transaction) {
            $pdo->startTransaction();
        }

        $defs = static::getDefFK();
        $ns = self::getNamespace();

        $recursiveBackup = self::getRecursive();

        foreach ($defs as $key => $def) { // ['/tablaparentxcategory']=['key'=>...]

            if ($def['key'] === 'MANYTOMANY' && isset($entity[$key])
                && is_array($entity[$key])
            ) { //hasRecursive($recursiveInit . $key)
                $classRef = $ns . ucfirst($def['reftable']) . 'Repo';
                $class2 = $ns . ucfirst($def['table2']) . 'Repo';

                $col1 = ltrim($def['col'], '/');
                $refcol = ltrim($def['refcol'], '/');
                //$refcol2 = ltrim($def['refcol2'], '/');
                $col2 = $def['col2'];

                //new dBug($newRowsKeys);
                //new dBug($def);
                //self::setRecursive([$def['refcol2']]);
                self::setRecursive([]);


                $cols2 = [];
                foreach ($entity[$key] as $item) {
                    $cols2[] = $item[$col2];
                }
                //new dBug($cols2);
                $relationalObjDelete = [$refcol => $entity[$col1]];
                //new dBug($relationalObjDelete);
                $classRef::delete($relationalObjDelete, false);
                var_dump($pdo->lastQuery);

                if (self::getPdoOne()->hasRecursive($key, $recursiveBackup)) {
                    foreach ($cols2 as $c2) {
                        // $k = $v[$refcol2];
                        $object2Delete = [$col2 => $c2];
                        $class2::delete($object2Delete, false);
                        //new dBug($object2Delete);
                    }
                }
                self::setRecursive($recursiveBackup);
            }
        }
        //new dBug("delete entity copy " . static::TABLE);
        //new dBug($entityCopy);
        $r = self::getPdoOne()->delete(static::TABLE, $entityCopy);


        if ($transaction) {
            //self::getPdoOne()->rollback();
            self::getPdoOne()->commit();
        }
        return $r;
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

    public static function intersectArraysNotNull($arrayValues, $arrayIndex, $indexIsKey = false)
    {
        $result = [];

        foreach ($arrayIndex as $k => $v) {
            if ($indexIsKey) {
                if (isset($arrayValues[$k])) {
                    $result[$k] = $arrayValues[$k];
                }
            } elseif (isset($arrayValues[$v])) {
                $result[$v] = $arrayValues[$v];
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
        return self::_delete($pksI, $transaction);
    }

    /**
     * It gets a registry using the primary key.
     *
     * @param mixed $pk If mixed
     *
     * @return array|bool static::factory()
     * @throws Exception
     */
    protected static function _first($pk = null)
    {
        $pk = is_array($pk) ? $pk : [static::PK[0] => $pk];
        $r = self::generationStart('first', $pk);
        if (is_array($r)) {
            return $r[0];
        }
        return $r;
    }

    public static function getNamespace()
    {
        if (strpos(static::class, '\\')) { // we assume that every repo class lives in the same namespace.
            $ns = explode('\\', static::class);
            array_pop($ns);
            $ns =implode('\\',$ns).'\\';
        } else {
            $ns = '';
        }
        return $ns;
    }

    public static function getRecursive()
    {
        return self::getPdoOne()->getRecursive();
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
     * ProductRepo::setRecursive(['/ProductExtension'])::toList(); // select .. from Product inner join ProductExtension
     * </pre>
     * - If the relation is manytomany, then the system load the relational table (always, not matter the recursivity), 
     * and it reads/insert/update the next values only if the value is marked as recursive. Example:<br>
     * <pre>
     * ProductRepo::setRecursive(['/product_x_category'])::toList(); // it returns porduct, productxcategory and category
     * ProductRepo::setRecursive([])->toList(); // it returns porduct and productxcategory (if /productcategory is marked as
     * manytomany)
     * </pre>
     *
     * 
     * @param array $recursive
     *
     * @return self
     * @see static::getDefFK for where to define the relation.        
     */
    public static function setRecursive($recursive)
    {
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
     * @param null   $ttl
     * @param string $family
     *
     * @return self
     */
    public static function useCache($ttl = null, $family = '')
    {
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
     *
     * @return self
     */
    public static function invalidateCache($family = '')
    {
        self::getPdoOne()->invalidateCache('', $family);
        return static::ME;
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
     * @param array|string   $sql =static::factory()
     * @param null|array|int $param
     *
     * @return static
     */
    public static function where($sql, $param = PdoOne::NULL)
    {
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
    public static function count($where = null)
    {
        return (int)self::getPdoOne()->count()->from(static::TABLE)->where($where)->firstScalar();
    }

    /**
     * @param $sql
     * @param $param
     *
     * @return self
     */
    public function having($sql, $param = self::NULL)
    {
        self::getPdoOne()->having($sql, $param);
        return static::ME;
    }

}