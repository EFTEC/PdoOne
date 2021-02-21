<?php /** @noinspection SqlNoDataSourceInspection */

use eftec\PdoOne;
use mapache_commons\Collection;


include "../../vendor/autoload.php";
include "../Collection.php";
include "../dBug.php";
echo "<body><div style='width:600px'>";
// connecting to database sakila at 127.0.0.1 with user root and password abc.123
$dao=new PdoOne("mysql","127.0.0.1","root","abc.123","sakila","");
$dao->logLevel=3;
try {
    echo "<h1>Connection. The instance {$dao->server}, base:{$dao->db}  user:{$dao->user} and password:{$dao->pwd} must exists</h1>";
    $dao->connect();
    echo "Connected A-OK!<br>";
} catch (Exception $e) {
    echo "<h2>connection error:</h2>";
    echo $dao->lastError()."-".$e->getMessage()."<br>";
    die(1);
}

if($dao->tableExist('mysec_table')) {
    $dao->dropTable('mysec_table');
}
if($dao->objectExist('next_mysec_table','function')) {
    $dao->drop('next_mysec_table','function');
}

$dao->createSequence('mysec_table','sequence');