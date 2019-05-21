<?php
namespace eftec;

/**
 * Class MessageList
 * @package eftec
 * @author Jorge Castro Castillo
 * @version 1.9 20181015
 * @copyright (c) Jorge Castro C. LGLPV2 License  https://github.com/EFTEC/ValidationOne
 * @see https://github.com/EFTEC/ValidationOne
 */
class MessageList
{
    /** @var  MessageItem[] Array of containers */
    var $items;
    /** @var int Number of errors stored globally */
    var $errorcount=0;
    /** @var int Number of warnings stored globally */
    var $warningcount=0;
    /** @var int Number of information stored globally */
    var $infocount=0;
    /** @var int Number of success stored globally */
    var $successcount=0;
    private $firstError=null;
    private $firstWarning=null;
    private $firstInfo=null;
    private $firstSuccess=null;

    /**
     * MessageList constructor.
     */
    public function __construct()
    {
        $this->items=array();
    }

    public function resetAll() {
        $this->errorcount=0;
        $this->warningcount=0;
        $this->infocount=0;
        $this->successcount=0;
        $this->items=array();
        $this->firstError=null;
        $this->firstWarning=null;
        $this->firstInfo=null;
        $this->firstSuccess=null;
    }
    /**
     * You could add a message (including errors,warning..) and store in a $id
     * @param string $id Identified of the container message (where the message will be stored)
     * @param string $message message to show. Example: 'the value is incorrect'
     * @param string $level = error|warning|info|success
     */
    public function addItem($id,$message,$level='error') {
        $id=($id==='')?"0":$id;
        if (!isset($this->items[$id])) {
            $this->items[$id]=new MessageItem();
        }
        switch ($level) {
            case 'error':
                $this->errorcount++;
                if ($this->firstError===null) $this->firstError=$message;
                $this->items[$id]->addError($message);
                break;
            case 'warning':
                $this->warningcount++;
                if ($this->firstWarning===null) $this->firstWarning=$message;
                $this->items[$id]->addWarning($message);
                break;
            case 'info':
                $this->infocount++;
                if ($this->firstInfo===null) $this->firstInfo=$message;
                $this->items[$id]->addInfo($message);
                break;
            case 'success':
                $this->successcount++;
                if ($this->firstSuccess===null) $this->firstSuccess=$message;
                $this->items[$id]->addSuccess($message);
                break;
        }
    }

    /**
     * @return array
     */
    public function allIds() {
        return array_keys($this->items);
    }

    /**
     * It returns an error item. If the item doesn't exist then it returns an empty object (not null)
     * @param string $id Id of the container
     * @return MessageItem
     */
    public function get($id) {
        $id=($id==='')?"0":$id;
        if (!isset($this->items[$id])) {
            return new MessageItem(); // we returns an empty error.
        }
        return $this->items[$id];
    }

    /**
     * find a value by the index and returns the text (bootstrap 4)
     * @param string $id Id of the container
     * @return string
     */
    public function cssClass($id) {
        $id=($id==='')?"0":$id;
        if (!isset($this->items[$id])) return "";
        if (@$this->items[$id]->countError()) {
            return "danger";
        }
        if ($this->items[$id]->countWarning()) {
            return "warning";
        }
        if ($this->items[$id]->countInfo()) {
            return "info";
        }
        if ($this->items[$id]->countSuccess()) {
            return "success";
        }
        return "";
    }

    /**
     * It returns the first message of error (if any)
     * @return string empty if there is none
     */
    public function firstErrorText() {
        return ($this->errorcount==0)?"":$this->firstError;
    }
    /**
     * It returns the first message of error (if any), if not,
     * it returns the first message of warning (if any)
     * @return string empty if there is none
     */
    public function firstErrorOrWarning() {
        if ($this->errorcount) return $this->firstError;
        return ($this->warningcount==0)?"":$this->firstWarning;
    }
    /**
     * It returns the first message of warning (if any)
     * @return string empty if there is none
     */
    public function firstWarningText() {
        return ($this->warningcount==0)?"":$this->firstWarning;
    }
    /**
     * It returns the first message of information (if any)
     * @return string empty if there is none
     */
    public function firstInfoText() {
        return ($this->infocount==0)?"":$this->firstInfo;
    }
    /**
     * It returns the first message of success (if any)
     * @return string empty if there is none
     */
    public function firstSuccessText() {
        return ($this->successcount==0)?"":$this->firstSuccess;
    }
    /**
     * It returns an array with all messages of error of all containers.
     * @return string[] empty if there is none
     */
    public function allErrorArray() {
        $r=array();
        foreach($this->items as $v) {
            $r=array_merge($r,$v->allError());
        }
        return $r;
    }
    /**
     * It returns an array with all messages of info of all containers.
     * @return string[] empty if there is none
     */
    public function allInfoArray() {
        $r=array();
        foreach($this->items as $v) {
            $r=array_merge($r,$v->allInfo());
        }
        return $r;
    }
    /**
     * It returns an array with all messages of warning of all containers.
     * @return string[] empty if there is none
     */
    public function allWarningArray() {
        $r=array();
        foreach($this->items as $v) {
            $r=array_merge($r,$v->allWarning());
        }
        return $r;
    }
    /**
     * It returns an array with all messages of success of all containers.
     * @return string[] empty if there is none
     */
    public function AllSuccessArray() {
        $r=array();
        foreach($this->items as $v) {
            $r=array_merge($r,$v->allSuccess());
        }
        return $r;
    }
    /**
     * It returns an array with all messages of any type of all containers
     * @return string[] empty if there is none
     */
    public function allArray() {
        $r=array();
        foreach($this->items as $v) {
            $r=array_merge($r,$v->allError());
            $r=array_merge($r,$v->allWarning());
            $r=array_merge($r,$v->allInfo());
            $r=array_merge($r,$v->allSuccess());
        }
        return $r;
    }
    /**
     * It returns an array with all messages of errors and warnings of all containers.
     * @return string[] empty if there is none
     */
    public function allErrorOrWarningArray() {
        $r=array();
        foreach($this->items as $v) {
            $r=array_merge($r,$v->allError());
            $r=array_merge($r,$v->allWarning());
        }
        return $r;
    }
}