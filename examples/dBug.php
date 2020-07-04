<?php /** @noinspection PhpUnusedParameterInspection */
/** @noinspection FopenBinaryUnsafeUsageInspection */
/** @noinspection PhpComposerExtensionStubsInspection */
/** @noinspection AlterInForeachInspection */
/** @noinspection PregQuoteUsageInspection */
/** @noinspection UnknownInspectionInspection */
/** @noinspection HtmlUnknownAttribute */
/** @noinspection HtmlDeprecatedAttribute */
/*********************************************************************************************************************\
 *
 * USAGE
 * =============
 * new dBug ( variable [,forceType] );
 * example:
 * new dBug ( $myVariable );
 *
 *
 * if the optional "forceType" string is given, the variable supplied to the
 * function is forced to have that forceType type.
 * example: new dBug( $myVariable , "array" );
 * will force $myVariable to be treated and dumped as an array type,
 * even though it might originally have been a string type, etc.
 *
 * NOTE!
 * ==============
 * forceType is REQUIRED for dumping an xml string or xml file
 * new dBug ( $strXml, "xml" );
 *
 * \*********************************************************************************************************************/
/**
 * @author ospinto
 * @author KOLANICH
 * @author eftec
 */

namespace dBug;

use Exception;
use Iterator;
use PDOStatement;
use ReflectionClass;
use ReflectionException;
use ReflectionObject;
use ReflectionProperty;
use SQLite3Result;

class dBug
{
    public $xmlCData;
    public $xmlSData;
    public $xmlDData;
    public $xmlCount = 0;
    public $xmlAttrib;
    public $xmlName;
    //var $arrType=array("array",'object',"resource","boolean","NULL");

    //!shows wheither the main header for this dBug call was drawn
    public $bInitialized = false;
    public $bCollapsed = false;
    /** @var bool if true then it shows the type of variable */
    public $vType = true;
    public $arrHistory = array();
    public static $embeddedStringMaxLength = 80;
    public $classRefl;

    /**
     * @param mixed $var Variable to dump
     * @param string $forceType type to marshall $var to show
     * @param boolean $bCollapsed should output be collapsed
     * @param bool $varType
     * @throws ReflectionException
     * @noinspection PhpUnhandledExceptionInspection
     */
    public function __construct($var, $forceType = "", $bCollapsed = false, $varType = true) {
        //include js and css scripts
        if (!defined('BDBUGINIT')) {
            define('BDBUGINIT', true);
            static::initJSandCSS();
        }
        $this->vType = $varType;
        $this->classRefl = new ReflectionClass(get_class($this));

        $arrAccept = array("array", 'object', "xml"); //array of variable types that can be "forced"
        $this->bCollapsed = $bCollapsed;
        if (in_array($forceType, $arrAccept)) {
            $this->{"varIs" . ucfirst($forceType)}($var);
        } else {
            $this->checkType($var);
        }
    }

    //get variable name
    public function getVariableName() {
        $arrBacktrace = debug_backtrace();

        //possible 'included' functions
        $arrInclude = array("include", "include_once", "require", "require_once");

        //check for any included/required files. if found, get array of the last included file (they contain the right line numbers)
        for ($i = count($arrBacktrace) - 1; $i >= 0; $i--) {
            $arrCurrent = $arrBacktrace[$i];
            if (array_key_exists("function", $arrCurrent) && (in_array($arrCurrent["function"], $arrInclude) ||
                    (0 != strcasecmp($arrCurrent["function"], $this->classRefl->getConstructor()->getName())))) {
                continue;
            }

            $arrFile = $arrCurrent;

            break;
        }

        if (isset($arrFile)) {
            $arrLines = file($arrFile["file"]);
            $code = $arrLines[($arrFile["line"] - 1)];

            //find call to dBug class
            preg_match('/\bnew\s+(?:' . preg_quote($this->classRefl->getNamespaceName()) . '\\\\)?' .
                preg_quote($this->classRefl->getShortName()) . '\s*\(\s*(.+)\s*\);/i', $code, $arrMatches);
            return isset($arrMatches[1]) ? $arrMatches[1] : '[multiline]';
        }

        return "";
    }

    public function initializeHeader(&$header) {
        if (!$this->bInitialized) {
            $header = $this->getVariableName() . " (" . $header . ")";
            $this->bInitialized = true;
        }
    }


    //<editor-fold desc="name rendering functions">
    /**
     * name rendering functions
     * used to make tables representing different variables
     */
    //!creates the main table header
    /**
     * @param string $type name of the style of the header cell
     * @param string $header the text of the header cell
     * @param integer $colspan colspan property for the header cell
     */
    public function makeTableHeader($type, $header, $colspan = 2) {
        $this->initializeHeader($header);
        $this->renderTableHeader($type, $header, $colspan);
    }

    //!draws the main table header

    /**
     * @param string $type name of the style of the header cell
     * @param string $text the text of the header cell
     * @param integer $colspan colspan property for the header cell
     */
    public function renderTableHeader($type, $text, $colspan = 0) {
        echo '<table cellspacing=2 cellpadding=3 class="dBug dBug_' . $type . '">
				<tr>
					<td ' . (($this->bCollapsed) ? 'style="font-style:italic" ' : '') . 'class="dBug_' . $type .
            'Header" ' . ($colspan ? 'colspan=' . $colspan : '') . ' onClick="dBug_toggleTable(this)">' . $text . '</td>
				</tr>';
    }

    //!renders table of 2 cells in 1 row [type][value]

    /**
     * @param string $headerStyle name of the style of the header cell
     * @param string $header the text of the header cell
     * @param string $value the text of the value cell
     * @param string $valueStyle name of the style of the value cell
     * @param integer $colspan colspan property for the header cell
     */
    public function renderPrimitiveType($headerStyle, $header, $value, $valueStyle = null, $colspan = 0) {
        if (!$valueStyle) {
            $valueStyle = $headerStyle;
        }
        //$header='';
        $this->initializeHeader($header);
        echo '<table cellspacing=2 cellpadding=3 class="dBug_' . $headerStyle . '">
				<tr>';
        if ($this->vType) {
            echo '<td ' . (($this->bCollapsed) ? 'style="font-style:italic" ' : '') . 'class="dBug_' . $headerStyle .
                'Header" ' . ($colspan ? 'colspan=' . $colspan : '') . ' onClick="dBug_toggleRow(this)">' . $header .
                '</td>';
        }
        echo '<td valign="top" class="dBug_' . $valueStyle . '">' . $value . '</td></tr>';
    }

    //!creates the table row header

    /**
     * @param string $type name of the style of the key cell
     * @param string $header the text of the key cell
     */
    public function makeTDHeader($type, $header) {
        echo '<tr' . ($this->bCollapsed ? ' style="display:none"' : '') . '>
				<td valign="top" onClick="dBug_toggleRow(this)" class="dBug_' . $type . 'Key">' . $header . '</td>
				<td>';
    }

    //!closes table row
    public function closeTDRow() {
        return "</td></tr>\n";
    }
    //!@}

    //!prints error
    public function error($type) {
        $error = 'Error: Variable cannot be a';
        // this just checks if the type starts with a vowel or "x" and displays either "a" or "an"
        if (in_array(substr($type, 0, 1), array('a', 'e', 'i', 'o', 'u', 'x'))) {
            $error .= 'n';
        }

        return ($error . ' ' . $type . ' type');
    }

    //!check variable type andd process the value in right way
    public function checkType($var) {
        $type = gettype($var);
        switch ($type) {
            case 'resource':
                $this->varIsResource($var);
                break;
            case 'object':
                $this->varIsObject($var);
                break;
            case 'array':
                $this->varIsArray($var);
                break;
            case 'integer':
            case 'double':
                $this->varIsNumeric($var, $type);
                break;
            case 'NULL':
                $this->varIsNULL();
                break;
            case 'boolean':
                $this->varIsBoolean($var);
                break;
            case 'string':
                $this->varIsString($var);
                break;
            default:
                $var = ($var == '') ? '[empty string]' : $var;
                echo "<table cellspacing=0><tr>\n<td>" . $var . "</td>\n</tr>\n</table>\n";
                break;
        }
    }
    //</editor-fold>


    //<editor-fold desc="name functions for rendering different types">
    //!renders NULL as red-pink rectangle
    public function varIsNULL() {
        $this->makeTableHeader('false', 'NULL');
        echo '</table>';
    }

    //!renders numeric types : integers and doubles
    public function varIsNumeric($var, $type) {
        $this->renderPrimitiveType('numeric', $type, $var);
        echo '</table>';
    }

    /**
     * renders string either as primitive type (if it is short (less than $embeddedStringMaxLength chars)
     * and contains of one line) or line-by-line (otherwise)
     */
    public function varIsString(&$var) {
        if ($var == '') {
            $this->makeTableHeader('string', 'empty string');
            echo '</table>';

            return;
        }
        $length = strlen($var);
        $nv = htmlspecialchars($var, ENT_QUOTES | ENT_SUBSTITUTE, '');
        $lines = preg_split('/\R/u', $nv);
        $linesCount = count($lines);
        if ($linesCount === 1 && $length <= static::$embeddedStringMaxLength) {
            $this->renderPrimitiveType('string', 'string [' . $length . ']', $nv);
        } else {
            $this->makeTableHeader('string', 'string (' . $length . ' chars @ ' . $linesCount . ' lines)');
            foreach ($lines as $num => $line) {
                $this->makeTDHeader('string', $num);
                echo($line == '' ? '[empty line]' : $line);
                $this->closeTDRow();
            }
        }
        echo "</table>";
    }

    //!renders  boolean variable
    public function varIsBoolean(&$var) {
        $var ? $this->renderPrimitiveType('boolean', 'boolean', 'TRUE', 'booleanTrue')
            : $this->renderPrimitiveType('boolean', 'boolean', 'FALSE', 'booleanFalse');
        echo '</table>';
    }

    public function varIsArray(&$var) {
        $var_ser = serialize($var);
        $this->arrHistory[] = $var_ser;

        $this->makeTableHeader('array', 'array');
        if (is_array($var)) {
            foreach ($var as $key => $value) {
                $this->makeTDHeader('array', $key);

                //check for recursion
                if (is_array($value)) {
                    $var_ser = serialize($value);
                    if (in_array($var_ser, $this->arrHistory, true)) {
                        echo '*RECURSION*';
                        echo $this->closeTDRow();
                        continue;
                    }
                }

                //if(in_array(gettype($value),$this->arrType))
                $this->checkType($value);
                /*else {
                    $value=(trim($value)=="") ? "[empty string]" : $value;
                    echo $value;
                }*/
                echo $this->closeTDRow();
            }
        } else {
            echo '<tr><td>' . $this->error('array') . $this->closeTDRow();
        }
        array_pop($this->arrHistory);
        echo '</table>';
    }

    //! checks wheither variable is object of special type (using varIs*Object), and renders it if it is generic object
    public function varIsObject(&$var) {
        if ($this->varIsSpecialObject($var)) {
            return 1;
        }
        $var_ser = serialize($var);
        $this->arrHistory[] = $var_ser;

        if (is_object($var)) {
            $this->makeTableHeader('object', 'object ( ' . get_class($var) . ' )');
            if (method_exists($var, '__toString')) {
                $str = $var->__toString();
                if ($str !== null) {
                    $this->makeTDHeader('string', '[string representation]');
                    $this->varIsString($str);
                    echo $this->closeTDRow();
                }
            }
            $arrObjVars = get_object_vars($var);
            foreach ($arrObjVars as $key => $value) {

                //$value=(!is_object($value) && !is_array($value) && trim($value)=="") ? "[empty string]" : $value;
                $this->makeTDHeader('object', $key);

                //check for recursion
                if (is_object($value) || is_array($value)) {
                    $var_ser = serialize($value);
                    if (in_array($var_ser, $this->arrHistory, true)) {
                        echo (is_object($value)) ? '*RECURSION* -> $' . get_class($value) : '*RECURSION*';
                        echo $this->closeTDRow();
                        continue;
                    }
                }
                //if(in_array(gettype($value),$this->arrType))
                $this->checkType($value);
                //else
                //echo $value;
                echo $this->closeTDRow();
            }
            $arrObjMethods = get_class_methods(get_class($var));
            foreach ($arrObjMethods as $key => $value) {
                $this->makeTDHeader('object', $value);
                echo '[function]' . $this->closeTDRow();
            }
            if ($var instanceof Iterator) {
                foreach ($var as $key => $value) {
                    $this->makeTDHeader('array', $key);
                    $this->checkType($value);
                    echo $this->closeTDRow();
                }
            }
        } else {
            $this->makeTableHeader('object', 'object');
            echo '<tr><td>' . $this->error('object') . $this->closeTDRow();
        }
        array_pop($this->arrHistory);
        echo '</table>';
        return null;
    }

    public function varIsSpecialObject(&$var) {
        if ($this->varIsDBObject($var)) {
            return 1;
        }
        if ($var instanceof Exception) {
            $this->varIsException($var);

            return 1;
        }

        return 0;
    }

    //!shows info about different resources, uses customized rendering founctions when needed
    public function varIsResource($var) {
        $this->makeTableHeader('resourceC', 'resource', 1);
        echo "<tr>\n<td>\n";
        $restype = get_resource_type($var);
        switch ($restype) {
            case 'fbsql result':
            case 'mssql result':
            case 'msql query':
            case 'pgsql result':
            case 'sybase-db result':
            case 'sybase-ct result':
            case 'mysql result':
                $db = current(explode(' ', $restype));
                $this->varIsDBResource($var, $db);
                break;
            case 'gd':
                $this->varIsGDResource($var);
                break;
            case 'xml':
                $this->varIsXmlResource($var);
                break;
            case 'curl':
                $this->varIsCurlEasyResource($var);
                break;
            /*case "curl_multi":
                $this->varIsCurlMultiResource($var);
            break;*/ default:
            echo $restype . $this->closeTDRow();
            break;
        }
        echo $this->closeTDRow() . "</table>\n";
    }

    //!@}

    /**
     * @name functions for rendering different resources
     */
    //</editor-fold>
    //!@{

    //!shows information about curl easy handles
    /**simply iterates through handle info and displays everything which is not converted into false*/
    /** @noinspection PhpComposerExtensionStubsInspection */
    public function varIsCurlEasyResource(&$var) {
        $this->makeTableHeader('resource', 'curl easy handle', 2);
        $info = curl_getinfo($var);
        foreach ($info as $name => &$piece) {
            if ($piece) {
                $this->makeTDHeader('resource', $name);
                //echo $piece.$this->closeTDRow();
                $this->checkType($piece);
                echo $this->closeTDRow();
            }
        }
        unset($info);
        echo '</table>';

    }

    //!not implemented yet
    public function varIsCurlMultiResource(&$var) {
    }

    //!if variable is an image/gd resource type
    public function varIsGDResource(&$var) {
        $this->makeTableHeader('resource', 'gd', 2);
        $this->makeTDHeader('resource', 'Width');
        $this->checkType(imagesx($var));
        echo $this->closeTDRow();
        $this->makeTDHeader('resource', 'Height');
        $this->checkType(imagesy($var));
        echo $this->closeTDRow();
        $this->makeTDHeader('resource', 'Colors');
        $this->checkType(imageistruecolor($var) ? 'TrueColor (16 777 216)' : imagecolorstotal($var));
        echo $this->closeTDRow();

        $this->makeTDHeader('resource', 'Image');

        ob_start();
        imagepng($var);
        $img = ob_get_clean();

        echo '<img src="data:image/png;base64,' . base64_encode($img) . '"/>' . $this->closeTDRow();
        echo '</table>';
    }

    //!@}

    /**
     * @name database results rendering functions
     */
    //!@{

    //!renders either PDO or SQLite3 statement objects
    /**@returns 1 if the object is DB object, 0 otherwise */
    public function varIsDBObject($var) {
        $structure = array();
        $data = array();
        $retres = false;
        if ($var instanceof SQLite3Result) {
            //$var=clone $var;
            $count = $var->numColumns();
            for ($i = 0; $i < $count; $i++) {
                $structure[$i] = array();
                $structure[$i][0] = $var->columnName($i);
                $structure[$i][1] = $var->columnType($i);
            }
            $var->reset();
            while ($res = $var->fetchArray(SQLITE3_NUM)) {
                $data[] = $res;
            }
            $var->reset();
            $dbtype = 'SQLite3';
            unset($var);
            $this->renderDBData($dbtype, $structure, $data);
            $retres = true;
        }
        if ($var instanceof PDOStatement) {
            //$var=clone $var;
            $count = $var->columnCount();
            $col = null;
            for ($i = 0; $i < $count; $i++) {
                //$col=$var->getColumnMeta(0);
                $col = $var->getColumnMeta($i);
                $structure[$i] = array();
                $structure[$i][0] = $col['name'];
                $structure[$i][1] =
                    (isset($col['driver:decl_type']) ? (isset($col["len"]) ? "({$col["len"]})" : '') . "\n" : '') .
                    "({$col["native_type"]})";
            }
            unset($col);
            $data = $var->fetchAll();
            $var->closeCursor();
            $dbtype = 'PDOStatement';
            unset($var);
            $this->renderDBData($dbtype, $structure, $data);
            $retres = true;
        }
        unset($dbtype);
        unset($data);
        unset($structure);

        return $retres;
    }

    //!renders database data
    /**
     * @param string $objectType type of the db, it is only the name of header now
     * @param array $structure 'header' of the table - columns names and types
     * @param array $data rows of sql request result
     */
    /** @noinspection PhpSingleStatementWithBracesInspection
     */
    public function renderDBData(&$objectType, &$structure, &$data) {
        $this->makeTableHeader('database', $objectType, count($structure) + 1);
        echo '<tr><td class="dBug_databaseKey">&nbsp;</td>';
        foreach ($structure as $field) {
            echo '<td class="dBug_databaseKey"' . (isset($field[1]) ? ' title="' . $field[1] . '"' : "") . '>' .
                $field[0] . "</td>";
        }
        echo '</tr>';
        if (empty($data)) {
            echo '<tr><td class="dBug_resourceKey" colspan="' . (count($structure) + 1) . '">[empty result]</td></tr>';
        } else {
            $i = 0;
        }
        foreach ($data as $row) {
            echo "<tr>\n";
            echo '<td class="dBug_resourceKey">' . (++$i) . '</td>';
            foreach ($row as $k => $kValue) {
                $fieldrow = ($kValue === '') ? '[empty string]' : $row[$k];
                echo '<td>' . $fieldrow . "</td>\n";
            }
            echo "</tr>\n";
        }
        echo '</table>';
    }

    //!renders database resource (fetch result) into table or ... smth else
    public function varIsDBResource($var, $db = 'mysql') {
        if ($db === 'pgsql') {
            $db = 'pg';
        }
        if ($db === 'sybase-db' || $db === 'sybase-ct') {
            $db = 'sybase';
        }
        $arrFields = array('name', 'type', 'flags');
        $numrows = call_user_func($db . '_num_rows', $var);
        $numfields = call_user_func($db . '_num_fields', $var);
        $this->makeTableHeader('database', $db . ' result', $numfields + 1);
        echo '<tr><td class="dBug_databaseKey">&nbsp;</td>';
        for ($i = 0; $i < $numfields; $i++) {
            $field_header = '';
            foreach ($arrFields as $j => $jValue) {
                $db_func = $db . '_field_' . $jValue;
                if (function_exists($db_func)) {
                    $fheader = $db_func($var, $i) . " ";
                    if ($j == 0) {
                        $field_name = $fheader;
                    } else {
                        $field_header .= $fheader;
                    }
                }
            }
            $field[$i] = call_user_func($db . '_fetch_field', $var, $i);
            echo '<td class="dBug_databaseKey" title="' . $field_header . '">' . $field_name . '</td>';
        }
        echo '</tr>';
        for ($i = 0; $i < $numrows; $i++) {
            $row = call_user_func($db . '_fetch_array', $var, constant(strtoupper($db) . '_ASSOC'));
            echo "<tr>\n";
            echo '<td class="dBug_databaseKey">' . ($i + 1) . '</td>';
            for ($k = 0; $k < $numfields; $k++) {
                $fieldrow = $row[($field[$k]->name)];
                $fieldrow = ($fieldrow == '') ? '[empty string]' : $fieldrow;
                echo '<td>' . $fieldrow . "</td>\n";
            }
            echo "</tr>\n";
        }
        echo '</table>';
        if ($numrows > 0) {
            call_user_func($db . '_data_seek', $var, 0);
        }
    }
    //!@}

    /**
     * @name other special kinds of objects rendering functionality
     */
    //!@{

    //!array of properties of every exception to be rendered first
    public static $exceptionMainProps = array('message', 'code', 'file', 'line');
    //!array of properties of Exception of not to be rendered
    public static $exceptionExcludedProps = array(
        //'xdebug_message',
        'trace'
    );

    //!function used to render exceptions

    /**
     * Renders exceptions : at first basic fields, then custom fields.
     * Custom private and protected fields are rendered if reflection api is available
     */
    public function varIsException(&$var) {
        $code = $var->getCode();
        $this->makeTableHeader('Exception', get_class($var) . ' :: ' . $code);
        foreach (static::$exceptionMainProps as &$pname) {
            $this->makeTDHeader('Exception', $pname);
            $this->checkType($var->{'get' . ucfirst($pname)}());
            echo $this->closeTDRow();
        }
        unset($pname);
        echo '<tr><td></td></tr>';
        if (extension_loaded('Reflection')) {
            $refl = new ReflectionObject($var);
            $props = $refl->getProperties(ReflectionProperty::IS_PROTECTED | ReflectionProperty::IS_PRIVATE);
            foreach ($props as &$prop) {
                $pname = $prop->getName();
                if (in_array($pname, static::$exceptionMainProps) ||
                    in_array($pname, static::$exceptionExcludedProps)) {
                    continue;
                }
                $this->makeTDHeader('Exception', $pname);
                $prop->setAccessible(true);
                $this->checkType($prop->getValue($var));
                $prop->setAccessible(false);
                echo $this->closeTDRow();
            }
        }

        foreach ($var as $key => &$value) {
            if ($key === 'xdebug_message') {
                continue;
            }
            $this->makeTDHeader('Exception', $key);
            $this->checkType($value);
            echo $this->closeTDRow();
        }

        echo '</table>';
    }

    //!@}

    /**
     * @name xml rendering functions
     */
    //!@{

    //!if variable is an xml type
    //!remember, that you must force type to xml to use this
    public function varIsXml($var) {
        $this->varIsXmlResource($var);
    }

    //!if variable is an xml resource type

    /** @noinspection PhpUnhandledExceptionInspection */
    public function varIsXmlResource($var) {
        $xml_parser = xml_parser_create();
        xml_parser_set_option($xml_parser, XML_OPTION_CASE_FOLDING, 0);
        xml_set_element_handler($xml_parser, array(&$this, 'xmlStartElement'), array(&$this, 'xmlEndElement'));
        xml_set_character_data_handler($xml_parser, array(&$this, 'xmlCharacterData'));
        xml_set_default_handler($xml_parser, array(&$this, 'xmlDefaultHandler'));

        $this->makeTableHeader('xml', 'xml document', 2);
        $this->makeTDHeader('xml', 'xmlRoot');

        //attempt to open xml file
        $bFile = (!($fp = @fopen($var, 'r'))) ? false : true;

        //read xml file
        if ($bFile) {
            while ($data = str_replace("\n", '', fread($fp, 4096))) {
                $this->xmlParse($xml_parser, $data, feof($fp));
            }
        } //if xml is not a file, attempt to read it as a string
        else {
            if (!is_string($var)) {
                echo $this->error('xml') . $this->closeTDRow() . "</table>\n";

                return;
            }
            $data = $var;
            $this->xmlParse($xml_parser, $data, 1);
        }

        echo $this->closeTDRow() . "</table>\n";

    }

    //!parses xml
    public function xmlParse($xml_parser, $data, $bFinal) {
        if (!xml_parse($xml_parser, $data, $bFinal)) {
            throw new Exception(sprintf("dBug XML error: %s at line %d\n",
                xml_error_string(xml_get_error_code($xml_parser)),
                xml_get_current_line_number($xml_parser)));
        }
    }

    //!xml: inititiated when a start tag is encountered
    public function xmlStartElement($parser, $name, $attribs) {
        $this->xmlAttrib[$this->xmlCount] = $attribs;
        $this->xmlName[$this->xmlCount] = $name;
        $this->xmlSData[$this->xmlCount] = '$this->makeTableHeader("xml","xml element",2);';
        $this->xmlSData[$this->xmlCount] .= '$this->makeTDHeader("xml","xmlName");';
        $this->xmlSData[$this->xmlCount] .= 'echo "<strong>' . $this->xmlName[$this->xmlCount] .
            '</strong>".$this->closeTDRow();';
        $this->xmlSData[$this->xmlCount] .= '$this->makeTDHeader("xml","xmlAttributes");';
        if (count($attribs) > 0) {
            $this->xmlSData[$this->xmlCount] .= '$this->varIsArray($this->xmlAttrib[' . $this->xmlCount . ']);';
        } else {
            $this->xmlSData[$this->xmlCount] .= 'echo "&nbsp;";';
        }
        $this->xmlSData[$this->xmlCount] .= 'echo $this->closeTDRow();';
        $this->xmlCount++;
    }

    //!xml: initiated when an end tag is encountered
    public function xmlEndElement($parser, $name) {
        for ($i = 0; $i < $this->xmlCount; $i++) {
            eval($this->xmlSData[$i]);
            $this->makeTDHeader("xml", "xmlText");
            echo (!empty($this->xmlCData[$i])) ? $this->xmlCData[$i] : "&nbsp;";
            echo $this->closeTDRow();
            $this->makeTDHeader("xml", "xmlComment");
            echo (!empty($this->xmlDData[$i])) ? $this->xmlDData[$i] : "&nbsp;";
            echo $this->closeTDRow();
            $this->makeTDHeader('xml', "xmlChildren");
            unset($this->xmlCData[$i], $this->xmlDData[$i]);
        }
        echo $this->closeTDRow();
        echo '</table>';
        $this->xmlCount = 0;
    }

    //!xml: initiated when text between tags is encountered
    public function xmlCharacterData($parser, $data) {
        $count = $this->xmlCount - 1;
        if (!empty($this->xmlCData[$count])) {
            $this->xmlCData[$count] .= $data;
        } else {
            $this->xmlCData[$count] = $data;
        }
    }

    //!@}

    //!xml: initiated when a comment or other miscellaneous texts is encountered
    public function xmlDefaultHandler($parser, $data) {
        //strip '<!--' and '-->' off comments
        $data = str_replace(array("&lt;!--", "--&gt;"), "", htmlspecialchars($data));
        $count = $this->xmlCount - 1;
        if (!empty($this->xmlDData[$count])) {
            $this->xmlDData[$count] .= $data;
        } else {
            $this->xmlDData[$count] = $data;
        }
    }

    //! adds needed JS and CSS sources to page
    public static function initJSandCSS() {
        echo <<<SCRIPTS
			<!--suppress ALL -->
<script language="JavaScript">
			/* code modified from ColdFusion's cfdump code */
				function dBug_toggleRow(source)
				{
					var target = (document.all) ? source.parentElement.cells[1] : source.parentNode.lastChild;
					dBug_toggleTarget(target,dBug_toggleSource(source));
				}

				function dBug_toggleSource(source)
				{
					if (source.style.fontStyle==='italic') {
						source.style.fontStyle='normal';
						source.title='click to collapse';

						return 'open';
					} else {
						source.style.fontStyle='italic';
						source.title='click to expand';

						return 'closed';
					}
				}

				function dBug_toggleTarget(target,switchToState)
				{
					target.style.display = (switchToState==='open') ? '' : 'none';
				}

				function dBug_toggleTable(source)
				{
					var switchToState=dBug_toggleSource(source);
					if (document.all) {
						var table=source.parentElement.parentElement;
						for (var i=1;i<table.rows.length;i++) {
							target=table.rows[i];
							dBug_toggleTarget(target,switchToState);
						}
					} else {
						var table=source.parentNode.parentNode;
						for (var i=1;i<table.childNodes.length;i++) {
							target=table.childNodes[i];
							if (target.style) {
								dBug_toggleTarget(target,switchToState);
							}
						}
					}
				}
			</script>

			<style type="text/css">
				table.dBug_array,table.dBug_object,table.dBug_resource,table.dBug_resourceC,table.dBug_xml{
					font-family:Verdana, Arial, Helvetica, sans-serif; color:#000000; font-size:12px;
				}

				.dBug_arrayHeader,
				.dBug_objectHeader,
				.dBug_resourceHeader,
				.dBug_resourceCHeader,
				.dBug_xmlHeader,
				.dBug_falseHeader,
				.dBug_numericHeader,
				.dBug_databaseHeader
					{ font-weight:bold; color:#FFFFFF; cursor:pointer; }

				.dBug_arrayKey,
				.dBug_objectKey,
				.dBug_xmlKey,
				.dBug_stringHeader,
				.dBug_booleanHeader,
				.dBug_ExceptionHeader
					{ cursor:pointer; }

				.dBug_numeric td{
					color:black;
				}

				table.dBug_string,td.dBug_numericHeader{
					color:white;
				}

				/*Exception*/
				table.dBug_Exception {
					background-color:#AB1212;
					border-width: 5px;
					border-color: #FF006E;
					border-style: dashed;
					font-weight: bolder;
					color: #FFF8F8;
				}
				table.dBug_Exception td { background-color:#E13636;}
				table.dBug_Exception td.dBug_ExceptionHeader { background-color:#FF0000; }
				table.dBug_Exception td.dBug_ExceptionKey { background-color:#E65050; }

				/* array */
				table.dBug_array { background-color:#006600; }
				table.dBug_array td { background-color:#FFFFFF; }
				table.dBug_array td.dBug_arrayHeader { background-color:#009900; }
				table.dBug_array,.dBug_object td.dBug_arrayKey { background-color:#CCFFCC; }

				/* object */
				table.dBug_object { background-color:#0000CC; }
				table.dBug_object td { background-color:#FFFFFF; }
				table.dBug_object td.dBug_objectHeader { background-color:#4444CC; }
				table.dBug_object td.dBug_objectKey { background-color:#CCDDFF; }

				/* resource */
				table.dBug_resourceC { background-color:#884488; }
				table.dBug_resourceC td { background-color:#FFFFFF; }
				table.dBug_resourceC td.dBug_resourceCHeader { background-color:#AA66AA; }
				table.dBug_resourceC td.dBug_resourceCKey { background-color:#FFDDFF; }

				/* resource */
				table.dBug_resource { background-color:#884488; }
				table.dBug_resource td { background-color:#FFFFFF; }
				table.dBug_resource td.dBug_resourceHeader { background-color:#AA66AA; }
				table.dBug_resource td.dBug_resourceKey { background-color:#FFDDFF; }

				/* xml */
				table.dBug_xml { background-color:#888888; }
				table.dBug_xml td { background-color:#FFFFFF; }
				table.dBug_xml td.dBug_xmlHeader { background-color:#AAAAAA; }
				table.dBug_xml td.dBug_xmlKey { background-color:#DDDDDD; }


				/* database */
				table.dBug_database { background-color:#8FB6E6 }
				table.dBug_database td { background-color:#07DDF9; }
				table.dBug_database td.dBug_databaseHeader { background-color:#07F7FB; }
				table.dBug_database td.dBug_databaseKey { background-color:#AEF4F5; }


				/* FALSE and boolean false*/
				table.dBug_false { background-color:#CB0101; }
				table.dBug_false td { background-color:#FFFFFF; }
				table.dBug_boolean td.dBug_booleanFalse,table.dBug_false td.dBug_falseHeader { background-color:#F2054C; }
				table.dBug_false td.dBug_falseKey { background-color:#DDDDDD; }


				/* numeric */
				table.dBug_numeric { background-color:#F9C007; }
				table.dBug_numeric td { background-color:#FFFFFF; }
				table.dBug_numeric td.dBug_numericHeader { background-color:#F2D904; }
				//table.dBug_numeric td.dBug_numericKey { background-color:#DDDDDD; }


				/* string */
				table.dBug_string { background-color:#556832 }
				table.dBug_string td { background-color:#B3C520;}
				table.dBug_string td.dBug_stringHeader { background-color:#808000; }
				table.dBug_string,.dBug_object td.dBug_stringKey { background-color:#96A428; }

				/*boolean*/
				table.dBug_boolean { background-color:#43769F }
				table.dBug_boolean td.dBug_booleanHeader { background-color:#5EA5DE; }
				table.dBug_boolean td.dBug_booleanTrue { background-color:#04F258; }
				table.dBug_boolean td.dBug_booleanTrue,table.dBug_boolean td.dBug_booleanFalse { width:46px; text-align: center;border-radius: 45%;}

			</style>
SCRIPTS;
    }
}