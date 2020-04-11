<?php /** @noinspection PhpUnhandledExceptionInspection */

use eftec\PdoOne;

include "../vendor/autoload.php";

// connecting to database sakila at 127.0.0.1 with user root and password abc.123

//$dao=new PdoOne('mysql',"127.0.0.1","root","abc.123","sakila","logpdoone.txt");
$dao = new PdoOne('sqlsrv', "(local)\sqlexpress", "sa", "abc.123", "sakila", "logpdoone.txt");
$dao->logLevel = 3;
$dao->open();
echo "<pre>";
var_dump($dao->tableSorted(3, false, true)); // it returns the tables sortered
var_dump($dao->tableSorted(3, true, true)); // it returns all the tables that can't be sortered
echo "</pre>";
