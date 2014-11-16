<?php
namespace Olif;

if (! empty($_SERVER['HTTPS']) && @$_SERVER['HTTPS'] !== 'off' || @$_SERVER['SERVER_PORT'] == 443) {
    define("PROTOCOL", "https://");
    session_set_cookie_params((24 * 60 * 60), '/', '', true, true);
} else {
    define("PROTOCOL", "http://");
    session_set_cookie_params((24 * 60 * 60), '/', '', false, true);
}

session_cache_limiter('private_no_expire');
session_cache_expire(60 * 24 * 30);
session_start();

/**
 * OLIF SYSTEM
 * Alberto Vara 2013
 * Arrancamos cargando las partes estáticas del sistema (core) y las partes variables de cada proyecto (conf)
 */
/**
 * Constantes
 *
 * @var OLIF_ROOT directorio donde se encuentra la web
 * @var CONF_ROOT directorio donde se encuentra la configuración del sistema VARIABLE de cada proyecto
 * @var CORE_ROOT directorio donde se encuentra el sistema
 */
header("Content-Type: text/html;charset=utf-8");

// Adds X-Frame-Options to HTTP header, so that page can only be shown in an iframe of the same site.
header('X-Frame-Options: SAMEORIGIN');
/**
 * Rura absoluta de donde venimos.
 * Suele ser "[PATH_PROJECT]/public/" o "[PATH_PROJECT]/public/admin/"
 *
 * @var string
 */
define('OLIF_ROOT', str_replace(NODE_ROOT, "", $DIR_INDEX));
/**
 * Dominio donde nos encontramos
 *
 * @var string
 */
define("DOMAIN", @$_SERVER["HTTP_HOST"]);
/**
 * Carpeta de las vistas
 *
 * @var string
 */
define("VIEWS", "views");
/**
 * Carpeta de los controladores
 *
 * @var string
 */
define("CONTROLLERS", "controllers");
/**
 * Carpeta de los modelos
 *
 * @var string
 */
define("MODELS", "models");
/**
 * Carpeta de los scripts
 *
 * @var string
 */
define("SCRIPTS", "scripts");
/**
 * Carpeta de las librerías
 *
 * @var string
 */
define("LIBS", "libs");
/**
 * Carpeta de softwarede terceros obligatorias para el funcionamiento de un proyecto
 *
 * @var string
 */
define("THREEPARTY_REQ", "threeparty" . DIRECTORY_SEPARATOR);
/**
 * Carpeta de softwarede terceros
 *
 * @var string
 */
define("THREEPARTY", "vendor" . DIRECTORY_SEPARATOR);
/**
 * URL Ficticia usada en las llamadas AJAX
 *
 * @var string
 */
define("AJAX_URL", "kwsn/");
/**
 * URL real de los script RIA llamados por AJAX
 *
 * @var string
 */
define("RIA_FOLDER", "ria/");

/**
 * Variable declarada en el index para definir el nivel, si el core esta dentro de la carpeta o fuera
 *
 * @var string
 */
if (! isset($DIR_INDEX))
    $DIR_INDEX = "";
/**
 * Ruta relativa donde se encuentra la web.
 * En producción suele ser NULL la web pública y "admin/" si es el CMS.
 * En local y en los entornos de producción que lo permitan, será parecido a "public/" WP y "public/admin/" si es CMS
 *
 * @var string
 */
define("WEB_FOLDER", str_replace(OLIF_ROOT, "", $DIR_INDEX));
/**
 * Ruta Absoluta donde se encuentra el sistema OLIF
 *
 * @var string
 */
define("CORE_ROOT", OLIF_ROOT . 'olif' . DIRECTORY_SEPARATOR);
/**
 * Ruta Absoluta donde se encuentra la web y el CMS.
 * Ocurre lo mismo que en la constante WEB_FOLDER
 *
 * @var string
 */
define('WEBSITE_ROOT', OLIF_ROOT . WEB_FOLDER);
/**
 * Ruta Absoluta donde se encuentra las constantes particulares de cada proyecto
 *
 * @var string
 */
define('CONF_ROOT', OLIF_ROOT . 'conf' . DIRECTORY_SEPARATOR);
require_once (CONF_ROOT . "pathProject.php");
require_once (CONF_ROOT . "constantsProject.php");

/**
 * Tablas genéricas usadas en el sisema
 *
 * @var strings
 */
/**
 * Desde 3.0 TABLE_MENU NO ES OBLIGATORIA
 */
if (! defined("TABLE_MENU"))
    define("TABLE_MENU", "ceo_menu");
if (! defined("TABLE_MENU_FIELDS"))
    define("TABLE_MENU_FIELDS", "ID, CREATED, FILE, URL, ACTION,
        NAME_ES, NAME_EN, NAME_GE, NAME_FR, NAME_CAT, NAME_EUS, TYPE, LVL, SECURITY,
        TITLE_META_ES, TITLE_META_EN, TITLE_META_GE, TITLE_META_FR, TITLE_META_CAT, TITLE_META_EUS,
        DESC_META_ES, DESC_META_EN, DESC_META_GE, DESC_META_FR, DESC_META_CAT, DESC_META_EUS,
        KEYWORDS_META_ES, KEYWORDS_META_EN, KEYWORDS_META_GE, KEYWORDS_META_FR, KEYWORDS_META_CAT, KEYWORDS_META_EUS,
        CLASS, SEARCH_BOX, POSITION, STATUS");
/**
 * Desde 3.0 TABLE_USERS NO ES OBLIGATORIA
 */
if (! defined("TABLE_USERS"))
    define("TABLE_USERS", "ceo_users");
if (! defined("TABLE_USERS_FIELDS"))
    define("TABLE_USERS_FIELDS", "ID, EMAIL, PASSWORD, CREATED, NAME, SURNAME, PHONE, ADDRESS, GOOGLE_ACCOUNT, ROL, STATUS");
/**
 * TABLAS OBLIGATORIAS
 */
if (! defined("TABLE_ROLS"))
    define("TABLE_ROLS", "ceo_rols");
if (! defined("TABLE_ROLS_FIELDS"))
    define("TABLE_ROLS_FIELDS", "ID,NAME_ES,NAME_EN,DESCRIPTION,STATUS");
if (! defined("TABLE_FILES"))
    define("TABLE_FILES", "ceo_files");
if (! defined("TABLE_FILES_FIELDS"))
    define("TABLE_FILES_FIELDS", "ID, FK_USER, IP, PATH, FILENAME, MIME_TYPE, FK_GOOGLE_DRIVE, LAST_UPDATE ");
if (! defined("ROL_ADMIN_N"))
    define("ROL_ADMIN_N", "67");
if (! defined("ROL_USER_REGULAR_N"))
    define("ROL_USER_REGULAR_N", "2");
if (! defined("TABLE_LOGS"))
    define("TABLE_LOGS", "ceo_logs");
if (! defined("TABLE_COUNTRIES"))
    define("TABLE_COUNTRIES", "ceo_countries");
if (! defined("TABLE_PROVS"))
    define("TABLE_PROVS", "ceo_provinces");
if (! defined("TABLE_PROVS_FIELDS"))
    define("TABLE_PROVS_FIELDS", "ID, NAME_ES, NAME_CAT");
if (! defined('CORE_ROOT'))
    die("[F: " . __FILE__ . " Line " . __LINE__ . "]CORE_ROOT no defined");
    /* HTML_GENERICOS */
if (! defined("DEFAULT_ERROR_PERMS"))
    define("DEFAULT_ERROR_PERMS", (file_get_contents(CORE_ROOT . VIEWS . DIRECTORY_SEPARATOR . 'defaultTemplates/ERROR_PERMS.html', FILE_USE_INCLUDE_PATH)));
if (! defined("DEFAULT_NOT_FOUND"))
    define("DEFAULT_NOT_FOUND", (file_get_contents(CORE_ROOT . VIEWS . DIRECTORY_SEPARATOR . 'defaultTemplates/NOT_FOUND.html', FILE_USE_INCLUDE_PATH)));
if (! defined("DEFAULT_CONSTRUCT"))
    define("DEFAULT_CONSTRUCT", (file_get_contents(CORE_ROOT . VIEWS . DIRECTORY_SEPARATOR . 'defaultTemplates/CONSTRUCCION.html', FILE_USE_INCLUDE_PATH)));

/**
 * Inicializar el sistema
 */
require_once (CORE_ROOT . "scripts/sendMailError.php");
require_once (CORE_ROOT . "scripts/myErrorHandler.php");
// require_once(CORE_ROOT."scripts/myExceptionHandler.php");
$old_error_handler = set_error_handler("myErrorHandler");
// $old_exception_handler = set_exception_handler('myExceptionHandler');
/**
 * *******
 * Función para cargar todas las clases de Controladores para poder instanciar directamente.
 * ** ***** ===================\\//=================== ***** *
 */
require_once CORE_ROOT . MODELS . DIRECTORY_SEPARATOR . 'ModelDeveloper.php';
require_once CORE_ROOT . CONTROLLERS . DIRECTORY_SEPARATOR . 'ControllerDeveloper.php';

require_once CORE_ROOT . VIEWS . DIRECTORY_SEPARATOR . 'ViewPage.php';
/**
 * ***** ===================//\\=================== ***** *
 */

/**
 * *******
 * Creamos e instanciamos las clases mínimas
 * ** ***** ===================\\//=================== ***** *
 */
$devm = new ModelDeveloper();
$devm->getModelDB();
$devm->db->connect(DBHOST, DBUSER, DBPASS, DBNAME, DBPORT, DBSOCKET);

/**
 * ***** ===================//\\=================== ***** *
 */

/**
 * *******
 * Creamos e instanciamos las clases mínimas de página
 * ** ***** ===================\\//=================== ***** *
 */
$dev = new ControllerDeveloper();
$dev->init();

/**
 * ***** ===================//\\=================== ***** *
 */
/**
 * Variables que definen qué pagina estamos cargando y de que manera
 * ** ***** ===================\\//=================== ***** *
 */
/**
 * *
 * pag
 * Valor que se recoge para cargar una URL
 */
$pag = $dev->req->getVar('pag');
/**
 * *
 * lang
 * Idioma por defecto del Website
 */
$idioma = strtoupper($dev->req->getVar('lang'));
if (strlen($idioma) > 0) {
    if ($idioma == 'ES') {
        $dev->session->set('lang', 'ES');
    } elseif ($idioma == 'EN') {
        $dev->session->set('lang', 'EN');
    } else
        $dev->session->set('lang', 'ES');
}
if (strlen($dev->session->get('lang')) > 0)
    define("LANG_SELECTED", strtoupper($dev->session->get('lang')));
else
    define("LANG_SELECTED", 'ES');
/**
 * *
 * action
 * Valor que se recoge para cargar una URL
 * Valores para Página:
 * 'noIL' No carga la lógica de index.php
 * 'noIT' No carga la plantilla index.tpl
 * 'noILT' No carga ni index.php ni index.tpl
 * 'noHeader' Carga la lógica pero como plantilla no_header.tpl que no contiene nada. Sirve como marco requerido por la clase ViewPage
 * 'noContent' Carga la lógica pero como plantilla no_content.tpl que únicamente tiene las cabeceras, llamadas a css, js.. e inicia <body></body>
 * ## Si su valor es "KnightsWhoSayNi" es una llamada a una RIA, normalmente, una llamada AJAX. Entonces, mode puede y debe tomar los valores:
 * 'lo' Log-off: para llamadas que no hace falta estar autentificado
 * 'li' Log-in: para llamadas que se tiene que estar conectado
 */
$action = $dev->req->getVar('action');
/**
 * LLamada y estructura de URL ajax de ejemplo: admin/kwsn/lo/sendMenu/[TOKEN]
 */
if ($action == "KnightsWhoSayNi")
    $TYPE_SCRIPT = 'ajax';

/**
 * CHECK_LOGIN_TOKEN es la constante que nos sirve para
 *
 * @var strings
 */
define("CHECK_LOGIN_TOKEN", true);
/*
 * TODO: setSessionIDName especial para Atenea, ver como Generalizar
 */
$dev->access->setSessionIDName(SESSIONIDNAME);
$dev->access->setSessionSecName("clientSec");
$dev->access->setSessionNameName("clientName");
$dev->access->setSessionTokenName("clientToken");
$dev->access->setSessionLastAccessName("clientLastAccess");
$dev->access->setAction('UP');
$dev->access->setFieldUserNameShow('NAME');
$dev->access->setFieldUser('EMAIL');
$dev->access->setFieldUserOpenId('EMAIL');
$dev->access->setFieldPass('PASS');
$dev->access->setFieldRol('ROL');
$dev->access->setFieldLastAccess('SESSION_LAST_ACCESS');
$dev->access->setTableAccess(TABLE_USERS);
/**
 * Si es una Página....
 */
if ($TYPE_SCRIPT == 'page') {
    /**
     * *******
     *
     * ** ***** ===================\\//=================== ***** *
     */
    $vPage = new ViewPage();
    /**
     * ***** ===================//\\=================== ***** *
     */

    /**
     * *******
     * A través de la variable pag que hemos recogido, definimos que cargamos
     * *** ***** ===================\\//=================== ***** *
     */
    $dev->page->assignPage($pag, $action);
    $vPage->displayMeta();
    $vPage->displayVars();
    $vPage->displayLists();
    $vPage->displayFiles();
    $vPage->displayPage();
/**
 * Si es una LLamada Ajax
 */
} elseif ($TYPE_SCRIPT == 'ajax') {
    /**
     * *
     * action
     * ## Si su valor es "KnightsWhoSayNi" es una llamada a una RIA, normalmente, una llamada AJAX.
     * Entonces, mode puede y debe tomar los valores:
     * 'lo' Log-off: para llamadas que no hace falta estar autentificado
     * 'li' Log-in: para llamadas que se tiene que estar conectado
     * 'API': Para llamadas de sistemas externos
     */
    if (IS_APPENGINE === false) {
        $mode = $dev->req->getVar('mode');
        if ($mode == 'api') {
            $_GET['StokenOlif'] = 'API_SEND';
        }
        $token = $dev->req->getVar('StokenOlif');
        require_once CORE_ROOT . CONTROLLERS . DIRECTORY_SEPARATOR . 'ControllerCurl.php';
        $cCurl = ControllerCurl::getInstance();
        $cCurl->assignRIA($pag, $mode, $token);
        $cCurl->assignVars($dev->req->getAllPostVar());
        $cCurl->assignVars($dev->req->getAllGetVar());
        $cCurl->getRIA();
    } else {
        if ($_GET['mode'] == 'api' || $_POST['mode'] == 'api') {
            $_GET['StokenOlif'] = 'API_SEND';
        }
        unset($_POST['mode']);
        unset($_POST['action']);
        unset($_POST['pag']);
        unset($_GET['mode']);
        unset($_GET['action']);
        unset($_GET['pag']);

        $_POST['OLIF_ROOT'] = OLIF_ROOT;
        $_POST['NODE_ROOT'] = NODE_ROOT;
        $_POST['DIR_INDEX'] = WEBSITE_ROOT;
        $data = array_merge($dev->req->getAllPostVar(), $dev->req->getAllGetVar());
        $context = array(
            'http' => array(
                'method' => 'POST', //
                'header' => "Content-Type: application/x-www-form-urlencoded; charset=UTF-8\r\n" . 'Cookie: PHPSESSID=' . session_id() . '; ACSID=' . $_COOKIE["ACSID"] . '; SACSID=' . $_COOKIE["SACSID"] . '; dev_appserver_login=' . $_COOKIE["dev_appserver_login"] . '\r\n',
                'content' => http_build_query($data)
            )
        );
        $context = stream_context_create($context);
        $result = file_get_contents(WEB_URL . NODE_ROOT . RIA_FOLDER . $pag . ".php", false, $context);
        echo $result;
    }
    $mode = $dev->req->getVar('mode');
    if ($mode == 'api') {
        $_GET['StokenOlif'] = 'API_SEND';
    }
} elseif ($TYPE_SCRIPT == 'script') {

    $token = $dev->req->getVar('StokenOlif');
    if ($token != 'API_SEND') {
        if (! $dev->access->checkSessionToken($token, false)) {
            $dev->rise401();
        }
    }
}
