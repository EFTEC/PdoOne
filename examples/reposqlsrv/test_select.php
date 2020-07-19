<?php /** @noinspection PhpLanguageLevelInspection */

use eftec\PdoOne;
use mapache_commons\Collection;
use reposqlsrv\TableParentRepo;
use reposqlsrv\TestDb;


include "common.php";


$dummies= TestDb::base()->setThrowOnError(false)->select('*')->from('tableparent')->toList();
var_dump(TestDb::base()->errorText);
var_dump($dummies);

die(1);
/*



$r=TableParentRepo::toList();

echo Collection::generateTable($r);




$r=TableParentRepo::setRecursive(
    [

        '_idchildFK'
        ,'_idchild2FK'
        ,'_idchildFK/_idgrandchildFK'
        ,'_idchildFK/_idgrandchildFK/_tablagrandchildcat'
        ,'_idchildFK/_idgrandchildFK/_tablachild'
        ,'_tablaparentxcategory' // one to many
        ,'_idchildFK_idgrandchildFK_tablachild'
        ,'_tablaparentxcategory_idcategoryPKFK'
        //,'_tablaparentxcategory' // one to many
        //,'_tablaparentxcategory/idcategoryPKFK'
    ])::toList();

echo Collection::generateTable($r);

*/

//$parent= TablaParentRepo::first(1);
//die(1);
$parent= (TableParentRepo::setRecursive(
    [
        '_idchildFK',
        '_idchild2FK',
        '_idchildFK/_idgrandchildFK',
        '_idchildFK/_idgrandchildFK/_TableGrandChildTag',
        '_TableParentxCategory' // manytomany
        ,'_TableParentExt'
    ]))::first(1);
new \dBug\dBug($parent);
die(1);

$parent= (TableParentRepo::setRecursive(
    [
        '_idchildFK'
        ,'_idchild2FK'
        ,'_idchildFK/_idgrandchildFK'
        ,'_idchildFK/_idgrandchildFK/_tablachild'
        ,'_idchildFK/_idgrandchildFK/_TableGrandChildTag'
        //,'_idchildFK/idgrandchildFK/tablachild'
        
        //,'_tablaparentxcategory/idgrandchildFK'
        ,'_tablaparentxcategory' // one to many
        ,'_tablaparentxcategory/_idcategoryPKFK'
        ,'_TableParentxCategory'
        ,'_TableParentExt'
    ]))::first(1);
//var_dump($parent['_idchildFK']['idtablachildPK']);
echo "<br>";
new \dBug\dBug($parent);
//echo Collection::generateTable($parent);

/*
\repo\TablagrandchildRepo::createTable();
\repo\TablachildRepo::createTable();
\repo\TablaParentRepo::createTable();
*/