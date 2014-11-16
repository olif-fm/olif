<?php
namespace Olif;

if (! defined('NODE_ROOT'))
    define('NODE_ROOT', '');
$TYPE_SCRIPT = 'TEST';
$exist = false;
$bootstrap = "Bootstrap.php";
$dir = "olif";
$folder = "";
for ($i = 0; $i < 4 && $exist === false; $i ++) {
    if (is_dir($folder . $dir))
        $exist = true;
    else
        $folder .= "../";
}
$bootstrap = $folder . $dir . DIRECTORY_SEPARATOR . $bootstrap;
$DIR_INDEX = str_replace("olif/tests/", "", dirname(__FILE__) . DIRECTORY_SEPARATOR);
require_once ($bootstrap);

class RemoteConnect
{

    public function connectToServer($serverName = null)
    {
        if ($serverName == null) {
            throw new Exception("Este no es un nombre de servidor!");
        }
        $fp = fsockopen($serverName, 80);
        return ($fp) ? true : false;
    }

    public function returnSampleObject()
    {
        return $this;
    }
}

class OlifTest extends \PHPUnit_Framework_TestCase
{

    public $test;

    public function setUp()
    {
        $devm = new ModelDeveloper();
        $devm->getModelDB();

        $this->test = new ControllerDeveloper();
        $this->test->init();
        $this->assertTrue(is_object($this->test) === true);

        $this->assertTrue(get_class($this->test) === "Olif\ControllerDeveloper");
    }

    public function tearDown()
    {}

    /**
     * testDB
     * Necesitamos probar si la instancia se mantiene de un objeto a otro.
     * para ello utilizaremos al controlador Access
     */
    public function testDBConnect()
    {

        /**
         * Errores de conexión
         */
        /*
        ob_start();
        $result = $this->test->db->connect(DBHOST, "NOEXISTE", DBPASS, DBNAME, DBPORT, DBSOCKET);
        $result_msg = ob_get_clean();
        $this->assertTrue($result === false);
        $this->assertTrue($result_msg !== "");

        ob_start();
        $result = $this->test->db->connect(DBHOST, "", DBPASS, DBNAME, DBPORT, DBSOCKET);
        $result_msg = ob_get_clean();
        $this->assertTrue($result === false);
        $this->assertTrue($result_msg !== "");
        */
        /**
         * Si todo ok....
         */
        $result = $this->test->db->connect(DBHOST, DBUSER, DBPASS, DBNAME, DBPORT, DBSOCKET);
        $this->assertTrue($result === true);
        /**
         * Consultas Tabla
         */
        $result = $this->test->db->getFieldsName(TABLE_ROLS);
        $this->assertTrue($result !== false);
    }

    public function testDBNoExist()
    {
        /**
         * Errores de consulta
         */
        ob_start();
        $result = $this->test->db->getFieldsName("tabla_inventada");
        $result_msg = ob_get_clean();
        $this->assertCount(0, $result);
        $this->assertTrue($result_msg !== "");

        ob_start();
        $result = $this->test->db->masterQuery("Blablabla", array());
        $result_msg = ob_get_clean();
        $this->assertTrue($result === null);
        $this->assertTrue($result_msg !== "");
        /**
         * Consultas No existe
         */
        $result = $this->test->db->query(TABLE_ROLS_FIELDS, TABLE_ROLS, "", "ID = ?", "", array(
            'NO-EXISTE'
        ));
        $this->assertCount(0, $result);

        $result = $this->test->db->masterQuery("SELECT " . TABLE_ROLS_FIELDS . " FROM " . TABLE_ROLS . " WHERE ID = ?", array(
            'NO-EXISTE'
        ));
        $this->assertCount(0, $result);

        $result = $this->test->db->queryPaged(TABLE_ROLS_FIELDS, TABLE_ROLS, "", "ID = ?", "", array(
            'NO-EXISTE'
        ), 0);

        $this->assertCount(0, $result['elements']);
    }

    public function testDBInsert()
    {
        /**
         * Insertar
         */
        $this->test->db->setAction('I');
        $this->test->db->setTable(TABLE_ROLS);
        $this->test->db->setIUField('ID', 'NO-EXISTE');
        $this->test->db->setIUField('NAME_ES', 'NO-EXISTE');
        $this->test->db->setIUField('NAME_EN', 'NO-EXISTE');
        $this->test->db->setIUField('STATUS', 1);
        $result = $this->test->db->executeCommand();
        $this->assertTrue($result === true);
    }

    public function testDBUpdate()
    {
        /**
         * Insertar
         */
        $this->test->db->setAction('U');
        $this->test->db->setTable(TABLE_ROLS);
        $this->test->db->setCond('ID', 'NO-EXISTE');
        $this->test->db->setIUField('NAME_ES', 'NO-EXISTE2');
        $this->test->db->setIUField('NAME_EN', 'NO-EXISTE2');
        $result = $this->test->db->executeCommand();
        $this->assertTrue($result === true);
    }

    public function testDBExist()
    {
        /**
         * Consultas Existe
         */
        $result = $this->test->db->query(TABLE_ROLS_FIELDS, TABLE_ROLS, "", "", "", array());
        $this->assertNotCount(0, $result);

        $result = $this->test->db->query(TABLE_ROLS_FIELDS, TABLE_ROLS, "", "ID = ?", "", array(
            'NO-EXISTE'
        ));
        $this->assertNotCount(0, $result);

        $result = $this->test->db->masterQuery("SELECT " . TABLE_ROLS_FIELDS . " FROM " . TABLE_ROLS . " WHERE ID = ?", array(
            'NO-EXISTE'
        ));
        $this->assertNotCount(0, $result);

        $result = $this->test->db->queryPaged(TABLE_ROLS_FIELDS, TABLE_ROLS, "", "ID = ?", "", array(
            'NO-EXISTE'
        ), 0);
        $this->assertNotCount(0, $result['elements']);
        /**
         * Errores de consulta
         */
        ob_start();
        $result = $this->test->db->showConstructQuery();
        $result_msg = ob_get_clean();
        $this->assertTrue($result_msg !== "");
    }

    public function testDBDelete()
    {
        /**
         * Borrar
         */
        $this->test->db->setAction('D');
        $this->test->db->setTable(TABLE_ROLS);
        $this->test->db->setCond('ID', 'NO-EXISTE');
        $result = $this->test->db->executeCommand();
        $this->assertTrue($result === true);
    }

    public function testControllerAccess()
    {
        /**
         * Comprobamos la clase
         */
        $this->assertTrue(get_class($this->test->access) === "Olif\ControllerAccess");
        /**
         * Inicializamos las variables de sesión como en Bootstrap.php
         */
        $this->test->access->setSessionIDName(SESSIONIDNAME);
        $this->test->access->setSessionSecName("clientSec");
        $this->test->access->setSessionNameName("clientName");
        $this->test->access->setSessionTokenName("clientToken");
        $this->test->access->setSessionLastAccessName("clientLastAccess");
        $this->test->access->setAction('UP');
        $this->test->access->setFieldUserNameShow('NAME');
        $this->test->access->setFieldUser('EMAIL');
        $this->test->access->setFieldUserOpenId('EMAIL');
        $this->test->access->setFieldPass('PASS');
        $this->test->access->setFieldRol('ROL');
        $this->test->access->setFieldLastAccess('SESSION_LAST_ACCESS');
        $this->test->access->setTableAccess(TABLE_USERS);

        $this->test->access->setSessionID('MySessionID');
        $this->assertTrue($this->test->access->getSessionID() === 'MySessionID');

        $this->test->access->setSessionSec('MySessionSec');
        $this->assertTrue($this->test->access->getSessionSec() === 'MySessionSec');

        $this->test->access->setSessionToken('MySessionToken');
        $this->assertTrue($this->test->access->getSessionToken() === 'MySessionToken');

        $this->test->access->setSessionName('MySessionName');
        $this->assertTrue($this->test->access->getSessionName() === 'MySessionName');

        $this->test->access->setSessionLastAccess('MySessionName');
        $this->assertTrue($this->test->access->getSessionName() === 'MySessionName');

        $result = $this->test->access->loginAnonymous(true);
        $this->assertTrue($result === false);

        $result = $this->test->access->loginAnonymous();
        $this->assertTrue($result === false);
    /**
     * TODO: Verificar si existe tabla de usuarios, si es así probar login y demás
     */
    }

    public function testControllerRequest()
    {
        /**
         * Comprobamos la clase
         */
        $this->assertTrue(get_class($this->test->req) === "Olif\ControllerRequest");

        $testEmpty = $this->test->req->getVar('empty');

        $this->assertTrue($testEmpty === false);

        $_GET['test1'] = 'Prueba1';
        $test1 = $this->test->req->getVar('test1');
        $this->assertTrue($test1 === 'Prueba1');

        $_POST['test2'] = 'Prueba2';
        $test2 = $this->test->req->getVar('test2');
        $this->assertTrue($test2 === 'Prueba2');

        $test3 = $this->test->req->getAllGetVar();
        $this->assertTrue($test3['test1'] === 'Prueba1');

        $test4 = $this->test->req->getAllPostVar();
        $this->assertTrue($test4['test2'] === 'Prueba2');

        $_GET['test3'] = array(
            'Prueba3',
            'Prueba4'
        );

        $test5 = $this->test->req->getVar('test3');
        $this->assertTrue($test5[0] === 'Prueba3');

        $testXss = $this->test->req->detectXSS('<script>alert("test XSS");</script>');
        $this->assertTrue($testXss === true);
        /**
         * XSS injections
         */
        $_POST['xss'] = '<script>alert("test XSS");</script>';
        ob_start();
        $testXss = $this->test->req->getVar('xss');
        $result_msg = ob_get_clean();

        $this->assertTrue($testXss === false);
        $this->assertTrue($result_msg !== "");
        /**
         * XSS injections
         */
        $_GET['xss'] = array(
            'Prueba5',
            array(
                'Prueba6',
                '<script>alert("test XSS");</script>'
            )
        );
        ob_start();
        $testXss = $this->test->req->getVar('xss');
        $result_msg = ob_get_clean();

        $this->assertTrue($testXss === false);
        $this->assertTrue($result_msg !== "");
    }

    public function testControllerCookie()
    {
        /**
         * Comprobamos la clase
         */
        $this->assertTrue(get_class($this->test->cookie) === "Olif\ControllerCookie");

        $testEmpty = $this->test->cookie->get('empty');
        $this->assertTrue($testEmpty === null);
        ob_start();
        $test1 = $this->test->cookie->set('test1', 'valor1');
        $result_msg = ob_get_clean();
        // $this->assertTrue($test1 === false);

        $test1 = $this->test->cookie->destroy('test1');
        $this->assertTrue($test1 === false);

        $this->test->cookie->end();
    }

    public function testControllerFormat()
    {
        /**
         * Comprobamos la clase
         */
        $this->assertTrue(get_class($this->test->format) === "Olif\ControllerFormat");

        $date = $this->test->format->dateToSQL('23/10/2014');
        $this->assertTrue($date === '2014-10-23');

        $date = $this->test->format->dateToTPL('2014-10-23');
        $this->assertTrue($date === '23/10/2014');

        $date = $this->test->format->dateToTPL_beauty('2014-01-23');
        $date = $this->test->format->dateToTPL_beauty('2014-02-23');
        $date = $this->test->format->dateToTPL_beauty('2014-03-23');
        $date = $this->test->format->dateToTPL_beauty('2014-04-23');
        $date = $this->test->format->dateToTPL_beauty('2014-04-23', false, false);
        $date = $this->test->format->dateToTPL_beauty('2014-05-23');
        $date = $this->test->format->dateToTPL_beauty('2014-06-23');
        $date = $this->test->format->dateToTPL_beauty('2014-07-23');
        $date = $this->test->format->dateToTPL_beauty('2014-07-23', false, false);
        $date = $this->test->format->dateToTPL_beauty('2014-08-23');
        $date = $this->test->format->dateToTPL_beauty('2014-09-23');
        $date = $this->test->format->dateToTPL_beauty('2014-10-23');
        $date = $this->test->format->dateToTPL_beauty('2014-11-23');
        $date = $this->test->format->dateToTPL_beauty('2014-11-23', false, false);
        $date = $this->test->format->dateToTPL_beauty('2014-12-24'); // Miércoles
        $date = $this->test->format->dateToTPL_beauty('2014-12-23');
        $this->assertTrue($date === 'martes 23 de Diciembre');

        $text = $this->test->format->clearOnlyChars('bombón dulce');
        $this->assertTrue($text === 'bombn_dulce');

        $text = $this->test->format->textAreaToTPL('bombón<br>dulce');
        // $this->assertTrue($text === 'bombón\ndulce');
        $text = $this->test->format->textAreaToInput('bombón<br>dulce');
        // $this->assertTrue($text === 'bombón\ndulce');
        $date = $this->test->format->sumDate('23/10/2014', 2);
        $this->assertTrue($date === '25/10/2014');

        $date = $this->test->format->sumDate('2014-10-23', 2);
        $this->assertTrue($date === '25/10/2014');

        $number = $this->test->format->numberToSQL('23,25');
        $this->assertTrue($number === '23.25');

        $number = $this->test->format->numberToTPL('23.2599');
        $this->assertTrue($number === '23,26');

        $text = $this->test->format->noAcents('bombón');
        $this->assertTrue($text === 'bombon');

        $text = $this->test->format->clearTitleToUrl('bombón? dulce');
        $this->assertTrue($text === 'bombon_dulce');

        $text = $this->test->format->cutText('123456789', 4, true);
        $this->assertTrue($text === '1234...');

        $text = $this->test->format->cutText('123456 789', 4);
        $this->assertTrue($text === '123456...');

        $text = $this->test->format->cutText('<p>hola</p>', 4);
        $this->assertTrue($text === '<p>hola</p>');
        $text = $this->test->format->cutText('<p><a href="http://link.es">hola</a> Qué tal?</p>', 4);
        $this->assertTrue($text === '<p><a href="http://link.es"...</p>');
    }

    public function testControllerPage()
    {
        /**
         * Comprobamos la clase
         */
        $this->assertTrue(get_class($this->test->page) === "Olif\ControllerPage");
    }

    public function testControllerPageError404()
    {
        ob_start();
        $result = $this->test->page->assignPage("Inventada-no-existe-404", "");
        $result_msg = ob_get_clean();
        $this->assertTrue($result_msg !== "");
    }

    public function testControllerPageExist()
    {
        $this->test->page->assignPage("inicio", "");
        // $this->test->page->assignPage("", "");
    }

    public function testViewPage()
    {
        $vPage = new ViewPage();
        ob_start();
        $vPage->displayMeta();
        $vPage->displayVars();
        $vPage->displayLists();
        $vPage->displayFiles();
        $vPage->displayPage();
        $result_msg = ob_get_clean();
        $this->assertTrue($result_msg !== "");
    }

    public function testDBClose()
    {
        /**
         * Cerrar
         */
        $result = $this->test->db->close();
        $this->assertTrue($result !== false);
    }

    /**
     * testSingleton
     * Necesitamos probar si la instancia se mantiene de un objeto a otro.
     * para ello utilizaremos al controlador Access
     */
    public function testSingleton()
    {
        $this->test->access->setSessionID('MySessionID');
        $this->assertTrue($this->test->access->getSessionID() === 'MySessionID');

        $this->test2 = new ControllerDeveloper();
        $this->test2->init();

        $this->assertTrue($this->test2->access->getSessionID() === 'MySessionID');
    }
}
