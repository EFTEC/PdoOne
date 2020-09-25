<?php

use eftec\PdoOne;

include "../vendor/autoload.php";
include "Collection.php";
include "dBug.php";

$pdoOne=new PdoOne('oci','localhost/orcl','c##jorge',"abc.123",'c##jorge');
$pdoOne->open();

var_dump($pdoOne->runRawQuery('select * from dummy'));

echo "ok";
