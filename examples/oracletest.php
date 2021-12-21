<?php
use eftec\PdoOne;
use mapache_commons\Collection;

include "../vendor/autoload.php";
include "Collection.php";
include "dBug.php";


//  The web server will need the environmental variable TNS_ADMIN='Directory of tnsname.ora' unless the default location is used. I use '/etc/tns_admin'. Confirm using the phpinfo().

try {
    $cs='xepdb1';
    # $cs='(DESCRIPTION =(ADDRESS = (PROTOCOL = TCP)(HOST = 127.0.0.1)(PORT = 1521))(CONNECT_DATA =(SERVER = DEDICATED)(SERVICE_NAME = XEPDB1)))';
    //$cs='(DESCRIPTION =(ADDRESS = (PROTOCOL = TCP)(HOST = localhost)(PORT = 1521))(CONNECT_DATA =(SERVER = DEDICATED)(SERVICE_NAME = instancia1)))';
    //$cs='(DESCRIPTION=(ADDRESS=(PROTOCOL=tcp)(HOST=127.0.0.1)(PORT=1521)(CONNECT_DATA =(SERVER = DEDICATED)(SERVICE_NAME = instancia1) ))';
    $cs='(DESCRIPTION = (ADDRESS = (PROTOCOL = TCP)(HOST = PCJC)(PORT = 1521)) (CONNECT_DATA = (SERVER = DEDICATED) (SERVICE_NAME = XEPDB1)))';
    $conn = new PdoOne('oci', $cs, 'books_admin', 'MyPassword');
    $conn->logLevel=3;
    var_dump('open');
    $conn->open(true,true);
} catch(Exception $ex) {
    echo "<pre>";
    var_dump($ex);
    var_dump($conn->lastError());
    echo "</pre>";
    die(1);
}


if(!$conn->tableExist('TABLE1')) {
    $conn->createTable('TABLE1',[
            'COL1'=>'int not null'
            ,'COL2'=>'varchar2(200)'
            ,'COL3'=>'decimal(10,2)'
            ,'COL4'=>'date']
        ,['COL1'=>'PRIMARY KEY'
            ,'COL2'=>'KEY']);
}
echo "<hr>List with limit:<br>";
$data=$conn->select('*')->from('TABLE1')->order('col1')->limit("0,3")->toList();
var_dump($data);
echo "<hr>primary key:<br>";
var_dump($conn->getPK('TABLE1'));
echo "<br>";
var_dump($conn->tableExist('TABLE1'));
echo "<br>";

echo "<pre>getDefTableKeys:";
$keys=$conn->getDefTableKeys('TABLE1');
var_dump($keys);

echo "</pre><br>";

echo "<pre>getDefTable:";
$keys=$conn->getDefTable('TABLE1');
var_dump($keys);
die(1);
echo "</pre><br>";

echo Collection::generateTable($conn->objectList('table'));

IF($conn->tableExist('TABLE3')) {
    $conn->dropTable('TABLE3');
}


IF(!$conn->objectExist('SEQ1','sequence')) {
    $conn->createSequence('SEQ1');
}
echo "<hr>sequence:<br>";
var_dump($conn->getSequence(true,false,'SEQ1'));

$conn->createTable('TABLE3',[
    'col1'=>'int not null'
    ,'col2'=>'varchar2(200)']
    ,['col1'=>'PRIMARY KEY'
    ,'col2'=>'KEY']);


echo Collection::generateTable($data);

echo "loading..";

$data=$conn->getDefTableExtended('TABLE1');

echo "ok";
var_dump($data);
echo Collection::generateTable($data);

