<?php

use dBug\dBug;
use mapache_commons\Collection;
use repomysql\TableChildRepo;
use repomysql\TableParentRepo;


include "common.php";
//$result=TableParentRepo::insert();
$result=TableParentRepo::where('idtablaparentPK=2')->recursive('*')->toListKeyValue();


//$result=TableParentRepo::where('fieldkey',['key1'])->toList(); // ::setRecursive(['*'])
new dBug($result);
die(1);
echo "<br><br>";
$result=TableParentRepo::where('fieldkey',['key1'])->first(); // ::setRecursive(['*'])
new dBug($result);
echo "<br><br>";

$result=TableParentRepo::toList(); // ::setRecursive(['*'])
new dBug($result);
echo "<br><br>";


TableParentRepo::setRecursive([
    '_idchildFK',
    '_TableParentExt',
    '_TableParentxCategory',
    '_idchildFK/_idgrandchildFK',
    '_idchildFK/_idgrandchildFK/_TableChild'
])::testRecursive();




//new dBug(TableParentRepo::base()->runRawQuery('select * from tableparent',[],true));
TableChildRepo::$useModel = false;
$child = TableParentRepo::setRecursive([
    '_idchildFK',
    '_TableParentExt',
    '_TableParentxCategory',
    '_idchildFK/_idgrandchildFK',
    '_idchildFK/_idgrandchildFK/_TableChild'
])::first(2);
new dBug($child);

die(1);

TableParentRepo::$useModel = false;
$parent = (TableParentRepo::setRecursive([
    '_TableParentExt' // onetoone
]))::first(1);
new dBug($parent);

die(1);


$parent = (TableParentRepo::setRecursive([
    '_idchildFK',
    '_idchild2FK',
    '_idchildFK/_idgrandchildFK',
    '_idchildFK/_idgrandchildFK/_TableGrandChildTag',
    '_TableParentxCategory' // manytomany
    ,
    '_TableParentExt'
]))::first(1);
new dBug($parent);


//$r=TableParentRepo::toList();

//echo Collection::generateTable($r);
//TableParentRepo::$useModel=false;


$r = (TableParentRepo::setRecursive([

    '/idchildFK',
    '/idchild2FK',
    '/idchildFK/idgrandchildFK',
    '/idchildFK/idgrandchildFK/tablagrandchildcat',
    '/idchildFK/idgrandchildFK/tablachild',
    '/tablaparentxcategory' // one to many
    ,
    '/idchildFK/idgrandchildFK/tablachild',
    '/tablaparentxcategory/idcategoryPKFK'
    //,'/tablaparentxcategory' // one to many
    //,'/tablaparentxcategory/idcategoryPKFK'
]))::toList();

//new dBug($r);

echo Collection::generateTable($r);


//$parent= TablaParentRepo::first(1);
//die(1);
$parent = (TableParentRepo::setRecursive([
    '/idchildFK',
    '/idchild2FK',
    '/idchildFK/idgrandchildFK',
    '/idchildFK/idgrandchildFK/tablagrandchildcat',
    '/idchildFK/idgrandchildFK/tablachild'
    //,'/idchildFK/idgrandchildFK/tablachild'
    //,'/tablaparentxcategory/idgrandchildFK'
    ,
    '/tablaparentxcategory' // one to many
    ,
    '/tablaparentxcategory/idcategoryPKFK'
]))::first(1);
//var_dump($parent['/idchildFK']['idtablachildPK']);
echo "<br>";

new dBug($parent);
/*
\repo\TablagrandchildRepo::createTable();
\repo\TablachildRepo::createTable();
\repo\TablaParentRepo::createTable();
*/