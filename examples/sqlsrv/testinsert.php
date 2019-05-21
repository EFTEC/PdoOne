<?php /** @noinspection SqlResolve */

/** @noinspection SqlNoDataSourceInspection */

use eftec\PdoOne;

include "../../vendor/autoload.php";
include "../Collection.php";
include "../dBug.php";

// connecting to database sakila at 127.0.0.1 with user root and password abc.123
$dao=new PdoOne("sqlsrv","127.0.0.1","sa","abc.123","sakila","");
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

try {
    echo "<h1>Table truncate :</h1>";
    $dao->runRawQuery("truncate table typetable");
    echo $dao->lastQuery."<br>";
    $dao->runRawQuery("truncate table producttype");
    echo $dao->lastQuery."<br>";
} catch (Exception $e) {
    echo "<h2>Table truncate error:</h2>";
    echo $dao->lastError()."-".$e->getMessage()."<br>";
}
echo "<h1>You should run <a href='testbuilder.php'>testbuilder</a> first</h1>";


try {
    echo "<h1>Table insert (it's ok if it fails because it could exist):</h1>";
    $dao->runRawQuery('insert into typetable(type,name) values(?,?)'
        ,array(PDO::PARAM_INT,1,PDO::PARAM_STR,'Drink'));
    echo $dao->lastQuery."<br>";
    $dao->runRawQuery('insert into typetable(type,name) values(?,?)'
        ,array(PDO::PARAM_INT,2,PDO::PARAM_STR,'Yummy'));
    echo $dao->lastQuery."<br>";

    // $dao->insert("producttype",['idproducttype',PDO::PARAM_INT,'name',PDO::PARAM_STR,'type',PDO::PARAM_INT],[1,'Coca-Cola',1]);
    $dao->from("producttype")
        ->set(['idproducttype',PDO::PARAM_INT,0 ,'name',PDO::PARAM_STR,'Pepsi' ,'type',PDO::PARAM_INT,1])
        ->insert();
    echo $dao->lastQuery."<br>";
    

    $dao->from("producttype")
        ->set("idproducttype=?",[PDO::PARAM_INT,101])
        ->set('name=?',[PDO::PARAM_STR,'Pepsi'])
        ->set('type=?',[PDO::PARAM_INT,1])
        ->insert();
    echo $dao->lastQuery."<br>";

    $dao->from("producttype")
        ->set('(idproducttype,name,type) values (?,?,?)',[PDO::PARAM_INT,100,PDO::PARAM_STR,'Pepsi',PDO::PARAM_INT,1])
        ->insert();
    echo $dao->lastQuery."<br>";

    $dao->insert("producttype"
            ,['idproducttype',PDO::PARAM_INT,1 
		    ,'name',PDO::PARAM_STR,'Coca-Cola' 
		    ,'type',PDO::PARAM_INT,1]); // type1
    echo $dao->lastQuery."<br>";
    $dao->insert("producttype"
        ,['idproducttype',PDO::PARAM_INT,'name',PDO::PARAM_STR,'type',PDO::PARAM_INT]
        ,[2,'Fanta',1]); // type 2
    echo $dao->lastQuery."<br>";
    $dao->insert("producttype"
        ,['idproducttype'=>PDO::PARAM_INT,'name'=>PDO::PARAM_STR,'type'=>PDO::PARAM_INT] // type3 arrays declarative
        ,['idproducttype'=>3,'name'=>'Sprite','type'=>'1']);  // with definition of types.
    echo $dao->lastQuery."<br>";
    $dao->insert("producttype"
            ,['idproducttype'=>4
		    ,'name'=>"Kellogg's"
		    ,'type'=>2]); // type 4 array declarative, automatic type
    echo $dao->lastQuery."<br>";
    $dao->insert("producttype"
	        ,['idproducttype',PDO::PARAM_INT,'name',PDO::PARAM_STR,'type',PDO::PARAM_INT]
	        ,[5,'Chocapic',2]);
    echo $dao->lastQuery."<br>";
    $dao->insert("producttype"
	    ,['idproducttype',PDO::PARAM_INT,'name',PDO::PARAM_STR,'type',PDO::PARAM_INT]
	    ,[6,'CaptainCrunch',2]);
    echo $dao->lastQuery."<br>";
    $dao->insert("producttype"
	    ,['idproducttype',PDO::PARAM_INT,'name',PDO::PARAM_STR,'type',PDO::PARAM_INT]
	    ,[7,'will be deleted 1',2]);
    echo $dao->lastQuery."<br>";
    $dao->insert("producttype"
        ,['idproducttype',PDO::PARAM_INT,'name',PDO::PARAM_STR,'type',PDO::PARAM_INT]
        ,[8,'will be deleted 2',2]);
    echo $dao->lastQuery."<br>";

} catch (Exception $e) {
    echo "<h2>Table insert error:</h2>";
    echo $dao->lastError()."-".$e->getMessage()."<br>";
}