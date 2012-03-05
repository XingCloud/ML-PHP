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
class SDKRest{

    public $filePath = "";
    public $serviceName= "";
    public $tarLang = "";
    public $restFileInfo = "";
    public $restStringAdd = "";
	public $md5FileName = "";
	public $apiKey = "";
	public $fileMd5 = "";
	
    public function __construct($serviceName, $apiKey, $tarLang, $filePath){
        $this->restFileInfo = ML_REST_HOST."file/info";
        $this->restStringAdd = ML_REST_HOST."string/add";
        $this->apiKey = $apiKey;
        $this->serviceName= $serviceName;
        $this->tarLang = $tarLang;
        $this->filePath = $filePath;
        @$this->fileMd5 = $fileContentMd5;
        $ret = $this->getFileInfo();
        if($ret){
	        $retArray = json_decode($ret, true);
	        if($this->updateFileContent($retArray["data"]["md5"])){
		        $remoteFilePath = $retArray["data"]["request_address"];
		        $this->downloadFile($remoteFilePath, $filePath);
	        }
        }
    }
    
    /**
     * 从多语言服务器端获取本地缓存所对应文件的状态信息。
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
     * 将需要翻译的词条发送到多语言服务器上进行翻译
     * @param string $words 需要翻译的词条
     * 
     */
    public function restAdd($words){
		$timeStamp = ceil(time()*1000);
		$hash = md5($timeStamp.$this->apiKey);
        $data = array("service_name"=>$this->serviceName, "data"=>$words, "timestamp" => $timeStamp, "hash" => $hash);
        $requestUrl = $this->restPostRequest($this->restStringAdd, $data);
        return;
    }
    /**
     * Get请求
     * @param string $url Get请求地址，如：http://host/path?key1=val1&key2=val2...
     */
	public function restGetRequest($url){
        $curl_handle = curl_init();
        curl_setopt($curl_handle, CURLOPT_URL, $url);
        curl_setopt($curl_handle, CURLOPT_RETURNTRANSFER, 1);
        $fileInfo = curl_exec($curl_handle);
        curl_close($curl_handle);
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
        $curl_handle = curl_init();
        curl_setopt($curl_handle, CURLOPT_URL, $url);
        curl_setopt($curl_handle, CURLOPT_POST, 1);
        curl_setopt($curl_handle, CURLOPT_HEADER, 0);
        curl_setopt($curl_handle, CURLOPT_TIMEOUT, 30);
        curl_setopt($curl_handle, CURLOPT_CONNECTTIMEOUT, 2);
        curl_setopt($curl_handle, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($curl_handle, CURLOPT_FAILONERROR, 1);
        curl_setopt($curl_handle, CURLOPT_POSTFIELDS, $data);
        $filepath = curl_exec($curl_handle);
        curl_close($curl_handle);
        return $filepath;
    }

    public function downloadFile($remoteFilePath, $localFilePath){
        $curl_handle = curl_init();
        curl_setopt($curl_handle, CURLOPT_URL, $remoteFilePath);
		curl_setopt($curl_handle, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($curl_handle, CURLOPT_CONNECTTIMEOUT, 30);
		$contents = curl_exec($curl_handle);
		if(strlen($contents) == 2){
			$cacheArray = array();
		}else{
        	$cacheArray = json_decode($contents);
		}
		$cacheContent = $this->generatePHPFile($cacheArray);
        $this->setContent($cacheContent, $localFilePath);
    }
    /**
     * 本地生成缓存文件
     * @param array $contentArray 内存hashmap数据
     */
    public function generatePHPFile($contentArray){
    	$phpStr = "<?php\r\n\$fileContentMd5=\"".$this->fileMd5."\";\r\n\$cacheArray=array();\r\n";
    	foreach ($contentArray as $key => $val){
    		$phpStr = $phpStr."\$cacheArray[\"".$key."\"]=\"".$val."\";\r\n";
    	}
    	return $phpStr."?>";
    }
    
    public function setContent($cacheArray, $localFilePath){
    	@file_put_contents($localFilePath, $cacheArray);
    	@chmod($localFilePath, 0777);
    }
}

?>