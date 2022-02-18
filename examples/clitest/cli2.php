<?php
include '../../vendor/autoload.php';

$cli=new \eftec\CliOne\CliOne();

/*for($i=0;$i<254;$i++) {
    echo $i.chr($i)."<br>";
}
*/
$txt='<bold>12345678901234567890</bold>';
echo "<pre>";
$color=$cli->replaceColor($txt);
echo $color."\n";
echo $cli->colorLess($color)."\n";
echo $cli->colorMask($color)."\n";
echo $cli->removechar($color,3)."\n";
echo "</pre>";
