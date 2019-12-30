<?php /** @noinspection DuplicatedCode */
/** @noinspection PhpDuplicateSwitchCaseBodyInspection */
/** @noinspection PhpUnused */
/** @noinspection SqlDialectInspection */
/** @noinspection SqlWithoutWhere */
/** @noinspection SqlResolve */
/** @noinspection SqlNoDataSourceInspection */

namespace eftec;

use DateTime;
use Exception;
use PDO;
use PDOStatement;
use stdClass;

/**
 * Class PdoOne
 * This class wrappes PDO but it could be used for another framework/library.
 *
 * @version       1.15 20192612
 * @package       eftec
 * @author        Jorge Castro Castillo
 * @copyright (c) Jorge Castro C. MIT License  https://github.com/EFTEC/PdoOne
 * @see           https://github.com/EFTEC/PdoOne
 */
class PdoOne
{

    const NULL = PHP_INT_MAX;
    /** @var string|null Static date (when the date is empty) */
    static $dateEpoch = "2000-01-01 00:00:00.00000";
    //<editor-fold desc="server fields">
    /**
     * Text date format
     *
     * @var string
     * @see https://secure.php.net/manual/en/function.date.php
     */
    public static $dateFormat = 'Y-m-d';
    public static $dateHumanFormat = 'd/m/Y';
    /**
     * Text datetime format
     *
     * @var string
     * @see https://secure.php.net/manual/en/function.date.php
     */
    public static $dateTimeFormat = 'Y-m-d\TH:i:s\Z';
    public static $dateTimeHumanFormat = 'd/m/Y H:i:s';
    /**
     * Text datetime format with microseconds
     *
     * @var string
     * @see https://secure.php.net/manual/en/function.date.php
     */
    public static $dateTimeMicroFormat = 'Y-m-d\TH:i:s.u\Z';
    public static $dateTimeMicroHumanFormat = 'd/m/Y H:i:s.u';
    /**
     * @var string ISO format for date
     */
    public static $isoDate = '';
    public static $isoDateTimeMs = '';
    public static $isoDateTime = '';
    /** @var int nodeId It is the identifier of the node. It must be between 0..1023 */
    var $nodeId = 1;
    var $tableSequence = 'snowflake';
    /**
     * it is used to generate an unpredictable number by flipping positions. It must be changed.
     * $mask0 and $mask1 must have the same number of elements.
     * Each value must be from 0..17 (the size of snowflake, if it is used with snowflake)
     * $masks0=[0] and masks1[3] means that 01234->31204
     * number 14,15,16,17 ($masks1) has the highest entrophy
     *
     * @var array
     * @see \eftec\PdoOne::getUnpredictable
     */
    var $masks0 = [2, 0, 4, 5];
    var $masks1 = [16, 13, 12, 11];
    /** @var PdoOneEncryption */
    var $encryption = null;
    /** @var string=['mysql','sqlsrv','oracle'][$i] */
    var $databaseType;
    var $database_delimiter0 = '`';
    //</editor-fold>
    var $database_delimiter1 = '`';
    /** @var string server ip. Ex. 127.0.0.1 */
    var $server;
    var $user;
    var $pwd;
    var $db;
    var $charset = 'utf8';
    /** @var bool It is true if the database is connected otherwise,it's false */
    var $isOpen = false;
    /** @var bool If true (default), then it throws an error if happens an error. If false, then the execution continues */
    var $throwOnError = true;
    /** @var  PDO */
    var $conn1;
    /** @var  bool */
    var $transactionOpen;
    /** @var bool if the database is in READ ONLY mode or not. If true then we must avoid to write in the database. */
    var $readonly = false;
    /** @var string full filename of the log file. If it's empty then it doesn't store a log file. The log file is limited to 1mb */
    var $logFile = "";
    /** @var int
     * 0=no debug for production (all message of error are generic)<br>
     * 1=it shows an error message<br>
     * 2=it shows the error messages and the last query
     * 3=it shows the error messagr, the last query and the last parameters (if any). It could be unsafe (it could show password)
     */
    public $logLevel = 0;
    /** @var string last query executed */
    var $lastQuery;
    var $lastParam = [];
    /** @var int */
    private $affected_rows = 0;

    //<editor-fold desc="query builder fields">
    private $select = '';
    private $from = '';
    /** @var array */
    private $where = array();
    /** @var int[] PDO::PARAM_STR,PDO::PARAM_INT,PDO::PARAM_BOOL */
    private $whereParamType = array();
    /** @var null|array */
    private $whereParamAssoc = null;
    private $whereCounter = 0;
    /** @var array */
    private $whereParamValue = array();
    /** @var array */
    private $set = array();
    private $group = '';
    /** @var array */
    private $having = array();
    private $limit = '';

    private $distinct = '';
    private $order = '';
    //</editor-fold>

    /**
     * PdoOne constructor.  It doesn't open the connection to the database.
     *
     * @param string database ['mysql','sqlsrv','oracle','test'][$i]
     * @param string $server  server ip. Ex. 127.0.0.1
     * @param string $user    Ex. root
     * @param string $pwd     Ex. 12345
     * @param string $db      Ex. mybase
     * @param string $logFile Optional  log file. Example c:\\temp\log.log
     * @param string $charset Example utf8mb4
     * @param int    $nodeId  It is the id of the node (server). It is used for sequence. Form 0 to 1023
     *
     * @see PdoOne::connect()
     */
    public function __construct($database, $server, $user, $pwd, $db, $logFile = "", $charset = null, $nodeId = 1) {
        $this->databaseType = $database;
        switch ($this->databaseType) {
            case 'mysql':
                $this->database_delimiter0 = '`';
                $this->database_delimiter1 = '`';
                $charset = ($charset == null) ? 'utf8' : $charset;
                self::$isoDate = 'Y-m-d';
                self::$isoDateTime = 'Y-m-d H:i:s';
                self::$isoDateTimeMs = 'Y-m-d H:i:s.u';

                break;
            case 'sqlsrv':
                $this->database_delimiter0 = '[';
                $this->database_delimiter1 = ']';
                self::$isoDate = 'Ymd';
                self::$isoDateTime = 'Ymd H:i:s';
                self::$isoDateTimeMs = 'Ymd H:i:s.u';
                break;
            case 'test':
                $this->database_delimiter0 = '';
                $this->database_delimiter1 = '';
                self::$isoDate = 'Ymd';
                self::$isoDateTime = 'Ymd H:i:s';
                self::$isoDateTimeMs = 'Ymd H:i:s.u';
                break;

        }
        $this->server = $server;
        $this->user = $user;
        $this->pwd = $pwd;
        $this->db = $db;
        $this->logFile = $logFile;
        $this->charset = $charset;
        $this->nodeId = $nodeId;
        $this->encryption = new PdoOneEncryption($pwd,
            $user . $pwd); // by default, the encryption uses the same password than the db.
    }

    /**
     * Convert date from unix timestamp -> ISO (database format).
     * <p>Example: ::unixtime2Sql(1558656785); // returns 2019-05-24 00:13:05
     *
     * @param integer $dateNum
     *
     * @return string
     */
    public static function unixtime2Sql($dateNum) {
        // 31/01/2016 20:20:00 --> 2016-01-31 00:00
        if ($dateNum == null) {
            return PdoOne::$dateEpoch;
        }
        return date(self::$isoDateTimeMs, $dateNum);
    }

    /**
     * Convert date, from mysql date -> text (using a format pre-established)
     *
     * @param string $sqlField
     * @param bool $hasTime if true then the date contains time.
     *
     * @return string Returns a text with the date formatted (human readable)
     */
    public static function dateSql2Text($sqlField, $hasTime = false) {
        $tmpDate = self::dateTimeSql2PHP($sqlField, $hasTime);
        if ($tmpDate === null) {
            return null;
        }
        if ($hasTime) {
            return $tmpDate->format((strpos($sqlField, '.') !== false)
                ? self::$dateTimeMicroHumanFormat
                : self::$dateTimeHumanFormat);
        } else {
            return $tmpDate->format(self::$dateHumanFormat);
        }
    }

    /**
     * Convert date, from mysql -> php
     *
     * @param string $sqlField
     * @param bool $hasTime
     *
     * @return bool|DateTime|null
     */
    public static function dateTimeSql2PHP($sqlField, &$hasTime = false) {
        // 3  2016-01-31 00:00:00 -> 01/01/2016 00:00:00
        // mysql always returns the date/datetime/timestmamp in ansi format.
        if ($sqlField === "" || $sqlField === null) {
            if (PdoOne::$dateEpoch === null) {
                return null;
            }
            return DateTime::createFromFormat(self::$isoDateTimeMs, PdoOne::$dateEpoch);
        }

        if (strpos($sqlField, '.')) {
            // with date with time and microseconds
            //2018-02-06 05:06:07.123
            // Y-m-d H:i:s.v
            $hasTime = true;
            //$x = DateTime::createFromFormat("Y-m-d H:i:s.u", "2018-02-06 05:06:07.1234");
            return DateTime::createFromFormat(self::$isoDateTimeMs, $sqlField);
        } else {
            if (strpos($sqlField, ':')) {
                // date with time
                $hasTime = true;
                return DateTime::createFromFormat(self::$isoDateTime, $sqlField);
            } else {
                // only date
                $hasTime = false;
                return DateTime::createFromFormat(self::$isoDate, $sqlField);
            }
        }
    }

    /**
     * @param string $sqlField
     * @param string $inputFormat  =['iso','human','sql','class'][$i]
     * @param string $outputFormat =['iso','human','sql','class'][$i]
     *
     * @return bool|DateTime
     */
    public static function dateConvert($sqlField, $inputFormat, $outputFormat) {
        $ms = false;
        $time = false;
        $tmpDate = '';
        switch ($inputFormat) {
            case 'iso':
                if (strpos($sqlField, '.') !== false) {
                    $ms = true;
                    $tmpDate = DateTime::createFromFormat(self::$dateTimeMicroFormat, $sqlField);
                } else {
                    if (strpos($sqlField, ':') !== false) {
                        $time = true;
                        $tmpDate = DateTime::createFromFormat(self::$dateTimeFormat, $sqlField);
                    } else {
                        $tmpDate = DateTime::createFromFormat(self::$dateFormat, $sqlField);
                    }
                }
                break;
            case 'human':
                if (strpos($sqlField, '.') !== false) {
                    $ms = true;
                    $tmpDate = DateTime::createFromFormat(self::$dateTimeMicroHumanFormat, $sqlField);
                } else {
                    if (strpos($sqlField, ':') !== false) {
                        $time = true;
                        $tmpDate = DateTime::createFromFormat(self::$dateTimeHumanFormat, $sqlField);
                    } else {
                        $tmpDate = DateTime::createFromFormat(self::$dateHumanFormat, $sqlField);
                    }
                }
                break;
            case 'sql':
                if (strpos($sqlField, '.') !== false) {
                    $ms = true;
                    $tmpDate = DateTime::createFromFormat(self::$isoDateTimeMs, $sqlField);
                } else {
                    if (strpos($sqlField, ':') !== false) {
                        $time = true;
                        $tmpDate = DateTime::createFromFormat(self::$isoDateTime, $sqlField);
                    } else {
                        $tmpDate = DateTime::createFromFormat(self::$isoDate, $sqlField);
                    }
                }
                break;
            case 'class':
                $tmpDate = $sqlField;
                break;
            default:
                trigger_error('PdoOne: dateConvert type not defined');
        }
        if (!$tmpDate) {
            return false;
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
                break;
            case 'human':
                if ($ms) {
                    return $tmpDate->format(self::$dateTimeMicroHumanFormat);
                }
                if ($time) {
                    return $tmpDate->format(self::$dateTimeHumanFormat);
                }
                return $tmpDate->format(self::$dateHumanFormat);
                break;
            case 'sql':
                if ($ms) {
                    return $tmpDate->format(self::$isoDateTimeMs);
                }
                if ($time) {
                    return $tmpDate->format(self::$isoDateTime);
                }
                return $tmpDate->format(self::$isoDate);
                break;
            case 'class':
                return $tmpDate;
                break;
        }
        return false;
    }

    /**
     * Convert date, from text -> mysql (using a format pre-established)
     *
     * @param string $textDate Input date
     * @param bool   $hasTime  If true then it works with date and time (instead of date)
     *
     * @return string
     */
    public static function dateText2Sql($textDate, $hasTime = true) {
        $tmpFormat = (($hasTime)
            ? (strpos($textDate, '.') === false
                ? self::$dateTimeFormat
                : self::$dateTimeMicroFormat)
            : self::$dateFormat);
        $tmpDate = DateTime::createFromFormat($tmpFormat, $textDate);
        if (!$hasTime && $tmpDate) {
            $tmpDate->setTime(0, 0, 0);
        }
        return self::dateTimePHP2Sql($tmpDate); // it always returns a date with time. Mysql Ignores it.
    }

    /**
     * Conver date from php -> mysql
     * It always returns a time (00:00:00 if time is empty). it could returns microseconds 2010-01-01 00:00:00.00000
     *
     * @param DateTime $date
     *
     * @return string
     */
    public static function dateTimePHP2Sql($date) {
        // 31/01/2016 20:20:00 --> 2016-01-31 00:00
        if ($date == null) {
            return PdoOne::$dateEpoch;
        }
        if ($date->format("u") != '000000') {
            return $date->format(self::$isoDateTimeMs);
        } else {
            return $date->format(self::$isoDateTime);
        }

    }

    /**
     * Returns the current date(and time) in Text format.
     *
     * @param bool $hasTime
     * @param bool $hasMicroseconds
     *
     * @return string
     * @throws Exception
     * @see PdoOne::$dateTimeFormat
     */
    public static function dateTextNow($hasTime = true, $hasMicroseconds = false) {
        $tmpDate = new DateTime();
        if ($hasTime) {
            return $tmpDate->format(($hasMicroseconds !== false) ? self::$dateTimeMicroFormat : self::$dateTimeFormat);
        } else {
            return $tmpDate->format(self::$dateFormat);
        }
    }

    public static function dateMysqlNow($hasTime = true, $hasMicroseconds = false) {
        try {
            $tmpDate = new DateTime();
        } catch (Exception $e) {
            $tmpDate = null;
        }
        if ($hasTime) {
            return $tmpDate->format(($hasMicroseconds !== false) ? self::$isoDateTimeMs : self::$isoDateTime);
        } else {
            return $tmpDate->format(self::$isoDate);
        }
    }

    /**
     * It changes default database, schema or user.
     *
     * @param $dbName
     *
     * @test void this('travisdb')
     */
    public function db($dbName) {
        if (!$this->isOpen) {
            return;
        }
        $this->db = $dbName;
        $this->conn1->query('use ' . $dbName);
    }

    /**
     * returns if the database is in read-only mode or not.
     *
     * @return bool
     * @test equals false,this(),'the database is read only'
     */
    public function readonly() {
        return $this->readonly;
    }

    /**
     * Alias of PdoOne::connect()
     *
     * @param bool $failIfConnected
     *
     * @throws Exception
     * @test exception this(false)
     * @see  PdoOne::connect()
     */
    public function open($failIfConnected = true) {
        $this->connect($failIfConnected);
    }

    /**
     * Connects to the database.
     *
     * @param bool $failIfConnected true=it throw an error if it's connected, otherwise it does nothing
     *
     * @throws Exception
     * @test exception this(false)
     */
    public function connect($failIfConnected = true) {
        //mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
        if ($this->isOpen) {
            if (!$failIfConnected) {
                return;
            } // it's already connected.
            $this->throwError("Already connected", "");
        }
        try {
            if ($this->logLevel >= 2) {
                $this->storeInfo("connecting to {$this->server} {$this->user}/*** {$this->db}");
            }
            $cs = ($this->charset != '') ? ';charset=' . $this->charset : '';
            switch ($this->databaseType) {
                case 'mysql':
                    $this->conn1 = new PDO("{$this->databaseType}:host={$this->server};dbname={$this->db}{$cs}",
                        $this->user, $this->pwd);
                    break;
                case 'sqlsrv':
                    $this->conn1 = new PDO("{$this->databaseType}:server={$this->server};database={$this->db}{$cs}",
                        $this->user, $this->pwd);
                    break;
                case 'test':
                    $this->conn1 = new stdClass();
                    break;
                default:
                    $this->throwError("database not defined or supported {$this->databaseType}", "");
                    break;
            }
            $this->conn1->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

            $this->isOpen = true;
        } catch (Exception $ex) {
            $this->isOpen = false;
            $this->throwError("Failed to connect to {$this->databaseType}", $ex->getMessage(),
                '\nTRACE:' . $ex->getTraceAsString());
        }

    }

    /**
     * Write a log line for debug, clean the command chain then throw an error (if throwOnError==true)
     *
     * @param string       $txt        The message to show.
     * @param string       $txtExtra   It's only used if $logLevel>=2. It shows an extra message
     * @param string|array $extraParam It's only used if $logLevel>=3  It shows parameters (if any)
     *
     * @throws Exception
     * @see \eftec\PdoOne::$logLevel
     */
    function throwError($txt, $txtExtra, $extraParam = '') {
        if ($this->logLevel === 0) {
            $txt = 'Error on database';
        }
        if ($this->logLevel >= 2) {
            $txt .= $txtExtra;
        }
        if ($this->logLevel >= 2) {
            $txt .= ".\nLast query:[{$this->lastQuery}].";
        }
        if ($this->logLevel >= 3) {
            $txt .= "\nDatabase:" . $this->server . " - " . $this->db;
            if (is_array($extraParam)) {
                $txt .= "\nParams :[";
                foreach ($extraParam as $key => $item) {
                    $txt .= "\n$key = [$item] (" . strlen($item) . "),";
                }
                $txt .= "]";
            } else {
                $txt .= "\nParams :[" . $extraParam . "]";
            }
        }
        $this->builderReset(); // it resets the chain if any.
        if ($this->getMessages() === null) {
            $this->debugFile($txt, 'ERROR');
        } else {
            $this->getMessages()->addItem($this->db, $txt);
            $this->debugFile($txt, 'ERROR');
        }
        if ($this->throwOnError) {
            throw new Exception($txt);
        }
    }

    /**
     * It reset the parameters used to Build Query.
     */
    private function builderReset() {
        $this->select = '';
        $this->from = '';
        $this->where = [];
        $this->whereParamType = array();
        $this->whereParamAssoc = null;
        $this->whereCounter = 0;
        $this->whereParamValue = array();
        $this->set = [];
        $this->group = '';
        $this->having = [];
        $this->limit = '';
        $this->distinct = '';
        $this->order = '';
    }

    /**
     * Injects a Message Container.
     *
     * @return MessageList|null
     * @test equals null,this(),'this is not a message container'
     */
    public function getMessages() {
        if (function_exists('messages')) {
            return messages();
        }
        return null;
    }

    function debugFile($txt, $level = 'INFO') {
        if ($this->logFile == '') {
            return; // debug file is disabled.
        }
        $fz = @filesize($this->logFile);

        if (is_object($txt) || is_array($txt)) {
            $txtW = print_r($txt, true);
        } else {
            $txtW = $txt;
        }
        if ($fz > 10000000) {
            // mas de 10mb = reducirlo a cero.
            $fp = @fopen($this->logFile, 'w');
        } else {
            $fp = @fopen($this->logFile, 'a');
        }
        if ($this->logLevel == 2) {
            $txtW .= " param:" . json_encode($this->lastParam);
        }

        $txtW = str_replace("\r\n", " ", $txtW);
        $txtW = str_replace("\n", " ", $txtW);
        try {
            $now = new DateTime();
            @fwrite($fp, $now->format('c') . "\t" . $level . "\t" . $txtW . "\n");
        } catch (Exception $e) {
        }

        @fclose($fp);
    }

    /**
     * Write a log line for debug, clean the command chain then throw an error (if throwOnError==true)
     *
     * @param $txt
     *
     * @throws Exception
     */
    function storeInfo($txt) {
        if ($this->getMessages() === null) {
            $this->debugFile($txt, 'INFO');
        } else {
            $this->getMessages()->addItem($this->db, $txt, "info");
            $this->debugFile($txt, 'INFO');
        }
    }

    /**
     * It closes the connection
     *
     * @test void this()
     */
    public function close() {
        $this->isOpen = false;
        if ($this->conn1 === null) {
            return;
        } // its already close

        @$this->conn1 = null;
    }




    //<editor-fold desc="transaction functions">

    /**
     * It returns the next sequence.
     * It gets a collision free number if we don't do more than one operation
     * every 0.0001 seconds.
     * But, if we do 2 or more operations per seconds then, it adds a sequence number from
     * 0 to 4095
     * So, the limit of this function is 4096 operations per 0.0001 second.
     *
     * @see \eftec\PdoOne::getSequencePHP It's the same but it uses less resources but lacks of a sequence.
     *
     * @param bool $asFloat
     * @param bool $unpredictable
     *
     * @return string . Example string(19) "3639032938181434317"
     * @throws Exception
     */
    public function getSequence($asFloat = false, $unpredictable = false) {
        $sql = "select next_{$this->tableSequence}({$this->nodeId}) id";
        $r = $this->runRawQuery($sql, null, true);
        if ($unpredictable) {
            if (PHP_INT_SIZE == 4) {
                return $this->encryption->encryptSimple($r[0]['id']);
            } else {
                // $r is always a 32 bit number so it will fail in PHP 32bits
                return $this->encryption->encryptInteger($r[0]['id']);
            }
        }
        if ($asFloat) {
            return floatval($r[0]['id']);
        } else {
            return $r[0]['id'];
        }
    }

    /**
     * It runs an unprepared query.
     * <br><b>Example</b>:<br>
     *      $values=$con->runRawQuery('select * from table where id=?',["i",20]',true)
     *
     * @param string     $rawSql
     * @param array|null $param
     * @param bool       $returnArray
     *
     * @return bool|PDOStatement|array an array of associative or a pdo statement
     * @throws Exception
     * @test equals [0=>[1=>1]],this('select 1',null,true)
     */
    public function runRawQuery($rawSql, $param = null, $returnArray = true) {
        if (!$this->isOpen) {
            $this->throwError("It's not connected to the database", '');
            return false;
        }
        if ($this->readonly) {
            if (stripos($rawSql, 'insert ') === 0 || stripos($rawSql, 'update ') === 0
                || stripos($rawSql, 'delete ') === 0
            ) {
                // we aren't checking SQL-DLC queries. Also, "insert into" is stopped but "  insert into" not.
                $this->throwError("Database is in READ ONLY MODE", '');
            }
        }
        $this->lastParam = $param;
        $this->lastQuery = $rawSql;
        if ($this->logLevel >= 2) {
            $this->storeInfo($rawSql);
        }
        if ($param === null) {
            // the "where" chain doesn't have parameters.
            try {
                $rows = $this->conn1->query($rawSql);
            } catch (Exception $ex) {
                $rows = false;
                $this->throwError("Exception raw", $rawSql,
                    json_encode($this->lastParam) . '\nTRACE:' . $ex->getTraceAsString());
            }
            if ($rows === false) {
                $this->throwError("Unable to run raw query", $rawSql, $this->lastParam);
            }

            if ($returnArray && $rows instanceof PDOStatement) {
                if ($rows->columnCount() > 0) {
                    $result = @$rows->fetchAll(PDO::FETCH_ASSOC);
                    $this->affected_rows = $rows->rowCount();
                    return $result;
                } else {
                    $this->affected_rows = $rows->rowCount();
                    return true;
                }
            } else {
                $this->affected_rows = $rows->rowCount();
                return $rows;
            }
        }
        // the "where" has parameters.
        $stmt = $this->prepare($rawSql);
        $counter = 0;
        for ($i = 0; $i < count($param); $i += 2) {
            $counter++;
            $typeP = $this->stringToPdoParam($param[$i]);
            $stmt->bindParam($counter
                , $param[$i + 1]
                , $typeP);
        }
        //$stmt->bindParam($parType, ...$values);
        $this->runQuery($stmt);

        if ($returnArray && $stmt instanceof PDOStatement) {
            $rows = ($stmt->columnCount() > 0) ? $stmt->fetchAll(PDO::FETCH_ASSOC) : array();
            $this->affected_rows = $stmt->rowCount();
            $stmt = null;
            return $rows;
        } else {
            $this->affected_rows = $stmt->rowCount();
            return $stmt;
        }
    }

    /**
     * Prepare a query. It returns a mysqli statement.
     *
     * @param string $statement A SQL statement.
     *
     * @return PDOStatement returns the statement if correct otherwise null
     * @throws Exception
     */
    public function prepare($statement) {
        if (!$this->isOpen) {
            $this->throwError("It's not connected to the database", "");
            return null;
        }
        $this->lastQuery = $statement;
        if ($this->readonly) {
            if (stripos($statement, 'insert ') === 0 || stripos($statement, 'update ') === 0
                || stripos($statement, 'delete ') === 0
            ) {
                // we aren't checking SQL-DCL queries.
                $this->throwError("Database is in READ ONLY MODE", "");
            }
        }
        if ($this->logLevel >= 2) {
            $this->storeInfo($statement);
        }

        try {
            $stmt = $this->conn1->prepare($statement);
        } catch (Exception $ex) {
            $stmt = false;
            $this->throwError("Failed to prepare", $ex->getMessage(),
                json_encode($this->lastParam) . '\nTRACE:' . $ex->getTraceAsString());
        }
        if ($stmt === false) {
            $this->throwError("Unable to prepare query", $this->lastQuery, json_encode($this->lastParam));
        }
        return $stmt;
    }
    //</editor-fold>

    //<editor-fold desc="Date functions" defaultstate="collapsed" >

    private function stringToPdoParam($string) {
        if (is_int($string)) {
            return $string;
        }
        switch ($string) {
            case 'i':
                return PDO::PARAM_INT;
            case 's':
                return PDO::PARAM_STR;
            case 'd':
                return PDO::PARAM_STR;
            default:
                trigger_error("param type not defined [$string]");
                return null;
        }
    }

    /**
     * Run a prepared statement.
     * <br><b>Example</b>:<br>
     *      $con->runQuery($con->prepare('select * from table'));
     *
     * @param PDOStatement $stmt          PDOStatement
     * @param array|null   $namedArgument (optional)
     *
     * @return bool returns true if the operation is correct, otherwise false
     * @throws Exception
     * @test equals true,$this->pdoOne->runQuery($this->pdoOne->prepare('select 1 from dual'))
     * @test equals [1=>1],$this->pdoOne->select('1')->from('dual')->first(),'it must runs'
     */
    public function runQuery($stmt, $namedArgument = null) {
        if (!$this->isOpen) {
            $this->throwError("It's not connected to the database", "");
            return null;
        }
        try {
            $namedArgument = ($namedArgument === null) ? $this->whereParamAssoc : $namedArgument;
            $r = $stmt->execute($namedArgument);
        } catch (Exception $ex) {
            $r = false;
            $this->throwError("Failed to run query ", $this->lastQuery . "\nCAUSE: " . $ex->getMessage(),
                json_encode($this->lastParam) . '\nTRACE:' . $ex->getTraceAsString());
        }
        if ($r === false) {
            $this->throwError("Exception query ", $this->lastQuery, $this->lastParam);
        }
        return true;
    }

    /**
     * <p>This function returns an unique sequence<p>
     * It ensures a collision free number only if we don't do more than one operation
     * per 0.0001 second However,it also adds a pseudo random number (0-4095)
     * so the chances of collision is 1/4095 (per two operations done every 0.0001 second).<br>
     * It is based on Twitter's Snowflake number
     *
     * @param bool $unpredictable
     *
     * @return float
     * @see \eftec\PdoOne::getSequence
     */
    public function getSequencePHP($unpredictable = false) {
        $ms = microtime(true);
        //$ms=1000;
        $timestamp = (double)round($ms * 1000);
        $rand = (fmod($ms, 1) * 1000000) % 4096; // 4096= 2^12 It is the millionth of seconds
        $calc = (($timestamp - 1459440000000) << 22) + ($this->nodeId << 12) + $rand;
        usleep(1);

        if ($unpredictable) {
            if (PHP_INT_SIZE == 4) {
                return '' . $this->encryption->encryptSimple($calc);
            } else {
                // $r is always a 32 bit number so it will fail in PHP 32bits
                return '' . $this->encryption->encryptInteger($calc);
            }
        }
        return '' . $calc;
    }

    /**
     * It uses \eftec\PdoOne::$masks0 and \eftec\PdoOne::$masks1 to flip
     * the number, so they are not as predictable.
     * This function doesn't add entrophy. However, the generation of Snowflakes id
     * (getSequence/getSequencePHP) generates its own entrophy. Also,
     * both masks0[] and masks1[] adds an extra secrecy.
     *
     * @param $number
     *
     * @return mixed
     */
    public function getUnpredictable($number) {
        $string = "" . $number;
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
    public function getUnpredictableInv($number) {
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
    public function tableExist($tableName) {
        return $this->objectExist($tableName, 'table');
    }

    /**
     * returns true if the object exists
     * Currently only works with table
     *
     * @param string $objectName
     * @param string $type (table)
     *
     * @return bool
     * @throws Exception
     */
    private function objectExist($objectName, $type = 'table') {

        switch ($this->databaseType) {
            case 'mysql':
                switch ($type) {
                    case 'table':
                        $query
                            = "SELECT * FROM information_schema.tables where table_schema='{$this->db}' and table_name=?";
                        break;
                    default:
                        $this->throwError("objectExist: type [$type] not defined for {$this->databaseType}", "");
                        die(1);
                        break;
                }
                break;
            case 'sqlsrv':
                switch ($type) {
                    case 'table':
                        $query = "SELECT * FROM sys.objects where name=? and type_desc='USER_TABLE'";
                        break;
                    default:
                        $this->throwError("objectExist: type [$type] not defined for {$this->databaseType}", "");
                        die(1);
                        break;
                }
                break;
            case 'test':
                switch ($type) {
                    case 'table':
                        $query
                            = "SELECT * FROM tables where table_schema='{$this->db}' and table_name=?";
                        break;
                    default:
                        $this->throwError("objectExist: type [$type] not defined for {$this->databaseType}", "");
                        die(1);
                        break;
                }
                break;
            default:
                $this->throwError("database not defined or supported {$this->databaseType}", "");
                die(1);
        }
        $arr = $this->runRawQuery($query, [PDO::PARAM_STR, $objectName], true);
        return is_array($arr);
    }
    //</editor-fold>

    //<editor-fold desc="Query Builder functions" defaultstate="collapsed" >

    /**
     * It returns the statistics (minimum,maximum,average,sum and count) of a column of a table
     *
     * @param string $tableName  Name of the table
     * @param string $columnName The column name to analyze.
     *
     * @return array|bool Returns an array of the type ['min','max','avg','sum','count']
     * @throws Exception
     */
    public function statValue($tableName, $columnName) {
        $query = "select min($columnName) min
						,max($columnName) max
						,avg($columnName) avg
						,sum($columnName) sum
						,count($columnName) count
						 from $tableName";
        return $this->runRawQuery($query, null, true);
    }

    /**
     * Returns the columns of a table
     *
     * @param string $tableName The name of the table.
     *
     * @return array|bool=['colname','coltype','colsize','colpres','colscale','iskey','isidentity','isnullable']
     * @throws Exception
     */
    public function columnTable($tableName) {
        switch ($this->databaseType) {
            case 'mysql':
                $query = "SELECT column_name colname
								,data_type coltype
								,character_maximum_length colsize
								,numeric_precision colpres
								,numeric_scale colscale
								,if(column_key='PRI',1,0) iskey
								,if(extra='auto_increment',1,0)  isidentity
								,if(is_nullable='NO',1,0)  isnullable
					 	FROM information_schema.columns
						where table_schema='{$this->db}' and table_name='$tableName'";
                $r = $this->runRawQuery($query, null, true);
                break;
            case 'sqlsrv':
                $query = "SELECT col.name colname
							,st.name coltype
							,col.max_length colsize
							,col.precision colpres
							,col.scale colscale
							,pk.is_primary_key iskey
							,col.is_identity isidentity
							,col.is_nullable isnullable
						FROM sys.COLUMNS col
						inner join sys.objects obj on obj.object_id=col.object_id
						inner join sys.types st on col.system_type_id=st.system_type_id		
						left join sys.index_columns idx on obj.object_id=idx.object_id and col.column_id=idx.column_id
						left join sys.indexes pk on obj.object_id = pk.object_id and pk.index_id=idx.index_id and pk.is_primary_key=1
						where  obj.name='$tableName'";
                $r = $this->runRawQuery($query, null, true);
                break;
            case 'test':
                $query = "SELECT column_name colname
								,data_type coltype
								,character_maximum_length colsize
								,numeric_precision colpres
								,numeric_scale colscale
								,1 iskey
								,1 isidentity
								,1 isnullable
					 	FROM information_schema.columns
						where table_schema='{$this->db}' and table_name='$tableName'";
                $r = $this->runRawQuery($query, null, true);
                break;
            default:
                trigger_error("database type not defined");
                die(1);
        }

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
    public function foreignKeyTable($tableName) {
        switch ($this->databaseType) {
            case 'mysql':
                $query = "SELECT 
							column_name collocal,
						    REFERENCED_TABLE_NAME tablerem,
						    REFERENCED_COLUMN_NAME colrem
						 FROM information_schema.KEY_COLUMN_USAGE
						where table_name='$tableName' and constraint_schema='{$this->db}'
						and referenced_table_name is not null;";

                $r = $this->runRawQuery($query, null, true);
                break;
            case 'sqlsrv':
                $query = "SELECT col.name collocal
					,objrem.name tablerem
					,colrem.name colrem
					FROM sys.foreign_key_columns fk
					inner join sys.objects obj on obj.object_id=fk.parent_object_id
					inner join sys.COLUMNS col on obj.object_id=col.object_id and fk.parent_column_id=col.column_id
					inner join sys.types st on col.system_type_id=st.system_type_id	
					inner join sys.objects objrem on objrem.object_id=fk.referenced_object_id
					inner join sys.COLUMNS colrem on fk.referenced_object_id=colrem.object_id and fk.referenced_column_id=colrem.column_id
					where obj.name='$tableName' ";
                $r = $this->runRawQuery($query, null, true);
                break;
            case 'test':
                $query = "SELECT col.name collocal
					,objrem.name tablerem
					,colrem.name colrem
					FROM columns fk
					where obj.name='$tableName' ";
                $r = $this->runRawQuery($query, null, true);
                break;
            default:
                trigger_error("database type not defined");
                die(1);
        }

        return $r;
    }

    /**
     * Create a table<br>
     * <b>Example:</b><br>
     * <pre>
     * createTable('products',['id'=>'int not null','name'=>'varchar(50) not null'],'id');
     * </pre>
     *
     * @param string      $tableName  The name of the new table. This method will fail if the table exists.
     * @param array       $definition An associative array with the definition of the columns.<br>
     *                                Example ['id'=>'integer not null','name'=>'varchar(50) not null']
     * @param string|null $primaryKey The column's name that is primary key.
     * @param string      $extra      An extra operation inside of the definition of the table.
     *
     * @return array|bool|PDOStatement
     * @throws Exception
     */
    public function createTable($tableName, $definition, $primaryKey = null, $extra = '') {
        $sql = null;
        switch ($this->databaseType) {
            case 'mysql':
                $sql = "CREATE TABLE `{$tableName}` (";
                foreach ($definition as $key => $type) {
                    $sql .= "`$key` $type,";
                }
                if ($primaryKey) {
                    $sql .= " PRIMARY KEY(`$primaryKey`) ";
                } else {
                    $sql = substr($sql, 0, -1);
                }
                $sql .= "$extra ) ENGINE=MyISAM DEFAULT CHARSET=" . $this->charset;
                break;
            case 'sqlsrv':
                $sql = "set nocount on;
				CREATE TABLE [{$tableName}] (";
                foreach ($definition as $key => $type) {
                    $sql .= "[$key] $type,";
                }

                $sql .= "$extra ) ON [PRIMARY]; ";

                if ($primaryKey) {
                    $sql .= "
						ALTER TABLE [$tableName] ADD CONSTRAINT
							PK_$tableName PRIMARY KEY CLUSTERED 
							(
							[$primaryKey]
							) ON [PRIMARY]
						";
                }
                break;
            case 'test':
                $sql = "CREATE TABLE {$tableName} (";
                foreach ($definition as $key => $type) {
                    $sql .= "$key $type,";
                }
                if ($primaryKey) {
                    $sql .= " PRIMARY KEY(`$primaryKey`) ";
                } else {
                    $sql = substr($sql, 0, -1);
                }
                $sql .= "$extra )";
                break;
            default:
                $this->throwError("type not defined for create table", "");
        }
        return $this->runRawQuery($sql, null, true);
    }

    /**
     * Create a table used for a sequence<br>
     * The name of the sequence is defined by $pdoOne->tableSequence<br>
     *
     * @throws Exception
     */
    public function createSequence() {
        $sql = '';
        switch ($this->databaseType) {
            case 'mysql':
                $sql = "CREATE TABLE `{$this->tableSequence}` (
				  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
				  `stub` char(1) NOT NULL DEFAULT '',
				  PRIMARY KEY (`id`),
				  UNIQUE KEY `stub` (`stub`)
				) ENGINE=MyISAM AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;
				-- insert the firsrt value
				INSERT INTO `{$this->tableSequence}` (`stub`) VALUES ('a');
				SET GLOBAL log_bin_trust_function_creators = 1;";
                $this->runMultipleRawQuery($sql);
                $sql = "CREATE FUNCTION `next_{$this->tableSequence}`(node integer) RETURNS BIGINT(20)
					BEGIN
					    DECLARE epoch BIGINT(20);
					    DECLARE current_ms BIGINT(20);
					    DECLARE incr BIGINT(20);
					    SET current_ms = round(UNIX_TIMESTAMP(CURTIME(4)) * 1000);
					    SET epoch = 1459440000000; 
					    REPLACE INTO {$this->tableSequence} (stub) VALUES ('a');
					    SELECT LAST_INSERT_ID() INTO incr;    
					RETURN (current_ms - epoch) << 22 | (node << 12) | (incr % 4096);
					END;";
                break;
            case 'sqlsrv':
                $sql = "CREATE SEQUENCE [{$this->tableSequence}]
				    START WITH 1  
				    INCREMENT BY 1
			    ;";
                $sql .= "create PROCEDURE next_{$this->tableSequence}
					@node int
				AS
					BEGIN
						-- Copyright Jorge Castro https://github.com/EFTEC/PdoOne
						SET NOCOUNT ON;
						declare @return bigint
						declare @current_ms bigint; 
						declare @incr bigint;
						-- 2018-01-01 is an arbitrary epoch
						set @current_ms=cast(DATEDIFF(s, '2018-01-01 00:00:00', GETDATE()) as bigint) *cast(1000 as bigint)  + DATEPART(MILLISECOND,getutcdate());	
						SELECT @incr= NEXT VALUE FOR {$this->tableSequence};  
						-- current_ms << 22 | (node << 12) | (incr % 4096);
						set @return=(@current_ms*cast(4194304 as bigint)) + (@node *4096) + (@incr % 4096);
						select @return
					END";
                break;
            case 'test':
                $sql = "CREATE TABLE";
                break;
            default:
                $this->throwError("type not defined for create sequence", "");
        }
        $this->runRawQuery($sql);
    }

    /**
     * Run many  unprepared query separated by ;<br>
     * <b>Example:</b><br>
     * <pre>
     * ->runMultipleRawQuery("insert into() values(1); insert into() values(2)");<br>
     * </pre>
     *
     * @param string $listSql         SQL multiples queries separated by ";"
     * @param bool   $continueOnError if true then it continues on error.
     *
     * @return bool
     * @throws Exception
     */
    public function runMultipleRawQuery($listSql, $continueOnError = false) {
        if (!$this->isOpen) {
            $this->throwError("RMRQ: It's not connected to the database", "");
            return false;
        }
        $arr = explode(';', $listSql);
        $ok = true;
        $counter = 0;
        foreach ($arr as $rawSql) {
            if (trim($rawSql) != '') {
                if ($this->readonly) {
                    if (stripos($rawSql, 'insert ') === 0 || stripos($rawSql, 'update ') === 0
                        || stripos($rawSql, 'delete ') === 0
                    ) {
                        // we aren't checking SQL-DCL queries. Also, "insert into" is stopped but "  insert into" not.
                        $ok = false;
                        if (!$continueOnError) {
                            $this->throwError("Database is in READ ONLY MODE", "");
                        }
                    }
                }
                if ($this->logLevel >= 2) {
                    $this->storeInfo($rawSql);
                }
                $r = $this->conn1->query($rawSql);
                if ($r === false) {
                    $ok = false;
                    if (!$continueOnError) {
                        $this->throwError("Unable to run raw query", $this->lastQuery);
                    }
                } else {
                    $counter += $r->rowCount();
                }
            }
        }
        $this->affected_rows = $counter;
        return $ok;
    }

    /**
     * It starts a transaction. If fails then it returns false, otherwise true.
     *
     * @return bool
     * @test     equals true,this()
     * @posttest execution $this->pdoOne->commit();
     * @example  examples/testdb.php 92,4
     */
    public function startTransaction() {
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
     * @param bool $throw if true and it fails then it throws an error.
     *
     * @return bool
     * @throws Exception
     * @test equals false,(false),'transaction is not open'
     */
    public function commit($throw = true) {
        if (!$this->transactionOpen && $throw) {
            $this->throwError("Transaction not open to commit()", "");
            return false;
        }
        if (!$this->isOpen) {
            $this->throwError("It's not connected to the database", "");
            return false;
        }
        $this->transactionOpen = false;
        return @$this->conn1->commit();
    }

    /**
     * Rollback and close a transaction
     *
     * @param bool $throw if true and it fails then it throws an error.
     *
     * @return bool
     * @throws Exception
     * @test equals false,(false),'transaction is not open'
     */
    public function rollback($throw = true) {
        if (!$this->transactionOpen && $throw) {
            $this->throwError("Transaction not open  to rollback()", "");
        }
        if (!$this->isOpen) {
            $this->throwError("It's not connected to the database", "");
            return false;
        }
        $this->transactionOpen = false;
        return @$this->conn1->rollback();
    }

    /**
     * It generates a query for "count". It is a macro of select()
     * <br><b>Example</b>:<br>
     * <pre>
     * ->count('')->from('table')->firstScalar() // select count(*) from table<br>
     * ->count('from table')->firstScalar() // select count(*) from table<br>
     * ->count('from table where condition=1')->firstScalar() // select count(*) from table where condition=1<br>
     * ->count('from table','col')->firstScalar() // select count(col) from table<br>
     * </pre>
     *
     * @param string|null $sql
     * @param string      $arg
     *
     * @return PdoOne
     */
    public function count($sql = '', $arg = '*') {
        return $this->_aggFn('count', $sql, $arg);
    }

    private function _aggFn($method, $sql = '', $arg = '') {
        if ($arg === '') {
            $arg = $sql; // if the argument is empty then it uses sql as argument
            $sql = ''; // and it lefts sql as empty
        }
        return $this->select("select $method($arg) $sql");
    }

    /**
     * It adds a select to the query builder.
     * <br><b>Example</b>:<br>
     * <pre>
     * ->select("\*")->from('table') = <i>"select * from table"</i><br>
     * ->select(['col1','col2'])->from('table') = <i>"select col1,col2 from table"</i><br>
     * ->select('col1,col2')->from('table') = <i>"select col1,col2 from table"</i><br>
     * ->select('select *')->from('table') = <i>"select * from table"</i><br>
     * ->select('select * from table') = <i>"select * from table"</i><br>
     * ->select('select * from table where id=1') = <i>"select * from table where id=1"</i><br>
     * </pre>
     *
     * @param string|array $sql
     *
     * @return PdoOne
     * @test InstanceOf PdoOne::class,this('select 1 from DUAL')
     */
    public function select($sql) {
        if (is_array($sql)) {
            $this->select .= implode(', ', $sql);
        } else {
            if ($this->select === '') {
                $this->select = $sql;
            } else {
                $this->select .= ', ' . $sql;
            }
        }
        return $this;
    }

    /**
     * It generates a query for "sum". It is a macro of select()
     * <br><b>Example</b>:<br>
     * ->sum('from table','col')->firstScalar() // select sum(col) from table<br>
     * ->sum('col')->from('table')->firstScalar() // select sum(col) from table<br>
     * ->sum('','col')->from('table')->firstScalar() // select sum(col) from table<br>
     *
     * @param string $sql
     * @param string $arg
     *
     * @return PdoOne
     */
    public function sum($sql = '', $arg = '') {
        return $this->_aggFn('sum', $sql, $arg);
    }

    /**
     * It generates a query for "min". It is a macro of select()
     * <br><b>Example</b>:<br>
     * ->min('from table','col')->firstScalar() // select min(col) from table<br>
     * ->min('col')->from('table')->firstScalar() // select min(col) from table<br>
     * ->min('','col')->from('table')->firstScalar() // select min(col) from table<br>
     *
     * @param string $sql
     * @param string $arg
     *
     * @return PdoOne
     */
    public function min($sql = '', $arg = '') {
        return $this->_aggFn('min', $sql, $arg);
    }

    /**
     * It generates a query for "max". It is a macro of select()
     * <br><b>Example</b>:<br>
     * ->max('from table','col')->firstScalar() // select max(col) from table<br>
     * ->max('col')->from('table')->firstScalar() // select max(col) from table<br>
     * ->max('','col')->from('table')->firstScalar() // select max(col) from table<br>
     *
     * @param string $sql
     * @param string $arg
     *
     * @return PdoOne
     */
    public function max($sql = '', $arg = '') {
        return $this->_aggFn('max', $sql, $arg);
    }

    /**
     * It generates a query for "avg". It is a macro of select()
     * <br><b>Example</b>:<br>
     * ->avg('from table','col')->firstScalar() // select avg(col) from table<br>
     * ->avg('col')->from('table')->firstScalar() // select avg(col) from table<br>
     * ->avg('','col')->from('table')->firstScalar() // select avg(col) from table<br>
     *
     * @param string $sql
     * @param string $arg
     *
     * @return PdoOne
     */
    public function avg($sql = '', $arg = '') {
        return $this->_aggFn('avg', $sql, $arg);
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
     * @return PdoOne
     * @see \eftec\PdoOne::join
     */
    public function innerjoin($sql, $condition = '') {
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
     * @return PdoOne
     * @test InstanceOf PdoOne::class,this('tablejoin on t1.field=t2.field')
     */
    public function join($sql, $condition = '') {
        if ($this->from == '') {
            return $this->from($sql);
        }
        if ($condition != '') {
            $sql = "$sql on $condition";
        }
        $this->from .= ($sql) ? " inner join $sql " : '';
        return $this;
    }

    /**
     * Adds a from for a query. It could be used by select,insert,update and delete.<br>
     * <b>Example:</b><br>
     * <pre>
     *      from('table')
     *      from('table alias')
     *      from('table1,table2')
     *      from('table1 inner join table2 on table1.c=table2.c')
     * </pre>
     *
     * @param string $sql Input SQL query
     *
     * @return PdoOne
     * @test InstanceOf PdoOne::class,this('table t1')
     */
    public function from($sql) {
        $this->from = ($sql) ? $sql : '';
        return $this;
    }

    /**
     * Adds a left join to the pipeline. It is possible to chain more than one join<br>
     * <b>Example:</b><br>
     * <pre>
     *      left('table on t1.c1=t2.c2')
     *      left('table on table.c1=t2.c2').left('table2 on table1.c1=table2.c2')
     * </pre>
     *
     * @param string $sql Input SQL query
     *
     * @return PdoOne
     * @test InstanceOf PdoOne::class,this('table2 on table1.t1=table2.t2')
     */
    public function left($sql) {
        if ($this->from == '') {
            return $this->from($sql);
        }
        $this->from .= ($sql) ? " left join $sql" : '';
        return $this;
    }

    /**
     * Adds a right join to the pipeline. It is possible to chain more than one join<br>
     * <b>Example:</b><br>
     *      right('table on t1.c1=t2.c2')<br>
     *      right('table on table.c1=t2.c2').right('table2 on table1.c1=table2.c2')<br>
     *
     * @param string $sql Input SQL query
     *
     * @return PdoOne
     * @test InstanceOf PdoOne::class,this('table2 on table1.t1=table2.t2')
     */
    public function right($sql) {
        if ($this->from == '') {
            return $this->from($sql);
        }
        $this->from .= ($sql) ? " right join $sql" : '';
        return $this;
    }

    /**
     * It sets a value into the query (insert or update)<br>
     * <b>Example:</b><br>
     *      ->from("table")->set('field1=?,field2=?',['i',20,'s','hello'])->insert()<br>
     *      ->from("table")->set("type=?",['i',6])->where("i=1")->update()<br>
     *      set("type=?",6) // automatic<br>
     *
     * @param string|array $sqlOrArray
     * @param array|mixed  $param
     *
     * @return PdoOne
     * @test InstanceOf PdoOne::class,this('field1=?,field2=?',['i',20,'s','hello'])
     */
    public function set($sqlOrArray, $param = self::NULL) {
        if (count($this->where)) {
            trigger_error("you can't execute set() after a where()");
            //$this->throwError("you can't execute set() after a where()","");
        }
        if (is_string($sqlOrArray)) {
            $this->set[] = $sqlOrArray;
            // self::NULL  is used when no value is set. We can't use null because it is a valid option.
            if ($param === self::NULL) {
                return $this;
            }
            if (is_array($param)) {
                for ($i = 0; $i < count($param); $i += 2) {
                    $this->whereParamType[] = $param[$i];
                    $this->whereParamValue['i_' . $this->whereCounter] = $param[$i + 1];
                    $this->whereCounter++;
                }
            } else {
                $this->whereParamType[] = 's';
                $this->whereParamValue['i_' . $this->whereCounter] = $param;
                $this->whereCounter++;
            }
        } else {
            $col = array();
            $colT = array();
            $p = array();
            $this->constructParam($sqlOrArray, $param, $col, $colT, $p);
            foreach ($col as $k => $c) {
                $this->set[] = $this->addQuote($c) . "=?";
                $this->whereParamType[] = $p[$k * 2];
                $this->whereParamValue['i_' . $this->whereCounter] = $p[$k * 2 + 1];
                $this->whereCounter++;
            }
        }
        return $this;
    }

    /**
     * @param array|null $tableDefs       It could be a definition with or without values.
     *                                    If null then it is defined automatically by $arrayValue.
     * @param array|int  $values          if value is self::NULL then it's calculated without this value
     * @param array      $col
     * @param array      $colT
     * @param array      $param
     */
    private function constructParam($tableDefs, $values, &$col, &$colT, &$param) {
        if ($tableDefs === null || $this->isAssoc($tableDefs)) {
            if ($values === self::NULL) {
                // the type is calculated automatically. It could fails and it doesn't work with blob^
                reset($tableDefs);
                $firstKey = key($tableDefs);

                $hasDelimiter = strpos($firstKey, $this->database_delimiter0) === false ? false : true;

                foreach ($tableDefs as $k => $v) {

                    if ($hasDelimiter) {
                        if ($colT === null) {
                            $col[] = "$k=?";
                        } else {
                            $col[] = "$k";
                            $colT[] = '?';
                        }
                    } else {
                        if ($colT === null) {
                            $col[] = $this->addQuote($k) . '=?';
                        } else {
                            $col[] = $this->addQuote($k);
                            $colT[] = '?';
                        }
                    }
                    $vt = $this->getType($v);
                    $param[] = $vt;
                    $param[] = $v;
                }

            } else {
                if ($tableDefs === null) {
                    $tableDefs = $values;
                    foreach ($tableDefs as $k => $v) {
                        $tableDefs[$k] = 's';
                    }
                }
                // it uses two associative array, one for the type and another for the value
                foreach ($tableDefs as $k => $v) {
                    if ($colT === null) {
                        $col[] = "{$this->database_delimiter0}$k{$this->database_delimiter1}=?";
                    } else {
                        $col[] = "{$this->database_delimiter0}$k{$this->database_delimiter1}";
                        $colT[] = '?';
                    }

                    $param[] = $v;
                    $param[] = @$values[$k];
                }
            }
        } else {
            if ($values === self::NULL) {
                // it uses a single list, the first value is the column, the second value
                // is the type and the third is the value

                for ($i = 0; $i < count($tableDefs); $i += 3) {
                    if ($colT === null) {
                        $col[] = "{$this->database_delimiter0}" . $tableDefs[$i] . "{$this->database_delimiter1}=?";
                    } else {
                        $col[] = $tableDefs[$i];
                        $colT[] = '?';
                    }
                    $param[] = $tableDefs[$i + 1];
                    $param[] = $tableDefs[$i + 2];
                }
            } else {
                // it uses two list, the first value of the first list is the column, the second value is the type
                // , the second list only contains values.
                for ($i = 0; $i < count($tableDefs); $i += 2) {
                    if ($colT === null) {
                        $col[] = "{$this->database_delimiter0}" . $tableDefs[$i] . "{$this->database_delimiter1}=?";
                    } else {
                        $col[] = $tableDefs[$i];
                        $colT[] = '?';
                    }
                    $param[] = $tableDefs[$i + 1];
                    $param[] = $values[$i / 2];
                }
            }
        }
    }

    /**
     * It returns true if the array is an associative array.  False otherwise.<br>
     * <b>Example:</b><br>
     * isAssoc(['a1'=>1,'a2'=>2]); // true<br/>
     * isAssoc(['a1','a2']); // false<br/>
     * isAssoc('aaa'); isAssoc(null); // false<br/>
     *
     * @param mixed $array
     *
     * @return bool
     */
    private function isAssoc($array) {
        if ($array === null) {
            return false;
        }
        if (!is_array($array)) {
            return false;
        }
        return (array_values($array) !== $array);
    }

    private function addQuote($txt) {
        if (strpos($txt, $this->database_delimiter0) === false) {
            $quoted = $this->database_delimiter0 . $txt . $this->database_delimiter1;
            $quoted = str_replace('.', $this->database_delimiter1 . '.' . $this->database_delimiter0, $quoted);
            return $quoted;
        } else {
            return $txt;
        }
    }

    /**
     * @param mixed $v Variable
     *
     * @return int=[PDO::PARAM_STR,PDO::PARAM_INT,PDO::PARAM_BOOL][$i]
     * @test equals PDO::PARAM_STR,(20.3)
     * @test equals PDO::PARAM_STR,('hello')
     */
    private function getType(&$v) {
        switch (1) {
            case ($v === null):
                $vt = PDO::PARAM_STR;
                break;
            case (is_double($v)):
                $vt = PDO::PARAM_STR;
                break;
            case (is_numeric($v)):
                $vt = PDO::PARAM_INT;
                break;
            case (is_bool($v)):

                $vt = PDO::PARAM_INT;
                $v = ($v) ? 1 : 0;
                break;
            case (is_object($v) && get_class($v) == 'DateTime'):
                $vt = PDO::PARAM_STR;
                $v = PdoOne::dateTimePHP2Sql($v);
                break;
            default:
                $vt = PDO::PARAM_STR;
        }
        return $vt;
    }

    /**
     * It groups by a condition.<br>
     * <b>Example:</b><br>
     * ->select('col1,count(*)')->from('table')->group('col1')->toList();
     *
     * @param string $sql Input SQL query
     *
     * @return PdoOne
     * @test InstanceOf PdoOne::class,this('fieldgroup')
     */
    public function group($sql) {
        $this->group = ($sql) ? ' group by ' . $sql : '';
        return $this;
    }

    /**
     * It adds a having to the query builder.
     * <br><b>Example</b>:<br>
     *      select('*')->from('table')->group('col')->having('field=2')
     *      having( ['field'=>20] ) // associative array with automatic type
     *      having( ['field'=>['i',20]] ) // associative array with type defined
     *      having( ['field',20] ) // array automatic type
     *      having(['field',['i',20]] ) // array type defined
     *      having('field=20') // literal value
     *      having('field=?',[20]) // automatic type
     *      having('field',[20]) // automatic type (it's the same than where('field=?',[20])
     *      having('field=?', ['i',20] ) // type(i,d,s,b) defined
     *      having('field=?,field2=?', ['i',20,'s','hello'] )
     *
     * @param string|array $sql
     * @param array|mixed  $param
     *
     * @return PdoOne
     * @see  http://php.net/manual/en/mysqli-stmt.bind-param.php for types
     * @test InstanceOf PdoOne::class,this('field1=?,field2=?',['i',20,'s','hello'])
     */
    public function having($sql, $param = self::NULL) {
        return $this->where($sql, $param, true);
    }

    /**
     * <b>Example:</b><br>
     *      where( ['field'=>20] ) // associative array with automatic type
     *      where( ['field'=>['i',20]] ) // associative array with type defined
     *      where( ['field',20] ) // array automatic type
     *      where (['field',['i',20]] ) // array type defined
     *      where('field=20') // literal value
     *      where('field=?',[20]) // automatic type
     *      where('field',[20]) // automatic type (it's the same than where('field=?',[20])
     *      where('field=?', ['i',20] ) // type(i,d,s,b) defined
     *      where('field=?,field2=?', ['i',20,'s','hello'] )
     *      where('field=:field,field2=:field2', ['field'=>'hello','field2'=>'world'] ) // associative array as value
     *
     * @param string|array $sql      Input SQL query or associative/indexed array
     * @param array|mixed  $param    Associative or indexed array with the conditions.
     * @param bool         $isHaving if true then it is a HAVING sql commando instead of a WHERE.
     *
     * @return PdoOne
     * @see  http://php.net/manual/en/mysqli-stmt.bind-param.php for types
     * @test InstanceOf PdoOne::class,this('field1=?,field2=?',['i',20,'s','hello'])
     */
    public function where($sql, $param = self::NULL, $isHaving = false) {
        if (is_string($sql)) {
            if ($param === self::NULL) {
                if ($isHaving) {
                    $this->having[] = $sql;
                } else {
                    $this->where[] = $sql;
                }
                return $this;
            }
            switch (true) {
                case $this->isAssoc($param):
                    $this->whereParamAssoc = $param;
                    $this->whereParamType = array();
                    $this->whereParamValue = array();
                    $this->whereCounter = 0;
                    break;
                case !is_array($param):
                    if (strpos($sql, '?') === false) {
                        $sql .= '=?';
                    } // transform 'condition' to 'condition=?'
                    $this->whereParamType[] = $this->getType($param);
                    $this->whereParamValue['i_' . $this->whereCounter] = $param;
                    $this->whereCounter++;
                    break;
                case count($param) == 1:
                    $this->whereParamType[] = $this->getType($param[0]);
                    $this->whereParamValue['i_' . $this->whereCounter] = $param[0];
                    $this->whereCounter++;
                    break;
                default:
                    for ($i = 0; $i < count($param); $i += 2) {
                        $this->whereParamType[] = $param[$i];
                        $this->whereParamValue['i_' . $this->whereCounter] = $param[$i + 1];
                        $this->whereCounter++;
                    }
            }
            if ($isHaving) {
                $this->having[] = $sql;
            } else {
                $this->where[] = $sql;
            }

        } else {
            $col = array();
            $colT = array();
            $p = array();
            $this->constructParam($sql, $param, $col, $colT, $p);

            foreach ($col as $k => $c) {
                //$c=$this->database_delimiter0.str_replace('.',"{$this->database_delimiter0}.{$this->database_delimiter0}",$c).$this->database_delimiter1;
                if ($isHaving) {
                    $this->having[] = "$c=?";
                } else {
                    $this->where[] = "$c=?";
                }
                $this->whereParamType[] = $p[$k * 2];
                $this->whereParamValue['i_' . $this->whereCounter] = $p[$k * 2 + 1];
                $this->whereCounter++;
            }
        }
        return $this;
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
     * @return PdoOne
     * @test InstanceOf PdoOne::class,this('name desc')
     */
    public function order($sql) {

        $this->order = ($sql) ? ' order by ' . $sql : '';
        return $this;
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
     * @return PdoOne
     * @throws Exception
     * @test InstanceOf PdoOne::class,this('1,10')
     */
    public function limit($sql) {
        switch ($this->databaseType) {
            case 'mysql':
                $this->limit = ($sql) ? ' limit ' . $sql : '';
                break;
            case 'sqlsrv':
                if (!$this->order) {
                    $this->throwError("limit without a sort", "");
                }
                if (strpos($sql, ',')) {
                    $arr = explode(',', $sql);
                    $this->limit = " OFFSET {$arr[0]} ROWS FETCH NEXT {$arr[1]} ROWS ONLY";
                } else {
                    $this->limit = " OFFSET 0 ROWS FETCH NEXT $sql ROWS ONLY";
                }
                break;
            case 'test':
                if (!$this->order) {
                    $this->throwError("limit without a sort", "");
                }
                if (strpos($sql, ',')) {
                    $arr = explode(',', $sql);
                    $this->limit = " OFFSET {$arr[0]} ROWS FETCH NEXT {$arr[1]} ROWS ONLY";
                } else {
                    $this->limit = " OFFSET 0 ROWS FETCH NEXT $sql ROWS ONLY";
                }
                break;
            default:
                trigger_error("database not defined or supported {$this->databaseType}");
        }

        return $this;
    }

    /**
     * Adds a distinct to the query. The value is ignored if the select() is written complete.<br>
     * <pre>
     *      ->select("*")->distinct() // works
     *      ->select("select *")->distinct() // distinct is ignored.
     *</pre>
     *
     * @param string $sql Input SQL query
     *
     * @return PdoOne
     * @test InstanceOf PdoOne::class,this()
     */
    public function distinct($sql = 'distinct') {
        $this->distinct = ($sql) ? $sql . ' ' : '';
        return $this;
    }

    /**
     * It returns an declarative array of rows.<br>
     * Example:
     * <pre>
     * select('select id,name from table')->toList() // [['id'=>'1','name'='john'],['id'=>'2','name'=>'anna']]
     * </pre>
     *
     * @param int $pdoMode (optional) By default is PDO::FETCH_ASSOC
     *
     * @return array|bool
     * @throws Exception
     */
    public function toList($pdoMode = PDO::FETCH_ASSOC) {

        return $this->runGen(true, $pdoMode);
    }

    /**
     * Run builder query and returns a PDOStatement.
     *
     * @param bool $returnArray true=return an array. False return a PDOStatement
     * @param int  $extraMode   PDO::FETCH_ASSOC,PDO::FETCH_BOTH,PDO::FETCH_NUM,etc.
     *                          By default it returns $extraMode=PDO::FETCH_ASSOC
     *
     * @return bool|PDOStatement|array
     * @throws Exception
     */
    public function runGen($returnArray = true, $extraMode = PDO::FETCH_ASSOC) {
        $sql = $this->sqlGen();
        /** @var PDOStatement $stmt */
        $stmt = $this->prepare($sql);
        if ($stmt === null) {
            return false;
        }
        $values = array_values($this->whereParamValue);
        if (count($this->whereParamType)) {
            $counter = 0;
            $reval = true;
            foreach ($this->whereParamType as $k => $v) {
                $counter++;
                $typeP = $this->stringToPdoParam($this->whereParamType[$k]);
                $reval = $reval
                    && $stmt->bindParam($counter
                        , $values[$k]
                        , $typeP);
            }

            if (!$reval) {
                $this->throwError("Error in bind", "",
                    "type: " . json_encode($this->whereParamType) . " values:" . json_encode($values));
                return false;
            }

        }
        $this->runQuery($stmt);
        $this->builderReset();
        if ($returnArray) {
            $r = ($stmt->columnCount() > 0) ? $stmt->fetchAll($extraMode) : array();
            $stmt = null; // close
            return $r;
        } else {
            return $stmt;
        }
    }

    /**
     * Generates the sql (script). It doesn't run or execute the query.
     *
     * @param bool $resetStack if true then it reset all the values of the stack, including parameters.
     *
     * @return string
     */
    public function sqlGen($resetStack = false) {
        if (stripos($this->select, 'select') !== false) {
            // is it a full query? ->select=select * ..." instead of ->select=*
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
        if (!in_array('where', $words)) {
            if (count($this->where)) {
                if (!in_array('where', $words)) {
                    $where = ' where ' . implode(' and ', $this->where);
                } else {
                    $where = implode(' and ', $this->where);
                }
            } else {
                $where = '';
            }
        } else {
            $where = '';
        }
        if (count($this->having)) {
            $having = ' having ' . implode(' and ', $this->having);
        } else {
            $having = '';
        }

        $sql = $sql . $where . $this->group . $having . $this->order . $this->limit;

        if ($resetStack) {
            $this->builderReset();
        }
        return $sql;
    }

    /**
     * It returns an array of simple columns (not declarative). It uses the first column<br>
     * <b>Example:</b><br>
     * <pre>
     * select('select id from table')->toListSimple() // ['1','2','3','4']
     * </pre>
     *
     * @return array|bool
     * @throws Exception
     */
    public function toListSimple() {
        return $this->runGen(true, PDO::FETCH_COLUMN);
    }

    /**
     * It returns a PDOStatement.
     *
     * @return PDOStatement
     * @throws Exception
     */
    public function toResult() {
        return $this->runGen(false);
    }

    /**
     * It returns the first row.  If there is not row then it returns empty.
     * <br><b>Example</b>:<br>
     * <pre>
     *      $con->select('*')->from('table')->first(); // select * from table (first value)
     * </pre>
     *
     * @return array|null
     * @throws Exception
     */
    public function first() {
        /** @var PDOStatement $rows */
        $rows = $this->runGen(false);
        if ($rows === false) {
            return null;
        }
        if (!$rows->columnCount()) {
            return null;
        }
        while ($row = $rows->fetch(PDO::FETCH_ASSOC)) {
            $rows = null;
            return $row;
        }
        return null;
    }

    /**
     * Executes the query, and returns the first column of the first row in the result set returned by the query.
     * Additional columns or rows are ignored.
     * If value is found then it returns null.
     * <br><b>Example</b>:<br>
     * <pre>
     *      $con->select('*')->from('table')->firstScalar(); // select * from table (first scalar value)
     * </pre>
     *
     * @param string|null $colName If it's null then it uses the first column.
     *
     * @return mixed|null
     * @throws Exception
     */
    public function firstScalar($colName = null) {
        /** @var PDOStatement $rows */
        $rows = $this->runGen(false);
        if ($rows === false) {
            return null;
        }
        if (!$rows->columnCount()) {
            return null;
        }
        while ($row = $rows->fetch(PDO::FETCH_ASSOC)) {
            $rows = null;
            if ($colName === null) {
                return reset($row); // first column of the first row
            } else {
                return $row[$colName];
            }
        }
        return null;
    }

    /**
     * Returns the last row. It's not recommended. Use instead first() and change the order.
     * <br><b>Example</b>:<br>
     * <pre>
     *      $con->select('*')->from('table')->last(); // select * from table (last scalar value)
     * </pre>
     *
     * @return array|null
     * @throws Exception
     * @see \eftec\PdoOne::first
     */
    public function last() {
        /** @var PDOStatement $rows */
        $rows = $this->runGen(false);
        if ($rows === false) {
            return null;
        }
        if (!$rows->columnCount()) {
            return null;
        }
        /** @noinspection PhpStatementHasEmptyBodyInspection */
        while ($row = $rows->fetch(PDO::FETCH_ASSOC)) {
        }
        return $row;
    }

    /**
     * Generate and run an update in the database.
     * <br><b>Example</b>:<br>
     * <pre>
     *      update('table',['col1','i',10,'col2','s','hello world'],['where','i',10]);
     *      update('table',['col1','i','col2','s'],[10,'hello world'],['where','i'],[10]);
     *      ->from("producttype")
     *          ->set("name=?",['s','Captain-Crunch'])
     *          ->where('idproducttype=?',['i',6])
     *          ->update();
     * </pre>
     *
     * @param string       $tableName The name of the table.
     * @param string[]     $tableDef
     * @param string[]|int $values
     * @param string[]     $tableDefWhere
     * @param string[]|int $valueWhere
     *
     * @return mixed
     * @throws Exception
     */
    public function update(
        $tableName = null,
        $tableDef = null,
        $values = self::NULL,
        $tableDefWhere = null,
        $valueWhere = self::NULL
    ) {
        if ($tableName === null) {
            // using builder. from()->set()->where()->update()
            $errorCause = '';
            if ($this->from == "") {
                $errorCause = "you can't execute an empty update() without a from()";
            }
            if (count($this->set) === 0) {
                $errorCause = "you can't execute an empty update() without a set()";
            }
            if (count($this->where) === 0) {
                $errorCause = "you can't execute an empty update() without a where()";
            }
            if ($errorCause) {
                $this->throwError($errorCause, "");
                return false;
            }
            $sql = "update {$this->database_delimiter0}" . $this->from . "{$this->database_delimiter1} "
                . $this->constructSet() . ' ' . $this->constructWhere();
            $param = [];
            for ($i = 0; $i < count($this->whereParamType); $i++) {
                $param[] = $this->whereParamType[$i];
                $param[] = $this->whereParamValue['i_' . $i];
            }
            $this->builderReset();
            $stmt = $this->runRawQuery($sql, $param, true);
            return $this->affected_rows($stmt);
        } else {
            $col = [];
            $colT = null;
            $colWhere = [];
            $param = [];
            if ($tableDefWhere === null) {
                $this->constructParam($tableDef, self::NULL, $col, $colT, $param);
                $this->constructParam($values, self::NULL, $colWhere, $colT, $param);
            } else {
                $this->constructParam($tableDef, $values, $col, $colT, $param);
                $this->constructParam($tableDefWhere, $valueWhere, $colWhere, $colT, $param);
            }
            $sql = "update {$this->database_delimiter0}$tableName{$this->database_delimiter1} set " . implode(',', $col)
                . " where " . implode(' and ', $colWhere);
            $this->builderReset();
            $this->runRawQuery($sql, $param);
            return $this->insert_id();
        }
    }

    /**
     * @return string
     */
    private function constructSet() {
        if (count($this->set)) {
            $where = " set " . implode(',', $this->set);
        } else {
            $where = '';
        }
        return $where;
    }

    /**
     * @return string
     */
    private function constructWhere() {
        if (count($this->where)) {
            $where = ' where ' . implode(' and ', $this->where);
        } else {
            $where = '';
        }
        return $where;
    }

    /**
     * Returns the number of affected rows.
     *
     * @param PDOStatement|null|bool $stmt
     *
     * @return mixed
     */
    public function affected_rows($stmt = null) {
        if ($stmt instanceof PDOStatement) {
            if (!$this->isOpen) {
                return $stmt->rowCount();
            }
        }
        return $this->affected_rows; // returns previous calculated information
    }

    /**
     * Returns the last inserted identity.
     *
     * @return mixed
     */
    public function insert_id() {
        if (!$this->isOpen) {
            return -1;
        }
        return $this->conn1->lastInsertId();
    }

    /**
     * It allows to insert a declarative array. It uses "s" (string) as filetype.
     * <p>Example: ->insertObject('table',['field1'=>1,'field2'=>'aaa']);
     *
     * @param string $tableName     The name of the table.
     * @param array  $object        associative array with the colums and values
     * @param array  $excludeColumn (optional) columns to exclude. Example ['col1','col2']
     *
     * @return mixed
     * @throws Exception
     */
    public function insertObject($tableName, $object, $excludeColumn = []) {
        $tabledef = [];
        foreach ($object as $k => $field) {
            if (!in_array($k, $excludeColumn, true)) { // avoid $k=0 is always valid for numeric columns
                $tabledef[$k] = 's';
            }
        }
        foreach ($excludeColumn as $ex) {
            unset($object[$ex]);
        }
        return $this->insert($tableName, $tabledef, $object);
    }
    //</editor-fold>

    //<editor-fold desc="Encryption functions" defaultstate="collapsed" >

    /**
     * Generates and execute an insert command. Example:
     * Example:
     *      insert('table',['col1','i',10,'col2','s','hello world']); // ternary colname,type,value,...
     *      insert('table',null,['col1'=>10,'col2'=>'hello world']); // definition is obtained from the values
     *      insert('table',['col1'=>10,'col2'=>'hello world']); // definition is obtained from the values
     *      insert('table',['col1','i','col2','s'],[10,'hello world']); // definition (binary) and value
     *      insert('table',['col1'=>'i','col2'=>'s'],['col1'=>10,'col2'=>'hello world']); // definition declarative array)
     *      ->set(['col1','i',10,'col2','s','hello world'])
     *          ->from('table')
     *          ->insert();
     *
     * @param string        $tableName
     * @param string[]|null $tableDef
     * @param string[]|int  $values
     *
     * @return mixed
     * @throws Exception
     */
    public function insert($tableName = null, $tableDef = null, $values = self::NULL) {
        if ($tableName === null) {
            // using builder. from()->set()->insert()
            $errorCause = '';
            if ($this->from == "") {
                $errorCause = "you can't execute an empty insert() without a from()";
            }
            if (count($this->set) === 0) {
                $errorCause = "you can't execute an empty insert() without a set()";
            }
            if ($errorCause) {
                $this->throwError($errorCause, "");
                return false;
            }
            $sql
                = /** @lang text */
                "insert into {$this->database_delimiter0}" . $this->from . "{$this->database_delimiter1} "
                . $this->constructInsert();
            $param = [];

            for ($i = 0; $i < count($this->whereParamType); $i++) {
                $param[] = $this->whereParamType[$i];
                $param[] = $this->whereParamValue['i_' . $i];
            }
            $this->builderReset();
            $this->runRawQuery($sql, $param, true);
            return $this->insert_id();
        } else {
            $col = [];
            $colT = [];
            $param = [];
            $this->constructParam($tableDef, $values, $col, $colT, $param);
            $sql = "insert into {$this->database_delimiter0}$tableName{$this->database_delimiter1} (" . implode(',',
                    $col)
                . ") values(" . implode(',', $colT) . ")";
            $this->builderReset();

            $this->runRawQuery($sql, $param);
            return $this->insert_id();
        }
    }

    /**
     * @return string
     */
    private function constructInsert() {
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
                $where = "(" . implode(',', $arr) . ') values (' . implode(',', $val) . ')';
            } else {
                // set('(a,b,c) values(?,?,?)',[])
                $where = $first;
            }
        } else {
            $where = '';
        }
        return $where;
    }

    /**
     * Delete a row(s) if they exists.
     * Example:
     *      delete('table',['col1','i',10,'col2','s','hello world']);
     *      delete('table',['col1','i','col2','s'],[10,'hello world']);
     *      $db->from('table')
     *          ->where('..')
     *          ->delete() // running on a chain
     *
     * @param string       $tableName
     * @param string[]     $tableDefWhere
     * @param string[]|int $valueWhere
     *
     * @return mixed
     * @throws Exception
     */
    public function delete($tableName = null, $tableDefWhere = null, $valueWhere = self::NULL) {
        if ($tableName === null) {
            // using builder. from()->where()->delete()
            $errorCause = '';
            if ($this->from == "") {
                $errorCause = "you can't execute an empty delete() without a from()";
            }
            if (count($this->where) === 0) {
                $errorCause = "you can't execute an empty delete() without a where()";
            }
            if ($errorCause) {
                $this->throwError($errorCause, "");
                return false;
            }
            $sql = "delete from {$this->database_delimiter0}" . $this->from . "{$this->database_delimiter1} "
                . $this->constructWhere();
            $param = [];
            for ($i = 0; $i < count($this->whereParamType); $i++) {
                $param[] = $this->whereParamType[$i];
                $param[] = $this->whereParamValue['i_' . $i];
            }
            $this->builderReset();
            $stmt = $this->runRawQuery($sql, $param, true);
            return $this->affected_rows($stmt);
        } else {
            // using table/tabldefwhere/valuewhere
            $colWhere = [];
            $colT = null;
            $param = [];
            $this->constructParam($tableDefWhere, $valueWhere, $colWhere, $colT, $param);
            $sql = "delete from {$this->database_delimiter0}$tableName{$this->database_delimiter1} where "
                . implode(' and ', $colWhere);
            $this->builderReset();
            $stmt = $this->runRawQuery($sql, $param, true);
            return $this->affected_rows($stmt);
        }
    }
    //</editor-fold>

    //<editor-fold desc="Log functions" defaultstate="collapsed" >

    /**
     * @param string|int $password  <p>Use a integer if the method is INTEGER</p>
     * @param string     $salt      <p>Salt is not used by SIMPLE or INTEGER</p>
     * @param string     $encMethod <p>Example : AES-256-CTR See http://php.net/manual/en/function.openssl-get-cipher-methods.php </p>
     *                              <p>if SIMPLE then the encryption is simplified (generates a short result)</p>
     *                              <p>if INTEGER then the encryption is even simple (generates an integer)</p>
     *
     * @throws Exception
     * @test void this('123','somesalt','AES-128-CTR')
     */
    public function setEncryption($password, $salt, $encMethod) {

        if (!extension_loaded('openssl')) {
            $this->encryption->encEnabled = false;
            $this->throwError("OpenSSL not loaded, encryption disabled", "");
        } else {
            $this->encryption->encEnabled = true;
            $this->encryption->setEncryption($password, $salt, $encMethod);
        }
    }

    /**
     * Wrapper of PdoOneEncryption->encrypt
     *
     * @param $data
     *
     * @return bool|string
     * @see \eftec\PdoOneEncryption::encrypt
     */

    public function encrypt($data) {
        return $this->encryption->encrypt($data);
    }

    /**
     * Wrapper of PdoOneEncryption->decrypt
     *
     * @param $data
     *
     * @return bool|string
     * @see \eftec\PdoOneEncryption::decrypt
     */
    public function decrypt($data) {
        return $this->encryption->decrypt($data);
    }

    /**
     * Returns the last error.
     *
     * @return string
     */
    public function lastError() {
        if (!$this->isOpen) {
            return "It's not connected to the database";
        }
        return $this->conn1->errorInfo()[2];
    }

    //</editor-fold>

}
