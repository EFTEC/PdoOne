<?php
/** @noinspection PhpIncompatibleReturnTypeInspection
 * @noinspection ReturnTypeCanBeDeclaredInspection
 * @noinspection DuplicatedCode
 * @noinspection PhpUnused
 * @noinspection PhpUndefinedMethodInspection
 * @noinspection PhpUnusedLocalVariableInspection
 * @noinspection PhpUnusedAliasInspection
 * @noinspection NullPointerExceptionInspection
 * @noinspection SenselessProxyMethodInspection
 * @noinspection PhpParameterByRefIsNotUsedAsReferenceInspection
 */
namespace mysql\repomodel;
use eftec\PdoOne;
use Exception;

/**
 * Generated by PdoOne Version 2.1 Date generated Fri, 14 Aug 2020 16:47:00 -0400. 
 * DO NOT EDIT THIS CODE. THIS CODE WILL SELF GENERATE.
 * @copyright (c) Jorge Castro C. MIT License  https://github.com/EFTEC/PdoOne
 * Class TableCategoryModel
 * <pre>
 * $code=$pdoOne->generateAbstractModelClass({args});
 * </pre>
 */
abstract class AbstractTableCategoryModel
{
	/** @var int $IdTableCategoryPK  */
	public $IdTableCategoryPK;
	/** @var string $Name  */
	public $Name;

	/** @var TableParentxCategoryModel[] $_TableParentxCategory onetomany */
    public $_TableParentxCategory;

    //<editor-fold desc="array conversion">
    public static function fromArray($array) {
        if($array===null) {
            return null;
        }
        $obj=new TableCategoryModel();
		$obj->IdTableCategoryPK=isset($array['IdTableCategoryPK']) ?  $array['IdTableCategoryPK'] : null;
		$obj->Name=isset($array['Name']) ?  $array['Name'] : null;
		$obj->_TableParentxCategory=isset($array['_TableParentxCategory']) ?  
            $obj->_TableParentxCategory=TableParentxCategoryModel::fromArrayMultiple($array['_TableParentxCategory']) 
            : null; // onetomany

        return $obj;
    }
    public function toArray() {
        return (array) $this;
    }
    public static function fromArrayMultiple($array) {
        if($array===null) {
            return null;
        }
        $objs=[];
        foreach($array as $v) {
            $objs[]=self::fromArray($v);
        }
        return $objs;
    }
    //</editor-fold>
    
} // end class