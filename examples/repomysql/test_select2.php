<?php 

use dBug\dBug;
use eftec\PdoOne;
use mapache_commons\Collection;

use mysql\repomodel\TableParentModel;
use repomysql\TableParentRepo;


include "common.php";

new dBug(TableParentRepo::base()->runRawQuery('select * from tableparent',[],true));

$random='';
for($i=0;$i<10;$i++) {
    $random.=chr(mt_rand(64,90));
}

$m=new TableParentModel();
$m->fieldUnique=$random;
$m->fieldKey="key1";
$m->fieldVarchar='varchar';
$m->fieldDateTime=new DateTime();


//TableParentRepo::insert($m);


$parent= (TableParentRepo::setRecursive(
    [
        '_idchildFK',
        '_idchild2FK',
        '_idchildFK/_idgrandchildFK',
        '_idchildFK/_idgrandchildFK/_TableGrandChildTag',
        '_TableParentxCategory' // manytomany
        ,'_TableParentExt'
    ]))::first(1);
new dBug($parent);







//$r=TableParentRepo::toList();

//echo Collection::generateTable($r);
//TableParentRepo::$useModel=false;



$r=(TableParentRepo::setRecursive(
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
    ])) ::toList();

//new dBug($r);

echo Collection::generateTable($r);



//$parent= TablaParentRepo::first(1);
//die(1);
$parent= (TableParentRepo::setRecursive(
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

new dBug($parent);
/*
\repo\TablagrandchildRepo::createTable();
\repo\TablachildRepo::createTable();
\repo\TablaParentRepo::createTable();
*/