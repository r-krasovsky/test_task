<?php

$sSid = (array_key_exists("sid", $_COOKIE))? $_COOKIE['sid'] : "null";

if (!(isset($sSid) && preg_match("/^[a-z0-9]+$/i", $sSid) && (strlen($sSid) == 32))){
    $sSid = "";
}

//Пытаемся авторизовать пользователя
$oSession = new Session($sSid, "fw_dev", !$bIsAjax, CONFIG['session_time'], CONFIG['session_table']);

if (!$oSession->getAuthorized()) {
    $oCurrentUser = FALSE;
} else {
    $oCurrentUser = ModelUser::getUser($oSession->getUserId());
    setcookie("sid", $sSid, 0, "/");
}