<?php

use eftec\PdoOne;
use mapache_commons\Collection;

include "../vendor/autoload.php";
include "Collection.php";
include "dBug.php";
echo "<body><div style='width:600px'>";
// connecting to database sakila at 127.0.0.1 with user root and password abc.123
$dao=new PdoOne("mysql","127.0.0.1","root","abc.123","sakila","logpdoone.txt");
try {
    echo "<h1>Connection. The instance {$dao->server}, base:{$dao->db}  user:{$dao->user} and password:{$dao->pwd} must exists</h1>";
    $dao->connect();
    echo "Connected A-OK!<br>";
} catch (Exception $e) {
    echo "<h2>connection error:</h2>";
    echo $dao->lastError()."-".$e->getMessage()."<br>";
    die(1);
}
// creating tables
echo "<hr>Dropping and Creating table<br>";
try {
	$dao->runRawQuery('drop table myproducts');
} catch (Exception $e) {
}
$sqlT1="CREATE TABLE `myproducts` (
    `idproduct` INT NOT NULL,
    `name` VARCHAR(45) NULL,
    `type` VARCHAR(45) NULL,
    `id_category` INT NOT NULL,
    PRIMARY KEY (`idproduct`));";

try {
    $dao->runRawQuery($sqlT1);
} catch (Exception $e) {
    echo $e->getMessage()."<br>";
}
echo "<hr>Dropping And Creating table<br>";
try {
	$dao->runRawQuery('drop table product_category');
} catch (Exception $e) {
}
$sqlT2="CREATE TABLE `product_category` (
    `id_category` INT NOT NULL,
    `catname` VARCHAR(45) NULL,
    PRIMARY KEY (`id_category`));";

try {
    $dao->runRawQuery($sqlT2);
} catch (Exception $e) {
    echo $e->getMessage()."<br>";
}
echo "<hr>adding<br>";
// adding some data
try {
    $dao->set(['id_category' => 1, 'catname' => 'cheap'])
        ->from('product_category')->insert();
    echo "added<br>";
    $dao->set(['id_category'=>2,'catname'=>'normal'])
        ->from('product_category')->insert();
	echo "added<br>";
    $dao->set(['id_category'=>3,'catname'=>'expensive'])
        ->from('product_category')->insert();
	echo "added<br>";
} catch (Exception $e) {
}
echo "<hr>adding<br>";
// adding categories
try {
    $dao->set(['idproduct'=>1,'name'=>'cocacola'
        ,'type'=>'drink','id_category'=>1])
        ->from("myproducts")->insert();
    $dao->set(['idproduct'=>2,'name'=>'fanta'
        ,'type'=>'drink','id_category'=>1])
        ->from("myproducts")->insert();
    $dao->set(['idproduct'=>3,'name'=>'sprite'
        ,'type'=>'drink','id_category'=>1])
        ->from("myproducts")->insert();
    $dao->set(['idproduct'=>4,'name'=>'iphone'
        ,'type'=>'phone','id_category'=>2])
        ->from("myproducts")->insert();
    $dao->set(['idproduct'=>5,'name'=>'galaxy note'
        ,'type'=>'phone','id_category'=>2])
        ->from("myproducts")->insert();
    $dao->set(['idproduct'=>6,'name'=>'xiami'
        ,'type'=>'phone','id_category'=>2])
        ->from("myproducts")->insert();
    $dao->set(['idproduct'=>7,'name'=>'volvo',
        'type'=>'car','id_category'=>3])
        ->from("myproducts")->insert();
    $dao->set(['idproduct'=>8,'name'=>'bmw'
        ,'type'=>'car','id_category'=>3])
        ->from("myproducts")->insert();
} catch (Exception $e) {
}

// list products
$products=$dao->runRawQuery("select * from myproducts",null,true);
echo Collection::generateTable($products);

// Listing using procedure call
$products=$dao->select("*")->from("myproducts")->toList();
echo Collection::generateTable($products);

// list join (we could even add having()
$products=$dao->select("*")->from("myproducts my")
    ->join("product_category  p on my.id_category=p.id_category")->toList();
echo Collection::generateTable($products);
// Let's clean the join
$products=$dao->select("name,type,catname")->from("myproducts my")
    ->join("product_category  p on my.id_category=p.id_category")->toList();
echo Collection::generateTable($products);

// list join order
$products=$dao->select("name,type,catname")->from("myproducts my")
    ->join("product_category  p on my.id_category=p.id_category")
    ->order("name")->toList();
echo Collection::generateTable($products);

// We also could obtain the first value (or the last)
$products=$dao->select("name,type,catname")->from("myproducts my")
    ->join("product_category  p on my.id_category=p.id_category")->first();
echo Collection::generateTable($products);

// We also could obtain an escalar. It's useful if you want, for example, returns the number of elements.
$products=$dao->select("count(*)")->from("myproducts my")
    ->join("product_category  p on my.id_category=p.id_category")->firstScalar();
echo Collection::generateTable($products);

// And, we could add limit
$products=$dao->select("*")->from("myproducts my")
    ->join("product_category  p on my.id_category=p.id_category")
    ->order("name")->limit("1,3")->toList();
echo Collection::generateTable($products);

// And we could group
$products=$dao->select("catname,count(*) count")
    ->from("myproducts my")
    ->join("product_category  p on my.id_category=p.id_category")
    ->group("catname")
    ->toList();
echo Collection::generateTable($products);

die(1);

$now=new DateTime();
// running a raw query (unprepared statement)
try {
    echo "<h1>Table creation (it's ok if it fails if exists):</h1>";
    $dao->runRawQuery($sqlT1);
    echo $dao->lastQuery."<br>";
    $dao->runRawQuery($sqlT2);
    echo $dao->lastQuery."<br>";
} catch (Exception $e) {
    echo "<h2>Table creation error:</h2>";
    echo $dao->lastError()."-".$e->getMessage()."<br>";
}


try {
    echo "<hr>toList:";
    $results = $dao->select("*")->from("producttype")
        ->where('name=?', ['s', 'Coca-Cola'])
        ->where('idproducttype=?', ['i', 1])
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
        ->where(['name'=>'s','idproducttype'=>'i'],
            ['name'=>'Coca-Cola','idproducttype'=>1])
        ->toList();
    echo $dao->lastQuery;
    echo Collection::generateTable($results);

    echo "<hr>toList using associative array:";
    $results = $dao->select("*")->from("producttype")
        ->where(['name','s','idproducttype','i'],
            ['Coca-Cola',1])
        ->toList();
    echo $dao->lastQuery;
    echo Collection::generateTable($results);

    echo "<hr>toList using associative array:";
    $results = $dao->select("*")->from("producttype")
        ->where(['name','s','Coca-Cola','idproducttype','i',1])
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
        ->where('idproducttype>=?', ['i', 1])
        ->order('idproducttype desc')
        ->toList();
    echo $dao->lastQuery;
    echo Collection::generateTable($results);


    echo "<hr>toResult: ";
    $resultsQuery = $dao->select("*")->from("producttype")
        ->where('name=?', ['s', 'Coca-Cola'])
        ->where('idproducttype=?', ['i', 1])
        ->toResult();
    echo $dao->lastQuery;
    $results=$resultsQuery->fetch_all(PDO::FETCH_ASSOC);
    echo Collection::generateTable($results);
    $resultsQuery->free_result();

    echo "<hr>first: ";
    $results = $dao->select("*")->from("producttype")
        ->where('name=?', ['s', 'Coca-Cola'])
        ->where('idproducttype=?', ['i', 1])
        ->limit('1')
        ->first();
    echo $dao->lastQuery;
    echo Collection::generateTable($results);

    echo "<hr>first returns nothing :";
    $results = $dao->select("*")->from("producttype")
        ->where('name=?', ['s', 'Coca-Cola'])
        ->where('idproducttype=?', ['i', 55])
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
        ->where('idproducttype between ? and ?', ['i', 1, 'i', 3])
        ->toList();
    echo $dao->lastQuery;

    echo Collection::generateTable($results);

    echo "<hr>";
    $results = $dao->select("p.type,count(*) c")->from("producttype p")
        ->group("p.type")
        ->having('p.type>?',['i',0])
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