<?php
require("cache.php");
/**
 * php多语言SDK的主入口，有两个成员函数，一个是初始化构造接口，另一个是接收词条翻译接口；
 * 该版本支持多文件存储，但文件更新机制，用户可以指定词条存入的文件名，之后在该文件中取词。
 * 如果传入的初始语言和目标语言是相同的，那么不进行任何网络请求，不做任何翻译，直接返回原词条。
 * @param string $serviceName 是在行云平台申请多语言服务的服务名称；
 * @param string $apiKey 行云平台的每个多语言服务都会有一个给定的apiKey，服务的唯一标识
 * @param string $srcLang 未翻译词条对应语言的缩写（详细请查看行云平台具体文档）
 * @param string $tarLang 翻译结果对应语言的缩写
 * @param boolean $autoAddTrans 是否自动添加未翻译词条到多语言服务器，默认为FALSE
 * @param boolean $autoUpdateFile 是否自动从多语言服务器上更新本地缓存，默认为FALSE
 * @param Integer $queueNum 本地缓存队列长度，默认是10
 * @param string $cacheDir 本地缓存目录地址，默认是当前文件目录下
 *
 */
class ML{
	public $tranSign = False;
    public function __construct($serviceName, $apiKey, $srcLang, $tarLang, $autoAddTrans = FALSE, $autoUpdateFile = FALSE, $queueNum = 10, $cacheDir = ""){
    	if($srcLang == $tarLang){
    		$this->tranSign = False;
    	}else{
    		$this->tranSign = True;
        	$this->cache = new CacheObj($serviceName, $apiKey, $tarLang, $autoAddTrans, $autoUpdateFile, $queueNum, $cacheDir);
    	}
    }
	/**
	 * 翻译词条接口，输入词条，返回翻译结果，如果本地缓存中没有翻译结果，则返回原词条
	 * @param String $words	需要翻译的词条
	 * @param string $filename 本地缓存文件后缀名,以.json结尾,如果文件名后缀不是.json,那么会强行加以个.json后缀
	 * 
	 */
    public function trans($words, $fileName = "xc_words.json"){
    	if(!preg_match("/.*\.json$/", $fileName)){
    		$fileName = $fileName.".json";
    	}
        if(trim($words) and $this->tranSign){
            $words = $this->wrapWords($words);
            $content = $this->cache->findString($words, $fileName);
            $content = $this->unWrapWords($content);
            return $content;
        }else{
            return $words;
        }
    }
    public function wrapWords($words){
        $pattern = "/(<.*?>)/i";
        $matches = preg_split($pattern, $words, -1, PREG_SPLIT_DELIM_CAPTURE);

        $tmpWords = "";
        for($i = 0; $i < count($matches); $i++){
            if (preg_match("/<.*>/i", $matches[$i])){
                $tmpWords .= "{".$matches[$i]."}";
            }else{
                $tmpWords .= $matches[$i];
            }
        }
        return $tmpWords;
    }

    public function unWrapWords($words){
        $pattern = "/({.*?})/i";
        $matches = preg_split($pattern, $words, -1, PREG_SPLIT_DELIM_CAPTURE);

        $transWrods = "";

        for($i = 0; $i < count($matches); $i++){
            $transWrods .= trim($matches[$i], "{}");
        }
        return $transWrods;
    }
}

?>