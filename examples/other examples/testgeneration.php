<?php /** @noinspection PhpUnhandledExceptionInspection */

use eftec\PdoOne;

include '../vendor/autoload.php';

// connecting to database sakila at 127.0.0.1 with user root and password abc.123

$dao = new PdoOne('mysql', '127.0.0.1', 'root', 'abc.123', 'sakila', 'logpdoone.txt');
$dao->logLevel = 3;
$dao->throwOnError = true;
$dao->open();
//header("Content-Type: plain/text");
echo $dao->generateAbstractRepo('tablachild');

die(1);
actorRepo::setPdoOne($dao);
//actorRepo::createTable();
echo "<pre>";
var_dump(actorRepo::validTable());
echo "</pre>";
die(1);

/*function pdoOne() {
    global $dao;
    return $dao;
}
*/
CustomersRepo::setPdoOne($dao);
CustomersRepo::dropTable();
CustomersRepo::createTable([
    'CustomerId' => 'int not null',
    'Name' => 'varchar(50)',
    'Enabled' => 'int not null default 1'
]);
CustomersRepo::truncate();
// array(['CustomerId'=>0, 'Name'=>'', 'Enabled'=>0])

CustomersRepo::insert(['CustomerId' => 1, 'Name' => 'John', 'Enabled' => 1]);
CustomersRepo::insert(['CustomerId' => 2, 'Name' => 'Anna', 'Enabled' => 0]);
CustomersRepo::insert(['CustomerId' => 3, 'Name' => 'Peter', 'Enabled' => 0]);

CustomersRepo::update(['CustomerId' => 2, 'Name' => 'ANNAAA', 'Enabled' => 0]);
CustomersRepo::delete(3);
$x=CustomersRepo::get(1);


var_dump(CustomersRepo::get(1));
echo '<pre>';
$list=CustomersRepo::select();

var_dump($list);

var_dump(CustomersRepo::select(['Enabled' => 1]));
echo '</pre>';
var_dump(CustomersRepo::count());

/**
 * Generated by PdoOne Version 1.1
 * Class CustomersRepo
 */
class CustomersRepo
{
    const TABLE = 'Customers';
    const PK = 'CustomerId';
    /** @var PdoOne */
    public static $pdoOne = null;

    /**
     * @param array $definition
     * @param null  $extra
     *
     * @return array|bool|PDOStatement
     * @throws Exception
     */
    public static function createTable($definition, $extra = null) {
        if (!self::getPdoOne()->tableExist(self::TABLE)) {
            return self::getPdoOne()->createTable(self::TABLE, $definition, self::PK, $extra);
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
     * It cleans the whole table (delete all rows)
     *
     * @return array|bool|PDOStatement
     * @throws Exception
     */
    public static function truncate() {
        return self::getPdoOne()->truncate(self::TABLE);
    }

    /**
     * It drops the table (structure and values)
     *
     * @return array|bool|PDOStatement
     * @throws Exception
     */
    public static function dropTable() {
        if (!self::getPdoOne()->tableExist(self::TABLE)) {
            return self::getPdoOne()->dropTable(self::TABLE);
        }
        return false; // table does not exist
    }

    /**
     * Insert an new row
     *
     * @param array $obj =array('CustomerId'=>0, 'Name'=>'', 'Enabled'=>0)
     *
     * @return mixed
     * @throws Exception
     */
    public static function insert($obj) {
        return self::getPdoOne()->insertObject(self::TABLE, $obj);
    }

    /**
     * Update an registry
     *
     * @param array $obj =array('CustomerId'=>0, 'Name'=>'', 'Enabled'=>0)
     *
     * @return mixed
     * @throws Exception
     */
    public static function update($obj) {
        return self::getPdoOne()->from(self::TABLE)
            ->set($obj)
            ->where(self::PK, $obj[self::PK])
            ->update();
    }

    /**
     * It delete a registry
     *
     * @param mixed $pk
     *
     * @return mixed
     * @throws Exception
     */
    public static function delete($pk) {
        return self::getPdoOne()->from(self::TABLE)
            ->where(self::PK, $pk)
            ->delete();
    }

    /**
     * It gets a registry using the primary key.
     *
     * @param mixed $pk
     *
     * @return ['CustomerId'=>0, 'Name'=>'', 'Enabled'=>0]
     * @throws Exception
     */
    public static function get($pk) {
        return self::getPdoOne()->select('*')
            ->from(self::TABLE)
            ->where(self::PK, $pk)
            ->first();
    }

    /**
     * It returns a list of rows
     *
     * @param null|array $where =array('CustomerId'=>0, 'Name'=>'', 'Enabled'=>0)
     * @param null|string $order
     * @param null|string $limit
     *
     * @return [['CustomerId'=>0, 'Name'=>'', 'Enabled'=>0]]
     * @throws Exception
     */
    public static function select($where = null, $order = null, $limit = null) {
        return self::getPdoOne()->select('*')
            ->from(self::TABLE)
            ->where($where)
            ->order($order)
            ->limit($limit)
            ->toList();
    }

    /**
     * It returns the number of rows
     *
     * @param null|array $where =array('CustomerId'=>0, 'Name'=>'', 'Enabled'=>0)
     *
     * @return int
     * @throws Exception
     */
    public static function count($where = null) {
        return (int)self::getPdoOne()->count()
            ->from(self::TABLE)
            ->where($where)
            ->firstScalar();
    }
}



/**
 * Generated by PdoOne Version 1.28.1
 * Class actorRepo
 */
class actorRepo
{
    const TABLE = 'actor2';
    const PK = 'actor_id';

    const DEF=array (
        'actor_id' => 'smallint unsigned not null auto_increment',
        'first_name' => 'varchar(45) not null default \'ABC 123\'',
        'last_name' => 'varchar(45) not null',
        'last_update' => 'timestamp not null default CURRENT_TIMESTAMP on update CURRENT_TIMESTAMP',
    );


    /** @var PdoOne */
    public static $pdoOne = null;

    /**
     * It validates the table and returns an associative array with the errors.
     *
     * @return array If valid then it returns an empty array
     * @throws Exception
     */
    public function validTable() {
        return $this->validateDefTable(self::TABLE,self::DEF);
    }

    /**
     * It creates a new table<br>
     * If the table exists then the operation is ignored (and it returns false)
     *
     * @param array $definition
     * @param null  $extra
     *
     * @return array|bool|PDOStatement
     * @throws Exception
     */
    public static function createTable($definition=null, $extra = null) {
        if (!self::getPdoOne()->tableExist(self::TABLE)) {
            if($definition===null) $definition=self::DEF;
            return self::getPdoOne()->createTable(self::TABLE, $definition, self::PK, $extra);
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
     * It cleans the whole table (delete all rows)
     *
     * @return array|bool|PDOStatement
     * @throws Exception
     */
    public static function truncate() {
        return self::getPdoOne()->truncate(self::TABLE);
    }

    /**
     * It drops the table (structure and values)
     *
     * @return array|bool|PDOStatement
     * @throws Exception
     */
    public static function dropTable() {
        if (!self::getPdoOne()->tableExist(self::TABLE)) {
            return self::getPdoOne()->dropTable(self::TABLE);
        }
        return false; // table does not exist
    }

    /**
     * Insert an new row
     *
     * @param array $obj =["actor_id"=>0,"first_name"=>'',"last_name"=>'',"last_update"=>'']
     *
     * @return mixed
     * @throws Exception
     */
    public static function insert($obj) {
        return self::getPdoOne()->insertObject(self::TABLE, $obj);
    }

    /**
     * Update an registry
     *
     * @param array $obj =["actor_id"=>0,"first_name"=>'',"last_name"=>'',"last_update"=>'']
     *
     * @return mixed
     * @throws Exception
     */
    public static function update($obj) {
        return self::getPdoOne()->from(self::TABLE)
            ->set($obj)
            ->where(self::PK, $obj[self::PK])
            ->update();
    }

    /**
     * It deletes a registry
     *
     * @param mixed $pk
     *
     * @return mixed
     * @throws Exception
     */
    public static function delete($pk) {
        return self::getPdoOne()->from(self::TABLE)
            ->where(self::PK, $pk)
            ->delete();
    }

    /**
     * It gets a registry using the primary key.
     *
     * @param mixed $pk
     *
     * @return ["actor_id"=>0,"first_name"=>'',"last_name"=>'',"last_update"=>'']
     * @throws Exception
     */
    public static function get($pk) {
        return self::getPdoOne()->select('*')
            ->from(self::TABLE)
            ->where(self::PK, $pk)
            ->first();
    }

    /**
     * It returns a list of rows
     *
     * @param null|array $where =["actor_id"=>0,"first_name"=>'',"last_name"=>'',"last_update"=>'']
     * @param null|string $order
     * @param null|string $limit
     *
     * @return [["actor_id"=>0,"first_name"=>'',"last_name"=>'',"last_update"=>'']]
     * @throws Exception
     */
    public static function select($where = null, $order = null, $limit = null) {
        return self::getPdoOne()->select('*')
            ->from(self::TABLE)
            ->where($where)
            ->order($order)
            ->limit($limit)
            ->toList();
    }

    /**
     * It returns the number of rows
     *
     * @param null|array $where =["actor_id"=>0,"first_name"=>'',"last_name"=>'',"last_update"=>'']
     *
     * @return int
     * @throws Exception
     */
    public static function count($where = null) {
        return (int)self::getPdoOne()->count()
            ->from(self::TABLE)
            ->where($where)
            ->firstScalar();
    }
}
