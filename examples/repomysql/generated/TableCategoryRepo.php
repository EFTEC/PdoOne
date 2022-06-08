<?php
/** @noinspection AccessModifierPresentedInspection
 * @noinspection PhpUnusedAliasInspection
 * @noinspection UnknownInspectionInspection
 * @noinspection PhpUnused
 * @noinspection ReturnTypeCanBeDeclaredInspection
 */
namespace repomysql;
use mysql\repomodel\TableCategoryModel;
use Exception;

/**
 * Class TableCategoryRepo Copyright (c) Jorge Castro C. (https://github.com/EFTEC/PdoOne)<br>
 * <ul>
 * <li>IdTableCategoryPK int </li>
 * <li>Name string </li>
 * <li>_TableParentxCategory ONETOMANY (TableParentxCategoryModel)</li>
 * </ul>
 * Generated by PdoOne Version 2.32.1 Date generated Sat, 21 May 2022 09:24:25 -0300.<br>
 * <b>YOU CAN EDIT THIS CODE</b>. It is not replaced by the generation of the code, unless it is indicated<br>
 * <pre>
 * $code=$pdoOne->generateCodeClassRepo(''TableCategory'',''repomysql'','array('TableParent'=>'TableParentRepo','TableChild'=>'TableChildRepo','TableGrandChild'=>'TableGrandChildRepo','TableGrandChildTag'=>'TableGrandChildTagRepo','TableParentxCategory'=>'TableParentxCategoryRepo','TableCategory'=>'TableCategoryRepo','TableParentExt'=>'TableParentExtRepo',)',''mysql\repomodel\TableCategoryModel'','array(0=>array(0=>'IdTableCategoryPK',1=>'int',2=>NULL,),1=>array(0=>'Name',1=>'string',2=>NULL,),2=>array(0=>'_TableParentxCategory',1=>'ONETOMANY',2=>'TableParentxCategoryModel',),)');
 * </pre>
 * @see TableParentxCategoryModel
 */
class TableCategoryRepo extends AbstractTableCategoryRepo
{
    const ME=__CLASS__;
    const MODEL= TableCategoryModel::class;


}
