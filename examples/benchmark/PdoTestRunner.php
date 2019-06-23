<?php
require dirname(__FILE__) . '/PDOTestSuite.php';
$time = microtime(true);
$memory = memory_get_usage();
$test = new PDOTestSuite();
$test->initialize();
$test->run();
echo sprintf(" %11s | %6.2f |\n", number_format(memory_get_usage(true) - $memory), (microtime(true) - $time));