<?php
/**
 * ControllerApp
 * @version V 2.0.0.1
 * @author Alberto Vara (C) Copyright 2013
 * @package OLIF.ControllerApp
 *
 *
 */
namespace Olif;

require_once CORE_ROOT . LIBS . DIRECTORY_SEPARATOR . 'Asingleton.php';

abstract class ControllerApp extends Asingleton
{

    /**
     * $session
     * Objeto de la clase ControllerSession
     */
    public $session;

    /**
     * $access
     * Objeto de la clase ControllerAccess
     */
    public $access;

    /**
     * $token
     * Objeto de la clase ControllerToken
     */
    public $token;

    /**
     * $file
     * Objeto de la clase ControllerToken
     */
    public $file;

    /**
     * $curl
     * Objeto de la clase ControllerToken
     */
    public $curl;

    /**
     * $format
     * Objeto de la clase ControllerToken
     */
    public $format;

    /**
     * $req
     * Objeto de la clase ControllerRequest
     */
    public $req;

    /**
     * $model
     * Objeto de la clase modelo ModelDeveloper
     */
    public $model;

    /**
     * $google
     * Objeto de la clase ControllerGoogleAPI
     */
    public $google;

    /**
     * $oauth
     * Objeto de la clase ControllerOAuth
     */
    public $oauth;

    /**
     * $page
     * Objeto de la clase ControllerPage
     */
    public $page;

    /**
     * $cookie
     * Objeto de la clase ControllerPage
     */
    public $cookie;

    /**
     * $db
     * Objeto de la clase ModelDB
     * Excepcional mezclar el modelo con el controlador para facilitar el desarrollo
     */
    public $db;

    protected function __construct()
    {
        // parent::__construct();
    }

    /**
     * getModel
     * Crea el objeto de la clase basica de ModelDeveloper para poder llamar a los diferentes modelos
     *
     * @var object
     * @access public
     */
    public function getModel()
    {
        if (! class_exists('ModelDeveloper'))
            require_once CORE_ROOT . MODELS . DIRECTORY_SEPARATOR . 'ModelDeveloper.php';
        if (! is_object($this->model)) {
            $this->model = ModelDeveloper::getInstance();
        }
    }

    protected function getControllerPage()
    {
        if (! class_exists('ControllerPage'))
            require_once CORE_ROOT . CONTROLLERS . DIRECTORY_SEPARATOR . 'ControllerPage.php';
        if (! is_object($this->page)) {
            $this->page = ControllerPage::getInstance();
        }
    }

    public function getControllerRequest()
    {
        if (! class_exists('ControllerRequest'))
            require_once CORE_ROOT . CONTROLLERS . DIRECTORY_SEPARATOR . 'ControllerRequest.php';
        if (! is_object($this->req)) {
            $this->req = ControllerRequest::getInstance();
        }
    }

    protected function getControllerSession()
    {
        if (! class_exists('ControllerSession'))
            require_once CORE_ROOT . CONTROLLERS . DIRECTORY_SEPARATOR . 'ControllerSession.php';
        if (! is_object($this->session)) {
            $this->session = ControllerSession::getInstance();
        }
    }

    protected function getControllerAccess()
    {
        if (! class_exists('ControllerAccess'))
            require_once CORE_ROOT . CONTROLLERS . DIRECTORY_SEPARATOR . 'ControllerAccess.php';
        if (! is_object($this->access)) {
            $this->access = ControllerAccess::getInstance();
        }
    }

    protected function getControllerFormat()
    {
        if (! class_exists('ControllerFormat'))
            require_once CORE_ROOT . CONTROLLERS . DIRECTORY_SEPARATOR . 'ControllerFormat.php';
        if (! is_object($this->format)) {
            $this->format = ControllerFormat::getInstance();
        }
    }

    protected function getControllerToken()
    {
        if (! class_exists('ControllerToken'))
            require_once CORE_ROOT . CONTROLLERS . DIRECTORY_SEPARATOR . 'ControllerToken.php';
        if (! is_object($this->token)) {
            $this->token = ControllerToken::getInstance();
        }
    }

    protected function getControllerCookie()
    {
        if (! class_exists('ControllerCookie'))
            require_once CORE_ROOT . CONTROLLERS . DIRECTORY_SEPARATOR . 'ControllerCookie.php';
        if (! is_object($this->cookie)) {
            $this->cookie = ControllerCookie::getInstance();
        }
    }

    protected function getControllerCurl()
    {
        if (! class_exists('ControllerCurl'))
            require_once CORE_ROOT . CONTROLLERS . DIRECTORY_SEPARATOR . 'ControllerCurl.php';
        if (! is_object($this->curl)) {
            $this->curl = ControllerCurl::getInstance();
        }
    }

    public function getControllerFile()
    {
        if (! class_exists('ControllerFile'))
            require_once CORE_ROOT . CONTROLLERS . DIRECTORY_SEPARATOR . 'ControllerFile.php';
        if (! is_object($this->file)) {
            $this->file = ControllerFile::getInstance();
        }
    }

    public function getControllerMail()
    {
        if (! class_exists('ControllerMail'))
            require_once CORE_ROOT . CONTROLLERS . DIRECTORY_SEPARATOR . 'ControllerMail.php';
        if (! isset($this->mail))
            $this->mail = "";
        if (! is_object($this->mail)) {
            $this->mail = ControllerMail::getInstance();
        }
    }

    public function getControllerOAuth()
    {
        if (! class_exists('ControllerOAuth'))
            require_once CORE_ROOT . CONTROLLERS . DIRECTORY_SEPARATOR . 'ControllerOAuth.php';
        if (! is_object($this->oauth)) {
            $this->oauth = ControllerOAuth::getInstance();
        }
    }

    public function getControllerMenu()
    {
        if (! class_exists('ControllerMenu'))
            require_once CORE_ROOT . CONTROLLERS . DIRECTORY_SEPARATOR . 'ControllerMenu.php';
        if (! isset($this->menu))
            $this->menu = "";
        if (! is_object($this->menu)) {
            $this->menu = ControllerMenu::getInstance();
        }
    }

    public function getControllerModule($module, $class, $attr)
    {
        if (! class_exists($class))
            if (is_file(CORE_ROOT . 'modules' . DIRECTORY_SEPARATOR . $module . DIRECTORY_SEPARATOR . CONTROLLERS . DIRECTORY_SEPARATOR . $class . '.php'))
                require_once CORE_ROOT . 'modules' . DIRECTORY_SEPARATOR . $module . DIRECTORY_SEPARATOR . CONTROLLERS . DIRECTORY_SEPARATOR . $class . '.php';
        if (! class_exists($class))
            if (is_file(OLIF_ROOT . 'modules' . DIRECTORY_SEPARATOR . $module . DIRECTORY_SEPARATOR . CONTROLLERS . DIRECTORY_SEPARATOR . $class . '.php'))
                require_once OLIF_ROOT . 'modules' . DIRECTORY_SEPARATOR . $module . DIRECTORY_SEPARATOR . CONTROLLERS . DIRECTORY_SEPARATOR . $class . '.php';

        if (! isset($this->$attr))
            $this->$attr = " ";
        if (! is_object(@$this->$attr)) {
            /* Definimos el espacio de nombres de OLIF */
            $class = "\\Olif\\" . $class;
            $this->$attr = new $class();
            return $this->$attr;
        }
    }
}
