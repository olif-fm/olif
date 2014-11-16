<?php
/**
 * ControllerDeveloper
 * @version V 1.7
 * @author Alberto Vara (C) Copyright 2013
 * @package OLIF.ControllerDeveloper
 *
 * Esta clase sirve exclusivamente para facilitar a los desarrolladores el acceso a las clases de controladores
 * para manipular el modelo desde la lógica de cada proyecto sin tener que acceder a los métodos
 *
 * DEPENDS/REQUIRED:
 * + controllers/ControllerApp
 */
namespace Olif;

require_once CORE_ROOT.CONTROLLERS.DIRECTORY_SEPARATOR."ControllerApp.php";

class ControllerDeveloper extends ControllerApp {
    public function __construct() {

    }
    /**
     * @method init
     * Variable que guarda la accion a realizar
     * @access public
     *
     */
    public function init() {
        $this->getControllerMenu();

        $this->getControllerPage();

        $this->getControllerRequest();

        $this->getControllerSession();

        $this->getControllerToken();

        $this->getControllerAccess();

        $this->getControllerFormat();

        $this->getControllerCookie();


        $this->db = modelDB::getInstance();
    }
    public function test() {
        return "[".get_class($this)."::".__FUNCTION__."::".__LINE__."]";
    }
}
