<?php
namespace Olif;

interface Idb {
    public function connect($dbhost, $dbuser, $dbpass, $dbname, $port, $socket, $return_error);
    public function close();
    public function query($columns, $table, $join = "", $condition = "", $order = "", $parms = array(), $debug = false);
    public function masterQuery($sql, $parms, $debug = false);
    //public function command($type, $columns, $table, $condition, $parms, $debug = false, $set_log = true);
    //public function setAction($type);
    //public function insertLog($sql, $parms);
    public function startTransaction();
    public function endTransaction($result);
}
