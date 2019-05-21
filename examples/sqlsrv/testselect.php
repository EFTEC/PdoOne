<?php /** @noinspection SqlNoDataSourceInspection */

use eftec\PdoOne;
use mapache_commons\Collection;


include "../../vendor/autoload.php";
include "../Collection.php";
include "../dBug.php";
echo "<body><div style='width:600px'>";
// connecting to database sakila at 127.0.0.1 with user root and password abc.123
$dao=new PdoOne("sqlsrv","127.0.0.1","sa","abc.123","sakila","");
$dao->logLevel=2;
try {
    echo "<h1>Connection. The instance {$dao->server}, base:{$dao->db}  user:{$dao->user} and password:{$dao->pwd} must exists</h1>";
    $dao->connect();
    echo "Connected A-OK!<br>";
} catch (Exception $e) {
    echo "<h2>connection error:</h2>";
    echo $dao->lastError()."-".$e->getMessage()."<br>";
    die(1);
}
$sqlT1="set nocount on;
	CREATE TABLE dbo.typetable
	(
	type int NOT NULL,
	name varchar(50) NULL
	);
ALTER TABLE dbo.typetable ADD CONSTRAINT
	PK_typetable PRIMARY KEY CLUSTERED 
	(
	type
	) ";

$sqlT2="set nocount on;
	CREATE TABLE dbo.producttype
	(
	idproducttype int NOT NULL,
	name varchar(50) NULL,
	type int NULL
	);
ALTER TABLE dbo.producttype ADD CONSTRAINT
	PK_producttype PRIMARY KEY CLUSTERED 
	(
	idproducttype
	)";

$now=new DateTime();
// running a raw query (unprepared statement)
try {
    echo "<h1>Table creation (it's ok if it fails because it could exist):</h1>";
	//$dao->runRawQuery("set nocount on;drop table dbo.typetable");
    $dao->runRawQuery($sqlT1);
    echo $dao->lastQuery."<br>";
    $dao->runRawQuery($sqlT2);
    echo $dao->lastQuery."<br>";
} catch (Exception $e) {
    echo "<h2>Table creation error:</h2>";
    echo $dao->lastError()."-".$e->getMessage()."<br>";
}


//try {
	echo "<hr>toList (raw query):";
	$results = $dao->runRawQuery("select * from producttype where name=?",[PDO::PARAM_STR, 'Coca-Cola'],true);
	echo $dao->lastQuery;
	echo Collection::generateTable($results);
	
    echo "<hr>toList:";
    $results = $dao->select("*")->from("producttype")
        ->where('name=?', [PDO::PARAM_STR, 'Coca-Cola'])
        ->where('idproducttype=?', [PDO::PARAM_INT, 1])
        ->toList();
    echo $dao->lastQuery;
    echo Collection::generateTable($results);

    echo "<hr>toList without where:";
    $results = $dao->select("*")->from("producttype")
        ->toList();
    echo $dao->lastQuery;
    echo Collection::generateTable($results);

    echo "<hr>toList:";
    $results = $dao->select("*")->from("producttype")
        ->where("name='Coca-Cola'")
        ->toList();
    echo $dao->lastQuery;
    echo Collection::generateTable($results);

    echo "<hr>toList using associative array:";
    $results = $dao->select("*")->from("producttype")
        ->where(['name'=>'Coca-Cola','idproducttype'=>1])
        ->toList();
    echo $dao->lastQuery;
    echo Collection::generateTable($results);

    echo "<hr>toList using associative array:";
    $results = $dao->select("*")->from("producttype")
        ->where(['name'=>PDO::PARAM_STR,'idproducttype'=>PDO::PARAM_INT],
        ['name'=>'Coca-Cola','idproducttype'=>1])
        ->toList();
    echo $dao->lastQuery;
    echo Collection::generateTable($results);

    echo "<hr>toList using associative array:";
    $results = $dao->select("*")->from("producttype")
        ->where(['name',PDO::PARAM_STR,'idproducttype',PDO::PARAM_INT],
            ['Coca-Cola',1])
        ->toList();
    echo $dao->lastQuery;
    echo Collection::generateTable($results);

    echo "<hr>toList using associative array:";
    $results = $dao->select("*")->from("producttype")
        ->where(['name',PDO::PARAM_STR,'Coca-Cola','idproducttype',PDO::PARAM_INT,1])
        ->toList();
    echo $dao->lastQuery;
    echo Collection::generateTable($results);

    echo "<hr>toList (from join):";
    $results = $dao->select("pt.*,tt.name typetable_name")
        ->from("producttype pt")
        ->join("typetable tt on pt.type=tt.type")
        ->toList();
    echo $dao->lastQuery;
    echo Collection::generateTable($results);

    echo "<hr>toList (join left):";
    $results = $dao->select("pt.*,tt.name typetable_name")
        ->join("producttype pt")
        ->left("typetable tt on pt.type=tt.type")
        ->toList();
    echo $dao->lastQuery;
    echo Collection::generateTable($results);

    echo "<hr>toList (join left):";
    $results = $dao->select("pt.*,tt.name typetable_name")
        ->join("producttype pt")
        ->left("typetable tt on pt.type=tt.type")
        ->first();
    echo $dao->lastQuery;
    echo Collection::generateTable($results);

    echo "<hr>toList: ";
    $results = $dao->select("*")->from("producttype")
        ->where('idproducttype>=?', [PDO::PARAM_INT, 1])
        ->order('idproducttype desc')
        ->toList();
    echo $dao->lastQuery;
    echo Collection::generateTable($results);


    echo "<hr>toResult: ";
    $resultsQuery = $dao->select("*")->from("producttype")
        ->where('name=?', [PDO::PARAM_STR, 'Coca-Cola'])
        ->where('idproducttype=?', [PDO::PARAM_INT, 1])
        ->toResult();
    echo $dao->lastQuery;
    $results=$resultsQuery->fetchAll(PDO::FETCH_ASSOC);
    echo Collection::generateTable($results);
    $resultsQuery=null;

    echo "<hr>first:(1) ";
    $results = $dao->select("*")->from("producttype")
        ->where('name=?', [PDO::PARAM_STR, 'Coca-Cola'])
        ->where('idproducttype=?', [PDO::PARAM_INT, 1])
        ->first();
    /*->order('name')
        ->limit('1')*/
    echo $dao->lastQuery;
	var_dump($dao->lastParam);
    echo Collection::generateTable($results);

    echo "<hr>first returns nothing :";
    $results = $dao->select("*")->from("producttype")
        ->where('name=?', [PDO::PARAM_STR, 'Coca-Cola'])
        ->where('idproducttype=?', [PDO::PARAM_INT, 55])
	    ->order('name')
        ->limit('1')
        ->first();
    echo $dao->lastQuery;
    echo "<br><pre>";
    var_dump($results);
    echo "</pre>";

    echo "<hr>";
    $results = $dao->select("*")->from("producttype")
        ->where('idproducttype=1')
        ->runGen();
    echo $dao->lastQuery;
    echo Collection::generateTable($results);

    echo "<hr>";
    $results = $dao->select("*")->from("producttype p")
        ->where('idproducttype between ? and ?', [PDO::PARAM_INT, 1, PDO::PARAM_INT, 3])
        ->toList();
    echo $dao->lastQuery;

    echo Collection::generateTable($results);

    echo "<hr>";
    $results = $dao->select("p.type,count(*) c")->from("producttype p")
        ->group("p.type")
        ->having('p.type>?',[PDO::PARAM_INT,0])
        ->toList();
    echo $dao->lastQuery;
    echo Collection::generateTable($results);

/*
} catch(Exception $ex) {
    echo $ex->getMessage();
    echo $ex->getTraceAsString();
}
*/
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