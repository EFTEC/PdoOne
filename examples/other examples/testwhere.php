<?php /** @noinspection PhpUnreachableStatementInspection */

use dBug\dBug;
use eftec\PdoOne;
use mapache_commons\Collection;

include "../../vendor/autoload.php";
include "../Collection.php";
include "../dBug.php";
echo "<body><div>";
// connecting to database sakila at 127.0.0.1 with user root and password abc.123
$dao=new PdoOne("mysql","127.0.0.1","root","abc.123","sakila","logpdoone.txt");
$dao->logLevel=3;
try {
    echo "<h1>Connection. The instance {$dao->server}, base:{$dao->db}  user:{$dao->user} and password:{$dao->pwd} must exists</h1>";
    $dao->connect();
    echo "Connected A-OK!<br>";
} catch (Exception $e) {
    echo "<h2>connection error:</h2>";
    echo $dao->lastError()."-".$e->getMessage()."<br>";
    die(1);
}
$sqlT1="CREATE TABLE `typetable` (
    `type` INT NOT NULL,
    `name` VARCHAR(45) NULL,
    PRIMARY KEY (`type`));";

$sqlT2="CREATE TABLE `producttype` (
    `idproducttype` INT NOT NULL,
    `name` VARCHAR(45) NULL,
    `type` int not NULL,
    PRIMARY KEY (`idproducttype`))";

$now=new DateTime();
// running a raw query (unprepared statement)
try {
    echo "<h1>Table creation (it's ok if it fails because it could exist):</h1>";
    $dao->runRawQuery($sqlT1);
    echo $dao->lastQuery."<br>";
    $dao->runRawQuery($sqlT2);
    echo $dao->lastQuery."<br>";
} catch (Exception $e) {
    echo "<h2>Table creation error:</h2>";
    echo "<hr><pre>".$dao->lastError()."-".$e->getMessage()."</pre><br/>";
}

echo "<h1>toMeta:</h1>";

try {
    $dao->from('producttype')->set(['idproducttype', 'name', 'type'], [1, 'Coca-Cola', 1])->insert();
    $dao->from('producttype')->set(['idproducttype', 'name', 'type'], [2, 'aaa', 1])->insert();
} catch (Exception $e) {
    echo "<h2>Table insert error:</h2>";
    echo "<hr><pre>".$dao->lastError()."-".$e->getMessage()."</pre><br/>";
}

//$dao->constructParam2('name=? and type<?', [PDO::PARAM_STR, 'Coca-Cola','i',987]);

//$dao->

//constructParam2('name=:name and type<:type', [':name'=>'Coca-Cola',':type'=>987]);

$results = $dao->select("*")->from("producttype")
    ->where('name', ['Coca-Cola'])
    ->where('type', [1])
    ->toList();

new dBug($dao->lastQuery);
new dBug($dao->lastError());

echo Collection::generateTable($results);





//$results = $dao->select("*")->from("producttype")
//    ->where(['name'=>[PDO::PARAM_STR,'Coca-Cola']])
//    ->toList();

echo "<pre>";
var_dump($results);
echo "</pre>";
echo $dao->lastQuery;
