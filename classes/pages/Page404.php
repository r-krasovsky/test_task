<?php

class Page404 extends Page {
    protected $sTitle = "404 Страница не найдена";
    public function defaultMethod($iParam = FALSE) {
        global $oCurrentUser;
        if(!$oCurrentUser){
            $this->sPrependTpl = "common/common_header.tpl";
            $this->sAppendTpl = "common/common_footer.tpl";
        }
        
        
        $this->sSelfTpl = "Page404.tpl";
    }
}