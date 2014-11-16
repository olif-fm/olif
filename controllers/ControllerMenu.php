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
}
