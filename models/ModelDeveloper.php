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

require_once CORE_ROOT.MODELS.DIRECTORY_SEPARATOR."ModelApp.php";

class ModelDeveloper extends ModelApp {
    /**
     * $db
     * Objeto de la clase ModelDB
     * @var object
     */
    public $db;
    /**
     * $page
     * Objeto de la clase ModelPage
     *  */
    public $page;
    /**
     * $access
     * Objeto de la clase ModelAccess
     */
    public $access;

    public function __construct() {
        parent::__construct();
    }
    /**
     * @method init
     * Variable que guarda la accion a realizar
     * @access public
     * s
     */
    public function init() {

    }
}
