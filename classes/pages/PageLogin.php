<?php

class PageLogin extends Page {
    
    public function defaultMethod($iParam = FALSE) {
        $this->sTitle = "Авторизация";
        $this->sSelfTpl = "PageLogin.tpl";
        
        $this->sPrependTpl = "common/common_header.tpl";
        $this->sAppendTpl = "common/common_footer.tpl";
        
        
    }
    
    public function authorise() {
        global $oSession;
        $oUser = ModelUser::checkUserAuthorise(@$_POST["login"], @$_POST["passwd"]);
        
        if (!$oUser) {
            $this->setFormError("Введён неправильный логин или пароль");
        } else if (!$oUser->confirmed) {
            $this->setFormError("Пользователь не подтвержден. Перейдите по ссылке из письма.");
        } else if (!$oSession->getAuthorized()) {//Авторизуем пользователя
            $oSession->create($oUser->id);
            $new_sid = $oSession->getSid();
            $oSession->setVar("login", $_POST["login"]);
            setcookie("sid", $new_sid, 0, "/");
            $oSession->write();
            $this->setRedirectUrl("/");
            
            // Удаляем коды подтверждения регистрации только после успешной авторизации
            R::exec('DELETE FROM confirm_codes WHERE uid = ?', [$oUser->id]);
        }
    }
}