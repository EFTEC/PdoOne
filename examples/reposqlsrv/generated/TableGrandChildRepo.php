<?php
/** @noinspection AccessModifierPresentedInspection
 * @noinspection PhpUnusedAliasInspection
 * @noinspection UnknownInspectionInspection
 * @noinspection PhpUnused
 * @noinspection ReturnTypeCanBeDeclaredInspection
 */
namespace reposqlsrv;
use sqlsrv\repomodel\TableGrandChildModel;
use Exception;

/**
 * Class TableGrandChildRepo Copyright (c) Jorge Castro C. (https://github.com/EFTEC/PdoOne)<br>
 * <ul>
 * <li>idgrandchildPK int </li>
 * <li>NameGrandChild string </li>
 * <li>_TableGrandChildTag ONETOMANY (TableGrandChildTagModel)</li>
 * <li>_TableChild ONETOMANY (TableChildModel)</li>
 * </ul>
 * Generated by PdoOne Version 2.26 Date generated Sat, 19 Feb 2022 23:27:09 -0300.<br>
 * <b>YOU CAN EDIT THIS CODE</b>. It is not replaced by the generation of the code, unless it is indicated<br>
 * <pre>
 * $code=$pdoOne->generateCodeClassRepo(''TableGrandChild'',''reposqlsrv'','array('TableParent'=>'TableParentRepo','TableChild'=>'TableChildRepo','TableGrandChild'=>'TableGrandChildRepo','TableGrandChildTag'=>'TableGrandChildTagRepo','TableParentxCategory'=>'TableParentxCategoryRepo','TableCategory'=>'TableCategoryRepo','TableParentExt'=>'TableParentExtRepo',)',''sqlsrv\repomodel\TableGrandChildModel'','array(0=>array(0=>'idgrandchildPK',1=>'int',2=>NULL,),1=>array(0=>'NameGrandChild',1=>'string',2=>NULL,),2=>array(0=>'_TableGrandChildTag',1=>'ONETOMANY',2=>'TableGrandChildTagModel',),3=>array(0=>'_TableChild',1=>'ONETOMANY',2=>'TableChildModel',),)');
 * </pre>
 * @see TableGrandChildTagModel
 * @see TableChildModel
 */
class TableGrandChildRepo extends AbstractTableGrandChildRepo
{
    const ME=__CLASS__;
    const MODEL= TableGrandChildModel::class;


}
