<?php

class PageRegister extends Page {
    
    protected $sConfirmCode;
    protected $bConfirmSuccess = FALSE;
    
    public function defaultMethod($iParam) {
        $this->sSelfTpl = "PageRegister.tpl";
        $this->sPrependTpl = "common/common_header.tpl";
        $this->sAppendTpl = "common/common_footer.tpl";
        
    }
    
    /**
     * Проверяем данные формы и создаем пользователя
     * @throws \RedBeanPHP\RedException\SQL
     */
    public function save() {
        if (self::$bIsAjaxForm) {
            //Проверяем введенную капчу
            $data = [
                'secret'   => "0x5AE2143e9A4Ce6D5cBDC24Fda8e1B84fB309b352",
                'response' => $_POST['h-captcha-response'],
            ];
            
            $verify = curl_init();
            curl_setopt($verify, CURLOPT_URL, "https://hcaptcha.com/siteverify");
            curl_setopt($verify, CURLOPT_POST, TRUE);
            curl_setopt($verify, CURLOPT_POSTFIELDS, http_build_query($data));
            curl_setopt($verify, CURLOPT_RETURNTRANSFER, TRUE);
            $response = curl_exec($verify);
            
            if (curl_errno($verify)) {
                $this->setFormError('Curl error: ' . curl_error($verify));
            } else {
                $responseData = json_decode($response, FALSE);
                //Если все хорошо, проверяем введенные данные
                if ($responseData->success) {
                    
                    $sEmail = trim(@$_POST["email"]);
                    if ($sEmail === "") {
                        $this->addErrorField("email", "Заполните поле");
                    } else if (!filter_var($sEmail, FILTER_VALIDATE_EMAIL)) {
                        $this->addErrorField("email", "Укажите корректный email адрес");
                    } else {
                        $aUser = ModelUser::getUserByEmail($sEmail);
                        if ($aUser) {
                            $this->addErrorField("email", "Пользователь с таким email уже существует");
                        }
                    }
                    
                    $sName = trim(@$_POST["name"]);
                    if ($sName === "") {
                        $this->addErrorField("name", "Заполните поле");
                    }
                    
                    
                    $sPassword = trim(@$_POST["passwd"]);
                    if ($sPassword === "") {
                        $this->addErrorField("passwd", "Заполните поле");
                    } else if (!preg_match('/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)[a-zA-Z\d]{8,}$/', $sPassword)) {
                        $this->addErrorField("passwd", "Пароль должен содержать минимум восемь симвлов, латиницу, в верхнем и нижнем регистре и цифры");
                    }
                    
                    if (!$this->hasFormErrors()) {
                        $sPasswordConfirm = trim(@$_POST["passwd_confirm"]);
                        if ($sPasswordConfirm === "") {
                            $this->addErrorField("passwd_confirm", "Заполните поле");
                        } else if ($sPasswordConfirm !== $sPassword) {
                            $this->addErrorField("passwd_confirm", "Повторите пароль правильно");
                        }
                    }
    
                    
                    if (!$this->hasFormErrors()) {
                        $sImgBinary = FALSE;
                        
                        if (isset($_FILES) && array_key_exists("photo", $_FILES ) && is_uploaded_file($_FILES["photo"]['tmp_name'])) {
                            
                            if (!function_exists("gd_info")) {
                                $this->setFormError("НЕТ библиотеки GD!");
                            } else {
                                $aPhotoFile = $_FILES["photo"];
                                $sTmpFileName = $aPhotoFile['tmp_name'];
                                $bFileError = 0;
                                //------------------ Обрабатываем ошибки полученного файла ------------
                                if ($aPhotoFile['error'] === 1) {
                                    $bFileError = TRUE;
                                }
                                
                                if (!$bFileError && $aPhotoFile['error'] === 3) {
                                    $bFileError = TRUE;
                                }
                                
                                //Проверяем размер загружаемого файла
                                if (!$bFileError && $aPhotoFile['size'] > ceil(CONFIG["max_upload_size"])) {
                                    $bFileError = TRUE;
                                    $this->addErrorField("photo","Файл фото слишком большой!");
                                }
                                
                                //Проверяем, что загружаемый файл является изображением
                                $fInfo = new finfo();
                                $sFileMIME = $fInfo->file($sTmpFileName, FILEINFO_MIME);
                                preg_match('/image\/(.+);/i', $sFileMIME, $aMatches);
                                if (!$bFileError && (!count($aMatches) || !array_key_exists(1, $aMatches))) {
                                    $this->addErrorField("photo","Загружаемый файл не является изображением!");
                                    $bFileError = TRUE;
                                }
                                
                                $iMinLengthLongSide = 100;
                                if (!$bFileError) {
                                    $aImageSize = getimagesize($sTmpFileName);
                                    if ($aImageSize[1] < $iMinLengthLongSide) {
                                        $this->addErrorField("photo","Загруженное изображение иммет высоту меньше {$iMinLengthLongSide}px! Найдите другое изображение.");
                                        $bFileError = TRUE;
                                    }
                                }
                                
                                $sImgSource = FALSE;
                                if (!$bFileError) {
                                    switch ($aMatches[1]) {
                                        case "gif" :
                                            $sImgSource = imagecreatefromgif($sTmpFileName);
                                            break;
                                        case "jpeg" :
                                        case "jpg" :
                                        case "pjpeg" :
                                            $sImgSource = imagecreatefromjpeg($sTmpFileName);
                                            break;
                                        case "png" :
                                            $sImgSource = imagecreatefrompng($sTmpFileName);
                                            break;
                                        default:
                                            $this->addErrorField("photo","Не определен тип загруженного изображения.");
                                    }
                                    
                                }
                                if ($sImgSource !== FALSE) {
                                    $sImgSource = ResizeImg($sImgSource, 500, 500);
                                    ob_start();
                                    imagejpeg($sImgSource, NULL, 95);
                                    $sImgBinary = ob_get_clean();
                                }
                            }
                        }else{
                            $this->addErrorField("photo", "Выберите изображение");
                        }
                        
                        if(!$this->hasFormErrors()){
                            $iUserId = ModelUser::createUser($sEmail, $sName, $sPassword, $sImgBinary);
                            ModelUser::sendRegConfirmMail($iUserId);
                            $this->setRedirectUrl(CONFIG["site_url"]."register/confirm");
                        }
                    }
                } else {
                    $this->setFormError("Пройдите каптчу, пожалуйста.");
                }
            }
        } else {
            header("Location: " . CONFIG["site_url"] . "register");
        }
    }
    
    
    /**
     * Подтверждение регистрации пользователя
     * @param $sConfirmCode
     *
     * @return void
     */
    public function confirm($sConfirmCode){
        $this->sSelfTpl = "PageRegister_Confirm.tpl";
        $this->sPrependTpl = "common/common_header.tpl";
        $this->sAppendTpl = "common/common_footer.tpl";
        
        $this->sConfirmCode = trim($sConfirmCode);
        if($sConfirmCode !== ""){
            $this->bConfirmSuccess = (bool)ModelUser::confirmRegistration($this->sConfirmCode);
        }
    }
    
}