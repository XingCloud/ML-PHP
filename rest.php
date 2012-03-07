<?php
define('ML_REST_HOST', 'http://i.xingcloud.com/api/v1/');
/**
 * 与多语言服务器交互模块。
 * @param string filePath 本地缓存文件存放路径
 * @param string serviceName 是在行云平台申请多语言服务的服务名称；
 * @param string apiKey 行云平台的每个多语言服务都会有一个给定的apiKey，服务的唯一标识
 * @param string tarLang 翻译结果对应语言的缩写
 *
 */
class RestWrapper{

    public $filePath = "";
    public $serviceName= "";
    public $tarLang = "";
    public $restFileInfo = "";
    public $restStringAdd = "";
	public $md5FileName = "";
	public $apiKey = "";
	public $fileMd5 = "";
	public $logPath = "";
	
    public function __construct($serviceName, $apiKey, $tarLang, $filePath){
        $this->restFileInfo = ML_REST_HOST."file/info";
        $this->restStringAdd = ML_REST_HOST."string/add";
        $this->apiKey = $apiKey;
        $this->serviceName= $serviceName;
        $this->tarLang = $tarLang;
        $this->filePath = $filePath;
        $this->logPath = rtrim($filePath, ".php").".log";
        if(isset($fileContentMd5)){
        	$this->fileMd5 = $fileContentMd5;
        }
        $ret = $this->getFileInfo();
        if($ret){
	        $retArray = json_decode($ret, true);
	        if($this->updateFileContent($retArray["data"]["md5"])){
		        $remoteFilePath = $retArray["data"]["request_address"];
		        $this->downloadFile($remoteFilePath, $this->filePath);
	        }
        }
    }
    
    /**
     * 从多语言服务器端获取本地缓存所对应文件的状态信息。
     * 返回值为：
     *  @return
     *  {
	 * 	    'data': {
	 *	        'file_path': ,	文件路径
	 *	        'status': ,		文件当前状态
	 *	        'source': ,		源语言
	 *	        'target': ,		目标语言
	 *	        'source_words_count': ,	原词总数
	 *	        'human_translated': ,	人工翻译词总数
	 *	        'machine_translated': ,	机器翻译词总数
	 *	        'request_address': ,	文件http可访问地址
	 *	        'length': ,				文件内容长度
	 *	        'md5': ,				文件内容md5值
	 *	    }
	 *	}
     */
    public function getFileInfo(){
    	$timeStamp = ceil(time()*1000);
		$hash = md5($timeStamp.$this->apiKey);
    	$url = $this->restFileInfo."?service_name=".$this->serviceName."&locale=".$this->tarLang."&file_path=xc_words.json&timestamp=".$timeStamp."&hash=".$hash;
    	return $this->restGetRequest($url);
    }
    
    public function updateFileContent($md5Val){
    	if($this->fileMd5 == $md5Val){
    		return False;
    	}else{
    		$this->fileMd5 = $md5Val;
    		return True;
    	}
    }

    /**
     * 将需要翻译的词条发送到多语言服务器上进行翻译，成功返回OK，失败返回FALSE
     * @param string $words 需要翻译的词条
     * 
     */
    public function restAdd($words){
		$timeStamp = ceil(time()*1000);
		$hash = md5($timeStamp.$this->apiKey);
        $data = array("service_name"=>$this->serviceName, "data"=>$words, "timestamp" => $timeStamp, "hash" => $hash);
        $retVal = $this->restPostRequest($this->restStringAdd, $data);
        return;
    }
    /**
     * Get请求
     * @param string $url Get请求地址，如：http://host/path?key1=val1&key2=val2...
     */
	public function restGetRequest($url){
		try {
	        $curl_handle = curl_init();
	        curl_setopt($curl_handle, CURLOPT_URL, $url);
	        curl_setopt($curl_handle, CURLOPT_RETURNTRANSFER, 1);
	        $fileInfo = curl_exec($curl_handle);
	        curl_close($curl_handle);
		} catch (Exception $e){
			error_log($e->getMessage()."at line number is:".$e->getLine()."in the ".$e->getFile(), 3, $this->logPath);
		}
        return $fileInfo;
    }
 
    /**
     * Post请求
     * @param string $url 请求地址， 如：http://host/path
     * @param array $data post请求需要发送的数据，关联数组形式
     */
    public function restPostRequest($url, $data){
        $o = "";
        foreach($data as $k=>$v){
            $o.="$k=".urlencode($v)."&";
        }
        $data = substr($o, 0, -1);
        try {
	        $curl_handle = curl_init();
	        curl_setopt($curl_handle, CURLOPT_URL, $url);
	        curl_setopt($curl_handle, CURLOPT_POST, 1);
	        curl_setopt($curl_handle, CURLOPT_HEADER, 0);
	        curl_setopt($curl_handle, CURLOPT_TIMEOUT, 30);
	        curl_setopt($curl_handle, CURLOPT_CONNECTTIMEOUT, 2);
	        curl_setopt($curl_handle, CURLOPT_RETURNTRANSFER, 1);
	        curl_setopt($curl_handle, CURLOPT_FAILONERROR, 1);
	        curl_setopt($curl_handle, CURLOPT_POSTFIELDS, $data);
	        $ret = curl_exec($curl_handle);
	        curl_close($curl_handle);
        } catch (Exception $e){
        	error_log($e->getMessage()."at line number is:".$e->getLine()."in the ".$e->getFile(), 3, $this->logPath);
        }
        return $ret;
    }

    public function downloadFile($remoteFilePath, $localFilePath){
        $curl_handle = curl_init();
        curl_setopt($curl_handle, CURLOPT_URL, $remoteFilePath);
		curl_setopt($curl_handle, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($curl_handle, CURLOPT_CONNECTTIMEOUT, 30);
		$contents = curl_exec($curl_handle);
		$cacheContent = $this->generateJsonFile($contents);
        $this->setContent($cacheContent, $localFilePath);
    }
    /**
     * 本地生成缓存文件
     * @param array $contentArray 内存hashmap数据
     */
    public function generateJsonFile($contents){
    	$jsonStr = "{\"fileContentMd5\":\"".$this->fileMd5."\",\"data\":".$contents."}";
    	return $jsonStr;
    }
    
    public function setContent($cacheArray, $localFilePath){
    	try{
	    	file_put_contents($localFilePath, $cacheArray);
	    	chmod($localFilePath, 0777);
    	}catch (Exception $e){
    		error_log($e->getMessage()."at line number is:".$e->getLine()."in the ".$e->getFile(), 3, $this->logPath);
    	}
    }
}

?>