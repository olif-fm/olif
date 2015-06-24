<?php
/**
 * ModelDB
 * @version V 1.7
 * @author Alberto Vara (C) Copyright 2013
 * @package OLIF.ModelDB
 *
 * Interact with DDBB
 * DEPENDS/REQUIRED:
 * + libs/iDB
 * + models/modelApp
 */
namespace Olif;

require_once CORE_ROOT . LIBS . DIRECTORY_SEPARATOR . "Idb.php";

require_once CORE_ROOT . MODELS . DIRECTORY_SEPARATOR . "ModelApp.php";

class ModelDB extends ModelApp implements Idb
{

    /**
     * $h_db
     * Objeto donde se instancia la clase de BBDD
     */
    private $h_db;

    /**
     * $con_id
     * variable con la ID de la sesión
     */
    private $con_id = "";

    /**
     * $link
     * Objeto que recibe la conexión mysqli
     */
    public $link;

    /**
     * $actions
     * Posibles acciones del sistema
     */
    public $actions = array(
        'I' => 'INSERT',
        'U' => 'UPDATE',
        'D' => 'DELETE'
    );

    /**
     * $action
     * Variable que guarda la accion a realizar
     */
    public $action;

    /**
     * Variable que guarda los parámetros para contruir la query para UPDATE e INSERT
     *
     * @param array $params
     */
    public $params = array();

    /**
     * Variable que guarda las condiciones de una query para UPDATE o DELETE
     *
     * @param string $conds
     *
     */
    public $conds = "";

    /**
     * $params
     * Variable que guarda los parámetros para las condiciones de la query para UPDATE o DELETE
     */
    public $params_cond = array();

    /**
     * Variable que guarda la pabla donde atacar
     *
     * @param string $table
     */
    public $table = "";

    /**
     *
     * @param string $order
     */
    public $order = "";

    /**
     *
     * @param string $join
     */
    public $join = "";

    /**
     * $table
     * Variable que guarda la pabla donde atacar
     */
    protected $fields = array();

    /**
     * $lastID
     * Última ID insertada
     */
    private $lastID;

    /**
     * connect
     * Crea conexión SQL
     */
    public function connect($dbhost, $dbuser, $dbpass, $dbname, $port, $socket, $return_error = false)
    {
        $result = false;
        if (strlen($dbuser) == 0) {
            $texto_error = "[ERROR " . get_class($this) . "::" . __FUNCTION__ . "::" . __LINE__ . "] ERROR DE CONEXIÓN A BASE DE DATOS: \n";
            $texto_error .= "<br>No se ha seleccionado usuario";
            $this->sendError($texto_error);
        } else {
            if (! defined('DDBBTYPE') || DDBBTYPE == 'MYSQL' || DDBBTYPE == 'APPENGINE') {
                require_once CORE_ROOT . MODELS . DIRECTORY_SEPARATOR . "ModelDBMySQL.php";
                $this->h_db = ModelDBMySQL::getInstance();
                $result = $this->h_db->connect($dbhost, $dbuser, $dbpass, $dbname, $port, $socket, $return_error);
            } elseif (DDBBTYPE == 'PDO') {
                // TODO: NO IMPLEMENTADO
                $texto_error = "[ERROR " . get_class($this) . "::" . __FUNCTION__ . "::" . __LINE__ . "] PDO no implementado: \n";
                $this->sendError($texto_error);
            } else {
                $texto_error = "[ERROR " . get_class($this) . "::" . __FUNCTION__ . "::" . __LINE__ . "] No existe o no se ha seleccionado motor de BBDD: \n";
                $this->sendError($texto_error);
            }
        }
        return $result;
    }

    public function close()
    {
        return $this->h_db->close();
    }

    private function getConectionID()
    {
        $result = $this->masterQuery('SELECT CONNECTION_ID() CONNECTION', array());
        if (strlen($result[0]['CONNECTION']) > 0) {
            if (strlen($this->con_id) == 0)
                $this->con_id = $result[0]['CONNECTION'];
            return $result[0]['CONNECTION'];
        } else {
            return false;
        }
    }

    public function getFieldsName($table)
    {
        /* Obtener la información de campo de todas las columnas */
        return $this->masterQuery("SHOW COLUMNS FROM " . $table);
    }

    public function executeQuery($debug = false)
    {
        $result = $this->query($this->getFields('all', 'string'), $this->getTable(), $this->getJoin(), $this->getConds(), $this->getOrder(), $this->params_cond, $debug = false);
        $this->removeQuery();
        return $result;
    }

    /**
     * query
     *
     * @param
     *            columns: columnas de las tablas que se van a seleccionar en la consulta SQL
     * @param
     *            table: tabla que se seleccionará en la consulta
     * @param
     *            join: reglas para unir varias tablas
     * @param
     *            condition condiciones de unión
     * @param
     *            order condiciones de ordenación
     * @param
     *            params: parámetros de la consulta SQL
     * @param
     *            debug: devuelve array con los datos de la consulta SQL
     */
    public function query($columns, $table, $join = "", $condition = "", $order = "", $parms = array(), $debug = false)
    {
        return $this->h_db->query($columns, $table, $join, $condition, $order, $parms, $debug);
    }

    public function masterQuery($sql, $parms = array(), $returnResults = true, $debug = false)
    {
        return $this->h_db->masterQuery($sql, $parms, $returnResults, $debug);
    }

    public function queryPaged($columns, $table, $join, $condition, $order, $parms, $pag_show, $num_elements_page = 5, $num_block_pages = 3)
    {
        if (strpos($join, 'GROUP BY') !== false) {
            $result_page = $this->query("COUNT(*) NUM_ELEMENTS", $table, '', $condition, '', $parms, false);
        } else {
            $result_page = $this->query("COUNT(*) NUM_ELEMENTS", $table, $join, $condition, $order, $parms, false);
        }
        $total_block = ($num_block_pages * 2);
        if (! is_numeric($pag_show))
            $pag_show = 0;
        if ($pag_show < 0)
            $pag_show = 0;
            /* Calcular número de páginas */
        $result_final['num_pages'] = ceil($result_page[0]['NUM_ELEMENTS'] / $num_elements_page);
        /* Validar que la página a ver no es mayor que el número de páginas */
        if ($pag_show >= $result_final['num_pages'] && $pag_show > 0)
            $pag_show = $result_final['num_pages'] - 1;
        $in = $pag_show * $num_elements_page;
        $fin = $num_elements_page;
        $parms[] = $in;
        $parms[] = $fin;
        /* LIMITAMOS LOS RESULTADOS A LA PÁGINA ACTUAL */
        $result = $this->query($columns, $table, $join, $condition, $order . " LIMIT ?,?", $parms, false);

        $result_final['elements'] = $result;
        $result_final['num_elements'] = $result_page[0]['NUM_ELEMENTS'];
        $result_final['actual_page'] = $pag_show;
        $result_final['prev_page'] = $pag_show - 1;
        $result_final['next_page'] = $pag_show + 1;
        $result_final['blocks_next'] = array();
        $result_final['blocks_prev'] = array();
        if (is_numeric($num_block_pages)) {
            $num_blocks_next = $num_block_pages;
            $num_blocks_prev = $num_block_pages;
            $pag_show_aux = $pag_show + 1;
            if (($pag_show_aux + $num_blocks_next) > $result_final['num_pages']) {
                $max_blocks_next = $num_blocks_next - (($pag_show_aux + $num_blocks_next) - $result_final['num_pages']);
            } else {
                $max_blocks_next = $num_blocks_next;
            }

            if (($pag_show_aux - $num_blocks_prev) <= 0) {
                $max_blocks_prev = $num_blocks_prev - ($num_blocks_prev - $pag_show_aux + 1);
            } else {
                $max_blocks_prev = $num_blocks_prev;
            }

            if (($max_blocks_prev + $max_blocks_next) < $total_block) {
                if ($max_blocks_prev < $max_blocks_next) {
                    $max_blocks_next = $max_blocks_next + ($num_block_pages - $max_blocks_prev);
                } elseif ($max_blocks_prev > $max_blocks_next) {
                    $max_blocks_prev = $max_blocks_prev + ($num_block_pages - $max_blocks_next);
                }
            }
            $j = 0;
            for ($i = $pag_show + 1; $j < $max_blocks_next && $i < $result_final['num_pages']; $i ++) {
                $result_final['blocks_next'][] = $i;
                $j ++;
            }
            $j = 0;
            for ($i = $pag_show - 1; $j < $max_blocks_prev && $i >= 0; $i --) {
                $result_final['blocks_prev'][] = $i;
                $j ++;
            }
            if (count($result_final['blocks_prev']) > 0)
                sort($result_final['blocks_prev']);
        }
        unset($result);
        unset($result_page);
        return $result_final;
    }
    /*
     * public function querySearch($columns, $table, $join = "", $condition = "", $order = "", $fields = array()) { //TODO: Programar query especial para búsquedas }
     */
    /**
     * executeCommand
     * Ejecuta la query que se ha creado.
     *
     * @param
     *            debug: devuelve la query que se ejecuta
     * @param
     *            set_log: guardar si/no los datos en el log
     */
    public function executeCommand($debug = false, $set_log = true)
    {
        $result = array();
        $ok = true;
        if ($this->action == 'INSERT') {
            $sql = "INSERT INTO " . $this->getTable() . " (" . $this->getFields('all', 'string') . ") VALUES (";
            $parms = $this->getParams('P');
        } elseif ($this->action == 'UPDATE') {
            $sql = "UPDATE " . $this->getTable() . " SET " . $this->getFields('all', 'string') . " ";
            $parms = $this->getParams('PPC');
        } elseif ($this->action == 'DELETE') {
            $sql = "DELETE FROM " . $this->getTable() . "";
            $parms = $this->getParams('PC');
        }
        $debug_params = $parms;
        $types = "";
        $cond = "";
        $num_params = count($parms);
        if ($num_params > 0) {
            foreach ($parms as $key => $val) {
                $key = "";
                $val = utf8_encode($val);
            }
        }
        if ($num_params > 0) {
            for ($i = 0; $i < $num_params; $i ++) {
                if (strlen($cond) > 0 && $this->action == 'INSERT')
                    $cond .= ",?";
                else
                    $cond .= "?";
            }
        }
        if ($this->action == 'INSERT')
            $sql .= $cond . ")";
        if (strlen($this->conds) > 0)
            $sql .= " WHERE " . $this->conds;
        if (strlen($this->conds) == 0 && $this->action == 'U') {
            $ok = false;
            $error = "[ERROR Hidra_DB::command] En update obligatorio mandar parámetros";
        }
        if ($ok === true) {
            $results = $this->h_db->lunchQuery($sql, $parms, 'PUT', $debug);
            if ($set_log == true) {
                $ok = $this->insertLog($sql, $parms);
            }
        } else {
            $results = $error;
        }
        $this->removeQuery();
        if ($ok) {
            return $results;
        } else {
            return false;
        }
    }

    /**
     * Command
     *
     * @param
     *            type: tipo de comando: INSERT, UPDATE, DELETE
     * @param
     *            columns: columnas de las tablas que se van a seleccionar en la consulta SQL
     * @param
     *            table: tabla que se seleccionará en la consulta
     * @param
     *            condition condiciones de unión
     * @param
     *            params: parámetros de la consulta SQL
     */
    public function command($type, $columns, $table, $condition, $parms, $debug = false, $set_log = true)
    {
        $result = array();
        $ok = true;
        $debug_params = $parms;
        if ($type == 'INSERT') {
            $sql = "INSERT INTO " . $table . "  (" . $columns . ") VALUES (";
        } elseif ($type == 'UPDATE') {
            $sql = "UPDATE " . $table . " SET " . $columns . " ";
        } elseif ($type == 'DELETE') {
            if (strlen($columns) > 0) {
                $ok = false;
                $error = "[ERROR " . get_class($this) . "::" . __FUNCTION__ . "::" . __LINE__ . "] Se produjo un error al ejecutar la sentencia SQL: DELETE no puede llevar columnas que editar \n";
            }
            $sql = "DELETE FROM " . $table . "";
        }
        $types = "";
        $cond = "";
        $num_params = count($parms);
        foreach ($parms as $key => $val) {
            $key = "";
            $val = utf8_encode($val);
        }
        if ($num_params > 0) {
            for ($i = 0; $i < $num_params; $i ++) {
                $types .= "s";
                if (strlen($cond) > 0 && $type == "INSERT")
                    $cond .= ",?";
                else
                    $cond .= "?";
            }
        }
        if ($type == "INSERT")
            $sql .= $cond . ")";
        if (strlen($condition) > 0)
            $sql .= " WHERE " . $condition;
        if (strlen($condition) == 0 && $type == 'UPDATE') {
            $ok = false;
            $error = "[ERROR " . get_class($this) . "::" . __FUNCTION__ . "::" . __LINE__ . "] En update obligatorio mandar parámetros";
        }
        if ($ok === true) {
            $results = $this->h_db->lunchQuery($sql, $parms, 'PUT', $debug);
            if ($set_log == true) {
                $ok = $this->insertLog($sql, $parms);
            }
        } else {
            $results = $error;
        }
        $this->removeQuery();
        if ($ok) {
            return $results;
        } else {
            return false;
        }
    }

    /**
     * setAction
     * Define que tipo de query se va a construir
     *
     * @param
     *            $type
     */
    public function setAction($type)
    {
        $type = mb_strtoupper($type, 'UTF-8');
        if (array_key_exists($type, $this->actions)) {
            $this->action = $this->actions[$type];
        } else {
            $texto_error = "[ERROR " . get_class($this) . "::" . __FUNCTION__ . "::" . __LINE__ . "] No se ha seleccionado una acción válida\n";
            $result = $this->sendError($texto_error);
        }
        if (strlen($this->action) > 0)
            $result = true;
        else
            $result = false;

        return $result;
    }
    /**
     * setLastID
     * asigna valores table
     */
    public function setLastID($value)
    {
        $this->lastID = $value;
    }
    /**
     * setTable
     * asigna valores table
     */
    public function getLastID()
    {
        return $this->lastID;
    }

    /**
     * setTable
     * asigna valores table
     */
    public function setTable($table)
    {
        $this->table = $table;
    }

    /**
     * setTable
     * devuelve valores table
     */
    public function getTable()
    {
        return $this->table;
    }

    /**
     * setTable
     * devuelve valores table
     */
    public function removeTable()
    {
        $this->table = "";
    }

    /**
     * setField
     * asigna valores fields
     */
    public function setField($field)
    {
        if (strlen($field) > 0 && ! in_array($field, $this->fields))
            $this->fields[] = $field;
    }

    /**
     * getFields
     * devuelve los campos de la tabla de clientes
     *
     * @param $num string:
     *            número de resultados -> numérico o "all"
     * @param $method string:
     *            forma de devolverlo -> "string" o "array"
     */
    public function getFields($num = 'all', $method = 'array')
    {
        if ($method == 'array') {
            if ($num == 'all') {
                return (count($this->fields) > 0) ? $this->fields : false;
            } else {
                if (is_numeric($num))
                    return (strlen($this->fields[$num]) > 0) ? $this->fields[$num] : false;
                else
                    return false;
            }
        } elseif ($method == 'string') {
            if ($num == 'all') {
                $num_fields = count($this->fields);
            } elseif (is_numeric($num)) {
                $num_fields = $num;
            } else {
                return false;
            }
            $fields = "";
            $sep = "";
            if ($num_fields > 0) {
                // print_r($this->fields);
                for ($i = 0; $i < $num_fields; $i ++) {
                    if (($i + 1) == $num_fields) {
                        // $sep="";
                    }
                    if ($this->action == 'UPDATE') {
                        $fields .= $sep . $this->fields[$i] . "=?";
                    } else {
                        $fields .= $sep . $this->fields[$i];
                    }
                    $sep = ", ";
                }
            } else {
                return false;
            }
            return $fields;
        } else {
            return false;
        }
    }

    /**
     * setField
     * asigna valores fields
     */
    public function removeFields()
    {
        $this->fields = array();
    }

    /**
     *
     * @param
     *            field string
     * @return boolean
     */
    public function checkField($field)
    {
        return (! in_array($field, $this->fields)) ? true : false;
    }

    /**
     * Añade campos a la select.
     * Es diferente a la forma de con INSERT, UPDATE, DELETE ya que pueden ir como A.CAMPO1, B.CAMPO2...
     *
     * @param
     *            fields string/array
     * @return boolean
     */
    public function setSelectFields($fields)
    {
        if (is_array($fields)) {
            $fieldAr = $fields;
        } else {
            $fieldAr = explode(",", $fields);
        }
        $numField = count($fieldAr);
        if ($numField > 0) {
            for ($i = 0; $i < $numField; $i ++) {
                if ($this->checkField(trim($fieldAr[$i])))
                    $this->setField(trim($fieldAr[$i]));
            }
            return true;
        } else {
            return false;
        }
    }

    /**
     * setIUField
     * Añade un campo para ir construyendo la query
     *
     * @param $field_name: Nombre
     *            del campo de la BBDD
     * @param $value: valor
     *            a asignar
     *            * @param $not_null: si not_null = true, no se modificará el campo si la variable $value está vacía
     */
    public function setIUField($field_name, $value, $null = false, $blank = true)
    {
        if ($blank === false && $null === false && strlen($value) == 0) {
            // TODO:
        } elseif ($this->checkField(trim($field_name))) {
            $this->setField(trim($field_name));
            if (strlen($value) == 0) {
                if ($null === true)
                    $value = null;
                elseif ($blank === true)
                    $value = "";
            }
            $this->setParam($value);
        }
    }

    /**
     * setCond
     * Añade una condición cuando se trata de UPDATES o DELETES
     *
     * @param $fields_name: Nombre
     *            de la tabla a seleccionar
     */
    public function setCond($fields_name, $values, $valueCond = "", $sep = "AND", $opLogic = " = ")
    {
        if (is_array($values)) {
            $num_values = count($values);
            for ($i = 0; $i < $num_values; $i ++) {
                $this->setCondParam($values[$i]);
            }
        } elseif (strlen($values) > 0) {
            $this->setCondParam($values);
        } else {
            $this->setCondParam($values);
        }
        if (strlen($this->conds) > 0) {
            $this->conds .= " " . $sep . " " . $fields_name . " " . $opLogic . " " . $valueCond . "(?) ";
        } else {
            $this->conds = $fields_name . " " . $opLogic . " " . $valueCond . "(?)";
        }
        return true;
    }

    /**
     * getParams
     */
    public function getParams($action)
    {
        if ($action == 'PPC')
            return array_merge((array) $this->params, (array) $this->params_cond);
        elseif ($action == 'P')
            return $this->params;
        elseif ($action == 'PC')
            return $this->params_cond;
        else
            return array_merge((array) $this->params, (array) $this->params_cond);
    }

    /**
     * setCondParam
     */
    public function setCondParam($values = "")
    {
        $this->params_cond[] = $values;
        return true;
    }

    /**
     * setCondParam
     */
    public function setParam($values)
    {
        $this->params[] = $values;
        return true;
    }

    /**
     * getCond
     */
    public function getConds()
    {
        return $this->conds;
    }

    /**
     * setJoin
     */
    public function setJoin($join)
    {
        $this->join = $join;
    }

    /**
     * getJoin
     */
    public function getJoin()
    {
        return $this->join;
    }

    /**
     * setOrder
     */
    public function setOrder($order)
    {
        $this->order = $order;
    }

    /**
     * getJoin
     */
    public function getOrder()
    {
        return $this->order;
    }

    public function removeQuery()
    {
        $this->action = ""; // unset($this->action);
        $this->removeTable(); // unset($this->table);
        $this->removeFields(); // unset($this->query);
        $this->setJoin("");
        $this->setOrder("");
        $this->params = array(); // unset($this->params);
        $this->conds = ""; // unset($this->conds);
        $this->params_cond = array(); // unset($this->params_cond);
    }

    public function showConstructQuery()
    {
        echo "==================================================<br>";
        echo "+ SERVER:      " . mysql_get_server_info() . "                               ++<br>";
        echo "+ HOST:        " . mysql_get_host_info() . "                                 ++<br>";
        echo "+ CLIENT:      " . mysql_get_client_info() . "                               ++<br>";
        echo "+ ACTION:      " . $this->action . "                                          ++<br>";
        echo "+ TABLE:       " . $this->table . "                                   \r\t\r\t++<br>";
        echo "+ QUERY:       " . $this->getFields('all', 'string') . "                       ++<br>";
        echo "+ PARAMS:      <pre>" . print_r($this->params, true) . "</pre>      ++<br>";
        echo "+ CONDS:       " . $this->conds . "                                ++<br>";
        echo "+ PARAMS_COND: <pre>" . print_r($this->params_cond, true) . "</pre> ++<br>";
        echo "==================================================<br>";
    }

    public function insertLog($sql, $parms)
    {
        $log_params = array();
        $log_params[] = (isset($_SESSION['userID'])) ? @$_SESSION['userID'] : "public";
        if (isset($_SERVER["REMOTE_ADDR"])) {
            $log_params[] = $_SERVER["REMOTE_ADDR"];
        } else {
            $log_params[] = "";
        }
        $text_params = print_r($parms, true);
        $log_params[] = "# QUERY:\n" . $sql . "\n# PARAMS:\n" . $text_params . "\n";
        $ok = $this->command("INSERT", "FK_USER,IP,ACTION", "ceo_log", "", $log_params, false, false);
        return $ok;
    }

    /**
     * Devuelve el número de registros de una tabla
     *
     * @param string $table
     * @param string $join
     * @param string $condition
     * @param array $parms
     * @param string $debug
     * @return boolean
     */
    private function getMaxReg($table, $join, $condition, $parms, $debug = false)
    {
        $num_reg = $this->query("COUNT(ID) NUM_REG", $table, $join, $condition, "", $parms, $debug = false);
        if (count($num_reg) == 0) {
            return false;
        } else {
            return $num_reg[0]['NUM_REG'];
        }
    }

    /**
     * startTransaction
     * Desactiva autocomit para si se produce un error no guardar el proceso
     * $arr: Array a dividir
     */
    public function startTransaction()
    {
        return $this->h_db->startTransaction();
    }

    /**
     * endTransaction
     * Desactiva autocomit para si se produce un error no guardar el proceso
     */
    public function endTransaction($result)
    {
        return $this->h_db->endTransaction($result);
    }
}
