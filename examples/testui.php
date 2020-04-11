<?php

use eftec\PdoOne;
use mapache_commons\Collection;

include "../vendor/autoload.php";

$dao=new PdoOne("mysql","127.0.0.1","root","abc.123","sakila","logpdoone.txt");
$dao->logLevel=2;
$dao->connect(true);

$dao->render();