<?php

use eftec\PdoOne;

include "../vendor/autoload.php";


// connecting to database sakila at 127.0.0.1 with user root and password abc.123
$dao=new PdoOne("mysql","127.0.0.1","root","abc.123","sakila","");
$dao->logLevel=3;
$dao->throwOnError=true;
$dao->nodeId=1; // optional
$dao->tableSequence='snowflake'; // optional
try {
    echo "<h1>connection. The instance 127.0.0.1, base:sakila  user:root and password:abc.123 must exists</h1>";
    $dao->connect();
    echo "Connected A-OK!<br>";
} catch (Exception $e) {
    echo "<h2>connection error:</h2>";
    echo $dao->lastError()."-".$e->getMessage()."<br>";
    die(1);
}

echo "<h1>creating table sequence:</h1>";
try {

    if ($dao->tableExist('mysequence2')) {
        $dao->dropTable('mysequence2');
        echo "<h3>Table dropped</h3>";
    }
    if ($dao->objectExist('next_mysequence2','function')) {
        $dao->drop('next_mysequence2','function');
        echo "<h3>Function dropped</h3>";
    }
	$dao->createSequence('mysequence2','sequence');
    echo "<h3>Table created</h3>";

} catch (Exception $e) {
	echo "<h2>Error on creation of table</h2>";
	echo $dao->lastError()."-".$e->getMessage()."<br>";
}
echo "<h1>obtaining table sequence:</h1>";
for($i=0;$i<1000;$i++) {
	var_dump($dao->getSequence(false,false,'mysequence2'));
	echo "<br>";
	var_dump($dao->getSequence(true,false,'mysequence2'));
	echo "<br>";
}
