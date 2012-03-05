ML PHP SDK
=============

使用说明
--------

	<?php
	require("ml.php");
	
	$srcLang = "cn";
	$tarLang = "en";
	
	$sdkObj = new ML("php_test002", "f58b2cc533bca5bcb73afc976a005dd1",$srcLang, $tarLang,True, True);
	echo "<br>";
	$word = "行云,你好";
	$ab = $sdkObj->trans($word);
	echo $ab;
	
	echo "<br>";
	$word = "中国";
	$ab = $sdkObj->trans($word);
	echo $ab;
	
	echo "<br>";
	$word = "人民爱你";
	$ab = $sdkObj->trans($word);
	echo $ab;
	
	echo "<br>";
	$word = "我很生气，后果很严重";
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

