ML PHP SDK
=============

使用说明
--------

	<?php
	require("ml.php");

	$srcLang = "cn";
	$tarLang = "ko";

	$sdkObj = new ML("php_test001", "8c28052cf88fab323422dcf5bb74ab8d",$srcLang, $tarLang,True, True, 3);

	echo "<br>";
	$word = "行云,你好";
	$ab = $sdkObj->trans($word, "sdktest.json");
	echo $ab;

	echo "<br>";
	$word = "中国";
	$ab = $sdkObj->trans($word, "sdktest111.json");
	echo $ab;

	echo "<br>";
	$word = "人民爱你";
	$ab = $sdkObj->trans($word, "sdktest222.json");
	echo $ab;

	echo "<br>";
	$word = "我很生气，后果很严重";
	$ab = $sdkObj->trans($word, "sdktest333.json");
	echo $ab;


	echo "<br>";
	$word = "中国，两会";
	$ab = $sdkObj->trans($word, "sdktest444.json");
	echo $ab;

	?>
