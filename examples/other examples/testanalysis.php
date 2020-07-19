<?php

use eftec\PdoOne;

include "../autoload.php";


$dao=new PdoOne('mysql',"127.0.0.1","root","abc.123","sakila","logpdoone.txt");
$dao->logLevel=3;
$dao->open();
$dao->throwOnError=true;

$dao->createTable('tabla',[
        'actor_id'=>'smallint unsigned null auto_increment',
        "first_name "=>"varchar(45) null 'ABC'"
    ],"actor_id");


$r=$dao->runRawQuery('show columns from actor',[],true);

foreach($r as $col) {
    echo $col['Field'].' '.$col['Type'].' '.(($col['Type']==='NO')?"not null":'null').' '.$col['Default'].' '.$col['Extra']."<br>";
}

echo "<pre>";
var_dump($r);
echo "</pre>";