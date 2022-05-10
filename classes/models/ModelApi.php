<?php

class ModelApi {
    
    private $iResponseCode = 200;
    private $sReturnType;
    
    public function __construct($sUserKey, $sReturnType) {
        if($sUserKey === "" || $sReturnType === ""){
            $this->iResponseCode = 401;
        }else{
            $aUser = ModelUser::getUserByKey($sUserKey);
            if(!$aUser){
                $this->iResponseCode = 401;
            }
        }
        
        if ($this->iResponseCode !== 200){
            $this->response();
        }
        
        $this->sReturnType = $sReturnType;
        
    }
    
    public function callMethod($sMethodName, $iParam = FALSE) {
        $mResponse = "";
        if (method_exists($this, $sMethodName)) {
            $mResult = $this->$sMethodName($iParam);
            if($mResult !== FALSE){
                switch ($this->sReturnType){
                    case "json":
                        $mResponse = toJson($mResult, FALSE);
                        header('Content-type: application/json; charset=utf-8');
                        header("Content-Language: ru");
                        header('Access-Control-Allow-Origin: *');
                        break;
                    case "xml":
                        $mResponse = arrayToXML($mResult);
                        header('Content-type: application/xml');
                        header("Content-Language: ru");
                        header('Access-Control-Allow-Origin: *');
                        break;
                    default:
                        $this->iResponseCode = 401;
                }
            }else{
                $this->iResponseCode = 404;
            }
            
        }else{
            $this->iResponseCode = 404;
        }
       
       $this->response($mResponse);
    }
    
    private function response($mResponseData = ""){
        echo $mResponseData;
        http_response_code($this->iResponseCode);
        die();
    }
    
    
    
    private function list() {
        return (array) ModelUser::getUsersList();
    }
    
    private function user($iUserId) {
        $aUser = ModelUser::getUser($iUserId);
        if ($aUser) {
            $aUser = $aUser->export();
            $aUser["photo"] = CONFIG["site_url"] . "api/photo/" . $aUser["id"];
            return $aUser;
        }
        return FALSE;
    }
    
    private function photo($iUserId) {
        $irPhoto = ModelUser::getPhoto($iUserId);
        if ($irPhoto) {
            Header('Content-Type: image/jpeg');
            echo $irPhoto;
            die();
        } else {
            return FALSE;
        }
    }
}