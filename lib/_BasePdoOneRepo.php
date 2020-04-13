<?php /** @noinspection PhpUnused */
/** @noinspection PhpUndefinedMethodInspection */

/** @noinspection PhpUndefinedClassConstantInspection */


namespace eftec;


use Exception;
use PDOStatement;

/**
 * Class _BaseRepo
 * @version       1.2
 * @package       eftec
 * @author        Jorge Castro Castillo
 * @copyright (c) Jorge Castro C. MIT License  https://github.com/EFTEC/PdoOne
 */
abstract class _BasePdoOneRepo
{
    /** @var PdoOne */
    public static $pdoOne = null;


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
     * if the globla variable $pdoOne exists, then it is used<br>
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
     * @param mixed $pk
     *
     * @return array static::factory()
     * @throws Exception
     */
    public static function findById($pk) {
        return self::getPdoOne()->select('*')->from(static::TABLE)->where(static::PK, $pk)->first();
    }

    /**
     * It returns a list of rows
     *
     * @param null|array $where =static::factory()
     *
     * @return array [static::factory()]
     * @throws Exception
     */
    public static function findAll($where = null) {
        self::getPdoOne()->select('*')->from(static::TABLE)->where($where);
        return self::getPdoOne()->toList();
    }

    /**
     * @param null $ttl
     * @param string $family
     * @return self
     */
    public static function useCache($ttl = null, $family = '') {
        self::getPdoOne()->useCache($ttl, $family);
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
     * @param $sql
     * @return self
     */
    public static function right($sql) {
        self::getPdoOne()->right($sql);
        return static::ME;
    }

    /**
     * @param $sql
     * @return self
     */
    public static function group($sql) {
        self::getPdoOne()->group($sql);
        return static::ME;
    }

    /**
     * @param $sql
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