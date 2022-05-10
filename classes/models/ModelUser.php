<?php

class ModelUser {
    /**
     * Создаем нового пользователя
     *
     * @param $sEmail
     * @param $sName
     * @param $sPassword
     * @param $sImageBinary
     *
     * @return mixed
     */
    public static function createUser($sEmail, $sName, $sPassword, $sImageBinary) {
        $sKey = random_int(1, 999999) . $sEmail . "bla-bla-bla" . $sName . DB_TIME;
        R::exec("INSERT INTO user ( email, name, password, photo, `key`) VALUES (?, ?, SHA1(?), ?, SHA1(?))", [$sEmail, $sName, $sPassword, $sImageBinary, $sKey]);
        return R::getInsertID();
    }
    
    /**
     * Генерирует код подтверждения регистрации и отпправяет email
     * @param $iUserId
     *
     * @return void
     * @throws \RedBeanPHP\RedException\SQL
     */
    public static function sendRegConfirmMail($iUserId) {
        
        $aUser = self::getUser($iUserId);
        if($aUser){
    
            $sConfirmKey = sha1($aUser["email"] . "confirm" . $aUser["password"]. DB_TIME);
    
            $oConfirmCode = R::xdispense('confirm_codes');
            $oConfirmCode->uid = $iUserId;
            $oConfirmCode->code = $sConfirmKey;
            R::store($oConfirmCode);
    
            $to = $aUser["email"];
            $subject = 'Подтверждение регистрации';
    
            ob_start();
                include_once CONFIG["templates_path"] . "/PageRegister_ConfirmMail.tpl";
            $sMailTemplate = ob_get_clean();
            
            $headers = 'From: webmaster@example.com' . "\r\n";
            $headers .= 'Reply-To: webmaster@example.com' . "\r\n";
            $headers .= 'X-Mailer: PHP/' . PHP_VERSION;
            $headers .= 'MIME-Version: 1.0 ' . "\r\n";
            $headers .= 'Content-Type: text/html; charset=UTF-8' . "\r\n";
    
            mail($to, $subject, $sMailTemplate, $headers);
        }
    }
    
    /**
     * Проверяет код подтверждения регистрации пользователя и активирует учетку в случае успеха
     * @param $sConfirmCode
     *
     * @return array|null
     */
    public static function confirmRegistration($sConfirmCode) {
        $sConfirmCode = trim($sConfirmCode);
        $aUser = R::getRow('SELECT user.id, user.name FROM confirm_codes, user WHERE confirm_codes.code = ? AND user.id = confirm_codes.uid LIMIT 1;', [$sConfirmCode]);
        if ($aUser) {
            R::exec('UPDATE user SET confirmed = ? WHERE id = ? LIMIT 1;', [1, $aUser["id"]]);
        }
        return $aUser;
    }
    
    /**
     * Возвращает данные пользователя
     * @param $iUserId
     *
     * @return \RedBeanPHP\OODBBean|NULL
     */
    public static function getUser($iUserId) {
        return R::findOne("user", 'id = ?', [$iUserId]);
    }
    
    /**
     * Проверяет логин/пароль пользователя
     * @param $sLogin
     * @param $sPassword
     *
     * @return \RedBeanPHP\OODBBean|NULL
     */
    public static function checkUserAuthorise($sLogin, $sPassword) {
        
        return R::findOne("user", "email = :email AND password = SHA1(:password)", [
            ":email"    => $sLogin,
            ":password" => $sPassword,
        ]);
        
        
    }
    
    /**
     * Поиск пользователя по email
     * @param $sEmail
     *
     * @return \RedBeanPHP\OODBBean|NULL
     */
    public static function getUserByEmail($sEmail) {
        return R::findOne("user", 'email = ?', [$sEmail]);
    }
    
    /**
     * Поиск пользователя по его API ключу
     * @param $sUserKey
     *
     * @return \RedBeanPHP\OODBBean|NULL
     */
    public static function getUserByKey($sUserKey) {
        return R::findOne("user", '`key` = ?', [$sUserKey]);
    }
    
    /**
     * Возвращает список пользователей
     * @param $aOrderParams
     * @param $iLimitStart
     * @param $iRowsCount
     *
     * @return array|null
     */
    public static function getUsersList($aOrderParams = [], $iLimitStart = 0, $iRowsCount = 0) {
        $aFields = R::inspect('user');
        
        foreach ($aOrderParams as $sField => $sDestination) {
            if (!array_key_exists($sField, $aFields)) {
                unset($aOrderParams[$sField]);
            }
        }
        $sOrder = "";
        if (count($aOrderParams)) {
            $sOrder = "ORDER BY " . implode(", ", array_map(function($sField, $sDestination) {
                                                    return $sField . " " . $sDestination;
                                                }, array_keys($aOrderParams), array_values($aOrderParams))
                );
        }
        $iLimitStart = (int) $iLimitStart;
        $iRowsCount = (int) $iRowsCount;
        $sLimit = "";
        if ($iRowsCount) {
            $sLimit = " LIMIT ?, ?";
        }
        
        return R::getAll("SELECT id, `name`, email FROM user {$sOrder} {$sLimit}", [$iLimitStart, $iRowsCount]);
    }
    
    /**
     * Возвращает фото пользователя
     * @param $iUserId
     *
     * @return array|bool|mixed|null
     */
    public static function getPhoto($iUserId) {
        $oUser = ModelUser::getUser($iUserId);
        if ($oUser) {
            return $oUser->photo;
        } else {
            return FALSE;
        }
    }
}