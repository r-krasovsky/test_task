<?php
chdir('../../');
include_once 'vendor/autoload.php';

include_once 'init.php';
/**
 * Разбриаем URL запроса для роутинга
 */
$aExec = parseURLToExec($_SERVER["REQUEST_URI"]);

$sMethodName = (array_key_exists(3, $aExec)) ? $aExec[3] : "";
$iParam = (array_key_exists(5, $aExec)) ? (int) $aExec[5] : FALSE;

$oModelApi = new ModelApi(trim(param("key")), trim(param("type")));
$oModelApi->callMethod($sMethodName, $iParam);
