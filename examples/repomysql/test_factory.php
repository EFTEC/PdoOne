<?php /** @noinspection PhpLanguageLevelInspection */

use eftec\PdoOne;
use mapache_commons\Collection;
use repomysql\TableParentRepo;
use repomysql\TestDb;

include "common.php";
echo "<pre>";

var_dump(TestDb::base()->validateDefTable(
    TableParentRepo::TABLE
    ,TableParentRepo::getDef()
    ,TableParentRepo::getDefKey()
    ,TableParentRepo::getDefFK(true)));

echo "</pre>";


$r=TableParentRepo::factory();

echo Collection::generateTable($r);


$r=TableParentRepo::setRecursive(
    [
        '/idchildFK'
        ,'/idchild2FK'
        ,'/idchildFK/idgrandchildFK'
        ,'/idchildFK/idgrandchildFK/tablagrandchildcat'
        ,'/idchildFK/idgrandchildFK/TableChild'
        ,'/TableParentxCategory' // one to many
        ,'/TableParentxCategory/idcategoryPKFK'
    ])->factory();

new dBug\dBug($r);

