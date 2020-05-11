<?php
use eftec\PdoOne;
use mapache_commons\Collection;

include '../../vendor/autoload.php';
include '../Collection.php';

// connecting to database sakila at 127.0.0.1 with user root and password abc.123
$dao=new PdoOne('mysql', '127.0.0.1', 'root', 'abc.123', 'pdotest', '');
try {
    echo '<h1>connection. The instance 127.0.0.1, base:sakila  user:root and password:abc.123 must exists</h1>';
    $dao->connect();
} catch (Exception $ex) {

}
echo "<pre>";
$def=$dao->getDefTableKeys('tablaparentxcategory',false);
$clase = $dao->generateCodeClass('tablaparentxcategory', 'repo');
echo htmlentities($clase);
//echo htmlentities(var_dump($def));
echo "</pre>";
//die(1);


$arr=['tablaParent','tablagrandchild','tablaChild','tablaparentxcategory','tablacategory','tablagrandchildcat'];
foreach ($arr as $a) {
    try {
        $clase = $dao->generateCodeClass($a, 'repo');
        echo "saving {$a}Repo.php<br>";
        file_put_contents($a.'Repo.php',$clase);

    } catch (Exception $e) {
        echo "unable to create table $a : ".$e->getMessage()."<br>";
    }
}

