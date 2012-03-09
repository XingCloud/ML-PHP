<?php
require("rest.php");
/**
 * 管理本地翻译结果缓存。如果允许与多语言服务器交互，则判断本地缓存是否为最新，如果不是最新，那么更新本地缓存；
 * 将本地缓存加载到内存中，并生成一个hashmap，以供后面使用。
 * @param string $serviceName 是在行云平台申请多语言服务的服务名称；
 * @param string $apiKey 行云平台的每个多语言服务都会有一个给定的apiKey，服务的唯一标识
 * @param string $tarLang 翻译结果对应语言的缩写
 * @param boolean $autoAddTrans 是否自动添加未翻译词条到多语言服务器，默认为FALSE
 * @param boolean $autoUpdateFile 是否自动从多语言服务器上更新本地缓存，默认为FALSE
 * @param Integer $queueNum 本地缓存队列长度，默认是10
 * @param string $cacheDir 本地缓存目录地址，默认是当前文件目录下
 *
 */
class CacheObj{
    public $serviceName = "";
    public $tarLang = "";
    public $contentArray = array();
    public $queue = array();
    public $restObj;
    public $apiKey = "";
    public $autoAddTrans = FALSE;
    public $queueNum;
    public function __construct($serviceName, $apiKey, $tarLang, $autoAddTrans = FALSE, $autoUpdateFile = FALSE, $queueNum, $cacheDir = ""){
        $this->serviceName = $serviceName;
        $this->tarLang = $tarLang;
        $this->autoAddTrans = $autoAddTrans;
        $this->queueNum = $queueNum;
        if($autoUpdateFile){
        	$this->restObj = new RestWrapper($this->serviceName, $apiKey, $tarLang, $cacheDir);
        }
    }
    
    /**
     * 在本地缓存中查找词条翻译结果。如果词条翻译结果在本地缓存中不存在，那么将返回原词条，此时，如果允许允许与多语言
     * 服务器交互，那么将该词发送到多语言翻译平台上，进行翻译。
     * @param string $words 用来翻译的词条
     * @param string $fileName 词条存储的文件名
     */

    public function findString($words, $fileName){
    	$filePath = trim($cacheDir.DIRECTORY_SEPARATOR.$this->serviceName."_".$this->tarLang."_".$fileName, DIRECTORY_SEPARATOR);
    	$this->updateCache($fileName, $filePath);
        if(array_key_exists($fileName, $this->contentArray)){
        	if(array_key_exists($words, $this->contentArray[$fileName])){
        		return $this->contentArray[$fileName][$words];
        	}elseif ($this->autoAddTrans){
        		$this->restObj->restAdd($words, $fileName);
        	}
        }elseif(file_exists($filePath)){
        	$this->contentArray[$fileName] = json_decode(file_get_contents($filePath), True);
	        if(array_key_exists($words, $this->contentArray[$fileName])){
        		return $this->contentArray[$fileName][$words];
        	}elseif ($this->autoAddTrans){
        		$this->restObj->restAdd($words, $fileName);
        	}
        }else{
        	if($this->autoAddTrans){
            	$this->restObj->restAdd($words, $fileName);
        	}
            return $words;
        }
    }
    
    public function updateCache($fileName, $filePath){
    	if(file_exists($filePath)){
	    	if(array_key_exists($fileName, $this->queue)){
	    		foreach ($this->queue as $key => $val){
	    			if($key == $fileName){
	    				$this->queue[$key] = 0;
	    				break;
	    			}else{
	    				$this->queue[$key] = $val+1;
	    			}
	    		}
	    	}else{
	    		if(count($this->queue) > $this->queueNum){
	    			$keys = array_keys($this->queue);
	    			$key = $keys[count($this->queue)-1];
	    			unset($this->queue[$key]);
	    			unset($this->contentArray[$key]);
	    		}
	    		foreach ($this->queue as $key => $val){
	    			$this->queue[$key] = $val + 1;
	    		}
	    		$this->queue[$fileName] = 0;
	    	}
	    	asort($this->queue);
	    	var_dump($this->queue);
	    }
    }
}
?>