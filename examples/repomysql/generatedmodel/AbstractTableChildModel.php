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
 * Class TableChildModel
 * <pre>
 * $code=$pdoOne->generateAbstractModelClass({args});
 * </pre>
 */
abstract class AbstractTableChildModel
{
	/** @var int $idtablachildPK  */
	public $idtablachildPK;
	/** @var string $NameChild  */
	public $NameChild;
	/** @var int $idgrandchildFK  */
	public $idgrandchildFK;

	/** @var TableGrandChildModel $_idgrandchildFK manytoone */
    public $_idgrandchildFK;
	/** @var TableParentModel[] $_TableParent onetomany */
    public $_TableParent;

    //<editor-fold desc="array conversion">
    public static function fromArray($array) {
        if($array===null) {
            return null;
        }
        $obj=new TableChildModel();
		$obj->idtablachildPK=isset($array['idtablachildPK']) ?  $array['idtablachildPK'] : null;
		$obj->NameChild=isset($array['NameChild']) ?  $array['NameChild'] : null;
		$obj->idgrandchildFK=isset($array['idgrandchildFK']) ?  $array['idgrandchildFK'] : null;
		$obj->_idgrandchildFK=isset($array['_idgrandchildFK']) ? 
            $obj->_idgrandchildFK=TableGrandChildModel::fromArray($array['_idgrandchildFK']) 
            : null; // manytoone
		$obj->_TableParent=isset($array['_TableParent']) ?  
            $obj->_TableParent=TableParentModel::fromArrayMultiple($array['_TableParent']) 
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