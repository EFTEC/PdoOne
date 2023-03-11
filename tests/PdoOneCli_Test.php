<?php

namespace eftec\tests;

use eftec\CliOne\CliOne;
use eftec\PdoOneCli;
use Exception;
use PHPUnit\Framework\TestCase;

include_once __DIR__ . '/../lib/PdoOneCli.php';

class PdoOneCli_Test extends TestCase
{
    public function setUp():void {

        chdir(__DIR__);
    }
    /**
     * @return void
     * @throws Exception
     */
    public function test1(): void
    {


        CliOne::testUserInput(null);
        CliOne::testArguments(['program.php',
            'export',
            '--databasetype',
            'mysql',
            '--server',
            '127.0.0.1',
            '--user',
            'root',
            '--password',
            'abc.123',
            '--database',
            'sakila',
            '--input',
            'actor',
            '--output',
            'csv']);
        $p = new PdoOneCli();
        $p->getCli()->echo = false;
        $p->cliEngine();
        $this->assertStringContainsString('1,"PENELOPE"', $p->getCli()->getMemory(true));
        // second test
        CliOne::testArguments(['program.php',
            'export',
            '--databasetype',
            'mysql',
            '--server',
            '127.0.0.1',
            '--user',
            'root',
            '--password',
            'abc.123',
            '--database',
            'sakila',
            '--input',
            'actor',
            '--output',
            'json']);
        $p = new PdoOneCli();
        $p->getCli()->echo = false;
        $p->cliEngine();
        $this->assertStringContainsString('[{"actor_id":1,"first_name":"PENELOPE"', $p->getCli()->getMemory(true));
    }

    /**
     * @throws Exception
     */


    /**
     * @return void
     * @throws Exception
     */
    public function testinteractive1(): void
    {
        chdir(__DIR__);
        CliOne::testUserInput(['mysql', '127.0.0.1', 'root', 'abc.123', 'sakila', 'yes', 'tmp/c1']);
        CliOne::testArguments(['program.php',
            '-i']);
        $p = new PdoOneCli();
        $p->getCli()->echo = true;
        $p->cliEngine();
        $this->assertEquals([
            'databasetype' => 'mysql',
            'server' => '127.0.0.1',
            'user' => 'root',
            'password' => 'abc.123',
            'database' => 'sakila',
            'input' => '',
            'output' => '',
            'namespace' => '',
            'tablexclass' => array(),
            'conversion' => array(),
            'extracolumn' => array(),
            'removecolumn' => array(),
            'columnsTable' => array(),
            'help' => false,
            'classdirectory' => null,
            'classnamespace' => null,
        ], $p->getCli()->readData('tmp/c1')[1]);
    }

    /**
     * @throws Exception
     */
    public function testinteractive2(): void
    {
        chdir(__DIR__);
        CliOne::testUserInput(['mysql', '127.0.0.1', 'root', 'abc.123', 'sakila', 'yes', 'tmp/c1']);
        CliOne::testArguments(['program.php','-cli']);
        $p = new PdoOneCli();
        $p->getCli()->echo = true;
        $p->cliEngine();
        $this->assertEquals([
            'databasetype' => 'mysql',
            'server' => '127.0.0.1',
            'user' => 'root',
            'password' => 'abc.123',
            'database' => 'sakila',
            'input' => '',
            'output' => '',
            'namespace' => '',
            'tablexclass' => array(),
            'conversion' => array(),
            'extracolumn' => array(),
            'removecolumn' => array(),
            'columnsTable' => array(),
            'help' => false,
    'classdirectory' => null,
    'classnamespace' => null,
        ], $p->getCli()->readData('tmp/c1')[1]);
    }
}
