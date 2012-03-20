ML PHP SDK
=============

初始化：init()
--------------

ML($serviceName, $apiKey, $srcLang, $tarLang, $autoAddTrans = FALSE, $autoUpdateFile = FALSE, $queueNum = 10, $cacheDir = "")

通过该方法初始化ML。初始化后即可通过obj.trans()翻译词句(其中obj为初始化创建的多语言对象)。

#### 参数类型

* $serviceName: String - 服务名称, 如 "my_ml_test"
* $apiKey: String - 行云多语言管理系统分配的API密钥, 如 "21f...e35"
* $srcLang: String - 原始语言, 如"cn"
* $tarLang: String - 目标语言, 如"en", 如果与原始语言相同, 则不翻译直接原文返回
* $autoAddTrans: Boolean - 是否自动添加未翻译词句到多语言服务器, 默认为false
* $autoUpdateFile: Boolean - 是否自动从多语言服务器上更新本地缓存，默认为FALSE
* $queueNum: Integer - 本地缓存队列长度，默认是10
* $cacheDir: String - 本地缓存目录地址，默认是当前文件目录下

#### 返回值

多语言服务的一个对象 object

#### 代码示例

	// 在应用的主类初始化函数中加入下面这行代码，如果与原始语言相同，则不翻译直接原文返回
	$sdkObj = new ML("php_test001", "8c28052cf98fab323422dcf5bb74ab8d",$srcLang, $tarLang,True, True, 3);
		
翻译词句：trans()
-----------------

public function trans($words, $fileName = "xc_words.json")

通过该方法直接翻译词句。

#### 参数类型

* $source: String - 需要翻译的词句, 如 "游戏开始"
* $fileName: String - 翻译词条被组织到那个文件中， 默认是"xc_words.json"

#### 返回值

String - 翻译好的词句, 如 "game start"

#### 代码示例

	// 示例
	$sdkObj = new ML("php_test001", "8c28052cf98fab323422dcf5bb74ab8d",$srcLang, $tarLang,True, True, 3);
	$word = "行云,你好";
	$ab = $sdkObj->trans($word, "sdktest.json");
	