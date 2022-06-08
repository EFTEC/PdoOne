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
 * Class TablecategoryRepo Copyright (c) Jorge Castro C. (https://github.com/EFTEC/PdoOne)<br>
 * <ul>
 * <li>IdTableCategoryPK: int (alias of column IdTableCategoryPK) </li>
 * <li>Name: string (alias of column Name) </li>
 * <li>_TableParentxCategory: ONETOMANY (alias of column _TableParentxCategory) (TableparentxcategoryRepoModel)</li>
 * </ul>
 * Generated by PdoOne Version 2.32.1 Date generated Sun, 22 May 2022 19:29:29 -0300.<br>
 * <b>YOU CAN EDIT THIS CODE</b>. It is not replaced by the generation of the code, unless it is indicated<br>
 * <pre>
 * $code=$pdoOne->generateCodeClassRepo(''TableCategory'',''eftec\examples\clitest\repo2'','array('TableCategory'=>'TablecategoryRepo','TableChild'=>'TablechildRepo','TableGrandChild'=>'TablegrandchildRepo','TableGrandChildTag'=>'TablegrandchildtagRepo','TableParent'=>'TableParentRepo','TableParentExt'=>'TableparentextRepo','TableParentxCategory'=>'TableparentxcategoryRepo',)','''','array(0=>array(0=>'IdTableCategoryPK',1=>'int',2=>NULL,),1=>array(0=>'Name',1=>'string',2=>NULL,),2=>array(0=>'_TableParentxCategory',1=>'ONETOMANY',2=>'TableparentxcategoryRepoModel',),)','array('TableCategory'=>array('IdTableCategoryPK'=>'IdTableCategoryPK','Name'=>'Name',),'TableChild'=>array('idgrandchildFK'=>'idgrandchildFK','idtablachildPK'=>'idtablachildPK','NameChild'=>'NameChild',),'TableGrandChild'=>array('idgrandchildPK'=>'idgrandchildPK','NameGrandChild'=>'NameGrandChild',),'TableGrandChildTag'=>array('IdgrandchildFK'=>'IdgrandchildFK','IdTablaGrandChildTagPK'=>'IdTablaGrandChildTagPK','Name'=>'Name',),'TableParent'=>array('fieldDateTime'=>'fieldDateTime','fielDecimal'=>'fielDecimal','fieldInt'=>'fieldInt','fieldKey'=>'FieldKey','fieldUnique'=>'fieldUnique','fieldVarchar'=>'FieldText','idchild2FK'=>'IdChild2Foreign','idchildFK'=>'idchildFK','idtablaparentPK'=>'idtablaparentPK',),'TableParentExt'=>array('fieldExt'=>'fieldExt','idtablaparentExtPK'=>'idtablaparentExtPK',),'TableParentxCategory'=>array('idcategoryPKFK'=>'idcategoryPKFK','idtablaparentPKFK'=>'idtablaparentPKFK',),)');
 * </pre>
 * @see TableparentxcategoryRepoModel
 */
class TablecategoryRepo extends AbstractTablecategoryRepo
{
    const ME=__CLASS__;
    


}
