<?php
require("ml.php");

$serviceName = "dstest001";
$srcLang = "cn";
$tarLang = "en";

//ML::init($serviceName, $tarLang);
//
//$words = "求交往";
//
//$abc = ML::trans($words);
//echo $abc;

$sdkObj = new ML($serviceName, $srcLang, $tarLang);
echo "<br>";
$word = "行云,你好";
$ab = $sdkObj->trans($word);
echo $ab;

echo "<br>";
$word = "中国";
$ab = $sdkObj->trans($word);
echo $ab;

echo "<br>";
$word = "漂亮";
$ab = $sdkObj->trans($word);
echo $ab;

echo "<br>";
$word = "美女，帅哥";
$ab = $sdkObj->trans($word);
echo $ab;

echo "<br>";
$word = "北京";
$ab = $sdkObj->trans($word);
echo $ab;

echo "<br>";
$word = "多语言";
$ab = $sdkObj->trans($word);
echo $ab;
?>