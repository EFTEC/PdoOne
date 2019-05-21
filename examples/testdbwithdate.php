<?php

use eftec\PdoOne;

include "../vendor/autoload.php";


// connecting to database sakila at 127.0.0.1 with user root and password abc.123
$dao=new PdoOne("127.0.0.1","root","abc.123","sakila","");
try {
    echo "<h1>connection</h1>";
    $dao->connect();
    echo "Connected A-OK!<br>";
} catch (Exception $e) {
    echo "<h1>connection error:</h1>";
    echo $dao->lastError()."-".$e->getMessage()."<br>";
    die(1);
}
$sql="CREATE TABLE `productdate` (
  `idproduct` INT NOT NULL AUTO_INCREMENT,
  `name` VARCHAR(45) NULL,
  `coldate` date NULL,
  `coldatetime` datetime NULL,
  `coltimestamp` timestamp NULL,
  PRIMARY KEY (`idproduct`));";

$now=new DateTime();
echo "<h1>Date Convertion with epoch 2000-01-01:</h1>";
echo "PdoOne::dateTimeSql2PHP(null) : ";
var_dump(PdoOne::dateTimeSql2PHP(null));

echo "<br>PdoOne::unixtime2Sql(null) : ";
var_dump(PdoOne::unixtime2Sql(null));

echo "<br>PdoOne::dateTimePHP2Sql(null) : ";
var_dump(PdoOne::dateTimePHP2Sql(null));

echo "<h1>Date Convertion with epoch null:</h1>";
PdoOne::$dateEpoch=null;
echo "PdoOne::dateTimeSql2PHP(null) : ";
var_dump(PdoOne::dateTimeSql2PHP(null));

echo "<br>PdoOne::unixtime2Sql(null) : ";
var_dump(PdoOne::unixtime2Sql(null));

echo "<br>PdoOne::dateTimePHP2Sql(null) : ";
var_dump(PdoOne::dateTimePHP2Sql(null));



// running a raw query (unprepared statement)
try {
    echo "<h1>Table creation:</h1>";
    $dao->runRawQuery($sql);
} catch (Exception $e) {
    echo "<h1>Table creation error:</h1>";
    echo $dao->lastError()."-".$e->getMessage()."<br>";
}

// running a prepared statement
try {
    echo "<h1>Inserting Cocacola with the current date (prepared)</h1>";
    $sql="insert into `productdate`(name,coldate,coldatetime,coltimestamp) values(?,?,?,?)";
    $stmt=$dao->prepare($sql);
    $productName="Cocacola";
    $colDate=PdoOne::dateTimePHP2Sql($now);
    $colDateTime=PdoOne::dateTimePHP2Sql($now);
    $colTimestamp=PdoOne::dateTimePHP2Sql($now);
    $stmt->bind_param("ssss",$productName,$colDate,$colTimestamp,$colTimestamp); // s stand for string. Also i =integer, d = double and b=blob
    $dao->runQuery($stmt);
    echo "Last id inserted :".$dao->insert_id()."<br>";
} catch (Exception $e) {
    echo "<h1>Insert Cocacola (prepared) error:</h1>";
    echo $dao->lastError()."-".$e->getMessage()."<br>";
}

// running a prepared statement
try {
    echo "<h1>Inserting Cocacola with null date (prepared)</h1>";
    $sql="insert into `productdate`(name,coldate,coldatetime,coltimestamp) values(?,?,?,?)";
    $stmt=$dao->prepare($sql);
    $productName="Cocacola";
    $null=null;
    $stmt->bind_param("ssss",$productName,$null,$null,$null); // s stand for string. Also i =integer, d = double and b=blob
    $dao->runQuery($stmt);
    echo "Last id inserted :".$dao->insert_id()."<br>";
} catch (Exception $e) {
    echo "<h1>Insert Cocacola (prepared) error:</h1>";
    echo $dao->lastError()."-".$e->getMessage()."<br>";
}

// returning data (using a prepared statement)
try {
    echo "<h1>select (prepared)</h1>";
    $sql="select * from `productdate` order by name";
    $stmt=$dao->prepare($sql);
    $dao->runQuery($stmt);

    $rows = $stmt->get_result();

    echo "<table cellpadding='4px'><tr>
		<th>Id</th><th>Name</th><th>Date</th><th>DateTime</th><th>Timestamp</th>
		<th>Date Text</th><th>DateTime Text</th><th>Timestamp Text</th></tr>";

    // first method
    while ($row = $rows->fetch_assoc()) {
        echo "<tr>
        <td>{$row['idproduct']}</td>
        <td>{$row['name']}</td>
        <td>".showDate(PdoOne::dateTimeSql2PHP($row['coldate']))."</td>
        <td>".showDate(PdoOne::dateTimeSql2PHP($row['coldatetime']))."</td>
        <td>".showDate(PdoOne::dateTimeSql2PHP($row['coltimestamp']))."</td>
        <td>".PdoOne::dateSql2Text($row['coldate'])."</td>
        <td>".PdoOne::dateSql2Text($row['coldatetime'])."</td>
        <td>".PdoOne::dateSql2Text($row['coltimestamp'])."</td>
        </tr>";
    }
    echo "</table><br>";
    echo "<hr>";
    echo "date:";
    
    echo PdoOne::dateText2Sql("15/09/2018",false);
    
    // second method (fetch all fields)
    //$allRows=$rows->fetch_all(PDO::FETCH_ASSOC);

} catch (Exception $e) {
    echo "<h1>select (prepared) error:</h1>";
    echo $dao->lastError()."-".$e->getMessage()."<br>";
}

function showDate($date) {
	if ($date===null) return "null";
	return $date->format('r');
}
