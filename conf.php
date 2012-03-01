<?php
//The ML_CACHE_DIR is the dir of cache file stored. If it is "", 
//the cache file will store current dir.
if(PATH_SEPARATOR == ":"){
    define('ML_CACHE_DIR', ''); //"/var/www/php_sdk";
}else{
    define('ML_CACHE_DIR', ''); //D:\\";
}
//The ML_API_KEY is the only one key of your project accessed to ML server.
//The ML_CACHE_FILE_NAME is the cache file name.
define('ML_API_KEY', 'e08938e29e4ab7705cbc0524c93d206f');
define('ML_CACHE_FILE_NAME','defaultLang.php');

//The address which the user needn't changed any.
define('ML_REST_FILE_INFO', 'http://i.xingcloud.com/api/v1/file/info');
define('ML_REST_STRING_ADD', 'http://i.xingcloud.com/api/v1/string/add');

//Environment configuration. The two switch control the request the ML server. 
//For example, if ML_STRING_ADD_SWITCH is TRUE, it will add the new words to ML
//server. Otherwise, it won't add them to ML server, and so it won't generate any
//web request. 
define('ML_AUTO_ADD_STRING', FALSE);
define('ML_AUTO_UPDATE_FILE', FALSE);

?>