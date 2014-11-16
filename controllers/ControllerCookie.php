<?php
/**
 * ControllerCookie
 * @version V 1.7
 * @author Alberto Vara (C) Copyright 2013
 * @package OLIF.ControllerCookie
 *
 * Controla las cookies del sistema
 *
 * DEPENDS/REQUIRED:
 * + controllers/ControllerApp
 */
namespace Olif;

require_once CORE_ROOT . CONTROLLERS . DIRECTORY_SEPARATOR . "ControllerApp.php";

class ControllerCookie extends ControllerApp
{

    private static $actualCookie = null;

    public function __construct()
    {
        parent::__construct();
        if (class_exists('\Olif\ControllerRequest')) {
            $this->req = ControllerRequest::getInstance();
        } else {
            die("[ERROR " . get_class($this) . "::" . __FUNCTION__ . "::" . __LINE__ . "] No existe ControllerRequest");
        }
        // echo "Clase Dev:".print_r($this->page->model);
    }

    private static function check()
    {
        if (is_null(self::$actualCookie)) {
            self::$actualCookie = new ControllerCookie();
        }
    }

    /**
     * Establece una variable $name a un valor $value en $_SESSION
     * El valor $name de ser un valor válido establecido en SESSION_INDEXES
     *
     * @param $name Nombre
     *            del índice
     * @param $value Valor
     *            a establecer
     */
    public function set($name, $value, $time = "", $check_xss = true)
    {
        self::check();
        if (strlen($time) == 0) {
            // $time=time() + (20 * 365 * 24 * 60 * 60);
            $time = time() + (24 * 60 * 60);
        }
        if ($check_xss === true) {
            if (! $this->req->checkXSS($value))
                return false;
        }
        if (PROTOCOL == "http://") {
            return setcookie($name, $value, $time, '/', '', false, true);
        } else {
            return setcookie($name, $value, $time, '/', '', true, false);
        }
    }

    /**
     * Establece una array $name a un valor $array
     *
     * @param $name Nombre
     *            del índice
     * @param $value Valor
     *            a establecer
     */
    public function setArray($name, $array)
    {
        $result = false;
        if (is_array($array)) {
            $result = $this->set($name, serialize($array));
        }
        return $result;
    }

    /**
     * Devuelve el valor de $_COOKIE[$name]
     *
     * @param $value <ul>
     *            <li>Valor de $_SESSION[$name] si $name se encuentra en SESSION_INDEXES
     *            <li>null si $name no se encuentra en SESSION_INDEXES o $_SESSION[$name] no está establecido
     *            </ul>
     */
    public function get($name)
    {
        self::check();
        $ret = null;
        $this->req->checkXSS($name);
        if (isset($_COOKIE[$name]))
            $ret = $_COOKIE[$name];
        return $ret;
    }

    /**
     * Devuelve el valor de $_COOKIE[$name]
     *
     * @param $value <ul>
     *            <li>Valor de $_SESSION[$name] si $name se encuentra en SESSION_INDEXES
     *            <li>null si $name no se encuentra en SESSION_INDEXES o $_SESSION[$name] no está establecido
     *            </ul>
     */
    public function getArray($name)
    {
        $ret = unserialize($this->get($name));
        if (is_array($ret)) {
            return $ret;
        } else {
            return false;
        }
    }

    /**
     * Destruye una variable de sesión
     *
     * @param string $name
     *            Nombre de la variable
     * @return boolean <ul>
     *         <li> TRUE si se eliminó correctamente la variable
     *         <li> FALSE si no se pudo eliminar o no estaba instanciado $name en $_SESSION
     *         </ul>
     */
    public function destroy($name)
    {
        $ret = false;
        if (isset($name) && isset($_COOKIE[$name])) {
            unset($_COOKIE[$name]);
            $ret = true;
        }
        return $ret;
    }

    /**
     * Finaliza la sesión actual
     */
    public function end()
    {
        if (isset($_COOKIE)) {
            unset($_COOKIE);
        }
        self::$actualCookie = null;
    }
}
