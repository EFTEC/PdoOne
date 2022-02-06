<?php
/** @noinspection PhpMissingParamTypeInspection */
/** @noinspection UnknownInspectionInspection */
/** @noinspection SlowArrayOperationsInLoopInspection */

/** @noinspection PhpUnused */

namespace eftec;

use RuntimeException;

/**
 * Class MessageList
 *
 * @package       eftec
 * @author        Jorge Castro Castillo
 * @version       2.3 2022-02-05
 * @copyright (c) Jorge Castro C. mit License  https://github.com/EFTEC/MessageContainer
 * @see           https://github.com/EFTEC/MessageContainer
 */
class MessageContainer
{
    /** @var  MessageLocker[] Array of containers */
    public $items;
    /** @var int Number of errors stored globally */
    public $errorCount = 0;
    /** @var int Number of warnings stored globally */
    public $warningCount = 0;
    /** @var int Number of errors or warning stored globally */
    public $errorOrWarningCount = 0;
    /** @var int Number of information stored globally */
    public $infoCount = 0;
    /** @var int Number of success stored globally */
    public $successCount = 0;
    /** @var string[] Used to convert a type of message to a css class */
    public $cssClasses = ['error' => 'danger', 'warning' => 'warning', 'info' => 'info', 'success' => 'success'];

    private $throwOnError=false;
    private $logOnError=false;
    private $throwOnWarning=false;
    private $logOnWarning=false;
    /** @var null|MessageContainer singleton */
    protected static $instance;

    /**
     * MessageList constructor.
     */
    public function __construct($setSingleton=true)
    {
        $this->items = array();
        if($setSingleton) {
            self::$instance = $this;
        }
    }

    /**
     * Obtain the singleton and if it doesn't exist, then it is created.
     * @return MessageContainer
     */
    public static function instance(): MessageContainer
    {
        if(self::$instance===null) {
            self::$instance=new MessageContainer(false);
        }
        return self::$instance;
    }

    /**
     * It resets all the container and flush all the results.
     */
    public function resetAll(): void
    {
        $this->errorCount = 0;
        $this->warningCount = 0;
        $this->errorOrWarningCount = 0;
        $this->infoCount = 0;
        $this->successCount = 0;
        $this->items = array();
        $this->throwOnError=false;
        $this->logOnError=false;
        $this->throwOnWarning=false;
    }

    /**
     * If we store an error then we also throw a PHP exception.
     *
     * @param bool    $throwOnError  if true (default), then it throws an excepcion every time
     *                               we store an error.
     * @param boolean $includeWarning If true then it also includes warnings.
     * @return MessageContainer
     */
    public function throwOnError($throwOnError=true,$includeWarning=false): MessageContainer
    {
        $this->throwOnError=$throwOnError;
        $this->throwOnWarning=$includeWarning;
        return $this;
    }
    /**
     * If we store an error then we also save the information using error_log()
     *
     * @param bool    $logOnError  if true (default), then it throws an excepcion every time
     *                               we store an error.
     * @param boolean $includeWarning If true then it also includes warnings.
     * @return MessageContainer
     */
    public function LogOnError($logOnError=true,$includeWarning=false): MessageContainer
    {
        $this->logOnError=$logOnError;
        $this->logOnWarning=$includeWarning;
        return $this;
    }

    /**
     * You could add a message (including errors,warning, etc.) and store it in a $idLocker
     *
     * @param string $idLocker Identified of the locker (where the message will be stored)
     * @param string $message  message to show. Example: 'the value is incorrect'.<br>
     *                         You can also use variables (if you are set a context). Ex: {{var1}} <br>
     *                         You can also show the idlocker. Ex: {{_idlocker}}<br>
     * @param string $level    =['error','warning','info','success'][$i]
     * @param array  $context  [optional] it is an associative array with the values of the item<br>
     *                         For optimization, the context is not update if exists another context.
     */
    public function addItem($idLocker, $message, $level = 'error', $context = null): void
    {
        $idLocker = ($idLocker === '') ? '0' : $idLocker;
        if (!isset($this->items[$idLocker])) {
            $this->items[$idLocker] = new MessageLocker($idLocker, $context);
        } else {
            $this->items[$idLocker]->setContext($context);
        }
        // if the message contains a curly braces, then it is convert using the context.
        switch ($level) {
            case 'error':
                $this->errorCount++;
                $this->errorOrWarningCount++;
                $lastmsg=$this->items[$idLocker]->addError($message);
                if($this->logOnError) {
                    /** @noinspection ForgottenDebugOutputInspection */
                    error_log($lastmsg);
                }
                if($this->throwOnError) {
                    throw new RuntimeException($lastmsg);
                }
                break;
            case 'warning':
                $this->warningCount++;
                $this->errorOrWarningCount++;
                $lastmsg=$this->items[$idLocker]->addWarning($message);
                if($this->logOnWarning) {
                    /** @noinspection ForgottenDebugOutputInspection */
                    error_log($lastmsg);
                }
                if($this->throwOnWarning) {
                    throw new RuntimeException($lastmsg);
                }
                break;
            case 'info':
                $this->infoCount++;
                $this->items[$idLocker]->addInfo($message);
                break;
            case 'success':
                $this->successCount++;
                $this->items[$idLocker]->addSuccess($message);
                break;
        }
    }

    /**
     * It obtains all the ids for all the lockers.
     *
     * @return array
     */
    public function allIds(): array
    {
        return array_keys($this->items);
    }

    /**
     * Alias of $this->getMessage()
     * @param string $idLocker ID of the locker
     * @return MessageLocker
     */
    public function get($idLocker): MessageLocker
    {
        return $this->getLocker($idLocker);
    }

    /**
     * It returns a MessageLocker containing a locker.<br>
     * <b>If the locker doesn't exist then it returns an empty object (not null)</b>
     *
     * @param string $idLocker ID of the locker
     *
     * @return MessageLocker
     */
    public function getLocker($idLocker = ''): MessageLocker
    {
        $idLocker = ($idLocker === '') ? '0' : $idLocker;
        return $this->items[$idLocker] ?? new MessageLocker($idLocker);
    }

    /**
     * It returns a css class associated with the type of errors inside a locker<br>
     * If the locker contains more than one message, then it uses the most severe one (error,warning,etc.)<br>
     * The method uses the field <b>$this->cssClasses</b>, so you can change the CSS classes.
     * <pre>
     * $this->clsssClasses=['error'=>'class-red','warning'=>'class-yellow','info'=>'class-green','success'=>'class-blue'];
     * $css=$this->cssClass('customerId');
     * </pre>
     *
     * @param string $idLocker ID of the locker
     *
     * @return string
     */
    public function cssClass($idLocker): string
    {
        $idLocker = ($idLocker === '') ? '0' : $idLocker;
        if (!isset($this->items[$idLocker])) {
            return '';
        }
        if (@$this->items[$idLocker]->countError()) {
            return $this->cssClasses['error'];
        }
        if ($this->items[$idLocker]->countWarning()) {
            return $this->cssClasses['warning'];
        }
        if ($this->items[$idLocker]->countInfo()) {
            return $this->cssClasses['info'];
        }
        if ($this->items[$idLocker]->countSuccess()) {
            return $this->cssClasses['success'];
        }
        return '';
    }

    /**
     * It returns the first message of error or empty if none<br>
     * If not, then it returns the first message of warning or empty if none
     *
     * @param string $default if not message is found, then it returns this value
     * @return string empty if there is none
     * @see \eftec\MessageContainer::firstErrorText
     */
    public function firstErrorOrWarning($default = ''): string
    {
        return $this->firstErrorText($default, true);
    }
    /**
     * It returns the first message of error or empty if none<br>
     * If not, then it returns the first message of warning or empty if none
     *
     * @param string $default if not message is found, then it returns this value
     * @return string empty if there is none
     * @see \eftec\MessageContainer::firstErrorText
     */
    public function lastErrorOrWarning($default = ''): string
    {
        $r=$this->allErrorArray(true,'last');

        return $r[0] ?? $default;
    }

    /**
     * It returns the first message of error (as text) or empty if none
     *
     * @param string $default if not message is found, then it returns this value.
     * @param bool   $includeWarning if true then it also includes warning but any error has priority.
     * @return string empty (or default if there is none
     */
    public function firstErrorText($default = '', $includeWarning = false): string
    {
        $r=$this->allErrorArray($includeWarning,'first');
        return $r[0] ?? $default;
    }
    /**
     * It returns the last message of error (as text) or empty if none
     *
     * @param string $default if not message is found, then it returns this value.
     * @param bool   $includeWarning if true then it also includes warning but any error has priority.
     * @return string empty (or default if there is none
     */
    public function lastErrorText($default = '', $includeWarning = false): string
    {
        $r=$this->allErrorArray($includeWarning,'last');
        return $r[0] ?? $default;
    }

    /**
     * It returns the first message of warning or empty if none
     *
     * @param string $default if not message is found, then it returns this value
     * @return string empty if there is none
     */
    public function firstWarningText($default = ''): string
    {
        $r=$this->allWarningArray('first');
        return $r[0] ?? $default;
    }
    /**
     * It returns the last message of warning or empty if none
     *
     * @param string $default if not message is found, then it returns this value
     * @return string empty if there is none
     */
    public function lastWarningText($default = ''): string
    {
        $r=$this->allWarningArray('last');
        return $r[0] ?? $default;
    }

    /**
     * It returns the first message of information or empty if none
     *
     * @param string $default if not message is found, then it returns this value
     * @return string empty if there is none
     */
    public function firstInfoText($default = ''): string
    {
        $r=$this->allInfoArray('first');
        return $r[0] ?? $default;
    }
    /**
     * It returns the last message of information or empty if none
     *
     * @param string $default if not message is found, then it returns this value
     * @return string empty if there is none
     */
    public function lastInfoText($default = ''): string
    {
        $r=$this->allInfoArray('last');
        return $r[0] ?? $default;
    }

    /**
     * It returns the first message of success or empty if none
     *
     * @param string $default if not message is found, then it returns this value
     * @return string empty if there is none
     */
    public function firstSuccessText($default = ''): string
    {
        $r=$this->allSuccessArray('first');
        return $r[0] ?? $default;
    }
    /**
     * It returns the last message of success or empty if none
     *
     * @param string $default if not message is found, then it returns this value
     * @return string empty if there is none
     */
    public function lastSuccessText($default = ''): string
    {
        $r=$this->allSuccessArray('last');
        return $r[0] ?? $default;
    }

    /**
     * It returns an array with all messages of any type of all lockers
     *
     * @param null|string $level =[null,'error','warning','errorwarning','info','success'][$i] the level to show.<br>
     *                           Null means it shows all errors
     * @return string[] empty array if there is none
     */
    public function allArray($level = null): array
    {
        switch ($level) {
            case 'error':
                return $this->allErrorArray();
            case 'warning':
                return $this->allWarningArray();
            case 'errorwarning':
                return $this->allErrorOrWarningArray();
            case 'info':
                return $this->allInfoArray();
            case 'success':
                return $this->allSuccessArray();
        }
        $r = array();
        foreach ($this->items as $v) {
            $r = array_merge($r, $v->allError());
            $r = array_merge($r, $v->allWarning());
            $r = array_merge($r, $v->allInfo());
            $r = array_merge($r, $v->allSuccess());
        }
        return $r;
    }

    /**
     * It returns an array with all messages of error of all lockers.
     *
     * @param bool   $includeWarning if true then it also includes warnings.
     * @param string $position       =['*','first','last'][$i] the value to read<br>
     *                               * = reads all the errors (or warnings)<br>
     *                               first = reads the first error (or warning)<br>
     *                               last = reads the last error (or warning)<br>
     *
     * @return array empty if there is none
     */
    public function allErrorArray($includeWarning = false, $position='*') : array
    {
        $r = array();
        if ($includeWarning) {
            foreach ($this->items as $v) {
                switch ($position) {
                    case '*':
                        $r = array_merge($r, $v->allErrorOrWarning());
                        break;
                    case 'first':
                        $tmp=$v->firstErrorOrWarning();
                        if($tmp!==null) {
                            return [$tmp];
                        }
                        break;
                    case 'last':
                        $tmp=$v->lastErrorOrWarning();
                        if($tmp!==null) {
                            $r[]=$tmp;
                        }
                        break;
                    default:
                        throw new RuntimeException("MessageContainer::allErrorArray unknow type $position");
                }
            }
            if ($position==='last') {
                return end($r)?[end($r)]:[];
            }
            return $r;
        }
        foreach ($this->items as $v) {
            switch ($position) {
                case '*':
                    $r = array_merge($r, $v->allError());
                    break;
                case 'first':
                    $tmp=$v->firstError();
                    if($tmp!==null) {
                        return [$tmp];
                    }
                    break;
                case 'last':
                    $tmp=$v->lastError();
                    if($tmp!==null) {
                        $r[]=$tmp;
                    }
                    break;
                default:
                    throw new RuntimeException("MessageContainer::allErrorArray unknow type $position");
            }

        }
        return $r;
    }

    /**
     * It returns an array with all messages of warning of all lockers.
     *
     * @return string[] empty array if there is none
     */
    public function allWarningArray($position='*'): array
    {
        $r = array();
        foreach ($this->items as $v) {
            switch ($position) {
                case '*':
                    $r = array_merge($r, $v->allWarning());
                    break;
                case 'first':
                    $tmp=$v->firstWarning();
                    if($tmp!==null) {
                        return [$tmp];
                    }
                    break;
                case 'last':
                    $tmp=$v->lastWarning();
                    if($tmp!==null) {
                        $r[]=$tmp;
                    }
                    break;
                default:
                    throw new RuntimeException("MessageContainer::allWarningArray unknow type $position");
            }
        }
        if ($position==='last') {
            return end($r)?[end($r)]:[];
        }
        return $r;
    }

    /**
     * It returns an array with all messages of errors and warnings of all lockers.
     *
     * @return string[] empty array if there is none
     * @see \eftec\MessageContainer::allErrorArray
     */
    public function allErrorOrWarningArray(): array
    {
        return $this->allErrorArray(true);
    }

    /**
     * It returns an array with all messages of info of all lockers.
     *
     * @return string[] empty array if there is none
     */
    public function allInfoArray($position='*'): array
    {
        $r = array();
        foreach ($this->items as $v) {
            switch ($position) {
                case '*':
                    $r = array_merge($r, $v->allInfo());
                    break;
                case 'first':
                    $tmp=$v->firstInfo();
                    if($tmp!==null) {
                        return [$tmp];
                    }
                    break;
                case 'last':
                    $tmp=$v->lastInfo();
                    if($tmp!==null) {
                        $r[]=$tmp;
                    }
                    break;
                default:
                    throw new RuntimeException("MessageContainer::allInfoArray unknow type $position");
            }
        }
        if ($position==='last') {
            return end($r)?[end($r)]:[];
        }
        return $r;
    }

    /**
     * It returns an array with all messages of success of all lockers.
     *
     * @return string[] empty array if there is none
     */
    public function allSuccessArray($position='*'): array
    {
        $r = array();
        foreach ($this->items as $v) {
            switch ($position) {
                case '*':
                    $r = array_merge($r, $v->allSuccess());
                    break;
                case 'first':
                    $tmp=$v->firstSuccess();
                    if($tmp!==null) {
                        return [$tmp];
                    }
                    break;
                case 'last':
                    $tmp=$v->lastSuccess();
                    if($tmp!==null) {
                        $r[]=$tmp;
                    }
                    break;
                default:
                    throw new RuntimeException("MessageContainer::allSuccessArray unknow type $position");
            }
        }
        if ($position==='last') {
            return end($r)?[end($r)]:[];
        }
        return $r;
    }

    /**
     * It returns an associative array of the form <br>
     * <pre>
     * [
     *  ['id'=>'', // ID of the locker
     *  'level'=>'' // level of message (error, warning, info or success)
     *  'msg'=>'' // the message to show
     *  ]
     * ]
     * </pre>
     *
     * @param string $level=['*','error','warning','errorwarning','info','success'][$i] '*' (default means all levels).
     * @return array
     */
    public function allAssocArray($level = '*'): array
    {
        $result = [];
        foreach ($this->items as $v) {
            if ($level === 'error' || $level === 'errorwarning' || $level === '*') {
                $tmp = $v->allAssocArray('error');
                $result = array_merge($result, $tmp);
            }
            if ($level === 'warning' || $level === 'errorwarning' || $level === '*') {
                $tmp = $v->allAssocArray('warning');
                $result = array_merge($result, $tmp);
            }
            if ($level === 'info' || $level === '*') {
                $tmp = $v->allAssocArray('info');
                $result = array_merge($result, $tmp);
            }
            if ($level === 'success' || $level === '*') {
                $tmp = $v->allAssocArray('success');
                $result = array_merge($result, $tmp);
            }
        }
        return $result;
    }

    /**
     * It returns true if there is an error (or error and warning).
     *
     * @param bool $includeWarning If true then it also returns if there is a warning
     * @return bool
     */
    public function hasError($includeWarning = false): bool
    {
        $tmp = $includeWarning
            ? $this->errorCount
            : $this->errorOrWarningCount;
        return $tmp !== 0;
    }
}
