<?php

$a= ['a1'=>"1",'a2'=>2,'a3'=>'aaa\'bbb','a4'=>'aaa"bbb'];

function varExport($input, $indent = "\t") {
    switch (gettype($input)) {
        case 'string':
            $r= "'" . addcslashes($input, "\\\$\'\r\n\t\v\f") . "'";
            break;
        case 'array':
            $indexed = array_keys($input) === range(0, count($input) - 1);
            $r = [];
            foreach ($input as $key => $value) {
                $r[] = "$indent    " . ($indexed ? '' : varExport($key) . ' => ') .
                    varExport($value, "$indent    ");
            }

            $r= "[\n" . implode(",\n", $r) . "\n" . $indent . ']';
            break;
        case 'boolean':
            $r= $input ? 'TRUE' : 'FALSE';
            break;
        default:
            $r= var_export($input, true);
            break;
    }
    return $r;
}

$r=varExport($a);
//$r=str_replace("\\'",'--safe--',$r);
//$r=str_replace('"','\\"',$r);
var_dump($r);