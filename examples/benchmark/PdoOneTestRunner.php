<?php
require dirname(__FILE__) . '/PdoOneTestSuite.php';
include "../../lib/PdoOne.php";
include "../../lib/PdoOneEncryption.php";
$time = microtime(true);
$memory = memory_get_usage();
$test = new PdoOneTestSuite();
$test->initialize();
$test->run();
echo sprintf(" %11s | %6.2f |\n", number_format(memory_get_usage(true) - $memory), (microtime(true) - $time));