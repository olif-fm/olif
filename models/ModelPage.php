<?php
/**
 * ModelPage
 * @version V 1.7
 * @copyright Alberto Vara (C) Copyright 2013
 * @package OLIF.ModelPage
 *
 * Modelo de la página. Se controla los datos de las páginas de la web
 * DEPENDS/REQUIRED:
 * + models/ModelApp
 */
namespace Olif;

require_once CORE_ROOT . MODELS . DIRECTORY_SEPARATOR . "ModelApp.php";

class ModelPage extends ModelApp
{

    /**
     * vars
     * Variables que se asignarán a las plantillas
     */
    public $vars = array();

    /**
     * Variables-ficheros que se asignarán a las plantillas
     */
    public $varsFilesTpls = array();

    /**
     * lists
     * Listados que se asignarán en las plantillas
     */
    public $lists = array(
        '.' => array(
            0 => array()
        )
    );

    /**
     * * setVar
     * Asignamos las variables que luego se pasarán a la vista
     *
     * @param
     *            name string <p>
     *            Nombre de la variable
     *            </p>
     * @param
     *            value string <p>
     *            Valor de la variable
     *            </p>
     *
     * @access public
     */
    public function setVar($name, $value)
    {
        $this->vars[$name] = $value;
    }

    /**
     * setVarFileTemplate
     * Cargar una plantilla en una variable del la plantilla
     *
     * @param name $var
     *            <p>
     *            Nombre de la variable
     *            </p>
     * @param
     *            fullPathFile string <p>
     *            Ruta relativa del fichero
     *            </p>
     *
     * @access public
     */
    public function setVarFileTemplate($var, $pathFile)
    {
        $this->varsFilesTpls[$var] = $pathFile;
    }

    /**
     * getDictionary
     */
    public function getDictionary($lang)
    {
        $this->checkPages();
        $result = $this->db->query("ID," . $lang, TABLE_DICTIONARY, '', '', '', array());
        if (count($result) > 0) {
            return $result;
        } else {
            return false;
        }
    }
}
