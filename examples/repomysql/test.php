<?php

use eftec\examples\repomysql\repo\ProductcategoryRepo;
use eftec\examples\repomysql\repo\ProductRepo;

include '../../vendor/autoload.php';

$pdo=new \eftec\PdoOne('mysql','127.0.0.1','root','abc.123','api-assembler');
$pdo->logLevel=3;
$pdo->open();

$p= ProductRepo::recursive(['/_IdProductCategory'])->first();


echo "<pre>";
var_dump($p);
echo "</pre>";

$p=ProductRepo::factory();
$p['IdProduct']=555;
$p['Name']='name 555';
//$p['IdProductCategory']=555;

$pc= ProductcategoryRepo::factory();
$pc['IdProductCategory']=555;
$pc['Name']="cat5";

$p['_IdProductCategory']=$pc;

ProductRepo::recursive(['/_IdProductCategory'])->insert($p);
