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


$classCode=$dao->generateBaseClass('TestDb','repo'
    ,$relations);
file_put_contents('TestDb.php',$classCode);

foreach ($relations as $tableClassName=>$className) {
  

    
    try {
        $clase = $dao->generateCodeClass($tableClassName, 'repo',['/idchild2FK'             =>'PARENT'
                                                                  , '/tablaparentxcategory' =>'MANYTOMANY']
            ,$relations,[],[],[],'TestDB');
        $claseRepo = $dao->generateCodeClassRepo($tableClassName, 'repo',$relations);
        echo "saving {$className}Ext.php<br>";
        
        file_put_contents($className.'Ext.php',$clase);
        if(!file_exists($className.'.php')) {
            echo "saving {$className}.php<br>";
            // we don't want to replace this class.
            file_put_contents($className.'.php',$claseRepo);    
        }
        

    } catch (Exception $e) {
        echo "unable to create table $tableClassName : ".$e->getMessage()."<br>";
    }
}

