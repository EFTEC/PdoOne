<?php

namespace eftec\tests;
use eftec\PdoOne;
use PHPUnit\Framework\TestCase;
use eftec\examples\clitest\testdb2\InvoiceRepo;

class PdoOne_mysq_gen_test2 extends TestCase
{
    public function test1() {
        $pdo=new PdoOne('mysql','127.0.0.1:3306','root','abc.123','testdb2');
        $pdo->logLevel=3;
        $pdo->open();
        $dependency=['/_Customer',
            '/_invoicedetails',
            '/_Customer/_customerxcategories',
            '/_Customer/_City',
            '/_invoicedetails/_Product',
            '/_invoicedetails/_Product/_City',
            '/_invoicedetails/_City'];


    }

}
