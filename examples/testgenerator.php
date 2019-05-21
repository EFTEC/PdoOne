<?php
use eftec\PdoOne;
use mapache_commons\Collection;

include "../vendor/autoload.php";
include "Collection.php";

// connecting to database sakila at 127.0.0.1 with user root and password abc.123
$dao=new PdoOne("127.0.0.1","root","abc.123","sakila","");
try {
    echo "<h1>connection. The instance 127.0.0.1, base:sakile  user:root and password:abc.123 must exists</h1>";
    $dao->connect();
} catch (Exception $ex) {

}

$table='Compra';
$class='Compra';

$query="SELECT column_name,is_nullable,data_type,column_key,is_nullable
FROM information_schema.columns 
 WHERE table_schema='securitytest' AND table_name='sec_user'
order by ordinal_position
";

$cols=$dao->runRawQuery($query,null,true);
echo Collection::generateTable($cols);

$classtemplate="
class $class {
";
foreach($cols as $col) {
    $classtemplate.="\t var $".$col['COLUMN_NAME'].";\n";
}

$classtemplate.="
}
";

echo "<pre>";
var_dump($classtemplate);

echo "</pre>";
