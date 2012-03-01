<?php
require("cache.php");
class ML{
	public $tranSign = False;
    public function __construct($srcLang, $tarLang){
    	if($srcLang == $tarLang){
    		$this->tranSign = False;
    	}else{
    		$this->tranSign = True;
        	$this->cache = new CacheObj($tarLang);
    	}
    }

    public function trans($words){
        if(trim($words) and $this->tranSign){
            $content = $this->cache->findString($words);
            $content = str_replace("{", "", $content);
            $content = str_replace("}", "", $content);
            return $content;
        }else{
            return $words;
        }
    }
}

?>