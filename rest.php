<?php

class SDKRest{

    public $filePath = "";
    public $serviceName= "";
    public $tarLang = "";
    public $restFileGet = "";
    public $restStringAdd = "";
	public $md5FileName = "";
	public $cacheDir = "";
	public $apiKey = "";
	public $fileMd5 = "";
	
    public function __construct($serviceName, $tarLang, $filePath){
        global $address;
        $this->restFileGet = ML_REST_FILE_INFO;
        $this->restStringAdd = ML_REST_STRING_ADD;
        $this->md5FileName = ML_MD5_FILE_NAME;
        $this->cacheDir = ML_CACHE_DIR;
        $this->apiKey = ML_API_KEY;
        $this->serviceName= $serviceName;
        $this->tarLang = $tarLang;
        $this->filePath = $filePath;
        $this->fileMd5 = $fileContentMd5;
        $ret = $this->getFileInfo();
        if($ret){
	        $retArray = json_decode($ret, true);
	        if($this->updateFileContent($retArray["data"]["md5"])){
		        $remoteFilePath = $retArray["data"]["request_address"];
		        $this->downloadFile($remoteFilePath, $filePath);
	        }
        }
    }
    
    public function getFileInfo(){
    	$date = new DateTime();
		$timeStamp = $date->getTimestamp();
		$hash = md5($timeStamp.$this->apiKey);
    	$data = array("service_name"=>$this->serviceName, "lang"=>$this->tarLang, "file_path" => "xc_words.xml", "timestamp" => $timeStamp, "hash" => $hash);
    	return $this->restRequest($this->restFileGet, $data);
    }
    
    public function updateFileContent($md5Val){
    	if($this->fileMd5 == $md5Val){
    		return False;
    	}else{
    		$this->fileMd5 = $md5Val;
    		return True;
    	}
    }
    
	public function xml2phpString($xmlDoc){
        $phpStr = "<?php\r\n\$fileContentMd5=\"".$this->fileMd5."\";\r\n\$cacheArray=array();\r\n";
        $unitDom = $xmlDoc->getElementsByTagName("unit");
        if($unitDom->length == 0){
            return $retJson;
        }
        foreach($unitDom as $unit){
            $src = $unit->getElementsByTagName("source");
            $dst = $unit->getElementsByTagName("target");
            $phpStr = $phpStr."\$cacheArray[\"".$src->item(0)->nodeValue."\"]=\"".$dst->item(0)->nodeValue."\";\r\n";
        }
        return $phpStr."?>";
    }

    public function restAdd($words){
    	$date = new DateTime();
		$timeStamp = $date->getTimestamp();
		$hash = md5($timeStamp.$this->apiKey);
        $data = array("service_name"=>$this->serviceName, "data"=>$words, "timestamp" => $timeStamp, "hash" => $hash);
        $requestUrl = $this->restRequest($this->restStringAdd, $data);
        return;
    }
 
    public function restRequest($url, $data){
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
        $xmlDoc = new DOMDocument();
        $xmlDoc->load($remoteFilePath);
        $cacheArray = $this->xml2phpString($xmlDoc);
        $this->setContent($cacheArray, $localFilePath);
    }
    
    public function setContent($cacheArray, $localFilePath){
    	@file_put_contents($localFilePath, $cacheArray);
    	@chmod($localFilePath, 0777);
    }
}

?>