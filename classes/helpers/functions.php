<?php

function parseURLToExec($sUrl){
    $aUrl = parse_url($sUrl);
    unset($_GET[mb_substr($aUrl["path"], 1)]);
    $aExec = [];
    preg_match('/\/([a-z]+)?\/?(\/([a-z]+))?\/?(\/((?=.*[a-z])(?=.*\d)[a-z\d]+|\d+))?\/?$/i', $aUrl["path"], $aExec);
    return $aExec;
}



/**
 * Возвращает значение GET или POST параметра
 *
 * @param string $sName Имя параметра
 *
 * @return string | bool Возвращает значение параметра или FALSE если параметр не найден
 * @global array $_POST
 * @global array $_GET
 *
 */
function param($sName) {
    global $_POST, $_GET;
    if (array_key_exists($sName, $_GET)) {
        return $_GET[$sName];
    }
    if (array_key_exists($sName, $_POST)) {
        return $_POST[$sName];
    }
    return FALSE;
}


/**
 * Задает HTTP заголовки
 *
 */
function http_headers_send() {
    header("Date:" . gmdate("D, d M Y H:i:s", DB_TIME) . " GMT");
    header("Last-Modified: " . gmdate("D, d M Y H:i:s", DB_TIME) . "GMT");
    header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");
    header("Pragma: no-cache");
    header("Cache-Control: no-store, no-cache, must-revalidate");
    header("Connection: close");
    header("Content-Type: text/html; charset=utf-8");
    header("Content-Language: ru");
    
}


/**
 * Выбрасывает в поток данные преобразованные в JSON формат
 *
 * @param object|int|array|string $mResponse          Входные данные
 * @param boolean                 $bJSON_FORCE_OBJECT Устанавливать ли флаг JSON_FORCE_OBJECT для метода json_encode()
 *
 * @global float                  $iStartTime         Время запуска скрипта в микросекундах
 * @global bool                   $bRuntimeDebug      Признак необходимости отладки выполнения проекта
 *
 */
function response_to_ajax($mResponse, $bJSON_FORCE_OBJECT = TRUE) {
    
    $sJSON = toJson($mResponse, $bJSON_FORCE_OBJECT);
    
    if ((boolean) $bJSON_FORCE_OBJECT === TRUE) {
        header('Content-type: application/json; charset=utf-8');
    } else if (is_string($mResponse)) {
        header('Content-type: text/html; charset=utf-8');
    }
    
    header("Content-Language: ru");
    header('Access-Control-Allow-Origin: *');
    
    if (NULL === $sJSON) {
        echo 'Returned data is null';
    } else {
        echo trim($sJSON);
    }
    die();
}

/**
 * Преобразовывает массиы и объекты в JSON
 * @param $mResponse
 * @param $bJSON_FORCE_OBJECT
 *
 * @return string
 */
function toJson($mResponse, $bJSON_FORCE_OBJECT = TRUE) {
    if (is_object($mResponse)) {
        $mResponse = get_object_vars($mResponse);
    } else if (NULL === $mResponse) {
        $mResponse = "null";
    }
    
    if ((boolean) $bJSON_FORCE_OBJECT === TRUE) {
        $mReturn = json_encode($mResponse, JSON_FORCE_OBJECT | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_UNESCAPED_UNICODE);
    } else if (is_string($mResponse)) {
        $mReturn = trim($mResponse);
    } else {
        $mReturn = json_encode($mResponse, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_UNESCAPED_UNICODE);
    }
    
    return $mReturn;
}

/**
 * Конвертирует массив в XML
 *
 * @param array $aArray
 * @param null  $sRootElementName
 * @param null  $xml
 *
 * @return false|mixed
 */
function arrayToXML($aArray = [], $sRootElementName = NULL, $xml = NULL) {
    $_xml = $xml;
    if ($_xml === NULL) {
        $_xml = new SimpleXMLElement($sRootElementName ?? '<root/>');
    }
    if(is_array($aArray)){
        $_xml->addChild("count", count($aArray));
    
        foreach ($aArray as $key => $value) {
            if (is_array($value)) {
                if( is_numeric($key) ){
                    $key = 'item_'.$key;
                }
                arrayToXML($value, $key, $_xml->addChild($key));
            }else{
                $_xml->addChild($key, $value);
            }
        }
    }
    
    return $_xml->asXML();
}


/**
 * Изменяет размер полученного изображения, сохраняя пропорции
 *
 * @param resource $irImage Входное изображение
 * @param int      $iWidth  Требуемая ширина
 * @param int      $iHeight Требуемая высота
 *
 * @return resource Измененное изображение с заданными шириной/высотой
 */
function ResizeImg($irImage, $iWidth, $iHeight) {
    $imWidth = imagesx($irImage);
    $imHeight = imagesy($irImage);
    $fRatioOrig = $imWidth / $imHeight;
    
    if (!($imWidth <= $iWidth && $imHeight <= $iHeight)) {
        
        if ($iWidth / $iHeight > $fRatioOrig) {
            $iWidth = floor($iHeight * $fRatioOrig);
        } else {
            $iHeight = floor($iWidth / $fRatioOrig);
        }
        
        $imgThumb = imagecreatetruecolor($iWidth, $iHeight);
        imagecopyresampled($imgThumb, $irImage, 0, 0, 0, 0, $iWidth, $iHeight, $imWidth, $imHeight);
        
        return $imgThumb;
    }
    return $irImage;
}