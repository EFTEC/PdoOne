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
namespace sqlsrv\repomodel;
use eftec\PdoOne;
use Exception;

/**
 * Class TableParentxCategoryModel. Copyright (c) Jorge Castro C. (https://github.com/EFTEC/PdoOne)<br>
 * Generated by PdoOne Version 2.26 Date generated Sat, 19 Feb 2022 23:27:10 -0300.<br>
 * <b>DO NOT EDIT THIS CODE. THIS CODE WILL SELF GENERATE.</b><br>
 * <pre>
 * $code=$pdoOne->generateAbstractModelClass({args});
 * </pre>
 */
abstract class AbstractTableParentxCategoryModel
{
	/** @var int $idtablaparentPKFK  */
	public $idtablaparentPKFK;
	/** @var int $idcategoryPKFK  */
	public $idcategoryPKFK;

	/** @var TableCategoryModel $_idcategoryPKFK manytoone */
	public $_idcategoryPKFK;
	/** @var TableParentModel $_idtablaparentPKFK onetoone */
	public $_idtablaparentPKFK;


    /**
     * AbstractTableParentxCategoryModel constructor.
     *
     * @param array|null $array
     */
    public function __construct($array=null)
    {
        if($array===null) {
            return;
        }
        foreach($array as $k=>$v) {
            $this->{$k}=$v;
        }
    }

    //<editor-fold desc="array conversion">
    public static function fromArray($array) {
        if($array===null) {
            return null;
        }
        $obj=new TableParentxCategoryModel();
        		$obj->idtablaparentPKFK=isset($array['idtablaparentPKFK']) ?  $array['idtablaparentPKFK'] : null;
		$obj->idcategoryPKFK=isset($array['idcategoryPKFK']) ?  $array['idcategoryPKFK'] : null;
        		$obj->_idcategoryPKFK=isset($array['_idcategoryPKFK']) ? 
			$obj->_idcategoryPKFK=TableCategoryModel::fromArray($array['_idcategoryPKFK']) 
			: null; // manytoone
		($obj->_idcategoryPKFK !== null) 
			and $obj->_idcategoryPKFK->IdTableCategoryPK=&$obj->idcategoryPKFK; // linked manytoone
		$obj->_idtablaparentPKFK=isset($array['_idtablaparentPKFK']) ?  
			$obj->_idtablaparentPKFK=TableParentModel::fromArray($array['_idtablaparentPKFK']) 
			: null; // onetoone
		($obj->_idtablaparentPKFK !== null) 
			and $obj->_idtablaparentPKFK->idtablaparentPK=&$obj->idtablaparentPKFK; // linked onetoone

        return $obj;
    }

    /**
     * It converts the current object in an array
     *
     * @return mixed
     */
    public function toArray() {
        return static::objectToArray($this);
    }

    /**
     * It converts an array of arrays into an array of objects.
     *
     * @param array|null $array
     *
     * @return array|null
     */
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
