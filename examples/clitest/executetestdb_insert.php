<?php

use eftec\examples\clitest\testdb2\CityRepo;
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
$ent=CityRepo::factory();



//$ent=['NumCity' => 555,'NameCity' => 'cityexample'];
//new \dBug\dBug(CityRepo::insert($ent));
//new \dBug\dBug($ent);
$first=CityRepo::first();

new \dBug\dBug($first);
$ent['NumCity']=4;
$ent['Name']='cityexample 555';
new \dBug\dBug($ent);
CityRepo::update($ent);

//CityRepo::deleteById(555);



CityRepo::deleteById(570);

var_dump(CityRepo::toList());
echo "</pre>";
