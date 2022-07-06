<?php /** @noinspection PhpUnhandledExceptionInspection */

use eftec\examples\clitest\testdb2\InvoiceDetailRepo;
use eftec\examples\clitest\testdb2\InvoiceRepo;
use eftec\examples\clitest\testdb2\InvoicetypRepo;
use eftec\examples\clitest\testdb2\InvoicextypRepo;
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



// ['/_InvCustomer','/_Details','/_Types']
/** @var array $a1=InvoiceRepo::factoryUtil() */
$a1=InvoiceRepo::recursive(['/_Types','/_InvCustomer','/_Details'])->first();
var_dump('first:');
new \dBug\dBug($a1);
echo var_export($a1,true);

var_dump('inserting start...............');

$insert=false;

    $a1['_Types'][] = InvoicetypRepo::factory(['NumInvoiceType' => 1, 'NameType' => 'type2']);
    $a1['_Types'][] = InvoicetypRepo::factory(['NumInvoiceType' => 2, 'NameType' => 'type1']);
    $a1['_Types'][] = InvoicetypRepo::factory(['NumInvoiceType' => 3, 'NameType' => 'type2']);
//$a1['_Details'][]= InvoiceDetailRepo::factory(['Product' =>1,'Quantity' => 22.2 ]);

$result=InvoiceRepo::recursive(['/_Types','/_InvCustomer','/_Details'])->update($a1);

$a1=InvoiceRepo::recursive(['/_Types','/_InvCustomer','/_Details'])->first();
new \dBug\dBug($a1);
unset($a1['_Types'][1]);
$result=InvoiceRepo::recursive(['/_Types','/_InvCustomer','/_Details'])->update($a1);

$a1=InvoiceRepo::recursive(['/_Types','/_InvCustomer','/_Details'])->first();
new \dBug\dBug($a1);
var_dump('inserting end...............');

die(1);

var_dump('list');
//$a1=InvoiceRepo::useCache(20)->recursive($dependency)->where(['/_Customer/NumCustomer'=>33])->toList();
//$a1=InvoiceRepo::useCache(20)->recursive($dependency)->where(['/_Customer/NumCustomer'=>11])->first();
//new \dBug\dBug($a1);

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

