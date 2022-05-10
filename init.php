<?php
/**
 * Признак того, что запрос отправлен с помощью AJAX
 */
$bIsAjax = ((isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') || (isset($_POST["query_type"]) && $_POST["query_type"] == "ajax")) ? TRUE : FALSE;
include_once 'config.php';

$aInitErrors = [];

if (!function_exists("curl_init")) {
    $aInitErrors[] = "Библиотека curl не подключена";
}

if (!extension_loaded('pdo_mysql')) {
    $aInitErrors[] = "Библиотека pdo_mysql не подключена";
}
if(!function_exists("gd_info")){
    $aInitErrors[] = "Библиотека GD не подключена";
}


if(count($aInitErrors)){
    foreach ($aInitErrors as $sMessage){
        echo $sMessage."<br>";
    }
    die();
}

/*========================== AUTOLOAD ============================================================*/

$aAutoloadFolders = [
    "/classes",
    "/classes/models",
    "/classes/pages",
    "/classes/helpers",
];

foreach ($aAutoloadFolders as $sPath) {
    set_include_path(get_include_path() . PATH_SEPARATOR . __DIR__ . $sPath);
}

spl_autoload_register(function($class) {
    
    if (stream_resolve_include_path($class . '.php') !== FALSE) {
        include_once $class . '.php';
    }
});


spl_autoload_extensions('.php');

include_once __DIR__ . "/classes/helpers/functions.php";
/*========================== AUTOLOAD END ============================================================*/

// Создаём псевдоним для указанного класса
class_alias('\RedBeanPHP\R', '\R');

R::setup("mysql:host=" . CONFIG["mysql_db_host"] . ";dbname=" . CONFIG["mysql_db_name"], CONFIG["mysql_db_user"], CONFIG["mysql_db_password"], FALSE);

// Проверка подключения к БД
if (!R::testConnection()) {
    die('No DB connection!');
}

R::ext('xdispense', function($type) {
    return R::getRedBean()->dispense($type);
});

define("DB_TIME", R::getRow("SELECT UNIX_TIMESTAMP() as dbtime")["dbtime"]);
