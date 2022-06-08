<?php
/** @noinspection AccessModifierPresentedInspection
 * @noinspection PhpUnusedAliasInspection
 * @noinspection UnknownInspectionInspection
 * @noinspection PhpUnused
 * @noinspection ReturnTypeCanBeDeclaredInspection
 */
namespace eftec\examples\clitest\testdb2;

use Exception;

/**
 * Class InvoiceRepo Copyright (c) Jorge Castro C. (https://github.com/EFTEC/PdoOne)<br>
 * <ul>
 * <li>NumInvoice: int (alias of column IdInvoice) </li>
 * <li>Customer: int (alias of column Customer) </li>
 * <li>Total: float (alias of column Total) </li>
 * <li>Date: date (alias of column Date) </li>
 * <li>_Customer: MANYTOONE (alias of column _Customer) (CustomerRepoModel)</li>
 * <li>_invoicedetails: ONETOMANY (alias of column _invoicedetails) (InvoicedetailRepoModel)</li>
 * </ul>
 * Generated by PdoOne Version 2.32.1 Date generated Sun, 05 Jun 2022 09:16:35 -0300.<br>
 * <b>YOU CAN EDIT THIS CODE</b>. It is not replaced by the generation of the code, unless it is indicated<br>
 * <pre>
 * $code=$pdoOne->generateCodeClassRepo(''invoices'',''eftec\examples\clitest\testdb2'','array('cities'=>'CityRepo','categories'=>'CategoryRepo','customers'=>'CustomerRepo','customerxcategories'=>'CustomerXCategoryRepo','invoicedetails'=>'InvoicedetailRepo','invoices'=>'InvoiceRepo','products'=>'ProductRepo',)','''','array(0=>array(0=>'IdInvoice',1=>'int',2=>NULL,),1=>array(0=>'Customer',1=>'int',2=>NULL,),2=>array(0=>'Total',1=>'float',2=>NULL,),3=>array(0=>'Date',1=>'date',2=>NULL,),4=>array(0=>'_Customer',1=>'MANYTOONE',2=>'CustomerRepoModel',),5=>array(0=>'_invoicedetails',1=>'ONETOMANY',2=>'InvoicedetailRepoModel',),)','array('categories'=>array('IdCategory'=>'NumCategory','Name'=>'Name',),'cities'=>array('IdCity'=>'NumCity','Name'=>'NameCity',),'customers'=>array('City'=>'City','Email'=>'Email','IdCustomer'=>'NumCustomer','Name'=>'Name',),'customerxcategories'=>array('Category'=>'Category','Customer'=>'Customer',),'invoicedetails'=>array('IdInvoiceDetail'=>'NumInvoiceDetail','Invoice'=>'Invoice','Product'=>'Product','Quantity'=>'Quantity',),'invoices'=>array('Customer'=>'Customer','Date'=>'Date','IdInvoice'=>'NumInvoice','Total'=>'Total',),'products'=>array('City'=>'City','IdProducts'=>'Numproduct','Name'=>'Name',),)');
 * </pre>
 * @see CustomerRepoModel
 * @see InvoicedetailRepoModel
 */
class InvoiceRepo extends AbstractInvoiceRepo
{
    const ME=__CLASS__;
    


}
