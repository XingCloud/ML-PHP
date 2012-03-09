<?php
define('ML_REST_HOST', 'http://i.xingcloud.com/api/v1/');
/**
 * 与多语言服务器交互模块。
 * @param string $serviceName 是在行云平台申请多语言服务的服务名称；
 * @param string $apiKey 行云平台的每个多语言服务都会有一个给定的apiKey，服务的唯一标识
 * @param string $tarLang 翻译结果对应语言的缩写
 * @param string $cacheDir 文件缓存路径
 *
 */
class RestWrapper{

    public $serviceName= "";
    public $tarLang = "";
    public $restFileSnapshot = "";
    public $restStringAdd = "";
	public $apiKey = "";
	public $logPath = "";
	public $snapPath = "";
	public $cacheDir = "";
	
    public function __construct($serviceName, $apiKey, $tarLang, $cacheDir){
        $this->restFileSnapshot = ML_REST_HOST."file/snapshot";
        $this->restStringAdd = ML_REST_HOST."string/add";
        $this->apiKey = $apiKey;
        $this->serviceName= $serviceName;
        $this->tarLang = $tarLang;
        $this->logPath = trim($cacheDir.DIRECTORY_SEPARATOR.$serviceName."_".$tarLang.".log", DIRECTORY_SEPARATOR);
        $this->snapPath = trim($cacheDir.DIRECTORY_SEPARATOR.$serviceName."_".$tarLang.".snap", DIRECTORY_SEPARATOR);
        $this->cacheDir = $cacheDir;
        $ret = $this->getFilesInfo();
        if($ret){
	        $snapContent = $ret;
	        $this->updateFiles($snapContent);
        }else{
        	error_log("The files list in your project is NULL", 3, $this->logPath);
        }
    }
    
    /**
     * 更新本地缓存文件
     * @param json $newSnapContent 该项目这个目标语言下所有的最新文件信息列表。
     * 
     */
    public function updateFiles($newSnapContent){
    	$newSnapArray = json_decode($newSnapContent, True);
    	$requestPrefix = $newSnapArray["request_prefix"];
    	$newSnapDataArray = $newSnapArray["data"];
    	if(file_exists($this->snapPath)){
    		$oldSnapDataArray = json_decode(file_get_contents($this->snapPath),True);
    		foreach($newSnapDataArray as $key => $val){
    			$localFilePath = trim($cacheDir.DIRECTORY_SEPARATOR.$this->serviceName."_".$this->tarLang."_".$key, DIRECTORY_SEPARATOR);
    			if(!file_exists($localFilePath) || (file_exists($localFilePath) && $val != $oldSnapDataArray[$key])){
    				$remoteFilePath = $requestPrefix."/".$key."?md5=".$val;
	    			$this->downloadFile($remoteFilePath, $localFilePath);
    			}
    		}
    	}else{
    		foreach ($newSnapDataArray as $key => $val){
    			$remoteFilePath = $requestPrefix."/".$key."?md5=".$val;
    			$localFilePath = trim($cacheDir.DIRECTORY_SEPARATOR.$this->serviceName."_".$this->tarLang."_".$key, DIRECTORY_SEPARATOR);
    			$this->downloadFile($remoteFilePath, $localFilePath);
    		}
    	}
    	$this->setContent(json_encode($newSnapDataArray), $this->snapPath);
    }
    
    /**
     * 从多语言服务器端获取本地缓存所对应文件的状态信息。
     * 返回值为：
     *  @return
     *  {
     * 		"locale": "en",
     *		"data": {
     * 		"temp_file.xml": "fa84044066314a3e7680966618fc4b02",
     * 		"xc_words.json": "99914b932bd37a50b983c5e7c90ae93b"
     *		},
     *		"request_prefix": "http://10.1.4.200/service_test944396/en/default"
  	 *	}
     */
    public function getFilesInfo(){
    	$timeStamp = ceil(time()*1000);
		$hash = md5($timeStamp.$this->apiKey);
    	$url = $this->restFileSnapshot."?service_name=".$this->serviceName."&locale=".$this->tarLang."&timestamp=".$timeStamp."&hash=".$hash;
    	return $this->restGetRequest($url);
    }
    
    /**
     * 将需要翻译的词条发送到多语言服务器上进行翻译，成功返回OK，失败返回FALSE
     * @param string $words 需要翻译的词条
     * @param string $fileName 词条存储的文件名
     */
    public function restAdd($words, $fileName){
		$timeStamp = ceil(time()*1000);
		$hash = md5($timeStamp.$this->apiKey);
        $data = array("service_name"=>$this->serviceName, "data"=>$words, "timestamp" => $timeStamp, "hash" => $hash, "file_path" => $fileName, "create" => 1);
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
	        $filesInfo = curl_exec($curl_handle);
	        curl_close($curl_handle);
		} catch (Exception $e){
			error_log($e->getMessage()."at line number is:".$e->getLine()."in the ".$e->getFile(), 3, $this->logPath);
		}
        return $filesInfo;
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
    	try {
	        $curl_handle = curl_init();
	        curl_setopt($curl_handle, CURLOPT_URL, $remoteFilePath);
			curl_setopt($curl_handle, CURLOPT_RETURNTRANSFER, 1);
			curl_setopt($curl_handle, CURLOPT_CONNECTTIMEOUT, 30);
			$contents = curl_exec($curl_handle);
	        $this->setContent($contents, $localFilePath);
    	}catch (Exception $e){
    		error_log($e->getMessage()."at line number is:".$e->getLine()."in the ".$e->getFile(), 3, $this->logPath);
    	}
    }
    
    public function setContent($cacheContent, $localFilePath){
    	try{
	    	file_put_contents($localFilePath, $cacheContent);
	    	chmod($localFilePath, 0777);
    	}catch (Exception $e){
    		error_log($e->getMessage()."at line number is:".$e->getLine()."in the ".$e->getFile(), 3, $this->logPath);
    	}
    }
}

?>