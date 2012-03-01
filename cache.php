<?php
require("conf.php");
require("rest.php");

class CacheObj{
    public $serviceName = "";
    public $tarLang = "";
    public $cacheDir = "";
    public $fileName = "";
    public $filePath = "";
    public $contentArray = array();
    public $restObj;
    public $fileContentMd5 = "";

    public function __construct($serviceName, $tarLang){
    	global $cacheSign;
        $this->serviceName = $serviceName;
        $this->tarLang = $tarLang;
        $this->cacheDir = ML_CACHE_DIR;
        $this->fileName = ML_CACHE_FILE_NAME;
        if($this->cacheDir == ""){
        	$this->filePath = $this->serviceName.$this->tarLang.$this->fileName;
        }elseif(PATH_SEPARATOR == ":"){
        	$this->filePath = $this->cacheDir."/".$this->serviceName.$this->tarLang.$this->fileName;
        }else{
            $this->filePath = $this->cacheDir."\\".$this->serviceName.$this->tarLang.$this->fileName;
        }
        if(ML_AUTO_UPDATE_FILE){
        	$this->restObj = new SDKRest($serviceName, $tarLang, $this->filePath);
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

    public function findString($words){
        if(@$this->contentArray[$words]){
            return $this->contentArray[$words];
        }else{
        	if(ML_AUTO_ADD_STRING){
            	$this->restObj->restAdd($words);
        	}
            return $words;
        }
    }
}
?>