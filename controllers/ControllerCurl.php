<?php
/**
 * ControllerCurl
 * @version V 1.7
 * @author Alberto Vara (C) Copyright 2013
 * @package OLIF.ControllerCurl
 *
 * DEPENDS/REQUIRED:
 * + controllers/ControllerApp
 * + models/modelPage
 */
namespace Olif;

require_once CORE_ROOT.CONTROLLERS.DIRECTORY_SEPARATOR."ControllerApp.php";

class ControllerCurl extends ControllerApp {
    /**
     * $ch
     * Variable donde se inicialica Curl para llamadas internas
     */
    private $ch = null;
    /**
     * $tokenSend
     * token que se envía al script
     */
    private $tokenSend;
    /**
     * $che
     * Variable donde se inicialica Curl para llamadas externas
     */
    private $che = null;

    public $file = "";

    private $timeOut = 50;

    protected $actions = array('li', 'lo', 'lio', 'api');
    /**
     * $dataPost
     * Variables por defecto que se mandan por post a la llamada RIA para saber de donde se ha llamado
     */
    protected $dataPost = array(
            'OLIF_ROOT' => OLIF_ROOT,
            'NODE_ROOT' => NODE_ROOT,
            'DIR_INDEX' => WEBSITE_ROOT
            );

    public $url = "";

    public function __construct() {
        parent::__construct();
        $this->getControllerAccess();
    }
    private function initCurl() {
        if (function_exists('curl_init')) {
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_PORT, 80);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $this->timeOut);
            $this->ch = $ch;
        } else {
            $this->sendError(" No se encuentra la función curl_init");
        }
    }
    public function assignRIA($ria_script, $mode, $token) {
        if (!$this->checkMode($mode)) {
            $this->rise404('['.$ria_script.'] Accediendo a una página RIA sin modo. MODE: '.$mode.". TOKEN: ".$token);
        }
        if ($mode!='api') {
            if ($mode=='li' && !$this->access->isLogin()) {
                $this->rise401('['.$ria_script.'] Accediendo a una página RIA sin permisos por LI. MODE: '.$mode.". TOKEN: ".$token);
            }

            if (!$this->access->checkSessionToken($token, false)) {
                $this->rise401('['.$ria_script.'] Accediendo a una página RIA sin permisos por token. MODE: '.$mode.". TOKEN: ".$token." NAME ".$this->access->sessionName." SESSION ".$this->access->getSessionToken()." SESSION ".print_r($_SESSION, true));
            }
        }
        $this->tokenSend=$token;

        $this->file=$ria_script;
    }
    public function assignVars($vars) {
        foreach ($vars as $key => $value) {
            $this->assignVar($key, $value);
        }
    }
    public function assignVar($key, $value) {
        $this->dataPost[$key]=$value;
    }
    public function getRIA() {
        $this->initCurl();
        unset($this->dataPost['mode']);
        unset($this->dataPost['action']);
        unset($this->dataPost['pag']);
        $this->dataPost['StokenOlif']=$this->tokenSend;
        $this->dataPost['REMOTE_ADDR']=$_SERVER['REMOTE_ADDR'];
        $this->dataPost['REMOTE_PORT']=$_SERVER['REMOTE_PORT'];
        if (DOMAIN=='dev.gobalo.es') {
            $proxy = "127.0.0.1:80";
            $proxy = explode(':', $proxy);
            curl_setopt($this->ch, CURLOPT_PROXY, $proxy[0]);
            curl_setopt($this->ch, CURLOPT_PROXYPORT, $proxy[1]);
        }
        if (defined("CURL_SSLCERT") && CURL_SSLCERT==1) {
            curl_setopt($this->ch, CURLOPT_PORT, 443);
            curl_setopt($this->ch, CURLOPT_SSL_VERIFYHOST, 2);
            curl_setopt($this->ch, CURLOPT_SSL_VERIFYPEER, true);
            curl_setopt($this->ch, CURLOPT_SSLCERT, CURL_SSLCERT_PATH);
            curl_setopt($this->ch, CURLOPT_CAINFO, CURLOPT_CAINFO_PATH);
            curl_setopt($this->ch, CURLOPT_SSLKEY, CURL_SSLKEY_PATH);
        }
        $url=PROTOCOL.WEB_URL_NO_PROTOCOL.NODE_ROOT.RIA_FOLDER.$this->file.".php";
        if (defined("NEED_CURL_AUTHORIZATION") && NEED_CURL_AUTHORIZATION==1) {
                $headers = array(    "Authorization: Basic " . base64_encode(sprintf('%s:%s', NEED_CURL_AUTHORIZATION_USER, NEED_CURL_AUTHORIZATION_PASS))    );
                curl_setopt($this->ch, CURLOPT_HTTPHEADER, $headers);
        }
        $strCookie = 'PHPSESSID=' . session_id() . '; domain=' . WEB_URL_NO_PROTOCOL . ';path=/';
        session_write_close();
        curl_setopt($this->ch, CURLOPT_URL, $url);
        curl_setopt($this->ch, CURLOPT_COOKIE, $strCookie);
        curl_setopt($this->ch, CURLOPT_POSTFIELDS, http_build_query($this->dataPost));
        curl_setopt($this->ch, CURLOPT_USERAGENT, 'OLIF FM System');
        $data = curl_exec($this->ch);
        if (curl_errno($this->ch)) {
                echo "[ERROR ".get_class($this)."::".__FUNCTION__."::".__LINE__."] " . curl_errno($this->ch) . ": " . curl_error($this->ch);
        }
        if ($data == 'ERROR') {
                $this->rise500();
        } else {
                echo $data;
        }
        $this->closeRIACon();
    }
    public function closeRIACon() {
        curl_close($this->ch);
    }
    public function checkMode($mode) {
        return (in_array($mode, $this->actions))?true:false;
    }
    /* CURL FUERA DE SISTEMA */
    public function initEcurl() {
        if (function_exists('curl_init')) {
            $che = curl_init();
            curl_setopt($che, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($che, CURLOPT_POST, 1);
            curl_setopt($che, CURLOPT_CONNECTTIMEOUT, $this->timeOut);
            $this->che = $che;
        } else {
            $this->sendError(" No se encuentra la función curl_init");
        }
    }
    public function setESSL() {
        curl_setopt($this->che, CURLOPT_SSL_VERIFYPEER, true);
        curl_setopt($this->che, CURLOPT_SSL_VERIFYHOST, true);
    }
    public function setEPort($PORT = 80) {
        curl_setopt($this->che, CURLOPT_PORT, $PORT);
    }
    public function getURL($url, $return = false) {
        unset($this->dataPost['OLIF_ROOT']);
        unset($this->dataPost['NODE_ROOT']);
        unset($this->dataPost['DIR_INDEX']);
        curl_setopt($this->che, CURLOPT_URL, $url);
        curl_setopt($this->che, CURLOPT_POSTFIELDS, http_build_query($this->dataPost));
        $data = curl_exec($this->che);
        if ($data == 'ERROR') {
            $this->rise500();
        } else {
            if (curl_errno($this->che)) {
                $this->sendError("[ERROR ".get_class($this)."::".__FUNCTION__."::".__LINE__."] " . curl_errno($this->che) . ": " . curl_error($this->che));
                curl_close($this->che);
            } else {
                curl_close($this->che);
                if ($return) {
                    return $data;
                } else {
                    echo $data;
                }
            }
        }
    }
}
