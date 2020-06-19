<?php
use eftec\PdoOne;
use mapache_commons\Collection;

include '../../vendor/autoload.php';
include '../Collection.php';
include "dBug.php";

// connecting to database sakila at 127.0.0.1 with user root and password abc.123
$dao=new PdoOne('sqlsrv', 'PCJC\SQLEXPRESS', 'sa', 'abc.123', 'testdb', '');
try {
    echo '<h1>connection. The instance 127.0.0.1, base:sakila  user:root and password:abc.123 must exists</h1>';
    $dao->connect();
} catch (Exception $ex) {

}

//var_dump($dao->getDefTableKeys('tablacategory',true,'PRIMARY KEY'));

/*
echo "<pre>";
$def=$dao->getDefTableKeys('tablaparentxcategory',false);
$clase = $dao->generateCodeClass('tablaparentxcategory', 'repo');
echo htmlentities($clase);
//echo htmlentities(var_dump($def));
echo "</pre>";
//die(1);
*/



$relations=['tablaparent' =>'TablaParentRepo'
            ,'tablaparent_ext' =>'TablaParentExtRepo'
            ,'tablachild'=>'TablachildRepo'
            ,'tablaparentxcategory'=>'TablaparentxcategoryRepo'
            ,'tablacategory'=>'TablacategoryRepo'
            ,'tablagrandchildcat'=>'TablagrandchildcatRepo'
            ,'tablagrandchild'=>'TablagrandchildRepo'];

$logs = $dao->generateAllClasses($relations, 'TestDb', 'repo', __DIR__, false, [
    'tablaparent' => [
        '/idchild2FK'           => 'PARENT',
        '/tablaparentxcategory' => 'MANYTOMANY'
    ]
]);

echo "errors:<br>";
echo "<pre>";
var_dump($logs);
echo "</pre>";

