<?php
include '../../vendor/autoload.php';
include 'addressRepo.php';
include 'cityRepo.php';
include 'CountryRepo.php';

$pdoOne=new \eftec\PdoOne('mysql','127.0.0.1','root','abc.123','sakila');
$pdoOne->connect();
$pdoOne->logLevel=3;
$pdoOne->recursive(['country_id']);
//$city=CityRepo::factory();

$city=(CityRepo::setRecursive(['/country_id']))::toList();

echo '<pre>';
var_dump($city);
echo '</pre>';