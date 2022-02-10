<?php

use eftec\PdoOne;

include '../../vendor/autoload.php';
echo "<h1>configuring key value</h1>";

$pdo=new PdoOne("mysql","127.0.0.1","root","abc.123","travisdb","");
$pdo->logLevel=3;
$pdo->open();
$pdo->kv('KVTABLA',true);
$pdo->createTableKV();
echo "<pre>";
$pdo->setKV('hello','it is a value',1);
var_dump($pdo->getKV('hello'));
var_dump($pdo->existKV('hello'));
var_dump($pdo->delKV('hello'));
var_dump($pdo->existKV('hello'));
$pdo->setKV('hello','it is a value',1);
var_dump($pdo->getKV('hello'));
sleep(2);
var_dump($pdo->getKV('hello'));
echo "</pre>";
