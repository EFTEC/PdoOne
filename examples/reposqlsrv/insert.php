<?php 

use eftec\PdoOne;
use reposqlsrv\TableParentRepo;


include 'common.php';

$obj= TableParentRepo::factory();
//$obj['idtablaparentPK']=15;
$obj['field']='hi there';
$obj['idchildFK']=1;
$obj['idchild2FK']=2;
$obj['fieldVarchar']='Some text';
$obj['fieldInt']=50;
$obj['fieldDateTime']=new DateTime();
$obj['fielDecimal']=50.4;
$obj['fieldUnique']=mt_rand(0,5000000);



$id=TableParentRepo::insert($obj);

new dbug\dBug(TableParentRepo::first($id));
new dbug\dBug((TableParentRepo::first($id))['fieldDateTime']->format('d/m/y'));
