<?php /** @noinspection PhpUnhandledExceptionInspection */

use eftec\_BasePdoOneRepo;
use eftec\PdoOne;

include '../vendor/autoload.php';
include '../lib/_BasePdoOneRepo.php';
include "../Collection.php";

// connecting to database sakila at 127.0.0.1 with user root and password abc.123

$pdoOne = new PdoOne('sqlsrv', 'PCJC\SQLEXPRESS', 'sa', 'abc.123', 'test', 'logpdoone.txt');
$pdoOne->logLevel = 3;
$pdoOne->throwOnError = true;
$pdoOne->open();
//var_dump(Table_1Repo::dropTable());
//Table_1Repo::createTable();
//Table_1Repo::createFk();
(Table1Repo::where([1,2]))::findAll();

/**
 * Generated by PdoOne Version 1.31.1
 * @copyright (c) Jorge Castro C. MIT License  https://github.com/EFTEC/PdoOne
 * Class Table1Repo
 */
class Table1Repo extends _BasePdoOneRepo
{
    const TABLE = 'Table_1';
    const PK = 't1pk';
    private static $_instance = null;

    public static function getDef() {
        return [
            "t1pk" => "int NOT NULL IDENTITY(1,1)",
            "t2" => "int",
            "t3" => "varchar(50) DEFAULT ('IT IS A DEFAULT VALUE')",
            "t4" => "decimal(18,2)",
            "t5" => "float",
            "t6" => "tinyint",
            "t" => "nchar(10)"
        ];
    }
    public static function getDefKey() {
        return [
            "t1pk" => "PRIMARY KEY",
            "t4" => "UNIQUE KEY",
            "t6" => "KEY"
        ];
    }
    public static function getDefFK() {
        return [
            "t2" => "FOREIGN KEY REFERENCES tbl_for(TABLEFOR)"
        ];
    }
    public static function getColumns() {
        return ["t1pk"=>0,"t2"=>0,"t3"=>'',"t4"=>0.0,"t5"=>0.0,"t6"=>0,"t"=>''];
    }
    public static function getInstance ()
    {
        if (self::$_instance === null) {
            static::$_instance = new static();
        }
        return self::$_instance;
    }
}

var_dump(A2::f3());
//var_dump(A2::f3()::f1();
//var_dump(A2::f3());
