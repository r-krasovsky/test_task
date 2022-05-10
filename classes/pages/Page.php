<?php

abstract class Page {
    /**
     * @var bool Признак того, что запрос является данными формы переданными через AJAX
     */
    public static $bIsAjaxForm = FALSE;
    
    /**
     * Header template
     * @var string
     */
    protected $sPrependTpl = "";
    /**
     * Footer template
     * @var string
     */
    protected $sAppendTpl = "";
    /**
     * Page template
     * @var string
     */
    protected $sSelfTpl = "";
    /**
     * Заголовок страницы
     * @var string
     */
    protected $sTitle = "";
    /**
     * Текст сообщения для вывода в темплейт ошибки
     * @var string
     */
    protected $sTemplateError = "";
    
    /**
     * Массив возврщаемый после получения AJAX формы (результат валидации формы)
     * @var array
     */
    protected $aPostResult = [
        "error"          => FALSE,
        "error_fields"   => [], //Массив полей с ошибками и сообщений к ним
        "common_message" => [], //Общее сообщение ошибки формы
        "redirect_url"   => "", //URL перенаправления после успешной валидации формы и сохранения данных
        
    ];
    
    /**
     * Метод, вызываемый по умолчанию, если в URL не передан конкретный
     * @param $iParam //параметр передаваемый в URL
     *
     * @return mixed
     */
    abstract public function defaultMethod($iParam);
    
    
    /**
     * Подготовка, вызов метода и вывод ответа/страницы/JSON
     * @param $sMethodName
     * @param $iParam
     *
     * @return bool|void
     */
    public function callMethod($sMethodName = "", $iParam = FALSE){
        global $bIsAjax;
        
        
        self::$bIsAjaxForm = (array_key_exists("ajax_type", $_POST) && $_POST["ajax_type"] === "form");
    
        if(method_exists($this, $sMethodName)){
    
            ob_start();
            $mFuncResult = $this->$sMethodName($iParam);
            $sFunctionBuffer = trim(ob_get_clean());
            
            if (!count($_POST) && $mFuncResult !== FALSE) {
        
                http_headers_send();
                try {
                    $this->renderPage();
                }catch (Exception $e){
                    echo $e->getMessage();
                }
                
                return TRUE;
            } else if (self::$bIsAjaxForm) {
        
                if ($sFunctionBuffer !== '' && @$_POST["dataType"] === "json") {
                    $this->aPostResult["common_message"][] = "Неожиданный вывод выполнения метода: '{$sFunctionBuffer}'";
                    $this->aPostResult["error"] = TRUE;
                }
        
                $this->aPostResult["common_message"] = implode("<br/>", $this->aPostResult["common_message"]);
                response_to_ajax($this->aPostResult);
            } else if ($bIsAjax) {
                response_to_ajax($this->aPostResult);
            } else {
                echo $sFunctionBuffer;
            }
            
        }elseif ($sMethodName !== ""){
            $oPage404 = new Page404();
            $oPage404->callMethod();
        }else{
            $this->callMethod("defaultMethod", $iParam);//  defaultMethod($iParam);
        }
        
        
        
    }
    
    
    /**
     * Подготовка и вывод страницы
     * @return void
     * @throws Exception
     */
    protected function renderPage(){
        if ($this->sPrependTpl === '') {
            $this->sPrependTpl = "common/header.tpl";
        }
    
        if ($this->sAppendTpl === '') {
            $this->sAppendTpl  = "common/footer.tpl";
        }
    
        $sPrependContent = $this->renderTpl($this->sPrependTpl);
    
        if (trim($this->sSelfTpl) === '') {
            throw new Exception("У обьекта " . get_class($this) . " не указан файл шаблона. Укажите шаблон.");
            return FALSE;
        }else{
            $sMainContent = $this->renderTpl($this->sSelfTpl);
        }
       
        $sAppendContent = $this->renderTpl($this->sAppendTpl);
        echo $sPrependContent . $sMainContent . $sAppendContent;
        
    }
    
    /**
     * Отрисовка в буфер файла TPL
     * @param $sTplPath
     *
     * @return string
     */
    public function renderTpl($sTplPath){
        global $oCurrentUser;
        ob_start();
        if (file_exists(CONFIG["templates_path"] . "/" . $sTplPath)) {
            include_once CONFIG["templates_path"] . "/" . $sTplPath;
        }else {
            throw new InvalidArgumentException("TPL: " . CONFIG["templates_path"] . "/" . $sTplPath . " Not found!");
        }
        return ob_get_clean();
    }
    
    /**
     * Устанавливает сообщение ошибки поля формы
     * @param $sFieldName
     * @param $sMess
     *
     * @return void
     */
    public function addErrorField($sFieldName, $sMess) {
        $this->aPostResult["error_fields"][$sFieldName] = $sMess;
        $this->aPostResult["error"] = TRUE;
    }
    
    /**
     * Устанавливает URL перенаправления после успешной валидации формы
     * @param $sUrl
     *
     * @return void
     */
    protected function setRedirectUrl($sUrl) {
        $this->aPostResult["redirect_url"] = urldecode($sUrl);
    }
    
    /**
     * Устанавливает сообщение ошибки формы
     *
     * @param string $sMessage Текст сообщения
     */
    public function setFormError($sMessage) {
        $this->aPostResult["common_message"][] = $sMessage;
        $this->aPostResult["error"] = TRUE;
    }
    
    /**
     * Проверяет аличие ошибок валидации формы
     * @return bool
     */
    public function hasFormErrors() {
        return count($this->aPostResult["error_fields"]) || $this->aPostResult["error"];
    }
    
    function __destruct() {
        R::close();
    }
}