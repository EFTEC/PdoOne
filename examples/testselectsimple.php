<?php

use eftec\PdoOne;
use mapache_commons\Collection;

include "../vendor/autoload.php";
include "Collection.php";
include "dBug.php";
echo "<body><div style='width:600px'>";
// connecting to database sakila at 127.0.0.1 with user root and password abc.123
$dao=new PdoOne("mysql","127.0.0.1","root","abc.123","sakila","logdaoone.txt");
$dao->logLevel=2;
$dao->connect(true);

//try {
	echo "<hr>toList (raw query):";
	$results = $dao->runRawQuery("select * from producttype where name=?",[PDO::PARAM_STR, 'Coca-Cola'],true);
	echo $dao->lastQuery;
	echo Collection::generateTable($results);

/*
} catch(Exception $ex) {
    echo $ex->getMessage();
    echo $ex->getTraceAsString();
}
*/

echo "</div></body>";
function build_table($array){
    if (!isset($array[0])) {
        $tmp=$array;
        $array=array();
        $array[0]=$tmp;
    } // create an array with a single element
    if ($array[0]===null) {
        return "NULL<br>";
    }
    // start table
    $html = '<table style="border: 1px solid black;">';
    // header row
    $html .= '<tr>';
    foreach($array[0] as $key=>$value){
        $html .= '<th>' . htmlspecialchars($key) . '</th>';
    }
    $html .= '</tr>';

    // data rows
    foreach( $array as $key=>$value){
        $html .= '<tr>';
        foreach($value as $key2=>$value2){
            $html .= '<td>' . htmlspecialchars($value2) . '</td>';
        }
        $html .= '</tr>';
    }

    // finish table and return it

    $html .= '</table>';
    return $html;
}