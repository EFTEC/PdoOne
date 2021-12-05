<?php

$conn = oci_connect("web", "abc.123", "//127.0.0.1/XEPDB1");
if (!$conn) {
    $m = oci_error();
    echo $m['message'], "\n";
    exit;
}
else {
    print "Connected to Oracle!";
}
// Close the Oracle connection
oci_close($conn);

die(1);

$param = $_POST;
$db_username = "web";
$db_password = "abc.123";
$db = "oci:dbname=XEPDB1";
$db='oci:dbname=(DESCRIPTION =(ADDRESS = (PROTOCOL = TCP)(HOST = 127.0.0.1)(PORT = 1521))(CONNECT_DATA =(SERVER = DEDICATED)(SERVICE_NAME = XEPDB1)))';
$conn = new PDO($db, $db_username, $db_password);
