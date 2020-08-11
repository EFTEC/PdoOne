<?php

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
    echo $dao->lastError()."-".$e->getMessage()."<br>";
}

echo "<hr>toMeta:";
$results = $dao->select("*")->from("producttype")
    ->where('name=?', ['Coca-Cola'])
    ->where('idproducttype=?', [1])
    ->toMeta();
echo "<pre>";
var_dump($results);
echo "</pre>";
echo $dao->lastQuery;

echo Collection::generateTable($results);


try {
	echo "<hr>toList (raw query):";
	$results = $dao->runRawQuery("select * from producttype where name=?",['Coca-Cola'],true);
	echo $dao->lastQuery;
	echo Collection::generateTable($results);
	
	
	
    echo "<hr>toList: (1)";
    $results = $dao->select("*")->from("producttype")
        ->where('name=?', ['Coca-Cola'])
        ->where('idproducttype=?', [1])
        ->toList();
    echo $dao->lastQuery;
    echo Collection::generateTable($results);


    

    echo "<hr>toList without where:";
    $results = $dao->select("*")->from("producttype")
        ->toList();
    echo $dao->lastQuery;
    echo Collection::generateTable($results);

    echo "<hr>toList: (where string)";
    $results = $dao->select("*")->from("producttype")
        ->where("name='Coca-Cola'")
        ->toList();
    echo $dao->lastQuery;
    echo Collection::generateTable($results);

    echo "<hr>toList using associative array:<br>";
    $results = $dao->select("*")->from("producttype")
        ->where(['name'=>'Coca-Cola','idproducttype'=>1])
        ->toList();
    echo $dao->lastQuery;
    echo Collection::generateTable($results);

    echo "<hr>toList using associative array (using named where and arguments):<br>";
    $results = $dao->select("*")->from("producttype")
        ->where(['name=:name','idproducttype=:idproducttype'],
        ['name'=>'Coca-Cola','idproducttype'=>1])
        ->toList();
    echo $dao->lastQuery;
    echo Collection::generateTable($results);

    echo "<h2>toList using associative array:</h2>";
    $results = $dao->select("*")->from("producttype")
        ->where(['name','idproducttype'],
            ['Coca-Cola',1])
        ->toList();
    echo $dao->lastQuery;
    echo Collection::generateTable($results);

    
    echo "<h2>toList (from join):</h2>";
    $results = $dao->select("pt.*,tt.name typetable_name")
        ->from("producttype pt")
        ->join("typetable tt on pt.type=tt.type")
        ->toList();
    echo $dao->lastQuery;
    echo Collection::generateTable($results);

    echo "<h2>toList (join left):</h2>";
    $results = $dao->select("pt.*,tt.name typetable_name")
        ->from("producttype pt")
        ->left("typetable tt on pt.type=tt.type")
        ->toList();
    echo $dao->lastQuery;
    echo Collection::generateTable($results);

    echo "<h2>toList (join left):</h2>";
    $results = $dao->select("pt.*,tt.name typetable_name")
        ->from("producttype pt")
        ->left("typetable tt on pt.type=tt.type")
        ->first();
    echo $dao->lastQuery;
    echo Collection::generateTable($results);

    echo "<h2>toList: (3)</h2>";
    $results = $dao->select("*")->from("producttype")
        ->where('idproducttype>=?', [ 1])
        ->order('idproducttype desc')
        ->toList();
    echo $dao->lastQuery;
    echo Collection::generateTable($results);

    echo "<h2>toList: (4) </h2>";
    $results = $dao->select("*")->from("producttype")
                   ->where('idproducttype>0')
                   ->order('idproducttype desc')
                   ->toList();
    echo $dao->lastQuery;
    echo Collection::generateTable($results);


    echo "<h2>toResult:</h2>";
    $resultsQuery = $dao->select("*")->from("producttype")
        ->where('name=?', ['Coca-Cola'])
        ->where('idproducttype=?', [1])
        ->toResult();
    echo $dao->lastQuery;
    $results=$resultsQuery->fetchAll(PDO::FETCH_ASSOC);
    echo Collection::generateTable($results);
    $resultsQuery=null;

    echo "<h2>first:</h2>";
    $results = $dao->select("*")->from("producttype")
        ->where('name=?', ['Coca-Cola'])
        ->where('idproducttype=?', [1])
	    ->order('name')
        ->limit('1')
        ->first();
    echo $dao->lastQuery;
    echo Collection::generateTable($results);

    echo "<h2>first returns nothing :</h2>";
    $results = $dao->select("*")->from("producttype")
        ->where('name=?', ['Coca-Cola'])
        ->where('idproducttype=?', [55])
        ->limit('1')
        ->first();
    echo $dao->lastQuery;
    echo "<br><pre>";
    var_dump($results);
    echo "</pre>";

    echo "<h2>where simple fixed</h2>";
    $results = $dao->select("*")->from("producttype")
        ->where('idproducttype=1')
        ->runGen();
    echo $dao->lastQuery;
    echo Collection::generateTable($results);

    echo "<h2>string and array</h2>";
    $results = $dao->select("*")->from("producttype p")
        ->where('idproducttype between ? and ?', [1, 3])
        ->toList();
    echo $dao->lastQuery;

    echo Collection::generateTable($results);

    echo "<h2>having</h2>";
    $results = $dao->select("p.type,count(*) c")->from("producttype p")
        ->group("p.type")
        ->having('p.type>?',[0])
        ->toList();
    echo $dao->lastQuery;
    echo Collection::generateTable($results);


} catch(Exception $ex) {
    echo $ex->getMessage();
    echo $ex->getTraceAsString();
}

echo "</div></body>";
function build_table($array){
    if (!isset($array[0])) {
        $tmp=$array;
        $array=array();
        $array[0]=$tmp;
    } // create an array with a single element
    if ($array[0]===null) {
        return "NULL<br>";
    }
    // start table
    $html = '<table style="border: 1px solid black;">';
    // header row
    $html .= '<tr>';
    foreach($array[0] as $key=>$value){
        $html .= '<th>' . htmlspecialchars($key) . '</th>';
    }
    $html .= '</tr>';

    // data rows
    foreach( $array as $key=>$value){
        $html .= '<tr>';
        foreach($value as $key2=>$value2){
            $html .= '<td>' . htmlspecialchars($value2) . '</td>';
        }
        $html .= '</tr>';
    }

    // finish table and return it

    $html .= '</table>';
    return $html;
}