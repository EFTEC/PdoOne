<?php

use eftec\PdoOne;

include '../../vendor/autoload.php';
include '../Collection.php';
include '../dBug.php';

// connecting to database sakila at 127.0.0.1 with user root and password abc.123
$dao = new PdoOne('sqlsrv', 'PCJC\SQLEXPRESS', 'sa', 'abc.123', 'testdb', '');
//$dao = new PdoOne('mysql', '127.0.0.1', 'root', 'abc.123', 'testdb', '');
$dao->logLevel = 3;
try {
    echo '<h1>connection. The instance 127.0.0.1, base:testdb  user:root and password:abc.123 must exists</h1>';
    echo "<a href='https://github.com/EFTEC/example-relationaldatabase'>Database used</a><br><hr>";

    $dao->connect();
} catch (Exception $ex) {
    die($ex->getMessage());
}


$relations = [
    'TableParent'          => 'TableParentRepo',
    'TableChild'           => 'TableChildRepo',
    'TableGrandChild'      => 'TableGrandChildRepo',
    'TableGrandChildTag'   => 'TableGrandChildTagRepo',
    'TableParentxCategory' => 'TableParentxCategoryRepo',
    'TableCategory'        => 'TableCategoryRepo',
    'TableParentExt'       => 'TableParentExtRepo'
];
$tables = $dao->tableSorted();
foreach ($tables as $table) {
    echo "include 'generated/{$table}RepoExt.php';<br>";
    echo "include 'generated/{$table}Repo.php';<br>";
    echo "include 'generatedmodel/Abstract{$table}.php';<br>";
}

$dao->generateCodeClassConversions(['datetime' => 'datetime']);
$logs = $dao->generateAllClasses($relations, 'TestDb', ['reposqlsrv', 'sqlsrv\repomodel'],
    [__DIR__ . '/generated', __DIR__ . '/generatedmodel'], true, [
        'TableParent' => [
            '_idchild2FK'           => 'PARENT',
            '_TableParentxCategory' => 'MANYTOMANY'
        ]
    ]);

echo "errors:<br>";
echo "<pre>";
var_dump($logs);
echo "</pre>";
