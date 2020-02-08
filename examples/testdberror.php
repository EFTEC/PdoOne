<?php

use eftec\PdoOne;


include "../vendor/autoload.php";


// connecting to database sakila at 127.0.0.1 with user root and password abc.123



$dao=new PdoOne('mysql',"127.0.0.1","root","abc.123","sakilaxxxx","logpdoone.txt");
$dao->logLevel=3;
$dao->throwOnError=true;

try {
    echo "<h1>connection error</h1>";
    $dao->connect();
    echo "Connected A-OK!<br>";
} catch (Exception $e) {
    echo "<h1>connection error:</h1>";
    echo $dao->lastError()."-".$e->getMessage()."<br>";
}



try {
    $dao->select('1')->from('dual')->toList();
    $dao->runRawQuery("select 1 from dual");
} catch (Exception $e) {
    echo "<h1>select 1 from dual failed:</h1>";
    echo "Last Error:".$dao->lastError()."- GetMessage:".$e->getMessage()."<br>";
}


echo "Status:";
if (!$dao->isOpen) {
    echo "<img style='width: 14px; height: 14px;' src='https://assets-cdn.github.com/images/icons/emoji/unicode/274c.png'/><br>";
} else {
    echo "<img style='width: 14px; height: 14px;'  src='https://assets-cdn.github.com/images/icons/emoji/unicode/2714.png'/><br>";
}


$dao=new PdoOne("mysql","127.0.0.1","root","abc.123","sakila","logpdoone.txt");
$dao->logLevel=3;
try {
    echo "<h1>connection</h1>";
    $dao->connect();
    echo "Connected A-OK!<br>";
} catch (Exception $e) {
    echo "<h2>connection error:</h2>";
    echo $dao->lastError()."-".$e->getMessage()."<br>";
    die(1);
}



echo "Status:";
if (!$dao->isOpen) {
    echo "<img style='width: 14px; height: 14px;' src='https://assets-cdn.github.com/images/icons/emoji/unicode/274c.png'/><br>";
} else {
    echo "<img style='width: 14px; height: 14px;'  src='https://assets-cdn.github.com/images/icons/emoji/unicode/2714.png'/><br>";
}

$table="CREATE TABLE `product` (
  `idproduct` INT NOT NULL AUTO_INCREMENT,
  `name` VARCHAR(45) NULL,
  PRIMARY KEY (`idproduct`));";

$now=new DateTime();
// running a raw query (unprepared statement)
try {
    echo "<h1>Table creation:</h1>";
    $dao->runRawQuery($table);
} catch (Exception $e) {
    echo "<h2>Table creation error:</h2>";
    echo $dao->lastError()."-".$e->getMessage()."<br>";
}

try {
    echo "<h1>Nested Error: from()->insert()->set()</h1>";
    $dao->from("table")
        ->insert()
        ->set();
} catch (Exception $e) {
    echo "<h2>Nested Error:</h2>";
    echo $e->getMessage()."<br>";
}
try {
    echo "<h1>PK error: from()->set()->insert()</h1>";
    $dao->from("product")
        ->set(['idproduct','i',1])
        ->insert();
    $dao->from("product")
        ->set(['idproduct','i',1])
        ->insert();
} catch (Exception $e) {
    echo "<h2>Nested Error:</h2>";
    echo $e->getMessage()."<br>";
}



try {
    echo "<h1>Nested Error: from()->set()->where()</h1>";
    $dao->from("table")
        ->where(['b1'=>1])
        ->set(['a1'=>1]);
} catch (Exception $e) {
    echo "<h2>Nested Error:</h2>";
    echo $e->getMessage()."<br>";
}

// running a prepared statement
try {
    echo "<h1>Inserting Cocacola (prepared)</h1>";
    $sql="insert into `product11`(name) values(?)";
    echo "testing...";
    $stmt=$dao->prepare($sql);
    $productName="Cocacola";
    $stmt->bind_param("s",$productName); // s stand for string. Also i =integer, d = double and b=blob
    $dao->runQuery($stmt);
    echo "Last id inserted :".$dao->insert_id()."<br>";
} catch (Exception $e) {
    echo "<h2>Insert Cocacola (prepared) error:</h2>";
    echo $dao->lastError()."-".$e->getMessage()."<br>";
}
try {
    echo "<h1>Inserting O'Hara (prepared)</h1>";
    $sql="insert into `product`(name) values(?)";
    $stmt=$dao->prepare($sql);
    $productName="222222222222222222222222222222222222222222222222222222222222222222222222222222222222222222222222222222";
    $stmt->bind_param("s",$productName); // s stand for string. Also i =integer, d = double and b=blob
    $dao->runQuery($stmt);
    echo "Last id inserted :".$dao->insert_id()."<br>";
} catch (Exception $e) {
    echo "<h2>Insert (prepared) error:</h2>";
    echo $dao->lastError()."-".$e->getMessage()."<br>";
}


// returning data (using a prepared statement)
try {
    echo "<h1>select (prepared)</h1>";
    $sql="select * from `product33` order by name";
    $stmt=$dao->prepare($sql);
    $dao->runQuery($stmt);

    $rows = $stmt->get_result();

    echo "<table><tr><th>Id</th><th>Name</th></tr>";

    // first method
    while ($row = $rows->fetch_assoc()) {
        echo "<tr><td>".$row['idproduct']."</td><td>".$row['name']."</td></tr>";
    }
    echo "</table><br>";
    // second method (fetch all fields)
    //$allRows=$rows->fetch_all(PDO::FETCH_ASSOC);

} catch (Exception $e) {
    echo "<h2>select (prepared) error:</h2>";
    echo $dao->lastError()."-".$e->getMessage()."<br>";
}

try {
	echo "<h1>select (prepared with param)</h1>";
	$sql="select * from `product33`";
	$dao->select($sql)->where('field=?',[20])->runGen();


} catch (Exception $e) {
	echo "<h2>select (prepared with param) error:</h2>";
	echo $dao->lastError()."-".$e->getMessage()."<br>";
}



// running a transaction
try {
    echo "<h1>Insert transactional (prepared)</h1>";
    $sql="insert into `product`(name) values(?)";
    $dao->startTransaction();
    $stmt=$dao->prepare($sql);
    $productName="Fanta";
    $stmt->bind_param("s",$productName); // s stand for string. Also i =integer, d = double and b=blob
    $dao->runQuery($stmt);
    echo "Last id inserted :".$dao->insert_id()."<br>";
    $dao->commit(); // end transaction
} catch (Exception $e) {
    echo "<h1>Insert transactional error:</h1>";
    echo $dao->lastError()."-".$e->getMessage()."<br>";

    $dao->rollback(); // cancel transaction

}