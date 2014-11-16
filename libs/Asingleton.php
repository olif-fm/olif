<?php
/**
 * ControllerApp
 * @version V 1.7
 * @author Alberto Vara (C) Copyright 2013
 * @package OLIF.ControllerApp
 *
 *
 */
namespace Olif;

abstract class Asingleton
{

    /**
     * $_instances
     * Objeto de la clase ControllerToken
     */
    private static $_instances = array();

    /**
     * getInstance
     * Si ya existe la clase, la devuelve en vez de crearla de nuevo
     *
     * @return class
     */
    public static function getInstance()
    {
        if (version_compare(PHP_VERSION, '5.3.0', '<')) {
            $class = self::get_called_class();
        } else {
            $class = get_called_class();
        }
        if (! isset(self::$_instances[$class])) {
            self::$_instances[$class] = new $class();
        }
        return self::$_instances[$class];
    }

    public function __wakeup()
    {
        // TODO arreglar esto para que no salte en PhpUnit tests
        // trigger_error("No puedes deserializar una instancia de ". get_class($this) ." class.");
    }

    private function get_called_class()
    {
        $objects = array();
        $traces = debug_backtrace();
        foreach ($traces as $trace) {
            if (isset($trace['object'])) {
                if (is_object($trace['object'])) {
                    $objects[] = $trace['object'];
                }
            }
        }
        if (count($objects)) {
            return get_class($objects[0]);
        }
    }

    public function sendError($texto_error, $rise500 = true)
    {
        if ($rise500 === true) {
            $this->rise500($texto_error);
        } else {
            trigger_error($texto_error, E_USER_WARNING);
        }
    }

    public function rise401($texto_error)
    {
        if (! IS_DEV)
            header('HTTP/1.0 401 Not Access');

        echo ((defined('DEFAULT_ERROR_PERMS')) ? DEFAULT_ERROR_PERMS : 'Sin autorización');
        trigger_error($texto_error, SHOW_USER_ERRORS);

        if (! IS_DEV)
            die();
    }

    public function rise404($texto_error)
    {
        if (! IS_DEV)
            header('HTTP/1.0 404 Not Found');

        echo ((defined('DEFAULT_NOT_FOUND')) ? DEFAULT_NOT_FOUND : 'Página no encontrada');
        trigger_error($texto_error, SHOW_USER_ERRORS);

        if (! IS_DEV)
            die();
    }

    public function rise500($texto_error = "")
    {
        if (IS_DEV) {
            trigger_error("[" . get_called_class() . "] Se produjo un error: " . $texto_error, SHOW_USER_ERRORS);
        } else {
            header("HTTP/1.1 500 Internal Server Error", true, 500);
        }
    }
}
