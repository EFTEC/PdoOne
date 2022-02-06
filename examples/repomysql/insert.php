<?php

use eftec\PdoOne;
use repomysql\TableParentRepo;
use repomysql\TestDb;


include 'common.php';

TestDb::base()->setEncryption('abc.123','somesalt','AES-256-CTR');




$obj= TableParentRepo::factory();
//$obj['idtablaparentPK']=15;
$obj['field']='hi there';
$obj['idchildFK']=1;
$obj['idchild2FK']=2;
$obj['fieldVarchar']='Some text';
$obj['fieldInt']=50;
$obj['fieldDateTime']=new DateTime();
$obj['fielDecimal']=50.4;
$obj['fieldKey']='abcdef';
$obj['fieldUnique']=random_int(0,5000000);
echo "<hr>original:<br>";
new \dBug\dBug($obj);


$id=TableParentRepo::insert($obj);
echo "<hr>read:<br>";
new dbug\dBug(TableParentRepo::first($id));
echo "<hr>time:<br>";
//new dbug\dBug((TableParentRepo::first($id)->fieldDateTime->format('d/m/y')));
