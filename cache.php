<?php
require("rest.php");
/**
 * 管理本地翻译结果缓存。如果允许与多语言服务器交互，则判断本地缓存是否为最新，如果不是最新，那么更新本地缓存；
 * 将本地缓存加载到内存中，并生成一个hashmap，以供后面使用。
 * @param string serviceName 是在行云平台申请多语言服务的服务名称；
 * @param string apiKey 行云平台的每个多语言服务都会有一个给定的apiKey，服务的唯一标识
 * @param string tarLang 翻译结果对应语言的缩写
 * @param string filePath 本地缓存路径
 * @param array contentArray 内存hashmap
 * @param SDKRest restObj SDKRest对象实例
 * @param string fileContentMd5 本地缓存内容的md5值
 *
 */
class CacheObj{
    public $serviceName = "";
    public $tarLang = "";
    public $filePath = "";
    public $contentArray = array();
    public $restObj;
    public $fileContentMd5 = "";
    public $apiKey = "";
    
    public function __construct($serviceName, $apiKey, $tarLang, $autoAddTrans = FALSE, $autoUpdateFile = FALSE, $cacheDir = ""){
        $this->serviceName = $serviceName;
        $this->tarLang = $tarLang;
        
        if($this->cacheDir == ""){
                $this->filePath = $this->serviceName.$this->tarLang.".php";
        }elseif(PATH_SEPARATOR == ":"){
                $this->filePath = $cacheDir."/".$this->serviceName."_".$this->tarLang.".php";
        }else{
            $this->filePath = $cacheDir."\\".$this->serviceName."_".$this->tarLang.".php";
        }
        
        if($autoUpdateFile){
        	$this->restObj = new SDKRest($this->serviceName, $apiKey, $tarLang, $this->filePath);
        }

	    if(file_exists($this->filePath)){
			require($this->filePath);
			$this->contentArray = $cacheArray;
			$this->fileContentMd5 = $fileContentMd5;
		}else{
			$this->fileContentMd5 = "";
			$this->contentArray = array();
		}
    }
    
    /**
     * 在本地缓存中查找词条翻译结果。如果词条翻译结果在本地缓存中不存在，那么将返回原词条，此时，如果允许允许与多语言
     * 服务器交互，那么将该词发送到多语言翻译平台上，进行翻译。
     * @param string $words 用来翻译的词条
     */

    public function findString($words){
        if(@$this->contentArray[$words]){
            return $this->contentArray[$words];
        }else{
        	if($autoAddTrans){
            	$this->restObj->restAdd($words);
        	}
            return $words;
        }
    }
}
?>