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
 * Class TableGrandChildModel
 * <pre>
 * $code=$pdoOne->generateAbstractModelClass({args});
 * </pre>
 */
abstract class AbstractTableGrandChildModel
{
	/** @var int $idgrandchildPK  */
	public $idgrandchildPK;
	/** @var string $NameGrandChild  */
	public $NameGrandChild;

	/** @var TableChildModel[] $_TableChild onetomany */
    public $_TableChild;
	/** @var TableGrandChildTagModel[] $_TableGrandChildTag onetomany */
    public $_TableGrandChildTag;

    //<editor-fold desc="array conversion">
    public static function fromArray($array) {
        if($array===null) {
            return null;
        }
        $obj=new TableGrandChildModel();
		$obj->idgrandchildPK=isset($array['idgrandchildPK']) ?  $array['idgrandchildPK'] : null;
		$obj->NameGrandChild=isset($array['NameGrandChild']) ?  $array['NameGrandChild'] : null;
		$obj->_TableChild=isset($array['_TableChild']) ?  
            $obj->_TableChild=TableChildModel::fromArrayMultiple($array['_TableChild']) 
            : null; // onetomany
		$obj->_TableGrandChildTag=isset($array['_TableGrandChildTag']) ?  
            $obj->_TableGrandChildTag=TableGrandChildTagModel::fromArrayMultiple($array['_TableGrandChildTag']) 
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