<?php

/**
 * ControllerPage
 * @version V 1.7
 * @author Alberto Vara (C) Copyright 2013
 * @package OLIF.ControllerPage
 *
 * DEPENDS/REQUIRED:
 * + controllers/ControllerApp
 * + models/modelPage
 */
namespace Olif;

require_once CORE_ROOT . CONTROLLERS . DIRECTORY_SEPARATOR . "ControllerApp.php";
class ControllerPage extends ControllerApp {

    /**
     * fileTemplateIndex
     * Fichero TPL principal.
     * Por regla general es el index.tpl. En ViewPage, Depende del "action" Se modificará este fichero
     *
     * @var string
     * @access protected
     *
     */
    protected $fileTemplateIndex;

    /**
     * fileTemplate
     * Fichero TPL que se cargará dentro del index en {contenido}
     *
     * @var string
     * @access protected
     *
     */
    protected $fileTemplate;

    /**
     * $actionsPage
     * Define el comportamiento que podrá tener la visualización y la lógica entre la "base" que aporta el index y el contenido particular de cada sección o página.
     * Por defecto se carga la plantilla y la lógica de index, dentro, se carga la sección específica también con su lógica y su base
     *
     * @param
     *            noIL: No Index Logic
     * @param
     *            noIT: No Index Template
     * @param
     *            noILT: No Index Logic & template
     * @param
     *            Header: Carga Index Logic. Sólo carga la plantilla "no_header" que sólo contiene la metainformación del html
     * @param
     *            Content: Carga Index Logic. Sólo carga la plantilla "no_content" que no tiene nada.
     *
     * @access protected
     *
     */
    protected $actionsPage = array (
            'noIL',
            'noIT',
            'noILT',
            'noHeader',
            'noContent'
    );

    /**
     * $action
     * Acción que se aplicará sobre la página
     *
     * @access protected
     *
     */
    protected $action = "";

    /**
     * __construct
     * Creamos el modelo e instanciamos la clase de acceso para evaluar posteriormente los niveles de acceso
     *
     * @var bool
     * @access protected
     */
    public function __construct() {
        $this->getModel();
        $this->getControllerMenu();
        $this->model->getModelPage();
        $this->getControllerAccess();
        parent::__construct();
    }
    private $pageToLoad;
    /**
     * assignPages
     * Se asigna de qué tabla se sacan los datos para las páginas.
     * Si se detecta que nos encontramos en un CMS, la información se encuentra en la DB definida en la constante TABLE_MENU que por defecto es ceo_menu
     *
     * @return NULL
     * @var $pageToLoad string <p>
     *      Fichero a cargar
     *      </p>
     * @var action string <p>
     * @param
     *            noIL: No Index Logic
     * @param
     *            noILT: No Index Logic & template
     * @param
     *            noHeader: Carga Index Logic. Sólo carga la plantilla "no_header" que no tiene nada.
     * @param
     *            noContent: Carga Index Logic. Sólo carga la plantilla "no_content" que sólo contiene la metainformación del html.
     *            </p>
     *
     */
    public function assignPage($pageToLoad, $action = "") {
        $redirect = false;
        $islogin = false;
        if (strlen($pageToLoad) == 0) {
            $pageToLoad = 'inicio';
        }
        $this->pageToLoad = $pageToLoad;
        $this->menu->set($this->pageToLoad);
        /**
         * Verificamos que la página existe, si no, 4040
         */
        $this->checkPage();

        if (strlen($action) > 0) {
            $this->setAction($action);
        }
        if ($this->checkPerms() != 0)
            if ($this->access->isLogin())
                $islogin = true;
        if (! $islogin)
            $this->access->loginAnonymous();
        if ($this->checkPerms() != 0 && ! $islogin) {
            $redirect = 'login';
            header('location: ' . PROTOCOL . WEB_URL_NO_PROTOCOL . NODE_ROOT . 'login');
        }
        if ($this->menu->url == "login" && $islogin) {
            header('location: ' . PROTOCOL . WEB_URL_NO_PROTOCOL . NODE_ROOT . 'inicio');
        } elseif ($islogin && ! $this->access->checkLevelRol($this->checkPerms())) {
            $this->rise401("[ERROR " . get_class($this) . "::" . __FUNCTION__ . "::" . __LINE__ . "] Página " . $pageToLoad . " sin permisos: USUARIO LVL" . $this->access->getSessionSec() . " PÁGINA LVL: " . $this->checkPerms());
        } elseif ($this->checkPerms() == 0) {
            $redirect = false;
        }
        $this->assignVar('TOKEN', "" . $this->access->tokenProtectForm(), false);
        $this->assignVar('TOKEN_VAR', "" . $this->access->getSessionToken());
        if ($redirect === false) {
            /**
             * Si page action es una de las siguientes opciones no se carga el index (para reducir carga)
             */
            if (! in_array($this->getAction(), array (
                    'noIL',
                    'noILT'
            ))) {
                $this->setPageLogicIndex();
            }
            /**
             * Asignamos el fichero a FileTemplate por si en la lógica se cambia el menú
             */
            $this->fileTemplate = $this->menu->FILE;
            $this->setPageLogic();
        } else {
            $this->assignPage($redirect);
        }
    }

    /**
     * *
     * assignFileTemplate
     * Reemplaza la plantilla que se va a cargar de contenido
     *
     * @param string $file
     */
    public function setFileTemplate($file) {
        $this->fileTemplate = $file;
    }

    /**
     * getFileTemplate
     * Devuelve el template qque se asignará, si no, el fichero por defecto que sacamos del menú
     *
     * @return string
     */
    public function getFileTemplate() {
        if (strlen($this->fileTemplate) > 0) {
            return $this->fileTemplate;
        } else {
            return $this->menu->FILE;
        }
    }

    /**
     * Cambia el archivo de lógica a cargar.
     *
     * @param
     *            file string
     * @access public
     */
    public function setAction($action) {
        if (! in_array($action, $this->actionsPage))
            $this->sendError("[ERROR " . get_class($this) . "::" . __FUNCTION__ . "::" . __LINE__ . "] Acción erronea: " . $action);
        $this->action = $action;
    }

    /**
     * Devuelve el valor de action
     *
     * @param
     *            file string
     * @access public
     */
    public function getAction() {
        if (strlen($this->action) > 0) {
            return $this->action;
        } else {
            return $this->menu->action;
        }
    }

    /**
     * Asignamos el archivo de lógica.
     *
     * @access public
     */
    public function setPageLogic() {
        /**
         * Definimos $dev como global porque es el objeto que usarán los desarrolladores dentro de la lógica
         */
        global $dev;
        if ($this->checkPage()) {
            $logic = $this->menu->FILE . ".php";
            $logic_path = WEBSITE_ROOT . 'logic/' . $logic;
            if (is_file($logic_path)) {
                require_once $logic_path;
            }
        }
    }

    /**
     * Cambia el archivo de lógica BASE/INDEX a cargar.
     * Los permisos se asignan antes de cargar el fichero, por lo que chequear los permisos dentro del index o la logic no sirve
     *
     * @param
     *            file string
     * @access public
     */
    public function setPageLogicIndex() {
        /**
         * Definimos $dev como global porque es el objeto que usarán los desarrolladores dentro de la lógica
         */
        global $dev;
        $dev = new ControllerDeveloper();
        $dev->init();
        if ($this->checkPage()) {
            $logic = "index.php";
            $logic_path = WEBSITE_ROOT . 'logic/' . $logic;
            if (is_file($logic_path)) {
                require_once $logic_path;
            }
        }
    }

    /**
     * Cambia el archivo de plantilla BASE/INDEX a cargar.
     * Usado en ocasiones muy puntuales.
     *
     * @param
     *            file string
     * @access public
     */
    public function setFileTemplateIndex($file) {
        $this->fileTemplateIndex = $file;
    }

    /**
     * Devuelve BASE/INDEX a cargar.
     * Usado en ocasiones muy puntuales.
     *
     * @access public
     */
    public function getFileTemplateIndex() {
        if (strlen($this->fileTemplateIndex) > 0 && $this->fileTemplateIndex != null) {
            return $this->fileTemplateIndex;
        } else {
            return 'index';
        }
    }

    /**
     * Verifica que hay una página asignada
     *
     * @return bool
     * @access public
     */
    public function checkPage() {
        return (strlen($this->menu->id)) ? true : $this->rise404("[ERROR " . get_class($this) . "::" . __FUNCTION__ . "::" . __LINE__ . "] Página " . $this->pageToLoad . " no existe");
    }

    /**
     * Devuelve el nivel de seguridad de la página seleccionada.
     *
     * @return string
     * @access public
     */
    public function checkPerms() {
        return $this->menu->security;
    }

    /**
     * clientIsMovile
     * Detecta si el cliente que entra en la web es a través de un movil
     *
     * @param
     *            section bool <p>
     *            si se pone a false, devuelve siempre false
     *            </p>
     * @access public
     */
    public function checkMovile($section = true) {
        require_once (CORE_ROOT . THREEPARTY . 'Mobile-Detect/Mobile_Detect.php');
        $detect = new Mobile_Detect();
        if ($section === false) {
            return false;
        } else {
            if ($detect->isMobile() > 0 && $detect->isTablet() == 0) {

                return true;
            } else {
                return false;
            }
        }
    }

    /**
     * assignList
     * Asigna el una lista a el bucle $blockname de la plantilla con las variables $vararray
     *
     * @param
     *            blockname string <p>
     *            Nombre del bloque que se mostrará en la plantilla
     *            </p>
     * @param
     *            vararray array <p>
     *            Valores del array. la KEY del subarray tiene que ser STRING
     *            </p>
     * @access public
     *
     */
    public function assignList($blockname, $vararray) {
        /*
         * if (strlen($padre)>0) { $this->model->page->lists[$padre][$name][]=$params; } else { $this->model->page->lists[$name][]=$params; }
         */
        if (strpos($blockname, '.') !== false) {
            $blocks = explode('.', $blockname);
            $blockcount = sizeof($blocks) - 1;
            $str = &$this->model->page->lists;
            for ($i = 0; $i < $blockcount; $i ++) {
                $str = &$str[$blocks[$i]];
                $str = &$str[sizeof($str) - 1];
            }
            $s_row_count = isset($str[$blocks[$blockcount]]) ? sizeof($str[$blocks[$blockcount]]) : 0;
            $vararray['S_ROW_COUNT'] = $s_row_count;
            if (! $s_row_count)
                $vararray['S_FIRST_ROW'] = true;
                // Now the tricky part, we always assign S_LAST_ROW and remove the entry before
                // This is much more clever than going through the complete template data on display (phew)
            $vararray['S_LAST_ROW'] = true;
            if ($s_row_count > 0)
                unset($str[$blocks[$blockcount]][($s_row_count - 1)]['S_LAST_ROW']);
                // Now we add the block that we're actually assigning to.
                // We're adding a new iteration to this block with the given
                // variable assignments.
            $str[$blocks[$blockcount]][] = $vararray;
        } else {
            // Top-level block.
            $s_row_count = (isset($this->model->page->lists[$blockname])) ? sizeof($this->model->page->lists[$blockname]) : 0;
            $vararray['S_ROW_COUNT'] = $s_row_count;
            // Assign S_FIRST_ROW
            if (! $s_row_count)
                $vararray['S_FIRST_ROW'] = true;
                // We always assign S_LAST_ROW and remove the entry before
            $vararray['S_LAST_ROW'] = true;
            if ($s_row_count > 0)
                unset($this->model->page->lists[$blockname][($s_row_count - 1)]['S_LAST_ROW']);
                // Add a new iteration to this block with the variable assignments we were given.
            $this->model->page->lists[$blockname][] = $vararray;
        }
        return true;
    }
    /*
     * Función Auxiliar!!! borrar lo antes posible y ponerlo mejor!!!
     */
    public function getMenu() {
        return $this->menu->getlist('', 'ORDER BY POSITION ASC', 0, 1000);
    }

    /**
     * assignMenu
     * Asigna el menÃº a el bucle "menu" de la plantilla
     *
     * @access public
     *
     */
    public function assignMenu($use_node_root = '1') {
        $lang = (strlen(LANG_SELECTED) > 0) ? LANG_SELECTED : 'ES';
        $menu = $this->getMenu();
        $assignMenu = array ();
        $IS_CHILDER = 0;
        for ($i = 0; $i < $menu['num_elements']; $i ++) {
            $TAG_START_CHILDRENS_IS_OPEN = 0;
            if ($this->access->checkLevelRol($menu['elements'][$i]['SECURITY']) && $menu['elements'][$i]['TYPE'] != 'no_menu' && $menu['elements'][$i]['USE_NODE_ROOT'] == $use_node_root) {
                $menu['elements'][$i]['SELECTED'] = 0;
                $menu['elements'][$i]['START_CHILDRENS'] = 0;
                $menu['elements'][$i]['END_CHILDRENS'] = 0;
                if ($this->menu->id == $menu['elements'][$i]['ID']) {
                    $menu['elements'][$i]['SELECTED'] = 1;
                    $num_elements = $i - 1;
                    $actual_level = $this->menu->lvl;
                    for ($j = $num_elements; $j >= 0; $j --) {
                        if ($menu['elements'][$j]['LVL'] < $actual_level) {
                            $menu['elements'][$j]['SELECTED'] = 1;
                            $actual_level = $menu['elements'][$j]['LVL'];
                        }
                    }
                }
                $menu['elements'][$i]['IS_CHILDEN'] = $IS_CHILDER;
                if(isset($menu['elements'][$i + 1])){
                    if ($menu['elements'][$i]['LVL'] < $menu['elements'][$i + 1]['LVL'] && $menu['elements'][$i + 1]['TYPE'] != 'no_menu') {
                        $menu['elements'][$i]['START_CHILDRENS'] = 1;
                        $TAG_START_CHILDRENS_IS_OPEN = 1;
                        $IS_CHILDER = 0;
                    }
                    if ($menu['elements'][$i]['LVL'] > $menu['elements'][$i + 1]['LVL'] || $i + 1 == $menu['num_elements']) {
                        $menu['elements'][$i]['END_CHILDRENS'] = 1;
                        $IS_CHILDER = 1;
                    }
                }else{
                    $menu['elements'][$i]['END_CHILDRENS'] = 1;
                }
            }
        }
        for ($i = 0; $i < $menu['num_elements']; $i ++) {
            if ($this->access->checkLevelRol($menu['elements'][$i]['SECURITY']) && $menu['elements'][$i]['TYPE'] != 'no_menu' && $menu['elements'][$i]['USE_NODE_ROOT'] == $use_node_root) {
                $assignMenu[] = $menu['elements'][$i];
            }
        }
        $num_elements = count($assignMenu);
        /*
         * TODO: Need to test this script
         * if ($TAG_START_CHILDRENS_IS_OPEN == 1) {
         * $assignMenu[count($assignMenu) - 1]['END_CHILDRENS'] = 1;
         * }
         */
        for ($i = 0; $i < $num_elements; $i ++) {
            $assignMenu[$i]['NAME'] = $assignMenu[$i]['NAME_' . $lang];
            $this->assignList('menu', $assignMenu[$i]);
        }
    }

    /**
     * getPageURI
     * Devuelve la URI real que se está cargando.
     * ATENCION! distinta a la URI real del navegador.
     * Ejemplo. Si estamos en dominio.com/clientes/pagina/2. Esta URI devolverá "clientes"
     *
     * @access public
     *
     */
    public function getPageURI() {
        return $this->menu->url;
    }
    public function getPageName() {
        return $this->menu->name_ . LANG_SELECTED;
    }

    /**
     * setBreadCrumbs
     * Asigna las migas de pan para contruirla en la plantilla en bucle "breadcrums"
     *
     * @access public
     *
     */
    public function setBreadCrumbs($setInicio = true) {
        $lang = (strlen(LANG_SELECTED) > 0) ? LANG_SELECTED : 'ES';
        $breadcrums = $this->menu->getBreadCrumbs($setInicio = true);
        $num_breadcrums = count($breadcrums);
        for ($i = 0; $i < $num_breadcrums; $i ++) {
            $breadcrums[$i]['NAME'] = $breadcrums[$i]['NAME_' . $lang];
            if (($i + 1) < $num_breadcrums) {
                $breadcrums[$i]['LAST'] = false;
            } else {
                $breadcrums[$i]['LAST'] = true;
            }
            $this->assignList('breadcrums', $breadcrums[$i]);
        }
        return $breadcrums;
    }

    /**
     * assignVar
     * Asigna una variable a una plantilla
     *
     * @param
     *            name string <p>
     *            Nombre de la variable
     *            </p>
     * @param
     *            value string <p>
     *            Valor de la variable
     *            </p>
     * @access public
     *
     */
    public function assignVar($name, $value, $parse = true) {
        if ($parse === true)
            $value = htmlentities($value, ENT_QUOTES | ENT_IGNORE, "UTF-8");
        $this->model->page->setVar($name, $value);
    }

    /**
     * *
     * assignVarFileTemplate
     * Asigna en una variable una plantilla (.tpl)
     *
     * @param
     *            var string <p>
     *            Nombre de la variable
     *            </p>
     * @param
     *            file string <p>
     *            Valor de la variable
     *            </p>
     * @access public
     */
    public function assignVarFileTemplate($var, $file) {
        $this->model->page->setVarFileTemplate($var, $file);
    }

    /**
     * assignVars
     * Asigna variables a una plantilla en array
     */
    public function assignVars(array $values) {
        foreach ($values as $name => $value) {
            $this->model->page->setVar($name, $value);
        }
    }

    /**
     * noCache
     * Se elimina la caché para las páginas con contenido en constante cambio
     */
    public function noCache() {
        header("Cache-Control: no-store, no-cache, must-revalidate");
        header("Cache-Control: post-check=0, pre-check=0", false);
    }

    /**
     * assignDictionary
     * Añadido de Jose.
     * PENDIENTE de pasar a su correspondiente clase. SOLO SE USA EN BOOTSTRAP
     */
    public function assignDictionary($lang) {
        // Asignar el diccionario base
        $lang = strtoupper($lang);
        $dic = $this->model->page->getDictionary($lang);
        if ($dic !== false) {
            $num_words = count($dic);
            for ($i = 0; $i < $num_words; $i ++) {
                // echo "ID ".$dic[$i]['ID']." WORD".$dic[$i][$lang];
                $this->assignVar("dic_" . $dic[$i]['ID'], $dic[$i][$lang]);
            }
        }
    }
    public function getTitle() {
        $name = 'name_' . LANG_SELECTED;
        return $this->menu->{$name};
    }
    public function getMetaTitle() {
        $title_meta = 'title_meta_' . LANG_SELECTED;
        return $this->menu->{$title_meta};
    }
    public function getMetaDesc() {
        $desc_meta = 'desc_meta_' . LANG_SELECTED;
        return $this->menu->{$desc_meta};
    }
    public function getMetaKeywords() {
        $keywords_meta = 'keywords_meta_' . LANG_SELECTED;
        return $this->menu->{$keywords_meta};
    }
}
