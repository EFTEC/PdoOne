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
// [EDIT:use] you can edit this part
// Here you can add your custom use
// [/EDIT] end of edit

/**
 * Class TableGrandChildTagModel. Copyright (c) Jorge Castro C. (https://github.com/EFTEC/PdoOne)<br>
 * Generated by PdoOne Version 3.12.1 Date generated Sat, 03 Sep 2022 18:16:34 -0400.<br>
 * <b>DO NOT EDIT THE CODE OUTSIDE EDIT BLOCKS</b>. This code is generated<br>
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




    /**
     * AbstractTableGrandChildTagModel constructor.
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
        $obj=new TableGrandChildTagModel();
        		$obj->IdTablaGrandChildTagPK=isset($array['IdTablaGrandChildTagPK']) ?  $array['IdTablaGrandChildTagPK'] : null;
		$obj->Name=isset($array['Name']) ?  $array['Name'] : null;
		$obj->IdgrandchildFK=isset($array['IdgrandchildFK']) ?  $array['IdgrandchildFK'] : null;
        

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
    // [EDIT:content] you can edit this part
    // Here you can add your custom content.
    // [/EDIT] end of edit
} // end class
