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
$pdo->logLevel=3;
$pdo->open();
$pdo->setCacheService(new CacheServicesmysql());
new \dBug\dBug(InvoiceRepo::recursive(['/_InvCustomer'])->first(227));

new \dBug\dBug(InvoiceRepo::recursive(['/_InvCustomer'])->where("invoices.flag='1'")->toList());
new \dBug\dBug(InvoiceRepo::recursive(['/_InvCustomer'])->where(["FlagAlias"=>'1'])->toList());
die(1);


$id=224;


echo "<h1>delete</h1>";

$new=InvoiceRepo::factory(['Total' => 555,'Date' => '31/01/2020']);
$new['_InvCustomer']=CustomerRepo::factory(['NumCustomer'  => 0,'Name' => 'cus #200','Email' => 'aaa@aaa.com']);
$new['_Details'][]=InvoiceDetailRepo::factory(['Product' => 1,'Quantity' => 444]);
$new['_Details'][]=InvoiceDetailRepo::factory(['Product' => 2,'Quantity' => 400]);
$new['_Details'][]=InvoiceDetailRepo::factory(['Product' => 3,'Quantity' => 600]);
$new['_Types'][]=InvoicetypRepo::factory(['NumInvoiceType' => 1]);
$new['_Types'][]=InvoicetypRepo::factory(['NumInvoiceType' => 2]);
$new['_Types'][]=InvoicetypRepo::factory(['NumInvoiceType' => 3]);
echo "<h1>insert</h1>";
$v=InvoiceRepo::recursive(['/_InvCustomer','/_Details','/_Types' ])->insert($new);

var_dump($v);

$item=InvoiceRepo::recursive(['/_InvCustomer','/_Details','/_Types' ])->first($v);

new \dBug\dBug($item);

echo "<h1>update</h1>";
InvoiceRepo::recursive(['/_InvCustomer','/_Details','/_Types' ])->update($item);

var_dump($v);

$item=InvoiceRepo::recursive(['/_InvCustomer','/_Details','/_Types' ])->first($v);

new \dBug\dBug($item);



echo "<h1>delete</h1>";
$a2=InvoiceRepo::recursive(['/_Types','/_InvCustomer','/_Details'])->deleteById($v);


$item=InvoiceRepo::recursive(['/_InvCustomer','/_Details','/_Types' ])->first($v);

new \dBug\dBug($item);

die(1);

$a1= [
    'NumInvoice' => 200,
    'Customer' => 11,
    'Total' => '100.0000',
    'Date' => '2020-01-01',
    '_InvCustomer' =>
        [
            'NumCustomer' => 11,
            'Name' => 'donald UPDATED',
            'City' => 1,
            'Email' => 'aaa@ny.com',
        ],
    '_Details' =>
        [
            0 =>
                [
                    'NumInvoiceDetail' => 1,
                    'Invoice' => 123,
                    'Product' => 1,
                    'Quantity' => 20,
                ],
            1 =>
                [
                    'NumInvoiceDetail' => 2,
                    'Invoice' => 123,
                    'Product' => 2,
                    'Quantity' => 30,
                ],
        ],
    '_Types' =>
        [
            0 =>
                [
                    'NumInvoiceType' => 1,
                    'NameType' => 'type1',
                ],
            1 =>
                [
                    'NumInvoiceType' => 3,
                    'NameType' => 'type3',
                ],
        ],
];

$a2=InvoiceRepo::recursive(['/_Types','/_InvCustomer','/_Details'])->first($id);
new \dBug\dBug($a2);
die(1);


var_dump('inserting start...............');
echo "<h1>insert</h1>";
$id=InvoiceRepo::recursive(['/_Types','/_InvCustomer*','/_Details*'])->insert($a1);

$a2=InvoiceRepo::recursive(['/_Types','/_InvCustomer','/_Details'])->first($id);
new \dBug\dBug($a2);


die(1);

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

