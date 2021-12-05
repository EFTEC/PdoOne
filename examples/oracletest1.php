<?php

use eftec\PdoOne;

include "../vendor/autoload.php";
include "Collection.php";
include "dBug.php";

$pdoOne=new PdoOne('oci','127.0.0.1:1521/XEPDB1','web',"abc.123",'web');
//$pdoOne=new PdoOne('oci','XEPDB1','web',"abc.123",'web');
$pdoOne->logLevel=3;
$pdoOne->open();



var_dump($pdoOne->runRawQuery('select * from dummy'));

echo "ok";
