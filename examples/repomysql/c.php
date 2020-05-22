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
$pdoOne=new PdoOne('mysql', '127.0.0.1', 'root', 'abc.123', 'pdotest', '');
$pdoOne->logLevel=3;
try {
    echo '<h1>connection. The instance 127.0.0.1, base:sakila  user:root and password:abc.123 must exists</h1>';
    $pdoOne->connect();
} catch (Exception $ex) {

}

var_dump(TablaParentRepo::validTable());
die(1);

$r=TablaParentRepo::toList();

new \dBug\dBug($r);


$r=TablaParentRepo::setRecursive(
    [
        '/idchildFK'
        ,'/idchild2FK'
        ,'/idchildFK/idgrandchildFK'
        ,'/idchildFK/idgrandchildFK/tablagrandchildcat'
        ,'/idchildFK/idgrandchildFK/tablachild'
        ,'/tablaparentxcategory' // one to many
        ,'/idchildFK/idgrandchildFK/tablachild'
        ,'/tablaparentxcategory/idcategoryPKFK'
        //,'/tablaparentxcategory' // one to many
        //,'/tablaparentxcategory/idcategoryPKFK'
    ])::toList();

new \dBug\dBug($r);




die(1);

//$parent= TablaParentRepo::first(1);
//die(1);
$parent= (TablaParentRepo::setRecursive(
    [
        '/idchildFK'
        ,'/idchild2FK'
        ,'/idchildFK/idgrandchildFK'
        ,'/idchildFK/idgrandchildFK/tablagrandchildcat'
        ,'/idchildFK/idgrandchildFK/tablachild'
        //,'/idchildFK/idgrandchildFK/tablachild'
        //,'/tablaparentxcategory/idgrandchildFK'
        ,'/tablaparentxcategory' // one to many
        ,'/tablaparentxcategory/idcategoryPKFK'
    ]))::first(1);
//var_dump($parent['/idchildFK']['idtablachildPK']);
echo "<br>";
new \dBug\dBug($parent);

/*
\repo\TablagrandchildRepo::createTable();
\repo\TablachildRepo::createTable();
\repo\TablaParentRepo::createTable();
*/