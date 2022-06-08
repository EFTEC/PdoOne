<?php

use eftec\examples\clitest\repo2\TableparentRepo;
use eftec\PdoOne;
use eftec\examples\clitest\repo2\ActorRepo;

include '../../vendor/autoload.php';
include '../dbug.php';

$pdo=new PdoOne('mysql','127.0.0.1:3306','root','abc.123','testdb');
$pdo->logLevel=3;
$pdo->open();
echo "<pre>";
$a1=TableparentRepo::executePlan();
echo "</pre>";
die(1);
$a1= TableparentRepo::setRecursive(['_idchild2FK'])->where(['FieldText'=>'varchar'])->toList();
$query=str_replace(',',",<br>",PdoOne::instance()->lastQuery);

new \dBug\dBug(TableparentRepo::$gQuery);

new \dBug\dBug($a1);

