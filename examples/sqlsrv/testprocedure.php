<?php /** @noinspection SqlDialectInspection */
/** @noinspection SqlNoDataSourceInspection */

/** @noinspection SqlResolve */

use eftec\PdoOne;

include "../../vendor/autoload.php";


// connecting to database sakila at 127.0.0.1 with user root and password abc.123
$dao=new PdoOne("sqlsrv","PCJC\SQLSERVER2017","sa","abc.123","base1","");
$dao->throwOnError=true;
$dao->logLevel=3;
try {
    echo "<h1>connection. The instancePCJC\SQLSERVER2017, base:base1  user:root and password:abc.123 must exists</h1>";
    $dao->connect();
    echo "Connected A-OK!<br>";
} catch (Exception $e) {
    echo "<h2>connection error:</h2>";
    echo $dao->lastError()."-".$e->getMessage()."<br>";
    die(1);
}

$valores=['param1'=>'hello','param2'=>''];
$result="";

if($dao->objectExist('ping','procedure')) {
    $dao->drop('ping', 'procedure');
}

$dao->createProcedure('ping',[
    ['','param1','varchar(2000)'],
    ['output','param2','varchar(2000)']
],"SET NOCOUNT ON;
	set @param2=@param1 + ' world';
	select 'resultado' col1;");

echo "<pre>result:";
$resultado=$dao->callProcedure('ping',$valores,['param2']);

var_dump($valores);
var_dump($resultado);
var_dump($dao->errorText);

$valores=['param1'=>'hello','param2'=>''];
$result="";

die(1);
/*$stmt=$dao->conn1->prepare("declare @param2 varchar(200);
 exec dbo.ping @param1=:param1, @param2=:param2 output; 
 select @param2 col");*/
$stmt=$dao->conn1->prepare("{call ping (:param1 , :param2)}");

$stmt->bindParam(':param1', $valores['param1']);
$stmt->bindParam(':param2', $valores['param2'], PDO::PARAM_STR|PDO::PARAM_INPUT_OUTPUT, 4000);
// $stmt->bindParam(2, $result, PDO::PARAM_STR|PDO::PARAM_INPUT_OUTPUT, 100);
$stmt->execute();
$stmt->closeCursor();

/*while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    var_dump($row);
}*/

//var_dump($stmt->fetchAll());
//$dao->callProcedure('ping',$valores,['param2']);
//var_dump($dao->errorText);
var_dump($valores);
var_dump($result);
echo "</pre>";