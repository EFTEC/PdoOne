<?php
/** @noinspection SqlWithoutWhere */
/** @noinspection SqlResolve */
/** @noinspection SqlNoDataSourceInspection */

namespace eftec;

use DateTime;
use Exception;

use PDO;
use PDOStatement;


/**
 * Class PdoOne
 * This class wrappes PDO but it could be used for another framework/library.
 * @version 1.00 20190521
 * @package eftec
 * @author Jorge Castro Castillo
 * @copyright (c) Jorge Castro C. MIT License  https://github.com/EFTEC/PdoOne
 * @see https://github.com/EFTEC/PdoOne
 */
class PdoOne
{

	const NULL=PHP_INT_MAX;
	/** @var string|null Static date (when the date is empty) */
	static $dateEpoch = "2000-01-01 00:00:00.00000";
	//<editor-fold desc="server fields">
	var $nodeId=1;
	var $tableSequence='snowflake';
	/**
	 * it is used to generate an unpredictable number by flipping positions. It must be changed.
	 * $mask0 and $mask1 must have the same number of elements.
	 * Each value must be from 0..17 (the size of snowflake, if it is used with snowflake)
	 * $masks0=[0] and masks1[3] means that 01234->31204
	 * number 14,15,16,17 ($masks1) has the highest entrophy
	 * @var array
	 * @see \eftec\PdoOne::getUnpredictable
	 */
	var $masks0=[2,0,4,5];
	var $masks1=[16,13,12,11];

	/** @var PdoOneEncryption */
	var $encryption=null;
	/** @var string=['mysql','sqlsrv','oracle'][$i] */
	var $database;
	var $database_delimiter0='`';
	var $database_delimiter1='`';
	/** @var string server ip. Ex. 127.0.0.1 */
	var $server;
	var $user;
	var $pwd;
	var $db;
	var $charset='utf8';
	/** @var bool It is true if the database is connected otherwise,it's false */
	var $isOpen=false;
	/** @var bool If true (default), then it throws an error if happens an error. If false, then the execution continues */
	var $throwOnError=true;

	/** @var  \PDO */
	var $conn1;
	//</editor-fold>
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
	public $logLevel=0;

	/** @var string last query executed */
	var $lastQuery;
	var $lastParam=[];




	/**
	 * Text date format
	 * @var string
	 * @see https://secure.php.net/manual/en/function.date.php
	 */
	public static $dateFormat = 'Y-m-d';
	/**
	 * Text datetime format
	 * @var string
	 * @see https://secure.php.net/manual/en/function.date.php
	 */
	public static $dateTimeFormat = 'Y-m-d\TH:i:s\Z';
	/**
	 * Text datetime format with microseconds
	 * @var string
	 * @see https://secure.php.net/manual/en/function.date.php
	 */
	public static $dateTimeMicroFormat = 'Y-m-d\TH:i:s.u\Z';

	private $genSqlFields=false;
	var $lastSqlFields='';

	//<editor-fold desc="query builder fields">
	private $select = '';
	private $from = '';
	/** @var array */
	private $where = array();
	/** @var int[] PDO::PARAM_STR,PDO::PARAM_INT,PDO::PARAM_BOOL */
	private $whereParamType = array();
	private $whereCounter = 0;
	/** @var array  */
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
	 * DaoOne constructor.  It doesn't connect to the database.
	 * @param string database ['mysql','sqlsrv','oracle'][$i]
	 * @param string $server server ip. Ex. 127.0.0.1
	 * @param string $user Ex. root
	 * @param string $pwd Ex. 12345
	 * @param string $db Ex. mybase
	 * @param string $logFile Optional  log file. Example c:\\temp\log.log
	 * @param string $charset Example utf8mb4
	 * @param int $nodeId It is the id of the node (server). It is used for sequence. Up to 4096
	 * @see PdoOne::connect()
	 */
	public function __construct($database,$server, $user, $pwd, $db, $logFile = "",$charset=null,$nodeId=1)
	{
		$this->database=$database;
		switch ($this->database) {
			case 'mysql':
				$this->database_delimiter0='`';
				$this->database_delimiter1='`';
				$charset=($charset==null)?'utf8':$charset;
				break;
			case 'sqlsrv':
				$this->database_delimiter0='[';
				$this->database_delimiter1=']';
				break;
				
		}
		$this->server = $server;
		$this->user = $user;
		$this->pwd = $pwd;
		$this->db = $db;
		$this->logFile = $logFile;
		$this->charset=$charset;
		$this->nodeId=$nodeId;
		$this->encryption=new PdoOneEncryption($pwd,$user.$pwd); // by default, the encryption uses the same password than the db.
	}

	/**
	 * It changes default database.
	 * @param $dbName
	 * @test void this('travisdb')
	 */
	public function db($dbName) {
		if (!$this->isOpen) return;
		$this->db=$dbName;
		$this->conn1->query('use '.$dbName);
	}



	/**
	 * returns if the database is in read-only mode or not.
	 * @return bool
	 * @test equals false,this(),'the database is read only'
	 */
	public function readonly()
	{
		return $this->readonly;
	}

	/**
	 * Connects to the database.
	 * @param bool $failIfConnected  true=it throw an error if it's connected, otherwise it does nothing
	 * @throws Exception
	 * @test exception this(false)
	 */
	public function connect($failIfConnected=true)
	{
		//mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
		if ($this->isOpen) {
			if (!$failIfConnected) return; // it's already connected.
			$this->throwError("Already connected","");
		}
		try {
			if ($this->logLevel>=2) {
				$this->storeInfo("connecting to {$this->server} {$this->user}/*** {$this->db}");
			}
			$cs=($this->charset!='')?';charset='.$this->charset:'';
			switch ($this->database) {
				case 'mysql':
					$this->conn1 = new PDO("{$this->database}:host={$this->server};dbname={$this->db}{$cs}", $this->user, $this->pwd);
					break;
				case 'sqlsrv':
					$this->conn1 = new PDO("{$this->database}:server={$this->server};database={$this->db}{$cs}", $this->user, $this->pwd);
					break;
				default:
					throw new Exception("database not defined");
					break;
			}
			
			$this->conn1->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
	
			$this->isOpen=true;
		} catch (Exception $ex) {
			$this->isOpen=false;
			$this->throwError("Failed to connect to {$this->database}", $ex->getMessage());
		}

	}

	/**
	 * Alias of DaoOne::connect()
	 * @param bool $failIfConnected
	 * @throws Exception
	 * @test exception this(false)
	 *@see PdoOne::connect()
	 */
	public function open($failIfConnected=true) {
		$this->connect($failIfConnected);
	}

	/**
	 * It closes the connection
	 * @test void this()
	 */
	public function close() {
		$this->isOpen=false;
		if ($this->conn1===null) return; // its already close

		@$this->conn1=null;
	}

	/**
	 * Injects a Message Container.
	 * @return MessageList|null
	 * @test equals null,this(),'this is not a message container'
	 */
	public function getMessages() {
		if (function_exists('messages')) {
			return messages();
		}
		return null;
	}

	/**
	 * Run many  unprepared query separated by ;
	 * @param $listSql
	 * @param bool $continueOnError
	 * @return bool
	 * @throws Exception
	 */
	public function runMultipleRawQuery($listSql, $continueOnError = false)
	{
		if (!$this->isOpen) { $this->throwError("RMRQ: It's not connected to the database",""); return false; }
		$arr = explode(';', $listSql);
		$ok = true;
		foreach ($arr as $rawSql) {
			if(trim($rawSql)!='') {
				if ($this->readonly) {
					if (stripos($rawSql, 'insert ') === 0 || stripos($rawSql, 'update ') === 0 || stripos($rawSql, 'delete ') === 0) {
						// we aren't checking SQL-DCL queries. Also, "insert into" is stopped but "  insert into" not.
						$ok = false;
						if (!$continueOnError) {
							$this->throwError("Database is in READ ONLY MODE","");
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
						$this->throwError("Unable to run raw query",$this->lastQuery);
					}
				}
			}
		}
		return $ok;
	}

	/**
	 * It returns the next sequence.
	 * It gets a collision free number if we don't do more than one operation
	 * every 0.0001 seconds.
	 * But, if we do 2 or more operations per seconds then, it adds a sequence number from
	 * 0 to 4095
	 * So, the limit of this function is 4096 operations per 0.0001 second.
	 *
	 * @see \eftec\PdoOne::getSequencePHP It's the same but it uses less resources but lacks of a sequence.
	 * @param bool $asFloat
	 * @param bool $unpredictable
	 * @return string . Example string(19) "3639032938181434317"
	 * @throws Exception
	 */
	public function getSequence($asFloat=false,$unpredictable=false) {
		$sql="select next_{$this->tableSequence}({$this->nodeId}) id";
		$r=$this->runRawQuery($sql,null,true);
		if ($unpredictable) {
			if (PHP_INT_SIZE == 4) {
				return $this->encryption->encryptSimple($r[0]['id']);
			} else {
				// $r is always a 32 bit number so it will fail in PHP 32bits
				return $this->encryption->encryptInteger($r[0]['id']);
			}
		}
		if($asFloat)
			return floatval($r[0]['id']);
		else
			return $r[0]['id'];
	}

	/**
	 * <p>This function returns an unique sequence<p>
	 * It ensures a collision free number only if we don't do more than one operation
	 * per 0.0001 second However,it also adds a pseudo random number (0-4095)
	 * so the chances of collision is 1/4095 (per two operations done every 0.0001 second).
	 * @param bool $unpredictable
	 * @return float
	 *@see \eftec\PdoOne::getSequence
	 */
	public function getSequencePHP($unpredictable=false) {
		$ms=microtime(true);
		//$ms=1000;
		$timestamp=(double)round($ms*1000);
		$rand=(fmod($ms,1)*1000000)%4096; // 4096= 2^12 It is the millionth of seconds
		$calc=(($timestamp-1459440000000)<<22) + ($this->nodeId<<12) + $rand;
		usleep(1);

		if ($unpredictable) {
			if (PHP_INT_SIZE == 4) {
				return '' . $this->encryption->encryptSimple($calc);
			} else {
				// $r is always a 32 bit number so it will fail in PHP 32bits
				return '' . $this->encryption->encryptInteger($calc);
			}
		}
		return ''.$calc;
	}



	/**
	 * It uses \eftec\DaoOne::$masks0 and \eftec\DaoOne::$masks1 to flip
	 * the number, so they are not as predictable.
	 * This function doesn't add entrophy. However, the generation of Snowflakes id
	 * (getSequence/getSequencePHP) generates its own entrophy. Also,
	 * both masks0[] and masks1[] adds an extra secrecy.
	 * @param $number
	 * @return mixed
	 */
	public function getUnpredictable($number) {
		$string="".$number;
		$maskSize=count($this->masks0);

		for($i=0;$i<$maskSize;$i++) {
			$init=$this->masks0[$i];
			$end=$this->masks1[$i];
			$tmp=$string[$end];
			$string=substr_replace($string,$string[$init],$end,1);
			$string=substr_replace($string,$tmp,$init,1);
		}
		return $string;
	}
	/**
	 * it is the inverse of \eftec\DaoOne::getUnpredictable
	 * @param $number
	 * @return mixed
	 * @see \eftec\PdoOne::$masks0
	 * @see \eftec\PdoOne::$masks1
	 */
	public function getUnpredictableInv($number) {
		$maskSize=count($this->masks0);
		for($i=$maskSize-1;$i>=0;$i--) {
			$init=$this->masks1[$i];
			$end=$this->masks0[$i];
			$tmp=$number[$end];
			$number=substr_replace($number,$number[$init],$end,1);
			$number=substr_replace($number,$tmp,$init,1);
		}
		return $number;
	}

	/**
	 * Create a t
	 * @param $tableName
	 * @param $definition
	 * @param null $primaryKey
	 * @param string $extra
	 * @return array|bool|\PDOStatement
	 * @throws Exception
	 */
	public function createTable($tableName,$definition,$primaryKey=null,$extra='') {
		switch ($this->database) {
			case 'mysql':
				$sql="CREATE TABLE `{$tableName}` (";
				foreach($definition as $key=>$type) {
					$sql.="`$key` $type,";
				}
				if ($primaryKey) {
					$sql.=" PRIMARY KEY(`$primaryKey`) ";
				} else {
					$sql=substr($sql,0,-1);
				}
				$sql.="$extra ) ENGINE=MyISAM DEFAULT CHARSET=".$this->charset;			
				break;
			case 'sqlsrv':
				$sql="set nocount on;
				CREATE TABLE [{$tableName}] (";
				foreach($definition as $key=>$type) {
					$sql.="[$key] $type,";
				}

				$sql.="$extra ) ON [PRIMARY]; ";

				if ($primaryKey) {
					$sql.="
						ALTER TABLE [$tableName] ADD CONSTRAINT
							PK_$tableName PRIMARY KEY CLUSTERED 
							(
							[$primaryKey]
							) ON [PRIMARY]
						";
				}
				break;
			default:
				throw new Exception("type not defined for create table");
		}
		return $this->runRawQuery($sql,null,true);
	}
	/**
	 * Create a table for a sequence
	 * @throws Exception
	 */
	public function createSequence() {
		switch ($this->database) {
			case 'mysql':
				$sql="CREATE TABLE `{$this->tableSequence}` (
				  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
				  `stub` char(1) NOT NULL DEFAULT '',
				  PRIMARY KEY (`id`),
				  UNIQUE KEY `stub` (`stub`)
				) ENGINE=MyISAM AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;
				-- insert the firsrt value
				INSERT INTO `{$this->tableSequence}` (`stub`) VALUES ('a');
				SET GLOBAL log_bin_trust_function_creators = 1;";
						$this->runMultipleRawQuery($sql);
						$sql="CREATE FUNCTION `next_{$this->tableSequence}`(node integer) RETURNS BIGINT(20)
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
				$sql="CREATE SEQUENCE [{$this->tableSequence}]
			    START WITH 1  
			    INCREMENT BY 1
			    GO";
				$sql.="create PROCEDURE next_{$this->tableSequence}
						@node int
					AS
					BEGIN
					
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
			default:
				throw new Exception("type not defined for create sequence");	
		}
		$this->runRawQuery($sql);
	}




	//<editor-fold desc="transaction functions">
	/**
	 * @return bool
	 * @test equals true,this()
	 * @posttest execution $this->daoOne->commit();
	 * @example examples/testdb.php 92,4
	 */
	public function startTransaction()
	{
		if ($this->transactionOpen || !$this->isOpen) return false;
		$this->transactionOpen = true;
		$this->conn1->beginTransaction();
		return true;
	}

	/**
	 * Commit and close a transaction
	 * @param bool $throw
	 * @return bool
	 * @throws Exception
	 * @test equals false,(false),'transaction is not open'
	 */
	public function commit($throw=true)
	{
		if (!$this->transactionOpen && $throw) { $this->throwError("Transaction not open to commit()",""); return false; }
		if (!$this->isOpen) { $this->throwError("It's not connected to the database",""); return false; }
		$this->transactionOpen = false;
		return @$this->conn1->commit();
	}

	/**
	 * Rollback and close a transaction
	 * @param bool $throw
	 * @return bool
	 * @throws Exception
	 * @test equals false,(false),'transaction is not open'
	 */
	public function rollback($throw=true)
	{
		if (!$this->transactionOpen && $throw) $this->throwError("Transaction not open  to rollback()","");
		if (!$this->isOpen) { $this->throwError("It's not connected to the database",""); return false; }
		$this->transactionOpen = false;
		return @$this->conn1->rollback();
	}
	//</editor-fold>


	//<editor-fold desc="Date functions" defaultstate="collapsed" >

	/**
	 * Conver date from php -> mysql
	 * It always returns a time (00:00:00 if time is empty). it could returns microseconds 2010-01-01 00:00:00.00000
	 * @param DateTime $date
	 * @return string
	 */
	public static function dateTimePHP2Sql($date)
	{
		// 31/01/2016 20:20:00 --> 2016-01-31 00:00
		if ($date == null) {
			return PdoOne::$dateEpoch;
		}
		if ($date->format("u")!='000000') {
			return $date->format('Y-m-d H:i:s.u');
		} else {
			return $date->format('Y-m-d H:i:s');
		}

	}

	/**
	 * Convert date from unix -> mysql
	 * @param integer $dateNum
	 * @return string
	 */
	public static function unixtime2Sql($dateNum)
	{
		// 31/01/2016 20:20:00 --> 2016-01-31 00:00
		if ($dateNum == null) {
			return PdoOne::$dateEpoch;
		}
		try {
			$date2 = new DateTime(date("Y-m-d H:i:s.u", $dateNum));
		} catch (Exception $e) {
			return PdoOne::$dateEpoch;
		}
		return $date2->format('Y-m-d H:i:s.u');
	}

	/**
	 * Convert date, from mysql -> php
	 * @param $sqlField
	 * @param bool $hasTime
	 * @return bool|DateTime|null
	 */
	public static function dateTimeSql2PHP($sqlField,&$hasTime=false)
	{
		// 3  2016-01-31 00:00:00 -> 01/01/2016 00:00:00
		// mysql always returns the date/datetime/timestmamp in ansi format.
		if ($sqlField === "" || $sqlField===null) {
			if (PdoOne::$dateEpoch===null) return null;
			return DateTime::createFromFormat('Y-m-d H:i:s.u',PdoOne::$dateEpoch);
		}
		if (strpos($sqlField, '.')) {
			// with date with time and microseconds
			$hasTime=true;
			return DateTime::createFromFormat('Y-m-d H:i:s.u', $sqlField);
		} else {
			if (strpos($sqlField, ':')) {
				// date with time
				$hasTime=true;
				return DateTime::createFromFormat('Y-m-d H:i:s', $sqlField);
			} else {
				// only date
				$hasTime=false;
				return DateTime::createFromFormat('Y-m-d', $sqlField);
			}
		}
	}

	/**
	 * Convert date, from mysql -> text (using a format pre-established)
	 * @param $sqlField
	 * @return string
	 */
	public static function dateSql2Text($sqlField)
	{
		$hasTime=false;
		$tmpDate = self::dateTimeSql2PHP($sqlField,$hasTime);
		if ($tmpDate===null) return null;
		if ($hasTime) {
			return $tmpDate->format((strpos($sqlField,'.')!==false) ? self::$dateTimeMicroFormat : self::$dateTimeFormat);
		}  else {
			return $tmpDate->format(self::$dateFormat);
		}
	}

	/**
	 * Convert date, from mysql -> text (using a format pre-established)
	 * @param $textDate
	 * @param bool $hasTime
	 * @return string
	 */
	public static function dateText2Sql($textDate, $hasTime=true)
	{
		$tmpFormat=(($hasTime)
			?(strpos($textDate,'.')===false
				?self::$dateTimeFormat
				:self::$dateTimeMicroFormat)
			: self::$dateFormat);
		$tmpDate = DateTime::createFromFormat($tmpFormat, $textDate);
		if(!$hasTime && $tmpDate) {
			$tmpDate->setTime(0,0,0);
		}
		return self::dateTimePHP2Sql($tmpDate); // it always returns a date with time. Mysql Ignores it.
	}

	/**
	 * Returns the current date(and time) in Text format.
	 * @param bool $hasTime
	 * @param bool $hasMicroseconds
	 * @return string
	 * @throws Exception
	 *@see PdoOne::$dateTimeFormat
	 */
	public static function dateTextNow($hasTime=true,$hasMicroseconds=false)
	{
		$tmpDate = new DateTime();
		if ($hasTime) {
			return $tmpDate->format(($hasMicroseconds!==false) ? self::$dateTimeMicroFormat : self::$dateTimeFormat);
		}  else {
			return $tmpDate->format(self::$dateFormat);
		}
	}
	public static function dateMysqlNow($hasTime=true,$hasMicroseconds=false)
	{
		try {
			$tmpDate = new DateTime();
		} catch (Exception $e) {
			$tmpDate=null;
		}
		if ($hasTime) {
			return $tmpDate->format(($hasMicroseconds!==false) ? 'Y-m-d H:i:s.u' : 'Y-m-d H:i:s');
		}  else {
			return $tmpDate->format('Y-m-d');
		}
	}
	//</editor-fold>

	//<editor-fold desc="Query Builder functions" defaultstate="collapsed" >
	/**
	 * It adds a select to the query builder.
	 * <br><b>Example</b>:<br>
	 * ->select("\*")->from('table') = <i>"select * from table"</i><br>
	 * ->select(['col1','col2'])->from('table') = <i>"select col1,col2 from table"</i><br>
	 * ->select('col1,col2')->from('table') = <i>"select col1,col2 from table"</i><br>
	 * ->select('select *')->from('table') = <i>"select * from table"</i><br>
	 * ->select('select * from table') = <i>"select * from table"</i><br>
	 * ->select('select * from table where id=1') = <i>"select * from table where id=1"</i><br>
	 *
	 * @param string|array $sql
	 * @return PdoOne
	 * @test InstanceOf DaoOne::class,this('select 1 from DUAL')
	 */
	public function select($sql)
	{
		if (is_array($sql)) {
			$this->select.= implode(', ',$sql);
		} else {
			if ($this->select==='') {
				$this->select= $sql;	
			} else {
				$this->select.=', '.$sql;
			}
			
		}
		return $this;
	}

	/**
	 * It generates an inner join
	 * Example:
	 *          join('tablejoin on t1.field=t2.field')
	 *          join('tablejoin','t1.field=t2.field')
	 * @param string $sql Example "tablejoin on table1.field=tablejoin.field"
	 * @param string $condition
	 * @return PdoOne
	 * @test InstanceOf DaoOne::class,this('tablejoin on t1.field=t2.field')
	 */
	public function join($sql,$condition='')
	{
		if ($this->from == '') return $this->from($sql);
		if ($condition!='') $sql="$sql on $condition";
		$this->from .= ($sql) ? " inner join $sql " : '';
		return $this;
	}

	/**
	 * Macro of join.
	 * Example:
	 *          innerjoin('tablejoin on t1.field=t2.field')
	 *          innerjoin('tablejoin tj on t1.field=t2.field')
	 *          innerjoin('tablejoin','t1.field=t2.field')
	 * @param $sql
	 * @param string $condition
	 * @return PdoOne
	 * @see \eftec\PdoOne::join
	 */
	public function innerjoin($sql,$condition='')
	{
		return $this->join($sql,$condition);
	}


	/**
	 * Example:
	 *      from('table')
	 *      from('table alias')
	 *      from('table1,table2')
	 *      from('table1 inner join table2 on table1.c=table2.c')
	 * @param $sql
	 * @return PdoOne
	 * @test InstanceOf DaoOne::class,this('table t1')
	 */
	public function from($sql)
	{
		$this->from = ($sql) ? $sql: '';
		return $this;
	}

	/**
	 * @param $sql
	 * @return PdoOne
	 * @test InstanceOf DaoOne::class,this('table2 on table1.t1=table2.t2')
	 */
	public function left($sql)
	{
		if ($this->from == '') return $this->from($sql);
		$this->from .= ($sql) ? " left join $sql" : '';
		return $this;
	}

	/**
	 * @param $sql
	 * @return PdoOne
	 * @test InstanceOf DaoOne::class,this('table2 on table1.t1=table2.t2')
	 */
	public function right($sql)
	{
		if ($this->from == '') return $this->from($sql);
		$this->from .= ($sql) ? " right join $sql" : '';
		return $this;
	}

	/**
	 * Example:<br>
	 *      where( ['field'=>20] ) // associative array with automatic type
	 *      where( ['field'=>['i',20]] ) // associative array with type defined
	 *      where( ['field',20] ) // array automatic type
	 *      where (['field',['i',20]] ) // array type defined
	 *      where('field=20') // literal value
	 *      where('field=?',[20]) // automatic type
	 *      where('field',[20]) // automatic type (it's the same than where('field=?',[20])
	 *      where('field=?', ['i',20] ) // type(i,d,s,b) defined
	 *      where('field=?,field2=?', ['i',20,'s','hello'] )
	 * @param string|array $sql
	 * @param array|mixed $param
	 * @param bool $isHaving if true then it is a having instead of a where.
	 * @return PdoOne
	 * @see http://php.net/manual/en/mysqli-stmt.bind-param.php for types
	 * @test InstanceOf DaoOne::class,this('field1=?,field2=?',['i',20,'s','hello'])
	 */
	public function where($sql, $param = self::NULL,$isHaving=false)
	{
		if (is_string($sql)) {
			if ($param === self::NULL) {
				if($isHaving) $this->having[]=$sql; else $this->where[] = $sql;
				return $this;
			}
			switch (true) {
				case !is_array($param):
					if (strpos($sql,'?')===false) $sql.='=?'; // transform 'condition' to 'condition=?'
					$this->whereParamType[] = $this->getType($param);
					$this->whereParamValue['i_' . $this->whereCounter] = $param;
					$this->whereCounter++;
					break;
				case count($param)==1:
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
			if($isHaving) $this->having[]=$sql; else $this->where[] = $sql;

		} else {
			$col=array();
			$colT=array();
			$p=array();
			$this->constructParam($sql,$param,$col,$colT,$p);

			foreach($col as $k=>$c) {
				//$c=$this->database_delimiter0.str_replace('.',"{$this->database_delimiter0}.{$this->database_delimiter0}",$c).$this->database_delimiter1;
				if($isHaving) $this->having[]="$c=?"; else $this->where[] = "$c=?";
				$this->whereParamType[] = $p[$k*2];
				$this->whereParamValue['i_' . $this->whereCounter] = $p[$k*2+1];
				$this->whereCounter++;
			}
		}
		return $this;
	}

	/**
	 * Example:
	 *      set('field1=?,field2=?',['i',20,'s','hello'])
	 *      set("type=?",['i',6])
	 *      set("type=?",6) // automatic
	 * @param string|array $sqlOrArray
	 * @param array|mixed $param
	 * @return PdoOne
	 * @test InstanceOf DaoOne::class,this('field1=?,field2=?',['i',20,'s','hello'])
	 */
	public function set($sqlOrArray, $param = self::NULL )
	{
		if (count($this->where)) {
			trigger_error("you can't execute set() after a where()");
			//$this->throwError("you can't execute set() after a where()","");
		}
		if (is_string($sqlOrArray)) {
			$this->set[] = $sqlOrArray;
			// self::NULL  is used when no value is set. We can't use null because it is a valid option.
			if ($param === self::NULL ) return $this;
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

			$col=array();
			$colT=array();
			$p=array();
			$this->constructParam($sqlOrArray,$param,$col,$colT,$p);
			foreach($col as $k=>$c) {
				$this->set[] = "{$this->database_delimiter0}$c{$this->database_delimiter1}=?";
				$this->whereParamType[] = $p[$k*2];
				$this->whereParamValue['i_' . $this->whereCounter] = $p[$k*2+1];
				$this->whereCounter++;
			}
		}
		return $this;
	}
	private function addQuote($txt) {
		if (strpos($txt,$this->database_delimiter0)!==false) return $txt; // it has quotes.
	}
	/**
	 * @param $sql
	 * @return PdoOne
	 * @test InstanceOf DaoOne::class,this('fieldgroup')
	 */
	public function group($sql)
	{
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
	 * @param string|array $sql
	 * @param array|mixed $param
	 * @return PdoOne
	 * @see http://php.net/manual/en/mysqli-stmt.bind-param.php for types
	 * @test InstanceOf DaoOne::class,this('field1=?,field2=?',['i',20,'s','hello'])
	 */
	public function having($sql, $param = self::NULL)
	{
		return $this->where($sql,$param,true);
	}

	/**
	 * @param $sql
	 * @return PdoOne
	 * @test InstanceOf DaoOne::class,this('name desc')
	 */
	public function order($sql)
	{
		
		$this->order = ($sql) ? ' order by ' . $sql : '';
		return $this;
	}

	/**
	 * @param $sql
	 * @return PdoOne
	 * @throws Exception
	 * @test InstanceOf DaoOne::class,this('1,10')
	 */
	public function limit($sql)
	{
		switch ($this->database) {
			case 'mysql':
				$this->limit = ($sql) ? ' limit ' . $sql : '';
				break;
			case 'sqlsrv':
				if (!$this->order) {
					throw new Exception("limit without a sort");
				}
				if (strpos($sql,',')) {
					$arr=explode(',',$sql);
					$this->limit=" OFFSET {$arr[0]} ROWS FETCH NEXT {$arr[1]} ROWS ONLY";
				} else {
					$this->limit=" OFFSET 0 ROWS FETCH NEXT $sql ROWS ONLY";
				}
				break;
			default:
				trigger_error("database not defined or supported");
		}
		
		return $this;
	}

	/**
	 * Adds a distinct to the query. The value is ignored if the select() is written complete.
	 *      ->select("*")->distinct() // works
	 *      ->select("select *")->distinct() // distinct is ignored.
	 * @param $sql
	 * @return PdoOne
	 * @test InstanceOf DaoOne::class,this()
	 */
	public function distinct($sql = 'distinct')
	{
		$this->distinct = ($sql) ? $sql . ' ' : '';
		return $this;
	}

	/**
	 * It returns an array of rows.
	 * @return array|bool
	 * @throws Exception
	 */
	public function toList()
	{

		return $this->runGen(true);
	}

	/**
	 * Run builder query and returns a PDOStatement.
	 * @param bool $returnArray true=return an array. False return a PDOStatement
	 * @return bool|PDOStatement|array
	 * @throws Exception
	 */
	public function runGen($returnArray = true)
	{
		$sql = $this->sqlGen();
		/** @var PDOStatement $stmt */
		$stmt = $this->prepare($sql);
		if ($stmt===null) {
			return false;
		}
		$values = array_values($this->whereParamValue);
		if (count($this->whereParamType)) {
			$counter=0;
			$reval=true;
			foreach($this->whereParamType as $k=>$v) {
				$counter++;
				$reval=$reval && $stmt->bindParam($counter,$values[$k],$this->whereParamType[$k]);
				echo "adding param {$counter} {$values[$k]} {$this->whereParamType[$k]}<br>";
			}
			
			if (!$reval) {
				$this->throwError("Error in bind","","type: ".json_encode($this->whereParamType)." values:".json_encode($values));
				return false;
			}
		}
		$this->runQuery($stmt);
		if ($this->genSqlFields) {
			$this->lastSqlFields=$this->obtainSqlFields($stmt);
		}
		$this->builderReset();
		if ($returnArray) {
			$r = ($stmt->columnCount()>0) ? $stmt->fetchAll(PDO::FETCH_ASSOC) : array();
			$stmt=null; // close
			return $r;
		} else {
			return $stmt;
		}
	}

	/**
	 * @param bool $genSqlFields
	 * @return $this
	 * @test InstanceOf DaoOne::class,this(true)
	 */
	public function generateSqlFields($genSqlFields=true) {
		$this->genSqlFields=$genSqlFields;
		return $this;
	}

	/**
	 * @param PDOStatement $rows
	 * @return string
	 * @todo pendiente fetch_fields not on pdo
	 */
	private function obtainSqlFields($rows) {
		if (!$rows) return '';
		$fields=$rows->fetch_fields();
		$r='';
		foreach($fields as $f) {
			if ($f->orgname!=$f->name) {
				$r.="{$this->database_delimiter0}{$f->table}{$this->database_delimiter1}.{$this->database_delimiter0}$f->orgname{$this->database_delimiter1} {$this->database_delimiter0}$f->name{$this->database_delimiter1}, ";
			} else {
				$r.="{$this->database_delimiter0}{$f->table}{$this->database_delimiter1}.{$this->database_delimiter0}$f->orgname{$this->database_delimiter1}, ";
			}
		}
		return trim($r," \t\n\r\0\x0B,");
	}

	/**
	 * Generates the sql (script). It doesn't run or execute the query.
	 * @param bool $resetStack if true then it reset all the values of the stack, including parameters.
	 * @return string
	 */
	public function sqlGen($resetStack=false)
	{
		if (stripos($this->select,'select')!==false) {
			// is it a full query? ->select=select * ..." instead of ->select=*
			$words = preg_split('#\s+#',strtolower($this->select));
		} else {
			$words=[];
		}
		if (!in_array('select',$words)) {
			$sql='select ' . $this->distinct . $this->select;
		} else {
			$sql=$this->select; // the query already constains "select", so we don't want "select select * from".
		}
		if (!in_array('from',$words)) {
			$sql.=' from '.$this->from;
		} else {
			$sql.=$this->from;
		}
		if (!in_array('where',$words)) {
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
		
		$sql = $sql. $where . $this->group . $having . $this->order . $this->limit;

		if ($resetStack) $this->builderReset();
		return $sql;
	}

	/**
	 * Prepare a query. It returns a mysqli statement.
	 * @param $query string
	 * @return PDOStatement returns the statement if correct otherwise null
	 * @throws Exception
	 */
	public function prepare($query)
	{
		if (!$this->isOpen) { $this->throwError("It's not connected to the database",""); return null; }
		$this->lastParam=[];
		$this->lastQuery = $query;
		if ($this->readonly) {
			if (stripos($query, 'insert ') === 0 || stripos($query, 'update ') === 0 || stripos($query, 'delete ') === 0) {
				// we aren't checking SQL-DCL queries.
				$this->throwError("Database is in READ ONLY MODE","");
			}
		}
		if ($this->logLevel>=2) {
			$this->storeInfo($query);
		}

		try {
			$stmt = $this->conn1->prepare($query);
		} catch (Exception $ex) {
			$stmt=false;
			$this->throwError("Failed to prepare",$ex->getMessage(),json_encode($this->lastParam));
		}
		if ($stmt === false) {
			$this->throwError("Unable to prepare query",$this->lastQuery,json_encode($this->lastParam));
		}
		return $stmt;
	}

	/**
	 * Run a prepared statement.
	 * <br><b>Example</b>:<br>
	 *      $con->runQuery($con->prepare('select * from table'));
	 * @param $stmt PDOStatement
	 * @return bool returns true if the operation is correct, otherwise false
	 * @throws Exception
	 * @test equals true,$this->daoOne->runQuery($this->daoOne->prepare('select 1 from dual'))
	 * @test equals [1=>1],$this->daoOne->select('1')->from('dual')->first(),'it must runs'
	 */
	public function runQuery($stmt)
	{
		if (!$this->isOpen) { $this->throwError("It's not connected to the database",""); return null; }
		try {
			$r = $stmt->execute();
		} catch (Exception $ex) {
			$r=false;
			$this->throwError("Failed to run query",$this->lastQuery . " Cause:" . $ex->getMessage(),json_encode($this->lastParam));
		}
		if ($r === false) {
			$this->throwError("exception query",$this->lastQuery,json_encode($this->lastParam));
		}
		return true;
	}

	/**
	 * It reset the parameters used to Build Query.
	 */
	private function builderReset()
	{
		$this->select = '';
		$this->from = '';
		$this->where = [];
		$this->whereParamType = array();
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
	 * It returns a PDOStatement.
	 * @return PDOStatement
	 * @throws Exception
	 */
	public function toResult()
	{
		return $this->runGen(false);
	}

	/**
	 * It returns the first row.  If there is not row then it returns empty.
	 * <br><b>Example</b>:<br>
	 *      $con->select('*')->from('table')->first(); // select * from table (first value)
	 * @return array|null
	 * @throws Exception
	 */
	public function first()
	{
		/** @var \PDOStatement $rows */
		$rows = $this->runGen(false);
		if ($rows === false) return null;
		if (!$rows->columnCount()) return null;
		while ($row = $rows->fetch(PDO::FETCH_ASSOC)) {
			$rows=null;
			return $row;
		}
		return null;
	}

	/**
	 * Executes the query, and returns the first column of the first row in the result set returned by the query. Additional columns or rows are ignored.
	 * <br><b>Example</b>:<br>
	 *      $con->select('*')->from('table')->firstScalar(); // select * from table (first scalar value)
	 * @return mixed|null
	 * @throws Exception
	 */
	public function firstScalar()
	{
		/** @var \PDOStatement $rows */
		$rows = $this->runGen(false);
		if ($rows === false) return null;
		if (!$rows->columnCount()) return null;
		while ($row = $rows->fetch(PDO::FETCH_ASSOC)) {
			$rows=null;
			return reset($row); // first value
		}
		return null;
	}


	/**
	 * Returns the last row. It's not recommended. Use instead first() and change the order.
	 * <br><b>Example</b>:<br>
	 *      $con->select('*')->from('table')->last(); // select * from table (last scalar value)
	 * @return array|null
	 * @throws Exception
	 * @see \eftec\PdoOne::first
	 */
	public function last()
	{
		/** @var \PDOStatement $rows */
		$rows = $this->runGen(false);
		if ($rows === false) return null;
		if (!$rows->columnCount()) return null;
		/** @noinspection PhpStatementHasEmptyBodyInspection */
		while ($row = $rows->fetch(PDO::FETCH_ASSOC)) {
		}
		return $row;
	}


	/**
	 * Run an unprepared query.
	 * <br><b>Example</b>:<br>
	 *      $values=$con->runRawQuery('select * from table where id=?',["i",20]',true)
	 * @param string $rawSql
	 * @param array|null $param
	 * @param bool $returnArray
	 * @return bool|\PDOStatement|array an array of associative or a pdo statement
	 * @throws Exception
	 * @test equals [0=>[1=>1]],this('select 1',null,true)
	 */
	public function runRawQuery($rawSql, $param = null, $returnArray = true)
	{
		if (!$this->isOpen) { $this->throwError("It's not connected to the database",''); return false;}
		if ($this->readonly) {
			if (stripos($rawSql, 'insert ') === 0 || stripos($rawSql, 'update ') === 0 || stripos($rawSql, 'delete ') === 0) {
				// we aren't checking SQL-DCL queries. Also, "insert into" is stopped but "  insert into" not.
				$this->throwError("Database is in READ ONLY MODE",'');
			}
		}
		$this->lastParam=$param;
		$this->lastQuery=$rawSql;
		if ($this->logLevel>=2) {
			$this->storeInfo($rawSql);
		}
		if ($param === null) {
			// the "where" chain doesn't have parameters.
			try {
				$rows = $this->conn1->query($rawSql);
			} catch (Exception $ex) {
				$rows=false;
				$this->throwError("Exception raw",$rawSql);
			}
			if ($rows === false) {
				$this->throwError("Unable to run raw query",$rawSql);
			}
			if ($returnArray && $rows instanceof PDOStatement) {
				if ($rows->columnCount()>0) {
					return @$rows->fetchAll(PDO::FETCH_ASSOC);
				} else {
					return array();
				}
			} else {
				return $rows;
			}
		}
		// the "where" has parameters.
		$stmt = $this->prepare($rawSql);
		$counter=0;
		for ($i = 0; $i < count($param); $i += 2) {
			$counter++;
			$stmt->bindParam($counter,$param[$i+1],$param[$i]);
		}
		//$stmt->bindParam($parType, ...$values);
		$this->runQuery($stmt);
		
		if ($this->genSqlFields) {
			$this->lastSqlFields=$this->obtainSqlFields($stmt);
		}
		$stmt=null;
		if ($returnArray && $stmt instanceof PDOStatement) {
			$rows = ($stmt->columnCount()>0) ? $stmt->fetchAll(PDO::FETCH_ASSOC) : array();
			return $rows;
		} else {
			return $stmt;
		}
	}

	/**
	 * Returns the last inserted identity.
	 * @return mixed
	 */
	public function insert_id()
	{
		if (!$this->isOpen) return -1;
		return $this->conn1->lastInsertId();
	}

	/**
	 * Returns the number of affected rows.
	 * @param PDOStatement|null|bool $stmt
	 * @return mixed
	 */
	public function affected_rows($stmt)
	{
		if ($stmt instanceof PDOStatement) {
			if (!$this->isOpen) return $stmt->rowCount();
		}
		if (is_array($stmt)) {
			return count($stmt);
		}
		return 0;
	}

	/**
	 * Generate and run an update in the database.
	 * <br><b>Example</b>:<br>
	 *      update('table',['col1','i',10,'col2','s','hello world'],['where','i',10]);
	 *      update('table',['col1','i','col2','s'],[10,'hello world'],['where','i'],[10]);
	 *      ->from("producttype")
	 *          ->set("name=?",['s','Captain-Crunch'])
	 *          ->where('idproducttype=?',['i',6])
	 *          ->update();
	 * @param string $table
	 * @param string[] $tableDef
	 * @param string[]|int $value
	 * @param string[] $tableDefWhere
	 * @param string[]|int $valueWhere
	 * @return mixed
	 * @throws Exception
	 */
	public function update($table=null, $tableDef=null, $value=self::NULL, $tableDefWhere=null, $valueWhere=self::NULL)
	{
		if ($table===null) {
			// using builder. from()->set()->where()->update()
			$errorCause='';
			if ($this->from=="") $errorCause="you can't execute an empty update() without a from()";
			if (count($this->set)===0) $errorCause="you can't execute an empty update() without a set()";
			if (count($this->where)===0) $errorCause="you can't execute an empty update() without a where()";
			if ($errorCause) {
				$this->throwError($errorCause,"");
				return false;
			}
			$sql="update {$this->database_delimiter0}".$this->from."{$this->database_delimiter1} ".$this->constructSet().' '.$this->constructWhere();
			$param=[];
			for($i=0;$i<count($this->whereParamType);$i++) {
				$param[]=$this->whereParamType[$i];
				$param[]=$this->whereParamValue['i_'.$i];
			}
			$this->builderReset();
			$stmt=$this->runRawQuery($sql, $param,true);
			return $this->affected_rows($stmt);
		} else {
			$col = [];
			$colT = null;
			$colWhere = [];
			$param = [];
			if ($tableDefWhere === null) {
				$this->constructParam($tableDef, self::NULL, $col, $colT, $param);
				$this->constructParam($value, self::NULL, $colWhere, $colT, $param);
			} else {
				$this->constructParam($tableDef, $value, $col, $colT, $param);
				$this->constructParam($tableDefWhere, $valueWhere, $colWhere, $colT, $param);
			}
			$sql = "update {$this->database_delimiter0}$table{$this->database_delimiter1} set " . implode(',', $col) . " where " . implode(' and ', $colWhere);
			$this->builderReset();
			$this->runRawQuery($sql, $param);
			return $this->insert_id();
		}
	}
	/**
	 * Generates and execute an insert command. Example:
	 * Example:
	 *      insert('table',['col1','i',10,'col2','s','hello world']);
	 *      insert('table',['col1','i','col2','s'],[10,'hello world']);
	 *      insert('table',['col1'=>'i','col2'=>'s'],['col1'=>10,'col2'=>'hello world']);
	 *      ->set(['col1','i',10,'col2','s','hello world'])
	 *          ->from('table')
	 *          ->insert();
	 * @param string $table
	 * @param string[] $tableDef
	 * @param string[]|int $value
	 * @return mixed
	 * @throws Exception
	 */
	public function insert($table=null, $tableDef=null, $value = self::NULL)
	{
		if ($table===null) {
			// using builder. from()->set()->insert()
			$errorCause='';
			if ($this->from=="") $errorCause="you can't execute an empty insert() without a from()";
			if (count($this->set)===0) $errorCause="you can't execute an empty insert() without a set()";
			if ($errorCause) {
				$this->throwError($errorCause,"");
				return false;
			}
			$sql= /** @lang text */"insert into {$this->database_delimiter0}".$this->from."{$this->database_delimiter1} ".$this->constructInsert();
			$param=[];

			for($i=0;$i<count($this->whereParamType);$i++) {
				$param[]=$this->whereParamType[$i];
				$param[]=$this->whereParamValue['i_'.$i];
			}
			$this->builderReset();
			$this->runRawQuery($sql, $param,true);
			return $this->insert_id();
		} else {
			$col = [];
			$colT = [];
			$param = [];
			$this->constructParam($tableDef, $value, $col, $colT, $param);
			$sql = "insert into {$this->database_delimiter0}$table{$this->database_delimiter1} (" . implode(',', $col) . ") values(" . implode(',', $colT) . ")";
			$this->builderReset();
			$this->runRawQuery($sql, $param);
			return $this->insert_id();
		}
	}


	/**
	 * Delete a row(s) if they exists.
	 * Example:
	 *      delete('table',['col1','i',10,'col2','s','hello world']);
	 *      delete('table',['col1','i','col2','s'],[10,'hello world']);
	 *      $db->from('table')
	 *          ->where('..')
	 *          ->delete() // running on a chain
	 * @param string $table
	 * @param string[] $tableDefWhere
	 * @param string[]|int $valueWhere
	 * @return mixed
	 * @throws Exception
	 */
	public function delete($table=null, $tableDefWhere=null, $valueWhere=self::NULL)
	{
		if ($table===null) {
			// using builder. from()->where()->delete()
			$errorCause='';
			if ($this->from=="") $errorCause="you can't execute an empty delete() without a from()";
			if (count($this->where)===0) $errorCause="you can't execute an empty delete() without a where()";
			if ($errorCause) {
				$this->throwError($errorCause,"");
				return false;
			}
			$sql="delete from {$this->database_delimiter0}".$this->from."{$this->database_delimiter1} ".$this->constructWhere();
			$param=[];
			for($i=0;$i<count($this->whereParamType);$i++) {
				$param[]=$this->whereParamType[$i];
				$param[]=$this->whereParamValue['i_'.$i];
			}
			$this->builderReset();
			$stmt=$this->runRawQuery($sql, $param,true);
			return $this->affected_rows($stmt);
		} else {
			// using table/tabldefwhere/valuewhere
			$colWhere = [];
			$colT = null;
			$param = [];
			$this->constructParam($tableDefWhere, $valueWhere, $colWhere, $colT, $param);
			$sql = "delete from {$this->database_delimiter0}$table{$this->database_delimiter1} where " . implode(' and ', $colWhere);
			$this->builderReset();
			$stmt=$this->runRawQuery($sql, $param,true);
			return $this->affected_rows($stmt);
		}
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
	private function constructInsert() {
		if (count($this->set)) {
			$arr=[];
			$val=[];
			$first=$this->set[0];
			if (strpos($first,'=')!==false) {
				// set([])
				foreach($this->set as $v) {
					$tmp=explode('=',$v);
					$arr[]=$tmp[0];
					$val[]=$tmp[1];
				}
				$where = "(".implode(',',$arr).') values ('.implode(',', $val).')';
			} else {
				// set('(a,b,c) values(?,?,?)',[])
				$where=$first;
			}
		} else {
			$where = '';
		}
		return $where;
	}

	/**
	 * @param array $array1
	 * @param array|int $array2  if value is self::NULL then it's calculated without this value
	 * @param array $col
	 * @param array $colT
	 * @param array $param
	 */
	private function constructParam($array1,$array2,&$col,&$colT,&$param) {
		if ($this->isAssoc($array1)) {
			if ($array2 === self::NULL) {
				// the type is calculated automatically. It could fails and it doesn't work with blob
				foreach ($array1 as $k => $v) {
					if ($colT===null) {
						$col[] = "{$this->database_delimiter0}$k{$this->database_delimiter1}=?";
					} else {
						$col[] = $k;
						$colT[] = '?';
					}
					$vt=$this->getType($v);
					$param[] = $vt;
					$param[] = $v;
				}
			} else {
				// it uses two associative array, one for the type and another for the value
				foreach ($array1 as $k => $v) {
					if ($colT===null) {
						$col[] = "{$this->database_delimiter0}$k{$this->database_delimiter1}=?";
					} else {
						$col[] = $k;
						$colT[] = '?';
					}

					$param[] = $v;
					$param[] = @$array2[$k];
				}
			}
		} else {
			if ($array2 === self::NULL) {
				// it uses a single list, the first value is the column, the second value
				// is the type and the third is the value
				for ($i = 0; $i < count($array1); $i += 3) {
					if ($colT===null) {
						$col[] = "{$this->database_delimiter0}".$array1[$i]."{$this->database_delimiter1}=?";
					} else {
						$col[] = $array1[$i];
						$colT[] = '?';
					}
					$param[] = $array1[$i + 1];
					$param[] = $array1[$i + 2];
				}
			} else {
				// it uses two list, the first value of the first list is the column, the second value is the type
				// , the second list only contains values.
				for ($i = 0; $i < count($array1); $i += 2) {
					if ($colT===null) {
						$col[] = "{$this->database_delimiter0}".$array1[$i]."{$this->database_delimiter1}=?";
					} else {
						$col[] = $array1[$i];
						$colT[] = '?';
					}
					$param[] = $array1[$i + 1];
					$param[] = $array2[$i / 2];
				}
			}
		}
	}

	/**
	 * @param mixed $v Variable
	 * @return int=[PDO::PARAM_STR,PDO::PARAM_INT,PDO::PARAM_BOOL][$i]
	 * @test equals PDO::PARAM_STR,(20.3)
	 * @test equals PDO::PARAM_STR,('hello')
	 */
	private function getType(&$v) {
		switch (1) {
			case ($v===null):
				$vt=PDO::PARAM_STR;
				break;
			case (is_double($v)):
				$vt=PDO::PARAM_STR;
				break;
			case (is_numeric($v)):
				$vt=PDO::PARAM_INT;
				break;
			case (is_bool($v)):
				$vt=PDO::PARAM_BOOL;
				$v=($v)?1:0;
				break;
			case (is_object($v) && get_class($v)=='DateTime'):
				$vt=PDO::PARAM_STR;
				$v=PdoOne::dateTimePHP2Sql($v);
				break;
			default:
				$vt=PDO::PARAM_BOOL;
		}
		return $vt;
	}

	private function isAssoc($array){
		return (array_values($array) !== $array);
	}
	//</editor-fold>

	//<editor-fold desc="Encryption functions" defaultstate="collapsed" >
	/**
	 * @param string|int $password <p>Use a integer if the method is INTEGER</p>
	 * @param string $salt <p>Salt is not used by SIMPLE or INTEGER</p>
	 * @param string $encMethod <p>Example : AES-256-CTR See http://php.net/manual/en/function.openssl-get-cipher-methods.php </p>
	 * <p>if SIMPLE then the encryption is simplified (generates a short result)</p>
	 * <p>if INTEGER then the encryption is even simple (generates an integer)</p>
	 * @throws Exception
	 * @test void this('123','somesalt','AES-128-CTR')
	 */
	public function setEncryption($password, $salt, $encMethod)
	{

		if (!extension_loaded('openssl')) {
			$this->encryption->encEnabled=false;
			$this->throwError("OpenSSL not loaded, encryption disabled","");
		} else {
			$this->encryption->encEnabled=true;
			$this->encryption->setEncryption($password,$salt,$encMethod);
		}
	}
	/**
	 * Wrapper of DaoOneEncryption->encrypt
	 * @param $data
	 * @return bool|string
	 *@see \eftec\PdoOneEncryption::encrypt
	 */

	public function encrypt($data) {
		return $this->encryption->encrypt($data);
	}

	/**
	 * Wrapper of DaoOneEncryption->decrypt
	 * @param $data
	 * @return bool|string
	 *@see \eftec\PdoOneEncryption::decrypt
	 */
	public function decrypt($data) {
		return $this->encryption->decrypt($data);
	}
	//</editor-fold>

	//<editor-fold desc="Log functions" defaultstate="collapsed" >
	/**
	 * Returns the last error.
	 * @return string
	 */
	public function lastError()
	{
		if (!$this->isOpen) return "It's not connected to the database";
		return $this->conn1->errorInfo()[2];
	}

	/**
	 * Write a log line for debug, clean the command chain then throw an error (if throwOnError==true)
	 * @param string $txt
	 * @param string $txtExtra It's only used if $logLevel>=2
	 * @param string $extraParam It's only used if $logLevel>=3
	 * @throws Exception
	 * @see \eftec\PdoOne::$logLevel
	 */
	function throwError($txt,$txtExtra,$extraParam='')
	{
		if ($this->logLevel===0) $txt='Error on database';
		if ($this->logLevel>=2) $txt.=$txtExtra;
		if ($this->logLevel>=2) {
			$txt.=". Last query:[{$this->lastQuery}].";
		}
		if ($this->logLevel>=3) {
			$txt.=$extraParam;
		}		
		$this->builderReset(); // it resets the chain if any.
		if ($this->getMessages()===null) {
			$this->debugFile($txt,'ERROR');
		} else {
			$this->getMessages()->addItem($this->db,$txt);
			$this->debugFile($txt,'ERROR');
		}
		if ($this->throwOnError) {
			throw new Exception($txt);
		} 
	}
	/**
	 * Write a log line for debug, clean the command chain then throw an error (if throwOnError==true)
	 * @param $txt
	 * @throws Exception
	 */
	function storeInfo($txt)
	{
		if ($this->getMessages()===null) {
			$this->debugFile($txt,'INFO');
		} else {
			$this->getMessages()->addItem($this->db,$txt,"info");
			$this->debugFile($txt,'INFO');
		}
	}
	function debugFile($txt,$level='INFO') {
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
		if ($this->logLevel==2) {
			$txtW.=" param:".json_encode($this->lastParam);
		}

		$txtW = str_replace("\r\n", " ", $txtW);
		$txtW = str_replace("\n", " ", $txtW);
		try {
			$now = new DateTime();
			@fwrite($fp, $now->format('c')."\t".$level."\t".$txtW . "\n");
		} catch (Exception $e) {
		}

		@fclose($fp);
	}


	//</editor-fold>

}
