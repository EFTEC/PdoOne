<?php /** @noinspection PhpUnused */
/** @noinspection PhpRedundantVariableDocTypeInspection */
/** @noinspection SqlDialectInspection */
/** @noinspection SqlNoDataSourceInspection */
/** @noinspection PhpConditionAlreadyCheckedInspection */

namespace eftec;

use DateTime;
use eftec\ext\PdoOne_IExt;
use eftec\ext\PdoOne_Mysql;
use eftec\ext\PdoOne_Oci;
use eftec\ext\PdoOne_Sqlsrv;
use eftec\ext\PdoOne_TestMockup;
use Exception;
use PDO;
use PDOStatement;
use RuntimeException;
use stdClass;

/**
 * Class PdoOne
 * This class wrappes PDO, but it could be used for another framework/library.
 *
 * @see           https://github.com/EFTEC/PdoOne
 * @package       eftec
 * @author        Jorge Castro Castillo
 * @copyright (c) Jorge Castro C. Dual Licence: MIT and Commercial License  https://github.com/EFTEC/PdoOne
 * @version       3.5
 */
class PdoOne
{
    public const VERSION = '3.5';
    /** @var int We need this value because null and false could be a valid value. */
    public const NULL = PHP_INT_MAX;
    /** @var string Prefix of the tables */
    public static $prefixBase = '_';
    /** @var int Used for the method page() */
    public static $pageSize = 20;
    /** @var string|null Static date (when the date is empty) */
    public static $dateEpoch = '2000-01-01 00:00:00.00000';
    /**
     * Text date format
     *
     * @var string
     * @see https://secure.php.net/manual/en/function.date.php
     */
    public static $dateFormat = 'Y-m-d'; // we don't need to set the epoch to 1970.
    public static $dateHumanFormat = 'd/m/Y';
    /**
     * Text datetime format
     *
     * @var string
     * @see https://secure.php.net/manual/en/function.date.php
     */
    public static $dateTimeFormat = 'Y-m-d\TH:i:s\Z';
    public static $dateTimeHumanFormat = 'd/m/Y H:i:s';

    //<editor-fold desc="server fields">
    /**
     * Text datetime format with microseconds
     *
     * @var string
     * @see https://secure.php.net/manual/en/function.date.php
     */
    public static $dateTimeMicroFormat = 'Y-m-d\TH:i:s.u\Z';
    public static $dateTimeMicroHumanFormat = 'd/m/Y H:i:s.u';
    /** @var string This format is used to determine how the database will return a date */
    public static $isoDate = 'Y-m-d';
    public static $isoDateTimeMs = 'Y-m-d H:i:s.u';
    public static $isoDateTime = 'Y-m-d H:i:s';
    /** @var string This format is used to determine how the database expect a date value */
    public static $isoDateInput = '';
    public static $isoDateInputTimeMs = '';
    public static $isoDateInputTime = '';
    public $internalCacheCounter = 0;
    public $internalCache = [];
    /** @var int nodeId It is the identifier of the node. It must be between 0..1023 */
    public $nodeId = 1;
    public $tableSequence = 'snowflake';
    /**
     * it is used to generate an unpredictable number by flipping positions. It
     * must be changed.
     * $mask0 and $mask1 must have the same number of elements.
     * Each value must be from 0..17 (the size of snowflake, if it is used with
     * snowflake)
     * $masks0=[0] and masks1[3] means that 01234->31204
     * number 14,15,16,17 ($masks1) has the highest entrophy
     *
     * @var array
     * @see \eftec\PdoOne::getUnpredictable
     */
    public $masks0 = [2, 0, 4, 5];
    public $masks1 = [16, 13, 12, 11];
    /** @var PdoOneEncryption */
    public $encryption;
    /** @var string=['mysql','sqlsrv','test','oci'][$i] */
    public $databaseType;
    /** @var string It is generated and set automatically by the type of database */
    public $database_delimiter0 = '`';
    /** @var string It is generated and set automatically by the type of database */
    public $database_delimiter1 = '`';
    /** @var string It is generated and set automatically by the type of database */
    public $database_identityName = 'identity';
    /** @var string server ip. Ex. 127.0.0.1 127.0.0.1:3306 */
    public $server;
    public $user;
    /** @var null|string the unique id generate by sha256or $hashtype and based in the query, arguments, type
     * and methods
     */
    public $uid;
    public $lastBindParam = [];
    public $pwd;
    /** @var string The name of the database/schema */
    public $db;
    /** @var string the name of the locker */
    public $lockerId;
    public $charset = 'utf8';
    /** @var bool It is true if the database is connected otherwise,it's false */
    public $isOpen = false;
    /**
     * @var bool If true (default), then it throws an error if happens an error. If false, then the execution continues
     */
    public $throwOnError = true;
    /**
     * @var bool If true (default), then it throws a customer message.. If false, then it uses the default (PHP) style
     */
    public $customError = true;
    /** @var string[] PHP classes excluded by the custom error log todo: quitar */
    public $traceBlackList = []; //['PdoOne.php', 'PdoOneQuery.php', 'PdoOne_Mysql.php', 'PdoOne.Sqlsrv.php', 'PdoOne.Oci.php'
        //, 'PdoOneTestMockup.php', '_BasePdoOneRepo.php'];
    /** @var  PDO */
    public $conn1;
    /** @var  bool True if the transaction is open */
    public $transactionOpen;
    /** @var bool if the database is in READ ONLY mode or not. If true then we must avoid to write in the database. */
    public $readonly = false;
    /** @var boolean if not false then it logs the file using the php log file (if enabled) */
    private $logFile = false;
    /** @var string It stores the last error. runGet and beginTry resets it */
    public $errorText = '';
    public $isThrow = false;
    /** @var int=[0,1,2,3,4][$i]
     * <b>0</b>=no debug for production (all messages of errors are generic)<br>
     * <b>1</b>=it shows an error message<br>
     * <b>2</b>=it shows the error messages and the last query<br>
     * <b>3</b>=it shows the error messages, the last query, the trace and the last parameters (if any).
     * Note: it could show passwords<br>
     */
    public $logLevel = 0;
    /** @var string last query executed */
    public $lastQuery;
    public $lastParam = [];
    /** @var array the tables used in the queries and added by the methods from() and join() */
    public $tables = [];
    public $useInternalCache = false;
    /**
     * @var array
     * @see \eftec\PdoOne::generateCodeClassConversions
     * @see \eftec\PdoOne::generateAbstractRepo
     */
    public $codeClassConversion = [];
    //</editor-fold>
    public $genError = true;
    /** @var int */
    public $affected_rows = 0;
    /** @var PdoOne_IExt */
    public $service;
    /** @var IPdoOneCache The service of cache [optional] */
    public $cacheService;
    /** @var null|array it stores the values obtained by $this->tableDependency() */
    public $tableDependencyArrayCol;
    public $tableDependencyArray;
    /** @var null|array $partition is an associative array [column=>value] with a fixed and pre-established conditions */
    public $partition;
    /** @var MessageContainer it stores the messages. */
    private $messageContainer;

    protected $phpstart = "<?php\n";

    /** @var PdoOne */
    protected static $instance;


    /**
     * PdoOne constructor.  It doesn't open the connection to the database.
     *
     * @param string      $databaseType =['mysql','sqlsrv','oci','test'][$i]
     * @param string      $server       server ip. Ex. 127.0.0.1 127.0.0.1:3306<br>
     *                                  In 'oci' it could be 'orcl' or 'localhost/orcl' (instance name) or <br>
     *                                  (DESCRIPTION=(ADDRESS=(PROTOCOL=TCP)(HOST=localhost)(PORT=1521))<br>
     *                                  (CONNECT_DATA=(SERVICE_NAME=ORCL)))
     * @param string      $user         Ex. root.  In 'oci' the user is set in uppercase.
     * @param string      $pwd          Ex. 12345
     * @param string      $db           Ex. mybase. In 'oci' this value is ignored, and it uses $user
     * @param bool        $logFile      if true then it stores the error in the php log file (if any)
     * @param string|null $charset      Example utf8mb4
     * @param int         $nodeId       It is the id of the node (server). It is used
     *                                  for sequence. Form 0 to 1023
     *
     * @see PdoOne::connect()
     */
    public function __construct(
        string  $databaseType,
        string  $server,
        string  $user,
        string  $pwd,
        string  $db = '',
        bool    $logFile = false,
        ?string $charset = null,
        int     $nodeId = 1
    )
    {
        $this->construct($databaseType, $server, $user, $pwd, $db, $logFile, $charset, $nodeId);
        if (!class_exists('eftec\MessageContainer')) {
            throw new RuntimeException('MessageContainer class does not exist');
        }
        // autowire MessageContainer if the method exists.
        $this->messageContainer = MessageContainer::instance();
        if (self::$instance === null) {
            self::$instance = $this;
        }
    }

    /**
     * It returns the instance of PdoOne or throw an error if the instance is not set.
     * @param bool $throwIfNull
     * @return PdoOne|null
     */
    public static function instance(bool $throwIfNull = true): ?PdoOne
    {
        if (self::$instance === null && $throwIfNull) {
            throw new RuntimeException('instance not created for PdoOne');
        }
        return self::$instance;
    }

    protected function construct(
        $databaseType,
        $server,
        $user,
        $pwd,
        $db,
        $logFile = false,
        $charset = null,
        $nodeId = 1
    ): void
    {
        $this->databaseType = $databaseType;
        switch ($this->databaseType) {
            case 'mysql':
                $this->service = new PdoOne_Mysql($this);
                break;
            case 'sqlsrv':
                $this->service = new PdoOne_Sqlsrv($this);
                break;
            case 'oci':
                $user = strtoupper($user);
                $db = $user;
                $this->service = new PdoOne_Oci($this);
                break;
            case 'test':
                $this->service = new PdoOne_TestMockup($this);
                break;
            default:
                throw new RuntimeException('no database type selected');
        }
        $charset = $this->service->construct($charset, []);
        $this->server = $server;
        $this->user = $user;
        $this->pwd = $pwd;
        $this->db = $db;
        $this->lockerId = 'Pdo::' . $this->db;
        $this->tableDependencyArray = null;
        $this->tableDependencyArrayCol = null;
        $this->logFile = $logFile;
        $this->charset = $charset;
        $this->nodeId = $nodeId;
        // by default, the encryption uses the same password as the db.
        $this->encryption = new PdoOneEncryption($pwd, $user . $pwd);
    }

    public static function newColFK($key, $refcol, $reftable, $extra = null, $name = null): array
    {
        return ['key' => $key, 'refcol' => $refcol, 'reftable' => $reftable, 'extra' => $extra, 'name' => $name];
    }

    /**
     * We clean a sql that it could contain columns<br>
     * <b>Example:</b><br>
     * <pre>
     * PdoOne::cleanColumns("col1,col2"); // col1,col2
     * PdoOne::cleanColumns("col1';,col2"); // col1;,col2
     * </pre>
     * @param string $sql
     * @return array|string|string[]
     */
    public static function cleanColumns(string $sql) {
        return str_replace([chr(0),chr(8),chr(9),chr(13),"'",'"',chr(26),chr(92)],'',$sql);
    }
    public static function addParenthesis($txt, $start = '(', $end = ')')
    {
        if (self::hasParenthesis($txt, $start, $end) === false) {
            return $start . $txt . $end;
        }
        return $txt;
    }

    /**
     * It returns true if the text has parenthesis.
     *
     * @param string|null  $txt
     * @param string|array $start
     * @param string|array $end
     *
     * @return bool
     */
    public static function hasParenthesis(?string $txt, $start = '(', $end = ')'): bool
    {
        if (!$txt) {
            return false;
        }
        if (is_array($start)) {
            if (count($start) !== @count($end)) {
                return false;
            }
            foreach ($start as $k => $v) {
                if (strpos($txt, $v) === 0 && substr($txt, -1) === $end[$k]) {
                    return true;
                }
            }
        } elseif (strpos($txt, $start) === 0 && substr($txt, -1) === $end) {
            return true;
        }
        return false;
    }

    /**
     * Convert date from unix timestamp -> ISO (database format).
     * <p>Example: ::unixtime2Sql(1558656785); // returns 2019-05-24 00:13:05
     *
     * @param integer $dateNum
     *
     * @return string
     */
    public static function unixtime2Sql(int $dateNum): ?string
    {
        // 31/01/2016 20:20:00 --> 2016-01-31 00:00
        if ($dateNum === null) {
            return self::$dateEpoch;
        }
        return date(self::$isoDateTimeMs, $dateNum);
    }

    /**
     * Convert date, from mysql date -> text (using a format pre-established)
     *
     * @param string $sqlField
     * @param bool   $hasTime if true then the date contains time.
     *
     * @return string Returns a text with the date formatted (human-readable)
     */
    public static function dateSql2Text(string $sqlField, bool $hasTime = false)
    {
        $tmpDate = self::dateTimeSql2PHP($sqlField, $hasTime);
        if ($tmpDate === null) {
            return null;
        }
        if ($hasTime) {
            return $tmpDate->format((strpos($sqlField, '.') !== false) ? self::$dateTimeMicroHumanFormat
                : self::$dateTimeHumanFormat);
        }
        if (!$tmpDate) {
            return false;
        }
        return $tmpDate->format(self::$dateHumanFormat);
    }

    /**
     * Convert date, from mysql -> php
     *
     * @param string $sqlField
     * @param bool   $hasTime
     *
     * @return bool|DateTime|null
     */
    public static function dateTimeSql2PHP(string $sqlField, bool &$hasTime = false)
    {
        // 3  2016-01-31 00:00:00 -> 01/01/2016 00:00:00
        // mysql always returns the date/datetime/timestmamp in ansi format.
        if ($sqlField === '' || $sqlField === null) {
            if (self::$dateEpoch === null) {
                return null;
            }
            return DateTime::createFromFormat(self::$isoDateTimeMs, self::$dateEpoch);
        }
        if (strpos($sqlField, '.')) {
            // with date with time and microseconds
            //2018-02-06 05:06:07.123
            // Y-m-d H:i:s.v
            $hasTime = true;
            //$x = DateTime::createFromFormat("Y-m-d H:i:s.u", "2018-02-06 05:06:07.1234");
            return DateTime::createFromFormat(self::$isoDateTimeMs, $sqlField);
        }
        if (strpos($sqlField, ':')) {
            // date with time
            $hasTime = true;
            return DateTime::createFromFormat(self::$isoDateTime, $sqlField);
        }
        // only date
        $hasTime = false;
        return DateTime::createFromFormat(self::$isoDate, $sqlField);
    }

    /**
     * It converts a date (as string) into another format.<br>
     * Example:
     * <pre>
     * $pdoOne->dateConvert('01/01/2019','human','sql'); // 2019-01-01
     * </pre>
     * <b>iso</b> it is the standard format used for transporting<br>
     * <b>human</b> It is based in d/m/Y H:i:s, but it could be changed (self::dateHumanFormat)<br>
     * <b>sql</b> it is the format used by the database<br>
     * <b>class</b> it is an instance of a DateClass object<br>
     * <b>timestamp:</b> the time is presented as a timestamp value (integer)<br>
     *
     * @param mixed       $sqlField     The date to convert (the input value)
     * @param string      $inputFormat  =['iso','human','sql','class','timestamp'][$i] the input value type
     * @param string      $outputFormat =['iso','human','sql','class','timestamp'][$i] the output value type
     * @param string|null $force        =[null,'time','ms','none'][$i] It forces if the result gets time or
     *                                  microseconds<br>
     *                                  null = no force the result (it is calculated automatically)<br>
     *                                  time = returns with a precision of seconds<br>
     *                                  ms = returns with a precision of microseconds<br>
     *                                  none = it never returns any time<br>
     *
     * @return bool|DateTime
     */
    public static function dateConvert($sqlField, string $inputFormat, string $outputFormat, ?string $force = null)
    {
        /** @var boolean $ms if true then the value has microseconds */
        $ms = false;
        /** @var boolean $time if true then the value has time */
        $time = false;
        $tmpDate = self::dateConvertInput($sqlField, $inputFormat, $ms, $time);
        if (!$tmpDate) {
            return false;
        }
        if ($force !== null) {
            if ($force === 'ms') {
                $ms = true;
            } elseif ($force === 'time') {
                $time = true;
                $ms = false;
            } elseif ($force === 'none') {
                $time = false;
                $ms = false;
            }
        }
        switch ($outputFormat) {
            case 'iso':
                if ($ms) {
                    return $tmpDate->format(self::$dateTimeMicroFormat);
                }
                if ($time) {
                    return $tmpDate->format(self::$dateTimeFormat);
                }
                return $tmpDate->format(self::$dateFormat);
            case 'human':
                if ($ms) {
                    return $tmpDate->format(self::$dateTimeMicroHumanFormat);
                }
                if ($time) {
                    return $tmpDate->format(self::$dateTimeHumanFormat);
                }
                return $tmpDate->format(self::$dateHumanFormat);
            case 'sql':
                if ($ms) {
                    return $tmpDate->format(self::$isoDateInputTimeMs);
                }
                if ($time) {
                    return $tmpDate->format(self::$isoDateInputTime);
                }
                return $tmpDate->format(self::$isoDateInput);
            case 'class':
                return $tmpDate;
            case 'timestamp':
                return $tmpDate->getTimestamp();
        }
        return false;
    }

    /**
     * It converts a date and time value (expressed in different means) into a DateTime object or false if the operation
     * fails.<br>
     * <b>Example:</b><br>
     * <pre>
     * $r=PdoOne::dateConvertInput('01/12/2020','human',$ms,$time); // it depends on the fields self::$date*HumanFormat
     * $r=PdoOne::dateConvertInput('2020-12-01','iso',$ms,$time); // it depends on the fields self::$date*Format
     * $r=PdoOne::dateConvertInput('2020-12-01','sql',$ms,$time); // it depends on the database
     * $r=PdoOne::dateConvertInput(50000,'timestamp',$ms,$time); // a timestamp
     * $r=PdoOne::dateConvertInput(new DateTime(),'class',$ms,$time); // a DateTime object (it keeps the same one)
     * </pre>
     *
     * @param mixed   $inputValue  the input value.
     * @param string  $inputFormat =['iso','human','sql','class','timestamp'][$i] The input format
     * @param boolean $ms          [ref] It returns if it includes microseconds
     * @param boolean $time        [ref] It returns if it includes time
     * @return DateTime|false false if the operation fails
     * @noinspection PhpUnhandledExceptionInspection
     * @noinspection PhpDocMissingThrowsInspection
     */
    public static function dateConvertInput($inputValue, string $inputFormat, bool &$ms, bool &$time)
    {
        switch ($inputFormat) {
            case 'iso':
                if (strpos($inputValue, '.') !== false) {
                    $ms = true;
                    $tmpDate = DateTime::createFromFormat(self::$dateTimeMicroFormat, $inputValue);
                } elseif (strpos($inputValue, ':') !== false) {
                    $time = true;
                    $tmpDate = DateTime::createFromFormat(self::$dateTimeFormat, $inputValue);
                } else {
                    $tmpDate = DateTime::createFromFormat(self::$dateFormat, $inputValue);
                    if ($tmpDate === false) {
                        return false;
                    }
                    $tmpDate->setTime(0, 0);
                }
                break;
            case 'human':
                if (strpos($inputValue, '.') !== false) {
                    $ms = true;
                    $tmpDate = DateTime::createFromFormat(self::$dateTimeMicroHumanFormat, $inputValue);
                } elseif (strpos($inputValue, ':') !== false) {
                    $time = true;
                    $tmpDate = DateTime::createFromFormat(self::$dateTimeHumanFormat, $inputValue);
                } else {
                    $tmpDate = DateTime::createFromFormat(self::$dateHumanFormat, $inputValue);
                    if ($tmpDate === false) {
                        return false;
                    }
                    $tmpDate->setTime(0, 0);
                }
                break;
            case 'sql':
                if (strpos($inputValue, '.') !== false) {
                    $ms = true;
                    $tmpDate = DateTime::createFromFormat(self::$isoDateTimeMs, $inputValue);
                } elseif (strpos($inputValue, ':') !== false) {
                    $time = true;
                    $tmpDate = DateTime::createFromFormat(self::$isoDateTime, $inputValue);
                } else {
                    $tmpDate = DateTime::createFromFormat(self::$isoDate, $inputValue);
                    $tmpDate->setTime(0, 0);
                }
                break;
            case 'class':
                if (is_array($inputValue)) {
                    // sometimes we have a DateTime class, but it is converted into an array. We fixed this problem.
                    $inputValue = new DateTime($inputValue['date']);
                }
                /** @var DateTime $tmpDate */
                $tmpDate = $inputValue;
                if (!is_object($tmpDate)) {
                    $time = false;
                } else {
                    $time = $tmpDate->format('Gis') !== '000000';
                }
                break;
            case 'timestamp':
                $tmpDate = new DateTime();
                $tmpDate->setTimestamp($inputValue);
                $time = $tmpDate->format('Gis') !== '000000';
                $ms = fmod($inputValue, 1) !== 0.0;
                break;
            default:
                $tmpDate = false;
                trigger_error('PdoOne: dateConvert type not defined');
        }
        return $tmpDate;
    }

    /**
     * Convert date, from text -> mysql (using a format pre-established)
     *
     * @param string $textDate     Input date
     * @param bool   $hasTime      If true then it works with date and time
     *                             (instead of date)
     *
     * @return string
     */
    public static function dateText2Sql(string $textDate, bool $hasTime = true): ?string
    {
        if (($hasTime)) {
            $tmpFormat = strpos($textDate, '.') === false ? self::$dateTimeFormat : self::$dateTimeMicroFormat;
        } else {
            $tmpFormat = self::$dateFormat;
        }
        $tmpDate = DateTime::createFromFormat($tmpFormat, $textDate);
        if (!$hasTime && $tmpDate) {
            $tmpDate->setTime(0, 0);
        }
        return self::dateTimePHP2Sql($tmpDate); // it always returns a date with time. Mysql Ignores it.
    }

    /**
     * Conver date from php -> mysql
     * It always returns a time (00:00:00 if time is empty). it could return
     * microseconds 2010-01-01 00:00:00.00000
     *
     * @param DateTime $date
     *
     * @return string
     */
    public static function dateTimePHP2Sql(DateTime $date): ?string
    {
        // 31/01/2016 20:20:00 --> 2016-01-31 00:00
        if ($date == null) {
            return self::$dateEpoch;
        }
        if ($date->format('u') !== '000000') {
            return $date->format(self::$isoDateTimeMs);
        }
        return $date->format(self::$isoDateTime);
    }

    /**
     * Returns the current date(and time) in Text (human) format. Usually, it is d/m/Y H:i:s
     *
     * @param bool $hasTime
     * @param bool $hasMicroseconds
     *
     * @return string
     * @throws Exception
     * @see PdoOne::$dateTimeFormat
     */
    public static function dateTextNow(
        bool $hasTime = true,
        bool $hasMicroseconds = false
    ): string
    {
        $tmpDate = new DateTime();
        if ($hasTime) {
            return $tmpDate->format(($hasMicroseconds !== false) ? self::$dateTimeMicroHumanFormat
                : self::$dateTimeHumanFormat);
        }
        return $tmpDate->format(self::$dateHumanFormat);
    }

    /**
     * Returns the current (PHP server) date and time in the regular format. (Y-m-d\TH:i:s\Z in long format)
     *
     * @param bool $hasTime
     * @param bool $hasMicroseconds
     *
     * @return string
     * @throws Exception
     * @see PdoOne::$dateTimeFormat
     */
    public static function dateNow(
        bool $hasTime = true,
        bool $hasMicroseconds = false
    ): string
    {
        $tmpDate = new DateTime();
        if ($hasTime) {
            return $tmpDate->format(($hasMicroseconds !== false) ? self::$dateTimeMicroFormat : self::$dateTimeFormat);
        }
        return $tmpDate->format(self::$dateFormat);
    }

    /**
     * Returns the current date(and time) in SQL/ISO format. It depends on the type of database.
     *
     * @param bool $hasTime
     * @param bool $hasMicroseconds
     *
     * @return string
     */
    public static function dateSqlNow(bool $hasTime = true, bool $hasMicroseconds = false): string
    {
        $tmpDate = new DateTime();
        if ($hasTime) {
            return $tmpDate->format(($hasMicroseconds !== false) ? self::$isoDateTimeMs : self::$isoDateTime);
        }
        return $tmpDate->format(self::$isoDate);
    }


    public static function replaceBetween(
        $haystack,
        $startNeedle,
        $endNeedle,
        $replaceText,
        &$offset = 0,
        $replaceTag = false
    )
    {
        $ini = ($startNeedle === '') ? 0 : strpos($haystack, $startNeedle, $offset);
        if ($ini === false) {
            return false;
        }
        $ini2 = $ini + strlen($startNeedle); // exactly the position inside to where we want the value
        $p1 = ($endNeedle === '') ? strlen($haystack) : strpos($haystack, $endNeedle, $ini2);
        if ($p1 === false) {
            return false;
        }
        if ($replaceTag) {
            $len = $p1 + strlen($endNeedle) - $ini;
            $offset = $ini + $len;
            return substr_replace($haystack, $replaceText, $ini, $len);
        }
        $len = $p1 - $ini2;
        $offset = $ini2 + $len;
        return substr_replace($haystack, $replaceText, $ini2, $len);
    }

    public static function between($haystack, $startNeedle, $endNeedle, &$offset = 0, $ignoreCase = false)
    {
        if ($startNeedle === '') {
            $ini = 0;
        } else {
            $ini = ($ignoreCase) ? @stripos($haystack, $startNeedle, $offset)
                : @strpos($haystack, $startNeedle, $offset);
        }
        if ($ini === false) {
            return false;
        }
        $ini += strlen($startNeedle);
        if ($endNeedle === '') {
            $len = strlen($haystack);
        } else {
            $len = (($ignoreCase) ? stripos($haystack, $endNeedle, $ini) : strpos($haystack, $endNeedle, $ini));
            if ($len === false) {
                return false;
            }
            $len -= $ini;
        }
        $offset = $ini + $len;
        return substr($haystack, $ini, $len);
    }

    public static function tableCase($txt)
    {
        if ($txt === null || $txt === '') {
            return $txt;
        }
        if (strpos($txt, '_') !== false || strpos($txt, ' ') !== false) {
            $txt = strtolower($txt);
            $result = '';
            $l = strlen($txt);
            for ($i = 0; $i < $l; $i++) {
                $c = $txt[$i];
                if ($c === '_' || $c === ' ') {
                    if ($i !== $l - 1) {
                        $result .= strtoupper($txt[$i + 1]);
                        $i++;
                    } else {
                        $result .= $c;
                    }
                } else {
                    $result .= $c;
                }
            }
            return self::singularTable(ucfirst($result));
        }
        // the text is simple.
        return self::singularTable(ucfirst(strtolower($txt)));
    }

    /**
     * It converts a name to singular. This method is used automatically for the generation of the repository
     * classes<br>
     * <b>Example:</b><br>
     * <pre>
     * self::singularTable('categories'); // category
     * self::singularTable('churches'); // church
     * self::singularTable('prices'); // pric (it fail with this kind of cases)
     * self::singularTable('users'); // user
     * </pre>
     * @param $tableName
     * @return false|mixed|string
     */
    public static function singularTable($tableName)
    {
        $l = strlen($tableName);
        if ($l >= 3 && substr($tableName, -3) === 'ies') {
            // categories => category
            $tableName = substr($tableName, 0, $l - 3) . 'y';
        } else if ($l >= 2 && substr($tableName, -2) === 'es') {
            // churches => church (however it fails with prices => pric)
            $tableName = substr($tableName, 0, $l - 2);
        } else if ($l >= 1 && substr($tableName, -1) === 's') {
            // users => user
            $tableName = substr($tableName, 0, $l - 1);
        }
        return $tableName;
    }

    /**
     * It validates two definition of arrays.
     *
     * @param string       $table    The name of the table to valdiate
     * @param array        $defArray The definition of the table to compare
     * @param string|array $defKeys  The primary key or definition of keys
     * @param array        $defFK    The definition of the foreign keys
     *
     * @return array An array with all the errors or an empty array (if both matches).
     * @throws Exception
     */
    public function validateDefTable(string $table, array $defArray, $defKeys, array $defFK): array
    {
        // columns
        $defCurrent = $this->getDefTable($table);
        // if keys exists
        $error = [];
        foreach ($defCurrent as $k => $dc) {
            if (!isset($defArray[$k]) && !isset($defFK[$k])) {
                $error[$k] = "$k " . json_encode($dc) . " deleted";
            }
        }
        foreach ($defArray as $k => $dc) {
            if (!isset($defCurrent[$k])) {
                $error[$k] = "$k " . json_encode($dc) . " added";
            }
        }
        foreach ($defCurrent as $k => $dc) {
            if (isset($defArray[$k]) && strtolower($defArray[$k]['sql']) !== strtolower($dc['sql'])) {
                $error[$k] = "$k " . $dc['sql'] . " , $k " . $defArray[$k] . " are different";
            }
        }
        // keys
        if (!is_array($defKeys)) {
            $k = $defKeys;
            $defKeys = [];
            $defKeys[$k] = 'PRIMARY KEY';
        }
        $defCurrentKey = $this->getDefTableKeys($table);
        foreach ($defCurrentKey as $k => $dc) {
            if (!isset($defKeys[$k])) {
                $error[] = "key: $dc deleted";
            }
        }
        foreach ($defKeys as $k => $dc) {
            if (!isset($defCurrentKey[$k])) {
                $error[] = "key: $dc added";
            }
        }
        foreach ($defCurrentKey as $k => $dc) {
            if (strtolower($defKeys[$k]) !== strtolower($dc)) {
                $error[$k] = "key: $dc , $defKeys[$k] are different";
            }
        }
        // fk
        $defCurrentFK = $this->getDefTableFK($table);
        foreach ($defCurrentFK as $k => $dc) {
            if (!isset($defFK[$k])) {
                $error[] = "fk: " . json_encode($dc) . " deleted";
            }
        }
        foreach ($defFK as $k => $dc) {
            if (!isset($defCurrentFK[$k])) {
                $error[] = "fk: " . json_encode($dc) . " added";
            }
        }
        foreach ($defCurrentFK as $k => $dc) {
            if (strtolower($defFK[$k]) !== strtolower($dc)) {
                $error[$k] = "fk: $dc , $defFK[$k] are different";
            }
        }
        return $error;
    }

    /**
     * It get the definition of a table as an associative array<br>
     * <ul>
     * <li><b>phptype</b>: The PHP type of the column, for example int</li>
     * <li><b>conversion</b>: If the column requires a special conversion</li>
     * <li><b>type</b>: The SQL type of the column, for example int,varchar</li>
     * <li><b>size</b>: The size of the column, it could be two values for example "20,30"</li>
     * <li><b>null</b>: (boolean) if the column allows null</li>
     * <li><b>identity</b>: (boolean) if the column is identity</li>
     * <li><b>sql</b>: the sql syntax of the column</li>
     * </ul>
     * <b>Example:</b><br>
     * <pre>
     * $this->getDefTable('tablename',$conversion);
     * // ['col1'=>['alias'=>'','phptype'=>'int','conversion'=>null,'type'=>'int','size'=>null
     * // ,'null'=>false,'identity'=>true,'sql'='int not null auto_increment'
     * </pre>
     *
     * @param string     $table             The name of the table
     * @param array|null $specialConversion An associative array to set special conversion of values with the key as the
     *                                      column.
     *
     * @return array=[0]['alias'=>'','phptype'=>null,'conversion'=>null,'type'=>null,'size'=>null,'null'=>null
     *              ,'identity'=>null,'sql'=null]
     * @throws Exception
     */
    public function getDefTable(string $table, ?array $specialConversion = null): array
    {
        $r = $this->service->getDefTable($table); // ['col1'=>'int not null','col2'=>'varchar(50)']
        foreach ($r as $k => $v) {
            $t = explode(' ', trim($v), 2);
            // int unsigned default ...
            // string(30) not null default
            // float(20,3) not null default
            $type = $t[0];
            $conversion = $specialConversion[$k] ?? null;
            $extra = (count($t) > 1) ? $t[1] : null;
            if ($extra !== null && stripos($extra, 'not null') !== false) {
                $null = false;
            } else {
                $null = true;
            }
            if ($extra !== null && stripos($extra, $this->database_identityName) !== false) {
                $identity = true;
            } else {
                $identity = false;
            }
            $pPar = strpos($type, '(');
            if ($pPar !== false) {
                $dim = substr($type, $pPar + 1, strlen($type) - $pPar - 2);
                $type = substr($type, 0, $pPar);
            } else {
                $dim = null;
            }
            $r[$k] = [
                'alias' => $k,
                'phptype' => $this->dbTypeToPHP($type)[0],
                'conversion' => $conversion,
                'type' => $type,
                'size' => $dim,
                'null' => $null,
                'identity' => $identity,
                'sql' => $v
            ];
        }
        return $r;
    }

    /**
     * It converts a sql type into a 'php type' and a pdo::param type<br>
     * <b>Example:</b><br>
     * <pre>
     * $this->dbTypeToPHP('varchar'); // ['string',PDO::PARAM_STR]
     * $this->dbTypeToPHP('int'); // ['int',PDO::PARAM_INT]
     * </pre>
     * <b>PHP Types</b>: binary, date, datetime, decimal,int, string,time, timestamp<br>
     * <b>Param Types</b>: PDO::PARAM_LOB, PDO::PARAM_STR, PDO::PARAM_INT<br>
     *
     * @param string $type (lowercase)
     *
     * @return array
     */
    public function dbTypeToPHP(string $type): array
    {
        $type = strtolower($type);
        switch ($type) {
            case 'binary':
            case 'blob':
            case 'longblob':
            case 'longtext':
            case 'mediumblob':
            case 'mediumtext':
            case 'text':
            case 'tinyblob':
            case 'tinytext':
            case 'varbinary':
            case 'image':
                return ['binary', PDO::PARAM_LOB];
            case 'date':
                return ['date', PDO::PARAM_STR];
            case 'datetime':
            case 'datetime2':
            case 'datetimeoffset':
            case 'smalldatetime':
                return ['datetime', PDO::PARAM_STR];
            case 'decimal':
            case 'double':
            case 'float':
            case 'money':
            case 'numeric':
            case 'real':
            case 'smallmoney':
                return ['float', PDO::PARAM_STR];
            case 'bigint':
            case 'bit':
            case 'int':
            case 'mediumint':
            case 'smallint':
            case 'tinyint':
            case 'year':
            case 'number':
                return ['int', PDO::PARAM_INT];
            case 'table':
            case 'char':
            case 'enum':
            case 'geometry':
            case 'geometrycollection':
            case 'linestring':
            case 'multilinestring':
            case 'multipoint':
            case 'multipolygon':
            case 'point':
            case 'polygon':
            case 'set':
            case 'varchar':
            case 'cursor':
            case 'hierarchyid':
            case 'json':
            case 'nchar':
            case 'ntext':
            case 'nvarchar':
            case 'rowversion':
            case 'spatial geography types':
            case 'spatial geometry types':
            case 'sql_variant':
            case 'uniqueidentifier':
            case 'xml':
                return ['string', PDO::PARAM_STR];
            case 'time':
                return ['time', PDO::PARAM_STR];
            case 'timestamp':
                return ['timestamp', PDO::PARAM_STR];
        }
        return ['string', PDO::PARAM_STR];
    }

    /**
     * Returns an associative array with the definition of keys of a table.<br>
     * <b>IndexName</b>: Indicates the name of the index<br>
     * <b>ColumnName</b>: Indicates the name of the column<br>
     * <b>is_unique</b>: Is 0 if the value is not unique, otherwise 1<br>
     * <b>is_primary_key</b>: Is 1 if the value is a primary key, otherwise 0<br>
     * <b>TYPE</b>: returns PRIMARY KEY, UNIQUE KEY or KEY depending on the type of the key<br>
     * <b>Example:</b><br>
     * <pre>
     * $this->getDefTableKeys('table1');
     * // ["IndexName"=>'',"ColumnName"=>'',"is_unique"=>0,"is_primary_key"=>0,"TYPE"=>'']
     * </pre>
     *
     * @param string      $table        The name of the table to analize.
     * @param bool        $returnSimple true= returns as a simple associative
     *                                  array<br> Example:['id'=>'PRIMARY
     *                                  KEY','name'=>'FOREIGN KEY...']<br> false=
     *                                  returns as an associative array separated
     *                                  by parts<br>
     *                                  ['key','refcol','reftable','extra']<br>
     *
     * @param null|string $filter       if not null then it only returns keys that match the condition
     *
     * @return array=["IndexName"=>'',"ColumnName"=>'',"is_unique"=>0,"is_primary_key"=>0,"TYPE"=>0]
     * @throws Exception
     */
    public function getDefTableKeys(string $table, bool $returnSimple = true, ?string $filter = null): array
    {
        return $this->service->getDefTableKeys($table, $returnSimple, $filter);
    }

    /**
     * @param string $table            The name of the table to analize.
     * @param bool   $returnSimple     true= returns as a simple associative
     *                                 array<br> Example:['id'=>'PRIMARY
     *                                 KEY','name'=>'FOREIGN KEY...']<br> false=
     *                                 returns as an associative array separated
     *                                 by parts<br>
     *                                 ['key','refcol','reftable','extra']
     *
     * @param bool   $assocArray
     *
     * @return array
     * @throws Exception
     */
    public function getDefTableFK(string $table, bool $returnSimple = true, bool $assocArray = false): array
    {
        return $this->service->getDefTableFK($table, $returnSimple, null, $assocArray);
    }

    /**
     * It returns an associative array or a string with extended values of a table<br>
     * The results of the table depend on the kind of database. For example, sqlsrv returns the schema used (dbo),
     * while mysql returns the current schema (database).
     * <b>Example:</b><br>
     * <pre>
     * $this->getDefTableExtended('table'); // ['name','engine','schema','collation','description']
     * $this->getDefTableExtended('table',true); // "some description of the table"
     *
     * </pre><br>
     * <b>Fields returned:</b><br>
     * <ul>
     * <li>name = name of the table</li>
     * <li>engine = the engine of the table (mysql)</li>
     * <li>schema = the current schema (sqlserver) or database (mysql)</li>
     * <li>collation = the collation (mysql)</li>
     * <li>description = the description of the table</li>
     * </ul>
     *
     * @param string $table           The name of the table
     * @param bool   $onlyDescription If true then it only returns a description
     *
     * @return array|string|null
     * @throws Exception
     * @noinspection PhpUnused
     */
    public function getDefTableExtended(string $table, bool $onlyDescription = false)
    {
        return $this->service->getDefTableExtended($table, $onlyDescription);
    }

    protected static function removeTrailSlash($txt): string
    {
        return rtrim($txt, '/\\');
    }

    /**
     * @param string $database
     * @param string $server
     * @param string $user
     * @param string $pwd
     * @param string $db
     * @param string $input
     * @param string $output
     * @param string $namespace
     *
     * @return false|string
     * @throws Exception
     */
    public function run(
        string $database,
        string $server,
        string $user,
        string $pwd,
        string $db,
        string $input,
        string $output,
        string $namespace
    )
    {
        $this->construct($database, $server, $user, $pwd, $db);
        //$this->logLevel = 3;
        $this->connect(false);
        if (!$this->isOpen) {
            $r = "Unable to open database $database $server $user **** $db\n";
            $r .= $this->lastError();
            return $r;
        }
        if (stripos($input, 'select ') !== false || stripos($input, 'show ') !== false) {
            $query = $input;
        } else {
            $query = 'select * from ' . $this->addDelimiter($input);
        }
        switch ($output) {
            case 'csv':
                $result = $this->runRawQuery($query, []);
                if (!is_array($result)) {
                    return "No result or result error\n";
                }
                $head = '';
                foreach ($result[0] as $k => $row) {
                    $head .= $k . ',';
                }
                $head = rtrim($head, ',') . "\n";
                $r = $head;
                foreach ($result as $row) {
                    $line = '';
                    foreach ($row as $cell) {
                        $line .= self::fixCsv($cell) . ',';
                    }
                    $line = rtrim($line, ',') . "\n";
                    $r .= $line;
                }
                return $r;
            case 'json':
                try {
                    $result = $this->runRawQuery($query, []);
                } catch (Exception $ex) {
                    return json_encode(['error' => $this->lastError()]);
                }
                if (!is_array($result)) {
                    return "No result or result error\n";
                }
                return json_encode($result);
            case 'selectcode':
                return $this->generateCodeSelect($query);
            case 'arraycode':
                return $this->generateCodeArray($input, $query, false, false);
            case 'createcode':
                return $this->generateCodeCreate($input);
            case 'classcode':
                return $this->generateAbstractRepo($input, $namespace);
            default:
                return "Output $output not defined. Use csv/json/selectcode/arraycode/createcode/classcode";
        }
    }

    /**
     * Connects to the database.
     *
     * @param bool      $failIfConnected true=it throws an error if it's connected,
     *                                   otherwise it does nothing
     * @param bool|null $alterSession
     * @test exception this(false)
     */
    public function connect(bool $failIfConnected = true, ?bool $alterSession = null): void
    {
        $this->beginTry();
        if ($this->isOpen) {
            if (!$failIfConnected) {
                $this->endTry();
                return;
            } // it's already connected.
            $this->throwError('Already connected', '');
        }
        try {
            $this->storeInfo("connecting to $this->server $this->user/*** $this->db");
            $cs = ($this->charset) ? ';charset=' . $this->charset : '';
            $this->service->connect($cs, $alterSession);
            if ($this->conn1 instanceof stdClass) {
                $this->isOpen = true;
                $this->endTry();
                return;
            }
            $this->conn1->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $this->conn1->setAttribute(PDO::ATTR_STRINGIFY_FETCHES, false);
            //$this->conn1->setAttribute(PDO::ATTR_STRINGIFY_FETCHES, false); It is not required.
            $this->isOpen = true;
        } catch (Exception $ex) {
            $this->isOpen = false;
            $this->throwError("Failed to connect to $this->databaseType", $ex->getMessage(), '', true, $ex);
        }
        $this->endTry();
    }

    public function beginTry(): void
    {
        if ($this->customError) {
            set_exception_handler([$this, 'custom_exception_handler']);
        }
    }

    public function endTry(): void
    {
        if ($this->customError) {
            restore_exception_handler();
        }
    }

    /**
     * Write a log line for debug, clean the command chain then throw an error
     * (if throwOnError==true)
     *
     * @param string         $txt               The message to show or chain.
     * @param string|array   $txtExtra          It's only used if $logLevel>=2. It
     *                                          shows an extra message
     * @param string|array   $extraParam        It's only used if $logLevel>=3  It
     *                                          shows parameters (if any)
     *
     * @param bool           $throwError        if true then it throws error (is enabled). Otherwise, it stores the
     *                                          error.
     *
     * @param Exception|null $exception
     *
     * @see \eftec\PdoOne::$logLevel
     */
    public function throwError(string $txt, $txtExtra, $extraParam = '', bool $throwError = true, ?Exception $exception = null): void
    {
        if ($this->errorText !== '') {
            // there is another error pending to be displayed.
            $txt = $this->errorText;
        } else {
            if ($this->logLevel === 0) {
                $txt .= "\n{{Message:}} [Error on database]";
            }
            if ($this->logLevel >= 2) {
                $txt .= "\n{{Message:}} " . is_array($txtExtra) ? json_encode($txtExtra) : $txtExtra;
                if ($exception !== null) {
                    $txt .= "\n{{Message:}} " . $this->lastError() . ' ' . $exception->getMessage();
                } else {
                    $txt .= "\n{{Message:}} " . $this->lastError();
                }
            }
            if ($this->logLevel >= 2) {
                $txt .= "\n{{Last query:}} [$this->lastQuery]";
            }
            if ($this->logLevel >= 3) {
                $txt .= "\n{{Database:}} " . $this->server . ' - ' . $this->db;
                if (is_array($extraParam)) {
                    foreach ($extraParam as $k => $v) {
                        if (is_array($v) || is_object($v)) {
                            $v = json_encode($v);
                        }
                        $txt .= "\n{{" . $k . ":}} $v";
                    }
                } else {
                    $txt .= "\n{{Params:}} [" . $extraParam . "]";
                }
                if ($exception !== null) {
                    $txt = $this->custom_exception_handler($exception, $txt, true);
                }
            }
            $this->messageContainer->addItem($this->lockerId, $txt);
            $this->debugFile($txt, 'ERROR');
            $this->errorText = $txt;
        }
        if ($throwError && $this->throwOnError && $this->genError) {
            // endtry() invalidates this call (it is never called)
            throw new RuntimeException($txt);
        }
        $this->endTry();
    }

    public function clearError(): void
    {
        $this->errorText = '';
        $this->messageContainer->resetLocker($this->lockerId);
    }

    /**
     * Returns the last error.
     *
     * @return string
     */
    public function lastError(): string
    {
        if (!$this->isOpen) {
            return "It's not connected to the database";
        }
        return $this->conn1->errorInfo()[2] ?? '';
    }

    /**
     * @param             $exception
     * @param string|null $customMessage
     * @param false       $returnAsString
     * @return string
     */
    public function custom_exception_handler($exception, ?string $customMessage = null, bool $returnAsString = false): string
    {
        $isCli = !http_response_code();
        $customMessage = $customMessage ?? $exception->getMessage();
        $r = "Uncaught Exception: [" . get_class($exception) . "] code:" . $exception->getCode() . "\n"
            . $customMessage . "\n";
        if ($this->logLevel > 2) {
            $r .= "{{Trace:}}\n";
            foreach ($exception->getTrace() as $error) {
                // we remove all trace pointing to this file.
                $found = false;
                $file = $error['file'] ?? '(fileless)';
                foreach ($this->traceBlackList as $k) {
                    if (strpos($file, $k) !== false) {
                        $found = true;
                        break;
                    }
                }
                if (!$found) {
                    $args = [];
                    if (array_key_exists('args', $error)) {
                        if (!is_array($error['args'])) {
                            $error['args'] = [$error['args']]; // converting into array
                        }
                        foreach ($error['args'] as $v) {
                            if (is_object($v)) {
                                $args[] = get_class($v);
                            } else if (is_array($v)) {
                                $args[] = json_encode($v);
                            } elseif ($v === null) {
                                $args[] = '(null)';
                            } else {
                                $args[] = $v === self::NULL ? '(NULL)' : "'" . addslashes($v) . "'";
                            }
                        }
                    }
                    if (isset($error['class'])) {
                        $function = $error['class'] . $error['type'] . $error['function'];
                    } else {
                        $function = @$error['function'];
                    }
                    $r .= '<<<'.$file . ':' . @$error['line'] . ">>>\t" . $function . '('
                        . @implode(' , ', $args) . ')' . "\n";
                }
            }
        }
        if (!$isCli) {
            $r = str_replace(["\n", '[', ']','<<<','>>>', '{{', '}}', "\t"]
                , ["<br>", "<b>[", "]</b>",'<span style="background-color:blue; color:white">','</span>', '<u>', '</u>', '&nbsp;&nbsp;&nbsp;&nbsp;']
                , $r);
        }
        if (!$returnAsString) {
            echo $r;
            die(1);
        }
        return $r;
    }

    /**
     * It returns the insance of the MessageContainer or null if there is none.
     *
     * @return MessageContainer
     * @test equals null,this(),'this is not a message container'
     */
    public function getMessagesContainer(): MessageContainer
    {
        return $this->messageContainer;
    }

    public function getMessages($level = null): array
    {
        return $this->messageContainer->getLocker($this->lockerId)->all($level);
    }

    public function getErrors(): array
    {
        return $this->messageContainer->getLocker($this->lockerId)->allError();
    }

    public function getFirstError(): ?string
    {
        return $this->messageContainer->getLocker($this->lockerId)->firstError();
    }

    public function getLastError(): ?string
    {
        return $this->messageContainer->getLocker($this->lockerId)->lastError();
    }

    public function hasError($includeWarning = false): ?string
    {
        return $this->messageContainer->getLocker($this->lockerId)->hasError($includeWarning);
    }

    public function getInfos(): array
    {
        return $this->messageContainer->getLocker($this->lockerId)->allInfo();
    }

    public function getFirstInfo(): ?string
    {
        return $this->messageContainer->getLocker($this->lockerId)->firstInfo();
    }

    public function getLastInfo(): ?string
    {
        return $this->messageContainer->getLocker($this->lockerId)->lastInfo();
    }


    /**
     * Inject an instance of a messagecontainer. It is usually injected automatically when the instance of PdoOne is
     * created.
     *
     * @param MessageContainer $messageContainer
     * @return void
     */
    public function setMessages(MessageContainer $messageContainer): void
    {
        $this->messageContainer = $messageContainer;
    }

    public function debugFile($txt, $level = 'INFO'): void
    {
        if (!$this->logFile) {
            return; // debug file is disabled.
        }
        if (is_object($txt) || is_array($txt)) {
            $txtW = print_r($txt, true);
        } else {
            $txtW = $txt;
        }
        if ($this->logLevel === 2) {
            $txtW .= ' param:' . json_encode($this->lastParam);
        }
        /** @noinspection ForgottenDebugOutputInspection */
        error_log('[PdoOne]' . $level . "\t" . $txtW);
    }

    //<editor-fold desc="transaction functions">

    /**
     * Write a log line for debug if log level>2, clean the command chain then throw an error
     * (if throwOnError==true)
     *
     * @param string $txt
     *
     * @throws Exception
     */
    public function storeInfo(string $txt): void
    {
        if ($this->logLevel < 2) {
            return;
        }
        $this->messageContainer->addItem($this->lockerId, $txt, 'info');
        $this->debugFile($txt);
    }

    /**
     * It adds a delimiter to a text based in the type of database (` for mysql
     * and [] for sql server)<br> Example:<br>
     * $pdoOne->addDelimiter('hello world'); // `hello` world<br>
     * $pdoOne->addDelimiter('hello.world'); // `hello`.`world`<br>
     * $pdoOne->addDelimiter('hello=value); // `hello`=value<br>
     *
     * @param $txt
     *
     * @return mixed|string
     */
    public function addDelimiter($txt)
    {
        if (strpos($txt, $this->database_delimiter0) === false) {
            $pos = $this->strposa($txt, [' ', '=']);
            if ($pos === false) {
                $quoted = $this->database_delimiter0 . $txt . $this->database_delimiter1;
            } else {
                $arr = explode(substr($txt, $pos, 1), $txt, 2);
                $quoted
                    = $this->database_delimiter0 . $arr[0] . $this->database_delimiter1 . substr($txt, $pos, 1)
                    . $arr[1];
            }
            return str_replace('.', $this->database_delimiter1 . '.' . $this->database_delimiter0, $quoted);
        }
        // it has a delimiter, so we returned the same text.
        return $txt;
    }

    private function strposa($haystack, $needles = [])
    {
        $chr = [];
        foreach ($needles as $needle) {
            $res = strpos($haystack, $needle);
            if ($res !== false) {
                $chr[$needle] = $res;
            }
        }
        if (empty($chr)) {
            return false;
        }
        return min($chr);
    }

    //</editor-fold>

    /**
     * It runs a raw query
     * <br><b>Example</b>:<br>
     * <pre>
     * $values=$con->runRawQuery('select * from table where id=?',[20]',true); // with parameter
     * $values=$con->runRawQuery('select * from table where id=:name',['name'=>20]',true); // with named parameter
     * $values=$con->runRawQuery('select * from table,[]',true); // without parameter.
     ** $values=$con->runRawQuery('select * from table where id=?,[[1,20,PDO::PARAM_INT]]',true); // a full parameter.
     * </pr>
     *
     * @param string               $rawSql      The query to execute
     * @param array|null           $params      [type1,value1,type2,value2] or [name1=>value,name2=value2]
     * @param bool                 $returnArray if true then it returns an array. If false then it returns a
     *                                          PDOStatement
     * @param bool                 $useCache    if true then it uses cache (only if the service is available).
     * @param null|string|string[] $cacheFamily if cache is used, then it is used to set the family or group of the
     *                                          cache.
     * @return bool|PDOStatement|array an array of associative or a pdo statement. False is the operation fails
     * @throws Exception
     * @test equals [0=>[1=>1]],this('select 1',null,true)
     */
    public function runRawQuery(string $rawSql, ?array $params = null, ?bool $returnArray = true, bool $useCache = false, $cacheFamily = null)
    {
        $this->beginTry();
        if (!$this->isOpen) {
            $this->throwError("It's not connected to the database", '');
            return false;
        }
        if (!$rawSql) {
            $this->throwError("Query empty", '');
            return false;
        }
        $writeCommand = self::queryCommand($rawSql, true) !== 'dql';
        /** @var bool|string $uid it stores the unique identifier of the query */
        $uid = false;
        if ($this->readonly && $writeCommand) {
            // we aren't checking SQL-DLC queries. Also, "insert into" is stopped but "  insert into" not.
            $this->throwError('Database is in READ ONLY MODE', '');
            $this->endTry();
            return false;
        }
        if (!is_array($params) && $params !== null) {
            $this->throwError('runRawQuery, param must be null or an array', '');
            $this->endTry();
            return false;
        }
        if ($this->useInternalCache && $returnArray === true && !$writeCommand) {
            // if we use internal cache, then we return an array, and it is not a write command
            $uid = hash($this->encryption->hashType, $rawSql . serialize($params));
            if (isset($this->internalCache[$uid])) {
                // we have an internal cache, so we will return it.
                $this->internalCacheCounter++;
                $this->endTry();
                return $this->internalCache[$uid];
            }
        }
        $this->lastParam = $params;
        $this->lastQuery = $rawSql;
        $this->storeInfo($rawSql);
        if ($params === null) {
            $rows = $this->runRawQueryParamLess($rawSql, $returnArray);
            if ($uid !== false && $returnArray) {
                $this->internalCache[$uid] = $rows;
            }
            $this->endTry();
            return $rows;
        }
        // the "where" has parameters.
        $stmt = $this->prepare($rawSql);
        if ($stmt === false) {
            $this->throwError("Unable to prepare statement", $rawSql);
            return false;
        }
        $counter = 0;
        if ($this->isAssoc($params)) {
            // named parameter (aka col=:arg)
            $this->lastBindParam = $params;
            // [':name'=>value,':name2'=>value2];
            foreach ($params as $k => &$v) {
                $stmt->bindParam($k, $v, $this->getType($v)); // note, the second argument is &
            }
            unset($v);
        } else {
            // parameters numeric (aka col=?)
            $this->lastBindParam = [];
            $f = reset($params);
            if (is_array($f)) {
                // arrays of arrays.
                // [[name1,value1,type1,l1],[name2,value2,type2,l1]]
                foreach ($params as $param) {
                    $this->lastBindParam[$counter] = $param[0];
                    // note: the second field is & so we could not use $v
                    $param[3] = $param[3] ?? 0;
                    $stmt->bindParam(...$param);
                }
            } else {
                // [value1,value2]
                foreach ($params as $i => $iValue) {
                    //$counter++;
                    //$typeP = $this->stringToPdoParam($param[$i]);
                    $this->lastBindParam[$i] = $iValue;
                    //$stmt->bindParam($counter, $param[$i + 1], $typeP);
                    $stmt->bindParam($i + 1, $params[$i], $this->getType($params[$i]), 0);
                }
            }
        }
        if ($useCache !== false && $returnArray) {
            $this->uid = hash($this->encryption->hashType, $this->lastQuery . serialize($this->lastBindParam));
            $result = $this->cacheService->getCache($this->uid, $cacheFamily);
            if ($result !== false) {
                // it's found in the cache.
                if (is_array($result)) {
                    $this->affected_rows = count($result);
                } else {
                    $this->affected_rows = 0;
                }
                if ($uid !== false) {
                    $this->internalCache[$uid] = $result;
                }
                $this->endTry();
                return $result;
            }
        } else {
            $this->uid = null;
        }
        $this->runQuery($stmt);
        if ($returnArray && $stmt instanceof PDOStatement) {
            $rows = ($stmt->columnCount() > 0) ? $stmt->fetchAll(PDO::FETCH_ASSOC) : [];
            $this->affected_rows = $stmt->rowCount();
            $stmt = null;
            if ($uid !== false) {
                $this->internalCache[$uid] = $rows;
            }
            $this->endTry();
            return $rows;
        }
        if ($stmt instanceof PDOStatement) {
            $this->affected_rows = $stmt->rowCount();
        } else {
            $this->affected_rows = 0;
        }
        $this->endTry();
        return $stmt;
    }

    /**
     * It returns the sql command (in lower case) or the type (family) of sql command of a query<br>
     * Example:<br>
     * <pre>
     * $this->queryCommand("select * from table") // returns "select"
     * $this->queryCommand("select * from table",true) // returns "dql"
     * </pre>
     *
     * @param string $sql
     * @param false  $returnType if true then it returns DML (insert/updat/delete/etc) or DQL (select/show/display)
     *
     * @return string
     *
     */
    public static function queryCommand(string $sql, bool $returnType = false): string
    {
        if (!$sql) {
            return $returnType ? 'dml' : 'dql';
        }
        $command = strtolower((explode(' ', trim($sql)))[0]);
        if ($returnType) {
            if ($command === 'select' || $command === 'show' || $command === 'display') {
                return 'dql';
            }
            return 'dml';
        }
        return $command;
    }

    /**
     * Internal Use: It runs a raw query
     *
     * @param string $rawSql
     * @param bool   $returnArray
     *
     * @return array|bool|PDOStatement
     * @throws Exception
     * @see \eftec\PdoOne::runRawQuery
     */
    private function runRawQueryParamLess(string $rawSql, bool $returnArray)
    {
        $this->beginTry();
        // the "where" chain doesn't have parameters.
        try {
            $rows = $this->conn1->query($rawSql);
            if ($rows === false) {
                throw new RuntimeException('Unable to run raw runRawQueryParamLess', 9001);
            }
        } catch (Exception $ex) {
            $rows = false;
            $this->throwError('Exception in runRawQueryParamLess :', $rawSql, ['param' => $this->lastParam], true, $ex);
        }
        if ($returnArray && $rows instanceof PDOStatement) {
            if ($rows->columnCount() > 0) {
                $result = @$rows->fetchAll(PDO::FETCH_ASSOC);
                $this->affected_rows = $rows->rowCount();
                $this->endTry();
                return $result;
            }
            $this->affected_rows = $rows->rowCount();
            $this->endTry();
            return true;
        }
        $this->affected_rows = $rows->rowCount();
        $this->endTry();
        return $rows;
    }



    //<editor-fold desc="Date functions" defaultstate="collapsed" >

    /**
     * Prepare a query. It returns a mysqli statement.
     *
     * @param string $sql A SQL statement.
     *
     * @return PDOStatement returns the statement if correct otherwise null
     * @throws Exception
     */
    public function prepare(string $sql)
    {
        $this->beginTry();
        if (!$this->isOpen) {
            $this->throwError("It's not connected to the database", '');
            return null;
        }
        $this->lastQuery = $sql;
        if ($this->readonly) {
            if (stripos($sql, 'insert ') === 0 || stripos($sql, 'update ') === 0
                || stripos($sql, 'delete ') === 0
            ) {
                // we aren't checking SQL-DCL queries.
                $this->throwError('Database is in READ ONLY MODE', '');
            }
        }
        $this->storeInfo($sql);
        try {
            $stmt = $this->conn1->prepare($sql);
        } catch (Exception $ex) {
            $stmt = false;
            $this->throwError('Failed to prepare', $ex->getMessage() . $this->lastError(), ['param' => $this->lastParam], true, $ex);
        }
        if (($stmt === false) && $this->errorText === '') {
            $this->throwError('Unable to prepare query', $this->lastQuery, ['param' => $this->lastParam]);
        }
        $this->endTry();
        return $stmt;
    }

    /**
     * It returns true if the array is an associative array.  False
     * otherwise.<br>
     * <b>Example:</b><br>
     * isAssoc(['a1'=>1,'a2'=>2]); // true<br/>
     * isAssoc(['a1','a2']); // false<br/>
     * isAssoc('aaa'); isAssoc(null); // false<br/>
     *
     * @param mixed $array
     *
     * @return bool
     */
    private function isAssoc($array): bool
    {
        if ($array === null) {
            return false;
        }
        if (!is_array($array)) {
            return false;
        }
        return (array_values($array) !== $array);
    }

    /**
     * It returns the type of the PDO parameter based in the type of value of a variable
     *
     * @param mixed $v Variable
     *
     * @return int=[PDO::PARAM_STR,PDO::PARAM_INT,PDO::PARAM_BOOL][$i]
     * @test equals PDO::PARAM_STR,(20.3)
     * @test equals PDO::PARAM_STR,('hello')
     */
    public function getType(&$v): int
    {
        switch (1) {
            case (is_float($v)):
            case ($v === null):
                $vt = PDO::PARAM_STR;
                break;
            case (is_numeric($v)):
                $vt = PDO::PARAM_INT;
                break;
            case (is_bool($v)):
                $vt = PDO::PARAM_INT;
                $v = ($v) ? 1 : 0;
                break;
            case ($v instanceof DateTime):
                $vt = PDO::PARAM_STR;
                $v = self::dateTimePHP2Sql($v);
                break;
            default:
                $vt = PDO::PARAM_STR;
        }
        return $vt;
    }

    /**
     * Run a prepared statement.
     * <br><b>Example</b>:<br>
     *      $con->runQuery($con->prepare('select * from table'));
     *
     * @param PDOStatement $stmt          PDOStatement
     * @param array|null   $namedArgument (optional)
     *
     * @param bool         $throwError    (default true) if false, then it won't throw an error, but it will store the
     *                                    error
     *
     * @return bool returns true if the operation is correct, otherwise false
     * @throws Exception
     * @test equals true,$this->pdoOne->runQuery($this->pdoOne->prepare('select
     *     1 from dual'))
     * @test equals
     *     [1=>1],$this->pdoOne->select('1')->from('dual')->first(),'it
     *       must runs'
     */
    public function runQuery(PDOStatement $stmt, ?array $namedArgument = null, bool $throwError = true): ?bool
    {
        $this->beginTry();
        if (!$this->isOpen) {
            $this->throwError("It's not connected to the database", '', $throwError);
            return null;
        }
        try {
            $r = @$stmt->execute($namedArgument);
        } catch (Exception $ex) {
            //@$stmt->closeCursor();
            $this->throwError($this->databaseType . ':Failed to run query', $this->lastQuery,
                ['param' => $this->lastParam, 'error_last' => json_encode(error_get_last())], $throwError, $ex);
            return false;
        }
        if ($r === false) {
            //@$stmt->closeCursor();
            $this->throwError('Exception query ', $this->lastQuery, ['param' => $this->lastParam], $throwError);
            return false;
        }
        $this->endTry();
        return true;
    }

    protected static function fixCsv($value)
    {
        if (is_numeric($value)) {
            return $value;
        }
        $value = str_replace('"', '""', $value);
        return '"' . $value . '"';
    }

    public static function removeDoubleQuotes($value): ?string
    {
        if (!$value) {
            return null;
        }
        return trim($value, " \t\n\r\0\x0B\"");
    }

    /**
     * @param string $query
     *
     * @return string
     * @throws Exception
     */
    public function generateCodeSelect(string $query): string
    {
        $this->beginTry();
        $q = self::splitQuery($query);
        $code = '/** @var array $result=array(' . $this->generateCodeArray($query, $query) . ') */' . "\n";
        $code .= '$result=$pdo' . "\n";
        foreach ($q as $k => $v) {
            if ($v !== null) {
                $k2 = str_replace(' by', '', $k); // order by -> order
                foreach ($v as $vitem) {
                    $code .= "\t->$k2(\"$vitem\")\n";
                }
            }
        }
        $code .= "\t->toList();\n";
        $this->endTry();
        return $code;
    }

    protected static function splitQuery($query): array
    {
        $result = [];
        $parts = [
            'select',
            'from',
            'inner join',
            'inner join',
            'inner join',
            'inner join',
            'inner join',
            'inner join',
            'left join',
            'left join',
            'left join',
            'left join',
            'left join',
            'left join',
            'right join',
            'right join',
            'right join',
            'right join',
            'right join',
            'right join',
            'where',
            'group by',
            'having',
            'order by',
            'limit',
            '*END*',
        ];
        $partsRealIndex = [
            'select',
            'from',
            'innerjoin',
            'innerjoin',
            'innerjoin',
            'innerjoin',
            'innerjoin',
            'innerjoin',
            'left',
            'left',
            'left',
            'left',
            'left',
            'left',
            'right',
            'right',
            'right',
            'right',
            'right',
            'right',
            'where',
            'group',
            'having',
            'order',
            'limit',
            '*END*',
        ];
        $query = str_replace(array("\r\n", "\n", "\t", '   ', '  '), ' ',
            $query); // remove 3 or 2 space and put instead 1 space
        $query = ' ' . trim($query, " \t\n\r\0\x0B;") . '*END*'; // we also trim the last ; (if any)
        $pfin = 0;
        foreach ($parts as $kp => $part) {
            $ri = $partsRealIndex[$kp];
            if ($part !== '*END*') {
                //$result[$ri] = null;
                $pini = stripos($query, $part, $pfin);
                if ($pini !== false) {
                    $pini += strlen($part);
                    $found = false;
                    $cp = count($parts);
                    for ($i = $kp + 1; $i < $cp; $i++) {
                        $pfin = stripos($query, $parts[$i], $pini);
                        if ($pfin !== false) {
                            $found = $pfin;
                            break;
                        }
                    }
                    if ($found !== false) {
                        $pfin = $found;
                        if (!isset($result[$ri])) {
                            $result[$ri] = [];
                        }
                        $result[$ri][] = trim(substr($query, $pini, $pfin - $pini));
                    }
                }
            }
        }
        return $result;
    }

    /**
     * @param string      $table
     * @param string|null $sql
     * @param bool        $defaultNull
     * @param bool        $inline
     * @param bool        $recursive
     * @param array|null  $classRelations [optional] The relation table=>classname
     * @param array       $relation       [optional] An optional custom relation of columns
     * @param array       $aliases        [optional] the aliases of the columns of the current table
     * @return string
     * @throws Exception
     * @noinspection OnlyWritesOnParameterInspection
     * @noinspection PhpUnusedLocalVariableInspection
     */
    public function generateCodeArray(
        string  $table,
        ?string $sql = null,
        bool    $defaultNull = false,
        bool    $inline = true,
        bool    $recursive = false,
        ?array  $classRelations = null,
        array   $relation = [],
        array   $aliases = []
    ): string
    {
        $this->beginTry();
        if ($sql === null) {
            $sql = 'select * from ' . $this->addDelimiter($table);
        }
        $query = new PdoOneQuery($this);
        $r = $query->toMeta($sql);
        $ln = ($inline) ? '' : "\n";
        if ($recursive) {
            [$tables, $after, $before] = $this->tableDependency(true);
        } else {
            $tables = null;
            $after = null;
            $before = null;
        }
        $result = '[' . $ln;
        $used = [];
        $norepeat = [];
        foreach ($r as $row) {
            $name = $row['name'];
            $alias = $aliases[$name] ?? $name;
            if (!in_array($name, $used, true)) {
                if ($defaultNull) {
                    $default = 'null';
                } else {
                    $default = $this->typeDict($row);
                }
                $result .= "'" . $alias . "'=>" . $default . ',' . $ln;
                if ($recursive) {
                    if (isset($before[$table][$name])) {
                        foreach ($before[$table][$name] as $v3) {
                            if ($v3[1]
                                && $v3[0][0] !== self::$prefixBase
                            ) { // before is defined as [colremote,tableremote]
                                $colName = self::$prefixBase . $v3[1];
                                if (!$defaultNull) {
                                    $default = '(in_array($recursivePrefix.\'' . $colName . '\',$recursive,true))
                            ? [] 
                            : null';
                                } else {
                                    $default = 'null';
                                }
                                if (!in_array($colName, $norepeat, true)) {
                                    if (isset($relation[$colName])) {
                                        $rc =& $relation[$colName];
                                        $key = $rc['key'];
                                        if ($key === 'PARENT') {
                                            $default = 'null';
                                        }
                                        if ($key === 'ONETOONE' && !$defaultNull) {
                                            if ($classRelations === null
                                                || !isset($classRelations[$rc['reftable']])
                                            ) {
                                                $className = self::camelize($rc['reftable']) . 'Repo';
                                            } else {
                                                $className = $classRelations[$rc['reftable']];
                                            }
                                            $default = '(in_array($recursivePrefix.\'' . $colName . '\',$recursive,true))
                            ? ' . $className . '::factory(null,$recursivePrefix.\'' . $colName . '\') 
                            : null';
                                        }
                                        $result .= "'$colName'=>$default, /* $key! */$ln";
                                    } else {
                                        $result .= "'$colName'=>$default, /* onetomany */$ln";
                                    }
                                    $norepeat[] = $colName;
                                }
                            }
                        }
                    }
                    if (@$after[$table][$name]) {
                        if (!$defaultNull) {
                            if ($classRelations === null || !isset($classRelations[$after[$table][$name]])) {
                                $className = self::camelize($after[$table][$name]) . 'Repo';
                            } else {
                                $className = $classRelations[$after[$table][$name]];
                            }
                            $default = '(in_array($recursivePrefix.\'' . self::$prefixBase . $alias . '\',$recursive,true)) 
                            ? ' . $className . '::factory(null,$recursivePrefix.\'' . self::$prefixBase . $alias . '\') 
                            : null';
                        }
                        if (!in_array($name, $norepeat, true)) {
                            $namep = self::$prefixBase . $alias;
                            if (isset($relation[$namep])) {
                                /*array(5) {
                                    ["key"]=>
                                    string(11) "FOREIGN KEY"
                                    ["refcol"]=>
                                    string(14) "idtablachildPK"
                                    ["reftable"]=>
                                    string(10) "TableChild"
                                    ["extra"]=>
                                    string(0) ""
                                    ["name"]=>
                                    string(26) "FK_TableParent_TableChild1"
                                  }*/
                                $key = $relation[$namep]['key'];
                                if ($key !== 'PARENT') {
                                    // $default = 'null';
                                    $result .= "'" . $namep . "'=>" . $default . ', /* ' . $key . '!! */' . $ln;
                                    $norepeat[] = $name;
                                }
                            } else {
                                $result .= "'" . $namep . "'=>" . $default . ', /* manytoone */' . $ln;
                                $norepeat[] = $name;
                            }
                        }
                    }
                }
            }
            $used[] = $name;
        }
        $result .= ']' . $ln;
        $this->endTry();
        return str_replace(",$ln]", "$ln]", $result);
    }

    /**
     * @param array  $getDefTable    the definition of the tables with the colums no relation and its definition.
     * @param array  $classRelations The relation table=>classname
     * @param array  $relation       A list of all columns of the table that are relational.
     * @param string $type           =['constant','function'][$i]
     * @return array
     * @noinspection OnlyWritesOnParameterInspection
     * @noinspection PhpUnusedLocalVariableInspection
     */
    public function generateCodeArrayConst(
        array  $getDefTable,
        array  $classRelations,
        array  $relation,
        string $type
    ): array
    {
        $result = [];
        foreach ($getDefTable as $k => $v) {
            $result[$v['alias']] = null;
        }
        foreach ($relation as $k => $v) {
            switch ($v['key']) {
                case 'PARENT':
                    $clsRepo = $classRelations[$v['reftable']];
                    $result[$v['alias']] = null; // $clsRepo.'::factoryUtil()';
                    break;
                case 'MANYTOONE':
                case 'ONETOONE':
                    if ($type === 'constant') {
                        $clsRepo = $classRelations[$v['reftable']];
                        $result[$v['alias']] = '*' . $clsRepo . '::factoryUtil()' . '*';
                    } else {
                        $result[$v['alias']] = '*[]*';
                    }
                    break;
                case 'ONETOMANY':
                    if ($type === 'constant') {
                        $clsRepo = $classRelations[$v['reftable']];
                        $result[$v['alias']] = '*[' . $clsRepo . '::factoryUtil()]*';
                    } else {
                        $result[$v['alias']] = '*[]*';
                    }
                    break;
                case 'MANYTOMANY':
                    if ($type === 'constant') {
                        $clsRepo = $classRelations[$v['table2']];
                        $result[$v['alias']] = '*[' . $clsRepo . '::factoryUtil()' . ']*';
                    } else {
                        $result[$v['alias']] = '*[]*';
                    }
                    break;
            }
        }
        return $result;
    }

    /**
     * It returns an array with all the tables of the schema, also the foreign key and references  of each table<br>
     * <b>Example:</b>
     * <pre>
     * $this->tableDependency();
     * // ['table'=>['city','country'],
     * //    'after'=>['city'=>['country'],'country=>[]],
     * //    'before'=>['country'=>['city'],'city=>[]]
     * //   ]
     * $this->tableDependency(true);
     * // ["tables" => ["city","country"]
     * //    ,"after" => ["city" => ["countryfk" => "country"],"country" => []]
     * //    ,"before" => ["city" => [],"country" => ["country_id" => "country_id","city"]]
     * // ]
     * </pre>
     *
     * @param bool $returnColumn   If true then in "after" and "before", it returns the name of the columns
     * @param bool $forceLowerCase if true then the names of the tables are stored as lowercase
     *
     * @return array
     * @throws Exception
     */
    public function tableDependency(bool $returnColumn = false, bool $forceLowerCase = false): ?array
    {
        $this->beginTry();
        if ($returnColumn) {
            if ($this->tableDependencyArrayCol !== null) {
                $this->endTry();
                return $this->tableDependencyArrayCol;
            }
        } elseif ($this->tableDependencyArray !== null) {
            $this->endTry();
            return $this->tableDependencyArray;
        }
        $tables = $this->objectList('table', true);
        $after = [];
        $before = [];
        foreach ($tables as $table) {
            $before[$table] = [];
        }
        foreach ($tables as $table) {
            $arr = $this->getDefTableFK($table, false);
            $deps = [];
            foreach ($arr as $k => $v) {
                $v['reftable'] = ($forceLowerCase) ? strtolower($v['reftable']) : $v['reftable'];
                $k = ($forceLowerCase) ? strtolower($k) : $k;
                if ($returnColumn) {
                    // inverse relation
                    $deps[$k] = $v['reftable'];
                    if (!isset($before[$v['reftable']][$v['refcol']])) {
                        $before[$v['reftable']][$v['refcol']] = [];
                    }
                    $before[$v['reftable']][$v['refcol']][] = [$k, $table]; // remote column and remote table
                } else {
                    $deps[] = $v['reftable'];
                    $before[$v['reftable']][] = $table;
                }
            }
            $after[$table] = $deps; // ['city']=>['country','location']
        }
        if ($returnColumn) {
            $this->tableDependencyArrayCol = [$tables, $after, $before];
            $this->endTry();
            return $this->tableDependencyArrayCol;
        }
        $this->tableDependencyArray = [$tables, $after, $before];
        $this->endTry();
        return $this->tableDependencyArray;
    }

    /**
     * Returns a list of objects from the current schema/db<br>
     *
     * @param string $type         =['table','function'][$i] The type of the
     *                             object
     * @param bool   $onlyName     If true then it only returns the name of the
     *                             objects.
     *
     * @return bool|array
     * @throws Exception
     */
    public function objectList(string $type = 'table', bool $onlyName = false)
    {
        $this->beginTry();
        $query = $this->service->objectList($type, $onlyName);
        $this->endTry();
        if (strpos($query, '?') === false) {
            // query does not have an argument
            if ($onlyName) {
                return $this->select($query)->toListSimple();
            }
            return $this->runRawQuery($query, []);
        }
        // query has an argument
        if ($onlyName) {
            $values = $this->runRawQuery($query, [$this->db]);
            $final = [];
            foreach ($values as $v) {
                $final[] = reset($v);
            }
            return $final;
        }
        return $this->runRawQuery($query, [$this->db]);
    }

    /**
     * @param $sql
     * @return PdoOneQuery
     */
    public function select($sql): PdoOneQuery
    {
        return (new PdoOneQuery($this))->select($sql);
    }

    //</editor-fold>
    private function typeDict($row)
    {
        return $this->service->typeDict($row);
    }

    public static function camelize($input, $separator = '_')
    {
        return str_replace($separator, '', ucwords($input, $separator));
    }

    /**
     * @param string $tableName
     *
     * @return string
     * @throws Exception
     */
    public function generateCodeCreate(string $tableName): string
    {
        $this->beginTry();
        $code = "\$pdo->createTable('" . $tableName . "',\n";
        $arr = $this->getDefTable($tableName);
        $arrKey = $this->getDefTableKeys($tableName);
        $arrFK = self::varExport($this->getDefTableFK($tableName));
        $keys = self::varExport($arrKey);
        $code .= "\t" . self::varExport($arr);
        $code .= ",$keys);\n";
        $code .= "\$pdo->createFk('" . $tableName . "',\n";
        $code .= "$arrFK);\n";
        $this->endTry();
        return $code;
    }

    public static function varExport($input, $indent = "\t"): ?string
    {
        switch (gettype($input)) {
            case 'string':
                $r = "'" . addcslashes($input, "\\\$\'\r\n\t\v\f") . "'";
                break;
            case 'array':
                $indexed = array_keys($input) === range(0, count($input) - 1);
                $r = [];
                foreach ($input as $key => $value) {
                    $r[] = "$indent    " . ($indexed ? '' : self::varExport($key) . ' => ') . self::varExport($value,
                            "$indent    ");
                }
                $r = "[\n" . implode(",\n", $r) . "\n" . $indent . ']';
                break;
            case 'boolean':
                $r = $input ? 'TRUE' : 'FALSE';
                break;
            default:
                $r = var_export($input, true);
                break;
        }
        return str_replace(["'*", "*'", ' NULL', ' TRUE', ' FALSE'], ['', '', ' null', ' true', ' false'], $r);
    }

    /**
     * @param string $tableName
     * @param array|null $columnRelations
     * @param        $pkFirst
     * @param array  $aliasesAllTables
     * @return array|string
     * @throws Exception
     */
    public function generateGetRelations(string $tableName,?array $columnRelations,$pkFirst,array $aliasesAllTables):array {
        try {
            $deps = $this->tableDependency(true);
        } catch (Exception $e) {
            $this->endTry();
            return 'Error: Unable read table dependencies ' . $e->getMessage();
        } //  ["city"]=> {["city_id"]=> "address"}
        $after = @$deps[1][$tableName];
        if ($after === null) {
            $after = @$deps[1][strtolower($tableName)];
        }
        $before = @$deps[2][$tableName];
        if ($before === null) {
            $before = @$deps[2][strtolower($tableName)];
        }
        $aliases = $aliasesAllTables[$tableName] ?? [];
        $relation = $this->getDefTableFK($tableName, false, true);
        if (is_array($after) && is_array($before)) {
            foreach ($before as $key => $rows) { // $value is [relcol,table]
                foreach ($rows as $value) {
                    $relation[self::$prefixBase . $value[1]] = [
                        'key' => 'ONETOMANY',
                        'col' => $key,
                        'reftable' => $value[1],
                        'refcol' => $value[0] //, ltrim( $value[0],self::$prefixBase)
                    ];
                }
            }
        }
        // converts relations to ONETOONE
        foreach ($relation as $k => $rel) {
            if ($rel['key'] === 'ONETOMANY') {
                $pkref = null;
                $pkref = $this->service->getPK($rel['reftable'], $pkref);
                if (self::$prefixBase . $pkref[0] === $rel['refcol'] && count($pkref) === 1) {
                    $relation[$k]['key'] = 'ONETOONE';
                    $relation[$k]['refcol'] = ltrim($relation[$k]['refcol'], self::$prefixBase);
                }
            }
            if ($rel['key'] === 'MANYTOONE') {
                $pkref = null;
                $pkref = $this->service->getPK($rel['reftable'], $pkref);
                if ($pkref[0] === $rel['refcol'] && count($pkref) === 1
                    && (strcasecmp($k, self::$prefixBase . $pkFirst) === 0)
                ) {
                    // if they are linked by the pks and the pks are only 1.
                    $relation[$k]['key'] = 'ONETOONE';
                    $relation[$k]['col'] = $pkFirst;
                    $relation[$k]['refcol'] = ltrim($relation[$k]['refcol'], self::$prefixBase);
                }
            }
        }
        if ($columnRelations) {
            foreach ($relation as $k => $rel) {
                if (isset($columnRelations[$k])) {
                    // parent.
                    if ($columnRelations[$k] === 'PARENT') {
                        $relation[$k]['key'] = 'PARENT';
                    } elseif ($columnRelations[$k] === 'MANYTOMANY') {
                        // the table must have 2 primary keys.
                        $pks = null;
                        $pks = $this->service->getPK($rel['reftable'], $pks);
                        /** @noinspection PhpParamsInspection */
                        /** @noinspection PhpArrayIsAlwaysEmptyInspection */
                        /** @noinspection PhpConditionAlreadyCheckedInspection */
                        if ($pks !== false || count($pks) === 2) {
                            $relation[$k]['key'] = 'MANYTOMANY';
                            $refcol2 = (self::$prefixBase . $pks[0] === $relation[$k]['refcol']) ? $pks[1] : $pks[0];
                            try {
                                $defsFK = $this->service->getDefTableFK($relation[$k]['reftable'], false);
                            } catch (Exception $e) {
                                $this->endTry();
                                return ['Error: Unable read table dependencies ' . $e->getMessage(),null];
                            }
                            try {
                                $keys2 = $this->service->getDefTableKeys($defsFK[$refcol2]['reftable'], true,
                                    'PRIMARY KEY');
                            } catch (Exception $e) {
                                $this->endTry();
                                return ['Error: Unable read table dependencies' . $e->getMessage(),null];
                            }
                            $relation[$k]['refcol2'] = self::$prefixBase . $refcol2;
                            if (count($keys2) > 0) {
                                $keys2 = array_keys($keys2);
                                $relation[$k]['col2'] = $keys2[0];
                            } else {
                                $relation[$k]['col2'] = null;
                            }
                            $relation[$k]['table2'] = $defsFK[$refcol2]['reftable'];
                        }
                    }
                    // manytomany
                }
            }
        }
        $linked = '';
        foreach ($relation as $k => $v) {
            $ksimple = ltrim($k, '_'); // remove the _ from the beginner

            $alias = ($aliases[$k] ?? $k);
            $aliasCol = '_' . ($aliases[$ksimple] ?? $ksimple);
            $col = ltrim($aliasCol, '_');
            $refcol = ltrim($v['refcol'], '_');
            $refcol2 = isset($v['refcol2']) ? ltrim($v['refcol2'], '_') : null;
            $col2 = $v['col2'] ?? null;

            $aliasRef = '_' . @$aliasesAllTables[$v['reftable']][$refcol] ?? $refcol;

            $relation[$k]['alias'] = $alias;
            if (isset($v['col'])) {
                $relation[$k]['colalias'] = $aliases[$v['col']];
            }
            $relation[$k]['refcolalias'] = $aliasesAllTables[$v['reftable']][$refcol] ?? $refcol;
            $relation[$k]['refcol2alias'] = $aliasesAllTables[$v['reftable']][$refcol2] ?? $refcol2;
            if (isset($v['table2'])) {
                $relation[$k]['col2alias'] = $aliasesAllTables[$v['table2']][$col2] ?? $col2;
            }
            $key = $v['key'];
            if ($key === 'MANYTOONE') {
                //$col = ltrim($v['refcol'], '_');
                $aliasCol = $aliases[$col] ?? $col;
                $linked .= str_replace(
                    [
                        '{_col}',
                        '{refcol}',
                        '{col}'
                    ]
                    , [
                    $alias,
                    $aliasRef,
                    $aliasCol
                ],
                    "\t\t// \$row['{_col}']['{refcol}']=&\$row['{col}']; // linked field MANYTOONE\n");
            }
            if ($key === 'ONETOONE') {
                //$col = ltrim($v['refcol'], '_');
                //$col = ltrim($k, '_');
                $linked .= str_replace(
                    [
                        '{_col}',
                        '{refcol}',
                        '{col}'],
                    [
                        $k,
                        $aliasRef,
                        $col
                    ],
                    "\t\tisset(\$row['{_col}']) and \$row['{_col}']['{refcol}']=&\$row['{col}']; // linked field ONETOONE\n"
                );
            }
        }
        return [$relation,$linked];
    }
    /**
     * It generates a class<br>
     * <b>Example:</b><br>
     * <pre>
     * $class = $this->generateCodeClass('tablename', 'namespace\namespace2'
     *          ,['_idchild2FK'=>'PARENT' // relation
     *          ,'_tablaparentxcategory'=>'MANYTOMANY' // relation
     *          ,'col'=>'datetime3' // conversion
     *          ,'col2'=>'conversion(%s)' // custom conversion (identified by %s)
     *          ,'col3'=>] // custom conversion (identified by %s)
     *          ,'Repo');
     * $class = $this->generateCodeClass(['ClassName'=>'tablename'], 'namespace\namespace2'
     *          ,['/idchild2FK'=>'PARENT','/tablaparentxcategory'=>'MANYTOMANY']
     *          ,'Repo');
     * </pre>
     *
     * @param string|array  $tableName            The name of the table and the class.
     *                                            If the value is an array, then the key is the name of the table and
     *                                            the value is the name of the class
     * @param string        $namespace            The Namespace of the generated class
     * @param array|null    $columnRelations      An associative array to specific custom relations, such as PARENT<br>
     *                                            The key is the name of the columns and the value is the type of
     *                                            relation<br>
     * @param string[]|null $classRelations       The postfix of the class. Usually it is Repo or Dao.
     *
     * @param array         $specialConversion    An associative array to specify a custom conversion<br>
     *                                            The key is the name of the columns and the value is the type of
     *                                            relation<br>
     * @param string[]|null $defNoInsert          An array with the name of the columns to not insert. The identity
     *                                            is added automatically to this list
     * @param string[]|null $defNoUpdate          An array with the name of the columns to not update. The identity
     *                                            is added automatically to this list
     * @param string|null   $baseClass            The name of the base class. If no name then it uses the last namespace
     * @param string        $modelfullClass       (default:'') The full class of the model (with the namespace). If
     *                                            empty, then it doesn't use a model
     * @param array         $extraCols            An associative array with extra columns where they key is the name of
     *                                            the column and the value are the value to return (it is evaluated in
     *                                            the query). It is used by toList() and first(), it's also added to
     *                                            the model.
     *
     * @param array         $columnRemove
     * @param array         $aliasesAllTables     The aliases of every column of all table.<br>
     *                                            Example: ['col'=>'alias','col2'=>'alias2']
     * @return string|string[]
     * @throws Exception
     */
    public function generateAbstractRepo(
        $tableName,
        string $namespace = '',
        ?array $columnRelations = null,
        ?array $classRelations = null,
        array $specialConversion = [],
        ?array $defNoInsert = null,
        ?array $defNoUpdate = null,
        ?string $baseClass = null,
        string $modelfullClass = '',
        array $extraCols = [],
        array $columnRemove = [],
        array $aliasesAllTables = []
    )
    {
        /** @var array $aliases aliases of the current table */
        $aliases = $aliasesAllTables[$tableName] ?? [];
        $this->beginTry();
        $filename = __DIR__ . '/template/template_abstractrepo.php';
        $r = $this->phpstart . $this->openTemplate($filename);
        $lastns = explode('\\', $namespace);
        if ($modelfullClass) {
            $arr = explode('\\', $modelfullClass);
            $modelClass = end($arr);
            $modelUse = true;
        } else {
            $modelClass = false;
            $modelUse = false;
        }
        if ($baseClass === null) {
            $tmp3 = end($lastns);
            $baseClass = $tmp3 === false ? '' : $tmp3;
        }
        $fa = func_get_args();
        foreach ($fa as $f => $k) {
            if (is_array($k)) {
                $fa[$f] = str_replace([' ', "\r\n", "\n"], ['', '', ''], var_export($k, true));
            } else {
                $fa[$f] = "'$k'";
            }
        }
        if ($classRelations === null || !isset($classRelations[$tableName])) {
            $className = self::camelize($tableName);
        } else {
            $className = $classRelations[$tableName];
        }
        $extraColArray = '';
        foreach ($extraCols as $k => $v) {
            $extraColArray .= $v . ' as ' . $this->addQuote($k) . ',';
        }
        $extraColArray = rtrim($extraColArray, ',');
        $r = str_replace(array(
            '{version}',
            '{classname}',
            '{exception}',
            '{baseclass}',
            '{args}',
            '{table}',
            '{namespace}',
            '{modelnamespace}',
            '{classmodellist}',
            '{classmodelfirst}',
            '{extracol}'
        ), array(
            self::VERSION . ' Date generated ' . date('r'), //{version}
            $className, // {classname}
            ($namespace) ? 'use Exception;' : '',
            $baseClass, // {baseclass}
            implode(",", $fa),
            $tableName, // {table}
            ($namespace) ? "namespace $namespace;" : '', //{namespace}
            $modelUse ? "use $modelfullClass;" : '', // {modelnamespace}
            $modelUse ? "$modelClass::fromArrayMultiple( self::_toList(\$filter, \$filterValue));"
                : 'false; // no model set',  // {classmodellist}
            $modelUse ? "$modelClass::fromArray(self::_first(\$pk));" : 'false; // no model set' // {classmodelfirst}
        ,
            $extraColArray // {extracol}
        ), $r);
        $pk = $this->service->getPK($tableName, '??');
        $pkFirst = (is_array($pk) && count($pk) > 0) ? $pk[0] : null;



        [$relation,$linked]=$this->generateGetRelations($tableName,$columnRelations,$pkFirst,$aliasesAllTables);
        if(!is_array($relation)) {
            $this->endTry();
            return 'Error: Unable read fk of table ' . $relation;
        }
      /*  } catch (Exception $e) {
            $this->endTry();
            return 'Error: Unable read fk of table ' . $e->getMessage();
        }*/



        //die(1);
        $convertOutput = '';
        $convertInput = '';
        $getDefTable = $this->getDefTable($tableName, $specialConversion);
        foreach ($columnRemove as $v) {
            unset($getDefTable[$v]);
        }
        //die(1);
        // we forced the conversion but only if it is not specified explicit
        $allColumns = array_merge($getDefTable, $extraCols); // $extraColArray does not have type
        foreach ($allColumns as $kcol => $colDef) {
            $type = $colDef['type'] ?? null;
            $conversion = null;
            if (!isset($aliases[$kcol])) {
                $aliases[$kcol] = $kcol;
            }
            $getDefTable[$kcol]['alias'] = $aliases[$kcol] ?? $kcol;
            $alias = $getDefTable[$kcol]['alias'];
            if (isset($columnRelations[$kcol])) {
                $conversion = $columnRelations[$kcol];
                if ($type !== null) {
                    $getDefTable[$kcol]['conversion'] = $conversion;
                } else {
                    $type = 'new column';
                }
            } elseif ($type !== null && isset($this->codeClassConversion[$type])
                && $getDefTable[$kcol]['conversion'] === null
            ) {
                $conversion = $this->codeClassConversion[$type];
                $getDefTable[$kcol]['conversion'] = $conversion;
            }
            if ($conversion !== null) {
                if (is_array($conversion)) {
                    [$input, $output] = $conversion;
                } else {
                    $input = $conversion;
                    $output = $input;
                }
                switch ($input) {
                    case 'encrypt':
                        $tmp2 = "isset(%s) and %s=self::getPdoOne()->encrypt(%s);";
                        break;
                    case 'decrypt':
                        $tmp2 = "isset(%s) and %s=self::getPdoOne()->decrypt(%s);";
                        break;
                    case 'datetime3':
                        $tmp2 = "isset(%s) and %s=PdoOne::dateConvert(%s, 'human', 'sql');";
                        break;
                    case 'datetime4':
                        $tmp2 = '';
                        //$tmp2 = "isset(%s) and %s=PdoOne::dateConvert(%s, 'sql', 'sql');";
                        break;
                    case 'datetime2':
                        $tmp2 = "isset(%s) and %s=PdoOne::dateConvert(%s, 'iso', 'sql');";
                        break;
                    case 'datetime':
                        $tmp2 = "isset(%s) and %s=PdoOne::dateConvert(%s, 'class', 'sql');";
                        break;
                    case 'timestamp':
                        $tmp2 = "isset(%s) and %s=PdoOne::dateConvert(%s, 'timestamp', 'sql')";
                        break;
                    case 'bool':
                        $tmp2 = "isset(%s) and %s=(%s) ? 1 : 0;";
                        break;
                    case 'int':
                        $tmp2 = "isset(%s) and %s=(int)%s;";
                        break;
                    case 'string':
                        $tmp2 = "isset(%s) and %s=(string)%s;";
                        break;
                    case 'float':
                    case 'decimal':
                        $tmp2 = "isset(%s) and %s=(float)%s;";
                        break;
                    default:
                        if (strpos($input, '%s') !== false) {
                            $tmp2 = "%s=isset(%s) ? " . $input . " : null;";
                        } else {
                            $tmp2 = '// type ' . $input . ' not defined';
                        }
                }
                switch ($output) {
                    case 'encrypt':
                        $tmp = "%s=isset(%s) ? self::getPdoOne()->encrypt(%s) : null;";
                        break;
                    case 'decrypt':
                        $tmp = "%s=isset(%s) ? self::getPdoOne()->decrypt(%s) : null;";
                        break;
                    case 'datetime3':
                        $tmp = "%s=isset(%s) ? PdoOne::dateConvert(%s, 'sql', 'human') : null;";
                        break;
                    case 'datetime4':
                        // sql->sql no conversion
                        $tmp = '';
                        break;
                    case 'varchar':
                        // sql->sql no conversion
                        $tmp = "%s=isset(%s) ? (string)%s : null;";
                        break;
                    case 'datetime2':
                        $tmp = "%s=isset(%s) ? PdoOne::dateConvert(%s, 'sql', 'iso') : null;";
                        break;
                    case 'datetime':
                        $tmp = "%s=isset(%s) ? PdoOne::dateConvert(%s, 'sql', 'class') : null;";
                        break;
                    case 'timestamp':
                        $tmp = "%s=isset(%s) ? PdoOne::dateConvert(%s, 'sql', 'timestamp') : null;";
                        break;
                    case 'bool':
                        $tmp = "%s=isset(%s) ? (%s) ? true : false : null;";
                        break;
                    case 'int':
                        $tmp = "%s=isset(%s) ? (int)%s : null;";
                        break;
                    case 'float':
                    case 'decimal':
                        $tmp = "%s=isset(%s) ? (float)%s : null;";
                        break;
                    case null:
                    case 'nothing':
                    case 'null':
                        $tmp = "!isset(%s) and %s=null; // no conversion";
                        break;
                    default:
                        if (strpos($output, '%s') !== false) {
                            $tmp = "%s=isset(%s) ? " . $output . " : null;";
                        } else {
                            $tmp = '// type ' . $output . ' not defined';
                        }
                }
                if ($tmp !== '') {
                    $convertOutput .= "\t\t" . str_replace('%s', "\$row['$alias']", $tmp) . "\n";
                    $convertInput .= "\t\t" . str_replace('%s', "\$row['$alias']", $tmp2) . "\n";
                }
            } else {
                $tmp = "!isset(%s) and %s=null; // $type";
                $convertOutput .= "\t\t" . str_replace('%s', "\$row['$alias']", $tmp) . "\n";
            }
        }

        //$convertOutput.=$linked;
        $convertOutput = rtrim($convertOutput, "\n");
        $convertInput = rtrim($convertInput, "\n");
        // discard columns
        //$identities=$this->getDefTableKeys($tableName,);
        $identities = $this->getDefIdentities($tableName);
        if (count($identities) > 0) {
            $identity = $identities[0];
        } else {
            $identity = null;
        }
        if ($defNoInsert !== null) {
            $noInsert = array_merge($identities, $defNoInsert);
        } else {
            $noInsert = $identities;
        }
        if ($defNoUpdate !== null) {
            $noUpdate = array_merge($identities, $defNoUpdate);
        } else {
            $noUpdate = $identities;

        }
        /*$copy = $noInsert;
        $noInsert = [];
        foreach ($copy as $v) {
            if (isset($aliases[$v])) {
                $noInsert[] = $aliases[$v];
            } else {
                $noInsert[] = $v;
            }
        }*/
        /*$copy = $noUpdate;
        $noUpdate = [];
        foreach ($copy as $v) {
            if (isset($aliases[$v])) {
                $noUpdate[] = $aliases[$v];
            } else {
                $noUpdate[] = $v;
            }
        }*/
        //die(1);
        if ($pk) {
            // we never update the primary key.
            $noUpdate += $pk; // it adds and replaces duplicates, indexes are ignored.
        }
        $relation2 = [];
        foreach ($relation as $arr) {
            if ($arr['key'] !== 'FOREIGN KEY' && $arr['key'] !== 'PARENT' && $arr['key'] !== 'NONE') {
                @$relation2[$arr['key']][] = '/'.$arr['alias'];
            }
            //if($arr['key']==='MANYTOONE') {
            //    $relation2[]=$col;
            // }
        }
        $listAlias = [];
        foreach ($getDefTable as $k => $v) {
            $listAlias[$k] = $v['alias'];
        }
        try {
            $r = str_replace(array(
                '{pk}',
                '{identity}',
                '{def}',
                '{convertoutput}',
                '{convertinput}',
                '{defname}',
                '{defnamealias}',
                '{defnamealiasinv}',
                '{defkey}',
                '{defnoinsert}',
                '{defnoupdate}',
                '{deffk}',
                '{deffktype}',
                '{deffktype2}',
                '{array}',
                '{factory}',
                '{linked}'
            ), array(
                self::varExport($pk),
                self::varExport($identity), // {identity}
                //str_replace(["\n\t\t        ", "\n\t\t    ],"], ['', '],'], self::varExport($gdf, "\t\t")), // {def}
                self::varExport($getDefTable, "\t\t"), // {def}
                $convertOutput, // {convertoutput}
                $convertInput, // {convertinput}
                self::varExport(array_keys($getDefTable), "\t\t"), // {defname}
                self::varExport($listAlias, "\t\t"), // {defnamealias}
                self::varExport(array_flip($listAlias), "\t\t"), // {defnamealiasinv}
                self::varExport($this->getDefTableKeys($tableName), "\t\t"), // {defkey}
                self::varExport($noInsert, "\t\t"), // {defnoinsert}
                self::varExport($noUpdate, "\t\t"), // {defnoupdate}
                self::varExport($this->getDefTableFK($tableName), "\t\t\t"), //{deffk}
                self::varExport($relation, "\t\t"), //{deffktype}
                self::varExport($relation2, "\t\t"), //{deffktype2}
                self::varExport($this->generateCodeArrayConst(
                    $getDefTable, $classRelations, $relation, 'function'), "\t\t"), // {array}
                self::varExport($this->generateCodeArrayConst(
                    $getDefTable, $classRelations, $relation, 'constant'), "\t\t"), // {factory}
                $linked // {linked}
            ), $r);
        } catch (Exception $e) {
            $this->endTry();
            return "Unable read definition of tables " . $e->getMessage();
        }
        $this->endTry();
        return $r;
    }

    /**
     * @param string      $tableName
     * @param string|null $pkFirst the first primary key (if any)
     * @return array=['key','refcol','reftable','extra','name'][$i] where the key of the array is the name of the column
     */
    public function getRelations(string $tableName, ?string $pkFirst): array
    {
        try {
            $relation = $this->getDefTableFK($tableName, false, true);
        } catch (Exception $e) {
            $this->endTry();
            throw new RuntimeException('Error: Unable read fk of table ' . $e->getMessage());
        }
        // many to many
        /*foreach ($relation as $rel) {
            $tableMxM = $rel['reftable'];
            $tableFK = $this->getDefTableFK($tableMxM, false, true);
        }
        */
        try {
            $deps = $this->tableDependency(true);
        } catch (Exception $e) {
            $this->endTry();
            throw new RuntimeException('Error: Unable read table dependencies ' . $e->getMessage());
        } //  ["city"]=> {["city_id"]=> "address"}
        $after = @$deps[1][$tableName];
        if ($after === null) {
            $after = @$deps[1][strtolower($tableName)];
        }
        $before = @$deps[2][$tableName];
        if ($before === null) {
            $before = @$deps[2][strtolower($tableName)];
        }
        if (is_array($after) && is_array($before)) {
            foreach ($before as $key => $rows) { // $value is [relcol,table]
                foreach ($rows as $value) {
                    $relation[self::$prefixBase . $value[1]] = [
                        'key' => 'ONETOMANY',
                        'col' => $key,
                        'reftable' => $value[1],
                        'refcol' => $value[0] //, ltrim( $value[0],self::$prefixBase)
                    ];
                }
            }
        }
        // converts relations to ONETOONE
        foreach ($relation as $k => $rel) {
            if ($rel['key'] === 'ONETOMANY') {
                $pkref = null;
                $pkref = $this->service->getPK($rel['reftable'], $pkref);
                if (self::$prefixBase . $pkref[0] === $rel['refcol'] && count($pkref) === 1) {
                    $relation[$k]['key'] = 'ONETOONE';
                    $relation[$k]['refcol'] = ltrim($relation[$k]['refcol'], self::$prefixBase);
                }
            }
            if ($rel['key'] === 'MANYTOONE') {
                $pkref = null;
                $pkref = $this->service->getPK($rel['reftable'], $pkref);
                if ($pkref[0] === $rel['refcol'] && count($pkref) === 1
                    && (strcasecmp($k, self::$prefixBase . $pkFirst) === 0)
                ) {
                    // if they are linked by the pks and the pks are only 1.
                    $relation[$k]['key'] = 'ONETOONE';
                    $relation[$k]['col'] = $pkFirst;
                    $relation[$k]['refcol'] = ltrim($relation[$k]['refcol'], self::$prefixBase);
                }
            }
        }
        return $relation;
    }

    /**
     * It returns a field, column or table, the quotes defined by the current database type. It doesn't consider points
     * or space<br>
     * <pre>
     * $this->addQuote("aaa"); // [aaa] (sqlserver) `aaa` (mysql)
     * $this->addQuote("[aaa]"); // [aaa] (sqlserver, unchanged)
     * </pre>
     *
     * @param string $txt
     *
     * @return string
     * @see \eftec\PdoOne::addDelimiter to considers points
     */
    public function addQuote(string $txt): string
    {
        if (strlen($txt) < 2) {
            return $txt;
        }
        if ($txt[0] === $this->database_delimiter0 && substr($txt, -1) === $this->database_delimiter1) {
            // it is already quoted.
            return $txt;
        }
        return $this->database_delimiter0 . $txt . $this->database_delimiter1;
    }

    /**
     * It returns a simple array with all the columns that has identities/sequence.
     *
     * @param string $table
     *
     * @return array
     * @throws Exception
     */
    public function getDefIdentities(string $table): array
    {
        $this->beginTry();
        $r = $this->service->getDefTable($table);
        $identities = [];
        foreach ($r as $k => $v) {
            if (stripos($v, $this->database_identityName) !== false) {
                $identities[] = $k;
            }
        }
        $this->endTry();
        return $identities;
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
     * @throws Exception
     */
    public function set($sqlOrArray, $param = PdoOne::NULL): PdoOneQuery
    {
        return (new PdoOneQuery($this))->set($sqlOrArray, $param);
    }

    /**
     * Returns true if the current query has a "having" or "where"
     *
     * @param bool $having <b>true</b> it return the number of where<br>
     *                     <b>false</b> it returns the number of having
     *
     * @return bool
     * @noinspection PhpUnusedParameterInspection
     */
    public function hasWhere(bool $having = false): bool
    {
        // there is not a query, so it always returns false. This method is keep for compatibility with old code.
        return false;
    }

    /**
     * It starts a transaction. If the operation fails then it returns false.
     *
     * @return bool
     * @test     equals true,this()
     * @posttest execution $this->pdoOne->commit();
     * @example  examples/testdb.php 92,4
     */
    public function startTransaction(): bool
    {
        if ($this->transactionOpen || !$this->isOpen) {
            return false;
        }
        $this->transactionOpen = true;
        $this->conn1->beginTransaction();
        return true;
    }

    /**
     * Commit and close a transaction.
     *
     * @param bool $throw if true, and it fails then it throws an error.
     *
     * @return bool
     * @throws Exception
     * @test equals false,(false),'transaction is not open'
     */
    public function commit(bool $throw = true): bool
    {
        $this->beginTry();
        if (!$this->transactionOpen && $throw) {
            $this->throwError('Transaction is not open to commit()', '');
            return false;
        }
        if (!$this->isOpen) {
            $this->throwError("It's not connected to the database", '');
            return false;
        }
        $this->transactionOpen = false;
        $this->endTry();
        return @$this->conn1->commit();
    }

    /**
     * Rollback and close a transaction
     *
     * @param bool $throw [optional] if true, and it fails then it throws an error.
     *
     * @return bool
     * @throws Exception
     * @test equals false,(false),'transaction is not open'
     */
    public function rollback(bool $throw = true): bool
    {
        $this->beginTry();
        if (!$this->transactionOpen && $throw) {
            $this->throwError('Transaction not open  to rollback()', '');
        }
        if (!$this->isOpen && $throw) {
            $this->throwError("It's not connected to the database", '');
            return false;
        }
        $this->transactionOpen = false;
        try {
        $r = @$this->conn1->rollback();
        } catch(Exception $ex) {
            $r=false;
        }
        $this->endTry();
        return $r;
    }

    /**
     * It sets conversions depending of the type of data. This method is used together with generateCodeClassAll().
     * <b>This value persists across calls</b><br>
     * For example, if we always want to convert <b>tinyint</b> into <b>boolean</b>, then we could use this function
     * , instead of specify per each column.<br>
     * <b>Example:</b><br>
     * <pre>
     * $this->parent->generateCodeClassConversions(
     *      ['datetime'=>'datetime2'
     *      ,'tinyint'=>'bool' // converts tinyint as boolean
     *      ,'int'=['int',null] // converts input int as integer, and doesn't convert output int
     *      ]);
     * echo $this->parent->generateCodeClassAll('table');
     * $this->parent->generateCodeClassConversions(); // reset.
     * </pre>
     * <b>PHP Conversions</b>:
     * <ul>
     * <li>encrypt (encrypt value. Encryption must be set)</li>
     * <li>decrypt (decrypt a value if can. Encryption must be set)</li>
     * <li>datetime4 (sql string, no conversion). input (2020-12-30) --> db (2020-12-30) ---> output (30/12/2010)</li>
     * <li>datetime3 (human string). input (30/12/2010) --> db (2020-12-30) ---> output (30/12/2010)</li>
     * <li>datetime2 (iso format)</li>
     * <li>datetime (datetime class)</li>
     * <li>timestamp (int)</li>
     * <li>bool (boolean true or false <-> 1 or 0)</li>
     * <li>int (integer)</li>
     * <li>float (decimal)</li>
     * <li>custom function are defined by expression plus %s. Example trim(%s)</li>
     * <li>null/nothing (no conversion)</li>
     * </ul>
     *
     * @param array $conversion An associative array where the key is the type and the value is the conversion.
     *
     * @link https://github.com/EFTEC/PdoOne
     * @see  \eftec\PdoOne::generateAbstractRepo
     * @see  \eftec\PdoOne::setEncryption
     */
    public function generateCodeClassConversions(array $conversion = []): void
    {
        $this->codeClassConversion = $conversion;
    }
    //</editor-fold>
    //<editor-fold desc="DML" defaultstate="collapsed" >
    //</editor-fold>
    //<editor-fold desc="Cache" defaultstate="collapsed" >
    /**
     * It builds (generates source code) of the base, repo and repoext classes of the current schema.<br>
     * <b>Example:</b><br>
     * <pre>
     * // with model
     * $this->generateAllClasses([
     *          'products'=>['ProductRepo','ProductModel']
     *          ,'types'=>['TypeRepo','TypeModel']
     *          ],
     *          ,'SakilaBase'
     *          ,['eftec\repo','eftec\model']
     *          ,['c:/temp','c:/tempmodel']
     *          ,false,
     *          [
     *              'products'=>['_col'=>'PARENT' // relations
     *              ,'_col2'=>'MANYTOMANY' // relations
     *              ,'col1'=>'encrypt' // encrypt (input and output)
     *              ,'col2'=>['encrypt','decrypt'] // encrypt input and decrypt output
     *              ,'col3'=>['encrypt',null] // encrypt input and none output
     *          ]);
     * // without model
     * $this->generateAllClasses([
     *          'products'=>'ProductRepo'
     *          ,'types'=>'TypeRepo'
     *          ],
     *          ,'SakilaBase'
     *          ,'eftec\repo'
     *          ,'c:/temp'
     *          ,false,
     *          ['products'=>['_col'=>'PARENT','_col2'=>'MANYTOMANY'],
     *          ['products'=>['extracol'=>'now()']);
     * </pre>
     *
     * @param array        $relations       Where the key is the name of the table, and the value is an array with
     *                                      the name of the repository class and the name of the model class <br>
     *                                      If the value is not an array, then it doesn't build a model class<br>
     *                                      <b>Example:</b> ['products'=>'ProductRepo','types'=>'TypeRepo']<br>
     *                                      <b>Example:</b> ['products'=>['ProductRepo','ProductModel'] ]<br>
     * @param string       $baseClass       The name of the base class.
     * @param array|string $namespaces      (default:'') The name of the namespace. Example 'eftec\repo'<br>
     *                                      If we want to use a model class, then we need to set the namespace of the
     *                                      repository class and the namespace of the model class<br>
     *                                      ['c:/temp','c:/tempmodel'].
     * @param array|string $folders         (default:'') The name of the folder where the classes will be store.<br>
     *                                      If we want to use a model class, then we need to set the folder of the
     *                                      repository class and the folder of the model class<br>
     *                                      ['eftec\repo','eftec\model'].
     *                                      Example: 'c:/folder'
     * @param bool         $force           (default:false), if true then it will overwrite the repo files (if any).
     * @param array        $columnRelations (default:[]) An associative array with custom relations or
     *                                      conversion per table.<br>
     *                                      If we want to indicate a relation PARENT/MANYTOMANY, then we must use
     *                                      this array.<br>
     *                                      Example:['products'=>['_col'=>'PARENT','_col2'=>'MANYTOMANY']<br>
     *                                      If the column is not relational, then it is the column used to determine the
     *                                      conversion.<br>
     *                                      Example:['products'=>['col'=>'int']] // convert int input/output<br>
     *                                      Example:['products'=>['col'=>['encrypt','decrypt']] // encrypt input and
     *                                      decrypt output<br>
     *                                      <b>Conversion allowed</b> (see generateCodeClassConversions)
     * @param array        $extraColumns    An associative array with extra columns per table. It has the same form
     *                                      than $columnRelations. The columns are returned when we use toList() and
     *                                      first() and they are added to the model (if any) but they are not used in
     *                                      insert,update or delete<br>
     * @param array        $columnRemoves   An associative array to skip in the generation with the key as the name of
     *                                      the table and value an array with columns to be removed.<br>
     *                                      Example:['products'=>['colnotread']]
     *
     * @param array        $aliases         An associative array with the alias of every column.<br>
     *                                      If this array is empty, then it keeps the current value
     * @return array It returns an array with all the errors or warnings (if any).
     * @see \eftec\PdoOne::generateCodeClassConversions
     */
    public function generateAllClasses(
        array  $relations,
        string $baseClass,
               $namespaces = '',
               $folders = '',
        bool   $force = false,
        array  $columnRelations = [],
        array  $extraColumns = [],
        array  $columnRemoves = [],
        array  $aliases = []
    ): array
    {
        $internalCache = $this->useInternalCache;
        $this->setUseInternalCache();
        if (is_array($folders)) {
            [$folder, $folderModel] = $folders;
        } else {
            $folder = $folders;
            $folderModel = $folders;
        }
        if (is_array($namespaces)) {
            [$namespace, $namespaceModel] = $namespaces;
        } else {
            if (is_null($namespaces) || is_null($folder)) {
                throw new RuntimeException('namespace or folder is not set');
            }
            $namespace = $namespaces;
            $namespaceModel = $namespaces;
        }
        $firstKeyRelation = array_keys($relations)[0];
        $firstRelation = $relations[$firstKeyRelation]; // the first value of the relation arrays.
        if (is_array($firstRelation)) {
            $useModel = true;
            $relationsRepo = [];
            $relationsModel = [];
            foreach ($relations as $k => $v) {
                $relationsRepo[$k] = $v[0];
                $relationsModel[$k] = $v[1];
            }
        } else {
            $useModel = false;
            $relationsRepo = [];
            $relationsModel = [];
            foreach ($relations as $k => $v) {
                $relationsRepo[$k] = $v;
                $relationsModel[$k] = $v . 'Model';
            }
        }
        // BASE CLASS *******************************
        $folder = rtrim($folder, '/') . '/';
        $folderModel = rtrim($folderModel, '/') . '/';
        $logs = [];
        try {
            $classCode = $this->generateBaseClass($baseClass, $namespace, $relationsRepo, $useModel);
            $result = @file_put_contents($folder . $baseClass . '.php', $classCode);
        } catch (Exception $exception) {
            $result = false;
        }
        if ($result === false) {
            $logs[] = "Unable to save Base Class file '$folder$baseClass.php'";
        }
        // CODE CLASSES, MODELS *******************************
        foreach ($relationsRepo as $tableName => $className) {
            if ($useModel) {
                $modelname = $namespaceModel . '\\' . $relationsModel[$tableName];
            } else {
                $modelname = '';
            }
            try {
                $custom = $columnRelations[$tableName] ?? [];
                $extraCols = $extraColumns[$tableName] ?? [];
                $columnRem = $columnRemoves[$tableName] ?? [];
                $classCode1 = $this->generateAbstractRepo($tableName, $namespace, $custom, $relationsRepo, [], null, null,
                    $baseClass, $modelname, $extraCols, $columnRem, $aliases ?? []);
                $result = @file_put_contents($folder . "Abstract$className.php", $classCode1);
            } catch (Exception $e) {
                $result = false;
            }
            if ($result === false) {
                $logs[] = "Unable to save Repo Abstract Class file '{$folder}Abstract$className.php' "
                    . json_encode(error_get_last());
            }
            // creating model
            $resultcolumns = [];
            try {
                // we need to generate it to obtain resultcolumns
                $classModel1 = $this->generateAbstractModelClass($tableName, $namespaceModel, $custom,
                    $relationsModel, [], null, null, $baseClass, $extraCols, $columnRem, $resultcolumns);
            } catch (Exception $e) {
                $result = false;
                $classModel1 = 'error ' . $e->getMessage();
            }
            if ($result === false) {
                $logs[] = "Error: Unable to save Abstract Model Class file '{$folder}Abstract"
                    . $relationsModel[$tableName] . ".php' " . json_encode(error_get_last());
            }
            if ($useModel) {
                try {
                    //$custom = (isset($customRelation[$tableName])) ? $customRelation[$tableName] : [];
                    $result = @file_put_contents($folderModel . 'Abstract' . $relationsModel[$tableName] . '.php',
                        $classModel1);
                } catch (Exception $e) {
                    $result = false;
                }
                if ($result === false) {
                    $logs[] = "Error: Unable to save Abstract Model Class file '{$folder}Abstract"
                        . $relationsModel[$tableName] . ".php' " . json_encode(error_get_last());
                }
                try {
                    $filename = $folderModel . $relationsModel[$tableName] . '.php';
                    $classModel1 = $this->generateModelClass($tableName, $namespaceModel, $custom, $relationsModel, [],
                        null, null, $baseClass);
                    if ($force || @!file_exists($filename)) {
                        $result = @file_put_contents($filename, $classModel1);
                    } else {
                        $logs[] = "Warning: Unable to save Model Class file '$filename', file already exist, skipped";
                    }
                } catch (Exception $e) {
                    $result = false;
                }
                if ($result === false) {
                    $logs[] = "Error: Unable to save Model Class file '$filename' " . json_encode(error_get_last());
                }
            }
            try {
                $filename = $folder . $className . '.php';
                $classCode2 = $this->generateCodeClassRepo($tableName,
                    $namespace,
                    $relationsRepo,
                    $modelname,
                    $resultcolumns,
                    $aliases
                );
                if ($force || @!file_exists($filename)) {
                    // if the file exists then, we don't want to replace this class
                    $result = @file_put_contents($filename, $classCode2);
                } else {
                    $logs[] = "Warning: Unable to save Repo Class file '$folder$className.php', file already exist, skipped";
                }
            } catch (Exception $e) {
                $result = false;
            }
            if ($result === false) {
                $logs[] = "Error: Unable to save Repo Class file '$folder$className.php' " . json_encode(error_get_last());
            }
        }
        $this->setUseInternalCache($internalCache);
        return $logs;
    }

    /**
     * If true then the library will use the internal cache that stores DQL commands.<br>
     * By default, the internal cache is disabled<br>
     * The internal cache only lasts for the execution of the code, and it uses memory, but
     * it avoids querying values that are in memory.
     *
     * @param bool $useInternalCache
     * @return PdoOne
     */
    public function setUseInternalCache(bool $useInternalCache = true): PdoOne
    {
        $this->useInternalCache = $useInternalCache;
        return $this;
    }

    protected function openTemplate($filename)
    {
        $template = @file_get_contents($filename);
        if ($template === false) {
            throw new RuntimeException("Unable to read template file $filename");
        }
        // we delete and replace the first line.
        return substr($template, strpos($template, "\n") + 1);
    }

    public function generateBaseClass($baseClassName, $namespace, $classes, $modelUse = false)
    {
        $filename = __DIR__ . '/template/template_base.php';
        $r = $this->phpstart . $this->openTemplate($filename);
        /*foreach($classes as $id=>$entity) {
            foreach($entity as $k=>$class) {
                $classes[$id][$k] = $namespace . '\\' . $class;
            }
        }
        */
        $namespace = trim($namespace, '\\');
        return str_replace([
            '{type}',
            '{class}',
            '{exception}',
            '{namespace}',
            '{namespace2}',
            '{relations}',
            '{modeluse}',
            '{version}',
            '{compiled}'
        ], [
            $this->databaseType,
            $baseClassName,
            ($namespace) ? 'use Exception;' : '', // {exception}
            ($namespace) ? "namespace $namespace;" : '', // {namespace}
            ($namespace) ? "$namespace\\\\" : '', // {namespace2}
            $this::varExport($classes),
            $modelUse ? 'true' : 'false', // {modeluse}
            self::VERSION . ' Date generated ' . date('r'), // {version}
            _BasePdoOneRepo::BINARYVERSION, // {compiled}
        ], $r);
    }


    //</editor-fold>
    //<editor-fold desc="Log functions" defaultstate="collapsed" >
    /**
     * @param string $tableName
     * @param string $namespace
     * @param null   $customRelation
     * @param null   $classRelations
     * @param array  $specialConversion
     * @param null   $defNoInsert
     * @param null   $defNoUpdate
     * @param null   $baseClass
     * @param array  $extraColumn
     * @param array  $columnRemove
     * @param array  $resultColumns
     * @return string|string[]
     * @throws Exception
     * @noinspection PhpUnnecessaryCurlyVarSyntaxInspection
     */
    public function generateAbstractModelClass(
        string $tableName,
        string $namespace = '',
               $customRelation = null,
               $classRelations = null,
        array  $specialConversion = [],
               $defNoInsert = null,
               $defNoUpdate = null,
               $baseClass = null,
        array  $extraColumn = [],
        array  $columnRemove = [],
        array  &$resultColumns = []
    )
    {
        $resultColumns = [];
        $this->beginTry();
        $filename = __DIR__ . '/template/template_abstractmodel.php';
        $r = $this->phpstart . $this->openTemplate($filename);
        //$lastns = explode('\\', $namespace);
        //$baseClass = ($baseClass === null) ? end($lastns) : $baseClass;
        $fa = func_get_args();
        foreach ($fa as $f => $k) {
            if (is_array($k)) {
                $fa[$f] = str_replace([' ', "\r\n", "\n"], ['', '', ''], var_export($k, true));
            } else {
                $fa[$f] = "'$k'";
            }
        }
        if ($classRelations === null || !isset($classRelations[$tableName])) {
            $className = self::camelize($tableName);
        } else {
            $className = $classRelations[$tableName];
        }
        $r = str_replace(array(
            '{version}',
            '{classname}',
            '{exception}',
            '{namespace}'
        ), array(
            self::VERSION . ' Date generated ' . date('r'), //{version}
            $className, // {classname}
            ($namespace) ? 'use Exception;' : '',
            ($namespace) ? "namespace $namespace;" : ''
        ), $r);
        $pk = '??';
        $pk = $this->service->getPK($tableName, $pk);
        $pkFirst = (is_array($pk) && count($pk) > 0) ? $pk[0] : null;
        $relation = $this->getRelations($tableName, $pkFirst);
        if ($customRelation) {
            foreach ($relation as $k => $rel) {
                if (isset($customRelation[$k])) {
                    // parent.
                    if ($customRelation[$k] === 'PARENT') {
                        $relation[$k]['key'] = 'PARENT';
                    } elseif ($customRelation[$k] === 'MANYTOMANY') {
                        // the table must have 2 primary keys.
                        $pks = null;
                        $pks = $this->service->getPK($rel['reftable'], $pks);
                        /** @noinspection PhpParamsInspection */
                        /** @noinspection PhpArrayIsAlwaysEmptyInspection */
                        /** @noinspection PhpConditionAlreadyCheckedInspection */
                        if ($pks !== false || count($pks) === 2) {
                            $relation[$k]['key'] = 'MANYTOMANY';
                            $refcol2 = (self::$prefixBase . $pks[0] === $relation[$k]['refcol']) ? $pks[1]
                                : $pks[0];
                            try {
                                $defsFK = $this->service->getDefTableFK($relation[$k]['reftable'], false);
                            } catch (Exception $e) {
                                $this->endTry();
                                return 'Error: Unable read table dependencies ' . $e->getMessage();
                            }
                            try {
                                $keys2 = $this->service->getDefTableKeys($defsFK[$refcol2]['reftable'], true,
                                    'PRIMARY KEY');
                            } catch (Exception $e) {
                                $this->endTry();
                                return 'Error: Unable read table dependencies' . $e->getMessage();
                            }
                            $relation[$k]['refcol2'] = self::$prefixBase . $refcol2;
                            if (count($keys2) > 0) {
                                $keys2 = array_keys($keys2);
                                $relation[$k]['col2'] = $keys2[0];
                            } else {
                                $relation[$k]['col2'] = null;
                            }
                            $relation[$k]['table2'] = $defsFK[$refcol2]['reftable'];
                        }
                    }
                    // manytomany
                }
            }
        }
        //die(1);
        $gdf = $this->getDefTable($tableName, $specialConversion);
        foreach ($columnRemove as $v) {
            unset($gdf[$v]);
        }
        $fields = [];
        $fieldsb = [];
        foreach ($gdf as $varn => $field) {
            switch ($field['phptype']) { //binary, date, datetime, decimal,int, string,time, timestamp
                case 'binary':
                case 'date':
                case 'datetime':
                case 'decimal':
                case 'float':
                case 'int':
                case 'string':
                case 'time':
                case 'timestamp':
                    $resultColumns[] = [$varn, $field['phptype'], null];
                    $fields[] = "\t/** @var " . $field['phptype'] . " \$$varn  */\n\tpublic \$$varn;";
                    $fieldsb[] = "\t\t\$obj->$varn=isset(\$array['$varn']) ?  \$array['$varn'] : null;";
                    break;
            }
        }
        foreach ($extraColumn as $varn => $value) {
            $resultColumns[] = [$varn, 'mixed', null];
            $fields[] = "\t/** @var mixed \$$varn extra column: $value */\n\tpublic \$$varn;";
            $fieldsb[] = "\t\t\$obj->$varn=isset(\$array['$varn']) ?  \$array['$varn'] : null;";
        }
        $fieldsArr = implode("\n", $fields);
        $fieldsbArr = implode("\n", $fieldsb);
        $field2s = [];
        $field2sb = [];
        foreach ($relation as $varn => $field) {
            //$varnclean = ltrim($varn, PdoOne::$prefixBase);
            switch ($field['key']) {
                case 'FOREIGN KEY':
                    break;
                case 'MANYTOONE':
                    $class = $classRelations[$field['reftable']];
                    $resultColumns[] = [$varn, $field['key'], $class];
                    $field2s[] = "\t/** @var $class \$$varn manytoone */\n\tpublic \$$varn;";
                    $field2sb[] = "\t\t\$obj->$varn=isset(\$array['$varn']) ? \n\t\t\t\$obj->$varn=$class::fromArray(\$array['$varn']) \n\t\t\t: null; // manytoone";
                    $col = ltrim($varn, self::$prefixBase);
                    $rcol = $field['refcol'];
                    $field2sb[] = "\t\t(\$obj->$varn !== null) \n\t\t\tand \$obj->$varn->$rcol=&\$obj->$col; // linked manytoone";
                    break;
                case 'MANYTOMANY':
                    $class = $classRelations[$field['reftable']];
                    $resultColumns[] = [$varn, $field['key'], $class];
                    $field2s[] = "\t/** @var {$class}[] \$$varn manytomany */\n\tpublic \$$varn;";
                    $field2sb[] = "\t\t\$obj->$varn=isset(\$array['$varn']) ?  \n\t\t\t\$obj->$varn=$class::fromArrayMultiple(\$array['$varn']) \n\t\t\t: null; // manytomany";
                    break;
                case 'ONETOMANY':
                    $class = $classRelations[$field['reftable']];
                    $resultColumns[] = [$varn, $field['key'], $class];
                    $field2s[] = "\t/** @var {$class}[] \$$varn onetomany */\n\tpublic \$$varn;";
                    $field2sb[] = "\t\t\$obj->$varn=isset(\$array['$varn']) ?  \n\t\t\t\$obj->$varn=$class::fromArrayMultiple(\$array['$varn']) \n\t\t\t: null; // onetomany";
                    break;
                case 'ONETOONE':
                    $class = $classRelations[$field['reftable']];
                    $resultColumns[] = [$varn, $field['key'], $class];
                    $field2s[] = "\t/** @var $class \$$varn onetoone */\n\tpublic \$$varn;";
                    $field2sb[] = "\t\t\$obj->$varn=isset(\$array['$varn']) ?  \n\t\t\t\$obj->$varn=$class::fromArray(\$array['$varn']) \n\t\t\t: null; // onetoone";
                    $col = $field['col'] ?? $pkFirst;
                    $rcol = $field['refcol'];
                    $field2sb[] = "\t\t(\$obj->$varn !== null) \n\t\t\tand \$obj->$varn->$rcol=&\$obj->$col; // linked onetoone";
                    break;
            }
        }
        $fields2Arr = implode("\n", $field2s);
        $fields2Arrb = implode("\n", $field2sb);
        $r = str_replace(['{fields}', '{fieldsrel}', '{fieldsfa}', '{fieldsrelfa}'],
            [$fieldsArr, $fields2Arr, $fieldsbArr, $fields2Arrb], $r);
        if (@count($this->codeClassConversion) > 0) {
            // we forced the conversion but only if it is not specified explicit
            foreach ($gdf as $k => $colDef) {
                $type = $colDef['type'];
                if (isset($this->codeClassConversion[$type]) && $colDef['conversion'] === null) {
                    $gdf[$k]['conversion'] = $this->codeClassConversion[$type];
                }
            }
        }
        // discard columns
        $identities = $this->getDefIdentities($tableName);
        if ($defNoInsert !== null) {
            $noInsert = array_merge($identities, $defNoInsert);
        } else {
            $noInsert = $identities;
        }
        if ($defNoInsert !== null) {
            $noUpdate = array_merge($identities, $defNoUpdate);
        } else {
            $noUpdate = $identities;
        }
        try {
            $r = str_replace(array(
                '{pk}',
                '{def}',
                '{defname}',
                '{defkey}',
                '{defnoinsert}',
                '{defnoupdate}',
                '{deffk}',
                '{deffktype}',
                '{array}',
                '{array_null}'
            ), array(
                self::varExport($pk),
                //str_replace(["\n\t\t        ", "\n\t\t    ],"], ['', '],'], PdoOne::varExport($gdf, "\t\t")), // {def}
                self::varExport($gdf, "\t\t"),
                self::varExport(array_keys($gdf), "\t\t"), // {defname}
                self::varExport($this->getDefTableKeys($tableName), "\t\t"), // {defkey}
                self::varExport($noInsert, "\t\t"), // {defnoinsert}
                self::varExport($noUpdate, "\t\t"), // {defnoupdate}
                self::varExport($this->getDefTableFK($tableName), "\t\t\t"), //{deffk}
                self::varExport($relation, "\t\t"), //{deffktype}
                str_replace("\n", "\n\t\t",
                    rtrim($this->generateCodeArray($tableName, null, false, false, true, $classRelations, $relation),
                        "\n")),
                str_replace("\n", "\n\t\t",
                    rtrim($this->generateCodeArray($tableName, null, true, false, true, $classRelations, $relation),
                        "\n"))
            ), $r);
        } catch (Exception $e) {
            $this->endTry();
            return "Unable read definition of tables " . $e->getMessage();
        }
        $this->endTry();
        return $r;
    }

    /**
     * @param string $tableName
     * @param string $namespace
     * @param null   $customRelation
     * @param null   $classRelations
     * @param array  $specialConversion
     * @param null   $defNoInsert
     * @param null   $defNoUpdate
     * @param null   $baseClass
     *
     * @return string|string[]
     * @throws Exception
     * @noinspection PhpUnnecessaryCurlyVarSyntaxInspection
     */
    public function generateModelClass(
        string $tableName,
        string $namespace = '',
               $customRelation = null,
               $classRelations = null,
        array  $specialConversion = [],
               $defNoInsert = null,
               $defNoUpdate = null,
               $baseClass = null
    )
    {
        $this->beginTry();
        $filename = __DIR__ . '/template/template_model.php';
        $r = $this->phpstart . $this->openTemplate($filename);
        //$lastns = explode('\\', $namespace);
        //$baseClass = ($baseClass === null) ? end($lastns) : $baseClass;
        $fa = func_get_args();
        foreach ($fa as $f => $k) {
            if (is_array($k)) {
                $fa[$f] = str_replace([' ', "\r\n", "\n"], ['', '', ''], var_export($k, true));
            } else {
                $fa[$f] = "'$k'";
            }
        }
        if ($classRelations === null || !isset($classRelations[$tableName])) {
            $className = self::camelize($tableName);
        } else {
            $className = $classRelations[$tableName];
        }
        $r = str_replace(array(
            '{version}',
            '{classname}',
            '{exception}',
            '{namespace}'
        ), array(
            self::VERSION . ' Date generated ' . date('r'), //{version}
            $className, // {classname}
            ($namespace) ? 'use Exception;' : '',
            ($namespace) ? "namespace $namespace;" : ''
        ), $r);
        $pk = '??';
        $pk = $this->service->getPK($tableName, $pk);
        $pkFirst = (is_array($pk) && count($pk) > 0) ? $pk[0] : null;
        try {
            $relation = $this->getDefTableFK($tableName, false, true);
        } catch (Exception $e) {
            return 'Error: Unable read fk of table ' . $e->getMessage();
        }
        try {
            $deps = $this->tableDependency(true);
        } catch (Exception $e) {
            $this->endTry();
            return 'Error: Unable read table dependencies ' . $e->getMessage();
        } //  ["city"]=> {["city_id"]=> "address"}
        $after = @$deps[1][$tableName];
        if ($after === null) {
            $after = @$deps[1][strtolower($tableName)];
        }
        $before = @$deps[2][$tableName];
        if ($before === null) {
            $before = @$deps[2][strtolower($tableName)];
        }
        if (is_array($after) && is_array($before)) {
            foreach ($before as $key => $rows) { // $value is [relcol,table]
                foreach ($rows as $value) {
                    $relation[self::$prefixBase . $value[1]] = [
                        'key' => 'ONETOMANY',
                        'col' => $key,
                        'reftable' => $value[1],
                        'refcol' => $value[0]
                    ];
                }
            }
        }
        // converts relations to ONETOONE
        foreach ($relation as $k => $rel) {
            if ($rel['key'] === 'ONETOMANY') {
                $pkref = null;
                $pkref = $this->service->getPK($rel['reftable'], $pkref);
                if (self::$prefixBase . $pkref[0] === $rel['refcol'] && count($pkref) === 1) {
                    $relation[$k]['key'] = 'ONETOONE';
                    $relation[$k]['col'] = 'xxx3';
                    $relation[$k]['refcol'] = ltrim($relation[$k]['refcol'], self::$prefixBase);
                }
            }
            if ($rel['key'] === 'MANYTOONE') {
                $pkref = null;
                $pkref = $this->service->getPK($rel['reftable'], $pkref);
                if ($pkref[0] === $rel['refcol'] && count($pkref) === 1
                    && (strcasecmp($k, self::$prefixBase . $pkFirst) === 0)
                ) {
                    // if they are linked by the pks and the pks are only 1.
                    $relation[$k]['key'] = 'ONETOONE';
                    $relation[$k]['col'] = 'xxx4';
                    $relation[$k]['refcol'] = ltrim($relation[$k]['refcol'], self::$prefixBase);
                }
            }
        }
        if ($customRelation) {
            foreach ($relation as $k => $rel) {
                if (isset($customRelation[$k])) {
                    // parent.
                    if ($customRelation[$k] === 'PARENT') {
                        $relation[$k]['key'] = 'PARENT';
                    } elseif ($customRelation[$k] === 'MANYTOMANY') {
                        // the table must have 2 primary keys.
                        $pks = $this->service->getPK($rel['reftable']);
                        /** @noinspection PhpParamsInspection */
                        /** @noinspection PhpArrayIsAlwaysEmptyInspection */
                        /** @noinspection PhpConditionAlreadyCheckedInspection */
                        if ($pks !== false || count($pks) === 2) {
                            $relation[$k]['key'] = 'MANYTOMANY';
                            $refcol2 = (self::$prefixBase . $pks[0] === $relation[$k]['refcol']) ? $pks[1]
                                : $pks[0];
                            try {
                                $defsFK = $this->service->getDefTableFK($relation[$k]['reftable'], false);
                            } catch (Exception $e) {
                                $this->endTry();
                                return 'Error: Unable read table dependencies ' . $e->getMessage();
                            }
                            try {
                                $keys2 = $this->service->getDefTableKeys($defsFK[$refcol2]['reftable'], true,
                                    'PRIMARY KEY');
                            } catch (Exception $e) {
                                $this->endTry();
                                return 'Error: Unable read table dependencies' . $e->getMessage();
                            }
                            $relation[$k]['refcol2'] = self::$prefixBase . $refcol2;
                            if (count($keys2) > 0) {
                                $keys2 = array_keys($keys2);
                                $relation[$k]['col2'] = $keys2[0];
                            } else {
                                $relation[$k]['col2'] = null;
                            }
                            $relation[$k]['table2'] = $defsFK[$refcol2]['reftable'];
                        }
                    }
                    // manytomany
                }
            }
        }
        //die(1);
        $gdf = $this->getDefTable($tableName, $specialConversion);
        $fields = [];
        $fieldsb = [];
        foreach ($gdf as $varn => $field) {
            switch ($field['phptype']) { //binary, date, datetime, decimal,int, string,time, timestamp
                case 'binary':
                case 'date':
                case 'datetime':
                case 'decimal':
                case 'float':
                case 'int':
                case 'string':
                case 'time':
                case 'timestamp':
                    $fields[] = "\t/** @var " . $field['phptype'] . " \$$varn  */\n\tpublic \$$varn;";
                    $fieldsb[] = "\t\t\$obj->$varn=isset(\$array['$varn']) ?  \$array['$varn'] : null;";
                    break;
            }
        }
        $fieldsArr = implode("\n", $fields);
        $fieldsbArr = implode("\n", $fieldsb);
        $field2s = [];
        $field2sb = [];
        foreach ($relation as $varn => $field) {
            //$varnclean = ltrim($varn, PdoOne::$prefixBase);
            switch ($field['key']) {
                case 'FOREIGN KEY':
                    break;
                case 'MANYTOONE':
                    $class = $classRelations[$field['reftable']];
                    $field2s[] = "\t/** @var $class \$$varn manytoone */
    public \$$varn;";
                    $field2sb[] = "\t\t\$obj->$varn=isset(\$array['$varn']) ? 
            \$obj->$varn=$class::fromArray(\$array['$varn']) 
            : null; // manytoone";
                    break;
                case 'MANYTOMANY':
                    $class = $classRelations[$field['reftable']];
                    $field2s[] = "\t/** @var {$class}[] \$$varn manytomany */
    public \$$varn;";
                    $field2sb[] = "\t\t\$obj->$varn=isset(\$array['$varn']) ?  
            \$obj->$varn=$class::fromArrayMultiple(\$array['$varn']) 
            : null; // manytomany";
                    break;
                case 'ONETOMANY':
                    $class = $classRelations[$field['reftable']];
                    $field2s[] = "\t/** @var {$class}[] \$$varn onetomany */
    public \$$varn;";
                    $field2sb[] = "\t\t\$obj->$varn=isset(\$array['$varn']) ?  
            \$obj->$varn=$class::fromArrayMultiple(\$array['$varn']) 
            : null; // onetomany";
                    break;
                case 'ONETOONE':
                    $class = $classRelations[$field['reftable']];
                    $field2s[] = "\t/** @var $class \$$varn onetoone */
    public \$$varn;";
                    $field2sb[] = "\t\t\$obj->$varn=isset(\$array['$varn']) ?  
            \$obj->$varn=$class::fromArray(\$array['$varn']) 
            : null; // onetoone";
                    break;
            }
        }
        $fields2Arr = implode("\n", $field2s);
        $fields2Arrb = implode("\n", $field2sb);
        $r = str_replace(['{fields}', '{fieldsrel}', '{fieldsfa}', '{fieldsrelfa}'],
            [$fieldsArr, $fields2Arr, $fieldsbArr, $fields2Arrb], $r);
        if (@count($this->codeClassConversion) > 0) {
            // we forced the conversion but only if it is not specified explicit
            foreach ($gdf as $k => $colDef) {
                $type = $colDef['type'];
                if (isset($this->codeClassConversion[$type]) && $colDef['conversion'] === null) {
                    $gdf[$k]['conversion'] = $this->codeClassConversion[$type];
                }
            }
        }
        // discard columns
        $identities = $this->getDefIdentities($tableName);
        if ($defNoInsert !== null) {
            $noInsert = array_merge($identities, $defNoInsert);
        } else {
            $noInsert = $identities;
        }
        if ($defNoInsert !== null) {
            $noUpdate = array_merge($identities, $defNoUpdate);
        } else {
            $noUpdate = $identities;
        }
        try {
            $r = str_replace(array(
                '{pk}',
                '{def}',
                '{defname}',
                //'{defnamealias}',
                '{defkey}',
                '{defnoinsert}',
                '{defnoupdate}',
                '{deffk}',
                '{deffktype}',
                '{array}',
                '{array_null}'
            ), array(
                self::varExport($pk),
                //str_replace(["\n\t\t        ", "\n\t\t    ],"], ['', '],'], PdoOne::varExport($gdf, "\t\t")), // {def}
                self::varExport($gdf, "\t\t"),
                self::varExport(array_keys($gdf), "\t\t"), // {defname}
                //self::varExport(array_keys($gdf), "\t\t"), // {defnamealias}
                self::varExport($this->getDefTableKeys($tableName), "\t\t"), // {defkey}
                self::varExport($noInsert, "\t\t"), // {defnoinsert}
                self::varExport($noUpdate, "\t\t"), // {defnoupdate}
                self::varExport($this->getDefTableFK($tableName), "\t\t\t"), //{deffk}
                self::varExport($relation, "\t\t"), //{deffktype}
                str_replace("\n", "\n\t\t",
                    rtrim($this->generateCodeArray($tableName, null, false, false, true, $classRelations, $relation),
                        "\n")),
                str_replace("\n", "\n\t\t",
                    rtrim($this->generateCodeArray($tableName, null, true, false, true, $classRelations, $relation),
                        "\n"))
            ), $r);
        } catch (Exception $e) {
            $this->endTry();
            return "Unable read definition of tables " . $e->getMessage();
        }
        $this->endTry();
        return $r;
    }


    //</editor-fold>
    //<editor-fold desc="cli functions" defaultstate="collapsed" >
    public function generateCodeClassRepo(
        $tableName,
        $namespace = '',
        $classRelations = [],
        $modelfullClass = '',
        $resultColumns = [],
        $aliases = []
    )
    {
        $this->beginTry();
        //
        $filename = __DIR__ . '/template/template_classrepo.php';
        $r = $this->phpstart . $this->openTemplate($filename);
        $fa = func_get_args();
        foreach ($fa as $f => $k) {
            if (is_array($k)) {
                $fa[$f] = str_replace([' ', "\r\n", "\n"], ['', '', ''], var_export($k, true));
            } else {
                $fa[$f] = "'$k'";
            }
        }
        if ($modelfullClass) {
            $arr = explode('\\', $modelfullClass);
            $modelClass = end($arr);
            $modelUse = true;
        } else {
            $modelClass = false;
            $modelUse = false;
        }
        $helpcolumns = '';
        $related = '';
        foreach ($resultColumns as $v) {
            if ($v[2]) {
                $related .= " * @see $v[2]\n";
                $c = '(' . $v[2] . ')';
            } else {
                $c = '';
            }
            $alias = $aliases[$tableName][$v[0]] ?? $v[0];
            $helpcolumns .= " * <li>$alias: $v[1] (alias of column $v[0]) $c</li>\n";
        }
        $this->endTry();
        return str_replace(array(
            '{version}',
            '{classname}',
            '{exception}',
            '{args}',
            '{table}',
            '{namespace}',
            '{modelnamespace}',
            '{modelclass}',
            '{modeluse}',
            '{helpcolumns}',
            '{related}'
        ), array(
            self::VERSION . ' Date generated ' . date('r'), // {version}
            $classRelations[$tableName], // {class}
            ($namespace) ? 'use Exception;' : '',
            "'" . implode("','", $fa) . "'", // {args}
            $tableName, //{table}
            ($namespace) ? "namespace $namespace;" : '', // {namespace}
            $modelfullClass ? "use $modelfullClass;" : '', // {modelnamespace}
            $modelClass ? "const MODEL= $modelClass::class;" : '', // {modelclass}
            $modelUse ? 'true' : 'false', // {modeluse},
            rtrim($helpcolumns), // {helpcolumns}
            rtrim($related)
        ), $r);
    }

    /**
     * Flush and disable the internal cache. By default, the internal cache is not used unless it is set.
     *
     * @param bool $useInternalCache if true then it enables the internal cache.
     *
     * @see \eftec\PdoOne::setUseInternalCache
     */
    public function flushInternalCache(bool $useInternalCache = false): void
    {
        $this->internalCacheCounter = 0;
        $this->internalCache = [];
        $this->useInternalCache = $useInternalCache;
    }

    /**
     * It stores a cache. This method is used internally by PdoOne.<br>
     *
     * @param string          $uid    The unique id. It is generated by sha256 based in the query, parameters, type of
     *                                query and method.
     * @param string|string[] $family [optional] It is the family or group of the cache. It could be used to invalidate
     *                                the whole group. For example, to invalidate all the cache related with a table.
     * @param mixed|null      $data   The data to store
     * @param null|bool|int   $ttl    If null then the cache never expires.<br>
     *                                If false then we don't use cache.<br>
     *                                If int then it is the duration of the cache (in seconds)
     *
     * @return void.
     */
    public function setCache(string $uid, $family = '', $data = null, $ttl = null): void
    {
        if ($family === '*') {
            $family = $this->tables;
        }
        $this->cacheService->setCache($uid, $family, $data, $ttl);
    }

    /**
     * Invalidate a single cache or a list of cache based in a single uid or in
     * a family/group of cache.
     *
     * @param string|string[] $uid        The unique id. It is generated by sha256 (or by $hashtype)
     *                                    based in the query, parameters, type
     *                                    of query and method.
     * @param string|string[] $family     [optional] It is the family or group
     *                                    of
     *                                    the cache. It could be used to
     *                                    invalidate the whole group. For
     *                                    example, to invalidate all the cache
     *                                    related with a table.
     *
     * @return $this
     * @see \eftec\PdoOneEncryption::$hashType
     */
    public function invalidateCache($uid = '', $family = ''): PdoOne
    {
        if ($this->cacheService !== null) {
            if ($family === '*') {
                $family = $this->tables;
            }
            $this->cacheService->invalidateCache($uid, $family);
        }
        return $this;
    }

    /**
     * Returns the number of affected rows.
     *
     * @param PDOStatement|null|bool $stmt
     *
     * @return int
     */
    public function affected_rows($stmt = null): int
    {
        if ($stmt instanceof PDOStatement && !$this->isOpen) {
            return $stmt->rowCount();
        }
        return $this->affected_rows; // returns previous calculated information
    }

    /**
     * Returns the last inserted identity.
     *
     * @param string|null $sequenceName [optional] the name of the sequence
     *
     * @return int|bool a number or 0 if it is not found
     */
    public function insert_id(?string $sequenceName = null)
    {
        if (!$this->isOpen) {
            return -1;
        }
        $id = $this->conn1->lastInsertId($sequenceName);
        return $id === false ? false : (int)$id;
    }

    /**
     * @return IPdoOneCache|null
     */
    public function getCacheService(): ?object
    {
        return $this->cacheService;
    }

    /**
     * It sets the cache service (optional).
     *
     * @param object|null $cacheService Instance of an object that implements IPdoOneCache
     *
     * @return $this
     */
    public function setCacheService(?object $cacheService): PdoOne
    {
        $this->cacheService = $cacheService;
        return $this;
    }

    /**
     * @param string|int $password      <p>Use an integer if the method is
     *                                  INTEGER</p>
     * @param string     $salt          <p>Salt is not used by SIMPLE or
     *                                  INTEGER</p>
     * @param string     $encMethod     <p>Example: AES-256-CTR See
     *                                  http://php.net/manual/en/function.openssl-get-cipher-methods.php
     *                                  </p>
     *                                  <p>if SIMPLE then the encryption is
     *                                  simplified (generates a short
     *                                  result)</p>
     *                                  <p>if INTEGER then the encryption is
     *                                  even simple (generates an integer)</p>
     *
     * @return PdoOne
     * @throws Exception
     * @test void this('123','somesalt','AES-256-CTR')
     */
    public function setEncryption($password, string $salt, string $encMethod = 'AES-256-CTR'): PdoOne
    {
        $this->beginTry();
        if (!extension_loaded('openssl')) {
            $this->encryption->encEnabled = false;
            $this->throwError('OpenSSL not loaded, encryption disabled', '');
        } else {
            $this->encryption->encEnabled = true;
            $this->encryption->setEncryption($password, $salt, $encMethod);
        }
        $this->endTry();
        return $this;
    }

    /**
     * Wrapper of PdoOneEncryption->encrypt
     *
     * @param mixed $data The data to encrypt.<br>
     *                    If the method of encryption is INTEGER, then this number must be an INTEGER<br>
     *                    If the method of encryption is SIMPLE, then this value must be a primitive value<br>
     *                    If the method is other, then it could be any method compatible with your installation<br>
     *
     * @return int|string|null
     * @see \eftec\PdoOneEncryption::encrypt
     */
    public function encrypt($data)
    {
        return $this->encryption->encrypt($data);
    }

    /**
     * It generates a hash based in the hash type ($this->hashType), the data used and the SALT.
     * @param mixed $data It could be any type of serializable data.
     * @return false|string If the serialization is not set, then it returns the same value.
     */
    public function hash($data)
    {
        return $this->encryption->hash($data);
    }

    /**
     * Wrapper of PdoOneEncryption->decrypt. It decrypts an information if the algoritm allows to decrypt.<br>
     * The method of encryptation, SALT and PASSWORD must be the same.
     *
     * @param mixed $data The data to decrypt.
     * @return bool|string|int
     * @see \eftec\PdoOneEncryption::decrypt
     * @see https://www.php.net/manual/en/function.openssl-get-cipher-methods.php
     */
    public function decrypt($data)
    {
        return $this->encryption->decrypt($data);
    }

    public function render(): void
    {
        if ($this->logLevel) {
            ob_clean();
        }
        /** @noinspection PhpIfWithCommonPartsInspection */
        if (!$this->logLevel) {
            $web = <<<'LOGS'
<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title>PdoOne Login Screen</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="shortcut icon" href="https://raw.githubusercontent.com/EFTEC/AutoLoadOne/master/doc/favicon.ico">
LOGS;
            $web .= $this->bootstrapcss();
            $web .= <<<'LOGS'
  </head>

  <body>
  <br>
    <div class="section">
      <div class="container">
        <div class="row">
          <div class="col-md-12">
            <div class="panel panel-primary">
              <div class="panel-heading">
                <h3 class="panel-title">Login Screen</h3>
              </div>
              <div class="panel-body">
                <form class="form-horizontal" role="form" method="post" spellcheck="false">
                  <div class="form-group">
                    <div class="col-sm-2">
                      <label for="inputEmail3" class="control-label">User</label>
                    </div>
                    <div class="col-sm-10">
                      <input type="text" name="user" class="form-control" id="inputEmail3" placeholder="User">
                    </div>
                  </div>
                  <div class="form-group">
                    <div class="col-sm-2">
                      <label for="inputPassword3" class="control-label">Password</label>
                    </div>
                    <div class="col-sm-10">
                      <input type="password" name="password" class="form-control" id="inputPassword3" placeholder="Password">
                    </div>
                  </div>
                  <div class="form-group">
                    <div class="col-sm-offset-2 col-sm-10">
                      <button type="submit" class="btn btn-default">Sign in</button>
                    </div>
                  </div>
                </form>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </body>

</html>
LOGS;
            echo $web;
        } else {
            $web = <<<'TEM1'
<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title>PdoOne {{version}}</title>

    <link rel="shortcut icon" href="https://raw.githubusercontent.com/EFTEC/AutoLoadOne/master/doc/favicon.ico">
    <meta name="viewport" content="width=device-width, initial-scale=1">
TEM1;
            $web .= $this->bootstrapcss();
            $web .= <<<'TEM1'
</head>

  <body>
  <br>
    <div class="section">
      <div class="container-fluid">
        <div class="row">
          <div class="col-md-12">
            <div class="panel panel-primary">
              <div class="panel-heading">
                <h3 class="panel-title">PdoOne {{version}}.<div  class='pull-right' ><a style="color:white;" href="https://github.com/EFTEC/AutoLoadOne">Help Page</a></div></h3>
              </div>
              <div class="panel-body">
                <form class="form-horizontal" role="form" method="post">
                  <div class="form-group">
                    <div class="col-sm-offset-2 col-sm-10">
                      <button type="submit" name="button" value="1" class="btn btn-primary">Generate</button>
                      &nbsp;&nbsp;&nbsp;
                      <button type="submit" name="button" value="logout" class="btn btn-default">Logout</button>
                    </div>
                  </div>
                  <!-- database -->
                  <div class="form-group">
                    <div class="col-sm-2">
                      <label class="control-label">database <span class="text-danger">(Req)</span> </label>
                    </div>
                    <div class="col-sm-10">
                    <select name="database" class="form-control">
                        <option value="">--select a database--</option>
                        {{database}}
                    </select>
                      <em><b>Examples:</b> mysql, sqlsrv</em>
                    </div>
                  </div>
                  <!-- end database -->
                  <!-- server -->
                  <div class="form-group">
                    <div class="col-sm-2">
                      <label class="control-label">server <span class="text-danger">(Req)</span> </label>
                    </div>
                    <div class="col-sm-10">
                      <input type="text" class="form-control" placeholder="ex. 127.0.0.1 or (local)\sqlexpress"
                      name="server" value="{{server}}">
                      <em><b>Examples:</b> 127.0.0.1 or (local)\sqlexpress</em>
                    </div>
                  </div>
                  <!-- end server -->
                  <!-- user -->
                  <div class="form-group">
                    <div class="col-sm-2">
                      <label class="control-label">user <span class="text-danger">(Req)</span> </label>
                    </div>
                    <div class="col-sm-10">
                      <input type="text" class="form-control" placeholder="username"
                      name="user" value="{{user}}">
                      <em><b>Examples:</b> root, sa</em>
                    </div>
                  </div>
                  <!-- end user -->
                  <!-- pwd -->
                  <div class="form-group">
                    <div class="col-sm-2">
                      <label class="control-label">pwd <span class="text-danger">(Req)</span> </label>
                    </div>
                    <div class="col-sm-10">
                      <input type="text" class="form-control" placeholder="password"
                      name="pwd" value="{{pwd}}">
                      <em><b>Examples:</b> abc.123, 12345 (note: the password is visible)</em>
                    </div>
                  </div>
                  <!-- end pwd -->
                  <!-- db -->
                  <div class="form-group">
                    <div class="col-sm-2">
                      <label class="control-label">db <span class="text-danger">(Req)</span> </label>
                    </div>
                    <div class="col-sm-10">
                      <input type="text" class="form-control" placeholder="db"
                      name="db" value="{{db}}">
                      <em><b>Examples:</b> sakila, contoso, adventureworks</em>
                    </div>
                  </div>
                  <!-- end db -->
                  <!-- input -->
                  <div class="form-group">
                    <div class="col-sm-2">
                      <label class="control-label">input <span class="text-danger">(Req)</span> </label>
                    </div>
                    <div class="col-sm-10">
                        <textarea class="form-control" rows="10" name="input">{{input}}</textarea>

                      <em><b>Examples:</b> select * from table , tablename</em>
                    </div>
                  </div>
                  <!-- end input -->
                  <!-- output -->
                  <div class="form-group">
                    <div class="col-sm-2">
                      <label class="control-label">output <span class="text-danger">(Req)</span> </label>
                    </div>
                    <div class="col-sm-10">
                        <select name="output" class="form-control">
                            <option value="">--select an output--</option>
                            {{output}}
                        </select>

                      <em><b>Examples:</b> classcode,selectcode,arraycode,csv,json</em>
                    </div>
                  </div>
                  <!-- end output -->
                  <!-- pk -->
                  <div class="form-group">
                    <div class="col-sm-2">
                      <label class="control-label">pk <span class="text-danger">(Opt)</span> </label>
                    </div>
                    <div class="col-sm-10">
                      <input type="text" class="form-control" placeholder="namespace"
                      name="namespace" value="{{namespace}}">
                      <em><b>Examples:</b> namespace1\namespace2</em>
                    </div>
                  </div>
                  <!-- end pk -->
                  <!-- result -->
                  <div class="form-group" >
                    <div class="col-sm-2">
                      <label class="control-label">Log</label>
                    </div>
                    <div class="col-sm-10">
                      <textarea class="form-control" style="height:150px; overflow-y: scroll;">{{log}}</textarea>
                    </div>
                  </div>
                  <!-- result -->


                  <div class="form-group">
                    <div class="col-sm-offset-2 col-sm-10">
                      <button type="submit" name="button" value="1" class="btn btn-primary">Generate</button>
                      &nbsp;&nbsp;&nbsp;
                      <button type="submit" name="button" value="logout" class="btn btn-default">Logout</button>
                    </div>
                  </div>

                </form>
              </div>
              <div class="panel-footer">
                <h3 class="panel-title">&copy; <a href="https://github.com/EFTEC/AutoLoadOne">Jorge Castro C.</a> {{ms}}</h3>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </body>

</html> 
TEM1;
            $database = @$_POST['database'];
            $server = @$_POST['server'];
            $user = @$_POST['user'];
            $pwd = @$_POST['pwd'];
            $db = @$_POST['db'];
            $input = @$_POST['input'];
            $output = @$_POST['output'];
            $namespace = @$_POST['namespace'];
            $button = @$_POST['button'];
            $log = '';
            if ($button) {
                try {
                    $log = $this->run($database, $server, $user, $pwd, $db, $input, $output, $namespace);
                } catch (Exception $e) {
                    $log = $e->getMessage();
                }
            }
            $web = str_replace('{{version}}', $this::VERSION, $web);
            $valid = ['mysql', 'sqlsrv', 'oci'];
            $web = str_replace(array('{{database}}', '{{server}}', '{{user}}', '{{pwd}}', '{{db}}', '{{input}}'),
                array($this->runUtilCombo($valid, $database), $server, $user, $pwd, $db, $input), $web);
            $valid = [
                'classcode',
                'selectcode',
                'createcode',
                'arraycode',
                'csv',
                'json',
            ];
            $web = str_replace(array('{{output}}', '{{namespace}}', '{{log}}'),
                array($this->runUtilCombo($valid, $output), $namespace, $log), $web);
            $ms = 1;
            $web = str_replace('{{ms}}', $ms, $web);
            echo $web;
        }
    }

    public function bootstrapcss(): string
    {
        return <<<BOOTS
    	<style>
html{font-family:sans-serif;-webkit-text-size-adjust:100%;-ms-text-size-adjust:100%}body{margin:0}article,aside,details,figcaption,figure,footer,header,hgroup,main,menu,nav,section,summary{display:block}audio,canvas,progress,video{display:inline-block;vertical-align:baseline}audio:not([controls]){display:none;height:0}[hidden],template{display:none}a{background-color:transparent}a:active,a:hover{outline:0}abbr[title]{border-bottom:1px dotted}b,strong{font-weight:700}dfn{font-style:italic}h1{margin:.67em 0;font-size:2em}mark{color:#000;background:#ff0}small{font-size:80%}sub,sup{position:relative;font-size:75%;line-height:0;vertical-align:baseline}sup{top:-.5em}sub{bottom:-.25em}img{border:0}svg:not(:root){overflow:hidden}figure{margin:1em 40px}hr{height:0;-webkit-box-sizing:content-box;-moz-box-sizing:content-box;box-sizing:content-box}pre{overflow:auto}code,kbd,pre,samp{font-family:monospace,monospace;font-size:1em}button,input,optgroup,select,textarea{margin:0;font:inherit;color:inherit}button{overflow:visible}button,select{text-transform:none}button,html input[type=button],input[type=reset],input[type=submit]{-webkit-appearance:button;cursor:pointer}button[disabled],html input[disabled]{cursor:default}button::-moz-focus-inner,input::-moz-focus-inner{padding:0;border:0}input{line-height:normal}input[type=checkbox],input[type=radio]{-webkit-box-sizing:border-box;-moz-box-sizing:border-box;box-sizing:border-box;padding:0}input[type=number]::-webkit-inner-spin-button,input[type=number]::-webkit-outer-spin-button{height:auto}input[type=search]{-webkit-box-sizing:content-box;-moz-box-sizing:content-box;box-sizing:content-box;-webkit-appearance:textfield}input[type=search]::-webkit-search-cancel-button,input[type=search]::-webkit-search-decoration{-webkit-appearance:none}fieldset{padding:.35em .625em .75em;margin:0 2px;border:1px solid silver}legend{padding:0;border:0}textarea{overflow:auto}optgroup{font-weight:700}table{border-spacing:0;border-collapse:collapse}td,th{padding:0}/*! Source: https://github.com/h5bp/html5-boilerplate/blob/master/src/css/main.css */@media print{*,:after,:before{color:#000!important;text-shadow:none!important;background:0 0!important;-webkit-box-shadow:none!important;box-shadow:none!important}a,a:visited{text-decoration:underline}a[href]:after{content:" (" attr(href) ")"}abbr[title]:after{content:" (" attr(title) ")"}a[href^="#"]:after,a[href^="javascript:"]:after{content:""}blockquote,pre{border:1px solid #999;page-break-inside:avoid}thead{display:table-header-group}img,tr{page-break-inside:avoid}img{max-width:100%!important}h2,h3,p{orphans:3;widows:3}h2,h3{page-break-after:avoid}.navbar{display:none}.btn>.caret,.dropup>.btn>.caret{border-top-color:#000!important}.label{border:1px solid #000}.table{border-collapse:collapse!important}.table td,.table th{background-color:#fff!important}.table-bordered td,.table-bordered th{border:1px solid #ddd!important}}*{-webkit-box-sizing:border-box;-moz-box-sizing:border-box;box-sizing:border-box}:after,:before{-webkit-box-sizing:border-box;-moz-box-sizing:border-box;box-sizing:border-box}html{font-size:10px;-webkit-tap-highlight-color:transparent}body{font-family:"Helvetica Neue",Helvetica,Arial,sans-serif;font-size:14px;line-height:1.42857143;color:#333;background-color:#fff}button,input,select,textarea{font-family:inherit;font-size:inherit;line-height:inherit}a{color:#337ab7;text-decoration:none}a:focus,a:hover{color:#23527c;text-decoration:underline}a:focus{outline:5px auto -webkit-focus-ring-color;outline-offset:-2px}figure{margin:0}img{vertical-align:middle}.carousel-inner>.item>a>img,.carousel-inner>.item>img,.img-responsive,.thumbnail a>img,.thumbnail>img{display:block;max-width:100%;height:auto}.img-rounded{border-radius:6px}.img-thumbnail{display:inline-block;max-width:100%;height:auto;padding:4px;line-height:1.42857143;background-color:#fff;border:1px solid #ddd;border-radius:4px;-webkit-transition:all .2s ease-in-out;-o-transition:all .2s ease-in-out;transition:all .2s ease-in-out}.img-circle{border-radius:50%}hr{margin-top:20px;margin-bottom:20px;border:0;border-top:1px solid #eee}.sr-only{position:absolute;width:1px;height:1px;padding:0;margin:-1px;overflow:hidden;clip:rect(0,0,0,0);border:0}.sr-only-focusable:active,.sr-only-focusable:focus{position:static;width:auto;height:auto;margin:0;overflow:visible;clip:auto}[role=button]{cursor:pointer}.h1,.h2,.h3,.h4,.h5,.h6,h1,h2,h3,h4,h5,h6{font-family:inherit;font-weight:500;line-height:1.1;color:inherit}.h1 .small,.h1 small,.h2 .small,.h2 small,.h3 .small,.h3 small,.h4 .small,.h4 small,.h5 .small,.h5 small,.h6 .small,.h6 small,h1 .small,h1 small,h2 .small,h2 small,h3 .small,h3 small,h4 .small,h4 small,h5 .small,h5 small,h6 .small,h6 small{font-weight:400;line-height:1;color:#777}.h1,.h2,.h3,h1,h2,h3{margin-top:20px;margin-bottom:10px}.h1 .small,.h1 small,.h2 .small,.h2 small,.h3 .small,.h3 small,h1 .small,h1 small,h2 .small,h2 small,h3 .small,h3 small{font-size:65%}.h4,.h5,.h6,h4,h5,h6{margin-top:10px;margin-bottom:10px}.h4 .small,.h4 small,.h5 .small,.h5 small,.h6 .small,.h6 small,h4 .small,h4 small,h5 .small,h5 small,h6 .small,h6 small{font-size:75%}.h1,h1{font-size:36px}.h2,h2{font-size:30px}.h3,h3{font-size:24px}.h4,h4{font-size:18px}.h5,h5{font-size:14px}.h6,h6{font-size:12px}p{margin:0 0 10px}.lead{margin-bottom:20px;font-size:16px;font-weight:300;line-height:1.4}@media (min-width:768px){.lead{font-size:21px}}.small,small{font-size:85%}.mark,mark{padding:.2em;background-color:#fcf8e3}.text-left{text-align:left}.text-right{text-align:right}.text-center{text-align:center}.text-justify{text-align:justify}.text-nowrap{white-space:nowrap}.text-lowercase{text-transform:lowercase}.text-uppercase{text-transform:uppercase}.text-capitalize{text-transform:capitalize}.text-muted{color:#777}.text-primary{color:#337ab7}a.text-primary:focus,a.text-primary:hover{color:#286090}.text-success{color:#3c763d}a.text-success:focus,a.text-success:hover{color:#2b542c}.text-info{color:#31708f}a.text-info:focus,a.text-info:hover{color:#245269}.text-warning{color:#8a6d3b}a.text-warning:focus,a.text-warning:hover{color:#66512c}.text-danger{color:#a94442}a.text-danger:focus,a.text-danger:hover{color:#843534}.bg-primary{color:#fff;background-color:#337ab7}a.bg-primary:focus,a.bg-primary:hover{background-color:#286090}.bg-success{background-color:#dff0d8}a.bg-success:focus,a.bg-success:hover{background-color:#c1e2b3}.bg-info{background-color:#d9edf7}a.bg-info:focus,a.bg-info:hover{background-color:#afd9ee}.bg-warning{background-color:#fcf8e3}a.bg-warning:focus,a.bg-warning:hover{background-color:#f7ecb5}.bg-danger{background-color:#f2dede}a.bg-danger:focus,a.bg-danger:hover{background-color:#e4b9b9}.page-header{padding-bottom:9px;margin:40px 0 20px;border-bottom:1px solid #eee}ol,ul{margin-top:0;margin-bottom:10px}ol ol,ol ul,ul ol,ul ul{margin-bottom:0}.list-unstyled{padding-left:0;list-style:none}.list-inline{padding-left:0;margin-left:-5px;list-style:none}.list-inline>li{display:inline-block;padding-right:5px;padding-left:5px}dl{margin-top:0;margin-bottom:20px}dd,dt{line-height:1.42857143}dt{font-weight:700}dd{margin-left:0}@media (min-width:768px){.dl-horizontal dt{float:left;width:160px;overflow:hidden;clear:left;text-align:right;text-overflow:ellipsis;white-space:nowrap}.dl-horizontal dd{margin-left:180px}}abbr[data-original-title],abbr[title]{cursor:help;border-bottom:1px dotted #777}.initialism{font-size:90%;text-transform:uppercase}blockquote{padding:10px 20px;margin:0 0 20px;font-size:17.5px;border-left:5px solid #eee}blockquote ol:last-child,blockquote p:last-child,blockquote ul:last-child{margin-bottom:0}blockquote .small,blockquote footer,blockquote small{display:block;font-size:80%;line-height:1.42857143;color:#777}blockquote .small:before,blockquote footer:before,blockquote small:before{content:'\2014 \00A0'}.blockquote-reverse,blockquote.pull-right{padding-right:15px;padding-left:0;text-align:right;border-right:5px solid #eee;border-left:0}.blockquote-reverse .small:before,.blockquote-reverse footer:before,.blockquote-reverse small:before,blockquote.pull-right .small:before,blockquote.pull-right footer:before,blockquote.pull-right small:before{content:''}.blockquote-reverse .small:after,.blockquote-reverse footer:after,.blockquote-reverse small:after,blockquote.pull-right .small:after,blockquote.pull-right footer:after,blockquote.pull-right small:after{content:'\00A0 \2014'}address{margin-bottom:20px;font-style:normal;line-height:1.42857143}code,kbd,pre,samp{font-family:Menlo,Monaco,Consolas,"Courier New",monospace}code{padding:2px 4px;font-size:90%;color:#c7254e;background-color:#f9f2f4;border-radius:4px}kbd{padding:2px 4px;font-size:90%;color:#fff;background-color:#333;border-radius:3px;-webkit-box-shadow:inset 0 -1px 0 rgba(0,0,0,.25);box-shadow:inset 0 -1px 0 rgba(0,0,0,.25)}kbd kbd{padding:0;font-size:100%;font-weight:700;-webkit-box-shadow:none;box-shadow:none}pre{display:block;padding:9.5px;margin:0 0 10px;font-size:13px;line-height:1.42857143;color:#333;word-break:break-all;word-wrap:break-word;background-color:#f5f5f5;border:1px solid #ccc;border-radius:4px}pre code{padding:0;font-size:inherit;color:inherit;white-space:pre-wrap;background-color:transparent;border-radius:0}.pre-scrollable{max-height:340px;overflow-y:scroll}.container{padding-right:15px;padding-left:15px;margin-right:auto;margin-left:auto}@media (min-width:768px){.container{width:750px}}@media (min-width:992px){.container{width:970px}}@media (min-width:1200px){.container{width:1170px}}.container-fluid{padding-right:15px;padding-left:15px;margin-right:auto;margin-left:auto}.row{margin-right:-15px;margin-left:-15px}.col-lg-1,.col-lg-10,.col-lg-11,.col-lg-12,.col-lg-2,.col-lg-3,.col-lg-4,.col-lg-5,.col-lg-6,.col-lg-7,.col-lg-8,.col-lg-9,.col-md-1,.col-md-10,.col-md-11,.col-md-12,.col-md-2,.col-md-3,.col-md-4,.col-md-5,.col-md-6,.col-md-7,.col-md-8,.col-md-9,.col-sm-1,.col-sm-10,.col-sm-11,.col-sm-12,.col-sm-2,.col-sm-3,.col-sm-4,.col-sm-5,.col-sm-6,.col-sm-7,.col-sm-8,.col-sm-9,.col-xs-1,.col-xs-10,.col-xs-11,.col-xs-12,.col-xs-2,.col-xs-3,.col-xs-4,.col-xs-5,.col-xs-6,.col-xs-7,.col-xs-8,.col-xs-9{position:relative;min-height:1px;padding-right:15px;padding-left:15px}.col-xs-1,.col-xs-10,.col-xs-11,.col-xs-12,.col-xs-2,.col-xs-3,.col-xs-4,.col-xs-5,.col-xs-6,.col-xs-7,.col-xs-8,.col-xs-9{float:left}.col-xs-12{width:100%}.col-xs-11{width:91.66666667%}.col-xs-10{width:83.33333333%}.col-xs-9{width:75%}.col-xs-8{width:66.66666667%}.col-xs-7{width:58.33333333%}.col-xs-6{width:50%}.col-xs-5{width:41.66666667%}.col-xs-4{width:33.33333333%}.col-xs-3{width:25%}.col-xs-2{width:16.66666667%}.col-xs-1{width:8.33333333%}.col-xs-pull-12{right:100%}.col-xs-pull-11{right:91.66666667%}.col-xs-pull-10{right:83.33333333%}.col-xs-pull-9{right:75%}.col-xs-pull-8{right:66.66666667%}.col-xs-pull-7{right:58.33333333%}.col-xs-pull-6{right:50%}.col-xs-pull-5{right:41.66666667%}.col-xs-pull-4{right:33.33333333%}.col-xs-pull-3{right:25%}.col-xs-pull-2{right:16.66666667%}.col-xs-pull-1{right:8.33333333%}.col-xs-pull-0{right:auto}.col-xs-push-12{left:100%}.col-xs-push-11{left:91.66666667%}.col-xs-push-10{left:83.33333333%}.col-xs-push-9{left:75%}.col-xs-push-8{left:66.66666667%}.col-xs-push-7{left:58.33333333%}.col-xs-push-6{left:50%}.col-xs-push-5{left:41.66666667%}.col-xs-push-4{left:33.33333333%}.col-xs-push-3{left:25%}.col-xs-push-2{left:16.66666667%}.col-xs-push-1{left:8.33333333%}.col-xs-push-0{left:auto}.col-xs-offset-12{margin-left:100%}.col-xs-offset-11{margin-left:91.66666667%}.col-xs-offset-10{margin-left:83.33333333%}.col-xs-offset-9{margin-left:75%}.col-xs-offset-8{margin-left:66.66666667%}.col-xs-offset-7{margin-left:58.33333333%}.col-xs-offset-6{margin-left:50%}.col-xs-offset-5{margin-left:41.66666667%}.col-xs-offset-4{margin-left:33.33333333%}.col-xs-offset-3{margin-left:25%}.col-xs-offset-2{margin-left:16.66666667%}.col-xs-offset-1{margin-left:8.33333333%}.col-xs-offset-0{margin-left:0}@media (min-width:768px){.col-sm-1,.col-sm-10,.col-sm-11,.col-sm-12,.col-sm-2,.col-sm-3,.col-sm-4,.col-sm-5,.col-sm-6,.col-sm-7,.col-sm-8,.col-sm-9{float:left}.col-sm-12{width:100%}.col-sm-11{width:91.66666667%}.col-sm-10{width:83.33333333%}.col-sm-9{width:75%}.col-sm-8{width:66.66666667%}.col-sm-7{width:58.33333333%}.col-sm-6{width:50%}.col-sm-5{width:41.66666667%}.col-sm-4{width:33.33333333%}.col-sm-3{width:25%}.col-sm-2{width:16.66666667%}.col-sm-1{width:8.33333333%}.col-sm-pull-12{right:100%}.col-sm-pull-11{right:91.66666667%}.col-sm-pull-10{right:83.33333333%}.col-sm-pull-9{right:75%}.col-sm-pull-8{right:66.66666667%}.col-sm-pull-7{right:58.33333333%}.col-sm-pull-6{right:50%}.col-sm-pull-5{right:41.66666667%}.col-sm-pull-4{right:33.33333333%}.col-sm-pull-3{right:25%}.col-sm-pull-2{right:16.66666667%}.col-sm-pull-1{right:8.33333333%}.col-sm-pull-0{right:auto}.col-sm-push-12{left:100%}.col-sm-push-11{left:91.66666667%}.col-sm-push-10{left:83.33333333%}.col-sm-push-9{left:75%}.col-sm-push-8{left:66.66666667%}.col-sm-push-7{left:58.33333333%}.col-sm-push-6{left:50%}.col-sm-push-5{left:41.66666667%}.col-sm-push-4{left:33.33333333%}.col-sm-push-3{left:25%}.col-sm-push-2{left:16.66666667%}.col-sm-push-1{left:8.33333333%}.col-sm-push-0{left:auto}.col-sm-offset-12{margin-left:100%}.col-sm-offset-11{margin-left:91.66666667%}.col-sm-offset-10{margin-left:83.33333333%}.col-sm-offset-9{margin-left:75%}.col-sm-offset-8{margin-left:66.66666667%}.col-sm-offset-7{margin-left:58.33333333%}.col-sm-offset-6{margin-left:50%}.col-sm-offset-5{margin-left:41.66666667%}.col-sm-offset-4{margin-left:33.33333333%}.col-sm-offset-3{margin-left:25%}.col-sm-offset-2{margin-left:16.66666667%}.col-sm-offset-1{margin-left:8.33333333%}.col-sm-offset-0{margin-left:0}}@media (min-width:992px){.col-md-1,.col-md-10,.col-md-11,.col-md-12,.col-md-2,.col-md-3,.col-md-4,.col-md-5,.col-md-6,.col-md-7,.col-md-8,.col-md-9{float:left}.col-md-12{width:100%}.col-md-11{width:91.66666667%}.col-md-10{width:83.33333333%}.col-md-9{width:75%}.col-md-8{width:66.66666667%}.col-md-7{width:58.33333333%}.col-md-6{width:50%}.col-md-5{width:41.66666667%}.col-md-4{width:33.33333333%}.col-md-3{width:25%}.col-md-2{width:16.66666667%}.col-md-1{width:8.33333333%}.col-md-pull-12{right:100%}.col-md-pull-11{right:91.66666667%}.col-md-pull-10{right:83.33333333%}.col-md-pull-9{right:75%}.col-md-pull-8{right:66.66666667%}.col-md-pull-7{right:58.33333333%}.col-md-pull-6{right:50%}.col-md-pull-5{right:41.66666667%}.col-md-pull-4{right:33.33333333%}.col-md-pull-3{right:25%}.col-md-pull-2{right:16.66666667%}.col-md-pull-1{right:8.33333333%}.col-md-pull-0{right:auto}.col-md-push-12{left:100%}.col-md-push-11{left:91.66666667%}.col-md-push-10{left:83.33333333%}.col-md-push-9{left:75%}.col-md-push-8{left:66.66666667%}.col-md-push-7{left:58.33333333%}.col-md-push-6{left:50%}.col-md-push-5{left:41.66666667%}.col-md-push-4{left:33.33333333%}.col-md-push-3{left:25%}.col-md-push-2{left:16.66666667%}.col-md-push-1{left:8.33333333%}.col-md-push-0{left:auto}.col-md-offset-12{margin-left:100%}.col-md-offset-11{margin-left:91.66666667%}.col-md-offset-10{margin-left:83.33333333%}.col-md-offset-9{margin-left:75%}.col-md-offset-8{margin-left:66.66666667%}.col-md-offset-7{margin-left:58.33333333%}.col-md-offset-6{margin-left:50%}.col-md-offset-5{margin-left:41.66666667%}.col-md-offset-4{margin-left:33.33333333%}.col-md-offset-3{margin-left:25%}.col-md-offset-2{margin-left:16.66666667%}.col-md-offset-1{margin-left:8.33333333%}.col-md-offset-0{margin-left:0}}@media (min-width:1200px){.col-lg-1,.col-lg-10,.col-lg-11,.col-lg-12,.col-lg-2,.col-lg-3,.col-lg-4,.col-lg-5,.col-lg-6,.col-lg-7,.col-lg-8,.col-lg-9{float:left}.col-lg-12{width:100%}.col-lg-11{width:91.66666667%}.col-lg-10{width:83.33333333%}.col-lg-9{width:75%}.col-lg-8{width:66.66666667%}.col-lg-7{width:58.33333333%}.col-lg-6{width:50%}.col-lg-5{width:41.66666667%}.col-lg-4{width:33.33333333%}.col-lg-3{width:25%}.col-lg-2{width:16.66666667%}.col-lg-1{width:8.33333333%}.col-lg-pull-12{right:100%}.col-lg-pull-11{right:91.66666667%}.col-lg-pull-10{right:83.33333333%}.col-lg-pull-9{right:75%}.col-lg-pull-8{right:66.66666667%}.col-lg-pull-7{right:58.33333333%}.col-lg-pull-6{right:50%}.col-lg-pull-5{right:41.66666667%}.col-lg-pull-4{right:33.33333333%}.col-lg-pull-3{right:25%}.col-lg-pull-2{right:16.66666667%}.col-lg-pull-1{right:8.33333333%}.col-lg-pull-0{right:auto}.col-lg-push-12{left:100%}.col-lg-push-11{left:91.66666667%}.col-lg-push-10{left:83.33333333%}.col-lg-push-9{left:75%}.col-lg-push-8{left:66.66666667%}.col-lg-push-7{left:58.33333333%}.col-lg-push-6{left:50%}.col-lg-push-5{left:41.66666667%}.col-lg-push-4{left:33.33333333%}.col-lg-push-3{left:25%}.col-lg-push-2{left:16.66666667%}.col-lg-push-1{left:8.33333333%}.col-lg-push-0{left:auto}.col-lg-offset-12{margin-left:100%}.col-lg-offset-11{margin-left:91.66666667%}.col-lg-offset-10{margin-left:83.33333333%}.col-lg-offset-9{margin-left:75%}.col-lg-offset-8{margin-left:66.66666667%}.col-lg-offset-7{margin-left:58.33333333%}.col-lg-offset-6{margin-left:50%}.col-lg-offset-5{margin-left:41.66666667%}.col-lg-offset-4{margin-left:33.33333333%}.col-lg-offset-3{margin-left:25%}.col-lg-offset-2{margin-left:16.66666667%}.col-lg-offset-1{margin-left:8.33333333%}.col-lg-offset-0{margin-left:0}}table{background-color:transparent}caption{padding-top:8px;padding-bottom:8px;color:#777;text-align:left}th{text-align:left}.table{width:100%;max-width:100%;margin-bottom:20px}.table>tbody>tr>td,.table>tbody>tr>th,.table>tfoot>tr>td,.table>tfoot>tr>th,.table>thead>tr>td,.table>thead>tr>th{padding:8px;line-height:1.42857143;vertical-align:top;border-top:1px solid #ddd}.table>thead>tr>th{vertical-align:bottom;border-bottom:2px solid #ddd}.table>caption+thead>tr:first-child>td,.table>caption+thead>tr:first-child>th,.table>colgroup+thead>tr:first-child>td,.table>colgroup+thead>tr:first-child>th,.table>thead:first-child>tr:first-child>td,.table>thead:first-child>tr:first-child>th{border-top:0}.table>tbody+tbody{border-top:2px solid #ddd}.table .table{background-color:#fff}.table-condensed>tbody>tr>td,.table-condensed>tbody>tr>th,.table-condensed>tfoot>tr>td,.table-condensed>tfoot>tr>th,.table-condensed>thead>tr>td,.table-condensed>thead>tr>th{padding:5px}.table-bordered{border:1px solid #ddd}.table-bordered>tbody>tr>td,.table-bordered>tbody>tr>th,.table-bordered>tfoot>tr>td,.table-bordered>tfoot>tr>th,.table-bordered>thead>tr>td,.table-bordered>thead>tr>th{border:1px solid #ddd}.table-bordered>thead>tr>td,.table-bordered>thead>tr>th{border-bottom-width:2px}.table-striped>tbody>tr:nth-of-type(odd){background-color:#f9f9f9}.table-hover>tbody>tr:hover{background-color:#f5f5f5}table col[class*=col-]{position:static;display:table-column;float:none}table td[class*=col-],table th[class*=col-]{position:static;display:table-cell;float:none}.table>tbody>tr.active>td,.table>tbody>tr.active>th,.table>tbody>tr>td.active,.table>tbody>tr>th.active,.table>tfoot>tr.active>td,.table>tfoot>tr.active>th,.table>tfoot>tr>td.active,.table>tfoot>tr>th.active,.table>thead>tr.active>td,.table>thead>tr.active>th,.table>thead>tr>td.active,.table>thead>tr>th.active{background-color:#f5f5f5}.table-hover>tbody>tr.active:hover>td,.table-hover>tbody>tr.active:hover>th,.table-hover>tbody>tr:hover>.active,.table-hover>tbody>tr>td.active:hover,.table-hover>tbody>tr>th.active:hover{background-color:#e8e8e8}.table>tbody>tr.success>td,.table>tbody>tr.success>th,.table>tbody>tr>td.success,.table>tbody>tr>th.success,.table>tfoot>tr.success>td,.table>tfoot>tr.success>th,.table>tfoot>tr>td.success,.table>tfoot>tr>th.success,.table>thead>tr.success>td,.table>thead>tr.success>th,.table>thead>tr>td.success,.table>thead>tr>th.success{background-color:#dff0d8}.table-hover>tbody>tr.success:hover>td,.table-hover>tbody>tr.success:hover>th,.table-hover>tbody>tr:hover>.success,.table-hover>tbody>tr>td.success:hover,.table-hover>tbody>tr>th.success:hover{background-color:#d0e9c6}.table>tbody>tr.info>td,.table>tbody>tr.info>th,.table>tbody>tr>td.info,.table>tbody>tr>th.info,.table>tfoot>tr.info>td,.table>tfoot>tr.info>th,.table>tfoot>tr>td.info,.table>tfoot>tr>th.info,.table>thead>tr.info>td,.table>thead>tr.info>th,.table>thead>tr>td.info,.table>thead>tr>th.info{background-color:#d9edf7}.table-hover>tbody>tr.info:hover>td,.table-hover>tbody>tr.info:hover>th,.table-hover>tbody>tr:hover>.info,.table-hover>tbody>tr>td.info:hover,.table-hover>tbody>tr>th.info:hover{background-color:#c4e3f3}.table>tbody>tr.warning>td,.table>tbody>tr.warning>th,.table>tbody>tr>td.warning,.table>tbody>tr>th.warning,.table>tfoot>tr.warning>td,.table>tfoot>tr.warning>th,.table>tfoot>tr>td.warning,.table>tfoot>tr>th.warning,.table>thead>tr.warning>td,.table>thead>tr.warning>th,.table>thead>tr>td.warning,.table>thead>tr>th.warning{background-color:#fcf8e3}.table-hover>tbody>tr.warning:hover>td,.table-hover>tbody>tr.warning:hover>th,.table-hover>tbody>tr:hover>.warning,.table-hover>tbody>tr>td.warning:hover,.table-hover>tbody>tr>th.warning:hover{background-color:#faf2cc}.table>tbody>tr.danger>td,.table>tbody>tr.danger>th,.table>tbody>tr>td.danger,.table>tbody>tr>th.danger,.table>tfoot>tr.danger>td,.table>tfoot>tr.danger>th,.table>tfoot>tr>td.danger,.table>tfoot>tr>th.danger,.table>thead>tr.danger>td,.table>thead>tr.danger>th,.table>thead>tr>td.danger,.table>thead>tr>th.danger{background-color:#f2dede}.table-hover>tbody>tr.danger:hover>td,.table-hover>tbody>tr.danger:hover>th,.table-hover>tbody>tr:hover>.danger,.table-hover>tbody>tr>td.danger:hover,.table-hover>tbody>tr>th.danger:hover{background-color:#ebcccc}.table-responsive{min-height:.01%;overflow-x:auto}@media screen and (max-width:767px){.table-responsive{width:100%;margin-bottom:15px;overflow-y:hidden;-ms-overflow-style:-ms-autohiding-scrollbar;border:1px solid #ddd}.table-responsive>.table{margin-bottom:0}.table-responsive>.table>tbody>tr>td,.table-responsive>.table>tbody>tr>th,.table-responsive>.table>tfoot>tr>td,.table-responsive>.table>tfoot>tr>th,.table-responsive>.table>thead>tr>td,.table-responsive>.table>thead>tr>th{white-space:nowrap}.table-responsive>.table-bordered{border:0}.table-responsive>.table-bordered>tbody>tr>td:first-child,.table-responsive>.table-bordered>tbody>tr>th:first-child,.table-responsive>.table-bordered>tfoot>tr>td:first-child,.table-responsive>.table-bordered>tfoot>tr>th:first-child,.table-responsive>.table-bordered>thead>tr>td:first-child,.table-responsive>.table-bordered>thead>tr>th:first-child{border-left:0}.table-responsive>.table-bordered>tbody>tr>td:last-child,.table-responsive>.table-bordered>tbody>tr>th:last-child,.table-responsive>.table-bordered>tfoot>tr>td:last-child,.table-responsive>.table-bordered>tfoot>tr>th:last-child,.table-responsive>.table-bordered>thead>tr>td:last-child,.table-responsive>.table-bordered>thead>tr>th:last-child{border-right:0}.table-responsive>.table-bordered>tbody>tr:last-child>td,.table-responsive>.table-bordered>tbody>tr:last-child>th,.table-responsive>.table-bordered>tfoot>tr:last-child>td,.table-responsive>.table-bordered>tfoot>tr:last-child>th{border-bottom:0}}fieldset{min-width:0;padding:0;margin:0;border:0}legend{display:block;width:100%;padding:0;margin-bottom:20px;font-size:21px;line-height:inherit;color:#333;border:0;border-bottom:1px solid #e5e5e5}label{display:inline-block;max-width:100%;margin-bottom:5px;font-weight:700}input[type=search]{-webkit-box-sizing:border-box;-moz-box-sizing:border-box;box-sizing:border-box}input[type=checkbox],input[type=radio]{margin:4px 0 0;line-height:normal}input[type=file]{display:block}input[type=range]{display:block;width:100%}select[multiple],select[size]{height:auto}input[type=checkbox]:focus,input[type=file]:focus,input[type=radio]:focus{outline:5px auto -webkit-focus-ring-color;outline-offset:-2px}output{display:block;padding-top:7px;font-size:14px;line-height:1.42857143;color:#555}.form-control{display:block;width:100%;height:34px;padding:6px 12px;font-size:14px;line-height:1.42857143;color:#555;background-color:#fff;background-image:none;border:1px solid #ccc;border-radius:4px;-webkit-box-shadow:inset 0 1px 1px rgba(0,0,0,.075);box-shadow:inset 0 1px 1px rgba(0,0,0,.075);-webkit-transition:border-color ease-in-out .15s,-webkit-box-shadow ease-in-out .15s;-o-transition:border-color ease-in-out .15s,box-shadow ease-in-out .15s;transition:border-color ease-in-out .15s,box-shadow ease-in-out .15s}.form-control:focus{border-color:#66afe9;outline:0;-webkit-box-shadow:inset 0 1px 1px rgba(0,0,0,.075),0 0 8px rgba(102,175,233,.6);box-shadow:inset 0 1px 1px rgba(0,0,0,.075),0 0 8px rgba(102,175,233,.6)}.form-control::-moz-placeholder{color:#999;opacity:1}.form-control:-ms-input-placeholder{color:#999}.form-control::-webkit-input-placeholder{color:#999}.form-control::-ms-expand{background-color:transparent;border:0}.form-control[disabled],.form-control[readonly],fieldset[disabled] .form-control{background-color:#eee;opacity:1}.form-control[disabled],fieldset[disabled] .form-control{cursor:not-allowed}textarea.form-control{height:auto}input[type=search]{-webkit-appearance:none}@media screen and (-webkit-min-device-pixel-ratio:0){input[type=date].form-control,input[type=datetime-local].form-control,input[type=month].form-control,input[type=time].form-control{line-height:34px}.input-group-sm input[type=date],.input-group-sm input[type=datetime-local],.input-group-sm input[type=month],.input-group-sm input[type=time],input[type=date].input-sm,input[type=datetime-local].input-sm,input[type=month].input-sm,input[type=time].input-sm{line-height:30px}.input-group-lg input[type=date],.input-group-lg input[type=datetime-local],.input-group-lg input[type=month],.input-group-lg input[type=time],input[type=date].input-lg,input[type=datetime-local].input-lg,input[type=month].input-lg,input[type=time].input-lg{line-height:46px}}.form-group{margin-bottom:15px}.checkbox,.radio{position:relative;display:block;margin-top:10px;margin-bottom:10px}.checkbox label,.radio label{min-height:20px;padding-left:20px;margin-bottom:0;font-weight:400;cursor:pointer}.checkbox input[type=checkbox],.checkbox-inline input[type=checkbox],.radio input[type=radio],.radio-inline input[type=radio]{position:absolute;margin-left:-20px}.checkbox+.checkbox,.radio+.radio{margin-top:-5px}.checkbox-inline,.radio-inline{position:relative;display:inline-block;padding-left:20px;margin-bottom:0;font-weight:400;vertical-align:middle;cursor:pointer}.checkbox-inline+.checkbox-inline,.radio-inline+.radio-inline{margin-top:0;margin-left:10px}fieldset[disabled] input[type=checkbox],fieldset[disabled] input[type=radio],input[type=checkbox].disabled,input[type=checkbox][disabled],input[type=radio].disabled,input[type=radio][disabled]{cursor:not-allowed}.checkbox-inline.disabled,.radio-inline.disabled,fieldset[disabled] .checkbox-inline,fieldset[disabled] .radio-inline{cursor:not-allowed}.checkbox.disabled label,.radio.disabled label,fieldset[disabled] .checkbox label,fieldset[disabled] .radio label{cursor:not-allowed}.form-control-static{min-height:34px;padding-top:7px;padding-bottom:7px;margin-bottom:0}.form-control-static.input-lg,.form-control-static.input-sm{padding-right:0;padding-left:0}.input-sm{height:30px;padding:5px 10px;font-size:12px;line-height:1.5;border-radius:3px}select.input-sm{height:30px;line-height:30px}select[multiple].input-sm,textarea.input-sm{height:auto}.form-group-sm .form-control{height:30px;padding:5px 10px;font-size:12px;line-height:1.5;border-radius:3px}.form-group-sm select.form-control{height:30px;line-height:30px}.form-group-sm select[multiple].form-control,.form-group-sm textarea.form-control{height:auto}.form-group-sm .form-control-static{height:30px;min-height:32px;padding:6px 10px;font-size:12px;line-height:1.5}.input-lg{height:46px;padding:10px 16px;font-size:18px;line-height:1.3333333;border-radius:6px}select.input-lg{height:46px;line-height:46px}select[multiple].input-lg,textarea.input-lg{height:auto}.form-group-lg .form-control{height:46px;padding:10px 16px;font-size:18px;line-height:1.3333333;border-radius:6px}.form-group-lg select.form-control{height:46px;line-height:46px}.form-group-lg select[multiple].form-control,.form-group-lg textarea.form-control{height:auto}.form-group-lg .form-control-static{height:46px;min-height:38px;padding:11px 16px;font-size:18px;line-height:1.3333333}.has-feedback{position:relative}.has-feedback .form-control{padding-right:42.5px}.form-control-feedback{position:absolute;top:0;right:0;z-index:2;display:block;width:34px;height:34px;line-height:34px;text-align:center;pointer-events:none}.form-group-lg .form-control+.form-control-feedback,.input-group-lg+.form-control-feedback,.input-lg+.form-control-feedback{width:46px;height:46px;line-height:46px}.form-group-sm .form-control+.form-control-feedback,.input-group-sm+.form-control-feedback,.input-sm+.form-control-feedback{width:30px;height:30px;line-height:30px}.has-success .checkbox,.has-success .checkbox-inline,.has-success .control-label,.has-success .help-block,.has-success .radio,.has-success .radio-inline,.has-success.checkbox label,.has-success.checkbox-inline label,.has-success.radio label,.has-success.radio-inline label{color:#3c763d}.has-success .form-control{border-color:#3c763d;-webkit-box-shadow:inset 0 1px 1px rgba(0,0,0,.075);box-shadow:inset 0 1px 1px rgba(0,0,0,.075)}.has-success .form-control:focus{border-color:#2b542c;-webkit-box-shadow:inset 0 1px 1px rgba(0,0,0,.075),0 0 6px #67b168;box-shadow:inset 0 1px 1px rgba(0,0,0,.075),0 0 6px #67b168}.has-success .input-group-addon{color:#3c763d;background-color:#dff0d8;border-color:#3c763d}.has-success .form-control-feedback{color:#3c763d}.has-warning .checkbox,.has-warning .checkbox-inline,.has-warning .control-label,.has-warning .help-block,.has-warning .radio,.has-warning .radio-inline,.has-warning.checkbox label,.has-warning.checkbox-inline label,.has-warning.radio label,.has-warning.radio-inline label{color:#8a6d3b}.has-warning .form-control{border-color:#8a6d3b;-webkit-box-shadow:inset 0 1px 1px rgba(0,0,0,.075);box-shadow:inset 0 1px 1px rgba(0,0,0,.075)}.has-warning .form-control:focus{border-color:#66512c;-webkit-box-shadow:inset 0 1px 1px rgba(0,0,0,.075),0 0 6px #c0a16b;box-shadow:inset 0 1px 1px rgba(0,0,0,.075),0 0 6px #c0a16b}.has-warning .input-group-addon{color:#8a6d3b;background-color:#fcf8e3;border-color:#8a6d3b}.has-warning .form-control-feedback{color:#8a6d3b}.has-error .checkbox,.has-error .checkbox-inline,.has-error .control-label,.has-error .help-block,.has-error .radio,.has-error .radio-inline,.has-error.checkbox label,.has-error.checkbox-inline label,.has-error.radio label,.has-error.radio-inline label{color:#a94442}.has-error .form-control{border-color:#a94442;-webkit-box-shadow:inset 0 1px 1px rgba(0,0,0,.075);box-shadow:inset 0 1px 1px rgba(0,0,0,.075)}.has-error .form-control:focus{border-color:#843534;-webkit-box-shadow:inset 0 1px 1px rgba(0,0,0,.075),0 0 6px #ce8483;box-shadow:inset 0 1px 1px rgba(0,0,0,.075),0 0 6px #ce8483}.has-error .input-group-addon{color:#a94442;background-color:#f2dede;border-color:#a94442}.has-error .form-control-feedback{color:#a94442}.has-feedback label~.form-control-feedback{top:25px}.has-feedback label.sr-only~.form-control-feedback{top:0}.help-block{display:block;margin-top:5px;margin-bottom:10px;color:#737373}@media (min-width:768px){.form-inline .form-group{display:inline-block;margin-bottom:0;vertical-align:middle}.form-inline .form-control{display:inline-block;width:auto;vertical-align:middle}.form-inline .form-control-static{display:inline-block}.form-inline .input-group{display:inline-table;vertical-align:middle}.form-inline .input-group .form-control,.form-inline .input-group .input-group-addon,.form-inline .input-group .input-group-btn{width:auto}.form-inline .input-group>.form-control{width:100%}.form-inline .control-label{margin-bottom:0;vertical-align:middle}.form-inline .checkbox,.form-inline .radio{display:inline-block;margin-top:0;margin-bottom:0;vertical-align:middle}.form-inline .checkbox label,.form-inline .radio label{padding-left:0}.form-inline .checkbox input[type=checkbox],.form-inline .radio input[type=radio]{position:relative;margin-left:0}.form-inline .has-feedback .form-control-feedback{top:0}}.form-horizontal .checkbox,.form-horizontal .checkbox-inline,.form-horizontal .radio,.form-horizontal .radio-inline{padding-top:7px;margin-top:0;margin-bottom:0}.form-horizontal .checkbox,.form-horizontal .radio{min-height:27px}.form-horizontal .form-group{margin-right:-15px;margin-left:-15px}@media (min-width:768px){.form-horizontal .control-label{padding-top:7px;margin-bottom:0;text-align:right}}.form-horizontal .has-feedback .form-control-feedback{right:15px}@media (min-width:768px){.form-horizontal .form-group-lg .control-label{padding-top:11px;font-size:18px}}@media (min-width:768px){.form-horizontal .form-group-sm .control-label{padding-top:6px;font-size:12px}}.btn{display:inline-block;padding:6px 12px;margin-bottom:0;font-size:14px;font-weight:400;line-height:1.42857143;text-align:center;white-space:nowrap;vertical-align:middle;-ms-touch-action:manipulation;touch-action:manipulation;cursor:pointer;-webkit-user-select:none;-moz-user-select:none;-ms-user-select:none;user-select:none;background-image:none;border:1px solid transparent;border-radius:4px}.btn.active.focus,.btn.active:focus,.btn.focus,.btn:active.focus,.btn:active:focus,.btn:focus{outline:5px auto -webkit-focus-ring-color;outline-offset:-2px}.btn.focus,.btn:focus,.btn:hover{color:#333;text-decoration:none}.btn.active,.btn:active{background-image:none;outline:0;-webkit-box-shadow:inset 0 3px 5px rgba(0,0,0,.125);box-shadow:inset 0 3px 5px rgba(0,0,0,.125)}.btn.disabled,.btn[disabled],fieldset[disabled] .btn{cursor:not-allowed;-webkit-box-shadow:none;box-shadow:none;opacity:.65}a.btn.disabled,fieldset[disabled] a.btn{pointer-events:none}.btn-default{color:#333;background-color:#fff;border-color:#ccc}.btn-default.focus,.btn-default:focus{color:#333;background-color:#e6e6e6;border-color:#8c8c8c}.btn-default:hover{color:#333;background-color:#e6e6e6;border-color:#adadad}.btn-default.active,.btn-default:active,.open>.dropdown-toggle.btn-default{color:#333;background-color:#e6e6e6;border-color:#adadad}.btn-default.active.focus,.btn-default.active:focus,.btn-default.active:hover,.btn-default:active.focus,.btn-default:active:focus,.btn-default:active:hover,.open>.dropdown-toggle.btn-default.focus,.open>.dropdown-toggle.btn-default:focus,.open>.dropdown-toggle.btn-default:hover{color:#333;background-color:#d4d4d4;border-color:#8c8c8c}.btn-default.active,.btn-default:active,.open>.dropdown-toggle.btn-default{background-image:none}.btn-default.disabled.focus,.btn-default.disabled:focus,.btn-default.disabled:hover,.btn-default[disabled].focus,.btn-default[disabled]:focus,.btn-default[disabled]:hover,fieldset[disabled] .btn-default.focus,fieldset[disabled] .btn-default:focus,fieldset[disabled] .btn-default:hover{background-color:#fff;border-color:#ccc}.btn-default .badge{color:#fff;background-color:#333}.btn-primary{color:#fff;background-color:#337ab7;border-color:#2e6da4}.btn-primary.focus,.btn-primary:focus{color:#fff;background-color:#286090;border-color:#122b40}.btn-primary:hover{color:#fff;background-color:#286090;border-color:#204d74}.btn-primary.active,.btn-primary:active,.open>.dropdown-toggle.btn-primary{color:#fff;background-color:#286090;border-color:#204d74}.btn-primary.active.focus,.btn-primary.active:focus,.btn-primary.active:hover,.btn-primary:active.focus,.btn-primary:active:focus,.btn-primary:active:hover,.open>.dropdown-toggle.btn-primary.focus,.open>.dropdown-toggle.btn-primary:focus,.open>.dropdown-toggle.btn-primary:hover{color:#fff;background-color:#204d74;border-color:#122b40}.btn-primary.active,.btn-primary:active,.open>.dropdown-toggle.btn-primary{background-image:none}.btn-primary.disabled.focus,.btn-primary.disabled:focus,.btn-primary.disabled:hover,.btn-primary[disabled].focus,.btn-primary[disabled]:focus,.btn-primary[disabled]:hover,fieldset[disabled] .btn-primary.focus,fieldset[disabled] .btn-primary:focus,fieldset[disabled] .btn-primary:hover{background-color:#337ab7;border-color:#2e6da4}.btn-primary .badge{color:#337ab7;background-color:#fff}.btn-success{color:#fff;background-color:#5cb85c;border-color:#4cae4c}.btn-success.focus,.btn-success:focus{color:#fff;background-color:#449d44;border-color:#255625}.btn-success:hover{color:#fff;background-color:#449d44;border-color:#398439}.btn-success.active,.btn-success:active,.open>.dropdown-toggle.btn-success{color:#fff;background-color:#449d44;border-color:#398439}.btn-success.active.focus,.btn-success.active:focus,.btn-success.active:hover,.btn-success:active.focus,.btn-success:active:focus,.btn-success:active:hover,.open>.dropdown-toggle.btn-success.focus,.open>.dropdown-toggle.btn-success:focus,.open>.dropdown-toggle.btn-success:hover{color:#fff;background-color:#398439;border-color:#255625}.btn-success.active,.btn-success:active,.open>.dropdown-toggle.btn-success{background-image:none}.btn-success.disabled.focus,.btn-success.disabled:focus,.btn-success.disabled:hover,.btn-success[disabled].focus,.btn-success[disabled]:focus,.btn-success[disabled]:hover,fieldset[disabled] .btn-success.focus,fieldset[disabled] .btn-success:focus,fieldset[disabled] .btn-success:hover{background-color:#5cb85c;border-color:#4cae4c}.btn-success .badge{color:#5cb85c;background-color:#fff}.btn-info{color:#fff;background-color:#5bc0de;border-color:#46b8da}.btn-info.focus,.btn-info:focus{color:#fff;background-color:#31b0d5;border-color:#1b6d85}.btn-info:hover{color:#fff;background-color:#31b0d5;border-color:#269abc}.btn-info.active,.btn-info:active,.open>.dropdown-toggle.btn-info{color:#fff;background-color:#31b0d5;border-color:#269abc}.btn-info.active.focus,.btn-info.active:focus,.btn-info.active:hover,.btn-info:active.focus,.btn-info:active:focus,.btn-info:active:hover,.open>.dropdown-toggle.btn-info.focus,.open>.dropdown-toggle.btn-info:focus,.open>.dropdown-toggle.btn-info:hover{color:#fff;background-color:#269abc;border-color:#1b6d85}.btn-info.active,.btn-info:active,.open>.dropdown-toggle.btn-info{background-image:none}.btn-info.disabled.focus,.btn-info.disabled:focus,.btn-info.disabled:hover,.btn-info[disabled].focus,.btn-info[disabled]:focus,.btn-info[disabled]:hover,fieldset[disabled] .btn-info.focus,fieldset[disabled] .btn-info:focus,fieldset[disabled] .btn-info:hover{background-color:#5bc0de;border-color:#46b8da}.btn-info .badge{color:#5bc0de;background-color:#fff}.btn-warning{color:#fff;background-color:#f0ad4e;border-color:#eea236}.btn-warning.focus,.btn-warning:focus{color:#fff;background-color:#ec971f;border-color:#985f0d}.btn-warning:hover{color:#fff;background-color:#ec971f;border-color:#d58512}.btn-warning.active,.btn-warning:active,.open>.dropdown-toggle.btn-warning{color:#fff;background-color:#ec971f;border-color:#d58512}.btn-warning.active.focus,.btn-warning.active:focus,.btn-warning.active:hover,.btn-warning:active.focus,.btn-warning:active:focus,.btn-warning:active:hover,.open>.dropdown-toggle.btn-warning.focus,.open>.dropdown-toggle.btn-warning:focus,.open>.dropdown-toggle.btn-warning:hover{color:#fff;background-color:#d58512;border-color:#985f0d}.btn-warning.active,.btn-warning:active,.open>.dropdown-toggle.btn-warning{background-image:none}.btn-warning.disabled.focus,.btn-warning.disabled:focus,.btn-warning.disabled:hover,.btn-warning[disabled].focus,.btn-warning[disabled]:focus,.btn-warning[disabled]:hover,fieldset[disabled] .btn-warning.focus,fieldset[disabled] .btn-warning:focus,fieldset[disabled] .btn-warning:hover{background-color:#f0ad4e;border-color:#eea236}.btn-warning .badge{color:#f0ad4e;background-color:#fff}.btn-danger{color:#fff;background-color:#d9534f;border-color:#d43f3a}.btn-danger.focus,.btn-danger:focus{color:#fff;background-color:#c9302c;border-color:#761c19}.btn-danger:hover{color:#fff;background-color:#c9302c;border-color:#ac2925}.btn-danger.active,.btn-danger:active,.open>.dropdown-toggle.btn-danger{color:#fff;background-color:#c9302c;border-color:#ac2925}.btn-danger.active.focus,.btn-danger.active:focus,.btn-danger.active:hover,.btn-danger:active.focus,.btn-danger:active:focus,.btn-danger:active:hover,.open>.dropdown-toggle.btn-danger.focus,.open>.dropdown-toggle.btn-danger:focus,.open>.dropdown-toggle.btn-danger:hover{color:#fff;background-color:#ac2925;border-color:#761c19}.btn-danger.active,.btn-danger:active,.open>.dropdown-toggle.btn-danger{background-image:none}.btn-danger.disabled.focus,.btn-danger.disabled:focus,.btn-danger.disabled:hover,.btn-danger[disabled].focus,.btn-danger[disabled]:focus,.btn-danger[disabled]:hover,fieldset[disabled] .btn-danger.focus,fieldset[disabled] .btn-danger:focus,fieldset[disabled] .btn-danger:hover{background-color:#d9534f;border-color:#d43f3a}.btn-danger .badge{color:#d9534f;background-color:#fff}.btn-link{font-weight:400;color:#337ab7;border-radius:0}.btn-link,.btn-link.active,.btn-link:active,.btn-link[disabled],fieldset[disabled] .btn-link{background-color:transparent;-webkit-box-shadow:none;box-shadow:none}.btn-link,.btn-link:active,.btn-link:focus,.btn-link:hover{border-color:transparent}.btn-link:focus,.btn-link:hover{color:#23527c;text-decoration:underline;background-color:transparent}.btn-link[disabled]:focus,.btn-link[disabled]:hover,fieldset[disabled] .btn-link:focus,fieldset[disabled] .btn-link:hover{color:#777;text-decoration:none}.btn-group-lg>.btn,.btn-lg{padding:10px 16px;font-size:18px;line-height:1.3333333;border-radius:6px}.btn-group-sm>.btn,.btn-sm{padding:5px 10px;font-size:12px;line-height:1.5;border-radius:3px}.btn-group-xs>.btn,.btn-xs{padding:1px 5px;font-size:12px;line-height:1.5;border-radius:3px}.btn-block{display:block;width:100%}.btn-block+.btn-block{margin-top:5px}input[type=button].btn-block,input[type=reset].btn-block,input[type=submit].btn-block{width:100%}.fade{opacity:0;-webkit-transition:opacity .15s linear;-o-transition:opacity .15s linear;transition:opacity .15s linear}.fade.in{opacity:1}.collapse{display:none}.collapse.in{display:block}tr.collapse.in{display:table-row}tbody.collapse.in{display:table-row-group}.collapsing{position:relative;height:0;overflow:hidden;-webkit-transition-timing-function:ease;-o-transition-timing-function:ease;transition-timing-function:ease;-webkit-transition-duration:.35s;-o-transition-duration:.35s;transition-duration:.35s;-webkit-transition-property:height,visibility;-o-transition-property:height,visibility;transition-property:height,visibility}.caret{display:inline-block;width:0;height:0;margin-left:2px;vertical-align:middle;border-top:4px dashed;border-right:4px solid transparent;border-left:4px solid transparent}.dropdown,.dropup{position:relative}.dropdown-toggle:focus{outline:0}.dropdown-menu{position:absolute;top:100%;left:0;z-index:1000;display:none;float:left;min-width:160px;padding:5px 0;margin:2px 0 0;font-size:14px;text-align:left;list-style:none;background-color:#fff;-webkit-background-clip:padding-box;background-clip:padding-box;border:1px solid #ccc;border:1px solid rgba(0,0,0,.15);border-radius:4px;-webkit-box-shadow:0 6px 12px rgba(0,0,0,.175);box-shadow:0 6px 12px rgba(0,0,0,.175)}.dropdown-menu.pull-right{right:0;left:auto}.dropdown-menu .divider{height:1px;margin:9px 0;overflow:hidden;background-color:#e5e5e5}.dropdown-menu>li>a{display:block;padding:3px 20px;clear:both;font-weight:400;line-height:1.42857143;color:#333;white-space:nowrap}.dropdown-menu>li>a:focus,.dropdown-menu>li>a:hover{color:#262626;text-decoration:none;background-color:#f5f5f5}.dropdown-menu>.active>a,.dropdown-menu>.active>a:focus,.dropdown-menu>.active>a:hover{color:#fff;text-decoration:none;background-color:#337ab7;outline:0}.dropdown-menu>.disabled>a,.dropdown-menu>.disabled>a:focus,.dropdown-menu>.disabled>a:hover{color:#777}.dropdown-menu>.disabled>a:focus,.dropdown-menu>.disabled>a:hover{text-decoration:none;cursor:not-allowed;background-color:transparent;background-image:none}.open>.dropdown-menu{display:block}.open>a{outline:0}.dropdown-menu-right{right:0;left:auto}.dropdown-menu-left{right:auto;left:0}.dropdown-header{display:block;padding:3px 20px;font-size:12px;line-height:1.42857143;color:#777;white-space:nowrap}.dropdown-backdrop{position:fixed;top:0;right:0;bottom:0;left:0;z-index:990}.pull-right>.dropdown-menu{right:0;left:auto}.dropup .caret,.navbar-fixed-bottom .dropdown .caret{content:"";border-top:0;border-bottom:4px dashed}.dropup .dropdown-menu,.navbar-fixed-bottom .dropdown .dropdown-menu{top:auto;bottom:100%;margin-bottom:2px}@media (min-width:768px){.navbar-right .dropdown-menu{right:0;left:auto}.navbar-right .dropdown-menu-left{right:auto;left:0}}.btn-group,.btn-group-vertical{position:relative;display:inline-block;vertical-align:middle}.btn-group-vertical>.btn,.btn-group>.btn{position:relative;float:left}.btn-group-vertical>.btn.active,.btn-group-vertical>.btn:active,.btn-group-vertical>.btn:focus,.btn-group-vertical>.btn:hover,.btn-group>.btn.active,.btn-group>.btn:active,.btn-group>.btn:focus,.btn-group>.btn:hover{z-index:2}.btn-group .btn+.btn,.btn-group .btn+.btn-group,.btn-group .btn-group+.btn,.btn-group .btn-group+.btn-group{margin-left:-1px}.btn-toolbar{margin-left:-5px}.btn-toolbar .btn,.btn-toolbar .btn-group,.btn-toolbar .input-group{float:left}.btn-toolbar>.btn,.btn-toolbar>.btn-group,.btn-toolbar>.input-group{margin-left:5px}.btn-group>.btn:not(:first-child):not(:last-child):not(.dropdown-toggle){border-radius:0}.btn-group>.btn:first-child{margin-left:0}.btn-group>.btn:first-child:not(:last-child):not(.dropdown-toggle){border-top-right-radius:0;border-bottom-right-radius:0}.btn-group>.btn:last-child:not(:first-child),.btn-group>.dropdown-toggle:not(:first-child){border-top-left-radius:0;border-bottom-left-radius:0}.btn-group>.btn-group{float:left}.btn-group>.btn-group:not(:first-child):not(:last-child)>.btn{border-radius:0}.btn-group>.btn-group:first-child:not(:last-child)>.btn:last-child,.btn-group>.btn-group:first-child:not(:last-child)>.dropdown-toggle{border-top-right-radius:0;border-bottom-right-radius:0}.btn-group>.btn-group:last-child:not(:first-child)>.btn:first-child{border-top-left-radius:0;border-bottom-left-radius:0}.btn-group .dropdown-toggle:active,.btn-group.open .dropdown-toggle{outline:0}.btn-group>.btn+.dropdown-toggle{padding-right:8px;padding-left:8px}.btn-group>.btn-lg+.dropdown-toggle{padding-right:12px;padding-left:12px}.btn-group.open .dropdown-toggle{-webkit-box-shadow:inset 0 3px 5px rgba(0,0,0,.125);box-shadow:inset 0 3px 5px rgba(0,0,0,.125)}.btn-group.open .dropdown-toggle.btn-link{-webkit-box-shadow:none;box-shadow:none}.btn .caret{margin-left:0}.btn-lg .caret{border-width:5px 5px 0;border-bottom-width:0}.dropup .btn-lg .caret{border-width:0 5px 5px}.btn-group-vertical>.btn,.btn-group-vertical>.btn-group,.btn-group-vertical>.btn-group>.btn{display:block;float:none;width:100%;max-width:100%}.btn-group-vertical>.btn-group>.btn{float:none}.btn-group-vertical>.btn+.btn,.btn-group-vertical>.btn+.btn-group,.btn-group-vertical>.btn-group+.btn,.btn-group-vertical>.btn-group+.btn-group{margin-top:-1px;margin-left:0}.btn-group-vertical>.btn:not(:first-child):not(:last-child){border-radius:0}.btn-group-vertical>.btn:first-child:not(:last-child){border-top-left-radius:4px;border-top-right-radius:4px;border-bottom-right-radius:0;border-bottom-left-radius:0}.btn-group-vertical>.btn:last-child:not(:first-child){border-top-left-radius:0;border-top-right-radius:0;border-bottom-right-radius:4px;border-bottom-left-radius:4px}.btn-group-vertical>.btn-group:not(:first-child):not(:last-child)>.btn{border-radius:0}.btn-group-vertical>.btn-group:first-child:not(:last-child)>.btn:last-child,.btn-group-vertical>.btn-group:first-child:not(:last-child)>.dropdown-toggle{border-bottom-right-radius:0;border-bottom-left-radius:0}.btn-group-vertical>.btn-group:last-child:not(:first-child)>.btn:first-child{border-top-left-radius:0;border-top-right-radius:0}.btn-group-justified{display:table;width:100%;table-layout:fixed;border-collapse:separate}.btn-group-justified>.btn,.btn-group-justified>.btn-group{display:table-cell;float:none;width:1%}.btn-group-justified>.btn-group .btn{width:100%}.btn-group-justified>.btn-group .dropdown-menu{left:auto}[data-toggle=buttons]>.btn input[type=checkbox],[data-toggle=buttons]>.btn input[type=radio],[data-toggle=buttons]>.btn-group>.btn input[type=checkbox],[data-toggle=buttons]>.btn-group>.btn input[type=radio]{position:absolute;clip:rect(0,0,0,0);pointer-events:none}.input-group{position:relative;display:table;border-collapse:separate}.input-group[class*=col-]{float:none;padding-right:0;padding-left:0}.input-group .form-control{position:relative;z-index:2;float:left;width:100%;margin-bottom:0}.input-group .form-control:focus{z-index:3}.input-group-lg>.form-control,.input-group-lg>.input-group-addon,.input-group-lg>.input-group-btn>.btn{height:46px;padding:10px 16px;font-size:18px;line-height:1.3333333;border-radius:6px}select.input-group-lg>.form-control,select.input-group-lg>.input-group-addon,select.input-group-lg>.input-group-btn>.btn{height:46px;line-height:46px}select[multiple].input-group-lg>.form-control,select[multiple].input-group-lg>.input-group-addon,select[multiple].input-group-lg>.input-group-btn>.btn,textarea.input-group-lg>.form-control,textarea.input-group-lg>.input-group-addon,textarea.input-group-lg>.input-group-btn>.btn{height:auto}.input-group-sm>.form-control,.input-group-sm>.input-group-addon,.input-group-sm>.input-group-btn>.btn{height:30px;padding:5px 10px;font-size:12px;line-height:1.5;border-radius:3px}select.input-group-sm>.form-control,select.input-group-sm>.input-group-addon,select.input-group-sm>.input-group-btn>.btn{height:30px;line-height:30px}select[multiple].input-group-sm>.form-control,select[multiple].input-group-sm>.input-group-addon,select[multiple].input-group-sm>.input-group-btn>.btn,textarea.input-group-sm>.form-control,textarea.input-group-sm>.input-group-addon,textarea.input-group-sm>.input-group-btn>.btn{height:auto}.input-group .form-control,.input-group-addon,.input-group-btn{display:table-cell}.input-group .form-control:not(:first-child):not(:last-child),.input-group-addon:not(:first-child):not(:last-child),.input-group-btn:not(:first-child):not(:last-child){border-radius:0}.input-group-addon,.input-group-btn{width:1%;white-space:nowrap;vertical-align:middle}.input-group-addon{padding:6px 12px;font-size:14px;font-weight:400;line-height:1;color:#555;text-align:center;background-color:#eee;border:1px solid #ccc;border-radius:4px}.input-group-addon.input-sm{padding:5px 10px;font-size:12px;border-radius:3px}.input-group-addon.input-lg{padding:10px 16px;font-size:18px;border-radius:6px}.input-group-addon input[type=checkbox],.input-group-addon input[type=radio]{margin-top:0}.input-group .form-control:first-child,.input-group-addon:first-child,.input-group-btn:first-child>.btn,.input-group-btn:first-child>.btn-group>.btn,.input-group-btn:first-child>.dropdown-toggle,.input-group-btn:last-child>.btn-group:not(:last-child)>.btn,.input-group-btn:last-child>.btn:not(:last-child):not(.dropdown-toggle){border-top-right-radius:0;border-bottom-right-radius:0}.input-group-addon:first-child{border-right:0}.input-group .form-control:last-child,.input-group-addon:last-child,.input-group-btn:first-child>.btn-group:not(:first-child)>.btn,.input-group-btn:first-child>.btn:not(:first-child),.input-group-btn:last-child>.btn,.input-group-btn:last-child>.btn-group>.btn,.input-group-btn:last-child>.dropdown-toggle{border-top-left-radius:0;border-bottom-left-radius:0}.input-group-addon:last-child{border-left:0}.input-group-btn{position:relative;font-size:0;white-space:nowrap}.input-group-btn>.btn{position:relative}.input-group-btn>.btn+.btn{margin-left:-1px}.input-group-btn>.btn:active,.input-group-btn>.btn:focus,.input-group-btn>.btn:hover{z-index:2}.input-group-btn:first-child>.btn,.input-group-btn:first-child>.btn-group{margin-right:-1px}.input-group-btn:last-child>.btn,.input-group-btn:last-child>.btn-group{z-index:2;margin-left:-1px}.nav{padding-left:0;margin-bottom:0;list-style:none}.nav>li{position:relative;display:block}.nav>li>a{position:relative;display:block;padding:10px 15px}.nav>li>a:focus,.nav>li>a:hover{text-decoration:none;background-color:#eee}.nav>li.disabled>a{color:#777}.nav>li.disabled>a:focus,.nav>li.disabled>a:hover{color:#777;text-decoration:none;cursor:not-allowed;background-color:transparent}.nav .open>a,.nav .open>a:focus,.nav .open>a:hover{background-color:#eee;border-color:#337ab7}.nav .nav-divider{height:1px;margin:9px 0;overflow:hidden;background-color:#e5e5e5}.nav>li>a>img{max-width:none}.nav-tabs{border-bottom:1px solid #ddd}.nav-tabs>li{float:left;margin-bottom:-1px}.nav-tabs>li>a{margin-right:2px;line-height:1.42857143;border:1px solid transparent;border-radius:4px 4px 0 0}.nav-tabs>li>a:hover{border-color:#eee #eee #ddd}.nav-tabs>li.active>a,.nav-tabs>li.active>a:focus,.nav-tabs>li.active>a:hover{color:#555;cursor:default;background-color:#fff;border:1px solid #ddd;border-bottom-color:transparent}.nav-tabs.nav-justified{width:100%;border-bottom:0}.nav-tabs.nav-justified>li{float:none}.nav-tabs.nav-justified>li>a{margin-bottom:5px;text-align:center}.nav-tabs.nav-justified>.dropdown .dropdown-menu{top:auto;left:auto}@media (min-width:768px){.nav-tabs.nav-justified>li{display:table-cell;width:1%}.nav-tabs.nav-justified>li>a{margin-bottom:0}}.nav-tabs.nav-justified>li>a{margin-right:0;border-radius:4px}.nav-tabs.nav-justified>.active>a,.nav-tabs.nav-justified>.active>a:focus,.nav-tabs.nav-justified>.active>a:hover{border:1px solid #ddd}@media (min-width:768px){.nav-tabs.nav-justified>li>a{border-bottom:1px solid #ddd;border-radius:4px 4px 0 0}.nav-tabs.nav-justified>.active>a,.nav-tabs.nav-justified>.active>a:focus,.nav-tabs.nav-justified>.active>a:hover{border-bottom-color:#fff}}.nav-pills>li{float:left}.nav-pills>li>a{border-radius:4px}.nav-pills>li+li{margin-left:2px}.nav-pills>li.active>a,.nav-pills>li.active>a:focus,.nav-pills>li.active>a:hover{color:#fff;background-color:#337ab7}.nav-stacked>li{float:none}.nav-stacked>li+li{margin-top:2px;margin-left:0}.nav-justified{width:100%}.nav-justified>li{float:none}.nav-justified>li>a{margin-bottom:5px;text-align:center}.nav-justified>.dropdown .dropdown-menu{top:auto;left:auto}@media (min-width:768px){.nav-justified>li{display:table-cell;width:1%}.nav-justified>li>a{margin-bottom:0}}.nav-tabs-justified{border-bottom:0}.nav-tabs-justified>li>a{margin-right:0;border-radius:4px}.nav-tabs-justified>.active>a,.nav-tabs-justified>.active>a:focus,.nav-tabs-justified>.active>a:hover{border:1px solid #ddd}@media (min-width:768px){.nav-tabs-justified>li>a{border-bottom:1px solid #ddd;border-radius:4px 4px 0 0}.nav-tabs-justified>.active>a,.nav-tabs-justified>.active>a:focus,.nav-tabs-justified>.active>a:hover{border-bottom-color:#fff}}.tab-content>.tab-pane{display:none}.tab-content>.active{display:block}.nav-tabs .dropdown-menu{margin-top:-1px;border-top-left-radius:0;border-top-right-radius:0}.navbar{position:relative;min-height:50px;margin-bottom:20px;border:1px solid transparent}@media (min-width:768px){.navbar{border-radius:4px}}@media (min-width:768px){.navbar-header{float:left}}.navbar-collapse{padding-right:15px;padding-left:15px;overflow-x:visible;-webkit-overflow-scrolling:touch;border-top:1px solid transparent;-webkit-box-shadow:inset 0 1px 0 rgba(255,255,255,.1);box-shadow:inset 0 1px 0 rgba(255,255,255,.1)}.navbar-collapse.in{overflow-y:auto}@media (min-width:768px){.navbar-collapse{width:auto;border-top:0;-webkit-box-shadow:none;box-shadow:none}.navbar-collapse.collapse{display:block!important;height:auto!important;padding-bottom:0;overflow:visible!important}.navbar-collapse.in{overflow-y:visible}.navbar-fixed-bottom .navbar-collapse,.navbar-fixed-top .navbar-collapse,.navbar-static-top .navbar-collapse{padding-right:0;padding-left:0}}.navbar-fixed-bottom .navbar-collapse,.navbar-fixed-top .navbar-collapse{max-height:340px}@media (max-device-width:480px) and (orientation:landscape){.navbar-fixed-bottom .navbar-collapse,.navbar-fixed-top .navbar-collapse{max-height:200px}}.container-fluid>.navbar-collapse,.container-fluid>.navbar-header,.container>.navbar-collapse,.container>.navbar-header{margin-right:-15px;margin-left:-15px}@media (min-width:768px){.container-fluid>.navbar-collapse,.container-fluid>.navbar-header,.container>.navbar-collapse,.container>.navbar-header{margin-right:0;margin-left:0}}.navbar-static-top{z-index:1000;border-width:0 0 1px}@media (min-width:768px){.navbar-static-top{border-radius:0}}.navbar-fixed-bottom,.navbar-fixed-top{position:fixed;right:0;left:0;z-index:1030}@media (min-width:768px){.navbar-fixed-bottom,.navbar-fixed-top{border-radius:0}}.navbar-fixed-top{top:0;border-width:0 0 1px}.navbar-fixed-bottom{bottom:0;margin-bottom:0;border-width:1px 0 0}.navbar-brand{float:left;height:50px;padding:15px 15px;font-size:18px;line-height:20px}.navbar-brand:focus,.navbar-brand:hover{text-decoration:none}.navbar-brand>img{display:block}@media (min-width:768px){.navbar>.container .navbar-brand,.navbar>.container-fluid .navbar-brand{margin-left:-15px}}.navbar-toggle{position:relative;float:right;padding:9px 10px;margin-top:8px;margin-right:15px;margin-bottom:8px;background-color:transparent;background-image:none;border:1px solid transparent;border-radius:4px}.navbar-toggle:focus{outline:0}.navbar-toggle .icon-bar{display:block;width:22px;height:2px;border-radius:1px}.navbar-toggle .icon-bar+.icon-bar{margin-top:4px}@media (min-width:768px){.navbar-toggle{display:none}}.navbar-nav{margin:7.5px -15px}.navbar-nav>li>a{padding-top:10px;padding-bottom:10px;line-height:20px}@media (max-width:767px){.navbar-nav .open .dropdown-menu{position:static;float:none;width:auto;margin-top:0;background-color:transparent;border:0;-webkit-box-shadow:none;box-shadow:none}.navbar-nav .open .dropdown-menu .dropdown-header,.navbar-nav .open .dropdown-menu>li>a{padding:5px 15px 5px 25px}.navbar-nav .open .dropdown-menu>li>a{line-height:20px}.navbar-nav .open .dropdown-menu>li>a:focus,.navbar-nav .open .dropdown-menu>li>a:hover{background-image:none}}@media (min-width:768px){.navbar-nav{float:left;margin:0}.navbar-nav>li{float:left}.navbar-nav>li>a{padding-top:15px;padding-bottom:15px}}.navbar-form{padding:10px 15px;margin-top:8px;margin-right:-15px;margin-bottom:8px;margin-left:-15px;border-top:1px solid transparent;border-bottom:1px solid transparent;-webkit-box-shadow:inset 0 1px 0 rgba(255,255,255,.1),0 1px 0 rgba(255,255,255,.1);box-shadow:inset 0 1px 0 rgba(255,255,255,.1),0 1px 0 rgba(255,255,255,.1)}@media (min-width:768px){.navbar-form .form-group{display:inline-block;margin-bottom:0;vertical-align:middle}.navbar-form .form-control{display:inline-block;width:auto;vertical-align:middle}.navbar-form .form-control-static{display:inline-block}.navbar-form .input-group{display:inline-table;vertical-align:middle}.navbar-form .input-group .form-control,.navbar-form .input-group .input-group-addon,.navbar-form .input-group .input-group-btn{width:auto}.navbar-form .input-group>.form-control{width:100%}.navbar-form .control-label{margin-bottom:0;vertical-align:middle}.navbar-form .checkbox,.navbar-form .radio{display:inline-block;margin-top:0;margin-bottom:0;vertical-align:middle}.navbar-form .checkbox label,.navbar-form .radio label{padding-left:0}.navbar-form .checkbox input[type=checkbox],.navbar-form .radio input[type=radio]{position:relative;margin-left:0}.navbar-form .has-feedback .form-control-feedback{top:0}}@media (max-width:767px){.navbar-form .form-group{margin-bottom:5px}.navbar-form .form-group:last-child{margin-bottom:0}}@media (min-width:768px){.navbar-form{width:auto;padding-top:0;padding-bottom:0;margin-right:0;margin-left:0;border:0;-webkit-box-shadow:none;box-shadow:none}}.navbar-nav>li>.dropdown-menu{margin-top:0;border-top-left-radius:0;border-top-right-radius:0}.navbar-fixed-bottom .navbar-nav>li>.dropdown-menu{margin-bottom:0;border-top-left-radius:4px;border-top-right-radius:4px;border-bottom-right-radius:0;border-bottom-left-radius:0}.navbar-btn{margin-top:8px;margin-bottom:8px}.navbar-btn.btn-sm{margin-top:10px;margin-bottom:10px}.navbar-btn.btn-xs{margin-top:14px;margin-bottom:14px}.navbar-text{margin-top:15px;margin-bottom:15px}@media (min-width:768px){.navbar-text{float:left;margin-right:15px;margin-left:15px}}@media (min-width:768px){.navbar-left{float:left!important}.navbar-right{float:right!important;margin-right:-15px}.navbar-right~.navbar-right{margin-right:0}}.navbar-default{background-color:#f8f8f8;border-color:#e7e7e7}.navbar-default .navbar-brand{color:#777}.navbar-default .navbar-brand:focus,.navbar-default .navbar-brand:hover{color:#5e5e5e;background-color:transparent}.navbar-default .navbar-text{color:#777}.navbar-default .navbar-nav>li>a{color:#777}.navbar-default .navbar-nav>li>a:focus,.navbar-default .navbar-nav>li>a:hover{color:#333;background-color:transparent}.navbar-default .navbar-nav>.active>a,.navbar-default .navbar-nav>.active>a:focus,.navbar-default .navbar-nav>.active>a:hover{color:#555;background-color:#e7e7e7}.navbar-default .navbar-nav>.disabled>a,.navbar-default .navbar-nav>.disabled>a:focus,.navbar-default .navbar-nav>.disabled>a:hover{color:#ccc;background-color:transparent}.navbar-default .navbar-toggle{border-color:#ddd}.navbar-default .navbar-toggle:focus,.navbar-default .navbar-toggle:hover{background-color:#ddd}.navbar-default .navbar-toggle .icon-bar{background-color:#888}.navbar-default .navbar-collapse,.navbar-default .navbar-form{border-color:#e7e7e7}.navbar-default .navbar-nav>.open>a,.navbar-default .navbar-nav>.open>a:focus,.navbar-default .navbar-nav>.open>a:hover{color:#555;background-color:#e7e7e7}@media (max-width:767px){.navbar-default .navbar-nav .open .dropdown-menu>li>a{color:#777}.navbar-default .navbar-nav .open .dropdown-menu>li>a:focus,.navbar-default .navbar-nav .open .dropdown-menu>li>a:hover{color:#333;background-color:transparent}.navbar-default .navbar-nav .open .dropdown-menu>.active>a,.navbar-default .navbar-nav .open .dropdown-menu>.active>a:focus,.navbar-default .navbar-nav .open .dropdown-menu>.active>a:hover{color:#555;background-color:#e7e7e7}.navbar-default .navbar-nav .open .dropdown-menu>.disabled>a,.navbar-default .navbar-nav .open .dropdown-menu>.disabled>a:focus,.navbar-default .navbar-nav .open .dropdown-menu>.disabled>a:hover{color:#ccc;background-color:transparent}}.navbar-default .navbar-link{color:#777}.navbar-default .navbar-link:hover{color:#333}.navbar-default .btn-link{color:#777}.navbar-default .btn-link:focus,.navbar-default .btn-link:hover{color:#333}.navbar-default .btn-link[disabled]:focus,.navbar-default .btn-link[disabled]:hover,fieldset[disabled] .navbar-default .btn-link:focus,fieldset[disabled] .navbar-default .btn-link:hover{color:#ccc}.navbar-inverse{background-color:#222;border-color:#080808}.navbar-inverse .navbar-brand{color:#9d9d9d}.navbar-inverse .navbar-brand:focus,.navbar-inverse .navbar-brand:hover{color:#fff;background-color:transparent}.navbar-inverse .navbar-text{color:#9d9d9d}.navbar-inverse .navbar-nav>li>a{color:#9d9d9d}.navbar-inverse .navbar-nav>li>a:focus,.navbar-inverse .navbar-nav>li>a:hover{color:#fff;background-color:transparent}.navbar-inverse .navbar-nav>.active>a,.navbar-inverse .navbar-nav>.active>a:focus,.navbar-inverse .navbar-nav>.active>a:hover{color:#fff;background-color:#080808}.navbar-inverse .navbar-nav>.disabled>a,.navbar-inverse .navbar-nav>.disabled>a:focus,.navbar-inverse .navbar-nav>.disabled>a:hover{color:#444;background-color:transparent}.navbar-inverse .navbar-toggle{border-color:#333}.navbar-inverse .navbar-toggle:focus,.navbar-inverse .navbar-toggle:hover{background-color:#333}.navbar-inverse .navbar-toggle .icon-bar{background-color:#fff}.navbar-inverse .navbar-collapse,.navbar-inverse .navbar-form{border-color:#101010}.navbar-inverse .navbar-nav>.open>a,.navbar-inverse .navbar-nav>.open>a:focus,.navbar-inverse .navbar-nav>.open>a:hover{color:#fff;background-color:#080808}@media (max-width:767px){.navbar-inverse .navbar-nav .open .dropdown-menu>.dropdown-header{border-color:#080808}.navbar-inverse .navbar-nav .open .dropdown-menu .divider{background-color:#080808}.navbar-inverse .navbar-nav .open .dropdown-menu>li>a{color:#9d9d9d}.navbar-inverse .navbar-nav .open .dropdown-menu>li>a:focus,.navbar-inverse .navbar-nav .open .dropdown-menu>li>a:hover{color:#fff;background-color:transparent}.navbar-inverse .navbar-nav .open .dropdown-menu>.active>a,.navbar-inverse .navbar-nav .open .dropdown-menu>.active>a:focus,.navbar-inverse .navbar-nav .open .dropdown-menu>.active>a:hover{color:#fff;background-color:#080808}.navbar-inverse .navbar-nav .open .dropdown-menu>.disabled>a,.navbar-inverse .navbar-nav .open .dropdown-menu>.disabled>a:focus,.navbar-inverse .navbar-nav .open .dropdown-menu>.disabled>a:hover{color:#444;background-color:transparent}}.navbar-inverse .navbar-link{color:#9d9d9d}.navbar-inverse .navbar-link:hover{color:#fff}.navbar-inverse .btn-link{color:#9d9d9d}.navbar-inverse .btn-link:focus,.navbar-inverse .btn-link:hover{color:#fff}.navbar-inverse .btn-link[disabled]:focus,.navbar-inverse .btn-link[disabled]:hover,fieldset[disabled] .navbar-inverse .btn-link:focus,fieldset[disabled] .navbar-inverse .btn-link:hover{color:#444}.breadcrumb{padding:8px 15px;margin-bottom:20px;list-style:none;background-color:#f5f5f5;border-radius:4px}.breadcrumb>li{display:inline-block}.breadcrumb>li+li:before{padding:0 5px;color:#ccc;content:"/\00a0"}.breadcrumb>.active{color:#777}.label{display:inline;padding:.2em .6em .3em;font-size:75%;font-weight:700;line-height:1;color:#fff;text-align:center;white-space:nowrap;vertical-align:baseline;border-radius:.25em}a.label:focus,a.label:hover{color:#fff;text-decoration:none;cursor:pointer}.label:empty{display:none}.btn .label{position:relative;top:-1px}.label-default{background-color:#777}.label-default[href]:focus,.label-default[href]:hover{background-color:#5e5e5e}.label-primary{background-color:#337ab7}.label-primary[href]:focus,.label-primary[href]:hover{background-color:#286090}.label-success{background-color:#5cb85c}.label-success[href]:focus,.label-success[href]:hover{background-color:#449d44}.label-info{background-color:#5bc0de}.label-info[href]:focus,.label-info[href]:hover{background-color:#31b0d5}.label-warning{background-color:#f0ad4e}.label-warning[href]:focus,.label-warning[href]:hover{background-color:#ec971f}.label-danger{background-color:#d9534f}.label-danger[href]:focus,.label-danger[href]:hover{background-color:#c9302c}.badge{display:inline-block;min-width:10px;padding:3px 7px;font-size:12px;font-weight:700;line-height:1;color:#fff;text-align:center;white-space:nowrap;vertical-align:middle;background-color:#777;border-radius:10px}.badge:empty{display:none}.btn .badge{position:relative;top:-1px}.btn-group-xs>.btn .badge,.btn-xs .badge{top:0;padding:1px 5px}a.badge:focus,a.badge:hover{color:#fff;text-decoration:none;cursor:pointer}.list-group-item.active>.badge,.nav-pills>.active>a>.badge{color:#337ab7;background-color:#fff}.list-group-item>.badge{float:right}.list-group-item>.badge+.badge{margin-right:5px}.nav-pills>li>a>.badge{margin-left:3px}.jumbotron{padding-top:30px;padding-bottom:30px;margin-bottom:30px;color:inherit;background-color:#eee}.jumbotron .h1,.jumbotron h1{color:inherit}.jumbotron p{margin-bottom:15px;font-size:21px;font-weight:200}.jumbotron>hr{border-top-color:#d5d5d5}.container .jumbotron,.container-fluid .jumbotron{padding-right:15px;padding-left:15px;border-radius:6px}.jumbotron .container{max-width:100%}@media screen and (min-width:768px){.jumbotron{padding-top:48px;padding-bottom:48px}.container .jumbotron,.container-fluid .jumbotron{padding-right:60px;padding-left:60px}.jumbotron .h1,.jumbotron h1{font-size:63px}}.thumbnail{display:block;padding:4px;margin-bottom:20px;line-height:1.42857143;background-color:#fff;border:1px solid #ddd;border-radius:4px;-webkit-transition:border .2s ease-in-out;-o-transition:border .2s ease-in-out;transition:border .2s ease-in-out}.thumbnail a>img,.thumbnail>img{margin-right:auto;margin-left:auto}a.thumbnail.active,a.thumbnail:focus,a.thumbnail:hover{border-color:#337ab7}.thumbnail .caption{padding:9px;color:#333}.alert{padding:15px;margin-bottom:20px;border:1px solid transparent;border-radius:4px}.alert h4{margin-top:0;color:inherit}.alert .alert-link{font-weight:700}.alert>p,.alert>ul{margin-bottom:0}.alert>p+p{margin-top:5px}.alert-dismissable,.alert-dismissible{padding-right:35px}.alert-dismissable .close,.alert-dismissible .close{position:relative;top:-2px;right:-21px;color:inherit}.alert-success{color:#3c763d;background-color:#dff0d8;border-color:#d6e9c6}.alert-success hr{border-top-color:#c9e2b3}.alert-success .alert-link{color:#2b542c}.alert-info{color:#31708f;background-color:#d9edf7;border-color:#bce8f1}.alert-info hr{border-top-color:#a6e1ec}.alert-info .alert-link{color:#245269}.alert-warning{color:#8a6d3b;background-color:#fcf8e3;border-color:#faebcc}.alert-warning hr{border-top-color:#f7e1b5}.alert-warning .alert-link{color:#66512c}.alert-danger{color:#a94442;background-color:#f2dede;border-color:#ebccd1}.alert-danger hr{border-top-color:#e4b9c0}.alert-danger .alert-link{color:#843534}@-webkit-keyframes progress-bar-stripes{from{background-position:40px 0}to{background-position:0 0}}@-o-keyframes progress-bar-stripes{from{background-position:40px 0}to{background-position:0 0}}@keyframes progress-bar-stripes{from{background-position:40px 0}to{background-position:0 0}}.media{margin-top:15px}.media:first-child{margin-top:0}.media,.media-body{overflow:hidden;zoom:1}.media-body{width:10000px}.media-object{display:block}.media-object.img-thumbnail{max-width:none}.media-right,.media>.pull-right{padding-left:10px}.media-left,.media>.pull-left{padding-right:10px}.media-body,.media-left,.media-right{display:table-cell;vertical-align:top}.media-middle{vertical-align:middle}.media-bottom{vertical-align:bottom}.media-heading{margin-top:0;margin-bottom:5px}.media-list{padding-left:0;list-style:none}.list-group{padding-left:0;margin-bottom:20px}.list-group-item{position:relative;display:block;padding:10px 15px;margin-bottom:-1px;background-color:#fff;border:1px solid #ddd}.list-group-item:first-child{border-top-left-radius:4px;border-top-right-radius:4px}.list-group-item:last-child{margin-bottom:0;border-bottom-right-radius:4px;border-bottom-left-radius:4px}a.list-group-item,button.list-group-item{color:#555}a.list-group-item .list-group-item-heading,button.list-group-item .list-group-item-heading{color:#333}a.list-group-item:focus,a.list-group-item:hover,button.list-group-item:focus,button.list-group-item:hover{color:#555;text-decoration:none;background-color:#f5f5f5}button.list-group-item{width:100%;text-align:left}.list-group-item.disabled,.list-group-item.disabled:focus,.list-group-item.disabled:hover{color:#777;cursor:not-allowed;background-color:#eee}.list-group-item.disabled .list-group-item-heading,.list-group-item.disabled:focus .list-group-item-heading,.list-group-item.disabled:hover .list-group-item-heading{color:inherit}.list-group-item.disabled .list-group-item-text,.list-group-item.disabled:focus .list-group-item-text,.list-group-item.disabled:hover .list-group-item-text{color:#777}.list-group-item.active,.list-group-item.active:focus,.list-group-item.active:hover{z-index:2;color:#fff;background-color:#337ab7;border-color:#337ab7}.list-group-item.active .list-group-item-heading,.list-group-item.active .list-group-item-heading>.small,.list-group-item.active .list-group-item-heading>small,.list-group-item.active:focus .list-group-item-heading,.list-group-item.active:focus .list-group-item-heading>.small,.list-group-item.active:focus .list-group-item-heading>small,.list-group-item.active:hover .list-group-item-heading,.list-group-item.active:hover .list-group-item-heading>.small,.list-group-item.active:hover .list-group-item-heading>small{color:inherit}.list-group-item.active .list-group-item-text,.list-group-item.active:focus .list-group-item-text,.list-group-item.active:hover .list-group-item-text{color:#c7ddef}.list-group-item-success{color:#3c763d;background-color:#dff0d8}a.list-group-item-success,button.list-group-item-success{color:#3c763d}a.list-group-item-success .list-group-item-heading,button.list-group-item-success .list-group-item-heading{color:inherit}a.list-group-item-success:focus,a.list-group-item-success:hover,button.list-group-item-success:focus,button.list-group-item-success:hover{color:#3c763d;background-color:#d0e9c6}a.list-group-item-success.active,a.list-group-item-success.active:focus,a.list-group-item-success.active:hover,button.list-group-item-success.active,button.list-group-item-success.active:focus,button.list-group-item-success.active:hover{color:#fff;background-color:#3c763d;border-color:#3c763d}.list-group-item-info{color:#31708f;background-color:#d9edf7}a.list-group-item-info,button.list-group-item-info{color:#31708f}a.list-group-item-info .list-group-item-heading,button.list-group-item-info .list-group-item-heading{color:inherit}a.list-group-item-info:focus,a.list-group-item-info:hover,button.list-group-item-info:focus,button.list-group-item-info:hover{color:#31708f;background-color:#c4e3f3}a.list-group-item-info.active,a.list-group-item-info.active:focus,a.list-group-item-info.active:hover,button.list-group-item-info.active,button.list-group-item-info.active:focus,button.list-group-item-info.active:hover{color:#fff;background-color:#31708f;border-color:#31708f}.list-group-item-warning{color:#8a6d3b;background-color:#fcf8e3}a.list-group-item-warning,button.list-group-item-warning{color:#8a6d3b}a.list-group-item-warning .list-group-item-heading,button.list-group-item-warning .list-group-item-heading{color:inherit}a.list-group-item-warning:focus,a.list-group-item-warning:hover,button.list-group-item-warning:focus,button.list-group-item-warning:hover{color:#8a6d3b;background-color:#faf2cc}a.list-group-item-warning.active,a.list-group-item-warning.active:focus,a.list-group-item-warning.active:hover,button.list-group-item-warning.active,button.list-group-item-warning.active:focus,button.list-group-item-warning.active:hover{color:#fff;background-color:#8a6d3b;border-color:#8a6d3b}.list-group-item-danger{color:#a94442;background-color:#f2dede}a.list-group-item-danger,button.list-group-item-danger{color:#a94442}a.list-group-item-danger .list-group-item-heading,button.list-group-item-danger .list-group-item-heading{color:inherit}a.list-group-item-danger:focus,a.list-group-item-danger:hover,button.list-group-item-danger:focus,button.list-group-item-danger:hover{color:#a94442;background-color:#ebcccc}a.list-group-item-danger.active,a.list-group-item-danger.active:focus,a.list-group-item-danger.active:hover,button.list-group-item-danger.active,button.list-group-item-danger.active:focus,button.list-group-item-danger.active:hover{color:#fff;background-color:#a94442;border-color:#a94442}.list-group-item-heading{margin-top:0;margin-bottom:5px}.list-group-item-text{margin-bottom:0;line-height:1.3}.panel{margin-bottom:20px;background-color:#fff;border:1px solid transparent;border-radius:4px;-webkit-box-shadow:0 1px 1px rgba(0,0,0,.05);box-shadow:0 1px 1px rgba(0,0,0,.05)}.panel-body{padding:15px}.panel-heading{padding:10px 15px;border-bottom:1px solid transparent;border-top-left-radius:3px;border-top-right-radius:3px}.panel-heading>.dropdown .dropdown-toggle{color:inherit}.panel-title{margin-top:0;margin-bottom:0;font-size:16px;color:inherit}.panel-title>.small,.panel-title>.small>a,.panel-title>a,.panel-title>small,.panel-title>small>a{color:inherit}.panel-footer{padding:10px 15px;background-color:#f5f5f5;border-top:1px solid #ddd;border-bottom-right-radius:3px;border-bottom-left-radius:3px}.panel>.list-group,.panel>.panel-collapse>.list-group{margin-bottom:0}.panel>.list-group .list-group-item,.panel>.panel-collapse>.list-group .list-group-item{border-width:1px 0;border-radius:0}.panel>.list-group:first-child .list-group-item:first-child,.panel>.panel-collapse>.list-group:first-child .list-group-item:first-child{border-top:0;border-top-left-radius:3px;border-top-right-radius:3px}.panel>.list-group:last-child .list-group-item:last-child,.panel>.panel-collapse>.list-group:last-child .list-group-item:last-child{border-bottom:0;border-bottom-right-radius:3px;border-bottom-left-radius:3px}.panel>.panel-heading+.panel-collapse>.list-group .list-group-item:first-child{border-top-left-radius:0;border-top-right-radius:0}.panel-heading+.list-group .list-group-item:first-child{border-top-width:0}.list-group+.panel-footer{border-top-width:0}.panel>.panel-collapse>.table,.panel>.table,.panel>.table-responsive>.table{margin-bottom:0}.panel>.panel-collapse>.table caption,.panel>.table caption,.panel>.table-responsive>.table caption{padding-right:15px;padding-left:15px}.panel>.table-responsive:first-child>.table:first-child,.panel>.table:first-child{border-top-left-radius:3px;border-top-right-radius:3px}.panel>.table-responsive:first-child>.table:first-child>tbody:first-child>tr:first-child,.panel>.table-responsive:first-child>.table:first-child>thead:first-child>tr:first-child,.panel>.table:first-child>tbody:first-child>tr:first-child,.panel>.table:first-child>thead:first-child>tr:first-child{border-top-left-radius:3px;border-top-right-radius:3px}.panel>.table-responsive:first-child>.table:first-child>tbody:first-child>tr:first-child td:first-child,.panel>.table-responsive:first-child>.table:first-child>tbody:first-child>tr:first-child th:first-child,.panel>.table-responsive:first-child>.table:first-child>thead:first-child>tr:first-child td:first-child,.panel>.table-responsive:first-child>.table:first-child>thead:first-child>tr:first-child th:first-child,.panel>.table:first-child>tbody:first-child>tr:first-child td:first-child,.panel>.table:first-child>tbody:first-child>tr:first-child th:first-child,.panel>.table:first-child>thead:first-child>tr:first-child td:first-child,.panel>.table:first-child>thead:first-child>tr:first-child th:first-child{border-top-left-radius:3px}.panel>.table-responsive:first-child>.table:first-child>tbody:first-child>tr:first-child td:last-child,.panel>.table-responsive:first-child>.table:first-child>tbody:first-child>tr:first-child th:last-child,.panel>.table-responsive:first-child>.table:first-child>thead:first-child>tr:first-child td:last-child,.panel>.table-responsive:first-child>.table:first-child>thead:first-child>tr:first-child th:last-child,.panel>.table:first-child>tbody:first-child>tr:first-child td:last-child,.panel>.table:first-child>tbody:first-child>tr:first-child th:last-child,.panel>.table:first-child>thead:first-child>tr:first-child td:last-child,.panel>.table:first-child>thead:first-child>tr:first-child th:last-child{border-top-right-radius:3px}.panel>.table-responsive:last-child>.table:last-child,.panel>.table:last-child{border-bottom-right-radius:3px;border-bottom-left-radius:3px}.panel>.table-responsive:last-child>.table:last-child>tbody:last-child>tr:last-child,.panel>.table-responsive:last-child>.table:last-child>tfoot:last-child>tr:last-child,.panel>.table:last-child>tbody:last-child>tr:last-child,.panel>.table:last-child>tfoot:last-child>tr:last-child{border-bottom-right-radius:3px;border-bottom-left-radius:3px}.panel>.table-responsive:last-child>.table:last-child>tbody:last-child>tr:last-child td:first-child,.panel>.table-responsive:last-child>.table:last-child>tbody:last-child>tr:last-child th:first-child,.panel>.table-responsive:last-child>.table:last-child>tfoot:last-child>tr:last-child td:first-child,.panel>.table-responsive:last-child>.table:last-child>tfoot:last-child>tr:last-child th:first-child,.panel>.table:last-child>tbody:last-child>tr:last-child td:first-child,.panel>.table:last-child>tbody:last-child>tr:last-child th:first-child,.panel>.table:last-child>tfoot:last-child>tr:last-child td:first-child,.panel>.table:last-child>tfoot:last-child>tr:last-child th:first-child{border-bottom-left-radius:3px}.panel>.table-responsive:last-child>.table:last-child>tbody:last-child>tr:last-child td:last-child,.panel>.table-responsive:last-child>.table:last-child>tbody:last-child>tr:last-child th:last-child,.panel>.table-responsive:last-child>.table:last-child>tfoot:last-child>tr:last-child td:last-child,.panel>.table-responsive:last-child>.table:last-child>tfoot:last-child>tr:last-child th:last-child,.panel>.table:last-child>tbody:last-child>tr:last-child td:last-child,.panel>.table:last-child>tbody:last-child>tr:last-child th:last-child,.panel>.table:last-child>tfoot:last-child>tr:last-child td:last-child,.panel>.table:last-child>tfoot:last-child>tr:last-child th:last-child{border-bottom-right-radius:3px}.panel>.panel-body+.table,.panel>.panel-body+.table-responsive,.panel>.table+.panel-body,.panel>.table-responsive+.panel-body{border-top:1px solid #ddd}.panel>.table>tbody:first-child>tr:first-child td,.panel>.table>tbody:first-child>tr:first-child th{border-top:0}.panel>.table-bordered,.panel>.table-responsive>.table-bordered{border:0}.panel>.table-bordered>tbody>tr>td:first-child,.panel>.table-bordered>tbody>tr>th:first-child,.panel>.table-bordered>tfoot>tr>td:first-child,.panel>.table-bordered>tfoot>tr>th:first-child,.panel>.table-bordered>thead>tr>td:first-child,.panel>.table-bordered>thead>tr>th:first-child,.panel>.table-responsive>.table-bordered>tbody>tr>td:first-child,.panel>.table-responsive>.table-bordered>tbody>tr>th:first-child,.panel>.table-responsive>.table-bordered>tfoot>tr>td:first-child,.panel>.table-responsive>.table-bordered>tfoot>tr>th:first-child,.panel>.table-responsive>.table-bordered>thead>tr>td:first-child,.panel>.table-responsive>.table-bordered>thead>tr>th:first-child{border-left:0}.panel>.table-bordered>tbody>tr>td:last-child,.panel>.table-bordered>tbody>tr>th:last-child,.panel>.table-bordered>tfoot>tr>td:last-child,.panel>.table-bordered>tfoot>tr>th:last-child,.panel>.table-bordered>thead>tr>td:last-child,.panel>.table-bordered>thead>tr>th:last-child,.panel>.table-responsive>.table-bordered>tbody>tr>td:last-child,.panel>.table-responsive>.table-bordered>tbody>tr>th:last-child,.panel>.table-responsive>.table-bordered>tfoot>tr>td:last-child,.panel>.table-responsive>.table-bordered>tfoot>tr>th:last-child,.panel>.table-responsive>.table-bordered>thead>tr>td:last-child,.panel>.table-responsive>.table-bordered>thead>tr>th:last-child{border-right:0}.panel>.table-bordered>tbody>tr:first-child>td,.panel>.table-bordered>tbody>tr:first-child>th,.panel>.table-bordered>thead>tr:first-child>td,.panel>.table-bordered>thead>tr:first-child>th,.panel>.table-responsive>.table-bordered>tbody>tr:first-child>td,.panel>.table-responsive>.table-bordered>tbody>tr:first-child>th,.panel>.table-responsive>.table-bordered>thead>tr:first-child>td,.panel>.table-responsive>.table-bordered>thead>tr:first-child>th{border-bottom:0}.panel>.table-bordered>tbody>tr:last-child>td,.panel>.table-bordered>tbody>tr:last-child>th,.panel>.table-bordered>tfoot>tr:last-child>td,.panel>.table-bordered>tfoot>tr:last-child>th,.panel>.table-responsive>.table-bordered>tbody>tr:last-child>td,.panel>.table-responsive>.table-bordered>tbody>tr:last-child>th,.panel>.table-responsive>.table-bordered>tfoot>tr:last-child>td,.panel>.table-responsive>.table-bordered>tfoot>tr:last-child>th{border-bottom:0}.panel>.table-responsive{margin-bottom:0;border:0}.panel-group{margin-bottom:20px}.panel-group .panel{margin-bottom:0;border-radius:4px}.panel-group .panel+.panel{margin-top:5px}.panel-group .panel-heading{border-bottom:0}.panel-group .panel-heading+.panel-collapse>.list-group,.panel-group .panel-heading+.panel-collapse>.panel-body{border-top:1px solid #ddd}.panel-group .panel-footer{border-top:0}.panel-group .panel-footer+.panel-collapse .panel-body{border-bottom:1px solid #ddd}.panel-default{border-color:#ddd}.panel-default>.panel-heading{color:#333;background-color:#f5f5f5;border-color:#ddd}.panel-default>.panel-heading+.panel-collapse>.panel-body{border-top-color:#ddd}.panel-default>.panel-heading .badge{color:#f5f5f5;background-color:#333}.panel-default>.panel-footer+.panel-collapse>.panel-body{border-bottom-color:#ddd}.panel-primary{border-color:#337ab7}.panel-primary>.panel-heading{color:#fff;background-color:#337ab7;border-color:#337ab7}.panel-primary>.panel-heading+.panel-collapse>.panel-body{border-top-color:#337ab7}.panel-primary>.panel-heading .badge{color:#337ab7;background-color:#fff}.panel-primary>.panel-footer+.panel-collapse>.panel-body{border-bottom-color:#337ab7}.panel-success{border-color:#d6e9c6}.panel-success>.panel-heading{color:#3c763d;background-color:#dff0d8;border-color:#d6e9c6}.panel-success>.panel-heading+.panel-collapse>.panel-body{border-top-color:#d6e9c6}.panel-success>.panel-heading .badge{color:#dff0d8;background-color:#3c763d}.panel-success>.panel-footer+.panel-collapse>.panel-body{border-bottom-color:#d6e9c6}.panel-info{border-color:#bce8f1}.panel-info>.panel-heading{color:#31708f;background-color:#d9edf7;border-color:#bce8f1}.panel-info>.panel-heading+.panel-collapse>.panel-body{border-top-color:#bce8f1}.panel-info>.panel-heading .badge{color:#d9edf7;background-color:#31708f}.panel-info>.panel-footer+.panel-collapse>.panel-body{border-bottom-color:#bce8f1}.panel-warning{border-color:#faebcc}.panel-warning>.panel-heading{color:#8a6d3b;background-color:#fcf8e3;border-color:#faebcc}.panel-warning>.panel-heading+.panel-collapse>.panel-body{border-top-color:#faebcc}.panel-warning>.panel-heading .badge{color:#fcf8e3;background-color:#8a6d3b}.panel-warning>.panel-footer+.panel-collapse>.panel-body{border-bottom-color:#faebcc}.panel-danger{border-color:#ebccd1}.panel-danger>.panel-heading{color:#a94442;background-color:#f2dede;border-color:#ebccd1}.panel-danger>.panel-heading+.panel-collapse>.panel-body{border-top-color:#ebccd1}.panel-danger>.panel-heading .badge{color:#f2dede;background-color:#a94442}.panel-danger>.panel-footer+.panel-collapse>.panel-body{border-bottom-color:#ebccd1}.embed-responsive{position:relative;display:block;height:0;padding:0;overflow:hidden}.embed-responsive .embed-responsive-item,.embed-responsive embed,.embed-responsive iframe,.embed-responsive object,.embed-responsive video{position:absolute;top:0;bottom:0;left:0;width:100%;height:100%;border:0}.embed-responsive-16by9{padding-bottom:56.25%}.embed-responsive-4by3{padding-bottom:75%}.well{min-height:20px;padding:19px;margin-bottom:20px;background-color:#f5f5f5;border:1px solid #e3e3e3;border-radius:4px;-webkit-box-shadow:inset 0 1px 1px rgba(0,0,0,.05);box-shadow:inset 0 1px 1px rgba(0,0,0,.05)}.well blockquote{border-color:#ddd;border-color:rgba(0,0,0,.15)}.well-lg{padding:24px;border-radius:6px}.well-sm{padding:9px;border-radius:3px}.close{float:right;font-size:21px;font-weight:700;line-height:1;color:#000;text-shadow:0 1px 0 #fff;opacity:.2}.close:focus,.close:hover{color:#000;text-decoration:none;cursor:pointer;opacity:.5}button.close{-webkit-appearance:none;padding:0;cursor:pointer;background:0 0;border:0}.modal-open{overflow:hidden}.modal{position:fixed;top:0;right:0;bottom:0;left:0;z-index:1050;display:none;overflow:hidden;-webkit-overflow-scrolling:touch;outline:0}.modal.fade .modal-dialog{-webkit-transition:-webkit-transform .3s ease-out;-o-transition:-o-transform .3s ease-out;transition:transform .3s ease-out;-webkit-transform:translate(0,-25%);-ms-transform:translate(0,-25%);-o-transform:translate(0,-25%);transform:translate(0,-25%)}.modal.in .modal-dialog{-webkit-transform:translate(0,0);-ms-transform:translate(0,0);-o-transform:translate(0,0);transform:translate(0,0)}.modal-open .modal{overflow-x:hidden;overflow-y:auto}.modal-dialog{position:relative;width:auto;margin:10px}.modal-content{position:relative;background-color:#fff;-webkit-background-clip:padding-box;background-clip:padding-box;border:1px solid #999;border:1px solid rgba(0,0,0,.2);border-radius:6px;outline:0;-webkit-box-shadow:0 3px 9px rgba(0,0,0,.5);box-shadow:0 3px 9px rgba(0,0,0,.5)}.modal-backdrop{position:fixed;top:0;right:0;bottom:0;left:0;z-index:1040;background-color:#000}.modal-backdrop.fade{opacity:0}.modal-backdrop.in{opacity:.5}.modal-header{padding:15px;border-bottom:1px solid #e5e5e5}.modal-header .close{margin-top:-2px}.modal-title{margin:0;line-height:1.42857143}.modal-body{position:relative;padding:15px}.modal-footer{padding:15px;text-align:right;border-top:1px solid #e5e5e5}.modal-footer .btn+.btn{margin-bottom:0;margin-left:5px}.modal-footer .btn-group .btn+.btn{margin-left:-1px}.modal-footer .btn-block+.btn-block{margin-left:0}.modal-scrollbar-measure{position:absolute;top:-9999px;width:50px;height:50px;overflow:scroll}@media (min-width:768px){.modal-dialog{width:600px;margin:30px auto}.modal-content{-webkit-box-shadow:0 5px 15px rgba(0,0,0,.5);box-shadow:0 5px 15px rgba(0,0,0,.5)}.modal-sm{width:300px}}@media (min-width:992px){.modal-lg{width:900px}}.tooltip{position:absolute;z-index:1070;display:block;font-family:"Helvetica Neue",Helvetica,Arial,sans-serif;font-size:12px;font-style:normal;font-weight:400;line-height:1.42857143;text-align:left;text-align:start;text-decoration:none;text-shadow:none;text-transform:none;letter-spacing:normal;word-break:normal;word-spacing:normal;word-wrap:normal;white-space:normal;opacity:0;line-break:auto}.tooltip.in{opacity:.9}.tooltip.top{padding:5px 0;margin-top:-3px}.tooltip.right{padding:0 5px;margin-left:3px}.tooltip.bottom{padding:5px 0;margin-top:3px}.tooltip.left{padding:0 5px;margin-left:-3px}.tooltip-inner{max-width:200px;padding:3px 8px;color:#fff;text-align:center;background-color:#000;border-radius:4px}.tooltip-arrow{position:absolute;width:0;height:0;border-color:transparent;border-style:solid}.tooltip.top .tooltip-arrow{bottom:0;left:50%;margin-left:-5px;border-width:5px 5px 0;border-top-color:#000}.tooltip.top-left .tooltip-arrow{right:5px;bottom:0;margin-bottom:-5px;border-width:5px 5px 0;border-top-color:#000}.tooltip.top-right .tooltip-arrow{bottom:0;left:5px;margin-bottom:-5px;border-width:5px 5px 0;border-top-color:#000}.tooltip.right .tooltip-arrow{top:50%;left:0;margin-top:-5px;border-width:5px 5px 5px 0;border-right-color:#000}.tooltip.left .tooltip-arrow{top:50%;right:0;margin-top:-5px;border-width:5px 0 5px 5px;border-left-color:#000}.tooltip.bottom .tooltip-arrow{top:0;left:50%;margin-left:-5px;border-width:0 5px 5px;border-bottom-color:#000}.tooltip.bottom-left .tooltip-arrow{top:0;right:5px;margin-top:-5px;border-width:0 5px 5px;border-bottom-color:#000}.tooltip.bottom-right .tooltip-arrow{top:0;left:5px;margin-top:-5px;border-width:0 5px 5px;border-bottom-color:#000}.btn-group-vertical>.btn-group:after,.btn-group-vertical>.btn-group:before,.btn-toolbar:after,.btn-toolbar:before,.clearfix:after,.clearfix:before,.container-fluid:after,.container-fluid:before,.container:after,.container:before,.dl-horizontal dd:after,.dl-horizontal dd:before,.form-horizontal .form-group:after,.form-horizontal .form-group:before,.modal-footer:after,.modal-footer:before,.modal-header:after,.modal-header:before,.nav:after,.nav:before,.navbar-collapse:after,.navbar-collapse:before,.navbar-header:after,.navbar-header:before,.navbar:after,.navbar:before,.pager:after,.pager:before,.panel-body:after,.panel-body:before,.row:after,.row:before{display:table;content:" "}.btn-group-vertical>.btn-group:after,.btn-toolbar:after,.clearfix:after,.container-fluid:after,.container:after,.dl-horizontal dd:after,.form-horizontal .form-group:after,.modal-footer:after,.modal-header:after,.nav:after,.navbar-collapse:after,.navbar-header:after,.navbar:after,.pager:after,.panel-body:after,.row:after{clear:both}.center-block{display:block;margin-right:auto;margin-left:auto}.pull-right{float:right!important}.pull-left{float:left!important}.hide{display:none!important}.show{display:block!important}.invisible{visibility:hidden}.text-hide{font:0/0 a;color:transparent;text-shadow:none;background-color:transparent;border:0}.hidden{display:none!important}.affix{position:fixed}@-ms-viewport{width:device-width}.visible-lg,.visible-md,.visible-sm,.visible-xs{display:none!important}.visible-lg-block,.visible-lg-inline,.visible-lg-inline-block,.visible-md-block,.visible-md-inline,.visible-md-inline-block,.visible-sm-block,.visible-sm-inline,.visible-sm-inline-block,.visible-xs-block,.visible-xs-inline,.visible-xs-inline-block{display:none!important}@media (max-width:767px){.visible-xs{display:block!important}table.visible-xs{display:table!important}tr.visible-xs{display:table-row!important}td.visible-xs,th.visible-xs{display:table-cell!important}}@media (max-width:767px){.visible-xs-block{display:block!important}}@media (max-width:767px){.visible-xs-inline{display:inline!important}}@media (max-width:767px){.visible-xs-inline-block{display:inline-block!important}}@media (min-width:768px) and (max-width:991px){.visible-sm{display:block!important}table.visible-sm{display:table!important}tr.visible-sm{display:table-row!important}td.visible-sm,th.visible-sm{display:table-cell!important}}@media (min-width:768px) and (max-width:991px){.visible-sm-block{display:block!important}}@media (min-width:768px) and (max-width:991px){.visible-sm-inline{display:inline!important}}@media (min-width:768px) and (max-width:991px){.visible-sm-inline-block{display:inline-block!important}}@media (min-width:992px) and (max-width:1199px){.visible-md{display:block!important}table.visible-md{display:table!important}tr.visible-md{display:table-row!important}td.visible-md,th.visible-md{display:table-cell!important}}@media (min-width:992px) and (max-width:1199px){.visible-md-block{display:block!important}}@media (min-width:992px) and (max-width:1199px){.visible-md-inline{display:inline!important}}@media (min-width:992px) and (max-width:1199px){.visible-md-inline-block{display:inline-block!important}}@media (min-width:1200px){.visible-lg{display:block!important}table.visible-lg{display:table!important}tr.visible-lg{display:table-row!important}td.visible-lg,th.visible-lg{display:table-cell!important}}@media (min-width:1200px){.visible-lg-block{display:block!important}}@media (min-width:1200px){.visible-lg-inline{display:inline!important}}@media (min-width:1200px){.visible-lg-inline-block{display:inline-block!important}}@media (max-width:767px){.hidden-xs{display:none!important}}@media (min-width:768px) and (max-width:991px){.hidden-sm{display:none!important}}@media (min-width:992px) and (max-width:1199px){.hidden-md{display:none!important}}@media (min-width:1200px){.hidden-lg{display:none!important}}.visible-print{display:none!important}@media print{.visible-print{display:block!important}table.visible-print{display:table!important}tr.visible-print{display:table-row!important}td.visible-print,th.visible-print{display:table-cell!important}}.visible-print-block{display:none!important}@media print{.visible-print-block{display:block!important}}.visible-print-inline{display:none!important}@media print{.visible-print-inline{display:inline!important}}.visible-print-inline-block{display:none!important}@media print{.visible-print-inline-block{display:inline-block!important}}@media print{.hidden-print{display:none!important}}
</style>
BOOTS;
    }

    private function runUtilCombo($array, $select): string
    {
        $r = '';
        foreach ($array as $item) {
            /** @noinspection TypeUnsafeComparisonInspection */
            $r .= "<option value='$item' " . (($select == $item) ? 'selected' : '') . " >$item</option>";
        }
        return $r;
    }

    /**
     * It drops a table. It uses the method $this->drop();<br>
     * Note: if the table does not exist, then it could throw an exception or return false.
     *
     * @param string $tableName the name of the table to drop
     * @param string $extra     (optional) an extra value.
     *
     * @return bool
     * @throws Exception
     */
    public function dropTable(string $tableName, string $extra = ''): bool
    {
        $this->beginTry();
        $r = $this->drop($tableName, 'table', $extra);
        $this->endTry();
        return $r;
    }

    /**
     * It drops (DDL) an object
     *
     * @param string $objectName     The name of the object.
     * @param string $type           =['table','view','columns','function','procedure'][$i]
     *                               The type of object to drop.
     * @param string $extra          (optional) An extra value added at the end
     *                               of the query
     *
     * @return bool
     * @throws Exception
     */
    public function drop(string $objectName, string $type, string $extra = ''): bool
    {
        $this->beginTry();
        $sql = "drop $type " . $this->addDelimiter($objectName) . " $extra";
        $r = $this->conn1->exec($sql) !== false;
        $this->endTry();
        return $r;
    }

    /**
     * It truncates (DDL)  a table
     *
     * @param string $tableName
     * @param string $extra     (optional) An extra value added at the end of the
     *                          query
     * @param bool   $forced    If true then it forces the truncate (it is useful when the table has a foreign key)
     *
     * @return array|bool
     * @throws Exception
     */
    public function truncate(string $tableName, string $extra = '', bool $forced = false)
    {
        $this->beginTry();
        $r = $this->service->truncate($tableName, $extra, $forced);
        $this->endTry();
        return $r;
    }

    /**
     * It calls a store procedure.<br>
     * <b>Example:</b><br>
     * <pre>
     * $this->callProcedure('procexample',['in_name'=>'aa','in_description'=>'bbb'],['in_description])
     * </pre><br>
     * <b>Note:<b>sqlsrv could returns an associative array.
     *
     * @param string $procName      The name of the store procedure.
     * @param array  $arguments     An associative array with the name of the argument and it's value
     * @param array  $outputColumns [optional] the name of the columns that must be returned.
     * @return mixed|false returns a value if success, otherwise false. You can find the error message at
     *                              $this->errorText
     * @throws Exception
     */
    public function callProcedure(string $procName, array &$arguments = [], array $outputColumns = [])
    {
        $this->beginTry();
        try {
            $result = $this->service->callProcedure($procName, $arguments, $outputColumns);
            $this->endTry();
            return $result;
        } catch (Exception $ex) {
            $this->errorText = $ex->getMessage();
            $this->endTry();
            return false;
        }
    }

    /**
     * It resets the identity of a table (if any)
     *
     * @param string $tableName The name of the table
     * @param int    $newValue
     *
     * @return array|bool|null
     * @throws Exception
     */
    public function resetIdentity(string $tableName, int $newValue = 0)
    {
        $this->beginTry();
        $r = $this->service->resetIdentity($tableName, $newValue);
        $this->endTry();
        return $r;
    }

    /**
     * Create a table used for a sequence<br>
     * It also could create a function called next_name-of-the-table() <br>
     * The operation will fail if the table, sequence, function or procedure already exists.
     *
     * @param string|null $tableSequence     The table to use<br>
     *                                       If null then it uses the table
     *                                       defined in
     *                                       $pdoOne->tableSequence.
     * @param string      $method            =['snowflake','sequence'][$i]
     *                                       snowflake=it generates a value
     *                                       based on snowflake<br> sequence= it generates a regular sequence
     *                                       number
     *                                       (1,2,3...)<br>
     * @return bool
     * @throws Exception
     */
    public function createSequence(?string $tableSequence = null, string $method = 'snowflake'): bool
    {
        $this->beginTry();
        $tableSequence = $tableSequence ?? $this->tableSequence;
        $sqls = $this->service->createSequence($tableSequence, $method);
        $r = true;
        foreach ($sqls as $sql) {
            $r = $r && ($this->conn1->exec($sql) !== false);
        }
        $this->endTry();
        return $r;
    }

    /**
     * It creates a store procedure<br>
     * <b>Example:</b><br>
     * <pre>
     * // arg1 and arg2 are "in" arguments:
     * $this->createProcedure('proc1','in arg1 int,in arg2 varchar(50)','//body here');
     * // arg1 and arg2 are "in" arguments:
     * $this->createProcedure('proc1',['arg1'=>'int','arg2'=>'varchar(50)'],'//body here');
     * // arg1 is "in", arg2 is "out":
     * $this->createProcedure('proc1',
     *                      [
     *                          ['in','arg1','int'],
     *                          ['out','arg2','varchar(50)']
     *                      ],'//body here'); // mysql arg1 is "in", arg2 is "in":
     * $this->createProcedure('proc1',
     *                      [
     *                          ['','arg1','int'],
     *                          ['output','arg2','varchar(50)']
     *                      ],'//body here'); // sqlsrv arg1 is "in", arg2 is "output":
     * $this->createProcedure('proc1',
     *                      [
     *                          ['arg1','int'],
     *                          ['arg2','varchar(50)']
     *                      ],'//body here');
     * </pre>
     *
     * @param string       $procedureName The name of the store procedure
     * @param array|string $arguments     The arguments. It could be an associative array, a string or a multiple array
     * @param string       $body          The body of the store procedure
     * @param string       $extra
     * @return false|int
     * @throws Exception
     */
    public function createProcedure(string $procedureName, $arguments = [], string $body = '', string $extra = '')
    {
        $this->beginTry();
        $sql = $this->service->createProcedure($procedureName, $arguments, $body, $extra);
        $r = $this->conn1->exec($sql);
        $this->endTry();
        return $r;
    }

    /**
     * Create a table<br>
     * <b>Example:</b><br>
     * <pre>
     * // no universal (false indicates native sql)
     * createTable('products',['id'=>'int not null','name'=>'varchar(50) null'],'id','','',false);
     * // universal (true indicates universal)
     * createTable('products',['id int','name string(50) null'],'id','','',true);
     * </pre>
     *
     * @param string            $tableName        The name of the new table. This method will fail if the table exists.
     * @param array             $definition       An associative array with the definition of the columns.<br>
     *                                            <b>Example:</b><br>
     * @param string|null|array $primaryKey       The column's name that is primary key.<br>
     *                                            If the value is an associative array then it generates all keys
     * @param string|null       $extra            An extra operation inside of
     *                                            the definition of the table.
     * @param string|null       $extraOutside     An extra operation outside of
     *                                            the definition of the
     *                                            table.<br> It replaces the
     *                                            default values outside the
     *                                            table
     * @param bool              $universal        (default false), if true, then it expects a universal definition of
     *                                            table<br> This definition is simplified, and it works as: This
     *                                            definition is simplified ("column type null extra") and it doesn't
     *                                            contain all the definitions<br>
     *                                            <b>Example: universal vs native</b>
     *                                            <ul>
     *                                            <li>"name string(20) null" -> "name varchar(20) null"</li>
     *                                            <li>"creationDate datetime null" -> "creationDate date null"</li>
     *                                            <li>"id int" -> "id int not null"</li>
     *                                            <li>"id int  extra" -> "id int not null extra"</li>
     *                                            </ul>
     *                                            <b>allow nulls</b>: "null" for null or " " for not null.<br>
     *                                            <b>types allowed</b>: int, long, decimal, bool, date, datetime,
     *                                            timestamp, string<br>
     * @return bool
     * @throws Exception
     */
    public function createTable(
        string  $tableName,
        array   $definition,
                $primaryKey = null,
        ?string $extra = '',
        ?string $extraOutside = '',
        bool    $universal = false
    ): bool
    {
        if ($universal) {
            $definition = $this->convertUniversal($definition);
        }
        $this->endTry();
        $sql = $this->service->createTable($tableName, $definition, $primaryKey, $extra, $extraOutside);
        $r = $this->runMultipleRawQuery($sql);
        $this->endTry();
        return $r;
    }

    /**
     * it converts a natural definition of table to a specific definition of table.<br>
     * This definition is simplified ("column type null extra") and it doesn't contain all the definitions<br>
     * <b>Example:</b>
     * <pre>
     * "name string(20) null" -> "name varchar(20) null"
     * "creationDate datetime null" -> "creationDate date null"
     * "id int" -> "id int not null"
     * "id int  extra" -> "id int not null extra" (check the double space for null)
     * </pre>
     * <b>types allowed</b>: int, long, decimal, bool, date, datetime, timestamp, string<br>
     * @param array $simpledef
     * @return array
     */
    protected function convertUniversal(array $simpledef): array
    {
        $result = [];
        foreach ($simpledef as $v) {
            @[$name, $typeOrigin, $extra] = explode(' ', trim($v), 3);
            $tmp = explode(' ', str_replace(['(', ')'], [' ', ''], $typeOrigin), 2);
            $type = $tmp[0];
            $len = $tmp[1] ?? null;
            $extra = ' ' . $extra . ' ';
            $realType = $this->service->translateType($type, $len);
            $nullReal = strpos($extra, ' null ') !== false ? 'null' : 'not null';
            $identityReal = (strpos($extra, ' autonumeric ') !== false) ? $this->service->translateExtra('autonumeric') : '';
            $extra = trim(str_replace([' null ', ' autonumeric '], ['', ''], $extra));
            $result[$name] = "$realType $nullReal $identityReal $extra";
        }
        return $result;
    }

    /**
     * Run multiples unprepared query added as an array or separated by ;<br>
     * <b>Example:</b><br>
     * <pre>
     * $this->runMultipleRawQuery("insert into() values(1); insert into() values(2)");
     * $this->runMultipleRawQuery(["insert into() values(1)","insert into() values(2)"]);
     * </pre>
     *
     * @param string|array $listSql             SQL multiples queries separated
     *                                          by ";" or an array
     * @param bool         $continueOnError     if true then it continues on
     *                                          error.
     *
     * @return bool
     * @throws Exception
     */
    public function runMultipleRawQuery($listSql, bool $continueOnError = false): bool
    {
        $this->beginTry();
        if (!$this->isOpen) {
            $this->throwError("RMRQ: It's not connected to the database", '');
            return false;
        }
        $arr = (is_array($listSql)) ? $listSql : explode(';', $listSql);
        $ok = true;
        $counter = 0;
        foreach ($arr as $rawSql) {
            if (trim($rawSql) !== '') {
                if ($this->readonly) {
                    if (stripos($rawSql, 'insert ') === 0 || stripos($rawSql, 'update ') === 0
                        || stripos($rawSql, 'delete ') === 0
                    ) {
                        // we aren't checking SQL-DCL queries. Also, "insert into" is stopped but "  insert into" not.
                        $ok = false;
                        if (!$continueOnError) {
                            $this->throwError('Database is in READ ONLY MODE', '');
                        }
                    }
                }
                $this->lastQuery = $rawSql;
                $this->storeInfo($rawSql);
                $msgError = '';
                try {
                    $r = $this->conn1->query($rawSql);
                } catch (Exception $ex) {
                    $r = false;
                    $msgError = $ex->getMessage();
                }
                if ($r === false) {
                    $ok = false;
                    if (!$continueOnError) {
                        $this->throwError('Unable to run raw query', $this->lastQuery, $msgError);
                    }
                } else {
                    $counter += $r->rowCount();
                }
            }
        }
        $this->affected_rows = $counter;
        $this->endTry();
        return $ok;
    }

    /**
     * It adds foreign keys to a table<br>
     * <b>Example:<b><br>
     * <pre>
     * $this->createFK('table',['col'=>"FOREIGN KEY REFERENCES`tableref`(`colref`)"]); // mysql
     * $this->createFK('table',['col'=>"FOREIGN KEY REFERENCES[tableref]([colref])"]); // sqlsrv
     * $this->createFK('table',['col'=>"FOREIGN KEY REFERENCES TABLE1(COL1)"]); // oci
     * </pre>
     *
     * @param string $tableName   The name of the table.
     * @param array  $definitions Associative array with the definition (SQL) of the foreign keys.
     *
     * @return bool
     * @throws Exception
     */
    public function createFK(string $tableName, array $definitions): bool
    {
        $this->beginTry();
        $sql = $this->service->createFK($tableName, $definitions);
        $r = $this->runMultipleRawQuery($sql);
        $this->endTry();
        return $r;
    }

    /**
     * It creates indexes. It doesn't replace previous indexes. The definition could depend in the type of database<br>
     * <b>Example:</b><br>
     * <pre>
     * $this->createIndex('table',['col1'=>'INDEX','col2=>'UNIQUE INDEX']);
     * </pre>
     *
     * @param string $tableName   the name of the table.
     * @param array  $definitions An associative array
     * @return bool true if the operation was successful, otherwise false.
     * @throws Exception
     */
    public function createIndex(string $tableName, array $definitions): bool
    {
        $this->beginTry();
        $sql = $this->service->createIndex($tableName, $definitions);
        $r = $this->runMultipleRawQuery($sql);
        $this->endTry();
        return $r;
    }

    /**
     * It changes default database, schema or user.
     *
     * @param $dbName
     *
     * @test void this('travisdb')
     */
    public function db($dbName): void
    {
        $this->beginTry();
        if (!$this->isOpen) {
            $this->endTry();
            return;
        }
        $this->db = $dbName;
        $this->tableDependencyArray = null;
        $this->tableDependencyArrayCol = null;
        $this->conn1->exec($this->service->db($dbName));
        $this->endTry();
    }

    /**
     * returns if the database is in read-only mode or not.
     *
     * @return bool
     * @test equals false,this(),'the database is read only'
     */
    public function readonly(): bool
    {
        return $this->readonly;
    }

    /**
     * Alias of PdoOne::connect()
     *
     * @param bool $failIfConnected
     * @param bool $alterSession
     * @test exception this(false)
     * @see  PdoOne::connect()
     */
    public function open(bool $failIfConnected = true, bool $alterSession = false): void
    {
        $this->connect($failIfConnected, $alterSession);
    }

    /**
     * It closes the connection
     *
     * @test void this()
     */
    public function close(): void
    {
        $this->isOpen = false;
        if ($this->conn1 === null) {
            return;
        } // it's already close
        @$this->conn1 = null;
    }

    /**
     * It gets the primary key of a table
     *
     * @param string      $table     The name of the table
     * @param string|null $pkDefault The default pk if the key is not found.
     * @return array|false|mixed|string
     */
    public function getPK(string $table, ?string $pkDefault = null)
    {
        $this->beginTry();
        $r = $this->service->getPK($table, $pkDefault);
        $this->endTry();
        return $r;
    }

    /**
     * It returns the next sequence.
     * It gets a collision free number if we don't do more than one operation
     * every 0.0001 seconds.
     * But, if we do 2 or more operations per seconds then, it adds a sequence
     * number from
     * 0 to 4095
     * So, the limit of this function is 4096 operations per 0.0001 second.
     *
     * @see \eftec\PdoOne::getSequencePHP It's the same but it uses less
     *      resources but lacks of a sequence.
     *
     * @param bool   $asFloat          It returns the value as a float.
     * @param bool   $unpredictable    It returns the value as an unpredictable value. It flips some digits.
     * @param string $sequenceName     (optional) the name of the sequence. If
     *                                 not then it uses $this->tableSequence
     *
     * @return string . Example string(19) "3639032938181434317"
     * @throws Exception
     */
    public function getSequence(
        bool   $asFloat = false,
        bool   $unpredictable = false,
        string $sequenceName = ''
    )
    {
        $this->beginTry();
        $sql = $this->service->getSequence($sequenceName);
        $r = $this->runRawQuery($sql);
        $this->endTry();
        if ($unpredictable) {
            if (PHP_INT_SIZE === 4) {
                return $this->encryption->encryptSimple($r[0]['id']);
            }
            // $r is always a 32-bit number, so it will fail in PHP 32bits
            return $this->encryption->encryptInteger($r[0]['id']);
        }
        if ($asFloat) {
            return (float)$r[0]['id'];
        }
        return $r[0]['id'];
    }

    /**
     * <p>This function returns a unique sequence<p>
     * It ensures a collision free number only if we don't do more than one
     * operation per 0.0001 second However,it also adds a pseudo random number
     * (0-4095) so the chances of collision is 1/4095 (per two operations done
     * every 0.0001 second).<br> It is based on Twitter's Snowflake number
     *
     * @param bool $unpredictable
     *
     * @return string
     * @see \eftec\PdoOne::getSequence
     */
    public function getSequencePHP(bool $unpredictable = false): string
    {
        $ms = microtime(true);
        //$ms=1000;
        $timestamp = round($ms * 1000);
        $rand = ((int)fmod($ms, 1) * 1000000) % 4096; // 4096= 2^12 It is the millionth of seconds
        $calc = (($timestamp - 1459440000000) << 22) + ($this->nodeId << 12) + $rand;
        usleep(1);
        if ($unpredictable) {
            if (PHP_INT_SIZE === 4) {
                return '' . $this->encryption->encryptSimple($calc);
            }
            // $r is always a 32-bit number, so it will fail in PHP 32bits
            return '' . $this->encryption->encryptInteger($calc);
        }
        return '' . $calc;
    }

    /**
     * It uses \eftec\PdoOne::$masks0 and \eftec\PdoOne::$masks1 to flip
     * the number, so they are not as predictable.
     * This function doesn't add entrophy. However, the generation of Snowflakes
     * id
     * (getSequence/getSequencePHP) generates its own entrophy. Also,
     * both masks0[] and masks1[] adds an extra secrecy.
     *
     * @param $number
     *
     * @return array|string|string[]
     */
    public function getUnpredictable($number)
    {
        $string = '' . $number;
        $maskSize = count($this->masks0);
        for ($i = 0; $i < $maskSize; $i++) {
            $init = $this->masks0[$i];
            $end = $this->masks1[$i];
            $tmp = $string[$end];
            $string = substr_replace($string, $string[$init], $end, 1);
            $string = substr_replace($string, $tmp, $init, 1);
        }
        return $string;
    }

    /**
     * it is the inverse of \eftec\PdoOne::getUnpredictable
     *
     * @param $number
     *
     * @return mixed
     * @see \eftec\PdoOne::$masks0
     * @see \eftec\PdoOne::$masks1
     */
    public function getUnpredictableInv($number)
    {
        $maskSize = count($this->masks0);
        for ($i = $maskSize - 1; $i >= 0; $i--) {
            $init = $this->masks1[$i];
            $end = $this->masks0[$i];
            $tmp = $number[$end];
            $number = substr_replace($number, $number[$init], $end, 1);
            $number = substr_replace($number, $tmp, $init, 1);
        }
        return $number;
    }

    /**
     * Returns true if the table exists. It uses the default schema ($this->db)
     *
     * @param string $tableName The name of the table (without schema).
     *
     * @return bool true if the table exist
     * @throws Exception
     */
    public function tableExist(string $tableName): bool
    {
        $this->beginTry();
        $r = $this->objectExist($tableName);
        $this->endTry();
        return $r;
    }

    /**
     * returns true if the object exists
     * Currently only works with table
     *
     * @param string $objectName
     * @param string $type =['table','function','sequence','procedure'][$i] The type of the object
     *
     * @return bool
     * @throws Exception
     */
    public function objectExist(string $objectName, string $type = 'table'): bool
    {
        $this->beginTry();
        $query = $this->service->objectExist($type);
        if ($this->databaseType === 'oci') {
            $arr = $this->runRawQuery($query, [$objectName, $this->db]);
        } else {
            $arr = $this->runRawQuery($query, [$objectName]);
        }
        $r = is_array($arr) && count($arr) > 0;
        $this->endTry();
        return $r;
    }

    /** @noinspection TypeUnsafeComparisonInspection */
    /**
     * It returns a list of tables ordered by dependency (from no dependent to
     * more dependent)<br>
     * <b>Note:</b>: This operation is not foolproof because the tables could
     * have circular reference.
     *
     * @param int  $maxLoop            The number of tests. If the sort is
     *                                 correct, then it ends as fast as it can.
     * @param bool $returnProblems     [false] if true then it returns all the
     *                                 tables with problem
     * @param bool $debugTrace         [false] if true then it shows the
     *                                 operations done.
     *
     * @return array List of table.
     * @throws Exception
     */
    public function tableSorted(int $maxLoop = 5, bool $returnProblems = false, bool $debugTrace = false): array
    {
        $this->beginTry();
        [$tables, $after, $before] = $this->tableDependency();
        $tableSorted = [];
        // initial load
        foreach ($tables as $table) {
            $tableSorted[] = $table;
        }
        $problems = [];
        for ($i = 0; $i < $maxLoop; $i++) {
            if ($this->reSort($tables, $tableSorted, $after, $before, $problems, $debugTrace)) {
                break;
            }
        }
        $this->endTry();
        if ($returnProblems) {
            return $problems;
        }
        return $tableSorted;
    }


    //</editor-fold>
    //<editor-fold desc="chain calls">
    /**
     * Resort the tableSorted list based in dependencies.
     *
     * @param array $tables            An associative array with the name of the
     *                                 tables
     * @param array $tableSorted       (ref) An associative array with the name
     *                                 of the tables
     * @param array $after             $after[city]=[country,..]
     * @param array $before            $before[city]=[address]
     * @param array $tableProblems     (ref) an associative array whtn the name
     *                                 of the tables with problem.
     * @param bool  $debugTrace        If true then it shows a debug per
     *                                 operation.
     *
     * @return bool true if the sort is finished and there is nothing wrong.
     */
    protected function reSort(
        array $tables,
        array &$tableSorted,
        array $after,
        array $before,
        array &$tableProblems,
        bool  $debugTrace = false
    ): bool
    {
        shuffle($tables);
        $tableProblems = [];
        $nothingWrong = true;
        foreach ($tables as $table) {
            $pos = array_search($table, $tableSorted, true);
            // search for after in the wrong position
            $wrong = false;
            $pairProblem = '';
            for ($i = 0; $i < $pos; $i++) {
                if (in_array($tableSorted[$i], $before[$table], true)) {
                    $wrong = true;
                    $nothingWrong = false;
                    $pairProblem = $tableSorted[$i];
                    if ($debugTrace) {
                        echo "reSort: [wrong position] $table ($pos) is after " . $tableSorted[$i] . " ($i)<br>";
                    }
                    break;
                }
            }
            if ($wrong) {
                // the value is already in the list, we start removing it
                $cts = count($tableSorted);
                for ($i = $pos + 1; $i < $cts; $i++) {
                    $tableSorted[$i - 1] = $tableSorted[$i];
                }
                unset($tableSorted[count($tableSorted) - 1]); // we removed the last element.
                // We found the initial position to add.
                $pInitial = 0;
                foreach ($tableSorted as $k2 => $v2) {
                    if (in_array($v2, $after[$table], true)) {
                        $pInitial = $k2 + 1;
                    }
                }
                // we found the last position
                $pEnd = count($tableSorted);
                foreach ($tableSorted as $k2 => $v2) {
                    if (in_array($v2, $before[$table], true)) {
                        $pEnd = $k2 - 1;
                    }
                }
                if ($pEnd < $pInitial) {
                    $tableProblems[] = $table;
                    $tableProblems[] = $pairProblem;
                    if ($debugTrace) {
                        echo "reSort: $table There is a circular reference (From $pInitial to $pEnd)<br>";
                    }
                }
                if (isset($tableSorted[$pInitial])) {
                    if ($debugTrace) {
                        echo "reSort: moving $table to $pInitial<br>";
                    }
                    // the space is used, so we stack the values
                    for ($i = count($tableSorted) - 1; $i >= $pInitial; $i--) {
                        $tableSorted[$i + 1] = $tableSorted[$i];
                    }
                }
                $tableSorted[$pInitial] = $table;
            }
        }
        return $nothingWrong;
    }

    /**
     * It returns the statistics (minimum,maximum,average,sum and count) of a
     * column of a table
     *
     * @param string $tableName  Name of the table
     * @param string $columnName The column name to analyze.
     *
     * @return array|bool Returns an array of the type
     *                    ['min','max','avg','sum','count']
     * @throws Exception
     */
    public function statValue(string $tableName, string $columnName)
    {
        $this->beginTry();
        $query = "select min($columnName) min
						,max($columnName) max
						,avg($columnName) avg
						,sum($columnName) sum
						,count($columnName) count
						 from $tableName";
        $r = $this->runRawQuery($query);
        $this->endTry();
        return $r;
    }

    /**
     * Returns the columns of a table
     *
     * @param string $tableName The name of the table.
     *
     * @return array|bool=['colname','coltype','colsize','colpres','colscale','iskey','isidentity','isnullable']
     * @throws Exception
     */
    public function columnTable(string $tableName)
    {
        $this->beginTry();
        $query = $this->service->columnTable($tableName);
        $r = $this->runRawQuery($query);
        $this->endTry();
        return $r;
    }

    /**
     * Returns all the foreign keys (and relation) of a table
     *
     * @param string $tableName The name of the table.
     *
     * @return array|bool
     * @throws Exception
     */
    public function foreignKeyTable(string $tableName)
    {
        $this->beginTry();
        $query = $this->service->foreignKeyTable($tableName);
        $r = $this->runRawQuery($query);
        $this->endTry();
        return $r;
    }

    /**
     * Returns true if the sql starts with "select " or with "show ".
     *
     * @param string $sql The query
     *
     * @return bool
     */
    public function isQuery(string $sql): bool
    {
        $sql = trim($sql);
        return (stripos($sql, 'select ') === 0 || stripos($sql, 'show ') === 0);
    }

    /** @noinspection TypeUnsafeComparisonInspection */
    public function filterKey($condition, $columns, $returnSimple)
    {
        if ($condition === null) {
            // no filter.
            return $columns;
        }
        $result = [];
        foreach ($columns as $key => $col) {
            if ($returnSimple) {
                if ($col == $condition) {
                    $result[$key] = $col;
                }
            } elseif ($col['key'] == $condition) {
                $result[$key] = $col;
            }
        }
        return $result;
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
     * @param string|null       $tableName
     * @param string[]|null     $tableDef
     * @param string[]|int|null $values
     *
     * @return false|int|string Returns the identity (if any) or false if the operation fails.
     * @throws Exception
     */
    public function insert(
        ?string $tableName = null,
        ?array  $tableDef = null,
                $values = PdoOne::NULL
    )
    {
        $this->beginTry();
        $query = new PdoOneQuery($this);
        $r = $query->insert($tableName, $tableDef, $values);
        $this->endTry();
        return $r;
    }

    /**
     * It returns an array with the metadata of each column (i.e. name, type,
     * size, etc.) or false if error.
     *
     * @param string|null $sql     If null then it uses the generation of query
     *                             (if any).<br> if string then get the
     *                             statement of the query
     *
     * @param array       $args
     *
     * @return array|bool
     * @throws Exception
     */
    public function toMeta(?string $sql = null, array $args = [])
    {
        $this->beginTry();
        $query = new PdoOneQuery($this);
        $r = $query->toMeta($sql, $args);
        $this->endTry();
        return $r;
    }

    /**
     * If false then it won't generate an error.<br>
     * If true (default), then on error, it behaves normally<br>
     * If false, then the error is captured and store in $this::$errorText<br>
     * This command is specific for generation of query and its reseted when the query is executed.
     *
     * @param bool $error
     *
     * @return PdoOneQuery
     * @see \eftec\PdoOne::$errorText
     */
    public function genError(bool $error = false): PdoOneQuery
    {
        return (new PdoOneQuery($this))->genError($error);
    }

    /**
     * It generates a query for "min". It is a macro of select()
     * <br><b>Example</b>:<br>
     * ->min('from table','col')->firstScalar() // select min(col) from
     * table<br>
     * ->min('col')->from('table')->firstScalar() // select min(col) from
     * table<br>
     * ->min('','col')->from('table')->firstScalar() // select min(col) from
     * table<br>
     *
     * @param string $sql
     * @param string $arg
     *
     * @return float|int
     * @throws Exception
     */
    public function min(string $sql = '', string $arg = '')
    {
        return (new PdoOneQuery($this))->_aggFn('min', $sql, $arg);
    }

    /**
     * It generates a query for "count". It is a macro of select()
     * <br><b>Example</b>:<br>
     * <pre>
     * ->from('table')->count('') // select count(*) from
     * table<br>
     * ->count('from table')->firstScalar() // select count(*) from table<br>
     * ->count('from table where condition=1')->firstScalar() // select count(*)
     * from table where condition=1<br>
     * ->count('from table','col')->firstScalar() // select count(col) from
     * table<br>
     * </pre>
     *
     * @param string|null $sql [optional]
     * @param string      $arg [optional]
     *
     * @return int|float
     * @throws Exception
     */
    public function count(?string $sql = '', string $arg = '*')
    {
        return (new PdoOneQuery($this))->_aggFn('count', $sql, $arg);
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
     * @return float|int
     * @throws Exception
     */
    public function sum(string $sql = '', string $arg = '')
    {
        return (new PdoOneQuery($this))->_aggFn('sum', $sql, $arg);
    }

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
     * @return float|int
     * @throws Exception
     */
    public function max(string $sql = '', string $arg = '')
    {
        return (new PdoOneQuery($this))->_aggFn('max', $sql, $arg);
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
     * @return float|int
     * @throws Exception
     */
    public function avg(string $sql = '', string $arg = '')
    {
        return (new PdoOneQuery($this))->_aggFn('avg', $sql, $arg);
    }

    /**
     * Adds a from for a query. It could be used by select,insert,update and
     * delete.<br>
     * <b>Example:</b><br>
     * <pre>
     *      from('table')
     *      from('table alias')
     *      from('table alias','dbo') // from dbo.table alias
     *      from('table1,table2')
     *      from('table1 inner join table2 on table1.c=table2.c')
     * </pre>
     *
     * @param string      $sql    Input SQL query
     * @param string|null $schema The schema/database of the table without trailing dot.<br>
     *                            Example 'database' or 'database.dbo'
     *
     * @return PdoOneQuery
     * @test InstanceOf PdoOne::class,this('table t1')
     */
    public function from(string $sql, ?string $schema = null): PdoOneQuery
    {
        return (new PdoOneQuery($this))->from($sql, $schema);
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
    public function insertObject(string $tableName, &$object, array $excludeColumn = [])
    {
        return (new PdoOneQuery($this))->insertObject($tableName, $object, $excludeColumn);
    }

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
     * @param string|null   $tableName
     * @param string[]|null $tableDefWhere
     * @param string[]|int  $valueWhere
     *
     * @return false|int If successes then it returns the number of rows deleted.
     * @throws Exception
     */
    public function delete(
        ?string $tableName = null,
        ?array  $tableDefWhere = null,
                $valueWhere = PdoOne::NULL
    )
    {
        return (new PdoOneQuery($this))->delete($tableName, $tableDefWhere, $valueWhere);
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
     * @param string|null       $tableName The name of the table or the whole
     *                                     query.
     * @param string[]|null     $tableDef
     * @param string[]|int|null $values
     * @param string[]|null     $tableDefWhere
     * @param string[]|int|null $valueWhere
     *
     * @return false|int
     * @throws Exception
     */
    public function update(
        ?string $tableName = null,
        ?array  $tableDef = null,
                $values = PdoOne::NULL,
        ?array  $tableDefWhere = null,
                $valueWhere = PdoOne::NULL
    )
    {
        return (new PdoOneQuery($this))->update($tableName, $tableDef, $values, $tableDefWhere, $valueWhere);
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
    public function right(string $sql): PdoOneQuery
    {
        return (new PdoOneQuery($this))->right($sql);
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
    public function left(string $sql): PdoOneQuery
    {
        return (new PdoOneQuery($this))->left($sql);
    }

    /**
     * <b>Example:</b><br>
     *      where( ['field'=>20] ) // associative array with automatic type
     *      where( ['field'=>[20]] ) // associative array with type defined
     *      where( ['field',20] ) // array automatic type
     *      where (['field',[20]] ) // array type defined
     *      where('field=20') // literal value
     *      where('field=?',[20]) // automatic type
     *      where('field',[20]) // automatic type
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
     * @param string|null  $tablePrefix
     *
     * @return PdoOneQuery
     * @see  http://php.net/manual/en/mysqli-stmt.bind-param.php for types
     * @test InstanceOf
     *       PdoOne::class,this('field1=?,field2=?',[20,'hello'])
     */
    public function where($sql, $param = PdoOne::NULL, bool $isHaving = false, ?string $tablePrefix = null): PdoOneQuery
    {
        return (new PdoOneQuery($this))->where($sql, $param, $isHaving, $tablePrefix);
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
    public function having($sql, $param = PdoOne::NULL): PdoOneQuery
    {
        return (new PdoOneQuery($this))->having($sql, $param);
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
    public function join(string $sql, string $condition = ''): PdoOneQuery
    {
        return (new PdoOneQuery($this))->join($sql, $condition);
    }

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
    public function group(string $sql): PdoOneQuery
    {
        return (new PdoOneQuery($this))->group($sql);
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
    public function order(string $sql): PdoOneQuery
    {
        return (new PdoOneQuery($this))->order($sql);
    }

    //</editor-fold>
    //<editor-fold desc="cli utils">
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
    public function limit(string $sql): PdoOneQuery
    {
        return (new PdoOneQuery($this))->limit($sql);
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
    public function distinct(string $sql = 'distinct'): PdoOneQuery
    {
        return (new PdoOneQuery($this))->distinct($sql);
    }

    /**
     * It sets a recursive array.<br>
     * <b>Example:</b>:<br>
     * <pre>
     * $this->recursive(['field1','field2']);
     * </pre>
     *
     * @param array|mixed $rec The fields to load recursively.
     *
     * @return PdoOneQuery
     */
    public function recursive($rec): PdoOneQuery
    {
        return (new PdoOneQuery($this))->recursive($rec);
    }

    /**
     * It gets the recursive array.
     *
     * @return array
     */
    public function getRecursive(): array
    {
        return (new PdoOneQuery($this))->getRecursive();
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
     * @return PdoOneQuery
     */
    public function useCache($ttl = 0, $family = ''): PdoOneQuery
    {
        return (new PdoOneQuery($this))->useCache($ttl, $family);
    }
    //</editor-fold>
    //<editor-fold desc="key value">
    protected $tableKV = '';
    protected $defaultTableKV = '';

    public function setKvDefaultTable($table): PdoOne
    {
        $this->defaultTableKV = $table;
        $this->tableKV = $table;
        return $this;
    }

    public function kv($table): PdoOne
    {
        $this->tableKV = $table;
        return $this;
    }

    protected function resetKVChain(): void
    {
        $this->tableKV = $this->defaultTableKV;
    }


    /**
     * It creates the table for Key/Value database. If the table exists, or it is unable to create, then it returns
     * false.
     * @param bool $memoryKV
     * @return bool
     */
    public function createTableKV(bool $memoryKV = false): bool
    {
        if ($this->tableKV === '') {
            throw new RuntimeException('CreateTableKV,ou must set the table so you can use it');
        }
        try {
            if (!$this->tableExist($this->tableKV)) {
                $sql = $this->service->createTableKV($this->tableKV, $memoryKV);
                $this->runRawQuery($sql);
                $sql = $this->service->createIndex($this->tableKV, ['TIMESTAMP' => 'INDEX']);
                $this->runRawQuery($sql);
                return true;
            }
        } catch (Exception $ex) {
        }
        return false;
    }

    /**
     * It drops the table key-value. If the table doesn't exist, then it could throw an exception or returns false.
     * @return bool
     * @throws Exception
     */
    public function dropTableKV(): bool
    {
        if ($this->tableKV === '') {
            throw new RuntimeException('CreateTableKV,ou must set the table so you can use it');
        }
        return $this->dropTable($this->tableKV);
    }

    /**
     * It gets a value from a key-value storage
     * @param string $key
     * @param mixed  $valueIfNotFound
     * @return mixed The value if found. the valueIfNotfound if not found, or null in case of error.
     * @throws Exception
     */
    public function getKV(string $key, $valueIfNotFound = null)
    {
        $sql = "select KEYT,VALUE,TIMESTAMP from $this->tableKV where KEYT=?";
        // ["KEYT"]=> string(5) "hello" ["VALUE"]=> string(13) "it is a value" ["TIMESTAMP"]=> string(19) "2022-02-09 19:08:20"
        try {
            $r = $this->runRawQuery($sql, [$key]);
        } catch (Exception $e) {
            if ($this->throwOnError) {
                throw $e;
            }
            $r = null;
        }
        if (!isset($r[0])) {
            return $valueIfNotFound;
        }
        $timestamp = $r[0]['TIMESTAMP'];
        if ($timestamp !== null && $timestamp < time()) {
            // expired
            $this->delKV($key);
            return null;
        }
        return $r[0]['VALUE'];
    }

    /**
     * It sets a new key in the Key-Value storage. If the key exists, then it is replaced.<br>
     *
     * @param string $key
     * @param string $value
     * @param null   $timeout
     * @return bool
     * @throws Exception
     */
    public function setKV(string $key, string $value, $timeout = null): bool
    {
        $t = time();
        $row = $this->runRawQuery("select 1 from $this->tableKV where KEYT=:KEYT"
            , ['KEYT' => $key]);
        $exist = isset($row[0]);
        if ($exist) {
            if ($timeout === null) {
                $this->set(['VALUE' => $value, 'TIMESTAMP' => null])->where(['KEYT' => $key])->update($this->tableKV);
            } else {
                $d = $t + $timeout;
                $this->set(['VALUE' => $value, 'TIMESTAMP' => $d])->where(['KEYT' => $key])->update($this->tableKV);
            }
        } else if ($timeout === null) {
            $this->insert($this->tableKV, ['KEYT' => $key, 'VALUE' => $value, 'TIMESTAMP' => null]);
        } else {
            $d = $t + $timeout;
            $this->insert($this->tableKV, ['KEYT' => $key, 'VALUE' => $value, 'TIMESTAMP' => $d]);
        }
        try {
            $r = random_int(1, 1000);
            if ($r === 10) {
                $this->garbageCollectorKV();
            }
        } catch (Exception $ex) {
            if ($this->throwOnError) {
                throw $ex;
            }
        }
        return true;
    }

    /**
     * It deletes all the expired keys. setkv() call this method with a probability of 1/1000.
     * @return bool
     * @throws Exception
     */
    public function garbageCollectorKV(): bool
    {
        try {
            $t = time();
            $this->runRawQuery("delete from $this->tableKV where TIMESTAMP is null or TIMESTAMP<:TIMESTAMP", ['TIMESTAMP' => $t]);
            return true;
        } catch (Exception $ex) {
            if ($this->throwOnError) {
                throw $ex;
            }
        }
        return false;
    }

    /**
     * @param string $key
     * @return bool
     * @throws Exception
     */
    public function delKV(string $key): bool
    {
        try {
            $this->runRawQuery("delete from $this->tableKV where KEYT=:KEYT", ['KEYT' => $key]);
            return true;
        } catch (Exception $ex) {
            if ($this->throwOnError) {
                throw $ex;
            }
        }
        return false;
    }

    /**
     * Delete all the values from a key-value storage.
     * @return bool
     * @throws Exception
     */
    public function flushKV(): bool
    {
        try {
            return $this->runRawQuery("delete from $this->tableKV where 1=1") !== false;
        } catch (Exception $ex) {
            if ($this->throwOnError) {
                throw $ex;
            }
        }
        return false;
    }

    /**
     * It returns true if the key exists, and it hasn't expired.
     *
     * @param string $key
     * @return bool|null
     * @throws Exception
     */
    public function existKV(string $key): ?bool
    {
        try {
            $t = time();
            $row = $this->runRawQuery("select 1 from $this->tableKV where KEYT=:KEYT and (TIMESTAMP is null or TIMESTAMP>:TIMESTAMP)"
                , ['KEYT' => $key, 'TIMESTAMP' => $t]);
        } catch (Exception $e) {
            if ($this->throwOnError) {
                throw $e;
            }
            return null;
        }
        return isset($row[0]);
    }


    //</editor-fold>
}
