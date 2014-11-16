<?php
/**
 * ModelApp
 * @version V 1.7
 * @author Alberto Vara (C) Copyright 2013
 * @package OLIF.ModelApp
 *
 *
 */
namespace Olif;

require_once CORE_ROOT . LIBS . DIRECTORY_SEPARATOR . 'Asingleton.php';

abstract class ModelApp extends Asingleton
{

    /**
     * db
     * objeto de la instancia de modelDB
     */
    public $db;

    /**
     * access
     * objeto de la instancia de modelAccess
     */
    public $access;

    /**
     * page
     * objeto de la instancia de modelPage
     */
    public $page;

    /**
     * file
     * objeto de la instancia de modelFile
     */
    public $file;

    public function __construct()
    {
        // parent::__construct();
    }

    /**
     * Métodos que permiten interactuar a los modelos entre ellos.
     * ModelDB es un modelo que interactúa con la mayoría de modelos al ser el que trabaja sobre la base de datos.
     */
    public function getModelDB()
    {
        if (! class_exists('ModelDB'))
            require_once CORE_ROOT . MODELS . DIRECTORY_SEPARATOR . 'ModelDB.php';
        if (! is_object($this->db)) {
            $this->db = ModelDB::getInstance();
        }
    }

    public function getModelPage()
    {
        if (! class_exists('ModelPage'))
            require_once CORE_ROOT . MODELS . DIRECTORY_SEPARATOR . 'ModelPage.php';
        if (! is_object($this->page)) {
            $this->page = ModelPage::getInstance();
        }
    }

    public function getModelAccess()
    {
        if (! class_exists('ModelAccess'))
            require_once CORE_ROOT . MODELS . DIRECTORY_SEPARATOR . 'ModelAccess.php';
        if (! is_object($this->access)) {
            $this->access = ModelAccess::getInstance();
        }
    }

    public function getModelFile()
    {
        if (! class_exists('ModelFile'))
            require_once CORE_ROOT . MODELS . DIRECTORY_SEPARATOR . 'ModelFile.php';
        if (! is_object($this->file)) {
            $this->file = ModelFile::getInstance();
        }
    }

    public function getModelORM($ormSession)
    {
        if (! class_exists('ModelORM'))
            require_once CORE_ROOT . MODELS . DIRECTORY_SEPARATOR . 'ModelORM.php';
        if (! isset($this->$ormSession) || ! is_object($this->$ormSession)) {
            // $this->$ormSession = ModelORM::getInstance();
            $this->$ormSession = new ModelORM();
        }
    }

    public function getModelModule($module, $class, $attr)
    {
        if (! class_exists($class))
            if (is_file(CORE_ROOT . 'modules' . DIRECTORY_SEPARATOR . $module . DIRECTORY_SEPARATOR . MODELS . DIRECTORY_SEPARATOR . $class . '.php'))
                require_once CORE_ROOT . 'modules' . DIRECTORY_SEPARATOR . $module . DIRECTORY_SEPARATOR . MODELS . DIRECTORY_SEPARATOR . $class . '.php';
        if (! class_exists($class))
            if (is_file(OLIF_ROOT . 'modules' . DIRECTORY_SEPARATOR . $module . DIRECTORY_SEPARATOR . MODELS . DIRECTORY_SEPARATOR . $class . '.php'))
                require_once OLIF_ROOT . 'modules' . DIRECTORY_SEPARATOR . $module . DIRECTORY_SEPARATOR . MODELS . DIRECTORY_SEPARATOR . $class . '.php';

        if (! isset($this->$attr))
            @$this->$attr = "";
        if (! is_object(@$this->$attr)) {
            $class = "\\Olif\\" . $class;
            $this->$attr = $class::getInstance();
        }
    }
}
