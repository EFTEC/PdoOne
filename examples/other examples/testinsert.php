<?php

use dBug\dBug;
use eftec\PdoOne;
use mapache_commons\Collection;

include "../../vendor/autoload.php";
include "../dBug.php";
include '../Collection.php';

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
try {
    $dao->runRawQuery($sqlT1);
} catch (Exception $e) {
}



$sqlT2="CREATE TABLE `producttype` (
    `idproducttype` INT NOT NULL,
    `name` VARCHAR(45) NULL,
    `type` int not NULL,
    PRIMARY KEY (`idproducttype`))";
try {
    $dao->runRawQuery($sqlT1);
} catch (Exception $e) {
}
$sqlT2="CREATE TABLE `producttype_auto` (
    `idproducttype` INT NOT NULL AUTO_INCREMENT,
    `name` VARCHAR(45) NULL,
    `type` int not NULL,
    PRIMARY KEY (`idproducttype`))";
try {
    $dao->runRawQuery($sqlT2);
} catch (Exception $e) {
}


try {
    echo "<h1>Table truncate :</h1>";
    $dao->runRawQuery("truncate table typetable");
    echo $dao->lastQuery."<br>";
    $dao->runRawQuery("truncate table producttype");
    $dao->runRawQuery("truncate table producttype_auto");
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
    echo "<h1>Table insert 2</h1>";
    $dao->runRawQuery('insert into `typetable`(`type`,`name`) values(?,?)'
        ,array('i',2,'s','Yummy'));
    echo $dao->lastQuery."<br>";
    echo $dao->affected_rows()."<br>";

    echo "<h1>Table insert 3</h1>";
    // $dao->insert("producttype",['idproducttype','i','name','s','type','i'],[1,'Coca-Cola',1]);
    $dao->from("producttype")
        ->set(['idproducttype',0 
               ,'name','Pepsi' 
               ,'type',1])
        ->insert();
    echo "insert id:".$dao->insert_id()."<br>";
    echo $dao->lastQuery."<br>";
    
    echo "<h1>Table insert 4</h1>";
    $dao->from("producttype_auto")
        ->set(['idproducttype',['i',0] ,'name',['s','Pepsi'] ,'type',['i',1]])
        ->insert();
    echo "insert id (auto):".$dao->insert_id()."<br>";
    echo $dao->lastQuery."<br>";

    echo "<h1>Table insert 5</h1>";
    $object=['name'=>'Coca','type'=>1];
    $dao->insertObject('producttype_auto',$object);
    echo "insert id insertObject(auto):".$object['idproducttype']."<br>";
    echo $dao->lastQuery."<br>";

    echo "<h1>Table insert 6</h1>";
    $dao->from("producttype")
        ->set("idproducttype=?",['i',101])
        ->set('name=?',['s','Pepsi'])
        ->set('type=?',['i',1])
        ->insert();
    echo $dao->lastQuery."<br>";

    echo "<h1>Table insert 7</h1>";
    $dao->from("producttype")
        ->set('(idproducttype,name,type) values (?,?,?)',['i',100,'s','Pepsi','i',1])
        ->insert();
    echo $dao->lastQuery."<br>";

    echo "<h1>Table insert 8</h1>";
    $dao->insert("producttype"
            ,['idproducttype',['i',1] 
		    ,'name',['s','Coca-Cola'] 
		    ,'type',['i',1]]); // type1
    echo $dao->lastQuery."<br>";

    echo "<h1>Table insert 9</h1>";
    $dao->insert("producttype"
        ,['idproducttype','name','type']
        ,[2,'Fanta',1]); // type 2
    echo $dao->lastQuery."<br>";
    //$dao->insert("producttype"
    //    ,['idproducttype'=>'i','name'=>'s','type'=>'i'] // type3 arrays declarative
    //    ,['idproducttype'=>3,'name'=>'Sprite','type'=>'1']);  // with definition of types.
    //echo $dao->lastQuery."<br>";
    
    echo "<h1>Table insert 10</h1>";
    $dao->insert("producttype"
            ,['idproducttype'=>4
		    ,'name'=>"Kellogg's"
		    ,'type'=>2]); // type 4 array declarative, automatic type
    echo $dao->lastQuery."<br>";
    echo "<h1>Table update 10</h1>";
    $dao->update("producttype"
        ,['name'=>"Kellogg's2222"
          ,'type'=>2]
        ,null,['idproducttype'=>4]);
    
    $rows=$dao->runRawQuery('select * from producttype where idproducttype=?',['i',4]);
    new dBug($rows);

    echo "<h1>Table update 11</h1>";
 
    $dao->set(['name'=>"aaaa"
               ,'type'=>2])
        ->where(['idproducttype'=>4])
        ->update('producttype');
    echo "<h2>Table update 11 select</h2>";
    $rows=$dao->runRawQuery('select * from producttype where idproducttype=?',['i',4]);
    new dBug($rows);

    echo "<h1>Table update 12</h1>";

    $dao->set(['name','type'],["bbb",2])
        ->where(['idproducttype'],[4])
        ->update('producttype');
    echo "<h2>Table update 12 select</h2>";
    $rows=$dao->runRawQuery('select * from producttype where idproducttype=?',['i',4]);
    new dBug($rows);

    echo "<h1>Table delete 13</h1>";

    $dao->where(['idproducttype'],[4])
        ->delete('producttype');
    echo "<h2>Table select 13 select</h2>";
    $rows=$dao->runRawQuery('select * from producttype where idproducttype=?',['i',4]);
    new dBug($rows);

} catch (Exception $e) {
    echo "<h2>Table insert error:</h2>";
    echo $dao->lastError()."-".$e->getMessage()."<br>";
}