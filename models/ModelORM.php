<?php
/**
 * ModelSeason
 * @version V 0.1
 * @author Alberto Vara (C) Copyright 2014
 * @package OLIF.ModelSeason
 *
 * @desc Manage Seasons
 */
namespace Olif;

require_once CORE_ROOT . MODELS . DIRECTORY_SEPARATOR . "ModelApp.php";

class ModelORM extends ModelApp
{

    /**
     * table: tabla del objecto seleccionado
     *
     * @var unknown
     */
    var $table = "";

    /**
     * table_prefix: prefijo tabla del objecto seleccionado
     *
     * @var unknown
     */
    var $table_prefix = "";

    /**
     * fields: campos del objecto seleccionado
     *
     * @var unknown
     */
    var $fields = array();

    /**
     * fieldsQuery: campos del objecto seleccionado
     *
     * @var unknown
     */
    var $fieldsQuery = "";

    /**
     * joins: uniones que intervienen en la query del objecto seleccionado
     *
     * @var unknown
     */
    var $joins = "";

    /**
     * $conds: condiciones que intervienen con el objecto seleccionado
     *
     * @var unknown
     */
    var $conds = "";

    /**
     * $condsExtra: condiciones que intervienen con el objecto seleccionado
     *
     * @var unknown
     */
    var $condsExtra = "";

    /**
     * $paramsExtra:
     *
     * @var unknown
     */
    var $paramsExtra = array();

    /**
     * __construct
     * Instanciamos el objeto de base de datos ($db) para atacar a la tabla
     */
    public function __construct()
    {
        parent::__construct();
        $this->getModelDB();
    }
    /*
     * public function __call($name, $arguments) {
     * // Note: value of $name is case sensitive.
     * die("[".get_called_class()."]Calling object method '$name' "
     * . implode(', ', $arguments). " <b>NOT EXIST</b>\n");
     * }
     * public function __set($name, $value) {
     * echo "Setting '$name' to '$value'\n";
     * //$this->ob[strtoupper($name)] = $value;
     * }
     * public function __get($name) {
     * echo "[".get_called_class()."]Calling object method '$name' ";
     * //var_dump($this->ob);
     * //if (array_key_exists(strtoupper($name), $this->ob)) {
     * //return $this->ob[strtoupper($name)];
     * //}
     * $trace = debug_backtrace();
     * trigger_error(
     * 'Undefined property via __get(): ' . $name .
     * ' in ' . $trace[0]['file'] .
     * ' on line ' . $trace[0]['line'],
     * E_USER_NOTICE);
     * return null;
     * }
     */
    public function init($table, $table_prefix, $fields, $joins, $conds)
    {
        $this->table = $table;
        $this->table_prefix = $table_prefix;
        $this->fieldsQuery = $fields;
        $this->joins = $joins;
        $this->conds = $conds;
        $this->getFieldsName();
    }

    public function getFieldsName()
    {
        $result = $this->db->getFieldsName($this->table);
        $numFields = count($result);
        for ($i = 0; $i < $numFields; $i ++) {
            $this->fields[$result[$i]["Field"]] = "";
        }
        // var_dump($this->fields);
    }

    public function checkQuery()
    {
        echo "FIELDS: " . $this->fieldsQuery . "<br>\n";
        echo "TABLE: " . $this->table . "<br>\n";
        echo "TABLE PREFIX: " . $this->table_prefix . "<br>\n";
        echo "JOINS: " . $this->joins . "<br>\n";
        echo "CONDS: " . $this->conds . "<br>\n";
        echo "CONDS EXTRA: " . $this->condsExtra . "<br>\n";
        echo "PARAMS: " . print_r($this->paramsExtra) . "<br>\n";
    }

    public function save($object, $forceInsert)
    {
        $result = array(
            "result" => false,
            "response" => "",
            "action" => "",
            "ID" => ""
        );
        if (strlen($object['ID']) == 0) {
            $ID = uniqid();
            $this->db->setAction('I');
            $this->db->setIUField('ID', $ID);
            $blank = false;
            $result['action'] = "INSERT";
        } else {
            if ($forceInsert === true) {
                $ID = $object['ID'];
                $this->db->setAction('I');
                $this->db->setIUField('ID', $object['ID']);
                $blank = false;
                $result['action'] = "INSERT";
            } else {
                $ID = $object['ID'];
                $this->db->setAction('U');
                $this->db->setCond('ID', $object['ID']);
                $blank = true;
                $result['action'] = "UPDATE";
            }
        }
        // $this->checkQuery();
        $this->db->setTable($this->table);
        // var_dump($object);
        foreach ($object as $key => $value) {
            if ($key != 'ID' && key_exists($key, $this->fields)) {
                $null = false;

                // echo "<br>KEY: ".$key." VALUE.".$value." ";
                if (preg_match("/fk_/i", $key)) {
                    $null = true;
                    // echo "IS NULL";
                }

                $this->db->setIUField($key, $value, $null, $blank);
            }
        }
        if ($this->db->executeCommand()) {
            $result['result'] = true;
            $result['ID'] = $ID;
        }
        return $result;
    }

    public function delete($object)
    {
        $result = array(
            "result" => false,
            "action" => "DELETE",
            "ID" => ""
        );
        if (strlen($object['ID']) > 0) {
            $ID = $object['ID'];
            $this->db->setAction('D');
            $this->db->setCond('ID', $object['ID']);
            $this->db->setTable($this->table);
            if ($this->db->executeCommand()) {
                $result['result'] = true;
                $result['ID'] = $ID;
            }
        }
        return $result;
    }

    public function getExtraConds($conds, $params = array())
    {
        $this->condsExtra = $conds;
        $this->paramsExtra = $params;
    }

    public function get($id)
    {
        // $this->checkQuery();
        $result = $this->db->query($this->fieldsQuery, $this->table . " " . $this->table_prefix, $this->joins, $this->condsExtra . ((strlen($this->condsExtra) > 0) ? " AND " : '') . " " . $this->conds . ((strlen($this->conds) > 0) ? " AND " : '') . " " . $this->table_prefix . ((strlen($this->table_prefix) > 0) ? "." : '') . "ID = ?", "", array_merge($this->paramsExtra, array(
            $id
        )));
        if (count($result) > 0) {
            return $result[0];
        } else {
            return $this->fields;
        }
    }

    public function getlist($filters = array(), $ordeby = "", $pag = 0, $total_per_page = 10, $show_all = false)
    {
        $params = array();
        $COND = "";
        $ORDER = "";
        if (strlen($ordeby) > 0) {
            $ORDER = $ordeby;
        }
        if ($show_all !== false) {
            $this->conds = "";
        }
        // var_dump($filters);
        if (is_array($filters)) {
            /*
             * if (isset($filters['CAT']) && strlen($filters['CAT']) > 0) {
             * $COND .= $sep." B.CAT = ?";
             * $params = array($filters['CAT']);
             * }
             */
            if ((isset($filters['COND']) && strlen($filters['COND']) > 0)) {
                $COND .= $filters['COND'];
                $params = $filters['PARAMS'];
                // echo $COND;
                // var_dump($params);
            }
        }

        $result = $this->db->queryPaged($this->fieldsQuery, $this->table . " " . $this->table_prefix, $this->joins, $this->conds . ((strlen($this->conds) > 0 && strlen($COND) > 0) ? " AND " : '') . $COND, $ORDER, $params, $pag, $total_per_page, 2);
        if (count($result['elements']) > 0) {
            return $result;
        } else {
            return false;
        }
    }
}
