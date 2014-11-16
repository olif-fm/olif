<?php
/**
 * ModelAccess
 * @version V 1.7
 * @author Alberto Vara (C) Copyright 2013
 * @package OLIF.ModelAccess
 *
 * Control Data of the page to load
 * DEPENDS/REQUIRED:
 * + models/ModelApp
 * + models/modelDB
 */
namespace Olif;

require_once CORE_ROOT.MODELS.DIRECTORY_SEPARATOR."ModelApp.php";

class ModelAccess extends ModelApp {
    /**
     * table
     * Tabla que se va a atacar
     * @var string
     * */
    private $table;
    /**
     * fields
     * Campos que contendrá el usuario de la tabla "cliente"
     * @var string
     * */
    protected $fields;
    /**
     * $fields_user_show
     * Campo/s que se mostrarán del "cliente"
     */
    protected $fields_user_show = "";
    /**
     * field_user
     * Campo que contendrá el usuario de la tabla "cliente" a checkear
     * */
    protected $field_user;
    /**
     * field_user_openid
     * */
    protected $field_user_openid;
    /**
     * $field_pass
     * Campo que contendrá la contraseña de la tabla "cliente" a checkear
     */
    protected $field_pass = "";

    /**
     * $field_rol
     * Campo que contendrá el nivel de usuario
     */
    protected $field_rol = "";

    /**
     * $field_last_access
     * Campo que contendrá el último acceso de usuario
     */
    protected $field_last_access = "";

    protected $field_token = "SESSION_TOKEN";

    protected $field_validate = "VALIDATE";

    public function __construct() {
        parent::__construct();
        $this->getModelDB();
    }
    /**
     * setAccess
     * Define que tabla será la que se utilizará para checkear y acceder
     * @param string $access_table tabla a la que se accede
     * @param string $menu_fields campos que tendrá la tabla
     * */
    public function setFieldsAccess($access_table, $acces_fields) {
        $this->setTableAccess($access_table);
        $this->setFields($acces_fields);
    }
    /**
     * setTableUser
     * asigna valores al array fields
     * @param access_table string
     */
    public function setTableAccess($access_table) {
        $this->table=$access_table;
    }
    /**
     * setFields
     * @param field string
     */
    public function setFields($field) {
        $this->fields=$field;
    }
    /**
     * getFieldUser
     *
     */
    public function getTableAccess() {
        return $this->table;
    }
    /**
     * setFieldUserNameShow
     * asigna valores al array fields
     */
    public function setFieldUserNameShow($fields) {
        $this->fields_user_show=$fields;
    }
    /**
     * getFieldUser
     *
     */
    public function getFieldUserNameShow() {
        return $this->fields_user_show;
    }
    /**
     * setFieldPass
     *
     */
    public function setFieldUser($field) {
        $this->field_user=$field;
    }
    /**
     * getFieldPass
     *
     */
    public function getFieldUser() {
        return $this->field_user;
    }
    /**
     * setFieldUserOpenId
     *
     */
    public function setFieldUserOpenId($field) {
        if (strlen($field)>0) {
            $this->field_user_openid=$field;
        } else {
            return false;
        }
    }
    public function getFieldUserOpenId() {
        return $this->field_user_openid;
    }
    /**
     * setFieldPass
     * Campo de la contraseña
     */
    public function setFieldPass($field) {
        if (strlen($field)>0) {
            $this->field_pass=$field;
        } else {
            return false;
        }

    }
    /**
     * getFieldPass
     * Campo de la contraseña
     */
    public function getFieldPass() {
        return $this->field_pass;
    }
    /**
     * setFieldPass
     * Campo de la contraseña
     */
    public function setFieldRol($field) {
        $this->field_rol=$field;
    }
    /**
     * getFieldPass
     * Campo de la contraseña
     */
    public function getFieldRol() {
        return $this->field_rol;
    }
    /**
     * setFieldPass
     * Campo de la contraseña
     */
    public function setFieldLastAccess($field) {
        $this->field_last_access=$field;
    }
    /**
     * getFieldPass
     * Campo de la contraseña
     */
    public function getFielLastAccess() {
        return $this->field_last_access;
    }
    /**
     * getFieldUser
     * @return string fields
     */
    public function getFields() {
        return $this->fields;
    }
    /**
     * setAccess
     * En base a las condiciones que se hayan definido con con setTable.
     * LAS CONDICIONES DE LA QUERY SE DEFINEN EN ControllerAccess::setLoginActions()
     * @return array / bool si count = 0
     */
    public function setAccess() {
        $this->db->setField("ID");
        $this->db->setField($this->getFieldUser());
        $this->db->setField($this->getFieldUserNameShow());
        $this->db->setField($this->getFieldUserOpenId());
        $this->db->setField($this->getFieldRol());
        $this->db->setField($this->getFielLastAccess());
        $this->db->setTable($this->getTableAccess());
        //$this->db->showConstructQuery();
        $result=$this->db->executeQuery();
        //print_r($result);
        return ((count($result)==1)? $result : false);
    }
    public function setAccessToken($idUser, $token) {
        $this->db->setAction('U');
        $this->db->setIUFIeld('SESSION_TOKEN', $token);
        $this->db->setCond('ID', $idUser);
        $this->db->setTable($this->getTableAccess());
        return $this->db->executeCommand();

    }
    public function setCondUser($User) {
        return ((strlen($this->getFieldUser())>0)?$this->db->setCond($this->getFieldUser(), $User, ''):false);
    }
    public function setCondIdUser($idUser) {
        $this->db->setCond('ID', $idUser);
        return true;
    }
    public function setCondIdUserOpenId($email) {
        return ((strlen($this->getFieldUserOpenId())>0)?$this->db->setCond($this->getFieldUserOpenId(), $email):false);
    }
    public function setCondPass($passUser) {
        return ((strlen($this->field_pass)>0)?$this->db->setCond($this->field_pass, $passUser, 'SHA1'):false);
    }
    public function setCondToken($token) {
        return ((strlen($this->field_token)>0)?$this->db->setCond($this->field_token, $token):false);
    }
    public function setCondValidate() {
        return $this->db->setCond($this->field_validate, '1');
    }
    public function setCondStatus() {
        return $this->db->setCond('STATUS', '1');
    }
    public function checkAccess($idUser, $token = "") {
        $this->db->setTable($this->getTableAccess());
        $this->db->setSelectFields("ID");
        $this->setCondIdUser($idUser);
        if (strlen($token)>0)$this->setCondToken($token);
        $result=$this->db->executeQuery();
        if (count($result)==1)$this->updateAccess($idUser, $token);
        return ((count($result)==1)? $result : false);
    }
    public function updateAccess($idUser, $token) {
        $this->db->setAction('U');
        $this->db->setIUFIeld('SESSION_LAST_ACCESS', date("Y-m-d H:i:s"));
        $this->db->setIUFIeld('SESSION_TOKEN', $token);
        $this->db->setCond('ID', $idUser);
        $this->db->setTable($this->getTableAccess());
        return $this->db->executeCommand();
    }
}
