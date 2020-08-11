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
 * Generated by PdoOne Version 1.54 Date generated Mon, 10 Aug 2020 18:37:30 -0400. 
 * DO NOT EDIT THIS CODE. THIS CODE WILL SELF GENERATE.
 * @copyright (c) Jorge Castro C. MIT License  https://github.com/EFTEC/PdoOne
 * Class TableGrandChildTagModel
 * <pre>
 * $code=$pdoOne->generateAbstractModelClass({args});
 * </pre>
 */
abstract class AbstractTableGrandChildTagModel
{
	/** @var int $IdTablaGrandChildTagPK  */
	public $IdTablaGrandChildTagPK;
	/** @var string $Name  */
	public $Name;
	/** @var int $IdgrandchildFK  */
	public $IdgrandchildFK;

	/** @var TableGrandChildModel $_IdgrandchildFK manytoone */
    public $_IdgrandchildFK;

    //<editor-fold desc="array conversion">
    public static function fromArray($array) {
        if($array===null) {
            return null;
        }
        $obj=new TableGrandChildTagModel();
		$obj->IdTablaGrandChildTagPK=isset($array['IdTablaGrandChildTagPK']) ?  $array['IdTablaGrandChildTagPK'] : null;
		$obj->Name=isset($array['Name']) ?  $array['Name'] : null;
		$obj->IdgrandchildFK=isset($array['IdgrandchildFK']) ?  $array['IdgrandchildFK'] : null;
		$obj->_IdgrandchildFK=isset($array['_IdgrandchildFK']) ? 
            $obj->_IdgrandchildFK=TableGrandChildModel::fromArray($array['_IdgrandchildFK']) 
            : null; // manytoone
		($obj->_IdgrandchildFK !== null) 
            and $obj->_IdgrandchildFK->idgrandchildPK=&$obj->IdgrandchildFK; // linked manytoone

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