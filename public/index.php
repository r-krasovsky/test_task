<?php
chdir('../');
include_once 'vendor/autoload.php';

include_once 'init.php';
include_once 'init_session.php';

/**
 * Разбриаем URL запроса для роутинга
 */
$aExec = parseURLToExec($_SERVER["REQUEST_URI"]);

if (count($aExec)) {
    
    $sClassName = (array_key_exists(1, $aExec)) ? "Page" . ucfirst($aExec[1]) : "";
    $sMethodName = (array_key_exists(3, $aExec)) ? $aExec[3] : "";
    $iParam = (array_key_exists(5, $aExec)) ? $aExec[5] : FALSE;
    
    if ($sClassName === "PageLogout") {
        $oSession->destroy();
        header("Location: /");
        die();
    }
    
    /**
     * Если пользователь не авторизован то показываем ему страницу авторизации или регистрации
     */
    if (!$oCurrentUser) {
        
        if ($sClassName === "PageRegister") {
            $oPageRegister = new PageRegister();
            $oPageRegister->callMethod($sMethodName);
            
        }elseif ($sClassName === "PageConfirm"){
            $oPageRegister = new PageRegister();
            $oPageRegister->callMethod("confirm", $aExec[5]);
        }
        else {
            $oPageLogin = new PageLogin();
            if ($sClassName === get_class($oPageLogin) && method_exists($oPageLogin, $sMethodName)) {
                $oPageLogin->callMethod($sMethodName);
            } else if ($bIsAjax) {
                http_response_code(401);
                die();
            } else {
                $oPageLogin->callMethod();
            }
        }
        
    } else if (class_exists($sClassName)) {
        $oCalledClass = new $sClassName();
        $oCalledClass->callMethod($sMethodName, $iParam);
    } else if ($sClassName !== "") {
        $oPage404 = new Page404();
        $oPage404->callMethod();
        
    } else {//По умолчанию выводим страницу со списком пользователей
        $oPageUser = new PageUser();
        $oPageUser->callMethod();
    }
    
    
} else {
    $oPage404 = new Page404();
    $oPage404->callMethod();
}

