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
        $a1=InvoiceRepo::executePlan('', null, $dependency);
        //var_export($a1);
        $this->assertEquals(array (
            0 =>
                array (
                    'IdInvoice' => 123,
                    'Customer' => 11,
                    'Total' => '100.0000',
                    'Date' => '2020-01-01',
                    '_Customer' =>
                        array (
                            'IdCustomer' => 11,
                            'Name' => 'Donald',
                            'City' => 1,
                            'Email' => 'aaa@ny.com',
                            '_City' =>
                                array (
                                    'IdCity' => 1,
                                    'Name' => 'New York',
                                ),
                            '_customerxcategories' =>
                                array (
                                    0 =>
                                        array (
                                            'IdCategory' => 1,
                                            'Name' => 'Regular',
                                        ),
                                    1 =>
                                        array (
                                            'IdCategory' => 2,
                                            'Name' => 'Premium',
                                        ),
                                    2 =>
                                        array (
                                            'IdCategory' => 3,
                                            'Name' => 'Golden Card',
                                        ),
                                ),
                        ),
                    '_invoicedetails' =>
                        array (
                            0 =>
                                array (
                                    'IdInvoiceDetail' => 1,
                                    'Invoice' => 123,
                                    'Product' => 1,
                                    'Quantity' => 20,
                                    '_Product' =>
                                        array (
                                            'IdProducts' => 1,
                                            'Name' => 'Coca-Cola',
                                            'City' => 1,
                                            '_City' =>
                                                array (
                                                    'IdCity' => 1,
                                                    'Name' => 'New York',
                                                ),
                                        ),
                                ),
                            1 =>
                                array (
                                    'IdInvoiceDetail' => 2,
                                    'Invoice' => 123,
                                    'Product' => 2,
                                    'Quantity' => 30,
                                    '_Product' =>
                                        array (
                                            'IdProducts' => 2,
                                            'Name' => 'Tea',
                                            'City' => 2,
                                            '_City' =>
                                                array (
                                                    'IdCity' => 2,
                                                    'Name' => 'London',
                                                ),
                                        ),
                                ),
                        ),
                ),
            1 =>
                array (
                    'IdInvoice' => 124,
                    'Customer' => 22,
                    'Total' => '200.0000',
                    'Date' => '2020-01-20',
                    '_Customer' =>
                        array (
                            'IdCustomer' => 22,
                            'Name' => 'Boris',
                            'City' => 2,
                            'Email' => 'bbb@london.com',
                            '_City' =>
                                array (
                                    'IdCity' => 2,
                                    'Name' => 'London',
                                ),
                            '_customerxcategories' =>
                                array (
                                    0 =>
                                        array (
                                            'IdCategory' => 1,
                                            'Name' => 'Regular',
                                        ),
                                ),
                        ),
                    '_invoicedetails' =>
                        array (
                            0 =>
                                array (
                                    'IdInvoiceDetail' => 3,
                                    'Invoice' => 124,
                                    'Product' => 3,
                                    'Quantity' => 40,
                                    '_Product' =>
                                        array (
                                            'IdProducts' => 3,
                                            'Name' => 'Toyota',
                                            'City' => 3,
                                            '_City' =>
                                                array (
                                                    'IdCity' => 3,
                                                    'Name' => 'Tokyo',
                                                ),
                                        ),
                                ),
                        ),
                ),
            2 =>
                array (
                    'IdInvoice' => 125,
                    'Customer' => 33,
                    'Total' => '2000.0000',
                    'Date' => '2022-02-02',
                    '_Customer' =>
                        array (
                            'IdCustomer' => 33,
                            'Name' => 'Fumio',
                            'City' => 3,
                            'Email' => 'ccc@tokyo.com',
                            '_City' =>
                                array (
                                    'IdCity' => 3,
                                    'Name' => 'Tokyo',
                                ),
                            '_customerxcategories' =>
                                array (
                                    0 =>
                                        array (
                                            'IdCategory' => 2,
                                            'Name' => 'Premium',
                                        ),
                                ),
                        ),
                    '_invoicedetails' =>
                        array (
                            0 =>
                                array (
                                    'IdInvoiceDetail' => 4,
                                    'Invoice' => 125,
                                    'Product' => 4,
                                    'Quantity' => 50,
                                    '_Product' =>
                                        array (
                                            'IdProducts' => 4,
                                            'Name' => 'Huawei',
                                            'City' => 4,
                                            '_City' =>
                                                array (
                                                    'IdCity' => 4,
                                                    'Name' => 'Shangai',
                                                ),
                                        ),
                                ),
                        ),
                ),
        ),$a1);
    }

}
