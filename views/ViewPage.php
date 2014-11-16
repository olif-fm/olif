<?php
/**
 * ViewPage
 * @version V 1.7
 * @author Alberto Vara (C) Copyright 2013
 * @package OLIF.ViewPage
 *
 * DEPENDS/REQUIRED:
 * + models/ModelPage
 */
namespace Olif;

class ViewPage
{

    private $model;

    private $controller;

    private $template;

    public function __construct()
    {
        $this->controller = ControllerPage::getInstance();
        $this->model = ModelPage::getInstance();
        require_once (CORE_ROOT . THREEPARTY_REQ . "template" . DIRECTORY_SEPARATOR . "Template.php");
        $this->template = new \Template(WEBSITE_ROOT . 'templates' . DIRECTORY_SEPARATOR, WEBSITE_ROOT . 'cache/');
    }

    public function displayMeta()
    {

        $this->displayVar('page_title', $this->controller->getTitle());
        $this->displayVar('meta_title', $this->controller->getMetaTitle());
        $this->displayVar('meta_desc', $this->controller->getMetaDesc());
        $this->displayVar('meta_keywords', $this->controller->getMetaKeywords());
    }

    public function displayList($name, $params = array())
    {
        $this->template->assign_block_vars($name, $params);
    }

    public function displayLists()
    {
        $this->template->assign_blocks($this->model->lists);
    }

    public function displayVar($name, $value)
    {
        // echo "VAR: ".$name." VALUE: ".$value."<br>";
        $this->template->assign_var($name, $value);
    }

    public function displayVars($debug = false)
    {
        foreach ($this->model->vars as $name => $value) {
            // echo "ENTRA";
            $this->displayVar($name, $value);
            /*
             * $this->template->assign_vars(array( $name => ($value) ) );
             */
        }
    }

    public function displayFiles()
    {
        foreach ($this->model->varsFilesTpls as $var => $file) {
            $this->template->set_filenames(array(
                $var => $file
            ));
            $content = $this->template->assign_display($var);
            $this->template->assign_var($var, $content);
        }
    }

    public function displayPage()
    {
        // Si hay index:
        if ($this->controller->getAction() == 'noHeader') {
            $tpl_name = 'no_header';
            $tpl = $tpl_name . ".tpl";
            $this->template->set_filenames(array(
                'index' => $tpl
            ));
        } elseif ($this->controller->getAction() == 'noContent') {
            $tpl_name = 'no_content';
            $tpl = $tpl_name . ".tpl";
            $this->template->set_filenames(array(
                'index' => $tpl
            ));
        } elseif ($this->controller->getAction() != 'noIT') {
            $tpl_name = $this->controller->getFileTemplateIndex();

            $tpl = $tpl_name . ".tpl";
            $this->template->set_filenames(array(
                'index' => $tpl
            ));
        }
        $tpl_name = $this->controller->getFileTemplate();
        //echo "[ERROR " . get_class($this) . "::" . __FUNCTION__ . "::" . __LINE__ . "]<br>";
        //var_dump($tpl_name);
        $tpl = $tpl_name . ".tpl";
        $this->template->set_filenames(array(
            'contenido' => $tpl
        ));
        $content = $this->template->assign_display('contenido');
        $this->template->assign_var("contenido", $content);
        $this->template->display('index');
    }
}
