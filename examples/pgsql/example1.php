<?php

use eftec\PdoOne;

include "../../vendor/autoload.php";
include "../Collection.php";
include "../dBug.php";
//header('Content-Type: text/html; charset=ISO-8859-1');
//echo '<meta http-equiv="Content-Type" content="text/html; charset=ISO-8859-1" />';
echo "<body>";
//$cstring="pgsql:host=localhost;port=5432;dbname=postgres;options='--client_encoding=UTF8'user=postgres;password=abc.123";
//$p=new PDO($cstring);


$pdo=new PdoOne('pgsql','localhost','postgres','abc.123','termo2');
$pdo->logLevel=4;
$pdo->connect();
echo "<pre>";
//$pdo->truncate('customers');
//$pdo->resetIdentity('customers',1,'id');
var_dump($pdo->columnTable('customers'));
var_dump($pdo->objectList('procedure'));

//var_dump($pdo->select('*')->from('customers')->toList());
//var_dump($pdo->insert_id('customers_id_seq'));

/*$id=$pdo->insert('customers',['nombre'=>'new'],null,'id');
//$id=$pdo->insert('customers',['nombre'=>'new'],null);

//var_dump($pdo->objectList('table'));

var_dump($id);
var_dump($pdo->getPK('customers'));
*/
