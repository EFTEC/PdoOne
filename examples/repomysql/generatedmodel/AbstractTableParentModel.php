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
 * Generated by PdoOne Version 1.52 Date generated Mon, 27 Jul 2020 10:35:00 -0400. 
 * DO NOT EDIT THIS CODE. THIS CODE WILL SELF GENERATE.
 * @copyright (c) Jorge Castro C. MIT License  https://github.com/EFTEC/PdoOne
 * Class TableParentModel
 * <pre>
 * $code=$pdoOne->generateAbstractModelClass({args});
 * </pre>
 */
abstract class AbstractTableParentModel
{
	/** @var int $idtablaparentPK  */
	public $idtablaparentPK;
	/** @var string $fieldVarchar  */
	public $fieldVarchar;
	/** @var int $idchildFK  */
	public $idchildFK;
	/** @var int $idchild2FK  */
	public $idchild2FK;
	/** @var int $fieldInt  */
	public $fieldInt;
	/** @var decimal $fielDecimal  */
	public $fielDecimal;
	/** @var datetime $fieldDateTime  */
	public $fieldDateTime;
	/** @var string $fieldUnique  */
	public $fieldUnique;
	/** @var string $fieldKey  */
	public $fieldKey;

	/** @var TableChildModel $_idchildFK manytoone */
    public $_idchildFK;
	/** @var TableParentExtModel $_TableParentExt onetoone */
    public $_TableParentExt;
	/** @var TableParentxCategoryModel[] $_TableParentxCategory manytomany */
    public $_TableParentxCategory;

    //<editor-fold desc="array conversion">
    public static function fromArray($array) {
        if($array===null) {
            return null;
        }
        $obj=new TableParentModel();
		$obj->idtablaparentPK=isset($array['idtablaparentPK']) ?  $array['idtablaparentPK'] : null;
		$obj->fieldVarchar=isset($array['fieldVarchar']) ?  $array['fieldVarchar'] : null;
		$obj->idchildFK=isset($array['idchildFK']) ?  $array['idchildFK'] : null;
		$obj->idchild2FK=isset($array['idchild2FK']) ?  $array['idchild2FK'] : null;
		$obj->fieldInt=isset($array['fieldInt']) ?  $array['fieldInt'] : null;
		$obj->fielDecimal=isset($array['fielDecimal']) ?  $array['fielDecimal'] : null;
		$obj->fieldDateTime=isset($array['fieldDateTime']) ?  $array['fieldDateTime'] : null;
		$obj->fieldUnique=isset($array['fieldUnique']) ?  $array['fieldUnique'] : null;
		$obj->fieldKey=isset($array['fieldKey']) ?  $array['fieldKey'] : null;
		$obj->_idchildFK=isset($array['_idchildFK']) ? 
            $obj->_idchildFK=TableChildModel::fromArray($array['_idchildFK']) 
            : null; // manytoone
		$obj->_TableParentExt=isset($array['_TableParentExt']) ?  
            $obj->_TableParentExt=TableParentExtModel::fromArray($array['_TableParentExt']) 
            : null; // onetoone
		$obj->_TableParentxCategory=isset($array['_TableParentxCategory']) ?  
            $obj->_TableParentxCategory=TableParentxCategoryModel::fromArrayMultiple($array['_TableParentxCategory']) 
            : null; // manytomany

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