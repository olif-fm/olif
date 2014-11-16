<?php
/**
 * ControllerAccess
 * @version V 1.7
 * @copyright Alberto Vara (C) Copyright 2013
 * @package OLIF.ControllerAccess
 *
 * Controlador de accesos de usuario, si está conectado, si el token es válido....
 * DEPENDS/REQUIRED:
 * + controllers/ControllerApp
 * + models/modelAccess
 */
namespace Olif;

require_once CORE_ROOT . CONTROLLERS . DIRECTORY_SEPARATOR . "ControllerApp.php";

class ControllerAccess extends ControllerApp
{

    /**
     * $model
     * Objeto de modelAccess
     */
    public $model;

    /**
     * $action_login
     * Posibles acciones del sistema
     *
     * @var array
     */
    public $actions_login = array(
        'U' => 'USER',
        'UP' => 'USER_PASS',
        'P' => 'PASS',
        'OI' => 'OPEN_ID'
    );

    /**
     * $action_login
     * Variable que guarda la accion a realizar
     *
     * @var string
     */
    public $action_login = '';

    /**
     * $AG_client
     * Objeto donde se instancia la clase de Google_Client
     */
    public $AG_client = "";

    /**
     * $AG_service
     * Objeto donde se instancia la clase de Google_DriveService
     */
    public $AG_service = "";

    /**
     * $sessionID
     * Nombre de la variable de sesión que guardará la ID de usuario
     */
    public $sessionID = "";

    /**
     * $sessionSec
     * Nombre de la variable de sesión que guardará el Nivel de seguridad del usuario
     */
    private $sessionSec = "";

    /**
     * $sessionLastAccess
     * Nombre de la variable de sesión que guardará el último acceso de usuario
     */
    public $sessionLastAccess = "";

    /**
     * $defaultSec
     * Nombre de la variable de sesión que guardará el Nivel de seguridad del usuario
     */
    public $defaultSec = "";

    /**
     * $sessionName
     * Nombre de la variable de sesión que guardará el Nivel de seguridad del usuario
     */
    public $sessionName = "";

    /**
     * $sessionSec
     * Nombre de la variable de sesión que guardará el Nivel de seguridad del usuario
     */
    public $sessionToken = "";

    /**
     * $isCmsGeneric
     * Si es el CMS, hay variables preconfiguradas
     */
    public $isCmsGeneric = false;

    public function __construct()
    {
        parent::__construct();
        $this->getModel();
        $this->model->getModelAccess();
        $this->getControllerSession();
    }
    /* Session ID */
    /**
     * setSessionID
     * Asigna una variable para la ID de sesión definida con setSessionIDName
     *
     * @param
     *            value string <p>
     *            Valor a asignar
     *            </p>
     * @access public
     */
    public function setSessionID($value)
    {
        $this->session->set($this->sessionID, $value);
    }

    /**
     * setSessionIDName
     * Define el nombre de la variable de sesión para el ID
     *
     * @param
     *            name string <p>
     *            Nombre a asignar
     *            </p>
     * @access public
     */
    public function setSessionIDName($name)
    {
        if (strlen($name) > 0) {
            $this->sessionID = $name;
        }
    }

    /**
     * getSessionID
     * Devuelve la variable de la ID de sesión definida con setSessionID
     *
     * @access public
     */
    public function getSessionID()
    {
        if (strlen($this->sessionID) > 0) {
            return $this->session->get($this->sessionID);
        }
    }
    /* Session SECURITY */
    /**
     * setSessionSec
     * Asigna una variable para el nivel de seguridad de la sesión definida con setSessionSecName
     *
     * @param
     *            value string <p>
     *            Valor a asignar
     *            </p>
     * @access public
     */
    public function setSessionSec($value)
    {
        // echo "SET SESSION SEC: ".$value."<br>";
        $this->session->set($this->sessionSec, $value);
    }

    /**
     * setSessionIDName
     * Define el nombre de la variable de sesión para el nivel de seguridad
     *
     * @param
     *            name string <p>
     *            Nombre a asignar
     *            </p>
     * @access public
     */
    public function setSessionSecName($name)
    {
        if (strlen($name) > 0) {
            $this->sessionSec = $name;
            // echo "1SESSION SEC NAME".$name.". CLASS: ".debug_backtrace()."<br>";
        }
        // echo "2SESSION SEC NAME".$name.". CLASS: <pre>".print_r(debug_backtrace(),true)."</pre><br>";
    }

    /**
     * getSessionSec
     * Devuelve la variable de el nivel de seguridad de sesión definida con setSessionSec
     *
     * @access public
     */
    public function getSessionSec()
    {
        // echo "SESSION SEC".$this->sessionSec." CLASS: <pre>".print_r(debug_backtrace(),true)."</pre><br>";
        if (strlen($this->sessionSec) > 0) {
            return $this->session->get($this->sessionSec);
        }
    }
    /* Session TOKEN */
    /**
     * setSessionToken
     * Asigna el valor al token de sesión
     *
     * @param
     *            value string <p>
     *            Valor a asignar
     *            </p>
     * @access public
     */
    public function setSessionToken($value)
    {
        if (strlen($this->sessionToken) > 0) {
            $this->session->set($this->sessionToken, $value);
        }
    }

    /**
     * setSessionTokenName
     * Define el nombre de la variable de sesión para el token
     *
     * @param
     *            name string <p>
     *            Nombre a asignar
     *            </p>
     * @access public
     */
    public function setSessionTokenName($name)
    {
        if (strlen($name) > 0) {
            $this->sessionToken = $name;
        }
    }

    /**
     * getSessionToken
     * Devuelve el token de la sessión
     *
     * @access public
     */
    public function getSessionToken()
    {
        if (strlen($this->sessionToken) > 0) {
            return $this->session->get($this->sessionToken);
        }
    }
    /* Session NAME */
    /**
     * setSessionToken
     * Asigna el valor al nombre de usuario
     *
     * @param
     *            value string <p>
     *            Valor a asignar
     *            </p>
     * @access public
     */
    public function setSessionName($value)
    {
        $this->session->set($this->sessionName, $value);
    }

    /**
     * setSessionTokenName
     * Define el nombre de la variable de sesión para el nombre de usuario
     *
     * @param
     *            name string <p>
     *            Nombre a asignar
     *            </p>
     * @access public
     */
    public function setSessionNameName($name)
    {
        $this->sessionName = $name;
    }

    /**
     * getSessionToken
     * Devuelve el nombre del usuario conectado
     *
     * @access public
     */
    public function getSessionName()
    {
        if (strlen($this->sessionName) > 0) {
            return $this->session->get($this->sessionName);
        }
    }

    /**
     * setSessionLastAccess
     * Asigna una variable para la última sesión de sesión definida con setSessionIDName
     *
     * @param
     *            value string <p>
     *            Valor a asignar
     *            </p>
     * @access public
     */
    public function setSessionLastAccess($value)
    {
        $this->session->set($this->sessionLastAccess, $value);
    }

    /**
     * setSessionIDName
     * Define el nombre de la variable de sesión para el ID
     *
     * @param
     *            name string <p>
     *            Nombre a asignar
     *            </p>
     * @access public
     */
    public function setSessionLastAccessName($name)
    {
        if (strlen($name) > 0) {
            $this->sessionLastAccess = $name;
        }
    }

    /**
     * getSessionID
     * Devuelve la variable de la ID de sesión definida con setSessionID
     *
     * @access public
     */
    public function getSessionLastAccess()
    {
        if (strlen($this->sessionLastAccess) > 0) {
            return $this->session->get($this->sessionLastAccess);
        }
    }
    /* DEFINICION DEL MODELO: Campos que se usarán par atacar a la BBDD */
    /**
     * setFieldUserNameShow
     * Asigna el nombre del campo que se sacará el nombre de usuario de la sesión de la BBDD
     *
     * @param
     *            field string <p>
     *            Nombre del campo
     *            </p>
     * @access public
     */
    public function setFieldUserNameShow($field)
    {
        $this->model->access->setFieldUserNameShow($field);
    }

    /**
     * setFieldUser
     * Asigna el nombre del campo que se sacará el nickname/identificador/email de usuario de la sesión de la BBDD.
     * Suele ser el campo que se introduce en el form de acceso
     *
     * @param
     *            field string <p>
     *            Nombre del campo
     *            </p>
     * @access public
     */
    public function setFieldUser($field)
    {
        $this->model->access->setFieldUser($field);
    }

    /**
     * setFieldUserOpenId
     * Asigna el nombre del campo que se sacará el email de usuario de la sesión si es por OpenID de la BBDD.
     *
     * @param
     *            field string <p>
     *            Nombre del campo
     *            </p>
     * @access public
     */
    public function setFieldUserOpenId($field)
    {
        $this->model->access->setFieldUserOpenId($field);
    }

    /**
     * setFieldPass
     * Asigna el nombre del campo que se sacará la constraseña de usuario de la sesión de la BBDD.
     *
     * @param
     *            field string <p>
     *            Nombre del campo
     *            </p>
     * @access public
     */
    public function setFieldPass($field)
    {
        $this->model->access->setFieldPass($field);
    }

    /**
     * setFieldRol
     * Asigna el nombre del campo que se sacará el nivel de seguridad del usuario.
     * Suele ser el campo que se introduce en el form de acceso
     *
     * @param
     *            field string <p>
     *            Nombre del campo
     *            </p>
     * @access public
     */
    public function setFieldRol($field)
    {
        $this->model->access->setFieldRol($field);
    }

    /**
     * setFieldLastAccess
     * Asigna el nombre del campo que se sacará el último acceso del usuario.
     * Suele ser el campo que se introduce en el form de acceso
     *
     * @param
     *            field string <p>
     *            Nombre del campo
     *            </p>
     * @access public
     */
    public function setFieldLastAccess($field)
    {
        $this->model->access->setFieldLastAccess($field);
    }

    /**
     * setTableAccess
     * Asigna el nombre de los campos y la tablaque se sacarán de la sesión de la BBDD
     *
     * @param
     *            table string <p>
     *            Nombre de la tabla
     *            </p>
     * @param
     *            fields string <p>
     *            Nombre del campos. Separados por comas
     *            </p>
     * @access public
     */
    public function setTableAccess($table)
    {
        $this->model->access->setTableAccess($table);
    }

    /**
     * loginClientAnonymous
     * Conectar como usuario anónimo.
     * Cuando no existe sesión de usuario, se genera un token para las posibles peticiones que pueda hacer el usuario.
     * Por lo general, se llama a esta método en ControllerPage::assignPage
     *
     * @param
     *            reGenToken bool <p>
     *            Si es true regenera el token cuando se llama a este método aunque ya se haya creado antes
     *            </p>
     */
    public function loginAnonymous($reGenToken = false)
    {
        if (strlen($this->getSessionToken()) == 0) {
            $this->assignSessionToken();
        } elseif ($reGenToken === true) {
            $this->assignSessionToken();
        }
        return false;
    }

    /**
     * login
     * Conecta la sesión con el usuario de la BBDD
     *
     * @param
     *            name string <p>
     *            Identificador de usuario
     *            </p>
     * @param
     *            password string <p>
     *            Contraseña
     *            </p>
     *
     */
    public function login($name, $password = "")
    {
        $ok = false;
        $this->setLoginActions($name, $password);

        if ($this->checkLoginActions()) {
            $session_failures = $this->session->get('l_fails');
            $session_failures = 0;
            if ($session_failures < 3) {
                $result = $this->model->access->setAccess();
                $num_result = count($result);
                if ($num_result == 0 || $result === false) {
                    if (strlen($session_failures) > 0) {
                        $session_failures ++;
                        $this->session->set('l_fails', $session_failures);
                    } else {
                        $session_failures = 1;
                        $result = $this->session->set('l_fails', $session_failures);
                        if ($result) {
                            $session_failures = $this->session->get('l_fails');
                        }
                    }
                    if ($session_failures >= 3) {
                        $texto_error = DOMAIN . ": Sobrepasados los intentos de inicio de sesión:<br/>
                        <b>Usuario:</b>" . $name . "<br>
                        <b>Pass:</b>" . $password . "<br>";
                        $this->sendError($texto_error);
                        $ok = false;
                    }
                    $ok = false;
                } elseif ($num_result == 1) {
                    /*
                     * echo "<pre>";
                     * var_dump($result);
                     * echo "</pre>";
                     */
                    if ($this->assignSessionToken($result[0]["ID"]) !== false) {
                        $this->setSessionName(htmlentities($result[0][$this->model->access->getFieldUserNameShow()]));
                        $this->setSessionID($result[0]["ID"]);
                        $this->setSessionLastAccess($result[0][$this->model->access->getFielLastAccess()]);
                        if (strlen($result[0][$this->model->access->getFieldRol()]) > 0 && is_numeric($result[0][$this->model->access->getFieldRol()])) {
                            $this->setSessionSec($result[0][$this->model->access->getFieldRol()]);
                        } else {
                            $this->setSessionSec('0');
                        }
                        $ok = true;
                    }
                } elseif ($num_result > 1) {
                    $this->sendError("[" . get_class($this) . "::" . __FUNCTION__ . "::" . __LINE__ . "] Múltiples usuarios con los mismos datos:" . print_r($result, true) . " <br/>");
                }
            }
        }
        return $ok;
    }

    /**
     * assignSessionToken
     * Asigna un token a la sesión actual
     *
     * @param
     *            idUser string <p>
     *            Identificador de usuario
     *            </p>
     * @return string | bool si es false
     */
    public function assignSessionToken($idUser = "")
    {
        $token = ControllerToken::getInstance();
        $tokenString = $token->tokenGen(40);
        // echo "<br>";
        if (strlen($idUser) > 0) {
            $result = $this->model->access->setAccessToken($idUser, $tokenString);
        }
        $this->setSessionToken($tokenString);

        if (isset($result) && $result)
            return $tokenString;
        else
            return false;
    }

    /**
     * checkSessionToken
     * Comprueba que $token es igual que la variable de sesión self:sessionToken
     *
     * @param
     *            token string <p>
     *            Token a comprobar
     *            </p>
     * @param
     *            regen bool <p>
     *            Una vez verificado, regenerar un token nuevo
     *            </p>
     * @return bool
     */
    public function checkSessionToken($token, $regen = false)
    {
        $result = false;
        if (strlen($this->getSessionToken()) > 0 && strlen($token) > 0) {
            if ($this->getSessionToken() === $token) {
                $result = true;
            }
        }
        if ($regen === true) {
            $result = ($this->assignSessionToken($this->getSessionID()) !== false) ? true : false;
        }
        return $result;
    }

    /**
     * Genera un input con el token.
     * Esta funcion es usada por el controlador ControllerPage para pasrla a la vista
     *
     * @return string
     */
    public function tokenProtectForm()
    {
        return "<input type=\"hidden\" id =\"OLIF_TOKEN\" name=\"OLIF_TOKEN\" value=\"" . $this->getSessionToken() . "\" />";
    }

    /**
     * checkLevelRol
     * Comprueba los niveles de seguridad del usuario conectado
     *
     * @param
     *            lvlRol integer<p>
     *            Nivel a comprobar
     *            </p>
     * @return bool
     */
    public function checkLevelRol($lvlRol)
    {
        $ok = false;
        $lvlUser = $this->getSessionSec();
        if (strlen($lvlRol) > 0) {
            if ($lvlRol == 0) {
                $ok = true;
            } elseif (strlen($lvlUser) > 0) {
                if ($this->checkLevelRolFact($lvlRol, $lvlUser)) {
                    $ok = true;
                }
                /*
                 * Línea Auxiliar hasta que quite que rol admin = '1' en CMS
                 * if ($lvlUser<=$lvlRol) {
                 * //$ok=true;
                 * /* Línea Auxiliar hasta que quite que rol admin = '1' en CMS
                 * } elseif ($lvlUser==1) {
                 * //$ok=true;
                 * } elseif ($this->checkLevelRolFact($lvlRol, $lvlUser)) {
                 * $ok=true;
                 * }
                 */
            }
        }
        return $ok;
    }

    /**
     * checkLevelRolFact
     * Dado el lvlRol, se descompone en factoriales.
     * Si el rol de usuario está entre uno de esos valores tiene permisos
     *
     * @param
     *            lvlRol integer<p>
     *            Nivel base
     *            </p>
     * @param
     *            lvlRol integer<p>
     *            Nivel de usuario
     *            </p>
     * @return bool
     */
    public function checkLevelRolFact($lvlRol, $lvlUser)
    {
        $pf = array();
        $n = $lvlRol;
        for ($i = 2; $i <= $n / $i; $i ++) {
            while ($n % $i == 0) {
                $pf[] = $i;
                $n = $n / $i;
            }
        }
        if ($n > 1)
            $pf[] = $n;
        if ($lvlRol == $lvlUser)
            return true;
        return (in_array($lvlUser, $pf)) ? true : false;
        // return ($array === true) ? $pf : implode(' * ', $pf);
        // 2 3 5 7 11 13 17 19 23 29 31 37 41 43 47 53 59 61 67
        /*
         * $num=13*11*5*3;
         * echo "Factor primo: " . factors($num);
         * print_r(factors($num, true));
         * echo "<br>----------------<br>";
         * $num=13*11*7*5*3*2;
         * echo "Factor primo: de ".$num."" . factors($num)."<br>";
         * print_r(factors($num, true));
         * echo "<br>----------------<br>";
         * $num=13*11*7*5*3*2;
         * echo "Factor primo: de ".$num."" . factors($num)."<br>";
         * print_r(factors($num, true));
         * echo "<br>----------------<br>";
         * $num=53*11*7*5*3*2;
         * echo "Factor primo: de ".$num."" . factors($num)."<br>";
         * print_r(factors($num, true));
         * echo "<br>----------------<br>";
         * $num=19*11*3*2;
         * echo "Factor primo: de ".$num."" . factors($num)."<br>";
         * print_r(factors($num, true));
         * echo "<br>----------------<br>";
         * die();
         */
    }

    /**
     * isLogin
     * Comprueba que el usuario esta logueado y que existe en la BBDD.
     * CHECK_LOGIN_TOKEN es definido en bootstrap.
     *
     * @return bool
     *
     */
    public function isLogin()
    {
        if (defined("CHECK_LOGIN_TOKEN")) {
            $checkToken = CHECK_LOGIN_TOKEN;
        } else {
            $checkToken = true;
        }
        if ($checkToken) {
            if (strlen($this->getSessionID()) > 0 && strlen($this->getSessionToken()) > 0) {
                return $this->model->access->checkAccess($this->getSessionID(), $this->getSessionToken());
            } else {
                return false;
            }
        } else {
            if (strlen($this->getSessionID()) > 0) {
                return $this->model->access->checkAccess($this->getSessionID());
            } else {
                return false;
            }
        }
    }

    /**
     * setLoginActions
     * Asigna usuario y contraseña a la consulta que se va a realizar en base a la acción que se haya definido en $action_login con el método setAction()
     *
     * @param
     *            name string <p>
     *            identificador de usuario
     *            </p>
     * @param
     *            password string <p>
     *            Contraseña
     *            </p>
     *
     */
    public function setLoginActions($name, $password = "")
    {
        switch ($this->action_login) {
            case "USER":
                $this->model->access->setCondUser($name);
                break;
            case "USER_PASS":
                $this->model->access->setCondUser($name);
                $this->model->access->setCondPass($password);

                break;
            case "PASS":
                $this->model->access->setCondPass($password);
                break;
            case "OPEN_ID":
                $this->model->access->setCondIdUserOpenId($name);
                break;
        }
        $this->model->access->setCondStatus();
    }

    /**
     * checkLoginActions
     * Verifica que se puede hacer una consulta a la BBDD y que en base a lo defininido en this::setAction y this::setLoginActions es correcto
     *
     * @return boolean
     *
     */
    public function checkLoginActions()
    {
        $ok = true;
        $errors = array();
        if (strlen($this->action_login) > 0) {
            switch ($this->action_login) {
                case "USER":
                    if (strlen($this->model->access->getFieldUser()) == 0) {
                        $ok = false;
                        $errors[] = 'NO_FIELD_USER';
                    }
                    break;
                case "USER_PASS":
                    if (strlen($this->model->access->getFieldPass()) == 0 || strlen($this->model->access->getFieldUser()) == 0) {
                        $ok = false;
                        $errors[] = 'NO_FIELDS_USER_PASS';
                    }
                    break;
                case "PASS":
                    if (strlen($this->model->access->getFieldPass()) == 0) {
                        $ok = false;
                        $errors[] = 'NO_FIELD_PASS';
                    }
                    break;
                case "OPEN_ID":
                    if (strlen($this->model->access->getFieldUserOpenId()) == 0) {
                        $ok = false;
                        $errors[] = 'NO_FIELD_USER_OPENID';
                    }
                    break;
            }
        } else {
            $errors[] = 'NO_ACTION';
            $ok = false;
        }
        if (strlen($this->model->access->getFieldUserNameShow()) == 0) {
            $errors[] = 'NO_FIELD_USER_SHOW';
            $ok = false;
        }
        if (strlen($this->model->access->getTableAccess()) == 0) {
            $errors[] = 'NO_TABLE';
            $ok = false;
        }
        if ($this->model->access->getFields() === false) {
            $errors[] = 'NO_FIELDS';
            $ok = false;
        }
        if ($ok === false) {
            $texto_error = "[" . get_class($this) . "::" . __FUNCTION__ . "::" . __LINE__ . "] Se produjeron los siguientes errores: <br/>";
            for ($i = 0; $i < count($errors); $i ++) {
                $texto_error .= $errors[$i] . "<br>";
            }
            $this->sendError($texto_error, DOMAIN);
        }
        return $ok;
    }

    /**
     * setAction
     * Define que tipo de query se va a construir en base a las opciones definidas en $this->actions_login
     *
     * @param
     *            type string
     * @return boolean
     */
    public function setAction($type)
    {
        $type = mb_strtoupper($type, 'UTF-8');
        if (array_key_exists($type, $this->actions_login)) {
            $this->action_login = $this->actions_login[$type];
        } else {
            $texto_error = "[ERROR " . get_class($this) . "::" . __FUNCTION__ . "::" . __LINE__ . "] No se ha seleccionado una acción válida\n";
            $result = $this->sendError($texto_error, DOMAIN);
        }
        if (strlen($this->action_login) > 0)
            $result = true;
        else
            $result = false;

        return $result;
    }

    /**
     * logout
     * Desconecta al usuario
     */
    public function logout()
    {
        $this->setSessionID('');
        $this->setSessionSec('');
        return true;
    }

    /**
     * loginClientOpenID
     * Función para loguear con Gmail y OpenID
     *
     * @param
     *            name string <p>
     *            identificador de usuario
     *            </p>
     *
     */
    public function loginOpenID($email)
    {
        $this->setAction('OI');
        return $this->login($email);
    }
}
