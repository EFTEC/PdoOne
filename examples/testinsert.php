<?php
use eftec\PdoOne;

include "../vendor/autoload.php";
include "dBug.php";

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
    $dao->runRawQuery('insert into `typetable`(`type`,`name`) values(?,?)'
        ,array('i',1,'s','Drink'));
    echo $dao->lastQuery."<br>";
    $dao->runRawQuery('insert into `typetable`(`type`,`name`) values(?,?)'
        ,array('i',2,'s','Yummy'));
    echo $dao->lastQuery."<br>";
    echo $dao->affected_rows()."<br>";
    die(1);

    // $dao->insert("producttype",['idproducttype','i','name','s','type','i'],[1,'Coca-Cola',1]);
    $dao->from("producttype")
        ->set(['idproducttype','i',0 ,'name','s','Pepsi' ,'type','i',1])
        ->insert();
    echo $dao->lastQuery."<br>";

    $dao->from("producttype")
        ->set("idproducttype=?",['i',101])
        ->set('name=?',['s','Pepsi'])
        ->set('type=?',['i',1])
        ->insert();
    echo $dao->lastQuery."<br>";

    $dao->from("producttype")
        ->set('(idproducttype,name,type) values (?,?,?)',['i',100,'s','Pepsi','i',1])
        ->insert();
    echo $dao->lastQuery."<br>";

    $dao->insert("producttype"
            ,['idproducttype','i',1 
		    ,'name','s','Coca-Cola' 
		    ,'type','i',1]); // type1
    echo $dao->lastQuery."<br>";
    $dao->insert("producttype"
        ,['idproducttype','i','name','s','type','i']
        ,[2,'Fanta',1]); // type 2
    echo $dao->lastQuery."<br>";
    $dao->insert("producttype"
        ,['idproducttype'=>'i','name'=>'s','type'=>'i'] // type3 arrays declarative
        ,['idproducttype'=>3,'name'=>'Sprite','type'=>'1']);  // with definition of types.
    echo $dao->lastQuery."<br>";
    $dao->insert("producttype"
            ,['idproducttype'=>4
		    ,'name'=>"Kellogg's"
		    ,'type'=>2]); // type 4 array declarative, automatic type
    echo $dao->lastQuery."<br>";
    $dao->insert("producttype"
	        ,['idproducttype','i','name','s','type','i']
	        ,[5,'Chocapic',2]);
    echo $dao->lastQuery."<br>";
    $dao->insert("producttype"
	    ,['idproducttype','i','name','s','type','i']
	    ,[6,'CaptainCrunch',2]);
    echo $dao->lastQuery."<br>";
    $dao->insert("producttype"
	    ,['idproducttype','i','name','s','type','i']
	    ,[7,'will be deleted 1',2]);
    echo $dao->lastQuery."<br>";
    $dao->insert("producttype"
        ,['idproducttype','i','name','s','type','i']
        ,[8,'will be deleted 2',2]);
    echo $dao->lastQuery."<br>";

} catch (Exception $e) {
    echo "<h2>Table insert error:</h2>";
    echo $dao->lastError()."-".$e->getMessage()."<br>";
}