<?php
/**
 * ControllerORM
 * @version V 2.2
 * @author Alberto Vara (C) Copyright 2014
 * @package OLIF.ControllerORM
 *
 * @desc Manage Modules and objects
 */
namespace Olif;

require_once CORE_ROOT . LIBS . DIRECTORY_SEPARATOR . "Iorm.php";

require_once CORE_ROOT . CONTROLLERS . DIRECTORY_SEPARATOR . "ControllerApp.php";

abstract class ControllerORM extends ControllerApp implements IcontrollerORM
{

    /**
     * ID del objecto seleccionado
     *
     * @var unknown
     */
    protected $id = "";

    /**
     * OBJECT del objecto seleccionado
     *
     * @var unknown
     */
    protected $ob = "";

    /**
     * table: tabla del objecto seleccionado
     *
     * @var unknown
     */
    protected $table = "";

    /**
     * table_prefix: prefijo tabla del objecto seleccionado
     *
     * @var unknown
     */
    protected $table_prefix = "";

    /**
     * fields: campos del objecto seleccionado
     *
     * @var unknown
     */
    protected $fields = "";

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
    protected $conds = "";

    /**
     * $tables:
     *
     * @var unknown
     */
    protected $tables = "";

    /**
     * $tables: condiciones que intervienen con el objecto seleccionado
     *
     * @var unknown
     */
    protected $ormSession = "";

    public function __construct()
    {
        // parent::__construct();
        $this->ormSession = 'orm_' . $this->table;
        $this->getModel();
        $this->model->getModelORM($this->ormSession);
        // var_dump($this->model);
        $this->model->{$this->ormSession}->init($this->table, $this->table_prefix, $this->fields, $this->joins, $this->conds);
        $this->model->{$this->ormSession}->getFieldsName();
    }

    public function __set($name, $value)
    {
        // echo "Setting '$name' to '$value'\n";
        $this->ob[strtoupper($name)] = $value;
    }

    public function __get($name)
    {
        return $this->ob[strtoupper($name)];
    }

    public function __call($name, $arguments)
    {
        // Note: value of $name is case sensitive.
        echo "[" . get_called_class() . "]Calling object method '$name' " . implode(', ', $arguments) . " <b>NOT EXIST</b>\n";
    }

    /**
     * save
     * Si return true devuelve el resultado. Si es false, devuelve todos los valores de la opereación del modelo
     * @param boolean $return
     * @return string|boolean
     */
    public function save($return = true, $forceInsert = false)
    {
        /**
         * model return an array:
         * $result=Array(
         * "result" => false, BOOL
         * "action" => "", ACTION = UPDATE, INSERT OR NONE
         * "ID" => "", ID of action in DDBB
         * );
         */
        $result = $this->model->{$this->ormSession}->save($this->ob, $forceInsert);
        if (is_array($result) && $result['result'] !== false) {
            $this->set($result['ID']);
        }
        if ($return === true) {
            return $result['result'];
        } else {
            return $result;
        }
    }

    public function delete($return = true)
    {
        $result = $this->model->{$this->ormSession}->delete($this->ob);
        if ($return === true) {
            return $result['result'];
        } else {
            return $result;
        }
    }

    public function set($id = "")
    {
        $this->setId($id);
        // echo "ID: ".$id;
        $result = $this->model->{$this->ormSession}->get($this->id);
        // var_dump($result);
        if (count($result) > 0) {
            $this->ob = $result;
        } else {
            $this->ob = false;
            return false;
        }
    }

    /**
     * Condiciones extra cuando hacemos un set();
     *
     * @param unknown $conds
     * @param unknown $params
     */
    public function setExtraConds($conds, $params = array())
    {
        $this->model->{$this->ormSession}->getExtraConds($conds, $params);
    }

    public function setId($id = "")
    {
        $this->id = $id;
        $this->ob['ID'] = $id;
    }

    public function getId()
    {
        return $this->id;
    }

    public function get($field = "")
    {
        if (strlen($field) > 0) {
            if (in_array($field, $this->ob)) {
                return $this->ob[$field];
            } else {
                return false;
            }
        } else {
            return $this->ob;
        }
    }

    public function assign()
    {
        $this->getControllerPage();
        $result = $this->get();
        if ($result !== false && $result !== null) {
            // $num_results = count($result);
            $fields = array();
            foreach ($result as $clave => $valor) {
                $fields[$clave] = $valor;
            }
            $this->page->assignVars($fields);
        }
    }

    public function getlist($filters, $orderby, $pag, $total_per_page, $show_all = false)
    {
        $result = $this->model->{$this->ormSession}->getlist($filters, $orderby, $pag, $total_per_page, $show_all);
        if ($result !== false) {
            return $result;
        } else
            return false;
    }

    public function assignlist($blockName, $filters, $orderby, $show_all = false, $assignList = true)
    {
        $this->getControllerPage();
        $this->getControllerModule('tables', 'ControllerTables', 'tables');
        /**
         * ELEMENTOS DE PAGINACIÓN: Número de elementos por página
         */
        $pag = $this->tables->getPag();
        $total_per_page = $this->tables->getTotalPerPage();
        $result = $this->getlist($filters, $orderby, $pag, $total_per_page, $show_all);
        if ($result !== false && $assignList === true) {
            $num_results = count($result['elements']);
            $this->page->assignVar("num_pages", $result['num_pages']);
            $this->page->assignVar("total_results", $result['num_elements']);
            for ($i = 0; $i < $num_results; $i ++) {
                $fields = array();
                foreach ($result['elements'][$i] as $clave => $valor) {
                    $fields[$clave] = $valor;
                }
                $this->page->assignList($blockName, $fields);
            }
            $this->tables->setPagination($result);
        } else {
            return $result;
        }
    }

    public function assignlistInput($blockName, $selectedId, $orderby, $filters = array())
    {
        $this->getControllerPage();
        $result = $this->model->{$this->ormSession}->getlist($filters, $orderby, 0, 200, false);
        if ($result !== false) {
            $num_results = count($result['elements']);
            for ($i = 0; $i < $num_results; $i ++) {
                $fields = array();
                foreach ($result['elements'][$i] as $clave => $valor) {
                    $fields[$clave] = $valor;
                }
                if ($selectedId == $fields['ID']) {
                    $fields['SELECTED'] = 1;
                } else {
                    $fields['SELECTED'] = 0;
                }
                $this->page->assignList($blockName, $fields);
            }
        } else
            return false;
    }

    public function checkQuery()
    {
        $this->model->{$this->ormSession}->checkQuery();
    }
}
