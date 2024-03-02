<?php /** @noinspection GrazieInspection */
/** @noinspection UnknownInspectionInspection */
/** @noinspection PhpUnused */
/** @noinspection SqlDialectInspection */
/** @noinspection SqlNoDataSourceInspection */
/** @noinspection PhpConditionAlreadyCheckedInspection */

namespace eftec;

use DateTime;
use eftec\ext\PdoOne_IExt;
use eftec\ext\PdoOne_Mysql;
use eftec\ext\PdoOne_Sqlite;
use eftec\ext\PdoOne_Oci;
use eftec\ext\PdoOne_Sqlsrv;
use eftec\ext\PdoOne_TestMockup;
use Exception;
use JsonException;
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
 * @version       4.6.2
 */
class PdoOne
{
    public const VERSION = '4.6.2';
    /** @var int We need this value because null and false could be a valid value. */
    public const NULL = PHP_INT_MAX;
    /** @var string Prefix of the related columns. It is used for ORM */
    public static string $prefixBase = '_';
    /** @var string the prefix of every table, example "t_" */
    public string $prefixTable = '';
    /** @var int Used for the method page() */
    public static int $pageSize = 20;
    /** @var string|null Static date (when the date is empty) */
    public static ?string $dateEpoch = '2000-01-01 00:00:00.00000'; // we don't need to set the epoch to 1970.
    /**
     * Text date format
     *
     * @var string
     * @see https://secure.php.net/manual/en/function.date.php
     */
    public static string $dateFormat = 'Y-m-d';
    public static string $dateHumanFormat = 'd/m/Y';
    /**
     * Text datetime format
     *
     * @var string
     * @see https://secure.php.net/manual/en/function.date.php
     */
    public static string $dateTimeFormat = 'Y-m-d\TH:i:s\Z';
    public static string $dateTimeHumanFormat = 'd/m/Y H:i:s';
    //<editor-fold desc="server fields">
    /**
     * Text datetime format with microseconds
     *
     * @var string
     * @see https://secure.php.net/manual/en/function.date.php
     */
    public static string $dateTimeMicroFormat = 'Y-m-d\TH:i:s.u\Z';
    public static string $dateTimeMicroHumanFormat = 'd/m/Y H:i:s.u';
    /** @var string This format is used to determine how the database will return a date */
    public static string $isoDate = 'Y-m-d';
    public static string $isoDateTimeMs = 'Y-m-d H:i:s.u';
    public static string $isoDateTime = 'Y-m-d H:i:s';
    /** @var string This format is used to determine how the database expect a date value */
    public static string $isoDateInput = '';
    public static string $isoDateInputTimeMs = '';
    public static string $isoDateInputTime = '';
    public int $internalCacheCounter = 0;
    public array $internalCache = [];
    /** @var int nodeId It is the identifier of the node. It must be between 0..1023 */
    public int $nodeId = 1;
    public string $tableSequence = 'snowflake';
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
     * @see PdoOne::getUnpredictable
     */
    public array $masks0 = [2, 0, 4, 5];
    public array $masks1 = [16, 13, 12, 11];
    /** @var PdoOneEncryption */
    public PdoOneEncryption $encryption;
    /** @var string=['mysql','sqlsrv','test','sqlite','oci'][$i] */
    public string $databaseType;
    /** @var string It is generated and set automatically by the type of database */
    public string $database_delimiter0 = '`';
    /** @var string It is generated and set automatically by the type of database */
    public string $database_delimiter1 = '`';
    /** @var string It is generated and set automatically by the type of database */
    public string $database_identityName = 'identity';
    /** @var string server ip. Ex. 127.0.0.1 127.0.0.1:3306 */
    public string $server='';
    public ?string $user=null;
    /** @var null|string the unique id generate by sha256or $hashtype and based in the query, arguments, type
     * and methods
     */
    public ?string $uid=null;
    public array $lastBindParam = [];
    public ?string $pwd=null;
    /** @var string The name of the database/schema */
    public string $db;
    /** @var string the name of the locker */
    public string $lockerId;
    public string $charset = 'utf8';
    /** @var bool It is true if the database is connected otherwise,it's false */
    public bool $isOpen = false;
    /**
     * @var bool If true (default), then it throws an error if happens an error. If false, then the execution continues
     */
    public bool $throwOnError = true;
    /**
     * @var bool If true (default), then it throws a customer message.. If false, then it uses the default (PHP) style
     */
    public bool $customError = true;
    /** @var string[] PHP classes excluded by the custom error log */
    public array $traceBlackList = []; //['PdoOne.php', 'PdoOneQuery.php', 'PdoOne_Mysql.php', 'PdoOne.Sqlsrv.php', 'PdoOne.Oci.php'
    //, 'PdoOneTestMockup.php', '_BasePdoOneRepo.php'];
    /** @var  PDO|null */
    public ?PDO $conn1=null;
    /** @var  bool|null True if the transaction is open */
    public ?bool $transactionOpen=null;
    /** @var bool if the database is in READ ONLY mode or not. If true then we must avoid to write in the database. */
    public bool $readonly = false;
    /** @var boolean if true then it logs the file using the php log file (if enabled) */
    private bool $logFile = false;
    /** @var string It stores the last error. runGet and beginTry resets it */
    public string $errorText = '';
    public bool $isThrow = false;
    /** @var int=[0,1,2,3,4][$i]<br>
     * <b>0</b>=no debug for production (all messages of errors are generic). Log only errors<br>
     * <b>1</b>=it shows an error message. Log only errors<br>
     * <b>2</b>=it shows an error message. Log only errors and warnings<br>
     * <b>3</b>=it shows the error messages and the last query. Log everthing<br>
     * <b>4</b>=it shows the error messages, the last query, the trace and the last parameters (if any). Log on error
     * and info Note: it could show passwords and confidential information<br>
     */
    public int $logLevel = 0;
    /** @var string|null last query executed */
    public ?string $lastQuery=null;
    public ?array $lastParam = [];
    /** @var array the tables used in the queries and added by the methods from() and join() */
    public array $tables = [];
    public bool $useInternalCache = false;
    /**
     * @var array
     * @see PdoOne::generateCodeClassConversions
     * @see PdoOne::generateAbstractRepo
     */
    public array $codeClassConversion = [];
    //</editor-fold>
    public bool $genError = true;
    /** @var int */
    public int $affected_rows = 0;
    /** @var PdoOne_IExt */
    public PdoOne_IExt $service;
    /** @var mixed The service of cache [optional] */
    public $cacheService;
    /** @var null|array it stores the values obtained by $this->tableDependency() */
    public ?array $tableDependencyArrayCol=null;
    public ?array $tableDependencyArray=null;
    /** @var null|array $partition is an associative array [column=>value] with a fixed and pre-established conditions */
    public ?array $partition=null;
    /** @var MessageContainer|null it stores the messages. */
    private ?MessageContainer $messageContainer;
    /** @var PdoOne|null */
    protected static ?PdoOne $instance=null;
    protected string $tableKV = '';
    protected string $defaultTableKV = '';

    /**
     * PdoOne constructor.  It doesn't open the connection to the database.
     *
     * @param string      $databaseType =['mysql','sqlsrv','oci','sqlite','test'][$i]
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
     * @see          PdoOne::connect()
     * @noinspection ClassConstantCanBeUsedInspection
     */
    public function __construct(
        string  $databaseType,
        string  $server,
        string  $user = '',
        string  $pwd = '',
        string  $db = '',
        bool    $logFile = false,
        ?string $charset = null,
        int     $nodeId = 1,
        string  $tableKV = ''
    )
    {
        $this->construct($databaseType, $server, $user, $pwd, $db, $logFile, $charset, $nodeId, $tableKV);
        if (class_exists('eftec\MessageContainer')) {
            // autowire MessageContainer if the method exists.
            $this->messageContainer = MessageContainer::instance();
        }
        if (self::$instance === null) {
            self::$instance = $this;
        }
    }

    /**
     * It generates an instance using an array.<br>
     * @param array $array =['databaseType','server','user','pwd','database','logFile','charset','nodeId','tableKV'][$i]<br/>
     *                     it could be an associative array or an indexed array
     * @return PdoOne
     */
    public static function factoryFromArray(array $array): PdoOne
    {
        if (isset($array['databaseType'])) {
            return new self($array['databaseType'] ?? '',
                $array['server'] ?? '',
                $array['user'] ?? '',
                $array['pwd'] ?? '',
                $array['database'] ?? '',
                $array['logFile'] ?? false,
                $array['charset'] ?? null,
                $array['nodeId'] ?? 1,
                $array['tableKV'] ?? ""
            );
        }
        return new self($array[0] ?? '',
            $array[1] ?? '',
            $array[2] ?? '',
            $array[3] ?? '',
            $array[4] ?? '',
            $array[5] ?? false,
            $array[6] ?? null,
            $array[7] ?? 1,
            $array[8] ?? ""
        );
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
        $nodeId = 1,
        $tableKV = ''
    ): void
    {
        $this->databaseType = $databaseType;
        switch ($this->databaseType) {
            case 'mysql':
                $this->service = new PdoOne_Mysql($this);
                break;
            case 'sqlite':
                $this->service = new PdoOne_Sqlite($this);
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
        $this->setKvDefaultTable($tableKV);
    }

    /**
     * It sets if the library will use the log file or not.
     * @param bool $useLog
     * @return void
     */
    public function setUseLog(bool $useLog = true): void
    {
        $this->logFile = $useLog;
    }

    public static function newColFK($key, $refcol, $reftable, $extra = null, $name = null): array
    {
        return ['key' => $key, 'refcol' => $refcol, 'reftable' => $reftable, 'extra' => $extra, 'name' => $name];
    }

    /**
     * We clean a sql that it could contain columns<br>
     * **Example:**
     * ```php
     * PdoOne::cleanColumns("col1,col2"); // col1,col2
     * PdoOne::cleanColumns("col1';,col2"); // col1;,col2
     * ```
     * @param string $sql
     * @return array|string|string[]
     */
    public static function cleanColumns(string $sql)
    {
        return str_replace([chr(0), chr(8), chr(9), chr(13), "'", '"', chr(26), chr(92)], '', $sql);
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
     * It converts a date (as string) into another format or false if it fails.<br>
     * Example:
     * ```php
     * $pdoOne->dateConvert('01/01/2019','human','sql'); // 2019-01-01
     * ```
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
        $ms = false; // if true then the value has microseconds
        $time = false; //  if true then the value has time
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
     * **Example:**
     * ```php
     * $r=PdoOne::dateConvertInput('01/12/2020','human',$ms,$time); // it depends on the fields self::$date*HumanFormat
     * $r=PdoOne::dateConvertInput('2020-12-01','iso',$ms,$time); // it depends on the fields self::$date*Format
     * $r=PdoOne::dateConvertInput('2020-12-01','sql',$ms,$time); // it depends on the database
     * $r=PdoOne::dateConvertInput(50000,'timestamp',$ms,$time); // a timestamp
     * $r=PdoOne::dateConvertInput(new DateTime(),'class',$ms,$time); // a DateTime object (it keeps the same one)
     * ```
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
                    if ($tmpDate === false) {
                        // unable to convert
                        return false;
                    }
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

    /**
     * It converts serpent case into proper case, and it singularizes the table.
     * @param string|null $txt
     * @return false|mixed|string|null
     */
    public static function tableCase(?string $txt)
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
     * **Example:**
     * ```php
     * self::singularTable('categories'); // category
     * self::singularTable('churches'); // church
     * self::singularTable('prices'); // pric (it fail with this kind of cases)
     * self::singularTable('users'); // user
     * ```
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
                $error[$k] = "$k " . json_encode($dc, JSON_THROW_ON_ERROR) . " deleted";
            }
        }
        foreach ($defArray as $k => $dc) {
            if (!isset($defCurrent[$k])) {
                $error[$k] = "$k " . json_encode($dc, JSON_THROW_ON_ERROR) . " added";
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
                $error[] = "fk: " . json_encode($dc, JSON_THROW_ON_ERROR) . " deleted";
            }
        }
        foreach ($defFK as $k => $dc) {
            if (!isset($defCurrentFK[$k])) {
                $error[] = "fk: " . json_encode($dc, JSON_THROW_ON_ERROR) . " added";
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
     * It gets the definition of a table as an associative array<br>
     * <ul>
     * <li><b>phptype</b>: The PHP type of the column, for example int</li>
     * <li><b>conversion</b>: If the column requires a special conversion</li>
     * <li><b>type</b>: The SQL type of the column, for example int,varchar</li>
     * <li><b>size</b>: The size of the column, it could be two values for example "20,30"</li>
     * <li><b>null</b>: (boolean) if the column allows null</li>
     * <li><b>identity</b>: (boolean) if the column is identity</li>
     * <li><b>sql</b>: the sql syntax of the column</li>
     * </ul>
     * **Example:**
     * ```php
     * $this->getDefTable('tablename',$conversion);
     * // ['col1'=>['alias'=>'','phptype'=>'int','conversion'=>null,'type'=>'int','size'=>null
     * // ,'null'=>false,'identity'=>true,'sql'='int not null auto_increment'
     * ```
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
     * **Example:**
     * ```php
     * $this->dbTypeToPHP('varchar'); // ['string',PDO::PARAM_STR]
     * $this->dbTypeToPHP('int'); // ['int',PDO::PARAM_INT]
     * ```
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
     * **Example:**
     * ```php
     * $this->getDefTableKeys('table1');
     * // ["IndexName"=>'',"ColumnName"=>'',"is_unique"=>0,"is_primary_key"=>0,"TYPE"=>'']
     * ```
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
     * **Example:**
     * ```php
     * $this->getDefTableExtended('table'); // ['name','engine','schema','collation','description']
     * $this->getDefTableExtended('table',true); // "some description of the table"
     *
     * ```<br>
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
     * @noinspection PhpUnusedParameterInspection
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
        //$this->_logLevel = 3;
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
                    return json_encode(['error' => $this->lastError()], JSON_THROW_ON_ERROR);
                }
                if (!is_array($result)) {
                    return "No result or result error\n";
                }
                return json_encode($result, JSON_THROW_ON_ERROR);
            case 'selectcode':
                return $this->generateCodeSelect($query);
            case 'arraycode':
                return $this->generateCodeArray($input, $query, false, false);
            default:
                return "Error:Output $output not defined.";
        }
    }

    /**
     * Connects to the database.
     *
     * @param bool      $failIfConnected true=it throws an error if it's connected,
     *                                   otherwise it does nothing
     * @param bool|null $alterSession
     * @test exception this(false)
     * @throws JsonException
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
     * @throws JsonException
     * @see PdoOne
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
            if ($this->logLevel >= 1) {
                $txt .= "\n{{Message:}} " . is_array($txtExtra) ? json_encode($txtExtra, JSON_THROW_ON_ERROR) : $txtExtra;
                if ($exception !== null) {
                    $txt .= "\n{{Message:}} " . $this->lastError() . ' ' . $exception->getMessage();
                } else {
                    $txt .= "\n{{Message:}} " . $this->lastError();
                }
            }
            if ($this->logLevel >= 3) {
                $txt .= "\n{{Last query:}} [$this->lastQuery]";
            }
            if ($this->logLevel >= 4) {
                $txt .= "\n{{Database:}} " . $this->server . ' - ' . $this->db;
                if (is_array($extraParam)) {
                    foreach ($extraParam as $k => $v) {
                        if (is_array($v) || is_object($v)) {
                            $v = json_encode($v, JSON_THROW_ON_ERROR);
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
            if ($this->messageContainer !== null) {
                if ($this->logFile) {
                    $this->messageContainer->backupLog();
                    if ($this->logLevel >= 2) {
                        $this->messageContainer->setLog(true, true, true, true);
                    } else {
                        $this->messageContainer->setLog(true, true);
                    }
                }
                $this->messageContainer->addItem($this->lockerId, $txt);
                $this->messageContainer->restoreLog();
            }
            $this->errorText = $txt;
        }
        if ($throwError && $this->throwOnError && $this->genError) {
            // endtry() invalidates this call (it is never called)
            throw new RuntimeException($txt);
        }
        $this->endTry();
    }

    public function clearError(): PdoOne
    {
        $this->errorText = '';
        if ($this->messageContainer !== null) {
            $this->messageContainer->resetLocker($this->lockerId);
        }
        return $this;
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
     * @throws JsonException
     */
    public function custom_exception_handler($exception, ?string $customMessage = null, bool $returnAsString = false): string
    {
        $isCli = !http_response_code();
        $customMessage = $customMessage ?? $exception->getMessage();
        $r = "Uncaught Exception: [" . get_class($exception) . "] code:" . $exception->getCode() . "\n"
            . $customMessage . "\n";
        if ($this->logLevel > 3) {
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
                                $args[] = json_encode($v, JSON_THROW_ON_ERROR);
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
                    $r .= '<<<' . $file . ':' . @$error['line'] . ">>>\t" . $function . '('
                        . @implode(' , ', $args) . ')' . "\n";
                }
            }
        }
        if (!$isCli) {
            $r = str_replace(["\n", '[', ']', '<<<', '>>>', '{{', '}}', "\t"]
                , ["<br>", "<b>[", "]</b>", '<span style="background-color:blue; color:white">', '</span>', '<u>', '</u>', '&nbsp;&nbsp;&nbsp;&nbsp;']
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
    public function getMessagesContainer(): ?MessageContainer
    {
        return $this->messageContainer;
    }

    public function getMessages($level = null): ?array
    {
        if ($this->messageContainer !== null) {
            return $this->messageContainer->getLocker($this->lockerId)->all($level);
        }
        return null;
    }

    public function getErrors(): ?array
    {
        if ($this->messageContainer !== null) {
            return $this->messageContainer->getLocker($this->lockerId)->allError();
        }
        return null;
    }

    public function getFirstError(): ?string
    {
        if ($this->messageContainer !== null) {
            return $this->messageContainer->getLocker($this->lockerId)->firstError();
        }
        return null;
    }

    public function getLastError(): ?string
    {
        if ($this->messageContainer !== null) {
            return $this->messageContainer->getLocker($this->lockerId)->lastError();
        }
        return null;
    }

    public function hasError($includeWarning = false): ?string
    {
        if ($this->messageContainer !== null) {
            return $this->messageContainer->getLocker($this->lockerId)->hasError($includeWarning);
        }
        return null;
    }

    public function getInfos(): ?array
    {
        if ($this->messageContainer !== null) {
            return $this->messageContainer->getLocker($this->lockerId)->allInfo();
        }
        return null;
    }

    public function getFirstInfo(): ?string
    {
        if ($this->messageContainer !== null) {
            return $this->messageContainer->getLocker($this->lockerId)->firstInfo();
        }
        return null;
    }

    public function getLastInfo(): ?string
    {
        if ($this->messageContainer !== null) {
            return $this->messageContainer->getLocker($this->lockerId)->lastInfo();
        }
        return null;
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
        if ($this->logLevel <= 2) {
            return;
        }
        if ($this->messageContainer !== null) {
            $this->messageContainer->backupLog(); // we don't want to alter the current configuration
            if ($this->logFile) {
                if ($this->logLevel > 2) {
                    $this->messageContainer->setLog(true, true, true, true);
                } else {
                    $this->messageContainer->setLog(true, true);
                }
            }
            $this->messageContainer->addItem($this->lockerId, $txt, 'info');
            $this->messageContainer->restoreLog();
        }
    }

    /**
     * It adds a delimiter to a text based in the type of database (` for mysql
     * and [] for sql server)
     * **Example:**
     * ```php
     * $pdoOne->addDelimiter('hello world'); // `hello` world<br>
     * $pdoOne->addDelimiter('hello.world'); // `hello`.`world`<br>
     * $pdoOne->addDelimiter('hello=value'); // `hello`=value<br>
     * ```
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
     * **Example:**
     * ```php
     * $values=$con->runRawQuery('select * from table where id=?',[20],true); // with parameter
     * $values=$con->runRawQuery('select * from table where id=:name',['name'=>20],true); // with named parameter
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
                $this->storeInfo("OK (USING CACHE)");
                return $this->internalCache[$uid];
            }
        }
        $this->lastParam = $params;
        $this->lastQuery = $rawSql;
        $this->storeInfo("[INFO]\t$rawSql");
        if ($params === null) {
            $rows = $this->runRawQueryParamLess($rawSql, $returnArray);
            if ($uid !== false && $returnArray) {
                $this->internalCache[$uid] = $rows;
            }
            $this->endTry();
            $this->storeInfo("OK QUERY");
            return $rows;
        }
        // the "where" has parameters.
        $stmt = $this->prepare($rawSql);
        if ($stmt === false) {
            $this->throwError("Unable to prepare statement", $rawSql);
            $this->storeInfo("ERROR QUERY");
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
                    $stmt->bindParam($i + 1, $params[$i], $this->getType($params[$i]));
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
                $this->storeInfo("OK CACHED");
                return $result;
            }
        } else {
            $this->uid = null;
        }
        $resultQuery = $this->runQuery($stmt);
        if ($resultQuery === false) {
            $this->endTry();
            $this->storeInfo("error");
            return false;
        }
        if ($returnArray && $stmt instanceof PDOStatement) {
            $rows = ($stmt->columnCount() > 0) ? $stmt->fetchAll(PDO::FETCH_ASSOC) : [];
            $this->affected_rows = $stmt->rowCount();
            $stmt = null;
            if ($uid !== false) {
                $this->internalCache[$uid] = $rows;
            }
            $this->endTry();
            $this->storeInfo("OK");
            return $rows;
        }
        if ($stmt instanceof PDOStatement) {
            $this->affected_rows = $stmt->rowCount();
        } else {
            $this->affected_rows = 0;
        }
        $this->endTry();
        $this->storeInfo("OK");
        return $stmt;
    }

    /**
     * It returns the sql command (in lower case) or the type (family) of sql command of a query<br>
     * Example:<br>
     * ```php
     * $this->queryCommand("select * from table") // returns "select"
     * $this->queryCommand("select * from table",true) // returns "dql"
     * ```
     *
     * @param string $sql
     * @param false  $returnType if true then it returns DML (insert/updat/delete/etc.) or DQL (select/show/display)
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
     * @see PdoOne::runRawQuery
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
        try {
            $stmt = $this->conn1->prepare($sql);
        } catch (Exception $ex) {
            $stmt = false;
            $this->storeInfo("[INFO] [ERROR1] Statement:\t$sql ");
            $this->throwError('Failed to prepare', $ex->getMessage() . $this->lastError(), ['param' => $this->lastParam], true, $ex);
        }
        if (($stmt === false) && $this->errorText === '') {
            $this->storeInfo("[INFO] [ERROR2] Statement:\t$sql ");
            $this->throwError('Unable to prepare query', $this->lastQuery, ['param' => $this->lastParam]);
        }
        $this->endTry();
        if ($stmt !== false) {
            $this->storeInfo("[INFO] [OK] Statement:\t$sql ");
        }
        return $stmt;
    }

    /**
     * It returns true if the array is an associative array.  False
     * otherwise.<br>
     * **Example:**
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
     * **Example:**
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
     * @test equals true,$this->pdoOne->runQuery($this->pdoOne->prepare('select 1 from dual'))
     * @test equals
     *     [1=>1],$this->pdoOne->select('1')->from('dual')->first(),'it must run'
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
            $this->throwError($this->databaseType . ':Failed to run query ', $this->lastQuery,
                ['param' => $this->lastParam, 'error_last' => json_encode(error_get_last(), JSON_THROW_ON_ERROR)], $throwError, $ex);
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
        $query = str_replace(["\r\n", "\n", "\t", '   ', '  '], ' ',
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
                    if ($after[$table][$name] ?? false) {
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
     * This function is used to generate a list of recursive fields.
     * @param array  $getDefTable
     * @param array  $classRelations
     * @param array  $relation
     * @param string $type
     * @return array
     */
    protected function generateCodeArrayRecursive(array  $getDefTable,
                                                  array  $classRelations,
                                                  array  $relation,
                                                  string $type): array
    {
        $values = $this->generateCodeArrayConst($getDefTable, $classRelations, $relation, $type);
        if ($values === null) {
            return [];
        }
        $result = [];
        foreach ($values as $k => $v) {
            if (strpos($k, '_') === 0) {
                $result[] = '/' . $k;
            }
        }
        return $result;
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
     * **Example:**
     * ```php
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
     * ```
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
     * It gets the current date and time from the database.
     * @return string|null The value is returned in SQL format.
     */
    public function now(): string
    {
        return (new PdoOneQuery($this))->now();
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
     * It returns an associative array with the relations between the tables.
     * @param string     $tableName        The name of the table (no alias)
     * @param array|null $columnRelations
     * @param mixed      $pkFirst          the first primary key
     * @param array      $aliasesAllTables an associative array with all the aliases of the columns of all tables<br>
     *                                     ['table2'=>['col'=>'alias'..],'table20>['col'=>'alias'],..]
     * @return array|string
     * @throws Exception
     */
    public function generateGetRelations(string $tableName, ?array $columnRelations, $pkFirst, array $aliasesAllTables): array
    {
        try {
            $deps = $this->tableDependency(true);
        } catch (Exception $e) {
            $this->endTry();
            return 'Error: Unable read table dependencies ' . $e->getMessage();
        } //  ["city"]=> {["city_id"]=> "address"}
        $after = $deps[1][$tableName] ?? null;
        if ($after === null) {
            $after = $deps[1][strtolower($tableName)] ?? null;
        }
        $before = $deps[2][$tableName] ?? null;
        if ($before === null) {
            $before = $deps[2][strtolower($tableName)] ?? null;
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
                                return ['Error: Unable read table dependencies ' . $e->getMessage(), null];
                            }
                            try {
                                $keys2 = $this->service->getDefTableKeys($defsFK[$refcol2]['reftable'], true,
                                    'PRIMARY KEY');
                            } catch (Exception $e) {
                                $this->endTry();
                                return ['Error: Unable read table dependencies' . $e->getMessage(), null];
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
            $ksimple = ltrim($k, self::$prefixBase); // remove the _ from the beginner
            $alias = ($aliases[$k] ?? $k);
            $aliasCol = self::$prefixBase . ($aliases[$ksimple] ?? $ksimple);
            $col = ltrim($aliasCol, self::$prefixBase);
            $refcol = ltrim($v['refcol'], self::$prefixBase);
            $refcol2 = isset($v['refcol2']) ? ltrim($v['refcol2'], self::$prefixBase) : null;
            $col2 = $v['col2'] ?? null;
            $aliasRef = self::$prefixBase . @$aliasesAllTables[$v['reftable']][$refcol] ?? $refcol;
            $relation[$k]['alias'] = $alias;
            if (isset($v['col'])) {
                $relation[$k]['colalias'] = $aliases[$v['col']] ?? $v['col'];
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
        return [$relation, $linked];
    }

    /**
     * @param string      $tableName
     * @param string|null $pkFirst the first primary key (if any)
     * @return array=['key','refcol','reftable','extra','name'][$i] where the key of the array is the name of the column
     */
    public function getRelations(string $tableName, ?string $pkFirst): array
    {
        try {
            $relation = $this->getDefTableFK($this->prefixTable . $tableName, false, true);
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
        $after = $deps[1][$tableName] ?? null;
        if ($after === null) {
            $after = $deps[1][strtolower($tableName)] ?? null;
        }
        $before = $deps[2][$tableName] ?? null;
        if ($before === null) {
            $before = $deps[2][strtolower($tableName)] ?? null;
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
     * ```php
     * $this->addQuote("aaa"); // [aaa] (sqlserver) `aaa` (mysql)
     * $this->addQuote("[aaa]"); // [aaa] (sqlserver, unchanged)
     * ```
     *
     * @param string $txt
     *
     * @return string
     * @see PdoOne::addDelimiter to considers points
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
     * **Example:**
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
     * @param bool   $throw [optional] if true, and it fails then it throws an error.
     * @param string $cause
     * @return bool
     * @test equals false,(false),'transaction is not open'
     * @throws JsonException
     */
    public function rollback(bool $throw = true, string $cause = ''): bool
    {
        $this->beginTry();
        if (!$this->transactionOpen && $throw) {
            $this->throwError("Transaction not open to rollback($cause)", '');
        }
        if (!$this->isOpen && $throw) {
            $this->throwError("It's not connected to the database", '');
            return false;
        }
        $this->transactionOpen = false;
        try {
            $r = @$this->conn1->rollback();
        } catch (Exception $ex) {
            $r = false;
        }
        $this->endTry();
        return $r;
    }

    /**
     * It sets conversions depending on the type of data. This method is used together with generateCodeClassAll().
     * <b>This value persists across calls</b><br>
     * For example, if we always want to convert <b>tinyint</b> into <b>boolean</b>, then we could use this function
     * , instead of specify per each column.<br>
     * **Example:**
     * ```php
     * $this->parent->generateCodeClassConversions(
     *      ['datetime'=>'datetime2'
     *      ,'tinyint'=>'bool' // converts tinyint as boolean
     *      ,'int'=['int',null] // converts input int as integer, and doesn't convert output int
     *      ]);
     * echo $this->parent->generateCodeClassAll('table');
     * $this->parent->generateCodeClassConversions(); // reset.
     * ```
     * <b>PHP Conversions</b>:
     * <ul>
     * <li>encrypt (encrypt value. Encryption must be set)</li>
     * <li>decrypt (decrypt a value if the value can be decrypted). Encryption must be set</li>
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
     * @see  PdoOne::generateAbstractRepo
     * @see  PdoOne::setEncryption
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

    /**
     * It saves a file with some code or content
     * @param string      $filename The name of the filename
     * @param string|null $content  The content
     * @return false|int
     */
    public static function saveFile(string $filename, ?string $content)
    {
        try {
            $content = self::mixFiles($filename, $content);
            $f = @file_put_contents($filename, $content);
        } catch (Exception $e) {
            return false;
        }
        return $f;
    }

    /**
     * Mix a filename with a new content while keeping part of the old code<br>
     * It keeps the lines of the filename that are in betweeen "// [EDIT:type]" and "// [/EDIT]"<br>
     * The new content has priority, i.e. if the old content has more [EDIT] blocks than the new content,
     * then the old blocks are discarded.<br>
     * If the old content few more [EDIT] blocks than the new content, then it keeps the block in new content<br>
     * <b>Example</b>
     * newcontent:<br>
     * ```php
     * init
     * // [EDIT:c1]
     * ccc
     * // [/EDIT]
     * end
     * ```
     * filename content:<br>
     * ```php
     * init modified
     * // [EDIT:c1]
     * modified
     * // [/EDIT]
     * end modified
     * ```
     * result:<br>
     * ```php
     * init
     * // [EDIT:c1]
     * modified
     * // [/EDIT]
     * end
     * ```
     *
     * @param string      $filename   The full filename of the old archive. If the archive doesn't exist,
     *                                then it keeps the new content
     * @param string|null $newContent The new content to mix with the content of the filename.
     * @return string
     */
    public static function mixFiles(string $filename, ?string $newContent): string
    {
        if ($newContent === '' || $newContent === null) {
            return '';
        }
        $newContent = str_replace("\r\n", "\n", $newContent);
        if (@!file_exists($filename)) {
            // nothing changed
            return $newContent;
        }
        $oldContent = @file_get_contents($filename);
        if ($oldContent === false) {
            throw new RuntimeException("Unable to open $filename");
        }
        $oldContent = str_replace("\r\n", "\n", $oldContent);
        $lines = explode("\n", $oldContent);
        $oldValues = [];
        $name = '';
        foreach ($lines as $line) {
            if (strpos($line, "// [EDIT:") !== false) {
                $p0 = strpos($line, "// [EDIT:") + strlen("// [EDIT:");
                $p1 = strpos($line, ']', $p0);
                $name = substr($line, $p0, $p1 - $p0);
                $oldValues[$name] = '';
                continue;
            }
            if (strpos($line, "// [/EDIT]") !== false) {
                $name = '';
                continue;
            }
            if ($name !== '') {
                $oldValues[$name] .= $line . "\n";
            }
        }
        $name = '';
        $newValues = [];
        $lines = explode("\n", $newContent);
        $isReplacing = false;
        foreach ($lines as $line) {
            if (strpos($line, "// [EDIT:") !== false) {
                $p0 = strpos($line, "// [EDIT:") + strlen("// [EDIT:");
                $p1 = strpos($line, ']', $p0);
                $name = substr($line, $p0, $p1 - $p0);
                if (isset($oldValues[$name])) {
                    // content exists in the previous file, so we replace it
                    $newValues[] = $line;
                    $newValues[] = rtrim($oldValues[$name], "\n");
                    // and we mark as no continue to add lines until the end of the edit
                    $isReplacing = true;
                } else {
                    // content doesn't exist in previous file
                    $name = '';
                    $isReplacing = false;
                }
            }
            if (strpos($line, "// [/EDIT]") !== false) {
                // end of the edit
                $name = '';
                $isReplacing = false;
            }
            if ($name === '') {
                // normal line, add to newValues
                $newValues[] = $line;
            } else if (!$isReplacing) {
                // maybe it is never called.
                $newValues[] = $line;
            }
        }
        return implode("\n", $newValues);
    }


//</editor-fold>
//<editor-fold desc="Log functions" defaultstate="collapsed" >
//</editor-fold>
//<editor-fold desc="cli functions" defaultstate="collapsed" >
    /**
     * Flush and disable the internal cache. By default, the internal cache is not used unless it is set.
     *
     * @param bool $useInternalCache if true then it enables the internal cache.
     *
     * @see PdoOne::setUseInternalCache
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
     * @see PdoOneEncryption
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
     * It changes the hash type.
     *
     * @param string $hashType =hash_algos()[$i]
     * @return void
     * @see https://www.php.net/manual/en/function.hash-algos.php
     */
    public function setHashType(string $hashType): void
    {
        $this->encryption->setHashType($hashType);
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
     * @see PdoOneEncryption::encrypt
     */
    public function encrypt($data)
    {
        return $this->encryption->encrypt($data);
    }

    /**
     * It generates a hash based in the hash type ($this->hashType), the data used and the SALT.
     * @param mixed $data It could be any type of serializable data.
     * @return string If the serialization is not set, then it returns the same value.
     */
    public function hash($data): string
    {
        return $this->encryption->hash($data);
    }

    /**
     * Wrapper of PdoOneEncryption->decrypt. It decrypts an information if the algoritm allows to decrypt.<br>
     * The method of encryptation, SALT and PASSWORD must be the same.
     *
     * @param mixed $data The data to decrypt.
     * @return bool|string|int
     * @see PdoOneEncryption::decrypt
     * @see https://www.php.net/manual/en/function.openssl-get-cipher-methods.php
     */
    public function decrypt($data)
    {
        return $this->encryption->decrypt($data);
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
        $r = $this->drop($this->prefixTable . $tableName, 'table', $extra);
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
        $r = $this->service->truncate($this->prefixTable . $tableName, $extra, $forced);
        $this->endTry();
        return $r;
    }

    /**
     * It calls a store procedure.<br>
     * **Example:**
     * ```php
     * $this->callProcedure('procexample',['in_name'=>'aa','in_description'=>'bbb'],['in_description])
     * ```<br>
     * <b>Note:<b>sqlsrv could return an associative array.
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
        $r = $this->service->resetIdentity($this->prefixTable . $tableName, $newValue);
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
     * **Example:**
     * ```php
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
     * ```
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
     * **Example:**
     * ```php
     * // no universal (false indicates native sql)
     * createTable('products',['id'=>'int not null','name'=>'varchar(50) null'],'id','','',false);
     * // universal (true indicates universal)
     * createTable('products',['id int','name string(50) null'],'id','','',true);
     * ```
     *
     * @param string            $tableName        The name of the new table. This method will fail if the table exists.
     * @param array             $definition       An associative array with the definition of the columns.<br>
     *                                            **Example:**
     * @param string|null|array $primaryKey       The column's name that is primary key.<br>
     *                                            If the value is an associative array then it generates all keys.<br>
     *                                            The primary key could be indicated in the definition (Sqlite)
     * @param string|null       $extra            An extra operation inside the definition of the table.
     * @param string|null       $extraOutside     An extra operation outside the definition of the table.<br>
     *                                            It replaces the default values outside the table
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
        $definition = array_filter($definition); // we delete null values
        if ($universal) {
            $definition = $this->convertUniversal($definition);
        }
        $this->endTry();
        $sql = $this->service->createTable($this->prefixTable . $tableName, $definition, $primaryKey, $extra ?? '', $extraOutside ?? '');
        $r = $this->runMultipleRawQuery($sql);
        $this->endTry();
        return $r;
    }

    /**
     * It adds a columns to a table<br>
     * **Example:**
     * ```php
     * $this->addColumn("customer",['id'=>'int']);
     * $this->addColumn("customer",['id'=>'int not null default 0']); // mysql/sql/oracle
     * ```
     * @param string $tableName  The name of the new table.
     * @param array  $definition The definition of the columns<br>
     *                           the key is the name of the column.<br>
     *                           and the value is the definition of the column
     * @return bool
     * @throws Exception
     */
    public function addColumn(string $tableName, array $definition): bool
    {
        $this->endTry();
        $sql = $this->service->addColumn($tableName, $definition);
        $r = $this->runMultipleRawQuery($sql);
        $this->endTry();
        return $r;
    }

    /**
     * It deletes a column/s to a table<br>
     * **Example:**
     * ```php
     * $this->deleteColumn("customer",'col1');
     * $this->deleteColumn("customer",['col1','col2']);
     * ```
     * @param string       $tableName  The name of the new table.
     * @param array|string $definition The definition of the columns<br>
     *                                 the key is the name of the column.<br>
     *                                 and the value is the definition of the column
     * @return bool
     * @throws Exception
     */
    public function deleteColumn(string $tableName, $definition): bool
    {
        $this->endTry();
        $sql = $this->service->deleteColumn($tableName, $definition);
        $r = $this->runMultipleRawQuery($sql);
        $this->endTry();
        return $r;
    }

    /**
     * it converts a natural definition of table to a specific definition of table.<br>
     * This definition is simplified ("column type null extra") and it doesn't contain all the definitions<br>
     * **Example:**
     * ```php
     * "name string(20) null" -> "name varchar(20) null"
     * "creationDate datetime null" -> "creationDate date null"
     * "id int" -> "id int not null"
     * "id int  extra" -> "id int not null extra" (check the double space for null)
     * ```
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
     * **Example:**
     * ```php
     * $this->runMultipleRawQuery("insert into() values(1); insert into() values(2)");
     * $this->runMultipleRawQuery(["insert into() values(1)","insert into() values(2)"]);
     * ```
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
                //$this->storeInfo($rawSql);
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
     * **Example:**
     * ```php
     * $this->createFK('table',['col'=>"FOREIGN KEY REFERENCES`tableref`(`colref`)"]); // mysql
     * $this->createFK('table',['col'=>"FOREIGN KEY REFERENCES[tableref]([colref])"]); // sqlsrv
     * $this->createFK('table',['col'=>"FOREIGN KEY REFERENCES TABLE1(COL1)"]); // oci
     * ```
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
        $sql = $this->service->createFK($this->prefixTable . $tableName, $definitions);
        $r = $this->runMultipleRawQuery($sql);
        $this->endTry();
        return $r;
    }

    /**
     * It returns true if the array is of the type [[...]], otherwise false
     * @param array $items
     * @return bool
     */
    protected function isArrayItems(array $items): bool
    {
        return(isset($items[0]) && is_array($items[0]));
    }

    /**
     * It creates indexes. It doesn't replace previous indexes. The definition could depend on the type of database<br>
     * **Example:**
     * ```php
     * $this->createIndex('table',['col1'=>'INDEX','col2=>'UNIQUE INDEX']);
     * ```
     *
     * @param string $tableName   the name of the table.
     * @param array  $definitions An associative array
     * @return bool true if the operation was successful, otherwise false.
     * @throws Exception
     */
    public function createIndex(string $tableName, array $definitions): bool
    {
        $this->beginTry();
        $sql = $this->service->createIndex($this->prefixTable . $tableName, $definitions);
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
     * @test      exception this(false)
     * @throws JsonException
     * @see       PdoOne::connect()
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
     * @see PdoOne::getSequencePHP It's the same but it uses less
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
     * @see PdoOne::getSequence
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
     * @see PdoOne
     * @see PdoOne
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
        $r = $this->objectExist($this->prefixTable . $tableName);
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
						 from $this->prefixTable$tableName";
        $r = $this->runRawQuery($query);
        $this->endTry();
        return $r;
    }

    /**
     * Returns the columns of a table<br>
     * <ul>
     *     <li><b></b>colname: </b>name of the column</li>
     *     <li><b></b>coltype: </b>type of the column</li>
     *     <li><b></b>colsize: </b>size of the column</li>
     *     <li><b></b>colpres: </b>precision of the column</li>
     *     <li><b></b>colscale: </b>colscale of the column</li>
     *     <li><b></b>iskey: </b>1 if is primary key</li>
     *     <li><b></b>isidentity: </b>1 if the column is identity</li>
     *     <li><b></b>isnullable: </b>if the column is nullable</li>
     * </ul>
     * @param string $tableName The name of the table.
     * @return array|bool=['colname','coltype','colsize','colpres','colscale','iskey','isidentity','isnullable']
     * @throws Exception
     */
    public function columnTable(string $tableName)
    {
        $this->beginTry();
        $query = $this->service->columnTable($this->prefixTable . $tableName);
        $r = $this->runRawQuery($query);
        if ($this->databaseType === 'sqlite') {
            $tmpArr = $r;
            $r = [];
            foreach ($tmpArr as $v) {
                $row=[];
                $row['colname'] = $v['cid'];
                $row['coltype'] = $v['type'];
                $row['colsize'] = null;
                $row['colpres'] = null;
                $row['colscale'] = null;
                $row['iskey'] = $v['pk'];
                $row['isidentity'] = null;
                $row['isnullable'] = $v['notnull'];
                $r[]=$row;
            }
        }
        $this->endTry();
        return $r;
    }

    /**
     * Returns all the foreign keys (and relation) of a table
     *
     * @param string $tableName The name of the table.
     *
     * @return array|bool [[collocal,tablerem,colrem,fk_name]]
     * @throws Exception
     */
    public function foreignKeyTable(string $tableName)
    {
        $this->beginTry();
        $query = $this->service->foreignKeyTable($this->prefixTable . $tableName);
        $r = $this->runRawQuery($query);
        if ($this->databaseType === 'sqlite') {
            $tmpR = $r;
            $r = [];
            foreach ($tmpR as $v) {
                $row = [];
                $row['collocal']=$v['from'];
                $row['tablerem']=$v['table'];
                $row['colrem']=$v['to'];
                $row['fk_name']=$row['tablerem'].'_'.$row['colrem'];
                $r[]=$row;
            }
        }
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
     * **Example:**
     * ```php
     * insert('table',['col1',10,'col2','hello world']); // simple array: name1,value1,name2,value2..
     * insert('table',null,['col1'=>10,'col2'=>'hello world']); // definition is obtained from the values
     * insert('table',['col1'=>10,'col2'=>'hello world']); // definition is obtained from the values
     * insert('table',['col1','col2'],[10,'hello world']); // definition (binary) and value
     * insert('table',['col1','col2'],['col1'=>10,'col2'=>'hello world']); // definition declarative array
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
     * @see PdoOne
     */
    public function genError(bool $error = false): PdoOneQuery
    {
        return (new PdoOneQuery($this))->genError($error);
    }

    /**
     * It generates a query for "min". It is a macro of select()
     * **Example:**
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
     * **Example:**
     * ```php
     * ->from('table')->count('') // select count(*) from
     * table<br>
     * ->count('from table')->firstScalar() // select count(*) from table<br>
     * ->count('from table where condition=1')->firstScalar() // select count(*)
     * from table where condition=1<br>
     * ->count('from table','col')->firstScalar() // select count(col) from
     * table<br>
     * ```
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
     * **Example:**
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
     * **Example:**
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
     * **Example:**
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
     * **Example:**
     * ```php
     *      from('table')
     *      from('table alias')
     *      from('table alias','dbo') // from dbo.table alias
     *      from('table1,table2')
     *      from('table1 inner join table2 on table1.c=table2.c')
     * ```
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
     * Delete a row(s) if they exist.
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
     * **Example:**
     * ```php
     *      update('table',['col1',10,'col2','hello world'],['wherecol',10]);
     *      update('table',['col1','col2'],[10,'hello world'],['wherecol'],[10]);
     *      $this->from("producttype")
     *          ->set("name=?",['Captain-Crunch'])
     *          ->where('idproducttype=?',[6])
     *          ->update();
     *      update('product_category set col1=10 where idproducttype=1')
     * ```
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
     * **Example:**
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
     * **Example:**
     * ```php
     *      left('table on t1.c1=t2.c2')
     *      left('table on table.c1=t2.c2').left('table2 on
     * table1.c1=table2.c2')
     * ```
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
     * **Example:**
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
     * @test InstanceOf PdoOne::class,this('field1=?,field2=?',[20,'hello'])
     */
    public function where($sql, $param = PdoOne::NULL, bool $isHaving = false, ?string $tablePrefix = null): PdoOneQuery
    {
        return (new PdoOneQuery($this))->where($sql, $param, $isHaving, $tablePrefix);
    }

    /**
     * It adds a having to the query builder.
     * **Example:**
     *      select('*')->from('table')->group('col')->having('field=2')
     *      having( ['field'=>20] ) // associative array with automatic type
     *      having( ['field'=>[20]] ) // associative array with type defined
     *      having( ['field',20] ) // array automatic type
     *      having(['field',[20]] ) // array type defined
     *      having('field=20') // literal value
     *      having('field=?',[20]) // automatic type
     *      having('field',[20]) // automatic type (it's the same)
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
     * **Example:**
     * ```php
     *          join('tablejoin on t1.field=t2.field')<br>
     *          join('tablejoin','t1.field=t2.field')<br>
     * ```
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
     * **Example:**
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
     * **Example:**
     * ```php
     *      ->select("")->order("column")->toList();
     *      ->select("")->order("col1,col2")->toList();
     * ```
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
     * It adds a "limit" in a query. It depends on the type of database<br>
     * **Example:**
     * ```php
     *      ->select("")->limit("10,20")->toList();
     * ```
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
     * ```php
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
     * **Example:**
     * ```php
     * $this->recursive(['field1','field2']);
     * ```
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
     * ```php
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
     * ```
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
    /**
     * @return string
     */
    public function getTableKV(): string
    {
        return $this->tableKV;
    }

    /**
     * @return string
     */
    public function getDefaultTableKV(): string
    {
        return $this->defaultTableKV;
    }

    /**
     * It sets the default table and the current table of the type key-value
     * @param string $table the name of the table
     * @return $this
     */
    public function setKvDefaultTable(string $table = ''): PdoOne
    {
        $this->defaultTableKV = $table;
        $this->tableKV = $table;
        return $this;
    }

    /**
     * It sets the key-value table, but it does not set the default key-value table.<br>
     * This value could onlt be used in a single chain. Once the chain ends, it returns to the default value
     * defined by setKvDefaultTable()<br>
     * @param string $table the name of the table
     * @return $this
     */
    public function kv(string $table): PdoOne
    {
        $this->tableKV = $table;
        return $this;
    }

    /**
     * It resets the key-value chain and returns the current key-value table to the default value.
     * @return void
     */
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
     * @param string $key     the name of the key
     * @param string $value   the value to store
     * @param null   $timeout the timeout where this value will be keep.
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
     * It deletes a key stored in a key-value storage
     * @param string $key the name of the key
     * @return bool it returns true if the success.
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
     * @param string $key the name of the key
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

    /**
     * Check if the key-value table exists.
     * @return bool returns true if the table exists
     * @throws Exception
     */
    public function existKVTable(): ?bool
    {
        try {
            return $this->tableExist($this->tableKV);
        } catch (Exception $e) {
            if ($this->throwOnError) {
                throw $e;
            }
            return false;
        }
    }
//</editor-fold>
}
