<?php
/**
 * ControllerPayment
 * @version V 0.1
 * @author Alberto Vara (C) Copyright 2014
 * @package OLIF.ControllerPayment
 *
 * @desc
 */
namespace Olif;

require_once CORE_ROOT . CONTROLLERS . DIRECTORY_SEPARATOR . "ControllerORM.php";

class ControllerMenu extends ControllerORM
{

    /**
     * table: tabla del objecto seleccionado
     *
     * @var unknown
     */
    protected $table = TABLE_MENU;

    /**
     * table_prefix: prefijo tabla del objecto seleccionado
     *
     * @var unknown
     */
    protected $table_prefix = "C";

    /**
     * fields: campos del objecto seleccionado
     *
     * @var unknown
     */
    protected $fields = TABLE_MENU_FIELDS;

    /**
     * joins: uniones que intervienen en la query del objecto seleccionado
     *
     * @var unknown
     */
    protected $joins = "";

    /**
     * $conds: condiciones que intervienen con el objecto seleccionado
     *
     * @var unknown
     */
    protected $conds = "STATUS = '1' ";

    public function getBreadCrumbs($setInicio = true)
    {
        $menu = $this->getlist('', 'ORDER BY POSITION ASC', 0, 1000);
        //$menu = $this->getMenu();
        $encontrado = false;
        for ($i = 0; $i < $menu['num_elements'] && $encontrado === false; $i ++) {
            if ($menu['elements'][$i]['ID'] == $this->id) {
                $encontrado = true;
            }
        }
        $breadcrums = array();
        $breadcrums[] = $this->get();
        if ($i > 0) {
            $num_elements = $i - 1;
            $actual_level = $this->lvl;
            for ($i = $num_elements; $i >= 0; $i --) {
                if ($menu['elements'][$i]['LVL'] < $actual_level) {
                    $actual_level = $menu['elements'][$i]['LVL'];
                    $breadcrums[] = $menu['elements'][$i];
                }

                if ($this->url != 'inicio' && $menu['elements'][$i]['URL'] == 'inicio') {
                    if ($setInicio == true)
                        $breadcrums[] = $menu['elements'][$i];
                }
            }
        }
        $breadcrums = array_reverse($breadcrums);
        return $breadcrums;
    }
}
