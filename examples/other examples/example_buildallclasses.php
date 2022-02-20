<?php /** @noinspection PhpUnhandledExceptionInspection */

use eftec\PdoOne;

include "../../vendor/autoload.php";

// connecting to database sakila at 127.0.0.1 with user root and password abc.123

$dao=new PdoOne('mysql',"127.0.0.1","root","abc.123","ecospace","logpdoone.txt");
//$dao = new PdoOne('sqlsrv', "(local)\sqlexpress", "sa", "abc.123", "sakila", "logpdoone.txt");
$dao->logLevel = 3;
$dao->open();
$tables=$dao->tableSorted(3, false, false);
echo "<hr>";
echo "<pre>";
foreach($tables as $table) {
    $code=$dao->generateAbstractClass($table, 'termo2\test\dbtest\repo');
    $className=PdoOne::camelize($table);
    echo "// include 'ecospace/".$className."Repo.php';\n";
    echo '$result["'.$className.'"]=(\\termo2\\test\\dbtest\\repo\\'.$className.'Repo::validTable());'."\n";
    file_put_contents(__DIR__."/ecospace/".$className."Repo.php",$code);
    //echo "$table<br>";
}
echo "</pre>";
