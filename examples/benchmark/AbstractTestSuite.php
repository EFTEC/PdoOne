<?php
require_once dirname(__FILE__) . '/sfTimer.php';
abstract class AbstractTestSuite
{
    /** @var PDO $con */
    protected $con;
    protected $books = array();
    protected $authors = array();

    const NB_TEST = 1000;

    abstract function initialize();
    abstract function clearCache();
    abstract function beginTransaction();
    abstract function commit();
    abstract function runAuthorInsertion($i);
    abstract function runBookInsertion($i);
    abstract function runPKSearch($i);
    abstract function runComplexQuery($i);
    abstract function runHydrate($i);
    abstract function runJoinSearch($i);

    public function initTables()
    {
        try {
            $this->con->exec('DROP TABLE book');
            $this->con->exec('DROP TABLE author');
        } catch (PDOException $e) {
            // do nothing - the tables probably don't exist yet
        }
        $this->con->exec("CREATE TABLE author
		(
			id INTEGER  NOT NULL PRIMARY KEY AUTO_INCREMENT,
			first_name VARCHAR(128)  NOT NULL,
			last_name VARCHAR(128)  NOT NULL,
			email VARCHAR(128) default 'email'
		)");        
        $this->con->exec("CREATE TABLE book
		(
			id INTEGER  NOT NULL PRIMARY KEY AUTO_INCREMENT,
			title VARCHAR(255)  NOT NULL,
			isbn VARCHAR(24)  NOT NULL,
			price FLOAT,
			author_id INTEGER,
			FOREIGN KEY (author_id) REFERENCES author(id)
		)");

    }

    public function run()
    {
        $t1 =  $this->runTest('runAuthorInsertion', 2000);
        $t1 += $this->runTest('runBookInsertion', 2000);
        $t2 = $this->runTest('runPKSearch', 500);
        $t3 = $this->runTest('runComplexQuery', 1000);
        $t4 = $this->runTest('runHydrate', 1000);
        $t5 = $this->runTest('runJoinSearch', 1000);
        echo sprintf("| %32s | %6d | %6d | %6d | %6d | %6d | ", str_replace('TestSuite', '', get_class($this)), $t1, $t2, $t3, $t4, $t5);
    }

    public function runTest($methodName, $nbTest = self::NB_TEST)
    {
        $this->clearCache();
        $timer = new sfTimer();
        $this->beginTransaction();
        for($i=0; $i<$nbTest; $i++) {
            $this->$methodName($i);
        }
        $this->commit();
        $t = $timer->getElapsedTime();
        return $t * 1000;
    }
}