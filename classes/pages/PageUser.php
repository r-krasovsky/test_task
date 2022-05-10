<?php

class PageUser extends Page {
    
    public function defaultMethod($iUserId) {
        if($iUserId){
            $this->sTitle = "Профиль пользователя";
            $this->sSelfTpl = "PageUser.tpl";
            $iUserId = (int) $iUserId;
            $this->oUser = ModelUser::getUser($iUserId);
            if(!$this->oUser){
                $this->sTemplateError = "Пользователь не найден!";
                $this->sSelfTpl = "common/error.tpl";
                http_response_code(404);
            }
        }else{
            $this->list();
        }
    }
    
    public function list() {
        $this->sTitle = "Список пользователей";
        $this->sSelfTpl = "PageUser_List.tpl";
    }
    
    
    public function getUsersList() {
        $aResult = [];
        $aResult["draw"] = $_POST["draw"];
        $iRowsPerPage = (int) $_POST["length"];
        $iLimitStart = (int) $_POST["start"];
        
        $aResult["recordsTotal"] = R::count('user');
        $aResult["recordsFiltered"] = $aResult["recordsTotal"];
        
        
        $aResult["data"]  = ModelUser::getUsersList([$_POST["columns"][$_POST["order"][0]["column"]]["data"] => $_POST["order"][0]["dir"]], $iLimitStart, $iRowsPerPage); // R::getAll("SELECT id, `name`, email FROM user ORDER BY {$sOrder} LIMIT ?, ?", [ $iLimitStart, $iRowsPerPage]);
    
        response_to_ajax($aResult, FALSE);
    }
    
    public function photo($iUserId) {
        $irPhoto = ModelUser::getPhoto($iUserId);
        if($irPhoto){
            Header('Content-Type: image/jpeg');
            echo $irPhoto;
            die();
        }else{
            http_response_code(404);
            echo "User not found!";
            return FALSE;
        }
    }
}