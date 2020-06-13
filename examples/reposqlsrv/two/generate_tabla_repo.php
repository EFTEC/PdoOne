<?php
use eftec\PdoOne;
use mapache_commons\Collection;

include '../../../vendor/autoload.php';
include '../../Collection.php';
include "../dBug.php";

// connecting to database sakila at 127.0.0.1 with user root and password abc.123
$dao=new PdoOne('mysql', '127.0.0.1', 'root', 'abc.123', 'miniblog', '');
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

$arr=['tablaparent','tablaparent_ext','tablagrandchild','tablachild','tablaparentxcategory','tablacategory','tablagrandchildcat'];
$arr=['Blog','BlogImage'];
foreach ($arr as $a) {
    try {
        $clase = $dao->generateCodeClass($a, 'repo',['/idchild2FK'=>'PARENT'
        ,'/tablaparentxcategory'=>'MANYTOMANY'],'Dao');
        $claseRepo = $dao->generateCodeClassRepo($a, 'repo','Repo','Dao');
        echo "saving {$a}Dao.php<br>";
        echo "saving {$a}Repo.php<br>";
        file_put_contents($a.'Dao.php',$clase);
        if(!file_exists($a.'Repo.php')) {
            // we don't want to replace this class.
            file_put_contents($a.'Repo.php',$claseRepo);    
        }
        

    } catch (Exception $e) {
        echo "unable to create table $a : ".$e->getMessage()."<br>";
    }
}

