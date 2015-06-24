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

use Olif\Idb;

require_once CORE_ROOT . LIBS . DIRECTORY_SEPARATOR . "Idb.php";

require_once CORE_ROOT . MODELS . DIRECTORY_SEPARATOR . "ModelApp.php";

class ModelDBMySQL extends ModelApp implements Idb
{

    /**
     * $h_db
     * Objeto donde se instancia la clase mysqli
     */
    private $con;

    /**
     * connect
     * Crea conexión SQL
     */
    public function connect($dbhost, $dbuser, $dbpass, $dbname, $port, $socket, $return_error = false)
    {
        //TODO: NO DA FALLO CON CONEXIÓN MALA
        $result = true;
        ini_set("display_errors", 0);
        $this->con = mysqli_init();
        if (! $this->con) {
            die('mysqli_init failed');
        }
        if (! $this->con->options(MYSQLI_OPT_CONNECT_TIMEOUT, 5)) {
            die('Setting MYSQLI_OPT_CONNECT_TIMEOUT failed');
        }
        if (! $this->con->real_connect($dbhost, $dbuser, $dbpass, $dbname, $port)) {
            if ($return_error === true) {
                $result = mysqli_connect_errno();
            }
        }
        ini_set("display_errors", 1);
        /* check connection */
        if (($this->con->connect_error || $this->con->error) && $return_error === false) {
            $texto_error = "[ERROR " . get_class($this) . "::" . __FUNCTION__ . "::" . __LINE__ . "] Se produjo un error: " . $this->con->connect_error . "\n" . $this->con->error;
            $this->sendError($texto_error);
        }
        if($result===true){
            $this->con->set_charset("utf8");
        }
        return $result;
    }

    public function close()
    {
        $result = $this->con->close();
        if (! $result)
            $this->sendError("[" . get_class($this) . "::" . __FUNCTION__ . "::" . __LINE__ . "] ERROR cerrando conexión a BBDD ID <b>" . $this->con_id . "</b>");
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

        $result = array();
        $debug_params = $parms;
        $sql = "SELECT " . $columns . " FROM " . $table . " " . $join;
        if (strlen($condition) > 0) {
            if (strpos($join, 'GROUP BY') !== false) {
                $key_cond = " HAVING ";
            } else {
                $key_cond = " WHERE ";
            }
            $sql .= $key_cond . "" . $condition;
        }
        if (strlen($order) > 0)
            $sql .= " " . $order;
        return $this->lunchQuery($sql, $parms, 'GET', $debug);
    }

    /**
     *
     * @param unknown $sql
     * @param unknown $parms
     * @param unknown $action:
     *            GET; PUT;
     * @return multitype:multitype:unknown multitype:mixed
     */
    public function lunchQuery($sql, $parms, $action, $debug = false)
    {
        $results = false;
        $parameters = array();
        $stmt = $this->con->prepare($sql);
        if (! $stmt) {
            $texto_error = "[ERROR " . get_class($this) . "::" . __FUNCTION__ . "::" . __LINE__ . "] Se produjo un error al ejecutar la sentencia SQL: " . $sql . ".<br> El error fue: " . $this->con->error . "\n";
            $result = $this->sendError($texto_error);
        }
        $types = "";
        $num_params = count($parms);
        if ($num_params > 0) {
            for ($i = 0; $i < $num_params; $i ++) {
                $types .= "s";
            }
            $parms = array_merge((array) $types, (array) $parms);
            call_user_func_array(array(
                $stmt,
                "bind_param"
            ), $this->refValues($parms)) or die("[ERROR " . get_class($this) . "::" . __FUNCTION__ . "::" . __LINE__ . "] Query. Se produjo un error al ejecutar la sentencia ".$action." SQL: query: " . $sql . "<br>Params:" . json_encode($parms) . ". El error fue: " . $this->con->error);
        }
        unset($parms);
        $results = $stmt->execute();
        if ($this->con->error) {
            $texto_error = "[ERROR " . get_class($this) . "::" . __FUNCTION__ . "::" . __LINE__ . "] Se produjo un error: " . $this->con->error . "\n";
            $result = $this->sendError($texto_error);
        }

        if ($results == true) {
            if ($action == 'GET') {
                $meta = $stmt->result_metadata();
                while ($field = $meta->fetch_field()) {
                    $parameters[] = &$row[$field->name];
                }
                call_user_func_array(array(
                    $stmt,
                    'bind_result'
                ), $this->refValues($parameters));
                unset($parameters);
                $results = array();
                while ($stmt->fetch()) {
                    $x = array();
                    foreach ($row as $key => $val) {
                        $val = str_replace("\\\"", '"', $val);
                        $x[$key] = ($val);
                    }
                    $results[] = $x;
                }
            } elseif ($action == 'PUT') {
                $results = array();
                $results['ID'] = $this->con->insert_id;
            }
        }
        if ($debug == true) {
            $results['debug'] = array(
                'SQL' => $sql,
                'params' => $debug_params
            );
        }

        return $results;
    }

    public function masterQuery($sql, $parms = array(), $returnResults = true, $debug = false)
    {
        $result = array();
        $debug_params = $parms;
        $stmt = $this->con->prepare($sql);
        if (! $stmt) {
            $texto_error = "[ERROR " . get_class($this) . "::" . __FUNCTION__ . "::" . __LINE__ . "] Se produjo un error al ejecutar la sentencia SQL: " . $sql . ".<br> El error fue: " . $this->con->error . "\n";
            $result = $this->sendError($texto_error);
        } else {
            $types = "";
            if (count($parms) > 0) {
                $num_params = count($parms);
                if ($num_params > 0) {
                    for ($i = 0; $i < $num_params; $i ++) {
                        $types .= "s";
                    }
                    $parms = array_merge((array) $types, (array) $parms);
                    call_user_func_array(array(
                        $stmt,
                        "bind_param"
                    ), $this->refValues($parms));
                }
            }
            unset($parms);
            $stmt->execute();
            if ($this->con->error) {
                $texto_error = "[ERROR " . get_class($this) . "::" . __FUNCTION__ . "::" . __LINE__ . "] Se produjo un error: " . $this->con->error . "\n";
                $result = $this->sendError($texto_error, WEB_URL);
            }
            $meta = $stmt->result_metadata();
            $results = array();
            $this->lastID = $this->con->insert_id;
            if ($returnResults) {
                while ($field = $meta->fetch_field()) {
                    $parameters[] = &$row[$field->name];
                }
                call_user_func_array(array(
                    $stmt,
                    'bind_result'
                ), $this->refValues($parameters));
                unset($parameters);
                while ($stmt->fetch()) {
                    $x = array();
                    foreach ($row as $key => $val) {
                        $val = str_replace("\\\"", '"', $val);
                        $x[$key] = utf8_encode($val);
                    }
                    $results[] = $x;
                }
                if ($debug == true) {
                    $results['debug'] = array(
                        'SQL' => $sql,
                        'params' => $debug_params
                    );
                }
            }
        }
        return $results;
    }

    /**
     *
     * @param array $arr
     * @return array
     */
    private function refValues($arr)
    {
        if (strnatcmp(phpversion(), '5.3') >= 0) {
            $refs = array();
            foreach ($arr as $key => $value) {
                $value = $value;
                $refs[$key] = &$arr[$key];
            }
            $refs[$key] = $this->con->real_escape_string($refs[$key]);
            return $refs;
        }
        return $arr;
    }

    /**
     * startTransaction
     * Desactiva autocomit para si se produce un error no guardar el proceso
     * $arr: Array a dividir
     */
    public function startTransaction()
    {
        $this->con->autocommit(false);
        return true;
    }

    /**
     * endTransaction
     * Desactiva autocomit para si se produce un error no guardar el proceso
     */
    public function endTransaction($result)
    {
        if ($result === true) {
            $this->con->commit();
        }
        if ($result === false) {
            $this->con->rollback();
        }
        $this->con->autocommit(true);
        return true;
    }
}
