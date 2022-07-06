<?php

use eftec\examples\clitest\testdb2\CityRepo;
use eftec\examples\clitest\testdb2\InvoiceRepo;
use eftec\examples\clitest\testdb2\ProductRepo;
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
ProductRepo::deleteById(99);
$ent=ProductRepo::recursive(['/_CiudadRef' ])->first(1);
//$ent= ProductRepo::factory();
new \dBug\dBug($ent);
$ent['Name']='product #updated 2';
$ent['Ciudad']=1;
$ent['_CiudadRef']=CityRepo::factory(['NumCity' => 1,'Name' => 'City #updated2']);

ProductRepo::recursive(['/_CiudadRef'])->update($ent);
var_dump($ent);
die(1);

//$ent=['NumCity' => 555,'NameCity' => 'cityexample'];
//new \dBug\dBug(CityRepo::insert($ent));
//new \dBug\dBug($ent);



$ent['NumCity']=4;
$ent['Name']='cityexample 555';
new \dBug\dBug($ent);
$r=CityRepo::recursive(['/_customers' ])->update($ent);
var_dump($r);
var_dump('first:');
die(1);
$first=CityRepo::recursive(['*'])->first();
new \dBug\dBug($first);
//CityRepo::deleteById(555);



CityRepo::deleteById(570);

var_dump(CityRepo::toList());
echo "</pre>";
