<?php
/**
 * ControllerToken
 * @version V 1.7
 * @author Alberto Vara (C) Copyright 2013
 * @package OLIF.ControllerToken
 *
 * DEPENDS/REQUIRED:
 * + controllers/ControllerApp
 */
namespace Olif;

require_once CORE_ROOT.CONTROLLERS.DIRECTORY_SEPARATOR."ControllerApp.php";

class ControllerToken extends ControllerApp {
    /**
     * Tiempo en minutos que dura el token
     *
     * @access private
     * @var integer
     */
    private $tokenTimeout = 5;

    /**
     * Stores the new token.
     *
     * @access private
     * @var string
     */
    private $tokenVar = null;

    /**
     * Stores the error code raised by Check method.
     *
     * The possible values are:
     * 0 No error detected.
     * 1 No Request Token detected.
     * 2 No Session Token corrisponding to the Request Token.
     * 3 No value for the Session Token.
     * 4 Token reached the timeout.
     * @access private
     * @var integer
     */
    private $tokenError = 0;
    public function __construct() {
        parent::__construct();
        if (!isset($_SESSION)) {
            //TODO arreglar esto para que no salte en PhpUnit tests
            //trigger_error("[ERROR ".get_class($this)."::".__FUNCTION__."::".__LINE__."] No se ha iniciado sesión para crear tokens", E_USER_NOTICE);
        }
    }
    /**
     * tokenGen
     * Genera un token de longitud definida en la variable
     *
     * @access public
     * @var $length Longitud del Token
     */

    public function tokenGen($length = 8) {
        $cstrong = "";
        if (function_exists('openssl_random_pseudo_bytes')) {
            $hash = base64_encode(openssl_random_pseudo_bytes(60, $cstrong));
        } else {
            $hash = sha1(uniqid(rand(), true));
        }
        // Hashes a randomized UniqId.

        // Selects a random number between 1 and 32 (40-8)
        $n = rand(1, 2);
        // Generate the token retrieving a part of the hash starting from the random N number with 8 of lenght
        $token = substr($hash, $n, $length);
        $token=preg_replace("/[^a-zA-Z0-9]/i", "", $token);
        $this->tokenVar=$token;
        return $token;
    }
    /**
     * Sets token.
     *
     * @access private
     */
    public function tokenSet($timeout = 5) {
        if (is_numeric($timeout))$this->tokenTimeout = $timeout;
        $this->tokenVar = $this->tokenGen();
    }
    /**
     * Get token.
     * @access public
     */
    public function tokenGet() {
        return $this->tokenVar;
    }
    /**
     * Checks if the request have the right token set; after that, destroy the old tokens. Returns true if the request is ok, otherwise returns false.
     * @deprecated
     */
    public function tokenCheck($token) {
        // Check if the token has been sent.
        //echo "1-ENTRA! ".$token."<br/>";
        //$this->showArray($_SESSION);
        if (isset($token)) {
            //echo "2-ENTRA! ".$token."<br/>";
            // Check if the token exists
            if (isset($_SESSION["OS_".$token])) {
                //echo "3-ENTRA! ".$token."<br/>";
                // Check if the token isn't empty
                if (isset($_SESSION["OS_".$token])) {
                    $age = time()-$_SESSION["OS_".$token];
                    //echo "CRADA: ".$_SESSION["OS_".$token]."<br>";
                    //echo "AHORA: ".time()."<br>";
                    //echo "AGE: ".$age."<br>";
                    //echo "AGE: ".($this->tokenTimeout*60*60)."<br>";
                    // Check if the token did not timeout
                    if ($age > $this->tokenTimeout*60*60) $this->tokenError = 4;
                } else $this->tokenError = 3;
            } else $this->tokenError = 2;
        } else $this->tokenError = 1;
        // Anyway, destroys the old token.
        $this->tokenDelAll();
        return $this->tokenError;
    }

    /**
     * Evaluar el error del tocken.
     */
    public function tokenEvalError($error, $url = "", $return = 'die', $alert = true) {
        if ($error==0) {
            return true;
        }
        $tokens_msg=array(
            1 =>    array(
                "NAME"  => 'ERROR_TOKEN_x00_OS_01',
                "MSG"   => 'No Request Token detected.'
            ),
            2 =>    array(
                "NAME"  => 'ERROR_TOKEN_x00_OS_02',
                "MSG"   => 'No Session Token corrisponding to the Request Token.'
            ),
            3 =>    array(
                "NAME"  => 'ERROR_TOKEN_x00_OS_03',
                "MSG"   => 'No value for the Session Token.'
            ),
            4 =>    array(
                "NAME"  => 'ERROR_TOKEN_x00_OS_04',
                "MSG"   => 'Token reached the timeout'
            ),
        );
        if ($alert===true) {
            $errorText="ERROR EN TOKEN<br><b>Mensaje: </b>".$tokens_msg[$error]['MSG']."<br>Vars in Session:".print_r($_SESSION, true)."";
            $this->sendError($errorText, $url, "ERROR TOKEN ".$tokens_msg[$error]['NAME']);

        }
        if ($return=='die') {
            die($tokens_msg[$error]['NAME']);
        }
    }
    /**
     * Destruye todos los tokens
     */
    public function tokenDelAll() {
        $sessvars = array_keys($_SESSION);
        $tokens = array();
        foreach ($sessvars as $var) if (substr_compare($var, "OS_", 0, 3)==0) $tokens[]=$var;
        unset ($tokens[array_search("OS_".$this->tokenVar, $tokens)]);
        foreach ($tokens as $token) unset($_SESSION[$token]);
    }
}
