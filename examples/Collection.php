<?php

namespace mapache_commons;

/**
 * Class Collection
 * @package mapache_commons
 * @version 1.1 2018-09-26
 * @copyright Jorge Castro Castillo
 * @license Apache-2.0
 * @see https://github.com/EFTEC/mapache-commons
 */
class Collection
{
    /**
     * Returns true if array is an associative array, false is it's an indexed array
     * @param array $array
     * @return bool
     * @see https://stackoverflow.com/questions/173400/how-to-check-if-php-array-is-associative-or-sequential
     */
    public static function isAssoc($array){
        return (array_values($array) !== $array);
    }

    /**
     * Returns the first element of an array.
     * @param $array
     * @return mixed
     * @see https://stackoverflow.com/questions/1921421/get-the-first-element-of-an-array
     */
    public static function first($array) {
        return reset($array);
    }
    /**
     * Returns the first key of an array.
     * @param $array
     * @return mixed
     * @see  https://stackoverflow.com/questions/1921421/get-the-first-element-of-an-array
     */
    public static function firstKey($array) {
        reset($array);
        return key($array);
    }

    /**
     * Change the case of the key to lowercase
     * @param $arr
     * @return array
     * @see https://stackoverflow.com/questions/1444484/how-to-convert-all-keys-in-a-multi-dimenional-array-to-snake-case
     */
    public static function arrayKeyLower($arr)
    {
        return array_map(function($item){
            if(is_array($item)) {
                $item = self::arrayKeyLower($item);
            }
            return $item;
        },array_change_key_case($arr,CASE_LOWER));
    }
    /**
     * Change the case of the key to lowercase
     * @param $arr
     * @return array
     * @see https://stackoverflow.com/questions/1444484/how-to-convert-all-keys-in-a-multi-dimenional-array-to-snake-case
     */
    public static function arrayKeyUpper($arr)
    {
        return array_map(function($item){
            if(is_array($item)) {
                $item = self::arrayKeyUpper($item);
            }
            return $item;
        },array_change_key_case($arr,CASE_UPPER));
    }

    /**
     * Generate a table from an array
     * @param array|null|object $array
     * @param string|bool $css if true then it uses the build in style. If false then it doesn't use style. If string then it uses as class
     * @return string
     * @see https://stackoverflow.com/questions/4746079/how-to-create-a-html-table-from-a-php-array
     */
    public static function generateTable($array,$css=true){
        if(is_object($array)) {
            $array=(array)$array;
        }
        if (!isset($array[0])) {
            $tmp=$array;
            $array=array();
            $array[0]=$tmp;
        } // create an array with a single element
        if ($array[0]===null) {
            return "NULL<br>";
        }
        if ($css===true) {
            $html =
                '<style>.generateTable {
            border-collapse: collapse;
            width: 100%;
        }
        .generateTable td, .generateTable th {
            border: 1px solid #ddd;
            padding: 8px;
        }
        .generateTable tr:nth-child(even){background-color: #f2f2f2;}        
        .generateTable tr:hover {background-color: #ddd;}        
        .generateTable th {
            padding-top: 12px;
            padding-bottom: 12px;
            text-align: left;
            background-color: #4CAF50;
            color: white;
        }
        </style>';
        } else {
            $html='';
        }
        $html .= '<table class="'.(is_string($css)?$css:'generateTable').'">';
        // header row
        $html .= '<thead><tr >';
        if (is_array($array[0])) {
            foreach ($array[0] as $key => $value) {
                $html .= '<th >' . htmlspecialchars($key) . '</th>';
            }
        } else {
                $html .= '<th >Column</th>';
        }
        $html .= '</tr></thead>';

        // data rows
        foreach( $array as $key=>$value){
            $html .= '<tr >';
            if (is_array($value) || is_object($value)) {
                foreach ($value as $key2 => $value2) {
                    if(is_array($value2) ) {
                        $html .= '<td >' .self::generateTable($value2) . '</td>';
                        //$html .= '<td >' . htmlspecialchars(json_encode($value2)) . '</td>';    
                    } elseif(is_object($value2)) {
                        $html .= '<td >' . htmlspecialchars(json_encode($value2)) . '</td>';
                    } else {
                        $html .= '<td >' . htmlspecialchars($value2) . '</td>';
                    }
                    
                }
            } else {
                $html .= '<td >' . $value . '</td>';
            }
            $html .= '</tr>';
        }

        // finish table and return it

        $html .= '</table>';
        return $html;
    }
}