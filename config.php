<?php
$sGlobalPath = __DIR__;
$bIsHTTPS = ( (array_key_exists("HTTPS", $_SERVER) && $_SERVER["HTTPS"] == "on") || ( array_key_exists("SERVER_PORT", $_SERVER) &&  $_SERVER["SERVER_PORT"] === 443 ));

define("CONFIG", [
    
    "site_url" =>(($bIsHTTPS)? "https://":"http://") .  "websmith.sytes.net/",
    
    "templates_path" => $sGlobalPath."/template",
    
    "session_time" => 3600*24,
    "session_table" => "sessions",
    
    "max_upload_size" => 1048576 * 50,
    
    "mysql_db_host" => "localhost",
    "mysql_db_name" => "test_task",
    "mysql_db_user" => "test_task_user",
    "mysql_db_password" => "testpass",
]);
