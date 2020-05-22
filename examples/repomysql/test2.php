<?php /** @noinspection PhpLanguageLevelInspection */


use repo\TablaParentRepo;

include '../../vendor/autoload.php';
include 'TablaParentRepo.php';
include 'TablachildRepo.php';
include 'TablagrandchildRepo.php';


$pdoOne=new \eftec\PdoOne('mysql','127.0.0.1','root','abc.123','sakila');
$pdoOne->connect();
$pdoOne->logLevel=3;
$pdoOne->recursive(['country_id']);
//$city=CityRepo::factory();

$city= TablaparentRepo::setRecursive(['/idchild','/idchild2','/idchild2/idgrandchildFK'])::toList();

echo '<pre>';
var_dump($city);
echo '</pre>';

$city= \repo\TablachildRepo::_toList();

echo '<pre>';
var_dump($city);
echo '</pre>';