<?php /** @noinspection PhpLanguageLevelInspection */

use eftec\PdoOne;
use mapache_commons\Collection;
use reposqlsrv\TableParentRepo;

include "common.php";








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
    ])::factory();
    
new dBug\dBug($r);

