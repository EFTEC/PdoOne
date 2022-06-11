<?php

use eftec\examples\clitest\testdb2\InvoiceRepo;
use eftec\PdoOne;
use eftec\examples\clitest\testdb2\CustomerRepo;


include '../../vendor/autoload.php';
include '../dbug.php';
include 'cachetest.php';

$pdo=new PdoOne('mysql','127.0.0.1:3306','root','abc.123','testdb2');
$pdo->setCacheService(new CacheServicesmysql());


$pdo->logLevel=3;
$pdo->open();
echo "<pre>";
//::setRecursive(['_customerxcategories',''])
//$a1=CustomerRepo::executePlan();
$dependency=[
    '/_invoicedetails',
    '/_Customer',
    '/_Customer/_customerxcategories',
    '/_Customer/_customerxcategories/_Category', // added automatically
    '/_Customer/_customerxcategories/_Customer',
    '/_Customer/_customerxcategories/_Customer/_City',
    '/_Customer/_City',
    '/_invoicedetails/_Product',
    '/_invoicedetails/_Product/_City',
    '/_invoicedetails/_City'
];
var_dump('list');
//$a1=InvoiceRepo::useCache(20)->recursive($dependency)->where(['/_Customer/NumCustomer'=>33])->toList();
$a1=InvoiceRepo::useCache(20)->recursive($dependency)->where(['/_Customer/NumCustomer'=>11])->first();
new \dBug\dBug($a1);
die(1);
var_dump('list simple');
$a1=InvoiceRepo::useCache(20)->toList();
$a1=InvoiceRepo::useCache(20)->toList();
new \dBug\dBug($a1);

var_dump('first');
$a1=InvoiceRepo::useCache(20)->recursive($dependency)->first(125);
$a1=InvoiceRepo::useCache(20)->recursive($dependency)->first(125);
new \dBug\dBug($a1);
var_dump('first simple');
$firstSimple=InvoiceRepo::first(125);
new \dBug\dBug($firstSimple);

echo "</pre>";
die(1);
$a1= InvoiceRepo::setRecursive(['/_idchild2FK'])->where(['FieldText'=>'varchar'])->toList();
$query=str_replace(',',",<br>",PdoOne::instance()->lastQuery);

new \dBug\dBug(InvoiceRepo::$gQuery);

new \dBug\dBug($a1);

