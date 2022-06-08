<?php
/** @noinspection AccessModifierPresentedInspection
 * @noinspection PhpUnusedAliasInspection
 * @noinspection UnknownInspectionInspection
 * @noinspection PhpUnused
 * @noinspection ReturnTypeCanBeDeclaredInspection
 */
namespace eftec\examples\clitest\repo2;

use Exception;

/**
 * Class TableparentxcategoryRepo Copyright (c) Jorge Castro C. (https://github.com/EFTEC/PdoOne)<br>
 * <ul>
 * <li>idtablaparentPKFK: int (alias of column idtablaparentPKFK) </li>
 * <li>idcategoryPKFK: int (alias of column idcategoryPKFK) </li>
 * <li>_idcategoryPKFK: MANYTOONE (alias of column _idcategoryPKFK) (TablecategoryRepoModel)</li>
 * <li>_idtablaparentPKFK: ONETOONE (alias of column _idtablaparentPKFK) (TableParentRepoModel)</li>
 * </ul>
 * Generated by PdoOne Version 2.32.1 Date generated Sun, 22 May 2022 19:29:29 -0300.<br>
 * <b>YOU CAN EDIT THIS CODE</b>. It is not replaced by the generation of the code, unless it is indicated<br>
 * <pre>
 * $code=$pdoOne->generateCodeClassRepo(''TableParentxCategory'',''eftec\examples\clitest\repo2'','array('TableCategory'=>'TablecategoryRepo','TableChild'=>'TablechildRepo','TableGrandChild'=>'TablegrandchildRepo','TableGrandChildTag'=>'TablegrandchildtagRepo','TableParent'=>'TableParentRepo','TableParentExt'=>'TableparentextRepo','TableParentxCategory'=>'TableparentxcategoryRepo',)','''','array(0=>array(0=>'idtablaparentPKFK',1=>'int',2=>NULL,),1=>array(0=>'idcategoryPKFK',1=>'int',2=>NULL,),2=>array(0=>'_idcategoryPKFK',1=>'MANYTOONE',2=>'TablecategoryRepoModel',),3=>array(0=>'_idtablaparentPKFK',1=>'ONETOONE',2=>'TableParentRepoModel',),)','array('TableCategory'=>array('IdTableCategoryPK'=>'IdTableCategoryPK','Name'=>'Name',),'TableChild'=>array('idgrandchildFK'=>'idgrandchildFK','idtablachildPK'=>'idtablachildPK','NameChild'=>'NameChild',),'TableGrandChild'=>array('idgrandchildPK'=>'idgrandchildPK','NameGrandChild'=>'NameGrandChild',),'TableGrandChildTag'=>array('IdgrandchildFK'=>'IdgrandchildFK','IdTablaGrandChildTagPK'=>'IdTablaGrandChildTagPK','Name'=>'Name',),'TableParent'=>array('fieldDateTime'=>'fieldDateTime','fielDecimal'=>'fielDecimal','fieldInt'=>'fieldInt','fieldKey'=>'FieldKey','fieldUnique'=>'fieldUnique','fieldVarchar'=>'FieldText','idchild2FK'=>'IdChild2Foreign','idchildFK'=>'idchildFK','idtablaparentPK'=>'idtablaparentPK',),'TableParentExt'=>array('fieldExt'=>'fieldExt','idtablaparentExtPK'=>'idtablaparentExtPK',),'TableParentxCategory'=>array('idcategoryPKFK'=>'idcategoryPKFK','idtablaparentPKFK'=>'idtablaparentPKFK',),)');
 * </pre>
 * @see TablecategoryRepoModel
 * @see TableParentRepoModel
 */
class TableparentxcategoryRepo extends AbstractTableparentxcategoryRepo
{
    const ME=__CLASS__;
    


}
