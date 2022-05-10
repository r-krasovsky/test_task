<?php

class Session {
    /**
     * @var mixed|string Имя таблицы сессий в БД
     */
    private $sTableName = "sessions";
    private $iExpire;
    private $iInternalExpire = 7200;
    private $sSecret = 'bla-bla-bla';
    private $sSid = "";
    private $_SESS_VARS = [];
    private $bAuthorised = 0;
    private $iUserId = 0;
    private $iWritesCount = 0;
    private $sClientId;
    
    /**
     * Конструктор класса
     *
     * @param string $sSid          Сессионный ключ пользователя
     * @param string $sClientId     Имя области хранения переменных клиента
     * @param bool   $bRenewSession Продлевать ли сессию
     *
     */
    public function __construct($sSid, $sClientId = 'global', $bRenewSession = TRUE, $iSessionTime = 0, $sTableName = "") {
        $this->iExpire = ((int) $iSessionTime > 0) ? $iSessionTime : $this->iInternalExpire;
        $this->sTableName = (trim($sTableName) !== "") ? $sTableName : $this->sTableName;
        
        $this->killExpired();
        $this->sClientId = $sClientId;
        
        if ($this->sClientId === '') {
            echo "Error: Bad client ID in class Sessions.";
        } else if (preg_match("/^[a-z0-9]+$/i", $sSid) && (strlen($sSid) === 32)) { //Проверка ключа на валидность
            $oSessionData = R::findOne($this->sTableName, "session_id = ?", [$sSid]);
            
            if ($oSessionData && $oSessionData->fingerprint === $this->genFingerprint($sSid)) {
                
                $this->iUserId = (int) $oSessionData->user_id;
                $this->bAuthorised = TRUE;
                
                $tmp_var = json_decode($oSessionData->data, TRUE);
                if (!isset($tmp_var[$this->sClientId])) {
                    $tmp_var[$this->sClientId] = [];
                }
                
                $this->_SESS_VARS = $tmp_var[$this->sClientId];
                unset($tmp_var);
                
                $this->sSid = strtolower($oSessionData->session_id);
                if ($bRenewSession) {
                    $oSessionData->expire = DB_TIME + $this->iExpire;
                    R::store($oSessionData);
                }
            }
            
        } else {
            // if (strlen($sSid) <> 32) {
            //     // Write error to log
            // } else if (!preg_match("/^[a-z0-9]+$/i", $sSid)) {
            //     // Write error to log
            // }
        }
    }
    
    public function create($iUserId) {
        $iUserId = (int) $iUserId;
        if (!$this->bAuthorised && $iUserId) {
            $this->sSid = $this->genSid();
            
            $tmp_var = [$this->sClientId => []];
            
            R::exec("INSERT INTO {$this->sTableName} (session_id, user_id, `data`, expire, ip, user_agent, fingerprint) VALUES (?,?,?,?,?,?,?)",
                           [
                               $this->sSid,
                               $iUserId,
                               json_encode($tmp_var),
                               DB_TIME + $this->iExpire,
                               $_SERVER['REMOTE_ADDR'],
                               $_SERVER['HTTP_USER_AGENT'],
                               $this->genFingerprint($this->sSid),
                           ]
            );
            R::getInsertID();
            
            $this->bAuthorised = 1;
        }
    }
    
    public function getAuthorized() {
        return $this->bAuthorised;
    }
    
    public function getUserId() {
        return $this->iUserId;
    }
    
    private function genSid() {
        global $_SERVER;
        return md5(random_int(1, 99999999) . $this->sSecret . $_SERVER['REMOTE_ADDR'] . $_SERVER['HTTP_USER_AGENT'] . DB_TIME);
    }
    
    private function genFingerprint($sid) {
        global $_SERVER;
        return md5($this->sSecret . $_SERVER['REMOTE_ADDR'] . $_SERVER['HTTP_USER_AGENT'] . $sid);
    }
    
    public function getSid() {
        return $this->sSid;
    }
    
    public function setVar($sName, $sValue) {
        if ($this->bAuthorised && $sName !== '') {
            $this->_SESS_VARS[$sName] = $sValue;
            $this->iWritesCount++;
            return TRUE;
        }
        
        return FALSE;
    }
    
    public function getVar($sName) {
        if ($this->bAuthorised && $sName !== '' && isset($this->_SESS_VARS[$sName])) {
            return $this->_SESS_VARS[$sName];
        }
        return FALSE;
    }
    
    public function unsetVar($sName) {
        if ($this->bAuthorised && $sName !== '' && isset($this->_SESS_VARS[$sName])) {
            $this->iWritesCount++;
            unset($this->_SESS_VARS[$sName]);
            return TRUE;
        }
        return FALSE;
    }
    
    public function clearVars() {
        if ($this->bAuthorised && is_array($this->_SESS_VARS)) {
            $this->iWritesCount++;
            $this->_SESS_VARS = [];
            return TRUE;
        }
        return FALSE;
    }
    
    public function write() {
        if ($this->bAuthorised && $this->iWritesCount) {
            $this->iWritesCount = 0;
            
            $oSessionData = R::findOne($this->sTableName, "session_id = ?", [$this->sSid]);
            
            $tmp_var = json_decode($oSessionData->data, FALSE);
            
            if (!is_array($tmp_var)) {
                $tmp_var = [];
            }
            $tmp_var[$this->sClientId] = $this->_SESS_VARS;
            
            $oSessionData->data = json_encode($tmp_var);
            R::store($oSessionData);
        }
        return FALSE;
    }
    
    private function killExpired() {
        $aSessions = R::find('sessions', 'expire < UNIX_TIMESTAMP()');
        R::trashAll($aSessions);
    }
    
    public function destroy() {
        if ($this->bAuthorised) {
            $this->bAuthorised = 0;
            $this->iUserId = 0;
            return R::hunt($this->sTableName, "session_id = ?", [$this->sSid]);
        }
        return FALSE;
    }
    
    public function __destruct() {
        $this->write();
    }
}

?>