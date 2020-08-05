<?php 

use dBug\dBug;
use eftec\PdoOne;
use mapache_commons\Collection;

use mysql\repomodel\TableParentModel;
use repomysql\TableParentRepo;


include "common.php";

new dBug(TableParentRepo::base()->runRawQuery('select * from tableparent',[],true));


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
die(1);






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