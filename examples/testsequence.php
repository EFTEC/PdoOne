<?php

use eftec\PdoOne;

include "../vendor/autoload.php";


// connecting to database sakila at 127.0.0.1 with user root and password abc.123
$dao=new PdoOne("mysql","127.0.0.1","root","abc.123","sakila","");
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
	$dao->tableSequence='mysequence2';
	$dao->createSequence();

} catch (Exception $e) {
	echo "<h2>Table created or unable to create</h2>";
	echo $dao->lastError()."-".$e->getMessage()."<br>";
}
echo "<h1>obtaining table sequence:</h1>";
for($i=0;$i<1000;$i++) {
	var_dump($dao->getSequence());
	echo "<br>";
	var_dump($dao->getSequence(true));
	echo "<br>";
}
