<?php /** @noinspection PhpLanguageLevelInspection */

use eftec\PdoOne;
use mapache_commons\Collection;
use repomysql\TableParentRepo;

include "common.php";








$r=TableParentRepo::toList();

echo Collection::generateTable($r);




$r=TableParentRepo::setRecursive(
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
echo Collection::generateTable($parent);

/*
\repo\TablagrandchildRepo::createTable();
\repo\TablachildRepo::createTable();
\repo\TablaParentRepo::createTable();
*/