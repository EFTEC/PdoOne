<?php /** @noinspection PhpLanguageLevelInspection */

use eftec\PdoOne;
use repo\TablacategoryRepo;
use repo\TablaParentRepo;

include '../../vendor/autoload.php';
include '../Collection.php';

include "tablagrandchildRepo.php";
include "tablachildRepo.php";
include "tablacategoryRepo.php";
include "tablaParentRepo.php";

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


echo "<hr>exists<br>";

$obj2=TablaParentRepo::exist('222');
var_dump($pdoOne->errorText);
var_dump($obj2);


echo "<hr>inserting<br>";
$obj=TablaParentRepo::factoryNull();
$obj['field1']='aaa';
$obj['field2']=date('hh:mi:ss');
$obj['/tablaparentxcategory']=[];
$item=TablacategoryRepo::factoryNull();
$item['IdTablaCategoryPK']=1;
$obj['/tablaparentxcategory'][]=$item;
$item=TablacategoryRepo::factoryNull();
$item['IdTablaCategoryPK']=2;
$obj['/tablaparentxcategory'][]=$item;

$id=TablaParentRepo::setRecursive(['/tablaparentxcategory'])::insert($obj);

new \dBug\dBug($obj);

echo "<hr>exists<br>";

$obj2=TablaParentRepo::exist($obj);

new \dBug\dBug($obj2);

echo "<hr>get<br>";

$obj2=TablaParentRepo::setRecursive(['/tablaparentxcategory'])::first($id);

new \dBug\dBug($obj2);



TablaParentRepo::setRecursive(['/tablaparentxcategory'])::delete($obj);

die(1);

$obj=TablaParentRepo::setRecursive(['/tablaparentxcategory'])::first(49);

new \dBug\dBug($obj);

//unset($obj['/tablaparentxcategory'][1]);

$n1=TablacategoryRepo::factoryNull();
$n1['IdTablaCategoryPK']='19';
$n1['Name']='??';
$obj['/tablaparentxcategory'][]=$n1;

new \dBug\dBug($obj);

TablaParentRepo::setRecursive(['/tablaparentxcategory'])::update($obj);

new \dBug\dBug($obj);

//new \dBug\dBug(TablaParentRepo::setRecursive(['/tablaparentxcategory'])::first(49));
die(1);



$obj=TablaParentRepo::factoryNull();
$obj['field1']='aa49';
$obj['field2']='b49';
$obj['/tablaparentxcategory']=[];

$cat=TablacategoryRepo::factoryNull();


$obj['/tablaparentxcategory'][]= ['IdTablaCategoryPK'=>3,'Name'=>'cat #3'];
$obj['/tablaparentxcategory'][]= ['IdTablaCategoryPK'=>4,'Name'=>'cat #4'];

TablaParentRepo::insert($obj);
die(1);

var_dump(TablaParentRepo::exist(['idtablaparentPK'=>1]));
die(1);


$r=TablaParentRepo::setRecursive(
    [

        '/idchildFK',
        '/idchildFK/idgrandchildFK',
        '/idchildFK/idgrandchildFK/tablagrandchildcat'
        ,'/tablaparentxcategory' // manytomany
    ])::toList();

new \dBug\dBug($r);

die(1);

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