<?php

use eftec\PdoOne;

include "../../vendor/autoload.php";

$dao=new PdoOne("mysql","127.0.0.1","root","abc.123","sakila");

$t1=microtime(true);
for($i=0;$i<1000000;$i++) {
	$n = $dao->encryption->encrypt(rand(0,1000));
}
$t2=microtime(true);
echo "encrypt 10m  ".($t2-$t1)."<br>"; // 10m 1.30s

$t1=microtime(true);
for($i=0;$i<1000000;$i++) {
	$n = $dao->getSequencePHP(true);
}
$t2=microtime(true);
echo "encrypt 10m  ".($t2-$t1)."<br>"; // 10m 1.30s

die(1);

$number=(string)getSequencePHP();
$original=$number;

$masks0=[3,4,6,9,2,13,16,0];
$masks1=[5,6,8,2,5,5,3,17];

// 123456789012345678
// 387311051738458035

$c=count($masks0);

for($i=0;$i<$c;$i++) {
	$init=$masks0[$i];
	$end=$masks1[$i];
	$tmp=$number[$end];
	$number=substr_replace($number,$number[$init],$end,1);
	$number=substr_replace($number,$tmp,$init,1);
}

var_dump($number);

for($i=$c-1;$i>=0;$i--) {
	$init=$masks1[$i];
	$end=$masks0[$i];
	$tmp=$number[$end];
	$number=substr_replace($number,$number[$init],$end,1);
	$number=substr_replace($number,$tmp,$init,1);
}


var_dump($original);
var_dump($number);
die(1);


foreach($masks as $init=>$end) {
	$tmp=$number[$end];
	$number=substr_replace($number,$number[$init],$end,1);
	$number=substr_replace($number,$tmp,$init,1);
}

var_dump($number);


foreach($unmask as $init=>$end) {
	$tmp=$number[$end];
	$number=substr_replace($number,$number[$init],$end,1);
	$number=substr_replace($number,$tmp,$init,1);
}
var_dump($number);
die(1);


// 123456789012345678
// 387702040802891395

$mask=[19,18,17,16,15,14,13,12,11,10,9,8,7,6,5,4,3,2,1,0];
$mask=[0,1,2,3,4,5,6,7,8,9,10,11,12,13,14,15,16,17,18,19];
$string=str_pad('0',20-strlen($number)).$number;
$result=""; //str_pad('0',strlen($number));
for ($i = 0; $i < strlen($string); $i++) {
	$result.=$string[$mask[$i]];
	
}
echo "result [$result]<br>";

$ms=microtime(true);
echo microtime(true);
echo "<br>round:";
$t1=microtime(true);
for($i=0;$i<100000;$i++) {
	$calc=round($ms * 1000000) % 4096;
}
$t2=microtime(true);
var_dump($t2-$t1);
echo "<br>fmod:";
$t1=microtime(true);
for($i=0;$i<100000;$i++) {
	$calc=fmod($ms,1)*1000000 % 4096;
}



$t2=microtime(true);
var_dump($t2-$t1);
echo "<br>";

function getSequencePHP($asFloat=false) {
 	$ms=microtime(true);
	$timestamp=(double)round($ms*1000);		
	$rand=(double)round($ms*1000000)%4096; // 4096= 2^12 It is the millionth of seconds
	$calc=(($timestamp-1459440000000)<<22) + (1<<12) + $rand;
	return $calc;
}
echo "<hr>";
echo 100<<2;

echo "<hr>";
echo getSequencePHP();