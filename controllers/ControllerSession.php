<?php
/**
 * ControllerSession
 * @version V 1.7
 * @author Alberto Vara (C) Copyright 2013
 * @package OLIF.ControllerSession
 *
 * @desc
 *
 * DEPENDS/REQUIRED:
 * + controllers/ControllerApp
 * + controllers/ControllerRequest
 */
namespace Olif;

require_once CORE_ROOT.CONTROLLERS.DIRECTORY_SEPARATOR."ControllerApp.php";

class ControllerSession extends ControllerApp {
    private static $Session = null;

    private static function check() {
        if (is_null(self::$Session)) {
            self::$Session = new ControllerSession();
        }
    }
    /**
     * Establece una variable $name a un valor $value en $_SESSION
     * El valor $name de ser un valor válido establecido en SESSION_INDEXES
     * @param $name Nombre del índice
     * @param $value Valor a establecer
     */
    public function set($name, $value) {
        $this->getControllerRequest();
        self::check();
        $ret = false;
        $this->req->checkXSS($value);
        $_SESSION[$name] = $value;
        $ret=true;
        return $ret;
    }
    /**
     * Devuelve el valor de $_SESSION[$name]
     * El valor $name de ser un valor válido establecido en SESSION_INDEXES
     * @param $value
     * <ul>
     * <li>Valor de $_SESSION[$name] si $name se encuentra en SESSION_INDEXES
     * <li>null si $name no se encuentra en SESSION_INDEXES o $_SESSION[$name] no está establecido
     * </ul>
     */
    public function get($name) {
        if (strlen($name)==0) {
            $this->sendError("[ERROR ".get_called_class()."::".__FUNCTION__."::".__LINE__."] Se pasó una variable vacía");
        } else {
            $this->getControllerRequest();
            self::check();
            $ret = null;
            $this->req->checkXSS($name);
            if (isset($_SESSION[$name])) {
                $ret = $_SESSION[$name];
            }
            return $ret;
        }

    }
    /**
     * Destruye una variable de sesión
     * @param string $name Nombre de la variable
     * @return boolean
     * <ul>
     * <li> TRUE si se eliminó correctamente la variable
     * <li> FALSE si no se pudo eliminar o no estaba instanciado $name en $_SESSION
     * </ul>
     */
    public function destroy($name) {
        $ret = false;
        if (isset($name) && isset($_SESSION[$name])) {
            unset($_SESSION[$name]);
            $ret = true;
        }
        return $ret;
    }
    /**
     * Finaliza la sesión actual
     */
    public function end() {
        if (isset($_SESSION)) {
            session_destroy();
            unset($_SESSION);
        }
        self::$Session = null;
    }
}
