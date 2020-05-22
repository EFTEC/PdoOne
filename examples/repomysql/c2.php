<?php /** @noinspection PhpLanguageLevelInspection */

use eftec\PdoOne;
use repo\TablaParentRepo;

include '../../vendor/autoload.php';
include '../Collection.php';

include "tablagrandchildRepo.php";
include "tablachildRepo.php";
include "tablaParentRepo.php";
include "tablacategoryRepo.php";
include "tablaparentxcategoryRepo.php";
include "tablagrandchildcatRepo.php";
include "dBug.php";


// connecting to database sakila at 127.0.0.1 with user root and password abc.123
$pdoOne = new PdoOne("mysql", "127.0.0.1", "travis", "", "travisdb");
$pdoOne->logLevel=3;
try {
    echo '<h1>connection. The instance 127.0.0.1, base:sakila  user:root and password:abc.123 must exists</h1>';
    $pdoOne->connect();
} catch (Exception $ex) {

}


$rows = (TablaParentRepo::setRecursive([
                                           '/idchildFK',
                                           '/idchild2FK',
                                           '/idchildFK/idgrandchildFK',
                                           '/idchildFK/idgrandchildFK/tablagrandchildcat'
                                       ]))::first('1');

new \dBug\dBug($rows);